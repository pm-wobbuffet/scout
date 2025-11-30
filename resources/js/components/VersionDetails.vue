<template>
    <div v-if="logDetails.name == 'Point Cleared'">

    </div>
    <div v-else>
        hi
    </div>
</template>

<script setup lang="ts">
interface ScoutVersionDetails {
    name: string,
    reporter?: string,
}
interface MobAssignedEvent extends ScoutVersionDetails {
    zone_id?: number,
    mob_id?: number,
    point_id?: number,
    point_type?: string
}
interface Props {
    logDetails: MobAssignedEvent | ScoutVersionDetails
}

const props = defineProps<Props>();
</script>

<style scoped></style>