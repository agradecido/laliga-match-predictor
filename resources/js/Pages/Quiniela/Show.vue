<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TeamCrest from '@/Components/TeamCrest.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import type { PredictionChoice, QuinielaFixture, RoundSummary } from '@/types/quiniela';

const props = defineProps<{
    round: RoundSummary;
    fixtures: QuinielaFixture[];
}>();

const form = useForm({
    predictions: props.fixtures.map((fixture) => ({
        fixture_id: fixture.id,
        choice: fixture.choice as PredictionChoice | null,
    })),
});

const choices: PredictionChoice[] = ['1', 'X', '2'];

function submit() {
    form
        .transform((data) => ({
            predictions: data.predictions.filter((prediction) => prediction.choice !== null),
        }))
        .post(route('quiniela.predictions.store', props.round.id));
}
</script>

<template>
    <Head :title="`Jornada ${round.number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Jornada {{ round.number }}
                <span
                    v-if="round.is_locked"
                    class="ms-2 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700"
                >
                    Locked
                </span>
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

                            <div class="flex gap-2">
                                <label
                                    v-for="choice in choices"
                                    :key="choice"
                                    class="cursor-pointer rounded-md border px-3 py-1 text-sm font-medium"
                                    :class="form.predictions[index].choice === choice
                                        ? 'border-indigo-600 bg-indigo-600 text-white'
                                        : 'border-gray-300 text-gray-700'"
                                >
                                    <input
                                        type="radio"
                                        class="sr-only"
                                        :name="`fixture-${fixture.id}`"
                                        :value="choice"
                                        v-model="form.predictions[index].choice"
                                        :disabled="round.is_locked"
                                    />
                                    {{ choice }}
                                </label>
                            </div>
                        </div>

                        <div class="mt-6">
                            <PrimaryButton
                                :disabled="round.is_locked || form.processing"
                                type="submit"
                            >
                                Save predictions
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
