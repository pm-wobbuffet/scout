<template>
    <PopoverRoot @update:open="handleOpen">
        <PopoverTrigger as-child>
            <button
                class="border ml-1 rounded-sm p-1 bg-[rgba(0,0,0,0.4)] hover:bg-[rgba(0,0,255,0.1)] cursor-pointer dark:border-slate-500 transition-colors duration-300">
                <SettingsIcon class="" :size="18" />
            </button>
        </PopoverTrigger>
        <PopoverAnchor />
        <PopoverPortal>
            <PopoverContent>
                <PopoverClose />
                <PopoverArrow />
            </PopoverContent>
        </PopoverPortal>
    </PopoverRoot>
</template>

<script setup>
import {
    PopoverAnchor,
    PopoverArrow,
    PopoverClose,
    PopoverContent,
    PopoverPortal,
    PopoverRoot,
    PopoverTrigger
} from 'reka-ui';
import { SettingsIcon } from 'lucide-vue-next';
import { ref } from 'vue';

const popoverOpen = ref(false)

const handleOpen = (event) => {
    console.log(event)
    // Grab settings
}
</script>

<style lang="scss" scoped></style>