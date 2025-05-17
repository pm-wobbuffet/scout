<template>
    <Head title="Viewing Scouting Report" />
    <ScoutLayout>
        <ScoutContainer
        :scout-report="scout_report"
        :editmode="props.scout.collaborator_password && props.scout.finalized_at === null"
        :defaultId="props.defaultId"
        ></ScoutContainer>
    </ScoutLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import ScoutLayout from '@/layouts/ScoutLayout.vue';
import Scouter from '@/classes/Scouter';
import { inject, onBeforeMount, onBeforeUnmount, onMounted, ref } from 'vue';
import ScoutReport from '@/classes/ScoutReport';
import ScoutContainer from '@/components/ScoutContainer.vue';
import '../../echo';

const props = defineProps({
    expac: Array,
    scout: Object,
    defaultId: Number,
})

let scouter = null;
const scout_report = ref(null);
const emitter = inject('emitter')

onBeforeMount(() => {
    scouter = new Scouter(props.expac)
    scout_report.value = new ScoutReport(props.scout, scouter)
})
onMounted(() => {
    emitter.on('mob:markalive', (obj) => {
        console.log(obj)
    })
    emitter.on('point:assign-mob', (obj) => {
        console.log(obj)
    })
})
onBeforeUnmount(() => {
    emitter.off('mob:markalive')
    emitter.off('point:assign-mob')
})
</script>

<style lang="scss" scoped>

</style>