<template>
    <scroll-overlay wheel-unlock-key="Shift" class="max-w-[99vw]">
        <template #default="{ disableInteraction }">
            <vue-zoomable :initial-zoom="1" :min-zoom="1" :selector="`div.zone-map-container`"
                class="w-full relative border select-none" v-model:pan="pan" v-model:zoom="zoom" :mouse-enabled="true"
                :dbl-click-enabled="false" v-bind:disabled="disableInteraction" @zoom="checkZoomConstraints">
                <div draggable="false" class="zone-map-container" @contextmenu.prevent="">
                    <img class="" :src="mapImage" draggable="false" alt="Map of the zone" width="1024" height="1024"
                        @mousemove.self="handleMouseOver" @mouseout="handleMouseOut"
                        @dblclick.prevent="handleDoubleClick" />
                    <slot name="aetherytes" v-if="showAetherytes">
                        <div v-for="aetheryte in zone.aetherytes" class="aetheryte"
                            :key="`aetheryte-${aetheryte.id}-${props.instance}`" :style="{
                                'left': convertCoordToPercent(aetheryte.x, props.zone), 'top': convertCoordToPercent(aetheryte.y, props.zone),
                                'zoom': (1 / zoom).toFixed(2)
                            }" :data-title="getDisplayName(aetheryte)">
                        </div>
                    </slot>
                    <slot name="spawnpoints" v-if="showSpawnPoints">
                        <ZoneMapPoint v-for="point in scoutReport.getSpawnPointsForZone(zone)"
                            :key="`point-${point.id}-${props.instance}`" :point="point" :zone="props.zone"
                            :instance="props.instance" :editmode="props.editmode" :scout-report="props.scoutReport"
                            :style="{ zoom: (1 / zoom).toFixed(2) }" @click.stop.prevent=""
                            @contextToggled.prevent.stop="showContextMenu($event, point)"
                            @long-press.prevent="showContextMenu($event, point)" />
                        <context-menu ref="cmRef" v-model:show="showingContextMenu" :options="optionsComponent">
                            <template #itemRender="{ disabled, label, showRightArrow, onClick, onMouseEnter }">
                                <div :class="'mx-context-menu-item' + (disabled ? ' disabled' : '')" @click="onClick"
                                    @mouseenter="onMouseEnter">
                                    <span>{{ label }}</span>
                                    <span v-if="showRightArrow" class="right-arraw">>></span>
                                </div>
                            </template>
                            <context-menu-item label="Mark Occupied" v-if="!isOccupied(selectedPoint)"
                                @click="emitOccupied(selectedPoint, 1)">
                            </context-menu-item>
                            <context-menu-item label="Mark Unoccupied" v-else
                                @click="emitOccupied(selectedPoint, 0)"></context-menu-item>
                        </context-menu>
                    </slot>
                </div>
                <div>
                    <slot name="moblist" v-if="showMobList">
                        <div class="absolute mob-list top-0 left-0">
                            <ol class="block list-decimal pl-2">
                                <li v-for="(mob, index) in props.zone.mobs"
                                    :class="`group mob-number-${index} dead-mob-${props.scoutReport.isMobDead(mob.id, props.instance) ? 1 : 0}`"
                                    title="Toggle this mob as being dead/alive. Dead mobs will count toward scouting completion for this zone."
                                    :key="`moblist-mob-${mob.id}-${props.instance}`" @click="toggleMobStatus(mob)">
                                    <div class="flex items-center">
                                        <span>{{ getDisplayName(mob) }}</span>
                                        <SkullIcon class="inline-block p-0 m-0 pl-2 group-hover:visible"
                                            :class="{ 'visible': props.scoutReport.isMobDead(mob.id, props.instance), 'invisible': !props.scoutReport.isMobDead(mob.id, props.instance) }" />
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </slot>
                    <slot name="zone-name" v-if="showZoneName">
                        <div class="zone-name pointer-events-none">
                            <div class="zone-name-text">
                                {{ getZoneDisplayName(zone) }}
                                <span class="mapInstanceNumber"
                                    v-if="props.scoutReport.getInstanceCountForZone(zone.id) > 1">{{
                                        intToInstanceMapping[props.instance]
                                    }}</span>
                            </div>
                            <div v-if="is_hovered" class="zone-coords">{{ x_hover }}, {{ y_hover }}</div>
                        </div>
                    </slot>
                    <div class="absolute flex items-center bottom-1 left-1 text-center text-xs bg-[rgba(0,0,0,0.5)] hover:bg-black font-bold px-2 py-1 text-white dark:text-slate-200"
                        v-if="props.zone.allow_custom_points && props.editmode == true">
                        <TriangleAlert class="text-yellow-600 font-bold mr-1" :size="18" />
                        <span class="text-xs">Spawn points unknown. Double click or use Import to add spawn
                            points.</span>
                    </div>
                </div>
                <template #buttons>
                    <div
                        class="absolute bottom-2 right-2 scale-75 origin-bottom-right xl:bottom-4 xl:right-4 xl:scale-75">
                        <ul class="list-none">
                            <li class="btn-nav border-b-[#dee2e6] rounded-t-md">
                                <a @pointerdown.left="resetTransform">
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
                                <a @pointerdown.left="zoomIn">
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
                                <a @pointerdown.left="zoomOut">
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
        </template>
        <template #overlay>
            <div></div>
        </template>
    </scroll-overlay>
</template>

<script setup lang="ts">
import { convertCoordToPercent, getDisplayName, getZoneDisplayName, intToInstanceMapping } from '@/classes/helpers';
import ScoutReport from '@/classes/ScoutReport';
// import PointOccupiedDialog from '@/components/dialogs/PointOccupiedDialog.vue';
import ZoneMapPoint from '@/components/ZoneMapPoint.vue';
import { type Zone } from '@/types/gametypes';
import { SkullIcon, TriangleAlert } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
// import ScrollOverlay from '@/components/ScrollOverlay.vue';
import VueZoomable, { ScrollOverlay, ZoomableEvent } from "vue-zoomable";
import "vue-zoomable/dist/style.css";
import { type MenuOptions, ContextMenu, ContextMenuItem } from '@imengyu/vue3-context-menu';
// import ContextMenu from '@imengyu/vue3-context-menu';

interface Props {
    zone: Zone,
    instance: number,
    scoutReport: ScoutReport,
    editmode: boolean,
    showZoneName?: boolean,
    showMobList?: boolean,
    showAetherytes?: boolean,
    showSpawnPoints?: boolean,
}

const props = withDefaults(defineProps<Props>(), {
    showZoneName: true,
    showMobList: true,
    showAetherytes: true,
    showSpawnPoints: true,
})

// Variables to handle transforms of the map image
const zoom = ref(1)
const pan = ref({ x: 0, y: 0 })
const x_hover = ref(0)
const y_hover = ref(0)
const is_hovered = ref(false)

// Variables used by the Occupied contextmenu
// const occupiedDialog = useTemplateRef('occupiedDialog')
const showingContextMenu = ref(false)
const selectedPoint = ref(null)
const contextX = ref(0)
const contextY = ref(0)
// const parentWidth = ref(0)

const optionsComponent = reactive<MenuOptions>({
    iconFontClass: 'iconfont',
    customClass: "class-a",
    zIndex: 3,
    x: contextX,
    y: contextY
})

const getXYForEvent = function (event: PointerEvent) {
    const x = (event.offsetX / (<HTMLElement>event.target).clientWidth * props.zone.max_coord_size + 1).toFixed(1)
    const y = (event.offsetY / (<HTMLElement>event.target).clientHeight * props.zone.max_coord_size + 1).toFixed(1)
    return { 'x': x, 'y': y }
}

const handleDoubleClick = function (e: PointerEvent) {
    // No need to handle this if we don't allow custom points
    if (!props.zone.allow_custom_points) return
    if (!props.editmode) return

    const { x, y } = getXYForEvent(e)
    let point = props.scoutReport.getClosestPoint(props.zone, x, y, 2)
    // If they double click too closely to an existing point, ignore it
    // Ideally I'd trigger it on the point, but let's leave that as a TODO
    // TODO: Trigger point assignment

    if (point.distance && point.distance < 2) return

    // Create a new custom point for this
    point = props.scoutReport.createCustomPoint(props.zone, x, y)
    props.scoutReport.cycleMobOnPoint(point, props.instance)

}

const handleMouseOver = function (event) {
    const { x, y } = getXYForEvent(event)
    x_hover.value = Number(x)
    y_hover.value = Number(y)
    is_hovered.value = true
}

const handleMouseOut = function () {
    is_hovered.value = false
}

const emitOccupied = function (point, isOccupied) {
    if (isOccupied) {
        props.scoutReport.setOccupiedStatus(point, props.instance, 1)
        return
    }
    props.scoutReport.setOccupiedStatus(point, props.instance, 0)
}

const resetTransform = () => {
    zoom.value = 1
    pan.value = { x: 0, y: 0 }
}

const showContextMenu = function (e: PointerEvent, point) {
    const mob = props.scoutReport.getMobOnPoint(point, props.instance)
    // Don't allow points that have mobs on them to be marked occupied
    if (mob && mob.mob_id != null) {
        return
    }

    const bRect = e.target.getBoundingClientRect()
    showingContextMenu.value = true
    selectedPoint.value = point
    // @todo when css anchor() is supported in Firefox, revisit all this to see about using anchor elements for popover UI stuff
    contextX.value = bRect.left + 25
    contextY.value = bRect.top
}

const isOccupied = (point) => {
    if (!point) return
    const mob = props.scoutReport.getMobOnPoint(point, props.instance)
    if (!mob) {
        return false
    }
    return (mob.mob_id === null)
}
// const closeOccupyDialog = () => {
//     showingContextMenu.value = false
// }

const toggleMobStatus = function (mob) {
    if (!props.editmode) return
    props.scoutReport.toggleMobStatus(mob.id, props.instance)
}

const checkZoomConstraints = (e: ZoomableEvent) => {
    if (e.zoom <= 1) {
        zoom.value = 1
        pan.value = { x: 0, y: 0 }
    }
}

const zoomIn = () => {
    if (zoom.value >= 2.8) {
        zoom.value = 2.8
        return
    }
    zoom.value += 0.2
}

const zoomOut = () => {
    if (zoom.value <= 1) {
        zoom.value = 1
        pan.value = { x: 0, y: 0 }
        return
    }
    zoom.value -= 0.2
}

const mapImage = computed(() => {
    return `/maps/${props.zone.map_id}.webp`
})
</script>

<style scoped>
@reference '@resources/css/app.css';

:popover-open {
    position: absolute;
    inset: unset;
    bottom: 5px;
    right: 5px;
}

li.btn-nav {
    @apply hover:outline-4 hover:outline-[#32325d2d] bg-white;

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
<style>
/* ContextMenu */
@reference '@resources/css/app.css';

.mx-context-menu {
    @apply p-0 rounded-sm;
}

.mx-context-menu-item {
    @apply p-1 px-2 text-sm rounded-md;
}
</style>
