<template>
    <div class="map-container-block"
        :style="`--map-bg-image: url('/maps/${zone.map_id}.png')`" 
        @dblclick.prevent="(e) => dblClickMap(e, zone)">
        <div class="absolute mob-list">
            <ol class="block list-decimal pl-4">
                <li v-for="(mob, index) in zone.mobs" :class="`mob-number-${index}`">
                    {{ mob.name }}
                </li>
            </ol>
        </div>
        <div v-for="aetheryte in zone.aetherytes" class="text-white absolute h-[32px] w-[32px] aetheryte"
            :style="{ 'left': convertCoordToPercent(aetheryte.x, zone), 'top': convertCoordToPercent(aetheryte.y, zone) }"
            :data-title="aetheryte.name">
        </div>
        <button v-for="point in zone.spawn_points" class="" :class="`point-taken-by-${point.taken_by ?? ''}`"
            :style="{ 'left': convertCoordToPercent(point.x, zone), 'top': convertCoordToPercent(point.y, zone) }"
            :title="`${point.x},${point.y}`" 
            @click.stop.prevent="assignMob(zone, point, instance)"
            @dblclick.stop.prevent="false">{{ point.taken_by ?? '' }}</button>
        <div class="text-right font-semibold text-xl zone-name">
            {{ zone.name }}
            <span v-if="zone.default_instances > 1">{{ i }}</span>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    zone: Object,
    instance: Number,
})

const assignMob = function(zone, point, instance) {
    console.log(zone, point, instance)
}

const convertCoordToPercent = function(coord, zone) {
    //let c = (coord / Math.floor(43 / (zone.size_factor / 100) )) * 100
    let c = (coord - 1) / (zone.max_coord_size) * 100
    c = c.toString() + '%'
    return c
}

const mobCount = function(expac) {
    let mCount = 0
    expac.zones.forEach( (el) => {
        mCount += el.total_mobs
    })
    return mCount
}
</script>
