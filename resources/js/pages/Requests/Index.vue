<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import LateralBar from '@/Components/LateralBar.vue';
import Modal from '@/Components/Modal.vue';
import { create, index } from '@/routes/requests';
import { destroy, update } from '@/routes/tickets';
import type { Ticket, TicketStatus, TicketType } from '@/types';

const props = defineProps<{
    tickets: Ticket[];
    types: { value: TicketType; label: string }[];
    filters: {
        title?: string;
        type?: string;
        status?: string;
        from?: string;
        to?: string;
    };
}>();

const page = usePage();

const filters = ref({
    title: props.filters.title ?? '',
    type: props.filters.type ?? '',
    status: props.filters.status ?? '',
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
});

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(
    filters,
    (value) => {
        if (debounceTimer) clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() => {
            router.get(index().url, value, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 300);
    },
    { deep: true },
);

const clearFilters = () => {
    filters.value = { title: '', type: '', status: '', from: '', to: '' };
};

const statusStyles: Record<TicketStatus, string> = {
    aberta: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
    em_andamento:
        'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
    concluida:
        'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
};

const statusLabels: Record<TicketStatus, string> = {
    aberta: 'Aberto',
    em_andamento: 'Em andamento',
    concluida: 'Concluído',
};

const typeLabels = computed(
    () =>
        Object.fromEntries(
            props.types.map((t) => [t.value, t.label]),
        ) as Record<TicketType, string>,
);

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('pt-BR');

const formatDateTime = (value: string) =>
    new Date(value).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const selectedTicketId = ref<number | null>(null);
const selectedTicket = computed(
    () => props.tickets.find((t) => t.id === selectedTicketId.value) ?? null,
);
const canManage = computed(
    () => !!selectedTicket.value && selectedTicket.value.status === 'aberta',
);

const isEditing = ref(false);
const editForm = ref({ title: '', description: '', type: '' as TicketType });

const openTicket = (ticket: Ticket) => {
    selectedTicketId.value = ticket.id;
    isEditing.value = false;
};

const closeModal = () => {
    selectedTicketId.value = null;
    isEditing.value = false;
};

const startEditing = () => {
    if (!selectedTicket.value) return;

    editForm.value = {
        title: selectedTicket.value.title,
        description: selectedTicket.value.description,
        type: selectedTicket.value.type,
    };
    isEditing.value = true;
};

const saveEdit = () => {
    if (!selectedTicket.value) return;

    router.put(update(selectedTicket.value.id).url, editForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

const deleteTicket = () => {
    if (!selectedTicket.value) return;

    if (
        !confirm('Excluir esta solicitação? Essa ação não pode ser desfeita.')
    ) {
        return;
    }

    router.delete(destroy(selectedTicket.value.id).url, {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};
</script>

<template>
    <LateralBar>
        <div class="flex items-center justify-between">
            <div>
                <h1
                    class="text-2xl font-semibold text-gray-900 dark:text-white"
                >
                    Minhas solicitações
                </h1>

                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Acompanhe o status das suas solicitações.
                </p>
            </div>

            <Link
                :href="create()"
                class="shrink-0 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700"
            >
                Nova solicitação
            </Link>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <input
                v-model="filters.title"
                type="text"
                placeholder="Buscar por título..."
                class="min-w-48 flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            />

            <select
                v-model="filters.type"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            >
                <option value="">Todas as categorias</option>
                <option
                    v-for="type in types"
                    :key="type.value"
                    :value="type.value"
                >
                    {{ type.label }}
                </option>
            </select>

            <select
                v-model="filters.status"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            >
                <option value="">Todos os status</option>
                <option value="aberta">Aberto</option>
                <option value="em_andamento">Em andamento</option>
                <option value="concluida">Concluído</option>
            </select>

            <input
                v-model="filters.from"
                type="date"
                aria-label="De"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            />

            <input
                v-model="filters.to"
                type="date"
                aria-label="Até"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            />

            <button
                type="button"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                @click="clearFilters"
            >
                Limpar
            </button>
        </div>

        <div
            v-if="tickets.length === 0"
            class="mt-6 rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400"
        >
            Nenhuma solicitação encontrada.
        </div>

        <div
            v-else
            class="mt-6 overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
        >
            <table class="w-full text-left text-sm">
                <thead>
                    <tr
                        class="border-b border-gray-200 text-xs text-gray-400 dark:border-gray-800"
                    >
                        <th class="px-4 py-3 font-medium">Código</th>
                        <th class="px-4 py-3 font-medium">Título</th>
                        <th class="px-4 py-3 font-medium">Categoria</th>
                        <th class="px-4 py-3 font-medium">Solicitante</th>
                        <th class="px-4 py-3 font-medium">Data</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr
                        v-for="ticket in tickets"
                        :key="ticket.id"
                        class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/60"
                        @click="openTicket(ticket)"
                    >
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                            #{{ ticket.id }}
                        </td>
                        <td
                            class="px-4 py-3 font-medium text-gray-900 dark:text-white"
                        >
                            {{ ticket.title }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            {{ typeLabels[ticket.type] }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            {{ page.props.auth.user.name }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                            {{ formatDate(ticket.created_at) }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="statusStyles[ticket.status]"
                            >
                                {{ statusLabels[ticket.status] }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal :open="!!selectedTicket" @close="closeModal">
            <div
                v-if="selectedTicket"
                class="max-h-[85vh] overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-start justify-between">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        Detalhes da Solicitação
                    </h2>

                    <button
                        type="button"
                        class="-mt-1 -mr-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                        aria-label="Fechar"
                        @click="closeModal"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18 18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <p
                        class="text-base font-semibold text-gray-900 dark:text-white"
                    >
                        <span class="text-gray-400"
                            >#{{ selectedTicket.id }}</span
                        >
                        {{ selectedTicket.title }}
                    </p>

                    <span
                        class="rounded-full px-2.5 py-1 text-xs font-medium"
                        :class="statusStyles[selectedTicket.status]"
                    >
                        {{ statusLabels[selectedTicket.status] }}
                    </span>
                </div>

                <template v-if="isEditing">
                    <div class="mt-5 space-y-4">
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Título
                            </label>
                            <input
                                v-model="editForm.title"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Categoria
                            </label>
                            <select
                                v-model="editForm.type"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option
                                    v-for="type in types"
                                    :key="type.value"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Descrição
                            </label>
                            <textarea
                                v-model="editForm.description"
                                rows="4"
                                class="w-full resize-none rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                    </div>

                    <div class="mt-5 flex gap-2">
                        <button
                            type="button"
                            class="flex-1 rounded-lg bg-indigo-600 py-2.5 text-sm font-medium text-white hover:bg-indigo-700"
                            @click="saveEdit"
                        >
                            Salvar
                        </button>
                        <button
                            type="button"
                            class="flex-1 rounded-lg border border-gray-300 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                            @click="isEditing = false"
                        >
                            Cancelar
                        </button>
                    </div>
                </template>

                <template v-else>
                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="space-y-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-400">Categoria</p>
                                <p class="text-gray-900 dark:text-white">
                                    {{ typeLabels[selectedTicket.type] }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-400">Solicitante</p>
                                <p class="text-gray-900 dark:text-white">
                                    {{ page.props.auth.user.name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-400">
                                    Data de abertura
                                </p>
                                <p class="text-gray-900 dark:text-white">
                                    {{
                                        formatDateTime(
                                            selectedTicket.created_at,
                                        )
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-400">
                                    Encaminhada para
                                </p>
                                <p class="text-gray-900 dark:text-white">
                                    {{ selectedTicket.assignee?.name ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs text-gray-400">Descrição</p>
                            <p
                                class="mt-1 text-sm text-gray-700 dark:text-gray-300"
                            >
                                {{ selectedTicket.description }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="
                            selectedTicket.attachments &&
                            selectedTicket.attachments.length > 0
                        "
                        class="mt-5"
                    >
                        <p class="mb-2 text-xs text-gray-400">Imagens</p>
                        <div class="flex flex-wrap gap-2">
                            <a
                                v-for="attachment in selectedTicket.attachments"
                                :key="attachment.id"
                                :href="attachment.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="block h-16 w-16 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700"
                            >
                                <img
                                    :src="attachment.url"
                                    :alt="attachment.original_name"
                                    class="h-full w-full object-cover"
                                />
                            </a>
                        </div>
                    </div>

                    <div v-if="canManage" class="mt-6 flex items-center gap-2">
                        <button
                            type="button"
                            class="flex-1 rounded-lg border border-gray-300 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                            @click="startEditing"
                        >
                            Editar
                        </button>

                        <button
                            type="button"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-300 text-gray-500 hover:bg-red-50 hover:text-red-600 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                            aria-label="Excluir solicitação"
                            @click="deleteTicket"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4 7h16M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3m2 0v13a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V7h10Z"
                                />
                            </svg>
                        </button>
                    </div>

                    <p v-else class="mt-6 text-xs text-gray-400">
                        Esta solicitação não pode mais ser editada ou excluída
                        porque já está
                        {{ statusLabels[selectedTicket.status].toLowerCase() }}.
                    </p>
                </template>
            </div>
        </Modal>
    </LateralBar>
</template>
