<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Check } from '@lucide/vue';
import { computed, ref } from 'vue';
import ConsultationController from '@/actions/App/Http/Controllers/Public/ConsultationController';
import ContactCard from '@/components/public/ContactCard.vue';
import CtaLink from '@/components/public/CtaLink.vue';
import EnquiryForm from '@/components/public/EnquiryForm.vue';
import EnquirySuccess from '@/components/public/EnquirySuccess.vue';
import PageHero from '@/components/public/PageHero.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { PackageSummary, ServiceOption } from '@/types/publicContent';

const props = withDefaults(
    defineProps<{
        packages?: PackageSummary[];
        selectedPackage?: PackageSummary | null;
        packageUnavailable?: boolean;
        services?: ServiceOption[];
        contactMethods?: Record<string, string>;
        formsEnabled?: boolean;
        turnstileSiteKey?: string | null;
        consentText: string;
    }>(),
    {
        packages: () => [],
        selectedPackage: null,
        packageUnavailable: false,
        services: () => [],
        contactMethods: () => ({}),
        formsEnabled: true,
        turnstileSiteKey: null,
    },
);

const page = usePage();
const submitted = computed(() => page.flash.leadSubmitted);
const chosenPackage = ref<string | null>(props.selectedPackage?.slug ?? null);
const chosen = computed(
    () =>
        props.packages.find((pkg) => pkg.slug === chosenPackage.value) ?? null,
);
</script>

<template>
    <Head title="Book a consultation">
        <meta
            name="description"
            content="Request a consultation with ALCO Associate Solutions. Choose a starting package or ask for a tailored engagement."
        />
        <meta name="robots" content="noindex,follow" />
    </Head>

    <PublicLayout>
        <PageHero
            eyebrow="Book a consultation"
            title="Let’s talk through what your business needs."
            intro="Send a request below, or call or email us. We will confirm availability directly with you."
        />

        <section class="bg-muted py-16 sm:py-20">
            <div
                class="site-container grid gap-8 lg:grid-cols-[1.3fr_0.7fr] lg:items-start"
            >
                <EnquirySuccess
                    v-if="submitted"
                    title="Thank you, your request has been sent."
                    :reference="submitted.reference"
                    again-href="/book-consultation"
                    again-label="Send another request"
                >
                    This is a request, not a confirmed appointment. We will
                    contact you to agree a time and to confirm which support
                    suits your business.
                </EnquirySuccess>

                <div
                    v-else
                    class="rounded-xl border border-border bg-card p-6 shadow-card sm:p-8"
                >
                    <h2 class="text-2xl font-semibold">
                        Request a consultation
                    </h2>

                    <div
                        v-if="packageUnavailable && formsEnabled"
                        role="status"
                        class="mt-5 rounded-lg border border-border bg-muted p-4 text-sm leading-6 text-muted-foreground"
                    >
                        The package in your link can’t be selected right now.
                        Choose another option below, or ask for a tailored
                        engagement.
                    </div>

                    <template v-if="formsEnabled">
                        <p
                            class="mt-2 mb-8 text-base leading-7 text-muted-foreground"
                        >
                            This is a request, not a confirmed appointment. We
                            will contact you to agree a time.
                        </p>
                        <EnquiryForm
                            v-model:package="chosenPackage"
                            :submit="ConsultationController.store.form()"
                            variant="consultation"
                            :services="services"
                            :packages="packages"
                            :contact-methods="contactMethods"
                            :consent-text="consentText"
                            :turnstile-site-key="turnstileSiteKey"
                            submit-label="Send request"
                        />
                    </template>
                    <p
                        v-else
                        class="mt-3 text-base leading-7 text-muted-foreground"
                    >
                        Our online form is paused at the moment. Please call or
                        email us to request a consultation.
                    </p>
                </div>

                <div class="grid gap-6 lg:sticky lg:top-8">
                    <div
                        v-if="chosen && !submitted"
                        class="rounded-xl border-2 border-aqua-400 bg-aqua-50 p-7"
                    >
                        <p class="eyebrow text-aqua-700">Your chosen package</p>
                        <h2 class="mt-3 text-2xl font-semibold">
                            {{ chosen.name }}
                        </h2>
                        <p
                            v-if="chosen.summary"
                            class="mt-1 text-sm font-semibold text-aqua-700"
                        >
                            {{ chosen.summary }}
                        </p>
                        <ul class="mt-5 grid gap-2">
                            <li
                                v-if="chosen.includes"
                                class="flex gap-3 text-sm leading-6 text-muted-foreground"
                            >
                                <Check
                                    :size="18"
                                    class="mt-0.5 shrink-0 text-aqua-600"
                                    aria-hidden="true"
                                />
                                Everything in {{ chosen.includes.name }}
                            </li>
                            <li
                                v-for="item in chosen.inclusions"
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
                        <CtaLink
                            href="/services#packages"
                            variant="link"
                            icon="arrow-right"
                            class="mt-5"
                        >
                            Compare packages
                        </CtaLink>
                    </div>
                    <div
                        v-else-if="chosenPackage === 'tailored' && !submitted"
                        class="rounded-xl border-2 border-aqua-400 bg-aqua-50 p-7"
                    >
                        <p class="eyebrow text-aqua-700">Tailored engagement</p>
                        <p class="mt-3 text-sm leading-6 text-muted-foreground">
                            Tell us about your business in the form and we will
                            suggest the support that fits.
                        </p>
                    </div>

                    <ContactCard
                        title="Prefer to call or email?"
                        description="Use either number or our email address to request a consultation."
                    />
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
