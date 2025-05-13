<template>
    <div class="map-container-block" :style="`--map-bg-image: url('/maps/${zone.map_id}.png')`"
    @mousemove.self="handleMouseOver"
    @mouseout="handleMouseOut">

        <div class="absolute mob-list">
            <ol class="block list-decimal pl-4">
                <li v-for="(mob, index) in props.zone.mobs" :class="`group mob-number-${index} dead-mob-${getDeadMobStatus(mob, props.instance)}`"
                title="Toggle this mob as being dead/alive. Dead mobs will count toward scouting completion for this zone."
                @click="toggleMobStatus(mob)">
                    <div class="flex items-center">
                    <span>{{ getDisplayName(mob, props.language ?? 'en') }}</span>
                    <SkullIcon class="inline-block p-0 m-0 pl-2 group-hover:visible"
                    :class="{'visible': getDeadMobStatus(mob, props.instance), 'invisible': !getDeadMobStatus(mob, props.instance)}"
                     />
                    </div>
                </li>
            </ol>
        </div>

        <div v-for="aetheryte in zone.aetherytes" class="aetheryte"
            :style="{ 'left': convertCoordToPercent(aetheryte.x, props.zone), 'top': convertCoordToPercent(aetheryte.y, props.zone) }"
            :data-title="getDisplayName(aetheryte, 'en')">
        </div>

        <button v-for="point in scoutReport.getSpawnPointsForZone(zone)"
            class=""
            :style="{ 'left': convertCoordToPercent(point.x, zone), 'top': convertCoordToPercent(point.y, zone) }"
            :data-coords="getPointTitleDisplay(point)"
            >2</button>

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

const toggleMobStatus = function(mob) {
    return true
}

const getDeadMobStatus = function(mob, instance) {
    return 0
}

/* Events and things */
const handleMouseOver = function(event){
    is_hovered.value = true
    let {x, y} = getXYForEvent(event)
    x_hover.value = x
    y_hover.value = y
}

const handleMouseOut = function(event) {
    is_hovered.value = false
}
const getXYForEvent = function(event) {
    let x = Number(event.offsetX / event.srcElement.clientWidth * props.zone.max_coord_size + 1).toFixed(1)
    let y = Number(event.offsetY / event.srcElement.clientHeight * props.zone.max_coord_size + 1).toFixed(1)
    return {'x': x, 'y': y}
}

const getPointTitleDisplay = function(point) {
    /*
    if (isPointOccupied(point)) {
        return `${point.x},${point.y} (Occupied By B/S Rank)`
    }
        */
    return `${point.x},${point.y}`
}

</script>

<style lang="scss" scoped></style>