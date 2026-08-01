<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TeamCrest from '@/Components/TeamCrest.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import type { AdminFixture, RoundSummary } from '@/types/quiniela';

const props = defineProps<{
    round: Pick<RoundSummary, 'id' | 'number' | 'match_date'>;
    fixtures: AdminFixture[];
}>();

const form = useForm({
    scores: props.fixtures.map((fixture) => ({
        fixture_id: fixture.id,
        home_score: fixture.home_score,
        away_score: fixture.away_score,
    })),
});

function submit() {
    form.transform((data) => ({
        scores: data.scores.filter(
            (score) => score.home_score !== null && score.away_score !== null,
        ),
    })).put(route('admin.results.update', props.round.id));
}
</script>

<template>
    <Head :title="`Jornada ${round.number} - Resultados`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                Resultados de la jornada {{ round.number }}
            </h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white px-4 py-2 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
                    <form @submit.prevent="submit">
                        <div
                            v-for="(fixture, index) in fixtures"
                            :key="fixture.id"
                            class="flex flex-col gap-3 border-b border-gray-100 py-4 last:border-0 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700"
                        >
                            <div class="flex flex-wrap items-center gap-2">
                                <TeamCrest :team="fixture.home_team" />
                                <span>{{ fixture.home_team.name }}</span>
                                <span class="text-gray-400 dark:text-gray-500">vs</span>
                                <span>{{ fixture.away_team.name }}</span>
                                <TeamCrest :team="fixture.away_team" />
                            </div>

                            <div class="flex items-center gap-2">
                                <input
                                    type="number"
                                    min="0"
                                    class="w-16 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
                                    v-model.number="form.scores[index].home_score"
                                />
                                <span class="text-gray-400 dark:text-gray-500">-</span>
                                <input
                                    type="number"
                                    min="0"
                                    class="w-16 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
                                    v-model.number="form.scores[index].away_score"
                                />
                            </div>
                        </div>

                        <div class="mt-6">
                            <PrimaryButton
                                :disabled="form.processing"
                                type="submit"
                            >
                                Guardar resultados
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
