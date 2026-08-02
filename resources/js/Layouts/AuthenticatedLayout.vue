<script setup lang="ts">
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const page = usePage();

const currentRoundHref = computed(() =>
    page.props.currentRoundId ? route('quiniela.show', page.props.currentRoundId) : route('quiniela.index'),
);

const isCurrentRoundActive = computed(() =>
    page.props.currentRoundId
        ? route().current('quiniela.show', { round: page.props.currentRoundId })
        : route().current('quiniela.index'),
);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-950">
            <nav
                class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="currentRoundHref">
                                    <ApplicationLogo
                                        class="block h-9 w-auto"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="currentRoundHref"
                                    :active="isCurrentRoundActive"
                                >
                                    Jornada actual
                                </NavLink>
                                <NavLink
                                    :href="route('quiniela.index')"
                                    :active="route().current('quiniela.index') || route().current('quiniela.show')"
                                >
                                    Quiniela
                                </NavLink>
                                <NavLink
                                    :href="route('quiniela.leaderboard')"
                                    :active="route().current('quiniela.leaderboard')"
                                >
                                    Clasificación
                                </NavLink>
                                <div v-if="$page.props.auth.user.is_admin" class="relative flex items-center">
                                    <Dropdown align="left" width="48">
                                        <template #trigger>
                                            <button
                                                type="button"
                                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                                :class="route().current('admin.*')
                                                    ? 'border-indigo-400 text-gray-900 dark:border-indigo-600 dark:text-gray-100'
                                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:text-gray-300'"
                                            >
                                                Admin
                                            </button>
                                        </template>

                                        <template #content>
                                            <DropdownLink
                                                :href="route('admin.rounds.index')"
                                            >
                                                Jornadas
                                            </DropdownLink>
                                            <DropdownLink
                                                :href="route('admin.users.index')"
                                            >
                                                Jugadores
                                            </DropdownLink>
                                            <DropdownLink
                                                :href="route('admin.predictions.index')"
                                            >
                                                Pronósticos
                                            </DropdownLink>
                                            <DropdownLink
                                                :href="route('admin.results.index')"
                                            >
                                                Resultados
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center sm:gap-2">
                            <ThemeToggle />

                            <!-- Settings Dropdown -->
                            <div class="relative ms-1">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Perfil
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Cerrar sesión
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center gap-1 sm:hidden">
                            <ThemeToggle />

                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300 dark:focus:bg-gray-700 dark:focus:text-gray-300"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="currentRoundHref"
                            :active="isCurrentRoundActive"
                        >
                            Jornada actual
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('quiniela.index')"
                            :active="route().current('quiniela.index') || route().current('quiniela.show')"
                        >
                            Quiniela
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('quiniela.leaderboard')"
                            :active="route().current('quiniela.leaderboard')"
                        >
                            Clasificación
                        </ResponsiveNavLink>
                    </div>

                    <div
                        v-if="$page.props.auth.user.is_admin"
                        class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-700"
                    >
                        <div class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            Admin
                        </div>
                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink
                                :href="route('admin.rounds.index')"
                                :active="route().current('admin.rounds.*')"
                            >
                                Jornadas
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('admin.users.index')"
                                :active="route().current('admin.users.*')"
                            >
                                Jugadores
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('admin.predictions.index')"
                                :active="route().current('admin.predictions.*')"
                            >
                                Pronósticos
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('admin.results.index')"
                                :active="route().current('admin.results.*')"
                            >
                                Resultados
                            </ResponsiveNavLink>
                        </div>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-700"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Cerrar sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow dark:bg-gray-800"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
