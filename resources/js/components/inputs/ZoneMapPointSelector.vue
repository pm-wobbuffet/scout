<template>

    <scroll-overlay wheel-unlock-key="Shift">
        <vue-zoomable :initial-zoom="1" :min-zoom="1" :selector="`#zonemap${props.zone.id}`"
            class="w-full relative border select-none" v-model:pan="pan" v-model:zoom="zoom" :mouse-enabled="true"
            :enable-control-button="false" :dbl-click-enabled="false">
            <div :id="`zonemap${props.zone.id}`" class="select-none" @dblclick="mapDoubledClicked"><img
                    class="w-full aspect-square" :src="mapImage" draggable="false" alt="Map of the zone" width="1024"
                    height="1024" />
                <button v-for="point in props.zone.spawn_points" :key="`btnspawnpt-${point.id}`"
                    :aria-label="`Button to choose Spawn Point X=${point.x},Y=${point.Y}`" class="rounded-full absolute"
                    :class="{
                        selected: (point.id === selected_point),
                        deletedPt: (point?.deleted_at && point.deleted_at !== null)
                    }" :style="{
                        top: convertCoordToPercent(point.y, props.zone),
                        left: convertCoordToPercent(point.x, props.zone),
                        width: getButtonDimension,
                        height: getButtonDimension
                    }" @click="selectPoint(point.id)" />
            </div>
            <template #buttons>
                <div class="absolute bottom-4 right-4">
                    <ul class="list-none">
                        <li class="btn-nav border-b-[#dee2e6] rounded-t-md">
                            <a @pointerdown="ev => resetTransform(ev)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-minimize-2">
                                    <polyline points="4 14 10 14 10 20"></polyline>
                                    <polyline points="20 10 14 10 14 4"></polyline>
                                    <line x1="14" y1="10" x2="21" y2="3"></line>
                                    <line x1="3" y1="21" x2="10" y2="14"></line>
                                </svg>
                            </a>
                        </li>
                        <li class="btn-nav border-b-[#dee2e6]">
                            <a @pointerdown="ev => zoomIn(ev)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-zoom-in">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    <line x1="11" y1="8" x2="11" y2="14"></line>
                                    <line x1="8" y1="11" x2="14" y2="11"></line>
                                </svg>
                            </a>
                        </li>
                        <li class="btn-nav rounded-b-md border-b-[#dee2e6]">
                            <a @pointerdown="ev => zoomOut(ev)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-zoom-out">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    <line x1="8" y1="11" x2="14" y2="11"></line>
                                </svg>
                            </a>
                        </li>
                    </ul>
                </div>
            </template>
        </vue-zoomable>
        <template #overlay class="hidden">
            &nbsp;
        </template>
    </scroll-overlay>
</template>

<script setup lang="ts">
import { convertCoordToPercent, formatCoordinate } from '@/classes/helpers';
import { Zone } from '@/types/gametypes';
import { computed, ref } from 'vue';
import VueZoomable, { ScrollOverlay } from "vue-zoomable";
import "vue-zoomable/dist/style.css";


interface Props {
    zone: Zone
}
const props = defineProps<Props>()
const emit = defineEmits<{
    dblclicked: [x: number, y: number],
    buttondown: [key: string],
    buttonup: [key: string]
}>()
const model = defineModel<number>('selected_point')
const zoom = ref(1)
const pan = ref({ x: 0, y: 0 })

const mapImage = computed(() => {
    return `/maps/${props.zone.map_id}.webp`
})

const getButtonDimension = computed(() => {
    return (20 / Math.max(zoom.value, 1)).toString() + 'px'
})

const resetTransform = (ev) => {
    zoom.value = 1
    pan.value = { x: 0, y: 0 }
}

const zoomIn = (ev) => {
    if (zoom.value >= 2.8) return
    zoom.value += 0.2
}

const zoomOut = (ev) => {
    if (zoom.value <= 0.6) return
    zoom.value -= 0.2
}

const mapDoubledClicked = (ev: MouseEvent) => {
    const bounds = ev.target.getBoundingClientRect()
    const [cX, cY] = [ev.clientX - bounds.x, ev.clientY - bounds.y]
    const [imgW, imgH] = [ev.target.offsetWidth, ev.target.offsetHeight]

    emit('dblclicked',
        (cX / imgW * props.zone.max_coord_size).toFixed(1),
        (cY / imgH * props.zone.max_coord_size).toFixed(1)
    )
}

const selectPoint = (point_id: number) => {
    // if this point is already selected, clear out the value
    if (model.value === point_id) {
        model.value = null
        return
    }
    model.value = point_id

}
</script>

<style scoped>
@reference '@resources/css/app.css';

button {
    @apply rounded-full bg-black/50 absolute translate-x-[-50%] translate-y-[-50%] ring-2 ring-black flex items-center justify-center p-0 text-center font-mono cursor-pointer;
    @apply hover:ring-2 hover:ring-white;

    &.selected {
        @apply ring-red-300 ring-4;
    }

    &.deletedPt {
        @apply bg-red-500/50;
    }
}

li {
    @apply hover:outline-4 hover:outline-[#32325d2d] bg-white;
    /* box-shadow: 0 2px 14px #32325d0d, 0 2px 20px #0000000d; */
}

.btn-nav {
    a {
        @apply p-2 block;
        cursor: pointer;
        color: #868e96;
        transition: color .15s ease;

        &:hover {
            color: #555b61;
        }
    }
}
</style>