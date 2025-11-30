<template>
    <div>
        <span v-if="zone">{{ zone?.name }}</span>
        <span class="instanceNumber" v-if="hasInstances && instance_number">{{ instance_number }}</span>
        <span v-if="point">
            ({{ formatCoordinate(point.x) }}, {{ formatCoordinate(point.y) }})
        </span>
        <div v-if="mob">
            {{ mob.name }}
            <template v-if="props.logDetails.name == 'Mob Status Updated'">
                <span v-if="is_dead">(Dead)</span>
                <span v-if="!is_dead">(Alive)</span>
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import { formatCoordinate } from '@/classes/helpers';
import { computed, inject } from 'vue';

interface VersionUpdateDetails {
    name: string,
    reporter?: string,
    zone_id?: number,
    mob_id?: number,
    point_id?: number,
    point_type?: string
    is_dead?: boolean
}

interface Props {
    logDetails: VersionUpdateDetails
}

const props = defineProps<Props>();
const scoutReport = inject('scoutReport') as ScoutReport

const zone = computed(() => {
    if (props.logDetails.zone_id) {
        return scoutReport.value.scouter_instance.getZoneById(props.logDetails.zone_id)
    }
    return null
})
const hasInstances = computed(() => {
    if (zone.value) {
        return scoutReport.value.getInstanceCountForZone(zone.value.id) > 1
    }
    return false
})

const instance_number = computed(() => {
    if (props.logDetails.instance_number) {
        return props.logDetails.instance_number
    }
    return null
})
const point = computed(() => {
    if (props.logDetails.point_id) {
        return scoutReport.value.getSpawnPointById(props.logDetails.point_id, props.logDetails.point_type)
    }
    return null
})

const mob = computed(() => {
    if (props.logDetails.mob_id) {
        return scoutReport.value.scouter_instance.getMobById(props.logDetails.mob_id)
    }
    return null
})


</script>

<style scoped></style>