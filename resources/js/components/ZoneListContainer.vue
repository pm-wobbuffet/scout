<template>
    <main class="map-main-window">
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
import { watch, getCurrentInstance, onBeforeMount, ref, onMounted, toRef } from 'vue';

const props = defineProps({
    scoutReport: ScoutReport,
})
</script>

<style lang="scss" scoped>

</style>