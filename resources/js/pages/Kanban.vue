<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import LateralBar from '@/Components/LateralBar.vue';
import Modal from '@/Components/Modal.vue';
import { destroy, status as updateStatus, update } from '@/routes/tickets';
import type { Ticket, TicketStatus, TicketType } from '@/types';

const props = defineProps<{
    tickets: Ticket[];
    types: { value: TicketType; label: string }[];
}>();

const search = ref('');
const typeFilter = ref('');

const columns: { status: TicketStatus; label: string; dot: string }[] = [
    { status: 'aberta', label: 'Aberto', dot: 'bg-amber-400' },
    { status: 'em_andamento', label: 'Em Atendimento', dot: 'bg-blue-400' },
    { status: 'concluida', label: 'Concluído', dot: 'bg-emerald-400' },
];

const typeBorder: Record<TicketType, string> = {
    hardware: 'border-l-orange-400',
    software: 'border-l-blue-400',
    rede: 'border-l-purple-400',
    acesso: 'border-l-pink-400',
    outro: 'border-l-gray-400',
};

const typeBadge: Record<TicketType, string> = {
    hardware:
        'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400',
    software: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
    rede: 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400',
    acesso: 'bg-pink-50 text-pink-700 dark:bg-pink-500/10 dark:text-pink-400',
    outro: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
};

const typeLabels = computed(
    () =>
        Object.fromEntries(
            props.types.map((t) => [t.value, t.label]),
        ) as Record<TicketType, string>,
);

const statusLabels: Record<TicketStatus, string> = {
    aberta: 'Aberto',
    em_andamento: 'Em Atendimento',
    concluida: 'Concluído',
};

const filteredTickets = computed(() =>
    props.tickets.filter((ticket) => {
        const matchesSearch = ticket.title
            .toLowerCase()
            .includes(search.value.toLowerCase());
        const matchesType =
            !typeFilter.value || ticket.type === typeFilter.value;

        return matchesSearch && matchesType;
    }),
);

const ticketsByStatus = computed(() => {
    const groups: Record<TicketStatus, Ticket[]> = {
        aberta: [],
        em_andamento: [],
        concluida: [],
    };

    for (const ticket of filteredTickets.value) {
        groups[ticket.status].push(ticket);
    }

    return groups;
});

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

const isEditing = ref(false);
const isChangingStatus = ref(false);

const editForm = ref({ title: '', description: '', type: '' as TicketType });
const statusForm = ref<TicketStatus>('aberta');

const openTicket = (ticket: Ticket) => {
    selectedTicketId.value = ticket.id;
    isEditing.value = false;
    isChangingStatus.value = false;
};

const closeModal = () => {
    selectedTicketId.value = null;
    isEditing.value = false;
    isChangingStatus.value = false;
};

const startEditing = () => {
    if (!selectedTicket.value) return;

    editForm.value = {
        title: selectedTicket.value.title,
        description: selectedTicket.value.description,
        type: selectedTicket.value.type,
    };
    isChangingStatus.value = false;
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

const startChangingStatus = () => {
    if (!selectedTicket.value) return;

    statusForm.value = selectedTicket.value.status;
    isEditing.value = false;
    isChangingStatus.value = true;
};

const saveStatus = () => {
    if (!selectedTicket.value) return;

    router.patch(
        updateStatus(selectedTicket.value.id).url,
        { status: statusForm.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                isChangingStatus.value = false;
            },
        },
    );
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

const historyLabel = (
    entry: NonNullable<Ticket['status_histories']>[number],
) =>
    entry.from_status === null
        ? 'Solicitação criada'
        : `Alterado para ${statusLabels[entry.to_status]}`;
</script>

<template>
    <LateralBar>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Kanban
        </h1>

        <p class="mt-2 text-gray-600 dark:text-gray-400">
            Todas as solicitações do setor de TI.
        </p>

        <div class="mt-6 flex flex-wrap gap-3">
            <input
                v-model="search"
                type="text"
                placeholder="Buscar por título..."
                class="min-w-48 flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            />

            <select
                v-model="typeFilter"
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
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div
                v-for="column in columns"
                :key="column.status"
                class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-900/50"
            >
                <div class="mb-3 flex items-center gap-2 px-1">
                    <span class="h-2 w-2 rounded-full" :class="column.dot" />
                    <h2
                        class="text-sm font-semibold text-gray-700 dark:text-gray-200"
                    >
                        {{ column.label }}
                    </h2>
                    <span class="text-xs text-gray-400">
                        ({{ ticketsByStatus[column.status].length }})
                    </span>
                </div>

                <div class="space-y-3">
                    <button
                        v-for="ticket in ticketsByStatus[column.status]"
                        :key="ticket.id"
                        type="button"
                        class="block w-full rounded-lg border border-l-4 border-gray-200 bg-white p-3 text-left shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
                        :class="typeBorder[ticket.type]"
                        @click="openTicket(ticket)"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-400">
                                #{{ ticket.id }}
                            </span>
                        </div>

                        <p
                            class="mt-1 text-sm font-medium text-gray-900 dark:text-white"
                        >
                            {{ ticket.title }}
                        </p>

                        <span
                            class="mt-2 inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="typeBadge[ticket.type]"
                        >
                            {{ typeLabels[ticket.type] }}
                        </span>

                        <div
                            class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400"
                        >
                            <span class="flex items-center gap-1">
                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <circle cx="12" cy="8" r="4" />
                                    <path
                                        stroke-linecap="round"
                                        d="M4 20c0-4.2 3.6-7 8-7s8 2.8 8 7"
                                    />
                                </svg>
                                {{ ticket.user?.name ?? 'Usuário' }}
                            </span>

                            <span>{{ formatDate(ticket.created_at) }}</span>
                        </div>
                    </button>

                    <p
                        v-if="ticketsByStatus[column.status].length === 0"
                        class="px-1 py-6 text-center text-xs text-gray-400"
                    >
                        Nenhuma solicitação aqui.
                    </p>
                </div>
            </div>
        </div>

        <Modal
            :open="!!selectedTicket"
            max-width="max-w-2xl"
            @close="closeModal"
        >
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
                        :class="typeBadge[selectedTicket.type]"
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
                                    {{ selectedTicket.user?.name ?? '—' }}
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

                    <div class="mt-6">
                        <p class="mb-2 text-xs font-medium text-gray-400">
                            Histórico
                        </p>

                        <ol
                            class="space-y-3 border-l border-gray-200 pl-4 dark:border-gray-700"
                        >
                            <li
                                v-for="entry in selectedTicket.status_histories ??
                                []"
                                :key="entry.id"
                                class="relative text-sm"
                            >
                                <span
                                    class="absolute top-1 -left-5.25 h-2.5 w-2.5 rounded-full bg-indigo-500"
                                />
                                <p class="text-gray-900 dark:text-white">
                                    {{ formatDateTime(entry.created_at) }}
                                    <span class="font-medium">{{
                                        historyLabel(entry)
                                    }}</span>
                                </p>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ entry.changed_by?.name ?? 'Sistema' }}
                                    <span v-if="entry.changed_by?.role === 'ti'"
                                        >(TI)</span
                                    >
                                </p>
                            </li>
                        </ol>
                    </div>

                    <div
                        v-if="isChangingStatus"
                        class="mt-6 flex items-end gap-2"
                    >
                        <div class="flex-1">
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Novo status
                            </label>
                            <select
                                v-model="statusForm"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option
                                    v-for="column in columns"
                                    :key="column.status"
                                    :value="column.status"
                                >
                                    {{ column.label }}
                                </option>
                            </select>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700"
                            @click="saveStatus"
                        >
                            Salvar
                        </button>

                        <button
                            type="button"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                            @click="isChangingStatus = false"
                        >
                            Cancelar
                        </button>
                    </div>

                    <div v-else class="mt-6 flex items-center gap-2">
                        <button
                            type="button"
                            class="flex-1 rounded-lg border border-gray-300 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                            @click="startEditing"
                        >
                            Editar
                        </button>

                        <button
                            type="button"
                            class="flex-1 rounded-lg bg-indigo-600 py-2.5 text-sm font-medium text-white hover:bg-indigo-700"
                            @click="startChangingStatus"
                        >
                            Alterar Status
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
                </template>
            </div>
        </Modal>
    </LateralBar>
</template>
