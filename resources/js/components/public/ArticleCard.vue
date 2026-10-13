<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, Clock3 } from '@lucide/vue';
import type { ArticleSummary } from '@/types/publicContent';

defineProps<{ article: ArticleSummary; featured?: boolean }>();

function formattedDate(value: string | null): string {
    if (!value) return '';

    return new Intl.DateTimeFormat('en-NG', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(`${value}T12:00:00`));
}
</script>

<template>
    <article
        class="group overflow-hidden rounded-xl border border-border bg-card shadow-card transition-shadow hover:shadow-float"
        :class="featured ? 'md:grid md:grid-cols-2' : ''"
    >
        <div
            v-if="article.cover"
            class="overflow-hidden bg-aqua-50"
            :class="featured ? 'min-h-64 md:min-h-full' : 'aspect-[16/9]'"
        >
            <img
                :src="article.cover.src"
                :srcset="article.cover.srcset ?? undefined"
                :width="article.cover.width ?? undefined"
                :height="article.cover.height ?? undefined"
                :alt="article.cover.alt"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                loading="lazy"
            />
        </div>
        <div class="flex min-h-full flex-col p-6 sm:p-7">
            <p
                v-if="article.categories.length"
                class="text-xs font-bold tracking-[0.15em] text-aqua-700 uppercase"
            >
                {{ article.categories.join(' · ') }}
            </p>
            <h2
                class="mt-3 text-xl leading-snug font-semibold tracking-tight text-navy-900 sm:text-2xl"
            >
                <Link
                    :href="`/insights/${article.slug}`"
                    class="rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-aqua-600"
                    >{{ article.title }}</Link
                >
            </h2>
            <p class="mt-3 text-sm leading-7 text-muted-foreground">
                {{ article.excerpt }}
            </p>
            <div
                class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-medium text-muted-foreground"
            >
                <span v-if="article.published_at">{{
                    formattedDate(article.published_at)
                }}</span>
                <span class="inline-flex items-center gap-1.5">
                    <Clock3 :size="14" aria-hidden="true" />
                    {{ article.reading_minutes }} min read
                </span>
            </div>
            <Link
                :href="`/insights/${article.slug}`"
                class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-aqua-700 hover:underline"
            >
                Read article <ArrowUpRight :size="17" aria-hidden="true" />
            </Link>
        </div>
    </article>
</template>
