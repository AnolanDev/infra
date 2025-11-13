<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'assignedUser'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('assigned_to')) {
            $query->assignedTo($request->assigned_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        // Filtro de tickets abiertos/cerrados
        if ($request->filled('show_closed')) {
            if (!$request->show_closed) {
                $query->open();
            }
        } else {
            // Por defecto, mostrar solo tickets abiertos
            $query->open();
        }

        $tickets = $query->paginate(15)->withQueryString();

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'priority', 'category', 'assigned_to', 'search', 'show_closed']),
            'statuses' => Ticket::getStatuses(),
            'priorities' => Ticket::getPriorities(),
            'categories' => Ticket::getCategories(),
            'users' => User::active()->orderBy('name')->get(['id', 'name']),
            'stats' => [
                'open' => Ticket::open()->count(),
                'in_progress' => Ticket::byStatus(Ticket::STATUS_IN_PROGRESS)->count(),
                'pending' => Ticket::byStatus(Ticket::STATUS_PENDING)->count(),
                'overdue' => Ticket::overdue()->count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        return Inertia::render('Tickets/Create', [
            'priorities' => Ticket::getPriorities(),
            'categories' => Ticket::getCategories(),
            'users' => User::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:' . implode(',', array_keys(Ticket::getPriorities())),
            'category' => 'required|in:' . implode(',', array_keys(Ticket::getCategories())),
            'location' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after:now',
        ]);

        $ticket = Ticket::create([
            ...$validated,
            'user_id' => auth()->id(),
            'status' => $validated['assigned_to'] ?? null
                ? Ticket::STATUS_OPEN
                : Ticket::STATUS_NEW,
        ]);

        // Si se asigna directamente
        if (!empty($validated['assigned_to'])) {
            $assignedUser = User::find($validated['assigned_to']);
            $ticket->assignTo($assignedUser);
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket creado exitosamente.');
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load([
            'user',
            'assignedUser',
            'comments' => function ($query) {
                $query->with('user')->orderBy('created_at', 'asc');
            },
        ]);

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            'users' => User::active()->orderBy('name')->get(['id', 'name']),
            'statuses' => Ticket::getStatuses(),
            'priorities' => Ticket::getPriorities(),
            'canEdit' => $this->canEditTicket($ticket),
        ]);
    }

    /**
     * Show the form for editing the ticket.
     */
    public function edit(Ticket $ticket)
    {
        if (!$this->canEditTicket($ticket)) {
            abort(403, 'No tienes permiso para editar este ticket.');
        }

        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket,
            'priorities' => Ticket::getPriorities(),
            'categories' => Ticket::getCategories(),
            'users' => User::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified ticket.
     */
    public function update(Request $request, Ticket $ticket)
    {
        if (!$this->canEditTicket($ticket)) {
            abort(403, 'No tienes permiso para editar este ticket.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:' . implode(',', array_keys(Ticket::getPriorities())),
            'category' => 'required|in:' . implode(',', array_keys(Ticket::getCategories())),
            'location' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
        ]);

        $ticket->update($validated);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket actualizado exitosamente.');
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Ticket::getStatuses())),
        ]);

        $oldStatus = $ticket->status;
        $ticket->update(['status' => $validated['status']]);

        // Agregar comentario automático del cambio de estado
        $ticket->addComment(
            "Estado cambiado de '{$ticket->getStatusLabelAttribute()}' a '{$ticket->status_label}'",
            'status_change',
            true
        );

        return back()->with('success', 'Estado actualizado exitosamente.');
    }

    /**
     * Assign ticket to a user.
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $user = User::find($validated['assigned_to']);
        $ticket->assignTo($user);

        return back()->with('success', "Ticket asignado a {$user->name}.");
    }

    /**
     * Add comment to ticket.
     */
    public function addComment(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
            'type' => 'required|in:public,internal,solution',
            'is_private' => 'boolean',
        ]);

        $ticket->addComment(
            $validated['comment'],
            $validated['type'],
            $validated['is_private'] ?? false
        );

        return back()->with('success', 'Comentario agregado exitosamente.');
    }

    /**
     * Mark ticket as resolved.
     */
    public function resolve(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'solution' => 'nullable|string',
        ]);

        $ticket->markAsResolved($validated['solution'] ?? null);

        return back()->with('success', 'Ticket marcado como resuelto.');
    }

    /**
     * Mark ticket as closed.
     */
    public function close(Ticket $ticket)
    {
        $ticket->markAsClosed();

        return back()->with('success', 'Ticket cerrado exitosamente.');
    }

    /**
     * Reopen a ticket.
     */
    public function reopen(Ticket $ticket)
    {
        if (!$ticket->isClosed() && !$ticket->isResolved()) {
            return back()->with('error', 'Solo se pueden reabrir tickets cerrados o resueltos.');
        }

        $ticket->reopen();

        return back()->with('success', 'Ticket reabierto exitosamente.');
    }

    /**
     * Remove the specified ticket.
     */
    public function destroy(Ticket $ticket)
    {
        // Solo el creador o admin puede eliminar
        if ($ticket->user_id !== auth()->id() && !auth()->user()->isGlpiAdmin()) {
            abort(403, 'No tienes permiso para eliminar este ticket.');
        }

        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket eliminado exitosamente.');
    }

    /**
     * Check if user can edit ticket.
     */
    private function canEditTicket(Ticket $ticket): bool
    {
        $user = auth()->user();

        // El creador puede editar
        if ($ticket->user_id === $user->id) {
            return true;
        }

        // El asignado puede editar
        if ($ticket->assigned_to === $user->id) {
            return true;
        }

        // Los admin pueden editar cualquiera
        if ($user->isGlpiAdmin()) {
            return true;
        }

        return false;
    }
}
