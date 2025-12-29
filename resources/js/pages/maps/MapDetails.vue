<template>
    <div class="grid grid-cols-2 w-full gap-4">
        <div></div>
        <Card class="rounded-xl w-full">
            <CardHeader class="px-2 pb-0 text-center">
                <CardTitle class="text-xl">Point Details</CardTitle>
                <CardDescription>
                    Click a point to see details
                </CardDescription>
            </CardHeader>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import ZoomableZoneMap from '@/components/ZoomableZoneMap.vue';
import { Zone } from '@/types/gametypes';

interface Props {
    zone: Zone
}

const props = defineProps<Props>()

</script>

<style scoped></style>