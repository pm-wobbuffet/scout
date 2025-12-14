<template>
    <scroll-overlay wheel-unlock-key="Shift" class="max-w-[99vw]">
        <template #default="{ disableInteraction }">
            <vue-zoomable :initial-zoom="1" :min-zoom="1" :selector="`div.zone-map-container`"
                class="w-full relative border select-none" v-model:pan="pan" v-model:zoom="zoom" :mouse-enabled="true"
                :dbl-click-enabled="false" v-bind:disabled="disableInteraction">
                <div draggable="false" class="zone-map-container">
                    <img class="" :src="mapImage" draggable="false" alt="Map of the zone" width="1024" height="1024" />
                    <slot name="aetherytes" v-if="showAetherytes">
                        <div v-for="aetheryte in zone.aetherytes" class="aetheryte"
                            :key="`aetheryte-${aetheryte.id}-${props.instance}`" :style="{
                                'left': convertCoordToPercent(aetheryte.x, props.zone), 'top': convertCoordToPercent(aetheryte.y, props.zone),
                                'zoom': (1 / zoom).toFixed(2)
                            }" :data-title="getDisplayName(aetheryte, 'en')">
                        </div>
                    </slot>
                    <slot name="spawnpoints" v-if="showSpawnPoints">
                        <ZoneMapPoint v-for="point in scoutReport.getSpawnPointsForZone(zone)"
                            :key="`point-${point.id}-${props.instance}`" :point="point" :zone="props.zone"
                            :instance="props.instance" :editmode="props.editmode" :scout-report="props.scoutReport"
                            :style="{ zoom: (1 / zoom).toFixed(2) }" @click.stop.prevent=""
                            @contextToggled.prevent.stop="showContextMenu($event, point)"
                            @long-press.prevent="showContextMenu($event, point)" />
                        <PointOccupiedDialog :x="contextX" :y="contextY" :point="selectedPoint"
                            v-show="showingContextMenu" ref="occupied-dialog" :instance="props.instance"
                            :parent-width="parentWidth" :style="{
                                'zoom': (1 / zoom).toFixed(2)
                            }" @dialogClosed="closeOccupyDialog" />
                    </slot>
                </div>
                <div>
                    <slot name="moblist" v-if="showMobList">
                        <div class="absolute mob-list top-0 left-0">
                            <ol class="block list-decimal pl-4">
                                <li v-for="(mob, index) in props.zone.mobs"
                                    :class="`group mob-number-${index} dead-mob-${props.scoutReport.isMobDead(mob.id, props.instance) ? 1 : 0}`"
                                    title="Toggle this mob as being dead/alive. Dead mobs will count toward scouting completion for this zone."
                                    :key="`moblist-mob-${mob.id}-${props.instance}`" @click="toggleMobStatus(mob)">
                                    <div class="flex items-center">
                                        <span>{{ getDisplayName(mob, props.language ?? 'en') }}</span>
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
                                {{ getDisplayName(zone, 'en') }}
                                <span class="mapInstanceNumber"
                                    v-if="props.scoutReport.getInstanceCountForZone(zone.id) > 1">{{
                                        intToInstanceMapping[props.instance]
                                    }}</span>
                            </div>
                            <div v-if="is_hovered" class="zone-coords">{{ x_hover }}, {{ y_hover }}</div>
                        </div>
                    </slot>
                </div>
                <template #buttons>
                    <div
                        class="absolute bottom-2 right-2 scale-75 origin-bottom-right xl:bottom-4 xl:right-4 xl:scale-75">
                        <ul class="list-none">
                            <li class="btn-nav border-b-[#dee2e6] rounded-t-md">
                                <a @pointerdown.left="ev => resetTransform(ev)">
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
                                <a @pointerdown.left="ev => zoomIn(ev)">
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
                                <a @pointerdown.left="ev => zoomOut(ev)">
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
import { convertCoordToPercent, formatCoordinate, getDisplayName, intToInstanceMapping } from '@/classes/helpers';
import ScoutReport from '@/classes/ScoutReport';
import PointOccupiedDialog from '@/components/dialogs/PointOccupiedDialog.vue';
import ZoneMapPoint from '@/components/ZoneMapPoint.vue';
import { Zone } from '@/types/gametypes';
import { SkullIcon } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
// import ScrollOverlay from '@/components/ScrollOverlay.vue';
import VueZoomable, { ScrollOverlay } from "vue-zoomable";
import "vue-zoomable/dist/style.css";

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
const is_hovered = ref(false)

// Variables used by the Occupied contextmenu
// const PointOccupiedDialogRef = useTemplateRef('occupied-dialog')
const showingContextMenu = ref(false)
const selectedPoint = ref(null)
const contextX = ref(0)
const contextY = ref(0)
const parentWidth = ref(0)

onMounted(() => {
    // console.log(props, disableInteraction)
})

const resetTransform = (ev) => {
    zoom.value = 1
    pan.value = { x: 0, y: 0 }
}

const showContextMenu = function (e: PointerEvent, point) {
    const mob = props.scoutReport.getMobOnPoint(point, props.instance)
    // Don't allow points that have mobs on them to be marked occupied
    if (mob && mob.mob_id != null) {
        return
    }
    selectedPoint.value = point
    // contextX.value = e.srcElement.offsetLeft + 15
    // contextY.value = e.srcElement.offsetTop + 10
    contextX.value = e.pageX
    contextY.value = e.pageY
    parentWidth.value = e.srcElement.parentElement.offsetWidth
    showingContextMenu.value = true
}
const closeOccupyDialog = () => {
    showingContextMenu.value = false
}

const toggleMobStatus = function (mob) {
    if (!props.editmode) return
    props.scoutReport.toggleMobStatus(mob.id, props.instance)
}

const zoomIn = (ev) => {
    if (zoom.value >= 2.8) return
    zoom.value += 0.2
}

const zoomOut = (ev) => {
    if (zoom.value <= 1) return
    zoom.value -= 0.2
}

const mapImage = computed(() => {
    return `/maps/${props.zone.map_id}.webp`
})
</script>

<style scoped>
@reference '@resources/css/app.css';

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
