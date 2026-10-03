<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import LateralBar from '@/Components/LateralBar.vue';
import { accept, decline } from '@/routes/access-requests';
import type { AccessRequest, AccessRequestStatus } from '@/types';

defineProps<{
    requests: AccessRequest[];
}>();

const statusStyles: Record<AccessRequestStatus, string> = {
    pending:
        'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
    accepted:
        'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
    declined: 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
};

const statusLabels: Record<AccessRequestStatus, string> = {
    pending: 'Pendente',
    accepted: 'Aceita',
    declined: 'Recusada',
};

const acceptRequest = (id: number) => {
    router.post(accept(id).url, {}, { preserveScroll: true });
};

const declineRequest = (id: number) => {
    if (!confirm('Recusar esta solicitação de acesso?')) {
        return;
    }

    router.post(decline(id).url, {}, { preserveScroll: true });
};
</script>

<template>
    <LateralBar>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Solicitações de acesso
        </h1>

        <p class="mt-2 text-gray-600 dark:text-gray-400">
            Pessoas que pediram acesso ao portal.
        </p>

        <div
            v-if="requests.length === 0"
            class="mt-6 rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400"
        >
            Nenhuma solicitação de acesso ainda.
        </div>

        <div
            v-else
            class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
        >
            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                <li
                    v-for="request in requests"
                    :key="request.id"
                    class="flex flex-col gap-3 p-4 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p
                                class="font-medium text-gray-900 dark:text-white"
                            >
                                {{ request.name }}
                            </p>

                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="statusStyles[request.status]"
                            >
                                {{ statusLabels[request.status] }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ request.email }}
                        </p>

                        <p
                            class="mt-2 text-sm text-gray-600 dark:text-gray-300"
                        >
                            {{ request.reason }}
                        </p>
                    </div>

                    <div
                        v-if="request.status === 'pending'"
                        class="flex shrink-0 gap-2"
                    >
                        <button
                            type="button"
                            class="rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700"
                            @click="acceptRequest(request.id)"
                        >
                            Aceitar
                        </button>

                        <button
                            type="button"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                            @click="declineRequest(request.id)"
                        >
                            Recusar
                        </button>
                    </div>
                </li>
            </ul>
        </div>
    </LateralBar>
</template>
