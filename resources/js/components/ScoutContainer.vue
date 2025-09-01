<template>
    <div class="min-w-full min-h-[100vh]">
        <nav
            class="z-10 flex flex-col md:flex-row flex-wrap gap-1 w-full items-center justify-between bg-slate-500 dark:bg-slate-900 text-slate-100 dark:text-slate-400 p-2 min-h-[3rem] main-nav flex-grow-1 max-w-[100%] overflow-auto">
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
            <div class="flex flex-col md:flex-row gap-1 shrink">
                <AppearanceTabs />
                <div>export</div>
            </div>
        </nav>
        <ZoneListContainer :scout-report="props.scoutReport" :editmode="props.editmode" />
    </div>
</template>

<script setup>
import ExpansionListHeader from '@/components/ExpansionListHeader.vue';
import ZoneListContainer from '@/components/ZoneListContainer.vue';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import SettingsPopover from '@/components/dialogs/SettingsDialog.vue';
import SortOrderPopover from '@/components/popovers/SortOrderPopover.vue';

const props = defineProps({
    scoutReport: Object,
    editmode: Boolean,
    newlyCreated: Boolean,
    defaultId: Number,
})

</script>
