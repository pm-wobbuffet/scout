<template>
    <button
        class="inline-flex items-center font-bold cursor-pointer hover:brightness-110 transition-all duration-150 flex-row rounded-md border border-black/50 hover:border-black/25 dark:border-slate-300/50 p-0 gap-0 overflow-hidden">
        <div :class="className">
            <slot name="icon"></slot>
        </div>
        <div class="text-black dark:text-neutral-300">
            <slot />
        </div>
    </button>
</template>

<script setup>
import { cn } from '@/lib/utils';
import { Component, XIcon } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    iconClass: String,
})

const className = computed(() => cn('px-[2px]', props.iconClass));

</script>

<style scoped></style>