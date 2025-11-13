<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-secondary-900 sm:text-3xl">Tickets de Soporte</h1>
          <p class="mt-2 text-sm text-secondary-600">
            Gestiona y da seguimiento a los tickets del helpdesk
          </p>
        </div>
        <Link
          :href="route('tickets.create')"
          class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Nuevo Ticket
        </Link>
      </div>

      <!-- Stats Grid -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card variant="elevated">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">Abiertos</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.open }}</p>
            </div>
            <div class="rounded-full bg-blue-100 p-3">
              <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
          </div>
        </Card>

        <Card variant="elevated">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">En Progreso</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.in_progress }}</p>
            </div>
            <div class="rounded-full bg-yellow-100 p-3">
              <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
          </div>
        </Card>

        <Card variant="elevated">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">Pendientes</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.pending }}</p>
            </div>
            <div class="rounded-full bg-orange-100 p-3">
              <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </Card>

        <Card variant="elevated">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">Vencidos</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.overdue }}</p>
            </div>
            <div class="rounded-full bg-red-100 p-3">
              <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
          </div>
        </Card>
      </div>

      <!-- Filters and Search -->
      <Card variant="elevated">
        <form @submit.prevent="applyFilters" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
          <div>
            <label class="block text-sm font-medium text-secondary-700">Buscar</label>
            <input
              v-model="form.search"
              type="text"
              placeholder="Número, título..."
              class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700">Estado</label>
            <select
              v-model="form.status"
              class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            >
              <option value="">Todos</option>
              <option v-for="(label, value) in statuses" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700">Prioridad</label>
            <select
              v-model="form.priority"
              class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            >
              <option value="">Todas</option>
              <option v-for="(label, value) in priorities" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700">Categoría</label>
            <select
              v-model="form.category"
              class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            >
              <option value="">Todas</option>
              <option v-for="(label, value) in categories" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
          </div>

          <div class="flex items-end gap-2">
            <button
              type="submit"
              class="flex-1 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
            >
              Filtrar
            </button>
            <button
              type="button"
              @click="resetFilters"
              class="rounded-lg border border-secondary-300 bg-white px-4 py-2 text-sm font-semibold text-secondary-700 shadow-sm hover:bg-secondary-50"
            >
              Limpiar
            </button>
          </div>
        </form>

        <div class="mt-4 flex items-center gap-3">
          <label class="flex items-center gap-2">
            <input
              v-model="form.show_closed"
              type="checkbox"
              class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
              @change="applyFilters"
            />
            <span class="text-sm text-secondary-700">Mostrar tickets cerrados</span>
          </label>
        </div>
      </Card>

      <!-- Tickets List -->
      <Card variant="elevated">
        <div v-if="tickets.data.length === 0" class="py-12 text-center">
          <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <h3 class="mt-2 text-sm font-semibold text-secondary-900">No hay tickets</h3>
          <p class="mt-1 text-sm text-secondary-500">Comienza creando un nuevo ticket de soporte.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Ticket
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Título
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Estado
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Prioridad
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Categoría
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Asignado
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Fecha
                </th>
                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-secondary-200 bg-white">
              <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-secondary-50">
                <td class="whitespace-nowrap px-4 py-4">
                  <Link
                    :href="route('tickets.show', ticket.id)"
                    class="text-sm font-medium text-primary-600 hover:text-primary-700"
                  >
                    {{ ticket.ticket_number }}
                  </Link>
                </td>
                <td class="px-4 py-4">
                  <div class="max-w-xs">
                    <Link
                      :href="route('tickets.show', ticket.id)"
                      class="text-sm font-medium text-secondary-900 hover:text-primary-600"
                    >
                      {{ ticket.title }}
                    </Link>
                    <p class="mt-1 text-xs text-secondary-500">
                      Por: {{ ticket.user_name }}
                    </p>
                  </div>
                </td>
                <td class="whitespace-nowrap px-4 py-4">
                  <span
                    :class="getStatusBadgeClass(ticket.status_color)"
                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                  >
                    {{ ticket.status_label }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-4 py-4">
                  <span
                    :class="getPriorityBadgeClass(ticket.priority_color)"
                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                  >
                    {{ ticket.priority_label }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-4 py-4 text-sm text-secondary-900">
                  {{ ticket.category_label }}
                </td>
                <td class="whitespace-nowrap px-4 py-4 text-sm text-secondary-900">
                  {{ ticket.assigned_name || 'Sin asignar' }}
                </td>
                <td class="whitespace-nowrap px-4 py-4 text-sm text-secondary-500">
                  {{ formatDate(ticket.created_at) }}
                </td>
                <td class="whitespace-nowrap px-4 py-4 text-right text-sm">
                  <Link
                    :href="route('tickets.show', ticket.id)"
                    class="text-primary-600 hover:text-primary-700"
                  >
                    Ver
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="tickets.data.length > 0" class="mt-4 flex items-center justify-between border-t border-secondary-200 pt-4">
          <div class="text-sm text-secondary-700">
            Mostrando <span class="font-medium">{{ tickets.from }}</span> a
            <span class="font-medium">{{ tickets.to }}</span> de
            <span class="font-medium">{{ tickets.total }}</span> resultados
          </div>
          <div class="flex gap-2">
            <Link
              v-for="link in tickets.links"
              :key="link.label"
              :href="link.url || '#'"
              :class="[
                link.active
                  ? 'bg-primary-600 text-white'
                  : 'bg-white text-secondary-700 hover:bg-secondary-50',
                !link.url ? 'cursor-not-allowed opacity-50' : '',
                'rounded-lg border border-secondary-300 px-3 py-2 text-sm font-medium',
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
  tickets: Object,
  filters: Object,
  statuses: Object,
  priorities: Object,
  categories: Object,
  users: Array,
  stats: Object,
});

const form = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
  priority: props.filters.priority || '',
  category: props.filters.category || '',
  assigned_to: props.filters.assigned_to || '',
  show_closed: props.filters.show_closed || false,
});

const applyFilters = () => {
  router.get(route('tickets.index'), form, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  form.search = '';
  form.status = '';
  form.priority = '';
  form.category = '';
  form.assigned_to = '';
  form.show_closed = false;
  applyFilters();
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

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>
