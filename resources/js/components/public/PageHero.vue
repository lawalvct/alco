<script setup lang="ts">
import BrandPattern from '@/components/public/BrandPattern.vue';

defineProps<{
    title: string;
    eyebrow?: string | null;
    intro?: string | null;
}>();
</script>

<template>
    <section
        class="relative isolate overflow-hidden bg-navy-900 py-16 text-white sm:py-20"
    >
        <BrandPattern
            class="absolute -right-24 -bottom-28 -z-10 hidden h-105 w-85 text-aqua-400/20 md:block"
        />
        <div class="site-container">
            <slot name="before" />
            <p
                v-if="eyebrow"
                class="eyebrow text-aqua-400"
                :class="$slots.before && 'mt-12'"
            >
                {{ eyebrow }}
            </p>
            <h1
                class="mt-5 max-w-4xl text-4xl leading-tight font-semibold tracking-tight text-balance sm:text-5xl"
            >
                {{ title }}
            </h1>
            <p
                v-if="intro"
                class="mt-5 max-w-2xl text-base leading-7 text-white/80 sm:text-lg sm:leading-8"
            >
                {{ intro }}
            </p>
            <slot name="after-intro" />
        </div>
    </section>
</template>
