<script setup lang="ts">
import { ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import LateralBar from '@/Components/LateralBar.vue';
import Modal from '@/Components/Modal.vue';
import { resetPassword } from '@/routes/users';
import type { User } from '@/types';

defineProps<{
    users: User[];
}>();

const page = usePage();

const generatedPassword = ref<{ email: string; password: string } | null>(null);

watch(
    () => page.props.flash.generatedPassword,
    (value) => {
        if (value) {
            generatedPassword.value = value;
        }
    },
    { immediate: true },
);

const roleStyles: Record<string, string> = {
    ti: 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400',
    user: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
};

const roleLabels: Record<string, string> = {
    ti: 'TI',
    user: 'Usuário',
};

const resetUserPassword = (user: User) => {
    if (
        !confirm(
            `Gerar uma nova senha para ${user.name}? A senha atual deixará de funcionar.`,
        )
    ) {
        return;
    }

    router.post(resetPassword(user.id).url, {}, { preserveScroll: true });
};

const closeGeneratedPasswordModal = () => {
    generatedPassword.value = null;
};
</script>

<template>
    <LateralBar>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Usuários
        </h1>

        <p class="mt-2 text-gray-600 dark:text-gray-400">
            Contas com acesso ao portal.
        </p>

        <div
            class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
        >
            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                <li
                    v-for="user in users"
                    :key="user.id"
                    class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p
                                class="font-medium text-gray-900 dark:text-white"
                            >
                                {{ user.name }}
                            </p>

                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="roleStyles[user.role]"
                            >
                                {{ roleLabels[user.role] }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ user.email }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="shrink-0 rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                        @click="resetUserPassword(user)"
                    >
                        Gerar nova senha
                    </button>
                </li>
            </ul>
        </div>

        <Modal :open="!!generatedPassword" @close="closeGeneratedPasswordModal">
            <div
                v-if="generatedPassword"
                class="rounded-2xl border border-gray-200 bg-white p-8 shadow-2xl dark:border-gray-800 dark:bg-gray-900"
            >
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    Nova senha gerada
                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Compartilhe esta senha com
                    <strong>{{ generatedPassword.email }}</strong>
                    para que ele(a) possa entrar e depois alterá-la em
                    Configurações.
                </p>

                <p
                    class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-center font-mono text-lg tracking-wider text-gray-900 dark:border-gray-800 dark:bg-gray-800 dark:text-white"
                >
                    {{ generatedPassword.password }}
                </p>

                <button
                    type="button"
                    class="mt-6 w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-medium text-white hover:bg-indigo-700"
                    @click="closeGeneratedPasswordModal"
                >
                    Fechar
                </button>
            </div>
        </Modal>
    </LateralBar>
</template>
