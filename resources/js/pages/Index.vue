<template>

    <Head title="New Scout"></Head>
    <ScoutLayout>
        <ScoutContainer :scout-report="scout_report" :editmode="true" :defaultId="props.defaultId"></ScoutContainer>
    </ScoutLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import ScoutLayout from '@/layouts/ScoutLayout.vue';
import Scouter from '@/classes/Scouter';
import { onBeforeMount, onMounted, ref, watch, provide } from 'vue';
import ScoutReport from '@/classes/ScoutReport';
import ScoutContainer from '@/components/ScoutContainer.vue';

const props = defineProps({
    expac: Array,
    defaultId: Number,
})

let scouter = null
let scout_report = ref(null)
let editmode = ref(true)
provide('scoutReport', scout_report)
provide('editmode', editmode)

onBeforeMount(() => {
    scouter = new Scouter(props.expac)
    scout_report.value = new ScoutReport({}, scouter)

})
onMounted(() => {
    if(window && window.localStorage) {
        if(localStorage.getItem('scout-in-progress')) {
            scout_report.value.unserialize(localStorage.getItem('scout-in-progress'))
        }
    }
    watch(() => scout_report, () => {
        console.log('Scout report updated values')
        if (window && window.localStorage) {
            localStorage.setItem('scout-in-progress', scout_report.value.serialize())
        }
    }, { deep: true })
})

</script>

<style lang="scss" scoped></style>