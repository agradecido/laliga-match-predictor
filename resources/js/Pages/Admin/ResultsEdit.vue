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
    <Head :title="`Jornada ${round.number} - Results`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Jornada {{ round.number }} results
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit">
                        <div
                            v-for="(fixture, index) in fixtures"
                            :key="fixture.id"
                            class="flex items-center justify-between border-b border-gray-100 py-4 last:border-0"
                        >
                            <div class="flex items-center gap-2">
                                <TeamCrest :team="fixture.home_team" />
                                <span>{{ fixture.home_team.name }}</span>
                                <span class="text-gray-400">vs</span>
                                <span>{{ fixture.away_team.name }}</span>
                                <TeamCrest :team="fixture.away_team" />
                            </div>

                            <div class="flex items-center gap-2">
                                <input
                                    type="number"
                                    min="0"
                                    class="w-16 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model.number="form.scores[index].home_score"
                                />
                                <span class="text-gray-400">-</span>
                                <input
                                    type="number"
                                    min="0"
                                    class="w-16 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model.number="form.scores[index].away_score"
                                />
                            </div>
                        </div>

                        <div class="mt-6">
                            <PrimaryButton
                                :disabled="form.processing"
                                type="submit"
                            >
                                Save results
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
