<script setup lang="ts">
import { MapPin } from '@lucide/vue';
import { useSite } from '@/composables/useSite';

const site = useSite();
</script>

<template>
    <div class="rounded-xl bg-aqua-50 p-8">
        <MapPin :size="26" class="text-aqua-600" aria-hidden="true" />
        <h2 class="mt-5 text-xl font-semibold">Visit us</h2>
        <address
            class="mt-3 text-base leading-7 text-muted-foreground not-italic"
        >
            {{ site.address.street }},<br />{{ site.address.locality }},
            {{ site.address.region }},<br />{{ site.address.country }}
        </address>
    </div>
</template>
