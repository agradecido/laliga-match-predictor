<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { LeaderboardEntry, PotSummary } from '@/types/quiniela';

defineProps<{
    leaderboard: LeaderboardEntry[];
    pot: PotSummary | null;
}>();

const currencyFormatter = new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' });

function formatCurrency(amount: number): string {
    return currencyFormatter.format(amount);
}
</script>

<template>
    <Head title="Clasificación" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                Clasificación
            </h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="mx-auto max-w-2xl space-y-6 sm:px-6 lg:px-8">
                <div
                    v-if="pot"
                    class="overflow-hidden bg-white px-4 py-5 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800"
                >
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Bote
                    </h3>

                    <p v-if="pot.winner_name" class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                        🏆 Ganador: <span class="font-medium text-gray-900 dark:text-gray-100">{{ pot.winner_name }}</span>
                        — bote de {{ formatCurrency(pot.total_collected) }} repartido
                    </p>

                    <dl class="mt-3 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Cuota</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ formatCurrency(pot.entry_fee) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Recaudado</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ formatCurrency(pot.total_collected) }} / {{ formatCurrency(pot.total_expected) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Tu estado</dt>
                            <dd>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="pot.has_paid
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400'
                                        : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                                >
                                    {{ pot.has_paid ? 'Pagado' : 'Pendiente' }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-900/40 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3 sm:px-6">#</th>
                                    <th class="px-4 py-3 sm:px-6">Jugador</th>
                                    <th class="px-4 py-3 sm:px-6">Puntos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="(entry, index) in leaderboard"
                                    :key="entry.name"
                                >
                                    <td class="px-4 py-3 text-gray-500 sm:px-6 dark:text-gray-400">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 sm:px-6 dark:text-gray-100">{{ entry.name }}</td>
                                    <td class="px-4 py-3 sm:px-6">{{ entry.points }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
