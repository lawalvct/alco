<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { BadgeCheck, Layers, MapPin } from '@lucide/vue';
import { computed } from 'vue';
import BrandPattern from '@/components/public/BrandPattern.vue';
import CtaLink from '@/components/public/CtaLink.vue';
import CtaPanel from '@/components/public/CtaPanel.vue';
import PageSections from '@/components/public/PageSections.vue';
import { useSite } from '@/composables/useSite';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type {
    AboutContent,
    PackageSummary,
    ServiceSummary,
    TeamProfile,
} from '@/types/publicContent';

const props = withDefaults(
    defineProps<{
        about?: AboutContent | null;
        services?: ServiceSummary[];
        team?: TeamProfile[];
        packages?: PackageSummary[];
    }>(),
    { about: null, services: () => [], team: () => [], packages: () => [] },
);

/** Shown until an About page is published in Website → Pages. */
const defaultAbout: AboutContent = {
    title: 'About',
    hero_eyebrow: 'About ALCO',
    hero_line_one: 'Accounting support with a clear purpose.',
    hero_description:
        'ALCO Associate Solutions is an Accounting and Auditing Consultancy based in Ebute-Metta, Lagos.',
    seo_description:
        'About ALCO Associate Solutions, an Accounting and Auditing Consultancy in Ebute-Metta, Lagos, and the people behind it.',
    blocks: [
        {
            type: 'services',
            data: { eyebrow: 'What we do', title: 'Our areas of focus.' },
        },
        {
            type: 'team',
            data: { eyebrow: 'Leadership', title: 'The people behind ALCO.' },
        },
    ],
};

const site = useSite();
const content = computed(() => props.about ?? defaultAbout);
const hasInsertedCta = computed(() => content.value.blocks?.some((block) => block.type === 'cta') ?? false);

/** Verified business details from Site settings and Services, never marketing claims. */
const facts = computed(() =>
    [
        {
            icon: BadgeCheck,
            label: 'Registered as',
            value: site.value.category,
        },
        {
            icon: MapPin,
            label: 'Based in',
            value: site.value.address.summary,
        },
        {
            icon: Layers,
            label: 'Focus areas',
            value: props.services.map((service) => service.title).join(' · '),
        },
    ].filter((fact) => fact.value),
);
</script>

<template>
    <Head :title="content.title || 'About'">
        <meta
            name="description"
            :content="content.seo_description || content.hero_description"
        />
    </Head>

    <PublicLayout>
        <section
            class="relative isolate overflow-hidden bg-navy-900 text-white"
            aria-labelledby="about-title"
        >
            <BrandPattern
                class="absolute -right-24 -bottom-28 -z-10 hidden h-105 w-85 text-aqua-400/20 md:block"
            />
            <BrandPattern
                class="absolute top-16 -left-36 -z-10 hidden h-105 w-85 text-aqua-400/10 xl:block"
            />

            <div
                class="site-container grid gap-12 py-16 sm:py-20 lg:grid-cols-[1.2fr_0.8fr] lg:items-center lg:gap-16 lg:py-24"
            >
                <div>
                    <p class="eyebrow text-aqua-400">
                        {{ content.hero_eyebrow }}
                    </p>
                    <h1
                        id="about-title"
                        class="mt-5 max-w-3xl text-4xl leading-[1.08] font-semibold tracking-[-0.035em] text-balance sm:text-5xl lg:text-6xl"
                    >
                        {{ content.hero_line_one }}
                    </h1>
                    <p
                        class="mt-6 max-w-2xl text-base leading-7 text-white/80 sm:text-lg sm:leading-8"
                    >
                        {{ content.hero_description }}
                    </p>
                    <div
                        class="mt-9 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center"
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
                            Explore our services
                        </CtaLink>
                    </div>
                </div>

                <dl v-if="facts.length" class="grid gap-3">
                    <div
                        v-for="fact in facts"
                        :key="fact.label"
                        class="flex gap-4 rounded-xl border border-white/15 bg-white/5 p-5"
                    >
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-aqua-400/15 text-aqua-400"
                        >
                            <component
                                :is="fact.icon"
                                :size="22"
                                aria-hidden="true"
                            />
                        </span>
                        <div>
                            <dt
                                class="text-xs font-semibold tracking-[0.14em] text-white/60 uppercase"
                            >
                                {{ fact.label }}
                            </dt>
                            <dd class="mt-1 leading-6 font-semibold">
                                {{ fact.value }}
                            </dd>
                        </div>
                    </div>
                </dl>
            </div>
        </section>

        <PageSections
            :blocks="content.blocks"
            :services="services"
            :team="team"
            :packages="packages"
        />

        <CtaPanel
            v-if="!hasInsertedCta"
            id="about-cta"
            :title="content.cta_title || 'Let\u2019s talk about your business.'"
            :description="content.cta_description || 'Tell us what you need help with and we will suggest a sensible starting point.'"
        >
            <CtaLink href="/book-consultation" variant="accent">
                Book a Consultation
            </CtaLink>
            <CtaLink href="/contact" variant="inverse" icon="none">
                Contact us
            </CtaLink>
        </CtaPanel>
    </PublicLayout>
</template>
