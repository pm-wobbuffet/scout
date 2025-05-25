<template>

    <Head title="New Scout"></Head>
    <ScoutLayout>
        <ScoutContainer :scout-report="scout_report" :editmode="true" :defaultId="props.defaultId"></ScoutContainer>
    </ScoutLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import ScoutLayout from '@/layouts/ScoutLayout.vue';
import Scouter from '@/classes/Scouter';
import { onBeforeMount, onMounted, ref, watch, provide, inject, onBeforeUnmount } from 'vue';
import ScoutReport from '@/classes/ScoutReport';
import ScoutContainer from '@/components/ScoutContainer.vue';

const props = defineProps({
    expac: Array,
    defaultId: Number,
})

let scouter = null
const scout_report = ref(null)
const editmode = ref(true)
const emitter = inject('emitter')

provide('scoutReport', scout_report)
provide('editmode', editmode)

const form = useForm({

})

onBeforeMount(() => {
    scouter = new Scouter(props.expac)
    scout_report.value = new ScoutReport({}, scouter)

})
onMounted(() => {
    if (window && window.localStorage) {
        if (localStorage.getItem('scout-in-progress')) {
            scout_report.value.unserialize(localStorage.getItem('scout-in-progress'))
        }
    }
    watch(() => scout_report, () => {
        if (window && window.localStorage) {
            localStorage.setItem('scout-in-progress', scout_report.value.serialize())
        }
    }, { deep: true })
    emitter.on('scout:save', () => {
        console.log(form)
        form
            .transform((data) => ({
                ...data,
                title: scout_report.value?.title,
                points: scout_report.value?.point_data,
                instance_data: scout_report.value?.instance_data,
                scout_names: scout_report.value?.scout_names,
                dead_mobs: scout_report.value?.dead_mobs,
            }))
            .post(route('scout.store'), {
                preserveState: false
            })
    })
})
onBeforeUnmount(() => {
    emitter.off('scout:save')
})

</script>

<style lang="scss" scoped></style>