<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import type { Component } from 'vue';

defineProps<{
    title: string;
    description: string;
    href: string;
    icon: Component;
}>();
</script>

<template>
    <Link
        :href="href"
        class="group flex min-h-62 flex-col rounded-xl border border-border bg-muted p-6 transition-colors hover:border-aqua-400 hover:bg-aqua-50 focus-visible:outline-2 focus-visible:outline-aqua-600"
    >
        <component
            :is="icon"
            :size="30"
            :stroke-width="1.7"
            class="text-aqua-600"
            aria-hidden="true"
        />
        <h3 class="mt-8 text-xl font-semibold">{{ title }}</h3>
        <p class="mt-3 flex-1 text-sm leading-6 text-muted-foreground">
            {{ description }}
        </p>
        <span
            class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-aqua-700"
        >
            Learn more
            <ArrowUpRight
                :size="16"
                class="transition-transform motion-safe:group-hover:translate-x-0.5 motion-safe:group-hover:-translate-y-0.5"
                aria-hidden="true"
            />
        </span>
    </Link>
</template>
