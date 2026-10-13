<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, MapPin, Menu, Phone, X } from '@lucide/vue';
import { ref } from 'vue';
import BrandLockup from '@/components/public/BrandLockup.vue';
import { contactEmail, navigation } from '@/lib/publicSite';

const page = usePage();
const menuOpen = ref(false);

function isActive(href: string): boolean {
    return href === '/' ? page.url === '/' : page.url.startsWith(href);
}
</script>

<template>
    <div class="theme-light min-h-screen bg-background text-foreground">
        <a
            href="#main-content"
            class="sr-only rounded-md bg-white px-4 py-2.5 text-sm font-semibold text-navy-900 shadow-float focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:outline-2 focus:outline-aqua-600"
            >Skip to content</a
        >

        <div class="bg-aqua-50 text-navy-900">
            <div
                class="site-container flex items-center justify-between gap-4 py-2.5 text-xs font-semibold"
            >
                <span class="inline-flex items-center gap-2">
                    <MapPin :size="14" aria-hidden="true" />
                    Ebute-Metta, Lagos, Nigeria
                </span>
                <div class="hidden items-center gap-3 sm:flex">
                    <Phone :size="14" aria-hidden="true" />
                    <a class="hover:underline" href="tel:+2348140824230"
                        >+234 814 082 4230</a
                    >
                    <span aria-hidden="true" class="text-navy-400">|</span>
                    <a class="hover:underline" href="tel:+2348148484955"
                        >+234 814 848 4955</a
                    >
                </div>
                <a
                    class="inline-flex min-h-6 min-w-6 items-center justify-center sm:hidden"
                    href="tel:+2348140824230"
                    aria-label="Call ALCO on +234 814 082 4230"
                >
                    <Phone :size="16" aria-hidden="true" />
                </a>
            </div>
        </div>

        <div class="bg-navy-900 text-white">
            <header class="relative z-30 site-container">
                <div
                    class="flex min-h-20 items-center justify-between gap-5 border-b border-white/15 lg:min-h-24"
                >
                    <Link
                        href="/"
                        class="shrink-0 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-aqua-400"
                        aria-label="ALCO Associate Solutions — Home"
                        @click="menuOpen = false"
                    >
                        <BrandLockup size="lg" />
                    </Link>

                    <nav
                        class="hidden items-center gap-7 lg:flex xl:gap-9"
                        aria-label="Primary navigation"
                    >
                        <Link
                            v-for="item in navigation"
                            :key="item.href"
                            :href="item.href"
                            class="relative py-3 text-sm font-medium transition-colors hover:text-aqua-400 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aqua-400"
                            :class="
                                isActive(item.href)
                                    ? 'text-aqua-400'
                                    : 'text-white/90'
                            "
                            :aria-current="
                                isActive(item.href) ? 'page' : undefined
                            "
                        >
                            {{ item.label }}
                            <span
                                v-if="isActive(item.href)"
                                class="absolute right-0 bottom-0 left-0 h-0.5 bg-aqua-400"
                                aria-hidden="true"
                            />
                        </Link>
                    </nav>

                    <Link
                        href="/book-consultation"
                        class="hidden shrink-0 items-center gap-2 rounded-md border border-white/85 px-5 py-3 text-sm font-semibold transition-colors hover:bg-white hover:text-navy-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-aqua-400 lg:inline-flex"
                    >
                        Let's Talk
                        <ArrowUpRight :size="16" aria-hidden="true" />
                    </Link>

                    <button
                        type="button"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md border border-white/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aqua-400 lg:hidden"
                        :aria-expanded="menuOpen"
                        aria-controls="mobile-navigation"
                        :aria-label="
                            menuOpen ? 'Close navigation' : 'Open navigation'
                        "
                        @click="menuOpen = !menuOpen"
                    >
                        <X v-if="menuOpen" :size="22" aria-hidden="true" />
                        <Menu v-else :size="22" aria-hidden="true" />
                    </button>
                </div>

                <nav
                    v-if="menuOpen"
                    id="mobile-navigation"
                    class="grid gap-1 border-b border-white/15 py-3 lg:hidden"
                    aria-label="Mobile navigation"
                >
                    <Link
                        v-for="item in navigation"
                        :key="item.href"
                        :href="item.href"
                        class="rounded-md px-3 py-3 text-sm font-medium hover:bg-white/10"
                        :class="
                            isActive(item.href) ? 'text-aqua-400' : 'text-white'
                        "
                        :aria-current="isActive(item.href) ? 'page' : undefined"
                        @click="menuOpen = false"
                    >
                        {{ item.label }}
                    </Link>
                    <Link
                        href="/book-consultation"
                        class="mt-2 rounded-md bg-aqua-400 px-3 py-3 text-center text-sm font-bold text-navy-900 hover:bg-aqua-300"
                        @click="menuOpen = false"
                    >
                        Book a Consultation
                    </Link>
                </nav>
            </header>
        </div>

        <main id="main-content" tabindex="-1" class="focus:outline-none">
            <slot />
        </main>

        <footer class="bg-navy-950 text-white/75">
            <div
                class="site-container grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-[1.2fr_1fr_1fr]"
            >
                <div>
                    <BrandLockup class="text-white" />
                    <p class="mt-5 max-w-xs text-sm leading-6">
                        Professional Accounting Services. Bookkeeping, financial
                        reporting, tax filings and business solutions.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-white">Explore</p>
                    <div class="mt-4 grid grid-cols-2 gap-x-5 gap-y-3 text-sm">
                        <Link
                            v-for="item in navigation"
                            :key="item.href"
                            :href="item.href"
                            class="w-fit hover:text-aqua-400"
                        >
                            {{ item.label }}
                        </Link>
                    </div>
                </div>
                <div>
                    <p class="font-semibold text-white">Visit or contact</p>
                    <address class="mt-4 text-sm leading-6 not-italic">
                        18, Lagos Street, Ebute-Metta,<br />Lagos State, Nigeria
                    </address>
                    <div class="mt-3 grid gap-1 text-sm">
                        <a
                            :href="`mailto:${contactEmail}`"
                            class="w-fit hover:text-aqua-400"
                            >{{ contactEmail }}</a
                        >
                        <a
                            href="tel:+2348140824230"
                            class="w-fit hover:text-aqua-400"
                            >+234 814 082 4230</a
                        >
                        <a
                            href="tel:+2348148484955"
                            class="w-fit hover:text-aqua-400"
                            >+234 814 848 4955</a
                        >
                    </div>
                </div>
            </div>
            <div class="border-t border-white/10">
                <p class="site-container py-5 text-xs text-white/60">
                    © {{ new Date().getFullYear() }} ALCO Associate Solutions
                </p>
            </div>
        </footer>
    </div>
</template>
