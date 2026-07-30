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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Quiniela
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <ul class="divide-y divide-gray-200">
                        <li
                            v-for="round in rounds"
                            :key="round.id"
                        >
                            <Link
                                :href="route('quiniela.show', round.id)"
                                class="flex items-center justify-between px-6 py-4 hover:bg-gray-50"
                            >
                                <span class="font-medium text-gray-900">
                                    Jornada {{ round.number }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    {{ round.match_date }}
                                </span>
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="round.is_locked
                                        ? 'bg-red-100 text-red-700'
                                        : 'bg-green-100 text-green-700'"
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
