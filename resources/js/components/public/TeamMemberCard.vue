<script setup lang="ts">
import { computed, ref } from 'vue';
import BrandPattern from '@/components/public/BrandPattern.vue';
import type { TeamProfile } from '@/types/publicContent';

const props = withDefaults(
    defineProps<{
        member: TeamProfile;
        /** Portrait above the text; used when three or more people share a row. */
        stacked?: boolean;
    }>(),
    { stacked: false },
);

/** Longer biographies start shortened, with a toggle to read the rest. */
const PREVIEW_LENGTH = 280;

const expanded = ref(false);
const bioId = computed(() => `team-bio-${props.member.id}`);
const isLong = computed(() => (props.member.bio?.length ?? 0) > PREVIEW_LENGTH);
const initials = computed(() =>
    props.member.name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase(),
);
</script>

<template>
    <article
        class="flex h-full flex-col overflow-hidden rounded-2xl border border-border bg-white shadow-card"
        :class="!stacked && 'sm:flex-row'"
    >
        <div
            class="relative isolate aspect-[4/3] shrink-0 overflow-hidden bg-navy-900"
            :class="!stacked && 'sm:aspect-auto sm:w-56 lg:w-60'"
        >
            <img
                v-if="member.portrait"
                :src="member.portrait.src"
                :srcset="member.portrait.srcset ?? undefined"
                :sizes="
                    stacked
                        ? '(min-width: 1024px) 26rem, 100vw'
                        : '(min-width: 640px) 15rem, 100vw'
                "
                alt=""
                :width="member.portrait.width ?? undefined"
                :height="member.portrait.height ?? undefined"
                loading="lazy"
                class="absolute inset-0 h-full w-full object-cover object-top"
            />
            <div
                v-else
                class="absolute inset-0 flex items-center justify-center"
                aria-hidden="true"
            >
                <BrandPattern
                    class="absolute -right-10 -bottom-16 -z-10 h-64 w-52 text-aqua-400/20"
                />
                <span
                    class="text-5xl font-semibold tracking-wider text-aqua-400"
                    >{{ initials }}</span
                >
            </div>
        </div>

        <div class="flex flex-1 flex-col p-6 sm:p-8">
            <h3 class="text-xl font-semibold tracking-tight">
                {{ member.name }}
            </h3>
            <p v-if="member.role" class="mt-2 eyebrow text-aqua-700">
                {{ member.role }}
            </p>
            <div v-if="member.bio" class="mt-5">
                <p
                    :id="bioId"
                    class="text-sm leading-7 whitespace-pre-line text-muted-foreground"
                    :class="isLong && !expanded && 'line-clamp-5'"
                >
                    {{ member.bio }}
                </p>
                <button
                    v-if="isLong"
                    type="button"
                    class="mt-3 rounded-sm text-sm font-semibold text-aqua-700 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-aqua-600"
                    :aria-expanded="expanded"
                    :aria-controls="bioId"
                    @click="expanded = !expanded"
                >
                    {{ expanded ? 'Show less' : 'Read full biography' }}
                </button>
            </div>
        </div>
    </article>
</template>
