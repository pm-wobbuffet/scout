<template>
    <div class="min-w-full min-h-[100vh]">
        <nav
            class="flex flex-col md:flex-row flex-wrap gap-1 w-full items-center justify-between bg-slate-500 dark:bg-slate-900 text-slate-100 dark:text-slate-400 p-2 min-h-[3rem] main-nav flex-grow-1">
            <div class="shrink">
                <a href="/"><img src="/turtleknife.png" height="40" width="90" class="inline"
                        alt="The turtle says to murder" /></a>
            </div>
            <div class="flex flex-wrap flex-row expac-list place-self-center m-auto">
                <ExpansionListHeader :scout-report="props.scoutReport" />
            </div>
            <div class="flex shrink">
                <div class="flex">Settings</div>
                <div>export</div>
            </div>
        </nav>
        <ZoneListContainer
        :scout-report="props.scoutReport"
        :editmode="props.editmode" />
    </div>
</template>

<script setup>
import ScoutReport from '@/classes/ScoutReport';
import { onBeforeMount, onMounted, ref } from 'vue';
import ExpansionListHeader from '@/components/ExpansionListHeader.vue';
import ZoneListContainer from '@/components/ZoneListContainer.vue';

const props = defineProps({
    scoutReport: ScoutReport,
    editmode: Boolean,
    newlyCreated: Boolean,
    defaultId: Number,
})

</script>
