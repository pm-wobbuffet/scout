<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;
</script>

<template>
    <div class="inline-flex gap-1 rounded-lg">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            :title="label"
            @click="updateAppearance(value)"
            :class="[
                'flex items-center justify-center rounded-md px-2 py-1.5 transition-colors',
                appearance === value
                    ? 'bg-blue-200/25 shadow-xs dark:bg-blue-200/25 dark:text-neutral-100'
                    : 'bg-[rgba(0,0,0,0.4)] text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-[rgba(0,0,0,0.4)]',
            ]"
        ><component :is="Icon" class="h-4 w-4" /></button>
    </div>
</template>
