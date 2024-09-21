<template>
    <div class="map-container-block"
        :style="`--map-bg-image: url('/maps/${zone.map_id}.png')`"
        @click="showingContextMenu = false"
        @keyup.escape.prevent="showingContextMenu = false"
        @mousemove.self="handleMouseOver"
        @mouseout="handleMouseOut"
        @dblclick.prevent="(e) => dblClickMap(e, zone)">
        <PointStatusDialog :x="contextX" :y="contextY" :point="selectedPoint" ref="contextMenuRef" v-show="showingContextMenu" v-model="model"
        :instance="props.instance"
        :occupied="isPointOccupied(selectedPoint)"
        @point-occupied-updated="handleOccupiedUpdate"
         />
        <div class="absolute mob-list">
            <ol class="block list-decimal pl-4">
                <li v-for="(mob, index) in zone.mobs" :class="`group mob-number-${index} dead-mob-${getDeadMobStatus(mob, props.instance)}`"
                title="Toggle this mob as being dead/alive. Dead mobs will count toward scouting completion for this zone."
                @click="toggleMobStatus(mob)">
                    <div class="flex items-center">
                    <span>{{ getDisplayName(mob, props.language) }}</span>
                    <SkullOutlineIcon class="inline-block p-0 m-0 pl-2 scale-90 group-hover:visible"
                    :class="{'visible': getDeadMobStatus(mob, props.instance), 'invisible': !getDeadMobStatus(mob, props.instance)}"
                     />
                    </div>
                </li>
            </ol>
        </div>
        <div v-for="aetheryte in zone.aetherytes" class="text-white absolute h-[32px] w-[32px] aetheryte"
            :style="{ 'left': convertCoordToPercent(aetheryte.x, zone), 'top': convertCoordToPercent(aetheryte.y, zone) }"
            :data-title="getDisplayName(aetheryte, props.language)">
        </div>
        <button v-for="point in zone.spawn_points.concat(getCustomSpawnPoints())"
            class=""
            :class="calculatePointDisplayClasses(point)"
            :data-class="`point-taken-by-${getTakenMob(point.id)?.mob_index}`"
            :style="{ 'left': convertCoordToPercent(point.x, zone), 'top': convertCoordToPercent(point.y, zone) }"
            :data-title="`${point.x},${point.y}`"
            :disabled="isPointDisabled(point.id) && !isPointSelected(point.id)"
            :data-coords="`${point.x}, ${point.y}`"
            @click.stop.prevent="assignMob(point)"
            @contextmenu.stop.prevent="showContext(point, $event)"
            @dblclick.stop.prevent="false">{{ getTakenMob(point.id)?.mob_index ?? '' }}</button>
        <div class="absolute flex items-center bottom-1 left-4 text-center text-xs bg-slate-300 font-bold"
        v-if="props.zone.allow_custom_points && editMode == true"
        >
            <AlertOutlineIcon class="text-yellow-800 font-bold text-xl" />
            <span>Spawn points unknown. Double click or use Import to add spawn points.</span>
        </div>
        <div class="text-right font-semibold text-xl zone-name">
            {{ getDisplayName(zone, language) }}
            <span v-if="zone.default_instances > 1">{{ instance }}</span>
            <div v-if="is_hovered">({{ x_hover }},{{ y_hover }})</div>
        </div>
    </div>
</template>

<script setup>
import { getDisplayName } from "@/helpers";
import { onMounted, ref, getCurrentInstance, onBeforeMount, watch, onUpdated } from "vue"
import AlertOutlineIcon from "vue-material-design-icons/AlertOutline.vue";
import SkullOutlineIcon from "vue-material-design-icons/SkullOutline.vue";
import PointStatusDialog from "./PointStatusDialog.vue";

// Parent model link
const model = defineModel()

const selectedPoints = ref({})
const mobPoints = ref({})
const x_hover = ref(0)
const y_hover = ref(0)
const is_hovered = ref(false)
const editMode = ref(false)
const showingContextMenu = ref(false)
const contextMenuRef = ref(null)
const selectedPoint = ref({})
const contextX = ref(0)
const contextY = ref(0)

let mobs = []
let mobsById = {}

const props = defineProps({
    zone: Object,
    instance: Number,
    editmode: Boolean,
    language: String,
})

watch(() => model.value, function() {
    //console.log("Model Changed")
    updateMobSpawnAssignments()
},{deep:true})

onBeforeMount(() => {
    mobs = props.zone.mobs
    mobs.forEach((mob) => {
        mobsById[mob.id] = mob
    })

    // If there are already mobs assigned to this zone, fill out the mobPoints dictionary
    updateMobSpawnAssignments()

    if(props.editmode) {
        editMode.value = props.editmode
    }
})

const calculatePointDisplayClasses = function(point) {
    let ret = {}
    let mobId = getTakenMob(point.id)
    if(mobId.mob_index != '') {
        ret[`point-taken-by-${mobId.mob_index}`] = true
    }
    if(isPointOccupied(point)) {
        ret['point-occupied'] = true
    }
    //console.log(point.id, ret)
    return ret
}

/**
 * Show the occupy/unoccupy context menu to the user
 * @param {Object} point
 * @param {PointerEvent} e
 */
const showContext = function(point, e) {
    if(isPointSelected(point.id)) {
        return
    }
    selectedPoint.value = point
    contextX.value = e.srcElement.offsetLeft + 15
    contextY.value = e.srcElement.offsetTop - 10
    showingContextMenu.value = true
}

/**
 * Check whether a point has been marked as occupied by a B/S rank by the reporter
 * @param {Object} point
 */
const isPointOccupied = function(point) {
    if(point && point?.id) {
        if(model.value.occupied_points) {
            return (point.id in model.value.occupied_points
                    && props.instance in model.value.occupied_points[point.id]
                    && model.value.occupied_points[point.id][props.instance] == 1
                )
        }
    }
    return false
}

/**
 * Process a point occupy/unoccupy event
 * @param {Object} point Point object
 * @param {Number} instance instance number
 * @param {Number} occupiedValue 1 = occupied, 0 = unoccupied
 */
const handleOccupiedUpdate = function(point, instance, occupiedValue) {
    //console.log(point, instance, occupiedValue)
    model.value.occupied_points = model.value.occupied_points || {}
    model.value.occupied_points[point.id] = model.value.occupied_points[point.id] || {}
    model.value.occupied_points[point.id][instance] = occupiedValue
    emit('point-occupied-updated', point, instance, occupiedValue)
}

const getDeadMobStatus = function(mob, instance) {
    return model.value.mob_status?.[mob.id]?.[props.instance] ?? 0
}

const toggleMobStatus = function(mob) {
    // If a mob is already assigned to a spot, we shouldn't allow it to be marked as dead
    if(mob.id in mobPoints.value) {
        // Remove the status if it was already there
        delete(model.value.mob_status[mob.id][props.instance])
        return
    }
    if(model.value.mob_status?.[mob.id]?.[props.instance]) {
        // This mob has already been marked as dead, so we should remove it
        delete(model.value.mob_status[mob.id][props.instance])
        emit('mobStatusUpdated', mob, props.instance, 0)
        //console.log(`Removed dead mob indicator for ${mob.id} and instance ${props.instance}`)
    } else {
        // We should mark the mob as dead for now
        model.value.mob_status[mob.id] = model.value.mob_status?.[mob.id] || {}
        model.value.mob_status[mob.id][props.instance] = 1
        emit('mobStatusUpdated', mob, props.instance, 1)
        //console.log(`Added dead mob indicator for ${mob.id} and instance ${props.instance}`)
    }
}

// Remap mobs that are positioned to the mobPoints reactive element
const updateMobSpawnAssignments = function() {
    if(! (props.zone.id in model.value.point_data) ) {
        model.value.point_data[props.zone.id] = {}
        model.value.point_data[props.zone.id][props.instance] = []
    }
    if(! (props.instance in model.value.point_data[props.zone.id]) ) {
        model.value.point_data[props.zone.id][props.instance] = []
    }
    let combinedSpawnPoints = props.zone.spawn_points.concat(getCustomSpawnPoints())
    // Always empty out the currently assigned mob array on update
    // This prevents users from getting locked out of assigning mobs because of a phantom
    // mob taking up a previously assigned point when it had already been zero'ed out
    // by another user.
    // https://github.com/pm-wobbuffet/scout/issues/2
    mobPoints.value = {}
    if(combinedSpawnPoints?.length > 0) {
        combinedSpawnPoints.forEach((point) => {
            if(model.value.point_data[props.zone.id][props.instance]?.length > 0) {
                model.value.point_data[props.zone.id][props.instance].forEach((mob) => {
                    //console.log(`Placing mob ${mob.mob_id} on point ${mob.point_id}`)
                    mobPoints.value[mob.mob_id] = mob.point_id
                    // Remove any manual death marks if a player/API marks the mob as being somewhere
                    if(model.value.mob_status?.[mob.mob_id]?.[props.instance]) {
                        delete(model.value.mob_status[mob.mob_id][props.instance])
                    }
                })
            }
        })
    }
}

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
        if(isPointOccupied(getSpawnPointById(point_id))) {
            // Firefox won't allow you to contextmenu on a disabled point
            //return true
        }
        // Check if it's one of the spots that's B or S rank only
        // But if we don't have finalized data yet, keep it open just in case
        if(spawnPoint?.valid_mobs?.length === 0 && !props.zone.allow_custom_points) {
            return true
        }
        let remainingAvailableMobs = spawnPoint.valid_mobs.filter(function(mob) {
            if(mob.id in mobPoints.value) {
                return false
            }
            // Check if a mob has been manually flagged as dead; if so, never include
            // it in the list
            if(getDeadMobStatus(mob, props.instance)) {
                return false
            }
            return true
        })
        // If there are no overall zone mobs available, don't bother with further logic
        if (remainingAvailableMobs.length === 0) {
            return true
        }
        return false
    } else {
        //console.log(`${point_id} not found`)
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
    //console.log(curMobOnPoint, validMobs)

    // is the point marked as occupied?
    // make sure we still allow them to cycle through mobs if one was already placed somehow
    // before the point was marked as occupied
    if(isPointOccupied(point) && curMobOnPoint.mob_index == '') {
        return
    }

    if(curMobOnPoint.mob_index != '') {
        //console.log(`Removing ${curMobOnPoint.name} from ${point.id}`)
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
    if (index > -1) {
        //console.log(`Removing index ${index}`)
        model.value.point_data[props.zone.id][props.instance].splice(index, 1)
        //console.log(model.value.point_data?.[props.zone.id]?.[props.instance])
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
    return point.valid_mobs.filter(function(mob) {
        //return ! (el.id in mobPoints.value)
        if(mob.id in mobPoints.value) {
            return false
        }
        // Check if a mob has been manually flagged as dead; if so, never include
        // it in the list
        if(getDeadMobStatus(mob, props.instance)) {
            return false
        }
        return true
    })
}

const emit = defineEmits(['pointUpdated', 'mobStatusUpdated', 'point-occupied-updated'])
</script>
