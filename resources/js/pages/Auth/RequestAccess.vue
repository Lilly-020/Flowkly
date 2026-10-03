<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { login } from '@/routes';
import { store } from '@/routes/access-requests';

const submitted = ref(false);

const form = useForm({
    name: '',
    email: '',
    reason: '',
});

const submit = () => {
    form.post(store().url, {
        onSuccess: () => {
            submitted.value = true;
            form.reset();
        },
    });
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
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-500/10 text-indigo-400"
                >
                    <svg
                        class="h-7 w-7"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                    >
                        <circle cx="9" cy="8" r="3" />
                        <path
                            stroke-linecap="round"
                            d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"
                        />
                        <path stroke-linecap="round" d="M16 8h6M19 5v6" />
                    </svg>
                </span>

                <h1 class="mt-4 text-xl font-bold text-white">
                    Solicitar acesso
                </h1>

                <p class="mt-1 text-sm text-gray-400">
                    Preencha os dados abaixo e nossa equipe entrará em contato.
                </p>
            </div>

            <div
                v-if="submitted"
                class="mt-8 rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-4 text-center text-sm text-emerald-300"
            >
                Solicitação enviada com sucesso! Em breve entraremos em contato.
            </div>

            <form v-else class="mt-8 space-y-5" @submit.prevent="submit">
                <div>
                    <label
                        for="name"
                        class="mb-1.5 block text-sm font-medium text-gray-300"
                    >
                        Nome
                    </label>

                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-500 outline-none focus:border-white/30 focus:ring-1 focus:ring-indigo-500/50"
                    />

                    <p
                        v-if="form.errors.name"
                        class="mt-1.5 text-sm text-red-400"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

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
                        class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-500 outline-none focus:border-white/30 focus:ring-1 focus:ring-indigo-500/50"
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
                        for="reason"
                        class="mb-1.5 block text-sm font-medium text-gray-300"
                    >
                        Motivo
                    </label>

                    <textarea
                        id="reason"
                        v-model="form.reason"
                        rows="3"
                        required
                        class="w-full resize-none rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-500 outline-none focus:border-white/30 focus:ring-1 focus:ring-indigo-500/50"
                    />

                    <p
                        v-if="form.errors.reason"
                        class="mt-1.5 text-sm text-red-400"
                    >
                        {{ form.errors.reason }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-linear-to-r from-indigo-600 to-purple-600 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-60"
                >
                    {{ form.processing ? 'Enviando...' : 'Enviar solicitação' }}
                </button>
            </form>
        </div>
    </div>
</template>
