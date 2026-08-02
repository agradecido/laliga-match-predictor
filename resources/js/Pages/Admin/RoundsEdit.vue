<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import type { Season } from '@/types/admin';

const props = defineProps<{
    round: {
        id: number;
        season_id: number;
        number: number;
        match_date: string;
    };
    seasons: Season[];
}>();

const form = useForm({
    season_id: props.round.season_id as number | '',
    number: props.round.number as number | '',
    match_date: props.round.match_date,
});

function submit() {
    form.put(route('admin.rounds.update', props.round.id));
}
</script>

<template>
    <Head :title="`Admin - Editar jornada ${round.number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                Editar jornada {{ round.number }}
            </h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8 space-y-6">
                <div class="overflow-hidden bg-white px-4 py-6 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
                    <form class="space-y-6" @submit.prevent="submit">
                        <div>
                            <InputLabel for="season_id" value="Temporada" />
                            <select
                                id="season_id"
                                v-model="form.season_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
                            >
                                <option
                                    v-for="season in seasons"
                                    :key="season.id"
                                    :value="season.id"
                                >
                                    {{ season.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.season_id" />
                        </div>

                        <div>
                            <InputLabel for="number" value="Número de jornada" />
                            <input
                                id="number"
                                type="number"
                                min="1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
                                v-model.number="form.number"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.number" />
                        </div>

                        <div>
                            <InputLabel for="match_date" value="Fecha" />
                            <TextInput
                                id="match_date"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="form.match_date"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.match_date" />
                        </div>

                        <div class="flex items-center gap-3">
                            <PrimaryButton :disabled="form.processing" type="submit">
                                Guardar cambios
                            </PrimaryButton>
                            <Link :href="route('admin.rounds.index')">
                                <SecondaryButton type="button">Cancelar</SecondaryButton>
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
