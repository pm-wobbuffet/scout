<template>
    <div class="scout-report-options">
        <a href="#" class="bg-blue-400 dark:bg-blue-900 text-white dark:text-slate-300">
            <ArrowUp />
            Top
        </a>
        <a href="#" class="bg-blue-700 dark:bg-blue-800 text-white dark:text-slate-300" v-if="scout"
            @click.prevent="showShareDialog"
            title="Save the scout report to the database and share with others if desired">
            <ShareIcon /> Share
        </a>
        <a href="#" class="bg-blue-700 dark:bg-blue-800 text-white dark:text-slate-300" v-else
            @click.prevent="showShareDialog"
            title="Save the scout report to the database and share with others if desired">
            <ShareIcon /> Share
        </a>
        <button class="bg-slate-500 dark:bg-yellow-800" v-if="editmode && ((!scout) || scout?.finalized_at === null)"
            title="Import mob coordinates by pasting in chat logs" @click.prevent="showImportDialog">
            <ImportIcon /> Import
        </button>
        <button class="inline-flex rounded-md bg-red-400 dark:bg-red-800"
        v-if="editmode && scout && scout?.finalized_at === null">
            <FileLockIcon /> Finalize
        </button>
        <a href="#" class="inline-flex rounded-md bg-green-700 dark:bg-green-800">
            <ClipboardListIcon /> Details
        </a>
    </div>
</template>

<script setup>
import { ArrowUp, ClipboardIcon, ClipboardListIcon, FileLockIcon, ImportIcon, LockIcon, ShareIcon } from 'lucide-vue-next';
import { inject } from 'vue';

const scoutReport = inject('scoutReport')
const scout = inject('scout')
const editmode = inject('editmode')

</script>

<style lang="scss" scoped></style>