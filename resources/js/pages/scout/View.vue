<template>

    <Head title="Viewing Scouting Report" />
    <ScoutLayout>
        <ScoutContainer :scout-report="scout_report" :editmode="editmode" :defaultId="props.defaultId"></ScoutContainer>
    </ScoutLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import ScoutLayout from '@/layouts/ScoutLayout.vue';
import Scouter from '@/classes/Scouter';
import { computed, inject, onBeforeMount, onBeforeUnmount, onMounted, provide, ref } from 'vue';
import ScoutReport from '@/classes/ScoutReport';
import ScoutContainer from '@/components/ScoutContainer.vue';
import { useEchoPublic, configureEcho } from '@laravel/echo-vue';
import axios from 'axios';

const props = defineProps({
    expac: Array,
    scout: Object,
    defaultId: Number,
    flash: Object,
})

let scouter = null;
const scout_report = ref(null);
const emitter = inject('emitter')
const wsConnection = ref('disconnected');
// Hold a timeout reference for the fallback ajax polling mechanism.
const ajaxTimeout = ref(null);
// Time between AJAX polls in ms
// Only used when WS connection fails
const ajaxRefreshInterval = 10000;

configureEcho({
    broadcaster: "reverb",
});

const editmode = computed(() => {
    return props.scout.collaborator_password && props.scout.finalized_at === null
})
provide('connectionStatus', wsConnection)
provide('scoutReport', scout_report)
provide('scout', props.scout)
provide('editmode', editmode)
provide('newly_created', props.flash?.newly_created)

let channelName = `scouts.${props.scout.slug}`
if (props.scout.collaborator_password && props.scout.collaborator_password !== '') {
    channelName += `.${props.scout.collaborator_password}`
}

if (props.scout.finalized_at === null) {
    const t = useEchoPublic(channelName, ['.ScoutAssignMob', '.UpdatePointOccupancy'], (e) => {
        //console.log('Update Zone Points requested arrived for', e.zone_id, e.points)
        scout_report.value.updatePointDataForZone(e.zone_id, e.instance_number, e.points)
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
    useEchoPublic(channelName, '.UpdateMeta', (e) => {
        scout_report.value.title = e.title ?? ''
        scout_report.value.scouts = e.scouts ?? []
    })
}

const pollForUpdates = () => {
    // is the websocket connection active? if so, can ignore for now
    if (wsConnection.value === 'connected') {
        ajaxTimeout.value = setTimeout(pollForUpdates, ajaxRefreshInterval)
        return
    }
    //console.log('AJAX polling fallback triggered')

    axios.get(route('scout.updatelist', { scout: props.scout, password: props.scout.collaborator_password }))
        .then((response) => {
            scout_report.value.processAJAXUpdate(response.data)
            ajaxTimeout.value = setTimeout(pollForUpdates, ajaxRefreshInterval)
        }).catch((error) => {
            console.error(`Error message received`, error)
            ajaxTimeout.value = setTimeout(pollForUpdates, ajaxRefreshInterval)
        })
    //ajaxTimeout.value = setTimeout(pollForUpdates, ajaxRefreshInterval)
}

onBeforeMount(() => {
    scouter = new Scouter(props.expac)
    scout_report.value = new ScoutReport(props.scout, scouter)
})
onMounted(() => {
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
    emitter.on('occupy:status', (obj) => {
        axios.post(route('scout.updateOccupiedPoint', { scout: props.scout, password: props.scout.collaborator_password }), obj)
            .catch((error) => {
                console.error(error)
            })
    })
    emitter.on('meta:updated', () => {
        axios.post(route('scout.updateMeta', { scout: props.scout, password: props.scout.collaborator_password }), {
            title: scout_report.value.title,
            scouts: scout_report.value.scouts,
        })
    })
    emitter.on('import:zones-updated', (args) => {
        const zonePointData = scout_report.value.getAllPointDataForZones(args.zonelist)
        axios.patch(route('scout.importPoints', { scout: props.scout, password: props.scout.collaborator_password }), {
            zonelist: args.zonelist,
            point_data: zonePointData
        });
    })

    // Ajax fallback
    if (props.scout.collaborator_password && !props.scout.finalized_at) {
        ajaxTimeout.value = setTimeout(pollForUpdates, ajaxRefreshInterval)
    }

    // Was this a redirect from the log submission?
    if (props?.flash?.newly_created == true) {
        // Clear out any stored scout info, since they've made a successful submission
        if (window && localStorage) {
            localStorage.removeItem('scout-in-progress')
        }
        emitter.emit('show:share')
    }

})

onBeforeUnmount(() => {
    clearTimeout(ajaxTimeout.value)
    emitter.off('point:clear')
    emitter.off('point:assign-mob')
    emitter.off('mob:status')
    emitter.off('meta:updated')
})
</script>

<style lang="scss" scoped></style>