<template>
    <div class="map-image-list-grid order-2">
        <template v-for="zone in props.scoutReport.getZonesByExpansion()" :key="`zone-${zone.id}`">
            <!-- <ZoneMap v-for="i in props.scoutReport.getInstanceCountForZone(zone.id)" :key="`zonemap-${zone.id}-${i}`"
                :id="`zonemap-${zone.id}-${i}`" :data-zone-id="`${zone.id}`" :data-instance="i" :zone="zone"
                :instance="i" :editmode="props.editmode" :scoutReport="props.scoutReport" /> -->
            <zoomable-zone-map v-for="i in props.scoutReport.getInstanceCountForZone(zone.id)"
                :key="`zonemap-${zone.id}-${i}`" :id="`zonemap-${zone.id}-${i}`" :data-zone-id="`${zone.id}`"
                :data-instance="i" :zone="zone" :instance="i" :editmode="props.editmode"
                :scoutReport="props.scoutReport">
            </zoomable-zone-map>
        </template>
    </div>
</template>

<script setup>
import ZoneMap from '@/components/ZoneMap.vue';
import ZoomableZoneMap from '@/components/ZoomableZoneMap.vue';

const props = defineProps({
    scoutReport: Object,
    editmode: Boolean,
})
</script>

<style lang="scss" scoped></style>