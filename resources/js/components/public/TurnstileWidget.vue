<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { loadTurnstile } from '@/lib/turnstile';

const props = defineProps<{ siteKey: string }>();

const container = ref<HTMLElement | null>(null);
const failed = ref(false);
let widgetId: string | undefined;

onMounted(async () => {
    try {
        const turnstile = await loadTurnstile();

        if (container.value) {
            // Turnstile adds a hidden cf-turnstile-response input that the form submits.
            widgetId = turnstile.render(container.value, {
                sitekey: props.siteKey,
                theme: 'light',
            });
        }
    } catch {
        failed.value = true;
    }
});

onBeforeUnmount(() => {
    if (widgetId && window.turnstile) {
        window.turnstile.remove(widgetId);
    }
});
</script>

<template>
    <div>
        <div ref="container" class="min-h-16.5" />
        <p v-if="failed" class="text-sm text-destructive">
            The spam check could not load. Please refresh the page, or call or
            email us instead.
        </p>
    </div>
</template>
