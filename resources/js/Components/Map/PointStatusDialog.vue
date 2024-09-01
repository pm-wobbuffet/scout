<template>
    <div
        class="context-menu absolute text-nowrap text-sm bg-white dark:bg-gray-400 dark:text-gray-50 flex items-center border-black z-[100] p-0"
        :style="{ top: y + 'px', left: x + 'px' }"
        >
        <a href="#" class="p-1 hover:bg-slate-400 dark:hover:bg-slate-500"
        v-show="!occupied"
        @click="emitClick(point, instance, 1)"
        >Mark Point Occupied</a>
        <a href="#" class="p-1 hover:bg-slate-400 dark:hover:bg-slate-500"
        v-show="occupied"
        @click="emitClick(point, instance, 0)"
        >Mark Point Unoccupied</a>
    </div>
</template>

<script setup>

const { point, x, y, instance, occupied } = defineProps(['point', 'x', 'y', 'instance', 'occupied'])
const model = defineModel()
const emit = defineEmits(['point-occupied-updated'])

const emitClick = function(point, instance, newValue) {
    emit('point-occupied-updated', point, instance, newValue)
}

</script>

<style type="scss" scoped>
.context-menu {
    box-shadow: 3px 3px 4px rgba(0, 0, 0, 0.5);
}
</style>
