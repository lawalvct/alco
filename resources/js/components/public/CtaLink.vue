<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';

type Variant = 'primary' | 'accent' | 'inverse' | 'link' | 'link-inverse';

const props = withDefaults(
    defineProps<{
        href: string;
        variant?: Variant;
        size?: 'md' | 'lg';
        icon?: 'arrow-up-right' | 'arrow-right' | 'none';
    }>(),
    { variant: 'primary', size: 'md', icon: 'arrow-up-right' },
);

const variants: Record<Variant, string> = {
    // Navy button for light sections.
    primary:
        'bg-navy-900 text-white hover:bg-navy-700 focus-visible:outline-aqua-600',
    // Aqua button for navy sections.
    accent: 'bg-aqua-400 font-bold text-navy-900 hover:bg-aqua-300 focus-visible:outline-white',
    // Outline button for navy sections.
    inverse:
        'border border-white/85 text-white hover:bg-white hover:text-navy-900 focus-visible:outline-aqua-400',
    link: 'text-aqua-700 underline-offset-4 hover:underline focus-visible:outline-aqua-600',
    'link-inverse':
        'text-aqua-400 underline-offset-4 hover:underline focus-visible:outline-aqua-400',
};

const isButton = computed(() => !props.variant.startsWith('link'));
const iconComponent = computed(() =>
    props.icon === 'arrow-right'
        ? ArrowRight
        : props.icon === 'arrow-up-right'
          ? ArrowUpRight
          : null,
);
</script>

<template>
    <Link
        :href="href"
        class="inline-flex items-center gap-2.5 text-sm font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-4"
        :class="[
            variants[variant],
            isButton && 'justify-center rounded-md',
            isButton &&
                (size === 'lg' ? 'min-h-13 px-7 py-3.5' : 'min-h-12 px-6 py-3'),
            !isButton && 'rounded-sm',
        ]"
    >
        <slot />
        <component
            :is="iconComponent"
            v-if="iconComponent"
            :size="isButton ? 18 : 17"
            aria-hidden="true"
        />
    </Link>
</template>
