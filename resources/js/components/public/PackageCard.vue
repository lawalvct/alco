<script setup lang="ts">
import { Check } from '@lucide/vue';
import CtaLink from '@/components/public/CtaLink.vue';
import type { PackageSummary } from '@/types/publicContent';

withDefaults(
    defineProps<{
        pkg: PackageSummary;
        position?: number;
        headingLevel?: 'h2' | 'h3';
        showInclusions?: boolean;
    }>(),
    { headingLevel: 'h3', showInclusions: true },
);
</script>

<template>
    <article
        class="flex h-full flex-col rounded-xl border border-border bg-card p-7 shadow-card"
    >
        <p v-if="position" class="text-sm font-bold text-aqua-700">
            {{ String(position).padStart(2, '0') }}
        </p>
        <component
            :is="headingLevel"
            class="text-2xl font-semibold"
            :class="position && 'mt-4'"
        >
            {{ pkg.name }}
        </component>
        <p v-if="pkg.summary" class="mt-1 text-sm font-semibold text-aqua-700">
            {{ pkg.summary }}
        </p>
        <p class="mt-5 text-sm leading-6 text-muted-foreground">
            <span class="font-semibold text-foreground">Best for:</span>
            {{ pkg.audience }}
        </p>

        <div
            v-if="showInclusions"
            class="mt-6 flex-1 border-t border-border pt-6"
        >
            <p class="text-sm font-semibold">
                {{
                    pkg.includes
                        ? `Everything in ${pkg.includes.name}, plus:`
                        : 'What’s included:'
                }}
            </p>
            <ul class="mt-4 grid gap-3">
                <li
                    v-for="item in pkg.inclusions"
                    :key="item"
                    class="flex gap-3 text-sm leading-6 text-muted-foreground"
                >
                    <Check
                        :size="18"
                        class="mt-0.5 shrink-0 text-aqua-600"
                        aria-hidden="true"
                    />
                    {{ item }}
                </li>
            </ul>
        </div>
        <div v-else class="flex-1" />

        <div class="mt-8">
            <CtaLink
                v-if="pkg.selectable"
                :href="`/book-consultation?package=${encodeURIComponent(pkg.slug)}`"
                class="w-full"
            >
                Choose this package<span class="sr-only">: {{ pkg.name }}</span>
            </CtaLink>
            <p v-else class="text-sm leading-6 text-muted-foreground">
                Not open for new selections right now. Ask us about a tailored
                engagement.
            </p>
        </div>
    </article>
</template>
