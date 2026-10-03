<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { login } from '@/routes';
import { store } from '@/routes/login';

const props = defineProps<{
    type: 'usuario' | 'ti';
}>();

const form = useForm({
    type: props.type,
    email: '',
    password: '',
});

const isTi = computed(() => props.type === 'ti');

const submit = () => {
    form.post(store().url);
};
</script>

<template>
    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#0a0a23] px-4 py-10"
    >
        <div
            class="pointer-events-none absolute -top-32 -left-32 h-96 w-96 rounded-full bg-blue-600/30 blur-3xl"
        />
        <div
            class="pointer-events-none absolute -right-32 -bottom-32 h-96 w-96 rounded-full bg-purple-600/30 blur-3xl"
        />

        <div
            class="relative w-full max-w-md rounded-[28px] border border-white/10 bg-slate-900/60 p-8 shadow-2xl shadow-black/50 backdrop-blur-xl sm:p-10"
        >
            <Link
                :href="login()"
                class="inline-flex items-center gap-1 text-sm text-gray-400 transition-colors hover:text-white"
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
                        d="M15 18l-6-6 6-6"
                    />
                </svg>
                Voltar
            </Link>

            <div class="mt-6 flex flex-col items-center text-center">
                <span
                    class="flex h-14 w-14 items-center justify-center rounded-full"
                    :class="
                        isTi
                            ? 'bg-purple-500/10 text-purple-400'
                            : 'bg-blue-500/10 text-blue-400'
                    "
                >
                    <svg
                        v-if="isTi"
                        class="h-7 w-7"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 3l7 3.5v5.2c0 4.5-3 8.2-7 9.3-4-1.1-7-4.8-7-9.3V6.5L12 3Z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 12l2 2 4-4"
                        />
                    </svg>

                    <svg
                        v-else
                        class="h-7 w-7"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                    >
                        <circle cx="12" cy="8" r="4" />
                        <path
                            stroke-linecap="round"
                            d="M4 20c0-4.2 3.6-7 8-7s8 2.8 8 7"
                        />
                    </svg>
                </span>

                <h1 class="mt-4 text-xl font-bold text-white">
                    Login {{ isTi ? 'TI' : 'Usuário' }}
                </h1>

                <p class="mt-1 text-sm text-gray-400">
                    Acesse sua conta para continuar.
                </p>
            </div>

            <form class="mt-8 space-y-5" @submit.prevent="submit">
                <div>
                    <label
                        for="email"
                        class="mb-1.5 block text-sm font-medium text-gray-300"
                    >
                        E-mail
                    </label>

                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-500 outline-none focus:border-white/30"
                        :class="
                            isTi
                                ? 'focus:ring-1 focus:ring-purple-500/50'
                                : 'focus:ring-1 focus:ring-blue-500/50'
                        "
                    />

                    <p
                        v-if="form.errors.email"
                        class="mt-1.5 text-sm text-red-400"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label
                        for="password"
                        class="mb-1.5 block text-sm font-medium text-gray-300"
                    >
                        Senha
                    </label>

                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-500 outline-none focus:border-white/30"
                        :class="
                            isTi
                                ? 'focus:ring-1 focus:ring-purple-500/50'
                                : 'focus:ring-1 focus:ring-blue-500/50'
                        "
                    />

                    <p
                        v-if="form.errors.password"
                        class="mt-1.5 text-sm text-red-400"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-60"
                    :class="
                        isTi
                            ? 'bg-linear-to-r from-purple-600 to-fuchsia-600'
                            : 'bg-linear-to-r from-blue-600 to-indigo-600'
                    "
                >
                    {{ form.processing ? 'Entrando...' : 'Entrar' }}
                </button>
            </form>
        </div>
    </div>
</template>
