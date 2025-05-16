<template>
    <Head title="Viewing Scouting Report" />
    <ScoutLayout>
        <ScoutContainer
        :scout-report="scout_report"
        :editmode="true"
        :defaultId="props.defaultId"
        ></ScoutContainer>
    </ScoutLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import ScoutLayout from '@/layouts/ScoutLayout.vue';
import Scouter from '@/classes/Scouter';
import { onBeforeMount, ref } from 'vue';
import ScoutReport from '@/classes/ScoutReport';
import ScoutContainer from '@/components/ScoutContainer.vue';

const props = defineProps({
    expac: Array,
    scout: Object,
    defaultId: Number,
})

let scouter = null;
let scout_report = ref(null);

onBeforeMount(() => {
    scouter = new Scouter(props.expac)
    scout_report.value = new ScoutReport(props.scout, scouter)
})
</script>

<style lang="scss" scoped>

</style>