<script setup lang="ts">
import { reactive } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TeamCrest from '@/Components/TeamCrest.vue';
import { Head, router } from '@inertiajs/vue3';
import type { PredictionChoice, QuinielaFixture, RoundSummary } from '@/types/quiniela';

const props = defineProps<{
    round: RoundSummary;
    fixtures: QuinielaFixture[];
}>();

type SaveStatus = 'idle' | 'saving' | 'saved' | 'error';

const choices: PredictionChoice[] = ['1', 'X', '2'];

const selections = reactive<Record<number, PredictionChoice | null>>(
    Object.fromEntries(props.fixtures.map((fixture) => [fixture.id, fixture.choice])),
);

const status = reactive<Record<number, SaveStatus>>(
    Object.fromEntries(props.fixtures.map((fixture) => [fixture.id, 'idle'])),
);

function clearStatusLater(fixtureId: number, from: SaveStatus, delay: number) {
    setTimeout(() => {
        if (status[fixtureId] === from) {
            status[fixtureId] = 'idle';
        }
    }, delay);
}

function pick(fixture: QuinielaFixture, choice: PredictionChoice) {
    if (props.round.is_locked || selections[fixture.id] === choice) {
        return;
    }

    const previous = selections[fixture.id];
    selections[fixture.id] = choice;
    status[fixture.id] = 'saving';

    router.post(
        route('quiniela.predictions.store', props.round.id),
        { predictions: [{ fixture_id: fixture.id, choice }] },
        {
            preserveScroll: true,
            preserveState: true,
            only: [],
            onSuccess: () => {
                status[fixture.id] = 'saved';
                clearStatusLater(fixture.id, 'saved', 1500);
            },
            onError: () => {
                selections[fixture.id] = previous;
                status[fixture.id] = 'error';
                clearStatusLater(fixture.id, 'error', 2500);
            },
        },
    );
}
</script>

<template>
    <Head :title="`Jornada ${round.number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                Jornada {{ round.number }}
                <span
                    v-if="round.is_locked"
                    class="ms-2 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-400"
                >
                    Locked
                </span>
            </h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white px-4 py-2 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
                    <div
                        v-for="fixture in fixtures"
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

                        <div class="flex items-center justify-between gap-3 sm:justify-end">
                            <div class="flex gap-2">
                                <button
                                    v-for="choice in choices"
                                    :key="choice"
                                    type="button"
                                    class="min-w-11 flex-1 rounded-md border px-3 py-2 text-sm font-medium transition-colors sm:flex-initial sm:py-1"
                                    :class="[
                                        selections[fixture.id] === choice
                                            ? 'border-indigo-600 bg-indigo-600 text-white'
                                            : 'border-gray-300 text-gray-700 hover:border-gray-400 dark:border-gray-600 dark:text-gray-300 dark:hover:border-gray-500',
                                        round.is_locked ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
                                    ]"
                                    :disabled="round.is_locked"
                                    @click="pick(fixture, choice)"
                                >
                                    {{ choice }}
                                </button>
                            </div>

                            <div class="flex w-20 items-center gap-1 text-xs">
                                <template v-if="status[fixture.id] === 'saving'">
                                    <svg class="h-4 w-4 animate-spin text-gray-400" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                    </svg>
                                    <span class="text-gray-400">Guardando</span>
                                </template>
                                <template v-else-if="status[fixture.id] === 'saved'">
                                    <svg class="h-4 w-4 text-green-600 dark:text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.5 7.5a1 1 0 01-1.415 0l-3.5-3.5a1 1 0 111.415-1.42L8.5 12.5l6.79-6.79a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-green-600 dark:text-green-400">Guardado</span>
                                </template>
                                <template v-else-if="status[fixture.id] === 'error'">
                                    <svg class="h-4 w-4 text-red-600 dark:text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v4a1 1 0 102 0V7zm-1 7a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-red-600 dark:text-red-400">Error</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
