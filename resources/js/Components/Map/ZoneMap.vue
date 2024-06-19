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
            :class="`point-taken-by-${getTakenMob(point.id)?.mob_index}`"
            :style="{ 'left': convertCoordToPercent(point.x, zone), 'top': convertCoordToPercent(point.y, zone) }"
            :title="`${point.x},${point.y}`" 
            :disabled="isPointDisabled(point.id)"
            @click.stop.prevent="assignMob(point)"
            @dblclick.stop.prevent="false">{{ getTakenMob(point.id)?.mob_index ?? '' }}</button>
        <div class="text-right font-semibold text-xl zone-name">
            {{ zone.name }}
            <span v-if="zone.default_instances > 1">{{ instance }}</span>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, getCurrentInstance } from "vue"

// Parent model link
// Ideally, data structure should look like model[zone][instance] = Array of objects containing point_id and mob_id
const model = defineModel()

const selectedPoints = ref({})
const mobPoints = ref({})
let mobs = []
let mobsById = {}

onMounted(() => {
    mobs = props.zone.mobs
    mobs.forEach((mob) => {
        mobsById[mob.id] = mob
    })
    // If we're not tracking this zone already, add an entry for it
    if(! (props.zone.id in model.value) ) {
        model.value[props.zone.id] = {}
        model.value[props.zone.id][props.instance] = []
    }
    if(! (props.instance in model.value[props.zone.id]) ) {
        model.value[props.zone.id][props.instance] = []
    }
    // Does the instance have data already?
    let c = model.value[props.zone.id][props.instance]
    if (c.length > 0) {
        c.forEach((el) => {
            assignMobManual(el.point_id, el.mob_id)
        })
    }
    //console.log(model)
    const instance = getCurrentInstance()
    instance?.proxy?.$forceUpdate()

})

const props = defineProps({
    zone: Object,
    instance: Number,
})

const getMobIndex = function(mob_id) {
    let a = mobs.find((el) => el.id == mob_id)
    return a?.mob_index ?? ''
}

const isPointDisabled = function(point_id) {
    let pts = model.value?.[props.zone.id]?.[props.instance] ?? []
    if (pts.length >= props.zone.mobs.length ) {
        if (isPointSelected(point_id)) {
            return false
        }
        return true
    }
    return false
}

const isPointSelected = function(point_id) {
    let pts = model.value?.[props.zone.id]?.[props.instance] ?? []
    return pts.filter((el) => el.point_id == point_id).length > 0
}

const convertCoordToPercent = function(coord, zone) {
    let c = (coord - 1) / (zone.max_coord_size) * 100
    c = c.toString() + '%'
    return c
}
const getTakenMob = function(point_id) {
    let pts = model.value?.[props.zone.id]?.[props.instance] ?? []
    if(pts.length == 0) {
        return {'mob_index' : ''}
    }
    let mob = pts.find( (el) => el.point_id == point_id)
    
    return mobsById?.[mob?.mob_id] ?? {'mob_index': ''}
}

const assignMobManual = function(point, mob) {

}

const assignMob = function(point) {
    let validMobs = getValidMobs()
    let curMobOnPoint = getTakenMob(point.id)

    if ( curMobOnPoint.mob_index > 0 ) {
        // If this zone has more than 1 A rank, we can cycle to the next valid mob and insert it as the
        removeMob(point, curMobOnPoint)
        if (curMobOnPoint.mob_index == props.zone.mobs.length) {
            // If they click while on the 2nd mob for a zone, just remove the mob and return
            // so they get a blank free button
            return
        }
        // place the next valid mob on the point
        if (validMobs.length > 0) {
            placeMob(point, validMobs[0])
        }
    } else {
        // No mob on this spot yet, are there available mobs?
        if ( validMobs.length > 0 ) {
            console.log('Placing mob')
            // Yes
            placeMob(point, validMobs[0])
        } else {
            // Clear out the node
            removeMob(point, curMobOnPoint)
        }
    }
    
}

const removeMob = function(point, mob) {
    //console.log('Received', mob, ' to remove')
    const index = model.value[props.zone.id][props.instance].findIndex((el) => el.mob_id == mob.id)
    if (index > -1) {
        model.value[props.zone.id][props.instance].splice(index, 1)
    }
    /*
    model.value[props.zone.id][props.instance] = model.value?.[props.zone.id]?.[props.instance].filter(function(pointObj) {
        // keep any point that has a different mob id
        return pointObj.mob_id != mob.id
    })
    */
}

const placeMob = function(point, mob) {
    //console.log(`Placing indexed-mob ${mob.mob_index} on `, point)
    model.value?.[props.zone.id]?.[props.instance].push({
        point_id: point.id,
        mob_id: mob.id
    })
    
}

const getValidMobs = function() {
    let pts = model.value?.[props.zone.id]?.[props.instance] ?? []
    let ret = mobs.filter(function(mob) {
        let shouldKeep = true
        pts.forEach((el) => {
            if(el.mob_id == mob.id) {
                // Mob has already been assigned
                shouldKeep = false
            }
        })
        return shouldKeep
    })
    return ret
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
