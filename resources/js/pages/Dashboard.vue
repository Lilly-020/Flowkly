<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import LateralBar from '@/Components/LateralBar.vue';
import { kanban } from '@/routes';
import { create } from '@/routes/requests';
import type { Ticket, TicketStatus, TicketType } from '@/types';

type DashboardTicket = Pick<
    Ticket,
    'id' | 'title' | 'type' | 'status' | 'created_at'
>;

const props = defineProps<{
    stats: {
        total: number;
        aberta: number;
        em_andamento: number;
        concluida: number;
    };
    categories: {
        value: TicketType;
        label: string;
        count: number;
        percentage: number;
    }[];
    recentTickets: DashboardTicket[];
}>();

const page = usePage();
const firstName = computed(() => page.props.auth.user.name.split(' ')[0]);

const categoryColors: Record<TicketType, string> = {
    hardware: 'var(--cat-1)',
    software: 'var(--cat-2)',
    rede: 'var(--cat-3)',
    acesso: 'var(--cat-4)',
    outro: 'var(--cat-5)',
};

const typeBadge: Record<TicketType, string> = {
    hardware:
        'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400',
    software: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
    rede: 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400',
    acesso: 'bg-pink-50 text-pink-700 dark:bg-pink-500/10 dark:text-pink-400',
    outro: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
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
    em_andamento: 'Em atendimento',
    concluida: 'Concluído',
};

const typeLabels: Record<TicketType, string> = {
    hardware: 'Hardware',
    software: 'Software',
    rede: 'Rede',
    acesso: 'Acesso',
    outro: 'Outro',
};

const radius = 60;
const strokeWidth = 16;
const circumference = 2 * Math.PI * radius;
const gap = 6;

const donutSegments = computed(() => {
    let offset = 0;

    return props.categories.map((category) => {
        const length = (category.percentage / 100) * circumference;
        const dash = Math.max(length - gap, 0);
        const segment = {
            ...category,
            color: categoryColors[category.value],
            dasharray: `${dash} ${circumference - dash}`,
            dashoffset: -offset,
        };

        offset += length;

        return segment;
    });
});

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('pt-BR');

const statTiles = computed(() => [
    {
        label: 'Total de solicitações',
        value: props.stats.total,
        icon: 'M4 6h16M4 12h16M4 18h7',
        badge: 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
    },
    {
        label: 'Abertas',
        value: props.stats.aberta,
        icon: 'M12 9v4m0 4h.01M4.93 19h14.14c1.1 0 1.8-1.19 1.25-2.14L13.25 4.86a1.5 1.5 0 0 0-2.5 0L3.68 16.86C3.13 17.81 3.83 19 4.93 19Z',
        badge: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
    },
    {
        label: 'Em atendimento',
        value: props.stats.em_andamento,
        icon: 'M12 8v4l3 3M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z',
        badge: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
    },
    {
        label: 'Concluídas',
        value: props.stats.concluida,
        icon: 'm5 13 4 4L19 7',
        badge: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
    },
]);
</script>

<template>
    <LateralBar>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            Olá, {{ firstName }}! 👋
        </h1>

        <p class="mt-1 text-gray-600 dark:text-gray-400">
            Aqui está o resumo das suas solicitações.
        </p>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="tile in statTiles"
                :key="tile.label"
                class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900"
            >
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-lg"
                    :class="tile.badge"
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
                            :d="tile.icon"
                        />
                    </svg>
                </span>

                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ tile.label }}
                </p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ tile.value }}
                </p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div
                class="rounded-lg border border-gray-200 bg-white p-5 lg:col-span-2 dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <h2
                        class="text-base font-semibold text-gray-900 dark:text-white"
                    >
                        Minhas solicitações
                    </h2>

                    <Link
                        :href="kanban()"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                    >
                        Ver todas
                    </Link>
                </div>

                <div
                    v-if="recentTickets.length === 0"
                    class="py-10 text-center text-sm text-gray-400"
                >
                    Nenhuma solicitação vinculada a você ainda.
                </div>

                <div v-else class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-xs text-gray-400">
                                <th class="pb-2 font-medium">Código</th>
                                <th class="pb-2 font-medium">Título</th>
                                <th class="pb-2 font-medium">Categoria</th>
                                <th class="pb-2 font-medium">Status</th>
                                <th class="pb-2 font-medium">Data</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-100 dark:divide-gray-800"
                        >
                            <tr
                                v-for="ticket in recentTickets"
                                :key="ticket.id"
                            >
                                <td
                                    class="py-2.5 text-gray-500 dark:text-gray-400"
                                >
                                    #{{ ticket.id }}
                                </td>
                                <td
                                    class="py-2.5 font-medium text-gray-900 dark:text-white"
                                >
                                    {{ ticket.title }}
                                </td>
                                <td class="py-2.5">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="typeBadge[ticket.type]"
                                    >
                                        {{ typeLabels[ticket.type] }}
                                    </span>
                                </td>
                                <td class="py-2.5">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="statusStyles[ticket.status]"
                                    >
                                        {{ statusLabels[ticket.status] }}
                                    </span>
                                </td>
                                <td
                                    class="py-2.5 text-gray-500 dark:text-gray-400"
                                >
                                    {{ formatDate(ticket.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Link
                    :href="create()"
                    class="mt-5 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700"
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
                            d="M12 5v14M5 12h14"
                        />
                    </svg>
                    Nova Solicitação
                </Link>
            </div>

            <div
                class="category-chart rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900"
            >
                <h2
                    class="text-base font-semibold text-gray-900 dark:text-white"
                >
                    Solicitações por categoria
                </h2>

                <div
                    v-if="categories.length === 0"
                    class="py-10 text-center text-sm text-gray-400"
                >
                    Nenhuma categoria ainda.
                </div>

                <template v-else>
                    <div class="relative mx-auto mt-4 h-40 w-40">
                        <svg
                            viewBox="0 0 160 160"
                            class="h-full w-full -rotate-90"
                        >
                            <circle
                                cx="80"
                                cy="80"
                                :r="radius"
                                fill="none"
                                stroke="currentColor"
                                class="text-gray-100 dark:text-gray-800"
                                :stroke-width="strokeWidth"
                            />
                            <circle
                                v-for="segment in donutSegments"
                                :key="segment.value"
                                cx="80"
                                cy="80"
                                :r="radius"
                                fill="none"
                                :stroke="segment.color"
                                :stroke-width="strokeWidth"
                                stroke-linecap="round"
                                :stroke-dasharray="segment.dasharray"
                                :stroke-dashoffset="segment.dashoffset"
                            >
                                <title>
                                    {{ segment.label }}: {{ segment.count }} ({{
                                        segment.percentage
                                    }}%)
                                </title>
                            </circle>
                        </svg>

                        <div
                            class="absolute inset-0 flex flex-col items-center justify-center"
                        >
                            <span
                                class="text-2xl font-bold text-gray-900 dark:text-white"
                            >
                                {{ stats.total }}
                            </span>
                            <span class="text-xs text-gray-400">Total</span>
                        </div>
                    </div>

                    <ul class="mt-5 space-y-2.5">
                        <li
                            v-for="category in categories"
                            :key="category.value"
                            class="flex items-center justify-between text-sm"
                        >
                            <span
                                class="flex items-center gap-2 text-gray-700 dark:text-gray-300"
                            >
                                <span
                                    class="h-2.5 w-2.5 rounded-full"
                                    :style="{
                                        backgroundColor:
                                            categoryColors[category.value],
                                    }"
                                />
                                {{ category.label }}
                            </span>
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                            >
                                {{ category.percentage }}%
                            </span>
                        </li>
                    </ul>
                </template>
            </div>
        </div>
    </LateralBar>
</template>

<style scoped>
.category-chart {
    --cat-1: #2a78d6;
    --cat-2: #eb6834;
    --cat-3: #1baf7a;
    --cat-4: #eda100;
    --cat-5: #e87ba4;
}

@media (prefers-color-scheme: dark) {
    .category-chart {
        --cat-1: #3987e5;
        --cat-2: #d95926;
        --cat-3: #199e70;
        --cat-4: #c98500;
        --cat-5: #d55181;
    }
}
</style>
