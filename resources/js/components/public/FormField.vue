<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    id: string;
    label: string;
    error?: string;
    hint?: string;
    optional?: boolean;
}>();

const describedBy = computed(
    () =>
        [props.hint && `${props.id}-hint`, props.error && `${props.id}-error`]
            .filter(Boolean)
            .join(' ') || undefined,
);
</script>

<template>
    <div class="grid content-start gap-1.5">
        <label :for="id" class="text-sm font-semibold">
            {{ label }}
            <span v-if="optional" class="font-normal text-muted-foreground"
                >(optional)</span
            >
        </label>
        <p v-if="hint" :id="`${id}-hint`" class="text-sm text-muted-foreground">
            {{ hint }}
        </p>
        <slot :described-by="describedBy" :invalid="Boolean(error)" />
        <p
            v-if="error"
            :id="`${id}-error`"
            class="text-sm font-medium text-destructive"
        >
            {{ error }}
        </p>
    </div>
</template>
