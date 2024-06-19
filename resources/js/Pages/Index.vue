<template>
    <MapContainer
    :expac="props.expac"
     />
</template>

<script setup>
import MapContainer from '../Components/MapContainer.vue';
const props = defineProps({
    expac: Array,
})
</script>