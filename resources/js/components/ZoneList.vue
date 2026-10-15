<template>
    <div class="order-2">
        <div :class="mapGridClass">
            <template v-for="zone in props.scoutReport.getZonesByExpansion()" :key="`zone-${zone.id}`">
                <zoomable-zone-map v-for="i in props.scoutReport.getInstanceCountForZone(zone.id)"
                    :key="`zonemap-${zone.id}-${i}`" :id="`zonemap-${zone.id}-${i}`" :data-zone-id="`${zone.id}`"
                    :data-instance="i" :zone="zone" :instance="i" :editmode="props.editmode" class="map-container-block"
                    :scoutReport="props.scoutReport">
                </zoomable-zone-map>
            </template>
        </div>
    </div>
</template>

<script setup>
import ZoomableZoneMap from '@/components/ZoomableZoneMap.vue';
import { computed, inject } from 'vue';

const props = defineProps({
    scoutReport: Object,
    editmode: Boolean,
})

const settings = inject('settings');

const mapGridClass = computed(() => {
    if (settings.value.mapLayout === 'fixed') {
        return "map-image-list-two-col";
    }
    return "map-image-list-grid";
})
</script>

<style lang="scss" scoped></style>