<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { AdminRound } from '@/types/admin';

defineProps<{
    rounds: AdminRound[];
}>();
</script>

<template>
    <Head title="Admin - Jornadas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    Jornadas
                </h2>

                <Link :href="route('admin.rounds.create')">
                    <PrimaryButton>Nueva jornada</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-6 sm:py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li
                            v-for="round in rounds"
                            :key="round.id"
                        >
                            <Link
                                :href="route('admin.rounds.edit', round.id)"
                                class="flex flex-wrap items-center justify-between gap-2 px-4 py-4 hover:bg-gray-50 sm:px-6 dark:hover:bg-gray-700/50"
                            >
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ round.season_name }} — Jornada {{ round.number }}
                                </span>
                                <span class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ round.match_date }}
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="round.is_locked
                                            ? 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
                                            : 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400'"
                                    >
                                        {{ round.is_locked ? 'Cerrada' : 'Abierta' }}
                                    </span>
                                </span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
