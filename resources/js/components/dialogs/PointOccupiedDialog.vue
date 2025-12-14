<template>
    <div class="context-menu absolute text-nowrap text-xs flex items-center z-9999 p-0" ref="contextDiv"
        :style="computedStyle">
        <a href="#" class="p-1 hover:bg-slate-400 dark:hover:bg-slate-500" v-show="!occupied"
            @click.prevent="emitClick(1)">Mark Point Occupied</a>
        <a href="#" class="p-1 hover:bg-slate-400 dark:hover:bg-slate-500" v-show="occupied"
            @click.prevent="emitClick(0)">Mark Point Unoccupied</a>
    </div>
</template>

<script setup>
import { useEventListener } from '@vueuse/core';
import { computed, inject, onMounted, onUpdated, ref } from 'vue'

const { point, x, y, instance, parentWidth } = defineProps(['point', 'x', 'y', 'instance', 'parent-width'])
const model = defineModel()
const emit = defineEmits(['point-occupied', 'point-unoccupied', 'dialog-closed'])
const scoutReport = inject('scoutReport')
const contextDiv = ref(null)

const emitClick = function (isOccupied) {
    if (isOccupied) {
        scoutReport.value.setOccupiedStatus(point, instance, 1)
        return
    }
    scoutReport.value.setOccupiedStatus(point, instance, 0)
}


onMounted(() => {
    useEventListener(document.body, 'click', (e) => {
        emit('dialog-closed')
    })
    // document.body.addEventListener('click', (e) => {
    //     emit('dialog-closed')
    // })
})

const computedStyle = computed(() => {
    if (!point) {
        return { top: '0px', left: '0px' }
    }
    if (x + contextDiv.value.offsetWidth >= parentWidth) {
        // Need to right align
        return { top: `${y}px`, left: `${x - contextDiv.value.offsetWidth - 30}px` }
    }
    return { top: `${y}px`, left: `${x}px` }
    // return {
    //     'position-anchor': `--anchor-btn-${point.id}`,
    //     'position-area': 'center right',
    //     'position-try': `most-width --anchor-btn-${point.id}, flip inline, bottom left`,
    //     // 'position-try-fallbacks': `--anchor-btn-${point.id}, --anchor-btn-${point.id} flip-inline, bottom left`,
    // }
})

const occupied = computed(() => {
    if (!point) {
        return
    }
    const mob = scoutReport.value.getMobOnPoint(point, instance)
    if (!mob) {
        return false
    }
    return (mob.mob_id === null)
})
</script>

<style lang="scss" scoped>
@reference '@resources/css/pp.css';

.context-menu {
    background-color: var(--color-popover);
    color: var(--color-popover-foreground);
    box-shadow: 2px 2px 2px var(--color-ring);
}
</style>