<template>
    <div>
        {{ props.zone.name }}
    </div>
</template>

<script setup lang="ts">
const props = defineProps<{
    zone: Object
}>();

</script>

<style scoped></style>