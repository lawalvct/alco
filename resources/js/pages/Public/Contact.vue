<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ContactController from '@/actions/App/Http/Controllers/Public/ContactController';
import ContactCard from '@/components/public/ContactCard.vue';
import EnquiryForm from '@/components/public/EnquiryForm.vue';
import EnquirySuccess from '@/components/public/EnquirySuccess.vue';
import PageHero from '@/components/public/PageHero.vue';
import VisitCard from '@/components/public/VisitCard.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { ServiceOption } from '@/types/publicContent';

withDefaults(
    defineProps<{
        services?: ServiceOption[];
        formsEnabled?: boolean;
        turnstileSiteKey?: string | null;
        consentText: string;
    }>(),
    { services: () => [], formsEnabled: true, turnstileSiteKey: null },
);

const page = usePage();
const submitted = computed(() => page.flash.leadSubmitted);
</script>

<template>
    <Head title="Contact">
        <meta
            name="description"
            content="Call, email or send a message to ALCO Associate Solutions about bookkeeping, reporting, tax filings or a business question."
        />
    </Head>

    <PublicLayout>
        <PageHero
            eyebrow="Contact"
            title="Start a conversation with ALCO."
            intro="Call or email us about bookkeeping, reporting, tax filings or a business question, or send a message below."
        />

        <section class="bg-muted py-16 sm:py-20">
            <div
                class="site-container grid gap-8 lg:grid-cols-[1.3fr_0.7fr] lg:items-start"
            >
                <EnquirySuccess
                    v-if="submitted"
                    title="Thank you, your message has been sent."
                    :reference="submitted.reference"
                    again-href="/contact"
                    again-label="Send another message"
                >
                    We will reply using the details you gave us.
                </EnquirySuccess>

                <div
                    v-else
                    class="rounded-xl border border-border bg-card p-6 shadow-card sm:p-8"
                >
                    <h2 class="text-2xl font-semibold">Send us a message</h2>
                    <template v-if="formsEnabled">
                        <p
                            class="mt-2 mb-8 text-base leading-7 text-muted-foreground"
                        >
                            Tell us briefly what you need and how to reach you.
                        </p>
                        <EnquiryForm
                            :submit="ContactController.store.form()"
                            variant="contact"
                            :services="services"
                            :consent-text="consentText"
                            :turnstile-site-key="turnstileSiteKey"
                        />
                    </template>
                    <p
                        v-else
                        class="mt-3 text-base leading-7 text-muted-foreground"
                    >
                        Our online form is paused at the moment. Please call or
                        email us using the details on this page.
                    </p>
                </div>

                <div class="grid gap-6">
                    <ContactCard
                        title="Prefer to call or email?"
                        description="Call or email us using any of these details."
                    />
                    <VisitCard />
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
