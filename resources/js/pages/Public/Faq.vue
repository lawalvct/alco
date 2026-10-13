<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ContactCard from '@/components/public/ContactCard.vue';
import FaqList from '@/components/public/FaqList.vue';
import PageHero from '@/components/public/PageHero.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { FaqGroup } from '@/types/publicContent';

withDefaults(defineProps<{ groups?: FaqGroup[] }>(), { groups: () => [] });
</script>

<template>
    <Head title="Frequently asked questions">
        <meta
            name="description"
            content="Answers to common questions about working with ALCO Associate Solutions."
        />
    </Head>

    <PublicLayout>
        <PageHero
            eyebrow="FAQ"
            title="Answers to common questions."
            intro="If your question is not covered here, call or email us and we will help."
        />

        <section class="bg-aqua-50 py-16 sm:py-20">
            <div
                class="site-container grid gap-12 lg:grid-cols-[1.4fr_0.6fr] lg:items-start"
            >
                <div class="grid gap-12">
                    <section
                        v-for="(group, index) in groups"
                        :key="group.name"
                        :aria-labelledby="`faq-group-${index}`"
                    >
                        <h2
                            :id="`faq-group-${index}`"
                            class="text-2xl font-semibold tracking-tight"
                        >
                            {{ group.name }}
                        </h2>
                        <FaqList class="mt-5" :items="group.items" />
                    </section>

                    <p
                        v-if="!groups.length"
                        class="rounded-xl bg-white p-8 text-base leading-7 text-muted-foreground shadow-card"
                    >
                        Our questions and answers are being prepared. In the
                        meantime, please get in touch directly.
                    </p>
                </div>

                <ContactCard
                    class="lg:sticky lg:top-8"
                    title="Still have a question?"
                    description="Call either number or send an email and we will get back to you."
                />
            </div>
        </section>
    </PublicLayout>
</template>
