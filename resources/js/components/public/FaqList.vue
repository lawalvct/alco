<script setup lang="ts">
import type { FaqItem } from '@/types/publicContent';

defineProps<{ items: FaqItem[] }>();
</script>

<template>
    <div class="grid gap-3">
        <details
            v-for="item in items"
            :key="item.question"
            class="group rounded-xl bg-white px-6 py-5 shadow-card"
        >
            <summary
                class="cursor-pointer rounded-sm text-base font-semibold marker:text-aqua-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-aqua-600"
            >
                {{ item.question }}
            </summary>
            <p class="mt-4 leading-7 whitespace-pre-line text-muted-foreground">
                {{ item.answer }}
            </p>
        </details>
    </div>
</template>
