<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowUpRight, Info } from '@lucide/vue';
import ArticleCard from '@/components/public/ArticleCard.vue';
import PageHero from '@/components/public/PageHero.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { TopicHubContent } from '@/types/publicContent';

defineProps<{ topic: TopicHubContent }>();
</script>

<template>
    <Head :title="topic.title">
        <meta name="description" :content="topic.seo_description || topic.intro" />
        <link rel="canonical" :href="`/insights/topic/${topic.slug}`" />
    </Head>

    <PublicLayout>
        <PageHero eyebrow="ALCO Insights topic" :title="topic.title" :intro="topic.intro" />

        <main class="bg-white py-14 sm:py-20">
            <div class="site-container">
                <aside
                    v-if="topic.slug === 'vat'"
                    class="mb-12 flex gap-4 rounded-xl border border-aqua-100 bg-aqua-50 p-6 text-sm leading-7 text-navy-800"
                >
                    <Info :size="21" class="mt-0.5 shrink-0 text-aqua-700" aria-hidden="true" />
                    <p>
                        VAT information is educational and should be reviewed against your own circumstances and current applicable rules. ALCO publishes VAT content only after an accounting or tax review.
                    </p>
                </aside>

                <section v-if="topic.featured" aria-labelledby="featured-article-title">
                    <p class="text-xs font-bold tracking-[0.16em] text-aqua-700 uppercase">Featured insight</p>
                    <h2 id="featured-article-title" class="mt-3 mb-7 text-3xl font-semibold tracking-tight text-navy-900">
                        Start here
                    </h2>
                    <ArticleCard :article="topic.featured" featured />
                </section>

                <section
                    v-if="topic.articles.length"
                    class="mt-16"
                    aria-labelledby="topic-articles-title"
                >
                    <p class="text-xs font-bold tracking-[0.16em] text-aqua-700 uppercase">More on this topic</p>
                    <h2 id="topic-articles-title" class="mt-3 text-3xl font-semibold tracking-tight text-navy-900">
                        Related articles
                    </h2>
                    <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        <ArticleCard
                            v-for="article in topic.articles"
                            :key="article.slug"
                            :article="article"
                        />
                    </div>
                </section>

                <section
                    v-if="!topic.featured && !topic.articles.length"
                    class="rounded-xl border border-dashed border-border bg-muted p-8 text-center sm:p-12"
                >
                    <h2 class="text-2xl font-semibold text-navy-900">Articles are under review.</h2>
                    <p class="mx-auto mt-3 max-w-xl leading-7 text-muted-foreground">
                        ALCO will add reviewed content to this topic. If you need advice sooner, contact the team to discuss your situation.
                    </p>
                    <Link
                        href="/contact"
                        class="mt-6 inline-flex items-center gap-2 rounded-md bg-navy-900 px-5 py-3 text-sm font-bold text-white hover:bg-navy-800"
                        >Contact ALCO <ArrowUpRight :size="17" aria-hidden="true" />
                    </Link>
                </section>
            </div>
        </main>
    </PublicLayout>
</template>
