import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ComputedRef } from 'vue';
import type { SiteInfo } from '@/types/publicContent';

/** Business details managed in Admin → Site settings. */
export function useSite(): ComputedRef<SiteInfo> {
    const page = usePage();

    return computed(() => page.props.site);
}
