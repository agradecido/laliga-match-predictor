<script setup lang="ts">
import { ref } from 'vue';
import type { Team } from '@/types/quiniela';

defineProps<{
    team: Team;
}>();

const failed = ref(false);
</script>

<template>
    <img
        v-if="!failed"
        :src="`/assets/images/team-logos/${team.normalized_name}.svg`"
        :alt="team.name"
        width="28"
        height="28"
        class="inline-block"
        @error="failed = true"
    />
    <span
        v-else
        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-600"
        :title="team.name"
    >
        {{ team.name.slice(0, 2).toUpperCase() }}
    </span>
</template>
