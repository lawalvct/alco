<script setup lang="ts">
import BrandPattern from '@/components/public/BrandPattern.vue';
import CtaLink from '@/components/public/CtaLink.vue';
import SectionHeading from '@/components/public/SectionHeading.vue';

withDefaults(
    defineProps<{
        id?: string;
        eyebrow?: string;
        title?: string;
        description?: string;
        /** Drop the top padding when the section above is also white. */
        flushTop?: boolean;
    }>(),
    {
        id: 'cta-title',
        eyebrow: 'Let’s talk',
        title: 'Ready to bring more clarity to your business?',
        description:
            'Tell us what you need help with and begin a conversation with ALCO.',
        flushTop: false,
    },
);
</script>

<template>
    <section
        class="bg-white pb-20 sm:pb-24"
        :class="!flushTop && 'pt-20 sm:pt-24'"
        :aria-labelledby="id"
    >
        <div class="site-container">
            <div
                class="relative isolate overflow-hidden rounded-2xl bg-navy-900 px-6 py-14 text-center sm:px-12 sm:py-18"
            >
                <BrandPattern
                    class="absolute -bottom-24 -left-20 -z-10 hidden h-90 w-72 text-aqua-400/20 md:block"
                />
                <BrandPattern
                    class="absolute -top-24 -right-20 -z-10 hidden h-90 w-72 rotate-180 text-aqua-400/20 md:block"
                />
                <SectionHeading
                    :id="id"
                    class="mx-auto max-w-2xl"
                    tone="dark"
                    :eyebrow="eyebrow"
                    :title="title"
                    :description="description"
                />
                <div
                    class="mt-8 flex flex-col items-stretch justify-center gap-3 sm:flex-row sm:items-center"
                >
                    <slot>
                        <CtaLink href="/book-consultation" variant="accent">
                            Book a Consultation
                        </CtaLink>
                    </slot>
                </div>
            </div>
        </div>
    </section>
</template>
