<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 190 180"
        :class="className"
        v-bind="$attrs"
    >
        <path
            fill="currentColor"
            d="M12 164 91 13h13l68 129h-27L98 51 49 143h115l12 21H12Z"
        />
        <path
            fill="currentColor"
            d="m68 131 27-51h11l26 51h-25l-9-22-11 22H68Z"
        />
    </svg>
</template>
