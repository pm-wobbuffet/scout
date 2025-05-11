<template>
    <div class="min-w-full min-h-[100vh]">
        <nav
            class="flex flex-wrap gap-1 w-full items-center justify-between bg-slate-500 dark:bg-slate-900 text-slate-100 dark:text-slate-400 p-2 min-h-[3rem] main-nav flex-grow-1 sticky top-0">
            <div class="shrink">
                <a href="/"><img src="/turtleknife.png" height="40" width="90" class="inline"
                        alt="The turtle says to murder" /></a>
            </div>
            <ExpansionListHeader
            :scout-report="props.scoutReport"
            :selected-exp="selectedExp" />
            <div class="flex shrink">
                Export
            </div>
        </nav>
    </div>
</template>

<script setup>
import ScoutReport from '@/classes/ScoutReport';
import { ref } from 'vue';
import ExpansionListHeader from '@/components/ExpansionListHeader.vue';

const props = defineProps({
    scoutReport: ScoutReport,
    editmode: Boolean,
    newlyCreated: Boolean,
    defaultId: Number,
})

const defaultExp = ref(6)
const selectedExp = ref(props.defaultId)

</script>

<style lang="scss" scoped>

</style>