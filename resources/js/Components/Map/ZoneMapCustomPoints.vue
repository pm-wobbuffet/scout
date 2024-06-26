<template>
    <div class="">

    </div>
    <div class="map-container-block w-[900px] h-[900px]"
        :style="`--map-bg-image: url('/maps/${zone.map_id}.png')`">

        <div class="absolute mob-list pointer-events-none">
            <ol class="block list-decimal pl-4">
                <li v-for="(mob, index) in zone.mobs" :class="`mob-number-${index}`">
                    {{ getDisplayName(mob, props.language) }}
                </li>
            </ol>
        </div>

        <div v-for="aetheryte in zone.aetherytes" class="text-white absolute h-[32px] w-[32px] aetheryte"
            :style="{ 'left': convertCoordToPercent(aetheryte.x, zone), 'top': convertCoordToPercent(aetheryte.y, zone) }"
            :data-title="getDisplayName(aetheryte, props.language)">
        </div>
    </div>
</template>

<script setup>
import { getDisplayName } from '@/helpers';

const props = defineProps({
    zone: Object,
})
const convertCoordToPercent = function(coord, zone) {
    let c = (coord - 1) / (zone.max_coord_size) * 100
    c = c.toString() + '%'
    return c
}
</script>