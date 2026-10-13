<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CircleCheck } from '@lucide/vue';
import { onMounted, ref } from 'vue';

defineProps<{
    title: string;
    reference: string | null;
    againHref: string;
    againLabel: string;
}>();

const panel = ref<HTMLElement | null>(null);

// Move focus here so screen-reader and keyboard users hear the outcome.
onMounted(() => panel.value?.focus());
</script>

<template>
    <div
        ref="panel"
        role="status"
        tabindex="-1"
        class="rounded-xl border-2 border-aqua-400 bg-aqua-50 p-7 focus:outline-none"
    >
        <CircleCheck :size="30" class="text-aqua-700" aria-hidden="true" />
        <h2 class="mt-4 text-2xl font-semibold">{{ title }}</h2>
        <p class="mt-3 leading-7 text-muted-foreground">
            <slot />
        </p>
        <p v-if="reference" class="mt-4 text-sm leading-6">
            Your reference is
            <strong class="font-semibold tracking-wide">{{ reference }}</strong
            >. Please quote it if you call or email us about this.
        </p>
        <Link
            :href="againHref"
            class="mt-6 inline-flex text-sm font-semibold text-aqua-700 underline-offset-4 hover:underline"
        >
            {{ againLabel }}
        </Link>
    </div>
</template>
