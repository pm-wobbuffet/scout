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
            <div class="flex flex-row gap-0 shrink text-sm">
                <button
                    class="mr-2 flex items-center gap-x-1 bg-slate-600 p-2 rounded-md text-slate-100 dark:text-slate-300"
                    title="Export marks as text" @click.prevent="copyMarksAsText">
                    <SquareArrowRight title="Export marks as text" /> Export
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
//import AppearanceTabs from '@/components/AppearanceTabs.vue';
import SettingsPopover from '@/components/dialogs/SettingsDialog.vue';
import SortOrderPopover from '@/components/popovers/SortOrderPopover.vue';
import { SquareArrowRight, Files, Copy } from 'lucide-vue-next';
import { inject } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    scoutReport: Object,
    editmode: Boolean,
    newlyCreated: Boolean,
    defaultId: Number,
})

const scout = inject('scout')

</script>
