<template>
    <div class="flex flex-wrap flex-row expac-list place-self-center m-auto expansion-list-container max-w-[100%] overflow-x-auto">
        <button type="button" v-for="expansion in getExpansions()" :key="expansion.id"
            class="text-center border p-1 px-4 expac-list-item"
            :class="{ 'selected-expansion': expansion.id == props.scoutReport.getSelectedExpansion() }"
            @click="setSelectedExpansion(expansion.id)">
            <div>{{ expansion.abbreviation }}</div>
            <div class="text-sm whitespace-nowrap text-nowrap">
                {{ getMappedMobsForExpac(expansion) }}/{{ mobCount(expansion) }}
            </div>
        </button>
    </div>
</template>

<script setup>
import ScoutReport from '@/classes/ScoutReport';
import Scouter from '@/classes/Scouter';
import { onBeforeMount } from 'vue';

const props = defineProps({
    scoutReport: Object
})
/** @var Scouter scouter */
let scouter = null
onBeforeMount(() => {
    scouter = props.scoutReport.scouter_instance
})

const getExpansions = function () {
    return props.scoutReport.scouter_instance.expansion_data
}

const getMappedMobsForExpac = function (expac) {
    return props.scoutReport.getFoundMobCountForExpansion(expac.id)
}
const mobCount = function (expac) {
    let total_mobs = 0
    expac.zones.forEach((zone) => {
        total_mobs += (zone.mobs.length ?? 0) * props.scoutReport.getInstanceCountForZone(zone.id)
    })
    return total_mobs
}

const setSelectedExpansion = function(expacId) {
    console.log("Setting selected Expansion", expacId)
    props.scoutReport.setSelectedExpansion(expacId)
}
</script>
