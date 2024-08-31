<template>
    <div
        class="context-menu absolute text-nowrap text-sm bg-white dark:bg-gray-400 dark:text-gray-50 border-black flex items-center z-[100] p-0"
        :style="{ top: y + 'px', left: x + 'px' }"
        >
        <a href="#" class="p-1 hover:bg-slate-400 dark:hover:bg-slate-500"
        v-show="!isPointOccupied()"
        >Mark Point Occupied</a>
        <a href="#" class="p-1 hover:bg-slate-400 dark:hover:bg-slate-500"
        v-show="isPointOccupied()"
        >Mark Point Unoccupied</a>
    </div>
</template>

<script setup>

const { point, x, y, instance } = defineProps(['point', 'x', 'y', 'instance'])
const model = defineModel()
const emits = defineEmits(['point-occupied-updated'])

const isPointOccupied = function() {
    if(point && point?.id) {
        if(model.value.occupied_points) {
            return (point.id in model.value.occupied_points
                    && instance in model.value.occupied_points[point.id]
                    && model.value.occupied_points[point.id][instance] == 1
                )
        }
    }
    return false
}

</script>

<style type="scss" scoped>
.context-menu {
    box-shadow: 3px 3px 4px rgba(0, 0, 0, 0.5);
    min-width: 150px;
}
</style>
