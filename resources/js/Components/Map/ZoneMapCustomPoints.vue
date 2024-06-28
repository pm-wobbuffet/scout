<template>
    <div class="block bg-slate-500 text-slate-100 dark:text-slate-300 p-2 rounded-lg">
        <h1 class="text-lg text-center font-bold">Options</h1>
        <div class="flex flex-row flex-wrap items-center gap-1">
            <div>Coordinate Rounding:</div>
            <div>
                <select id="coordRounding" name="coordRounding"
                v-model="form.rounding">
                    <option v-for="option in roundingOptions" :value="option">
                        {{ option }}
                    </option>
                </select>
            </div>
            <div title="Only show custom points that were imported via an in-game coordinate chat message">Only Chat Log Lines:</div>
            <div>
                <input type="checkbox" name="coordChatOnly" value="1"
                title="Only show custom points that were imported via an in-game coordinate chat message"
                v-model="form.chatonly" />
            </div>
        </div>
    </div>
    <div class="map-container-block w-[900px] h-[900px]" :style="`--map-bg-image: url('/maps/${zone.map_id}.png')`">
        <div v-for="aetheryte in zone.aetherytes" class="text-white absolute h-[32px] w-[32px] aetheryte"
            :style="{ 'left': convertCoordToPercent(aetheryte.x, zone), 'top': convertCoordToPercent(aetheryte.y, zone) }"
            :data-title="getDisplayName(aetheryte, props.language)">
        </div>
        <button v-for="point in customPoints" class="bg-red custom-point"
            :style="{ 'left': convertCoordToPercent(point.agg_x, zone), 'top': convertCoordToPercent(point.agg_y, zone) }"
            :data-title="`${point.agg_x},${point.agg_y}`"
            :data-coords="`${point.agg_x}, ${point.agg_y} (${point.num_points} ${point.num_points == 1 ? 'Report' : 'Reports'})`">{{ point.num_points }}</button>

        <div class="text-right font-semibold text-xl zone-name">
            {{ getDisplayName(zone, props.language) }}
        </div>
    </div>
</template>

<script setup>
import { getDisplayName } from '@/helpers';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { onBeforeMount, onMounted, onUpdated, ref, watch } from 'vue';

const props = defineProps({
    zone: Object,
})

const customPoints = ref([])
const roundingOptions = [0.1, 0.5, 1, 2]
const form = useForm({
    verifiedOnly: Boolean,
    zone_id: Number,
    rounding: Number,
    chatonly: Number,
})

const convertCoordToPercent = function (coord, zone) {
    let c = (coord - 1) / (zone.max_coord_size) * 100
    c = c.toString() + '%'
    return c
}

watch(() => props.zone, () => {
    //console.log('zone updated')
    getPoints()
})



const getPoints = function () {
    axios.get(route('custompoints.zone', { zone: props.zone.id }),{
        params: {
            rounding: form.rounding,
            chatonly: form.chatonly ?? 0,
        }
    })
        .then((response) => {
            //console.log(response.data)
            customPoints.value = response.data
        })
        .catch((error) => {
            console.log(error)
        })
}

onBeforeMount(() => {
    form.rounding = 0.1
    form.chatonly = 0,
    getPoints()

    watch(() => [form.rounding, form.chatonly], () => {
        console.log('rounding updated')
        getPoints()
    })
})


</script>
