<template>
    <div class="w-full min-h-[100vh]">
        <nav
            class="flex flex-wrap gap-1 w-full items-center justify-between bg-slate-500 dark:bg-slate-900 text-slate-100 dark:text-slate-400 p-2 min-h-[3rem] main-nav flex-grow-1">
            <div class="shrink">
                <a href="/"><img src="/turtleknife.png" height="40" width="90" class="inline"
                        alt="The turtle says to murder" /></a>
            </div>
            <div class="flex flex-wrap flex-row expac-list place-self-center m-auto">
                <div class="expansion-list-container flex">
                    <div v-for="expansion in expac" :key="expansion.id"
                        class="text-center border p-1 px-4 expac-list-item" @click="setActiveExpac(expansion.id)"
                        :class="{ 'selected-expansion': expansion.id == selectedExp }">
                        <div>{{ expansion.abbreviation }}</div>
                        <div class="text-sm">
                            {{ getMappedMobsForExpac(expansion) }} / {{ mobCount(expansion) }}
                        </div>
                    </div>
                </div>
                <div class="flex">
                    <Popover class="self-center mr-auto basis-0 shrink ml-2 relative">
                    <PopoverButton
                        class="border flex items-center justify-center text-2xl bg-slate-400 dark:bg-slate-700 dark:border-slate-500 rounded-sm"
                        title="Change the sort order of zones">
                        <SortIcon />
                    </PopoverButton>
                    <PopoverPanel
                        class="absolute min-w-max mt-1 z-50 text-sm left-1/2 -translate-x-1/2 bg-white dark:bg-slate-700 dark:text-slate-300 border border-black p-4 text-black">
                        <div class="text-center border-b font-bold text-xl mb-2">Sort Order</div>
                        <div class="grid gap-2 items-center" style="grid-template-columns: 1fr auto;">
                            <template v-for="expac in getActiveExpac()">
                                <template v-for="(zone, index) in expac.zones" :key="`zone-sort-row-${zone.id}`">
                                    <div class="text-md font-bold">{{ getDisplayName(zone, defaultLanguage) }}</div>
                                    <div class="zone-sort-buttons">
                                        <button :disabled="index == expac.zones.length - 1"
                                            @click="changeZoneSort(expac.id, index, index + 1)">
                                            <ArrowDownIcon />
                                        </button>
                                        <button @click="changeZoneSort(expac.id, index, index - 1)"
                                            :disabled="index == 0">
                                            <ArrowUpIcon />
                                        </button>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </PopoverPanel>
                </Popover>
                    <button
                        class="self-center mr-auto basis-0 shrink ml-2 relative border flex items-center justify-center text-2xl bg-slate-400 dark:bg-slate-700 rounded-sm dark:border-slate-500"
                        title="Toggle display mode between dark/light">
                        <WeatherNightIcon v-if="lightDarkMode == 'dark'" @click="toggleDarkMode('light')" />
                        <WeatherSunnyIcon v-else-if="lightDarkMode == 'light'" @click="toggleDarkMode('dark')" />
                    </button>
                    <button
                        class="self-center mr-auto basis-0 shrink ml-2 relative border flex items-center justify-center bg-slate-400 dark:bg-slate-700 rounded-sm dark:border-slate-500"
                        title="Cycle through available display languages (English, Japanese, French, German)"
                        @click="cycleLanguage()">{{
                            defaultLanguage.toUpperCase() }}</button>

                    <button
                        class="self-center whitespace-nowrap mr-auto basis-0 shrink ml-2 relative border flex items-center justify-center bg-slate-400 dark:bg-slate-700 rounded-sm dark:border-slate-500"
                        title="Enter a display name to submit with your scout report (optional)"
                        v-if="!editingUsername"
                        @click.prevent="toggleUsernameEditor()"
                        >{{ getUserDisplayName() }}</button>
                    <div class="flex self-center justify-center items-center basis-0 shrink"
                    v-if="editingUsername">
                        <input class="p-0  border-white self-center mr-auto basis-0 shrink ml-2 relative border flex items-center justify-center bg-slate-400 dark:bg-slate-700 rounded-sm dark:border-slate-500"
                        id="txtDisplayUserName"
                        maxlength="30"
                        @keypress.enter="setUserDisplayName()"
                        :value="getUserDisplayName()">
                        <button
                        @click="setUserDisplayName()"
                        >
                            <CheckBold />
                        </button>
                    </div>

                </div>

            </div>
            <div class="flex shrink">
                <button
                class="mr-2 flex items-center gap-x-2 bg-slate-600 p-2 rounded-md text-slate-100 dark:text-slate-300"
                title="Export marks as text"
                @click.prevent="copyMarksAsText">
                    <ExportIcon title="Export marks as text" /> Export
                </button>
                <button
                    class="mr-2 flex items-center gap-x-2 bg-slate-600 p-2 rounded-md text-slate-100 dark:text-slate-300"
                    @click.prevent="showMarkOverlay = true">
                    <NoteMultipleOutline class="inline-block" />
                    Summary
                </button>
                <Link as="button" method="post" :href="route('scout.clone', scout)" :preserve-state="false"
                    v-if="scout?.id && !props.editmode"
                    class="bg-blue-400 p-2 rounded-md text-slate-100 flex items-center gap-x-1">
                <ContentCopyIcon class="inline-block" /> Duplicate
                </Link>
            </div>
        </nav>
        <main class="map-main-window">
            <textarea name="txtExport" id="txtExport" :value="getExportTextValue"
            class="hidden"></textarea>
            <div class="map-image-list order-2">
                <template v-for="zone in getMapsForExpansion()">
                    <ZoneMap v-for="i in zone.default_instances" :id="`zonemap-${zone.id}-${i}`"
                        :key="`zonemap-${zone.id}-${i}`" :zone="zone" :instance="i" :editmode="props.editmode"
                        :language="defaultLanguage" :ref="el => { zoneMaps[zone.id + '-' + i] = el }" v-model="form"
                        @pointUpdated="handlePointUpdated"
                        @mobStatusUpdated="handleMobStatusUpdate"
                        @point-occupied-updated="handleOccupiedUpdate"
                         />
                </template>
            </div>
            <aside class="sticky top-0 border border-gray-400 ml-1 self-start order-1 bg-white dark:bg-slate-800">
                <div class="font-bold bg-slate-300 p-1 dark:bg-slate-700 dark:text-slate-300" v-if="form.title != ''">
                    <div class="text-sm max-w-[200px] overflow-hidden overflow-ellipsis" :title="form.title">{{ form.title }}</div>
                </div>
                <div class="grid grid-cols-2 gap-1 p-1 scale-90">
                    <a href="#"
                        class="inline-flex rounded-md bg-blue-400 dark:bg-blue-900 px-3 text-white dark:text-slate-300 py-1 mr-1 font-bold">
                        <ArrowUpIcon /> Top
                    </a>
                    <a href="#"
                        class="inline-flex rounded-md bg-blue-700 dark:bg-blue-800 px-3 text-white dark:text-slate-300 py-1 font-bold"
                        v-if="props.scout" @click.prevent="showShareDialog">
                        <ExportIcon /> Share
                    </a>
                    <a href="#"
                        class="inline-flex rounded-md bg-blue-700 px-3 dark:bg-blue-800 text-white dark:text-slate-300 py-1 font-bold"
                        v-else @click.prevent="submitForm">
                        <ExportIcon /> Share
                    </a>
                    <button class="inline-flex rounded-md bg-slate-500 dark:bg-yellow-800 py-1 px-3 font-bold text-white dark:text-slate-300 mr-1"
                        v-if="props.editmode && !props.scout?.finalized_at"
                        @click.prevent="showImportDialog">
                        <ClipboardTextMultipleOutlineIcon />Import
                    </button>
                    <a href="#"
                        class="inline-flex rounded-md bg-red-400 dark:bg-red-800 py-1 font-bold px-3 text-white dark:text-slate-300"
                        title="Finalize/lock this scouting report. No further edits can be made after"
                        v-if="props.scout && !props.scout.finalized_at && props.editmode"
                        @click.prevent="handleFinalizeClick">
                        <FileLockOutlineIcon />
                        Finish
                    </a>
                    <a href="#"
                    class="inline-flex rounded-md bg-green-700 dark:bg-green-800 py-1 font-bold px-3 text-white dark:text-slate-300"
                    title="Enter details about the scouting report, including names of scouts"
                    @click.prevent="showDetailsDialog()"
                    >
                    <ClipboardTextOutline />
                    Details</a>
                </div>
                <div v-for="expac in getActiveExpac()">
                    <div class="font-bold bg-slate-300 p-1 dark:bg-slate-700 dark:text-slate-300">
                        {{ getDisplayName(expac, defaultLanguage) }}
                        <div class="italic text-sm inline-block">{{ getMappedMobsForExpac(expac) }}/{{ mobCount(expac)
                            }}</div>
                    </div>
                    <ul class="text-sm">
                        <template v-for="zone in expac.zones">
                            <li v-for="i in zone.default_instances" class="hover:bg-slate-200 dark:hover:bg-slate-700 ml-2 pr-2"
                                :class="{ 'line-through': isZoneScoutingComplete(zone, i) }"><a
                                    class="text-blue-500" :href="`#zonemap-${zone.id}-${i}`">{{ getDisplayName(zone,
                                        defaultLanguage) }}</a>
                                <span class="ml-1 font-bold text-blue-800 dark:text-blue-400" v-if="zone.default_instances > 1">{{ i
                                    }}</span>
                                <i class="text-sm ml-2 text-black dark:text-slate-200">{{ getFoundMobCount(zone.id, i)
                                    }}/{{ zone.mobs.length }}
                                </i>
                            </li>
                        </template>
                    </ul>
                </div>
                <div v-if="form.scouts?.length > 0">
                    <div class="font-bold bg-slate-300 p-1 dark:bg-slate-700 dark:text-slate-300">Scouts</div>
                    <div>
                        <ul class="text-sm">
                            <li v-for="(scout, index) in form.scouts" :key="`scoutname-${index}`"
                            class="ml-2 pr-2 text-blue-800 dark:text-blue-400 overflow-ellipsis"
                            :title="scout"
                            >{{ scout }}</li>
                        </ul>
                    </div>
                </div>
            </aside>
        </main>
        <dialog id="scoutDetails" class="relative min-w-[400px]"
        @close="handleDetailsUpdated">
            <h1 class="font-bold text-2xl mb-4">Scout Report Details</h1>
            <div class="grid grid-cols-2 items-center gap-2 w-full" style="grid-template-columns: auto 1fr;">
                <div>Title</div>
                <div>
                    <input type="text" name="scouttitle" class="rounded-md text-sm bg-white dark:bg-gray-300 text-black w-full" v-model="form.title"
                    maxlength="100"
                    title="You can optionally enter a descriptive title for the scout report, i.e. Adam 30 July AM Train">
                </div>
                <div>Scouts:</div>
                <div>
                    <div v-for="(scout, index) in form.scouts" class="flex gap-1 mb-1">
                        <input v-model="form.scouts[index]" class="rounded-md text-sm bg-white dark:bg-gray-300 text-black p-1">
                        <button class="rounded-md p-1 bg-gray-400 dark:bg-slate-400 text-black text-xs"
                        @click.prevent="removeScout(index)"
                        title="Remove this scout from the scouting report"
                        >Remove</button>
                    </div>
                    <div class="flex gap-1 mb-1">
                        <input id="txtNewScout" name="newscout" class="rounded-md text-sm bg-white dark:bg-gray-300 text-black p-1"
                        @keypress.enter.prevent="handleAddScout">
                        <button class="rounded-md p-1 bg-gray-400 dark:bg-slate-400 text-black text-xs"
                        title="Add a credit to the scouting report"
                        @click.prevent.stop="handleAddScout">Add</button>
                    </div>
                </div>
            </div>

        </dialog>
        <dialog id="shareModal" class="relative" v-if="scout">
            <h1 class="font-bold text-2xl mb-4">Share View-Only Map</h1>
            <p class="text-sm">This link provides a view only copy of the map. Users cannot submit changes to the map.
            </p>
            <div class="bg-blue-500 text-white p-4 mb-4 relative cursor-pointer"
                @click="copyLink(route('scout.view', { scout: props.scout.slug }) + '?' + cacheBusterAppend)">
                <span>{{ route('scout.view', { scout: props.scout.slug }) }}?{{ cacheBusterAppend }}</span>
                <div class="absolute bottom-0 right-0.5">
                    <ContentCopyIcon />
                </div>
            </div>
            <template v-if="scout.collaborator_password && !scout?.finalized_at">
                <h1 class="font-bold text-2xl mb-4">Share Editable Map</h1>
                <p class="text-sm">This link will allow users to edit/add points to the map, so only give it to trusted
                    users. <br>
                    <b>Important:</b> This is still a work in progress! It has worked in limited testing, but if you want to be extra safe
                <br> only have one person using a report at the same time.</p>
                <div class="bg-blue-500 text-white p-4 mb-4 relative cursor-pointer"
                    @click="copyLink(route('scout.view', { scout: props.scout.slug, password: props.scout.collaborator_password }))">
                    <span>{{ route('scout.view', {
                        scout: props.scout.slug, password: props.scout.collaborator_password
                    })
                        }}</span>
                    <div class="absolute bottom-0 right-0.5">
                        <ContentCopyIcon />
                    </div>
                </div>
            </template>
            <div class="absolute bottom-0 font-bold opacity-0 transition-opacity duration-300 w-[90%] text-center"
                id="copied-msg">Copied to clipboard!</div>
        </dialog>
        <div class="mark-summary-overlay" id="MarkSummaryPanel" v-if="showMarkOverlay">
            <div class="mark-summary-panel">
                <div class="flex w-full justify-between mb-8">
                    <h1>Mark Summary</h1>
                    <button class="border rounded-md px-2 bg-slate-400 font-bold text-sm"
                        @click.prevent="closeMarkOverlay()">Close</button>
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
                                            ({{ formatCoordinate(mob.x ?? getPointById(zone, mob.point_id)?.['x']) }},
                                            {{ formatCoordinate(mob.y ?? getPointById(zone, mob.point_id)?.['y']) }})
                                        </div>
                                    </div>
                                </fieldset>
                            </template>
                        </template>
                    </template>
                </div>
            </div>
        </div>
        <dialog id="pasteMarks" class="fixed">
            <h2 class="text-xl font-bold">Import Marks From Clipboard</h2>
            <p class="text-sm">
                Paste the output from your echo log into the box below and click the Import button.
                <br>
                The script will do its best to identify instances and proper zones by looking for the in-game instance
                character.
                <br>
                You can <i>override</i> this by including "Instance 2" or "i3" (etc), so you can use echo macros to
                re-order
                your flags
                <br>
                Example: <b>Nariphon Lakeland ( 35.5 , 27.2 ) Z: 0.3 INSTANCE 3</b> would be parsed as being in instance
                3
                <br>
                <b>Be cautious if you paste in a line without a mob name - make sure it's not an accidental B rank coordinate
                    <br>
                    If the coordinates do not include mob name, it will assign them in order of the zone mob list<br>
                </b>
            </p>
            <div>
                <textarea name="pastedLog" id="pastedLog" class="w-full" rows="6"></textarea>
                <button type="button"
                    @click.prevent="parsePastedLog()"
                    :disabled="processingImport"
                    class="bg-blue-300 dark:bg-slate-800 p-2 border-black rounded-md font-bold">Import</button>
                <div class="text-sm font-mono" v-if="outputTextFromImport != ''">
                    <div v-html="outputTextFromImport"></div>
                </div>
            </div>
        </dialog>
    </div>
</template>

<script setup>
//  instance 1, 2, 3 icons for later
import { computed, nextTick, onBeforeMount, onMounted, ref, watch } from "vue";
import ZoneMap from '@/Components/Map/ZoneMap.vue';
import { useForm, Link } from "@inertiajs/vue3";
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue";
import ArrowUpIcon from "vue-material-design-icons/ArrowUp.vue";
import ExportIcon from "vue-material-design-icons/Export.vue";
import ContentCopyIcon from "vue-material-design-icons/ContentCopy.vue";
import ClipboardArrowUpOutlineIcon from "vue-material-design-icons/ClipboardArrowUpOutline.vue";
import ClipboardTextOutline from "vue-material-design-icons/ClipboardTextOutline.vue";
import NoteMultipleOutline from "vue-material-design-icons/NoteMultipleOutline.vue";
import FileLockOutlineIcon from "vue-material-design-icons/FileLockOutline.vue";
import SortIcon from "vue-material-design-icons/Sort.vue";
import ArrowDownIcon from "vue-material-design-icons/ArrowDown.vue"
import WeatherSunnyIcon from 'vue-material-design-icons/WeatherSunny.vue';
import WeatherNightIcon from "vue-material-design-icons/WeatherNight.vue";
import ClipboardTextMultipleOutlineIcon from "vue-material-design-icons/ClipboardTextMultipleOutline.vue";
import CheckBold from "vue-material-design-icons/CheckBold.vue";
import { formatCoordinate, getDisplayName, languages } from "@/helpers";
import { useToast } from "vue-toastification";

const emit = defineEmits(['pointUpdated', 'mapFinalized', 'clipboardImport', 'pauseUpdates', 'resumeUpdates', 'metaDetailsUpdated', 'mobStatusUpdated', 'point-occupied-updated'])

const processUpdate = function (payload) {
    if ('point_data' in payload) {
        form.point_data = payload.point_data
    }
    if ('custom_points' in payload) {
        form.custom_points = payload.custom_points
    }
    if ('scouts' in payload) {
        form.scouts = payload.scouts
    }
    if ('mob_status' in payload) {
        form.mob_status = payload.mob_status
    }
    if ('occupied_points' in payload) {
        form.occupied_points = payload.occupied_points
    }
    processingImport.value = false
}
defineExpose({
    processUpdate
})

const props = defineProps({
    expac: Array,
    scout: Object,
    editmode: Boolean,
    newlyCreated: Boolean,
    defaultId: Number,
})

const defaultExp = ref(6)
const selectedExp = ref(props.defaultId)
const cacheBusterAppend = ref(1)
const showMarkOverlay = ref(false)
const sortOrders = ref({})
const zoneMaps = ref({})
const lightDarkMode = ref('light')
const defaultLanguage = ref('en')
const outputTextFromImport = ref('')
const processingImport = ref(false)
const allowBlankMobImport = ref(true)
const editingUsername = ref(false)
const toast = useToast()

let lastPointAddTime = -1 * Date.now()

const form = useForm({
    point_data: {},
    occupied_points: {},
    custom_points: [],
    mob_status: {},
    title: '',
    scouts: [],
})

const toggleUsernameEditor = function() {
    editingUsername.value = true
    nextTick(() => {
        const txtUname = document.getElementById('txtDisplayUserName')
        txtUname.focus()
        txtUname.select()
    })
}

const getUserDisplayName = function() {
    if('username' in localStorage) {
        return localStorage.getItem('username')
    }
    return 'Anonymous'
}

const setUserDisplayName = function() {
    const txtUname = document.getElementById('txtDisplayUserName')
    if(txtUname.value.length > 0) {
        if(txtUname.value.length > 30) {
            txtUname.value = txtUname.value.substring(0, 30)
        }
        localStorage.setItem('username', txtUname.value)
    } else {
        localStorage.removeItem('username')
    }
    editingUsername.value = false
}

const removeScout = function(index) {
    if(index >= 0 && index < form.scouts.length) {
        form.scouts.splice(index, 1)
    }
}

const handleAddScout = function(event) {
    const txtScoutName = document.getElementById('txtNewScout')
    if(txtScoutName.value != '') {
        if(!form.scouts.includes(txtScoutName.value)) {
            // Make sure they can't add duplicates
            form.scouts.push(txtScoutName.value)
        }
        txtScoutName.value = ''
    }
}

const handleDetailsUpdated = function(event) {
    emit('metaDetailsUpdated', form.title, form.scouts)
}

const closeMarkOverlay = function (event) {
    showMarkOverlay.value = false
}
const getCustomSpawnPoints = function (zone_id) {
    return form.custom_points.filter((el) => el.zone_id == zone_id) ?? [];
}

const showImportDialog = function (event) {
    let curScrollY = window.scrollY
    outputTextFromImport.value = ''
    document.getElementById('pasteMarks').showModal()
    window.scrollTo(0, curScrollY)
}

const showDetailsDialog = function(event) {
    emit('pauseUpdates')
    document.getElementById('scoutDetails').showModal()
}

const copyMarksAsText = async function() {
    try {
        let linkText = document.getElementById('txtExport').value
        await navigator.clipboard.writeText(linkText)
        toast.success('Marks copied!')
    } catch (err) {
        // Silently fail
        console.log(err)
    }
}

const getExportTextValue = computed(() => {
    const intToName = function(val) {
        return {
            1: 'ONE',
            2: 'TWO',
            3: 'THREE',
            4: 'FOUR',
            5: 'FIVE',
            6: 'SIX',
        }[val] ?? ''
    }
    let ret = ''
    props.expac.forEach((expansion) => {
        if(getMappedMobsForExpac(expansion) > 0) {
            expansion.zones.forEach((zone) => {
                for(let i = 1; i <= zone.default_instances; i++) {
                    if(getFoundMobCount(zone.id, i) > 0) {
                        form.point_data[zone.id][i].forEach((mob) => {
                            ret += zone.mobs.find((el) => el.id == mob.mob_id).name
                            ret += ` @ \uE0BB${zone.name}`
                            if(zone.default_instances > 1) {
                                ret += intToInstanceMapping[i]
                            }
                            ret += ` ( ${formatCoordinate(mob.x ?? getPointById(zone, mob.point_id)?.['x'])} , ${formatCoordinate(mob.y ?? getPointById(zone, mob.point_id)?.['y'])} ) `
                            if(zone.default_instances > 1) {
                                ret += `Instance ${intToName(i)}`
                            }
                            ret += "\n"
                        })
                    }
                }
            })
        }
    })
    return ret
})

const cycleLanguage = function () {
    let curIndex = languages.findIndex((el) => el.abbrev == defaultLanguage.value)
    if (curIndex < 0 || curIndex == languages.length - 1) {
        // If no match or on last lang, cycle back to the first
        defaultLanguage.value = languages[0].abbrev
    } else {
        defaultLanguage.value = languages[curIndex + 1].abbrev
    }
    localStorage.setItem('defaultLanguage', defaultLanguage.value)
}

const toggleDarkMode = function (newMode) {
    lightDarkMode.value = newMode
    localStorage.setItem('theme', newMode)
    if (newMode === 'light') {
        document.documentElement.classList.remove('dark')
    } else {
        document.documentElement.classList.add('dark')
    }
}

const copyLink = async function (linkText) {
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

const changeZoneSort = function (expac_id, first_idx, second_idx) {
    let zOne = getExpacById(expac_id).zones[first_idx]
    let zTwo = getExpacById(expac_id).zones[second_idx]
    // Swap out the values that are in the zones' sort_priority field
    if (zOne && zTwo) {
        let origFirstVal = sortOrders.value[zOne.id]
        sortOrders.value[zOne.id] = sortOrders.value[zTwo.id]
        sortOrders.value[zTwo.id] = origFirstVal
        localStorage.setItem('sortOrders', JSON.stringify(sortOrders.value))
    }
}

const showShareDialog = function () {
    cacheBusterAppend.value += 1
    document.getElementById('shareModal').showModal()
}

const handleFinalizeClick = function () {
    if (confirm('Do you really wish to finalize this scouting report? No further edits can be made afterwards.')) {
        emit('mapFinalized')
    }
}

const handlePointUpdated = function (point, mob, zone_id, instance_number) {
    emit('pointUpdated', point, mob, form.point_data, getInstanceCounts(), zone_id, instance_number, form.custom_points, getUserDisplayName())
}

const handleMobStatusUpdate = function(mob, instance, status) {
    emit('mobStatusUpdated', mob, instance, status)
}

const handleOccupiedUpdate = function(point, instance, newValue) {
    emit('point-occupied-updated', point, instance, newValue)
}

const getAllZoneSpawnPoints = function(zone) {
    return zone.spawn_points.concat(getCustomSpawnPoints(zone.id))
}

const isZoneScoutingComplete = function(zone, instance_number) {
    // Get the remaining valid mobs for a particular zone
    let remainingMobs = zone.mobs.filter((mob) => {
        if(form.mob_status && mob.id in form?.mob_status && form?.mob_status?.[mob.id]?.[instance_number]) {
            return false
        }
        return true
    })

    return getFoundMobCount(zone.id, instance_number) == remainingMobs.length
}

const getClosestSpawnPoint = function(zone, x, y) {
    function d(point) {
        return Math.pow(point.x - x, 2) + Math.pow(point.y - y, 2)
    }
    function trueDistance(pointOne, pointTwo) {
        return Math.sqrt(Math.pow(pointTwo.x - pointOne.x, 2) + Math.pow(pointTwo.y - pointOne.y, 2))
    }
    let spawnPts = getAllZoneSpawnPoints(zone)
    if (spawnPts.length > 0) {
        // This zone has predefined points, so we should look for closest match
        let closest = spawnPts.reduce((a, b) => {
            return d(a) < d(b) ? a : b
        })
        let distance = trueDistance({ x: x, y: y }, closest)
        return {
            'point': closest,
            'distance': distance,
        }
    }
    // Return false and let the program decide if we need to add custom points
    return false
}

const createCustomSpawnPoint = function(zone, x, y) {
    let lastEl = form.custom_points.push({
        'x': x,
        'y': y,
        'zone_id': zone.id,
        'id': --lastPointAddTime,
        'valid_mobs': zone.mobs,
    })
    return form.custom_points[lastEl - 1]
}

const manualAssignMob = function (zone, instance, point, mob, line) {
    if (!props.editmode) return
    //console.log(point, mob)
    if (!(zone.id in form.point_data)) {
        form.point_data[zone.id] = {}
    }
    if (!(instance in form.point_data?.[zone.id])) {
        form.point_data[zone.id][instance] = []
    }
    //console.log("Manual Assign Mob Called", zone, instance, point, mob, form.point_data[zone.id][instance])
    // See if the mob is already assigned in this zone
    let mobArrayIdx = form.point_data[zone.id][instance].findIndex((el) => {
        return el.mob_id == mob.id
    })
    //console.log(`Found ${mobArrayIdx} for ${mob.name} in ${zone.name} and instance ${instance} `, form.point_data[zone.id][instance])
    if (mobArrayIdx > -1) {
        form.point_data[zone.id][instance].splice(mobArrayIdx, 1)
        // form.point_data[zone.id][instance] = form.point_data[zone.id][instance].filter((el) => {
        //     return el.mob_id != mob.id
        // })
    }
    //console.log(form.point_data[zone.id][instance])
    form.point_data[zone.id][instance].push({
        point_id: point.id,
        mob_id: mob.id,
        x: point.x,
        y: point.y,
        expansion_id: zone.expansion_id,
        line: line,
    })
}

const getAlreadyFoundMobIds = function(zone, instance) {
    let mobList = form.point_data?.[zone.id]?.[instance] ?? []
    let ret = []
    mobList.forEach((el) => {
        ret.push(el.mob_id)
    })
    return ret
}

const getMobOnPoint = function(zone, instance, point) {
    let mobList = form.point_data?.[zone.id]?.[instance] ?? []
    return mobList.find((el) => {
        return el.point_id == point.id
    })
}

const logImportFail = function(assignments, line, errorMessage) {
    assignments.fail.push({
        line: line,
        reason: errorMessage
    })
}

const isMobValidForPoint = function(mob, point) {
    //console.log(point.valid_mobs)
    let ret = false
    //console.log(`Testing if ${mob.name} is valid for ${point.id}`, point.valid_mobs)
    point.valid_mobs.forEach((el) => {
        //console.log(el)
        if(el.id == mob.id) {
            ret = true
        }
    })
    return ret
}

const getPointTakenByMob = function(zone, instance, mob) {
    let mobList = form.point_data?.[zone.id]?.[instance] ?? []
    return mobList.find((el) => {
        return el.mob_id == mob.id
    })
}

const getPointById = function(zone, point_id) {
    return getAllZoneSpawnPoints(zone).find((pt) => {
        return pt.id == point_id
    })
}

const instanceToIntMapping = {
        "": 1,
        "": 2,
        "": 3,
        "": 4,
        "": 5,
        "": 6,
}
const intToInstanceMapping = Object.fromEntries(Object.entries(instanceToIntMapping).map(([key, value]) => [value, key]))

const parsePastedLog = function () {
    const txtArea = document.getElementById('pastedLog')
    processingImport.value = true
    outputTextFromImport.value = ''

    // Store the list of things we did
    let assignments = {
        'fail': [],
        'success': [],
    }

    //\uE0B1 = Instance 1 , \uE0B2 = 2 , \uE0B3 = 3  \uE0BB = start of flag marker
    const re = /[\uE0BB]([^\uE0B1-\uE0B6]*)([\uE0B1-\uE0B6]?) \( ([0-9\.]+)\W+,\W+([0-9\.]+)\W+\).*/
    const instanceCheck = /(instance.|i)([1|2|3])/i
    const linesArr = txtArea.value.split(/\r?\n/);
    linesArr.forEach((line) => {
        let found = line.match(re)
        let instance = 1
        if (found) {
            let zoneName = found[1]
            let x = parseFloat(found[3])
            let y = parseFloat(found[4])
            let zone = getZoneByName(zoneName)
            let point = null
            let closestPoint = getClosestSpawnPoint(zone, x, y)

            if(!closestPoint && zone.allow_custom_points) {
                // There was no point found close to this mob, so create one
                point = createCustomSpawnPoint(zone, x, y)
            } else if (closestPoint && closestPoint.distance > 1.5 && zone.allow_custom_points) {
                // If a point from chat is over 1.5 total distance units away, create a new point
                point = createCustomSpawnPoint(zone, x, y)
            } else {
                point = closestPoint.point
            }

            if (found[2]) {
                instance = instanceToIntMapping[found[2]] ?? 1
            }
            let instOverride = line.match(instanceCheck)
            if (instOverride) {
                instance = Number(instOverride[2])
            }
            // If the zone expects instances, but we don't get an instance marker in the line
            // it's safer to *not* assume instance 1 so we don't break any existing points
            // on accident
            if(!found[2] && !instOverride && zone.default_instances > 1) {
                assignments.fail.push({
                    'line': line,
                    'reason': 'No instance number found - cannot determine correct instance'
                })
                return false
            }
            if(instance > zone.default_instances) {
                assignments.fail.push({
                    'line': line,
                    'reason': 'Invalid instance number detected.'
                })
                return false
            }
            // Look for the first mob whose name or foreign names match the given input
            let mob = zone.mobs.find((el) => {
                let lc = line.toLowerCase()
                if (lc.includes(el.name.toLowerCase())) {
                    return true
                }
                for (let lang in el.names) {
                    if (lc.includes(el.names[lang].toLowerCase())) {
                        return true
                    }
                }
                return false
            })
            if(!mob && allowBlankMobImport.value == true) {
                // Are there already mobs assigned for this zone?
                let mobsAssigned = getAlreadyFoundMobIds(zone, instance)
                let mobOnPoint = getMobOnPoint(zone, instance, point)
                if(mobOnPoint?.mob_id > 0) {
                    logImportFail(assignments, line, 'This point already contains a mob')
                    return
                }
                let validMobs = point.valid_mobs.filter((testMob) => {
                    //console.log(`Testing ${testMob.id} against`, mobsAssigned)
                    return !mobsAssigned.includes(testMob.id)
                })
                //console.log(validMobs)
                let otherMob = zone.mobs.find((m) => m.id == mobsAssigned[0])

                // If the other mob in this zone exists already, see if we can place it on this spot
                if(validMobs.length < 1 && otherMob && isMobValidForPoint(otherMob, point)) {
                    // Take the other assigned mob and stick him onto this point
                    let oldPt = getPointTakenByMob(zone, instance, otherMob)
                    oldPt = getPointById(zone, oldPt.point_id)
                    manualAssignMob(zone, instance, point, otherMob, line)

                    mobsAssigned = getAlreadyFoundMobIds(zone, instance)
                    //console.log('new mobs assigned', mobsAssigned, oldPt, otherMob, point)
                    validMobs = oldPt.valid_mobs.filter((testMob) => {
                        return !mobsAssigned.includes(testMob.id)
                    })
                    // Now that we've reassigned the proper mob, set point = the old previous point
                    // we grabbed the other mob from
                    point = oldPt
                }

                if(validMobs.length < 1) {
                    assignments.fail.push({
                        'line': line,
                        'reason': 'List of available mobs to place is already exhausted'
                    })
                    return
                } else {
                    mob = validMobs[0]
                }
            }
            if (mob && zone && x && y) {
                // console.log(`Trying ${zone.name} with ${mob.name} at ${x}, ${y}`)
                //let point = getClosestSpawnPoint(zone, x, y, mob.name)
                // console.log('FOund point', point)
                if (point) {
                    manualAssignMob(zone, instance, point, mob, line)
                    assignments.success.push({
                        zone: zone,
                        instance: instance,
                        mob: mob,
                        point: point,
                    })
                }
            } else {
                assignments.fail.push({
                    'line': line,
                    'reason': 'Could not detect a proper mob or point'
                })
            }
        }
    })
    //console.log(form.point_data)
    //txtArea.value = ''
    if(assignments) {
        outputTextFromImport.value = `Imported ${assignments.success.length ?? 0} lines successfully.`
        if(assignments.fail.length > 0) {
            outputTextFromImport.value += '<br>Failures:<br>'
            assignments.fail.forEach((fail) => {
                outputTextFromImport.value += `<b>${fail.line}</b> - ${fail.reason}<br>`
            })
        }
    }
    emit('clipboardImport', assignments, form.point_data, form.custom_points, getInstanceCounts())
    if(!props.scout) {
        processingImport.value = false
    }
    txtArea.value = ''
    return assignments
}

onMounted(() => {
    if (displayMode) {
        lightDarkMode.value = displayMode
    }
    if ('defaultLanguage' in localStorage) {
        defaultLanguage.value = localStorage.getItem('defaultLanguage')
    }
    //showImportDialog()
    //showDetailsDialog()

    //const dialog = document.getElementById('shareModal')
    const dialogs = document.querySelectorAll('dialog')
    // This bit of code lets you click outside of the Share modal in the backdrop area and have it
    // close the page
    if (dialogs) {
        dialogs.forEach((el) => {
            el.addEventListener("click", function (event) {
                const rect = el.getBoundingClientRect();
                const isInDialog = (
                    rect.top <= event.clientY &&
                    event.clientY <= rect.top + rect.height &&
                    rect.left <= event.clientX &&
                    event.clientX <= rect.left + rect.width
                );
                if (!isInDialog) {
                    el.close();
                }
            });
        })

    }
    // If they are being redirected from the main page after creating a new "Share" link
    // show them the Share modal
    if (props?.newlyCreated == true) {
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
    if (props?.scout?.point_data) {
        // Need to add custom points if they exist in the data
        //console.log(props.scout.point_data)
        for (let mapId in props.scout.point_data) {
            for (let instanceId in props.scout.point_data[mapId]) {
                if (props.scout.point_data[mapId][instanceId]?.length > 0) {
                    props.scout.point_data[mapId][instanceId].forEach((el) => {
                        if (el.point_id < 0) {
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

    if (props?.scout?.title) {
        form.title = props.scout.title
    }
    if (props?.scout?.scouts) {
        form.scouts = props.scout.scouts
    } else {
        form.scouts = []
    }
    if (props?.scout?.mob_status) {
        form.mob_status = props.scout.mob_status
    } else {
        form.mob_status = {}
    }
    if (props?.scout?.occupied_points) {
        form.occupied_points = props.scout.occupied_points
    } else {
        form.occupied_points = {}
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
            if (zone.id in userSort) {
                sortOrders.value[zone.id] = userSort[zone.id]
            } else {
                sortOrders.value[zone.id] = zone.sort_priority
            }
        })
    })
})

const zoneSortFunction = function (a, b) {
    return (sortOrders.value[a.id] - sortOrders.value[b.id])
}

const getZoneByName = function (zoneName) {
    for (let i = 0; i < props.expac.length; i++) {
        for (let j = 0; j < props.expac[i].zones.length; j++) {
            let z = props.expac[i].zones[j]
            if (z.name == zoneName
            || z.names['en'] == zoneName
            || z.names['de'] == zoneName
            || z.names['ja'] == zoneName
            || z.names['fr'] == zoneName
            ) {
                return z
            }
        }
    }
}

const addCustomSpawnPoint = function (point_data, mapId, instanceId) {
    // Does the point already exist
    // point_data includes expansion_id for convenience from the DB
    let ex = getExpacById(point_data.expansion_id)
    if (!ex) return
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

const submitForm = function () {
    if (props.editmode || props.editmode == true && !props.scout) {
        form
            .transform((data) => ({
                ...data,
                instance_data: getInstanceCounts(),
                scouter_name: getUserDisplayName(),
            }))
            .post(route('scout.store'), { preserveState: false })
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

const getExpacById = function (searchValue) {
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
