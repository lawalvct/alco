<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import FormField from '@/components/public/FormField.vue';
import TurnstileWidget from '@/components/public/TurnstileWidget.vue';
import type { PackageSummary, ServiceOption } from '@/types/publicContent';
import type { RouteFormDefinition } from '@/wayfinder';

const props = withDefaults(
    defineProps<{
        submit: RouteFormDefinition<'post'>;
        variant: 'contact' | 'consultation';
        services: ServiceOption[];
        consentText: string;
        turnstileSiteKey?: string | null;
        packages?: PackageSummary[];
        contactMethods?: Record<string, string>;
        submitLabel?: string;
    }>(),
    {
        turnstileSiteKey: null,
        packages: () => [],
        contactMethods: () => ({}),
        submitLabel: 'Send message',
    },
);

/** Two-way bound so the page can show a summary of the chosen package. */
const chosenPackage = defineModel<string | null>('package', { default: null });

const summary = ref<HTMLElement | null>(null);

const fieldClass =
    'block w-full rounded-md border border-input bg-white px-3.5 py-2.5 text-base text-foreground placeholder:text-navy-400 focus-visible:border-aqua-600 focus-visible:ring-3 focus-visible:ring-aqua-600/25 focus-visible:outline-none aria-invalid:border-destructive';

async function focusSummary(): Promise<void> {
    await nextTick();
    summary.value?.focus();
}

function errorCount(errors: Record<string, string | undefined>): number {
    return Object.keys(errors).filter((key) => key !== 'form').length;
}
</script>

<template>
    <Form
        v-bind="props.submit"
        v-slot="{ errors, processing }"
        :options="{ preserveScroll: true }"
        novalidate
        class="grid gap-6 sm:grid-cols-2"
        @error="focusSummary"
    >
        <div
            v-if="errors.form || errorCount(errors)"
            ref="summary"
            role="alert"
            tabindex="-1"
            class="rounded-lg border border-destructive/40 bg-destructive/5 p-4 text-sm leading-6 text-destructive focus:outline-none sm:col-span-2"
        >
            <p v-if="errors.form" class="font-semibold">{{ errors.form }}</p>
            <p v-if="errorCount(errors)">
                Please check the
                {{
                    errorCount(errors) === 1
                        ? 'highlighted field'
                        : `${errorCount(errors)} highlighted fields`
                }}
                below.
            </p>
        </div>

        <FormField
            id="name"
            v-slot="{ describedBy, invalid }"
            label="Your name"
            :error="errors.name"
            class="sm:col-span-2"
        >
            <input
                id="name"
                name="name"
                type="text"
                autocomplete="name"
                aria-required="true"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy"
                :class="fieldClass"
            />
        </FormField>

        <FormField
            id="email"
            v-slot="{ describedBy, invalid }"
            label="Email"
            hint="Give an email address, a phone number or both."
            :error="errors.email"
        >
            <input
                id="email"
                name="email"
                type="email"
                autocomplete="email"
                inputmode="email"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy"
                :class="fieldClass"
            />
        </FormField>

        <FormField
            id="phone"
            v-slot="{ describedBy, invalid }"
            label="Phone"
            hint="Include +234 or your country code."
            :error="errors.phone"
        >
            <input
                id="phone"
                name="phone"
                type="tel"
                autocomplete="tel"
                inputmode="tel"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy"
                :class="fieldClass"
            />
        </FormField>

        <FormField
            id="service_id"
            v-slot="{ describedBy, invalid }"
            label="Service you are interested in"
            optional
            :error="errors.service_id"
            class="sm:col-span-2"
        >
            <select
                id="service_id"
                name="service_id"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy"
                :class="fieldClass"
            >
                <option value="">Not sure, or something else</option>
                <option
                    v-for="service in services"
                    :key="service.id"
                    :value="service.id"
                >
                    {{ service.title }}
                </option>
            </select>
        </FormField>

        <template v-if="variant === 'consultation'">
            <fieldset
                v-if="packages.length"
                class="grid gap-3 sm:col-span-2"
                :aria-describedby="
                    errors.package ? 'package-error' : 'package-hint'
                "
            >
                <legend class="text-sm font-semibold">
                    Preferred package
                    <span class="font-normal text-muted-foreground"
                        >(optional)</span
                    >
                </legend>
                <p id="package-hint" class="text-sm text-muted-foreground">
                    A starting point only. We confirm suitability, scope and
                    terms with you before any work begins.
                </p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label
                        v-for="pkg in packages"
                        :key="pkg.slug"
                        class="flex cursor-pointer gap-3 rounded-lg border border-border bg-white p-4 transition-colors focus-within:ring-3 focus-within:ring-aqua-600/25 hover:border-aqua-400 has-checked:border-aqua-600 has-checked:bg-aqua-50"
                    >
                        <input
                            v-model="chosenPackage"
                            type="radio"
                            name="package"
                            :value="pkg.slug"
                            class="mt-1 size-4 shrink-0 accent-navy-900"
                        />
                        <span>
                            <span class="block font-semibold">{{
                                pkg.name
                            }}</span>
                            <span
                                class="block text-sm leading-6 text-muted-foreground"
                                >{{ pkg.summary }}</span
                            >
                        </span>
                    </label>
                    <label
                        class="flex cursor-pointer gap-3 rounded-lg border border-border bg-white p-4 transition-colors focus-within:ring-3 focus-within:ring-aqua-600/25 hover:border-aqua-400 has-checked:border-aqua-600 has-checked:bg-aqua-50"
                    >
                        <input
                            v-model="chosenPackage"
                            type="radio"
                            name="package"
                            value="tailored"
                            class="mt-1 size-4 shrink-0 accent-navy-900"
                        />
                        <span>
                            <span class="block font-semibold"
                                >Not sure yet</span
                            >
                            <span
                                class="block text-sm leading-6 text-muted-foreground"
                                >Tell us what you need and we will suggest a
                                tailored engagement.</span
                            >
                        </span>
                    </label>
                </div>
                <p
                    v-if="errors.package"
                    id="package-error"
                    class="text-sm font-medium text-destructive"
                >
                    {{ errors.package }}
                </p>
            </fieldset>

            <FormField
                id="preferred_contact_method"
                v-slot="{ describedBy, invalid }"
                label="How should we contact you?"
                optional
                :error="errors.preferred_contact_method"
            >
                <select
                    id="preferred_contact_method"
                    name="preferred_contact_method"
                    :aria-invalid="invalid || undefined"
                    :aria-describedby="describedBy"
                    :class="fieldClass"
                >
                    <option value="">No preference</option>
                    <option
                        v-for="(label, value) in contactMethods"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
            </FormField>

            <FormField
                id="availability"
                v-slot="{ describedBy, invalid }"
                label="When are you usually available?"
                optional
                :error="errors.availability"
            >
                <input
                    id="availability"
                    name="availability"
                    type="text"
                    maxlength="200"
                    placeholder="For example: weekday mornings"
                    :aria-invalid="invalid || undefined"
                    :aria-describedby="describedBy"
                    :class="fieldClass"
                />
            </FormField>
        </template>

        <FormField
            id="message"
            v-slot="{ describedBy, invalid }"
            :label="
                variant === 'contact'
                    ? 'How can we help?'
                    : 'Anything we should know before we talk?'
            "
            :optional="variant === 'consultation'"
            hint="Please do not include bank details or confidential financial records."
            :error="errors.message"
            class="sm:col-span-2"
        >
            <textarea
                id="message"
                name="message"
                rows="5"
                maxlength="2000"
                :aria-required="variant === 'contact' || undefined"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy"
                :class="fieldClass"
            />
        </FormField>

        <!-- Bots fill in every field; people never see this one. -->
        <div
            class="absolute left-[-9999px] h-px w-px overflow-hidden"
            aria-hidden="true"
        >
            <label for="website">Leave this field empty</label>
            <input
                id="website"
                type="text"
                name="website"
                tabindex="-1"
                autocomplete="off"
            />
        </div>

        <div class="grid gap-1.5 sm:col-span-2">
            <label class="flex items-start gap-3 text-sm leading-6">
                <input
                    type="checkbox"
                    name="consent"
                    value="1"
                    class="mt-1 size-4 shrink-0 accent-navy-900"
                    aria-required="true"
                    :aria-invalid="Boolean(errors.consent) || undefined"
                    :aria-describedby="
                        errors.consent ? 'consent-error' : undefined
                    "
                />
                <span>{{ consentText }}</span>
            </label>
            <p
                v-if="errors.consent"
                id="consent-error"
                class="text-sm font-medium text-destructive"
            >
                {{ errors.consent }}
            </p>
        </div>

        <div v-if="turnstileSiteKey" class="grid gap-1.5 sm:col-span-2">
            <TurnstileWidget :site-key="turnstileSiteKey" />
            <p
                v-if="errors['cf-turnstile-response']"
                class="text-sm font-medium text-destructive"
            >
                {{ errors['cf-turnstile-response'] }}
            </p>
        </div>

        <div class="sm:col-span-2">
            <button
                type="submit"
                :disabled="processing"
                class="inline-flex min-h-12 w-full items-center justify-center rounded-md bg-navy-900 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-navy-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-aqua-600 disabled:cursor-wait disabled:opacity-70 sm:w-auto"
            >
                {{ processing ? 'Sending…' : submitLabel }}
            </button>
        </div>
    </Form>
</template>
