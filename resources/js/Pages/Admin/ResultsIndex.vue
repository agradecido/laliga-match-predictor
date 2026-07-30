<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { RoundSummary } from '@/types/quiniela';

defineProps<{
    rounds: RoundSummary[];
}>();
</script>

<template>
    <Head title="Admin - Results" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                Enter results
            </h2>
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
                                :href="route('admin.results.edit', round.id)"
                                class="flex flex-wrap items-center justify-between gap-2 px-4 py-4 hover:bg-gray-50 sm:px-6 dark:hover:bg-gray-700/50"
                            >
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    Jornada {{ round.number }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ round.match_date }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
