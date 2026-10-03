<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import LateralBar from '@/Components/LateralBar.vue';
import { password as updatePassword } from '@/routes/settings';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(updatePassword().url, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <LateralBar>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Configurações
        </h1>

        <p class="mt-2 text-gray-600 dark:text-gray-400">
            Altere sua senha de acesso.
        </p>

        <form
            class="mt-6 max-w-md space-y-4 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            @submit.prevent="submit"
        >
            <div>
                <label
                    for="current_password"
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Senha atual
                </label>

                <input
                    id="current_password"
                    v-model="form.current_password"
                    type="password"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />

                <p
                    v-if="form.errors.current_password"
                    class="mt-1.5 text-sm text-red-600 dark:text-red-400"
                >
                    {{ form.errors.current_password }}
                </p>
            </div>

            <div>
                <label
                    for="password"
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Nova senha
                </label>

                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />

                <p
                    v-if="form.errors.password"
                    class="mt-1.5 text-sm text-red-600 dark:text-red-400"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <div>
                <label
                    for="password_confirmation"
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Confirmar nova senha
                </label>

                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
            >
                {{ form.processing ? 'Salvando...' : 'Salvar nova senha' }}
            </button>
        </form>
    </LateralBar>
</template>
