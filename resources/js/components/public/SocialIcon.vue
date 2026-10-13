<script setup lang="ts">
withDefaults(defineProps<{ network: string; size?: number }>(), { size: 18 });
</script>

<template>
    <svg
        v-if="network === 'Facebook'"
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="currentColor"
        aria-hidden="true"
        focusable="false"
    >
        <path
            d="M12 2C6.48 2 2 6.49 2 12.02c0 5 3.66 9.15 8.44 9.88v-6.99H7.9v-2.89h2.54V9.81c0-2.51 1.49-3.9 3.77-3.9 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.77l-.44 2.89h-2.33v6.99c4.78-.73 8.44-4.88 8.44-9.88C22 6.49 17.52 2 12 2"
        />
    </svg>
    <svg
        v-else-if="network === 'LinkedIn'"
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="currentColor"
        aria-hidden="true"
        focusable="false"
    >
        <path
            d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.13 1.44-2.13 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.78C.8 0 0 .77 0 1.72v20.56C0 23.23.8 24 1.78 24h20.44C23.2 24 24 23.23 24 22.28V1.72C24 .77 23.2 0 22.22 0"
        />
    </svg>
    <svg
        v-else-if="network === 'TikTok'"
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="currentColor"
        aria-hidden="true"
        focusable="false"
    >
        <path
            d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.11V2h-3.31v13.67a2.9 2.9 0 0 1-2.9 2.9A2.9 2.9 0 0 1 6.71 15.7a2.9 2.9 0 0 1 2.9-2.9c.3 0 .59.05.86.14V9.55a6.31 6.31 0 0 0-.86-.06 6.21 6.21 0 0 0-6.21 6.21 6.21 6.21 0 0 0 6.21 6.21 6.21 6.21 0 0 0 6.21-6.21V8.8a8.1 8.1 0 0 0 4.77 1.55V7.04a4.8 4.8 0 0 1-1-.35z"
        />
    </svg>
</template>
