<template>

    <Head title="Viewing Scouting Report" />
    <ScoutLayout>
        <ScoutContainer :scout-report="scout_report"
            :editmode="props.scout.collaborator_password && props.scout.finalized_at === null"
            :defaultId="props.defaultId"></ScoutContainer>
    </ScoutLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import ScoutLayout from '@/layouts/ScoutLayout.vue';
import Scouter from '@/classes/Scouter';
import { inject, onBeforeMount, onBeforeUnmount, onMounted, onUnmounted, provide, ref } from 'vue';
import ScoutReport from '@/classes/ScoutReport';
import ScoutContainer from '@/components/ScoutContainer.vue';
import { useEchoPublic, useEcho, configureEcho } from '@laravel/echo-vue';
import axios from 'axios';

const props = defineProps({
    expac: Array,
    scout: Object,
    defaultId: Number,
})

let scouter = null;
const scout_report = ref(null);
const emitter = inject('emitter')
const wsConnection = ref('disconnected');

provide('connectionStatus', wsConnection)

configureEcho({
    broadcaster: "reverb",
});

let channelName = `scouts.${props.scout.slug}`
if (props.scout.collaborator_password && props.scout.collaborator_password !== '') {
    channelName += `.${props.scout.collaborator_password}`
}



onBeforeMount(() => {
    scouter = new Scouter(props.expac)
    scout_report.value = new ScoutReport(props.scout, scouter)
})
onMounted(() => {
    const t = useEchoPublic(channelName, '.ScoutAssignMob', (e) => {
        scout_report.value.updatePointDataForZone(e.zone_id, e.points)
    })

    t.channel().pusher.connection.bind('state_change', (states) => {
        wsConnection.value = states.current
    })

    useEchoPublic(channelName, '.ScoutClearPoint', (e) => {
        scout_report.value.removeMobFromPoint(e, e.instance_number)
    })
    useEchoPublic(channelName, '.UpdateMobStatus', (e) => {
        if (e.is_dead) {
            scout_report.value.addDeadMobToList(e.mob_id, e.instance_number)
        } else {
            scout_report.value.removeDeadMobFromList(e.mob_id, e.instance_number)
        }
    })
    emitter.on('mob:status', (obj) => {
        axios.post(route('scout.updatemobstatus', { scout: props.scout, password: props.scout.collaborator_password }), {
            slug: props.scout.slug,
            collaborator_password: props.scout.collaborator_password,
            ...obj
        })
    })
    emitter.on('point:assign-mob', (obj) => {
        axios.post(route('scout.assignmob', { scout: props.scout, password: props.scout.collaborator_password }), {
            slug: props.scout.slug,
            collaborator_password: props.scout.collaborator_password,
            ...obj
        })
    })
    emitter.on('point:clear', (obj) => {
        axios.post(route('scout.clearpoint', { scout: props.scout, password: props.scout.collaborator_password }), {
            slug: props.scout.slug,
            collaborator_password: props.scout.collaborator_password,
            ...obj
        })
    })
})

onBeforeUnmount(() => {
    emitter.off('point:clear')
    emitter.off('point:assign-mob')
    emitter.off('mob:status')
})
</script>

<style lang="scss" scoped></style>