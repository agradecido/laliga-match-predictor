<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { LeaderboardEntry } from '@/types/quiniela';

defineProps<{
    leaderboard: LeaderboardEntry[];
}>();
</script>

<template>
    <Head title="Clasificación" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                Clasificación
            </h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-900/40 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3 sm:px-6">#</th>
                                    <th class="px-4 py-3 sm:px-6">Jugador</th>
                                    <th class="px-4 py-3 sm:px-6">Puntos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="(entry, index) in leaderboard"
                                    :key="entry.name"
                                >
                                    <td class="px-4 py-3 text-gray-500 sm:px-6 dark:text-gray-400">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 sm:px-6 dark:text-gray-100">{{ entry.name }}</td>
                                    <td class="px-4 py-3 sm:px-6">{{ entry.points }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
