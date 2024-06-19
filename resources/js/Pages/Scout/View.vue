<template>
    <MapContainer
    :expac="props.expac"
    :scout="props.scout"
    @mapUpdated="handleMapUpdate"
    />
</template>

<script setup>
import MapContainer from '@/Components/MapContainer.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    expac: Array,
    scout: Object,
})

const handleMapUpdate = function(point_data, instance_data) {
    console.log(point_data, instance_data)
}
</script>