<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHero from '@/components/public/PageHero.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { LegalContent } from '@/types/publicContent';

defineProps<{ legal: LegalContent }>();
</script>

<template>
    <Head :title="legal.title">
        <meta
            v-if="legal.seo_description"
            name="description"
            :content="legal.seo_description"
        />
    </Head>

    <PublicLayout>
        <PageHero
            :eyebrow="legal.eyebrow"
            :title="legal.title"
            :intro="legal.intro"
        />

        <section class="bg-white py-16 sm:py-20">
            <div class="site-container max-w-3xl">
                <p
                    v-if="legal.effective_date"
                    class="text-sm font-semibold text-muted-foreground"
                >
                    Last updated: {{ legal.effective_date }}
                </p>
                <section
                    v-for="(section, index) in legal.sections"
                    :key="section.heading"
                    :aria-labelledby="`legal-${index}`"
                    :class="index === 0 && !legal.effective_date ? '' : 'mt-12'"
                >
                    <h2
                        :id="`legal-${index}`"
                        class="text-2xl font-semibold tracking-tight"
                    >
                        {{ section.heading }}
                    </h2>
                    <div
                        class="article-rich-content mt-4 text-base leading-8 text-muted-foreground [&>:first-child]:mt-0"
                        v-html="section.body_html"
                    />
                </section>
            </div>
        </section>
    </PublicLayout>
</template>
