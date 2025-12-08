<template>
    <button class="" :class="calculatePointDisplayClasses(props.point)"
        :style="{ 'left': convertCoordToPercent(props.point.x, props.zone), 'top': convertCoordToPercent(props.point.y, props.zone) }"
        ref="refHook" :data-coords="getPointTitleDisplay(props.point)" :data-title="getPointTitleDisplay(props.point)"
        @mouseup.left="onClick" @contextmenu.prevent.stop="(ev) => emit('contextToggled', ev)">{{
            mobOnPoint?.mob_index ?? '' }}</button>
</template>

<script setup>
import ScoutReport from '@/classes/ScoutReport';
import { convertCoordToPercent } from '@/classes/helpers';
import { onLongPress } from '@vueuse/core';
import { computed, ref, useTemplateRef } from 'vue';

const props = defineProps({
    zone: Object,
    point: Object,
    instance: Number,
    scoutReport: ScoutReport,
    editmode: Boolean,
})
const refHook = useTemplateRef('refHook')
const isLongPressed = ref(true)

const emit = defineEmits(['longPress', 'contextToggled'])
onLongPress(refHook,
    (e) => {
        emit('longPress', e, props.point)
        isLongPressed.value = false
    },
    {
        delay: 300,
        onMouseUp: (dur, dist, isLongPress) => {
            console.log(dur, dist, isLongPress)
            isLongPressed.value = isLongPress
        },
        modifiers: {
        },
    }
)
const onClick = () => {
    if (isLongPressed.value !== true) {
        assignMob()
    }
}
// onLongPress(refHook,
//     (e) => {
//         emit("longPress", e, props.point)
//     },
//     {
//         onMouseUp: (dur, dist, isLongPress) => {
//             // If they didn't hold down for the intended duration,
//             // treat it like a normal click event
//             if (!isLongPress) {
//                 assignMob()
//             }
//         },
//         delay: 300
//     }
// )

const assignMob = function () {
    // End early if we're not in edit mode
    if (!props.editmode) return
    if (isPointOccupied.value === true) return
    if (isLongPressed.value === true) return

    props.scoutReport.cycleMobOnPoint(props.point, props.instance)
    isLongPressed.value = false
}

const isPointDisabled = computed(() => {
    if (!props.editmode && !isPointSelected.value) {
        return false
    }
    // Need to account for custom points not having a valid_mobs array by default
    const valid_mobs = props.point?.valid_mobs || props.zone.mobs
    if (valid_mobs.length === 0 && !props.zone.allow_custom_points) {
        return true
    }
    const remainingAvailableMobs = valid_mobs.filter((mob) => {
        if (props.scoutReport.isMobDead(mob.id, props.instance)) {
            return false
        }
        if (props.scoutReport.isMobAssigned(mob.id, props.instance)) {
            return false
        }
        return true
    })

    if (remainingAvailableMobs.length === 0) {
        return true
    }
    return false
})

const isPointOccupied = computed(() => {
    const m = props.scoutReport.getMobOnPoint(props.point, props.instance)
    if (!m) {
        return false
    }
    if (m.mob_id === null) {
        return true
    }
    return false
})

const isPointSelected = computed(() => {
    return props.scoutReport.point_data.some((mobpoint) => {
        return mobpoint.mob_id !== null
            && mobpoint.mob_id > 0
            && mobpoint.instance_number == props.instance
            && mobpoint.point_id == props.point.id
            && mobpoint.point_type == props.point.point_type
    })
})

const mobOnPoint = computed(() => {
    const a = props.scoutReport.getMobOnPoint(props.point, props.instance)
    if (!a) {
        return false
    }
    return props.scoutReport.scouter_instance.getMobById(a.mob_id)
})

const calculatePointDisplayClasses = function (point) {
    const ret = {}

    if (props.scoutReport.isZoneScoutingComplete(
        props.scoutReport.scouter_instance.getZoneById(point.zone_id),
        props.instance
    ) || !props.editmode) {
        ret['point-disabled'] = true
    }

    if (isPointDisabled.value) {
        ret['point-disabled'] = true
    }
    if (mobOnPoint.value !== false) {
        if (mobOnPoint.value === null) {
            ret['point-occupied'] = true
        } else {
            ret[`point-taken-by-${mobOnPoint.value.mob_index}`] = true
            ret['point-disabled'] = false
        }
    }

    return ret
}

const getPointTitleDisplay = function (point) {
    let ret = ""
    let x = point.x
    let y = point.y
    /* Override x and y display for points that have override data */
    const a = props.scoutReport.getMobOnPoint(props.point, props.instance)
    if (a && a.x) {
        x = a.x
    }
    if (a && a.y) {
        y = a.y
    }
    if (isPointOccupied.value) {
        ret = `${x},${y} (Occupied)`
    } else {
        ret = `${x}, ${y} PID: ${point.id}`
    }
    if (a && a.reporter) {
        ret += `(${a.reporter})`
    }
    return ret
}

</script>

<style lang="scss" scoped></style>