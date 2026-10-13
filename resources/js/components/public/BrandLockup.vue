<script setup lang="ts">
import BrandMark from '@/components/public/BrandMark.vue';

withDefaults(defineProps<{ size?: 'sm' | 'lg' }>(), { size: 'sm' });
</script>

<template>
    <span class="inline-flex items-center gap-2.5">
        <BrandMark
            class="shrink-0"
            :class="
                size === 'lg'
                    ? 'h-12 w-13 sm:h-14 sm:w-[59px]'
                    : 'h-8 w-8 sm:h-10 sm:w-10'
            "
        />
        <span
            class="font-semibold"
            :class="
                size === 'lg'
                    ? 'text-[1.7rem] tracking-[0.09em] sm:text-[2rem]'
                    : 'text-lg tracking-[0.12em] sm:text-xl'
            "
            >ALCO</span
        >
        <span
            class="ml-1 border-l border-current/40 pl-3 text-xs leading-[1.1] font-medium tracking-[0.02em] opacity-85"
            :class="size === 'lg' ? 'hidden sm:block' : ''"
            >Associate<br />Solutions</span
        >
    </span>
</template>
