<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowUpRight, Calculator, ShieldCheck } from '@lucide/vue';
import PageHero from '@/components/public/PageHero.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { PageCopy } from '@/types/publicContent';

defineProps<{
    tools: { slug: string; title: string; summary: string }[];
    pageCopy: PageCopy;
}>();
</script>

<template>
    <Head :title="pageCopy.title">
        <meta
            name="description"
            :content="pageCopy.seo_description"
        />
    </Head>

    <PublicLayout>
        <PageHero
            :eyebrow="pageCopy.hero_eyebrow"
            :title="pageCopy.hero_line_one"
            :intro="pageCopy.hero_description"
        />

        <section class="site-container py-14 sm:py-20">
            <div
                class="mb-10 flex flex-col gap-4 border-b border-border pb-8 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <p class="eyebrow text-aqua-700">Explore the tools</p>
                    <h2
                        class="mt-3 text-3xl font-semibold tracking-tight text-navy-900"
                    >
                        Choose a calculation.
                    </h2>
                </div>
                <p class="max-w-md text-sm leading-6 text-muted-foreground">
                    Tax rates are entered manually as estimates until an
                    approved, dated rule is available in the CMS.
                </p>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <Link
                    v-for="(tool, index) in tools"
                    :key="tool.slug"
                    :href="`/tools/${tool.slug}`"
                    class="group flex min-h-62 flex-col rounded-xl border border-border bg-white p-7 shadow-card transition-all hover:-translate-y-1 hover:border-aqua-500 hover:shadow-float focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aqua-700"
                >
                    <div class="flex items-start justify-between">
                        <span
                            class="flex size-11 items-center justify-center rounded-lg bg-aqua-50 text-aqua-700"
                            ><Calculator :size="21" aria-hidden="true"
                        /></span>
                        <span
                            class="text-xs font-bold tracking-widest text-navy-400"
                            >{{ String(index + 1).padStart(2, '0') }}</span
                        >
                    </div>
                    <h3 class="mt-7 text-xl font-semibold text-navy-900">
                        {{ tool.title }}
                    </h3>
                    <p
                        class="mt-3 flex-1 text-sm leading-6 text-muted-foreground"
                    >
                        {{ tool.summary }}
                    </p>
                    <span
                        class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-aqua-700"
                        >Open calculator
                        <ArrowUpRight
                            :size="17"
                            aria-hidden="true"
                            class="transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                    /></span>
                </Link>
            </div>
        </section>

        <section class="site-container pb-16 sm:pb-20">
            <div class="max-w-4xl border-t border-border pt-10">
                <h2 class="text-2xl font-semibold text-navy-900">
                    How to use ALCO's accounting tools
                </h2>
                <p class="mt-4 text-sm leading-7 text-navy-700">
                    Choose a calculator, enter figures from the same reporting
                    period where relevant, and review its formula and
                    assumptions before using the result. Each tool opens without
                    an account. You can adjust your inputs and calculate again
                    as often as you need.
                </p>
                <p class="mt-3 text-sm leading-7 text-navy-700">
                    The VAT and withholding tax tools do not guess a statutory
                    rate or decide a transaction's tax classification. Use a
                    clearly labelled manual estimate or an approved, dated rule
                    when ALCO has configured one.
                </p>
            </div>
        </section>

        <section class="bg-navy-900 py-12 text-white">
            <div
                class="site-container flex flex-col gap-5 md:flex-row md:items-center"
            >
                <ShieldCheck
                    :size="30"
                    class="shrink-0 text-aqua-400"
                    aria-hidden="true"
                />
                <p class="max-w-3xl text-sm leading-7 text-white/80">
                    Results are educational estimates based on the figures and
                    assumptions you enter. Check current tax treatment and
                    important decisions with a qualified professional.
                </p>
                <Link
                    href="/book-consultation"
                    class="w-fit shrink-0 rounded-md bg-aqua-400 px-5 py-3 text-sm font-bold text-navy-900 hover:bg-aqua-300 md:ml-auto"
                    >Talk to ALCO</Link
                >
            </div>
        </section>
    </PublicLayout>
</template>
