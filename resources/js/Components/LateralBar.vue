<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { dashboard, kanban, logout, settings } from '@/routes';
import { index as accessRequestsIndex } from '@/routes/access-requests';
import {
    create as createRequest,
    index as requestsIndex,
} from '@/routes/requests';
import { index as usersIndex } from '@/routes/users';
import type { UserRole } from '@/types/auth';
import type { RouteDefinition } from '@/wayfinder';

const page = usePage();
const mobileOpen = ref(false);

const user = computed(() => page.props.auth.user);

const allNavigation: {
    name: string;
    href: RouteDefinition<'get'>;
    icon: string;
    roles: UserRole[];
    circles?: { cx: number; cy: number; r: number }[];
}[] = [
    {
        name: 'Dashboard',
        href: dashboard(),
        icon: 'M3 12l9-9 9 9M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10',
        roles: ['ti'],
    },
    {
        name: 'Solicitações',
        href: accessRequestsIndex(),
        icon: 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01',
        roles: ['ti'],
    },
    {
        name: 'Minhas solicitações',
        href: requestsIndex(),
        icon: 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01',
        roles: ['user'],
    },
    {
        name: 'Nova solicitação',
        href: createRequest(),
        icon: 'M12 5v14M5 12h14',
        roles: ['user', 'ti'],
    },
    {
        name: 'Kanban',
        href: kanban(),
        icon: 'M3 4h5v16H3zM10 4h5v10h-5zM17 4h4v7h-4z',
        roles: ['ti'],
    },
    {
        name: 'Usuários',
        href: usersIndex(),
        icon: 'M9 20H4v-2a4 4 0 0 1 4-4h2a4 4 0 0 1 4 4v2h-5Zm0-9a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm9 9v-2a4 4 0 0 0-3-3.87M15 4.13A4 4 0 0 1 15 12',
        roles: ['ti'],
    },
    {
        name: 'Configurações',
        href: settings(),
        icon: 'M4 6h10M4 12h16M4 18h10',
        circles: [
            { cx: 17, cy: 6, r: 2 },
            { cx: 8, cy: 18, r: 2 },
        ],
        roles: ['user', 'ti'],
    },
];

const navigation = computed(() =>
    allNavigation.filter((item) => item.roles.includes(user.value.role)),
);

const isActive = (url: string) => page.url === url;

const closeMobile = () => {
    mobileOpen.value = false;
};

const signOut = () => {
    router.post(logout().url);
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
        <!-- Mobile top bar -->
        <header
            class="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-gray-200 bg-white px-4 lg:hidden dark:border-gray-800 dark:bg-gray-900"
        >
            <span class="text-lg font-semibold text-gray-900 dark:text-white">
                Flowkly
            </span>

            <button
                type="button"
                class="-mr-1 inline-flex h-10 w-10 items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                :aria-expanded="mobileOpen"
                aria-label="Abrir menu"
                @click="mobileOpen = true"
            >
                <svg
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                </svg>
            </button>
        </header>

        <!-- Mobile backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-150"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileOpen"
                class="fixed inset-0 z-40 bg-black/50 lg:hidden"
                @click="closeMobile"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform duration-200 ease-in-out lg:translate-x-0 dark:border-gray-800 dark:bg-gray-900"
            :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-14 items-center justify-between px-4 lg:h-16">
                <span
                    class="text-lg font-semibold text-gray-900 dark:text-white"
                >
                    Flowkly
                </span>

                <button
                    type="button"
                    class="-mr-1 inline-flex h-10 w-10 items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 lg:hidden dark:text-gray-300 dark:hover:bg-gray-800"
                    aria-label="Fechar menu"
                    @click="closeMobile"
                >
                    <svg
                        class="h-6 w-6"
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

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors"
                    :class="
                        isActive(item.href.url)
                            ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400'
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'
                    "
                    @click="closeMobile"
                >
                    <svg
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            :d="item.icon"
                        />
                        <circle
                            v-for="(circle, index) in 'circles' in item
                                ? item.circles
                                : []"
                            :key="index"
                            :cx="circle.cx"
                            :cy="circle.cy"
                            :r="circle.r"
                        />
                    </svg>
                    {{ item.name }}
                </Link>
            </nav>

            <div class="border-t border-gray-200 p-3 dark:border-gray-800">
                <div class="flex items-center gap-3 rounded-md px-3 py-2">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white"
                    >
                        {{ user.name.charAt(0).toUpperCase() }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <p
                            class="truncate text-sm font-medium text-gray-900 dark:text-white"
                        >
                            {{ user.name }}
                        </p>
                        <p
                            class="truncate text-xs text-gray-500 dark:text-gray-400"
                        >
                            {{ user.email }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="mt-1 flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    @click="signOut"
                >
                    <svg
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"
                        />
                    </svg>
                    Sair
                </button>
            </div>
        </aside>

        <!-- Page content -->
        <main class="lg:pl-64">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div
                    v-if="page.props.flash.success"
                    class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300"
                >
                    {{ page.props.flash.success }}
                </div>

                <div
                    v-if="page.props.flash.error"
                    class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300"
                >
                    {{ page.props.flash.error }}
                </div>

                <slot />
            </div>
        </main>
    </div>
</template>
