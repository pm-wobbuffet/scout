<template>
    <div>
        <Head title="Scouting Report" />
        <MapContainer
        :expac="props.expac"
        :scout="props.scout"
        @pointUpdated="handlePointUpdate"
        @mapFinalized="handleMapFinalized"
        @clipboardImport="handleClipboardImport"
        :editmode="props.scout.collaborator_password != null && !props.scout.finalized_at"
        :newly-created="props.flash?.newly_created"
        ref="mapRef"
        />
    </div>
</template>

<script setup>
import MapContainer from '@/Components/MapContainer.vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, onUnmounted, ref, onBeforeMount } from 'vue';
import { useToast } from "vue-toastification";

const props = defineProps({
    expac: Array,
    scout: Object,
    flash: Object,
})

const mapRef = ref(null)
const processUpdates = ref(true)
const maxUpdateId = ref(0)
let updateTimeout = ref(null)
// Number of seconds to wait between update pings
const refreshTime = 5000
const toast = useToast()

const handleClipboardImport = function(assignments, point_data, custom_points, instance_data) {
    if(!props.scout?.collaborator_password || props.scout.collaborator_password == '') return
    clearTimeout(updateTimeout)
    processUpdates.value = false
    //console.log(assignments, point_data, custom_points)
    axios.post(route('scout.import', {scout: props.scout, password: props.scout.collaborator_password}),{
        point_data: point_data,
        custom_points: custom_points,
        instance_data: instance_data
    }).then((response) => {
        mapRef.value.processUpdate(response.data)
        updateTimeout = setTimeout(pollUpdates, refreshTime)
        processUpdates.value = true
    }).catch(function (error) {
        console.log(error);
    });
}

const handleMapFinalized = function()
{
    router.post(route('scout.finalize',
    {scout: props.scout, password: props.scout.collaborator_password} ))
}

const handlePointUpdate = function(point, mob, point_data, instance_data, zone_id, instance_number, custom_points) {
    //console.log('Point Update Called')
    if(!props.scout?.collaborator_password || props.scout.collaborator_password == '') return
    // Stop any get update requests that are pending
    clearTimeout(updateTimeout)
    axios.post(route('scout.update', {scout: props.scout, password: props.scout.collaborator_password}),{
        point_data: point_data,
        instance_data: instance_data,
        point: point,
        mob: mob,
        zone_id: zone_id,
        instance_number: instance_number,
        custom_points: custom_points, 
    })
    .then((response) => {
        //console.log(response.data)
        mapRef.value.processUpdate(response.data)
        if(response.data.collision) {
            toast.error("Another user has already positioned that mob. Your data has been refreshed.")
        }
        updateTimeout = setTimeout(pollUpdates, refreshTime)
    })
    .catch(function (error) {
        console.log(error);
    });
}

const pollUpdates = function() {
    // return
    axios.get(route('scout.updatelist', {
        scout: props.scout.slug, 
        password: props.scout.collaborator_password,
        last_id: maxUpdateId.value,
    }))
    .then((response) => {
        if(processUpdates.value == true) {
            mapRef.value.processUpdate(response.data)
            clearTimeout(updateTimeout)
        }
        
        // As long the map isn't finalized, keep on polling for updates
        if(!response.data.finalized_at) {
            updateTimeout = setTimeout(pollUpdates, refreshTime)
        } else {
            router.get(route('scout.view',{scout: props.scout, password: props.scout.collaborator_password}),{}, {
                preserveScroll:true
            })
        }
    })
}
onMounted(() => {
    if(props.scout.updates_max_id) {
        maxUpdateId.value = props.scout.updates_max_id
    }
    if(props.scout.collaborator_password && !props.scout.finalized_at) {
        updateTimeout = setTimeout(pollUpdates, refreshTime)
    }
})
</script>