<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight, Check } from '@lucide/vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { ServiceDetail } from '@/types/publicContent';

defineProps<{ service: ServiceDetail }>();
</script>

<template>
    <Head :title="service.title">
        <meta name="description" :content="service.seo_description" />
        <meta name="robots" content="noindex,follow" />
    </Head>

    <PublicLayout>
        <main>
            <section
                class="bg-[#062849] px-5 py-17 text-white sm:px-8 sm:py-23 lg:px-12"
            >
                <div class="mx-auto max-w-[1370px]">
                    <Link
                        href="/services"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-[#52d7f0] hover:underline"
                    >
                        <ArrowLeft :size="17" aria-hidden="true" />
                        All services
                    </Link>
                    <p
                        class="mt-12 text-xs font-bold tracking-[0.18em] text-[#52d7f0] uppercase"
                    >
                        ALCO services
                    </p>
                    <h1
                        class="mt-4 max-w-4xl text-4xl leading-tight font-semibold tracking-tight sm:text-5xl"
                    >
                        {{ service.title }}
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-white/80">
                        {{ service.summary }}
                    </p>
                </div>
            </section>

            <section class="bg-white px-5 py-20 sm:px-8 lg:px-12">
                <div
                    class="mx-auto grid max-w-[1370px] gap-12 lg:grid-cols-[1.25fr_0.75fr] lg:gap-20"
                >
                    <div>
                        <p
                            class="text-xs font-bold tracking-[0.18em] text-[#168bae] uppercase"
                        >
                            The service
                        </p>
                        <h2 class="mt-4 text-3xl font-semibold tracking-tight">
                            A clearer view of the work ahead.
                        </h2>
                        <p class="mt-6 text-lg leading-8 text-[#4d6273]">
                            {{ service.body }}
                        </p>
                        <div
                            v-if="service.audience"
                            class="mt-10 rounded-xl bg-[#eaf6fb] p-7"
                        >
                            <h3 class="text-xl font-semibold">
                                Who it can help
                            </h3>
                            <p class="mt-3 leading-7 text-[#4d6273]">
                                {{ service.audience }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <div
                            class="rounded-xl border border-[#dbe6ec] bg-[#f8fbfc] p-7"
                        >
                            <h2 class="text-xl font-semibold">
                                What we can discuss
                            </h2>
                            <ul class="mt-5 grid gap-4">
                                <li
                                    v-for="item in service.deliverables"
                                    :key="item.label"
                                    class="flex gap-3 text-sm leading-6 text-[#4d6273]"
                                >
                                    <Check
                                        :size="19"
                                        class="mt-0.5 shrink-0 text-[#168bae]"
                                        aria-hidden="true"
                                    />
                                    {{ item.label }}
                                </li>
                            </ul>
                            <Link
                                href="/book-consultation"
                                class="mt-9 inline-flex min-h-12 items-center gap-3 rounded-md bg-[#062849] px-6 py-3 text-sm font-semibold text-white hover:bg-[#0b426e]"
                            >
                                Discuss your needs
                                <ArrowUpRight :size="18" aria-hidden="true" />
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
