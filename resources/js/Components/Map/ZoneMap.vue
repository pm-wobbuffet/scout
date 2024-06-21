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
            :disabled="isPointDisabled(point.id)"
            :data-coords="`${point.x}, ${point.y} id:${point.id}`"
            @click.stop.prevent="assignMob(point)"
            @dblclick.stop.prevent="false">{{ getTakenMob(point.id)?.mob_index ?? '' }}</button>
        <!-- <button v-for="point in getCustomSpawnPoints()"  
            class="" 
            :class="`point-taken-by-${getTakenMob(point.id)?.mob_index}`"
            :style="{ 'left': convertCoordToPercent(point.x, zone), 'top': convertCoordToPercent(point.y, zone) }"
            :data-title="`${point.x},${point.y}`" 
            :disabled="isPointDisabled(point.id)"
            :data-coords="`${point.x}, ${point.y} id:${point.id}`"
            @click.stop.prevent="assignMob(point)"
            @dblclick.stop.prevent="false">{{ getTakenMob(point.id)?.mob_index ?? '' }}</button> -->
        <div class="absolute flex items-center bottom-1 left-4 text-center text-xs bg-slate-300 font-bold"
        v-if="props.zone.allow_custom_points && props.editmode == true"
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
    //let lastEl = zone.spawn_points.push({
    let lastEl = model.value.custom_points.push({
        'x': x,
        'y': y,
        'zone_id': zone.id,
        'id': -1 * Date.now(),
    })
    //console.log(model.value.custom_points)
    // Assign a mob to this point since they're creating it for a reason
    //assignMob(zone.spawn_points[lastEl - 1])
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
    if(!props.editmode && !isPointSelected(point_id)) {
        return true
    }
    let pts = model.value.point_data?.[props.zone.id]?.[props.instance] ?? []
    if (pts.length >= props.zone.mobs.length ) {
        if (isPointSelected(point_id)) {
            return false
        }
        return true
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
const getTakenMob = function(point_id) {
    let pts = model.value.point_data?.[props.zone.id]?.[props.instance] ?? []
    if(pts.length == 0) {
        return {'mob_index' : ''}
    }
    let mob = pts.find( (el) => el.point_id == point_id)
    
    return mobsById?.[mob?.mob_id] ?? {'mob_index': ''}
}

const assignMob = function(point) {
    if(!props.editmode) return
    let validMobs = getValidMobs()
    let curMobOnPoint = getTakenMob(point.id)

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
    //emit('mapUpdated')
}

const removeMob = function(point, mob) {
    const index = model.value.point_data?.[props.zone.id]?.[props.instance].findIndex((el) => el.mob_id == mob.id) ?? -1
    if (index && index > -1) {
        model.value.point_data[props.zone.id][props.instance].splice(index, 1)
    }
}

const placeMob = function(point, mob) {
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
    //console.log(model.value.point_data?.[props.zone.id]?.[props.instance])
}

const getValidMobs = function() {
    let pts = model.value.point_data?.[props.zone.id]?.[props.instance] ?? []
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

const emit = defineEmits(['mapUpdated', 'pointUpdated'])
</script>
