<template>
    <div>
        <Head title="Scouting Report" />
        <MapContainer
        :expac="props.expac"
        :scout="props.scout"
        @mapUpdated="handleMapUpdate"
        @pointUpdated="handlePointUpdate"
        :editmode="props.scout.collaborator_password != null"
        :newly-created="props.flash?.newly_created"
        ref="mapRef"
        />
    </div>
</template>

<script setup>
import MapContainer from '@/Components/MapContainer.vue';
import { useForm, Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    expac: Array,
    scout: Object,
    flash: Object,
})

const mapRef = ref(null)
const maxUpdateId = ref(0)
const abortContoller = new AbortController()
const refreshTime = 3000

const handleMapUpdate = function(point_data, instance_data) {
    //console.log(point_data, instance_data)
}

const handlePointUpdate = function(point, mob, point_data, instance_data, zone_id, instance_number) {
    if(!props.scout?.collaborator_password || props.scout.collaborator_password == '') return
    // Stop any get update requests that are pending
    if(abortContoller.abort) {
        abortContoller.abort()
    }
    axios.post(route('scout.update', {scout: props.scout, password: props.scout.collaborator_password}),{
        point_data: point_data,
        instance_data: instance_data,
        point: point,
        mob: mob,
        zone_id: zone_id,
        instance_number: instance_number    
    })
    .then((response) => {
        //console.log(response.data)
        mapRef.value.processUpdate(response.data)
        setTimeout(pollUpdates, refreshTime)
    })
}

const pollUpdates = function() {
    axios.get(route('scout.updatelist', {
        scout: props.scout.slug, 
        password: props.scout.collaborator_password,
        last_id: maxUpdateId.value,
    }),{
        signal: abortContoller.value
    })
    .then((response) => {
        mapRef.value.processUpdate(response.data)
        setTimeout(pollUpdates, refreshTime)
    })
}

onMounted(() => {
    if(props.scout.updates_max_id) {
        maxUpdateId.value = props.scout.updates_max_id
    }
    if(props.scout.collaborator_password) {
        setTimeout(pollUpdates, refreshTime)
    }
})
</script>