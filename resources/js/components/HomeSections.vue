<script setup lang="ts">
import type { HomeBlock } from '@/types/publicContent';

defineProps<{ blocks: HomeBlock[] }>();
</script>

<template>
    <template v-for="(block, index) in blocks" :key="`${block.type}-${index}`">
        <section
            v-if="block.type === 'intro'"
            class="bg-[#f5fafc] py-20 sm:py-24"
            :aria-labelledby="`home-block-${index}`"
        >
            <div
                class="mx-auto grid max-w-[1370px] gap-8 px-5 sm:px-8 lg:grid-cols-[0.7fr_1.3fr] lg:items-start lg:gap-20 lg:px-12"
            >
                <p
                    class="text-xs font-bold tracking-[0.18em] text-[#168bae] uppercase"
                >
                    {{ block.data.eyebrow }}
                </p>
                <div>
                    <h2
                        :id="`home-block-${index}`"
                        class="max-w-3xl text-3xl leading-tight font-semibold tracking-tight sm:text-4xl"
                    >
                        {{ block.data.title }}
                    </h2>
                    <p
                        class="mt-6 max-w-3xl text-base leading-8 text-[#4d6273] sm:text-lg"
                    >
                        {{ block.data.body }}
                    </p>
                </div>
            </div>
        </section>

        <section
            v-else-if="block.type === 'audience'"
            class="bg-[#062849] py-20 text-white sm:py-24"
            :aria-labelledby="`home-block-${index}`"
        >
            <div class="mx-auto max-w-[1370px] px-5 sm:px-8 lg:px-12">
                <p
                    class="text-xs font-bold tracking-[0.18em] text-[#52d7f0] uppercase"
                >
                    {{ block.data.eyebrow }}
                </p>
                <div
                    class="mt-4 grid gap-5 lg:grid-cols-[1fr_0.8fr] lg:items-end lg:gap-16"
                >
                    <h2
                        :id="`home-block-${index}`"
                        class="max-w-2xl text-3xl leading-tight font-semibold tracking-tight sm:text-4xl"
                    >
                        {{ block.data.title }}
                    </h2>
                    <p class="max-w-lg leading-7 text-white/75">
                        {{ block.data.body }}
                    </p>
                </div>
                <div class="mt-10 grid gap-4 md:grid-cols-3">
                    <div
                        v-for="item in block.data.items ?? []"
                        :key="item.label"
                        class="rounded-xl border border-white/20 bg-white/5 p-7"
                    >
                        <h3 class="text-xl font-semibold text-white">
                            {{ item.label }}
                        </h3>
                        <p class="mt-4 text-sm leading-7 text-white/75">
                            {{ item.detail }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section
            v-else-if="block.type === 'process'"
            class="bg-white py-20 sm:py-24"
            :aria-labelledby="`home-block-${index}`"
        >
            <div class="mx-auto max-w-[1370px] px-5 sm:px-8 lg:px-12">
                <p
                    class="text-xs font-bold tracking-[0.18em] text-[#168bae] uppercase"
                >
                    {{ block.data.eyebrow }}
                </p>
                <h2
                    :id="`home-block-${index}`"
                    class="mt-4 max-w-3xl text-3xl leading-tight font-semibold tracking-tight sm:text-4xl"
                >
                    {{ block.data.title }}
                </h2>
                <ol class="mt-10 grid gap-4 md:grid-cols-3">
                    <li
                        v-for="(step, stepIndex) in block.data.steps ?? []"
                        :key="step.title"
                        class="rounded-xl border border-[#dbe6ec] bg-[#f8fbfc] p-7"
                    >
                        <span class="text-sm font-bold text-[#168bae]"
                            >0{{ stepIndex + 1 }}</span
                        >
                        <h3 class="mt-6 text-xl font-semibold">
                            {{ step.title }}
                        </h3>
                        <p class="mt-3 text-sm leading-7 text-[#4d6273]">
                            {{ step.detail }}
                        </p>
                    </li>
                </ol>
            </div>
        </section>

        <section
            v-else-if="block.type === 'faq'"
            class="bg-[#eaf6fb] py-20 sm:py-24"
            :aria-labelledby="`home-block-${index}`"
        >
            <div class="mx-auto max-w-4xl px-5 sm:px-8">
                <p
                    class="text-xs font-bold tracking-[0.18em] text-[#168bae] uppercase"
                >
                    {{ block.data.eyebrow }}
                </p>
                <h2
                    :id="`home-block-${index}`"
                    class="mt-4 text-3xl leading-tight font-semibold tracking-tight sm:text-4xl"
                >
                    {{ block.data.title }}
                </h2>
                <div class="mt-9 grid gap-3">
                    <details
                        v-for="item in block.data.items ?? []"
                        :key="item.question"
                        class="group rounded-xl bg-white px-6 py-5 shadow-sm"
                    >
                        <summary
                            class="cursor-pointer text-base font-semibold marker:text-[#168bae]"
                        >
                            {{ item.question }}
                        </summary>
                        <p class="mt-4 leading-7 text-[#4d6273]">
                            {{ item.answer }}
                        </p>
                    </details>
                </div>
            </div>
        </section>
    </template>
</template>
