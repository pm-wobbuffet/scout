<template>
    <div class="map-container-block" @dblclick.prevent="handleDoubleClick">
        <div class="absolute top-0 left-0 size-full bg-cover"
            :style="`background-image: url('/maps/${zone.map_id}.png');`" @mousemove.self="handleMouseOver"
            @mouseout="handleMouseOut" />
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
            @contextmenu.prevent.stop="handleContextMenu($event, point)" @dblclick.stop=""
            @long-press.prevent="handleContextMenu($event, point)" />
        <div class="absolute flex items-center bottom-1 left-1 text-center text-xs bg-[rgba(0,0,0,0.5)] hover:bg-black font-bold px-2 py-1 text-white dark:text-slate-200"
            v-if="props.zone.allow_custom_points && props.editmode == true">
            <TriangleAlert class="text-yellow-600 font-bold text-xl" />
            <span>Spawn points unknown. Double click or use Import to add spawn points.</span>
        </div>
        <div class="zone-name pointer-events-none">
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
import { SkullIcon, TriangleAlert } from 'lucide-vue-next';
import { onMounted, ref, useTemplateRef } from "vue";

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

const handleContextMenu = function (e, point) {
    const mob = props.scoutReport.getMobOnPoint(point, props.instance)
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

const handleDoubleClick = function (e) {
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
    x_hover.value = x
    y_hover.value = y
    is_hovered.value = true
}
const handleMouseOut = function () {
    is_hovered.value = false
}
const getXYForEvent = function (event) {
    const x = Number(event.offsetX / event.srcElement.clientWidth * props.zone.max_coord_size + 1).toFixed(1)
    const y = Number(event.offsetY / event.srcElement.clientHeight * props.zone.max_coord_size + 1).toFixed(1)
    return { 'x': x, 'y': y }
}



const closeOccupyDialog = () => {
    showingContextMenu.value = false
}

</script>

<style lang="scss" scoped></style>