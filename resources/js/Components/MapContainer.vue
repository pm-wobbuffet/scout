<template>
    <div class="w-full min-h-[100vh]">
        <nav
            class="flex flex-wrap w-full items-center justify-between bg-slate-500 text-slate-100 p-2 min-h-[3rem] main-nav flex-grow-1">
            <div>
                <a href="/"><img src="/turtleknife.png" height="40" width="90" class="inline" alt="The turtle says to murder" /></a>
            </div>
            <div class="flex expac-list place-self-center">
                <div v-for="expansion in expac" :key="expansion.id" class="text-center border p-1 px-4 expac-list-item"
                    @click="setActiveExpac(expansion.id)"
                    :class="{ 'selected-expansion': expansion.id == selectedExp }">
                    <div>{{ expansion.abbreviation }}</div>
                    <div class="text-sm">
                        {{ getMappedMobsForExpac(expansion) }} / {{ mobCount(expansion) }}
                    </div>
                </div>
            </div>
            <div class="flex">
                <a href="#" @click.stop.prevent="printForm()" class="w-[90px]"> </a>
            </div>
        </nav>
        <main class="map-main-window">
            <div class="map-image-list order-2">
                <template v-for="zone in getMapsForExpansion()">
                    <ZoneMap v-for="i in zone.default_instances" :id="`zonemap-${zone.id}-${i}`"
                        :key="`zonemap-${zone.id}-${i}`" 
                        :zone="zone" 
                        :instance="i" 
                        :editmode="props.editmode"
                        v-model="form.point_data"
                        @mapUpdated="handleMapUpdated"
                        @pointUpdated="handlePointUpdated"
                        />
                </template>
            </div>
            <aside class="sticky top-0 border border-gray-400 ml-1 self-start order-1 bg-white">
                <div class="p-2">
                    <a href="#" class="rounded-md bg-blue-400 px-3 text-white py-1 mr-1 font-bold"
                    ><ArrowUpIcon /> Top</a>
                    <a href="#" class="rounded-md bg-blue-700 px-3 text-white py-1 font-bold"
                    v-if="props.scout"
                    @click.prevent="showShareDialog"
                    ><ExportIcon /> Share</a>
                    <a href="#" class="rounded-md bg-blue-700 px-3 text-white py-1 font-bold" v-else
                    @click.prevent="submitForm"><ExportIcon /> Share</a>
                </div>
                <!--
                <div class="p-2 text-center">
                    <a href="#" class="rounded-md bg-slate-400 px-3 text-white py-1 font-bold">
                        <ContentCopyIcon />
                        Import
                    </a>
                </div>
                -->
                <div v-for="expac in getActiveExpac()">
                    <div class="font-bold bg-slate-300 p-1">
                        {{ expac.name }}
                        <div class="italic text-sm inline-block">{{ getMappedMobsForExpac(expac) }}/{{ mobCount(expac) }}</div>
                    </div>
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
        <dialog id="shareModal" class="relative" v-if="scout">
            <h1 class="font-bold text-2xl mb-4">Share View-Only Map</h1>
            <p class="text-sm">This link provides a view only copy of the map. Users cannot submit changes to the map.</p>
            <div class="bg-blue-500 text-white p-4 mb-4 relative cursor-pointer"
            @click="copyLink(route('scout.view', {scout: props.scout.slug}))">
                <span
                >{{ route('scout.view', {scout: props.scout.slug}) }}</span>
                <div class="absolute bottom-0 right-0.5"><ContentCopyIcon /></div>
            </div>
            <h1 class="font-bold text-2xl mb-4">Share Editable Map</h1>
            <p class="text-sm">This link will allow users to edit/add points to the map, so only give it to trusted users.</p>
            <div class="bg-blue-500 text-white p-4 mb-4 relative cursor-pointer"
            @click="copyLink(route('scout.view', {scout: props.scout.slug, password: props.scout.collaborator_password}))">
                <span
                >{{ route('scout.view', {scout: props.scout.slug, password: props.scout.collaborator_password}) }}</span>
                <div class="absolute bottom-0 right-0.5"><ContentCopyIcon /></div>
            </div>
            <div class="absolute bottom-0 font-bold opacity-0 transition-opacity duration-300 w-[90%] text-center" id="copied-msg">Copied to clipboard!</div>
        </dialog>
    </div>
</template>

<script setup>
//  instance 1, 2, 3 icons for later
import { computed, onBeforeMount, onMounted, ref, watch } from "vue";
import ZoneMap from '@/Components/Map/ZoneMap.vue';
import { useForm, Link } from "@inertiajs/vue3";
import ArrowUpIcon from "vue-material-design-icons/ArrowUp.vue";
import ExportIcon from "vue-material-design-icons/Export.vue";
import ContentCopyIcon from "vue-material-design-icons/ContentCopy.vue";
import ClipboardArrowUpOutlineIcon from "vue-material-design-icons/ClipboardArrowUpOutline.vue";

const emit = defineEmits(['mapUpdated', 'pointUpdated'])
const processUpdate = function(payload) {
    if('point_data' in payload) {
        props.scout.point_data = payload.point_data
    }
}
defineExpose({
    processUpdate
})

const props = defineProps({
    expac: Array,
    scout: Object,
    editmode: Boolean,
})

const defaultExp = ref(6)
const selectedExp = ref(6)

const form = useForm({
    point_data: {},
})


const copyLink = async function(linkText) {
    try {
        await navigator.clipboard.writeText(linkText)
        document.getElementById('copied-msg').classList.remove('opacity-0')
        setTimeout(() => {
            document.getElementById('copied-msg').classList.add('opacity-0')
        }, 2000)
    } catch (err) {
        // Silently fail
    }
}

const showShareDialog = function() {
    document.getElementById('shareModal').showModal()
}

const handlePointUpdated = function(point, mob, zone_id, instance_number) {
    emit('pointUpdated', point, mob, form.point_data, getInstanceCounts(), zone_id, instance_number)
}

const handleMapUpdated = function() {
    emit('mapUpdated', form.point_data, getInstanceCounts())
}

onMounted(() => {
    const dialog = document.getElementById('shareModal')
    if(dialog) {
        dialog.addEventListener("click", function(event) {
        const rect = dialog.getBoundingClientRect();
        const isInDialog = (
            rect.top <= event.clientY &&
            event.clientY <= rect.top + rect.height &&
            rect.left <= event.clientX &&
            event.clientX <= rect.left + rect.width
        );
        if (!isInDialog) {
            dialog.close();
        }
    });
    }
    
    //document.getElementById('shareModal').showModal()
})

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
                if(props.scout.point_data[mapId][instanceId]?.length > 0) {
                    props.scout.point_data[mapId][instanceId].forEach((el) => {
                        if(el.point_id < 0) {
                            // Custom id, we need to add it to the zone's list of points
                            addCustomSpawnPoint(el, mapId, instanceId)
                        }
                    })
                }
            }
        }
        form.point_data = props.scout.point_data
    }
    // Should we update the default displayed expansion?
    // Cycle through expacs and if there are any mapped mobs for it, set that tab to be the active
    props.expac.forEach((expansion) => {
        if (getMappedMobsForExpac(expansion) > 0) {
            selectedExp.value = expansion.id
        }
    })
})

const getClosestSpawnPoint = function(zone, x, y, mob) {
    const d = function(pointOne, pointTwo) {
        return Math.sqrt(Math.pow(pointTwo.x - pointOne.x, 2) + Math.pow(pointTwo.y - pointOne.y, 2))
    }
    const closest = zone.spawn_points.sort((a, b) => {
        return d(a, b)
    })
    console.log(zone, x, y, mob, closest.slice(0, 1))
    /*
    function d(point) {
        return Math.pow(parseFloat(point.x), 2) + Math.pow(parseFloat(point.y), 2);
    }

    function trueDistance(pointOne, pointTwo) {
        return Math.sqrt(Math.pow(pointTwo.x - pointOne.x, 2) + Math.pow(pointTwo.y - pointOne.y, 2))
    }

    let spawnpoints = [{x: x, y: y}, ...zone.spawn_points]
    let closest = spawnpoints.slice(1).reduce(function (min, p) {
        if (d(p) < min.d) min.point = p;
            return min;
        }, { point: spawnpoints[0], d: d(spawnpoints[0]) })
    console.log(x, y, closest, trueDistance(spawnpoints[0], closest.point, mob))
    */
}

const getZoneByName = function(zoneName) {
    for(let i = 0; i < props.expac.length; i++) {
        for(let j = 0; j < props.expac[i].zones.length; j++) {
            let z = props.expac[i].zones[j]
            if(z.name == zoneName) {
                return z
            }
        }
    }
}

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

const submitForm = function() {
    if(props.editmode || props.editmode == true && !props.scout) {
        form
        .transform((data) => ({
            ...data,
            instance_data: getInstanceCounts(),
        }))
        .post(route('scout.store'))
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