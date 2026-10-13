<script setup lang="ts">
import { Mail, MessageCircle, Phone } from '@lucide/vue';
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

const whatsappMessage = encodeURIComponent(
    'Hello ALCO, I would like to ask about your services.',
);
</script>

<template>
    <div class="rounded-xl border border-border bg-card p-8 shadow-card">
        <h2 class="text-2xl font-semibold">{{ title }}</h2>
        <p class="mt-3 text-base leading-7 text-muted-foreground">
            {{ description }}
        </p>
        <div class="mt-7 grid gap-4">
            <a
                v-for="email in site.emails"
                :key="email"
                :href="`mailto:${email}`"
                class="inline-flex w-fit items-center gap-3 text-lg font-semibold break-all text-aqua-700 hover:underline"
                ><Mail :size="19" class="shrink-0" aria-hidden="true" />{{
                    email
                }}</a
            >
            <div
                v-for="phone in site.phones"
                :key="phone.tel"
                class="flex flex-wrap items-center gap-x-4 gap-y-2"
            >
                <a
                    :href="`tel:${phone.tel}`"
                    class="inline-flex w-fit items-center gap-3 text-lg font-semibold text-aqua-700 hover:underline"
                    ><Phone :size="19" class="shrink-0" aria-hidden="true" />{{
                        phone.display
                    }}</a
                >
                <a
                    v-if="phone.whatsapp"
                    :href="`${phone.whatsapp}?text=${whatsappMessage}`"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex min-h-9 items-center gap-2 rounded-full border border-aqua-600 px-3.5 text-sm font-semibold text-aqua-700 transition-colors hover:bg-aqua-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aqua-600"
                    ><MessageCircle
                        :size="16"
                        class="shrink-0"
                        aria-hidden="true"
                    />Chat on WhatsApp<span class="sr-only">
                        with {{ phone.display }} (opens in a new tab)</span
                    ></a
                >
            </div>
        </div>
    </div>
</template>
