<template>
  <AppLayout>
    <div class="mx-auto max-w-5xl space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Link
            :href="route('tickets.index')"
            class="rounded-lg p-2 text-secondary-600 transition-colors hover:bg-secondary-100 hover:text-secondary-900"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-secondary-900">{{ ticket.ticket_number }}</h1>
            <p class="mt-1 text-sm text-secondary-600">
              Creado {{ formatDate(ticket.created_at) }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <Link
            v-if="canEdit"
            :href="route('tickets.edit', ticket.id)"
            class="rounded-lg border border-secondary-300 bg-white px-4 py-2 text-sm font-semibold text-secondary-700 shadow-sm hover:bg-secondary-50"
          >
            Editar
          </Link>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="space-y-6 lg:col-span-2">
          <!-- Ticket Details -->
          <Card variant="elevated">
            <div class="space-y-4">
              <div>
                <h2 class="text-xl font-bold text-secondary-900">{{ ticket.title }}</h2>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                  <span
                    :class="getStatusBadgeClass(ticket.status_color)"
                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                  >
                    {{ ticket.status_label }}
                  </span>
                  <span
                    :class="getPriorityBadgeClass(ticket.priority_color)"
                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                  >
                    {{ ticket.priority_label }}
                  </span>
                  <span class="inline-flex items-center gap-1 text-sm text-secondary-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    {{ ticket.category_label }}
                  </span>
                </div>
              </div>

              <div class="border-t border-secondary-200 pt-4">
                <h3 class="text-sm font-medium text-secondary-900">Descripción</h3>
                <p class="mt-2 whitespace-pre-wrap text-sm text-secondary-700">
                  {{ ticket.description }}
                </p>
              </div>
            </div>
          </Card>

          <!-- Comments Section -->
          <Card variant="elevated">
            <h3 class="mb-4 text-lg font-semibold text-secondary-900">Comentarios</h3>

            <!-- Comments List -->
            <div class="space-y-4">
              <div
                v-for="comment in ticket.comments"
                :key="comment.id"
                :class="[
                  'rounded-lg border p-4',
                  comment.is_private
                    ? 'border-amber-200 bg-amber-50'
                    : 'border-secondary-200 bg-white',
                ]"
              >
                <div class="flex items-start justify-between">
                  <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-600">
                      {{ getInitials(comment.user_name) }}
                    </div>
                    <div>
                      <p class="text-sm font-medium text-secondary-900">{{ comment.user_name }}</p>
                      <p class="text-xs text-secondary-500">{{ formatDate(comment.created_at) }}</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-2">
                    <span
                      v-if="comment.is_private"
                      class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800"
                    >
                      <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                      </svg>
                      Privado
                    </span>
                    <span
                      :class="getCommentTypeBadge(comment.type)"
                      class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                    >
                      {{ comment.type_label }}
                    </span>
                  </div>
                </div>
                <p class="mt-3 whitespace-pre-wrap text-sm text-secondary-700">
                  {{ comment.comment }}
                </p>
              </div>

              <div v-if="ticket.comments.length === 0" class="py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p class="mt-2 text-sm text-secondary-500">No hay comentarios aún</p>
              </div>
            </div>

            <!-- Add Comment Form -->
            <div class="mt-6 border-t border-secondary-200 pt-6">
              <form @submit.prevent="addComment" class="space-y-4">
                <div>
                  <label for="comment" class="block text-sm font-medium text-secondary-700">
                    Agregar comentario
                  </label>
                  <textarea
                    id="comment"
                    v-model="commentForm.comment"
                    rows="4"
                    placeholder="Escribe tu comentario..."
                    class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    :class="{ 'border-red-500': commentForm.errors.comment }"
                  />
                  <p v-if="commentForm.errors.comment" class="mt-1 text-sm text-red-600">
                    {{ commentForm.errors.comment }}
                  </p>
                </div>

                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2">
                      <input
                        v-model="commentForm.is_private"
                        type="checkbox"
                        class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                      />
                      <span class="text-sm text-secondary-700">Comentario privado</span>
                    </label>

                    <select
                      v-model="commentForm.type"
                      class="rounded-lg border-secondary-300 py-1 text-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                      <option value="public">Público</option>
                      <option value="internal">Interno</option>
                      <option value="solution">Solución</option>
                    </select>
                  </div>

                  <button
                    type="submit"
                    :disabled="commentForm.processing || !commentForm.comment"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 disabled:opacity-50"
                  >
                    Agregar
                  </button>
                </div>
              </form>
            </div>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Quick Actions -->
          <Card variant="elevated">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-secondary-500">
              Acciones
            </h3>
            <div class="space-y-2">
              <!-- Change Status -->
              <div v-if="ticket.status !== 'cerrado'">
                <label class="block text-xs font-medium text-secondary-700">Cambiar estado</label>
                <select
                  v-model="statusForm.status"
                  @change="updateStatus"
                  class="mt-1 block w-full rounded-lg border-secondary-300 py-2 text-sm focus:border-primary-500 focus:ring-primary-500"
                >
                  <option v-for="(label, value) in statuses" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
              </div>

              <!-- Assign -->
              <div>
                <label class="block text-xs font-medium text-secondary-700">Asignar a</label>
                <select
                  v-model="assignForm.assigned_to"
                  @change="assignTicket"
                  class="mt-1 block w-full rounded-lg border-secondary-300 py-2 text-sm focus:border-primary-500 focus:ring-primary-500"
                >
                  <option value="">Sin asignar</option>
                  <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                  </option>
                </select>
              </div>

              <!-- Quick Actions Buttons -->
              <div class="space-y-2 pt-4">
                <button
                  v-if="ticket.status !== 'resuelto' && ticket.status !== 'cerrado'"
                  @click="resolveTicket"
                  class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                >
                  Marcar como Resuelto
                </button>

                <button
                  v-if="ticket.status === 'resuelto'"
                  @click="closeTicket"
                  class="w-full rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                >
                  Cerrar Ticket
                </button>

                <button
                  v-if="ticket.status === 'cerrado' || ticket.status === 'resuelto'"
                  @click="reopenTicket"
                  class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                >
                  Reabrir Ticket
                </button>
              </div>
            </div>
          </Card>

          <!-- Details -->
          <Card variant="elevated">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-secondary-500">
              Detalles
            </h3>
            <dl class="space-y-3 text-sm">
              <div>
                <dt class="font-medium text-secondary-900">Reportado por</dt>
                <dd class="mt-1 text-secondary-700">{{ ticket.user_name }}</dd>
              </div>

              <div v-if="ticket.assigned_name">
                <dt class="font-medium text-secondary-900">Asignado a</dt>
                <dd class="mt-1 text-secondary-700">{{ ticket.assigned_name }}</dd>
              </div>

              <div v-if="ticket.location">
                <dt class="font-medium text-secondary-900">Ubicación</dt>
                <dd class="mt-1 text-secondary-700">{{ ticket.location }}</dd>
              </div>

              <div v-if="ticket.department">
                <dt class="font-medium text-secondary-900">Departamento</dt>
                <dd class="mt-1 text-secondary-700">{{ ticket.department }}</dd>
              </div>

              <div v-if="ticket.due_date">
                <dt class="font-medium text-secondary-900">Vencimiento</dt>
                <dd class="mt-1 text-secondary-700">
                  {{ formatDate(ticket.due_date) }}
                  <span v-if="ticket.is_overdue" class="ml-2 text-red-600">(Vencido)</span>
                </dd>
              </div>

              <div v-if="ticket.resolved_at">
                <dt class="font-medium text-secondary-900">Resuelto</dt>
                <dd class="mt-1 text-secondary-700">{{ formatDate(ticket.resolved_at) }}</dd>
              </div>

              <div v-if="ticket.resolution_time">
                <dt class="font-medium text-secondary-900">Tiempo de resolución</dt>
                <dd class="mt-1 text-secondary-700">{{ formatMinutes(ticket.resolution_time) }}</dd>
              </div>
            </dl>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
  ticket: Object,
  users: Array,
  statuses: Object,
  priorities: Object,
  canEdit: Boolean,
});

const commentForm = useForm({
  comment: '',
  type: 'public',
  is_private: false,
});

const statusForm = useForm({
  status: props.ticket.status,
});

const assignForm = useForm({
  assigned_to: props.ticket.assigned_to || '',
});

const addComment = () => {
  commentForm.post(route('tickets.comments', props.ticket.id), {
    preserveScroll: true,
    onSuccess: () => {
      commentForm.reset();
    },
  });
};

const updateStatus = () => {
  statusForm.patch(route('tickets.status', props.ticket.id), {
    preserveScroll: true,
  });
};

const assignTicket = () => {
  assignForm.patch(route('tickets.assign', props.ticket.id), {
    preserveScroll: true,
  });
};

const resolveTicket = () => {
  router.patch(route('tickets.resolve', props.ticket.id), {}, {
    preserveScroll: true,
  });
};

const closeTicket = () => {
  router.patch(route('tickets.close', props.ticket.id), {}, {
    preserveScroll: true,
  });
};

const reopenTicket = () => {
  router.patch(route('tickets.reopen', props.ticket.id), {}, {
    preserveScroll: true,
  });
};

const getStatusBadgeClass = (color) => {
  const classes = {
    blue: 'bg-blue-100 text-blue-800',
    cyan: 'bg-cyan-100 text-cyan-800',
    yellow: 'bg-yellow-100 text-yellow-800',
    orange: 'bg-orange-100 text-orange-800',
    green: 'bg-green-100 text-green-800',
    gray: 'bg-gray-100 text-gray-800',
    red: 'bg-red-100 text-red-800',
  };
  return classes[color] || classes.gray;
};

const getPriorityBadgeClass = (color) => {
  const classes = {
    gray: 'bg-gray-100 text-gray-800',
    blue: 'bg-blue-100 text-blue-800',
    orange: 'bg-orange-100 text-orange-800',
    red: 'bg-red-100 text-red-800',
  };
  return classes[color] || classes.gray;
};

const getCommentTypeBadge = (type) => {
  const classes = {
    public: 'bg-blue-100 text-blue-800',
    internal: 'bg-purple-100 text-purple-800',
    solution: 'bg-green-100 text-green-800',
    status_change: 'bg-gray-100 text-gray-800',
  };
  return classes[type] || classes.public;
};

const getInitials = (name) => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatMinutes = (minutes) => {
  const hours = Math.floor(minutes / 60);
  const mins = minutes % 60;
  if (hours > 0) {
    return `${hours}h ${mins}m`;
  }
  return `${mins}m`;
};
</script>
