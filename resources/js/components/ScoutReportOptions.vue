<template>
    <div class="scout-report-options">
        <a href="#" class="bg-blue-400 dark:bg-blue-900 text-white dark:text-slate-300">
            <ArrowUp />
            Top
        </a>
        <ShareScoutDialog v-if="scout" />
        <a href="#" class="bg-blue-700 dark:bg-blue-800 text-white dark:text-slate-300" v-else
            @click.prevent="submitScout" title="Save the scout report to the database and share with others if desired">
            <ShareIcon /> Share
        </a>
        <ImportPointsDialog v-if="editmode && ((!scout) || scout?.finalized_at === null)" />
        <!-- <button class="bg-slate-500 dark:bg-yellow-800" v-if="editmode && ((!scout) || scout?.finalized_at === null)"
            title="Import mob coordinates by pasting in chat logs" @click.prevent="showImportDialog">
            <ImportIcon /> Import
        </button> -->
        <button class="inline-flex rounded-md bg-red-400 dark:bg-red-800"
            v-if="editmode && scout && scout?.finalized_at === null">
            <FileLockIcon /> Finalize
        </button>
        <ScoutDetailsDialog />
    </div>
</template>

<script setup>
import ImportPointsDialog from '@/components/dialogs/ImportPointsDialog.vue';
import ScoutDetailsDialog from '@/components/dialogs/ScoutDetailsDialog.vue';
import ShareScoutDialog from '@/components/dialogs/ShareScoutDialog.vue';
import { ArrowUp, FileLockIcon, ImportIcon, ShareIcon } from 'lucide-vue-next';
import { inject } from 'vue';

const scout = inject('scout', null)
const editmode = inject('editmode', false)
const emitter = inject('emitter')

const submitScout = () => {
    emitter.emit('scout:save')
}

</script>

<style lang="scss" scoped></style>