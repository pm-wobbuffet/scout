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
            class="" 
            :class="`point-taken-by-${getTakenMob(point.id) ?? ''}`"
            :style="{ 'left': convertCoordToPercent(point.x, zone), 'top': convertCoordToPercent(point.y, zone) }"
            :title="`${point.x},${point.y}`" 
            @click.stop.prevent="assignMob(point)"
            @dblclick.stop.prevent="false">{{ selectedPoints[point.id] ?? '' }}</button>
        <div class="text-right font-semibold text-xl zone-name">
            {{ zone.name }}
            <span v-if="zone.default_instances > 1">{{ instance }}</span>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue"

const emit = defineEmits(['selectionsUpdated'])

const selectedPoints = ref({})
const mobPoints = ref({})

const props = defineProps({
    zone: Object,
    instance: Number,
})

watch(selectedPoints, function(newVal, oldVal) {
    //console.log('watch fired')
    emit('selectionsUpdated', selectedPoints.value)
},{deep: true})

const getTakenMob = function(id) {
    if(id in selectedPoints.value) {
        return selectedPoints.value[id]
    }
    return ''
}
const assignMob = function(point) {
    let validMobs = getValidMobs()
    let curSelection = selectedPoints.value[point.id] ?? 0

    console.log('Valid mobs', validMobs, 'Cur Selection', curSelection)
    if(validMobs.length == 0 && props.zone.mobs.length == 1)
    {
        console.log('current mobPoints: ', mobPoints.value[1])
        // There's only one mob for this zone, so we should let the user
        // go ahead and re-assign its spot to the new one
        delete(mobPoints.value[getTakenMob(mobPoints.value[1].id)])
        delete(selectedPoints.value[point.id])
        //curSelection = 0
        return
    }

    if(curSelection == props.zone.mobs.length) {
        // They reached the end of the mob list, so cycle back to resetting the node
        delete(mobPoints.value[getTakenMob(point.id)])
        delete(selectedPoints.value[point.id])
        return
    }
    
    if(getTakenMob(point.id) > 0) {
        // They already had this mob picked, so we need to clear that
        // before assigning the new mob
        delete(mobPoints.value[getTakenMob(point.id)])
        delete(selectedPoints.value[point.id])
    }
    if (validMobs.length > 0) {
        // There are still unassigned mobs available to place
        let mob = validMobs[0]
        mobPoints.value[mob.mob_index] = point
        selectedPoints.value[point.id] = mob.mob_index
    }
}

const getValidMobs = function() {
    let ret = []
    for( let i = 0; i < props.zone.mobs.length; i++ ) {
        let mob = props.zone.mobs[i]
        if( !(mob.mob_index in mobPoints.value) ) {
            ret.push(mob)
        }
    }
    return ret
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

// const assignMob = function(zone, point) {
//     //console.log(zone,point)
//     if(point.taken_by && point.taken_by != null) {
//         if(point.taken_by == zone.mobs.length) {
//             point.taken_by = null;
//             zone.mobs[zone.mobs.length - 1].taken_point = null
//             return
//         }
//     }
//     // If this zone only has one mob, go ahead and reassign the active point when they click
//     if (zone.mobs.length == 1 && zone.mobs[0].taken_point && zone.mobs[0].taken_point != null) {
//         let prevPoint = zone.spawn_points.find((el) => el.id == zone.mobs[0].taken_point)
//         point.taken_by = 1
//         zone.mobs[0]['taken_point'] = point.id
//         prevPoint.taken_by = null
//         return
//     }
//     for ( let i = 0; i < zone.mobs.length ; i++) {
//         if( !('taken_point' in zone.mobs[i]) || zone.mobs[i].taken_point == null) {
//             // The mob hasn't yet taken a spot, go ahead and assign it
//             zone.mobs[i]['taken_point'] = point.id
//             point['taken_by'] = i + 1
//             return
//         } else {
//             // The mob is already in a spot.
//             // If it's this one, clear things out
//             if (zone.mobs[i].taken_point == point.id) {
//                 zone.mobs[i].taken_point = null
//                 point.taken_by = null
//             }
//         }
//     }
// }

</script>
