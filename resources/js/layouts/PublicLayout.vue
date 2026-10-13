<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, MapPin, Menu, Phone, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import BrandLockup from '@/components/public/BrandLockup.vue';
import MenuAnchor from '@/components/public/MenuAnchor.vue';
import SocialIcon from '@/components/public/SocialIcon.vue';
import { useSite } from '@/composables/useSite';
import type { MenuLink } from '@/types/publicContent';

const page = usePage();
const site = useSite();
const headerLinks = computed(() => page.props.navigation.header);
const footerLinks = computed(() => page.props.navigation.footer);
const legalLinks = computed(() => page.props.legalLinks);
const menuOpen = ref(false);
const whatsappPhones = computed(() =>
    site.value.phones.filter((phone) => phone.whatsapp),
);

function isActive(link: MenuLink): boolean {
    if (link.external) {
        return false;
    }

    return link.href === '/'
        ? page.url === '/'
        : page.url.startsWith(link.href);
}
</script>

<template>
    <Head>
        <link
            head-key="canonical"
            rel="canonical"
            :href="page.props.canonical"
        />
    </Head>

    <div
        class="theme-light min-h-screen bg-background text-foreground print:min-h-0"
    >
        <a
            href="#main-content"
            class="sr-only rounded-md bg-white px-4 py-2.5 text-sm font-semibold text-navy-900 shadow-float focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:outline-2 focus:outline-aqua-600"
            >Skip to content</a
        >

        <div class="bg-aqua-50 text-navy-900 print:hidden">
            <div
                class="site-container flex items-center justify-between gap-3 py-2.5 text-xs font-semibold"
            >
                <span class="inline-flex min-w-0 items-center gap-2">
                    <MapPin class="shrink-0" :size="14" aria-hidden="true" />
                    <span class="truncate">{{ site.address.summary }}</span>
                </span>
                <div class="flex shrink-0 items-center gap-2 sm:gap-4">
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
                            <a
                                class="hover:underline"
                                :href="`tel:${phone.tel}`"
                                >{{ phone.display }}</a
                            >
                        </template>
                    </div>
                    <a
                        v-if="site.phones[0]"
                        class="inline-flex min-h-8 min-w-8 items-center justify-center rounded-full hover:bg-aqua-100 sm:hidden"
                        :href="`tel:${site.phones[0].tel}`"
                        :aria-label="`Call ALCO on ${site.phones[0].display}`"
                    >
                        <Phone :size="16" aria-hidden="true" />
                    </a>
                    <nav
                        v-if="site.social.length"
                        class="flex items-center gap-1"
                        aria-label="ALCO social media"
                    >
                        <a
                            v-for="link in site.social"
                            :key="link.url"
                            :href="link.url"
                            class="inline-flex min-h-8 min-w-8 items-center justify-center rounded-full text-navy-900 transition-colors hover:bg-aqua-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aqua-700"
                            :aria-label="`Visit ALCO on ${link.label}`"
                            :title="link.label"
                            rel="noopener noreferrer"
                            target="_blank"
                        >
                            <SocialIcon :network="link.label" :size="18" />
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <div class="bg-navy-900 text-white print:hidden">
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
                        <MenuAnchor
                            v-for="item in headerLinks"
                            :key="`${item.label}-${item.href}`"
                            :link="item"
                            class="relative py-3 text-sm font-medium transition-colors hover:text-aqua-400 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aqua-400"
                            :class="
                                isActive(item)
                                    ? 'text-aqua-400'
                                    : 'text-white/90'
                            "
                            :aria-current="isActive(item) ? 'page' : undefined"
                        >
                            {{ item.label }}
                            <span
                                v-if="isActive(item)"
                                class="absolute right-0 bottom-0 left-0 h-0.5 bg-aqua-400"
                                aria-hidden="true"
                            />
                        </MenuAnchor>
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
                    <MenuAnchor
                        v-for="item in headerLinks"
                        :key="`${item.label}-${item.href}`"
                        :link="item"
                        class="rounded-md px-3 py-3 text-sm font-medium hover:bg-white/10"
                        :class="isActive(item) ? 'text-aqua-400' : 'text-white'"
                        :aria-current="isActive(item) ? 'page' : undefined"
                        @click="menuOpen = false"
                    >
                        {{ item.label }}
                    </MenuAnchor>
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

        <footer class="bg-navy-950 text-white/75 print:hidden">
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
                        <MenuAnchor
                            v-for="item in footerLinks"
                            :key="`${item.label}-${item.href}`"
                            :link="item"
                            class="w-fit hover:text-aqua-400"
                        />
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
                            v-for="email in site.emails"
                            :key="email"
                            :href="`mailto:${email}`"
                            class="w-fit break-all hover:text-aqua-400"
                            >{{ email }}</a
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
                <div
                    class="site-container flex flex-col gap-3 py-5 text-xs text-white/60 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p>© {{ new Date().getFullYear() }} {{ site.name }}</p>
                    <ul
                        v-if="legalLinks.length"
                        class="flex flex-wrap gap-x-5 gap-y-2"
                    >
                        <li v-for="link in legalLinks" :key="link.href">
                            <Link
                                :href="link.href"
                                class="hover:text-aqua-400"
                                >{{ link.label }}</Link
                            >
                        </li>
                    </ul>
                </div>
            </div>
        </footer>
    </div>
</template>
