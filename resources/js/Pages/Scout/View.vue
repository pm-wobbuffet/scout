<template>
    <MapContainer
    :expac="props.expac"
    :scout="props.scout"
    @mapUpdated="handleMapUpdate"
    @pointUpdated="handlePointUpdate"
    />
</template>

<script setup>
import MapContainer from '@/Components/MapContainer.vue';
import { useForm } from '@inertiajs/vue3';
import Echo from 'laravel-echo';
import { onMounted } from 'vue';

const props = defineProps({
    expac: Array,
    scout: Object,
})

const handleMapUpdate = function(point_data, instance_data) {
    console.log(point_data, instance_data)
}

const handlePointUpdate = function(point, mob) {
    console.log(point, mob)
}

onMounted(() => {
    // if(props.scout.collaborator_password) {
    //     Echo.private(`Scout.${props.scout.id}.${props.scout.collaborator_password}`)
    //     .listen('OrderShipmentStatusUpdated', (e) => {
    //         console.log(e.order);
    //     });
    // }
})
</script>