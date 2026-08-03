<script setup lang="ts">
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import type { AdminPotPlayer, AdminPotSeason } from '@/types/admin';

const props = defineProps<{
    season: AdminPotSeason | null;
    players: AdminPotPlayer[];
    total_collected: number;
    total_expected: number;
    leader_name: string | null;
}>();

const currencyFormatter = new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' });

function formatCurrency(amount: number): string {
    return currencyFormatter.format(amount);
}

const feeForm = useForm({
    entry_fee: (props.season?.entry_fee ?? '') as number | '',
});

function submitFee() {
    feeForm.put(route('admin.pot.fee.update'));
}

function togglePayment(player: AdminPotPlayer) {
    router.post(
        route('admin.pot.payments.toggle', player.id),
        {},
        { preserveScroll: true },
    );
}

const payoutForm = useForm({
    winner_user_id: '' as number | '',
});

function submitPayout() {
    payoutForm.post(route('admin.pot.payout'), {
        onSuccess: () => payoutForm.reset(),
    });
}

const paidCount = computed(() => props.players.filter((player) => player.has_paid).length);
</script>

<template>
    <Head title="Admin - Bote" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                Bote
            </h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                <p
                    v-if="!season"
                    class="rounded-lg bg-white p-6 text-sm text-gray-500 shadow-sm sm:rounded-lg dark:bg-gray-800 dark:text-gray-400"
                >
                    No hay ninguna temporada activa todavía.
                </p>

                <template v-else>
                    <div class="overflow-hidden bg-white px-4 py-6 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Cuota — {{ season.name }}
                        </h3>
                        <form class="mt-4 flex flex-wrap items-end gap-3" @submit.prevent="submitFee">
                            <div>
                                <InputLabel for="entry_fee" value="Cuota por persona (€)" />
                                <input
                                    id="entry_fee"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
                                    v-model.number="feeForm.entry_fee"
                                />
                                <InputError class="mt-2" :message="feeForm.errors.entry_fee" />
                            </div>
                            <PrimaryButton :disabled="feeForm.processing" type="submit">
                                Guardar cuota
                            </PrimaryButton>
                        </form>

                        <dl v-if="season.entry_fee !== null" class="mt-6 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Recaudado</dt>
                                <dd class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ formatCurrency(total_collected) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Esperado</dt>
                                <dd class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ formatCurrency(total_expected) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Líder actual</dt>
                                <dd class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ leader_name ?? '—' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="flex items-center justify-between px-4 py-4 sm:px-6">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                Pagos
                            </h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ paidCount }}/{{ players.length }} pagados
                            </span>
                        </div>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            <li
                                v-for="player in players"
                                :key="player.id"
                                class="flex items-center justify-between gap-2 px-4 py-4 sm:px-6"
                            >
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ player.name }}
                                </span>
                                <div class="flex items-center gap-3">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="player.has_paid
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400'
                                            : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                                    >
                                        {{ player.has_paid ? 'Pagado' : 'Pendiente' }}
                                    </span>
                                    <SecondaryButton type="button" @click="togglePayment(player)">
                                        {{ player.has_paid ? 'Marcar pendiente' : 'Marcar pagado' }}
                                    </SecondaryButton>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="overflow-hidden bg-white px-4 py-6 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Reparto del bote
                        </h3>

                        <p v-if="season.winner_name" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            🏆 Ganador: <span class="font-medium text-gray-900 dark:text-gray-100">{{ season.winner_name }}</span>
                            — repartido el {{ season.payout_at }}
                        </p>

                        <form class="mt-4 flex flex-wrap items-end gap-3" @submit.prevent="submitPayout">
                            <div>
                                <InputLabel for="winner_user_id" value="Ganador" />
                                <select
                                    id="winner_user_id"
                                    v-model="payoutForm.winner_user_id"
                                    class="mt-1 block w-56 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
                                >
                                    <option value="" disabled>Selecciona un jugador</option>
                                    <option
                                        v-for="player in players"
                                        :key="player.id"
                                        :value="player.id"
                                    >
                                        {{ player.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="payoutForm.errors.winner_user_id" />
                            </div>
                            <PrimaryButton :disabled="payoutForm.processing" type="submit">
                                Marcar bote repartido
                            </PrimaryButton>
                        </form>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
