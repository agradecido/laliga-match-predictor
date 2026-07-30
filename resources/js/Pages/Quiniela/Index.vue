<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { RoundSummary } from '@/types/quiniela';

defineProps<{
    rounds: RoundSummary[];
}>();
</script>

<template>
    <Head title="Quiniela" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                Quiniela
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
                                :href="route('quiniela.show', round.id)"
                                class="flex flex-wrap items-center justify-between gap-2 px-4 py-4 hover:bg-gray-50 sm:px-6 dark:hover:bg-gray-700/50"
                            >
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    Jornada {{ round.number }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ round.match_date }}
                                </span>
                                <span
                                    class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="round.is_locked
                                        ? 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400'
                                        : 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400'"
                                >
                                    {{ round.is_locked ? 'Locked' : 'Open' }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
