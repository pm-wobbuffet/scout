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
                        {{ getMappedMobsForExpac(expansion) }} / {{ mobCount(expansion) }}
                    </div>
                    
                </div>
            </div>
            <div>
                <a href="#" @click.stop.prevent="printForm()">Shhh</a>
            </div>
        </nav>
        <main class="map-main-window">
            <div class="map-image-list order-2">
                <template v-for="zone in getMapsForExpansion()">
                    <ZoneMap v-for="i in zone.default_instances"
                        :key="`zonemap-${zone.id}-${i}`" 
                        :zone="zone"
                        :instance="i"
                        v-model="form.selectedPoints"
                        @selections-updated="(ev) => updateSelections(ev, zone, i)"
                    />
                </template>
            </div>
            <aside class="sticky border border-gray-400 ml-1 self-start order-1">
                <div>Top | Share</div>
                <div v-for="expac in getActiveExpac()">
                    <div class="font-bold bg-slate-300 p-1">{{ expac.name }}</div>
                    <ul>
                        <template v-for="zone in expac.zones">
                            <li v-for="i in zone.default_instances"
                            >{{ zone.name }} 
                            <span v-if="zone.default_instances > 1">{{ i }}</span>
                            </li>
                        </template>
                    </ul>
                </div>
            </aside>
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
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

const printForm = function() {
    console.log(form.selectedPoints)
}

const updateSelections = function(event, zone, instance_number)
{
    if (! (zone.id in form.selectedPoints) ) {
        form.selectedPoints[zone.id] = {}
    }
    if(! (instance_number in form.selectedPoints[zone.id])) {
        form.selectedPoints[zone.id][instance_number] = []
    }
    form.selectedPoints[zone.id][instance_number] = event
}

const getMobsForZone = function(zone, instance_number) {

}

const getMappedMobsForExpac = function(expac) {
    return 0
    let totalSeen = 0
    for( let i = 0; i < expac.zones.length; i++ ) {
        let z = expac.zones[i]
        for ( let j = 1; j <= z.default_instances; j++) {
            if(form.selectedPoints[z.id] && j in form.selectedPoints[z.id]) {
                totalSeen += Object.keys(form.selectedPoints[z.id][j]).length
            }
        }
    }
    return totalSeen
}

const getActiveExpac = function() {
    return [props.expac.find((el) => el.id == selectedExp.value)]
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