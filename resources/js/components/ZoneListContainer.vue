<template>
    <main class="map-main-window">
        <!-- Main map area -->
         <ZoneList
         :scout-report="props.scoutReport"
         />
        <!-- Control buttons -->
        <!-- List of current expac zones and status -->
         <ZoneListSidebar
         :scout-report="props.scoutReport"
         />
    </main>
</template>

<script setup>
import ScoutReport from '@/classes/ScoutReport';
import ZoneListSidebar from '@/components/ZoneListSidebar.vue';
import ZoneList from '@/components/ZoneList.vue';

import { watch, getCurrentInstance, onBeforeMount, ref, onMounted, toRef } from 'vue';

const props = defineProps({
    scoutReport: ScoutReport,
    editmode: Boolean,
})
</script>

<style lang="scss" scoped>

</style>