<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TeamCrest from '@/Components/TeamCrest.vue';
import { Head, useForm } from '@inertiajs/vue3';
import type { AdminPlayerPicks, AdminPredictionFixture } from '@/types/admin';

const props = defineProps<{
    round: {
        id: number;
        number: number;
        match_date: string;
    };
    fixtures: AdminPredictionFixture[];
    players: AdminPlayerPicks[];
}>();

const form = useForm({
    entries: props.players.flatMap((player) =>
        player.picks.map((pick) => ({
            user_id: player.id,
            fixture_id: pick.fixture_id,
            choice: pick.choice,
        })),
    ),
});

function entryIndex(playerIndex: number, fixtureIndex: number) {
    return playerIndex * props.fixtures.length + fixtureIndex;
}

function submit() {
    form.put(route('admin.predictions.update', props.round.id));
}
</script>

<template>
    <Head :title="`Admin - Pronósticos jornada ${round.number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                Pronósticos de la jornada {{ round.number }}
            </h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <form @submit.prevent="submit">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="whitespace-nowrap px-4 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Partido
                                        </th>
                                        <th
                                            v-for="player in players"
                                            :key="player.id"
                                            class="whitespace-nowrap px-4 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            {{ player.name }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(fixture, fixtureIndex) in fixtures" :key="fixture.id">
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <TeamCrest :team="fixture.home_team" />
                                                <span>{{ fixture.home_team.name }}</span>
                                                <span class="text-gray-400 dark:text-gray-500">-</span>
                                                <span>{{ fixture.away_team.name }}</span>
                                                <TeamCrest :team="fixture.away_team" />
                                                <span
                                                    v-if="fixture.result_sign"
                                                    class="ms-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                >
                                                    {{ fixture.result_sign }}
                                                </span>
                                            </div>
                                        </td>
                                        <td
                                            v-for="(player, playerIndex) in players"
                                            :key="player.id"
                                            class="px-4 py-3"
                                        >
                                            <select
                                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
                                                v-model="form.entries[entryIndex(playerIndex, fixtureIndex)].choice"
                                            >
                                                <option :value="null">—</option>
                                                <option value="1">1</option>
                                                <option value="X">X</option>
                                                <option value="2">2</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="p-4 sm:p-6">
                            <PrimaryButton :disabled="form.processing" type="submit">
                                Guardar pronósticos
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
