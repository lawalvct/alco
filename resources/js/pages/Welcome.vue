<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    BookOpen,
    ChartNoAxesCombined,
    FileText,
    UsersRound,
} from '@lucide/vue';
import { computed } from 'vue';
import HomeSections from '@/components/HomeSections.vue';
import BrandPattern from '@/components/public/BrandPattern.vue';
import CtaLink from '@/components/public/CtaLink.vue';
import SectionHeading from '@/components/public/SectionHeading.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { services as fallbackServices } from '@/lib/publicSite';
import type { HomeContent, ServiceSummary } from '@/types/publicContent';

const serviceIcons = [BookOpen, ChartNoAxesCombined, FileText, UsersRound];
const discoverCards = [
    {
        href: '/insights',
        icon: BookOpen,
        title: 'Insights',
        text: 'Practical articles on accounting, reporting and tax topics.',
    },
    {
        href: '/tools',
        icon: ChartNoAxesCombined,
        title: 'Accounting Tools',
        text: 'Calculators to help you test and understand key figures.',
    },
];
const props = defineProps<{
    home?: HomeContent | null;
    publishedServices?: ServiceSummary[];
}>();

const defaultHome: HomeContent = {
    title: 'Home',
    hero_eyebrow: 'ALCO Associate Solutions',
    hero_line_one: 'Your numbers, in order.',
    hero_line_two: 'Your business,',
    hero_accent: 'moving forward.',
    hero_description:
        'Professional accounting, clear reporting and practical business support.',
    seo_description:
        'ALCO Associate Solutions offers bookkeeping, financial reporting, tax filings and business solutions in Lagos, Nigeria.',
    blocks: [],
};

const homeContent = computed(() => props.home ?? defaultHome);
const serviceCards = computed(() =>
    props.publishedServices?.length
        ? props.publishedServices.map((service) => ({
              slug: service.slug,
              title: service.title,
              description: service.summary,
              href: `/services/${service.slug}`,
          }))
        : fallbackServices.map((service) => ({
              ...service,
              href: `/services#${service.slug}`,
          })),
);
</script>

<template>
    <Head title="Home">
        <meta
            name="description"
            :content="
                homeContent.seo_description ?? defaultHome.seo_description
            "
        />
    </Head>

    <PublicLayout>
        <section
            class="relative isolate overflow-hidden bg-navy-900 text-white"
            aria-labelledby="hero-title"
        >
            <BrandPattern
                class="absolute top-24 -left-32 -z-10 hidden h-107.5 w-87.5 text-aqua-400/30 lg:block"
            />
            <BrandPattern
                class="absolute top-10 -right-32 -z-10 hidden h-107.5 w-87.5 rotate-180 text-aqua-400/30 lg:block"
            />

            <div class="site-container pt-14 pb-17 sm:pt-18 lg:pt-16 lg:pb-22">
                <div class="mx-auto max-w-312.5 text-center">
                    <p
                        class="eyebrow tracking-[0.23em] text-aqua-400 sm:text-sm"
                    >
                        {{ homeContent.hero_eyebrow }}
                    </p>
                    <h1
                        id="hero-title"
                        class="mx-auto mt-6 text-[clamp(2.6rem,5.3vw,5rem)] leading-[1.09] font-semibold tracking-[-0.04em]"
                    >
                        {{ homeContent.hero_line_one }}
                        <span class="block">
                            {{ homeContent.hero_line_two }}
                            <span class="text-aqua-400">{{
                                homeContent.hero_accent
                            }}</span>
                        </span>
                    </h1>
                    <p
                        class="mx-auto mt-6 max-w-195 text-base leading-7 text-white/80 sm:text-lg"
                    >
                        {{ homeContent.hero_description }}
                    </p>
                    <div
                        class="mt-8 flex flex-col items-stretch justify-center gap-3 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <CtaLink
                            href="/book-consultation"
                            variant="accent"
                            size="lg"
                        >
                            Book a Consultation
                        </CtaLink>
                        <CtaLink
                            href="/services"
                            variant="inverse"
                            size="lg"
                            icon="none"
                        >
                            Explore Our Services
                        </CtaLink>
                    </div>
                </div>

                <figure class="mt-11 sm:mt-14">
                    <figcaption
                        class="mb-3 text-xs font-semibold tracking-widest text-white/85"
                    >
                        Clarity today. Confidence for what comes next.
                    </figcaption>
                    <img
                        src="/images/hero-consultation.webp"
                        alt="Three professionals reviewing financial documents together at a conference table"
                        width="1983"
                        height="793"
                        fetchpriority="high"
                        class="h-61.25 w-full rounded-xl object-cover object-center sm:h-87.5 lg:h-107.5"
                    />
                </figure>

                <nav
                    class="relative z-10 mx-auto -mt-9 grid grid-cols-2 rounded-2xl bg-white px-3 py-2 text-navy-900 shadow-float md:grid-cols-4 md:px-5 md:py-3"
                    aria-label="Explore ALCO services"
                >
                    <Link
                        v-for="(service, index) in serviceCards"
                        :key="service.slug"
                        :href="service.href"
                        class="group flex min-h-21 items-center gap-3 rounded-lg px-3 py-3 transition-colors hover:bg-aqua-50 focus-visible:outline-2 focus-visible:outline-aqua-600 sm:gap-4 sm:px-5 md:min-h-19"
                    >
                        <component
                            :is="serviceIcons[index % serviceIcons.length]"
                            :size="27"
                            :stroke-width="1.8"
                            class="shrink-0"
                            aria-hidden="true"
                        />
                        <span
                            class="flex-1 text-xs leading-tight font-semibold sm:text-sm"
                            >{{ service.title }}</span
                        >
                        <ArrowUpRight
                            :size="17"
                            class="shrink-0 text-aqua-600 transition-transform motion-safe:group-hover:translate-x-0.5 motion-safe:group-hover:-translate-y-0.5"
                            aria-hidden="true"
                        />
                    </Link>
                </nav>
            </div>
        </section>

        <section
            class="bg-white py-20 sm:py-24"
            aria-labelledby="services-title"
        >
            <div class="site-container">
                <div
                    class="flex flex-col justify-between gap-6 md:flex-row md:items-end"
                >
                    <SectionHeading
                        id="services-title"
                        class="max-w-2xl"
                        eyebrow="What we do"
                        title="Good decisions begin with a clear picture."
                    />
                    <CtaLink href="/services" variant="link" icon="arrow-right">
                        View all services
                    </CtaLink>
                </div>
                <div class="mt-10 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <Link
                        v-for="(service, index) in serviceCards"
                        :key="service.slug"
                        :href="service.href"
                        class="group flex min-h-62 flex-col rounded-xl border border-border bg-muted p-6 transition-colors hover:border-aqua-400 hover:bg-aqua-50 focus-visible:outline-2 focus-visible:outline-aqua-600"
                    >
                        <component
                            :is="serviceIcons[index % serviceIcons.length]"
                            :size="30"
                            :stroke-width="1.7"
                            class="text-aqua-600"
                            aria-hidden="true"
                        />
                        <h3 class="mt-8 text-xl font-semibold">
                            {{ service.title }}
                        </h3>
                        <p
                            class="mt-3 flex-1 text-sm leading-6 text-muted-foreground"
                        >
                            {{ service.description }}
                        </p>
                        <span
                            class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-aqua-700"
                        >
                            Learn more
                            <ArrowUpRight :size="16" aria-hidden="true" />
                        </span>
                    </Link>
                </div>
            </div>
        </section>

        <HomeSections
            v-if="homeContent.blocks?.length"
            :blocks="homeContent.blocks"
        />

        <!-- White with a hairline so it stays distinct whichever CMS block precedes it. -->
        <section
            class="border-t border-border bg-white py-20 sm:py-24"
            aria-labelledby="discover-title"
        >
            <div
                class="site-container grid gap-10 lg:grid-cols-[1fr_1.1fr] lg:items-center lg:gap-20"
            >
                <SectionHeading
                    id="discover-title"
                    class="max-w-lg"
                    eyebrow="Explore more"
                    title="Useful guidance for the work ahead."
                    description="Our Insights and Accounting Tools sections are being prepared to make everyday financial questions easier to explore."
                />
                <div class="grid gap-4 sm:grid-cols-2">
                    <Link
                        v-for="card in discoverCards"
                        :key="card.href"
                        :href="card.href"
                        class="group rounded-xl border border-border bg-muted p-7 transition hover:border-aqua-400 hover:bg-aqua-50 focus-visible:outline-2 focus-visible:outline-aqua-600 motion-safe:hover:-translate-y-1"
                    >
                        <component
                            :is="card.icon"
                            :size="30"
                            class="text-aqua-600"
                            aria-hidden="true"
                        />
                        <h3 class="mt-9 text-xl font-semibold">
                            {{ card.title }}
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-muted-foreground">
                            {{ card.text }}
                        </p>
                        <ArrowUpRight
                            :size="20"
                            class="mt-7 text-aqua-600"
                            aria-hidden="true"
                        />
                    </Link>
                </div>
            </div>
        </section>

        <section
            class="bg-white pb-20 sm:pb-24"
            aria-labelledby="contact-title"
        >
            <div class="site-container">
                <div
                    class="relative isolate overflow-hidden rounded-2xl bg-navy-900 px-6 py-14 text-center sm:px-12 sm:py-18"
                >
                    <BrandPattern
                        class="absolute -bottom-24 -left-20 -z-10 hidden h-90 w-72 text-aqua-400/20 md:block"
                    />
                    <BrandPattern
                        class="absolute -top-24 -right-20 -z-10 hidden h-90 w-72 rotate-180 text-aqua-400/20 md:block"
                    />
                    <SectionHeading
                        id="contact-title"
                        class="mx-auto max-w-2xl"
                        tone="dark"
                        eyebrow="Let's talk"
                        title="Ready to bring more clarity to your business?"
                        description="Tell us what you need help with and begin a conversation with ALCO."
                    />
                    <CtaLink
                        href="/book-consultation"
                        variant="accent"
                        class="mt-8"
                    >
                        Book a Consultation
                    </CtaLink>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
