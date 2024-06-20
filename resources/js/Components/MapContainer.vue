<template>
    <div class="w-full min-h-[100vh]">
        <nav
            class="flex flex-wrap w-full items-center justify-between bg-slate-500 text-slate-100 p-2 min-h-[3rem] main-nav flex-grow-1">
            <div>Logo</div>
            <div class="flex expac-list">
                <div v-for="expansion in expac" :key="expansion.id" class="text-center border p-1 px-4 expac-list-item"
                    @click="setActiveExpac(expansion.id)"
                    :class="{ 'selected-expansion': expansion.id == selectedExp }">
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
                    <ZoneMap v-for="i in zone.default_instances" :id="`zonemap-${zone.id}-${i}`"
                        :key="`zonemap-${zone.id}-${i}`" :zone="zone" :instance="i" v-model="form.point_data"
                        @mapUpdated="handleMapUpdated"
                        @pointUpdated="handlePointUpdated"
                        />
                </template>
            </div>
            <aside class="sticky top-0 border border-gray-400 ml-1 self-start order-1 bg-white">
                <div>Top | Share</div>
                <div v-for="expac in getActiveExpac()">
                    <div class="font-bold bg-slate-300 p-1">{{ expac.name }}</div>
                    <ul>
                        <template v-for="zone in expac.zones">
                            <li v-for="i in zone.default_instances" class="hover:bg-slate-200 ml-2 pr-2"><a class="text-blue-500"
                                    :href="`#zonemap-${zone.id}-${i}`">{{ zone.name }}</a>
                                <span class="ml-1 font-bold text-blue-800" v-if="zone.default_instances > 1">{{ i
                                    }}</span>
                                <i class="text-sm ml-2">{{ getFoundMobCount(zone.id, i) }}/{{ zone.mobs.length }}
                                </i>
                            </li>
                        </template>
                    </ul>
                </div>
            </aside>
        </main>
    </div>
</template>

<script setup>
//  instance 1, 2, 3 icons for later
import { computed, onBeforeMount, onMounted, ref, watch } from "vue";
import ZoneMap from '@/Components/Map/ZoneMap.vue';
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(['mapUpdated', 'pointUpdated'])

const props = defineProps({
    expac: Array,
    scout: Object,
})

const defaultExp = ref(6)
const selectedExp = ref(6)

const form = useForm({
    point_data: {},
})


const handlePointUpdated = function(point, mob) {
    emit('pointUpdated', point, mob)
}

const handleMapUpdated = function() {
    emit('mapUpdated', form.point_data, getInstanceCounts())
}

// watch(form, () => {
//     emit('mapUpdated', form.point_data, getInstanceCounts())
// },{
// })

onBeforeMount(() => {
    if (props?.scout?.instance_data) {
        for (let i = 0; i < props.expac.length; i++) {
            let ex = props.expac[i]
            for (let j = 0; j < ex.zones.length; j++) {
                let z = ex.zones[j]
                if (z.id in props.scout.instance_data) {
                    //console.log("Found override instance data for", z)
                    z.default_instances = Number(props.scout.instance_data[z.id])
                }
            }
        }
    }
    if(props?.scout?.point_data) {
        // Need to add custom points if they exist in the data
        //console.log(props.scout.point_data)
        for( let mapId in props.scout.point_data ) {
            for( let instanceId in props.scout.point_data[mapId] ) {
                props.scout.point_data[mapId][instanceId].forEach((el) => {
                    if(el.point_id < 0) {
                        // Custom id, we need to add it to the zone's list of points
                        addCustomSpawnPoint(el, mapId, instanceId)
                    }
                    
                })
            }
        }
        form.point_data = props.scout.point_data
    }
})

const addCustomSpawnPoint = function(point_data, mapId, instanceId) {
    // Does the point already exist
    // point_data includes expansion_id for convenience from the DB
    let ex = getExpacById(point_data.expansion_id)
    if(!ex) return
    let sMap = ex.zones.find((el) => el.id == mapId)
    if(sMap && sMap.spawn_points && !sMap.spawn_points.find((z) => z.id == point_data.point_id)) {
        sMap.spawn_points.push({
            'x': point_data.x,
            'y': point_data.y,
            'zone_id': sMap.id,
            'id': point_data.point_id
        })
    }
    
}

const printForm = function () {
    form
        .transform((data) => ({
            ...data,
            instance_data: getInstanceCounts(),
        }))
        .post(route('scout.store'))
}

const getInstanceCounts = function () {
    let arr = {}
    props.expac.forEach((expac) => {
        expac.zones.forEach((zone) => {
            arr[zone.id] = zone.default_instances
        })
    })
    return arr
}

const getFoundMobCount = function (zone, instance_number) {
    return form.point_data?.[zone]?.[instance_number].length ?? 0
}

const getMappedMobsForExpac = function (expac) {
    //console.log(expac);

    let totalSeen = 0
    for (let i = 0; i < expac.zones.length; i++) {
        let z = expac.zones[i]
        for (let j = 1; j <= z.default_instances; j++) {
            totalSeen += form.point_data?.[z.id]?.[j].length ?? 0
        }
    }
    return totalSeen
}

const getExpacById = function(searchValue) {
    return props.expac.find((el) => el.id == searchValue)
}
const getActiveExpac = function () {
    //return [props.expac.find((el) => el.id == selectedExp.value)]
    return [getExpacById(selectedExp.value)]
}
const setActiveExpac = function (expac_id) {
    selectedExp.value = expac_id
}

const convertCoordToPercent = function (coord, zone) {
    //let c = (coord / Math.floor(43 / (zone.size_factor / 100) )) * 100
    let c = (coord - 1) / (zone.max_coord_size) * 100
    c = c.toString() + '%'
    return c
}

const getMapsForExpansion = function () {
    //return []
    let curExpac = props.expac.find((el) => el.id == selectedExp.value)
    //console.log(props.expac)
    if (curExpac.zones && curExpac.zones.length > 0) {
        return curExpac.zones
    }
    return []
}

const mobCount = function (expac) {
    // console.log(expac.zones)
    let mCount = 0
    expac.zones.forEach((el) => {
        mCount += (el.mobs.length * el.default_instances)
    })
    return mCount
}

</script>