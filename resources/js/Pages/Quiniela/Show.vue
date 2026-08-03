<script setup lang="ts">
import { computed, reactive } from 'vue';
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

const choiceNames: Record<PredictionChoice, string> = {
    '1': 'Local',
    X: 'Empate',
    '2': 'Visitante',
};

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
    if (props.round.is_locked) {
        return;
    }

    const previous = selections[fixture.id];
    const nextChoice = previous === choice ? null : choice;
    selections[fixture.id] = nextChoice;
    status[fixture.id] = 'saving';

    router.post(
        route('quiniela.predictions.store', props.round.id),
        { predictions: [{ fixture_id: fixture.id, choice: nextChoice }] },
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

const total = props.fixtures.length;

const marked = computed(() => props.fixtures.filter((fixture) => selections[fixture.id]).length);

const progress = computed(() => (total > 0 ? (marked.value / total) * 100 : 0));

const matchDate = computed(() => {
    const [year, month, day] = props.round.match_date.split('-').map(Number);

    if (!year || !month || !day) {
        return props.round.match_date;
    }

    const formatted = new Intl.DateTimeFormat('es-ES', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    }).format(new Date(year, month - 1, day));

    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
});

const statusStyles: Record<Exclude<SaveStatus, 'idle'>, string> = {
    saving: 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-300',
    saved: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    error: 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-300',
};

function sideState(fixture: QuinielaFixture, side: 'home' | 'away') {
    const choice = selections[fixture.id];

    if (!choice || choice === 'X') {
        return 'neutral';
    }

    return (choice === '1') === (side === 'home') ? 'backed' : 'faded';
}

function formatKickoff(value: string): string {
    const [datePart, timePart] = value.split(' ');
    const [year, month, day] = datePart.split('-').map(Number);
    const [hour, minute] = timePart.split(':').map(Number);
    const date = new Date(year, month - 1, day);

    const weekday = new Intl.DateTimeFormat('es-ES', { weekday: 'short' }).format(date);
    const dayMonth = new Intl.DateTimeFormat('es-ES', { day: 'numeric', month: 'short' }).format(date);
    const time = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
    const label = `${weekday} ${dayMonth}`;

    return `${label.charAt(0).toUpperCase()}${label.slice(1)} · ${time}`;
}

function choiceDescription(fixture: QuinielaFixture, choice: PredictionChoice) {
    if (choice === '1') {
        return `Gana ${fixture.home_team.name}`;
    }

    if (choice === '2') {
        return `Gana ${fixture.away_team.name}`;
    }

    return `Empate entre ${fixture.home_team.name} y ${fixture.away_team.name}`;
}
</script>

<template>
    <Head :title="`Jornada ${round.number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                Jornada {{ round.number }}
            </h2>
        </template>

        <div class="py-6 sm:py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <section
                    class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10"
                >
                    <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-4 p-5 sm:p-6">
                        <div>
                            <p class="font-mono text-[11px] uppercase tracking-[0.22em] text-gray-400 dark:text-gray-500">
                                Boleto
                            </p>
                            <p class="mt-1.5 text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ matchDate }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{
                                    round.is_locked
                                        ? 'La jornada empezó: ya no se pueden cambiar los pronósticos.'
                                        : ''
                                }}
                            </p>
                        </div>

                        <div class="flex flex-col items-start gap-2 sm:items-end">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                                :class="
                                    round.is_locked
                                        ? 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'
                                        : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
                                "
                            >
                                <svg
                                    v-if="round.is_locked"
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 1a4 4 0 00-4 4v3H5.5A1.5 1.5 0 004 9.5v7A1.5 1.5 0 005.5 18h9a1.5 1.5 0 001.5-1.5v-7A1.5 1.5 0 0014.5 8H14V5a4 4 0 00-4-4zm2 7V5a2 2 0 10-4 0v3h4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span v-else class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true" />
                                {{ round.is_locked ? 'Cerrada' : 'Abierta' }}
                            </span>

                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-mono font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{
                                    marked
                                }}</span>
                                <span class="font-mono tabular-nums text-gray-400 dark:text-gray-500">/{{ total }}</span>
                                marcados
                            </p>
                        </div>
                    </div>

                    <div class="h-1 w-full bg-gray-100 dark:bg-white/10">
                        <div
                            class="h-full rounded-r-full transition-[width] duration-500 ease-out motion-reduce:transition-none"
                            :class="round.is_locked ? 'bg-gray-400 dark:bg-gray-500' : 'bg-indigo-500'"
                            :style="{ width: `${progress}%` }"
                        />
                    </div>
                </section>

                <ul v-if="total > 0" class="mt-4 space-y-3 sm:mt-5 sm:space-y-4">
                    <li v-for="(fixture, index) in fixtures" :key="fixture.id">
                        <article
                            class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5 transition duration-200 dark:bg-gray-800 dark:ring-white/10"
                            :class="
                                round.is_locked
                                    ? ''
                                    : 'hover:shadow-md hover:ring-gray-900/10 dark:hover:ring-white/20 motion-reduce:transition-none'
                            "
                        >
                            <div class="grid grid-cols-[auto_1fr_auto] items-center gap-3 px-4 pt-3 sm:px-5">
                                <span
                                    class="font-mono text-[11px] font-semibold tabular-nums tracking-[0.18em] text-gray-500 dark:text-gray-400"
                                >
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>

                                <div class="truncate text-center">
                                    <span
                                        v-if="fixture.kickoff_at"
                                        class="font-mono text-[11px] font-medium tabular-nums text-gray-400 dark:text-gray-500"
                                    >
                                        {{ formatKickoff(fixture.kickoff_at) }}
                                    </span>
                                </div>

                                <div aria-live="polite" class="flex min-h-[1.25rem] items-center justify-end">
                                    <Transition
                                        enter-active-class="transition duration-200 ease-out motion-reduce:transition-none"
                                        enter-from-class="translate-y-0.5 opacity-0"
                                        leave-active-class="transition duration-150 ease-in motion-reduce:transition-none"
                                        leave-to-class="opacity-0"
                                    >
                                        <span
                                            v-if="status[fixture.id] !== 'idle'"
                                            class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[11px] font-medium"
                                            :class="statusStyles[status[fixture.id] as Exclude<SaveStatus, 'idle'>]"
                                        >
                                            <svg
                                                v-if="status[fixture.id] === 'saving'"
                                                class="h-3 w-3 animate-spin"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                aria-hidden="true"
                                            >
                                                <circle
                                                    class="opacity-25"
                                                    cx="12"
                                                    cy="12"
                                                    r="10"
                                                    stroke="currentColor"
                                                    stroke-width="4"
                                                />
                                                <path
                                                    class="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                                />
                                            </svg>
                                            <svg
                                                v-else-if="status[fixture.id] === 'saved'"
                                                class="h-3.5 w-3.5"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.704 5.29a1 1 0 010 1.42l-7.5 7.5a1 1 0 01-1.415 0l-3.5-3.5a1 1 0 111.415-1.42L8.5 12.5l6.79-6.79a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <svg
                                                v-else
                                                class="h-3.5 w-3.5"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v4a1 1 0 102 0V7zm-1 7a1 1 0 100-2 1 1 0 000 2z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>

                                            <template v-if="status[fixture.id] === 'saving'">Guardando</template>
                                            <template v-else-if="status[fixture.id] === 'saved'">Guardado</template>
                                            <template v-else>No se guardó, vuelve a tocar</template>
                                        </span>
                                    </Transition>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 px-2 pb-4 pt-1 sm:px-3">
                                <div
                                    v-for="side in (['home', 'away'] as const)"
                                    :key="side"
                                    class="flex flex-col items-center gap-2.5 px-2 text-center sm:px-3"
                                    :class="
                                        side === 'away' ? 'border-l border-gray-100 dark:border-white/10' : ''
                                    "
                                >
                                    <span
                                        class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50 p-2 ring-1 ring-gray-900/5 transition duration-300 motion-reduce:transition-none sm:h-20 sm:w-20 sm:p-2.5 dark:bg-gray-100 dark:ring-white/20"
                                        :class="
                                            sideState(fixture, side) === 'faded'
                                                ? 'opacity-40 grayscale'
                                                : 'opacity-100'
                                        "
                                    >
                                        <TeamCrest
                                            :team="side === 'home' ? fixture.home_team : fixture.away_team"
                                            size-class="h-full w-full text-sm sm:text-base"
                                        />
                                    </span>

                                    <span
                                        class="min-h-[2.25rem] text-balance text-[13px] leading-snug transition-colors duration-200 motion-reduce:transition-none sm:min-h-[2.5rem] sm:text-sm"
                                        :class="{
                                            'font-semibold text-gray-900 dark:text-white':
                                                sideState(fixture, side) === 'backed',
                                            'font-medium text-gray-500 dark:text-gray-400':
                                                sideState(fixture, side) === 'faded',
                                            'font-medium text-gray-700 dark:text-gray-300':
                                                sideState(fixture, side) === 'neutral',
                                        }"
                                    >
                                        {{ side === 'home' ? fixture.home_team.name : fixture.away_team.name }}
                                    </span>
                                </div>
                            </div>

                            <div class="px-3 pb-3 sm:px-4 sm:pb-4">
                                <p
                                    v-if="round.is_locked && !selections[fixture.id]"
                                    class="rounded-xl bg-gray-50 py-3 text-center text-sm font-medium text-gray-500 dark:bg-white/5 dark:text-gray-400"
                                >
                                    Sin pronóstico
                                </p>

                                <div
                                    v-else
                                    role="group"
                                    :aria-label="`Pronóstico para ${fixture.home_team.name} contra ${fixture.away_team.name}`"
                                    class="grid grid-cols-3 gap-1 rounded-xl bg-gray-100 p-1 dark:bg-white/5"
                                >
                                    <button
                                        v-for="choice in choices"
                                        :key="choice"
                                        type="button"
                                        class="flex min-h-[3rem] flex-col items-center justify-center gap-1 rounded-lg px-1 py-2 transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-100 motion-reduce:transition-none dark:focus-visible:ring-offset-gray-800"
                                        :class="[
                                            selections[fixture.id] === choice
                                                ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30'
                                                : 'text-gray-600 dark:text-gray-300',
                                            round.is_locked
                                                ? 'cursor-not-allowed'
                                                : selections[fixture.id] === choice
                                                  ? 'cursor-default'
                                                  : 'cursor-pointer hover:bg-white hover:text-gray-900 hover:shadow-sm dark:hover:bg-white/10 dark:hover:text-white',
                                            round.is_locked && selections[fixture.id] !== choice ? 'opacity-40' : '',
                                        ]"
                                        :disabled="round.is_locked"
                                        :aria-pressed="selections[fixture.id] === choice"
                                        :aria-label="choiceDescription(fixture, choice)"
                                        @click="pick(fixture, choice)"
                                    >
                                        <span class="font-mono text-base font-bold leading-none tabular-nums">
                                            {{ choice }}
                                        </span>
                                        <span
                                            class="text-[10px] font-semibold uppercase leading-none tracking-wider"
                                            :class="
                                                selections[fixture.id] === choice
                                                    ? 'text-indigo-100'
                                                    : 'text-gray-500 dark:text-gray-400'
                                            "
                                        >
                                            {{ choiceNames[choice] }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </article>
                    </li>
                </ul>

                <div
                    v-if="total > 0"
                    class="mt-4 flex flex-wrap items-center justify-between gap-x-6 gap-y-2 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5 sm:mt-5 sm:p-6 dark:bg-gray-800 dark:ring-white/10"
                >
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-mono font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{
                            marked
                        }}</span>
                        <span class="font-mono tabular-nums text-gray-400 dark:text-gray-500">/{{ total }}</span>
                        marcados
                    </p>

                    <p
                        class="text-sm font-medium"
                        :class="
                            marked === total
                                ? 'text-emerald-700 dark:text-emerald-300'
                                : 'text-gray-500 dark:text-gray-400'
                        "
                    >
                        {{
                            marked === total
                                ? `¡Listo! Has marcado los ${total} pronósticos`
                                : `Te faltan ${total - marked} pronósticos`
                        }}
                    </p>
                </div>

                <p
                    v-else
                    class="mt-4 rounded-2xl bg-white p-10 text-center text-sm text-gray-500 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-800 dark:text-gray-400 dark:ring-white/10"
                >
                    Esta jornada todavía no tiene partidos.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
