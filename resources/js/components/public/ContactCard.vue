<script setup lang="ts">
import { Mail, Phone } from '@lucide/vue';
import { useSite } from '@/composables/useSite';

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
    }>(),
    {
        title: 'Contact ALCO',
        description:
            'Call either number or send an email to start a conversation or request a consultation.',
    },
);

const site = useSite();
</script>

<template>
    <div class="rounded-xl border border-border bg-card p-8 shadow-card">
        <h2 class="text-2xl font-semibold">{{ title }}</h2>
        <p class="mt-3 text-base leading-7 text-muted-foreground">
            {{ description }}
        </p>
        <div class="mt-7 grid gap-4">
            <a
                v-if="site.email"
                :href="`mailto:${site.email}`"
                class="inline-flex w-fit items-center gap-3 text-lg font-semibold break-all text-aqua-700 hover:underline"
                ><Mail :size="19" class="shrink-0" aria-hidden="true" />{{
                    site.email
                }}</a
            >
            <a
                v-for="phone in site.phones"
                :key="phone.tel"
                :href="`tel:${phone.tel}`"
                class="inline-flex w-fit items-center gap-3 text-lg font-semibold text-aqua-700 hover:underline"
                ><Phone :size="19" class="shrink-0" aria-hidden="true" />{{
                    phone.display
                }}</a
            >
        </div>
    </div>
</template>
