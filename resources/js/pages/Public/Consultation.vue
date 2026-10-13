<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Check } from '@lucide/vue';
import ContactCard from '@/components/public/ContactCard.vue';
import CtaLink from '@/components/public/CtaLink.vue';
import PageHero from '@/components/public/PageHero.vue';
import VisitCard from '@/components/public/VisitCard.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { PackageSummary } from '@/types/publicContent';

withDefaults(
    defineProps<{
        packages?: PackageSummary[];
        selectedPackage?: PackageSummary | null;
        packageUnavailable?: boolean;
    }>(),
    { packages: () => [], selectedPackage: null, packageUnavailable: false },
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
            intro="Call or email us to request a consultation. We will confirm availability directly with you."
        />

        <section class="site-container py-16 sm:py-20">
            <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="grid content-start gap-6">
                    <div
                        v-if="selectedPackage"
                        class="rounded-xl border-2 border-aqua-400 bg-aqua-50 p-7"
                    >
                        <p class="eyebrow text-aqua-700">
                            Your preferred package
                        </p>
                        <h2 class="mt-3 text-2xl font-semibold">
                            {{ selectedPackage.name }}
                        </h2>
                        <p
                            v-if="selectedPackage.summary"
                            class="mt-1 text-sm font-semibold text-aqua-700"
                        >
                            {{ selectedPackage.summary }}
                        </p>
                        <ul
                            v-if="selectedPackage.inclusions.length"
                            class="mt-5 grid gap-2"
                        >
                            <li
                                v-if="selectedPackage.includes"
                                class="flex gap-3 text-sm leading-6 text-muted-foreground"
                            >
                                <Check
                                    :size="18"
                                    class="mt-0.5 shrink-0 text-aqua-600"
                                    aria-hidden="true"
                                />
                                Everything in
                                {{ selectedPackage.includes.name }}
                            </li>
                            <li
                                v-for="item in selectedPackage.inclusions"
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
                        <p class="mt-5 text-sm leading-6 text-muted-foreground">
                            Mention this package when you call or email. We
                            confirm whether it suits your business, and agree
                            the scope and terms with you, before any work
                            begins.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2">
                            <CtaLink
                                href="/services#packages"
                                variant="link"
                                icon="none"
                            >
                                Compare packages
                            </CtaLink>
                            <CtaLink
                                href="/book-consultation"
                                variant="link"
                                icon="none"
                            >
                                Clear selection
                            </CtaLink>
                        </div>
                    </div>

                    <div
                        v-else-if="packageUnavailable"
                        role="status"
                        class="rounded-xl border border-border bg-muted p-7"
                    >
                        <h2 class="text-lg font-semibold">
                            That package can’t be selected right now
                        </h2>
                        <p class="mt-2 text-sm leading-6 text-muted-foreground">
                            You can still ask for a tailored engagement
                            {{
                                packages.length
                                    ? 'or choose one of the packages below'
                                    : ''
                            }}.
                        </p>
                    </div>

                    <ContactCard
                        title="Request your consultation"
                        description="Call either number or send an email. This is a request, not a confirmed appointment; we will reply to agree a time."
                    />

                    <nav
                        v-if="packages.length"
                        class="rounded-xl border border-border bg-card p-7"
                        aria-labelledby="package-choice-title"
                    >
                        <h2
                            id="package-choice-title"
                            class="text-lg font-semibold"
                        >
                            {{
                                selectedPackage
                                    ? 'Prefer a different package?'
                                    : 'Have a package in mind?'
                            }}
                        </h2>
                        <p class="mt-2 text-sm leading-6 text-muted-foreground">
                            Choosing one is optional. We can also shape a
                            tailored engagement with you.
                        </p>
                        <ul class="mt-5 flex flex-wrap gap-2">
                            <li v-for="pkg in packages" :key="pkg.slug">
                                <Link
                                    :href="`/book-consultation?package=${encodeURIComponent(pkg.slug)}`"
                                    preserve-scroll
                                    class="inline-flex min-h-10 items-center rounded-full border px-4 text-sm font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aqua-600"
                                    :class="
                                        selectedPackage?.slug === pkg.slug
                                            ? 'border-navy-900 bg-navy-900 text-white'
                                            : 'border-border bg-white text-navy-900 hover:border-aqua-400 hover:bg-aqua-50'
                                    "
                                    :aria-current="
                                        selectedPackage?.slug === pkg.slug
                                            ? 'true'
                                            : undefined
                                    "
                                >
                                    {{ pkg.name }}
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </div>

                <VisitCard class="self-start" />
            </div>
        </section>
    </PublicLayout>
</template>
