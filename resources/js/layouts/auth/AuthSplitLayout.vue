<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import BrandLockup from '@/components/public/BrandLockup.vue';
import BrandPattern from '@/components/public/BrandPattern.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <main
        class="theme-light flex min-h-screen flex-col bg-muted text-foreground lg:grid lg:grid-cols-2"
    >
        <div
            class="relative isolate order-2 flex min-h-55 flex-col justify-between overflow-hidden bg-navy-900 px-6 py-5 text-white sm:px-10 sm:py-9 lg:order-1 lg:min-h-screen lg:px-14 lg:py-12 xl:px-20"
        >
            <div
                class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_18%_12%,color-mix(in_srgb,var(--color-aqua-400)_19%,transparent),transparent_40%),linear-gradient(145deg,var(--color-navy-900)_12%,var(--color-navy-800)_100%)]"
                aria-hidden="true"
            />
            <BrandPattern
                class="absolute -right-20 -bottom-20 -z-10 h-110 w-90 text-aqua-400/20 sm:h-135 sm:w-110"
            />

            <Link
                :href="home()"
                class="relative z-10 w-fit rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-aqua-400"
                aria-label="ALCO Associate Solutions — Home"
            >
                <BrandLockup />
            </Link>

            <div class="relative z-10 mt-6 max-w-xl sm:mt-10 lg:mt-0">
                <p class="eyebrow tracking-[0.22em] text-aqua-400">
                    {{ name }}
                </p>
                <h2
                    class="mt-3 max-w-lg text-2xl leading-tight font-semibold tracking-[-0.035em] sm:mt-4 sm:text-4xl lg:text-5xl"
                >
                    Your numbers, in order.
                    <span class="mt-1 block text-aqua-400">
                        Your business, moving forward.
                    </span>
                </h2>
                <p
                    class="mt-5 hidden max-w-md text-sm leading-6 text-white/75 sm:block sm:text-base sm:leading-7"
                >
                    Professional accounting and practical business support, all
                    in one place.
                </p>
            </div>

            <p
                class="relative z-10 mt-5 hidden text-xs text-white/60 sm:block lg:mt-0"
            >
                &copy; {{ new Date().getFullYear() }} ALCO Associate Solutions
            </p>
        </div>

        <section
            class="order-1 flex items-center justify-center px-5 py-10 sm:px-8 sm:py-14 lg:order-2 lg:min-h-screen lg:px-10 lg:py-12"
        >
            <div
                class="w-full max-w-md rounded-2xl border border-navy-100 bg-card px-6 py-8 shadow-panel sm:px-9 sm:py-10"
            >
                <div class="mb-8 flex flex-col gap-2">
                    <h1
                        v-if="title"
                        class="text-2xl font-semibold tracking-tight sm:text-[1.7rem]"
                    >
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="text-sm leading-6 text-muted-foreground"
                    >
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </section>
    </main>
</template>
