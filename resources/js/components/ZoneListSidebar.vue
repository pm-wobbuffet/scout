<template>
    <aside
        class="md:left-auto ml-1 self-start order-1 bg-(--background) dark:bg-slate-800 text-nowrap whitespace-nowrap">
        <div class="border border-gray-400 bg-(--background-lighter)">
            <div class="font-bold bg-slate-300 pl-1 hidden md:block dark:bg-slate-700 dark:text-slate-300"
                v-if="props.scoutReport.title != ''">
                <div class="text-sm max-w-[200px] overflow-hidden overflow-ellipsis" :title="props.scoutReport.title">{{
                    props.scoutReport.title ?? 'Untitled Scouting Report' }}
                </div>
            </div>
            <ScoutReportOptions />
            <div>
                <div class="font-bold bg-slate-300 pl-1 text-sm dark:bg-slate-700 dark:text-slate-300">
                    {{ getDisplayName(activeExpansion, 'en') }}
                    <span class="text-xs italic">
                        {{ getMappedMobsForExpac(activeExpansion) }} /
                        {{ mobCount(activeExpansion) }}
                    </span>
                </div>
                <ul class="text-sm">
                    <template v-for="zone in activeExpansion.zones">
                        <li v-for="i in props.scoutReport.getInstanceCountForZone(zone.id)"
                            :id="`zonelink-${zone.id}-${i}`" :key="`zonelink-${zone.id}-${i}`"
                            class="flex items-center hover:bg-slate-200 dark:hover:bg-slate-700 ml-2 pr-2" :class="{
                                'hidden md:flex': !(`${zone.id}-${i}` in visibleMaps)
                            }"><a
                                class="block text-blue-500 max-w-(--sidebar-max-link-width) overflow-hidden text-ellipsis wrap-normal"
                                :href="`#zonemap-${zone.id}-${i}`"
                                :class="{ 'line-through': props.scoutReport.isZoneScoutingComplete(zone, i) }">{{
                                    getDisplayName(zone,
                                        'en') }}</a>
                            <span class="ml-1 text-blue-800 dark:text-blue-400 font-[FFXIV\_Lodestone\_SSF]"
                                v-if="props.scoutReport.getInstanceCountForZone(zone.id) > 1">{{ intToInstanceMapping[i]
                                    ??
                                    i }}</span>
                            <i class="text-sm ml-2 text-black dark:text-slate-200">{{
                                props.scoutReport.getFoundMobCountForZone(zone.id, i)
                                }}/{{ zone.mobs.length }}
                            </i>
                        </li>
                    </template>
                </ul>
            </div>
            <ConnectionStatus :connection-status="connectionStatus" class="text-sm px-2"
                v-if="connectionStatus != null" />
            <div v-if="props.scoutReport.scouts.length > 0">
                <div class="font-bold text-sm bg-slate-300 p-1 dark:bg-slate-700 dark:text-slate-300">Scouts</div>
                <div>
                    <ul class="text-sm">
                        <li v-for="(scout, index) in props.scoutReport.scouts" :key="`scoutname-${index}`"
                            class="ml-2 pr-2 text-blue-800 dark:text-blue-400 overflow-ellipsis"
                            :title="scout.scout_name">
                            {{ scout.scout_name }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <QuickPasteArea />
    </aside>
</template>

<script setup>
import { getDisplayName, intToInstanceMapping } from '@/classes/helpers';
import ScoutReportOptions from '@/components/ScoutReportOptions.vue';
import ConnectionStatus from '@/components/ui/zonelist/ConnectionStatus.vue';
import { onBeforeMount, ref, computed, onMounted, onUnmounted, onUpdated, inject } from 'vue';
import QuickPasteArea from './QuickPasteArea.vue';


const props = defineProps({
    scoutReport: Object,
})

const getMappedMobsForExpac = expac => {
    return props.scoutReport.getFoundMobCountForExpansion(expac.id)
}
const mobCount = expac => {
    let total_mobs = 0
    expac.zones.forEach((zone) => {
        total_mobs += (zone.mobs.length ?? 0) * props.scoutReport.getInstanceCountForZone(zone.id)
    })
    return total_mobs
}

const scouter = ref(null)
const activeExpansion = computed(() => {
    return scouter.value?.getExpacById(props.scoutReport.getSelectedExpansion())
})

const connectionStatus = inject('connectionStatus', null)

const handleIntersectionObserve = function (elements) {
    elements.forEach((el) => {
        const zone_id = el.target.dataset.zoneId
        const instance_number = el.target.dataset.instance
        if (el.isIntersecting) {
            visibleMaps.value[`${zone_id}-${instance_number}`] = 1
        } else {
            if (`${zone_id}-${instance_number}` in visibleMaps.value) {
                delete (visibleMaps.value[`${zone_id}-${instance_number}`])
            }
        }
    })
}

let observer = null
const visibleMaps = ref({})

onBeforeMount(() => {
    scouter.value = props.scoutReport.scouter_instance
    observer = new IntersectionObserver(handleIntersectionObserve, {
        root: null,
        rootMargin: "-10px"
    })
})

onMounted(() => {
    document.querySelectorAll('.map-container-block').forEach((mapBlock) => {
        observer.observe(mapBlock)
    })
})
onUpdated(() => {
    observer.disconnect()
    document.querySelectorAll('.map-container-block').forEach((mapBlock) => {
        observer.observe(mapBlock)
    })
})

onUnmounted(() => {
    observer.disconnect()
})


</script>

<style lang="scss" scoped></style>