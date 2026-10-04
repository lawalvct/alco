<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, ArrowUpRight, MapPin, Phone } from '@lucide/vue';
import { computed } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { plannedTools, services } from '@/lib/publicSite';

type SectionKind =
    | 'about'
    | 'services'
    | 'insights'
    | 'tools'
    | 'contact'
    | 'consultation';

const props = defineProps<{ kind: SectionKind }>();

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
    contact: {
        eyebrow: 'Contact',
        title: 'Start a conversation with ALCO.',
        intro: 'Speak with us about bookkeeping, reporting, tax filings or a business question.',
    },
    consultation: {
        eyebrow: 'Book a consultation',
        title: 'Let’s talk through what your business needs.',
        intro: 'Call either number below to request a consultation. We will confirm availability directly with you.',
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
        <main>
            <section
                class="bg-[#062849] px-5 py-16 text-white sm:px-8 sm:py-20 lg:px-12"
            >
                <div class="mx-auto max-w-[1370px]">
                    <p
                        class="text-xs font-bold tracking-[0.2em] text-[#52d7f0] uppercase"
                    >
                        {{ content.eyebrow }}
                    </p>
                    <h1
                        class="mt-5 max-w-4xl text-4xl leading-tight font-semibold tracking-tight sm:text-5xl"
                    >
                        {{ content.title }}
                    </h1>
                    <p
                        class="mt-5 max-w-2xl text-base leading-7 text-white/80 sm:text-lg"
                    >
                        {{ content.intro }}
                    </p>
                </div>
            </section>

            <section
                class="mx-auto max-w-[1370px] px-5 py-16 sm:px-8 sm:py-20 lg:px-12"
            >
                <template v-if="kind === 'services'">
                    <div class="grid gap-5 md:grid-cols-2">
                        <article
                            v-for="(service, index) in services"
                            :id="service.slug"
                            :key="service.slug"
                            class="scroll-mt-7 rounded-xl border border-[#dbe6ec] bg-white p-7 shadow-sm"
                        >
                            <p class="text-sm font-bold text-[#168bae]">
                                0{{ index + 1 }}
                            </p>
                            <h2 class="mt-5 text-2xl font-semibold">
                                {{ service.title }}
                            </h2>
                            <p
                                class="mt-3 max-w-md text-base leading-7 text-[#4d6273]"
                            >
                                {{ service.description }}
                            </p>
                            <Link
                                href="/book-consultation"
                                class="mt-7 inline-flex items-center gap-2 text-sm font-semibold text-[#087c9e] hover:underline"
                            >
                                Discuss this service
                                <ArrowUpRight :size="17" aria-hidden="true" />
                            </Link>
                        </article>
                    </div>
                </template>

                <template v-else-if="kind === 'about'">
                    <div class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr]">
                        <div>
                            <h2 class="text-2xl font-semibold">
                                Professional Accounting Services.
                            </h2>
                            <p
                                class="mt-4 max-w-2xl text-base leading-7 text-[#4d6273]"
                            >
                                Bookkeeping, financial reporting, tax filings
                                and business solutions are the focus of our
                                work.
                            </p>
                            <Link
                                href="/services"
                                class="mt-7 inline-flex items-center gap-2 text-sm font-semibold text-[#087c9e] hover:underline"
                                >Explore our services
                                <ArrowRight :size="17" aria-hidden="true"
                            /></Link>
                        </div>
                        <div class="rounded-xl bg-[#eaf6fb] p-7">
                            <h2 class="text-xl font-semibold">Leadership</h2>
                            <ul class="mt-5 grid gap-4 text-base">
                                <li>Abdulsalam Latifat Damilola</li>
                                <li>Abdulazeez Azeez Olaitan</li>
                            </ul>
                        </div>
                    </div>
                </template>

                <template v-else-if="kind === 'tools'">
                    <h2 class="text-2xl font-semibold">Planned tools</h2>
                    <p
                        class="mt-3 max-w-2xl text-base leading-7 text-[#4d6273]"
                    >
                        These tools will become available after their settings
                        and calculations have been reviewed.
                    </p>
                    <ul class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <li
                            v-for="tool in plannedTools"
                            :key="tool"
                            class="rounded-lg border border-[#dbe6ec] bg-white px-5 py-4 text-sm font-medium"
                        >
                            {{ tool }}
                        </li>
                    </ul>
                </template>

                <template v-else-if="kind === 'insights'">
                    <div
                        class="max-w-3xl rounded-xl border border-[#dbe6ec] bg-white p-8 shadow-sm"
                    >
                        <p
                            class="text-xs font-bold tracking-[0.18em] text-[#168bae] uppercase"
                        >
                            Editorial review in progress
                        </p>
                        <h2 class="mt-4 text-2xl font-semibold">
                            Guides grounded in useful, reviewed information.
                        </h2>
                        <p class="mt-4 text-base leading-7 text-[#4d6273]">
                            The first articles will cover accounting and
                            business topics, including a VAT guide linked to its
                            calculator. We will publish them after professional
                            review.
                        </p>
                    </div>
                </template>

                <template v-else>
                    <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                        <div
                            class="rounded-xl border border-[#dbe6ec] bg-white p-8 shadow-sm"
                        >
                            <h2 class="text-2xl font-semibold">Call ALCO</h2>
                            <p class="mt-3 text-base leading-7 text-[#4d6273]">
                                Choose either number to speak with us or arrange
                                a consultation.
                            </p>
                            <div class="mt-7 grid gap-4">
                                <a
                                    href="tel:+2348140824230"
                                    class="inline-flex items-center gap-3 text-lg font-semibold text-[#087c9e] hover:underline"
                                    ><Phone :size="19" aria-hidden="true" />+234
                                    814 082 4230</a
                                >
                                <a
                                    href="tel:+2348148484955"
                                    class="inline-flex items-center gap-3 text-lg font-semibold text-[#087c9e] hover:underline"
                                    ><Phone :size="19" aria-hidden="true" />+234
                                    814 848 4955</a
                                >
                            </div>
                        </div>
                        <div class="rounded-xl bg-[#eaf6fb] p-8">
                            <MapPin
                                :size="26"
                                class="text-[#168bae]"
                                aria-hidden="true"
                            />
                            <h2 class="mt-5 text-xl font-semibold">Visit us</h2>
                            <address
                                class="mt-3 text-base leading-7 text-[#4d6273] not-italic"
                            >
                                18, Lagos Street,<br />Ebute-Metta, Lagos State,
                                Nigeria
                            </address>
                        </div>
                    </div>
                </template>
            </section>
        </main>
    </PublicLayout>
</template>
