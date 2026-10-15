<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight, Clock3, FileText } from '@lucide/vue';
import ArticleCard from '@/components/public/ArticleCard.vue';
import PageHero from '@/components/public/PageHero.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type {
    ArticleDetail,
    ArticleSummary,
    ServiceSummary,
} from '@/types/publicContent';

const props = defineProps<{
    post: ArticleDetail;
    relatedPosts: ArticleSummary[];
    relatedServices: ServiceSummary[];
}>();

function formattedDate(value: string | null): string {
    if (!value) return '';

    return new Intl.DateTimeFormat('en-NG', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(`${value}T12:00:00`));
}
</script>

<template>
    <Head :title="post.title">
        <meta name="description" :content="post.seo_description" />
    </Head>

    <PublicLayout>
        <PageHero
            eyebrow="ALCO Insights"
            :title="post.title"
            :intro="post.excerpt"
        >
            <template #before>
                <Link
                    href="/insights"
                    class="mb-8 inline-flex items-center gap-2 text-sm font-bold text-aqua-700 hover:underline"
                >
                    <ArrowLeft :size="17" aria-hidden="true" />
                    All insights
                </Link>
            </template>
            <template #after-intro>
                <div
                    class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-navy-700"
                >
                    <span v-if="post.published_at"
                        >Published {{ formattedDate(post.published_at) }}</span
                    >
                    <span class="inline-flex items-center gap-1.5">
                        <Clock3 :size="16" aria-hidden="true" />
                        {{ post.reading_minutes }} min read
                    </span>
                    <span v-if="post.author_name"
                        >By {{ post.author_name }}</span
                    >
                </div>
            </template>
        </PageHero>

        <main class="bg-white py-14 sm:py-20">
            <article class="site-container max-w-4xl">
                <img
                    v-if="post.cover"
                    :src="post.cover.src"
                    :srcset="post.cover.srcset ?? undefined"
                    :width="post.cover.width ?? undefined"
                    :height="post.cover.height ?? undefined"
                    :alt="post.cover.alt"
                    class="mb-10 aspect-[16/9] w-full rounded-xl object-cover"
                    fetchpriority="high"
                />

                <div
                    v-if="post.categories.length || post.tags.length"
                    class="mb-8 flex flex-wrap gap-2"
                >
                    <span
                        v-for="category in post.categories"
                        :key="`category-${category}`"
                        class="rounded-full bg-aqua-50 px-3 py-1 text-xs font-bold text-aqua-800"
                        >{{ category }}</span
                    >
                    <span
                        v-for="tag in post.tags"
                        :key="`tag-${tag}`"
                        class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground"
                        >{{ tag }}</span
                    >
                </div>

                <div
                    class="article-rich-content text-base leading-8 text-muted-foreground sm:text-lg"
                    v-html="post.body_html"
                />

                <aside
                    v-if="post.reviewer_name || post.reviewed_at"
                    class="mt-10 rounded-xl border border-aqua-100 bg-aqua-50 p-6"
                >
                    <p class="text-sm font-bold text-navy-900">
                        Editorial review
                    </p>
                    <p class="mt-2 text-sm leading-6 text-navy-700">
                        <template v-if="post.reviewer_name"
                            >Reviewed by {{ post.reviewer_name }}</template
                        >
                        <template v-if="post.reviewer_name && post.reviewed_at">
                            on
                        </template>
                        <template v-if="post.reviewed_at">{{
                            formattedDate(post.reviewed_at)
                        }}</template>
                    </p>
                </aside>

                <section
                    v-if="post.sources.length"
                    class="mt-12 border-t border-border pt-9"
                >
                    <h2 class="text-2xl font-semibold text-navy-900">
                        Sources
                    </h2>
                    <ul class="mt-5 grid gap-3">
                        <li v-for="source in post.sources" :key="source.url">
                            <a
                                :href="source.url"
                                class="text-sm font-semibold text-aqua-700 hover:underline"
                                rel="noopener noreferrer"
                                target="_blank"
                                >{{ source.title
                                }}<span class="sr-only">
                                    (opens in a new tab)</span
                                ></a
                            >
                            <span
                                v-if="source.accessed_on"
                                class="ml-2 text-sm text-muted-foreground"
                                >Accessed
                                {{ formattedDate(source.accessed_on) }}</span
                            >
                        </li>
                    </ul>
                </section>
            </article>

            <section
                v-if="relatedServices.length"
                class="mt-16 bg-muted py-14 sm:py-16"
                aria-labelledby="related-services-title"
            >
                <div class="site-container">
                    <p
                        class="text-xs font-bold tracking-[0.16em] text-aqua-700 uppercase"
                    >
                        Related ALCO services
                    </p>
                    <h2
                        id="related-services-title"
                        class="mt-3 text-3xl font-semibold tracking-tight text-navy-900"
                    >
                        Continue the conversation with the right support.
                    </h2>
                    <div class="mt-8 grid gap-4 md:grid-cols-2">
                        <Link
                            v-for="service in relatedServices"
                            :key="service.slug"
                            :href="`/services/${service.slug}`"
                            class="rounded-xl border border-border bg-white p-6 hover:border-aqua-600"
                        >
                            <FileText
                                :size="22"
                                class="text-aqua-700"
                                aria-hidden="true"
                            />
                            <h3
                                class="mt-4 text-xl font-semibold text-navy-900"
                            >
                                {{ service.title }}
                            </h3>
                            <p
                                class="mt-2 text-sm leading-6 text-muted-foreground"
                            >
                                {{ service.summary }}
                            </p>
                            <span
                                class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-aqua-700"
                                >Explore service
                                <ArrowUpRight :size="16" aria-hidden="true" />
                            </span>
                        </Link>
                    </div>
                </div>
            </section>

            <section
                v-if="relatedPosts.length"
                class="site-container mt-16"
                aria-labelledby="related-insights-title"
            >
                <p
                    class="text-xs font-bold tracking-[0.16em] text-aqua-700 uppercase"
                >
                    Keep reading
                </p>
                <h2
                    id="related-insights-title"
                    class="mt-3 text-3xl font-semibold tracking-tight text-navy-900"
                >
                    Related insights
                </h2>
                <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <ArticleCard
                        v-for="article in relatedPosts"
                        :key="article.slug"
                        :article="article"
                    />
                </div>
            </section>

            <section class="site-container mt-16">
                <div class="rounded-xl bg-navy-900 p-8 text-white sm:p-10">
                    <h2 class="text-2xl font-semibold">
                        Have a question about your business records?
                    </h2>
                    <p class="mt-3 max-w-2xl leading-7 text-white/75">
                        Tell us what you need to understand and we can discuss
                        the appropriate accounting support.
                    </p>
                    <Link
                        href="/book-consultation"
                        class="mt-7 inline-flex items-center gap-2 rounded-md bg-aqua-400 px-5 py-3 text-sm font-bold text-navy-900 hover:bg-aqua-300"
                        >Book a consultation
                        <ArrowUpRight :size="17" aria-hidden="true" />
                    </Link>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
