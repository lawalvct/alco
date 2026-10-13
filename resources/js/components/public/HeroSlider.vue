<script setup lang="ts">
import { ChevronLeft, ChevronRight, Pause, Play } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type {
    HeroSlide,
    HeroSliderSettings,
    ImageSource,
} from '@/types/publicContent';

const props = defineProps<{
    slides: HeroSlide[];
    settings: HeroSliderSettings;
    fallback: ImageSource & { alt: string };
}>();

const PAUSE_KEY = 'alco-hero-paused';

const active = ref(0);
// Only the first slide loads on the server render; the rest load on demand.
const loaded = ref(new Set<number>([0]));
const reducedMotion = ref(true);
const pausedForSession = ref(false);
const hovering = ref(false);
const focusWithin = ref(false);
let timer: ReturnType<typeof setTimeout> | undefined;

const count = computed(() => props.slides.length);
const canAutoplay = computed(
    () => props.settings.autoplay && !reducedMotion.value && count.value > 1,
);
// Hover and keyboard focus pause only while they last; the pause button,
// arrows and dots pause for the rest of the visit (WAI-ARIA carousel pattern).
const rotating = computed(
    () =>
        canAutoplay.value &&
        !pausedForSession.value &&
        !hovering.value &&
        !focusWithin.value,
);

function load(index: number): void {
    if (!loaded.value.has(index)) {
        loaded.value = new Set([...loaded.value, index]);
    }
}

function show(index: number): void {
    active.value = (index + count.value) % count.value;
    load(active.value);
    load((active.value + 1) % count.value);
}

function pauseForSession(): void {
    pausedForSession.value = true;

    try {
        sessionStorage.setItem(PAUSE_KEY, '1');
    } catch {
        // Storage can be unavailable (private mode); pausing still works.
    }
}

function select(index: number): void {
    pauseForSession();
    show(index);
}

function togglePlayback(): void {
    if (pausedForSession.value) {
        pausedForSession.value = false;

        try {
            sessionStorage.removeItem(PAUSE_KEY);
        } catch {
            // Ignore unavailable storage.
        }
    } else {
        pauseForSession();
    }
}

function onFocusOut(event: FocusEvent): void {
    const next = event.relatedTarget;

    focusWithin.value =
        next instanceof Node &&
        (event.currentTarget as HTMLElement).contains(next);
}

function schedule(): void {
    clearTimeout(timer);

    if (rotating.value) {
        timer = setTimeout(
            () => show(active.value + 1),
            props.settings.interval_seconds * 1000,
        );
    }
}

watch([rotating, active], schedule);

onMounted(() => {
    reducedMotion.value = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    try {
        pausedForSession.value = sessionStorage.getItem(PAUSE_KEY) === '1';
    } catch {
        pausedForSession.value = false;
    }

    if (count.value > 1) {
        // Prefetch the next slide at low priority once the page has settled.
        setTimeout(() => load(1), 1500);
    }

    schedule();
});

onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
    <img
        v-if="count === 0"
        :src="fallback.src"
        :alt="fallback.alt"
        :width="fallback.width ?? undefined"
        :height="fallback.height ?? undefined"
        fetchpriority="high"
        class="h-61.25 w-full rounded-xl object-cover object-center sm:h-87.5 lg:h-107.5"
    />

    <section
        v-else
        aria-roledescription="carousel"
        aria-label="ALCO highlights"
        class="relative"
        @mouseenter="hovering = true"
        @mouseleave="hovering = false"
        @focusin="focusWithin = true"
        @focusout="onFocusOut"
    >
        <div
            class="relative h-61.25 overflow-hidden rounded-xl bg-navy-800 sm:h-87.5 lg:h-107.5"
            :aria-live="rotating ? 'off' : 'polite'"
        >
            <div
                v-for="(slide, index) in slides"
                :key="slide.id"
                role="group"
                aria-roledescription="slide"
                :aria-label="`${index + 1} of ${count}`"
                :aria-hidden="index !== active"
                class="absolute inset-0 transition-opacity duration-700 motion-reduce:transition-none"
                :class="index === active ? 'opacity-100' : 'opacity-0'"
            >
                <picture v-if="loaded.has(index)">
                    <source
                        v-if="slide.mobile"
                        media="(max-width: 639px)"
                        :srcset="slide.mobile.src"
                        :width="slide.mobile.width ?? undefined"
                        :height="slide.mobile.height ?? undefined"
                    />
                    <img
                        :src="slide.src"
                        :alt="slide.alt"
                        :width="slide.width ?? undefined"
                        :height="slide.height ?? undefined"
                        :loading="index === 0 ? 'eager' : 'lazy'"
                        :fetchpriority="index === 0 ? 'high' : 'low'"
                        decoding="async"
                        class="h-full w-full object-cover [object-position:var(--mobile-position)] sm:[object-position:var(--position)]"
                        :style="{
                            '--position': slide.position,
                            '--mobile-position': slide.mobile_position,
                        }"
                    />
                </picture>
            </div>
        </div>

        <div
            v-if="count > 1"
            class="absolute top-3 right-3 flex items-center gap-1 rounded-full bg-navy-950/75 p-1 text-white backdrop-blur-sm sm:top-4 sm:right-4"
        >
            <button
                type="button"
                class="inline-flex size-9 items-center justify-center rounded-full hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-aqua-400"
                aria-label="Previous slide"
                @click="select(active - 1)"
            >
                <ChevronLeft :size="18" aria-hidden="true" />
            </button>
            <div class="flex items-center">
                <button
                    v-for="(slide, index) in slides"
                    :key="slide.id"
                    type="button"
                    class="group inline-flex size-6 items-center justify-center rounded-full focus-visible:outline-2 focus-visible:outline-aqua-400"
                    :aria-label="`Show slide ${index + 1}`"
                    :aria-current="index === active ? 'true' : undefined"
                    @click="select(index)"
                >
                    <span
                        class="block h-2 rounded-full transition-all motion-reduce:transition-none"
                        :class="
                            index === active
                                ? 'w-5 bg-aqua-400'
                                : 'w-2 bg-white/60 group-hover:bg-white'
                        "
                        aria-hidden="true"
                    />
                </button>
            </div>
            <button
                type="button"
                class="inline-flex size-9 items-center justify-center rounded-full hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-aqua-400"
                aria-label="Next slide"
                @click="select(active + 1)"
            >
                <ChevronRight :size="18" aria-hidden="true" />
            </button>
            <button
                v-if="canAutoplay"
                type="button"
                class="inline-flex size-9 items-center justify-center rounded-full hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-aqua-400"
                :aria-label="
                    pausedForSession
                        ? 'Start slide rotation'
                        : 'Stop slide rotation'
                "
                @click="togglePlayback"
            >
                <Play v-if="pausedForSession" :size="16" aria-hidden="true" />
                <Pause v-else :size="16" aria-hidden="true" />
            </button>
        </div>
    </section>
</template>
