<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Check } from '@lucide/vue';
import CtaLink from '@/components/public/CtaLink.vue';
import PageHero from '@/components/public/PageHero.vue';
import SectionHeading from '@/components/public/SectionHeading.vue';
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
        <PageHero
            eyebrow="ALCO services"
            :title="service.title"
            :intro="service.summary"
        >
            <template #before>
                <Link
                    href="/services"
                    class="inline-flex items-center gap-2 rounded-sm text-sm font-semibold text-aqua-400 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-aqua-400"
                >
                    <ArrowLeft :size="17" aria-hidden="true" />
                    All services
                </Link>
            </template>
        </PageHero>

        <section class="bg-white py-20">
            <div
                class="site-container grid gap-12 lg:grid-cols-[1.25fr_0.75fr] lg:gap-20"
            >
                <div>
                    <SectionHeading
                        eyebrow="The service"
                        title="A clearer view of the work ahead."
                    />
                    <p class="mt-6 text-lg leading-8 text-muted-foreground">
                        {{ service.body }}
                    </p>
                    <div
                        v-if="service.audience"
                        class="mt-10 rounded-xl bg-aqua-50 p-7"
                    >
                        <h3 class="text-xl font-semibold">Who it can help</h3>
                        <p class="mt-3 leading-7 text-muted-foreground">
                            {{ service.audience }}
                        </p>
                    </div>
                </div>
                <div>
                    <div class="rounded-xl border border-border bg-muted p-7">
                        <h2 class="text-xl font-semibold">
                            What we can discuss
                        </h2>
                        <ul class="mt-5 grid gap-4">
                            <li
                                v-for="item in service.deliverables"
                                :key="item.label"
                                class="flex gap-3 text-sm leading-6 text-muted-foreground"
                            >
                                <Check
                                    :size="19"
                                    class="mt-0.5 shrink-0 text-aqua-600"
                                    aria-hidden="true"
                                />
                                {{ item.label }}
                            </li>
                        </ul>
                        <CtaLink href="/book-consultation" class="mt-9">
                            Discuss your needs
                        </CtaLink>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
