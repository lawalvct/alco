<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    ArrowUpRight,
    BookOpen,
    ChartNoAxesCombined,
    FileText,
    UsersRound,
} from '@lucide/vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { services } from '@/lib/publicSite';

const serviceIcons = [BookOpen, ChartNoAxesCombined, FileText, UsersRound];
</script>

<template>
    <Head title="Home">
        <meta
            name="description"
            content="ALCO Associate Solutions offers bookkeeping, financial reporting, tax filings and business solutions in Lagos, Nigeria."
        />
    </Head>

    <PublicLayout>
        <main>
            <section
                class="relative overflow-hidden bg-[#062849] text-white"
                aria-labelledby="hero-title"
            >
                <svg
                    class="pointer-events-none absolute top-24 -left-28 hidden h-[430px] w-[330px] text-[#42cce8]/35 lg:block"
                    viewBox="0 0 330 430"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M-50 380 123 25 290 380H190L123 235 58 380Z"
                        stroke="currentColor"
                        stroke-width="1.2"
                    />
                    <path
                        d="M-20 430 123 112 270 430"
                        stroke="currentColor"
                        stroke-width="1.2"
                    />
                </svg>
                <svg
                    class="pointer-events-none absolute top-14 -right-24 hidden h-[430px] w-[330px] rotate-180 text-[#42cce8]/35 lg:block"
                    viewBox="0 0 330 430"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M-50 380 123 25 290 380H190L123 235 58 380Z"
                        stroke="currentColor"
                        stroke-width="1.2"
                    />
                    <path
                        d="M-20 430 123 112 270 430"
                        stroke="currentColor"
                        stroke-width="1.2"
                    />
                </svg>

                <div
                    class="relative mx-auto max-w-[1512px] px-5 pt-14 pb-17 sm:px-8 sm:pt-18 lg:px-12 lg:pt-16 lg:pb-22"
                >
                    <div class="mx-auto max-w-[1250px] text-center">
                        <p
                            class="text-xs font-bold tracking-[0.23em] text-[#51d7f0] uppercase sm:text-sm"
                        >
                            ALCO Associate Solutions
                        </p>
                        <h1
                            id="hero-title"
                            class="mx-auto mt-6 text-[clamp(2.6rem,5.3vw,5rem)] leading-[1.09] font-semibold tracking-[-0.046em]"
                        >
                            Your numbers, in order.
                            <span class="block">
                                Your business,
                                <span class="text-[#51d7f0]"
                                    >moving forward.</span
                                >
                            </span>
                        </h1>
                        <p
                            class="mx-auto mt-6 max-w-[780px] text-base leading-7 text-white/80 sm:text-lg"
                        >
                            Professional accounting, clear reporting and
                            practical business support.
                        </p>
                        <div
                            class="mt-8 flex flex-col items-stretch justify-center gap-3 sm:flex-row sm:items-center sm:gap-4"
                        >
                            <Link
                                href="/book-consultation"
                                class="inline-flex min-h-13 items-center justify-center gap-3 rounded-md bg-[#52d7f0] px-7 py-3.5 text-sm font-bold text-[#062849] transition-colors hover:bg-[#8ae8f8] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white"
                            >
                                Book a Consultation
                                <ArrowUpRight :size="18" aria-hidden="true" />
                            </Link>
                            <Link
                                href="/services"
                                class="inline-flex min-h-13 items-center justify-center rounded-md border border-white/85 px-7 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-white hover:text-[#062849] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#52d7f0]"
                            >
                                Explore Our Services
                            </Link>
                        </div>
                    </div>

                    <figure class="mt-11 sm:mt-14">
                        <figcaption
                            class="mb-3 text-xs font-semibold tracking-[0.1em] text-white/85"
                        >
                            Clarity today. Confidence for what comes next.
                        </figcaption>
                        <img
                            src="/images/hero-consultation.webp"
                            alt="Three professionals reviewing financial documents together at a conference table"
                            width="1983"
                            height="793"
                            fetchpriority="high"
                            class="h-[245px] w-full rounded-xl object-cover object-center sm:h-[350px] lg:h-[430px]"
                        />
                    </figure>

                    <nav
                        class="relative z-10 mx-auto -mt-9 grid max-w-[1370px] grid-cols-2 rounded-2xl bg-white px-3 py-2 text-[#0b2b4c] shadow-[0_15px_40px_rgba(3,20,41,0.17)] md:grid-cols-4 md:px-5 md:py-3"
                        aria-label="Explore ALCO services"
                    >
                        <Link
                            v-for="(service, index) in services"
                            :key="service.slug"
                            :href="'/services#' + service.slug"
                            class="group flex min-h-21 items-center gap-3 rounded-lg px-3 py-3 transition-colors hover:bg-[#eaf6fb] focus-visible:outline-2 focus-visible:outline-[#1d9fc0] sm:gap-4 sm:px-5 md:min-h-19"
                        >
                            <component
                                :is="serviceIcons[index]"
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
                                class="shrink-0 text-[#1d9fc0] transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
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
                <div class="mx-auto max-w-[1370px] px-5 sm:px-8 lg:px-12">
                    <div
                        class="flex flex-col justify-between gap-6 md:flex-row md:items-end"
                    >
                        <div class="max-w-2xl">
                            <p
                                class="text-xs font-bold tracking-[0.18em] text-[#168bae] uppercase"
                            >
                                What we do
                            </p>
                            <h2
                                id="services-title"
                                class="mt-4 text-3xl leading-tight font-semibold tracking-tight text-[#082c4e] sm:text-4xl"
                            >
                                Good decisions begin with a clear picture.
                            </h2>
                        </div>
                        <Link
                            href="/services"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-[#087c9e] hover:underline"
                        >
                            View all services
                            <ArrowRight :size="18" aria-hidden="true" />
                        </Link>
                    </div>
                    <div class="mt-10 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <Link
                            v-for="(service, index) in services"
                            :key="service.slug"
                            :href="'/services#' + service.slug"
                            class="group flex min-h-62 flex-col rounded-xl border border-[#dbe6ec] bg-[#f8fbfc] p-6 transition-colors hover:border-[#42cce8] hover:bg-[#f0faff] focus-visible:outline-2 focus-visible:outline-[#168bae]"
                        >
                            <component
                                :is="serviceIcons[index]"
                                :size="30"
                                :stroke-width="1.7"
                                class="text-[#0f87a9]"
                                aria-hidden="true"
                            />
                            <h3 class="mt-8 text-xl font-semibold">
                                {{ service.title }}
                            </h3>
                            <p
                                class="mt-3 flex-1 text-sm leading-6 text-[#4d6273]"
                            >
                                {{ service.description }}
                            </p>
                            <span
                                class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-[#087c9e]"
                            >
                                Learn more
                                <ArrowUpRight :size="16" aria-hidden="true" />
                            </span>
                        </Link>
                    </div>
                </div>
            </section>

            <section
                class="bg-[#eaf6fb] py-20 sm:py-24"
                aria-labelledby="discover-title"
            >
                <div
                    class="mx-auto grid max-w-[1370px] gap-10 px-5 sm:px-8 lg:grid-cols-[1fr_1.1fr] lg:items-center lg:gap-20 lg:px-12"
                >
                    <div>
                        <p
                            class="text-xs font-bold tracking-[0.18em] text-[#168bae] uppercase"
                        >
                            Explore more
                        </p>
                        <h2
                            id="discover-title"
                            class="mt-4 text-3xl leading-tight font-semibold tracking-tight sm:text-4xl"
                        >
                            Useful guidance for the work ahead.
                        </h2>
                        <p
                            class="mt-5 max-w-lg text-base leading-7 text-[#4d6273]"
                        >
                            Our Insights and Accounting Tools sections are being
                            prepared to make everyday financial questions easier
                            to explore.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <Link
                            href="/insights"
                            class="group rounded-xl bg-white p-7 shadow-sm transition-transform hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-[#168bae]"
                        >
                            <BookOpen
                                :size="30"
                                class="text-[#168bae]"
                                aria-hidden="true"
                            />
                            <h3 class="mt-9 text-xl font-semibold">Insights</h3>
                            <p class="mt-3 text-sm leading-6 text-[#4d6273]">
                                Practical articles on accounting, reporting and
                                tax topics.
                            </p>
                            <ArrowUpRight
                                :size="20"
                                class="mt-7 text-[#168bae]"
                                aria-hidden="true"
                            />
                        </Link>
                        <Link
                            href="/tools"
                            class="group rounded-xl bg-white p-7 shadow-sm transition-transform hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-[#168bae]"
                        >
                            <ChartNoAxesCombined
                                :size="30"
                                class="text-[#168bae]"
                                aria-hidden="true"
                            />
                            <h3 class="mt-9 text-xl font-semibold">
                                Accounting Tools
                            </h3>
                            <p class="mt-3 text-sm leading-6 text-[#4d6273]">
                                Calculators to help you test and understand key
                                figures.
                            </p>
                            <ArrowUpRight
                                :size="20"
                                class="mt-7 text-[#168bae]"
                                aria-hidden="true"
                            />
                        </Link>
                    </div>
                </div>
            </section>

            <section
                class="bg-white py-20 text-center sm:py-24"
                aria-labelledby="contact-title"
            >
                <div class="mx-auto max-w-3xl px-5">
                    <p
                        class="text-xs font-bold tracking-[0.18em] text-[#168bae] uppercase"
                    >
                        Let's talk
                    </p>
                    <h2
                        id="contact-title"
                        class="mt-4 text-3xl leading-tight font-semibold tracking-tight sm:text-4xl"
                    >
                        Ready to bring more clarity to your business?
                    </h2>
                    <p class="mt-5 text-base leading-7 text-[#4d6273]">
                        Tell us what you need help with and begin a conversation
                        with ALCO.
                    </p>
                    <Link
                        href="/book-consultation"
                        class="mt-8 inline-flex min-h-12 items-center gap-3 rounded-md bg-[#062849] px-7 py-3 text-sm font-semibold text-white hover:bg-[#0b426e] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#168bae]"
                    >
                        Book a Consultation
                        <ArrowUpRight :size="18" aria-hidden="true" />
                    </Link>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
