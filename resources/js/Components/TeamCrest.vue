<script setup lang="ts">
import { computed, ref } from 'vue';
import type { Team } from '@/types/quiniela';

const props = withDefaults(
    defineProps<{
        team: Team;
        sizeClass?: string;
    }>(),
    {
        sizeClass: 'h-7 w-7 text-xs',
    },
);

const failed = ref(false);

const clubTokens = new Set(['fc', 'cf', 'rcd', 'rc', 'ud', 'cd', 'ca', 'sd', 'sad', 'de', 'r']);

const initials = computed(() => {
    const word = props.team.name
        .split(/[\s.]+/)
        .filter(Boolean)
        .find((part) => !clubTokens.has(part.replace(/\./g, '').toLowerCase()));

    return (word ?? props.team.name).slice(0, 3).toUpperCase();
});
</script>

<template>
    <img
        v-if="!failed"
        :src="`/assets/images/team-logos/${team.normalized_name}.svg`"
        :alt="`Escudo de ${team.name}`"
        class="inline-block object-contain"
        :class="sizeClass"
        @error="failed = true"
    />
    <span
        v-else
        role="img"
        :aria-label="`Escudo de ${team.name}`"
        class="inline-flex items-center justify-center rounded-full bg-gray-200 font-bold tracking-tight text-gray-700 ring-1 ring-black/5"
        :class="sizeClass"
        >{{ initials }}</span
    >
</template>
