<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHero from '@/components/public/PageHero.vue';
import PageSections from '@/components/public/PageSections.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type {
    HomeBlock,
    PackageSummary,
    ServiceSummary,
    TeamProfile,
} from '@/types/publicContent';

defineProps<{
    content: {
        title: string;
        hero_eyebrow?: string | null;
        hero_line_one?: string | null;
        hero_description?: string | null;
        seo_description?: string | null;
        blocks: HomeBlock[];
    };
    services: ServiceSummary[];
    team: TeamProfile[];
    packages: PackageSummary[];
}>();
</script>

<template>
    <Head :title="content.title">
        <meta
            name="description"
            :content="content.seo_description || content.hero_description || content.title"
        />
    </Head>

    <PublicLayout>
        <PageHero
            :eyebrow="content.hero_eyebrow || 'ALCO Associate Solutions'"
            :title="content.hero_line_one || content.title"
            :intro="content.hero_description || undefined"
        />
        <PageSections
            v-if="content.blocks?.length"
            :blocks="content.blocks"
            :services="services"
            :team="team"
            :packages="packages"
        />
    </PublicLayout>
</template>
