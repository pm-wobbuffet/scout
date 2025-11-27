<template>
    <div class="flex flex-row expac-list place-self-center m-auto expansion-list-container max-w-[100%]">
        <button type="button" v-for="expansion in getExpansions()" :key="expansion.id"
            class="text-center border p-1 px-4 expac-list-item"
            :class="{ 'selected-expansion': expansion.id == scoutReport.getSelectedExpansion() }"
            @click="setSelectedExpansion(expansion.id)">
            <div>{{ expansion.abbreviation }}</div>
            <div class="text-sm whitespace-nowrap text-nowrap">
                {{ getMappedMobsForExpac(expansion) }}/{{ mobCount(expansion) }}
            </div>
        </button>
    </div>
</template>

<script setup>
import { inject } from 'vue'

const scoutReport = inject('scoutReport')

const getExpansions = () => {
    return scoutReport?.value?.scouter_instance?.expansion_data
}

const getMappedMobsForExpac = expac => {
    return scoutReport.value.getFoundMobCountForExpansion(expac.id)
}
const mobCount = expac => {
    let total_mobs = 0
    expac.zones.forEach((zone) => {
        total_mobs += (zone.mobs.length ?? 0) * scoutReport.value.getInstanceCountForZone(zone.id)
    })
    return total_mobs
}

const setSelectedExpansion = expacId => {
    scoutReport.value.setSelectedExpansion(expacId)
}
</script>
