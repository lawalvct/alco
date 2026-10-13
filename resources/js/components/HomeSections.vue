<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import CtaLink from '@/components/public/CtaLink.vue';
import SectionHeading from '@/components/public/SectionHeading.vue';
import type { HomeBlock, PackageSummary } from '@/types/publicContent';

withDefaults(
    defineProps<{ blocks: HomeBlock[]; packages?: PackageSummary[] }>(),
    { packages: () => [] },
);
</script>

<template>
    <template v-for="(block, index) in blocks" :key="`${block.type}-${index}`">
        <section
            v-if="block.type === 'packages' && packages.length"
            class="border-t border-border bg-white py-20 sm:py-24"
            :aria-labelledby="`home-block-${index}`"
        >
            <div class="site-container">
                <div
                    class="flex flex-col justify-between gap-6 md:flex-row md:items-end"
                >
                    <SectionHeading
                        :id="`home-block-${index}`"
                        class="max-w-2xl"
                        :eyebrow="block.data.eyebrow"
                        :title="block.data.title"
                        :description="block.data.body"
                    />
                    <CtaLink
                        href="/services#packages"
                        variant="link"
                        icon="arrow-right"
                    >
                        Compare packages
                    </CtaLink>
                </div>
                <ol class="mt-10 grid gap-4 md:grid-cols-3">
                    <li v-for="(pkg, pkgIndex) in packages" :key="pkg.slug">
                        <Link
                            href="/services#packages"
                            class="group flex h-full flex-col rounded-xl border border-border bg-muted p-7 transition-colors hover:border-aqua-400 hover:bg-aqua-50 focus-visible:outline-2 focus-visible:outline-aqua-600"
                        >
                            <span class="text-sm font-bold text-aqua-700">
                                {{ String(pkgIndex + 1).padStart(2, '0') }}
                            </span>
                            <h3 class="mt-5 text-xl font-semibold">
                                {{ pkg.name }}
                            </h3>
                            <p
                                v-if="pkg.summary"
                                class="mt-1 text-sm font-semibold text-aqua-700"
                            >
                                {{ pkg.summary }}
                            </p>
                            <p
                                class="mt-4 flex-1 text-sm leading-6 text-muted-foreground"
                            >
                                {{ pkg.audience }}
                            </p>
                            <span
                                class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-aqua-700"
                            >
                                See what’s included
                                <ArrowRight
                                    :size="16"
                                    class="transition-transform motion-safe:group-hover:translate-x-0.5"
                                    aria-hidden="true"
                                />
                            </span>
                        </Link>
                    </li>
                </ol>
            </div>
        </section>

        <section
            v-else-if="block.type === 'intro'"
            class="bg-muted py-20 sm:py-24"
            :aria-labelledby="`home-block-${index}`"
        >
            <div
                class="site-container grid gap-8 lg:grid-cols-[0.7fr_1.3fr] lg:items-start lg:gap-20"
            >
                <p class="eyebrow text-aqua-700">
                    {{ block.data.eyebrow }}
                </p>
                <div>
                    <SectionHeading
                        :id="`home-block-${index}`"
                        class="max-w-3xl"
                        :title="block.data.title"
                    />
                    <p
                        class="mt-6 max-w-3xl text-base leading-8 text-muted-foreground sm:text-lg"
                    >
                        {{ block.data.body }}
                    </p>
                </div>
            </div>
        </section>

        <section
            v-else-if="block.type === 'audience'"
            class="bg-navy-900 py-20 text-white sm:py-24"
            :aria-labelledby="`home-block-${index}`"
        >
            <div class="site-container">
                <div
                    class="grid gap-5 lg:grid-cols-[1fr_0.8fr] lg:items-end lg:gap-16"
                >
                    <SectionHeading
                        :id="`home-block-${index}`"
                        class="max-w-2xl"
                        tone="dark"
                        :eyebrow="block.data.eyebrow"
                        :title="block.data.title"
                    />
                    <p class="max-w-lg leading-7 text-white/75">
                        {{ block.data.body }}
                    </p>
                </div>
                <div class="mt-10 grid gap-4 md:grid-cols-3">
                    <div
                        v-for="item in block.data.items ?? []"
                        :key="item.label"
                        class="rounded-xl border border-white/20 bg-white/5 p-7"
                    >
                        <h3 class="text-xl font-semibold text-white">
                            {{ item.label }}
                        </h3>
                        <p class="mt-4 text-sm leading-7 text-white/75">
                            {{ item.detail }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section
            v-else-if="block.type === 'process'"
            class="bg-white py-20 sm:py-24"
            :aria-labelledby="`home-block-${index}`"
        >
            <div class="site-container">
                <SectionHeading
                    :id="`home-block-${index}`"
                    class="max-w-3xl"
                    :eyebrow="block.data.eyebrow"
                    :title="block.data.title"
                />
                <ol class="mt-10 grid gap-4 md:grid-cols-3">
                    <li
                        v-for="(step, stepIndex) in block.data.steps ?? []"
                        :key="step.title"
                        class="rounded-xl border border-border bg-muted p-7"
                    >
                        <span class="text-sm font-bold text-aqua-700"
                            >0{{ stepIndex + 1 }}</span
                        >
                        <h3 class="mt-6 text-xl font-semibold">
                            {{ step.title }}
                        </h3>
                        <p class="mt-3 text-sm leading-7 text-muted-foreground">
                            {{ step.detail }}
                        </p>
                    </li>
                </ol>
            </div>
        </section>

        <section
            v-else-if="block.type === 'faq'"
            class="bg-aqua-50 py-20 sm:py-24"
            :aria-labelledby="`home-block-${index}`"
        >
            <div class="site-container max-w-4xl">
                <SectionHeading
                    :id="`home-block-${index}`"
                    :eyebrow="block.data.eyebrow"
                    :title="block.data.title"
                />
                <div class="mt-9 grid gap-3">
                    <details
                        v-for="item in block.data.items ?? []"
                        :key="item.question"
                        class="group rounded-xl bg-white px-6 py-5 shadow-card"
                    >
                        <summary
                            class="cursor-pointer rounded-sm text-base font-semibold marker:text-aqua-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-aqua-600"
                        >
                            {{ item.question }}
                        </summary>
                        <p class="mt-4 leading-7 text-muted-foreground">
                            {{ item.answer }}
                        </p>
                    </details>
                </div>
            </div>
        </section>
    </template>
</template>
