<template>
    <div class="map-container-block"
        :style="`--map-bg-image: url('/maps/${zone.map_id}.png')`"
        @mousemove.self="handleMouseOver"
        @mouseout="handleMouseOut" 
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
        <button v-for="point in zone.spawn_points.concat(getCustomSpawnPoints())"  
            class="" 
            :class="`point-taken-by-${getTakenMob(point.id)?.mob_index}`"
            :style="{ 'left': convertCoordToPercent(point.x, zone), 'top': convertCoordToPercent(point.y, zone) }"
            :data-title="`${point.x},${point.y}`" 
            :disabled="isPointDisabled(point.id) && !isPointSelected(point.id)"
            :data-coords="`${point.x}, ${point.y} id:${point.id}`"
            @click.stop.prevent="assignMob(point)"
            @dblclick.stop.prevent="false">{{ getTakenMob(point.id)?.mob_index ?? '' }}</button>
        <div class="absolute flex items-center bottom-1 left-4 text-center text-xs bg-slate-300 font-bold"
        v-if="props.zone.allow_custom_points && editMode == true"
        >
            <AlertOutlineIcon class="text-yellow-800 font-bold text-xl" />
            <span>Spawn points unknown. Custom spawn points can be added by double clicking.</span>
        </div>
        <div class="text-right font-semibold text-xl zone-name">
            {{ zone.name }}
            <span v-if="zone.default_instances > 1">{{ instance }}</span>
            <div v-if="is_hovered">({{ x_hover }},{{ y_hover }})</div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, getCurrentInstance, onBeforeMount } from "vue"
import AlertOutlineIcon from "vue-material-design-icons/AlertOutline.vue";

// Parent model link
const model = defineModel()

const selectedPoints = ref({})
const mobPoints = ref({})
const x_hover = ref(0)
const y_hover = ref(0)
const is_hovered = ref(false)
const editMode = ref(false)
let mobs = []
let mobsById = {}

const props = defineProps({
    zone: Object,
    instance: Number,
    editmode: Boolean,
})

onBeforeMount(() => {
    mobs = props.zone.mobs
    mobs.forEach((mob) => {
        mobsById[mob.id] = mob
    })
    // If we're not tracking this zone already, add an entry for it
    if(! (props.zone.id in model.value.point_data) ) {
        model.value.point_data[props.zone.id] = {}
        model.value.point_data[props.zone.id][props.instance] = []
    }
    if(! (props.instance in model.value.point_data[props.zone.id]) ) {
        model.value.point_data[props.zone.id][props.instance] = []
    }

    // If there are already mobs assigned to this zone, fill out the mobPoints dictionary
    if(props.zone.spawn_points?.length > 0) {
        props.zone.spawn_points.forEach((point) => {
            if(model.value.point_data[props.zone.id][props.instance].length > 0) {
                model.value.point_data[props.zone.id][props.instance].forEach((mob) => {
                    //console.log(`Placing mob ${mob.mob_id} on point ${mob.point_id}`)
                    mobPoints.value[mob.mob_id] = mob.point_id
                })
            }
        })
    }

    if(props.editmode) {
        editMode.value = props.editmode
    }
    
})

const getCustomSpawnPoints = function() {
    return model.value.custom_points.filter((el) => el.zone_id == props.zone.id) ?? [];
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
const dblClickMap = function(event, zone)
{
    if(!zone.allow_custom_points) return
    let x = Number(event.offsetX / event.srcElement.clientWidth * zone.max_coord_size + 1).toFixed(1)
    let y = Number(event.offsetY / event.srcElement.clientHeight * zone.max_coord_size + 1).toFixed(1)
    let lastEl = model.value.custom_points.push({
        'x': x,
        'y': y,
        'zone_id': zone.id,
        'id': -1 * Date.now(),
        'valid_mobs': props.zone.mobs,
    })
    assignMob(model.value.custom_points[lastEl - 1])

}

const getXYForEvent = function(event) {
    let x = Number(event.offsetX / event.srcElement.clientWidth * props.zone.max_coord_size + 1).toFixed(1)
    let y = Number(event.offsetY / event.srcElement.clientHeight * props.zone.max_coord_size + 1).toFixed(1)
    return {'x': x, 'y': y}
}

const getMobIndex = function(mob_id) {
    let a = mobs.find((el) => el.id == mob_id)
    return a?.mob_index ?? ''
}

const isPointDisabled = function(point_id) {
    // Short circut and prevent point from being used in View only mode
    if(!props.editmode && !isPointSelected(point_id)) {
        return true
    }
    let spawnPoint = getSpawnPointById(point_id)
    if(spawnPoint) {
        // Check if it's one of the spots that's B or S rank only
        // But if we don't have finalized data yet, keep it open just in case
        if(spawnPoint?.valid_mobs?.length === 0 && !props.zone.allow_custom_points) {
            return true
        }
        let remainingAvailableMobs = spawnPoint.valid_mobs.filter((el) => !(el.id in mobPoints.value))
        // If there are no overall zone mobs available, don't bother with further logic
        if (remainingAvailableMobs.length === 0) {
            return true
        }
        return false
    } else {
        console.log(`${point_id} not found`)
    }
    return false
}

const isPointSelected = function(point_id) {
    let pts = model.value.point_data?.[props.zone.id]?.[props.instance] ?? []
    return pts.filter((el) => el.point_id == point_id).length > 0
}

const convertCoordToPercent = function(coord, zone) {
    let c = (coord - 1) / (zone.max_coord_size) * 100
    c = c.toString() + '%'
    return c
}

const getSpawnPointById = function(id) {
    return props.zone.spawn_points.concat(getCustomSpawnPoints()).find((el) => el.id == id)
    //return props.zone.spawn_points.find((el) => el.id == id)
}

const getTakenMob = function(point_id) {
    let pts = model.value.point_data?.[props.zone.id]?.[props.instance] ?? []
    if(pts.length == 0) {
        return {'mob_index' : ''}
    }
    let mob = pts.find( (el) => el.point_id == point_id)
    
    return mobsById?.[mob?.mob_id] ?? {'mob_index': ''}
}

const assignMob = function(point) {
    // Short circuit and prevent changing things in View mode
    if(!props.editmode) return
    let curMobOnPoint = getTakenMob(point.id)
    let validMobs = getValidMobsForPoint(point)

    if(curMobOnPoint.mob_index != '') {
        removeMob(point, curMobOnPoint)
    }
    // If there's a valid mob left, cycle to it
    // If the current mob is the last of the valid mobs for a zone,
    // cycle back to the blank state
    if(validMobs.length > 0 && curMobOnPoint.mob_index != props.zone.mobs.length) {
        placeMob(point, validMobs[0])
    }
    emit('pointUpdated', point, getTakenMob(point.id), props.zone.id, props.instance)
}

const removeMob = function(point, mob) {
    const index = model.value.point_data?.[props.zone.id]?.[props.instance].findIndex((el) => el.mob_id == mob.id) ?? -1
    if (index != undefined && index > -1) {
        model.value.point_data[props.zone.id][props.instance].splice(index, 1)
        delete mobPoints.value[mob.id]
    }
}

const placeMob = function(point, mob) {
    if(! (props.zone.id in model.value.point_data)) {
        model.value.point_data[props.zone.id] = {}
    }
    if(! (props.instance in model.value.point_data?.[props.zone.id])) {
        model.value.point_data[props.zone.id][props.instance] = []
    }
    model.value.point_data?.[props.zone.id]?.[props.instance].push({
        point_id: point.id,
        mob_id: mob.id,
        x: point.x,
        y: point.y,
        expansion_id: props.zone.expansion_id,
    })
    mobPoints.value[mob.id] = point.id
}

const getValidMobsForPoint = function(point) {
    return point.valid_mobs.filter(function(el) {
        return ! (el.id in mobPoints.value)
    })
}

const emit = defineEmits(['pointUpdated'])
</script>
