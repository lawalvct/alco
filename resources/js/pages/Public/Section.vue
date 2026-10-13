<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import CtaLink from '@/components/public/CtaLink.vue';
import PackageCard from '@/components/public/PackageCard.vue';
import PageHero from '@/components/public/PageHero.vue';
import SectionHeading from '@/components/public/SectionHeading.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { plannedTools, services as fallbackServices } from '@/lib/publicSite';
import type {
    PackageSummary,
    ServiceSummary,
    TeamProfile,
} from '@/types/publicContent';

type SectionKind = 'about' | 'services' | 'insights' | 'tools';

const props = withDefaults(
    defineProps<{
        kind: SectionKind;
        publishedServices?: ServiceSummary[];
        packages?: PackageSummary[];
        team?: TeamProfile[];
    }>(),
    { publishedServices: () => [], packages: () => [], team: () => [] },
);

const serviceCards = computed(() =>
    props.publishedServices.length
        ? props.publishedServices.map((service) => ({
              slug: service.slug,
              title: service.title,
              description: service.summary,
              href: `/services/${service.slug}`,
          }))
        : fallbackServices.map((service) => ({
              ...service,
              href: '/book-consultation',
          })),
);

const pageContent: Record<
    SectionKind,
    { eyebrow: string; title: string; intro: string }
> = {
    about: {
        eyebrow: 'About ALCO',
        title: 'Accounting support with a clear purpose.',
        intro: 'ALCO Associate Solutions is an Accounting and Auditing Consultancy based in Ebute-Metta, Lagos.',
    },
    services: {
        eyebrow: 'Our services',
        title: 'The right support for your numbers and your next decision.',
        intro: 'Explore our core areas of accounting and business support. Contact us to discuss the scope that fits your needs.',
    },
    insights: {
        eyebrow: 'Insights',
        title: 'Useful thinking for everyday business questions.',
        intro: 'Our articles and VAT content hub are being prepared and reviewed before publication.',
    },
    tools: {
        eyebrow: 'Accounting tools',
        title: 'Practical calculators are on the way.',
        intro: 'We are preparing accounting tools with reviewed formulas and configurable tax settings.',
    },
};

const content = computed(() => pageContent[props.kind]);
</script>

<template>
    <Head :title="content.title">
        <meta name="description" :content="content.intro" />
        <meta name="robots" content="noindex,follow" />
    </Head>

    <PublicLayout>
        <PageHero
            :eyebrow="content.eyebrow"
            :title="content.title"
            :intro="content.intro"
        />

        <template v-if="kind === 'services'">
            <section class="site-container py-16 sm:py-20">
                <div class="grid gap-5 md:grid-cols-2">
                    <article
                        v-for="(service, index) in serviceCards"
                        :id="service.slug"
                        :key="service.slug"
                        class="scroll-mt-7 rounded-xl border border-border bg-card p-7 shadow-card"
                    >
                        <p class="text-sm font-bold text-aqua-700">
                            0{{ index + 1 }}
                        </p>
                        <h2 class="mt-5 text-2xl font-semibold">
                            {{ service.title }}
                        </h2>
                        <p
                            class="mt-3 max-w-md text-base leading-7 text-muted-foreground"
                        >
                            {{ service.description }}
                        </p>
                        <CtaLink
                            :href="service.href"
                            variant="link"
                            class="mt-7"
                        >
                            Explore this service
                        </CtaLink>
                    </article>
                </div>
            </section>

            <section
                v-if="packages.length"
                id="packages"
                class="scroll-mt-7 border-t border-border bg-muted py-16 sm:py-20"
                aria-labelledby="packages-title"
            >
                <div class="site-container">
                    <SectionHeading
                        id="packages-title"
                        class="max-w-3xl"
                        eyebrow="Engagement packages"
                        title="Choose a starting point for ongoing support."
                        description="Each package builds on the one before it. Choosing a package adds it to your consultation request; we confirm suitability, scope and terms with you before any work begins."
                    />
                    <ol class="mt-10 grid gap-5 lg:grid-cols-3">
                        <li v-for="(pkg, index) in packages" :key="pkg.slug">
                            <PackageCard :pkg="pkg" :position="index + 1" />
                        </li>
                    </ol>
                    <p class="mt-8 text-sm leading-6 text-muted-foreground">
                        Need something different?
                        <CtaLink
                            href="/book-consultation"
                            variant="link"
                            icon="none"
                            >Request a tailored engagement</CtaLink
                        >.
                    </p>
                </div>
            </section>
        </template>

        <section v-else class="site-container py-16 sm:py-20">
            <template v-if="kind === 'about'">
                <div class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr]">
                    <div>
                        <h2 class="text-2xl font-semibold">
                            Professional Accounting Services.
                        </h2>
                        <p
                            class="mt-4 max-w-2xl text-base leading-7 text-muted-foreground"
                        >
                            Bookkeeping, financial reporting, tax filings and
                            business solutions are the focus of our work.
                        </p>
                        <CtaLink
                            href="/services"
                            variant="link"
                            icon="arrow-right"
                            class="mt-7"
                        >
                            Explore our services
                        </CtaLink>
                    </div>
                    <div v-if="team.length" class="rounded-xl bg-aqua-50 p-7">
                        <h2 class="text-xl font-semibold">Leadership</h2>
                        <ul class="mt-5 grid gap-6">
                            <li
                                v-for="member in team"
                                :key="member.id"
                                class="flex gap-4"
                            >
                                <img
                                    v-if="member.portrait"
                                    :src="member.portrait.src"
                                    :srcset="
                                        member.portrait.srcset ?? undefined
                                    "
                                    sizes="64px"
                                    alt=""
                                    :width="member.portrait.width ?? undefined"
                                    :height="
                                        member.portrait.height ?? undefined
                                    "
                                    loading="lazy"
                                    class="size-16 shrink-0 rounded-full object-cover"
                                />
                                <div>
                                    <p class="text-base font-semibold">
                                        {{ member.name }}
                                    </p>
                                    <p
                                        v-if="member.role"
                                        class="text-sm font-medium text-aqua-700"
                                    >
                                        {{ member.role }}
                                    </p>
                                    <p
                                        v-if="member.bio"
                                        class="mt-2 text-sm leading-6 whitespace-pre-line text-muted-foreground"
                                    >
                                        {{ member.bio }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </template>

            <template v-else-if="kind === 'tools'">
                <h2 class="text-2xl font-semibold">Planned tools</h2>
                <p
                    class="mt-3 max-w-2xl text-base leading-7 text-muted-foreground"
                >
                    These tools will become available after their settings and
                    calculations have been reviewed.
                </p>
                <ul class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <li
                        v-for="tool in plannedTools"
                        :key="tool"
                        class="rounded-lg border border-border bg-card px-5 py-4 text-sm font-medium"
                    >
                        {{ tool }}
                    </li>
                </ul>
            </template>

            <template v-else>
                <div
                    class="max-w-3xl rounded-xl border border-border bg-card p-8 shadow-card"
                >
                    <p class="eyebrow text-aqua-700">
                        Editorial review in progress
                    </p>
                    <h2 class="mt-4 text-2xl font-semibold">
                        Guides grounded in useful, reviewed information.
                    </h2>
                    <p class="mt-4 text-base leading-7 text-muted-foreground">
                        The first articles will cover accounting and business
                        topics, including a VAT guide linked to its calculator.
                        We will publish them after professional review.
                    </p>
                </div>
            </template>
        </section>
    </PublicLayout>
</template>
