<script setup lang="ts">
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TeamCrest from '@/Components/TeamCrest.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { AdminUserRound } from '@/types/admin';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        is_admin: boolean;
        created_at: string;
    };
    rounds: AdminUserRound[];
}>();

const currentIndex = ref(props.rounds.length > 0 ? props.rounds.length - 1 : 0);

const currentRound = computed<AdminUserRound | null>(() => props.rounds[currentIndex.value] ?? null);

function goPrevious() {
    if (currentIndex.value > 0) {
        currentIndex.value--;
    }
}

function goNext() {
    if (currentIndex.value < props.rounds.length - 1) {
        currentIndex.value++;
    }
}

let touchStartX = 0;

function onTouchStart(event: TouchEvent) {
    touchStartX = event.changedTouches[0].screenX;
}

function onTouchEnd(event: TouchEvent) {
    const deltaX = event.changedTouches[0].screenX - touchStartX;
    const threshold = 50;

    if (deltaX > threshold) {
        goPrevious();
    } else if (deltaX < -threshold) {
        goNext();
    }
}
</script>

<template>
    <Head :title="`Admin - ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    {{ user.name }}
                </h2>
                <Link :href="route('admin.users.edit', user.id)">
                    <SecondaryButton type="button">Editar</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-6 sm:py-12">
            <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white px-4 py-6 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Correo electrónico</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ user.email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Rol</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ user.is_admin ? 'Administrador' : 'Jugador' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Registrado el</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ user.created_at }}</dd>
                        </div>
                    </dl>
                </div>

                <div v-if="rounds.length > 0" class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b border-gray-100 px-2 py-4 sm:px-4 dark:border-gray-700">
                        <button
                            type="button"
                            class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                            :disabled="currentIndex === 0"
                            aria-label="Jornada anterior"
                            @click="goPrevious"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path
                                    fill-rule="evenodd"
                                    d="M12.79 5.23a.75.75 0 010 1.06L9.06 10l3.73 3.71a.75.75 0 11-1.06 1.06l-4.25-4.25a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.06 0z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>

                        <div class="text-center">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Jornada {{ currentRound?.round_number }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ currentRound?.match_date }} · {{ currentIndex + 1 }} de {{ rounds.length }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                            :disabled="currentIndex === rounds.length - 1"
                            aria-label="Jornada siguiente"
                            @click="goNext"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path
                                    fill-rule="evenodd"
                                    d="M7.21 14.77a.75.75 0 010-1.06L10.94 10 7.21 6.29a.75.75 0 111.06-1.06l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06 0z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </div>

                    <ul
                        class="divide-y divide-gray-200 dark:divide-gray-700"
                        @touchstart="onTouchStart"
                        @touchend="onTouchEnd"
                    >
                        <li
                            v-for="prediction in currentRound?.predictions ?? []"
                            :key="prediction.id"
                            class="flex flex-wrap items-center justify-between gap-2 px-4 py-4 sm:px-6"
                        >
                            <div class="flex flex-wrap items-center gap-2">
                                <TeamCrest :team="prediction.home_team" />
                                <span>{{ prediction.home_team.name }}</span>
                                <span class="text-gray-400 dark:text-gray-500">vs</span>
                                <span>{{ prediction.away_team.name }}</span>
                                <TeamCrest :team="prediction.away_team" />
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="rounded-md bg-gray-100 px-2 py-1 text-sm font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                    {{ prediction.choice }}
                                </span>
                                <span
                                    v-if="prediction.is_correct !== null"
                                    class="rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="prediction.is_correct
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400'
                                        : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400'"
                                >
                                    {{ prediction.is_correct ? 'Acierto' : 'Fallo' }}
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>

                <p
                    v-else
                    class="rounded-lg bg-white px-4 py-6 text-center text-sm text-gray-500 shadow-sm sm:rounded-lg dark:bg-gray-800 dark:text-gray-400"
                >
                    Este jugador todavía no ha hecho ningún pronóstico.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
