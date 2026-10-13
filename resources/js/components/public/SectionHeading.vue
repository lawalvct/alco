<script setup lang="ts">
withDefaults(
    defineProps<{
        title: string;
        eyebrow?: string | null;
        description?: string | null;
        id?: string;
        tone?: 'light' | 'dark';
    }>(),
    { tone: 'light' },
);
</script>

<template>
    <div>
        <p
            v-if="eyebrow"
            class="eyebrow"
            :class="tone === 'dark' ? 'text-aqua-400' : 'text-aqua-700'"
        >
            {{ eyebrow }}
        </p>
        <h2
            :id="id"
            class="text-3xl leading-tight font-semibold tracking-tight text-balance sm:text-4xl"
            :class="[eyebrow && 'mt-4', tone === 'dark' && 'text-white']"
        >
            {{ title }}
        </h2>
        <p
            v-if="description"
            class="mt-5 text-base leading-7"
            :class="tone === 'dark' ? 'text-white/75' : 'text-muted-foreground'"
        >
            {{ description }}
        </p>
    </div>
</template>
