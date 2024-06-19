<template>
    <div class="map-container-block"
        :style="`--map-bg-image: url('/maps/${zone.map_id}.png')`" 
        @dblclick.prevent="(e) => dblClickMap(e, zone)">
        <div class="absolute mob-list">
            <ol class="block list-decimal pl-4">
                <li v-for="(mob, index) in zone.mobs" :class="`mob-number-${index}`">
                    {{ mob.name }}
                </li>
            </ol>
        </div>
        <div v-for="aetheryte in zone.aetherytes" class="text-white absolute h-[32px] w-[32px] aetheryte"
            :style="{ 'left': convertCoordToPercent(aetheryte.x, zone), 'top': convertCoordToPercent(aetheryte.y, zone) }"
            :data-title="aetheryte.name">
        </div>
        <button v-for="point in zone.spawn_points"
            :key="`spawnpoint-${point.id}-${props.instance}`" 
            class="" 
            :class="`point-taken-by-${getTakenMob(point.id) ?? ''}`"
            :style="{ 'left': convertCoordToPercent(point.x, zone), 'top': convertCoordToPercent(point.y, zone) }"
            :title="`${point.x},${point.y}`" 
            :disabled="Object.keys(selectedPoints).length >= zone.mobs.length && !selectedPoints[point.id]"
            @click.stop.prevent="assignMob(point)"
            @dblclick.stop.prevent="false">{{ selectedPoints[point.id] ?? '' }}</button>
        <div class="text-right font-semibold text-xl zone-name">
            {{ zone.name }}
            <span v-if="zone.default_instances > 1">{{ instance }}</span>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, watch } from "vue"

const emit = defineEmits(['selectionsUpdated'])

const selectedPoints = ref({})
const mobPoints = ref({})

const props = defineProps({
    zone: Object,
    instance: Number,
})

watch(selectedPoints, function(newVal, oldVal) {
    emit('selectionsUpdated', selectedPoints.value)
}, {deep: true})

const getTakenMob = function(id) {
    if(id in selectedPoints.value) {
        return selectedPoints.value[id]
    }
    return ''
}
const removeMob = function(point, mob_index) {
    //console.log('Removing', mob_index, 'from', mobPoints)
    delete mobPoints.value[mob_index]
    delete selectedPoints.value[point.id]
}
const placeMob = function(point, mob_index) {
    //console.log(`Placing indexed-mob ${mob_index} on `, point)
    selectedPoints.value[point.id] = mob_index
    mobPoints.value[mob_index] = point
}
const getValidMobs = function() {
    let ret = []
    //console.log('Getting valid mobs, current mobPoints', mobPoints)
    for( let i = 0; i < props.zone.mobs.length; i++ ) {
        let mob = props.zone.mobs[i]
        if( !(mob.mob_index in mobPoints.value) ) {
            ret.push(mob)
        }
    }
    return ret
}

const assignMob = function(point) {
    let validMobs = getValidMobs()
    let curMobOnPoint = getTakenMob(point.id)

    // Is this point already occupied?
    if ( curMobOnPoint > 0 ) {
        // If this zone has more than 1 A rank, we can cycle to the next valid mob and insert it as the
        removeMob(point, curMobOnPoint)
        if (curMobOnPoint == props.zone.mobs.length) {
            // If they click while on the 2nd mob for a zone, just remove the mob and return
            // so they get a blank free button
            return
        }
        // place the next valid mob on the point
        if (validMobs.length > 0) {
            placeMob(point, validMobs[0].mob_index)
        }
    } else {
        // No mob on this spot yet, are there available mobs?
        if ( validMobs.length > 0 ) {
            // Yes
            placeMob(point, validMobs[0].mob_index)
        } else {
            // Clear out the node
            removeMob(point, curMobOnPoint)
        }
    }
}

const convertCoordToPercent = function(coord, zone) {
    let c = (coord - 1) / (zone.max_coord_size) * 100
    c = c.toString() + '%'
    return c
}

const mobCount = function(expac) {
    let mCount = 0
    expac.zones.forEach( (el) => {
        mCount += el.total_mobs
    })
    return mCount
}

const dblClickMap = function(event,zone)
{
    let x = Number(event.offsetX / event.srcElement.clientWidth * zone.max_coord_size + 1).toFixed(1)
    let y = Number(event.offsetY / event.srcElement.clientHeight * zone.max_coord_size + 1).toFixed(1)
    let lastEl = zone.spawn_points.push({
        'x': x,
        'y': y,
        'zone_id': zone.id,
        'id': -1 * Date.now(),
    })
    // Assign a mob to this point since they're creating it for a reason
    assignMob(zone.spawn_points[lastEl - 1])
}

</script>
