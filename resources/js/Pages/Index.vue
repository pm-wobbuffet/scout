<template>
    <div>
        <Head title="Scouting Report" />
        <MapContainer
        :expac="props.expac"
        :editmode="true"
         />
    </div>
</template>

<script setup>
import MapContainer from '../Components/MapContainer.vue';
import { Head } from '@inertiajs/vue3';
const props = defineProps({
    expac: Array,
})
</script>