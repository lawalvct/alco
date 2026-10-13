<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, MapPin, Menu, Phone, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import BrandLockup from '@/components/public/BrandLockup.vue';
import { useSite } from '@/composables/useSite';
import { navigation } from '@/lib/publicSite';

const page = usePage();
const site = useSite();
const menuOpen = ref(false);
const whatsappPhones = computed(() =>
    site.value.phones.filter((phone) => phone.whatsapp),
);

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
                    {{ site.address.summary }}
                </span>
                <div
                    v-if="site.phones.length"
                    class="hidden items-center gap-3 sm:flex"
                >
                    <Phone :size="14" aria-hidden="true" />
                    <template
                        v-for="(phone, index) in site.phones"
                        :key="phone.tel"
                    >
                        <span
                            v-if="index > 0"
                            aria-hidden="true"
                            class="text-navy-400"
                            >|</span
                        >
                        <a class="hover:underline" :href="`tel:${phone.tel}`">{{
                            phone.display
                        }}</a>
                    </template>
                </div>
                <a
                    v-if="site.phones[0]"
                    class="inline-flex min-h-6 min-w-6 items-center justify-center sm:hidden"
                    :href="`tel:${site.phones[0].tel}`"
                    :aria-label="`Call ALCO on ${site.phones[0].display}`"
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
                    <p
                        v-if="site.positioning"
                        class="mt-5 max-w-xs text-sm leading-6"
                    >
                        {{ site.positioning }}
                    </p>
                    <ul
                        v-if="site.social.length"
                        class="mt-5 flex flex-wrap gap-x-5 gap-y-2 text-sm"
                    >
                        <li v-for="link in site.social" :key="link.url">
                            <a
                                :href="link.url"
                                class="font-semibold text-white hover:text-aqua-400"
                                rel="noopener"
                                target="_blank"
                                >{{ link.label
                                }}<span class="sr-only">
                                    (opens in a new tab)</span
                                ></a
                            >
                        </li>
                    </ul>
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
                        {{ site.address.street }},
                        {{ site.address.locality }},<br />{{
                            site.address.region
                        }},
                        {{ site.address.country }}
                    </address>
                    <div class="mt-3 grid gap-1 text-sm">
                        <a
                            v-if="site.email"
                            :href="`mailto:${site.email}`"
                            class="w-fit hover:text-aqua-400"
                            >{{ site.email }}</a
                        >
                        <a
                            v-for="phone in site.phones"
                            :key="phone.tel"
                            :href="`tel:${phone.tel}`"
                            class="w-fit hover:text-aqua-400"
                            >{{ phone.display }}</a
                        >
                        <a
                            v-for="phone in whatsappPhones"
                            :key="`wa-${phone.tel}`"
                            :href="phone.whatsapp ?? undefined"
                            class="w-fit hover:text-aqua-400"
                            rel="noopener"
                            target="_blank"
                            >WhatsApp {{ phone.display
                            }}<span class="sr-only">
                                (opens in a new tab)</span
                            ></a
                        >
                    </div>
                </div>
            </div>
            <div class="border-t border-white/10">
                <p class="site-container py-5 text-xs text-white/60">
                    © {{ new Date().getFullYear() }} {{ site.name }}
                </p>
            </div>
        </footer>
    </div>
</template>
