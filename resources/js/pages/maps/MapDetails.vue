<template>
    <div class="flex w-full gap-2">
        <ZoomableMapBase :zone="props.zone">
            <template #points>
                <button v-for="point in page.props.point_data" :key="`point-${point.agg_x}-${point.agg_y}`"
                    class="point_data" :style="{
                        'left': convertCoordToPercent(point.agg_x, props.zone),
                        'top': convertCoordToPercent(point.agg_y, props.zone),
                        'width': getSize(point.num_points),
                        'height': getSize(point.num_points)
                    }" v-text="point.num_points"
                    :data-coords="`(${formatCoordinate(point.agg_x)}, ${formatCoordinate(point.agg_y)})`"></button>
            </template>
        </ZoomableMapBase>
        <Card class="rounded-xl w-full">
            <CardHeader class="px-2 pb-0 text-center">
                <CardTitle class="text-xl">Filters and Options</CardTitle>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="">
                    <fieldset class="border rounded-md p-2 text-lg">
                        <legend class="font-bold">Coordinate Rounding</legend>
                        <div class="flex gap-x-2">
                            <label><input type="radio" value="0.1" class="mr-2" v-model="form.rounding" />0.1</label>
                            <label><input type="radio" value="0.5" class="mr-2" v-model="form.rounding" />0.5</label>
                            <label><input type="radio" value="1" class="mr-2" v-model="form.rounding" />1</label>
                            <label><input type="radio" value="2" class="mr-2" v-model="form.rounding" />2</label>
                        </div>
                    </fieldset>
                    <fieldset class="border rounded-md p-2 text-lg">
                        <legend class="font-bold">Mob Seen</legend>
                        <label class="mr-2"><input type="radio" :value="null" class="mr-2" v-model="form.mobid" />Any A
                            Rank</label>
                        <template v-for="mob in props.zone.mobs" :key="`mobs-${mob.id}`">
                            <label class="mr-2"><input type="radio" :value="mob.id" class="mr-2" v-model="form.mobid" />
                                {{ getDisplayName(mob) }}</label>
                        </template>
                    </fieldset>
                    <Button variant="default" class="mt-2 flex w-full" @click="submitForm">Update View</Button>
                </form>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { convertCoordToPercent, getDisplayName } from '@/classes/helpers';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import ZoomableMapBase from '@/components/ZoomableMapBase.vue';
import ZoomableZoneMap from '@/components/ZoomableZoneMap.vue';
import { Zone } from '@/types/gametypes';
import { useForm, usePage } from '@inertiajs/vue3';
import { onBeforeMount, ref } from 'vue';
import { formatCoordinate } from '@/classes/helpers';

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
        point_data: PData[],
        rounding: number,
    }
}
const form = useForm({
    rounding: 0.1,
    mobid: null
})

const props = defineProps<Props>()
const page = usePage()
const max_count = ref(1)

const getSize = (num_pts) => {
    return 20 + (10 * (num_pts / max_count.value)) + 'px'
}

const submitForm = () => {
    form.get(route('maps.index', { zone: props.zone.id }))
}

onBeforeMount(() => {
    max_count.value = page.props.point_data.reduce((max, current) => {
        if (current.num_points > max) {
            return current.num_points
        }
        return max
    }, 1)

    form.rounding = page.props.rounding ?? 0.1
    form.mobid = page.props.mobid ?? null
})


</script>

<style scoped>
@reference 'tailwindcss';

button.point_data {
    @apply bg-blue-700/50 text-white translate-x-[-50%] translate-y-[-50%] rounded-full border border-black outline-2 outline-white/50 hover:outline-4 absolute flex items-center justify-center text-center;
}

button.point_data:hover {
    @apply bg-blue-700 z-10;
}

button.point_data:hover::after {
    @apply z-1001 absolute -top-6 left-1/2 translate-x-[-50%] text-sm font-bold text-white text-nowrap bg-slate-400 border border-white px-2 rounded-full pointer-events-none;
    text-shadow: 1px 1px 1px rgba(0, 0, 0, 1);
    content: attr(data-coords)
}
</style>