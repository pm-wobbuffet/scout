<template>
    <div class="map-container-block" :style="`--map-bg-image: url('/maps/${zone.map_id}.png')`"
        @mousemove.self="handleMouseOver" @mouseout="handleMouseOut">

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

        <button v-for="point in scoutReport.getSpawnPointsForZone(zone)" :key="`point-${point.id}-${props.instance}`"
            class="" :class="calculatePointDisplayClasses(point)"
            :style="{ 'left': convertCoordToPercent(point.x, zone), 'top': convertCoordToPercent(point.y, zone) }"
            :data-coords="getPointTitleDisplay(point)" :data-title="getPointTitleDisplay(point)">{{
                getMobIndexForDisplay(point) }}</button>

        <div v-for="aetheryte in zone.aetherytes" class="aetheryte" :key="`aetheryte-${aetheryte.id}-${props.instance}`"
            :style="{ 'left': convertCoordToPercent(aetheryte.x, props.zone), 'top': convertCoordToPercent(aetheryte.y, props.zone) }"
            :data-title="getDisplayName(aetheryte, 'en')">
        </div>

        <div class="zone-name">
            {{ getDisplayName(zone, 'en') }}
            <span v-if="zone.default_instances > 1">{{ instance }}</span>
            <div v-if="is_hovered">({{ x_hover }},{{ y_hover }})</div>
        </div>
    </div>
</template>

<script setup>
import { getDisplayName, convertCoordToPercent } from '@/classes/helpers';
import ScoutReport from '@/classes/ScoutReport';
import { SkullIcon } from 'lucide-vue-next';
import { ref } from "vue";

const props = defineProps({
    zone: Object,
    instance: Number,
    editmode: Boolean,
    scoutReport: ScoutReport,
})

const is_hovered = ref(false)
const x_hover = ref(0)
const y_hover = ref(0)

const toggleMobStatus = function (mob) {
    return true
}

const calculatePointDisplayClasses = function (point) {
    let ret = {}
    const mobPoint = props.scoutReport.getMobOnPoint(point.id, props.instance)

    if (mobPoint.length > 0) {
        const mobInfo = props.scoutReport.scouter_instance.getMobById(mobPoint[0].mob_id)
        if (mobInfo === null) {
            // Occupied point
            ret['point-occupied'] = true
        } else {
            if ('mob_index' in mobInfo && mobInfo.mob_index != '') {
                ret[`point-taken-by-${mobInfo.mob_index}`] = true
            }
        }
    } else {
        // See if scouting is complete
        if(props.scoutReport.isZoneScoutingComplete(props.scoutReport.scouter_instance.getZoneById(point.zone_id), props.instance)) {
            ret['point-disabled'] = true
        }
    }
    return ret
}

const getMobIndexForDisplay = function (point) {
    const mobPoint = props.scoutReport.getMobOnPoint(point.id, props.instance)

    if (mobPoint.length > 0) {
        const mobInfo = props.scoutReport.scouter_instance.getMobById(mobPoint[0].mob_id)
        if (mobInfo && 'mob_index' in mobInfo && mobInfo.mob_index != '') {
            return mobInfo.mob_index
        }
    }
    return ''
}

const getDeadMobStatus = function (mob, instance) {
    return 0
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

const getPointTitleDisplay = function (point) {
    /*
    if (isPointOccupied(point)) {
        return `${point.x},${point.y} (Occupied By B/S Rank)`
    }
        */
    return `${point.x}, ${point.y} PID: ${point.id}`
}

</script>

<style lang="scss" scoped></style>