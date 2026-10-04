<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

const services = [
    'Bookkeeping',
    'Financial Reporting',
    'Tax Filings',
    'Business Solutions',
];
</script>

<template>
    <Head title="Home">
        <meta
            name="description"
            content="ALCO Associate Solutions provides professional accounting services in Lagos, Nigeria."
        />
    </Head>

    <div class="min-h-screen bg-[#f8f7f3] text-[#183354]">
        <header class="border-b border-[#183354]/10 bg-white">
            <div
                class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-6 py-5"
            >
                <img
                    src="/brand/alco-logo-original.jpeg"
                    alt="ALCO"
                    class="h-16 w-auto object-contain"
                />
                <a
                    class="text-sm font-semibold underline-offset-4 hover:underline"
                    href="tel:+2348140824230"
                    >+234 814 082 4230</a
                >
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-20 sm:py-28">
            <p class="mb-5 text-sm font-bold tracking-[0.18em] uppercase">
                ALCO Associate Solutions
            </p>
            <h1
                class="max-w-4xl text-4xl leading-tight font-semibold tracking-tight sm:text-6xl"
            >
                Professional Accounting Services.
            </h1>
            <p class="mt-7 max-w-2xl text-lg leading-relaxed text-[#45566b]">
                Bookkeeping, financial reporting, tax filings and practical
                business solutions.
            </p>
            <div class="mt-10 flex flex-wrap gap-3" aria-label="Our services">
                <span
                    v-for="service in services"
                    :key="service"
                    class="rounded-full border border-[#183354]/20 bg-white px-4 py-2 text-sm"
                    >{{ service }}</span
                >
            </div>
            <p
                class="mt-14 max-w-2xl border-t border-[#183354]/15 pt-6 text-sm leading-relaxed text-[#45566b]"
            >
                This is the project foundation. The final homepage design will
                follow the client's selection.
            </p>
        </main>

        <footer
            class="border-t border-[#183354]/10 px-6 py-7 text-center text-sm text-[#45566b]"
        >
            18, Lagos Street, Ebute-Metta, Lagos State, Nigeria
        </footer>
    </div>
</template>
