<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { computed, ref } from 'vue';
import ArticleCard from '@/components/public/ArticleCard.vue';
import PageHero from '@/components/public/PageHero.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type {
    InsightCategory,
    PaginatedArticles,
} from '@/types/publicContent';

const props = defineProps<{
    articles: PaginatedArticles;
    categories: InsightCategory[];
    selectedCategory: InsightCategory | null;
    filters: { q: string };
}>();

const search = ref(props.filters.q);
const title = computed(() =>
    props.selectedCategory ? props.selectedCategory.name : 'Insights',
);
const description = computed(
    () =>
        props.selectedCategory?.seo_description ||
        props.selectedCategory?.description ||
        'Practical accounting and business insights from ALCO Associate Solutions.',
);

function submitSearch(): void {
    router.get(
        props.selectedCategory
            ? `/insights/category/${props.selectedCategory.slug}`
            : '/insights',
        search.value.trim() ? { q: search.value.trim() } : {},
        { preserveScroll: true, preserveState: false, replace: true },
    );
}
</script>

<template>
    <Head :title="title">
        <meta name="description" :content="description" />
    </Head>

    <PublicLayout>
        <PageHero
            eyebrow="ALCO Insights"
            :title="selectedCategory ? `${selectedCategory.name} insights` : 'Clearer thinking for your business.'"
            :intro="selectedCategory?.description || 'Practical articles on accounting, reporting, tax preparation and business finance. We publish only content that has completed ALCO’s editorial review.'"
        />

        <main class="bg-muted py-14 sm:py-20">
            <div class="site-container">
                <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
                    <nav
                        v-if="categories.length"
                        class="flex flex-wrap gap-2"
                        aria-label="Insight categories"
                    >
                        <Link
                            href="/insights"
                            class="rounded-full border px-4 py-2 text-sm font-semibold transition-colors"
                            :class="!selectedCategory ? 'border-navy-900 bg-navy-900 text-white' : 'border-border bg-white text-navy-900 hover:border-aqua-600'"
                            >All insights</Link
                        >
                        <Link
                            v-for="category in categories"
                            :key="category.slug"
                            :href="`/insights/category/${category.slug}`"
                            class="rounded-full border px-4 py-2 text-sm font-semibold transition-colors"
                            :class="selectedCategory?.slug === category.slug ? 'border-navy-900 bg-navy-900 text-white' : 'border-border bg-white text-navy-900 hover:border-aqua-600'"
                            >{{ category.name }}</Link
                        >
                    </nav>
                    <form
                        class="flex w-full max-w-md gap-2"
                        role="search"
                        @submit.prevent="submitSearch"
                    >
                        <label class="sr-only" for="insights-search"
                            >Search insights</label
                        >
                        <input
                            id="insights-search"
                            v-model="search"
                            type="search"
                            maxlength="100"
                            placeholder="Search insights"
                            class="min-h-11 min-w-0 flex-1 rounded-md border border-border bg-white px-4 text-sm outline-none ring-aqua-600 focus:ring-2"
                        />
                        <button
                            type="submit"
                            class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md bg-navy-900 text-white hover:bg-navy-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aqua-600"
                            aria-label="Search insights"
                        >
                            <Search :size="19" aria-hidden="true" />
                        </button>
                    </form>
                </div>

                <p
                    v-if="filters.q"
                    class="mt-8 text-sm text-muted-foreground"
                >
                    {{ articles.total }} article{{ articles.total === 1 ? '' : 's' }}
                    found for “{{ filters.q }}”.
                </p>

                <div
                    v-if="articles.data.length"
                    class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3"
                >
                    <ArticleCard
                        v-for="article in articles.data"
                        :key="article.slug"
                        :article="article"
                    />
                </div>
                <div
                    v-else
                    class="mt-10 rounded-xl border border-dashed border-border bg-white p-8 text-center sm:p-12"
                >
                    <h2 class="text-2xl font-semibold text-navy-900">
                        Insights are being prepared.
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl leading-7 text-muted-foreground">
                        ALCO is reviewing articles before publication. Please return soon or contact us to discuss your question directly.
                    </p>
                    <Link
                        href="/contact"
                        class="mt-6 inline-flex rounded-md bg-navy-900 px-5 py-3 text-sm font-bold text-white hover:bg-navy-800"
                        >Contact ALCO</Link
                    >
                </div>

                <nav
                    v-if="articles.last_page > 1"
                    class="mt-10 flex flex-wrap justify-center gap-2"
                    aria-label="Insights pages"
                >
                    <template v-for="link in articles.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-md border px-3 text-sm font-semibold"
                            :class="link.active ? 'border-navy-900 bg-navy-900 text-white' : 'border-border bg-white text-navy-900 hover:border-aqua-600'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-md border border-border bg-white px-3 text-sm text-muted-foreground"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </main>
    </PublicLayout>
</template>
