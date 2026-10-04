<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, MapPin, Menu, Phone, X } from '@lucide/vue';
import { ref } from 'vue';
import { navigation } from '@/lib/publicSite';

const page = usePage();
const menuOpen = ref(false);

function isActive(href: string): boolean {
    return href === '/' ? page.url === '/' : page.url.startsWith(href);
}
</script>

<template>
    <div class="min-h-screen bg-[#f6f8fa] text-[#0b2b4c]">
        <div class="bg-[#eaf6fb] text-[#0b2b4c]">
            <div
                class="mx-auto flex max-w-[1512px] items-center justify-between gap-4 px-5 py-2.5 text-xs font-semibold sm:px-8 lg:px-12"
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
                    <span aria-hidden="true" class="text-[#8198a8]">|</span>
                    <a class="hover:underline" href="tel:+2348148484955"
                        >+234 814 848 4955</a
                    >
                </div>
                <a
                    class="sm:hidden"
                    href="tel:+2348140824230"
                    aria-label="Call ALCO"
                >
                    <Phone :size="16" aria-hidden="true" />
                </a>
            </div>
        </div>

        <div class="bg-[#062849] text-white">
            <header
                class="relative z-30 mx-auto max-w-[1512px] px-5 sm:px-8 lg:px-12"
            >
                <div
                    class="flex min-h-20 items-center justify-between gap-5 border-b border-white/15 lg:min-h-24"
                >
                    <Link
                        href="/"
                        class="flex shrink-0 items-center gap-2.5 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#53d7ef]"
                        aria-label="ALCO Associate Solutions — Home"
                        @click="menuOpen = false"
                    >
                        <img
                            src="/brand/alco-mark-white.svg"
                            alt=""
                            width="58"
                            height="55"
                            class="h-12 w-[52px] shrink-0 sm:h-14 sm:w-[59px]"
                        />
                        <span
                            class="text-[1.7rem] font-semibold tracking-[0.09em] sm:text-[2rem]"
                            >ALCO</span
                        >
                        <span
                            class="ml-1 hidden border-l border-white/40 pl-3 text-xs leading-[1.1] font-medium tracking-[0.02em] sm:block"
                            >Associate<br />Solutions</span
                        >
                    </Link>

                    <nav
                        class="hidden items-center gap-7 lg:flex xl:gap-9"
                        aria-label="Primary navigation"
                    >
                        <Link
                            v-for="item in navigation"
                            :key="item.href"
                            :href="item.href"
                            class="relative py-3 text-sm font-medium transition-colors hover:text-[#58d8ef] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#58d8ef]"
                            :class="
                                isActive(item.href)
                                    ? 'text-[#58d8ef]'
                                    : 'text-white/90'
                            "
                            :aria-current="
                                isActive(item.href) ? 'page' : undefined
                            "
                        >
                            {{ item.label }}
                            <span
                                v-if="isActive(item.href)"
                                class="absolute right-0 bottom-0 left-0 h-0.5 bg-[#58d8ef]"
                                aria-hidden="true"
                            />
                        </Link>
                    </nav>

                    <Link
                        href="/book-consultation"
                        class="hidden shrink-0 items-center gap-2 rounded-md border border-white/85 px-5 py-3 text-sm font-semibold transition-colors hover:bg-white hover:text-[#062849] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#58d8ef] lg:inline-flex"
                    >
                        Let's Talk
                        <ArrowUpRight :size="16" aria-hidden="true" />
                    </Link>

                    <button
                        type="button"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md border border-white/50 lg:hidden"
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
                            isActive(item.href)
                                ? 'text-[#58d8ef]'
                                : 'text-white'
                        "
                        :aria-current="isActive(item.href) ? 'page' : undefined"
                        @click="menuOpen = false"
                    >
                        {{ item.label }}
                    </Link>
                    <Link
                        href="/book-consultation"
                        class="mt-2 rounded-md bg-[#58d8ef] px-3 py-3 text-center text-sm font-semibold text-[#062849]"
                        @click="menuOpen = false"
                    >
                        Book a Consultation
                    </Link>
                </nav>
            </header>
        </div>

        <slot />

        <footer class="bg-[#062849] text-white/80">
            <div
                class="mx-auto grid max-w-[1512px] gap-8 px-5 py-12 sm:px-8 md:grid-cols-2 lg:grid-cols-3 lg:px-12"
            >
                <div>
                    <p
                        class="text-xl font-semibold tracking-[0.08em] text-white"
                    >
                        ALCO
                    </p>
                    <p class="mt-2 text-sm">Associate Solutions</p>
                    <p class="mt-4 max-w-xs text-sm leading-6">
                        Professional Accounting Services.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-white">Explore</p>
                    <div class="mt-4 grid grid-cols-2 gap-x-5 gap-y-3 text-sm">
                        <Link
                            v-for="item in navigation"
                            :key="item.href"
                            :href="item.href"
                            class="hover:text-[#58d8ef]"
                        >
                            {{ item.label }}
                        </Link>
                    </div>
                </div>
                <div>
                    <p class="font-semibold text-white">Visit or call</p>
                    <address class="mt-4 text-sm leading-6 not-italic">
                        18, Lagos Street, Ebute-Metta,<br />Lagos State, Nigeria
                    </address>
                    <div class="mt-3 grid gap-1 text-sm">
                        <a
                            href="tel:+2348140824230"
                            class="hover:text-[#58d8ef]"
                            >+234 814 082 4230</a
                        >
                        <a
                            href="tel:+2348148484955"
                            class="hover:text-[#58d8ef]"
                            >+234 814 848 4955</a
                        >
                    </div>
                </div>
            </div>
            <div
                class="border-t border-white/15 py-5 text-center text-xs text-white/60"
            >
                © {{ new Date().getFullYear() }} ALCO Associate Solutions
            </div>
        </footer>
    </div>
</template>
