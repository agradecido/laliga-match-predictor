<script setup lang="ts">
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import type { AdminUser } from '@/types/admin';

defineProps<{
    users: AdminUser[];
}>();

const page = usePage();

const confirmingDeletionFor = ref<number | null>(null);
const deleteForm = useForm({});

function deleteUser() {
    if (confirmingDeletionFor.value === null) {
        return;
    }

    deleteForm.delete(route('admin.users.destroy', confirmingDeletionFor.value), {
        onSuccess: () => (confirmingDeletionFor.value = null),
    });
}
</script>

<template>
    <Head title="Admin - Jugadores" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    Jugadores
                </h2>

                <Link :href="route('admin.users.create')">
                    <PrimaryButton>Nuevo jugador</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-6 sm:py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li
                            v-for="user in users"
                            :key="user.id"
                            class="flex flex-wrap items-center justify-between gap-2 px-4 py-4 sm:px-6"
                        >
                            <Link
                                :href="route('admin.users.show', user.id)"
                                class="flex flex-col hover:underline"
                            >
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ user.name }}
                                    <span
                                        v-if="user.is_admin"
                                        class="ms-2 rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400"
                                    >
                                        Admin
                                    </span>
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ user.email }}
                                </span>
                            </Link>

                            <div class="flex items-center gap-3">
                                <Link
                                    :href="route('admin.users.edit', user.id)"
                                    class="text-sm text-indigo-600 hover:underline dark:text-indigo-400"
                                >
                                    Editar
                                </Link>
                                <button
                                    v-if="user.id !== page.props.auth.user.id"
                                    type="button"
                                    class="text-sm text-red-600 hover:underline dark:text-red-400"
                                    @click="confirmingDeletionFor = user.id"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <Modal :show="confirmingDeletionFor !== null" @close="confirmingDeletionFor = null">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    ¿Eliminar este jugador?
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Se borrará su cuenta y todos sus pronósticos de forma permanente.
                </p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="confirmingDeletionFor = null">
                        Cancelar
                    </SecondaryButton>
                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': deleteForm.processing }"
                        :disabled="deleteForm.processing"
                        @click="deleteUser"
                    >
                        Eliminar jugador
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
