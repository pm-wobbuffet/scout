<template>
    <Head title="New Scout"></Head>
    <ScoutLayout>

    </ScoutLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import ScoutLayout from '@/layouts/ScoutLayout.vue';

const props = defineProps({
    expac: Array,
    defaultId: Number,
})
</script>

<style lang="scss" scoped>

</style>