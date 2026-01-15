<template>
    <div class="min-w-full min-h-[100vh]">
        <nav class="main-nav shadow-md">
            <div class="shrink hidden md:block">
                <a href="/"><img src="/turtleknife.png" height="40" width="90" class="inline"
                        alt="Turtle Scout Logo, friendly turtle with a knife" /></a>
            </div>
            <div class="flex flex-row items-center gap-0">
                <ExpansionListHeader :scout-report="props.scoutReport" />
                <div class="flex flex-row items-center gap-0">
                    <SortOrderPopover />
                    <SettingsPopover />
                </div>
            </div>
            <div class="flex flex-row gap-0 shrink text-sm">
                <button
                    class="mr-2 flex items-center gap-x-1 bg-slate-600 p-2 rounded-md text-slate-100 dark:text-slate-300"
                    title="Export marks as text" @click="doCopy()">
                    <SquareArrowRight title="Export marks as text" />
                    <span>Export</span>
                </button>
                <button
                    class="mr-2 flex items-center gap-x-1 bg-slate-600 p-2 rounded-md text-slate-100 dark:text-slate-300"
                    @click.prevent="showMarkOverlay = true">
                    <Files class="inline-block" />
                    Summary
                </button>
                <Link as="button" method="post" :href="route('scout.clone', { scout: scout })" :preserve-state="false"
                    v-if="(scout?.id && !props.editmode)"
                    class="bg-blue-400 p-2 rounded-md text-slate-100 flex items-center gap-x-1">
                    <Copy class="inline-block" /> Duplicate
                </Link>
            </div>
        </nav>
        <ZoneListContainer :scout-report="props.scoutReport" :editmode="props.editmode" />
        <div class="mark-summary-overlay" id="MarkSummaryPanel" v-if="showMarkOverlay">
            <div class="mark-summary-panel">
                <div class="flex w-full justify-between mb-8">
                    <h1>Mark Summary</h1>
                    <button class="border rounded-md px-2 bg-slate-400 font-bold text-sm"
                        @click.prevent="showMarkOverlay = false">Close</button>
                </div>
                <div v-for="expansion in props.scoutReport.scouter_instance.expansion_data.toReversed()"
                    :key="`summary-expansion-container-${expansion.id}`">
                    <template v-if="props.scoutReport.getFoundMobCountForExpansion(expansion.id) > 0">
                        <h2>{{ expansion.name }}</h2>
                        <template v-for="zone in props.scoutReport.getZonesByExpansion(expansion.id)">
                            <template v-for="i in props.scoutReport.getInstanceCountForZone(zone.id)">
                                <fieldset v-if="props.scoutReport.getFoundMobCountForZone(zone.id, i)"
                                    :key="`fieldset-zone-${zone.id}-${i}`">
                                    <legend>{{ zone.name }}
                                        <span v-if="props.scoutReport.getInstanceCountForZone(zone.id) > 1">{{ i
                                        }}</span>
                                    </legend>
                                    <div v-for="mobPoint in props.scoutReport.getFoundMobInfoForZone(zone.id, i)"
                                        :key="`moblist-${zone.id}-${i}-${mobPoint.id}`">
                                        {{ getDisplayName(mobPoint.mob) }}
                                        (
                                        {{ formatCoordinate(mobPoint.x ?? mobPoint.spawn_point.x) }},
                                        {{ formatCoordinate(mobPoint.y ?? mobPoint.spawn_point.y) }}
                                        )
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
import ExpansionListHeader from '@/components/ExpansionListHeader.vue';
import ZoneListContainer from '@/components/ZoneListContainer.vue';
import SettingsPopover from '@/components/dialogs/SettingsDialog.vue';
import SortOrderPopover from '@/components/popovers/SortOrderPopover.vue';
import { SquareArrowRight, Files, Copy } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { inject, ref } from 'vue';
// import { useToast } from 'vue-toastification';
import { useClipboard } from '@vueuse/core';
import { intToInstanceMapping, formatCoordinate, getDisplayName } from '@/classes/helpers';

const props = defineProps({
    scoutReport: Object,
    editmode: Boolean,
    newlyCreated: Boolean,
    defaultId: Number,
})
const scout = inject('scout', null)
const toast = inject('Toast')

const showMarkOverlay = ref(false)

const { copy, copied } = useClipboard()

const doCopy = () => {
    copy(getClipboardText())
    if (copied) {
        toast.success('Copied to clipboard!')
    }

}

const getClipboardText = () => {
    const intToName = (val) => {
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
    props.scoutReport.scouter_instance?.expansion_data?.slice().reverse().forEach((expac) => {
        // Does the expansion have mobs found?
        if (props.scoutReport.getFoundMobCountForExpansion(expac.id) > 0) {
            props.scoutReport.getZonesByExpansion(expac.id).forEach((zone) => {
                //expac.zones.forEach((zone) => {
                const instance_count = props.scoutReport.getInstanceCountForZone(zone.id)
                for (let i = 1; i <= instance_count; i++) {
                    // Were there mobs in this zone?
                    if (props.scoutReport.getFoundMobCountForZone(zone.id, i) < 1) continue
                    props.scoutReport.getFoundMobInfoForZone(zone.id, i).forEach((mobPoint) => {
                        ret += getDisplayName(mobPoint.mob)
                        ret += ` @ \uE0BB${zone.name}`
                        if (instance_count > 1) ret += intToInstanceMapping[i]
                        ret += ` ( ${formatCoordinate(mobPoint.x ?? mobPoint.spawn_point.x)} , ${formatCoordinate(mobPoint.y ?? mobPoint.spawn_point.y)} )`
                        if (instance_count > 1) ret += ` Instance ${intToName(i)}`
                        ret += "\n"
                    })
                }
            })
        }
    })
    return ret
}

</script>
