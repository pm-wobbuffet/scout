<template>
    <div class="flex w-full gap-2">
        <ZoomableMapBase :zone="props.zone">
            <template #points>
                <button v-for="point in page.props.point_data" :key="`point-${point.agg_x}-${point.agg_y}`" class=""
                    :style="{
                        'left': convertCoordToPercent(point.agg_x, props.zone),
                        'top': convertCoordToPercent(point.agg_y, props.zone),
                        'width': getSize(point.num_points),
                        'height': getSize(point.num_points)
                    }" v-text="point.num_points" :data-coords="`(${point.agg_x}, ${point.agg_y})`"></button>
            </template>
        </ZoomableMapBase>
        <Card class="rounded-xl w-full">
            <CardHeader class="px-2 pb-0 text-center">
                <CardTitle class="text-xl">Filters and Options</CardTitle>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="">

                </form>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { convertCoordToPercent } from '@/classes/helpers';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import ZoomableMapBase from '@/components/ZoomableMapBase.vue';
import ZoomableZoneMap from '@/components/ZoomableZoneMap.vue';
import { Zone } from '@/types/gametypes';
import { usePage } from '@inertiajs/vue3';
import { onBeforeMount, ref } from 'vue';

interface Props {
    zone: Zone
}
type PData = {
    agg_x: number,
    agg_y: number,
    num_points: number
}
interface PageProps {
    props: {
        point_data: PData[]
    }
}

const props = defineProps<Props>()
const page = usePage()
const max_count = ref(1)

const getSize = (num_pts) => {
    return 20 + (20 * (num_pts / max_count.value)) + 'px'
}

onBeforeMount(() => {
    max_count.value = page.props.point_data.reduce((max, current) => {
        if (current.num_points > max) {
            return current.num_points
        }
        return max
    }, 1)
})


</script>

<style scoped>
@reference 'tailwindcss';

button {
    @apply bg-blue-700/80 text-white translate-x-[-50%] translate-y-[-50%] rounded-full bg-gray-200 border border-black outline-2 outline-white/50 hover:outline-4 absolute flex items-center justify-center text-center;
}

button:hover::after {
    @apply z-1001 absolute -top-6 left-1/2 translate-x-[-50%] text-sm font-bold text-white text-nowrap bg-slate-400 border border-white px-2 rounded-full pointer-events-none;
    text-shadow: 1px 1px 1px rgba(0, 0, 0, 1);
    content: attr(data-coords)
}
</style>