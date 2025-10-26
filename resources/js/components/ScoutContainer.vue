<template>
    <div class="min-w-full min-h-[100vh]">
        <nav class="main-nav">
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
    </div>
</template>

<script setup>
import ExpansionListHeader from '@/components/ExpansionListHeader.vue';
import ZoneListContainer from '@/components/ZoneListContainer.vue';
import SettingsPopover from '@/components/dialogs/SettingsDialog.vue';
import SortOrderPopover from '@/components/popovers/SortOrderPopover.vue';
import { SquareArrowRight, Files, Copy } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { inject } from 'vue';
import { useToast } from 'vue-toastification';
import { useClipboard } from '@vueuse/core';
import { intToInstanceMapping, formatCoordinate, getDisplayName } from '@/classes/helpers';

const props = defineProps({
    scoutReport: Object,
    editmode: Boolean,
    newlyCreated: Boolean,
    defaultId: Number,
})
const scout = inject('scout', null)
const toast = useToast()

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
    props.scoutReport.scouter_instance.expansion_data.forEach((expac) => {
        // Does the expansion have mobs found?
        if (props.scoutReport.getFoundMobCountForExpansion(expac.id) > 0) {
            expac.zones.forEach((zone) => {
                const instance_count = props.scoutReport.getInstanceCountForZone(zone.id)
                for (let i = 1; i <= instance_count; i++) {
                    // Were there mobs in this zone?
                    if (props.scoutReport.getFoundMobCountForZone(zone.id, i) < 1) continue
                    const pts = props.scoutReport.point_data.filter((pt) => {
                        return (pt.zone_id == zone.id && pt.instance_number == i && pt.mob_id !== null)
                    })
                    pts.forEach((mobPoint) => {
                        // TODO: get proper X and Y for points that don't have them attached to the mobPoint
                        // i.e. a user just clicks a circle instead of imports
                        const mob = props.scoutReport.scouter_instance.getMobById(mobPoint.mob_id)
                        const spawn_pt = props.scoutReport.getSpawnPointById(mobPoint.point_id, mobPoint.point_type)
                        ret += getDisplayName(mob)
                        ret += ` @ \uE0BB${zone.name}`
                        if (instance_count > 1) ret += intToInstanceMapping[i]
                        ret += ` ( ${formatCoordinate(mobPoint.x ?? spawn_pt.x)} , ${formatCoordinate(mobPoint.y ?? spawn_pt.y)} )`
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
