<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import LateralBar from '@/Components/LateralBar.vue';
import { create } from '@/routes/requests';
import type { Ticket, TicketStatus, TicketType } from '@/types';

defineProps<{
    tickets: Ticket[];
}>();

const statusStyles: Record<TicketStatus, string> = {
    aberta: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
    em_andamento:
        'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
    concluida:
        'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
};

const statusLabels: Record<TicketStatus, string> = {
    aberta: 'Aberta',
    em_andamento: 'Em andamento',
    concluida: 'Concluída',
};

const typeLabels: Record<TicketType, string> = {
    hardware: 'Hardware',
    software: 'Software',
    rede: 'Rede',
    acesso: 'Acesso',
    outro: 'Outro',
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

        <div
            v-if="tickets.length === 0"
            class="mt-6 rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400"
        >
            Você ainda não criou nenhuma solicitação.
        </div>

        <div
            v-else
            class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
        >
            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                <li v-for="ticket in tickets" :key="ticket.id" class="p-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ ticket.title }}
                        </p>

                        <span
                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="statusStyles[ticket.status]"
                        >
                            {{ statusLabels[ticket.status] }}
                        </span>

                        <span
                            class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                        >
                            {{ typeLabels[ticket.type] }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        {{ ticket.description }}
                    </p>

                    <div
                        v-if="
                            ticket.attachments && ticket.attachments.length > 0
                        "
                        class="mt-3 flex flex-wrap gap-2"
                    >
                        <a
                            v-for="attachment in ticket.attachments"
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

                    <p
                        v-if="ticket.assignee"
                        class="mt-3 text-xs text-gray-500 dark:text-gray-400"
                    >
                        Encaminhada para {{ ticket.assignee.name }}
                    </p>
                </li>
            </ul>
        </div>
    </LateralBar>
</template>
