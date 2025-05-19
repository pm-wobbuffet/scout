<template>
    <aside
        class="sticky top-0 left-0 md:left-auto border border-gray-400 ml-1 self-start order-1 bg-white dark:bg-slate-800 text-nowrap whitespace-nowrap">
        <div class="font-bold bg-slate-300 pl-1 hidden md:block dark:bg-slate-700 dark:text-slate-300"
            v-if="props.scoutReport.title != ''">
            <div class="text-sm max-w-[200px] overflow-hidden overflow-ellipsis" :title="props.scoutReport.title">{{
                props.scoutReport.title }}
            </div>
        </div>
        <div>
            <div class="font-bold bg-slate-300 p-1 dark:bg-slate-700 dark:text-slate-300">
                {{ getDisplayName(activeExpansion, 'en') }}
            </div>
            <ul class="text-sm">
                <template v-for="zone in activeExpansion.zones">
                    <li v-for="i in props.scoutReport.getInstanceCountForZone(zone.id)" :id="`zonelink-${zone.id}-${i}`"
                        class="hover:bg-slate-200 dark:hover:bg-slate-700 ml-2 pr-2" :class="{
                            'hidden md:block': !(`${zone.id}-${i}` in visibleMaps)
                        }"><a class="text-blue-500" :href="`#zonemap-${zone.id}-${i}`"
                            :class="{ 'line-through': props.scoutReport.isZoneScoutingComplete(zone, i) }">{{
                                getDisplayName(zone,
                                    'en') }}</a>
                        <span class="ml-1 font-bold text-blue-800 dark:text-blue-400"
                            v-if="props.scoutReport.getInstanceCountForZone(zone.id) > 1">{{ i
                            }}</span>
                        <i class="text-sm ml-2 text-black dark:text-slate-200 font-mono">{{
                            props.scoutReport.getFoundMobCountForZone(zone.id, i)
                            }}/{{ zone.mobs.length }}
                        </i>
                    </li>
                </template>
            </ul>
        </div>
        <div class="text-sm px-2" v-if="connectionStatus != null">
            <div v-if="connectionStatus === 'connected'">
                <div class="inline-block rounded-full bg-green-300 w-[10px] h-[10px] mr-1"></div>
                Connected
            </div>
            <div v-else-if="connectionStatus === 'disconnected'">
                <div class="inline-block rounded-full bg-red-300 w-[10px] h-[10px] mr-1"></div>
                Disconnected
            </div>
            <div v-else-if="connectionStatus === 'connecting'">
                <div class="inline-block rounded-full bg-yellow-300 w-[10px] h-[10px] mr-1"></div>
                Connecting
            </div>
        </div>
    </aside>
</template>

<script setup>
import ScoutReport from '@/classes/ScoutReport';
import { getDisplayName } from '@/classes/helpers';
import { onBeforeMount, ref, computed, onMounted, onUnmounted, onUpdated, inject } from 'vue';


const props = defineProps({
    scoutReport: Object,
})

const scouter = ref(null)
const activeExpansion = computed(() => {
    return scouter.value.getExpacById(props.scoutReport.getSelectedExpansion())
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

const observer = new IntersectionObserver(handleIntersectionObserve, {
    root: null,
    rootMargin: "-10px"
})
const visibleMaps = ref({})

onBeforeMount(() => {
    scouter.value = props.scoutReport.scouter_instance
    //activeExpansion.value = scouter.value.getExpacById(props.scoutReport.getSelectedExpansion())
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