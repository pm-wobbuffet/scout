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
import { inject, onBeforeMount, onBeforeUnmount, onMounted, onUnmounted, ref } from 'vue';
import ScoutReport from '@/classes/ScoutReport';
import ScoutContainer from '@/components/ScoutContainer.vue';
import { useEchoPublic } from '@laravel/echo-vue';

const props = defineProps({
    expac: Array,
    scout: Object,
    defaultId: Number,
})

let scouter = null;
const scout_report = ref(null);
const emitter = inject('emitter')

let channelName = `scouts.${props.scout.slug}`
if (props.scout.collaborator_password && props.scout.collaborator_password !== '') {
    channelName += `.${props.scout.collaborator_password}`
}

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
        outbound.send_event('ScoutAssignMob', {
            slug: props.scout.slug,
            collaborator_password: props.scout.collaborator_password,
            ...obj
        }, channelName)
    })

    const t = useEchoPublic(
        channelName,
        'PointAssignMob',
        (e) => {
            console.log(e)
        }
    )
    // Grab reference to Pusher instance so we can use send_events
    const outbound = t.channel().pusher
})

onBeforeUnmount(() => {
    emitter.off('mob:markalive')
    emitter.off('point:assign-mob')
})
</script>

<style lang="scss" scoped></style>