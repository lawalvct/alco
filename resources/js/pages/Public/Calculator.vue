<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowUpRight,
    Calculator,
    Info,
    Printer,
    RotateCcw,
} from '@lucide/vue';
import { computed, reactive, ref } from 'vue';
import ArticleCard from '@/components/public/ArticleCard.vue';
import PageHero from '@/components/public/PageHero.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { ArticleSummary } from '@/types/publicContent';

type ToolField = {
    name: string;
    label: string;
    type: 'number' | 'select' | 'date' | 'text';
    unit?: string;
    required: boolean;
    default?: string | null;
    options?: Record<string, string>;
};
type Metric = {
    label: string;
    value: string | null;
    unit: string;
    note: string | null;
};
type Calculation = {
    metrics: Metric[];
    assumptions: string[];
    schedule?: Record<string, string | number>[];
    currency: string;
    formula_version: string;
    config_version: number | null;
    source_url: string | null;
    rounding: string;
};

const props = defineProps<{
    tool: {
        slug: string;
        title: string;
        heading: string;
        summary: string;
        intro: string;
        currency: string;
        seoTitle: string;
        seoDescription: string;
    };
    definition: { description: string; formula: string; fields: ToolField[] };
    guide: {
        intro: string;
        steps: string[];
        example: string;
        faqs: { question: string; answer: string }[];
    };
    relatedTools: { slug: string; title: string; summary: string }[];
    relatedPosts: ArticleSummary[];
    today: string;
    approvedRules: {
        key: string;
        label: string;
        period: string;
        version: number;
    }[];
}>();

const initialValues = () =>
    Object.fromEntries(
        props.definition.fields.map((field) => [
            field.name,
            field.type === 'date' ? props.today : (field.default ?? ''),
        ]),
    );
const values = reactive<Record<string, string>>(initialValues());
const calculation = ref<Calculation | null>(null);
const calculatedInputs = ref<Record<string, string> | null>(null);
const calculatedAt = ref('');
const busy = ref(false);
const error = ref('');
const fieldErrors = ref<Record<string, string>>({});
const isTaxTool = computed(() =>
    ['vat', 'withholding-tax'].includes(props.tool.slug),
);

function fieldVisibleFor(
    field: ToolField,
    inputs: Record<string, string>,
): boolean {
    if (field.name === 'rate_percent') return inputs.rate_source === 'manual';
    if (field.name === 'scope_key') return inputs.rate_source === 'approved';
    if (field.name === 'selling_price') return inputs.mode === 'price';
    if (field.name === 'target_percent')
        return ['target_markup', 'target_margin'].includes(inputs.mode);
    if (field.name === 'reducing_rate_percent')
        return inputs.mode === 'reducing';
    return true;
}

function fieldVisible(field: ToolField): boolean {
    return fieldVisibleFor(field, values);
}

const printFields = computed(() => {
    const inputs = calculatedInputs.value;
    if (!inputs) return [];

    return props.definition.fields.filter(
        (field) => fieldVisibleFor(field, inputs) && inputs[field.name] !== '',
    );
});

function printFieldValue(field: ToolField): string {
    const value = calculatedInputs.value?.[field.name] ?? '';

    if (field.type === 'select') return field.options?.[value] ?? value;
    if (field.unit === 'currency')
        return `${props.tool.currency} ${new Intl.NumberFormat('en-NG', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(Number(value))}`;
    if (field.unit === 'percent') return `${value}%`;

    return value;
}

function formatMetric(metric: Metric): string {
    if (metric.value === null) return 'Not defined';
    const amount = Number(metric.value);
    if (metric.unit === 'currency')
        return new Intl.NumberFormat('en-NG', {
            style: 'currency',
            currency: props.tool.currency,
            maximumFractionDigits: 2,
        }).format(amount);
    if (metric.unit === 'percent')
        return `${new Intl.NumberFormat('en-NG', { maximumFractionDigits: 2 }).format(amount)}%`;
    if (metric.unit === 'units')
        return new Intl.NumberFormat('en-NG', {
            maximumFractionDigits: 0,
        }).format(amount);
    return `${new Intl.NumberFormat('en-NG', { maximumFractionDigits: 2 }).format(amount)} ${metric.unit === 'times' ? '×' : metric.unit}`;
}

function normalizeAmountInput(input: string): string {
    const characters = input.replace(/[^\d.]/g, '');
    const decimalPoint = characters.indexOf('.');
    const whole = (
        decimalPoint === -1 ? characters : characters.slice(0, decimalPoint)
    ).replace(/^0+(?=\d)/, '');

    if (decimalPoint === -1) return whole;

    const fraction = characters
        .slice(decimalPoint + 1)
        .replace(/\./g, '')
        .slice(0, 2);

    return `${whole || '0'}.${fraction}`;
}

function formatAmountInput(value: string): string {
    if (!value) return '';

    const [whole, fraction] = value.split('.');
    const grouped = whole.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    return fraction === undefined ? grouped : `${grouped}.${fraction}`;
}

function handleFieldInput(field: ToolField, event: Event): void {
    const input = event.target as HTMLInputElement;

    if (field.unit !== 'currency') {
        values[field.name] = input.value;
        return;
    }

    const significantBeforeCursor = input.value
        .slice(0, input.selectionStart ?? input.value.length)
        .replace(/[^\d.]/g, '').length;
    const raw = normalizeAmountInput(input.value);
    const formatted = formatAmountInput(raw);
    values[field.name] = raw;
    input.value = formatted;

    let cursor = 0;
    let significantCount = 0;
    while (
        cursor < formatted.length &&
        significantCount < significantBeforeCursor
    ) {
        if (/[\d.]/.test(formatted[cursor])) significantCount++;
        cursor++;
    }
    input.setSelectionRange(cursor, cursor);
}

function finishAmountInput(field: ToolField): void {
    if (field.unit === 'currency') {
        values[field.name] = values[field.name].replace(/\.$/, '');
    }
}

function reset(): void {
    Object.assign(values, initialValues());
    calculation.value = null;
    calculatedInputs.value = null;
    fieldErrors.value = {};
    error.value = '';
}

function printResult(): void {
    window.print();
}

async function calculate(): Promise<void> {
    busy.value = true;
    error.value = '';
    fieldErrors.value = {};
    calculation.value = null;
    calculatedInputs.value = null;

    try {
        const submittedInputs = Object.fromEntries(
            props.definition.fields.map((field) => [
                field.name,
                field.unit === 'currency'
                    ? values[field.name].replace(/\.$/, '')
                    : values[field.name],
            ]),
        );
        const csrf =
            document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                ?.content ?? '';
        const response = await fetch(`/tools/${props.tool.slug}/calculate`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(submittedInputs),
        });
        const payload = await response.json();

        if (!response.ok) {
            if (response.status === 422 && payload.errors) {
                fieldErrors.value = Object.fromEntries(
                    Object.entries(
                        payload.errors as Record<string, string[]>,
                    ).map(([key, messages]) => [key, messages[0]]),
                );
                error.value = 'Please check the highlighted inputs.';
            } else {
                error.value =
                    response.status === 429
                        ? 'Too many calculations in a short time. Please wait a minute.'
                        : 'The calculation could not be completed. Please try again.';
            }
            return;
        }

        calculation.value = payload as Calculation;
        calculatedInputs.value = submittedInputs;
        calculatedAt.value = new Intl.DateTimeFormat('en-NG', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(new Date());
    } catch {
        error.value = 'The calculator could not connect. Please try again.';
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <Head :title="tool.seoTitle">
        <meta name="description" :content="tool.seoDescription" />
    </Head>
    <PublicLayout>
        <PageHero
            class="print:hidden"
            eyebrow="Free accounting calculator"
            :title="tool.heading"
            :intro="tool.intro"
        >
            <template #before
                ><Link
                    href="/tools"
                    class="inline-flex items-center gap-2 text-sm font-bold text-aqua-300 hover:underline"
                    ><ArrowLeft :size="17" aria-hidden="true" /> All tools</Link
                ></template
            >
        </PageHero>

        <section class="site-container py-12 sm:py-18 print:hidden">
            <div
                class="grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(19rem,0.8fr)] lg:items-start"
            >
                <form
                    class="rounded-xl border border-border bg-white p-6 shadow-card sm:p-8"
                    @submit.prevent="calculate"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="flex size-10 items-center justify-center rounded-lg bg-aqua-50 text-aqua-700"
                            ><Calculator :size="20" aria-hidden="true"
                        /></span>
                        <h2 class="text-xl font-semibold text-navy-900">
                            Enter your figures
                        </h2>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-muted-foreground">
                        Amounts display in {{ tool.currency }} with thousands
                        separators (for example, 1,000.00). No exchange-rate
                        conversion is applied.
                    </p>
                    <div class="mt-8 grid gap-5 sm:grid-cols-2">
                        <div
                            v-for="field in definition.fields.filter(
                                fieldVisible,
                            )"
                            :key="field.name"
                            class="min-w-0"
                            :class="
                                [
                                    'amount',
                                    'eligible_base',
                                    'payable_amount',
                                    'principal',
                                ].includes(field.name)
                                    ? 'sm:col-span-2'
                                    : ''
                            "
                        >
                            <label
                                :for="field.name"
                                class="mb-2 block text-sm font-semibold text-navy-900"
                                >{{ field.label
                                }}<span
                                    v-if="field.required"
                                    class="ml-1 text-aqua-700"
                                    aria-hidden="true"
                                    >*</span
                                ></label
                            >
                            <select
                                v-if="field.type === 'select'"
                                :id="field.name"
                                v-model="values[field.name]"
                                :aria-invalid="!!fieldErrors[field.name]"
                                class="min-h-12 w-full rounded-md border border-border bg-white px-3 text-sm text-navy-900 focus:border-aqua-700 focus:ring-2 focus:ring-aqua-100 focus:outline-none"
                            >
                                <option
                                    v-for="(label, value) in field.options"
                                    :key="value"
                                    :value="value"
                                    :disabled="
                                        field.name === 'rate_source' &&
                                        value === 'approved' &&
                                        approvedRules.length === 0
                                    "
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <select
                                v-else-if="
                                    field.name === 'scope_key' &&
                                    approvedRules.length
                                "
                                :id="field.name"
                                v-model="values[field.name]"
                                class="min-h-12 w-full rounded-md border border-border bg-white px-3 text-sm text-navy-900 focus:border-aqua-700 focus:ring-2 focus:ring-aqua-100 focus:outline-none"
                            >
                                <option value="">
                                    Choose a reviewed classification
                                </option>
                                <option
                                    v-for="rule in approvedRules"
                                    :key="`${rule.key}-${rule.version}`"
                                    :value="rule.key"
                                >
                                    {{ rule.label }} ({{ rule.period }})
                                </option>
                            </select>
                            <input
                                v-else
                                :id="field.name"
                                :value="
                                    field.unit === 'currency'
                                        ? formatAmountInput(values[field.name])
                                        : values[field.name]
                                "
                                :type="
                                    field.unit === 'currency'
                                        ? 'text'
                                        : field.type
                                "
                                :inputmode="
                                    field.unit === 'currency'
                                        ? 'decimal'
                                        : undefined
                                "
                                :placeholder="
                                    field.unit === 'currency'
                                        ? 'e.g. 1,000.00'
                                        : undefined
                                "
                                :step="
                                    field.type === 'number' &&
                                    field.unit !== 'currency'
                                        ? 'any'
                                        : undefined
                                "
                                :min="
                                    field.type === 'number' &&
                                    field.unit !== 'currency'
                                        ? 0
                                        : undefined
                                "
                                :required="field.required"
                                :aria-invalid="!!fieldErrors[field.name]"
                                :aria-describedby="
                                    fieldErrors[field.name]
                                        ? `${field.name}-error`
                                        : undefined
                                "
                                class="min-h-12 w-full rounded-md border border-border bg-white px-3 text-sm text-navy-900 placeholder:text-navy-400 focus:border-aqua-700 focus:ring-2 focus:ring-aqua-100 focus:outline-none"
                                :class="
                                    fieldErrors[field.name]
                                        ? 'border-red-500'
                                        : ''
                                "
                                @input="handleFieldInput(field, $event)"
                                @blur="finishAmountInput(field)"
                            />
                            <p
                                v-if="fieldErrors[field.name]"
                                :id="`${field.name}-error`"
                                class="mt-2 text-sm text-red-700"
                            >
                                {{ fieldErrors[field.name] }}
                            </p>
                        </div>
                    </div>
                    <p
                        v-if="isTaxTool"
                        class="mt-7 flex gap-2 rounded-lg bg-aqua-50 p-4 text-sm leading-6 text-navy-800"
                    >
                        <Info
                            :size="18"
                            class="mt-0.5 shrink-0 text-aqua-700"
                            aria-hidden="true"
                        />
                        {{
                            approvedRules.length
                                ? 'Manual rates are illustrative. Approved rules are selected by classification and transaction date.'
                                : 'No approved tax rule is configured yet. Enter a manual rate for an illustrative estimate only.'
                        }}
                    </p>
                    <p
                        v-if="error"
                        role="alert"
                        class="mt-5 rounded-md bg-red-50 p-3 text-sm font-semibold text-red-800"
                    >
                        {{ error }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <button
                            type="submit"
                            :disabled="busy"
                            class="min-h-12 rounded-md bg-navy-900 px-6 text-sm font-bold text-white hover:bg-navy-800 disabled:opacity-60"
                        >
                            {{ busy ? 'Calculating…' : 'Calculate' }}</button
                        ><button
                            type="button"
                            class="inline-flex min-h-12 items-center gap-2 rounded-md border border-border px-5 text-sm font-semibold text-navy-800 hover:bg-muted"
                            @click="reset"
                        >
                            <RotateCcw :size="16" aria-hidden="true" /> Reset
                        </button>
                    </div>
                </form>

                <div class="space-y-6 lg:sticky lg:top-6">
                    <section
                        aria-live="polite"
                        aria-atomic="true"
                        class="rounded-xl border border-border bg-navy-900 p-6 text-white shadow-card sm:p-8"
                    >
                        <p class="eyebrow text-aqua-400">Your result</p>
                        <template v-if="calculation">
                            <dl class="mt-6 divide-y divide-white/15">
                                <div
                                    v-for="metric in calculation.metrics"
                                    :key="metric.label"
                                    class="py-3"
                                >
                                    <dt class="text-sm text-white/70">
                                        {{ metric.label }}
                                    </dt>
                                    <dd
                                        class="mt-1 text-2xl font-semibold text-white tabular-nums"
                                    >
                                        {{ formatMetric(metric) }}
                                    </dd>
                                    <p
                                        v-if="metric.note"
                                        class="mt-1 text-xs leading-5 text-white/70"
                                    >
                                        {{ metric.note }}
                                    </p>
                                </div>
                            </dl>
                            <button
                                type="button"
                                class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-aqua-300 hover:underline print:hidden"
                                @click="printResult"
                            >
                                <Printer :size="16" aria-hidden="true" /> Print
                                result
                            </button>
                        </template>
                        <p v-else class="mt-5 text-sm leading-7 text-white/75">
                            Complete the fields and select Calculate to see a
                            breakdown here.
                        </p>
                    </section>

                    <section
                        class="rounded-xl border border-border bg-aqua-50 p-6 text-sm leading-6 text-navy-800"
                    >
                        <h2 class="font-semibold text-navy-900">
                            How this works
                        </h2>
                        <p class="mt-2">{{ definition.formula }}</p>
                        <template v-if="calculation"
                            ><p class="mt-4 font-semibold">Assumptions</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                <li
                                    v-for="note in calculation.assumptions"
                                    :key="note"
                                >
                                    {{ note }}
                                </li>
                            </ul>
                            <p class="mt-3 text-xs">
                                Formula v{{ calculation.formula_version }} ·
                                {{ calculation.rounding }}
                            </p>
                            <p
                                v-if="calculation.config_version"
                                class="mt-2 text-xs"
                            >
                                Approved rule version ID:
                                {{ calculation.config_version }}
                                <a
                                    v-if="calculation.source_url"
                                    :href="calculation.source_url"
                                    class="font-semibold underline"
                                    rel="noopener noreferrer"
                                    target="_blank"
                                    >View source</a
                                >
                            </p></template
                        >
                    </section>
                </div>
            </div>

            <section
                v-if="calculation?.schedule?.length"
                class="mt-10 overflow-x-auto rounded-xl border border-border bg-white p-6"
            >
                <h2 class="text-xl font-semibold text-navy-900">
                    Period schedule
                </h2>
                <table class="mt-5 w-full min-w-150 text-left text-sm">
                    <thead class="border-b border-border text-navy-800">
                        <tr>
                            <th
                                v-for="key in Object.keys(
                                    calculation.schedule[0],
                                )"
                                :key="key"
                                class="px-3 py-3 capitalize"
                            >
                                {{ key }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(row, index) in calculation.schedule"
                            :key="index"
                            class="border-b border-border/70"
                        >
                            <td
                                v-for="key in Object.keys(
                                    calculation.schedule[0],
                                )"
                                :key="key"
                                class="px-3 py-2 tabular-nums"
                            >
                                {{ row[key] }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="mt-16 grid gap-8 lg:grid-cols-[1.25fr_0.75fr]">
                <div>
                    <p class="eyebrow text-aqua-700">Calculator guide</p>
                    <h2 class="mt-3 text-2xl font-semibold text-navy-900">
                        How to use this {{ tool.title.toLowerCase() }}
                    </h2>
                    <p class="mt-4 max-w-3xl leading-7 text-navy-700">
                        {{ guide.intro }}
                    </p>
                    <ol class="mt-6 space-y-4">
                        <li
                            v-for="(step, index) in guide.steps"
                            :key="index"
                            class="flex gap-4 text-sm leading-6 text-navy-800"
                        >
                            <span
                                class="flex size-8 shrink-0 items-center justify-center rounded-full bg-aqua-50 font-bold text-aqua-700"
                                >{{ index + 1 }}</span
                            >
                            <span class="pt-1">{{ step }}</span>
                        </li>
                    </ol>
                </div>
                <aside
                    class="h-fit rounded-xl border border-border bg-navy-50 p-6"
                >
                    <h2 class="text-lg font-semibold text-navy-900">
                        Worked example
                    </h2>
                    <p class="mt-3 text-sm leading-7 text-navy-800">
                        {{ guide.example }}
                    </p>
                    <p
                        class="mt-5 border-t border-border pt-4 text-xs leading-6 text-navy-600"
                    >
                        Example figures explain the arithmetic. Results depend
                        on the values and assumptions entered in the calculator.
                    </p>
                </aside>
            </section>

            <section class="mt-16 border-t border-border pt-12">
                <p class="eyebrow text-aqua-700">Common questions</p>
                <h2 class="mt-3 text-2xl font-semibold text-navy-900">
                    Questions about {{ tool.title.toLowerCase() }}
                </h2>
                <div class="mt-7 grid gap-6 md:grid-cols-2">
                    <article
                        v-for="item in guide.faqs"
                        :key="item.question"
                        class="rounded-xl border border-border bg-white p-6"
                    >
                        <h3 class="font-semibold text-navy-900">
                            {{ item.question }}
                        </h3>
                        <p class="mt-3 text-sm leading-7 text-navy-700">
                            {{ item.answer }}
                        </p>
                    </article>
                </div>
            </section>

            <section v-if="relatedTools.length" class="mt-16">
                <p class="eyebrow text-aqua-700">Continue exploring</p>
                <h2 class="mt-3 text-2xl font-semibold text-navy-900">
                    Related accounting calculators
                </h2>
                <div class="mt-7 grid gap-5 md:grid-cols-3">
                    <Link
                        v-for="related in relatedTools"
                        :key="related.slug"
                        :href="`/tools/${related.slug}`"
                        class="group rounded-xl border border-border bg-white p-6 hover:border-aqua-500 hover:shadow-card"
                    >
                        <h3 class="font-semibold text-navy-900">
                            {{ related.title }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-navy-600">
                            {{ related.summary }}
                        </p>
                        <span
                            class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-aqua-700"
                            >Open tool
                            <ArrowUpRight :size="16" aria-hidden="true"
                        /></span>
                    </Link>
                </div>
            </section>

            <section v-if="relatedPosts.length" class="mt-16">
                <p class="eyebrow text-aqua-700">Further reading</p>
                <h2 class="mt-3 text-2xl font-semibold text-navy-900">
                    Related insights
                </h2>
                <div class="mt-7 grid gap-5 md:grid-cols-3">
                    <ArticleCard
                        v-for="post in relatedPosts"
                        :key="post.slug"
                        :article="post"
                    />
                </div>
            </section>

            <div
                class="mt-16 flex flex-col gap-5 rounded-xl bg-navy-900 p-8 text-white sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-2xl font-semibold">
                        Want help interpreting your figures?
                    </h2>
                    <p class="mt-2 text-sm text-white/75">
                        Talk with ALCO about the records and decisions behind
                        the result.
                    </p>
                </div>
                <Link
                    href="/book-consultation"
                    class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-md bg-aqua-400 px-5 text-sm font-bold text-navy-900 hover:bg-aqua-300"
                    >Book a consultation
                    <ArrowUpRight :size="16" aria-hidden="true"
                /></Link>
            </div>
        </section>

        <section
            v-if="calculation && calculatedInputs"
            class="calculator-print-report hidden"
            aria-label="Printable calculator report"
        >
            <header class="report-header">
                <div class="report-kicker">
                    <strong>ALCO Associate Solutions</strong>
                    <span>Accounting tools · {{ calculatedAt }}</span>
                </div>
                <h1>{{ tool.title }}</h1>
                <p>{{ tool.summary }}</p>
            </header>

            <div class="report-columns">
                <section>
                    <h2>Entered figures</h2>
                    <dl>
                        <div
                            v-for="field in printFields"
                            :key="field.name"
                            class="report-row"
                        >
                            <dt>{{ field.label }}</dt>
                            <dd>{{ printFieldValue(field) }}</dd>
                        </div>
                    </dl>
                </section>
                <section>
                    <h2>Results</h2>
                    <dl>
                        <div
                            v-for="metric in calculation.metrics"
                            :key="metric.label"
                            class="report-row"
                        >
                            <dt>{{ metric.label }}</dt>
                            <dd>
                                {{ formatMetric(metric) }}
                                <small v-if="metric.note">{{
                                    metric.note
                                }}</small>
                            </dd>
                        </div>
                    </dl>
                </section>
            </div>

            <section class="report-explanation">
                <h2>Method and assumptions</h2>
                <p>{{ definition.formula }}</p>
                <ul>
                    <li
                        v-for="assumption in calculation.assumptions"
                        :key="assumption"
                    >
                        {{ assumption }}
                    </li>
                </ul>
                <p class="report-meta">
                    Formula v{{ calculation.formula_version }} ·
                    {{ calculation.rounding }}
                    <span v-if="calculation.config_version">
                        · Approved rule ID
                        {{ calculation.config_version }}</span
                    >
                </p>
                <p v-if="calculation.source_url" class="report-meta">
                    Rule source: {{ calculation.source_url }}
                </p>
            </section>

            <section
                v-if="calculation.schedule?.length"
                class="report-schedule"
            >
                <h2>Period schedule</h2>
                <table>
                    <thead>
                        <tr>
                            <th
                                v-for="key in Object.keys(
                                    calculation.schedule[0],
                                )"
                                :key="key"
                            >
                                {{ key }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(row, index) in calculation.schedule"
                            :key="index"
                        >
                            <td
                                v-for="key in Object.keys(
                                    calculation.schedule[0],
                                )"
                                :key="key"
                            >
                                {{ row[key] }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <p class="report-footer">
                alcosolutions.ng · Educational estimate based on the figures
                entered. Confirm tax treatment and important decisions with
                ALCO.
            </p>
        </section>
    </PublicLayout>
</template>

<style scoped>
@media print {
    @page {
        size: A4;
        margin: 12mm;
    }

    .calculator-print-report {
        display: block !important;
        color: #1b2c3d;
        font-size: 9pt;
        line-height: 1.35;
    }

    .report-header {
        border-bottom: 1px solid #b8c8d3;
        padding-bottom: 4mm;
        margin-bottom: 5mm;
    }

    .report-kicker {
        display: flex;
        justify-content: space-between;
        gap: 8mm;
        color: #465d78;
        font-size: 8pt;
    }

    .report-header h1 {
        margin: 3mm 0 1mm;
        color: #062849;
        font-size: 17pt;
        font-weight: 700;
    }

    .report-header p {
        margin: 0;
    }

    .report-columns {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8mm;
        align-items: start;
    }

    .calculator-print-report h2 {
        margin: 0 0 2mm;
        color: #062849;
        font-size: 10pt;
        font-weight: 700;
    }

    .report-row {
        display: flex;
        justify-content: space-between;
        gap: 3mm;
        border-bottom: 1px solid #e0e7ed;
        padding: 1.5mm 0;
        break-inside: avoid;
    }

    .report-row dt {
        max-width: 55%;
    }

    .report-row dd {
        margin: 0;
        font-weight: 600;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .report-row small {
        display: block;
        font-size: 7pt;
        font-weight: 400;
    }

    .report-explanation,
    .report-schedule {
        margin-top: 5mm;
    }

    .report-explanation {
        break-inside: avoid;
    }

    .report-explanation p {
        margin: 1mm 0;
    }

    .report-explanation ul {
        margin: 2mm 0 0;
        padding-left: 5mm;
    }

    .report-meta,
    .report-footer {
        color: #465d78;
        font-size: 7pt;
        overflow-wrap: anywhere;
    }

    .report-schedule table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
    }

    .report-schedule thead {
        display: table-header-group;
    }

    .report-schedule th,
    .report-schedule td {
        border-bottom: 1px solid #e0e7ed;
        padding: 1.5mm 2mm;
        text-align: right;
    }

    .report-schedule th:first-child,
    .report-schedule td:first-child {
        text-align: left;
    }

    .report-schedule tr {
        break-inside: avoid;
    }

    .report-footer {
        margin-top: 5mm;
        border-top: 1px solid #b8c8d3;
        padding-top: 2mm;
    }
}
</style>
