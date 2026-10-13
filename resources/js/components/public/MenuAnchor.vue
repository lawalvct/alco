<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { MenuLink } from '@/types/publicContent';

defineProps<{ link: MenuLink }>();
</script>

<template>
    <a v-if="link.external" :href="link.href" target="_blank" rel="noopener"
        ><slot>{{ link.label }}</slot
        ><span class="sr-only"> (opens in a new tab)</span></a
    >
    <Link v-else :href="link.href"
        ><slot>{{ link.label }}</slot></Link
    >
</template>
