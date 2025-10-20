<template>
    <div class="map-container-block" :style="`--map-bg-image: url('/maps/${zone.map_id}.png');`"
        @mousemove.self="handleMouseOver" @mouseout="handleMouseOut">
        <div class="absolute top-0 left-0 size-full bg-cover" style="background-image: var(--map-bg-image);" />
        <PointOccupiedDialog :x="contextX" :y="contextY" :point="selectedPoint" v-show="showingContextMenu"
            ref="occupied-dialog" :instance="props.instance" :parent-width="parentWidth"
            @dialogClosed="closeOccupyDialog" />
        <div class="absolute mob-list">
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
        <div v-for="aetheryte in zone.aetherytes" class="aetheryte" :key="`aetheryte-${aetheryte.id}-${props.instance}`"
            :style="{ 'left': convertCoordToPercent(aetheryte.x, props.zone), 'top': convertCoordToPercent(aetheryte.y, props.zone) }"
            :data-title="getDisplayName(aetheryte, 'en')">
        </div>
        <ZoneMapPoint v-for="point in scoutReport.getSpawnPointsForZone(zone)"
            :key="`point-${point.id}-${props.instance}`" :point="point" :zone="props.zone" :instance="props.instance"
            :editmode="props.editmode" :scout-report="props.scoutReport"
            @contextmenu.prevent.stop="handleContextMenu($event, point)" />
        <div class="zone-name">
            {{ getDisplayName(zone, 'en') }}
            <span v-if="props.scoutReport.getInstanceCountForZone(zone.id) > 1">{{ instance }}</span>
            <div v-if="is_hovered">({{ x_hover }},{{ y_hover }})</div>
        </div>
    </div>
</template>

<script setup>
import { getDisplayName, convertCoordToPercent } from '@/classes/helpers';
import ScoutReport from '@/classes/ScoutReport';
import PointOccupiedDialog from '@/components/dialogs/PointOccupiedDialog.vue';
import ZoneMapPoint from '@/components/ZoneMapPoint.vue';
import { SkullIcon } from 'lucide-vue-next';
import { ref, useTemplateRef } from "vue";
import VueZoomable from "vue-zoomable";
import "vue-zoomable/dist/style.css";

const props = defineProps({
    zone: Object,
    instance: Number,
    editmode: Boolean,
    scoutReport: Object,
})

const is_hovered = ref(false)
const x_hover = ref(0)
const y_hover = ref(0)

// Variables used by the Occupied contextmenu
const PointOccupiedDialogRef = useTemplateRef('occupied-dialog')
const showingContextMenu = ref(false)
const selectedPoint = ref(null)
const contextX = ref(0)
const contextY = ref(0)
const parentWidth = ref(0)


const toggleMobStatus = function (mob) {
    if (!props.editmode) return
    props.scoutReport.toggleMobStatus(mob.id, props.instance)
}

/* Events and things */
const handleMouseOver = function (event) {
    is_hovered.value = true
    const { x, y } = getXYForEvent(event)
    x_hover.value = x
    y_hover.value = y
}

const handleMouseOut = function () {
    is_hovered.value = false
}
const getXYForEvent = function (event) {
    const x = Number(event.offsetX / event.srcElement.clientWidth * props.zone.max_coord_size + 1).toFixed(1)
    const y = Number(event.offsetY / event.srcElement.clientHeight * props.zone.max_coord_size + 1).toFixed(1)
    return { 'x': x, 'y': y }
}

const handleContextMenu = function (e, point) {
    const mob = props.scoutReport.getMobOnPoint(point.id, props.instance)
    // Don't allow points that have mobs on them to be marked occupied
    if (mob && mob.mob_id != null) {
        return
    }
    selectedPoint.value = point
    contextX.value = e.srcElement.offsetLeft + 15
    contextY.value = e.srcElement.offsetTop - 10
    parentWidth.value = e.srcElement.parentElement.offsetWidth
    showingContextMenu.value = true
}

const closeOccupyDialog = () => {
    showingContextMenu.value = false
}

</script>

<style lang="scss" scoped></style>