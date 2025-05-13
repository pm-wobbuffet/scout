<template>
    <div class="map-image-list-grid order-2">
        <template v-for="zone in props.scoutReport.getZonesByExpansion()" :id="`zone-${zone.id}`">
            <ZoneMap v-for="i in props.scoutReport.getInstanceCountForZone(zone.id)"
            :id="`zonemap-${zone.id}-${i}`"
            :data-zone-id="`${zone.id}`"
            :data-instance="i"
            :zone="zone"
            :instance="i"
            :editmode="props.editmode"
            :scoutReport="props.scoutReport"
            />
        </template>
    </div>
</template>

<script setup>
import ScoutReport from '@/classes/ScoutReport';
import ZoneMap from '@/components/ZoneMap.vue';

const props = defineProps({
    scoutReport: ScoutReport,
    editmode: Boolean,
})
</script>

<style lang="scss" scoped>

</style>