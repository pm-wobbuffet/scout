<template>

    <Head title="Viewing Scouting Report" />
    <ScoutLayout>
        <ScoutContainer :scout-report="scout_report" :editmode="editmode" :defaultId="props.defaultId"></ScoutContainer>
    </ScoutLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
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
    ajaxRefreshInterval: Number,
})


const scouter = new Scouter(props.expac)
const scout_report = ref(new ScoutReport(props.scout, scouter))
const emitter = inject('emitter')
const wsConnection = ref(null);
// Hold a timeout reference for the fallback ajax polling mechanism.
const ajaxTimeout = ref(null);
// Time between AJAX polls in ms
// Only used when WS connection fails

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

const pollForUpdates = () => {
    // is the websocket connection active? if so, can ignore for now
    if (wsConnection.value === 'connected') {
        ajaxTimeout.value = setTimeout(pollForUpdates, props.ajaxRefreshInterval)
        return
    }
    //console.log('AJAX polling fallback triggered')

    axios.get(route('scout.updatelist', { scout: props.scout, password: props.scout.collaborator_password }))
        .then((response) => {
            scout_report.value.processAJAXUpdate(response.data)
            ajaxTimeout.value = setTimeout(pollForUpdates, props.ajaxRefreshInterval)
        }).catch((error) => {
            console.error(`Error message received`, error)
            ajaxTimeout.value = setTimeout(pollForUpdates, props.ajaxRefreshInterval)
        })
    //ajaxTimeout.value = setTimeout(pollForUpdates, props.ajaxRefreshInterval)
}

onBeforeMount(() => {

})
onMounted(() => {

    if (props.scout.finalized_at === null) {
        const t = useEchoPublic(channelName, ['.ScoutAssignMob', '.UpdatePointOccupancy'], (e) => {
            scout_report.value.updatePointDataForZone(e.zone_id, e.instance_number, e.points, e.custom_points)
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
        useEchoPublic(channelName, '.UpdateAllMobStatus', (e) => {
            scout_report.value.setDeadMobList(e.dead_mobs)
        })
        useEchoPublic(channelName, '.UpdateMeta', (e) => {
            scout_report.value.title = e.title ?? ''
            scout_report.value.scouts = e.scouts ?? []
        })
        useEchoPublic(channelName, '.UpdateZonesOccupancy', (e) => {
            scout_report.value.handleZoneOccupancyUpdate(e)
        })
        useEchoPublic(channelName, '.UpdateInstanceCounts', (e) => {
            scout_report.value.instance_data = e.instance_data
        })
        useEchoPublic(channelName, '.FinalizeReport', (e) => {
            router.get(route('scout.view', { scout: props.scout }))
        })
    }

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
        }).then((data) => {
            if ('custom_points' in data.data) {
                // Need to update any of our custom points that have custom negative ID numbers to their newly assigned
                // actual database ID numbers
                scout_report.value.processCustomPointValues(data.data.custom_points)
            }
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
            point_data: zonePointData,
            custom_points: scout_report.value.custom_points
        });
    })
    emitter.on('instances:updated', (args) => {
        axios.post(route('scout.updateinstances', { scout: props.scout, password: props.scout.collaborator_password }), {
            ...args
        });
    })
    emitter.on('scout:finalize', (args) => {
        axios.post(route('scout.finalize', { scout: props.scout, password: props.scout.collaborator_password }))
            .then(() => {
                router.get(route('scout.view', { scout: props.scout }))
            })
    })

    // Ajax fallback
    if (props.scout.collaborator_password && !props.scout.finalized_at) {
        ajaxTimeout.value = setTimeout(pollForUpdates, props.ajaxRefreshInterval)
    }

    // Was this a redirect from the log submission?
    if (props?.flash?.newly_created == true) {
        // Clear out any stored scout info, since they've made a successful submission
        if (typeof window !== "undefined" && typeof localStorage !== "undefined") {
            localStorage.removeItem('scout-in-progress')
        }
        emitter.emit('show:share')
    }

})

onBeforeUnmount(() => {
    clearTimeout(ajaxTimeout.value)
    emitter.off('*')
})
</script>

<style lang="scss" scoped></style>