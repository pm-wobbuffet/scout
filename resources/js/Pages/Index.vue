<template>
    <div class="w-full min-h-[100vh]">
        <nav class="flex flex-wrap w-full items-center justify-between bg-slate-500 text-slate-100 p-2 min-h-[3rem] main-nav flex-grow-1">
            <div>Logo</div>
            <div class="flex expac-list">
                <div v-for="expansion in expac" :key="expansion.id" class="text-center border p-1 px-4 expac-list-item"
                @click="setActiveExpac(expansion.id)"
                :class="{'selected-expansion': expansion.id == selectedExp}">
                    <div>{{ expansion.abbreviation }}</div>
                    <div class="text-sm">
                        0 / {{ mobCount(expansion) }}
                    </div>
                    
                </div>
            </div>
            <div>Share</div>
        </nav>
        <main class="map-main-window">
            <div class="map-image-list order-2">
                <template v-for="zone in getMapsForExpansion()">
                    <ZoneMap v-for="i in zone.default_instances"
                        :key="`zonemap-${zone.id}-${i}`" 
                        :zone="zone"
                        :instance="i"
                        @selections-updated="(ev) => updateSelections(ev, zone, i)"
                    />
                </template>
                <!-- <template v-for="zone in getMapsForExpansion()" :key="`mapblock-${zone.id}`">
                    <div class="map-container-block" v-for="i in zone.default_instances" 
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
                        :style="{'left': convertCoordToPercent(aetheryte.x, zone), 'top': convertCoordToPercent(aetheryte.y, zone)}" :data-title="aetheryte.name">
                        </div>
                        <button v-for="point in zone.spawn_points" class=""
                        :class="`point-taken-by-${point.taken_by ?? ''}`"
                        :style="{'left': convertCoordToPercent(point.x, zone), 'top': convertCoordToPercent(point.y, zone)}"
                        :title="`${point.x},${point.y}`"
                        @click.stop.prevent="assignMob(zone, point)"
                        @dblclick.stop.prevent="false"
                         >{{ point.taken_by ?? '' }}</button>
                        <div class="text-right font-semibold text-xl zone-name">
                            {{ zone.name }}
                            <span v-if="zone.default_instances > 1">{{ i }}</span>
                        </div>
                    </div>
                </template> -->
            </div>
            <aside class="sticky border border-gray-400 p-2 self-start order-1">
                Top | Share

            </aside>
        </main>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import ZoneMap from '../Components/Map/ZoneMap.vue';
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    expac: Array,
})

const form = useForm({
    selectedPoints: {},
})

const defaultExp = ref(6)
const selectedExp = ref(6)


const updateSelections = function(event, zone, instance_number)
{
    //console.log(event, zone, instance_number)
    if (! (zone.id in form.selectedPoints) ) {
        form.selectedPoints[zone.id] = {}
    }
    if(! (instance_number in form.selectedPoints[zone.id])) {
        form.selectedPoints[zone.id][instance_number] = []
    }
    form.selectedPoints[zone.id][instance_number] = event
    //console.log(form.selectedPoints)
}

const assignMob = function(zone, point) {
    //console.log(zone,point)
    if(point.taken_by && point.taken_by != null) {
        if(point.taken_by == zone.mobs.length) {
            point.taken_by = null;
            zone.mobs[zone.mobs.length - 1].taken_point = null
            return
        }
    }
    // If this zone only has one mob, go ahead and reassign the active point when they click
    if (zone.mobs.length == 1 && zone.mobs[0].taken_point && zone.mobs[0].taken_point != null) {
        let prevPoint = zone.spawn_points.find((el) => el.id == zone.mobs[0].taken_point)
        point.taken_by = 1
        zone.mobs[0]['taken_point'] = point.id
        prevPoint.taken_by = null
        return
    }
    for ( let i = 0; i < zone.mobs.length ; i++) {
        if( !('taken_point' in zone.mobs[i]) || zone.mobs[i].taken_point == null) {
            // The mob hasn't yet taken a spot, go ahead and assign it
            zone.mobs[i]['taken_point'] = point.id
            point['taken_by'] = i + 1
            return
        } else {
            // The mob is already in a spot.
            // If it's this one, clear things out
            if (zone.mobs[i].taken_point == point.id) {
                zone.mobs[i].taken_point = null
                point.taken_by = null
            }
        }
    }
}

const setActiveExpac = function(expac_id) {
    selectedExp.value = expac_id
}

const convertCoordToPercent = function(coord, zone) {
    //let c = (coord / Math.floor(43 / (zone.size_factor / 100) )) * 100
    let c = (coord - 1) / (zone.max_coord_size) * 100
    c = c.toString() + '%'
    return c
}

const getMapsForExpansion = function() {
    //return []
    let curExpac = props.expac.find((el) => el.id == selectedExp.value)
    //console.log(props.expac)
    if(curExpac.zones && curExpac.zones.length > 0) {
        return curExpac.zones
    }
    return []
}

const mobCount = function(expac) {
    let mCount = 0
    expac.zones.forEach( (el) => {
        mCount += el.total_mobs
    })
    return mCount
}


</script>