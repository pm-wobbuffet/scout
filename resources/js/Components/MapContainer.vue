<template>
    <div class="w-full min-h-[100vh]">
        <nav
            class="flex flex-wrap w-full items-center justify-between bg-slate-500 text-slate-100 p-2 min-h-[3rem] main-nav flex-grow-1">
            <div class="shrink">
                <a href="/"><img src="/turtleknife.png" height="40" width="90" class="inline" alt="The turtle says to murder" /></a>
            </div>
            <div class="flex flex-row expac-list place-self-center m-auto">
                <div class="expansion-list-container flex">
                    <div v-for="expansion in expac" :key="expansion.id" class="text-center border p-1 px-4 expac-list-item"
                        @click="setActiveExpac(expansion.id)"
                        :class="{ 'selected-expansion': expansion.id == selectedExp }">
                        <div>{{ expansion.abbreviation }}</div>
                        <div class="text-sm">
                            {{ getMappedMobsForExpac(expansion) }} / {{ mobCount(expansion) }}
                        </div>
                    </div>
                </div>
                <Popover class="self-center mr-auto basis-0 shrink ml-2 relative">
                    <PopoverButton class="border flex items-center justify-center text-2xl bg-slate-400 rounded-sm"
                    ><SortIcon /></PopoverButton>
                    <PopoverPanel class="absolute min-w-max mt-1 z-50 text-sm left-1/2 -translate-x-1/2 bg-white border border-black p-4 text-black">
                        <div class="text-center border-b font-bold text-xl mb-2">Sort Order</div>
                        <div class="grid gap-2 items-center" style="grid-template-columns: 1fr auto;">
                            <template v-for="expac in getActiveExpac()">
                                <template v-for="(zone, index) in expac.zones" :key="`zone-sort-row-${zone.id}`">
                                    <div class="text-md font-bold">{{ zone.name }}</div>
                                    <div class="zone-sort-buttons">
                                        <button
                                        :disabled="index == expac.zones.length - 1"
                                        @click="changeZoneSort(expac.id, index, index+1)"
                                        ><ArrowDownIcon /></button>
                                        <button
                                        @click="changeZoneSort(expac.id, index, index-1)"
                                        :disabled="index == 0"
                                        ><ArrowUpIcon /></button>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </PopoverPanel>
                </Popover>
            </div>
            <!-- <button title="Settings" class="bg-slate-600 ml-2 rounded-md border flex justify-center items-center mr-auto text-2xl pb-[5px] px-1"
            ><CogIcon /></button> -->
            
            <div class="flex shrink">
                <button class="mr-2 flex items-center gap-x-2 bg-slate-600 p-2 rounded-md text-slate-100" @click.prevent="showMarkOverlay=true">
                    <NoteMultipleOutline class="inline-block" />
                    Summary
                </button>
                <Link as="button" method="post" :href="route('scout.clone', scout)" :preserve-state="false" 
                v-if="scout?.id && !props.editmode" class="bg-blue-400 p-2 rounded-md text-slate-100 flex items-center gap-x-1"
                ><ContentCopyIcon class="inline-block" /> Duplicate
                </Link>
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
                        v-model="form"
                        @pointUpdated="handlePointUpdated"
                        />
                </template>
            </div>
            <aside class="sticky top-0 border border-gray-400 ml-1 self-start order-1 bg-white">
                <div class="p-1 text-center">
                    <a href="#" class="inline-flex rounded-md bg-blue-400 px-3 text-white py-1 mr-1 font-bold"
                    ><ArrowUpIcon /> Top</a>
                    <a href="#" class="inline-flex rounded-md bg-blue-700 px-3 text-white py-1 font-bold"
                    v-if="props.scout"
                    @click.prevent="showShareDialog"
                    ><ExportIcon /> Share</a>
                    <a href="#" class="inline-flex rounded-md bg-blue-700 px-3 text-white py-1 font-bold" v-else
                    @click.prevent="submitForm"><ExportIcon /> Share</a>
                </div>
                <div class="p-1 text-center" v-if="props.scout && !props.scout.finalized_at">
                    <a href="#" class="inline-flex rounded-md bg-red-400 py-1 font-bold px-3 text-white"
                    title="Finalize/lock this scouting report. No further edits can be made after"
                    @click.prevent="handleFinalizeClick"
                    ><FileLockOutlineIcon />
                        Finalize</a>
                </div>
                <div v-for="expac in getActiveExpac()">
                    <div class="font-bold bg-slate-300 p-1">
                        {{ expac.name }}
                        <div class="italic text-sm inline-block">{{ getMappedMobsForExpac(expac) }}/{{ mobCount(expac) }}</div>
                    </div>
                    <ul class="text-sm">
                        <template v-for="zone in expac.zones">
                            <li v-for="i in zone.default_instances" class="hover:bg-slate-200 ml-2 pr-2"
                            :class="{'line-through': getFoundMobCount(zone.id, i) == zone.mobs.length}"><a class="text-blue-500"
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
            @click="copyLink(route('scout.view', {scout: props.scout.slug})+'?'+cacheBusterAppend)">
                <span
                >{{ route('scout.view', {scout: props.scout.slug}) }}?{{ cacheBusterAppend }}</span>
                <div class="absolute bottom-0 right-0.5"><ContentCopyIcon /></div>
            </div>
            <template v-if="scout.collaborator_password">
                <h1 class="font-bold text-2xl mb-4">Share Editable Map</h1>
                <p class="text-sm">This link will allow users to edit/add points to the map, so only give it to trusted users.</p>
                <div class="bg-blue-500 text-white p-4 mb-4 relative cursor-pointer"
                @click="copyLink(route('scout.view', {scout: props.scout.slug, password: props.scout.collaborator_password}))">
                    <span
                    >{{ route('scout.view', {scout: props.scout.slug, password: props.scout.collaborator_password}) }}</span>
                    <div class="absolute bottom-0 right-0.5"><ContentCopyIcon /></div>
                </div>
            </template>
            <div class="absolute bottom-0 font-bold opacity-0 transition-opacity duration-300 w-[90%] text-center" id="copied-msg">Copied to clipboard!</div>
        </dialog>
        <div class="mark-summary-overlay" id="MarkSummaryPanel" v-if="showMarkOverlay">
            <div class="mark-summary-panel">
                <div class="flex w-full justify-between mb-8">
                    <h1>Mark Summary</h1>
                    <button class="border rounded-md px-2 bg-slate-400 font-bold text-sm" @click.prevent="closeMarkOverlay()">Close</button>
                </div>
                <div v-for="expansion in expac.toReversed()" :key="expansion.id">
                    <template v-if="getMappedMobsForExpac(expansion) > 0">
                        <h2>{{ expansion.name }}</h2>
                        <template v-for="zone in expansion.zones">
                            <template v-for="i in zone.default_instances">
                                <fieldset v-if="getFoundMobCount(zone.id, i) > 0">
                                    <legend>{{ zone.name }}
                                        <span v-if="zone.default_instances > 1">{{ i }}</span>
                                    </legend>
                                    <div v-for="mob in form.point_data[zone.id][i]">
                                        <div>
                                            {{ zone.mobs.find((el) => el.id == mob.mob_id).name }}
                                            ({{ mob.x }}, 
                                            {{ mob.y }})
                                        </div>
                                    </div>
                                </fieldset>
                            </template>
                        </template>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
//  instance 1, 2, 3 icons for later
import { computed, onBeforeMount, onMounted, ref, watch } from "vue";
import ZoneMap from '@/Components/Map/ZoneMap.vue';
import { useForm, Link } from "@inertiajs/vue3";
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue";
import ArrowUpIcon from "vue-material-design-icons/ArrowUp.vue";
import ExportIcon from "vue-material-design-icons/Export.vue";
import ContentCopyIcon from "vue-material-design-icons/ContentCopy.vue";
import ClipboardArrowUpOutlineIcon from "vue-material-design-icons/ClipboardArrowUpOutline.vue";
import NoteMultipleOutline from "vue-material-design-icons/NoteMultipleOutline.vue";
import FileLockOutlineIcon from "vue-material-design-icons/FileLockOutline.vue";
import SortIcon from "vue-material-design-icons/Sort.vue";
import ArrowDownIcon from "vue-material-design-icons/ArrowDown.vue"

const emit = defineEmits(['pointUpdated', 'mapFinalized'])

const processUpdate = function(payload) {
    if('point_data' in payload) {
        //props.scout.point_data = payload.point_data
        form.point_data = payload.point_data
    }
    if('custom_points' in payload) {
        form.custom_points = payload.custom_points
    }
}
defineExpose({
    processUpdate
})

const props = defineProps({
    expac: Array,
    scout: Object,
    editmode: Boolean,
    newlyCreated: Boolean,
})

const defaultExp = ref(6)
const selectedExp = ref(6)
const cacheBusterAppend = ref(1)
const showMarkOverlay = ref(false)
const sortOrders = ref({})

const form = useForm({
    point_data: {},
    custom_points: [],
})


const closeMarkOverlay = function(event) {
    showMarkOverlay.value = false
}

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

const changeZoneSort = function(expac_id,first_idx, second_idx) {
    let zOne = getExpacById(expac_id).zones[first_idx]
    let zTwo = getExpacById(expac_id).zones[second_idx]
    // Swap out the values that are in the zones' sort_priority field
    if(zOne && zTwo) {
        let origFirstVal = sortOrders.value[zOne.id]
        sortOrders.value[zOne.id] = sortOrders.value[zTwo.id]
        sortOrders.value[zTwo.id] = origFirstVal
        localStorage.setItem('sortOrders', JSON.stringify(sortOrders.value))
    }
}

const showShareDialog = function() {
    cacheBusterAppend.value += 1
    document.getElementById('shareModal').showModal()
}

const handleFinalizeClick = function() {
    if(confirm('Do you really wish to finalize this scouting report? No further edits can be made afterwards.')) {
        emit('mapFinalized')
    }
}

const handlePointUpdated = function(point, mob, zone_id, instance_number) {
    emit('pointUpdated', point, mob, form.point_data, getInstanceCounts(), zone_id, instance_number, form.custom_points)
}

onMounted(() => {
    
    const dialog = document.getElementById('shareModal')
    // This bit of code lets you click outside of the Share modal in the backdrop area and have it
    // close the page
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
    // If they are being redirected from the main page after creating a new "Share" link
    // show them the Share modal
    if(props?.newlyCreated == true) {
        showShareDialog()
    }
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
        form.custom_points = props.scout.custom_points
    }
    
    props.expac.forEach((expansion) => {
        // Should we update the default displayed expansion?
        // Cycle through expacs and if there are any mapped mobs for it, set that tab to be the active
        if (getMappedMobsForExpac(expansion) > 0) {
            selectedExp.value = expansion.id
        }
        // Initialize sort order array
        let userSort = JSON.parse(localStorage.getItem('sortOrders') ?? '{}')

        expansion.zones.forEach((zone) => {
            if(zone.id in userSort) {
                sortOrders.value[zone.id] = userSort[zone.id]
            } else {
                sortOrders.value[zone.id] = zone.sort_priority
            }
        })
    })
})

const zoneSortFunction = function(a, b) {
    return (sortOrders.value[a.id] - sortOrders.value[b.id])
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
    /*
    if(sMap && sMap.spawn_points && !sMap.spawn_points.find((z) => z.id == point_data.point_id)) {
        sMap.spawn_points.push({
            'x': point_data.x,
            'y': point_data.y,
            'zone_id': sMap.id,
            'id': point_data.point_id
        })
    }
        */
    
}

const submitForm = function() {
    if(props.editmode || props.editmode == true && !props.scout) {
        form
        .transform((data) => ({
            ...data,
            instance_data: getInstanceCounts(),
        }))
        .post(route('scout.store'),{preserveState:false})
    }
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
    return form.point_data?.[zone]?.[instance_number]?.length ?? 0
}

const getMappedMobsForExpac = function (expac) {
    let totalSeen = 0
    for (let i = 0; i < expac.zones.length; i++) {
        let z = expac.zones[i]
        for (let j = 1; j <= z.default_instances; j++) {
            totalSeen += form.point_data?.[z.id]?.[j]?.length ?? 0
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
        return curExpac.zones.sort(zoneSortFunction)
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