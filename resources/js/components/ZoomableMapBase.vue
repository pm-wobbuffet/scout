<template>
    <scroll-overlay>
        <VueZoomable :initial-zoom="1" :selector="`div.zone-map-container`" class="h-full">
            <div class="relative zone-map-container w-full h-full">
                <img :src="mapImage" class="" />
                <slot name="aetherytes" v-if="showAetherytes">
                    <div v-for="aetheryte in zone.aetherytes" class="aetheryte absolute z-10"
                        :key="`aetheryte-${aetheryte.id}`" :style="{
                            'left': convertCoordToPercent(aetheryte.x, props.zone), 'top': convertCoordToPercent(aetheryte.y, props.zone),
                            'zoom': (1 / zoom).toFixed(2)
                        }" :data-title="getDisplayName(aetheryte, 'en')">
                    </div>
                </slot>
                <slot name="points"></slot>
            </div>
            <template #buttons>
                <div></div>
            </template>
        </VueZoomable>
    </scroll-overlay>
</template>

<script setup lang="ts">
import { convertCoordToPercent, getDisplayName } from '@/classes/helpers';
import { Zone } from '@/types/gametypes';
import { computed, ref } from 'vue';
import VueZoomable, { ScrollOverlay } from 'vue-zoomable';


interface Props {
    zone?: Zone
}

const props = defineProps<Props>()

const showAetherytes = ref(true)
const zoom = ref(1)

const mapImage = computed(() => {
    return `/maps/${props.zone.map_id}.webp`
})

</script>

<style scoped></style>