<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { LeaderboardEntry } from '@/types/quiniela';

defineProps<{
    leaderboard: LeaderboardEntry[];
}>();
</script>

<template>
    <Head title="Leaderboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Leaderboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3">#</th>
                                <th class="px-6 py-3">Player</th>
                                <th class="px-6 py-3">Points</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="(entry, index) in leaderboard"
                                :key="entry.name"
                            >
                                <td class="px-6 py-3 text-gray-500">{{ index + 1 }}</td>
                                <td class="px-6 py-3 font-medium text-gray-900">{{ entry.name }}</td>
                                <td class="px-6 py-3">{{ entry.points }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
