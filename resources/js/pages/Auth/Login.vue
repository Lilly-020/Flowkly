<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { create as requestAccess } from '@/routes/access-requests';
import { ti, usuario } from '@/routes/login';
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
            class="pointer-events-none absolute top-1/2 left-1/2 h-128 w-lg -translate-x-1/2 -translate-y-1/2 rounded-full bg-indigo-600/10 blur-3xl"
        />

        <div
            class="relative w-full max-w-3xl rounded-[28px] border border-white/10 bg-slate-900/60 p-8 shadow-2xl shadow-black/50 backdrop-blur-xl sm:p-12"
        >
            <div class="flex flex-col items-center text-center">
                <div class="mb-6 flex items-center gap-2">
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-linear-to-br from-blue-500 to-purple-600 shadow-lg shadow-blue-500/30"
                    >
                        <svg
                            class="h-5 w-5 text-white"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z"
                            />
                        </svg>
                    </span>
                    <span class="text-2xl font-bold text-white">
                        Flowkly
                        <span class="font-semibold text-gray-300">
                            Soluções
                        </span>
                    </span>
                </div>

                <h1 class="text-2xl font-bold text-white sm:text-3xl">
                    Portal de Solicitações Internas
                </h1>

                <p class="mt-3 max-w-md text-sm text-gray-400 sm:text-base">
                    Registre suas demandas e acompanhe o andamento de forma
                    simples e rápida.
                </p>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div
                    class="flex flex-col items-center rounded-2xl border border-white/10 bg-white/3 p-8 text-center transition-colors hover:border-white/20"
                >
                    <span
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-500/10 text-blue-400"
                    >
                        <svg
                            class="h-8 w-8"
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

                    <h2 class="mt-4 text-lg font-semibold text-white">
                        Usuário
                    </h2>

                    <p class="mt-2 text-sm text-gray-400">
                        Acesse suas solicitações, acompanhe o status e registre
                        novas demandas.
                    </p>

                    <Link
                        :href="usuario()"
                        class="mt-6 w-full rounded-lg bg-linear-to-r from-blue-600 to-indigo-600 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90"
                    >
                        Entrar
                    </Link>
                </div>

                <div
                    class="flex flex-col items-center rounded-2xl border border-white/10 bg-white/3 p-8 text-center transition-colors hover:border-white/20"
                >
                    <span
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-purple-500/10 text-purple-400"
                    >
                        <svg
                            class="h-8 w-8"
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
                    </span>

                    <h2 class="mt-4 text-lg font-semibold text-white">TI</h2>

                    <p class="mt-2 text-sm text-gray-400">
                        Gerencie as solicitações, acompanhe o andamento e
                        mantenha o sistema organizado.
                    </p>

                    <Link
                        :href="ti()"
                        class="mt-6 w-full rounded-lg bg-linear-to-r from-purple-600 to-fuchsia-600 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90"
                    >
                        Entrar
                    </Link>
                </div>
            </div>

            <div class="mt-8 flex justify-center">
                <Link
                    :href="requestAccess()"
                    class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-medium text-gray-300 transition-colors hover:border-white/20 hover:text-white"
                >
                    <svg
                        class="h-4 w-4"
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
                    Solicitar acesso
                </Link>
            </div>

            <div class="mt-10 border-t border-white/10 pt-6 text-center">
                <p class="text-sm font-semibold text-white">Flowkly Soluções</p>
                <p class="mt-1 text-xs text-gray-500">
                    Tecnologia que conecta pessoas e processos.
                </p>
            </div>
        </div>
    </div>
</template>
