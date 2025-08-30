<template>
    <PopoverRoot @update:open="handleOpen">
        <PopoverTrigger as-child>
            <button
                class="border rounded-sm ml-1 p-1 bg-[rgba(0,0,0,0.4)] hover:bg-[rgba(0,0,255,0.1)] cursor-pointer dark:border-slate-500 transition-colors duration-300"
                title="Adjust the sort order of this expansion's zones"
                aria-label="Adjust the sort order of this expansion's zones">
                <ArrowDownUpIcon class="" :size="18" />
            </button>
        </PopoverTrigger>
        <PopoverPortal>
            <PopoverContent side="bottom" :side-offset="5" align="center"
                class="rounded-lg p-2 min-w-[300px] bg-white dark:bg-slate-700 shadow-sm border will-change-[transform,opacity] data-[state=open]:animate-(--animate-slide-up-and-fade)">
                <h1 class="text-center font-bold text-xl">Zone Order/Instances</h1>
                <table class="w-full">
                    <tr v-for="(zone, index) in scoutReport.getZonesByExpansion()" :key="`zone-sort-row-${zone.id}`"
                        class="border-b">
                        <td class="py-1 font-semibold">{{ getDisplayName(zone) }}</td>
                        <td class="zone-sort-buttons">
                            <button class="border disabled:opacity-25 disabled:border-0 mr-1"
                                :disabled="index == scoutReport.getZonesByExpansion().length - 1">
                                <ArrowDownIcon />
                            </button>
                            <button class="border disabled:opacity-25 disabled:border-0" :disabled="index == 0">
                                <ArrowUpIcon />
                            </button>
                        </td>
                        <td>
                            <input type="number" class="border max-w-[3rem] text-center" min="1" :data-zone-id="zone.id"
                                :value="scoutReport.getInstanceCountForZone(zone.id)" @input="updateInstanceCount" />
                        </td>
                    </tr>
                </table>
                <PopoverArrow class="fill-white dark:fill-slate-700" v-show="true" />
            </PopoverContent>
        </PopoverPortal>
    </PopoverRoot>
</template>

<script setup>
import {
    PopoverAnchor,
    PopoverArrow,
    PopoverClose,
    PopoverContent,
    PopoverPortal,
    PopoverRoot,
    PopoverTrigger
} from 'reka-ui';
import { ArrowDownUpIcon, ArrowUpIcon, ArrowDownIcon } from 'lucide-vue-next';
import { inject, ref } from 'vue';
import { getDisplayName } from '@/classes/helpers';

const popoverOpen = ref(false)
const scoutReport = inject('scoutReport')
const scouter = scoutReport.scouter_instance

const handleOpen = (isOpen) => {
    //console.log(event)
}

const updateInstanceCount = (event) => {
    scoutReport.value.setInstanceCountForZone(Number(event.target.dataset.zoneId), Number(event.target.value))
}

const changeZoneSort = (initial_index, target_index) => {
    expansion_id = scoutReport.value.getSelectedExpansion()

}

</script>

<style lang="scss" scoped></style>