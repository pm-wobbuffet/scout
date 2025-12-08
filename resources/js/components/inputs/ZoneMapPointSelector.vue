<template>

    <div class="relative max-w-3xl">
        <vue-zoomable :initial-zoom="1" :min-zoom="1" :selector="`#zonemap${props.zone.id}`"
            class="w-full absolute top-0 left-0 border select-none" v-model:pan="pan" v-model:zoom="zoom"
            :button-pan-step="50" :mouse-enabled="true">
            <div :id="`zonemap${props.zone.id}`" class="select-none"><img class="select-none" :src="mapImage"
                    draggable="false" alt="Map of the zone" />
                <button v-for="point in props.zone.spawn_points" :key="`btnspawnpt-${point.id}`"
                    class="rounded-full absolute bg-gray-400" :class="{
                        selected: (point.id === selected_point)
                    }" :style="{
                        top: convertCoordToPercent(point.y, props.zone),
                        left: convertCoordToPercent(point.x, props.zone),
                        width: getButtonDimension,
                        height: getButtonDimension
                    }" @click="selectPoint(point.id)"></button>
            </div>
        </vue-zoomable>
        <div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { convertCoordToPercent } from '@/classes/helpers';
import { Zone } from '@/types/gametypes';
import { computed, ref } from 'vue';
import VueZoomable, { ScrollOverlay } from "vue-zoomable";
import "vue-zoomable/dist/style.css";


interface Props {
    zone: Zone
}
const props = defineProps<Props>();
const model = defineModel<number>('selected_point')
const zoom = ref(1)
const pan = ref({ x: 0, y: 0 })

const mapImage = computed(() => {
    return `/maps/${props.zone.map_id}.webp`
})

const getButtonDimension = computed(() => {
    return (20 / Math.max(zoom.value, 1)).toString() + 'px'
})

const selectPoint = (point_id: number) => {
    // if this point is already selected, clear out the value
    if (model.value === point_id) {
        model.value = null
        return
    }
    model.value = point_id

}

// const getCoord = function (coord: number, zone: Zone) {
//     let c = (coord - 1) / (zone.max_coord_size) * 100
//     c *= zoom.value
//     return c.toString() + '%';
// }
</script>

<style scoped>
@reference '@resources/css/app.css';

img {
    user-select: none;
    -webkit-user-drag: none;
}

button {
    @apply rounded-full bg-black/50 absolute translate-x-[-50%] translate-y-[-50%] ring-2 ring-black flex items-center justify-center p-0 text-center font-mono cursor-pointer;
    @apply hover:ring-2 hover:ring-white;

    &.selected {
        @apply ring-red-300 bg-red-500 ring-4;
    }
}

/* @keyframes pulse {
    0% {
        @apply ring-2;
    }

    50% {
        @apply ring-4;
    }

    100% {
        @apply ring-2;
    }
} */
</style>