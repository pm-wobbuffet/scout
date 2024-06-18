<template>
    <div class="w-full min-h-[100vh]">
        <nav class="flex flex-wrap w-full items-center justify-between bg-slate-500 text-slate-100 p-2 min-h-[3rem] main-nav flex-grow-1">
            <div>Logo</div>
            <div class="flex expac-list">
                <div v-for="expansion in expac" :key="expansion.id" class="text-center border p-1 px-4 expac-list-item"
                @click="setActiveExpac(expansion.id)"
                :class="{'selected-expansion': expansion.id == selectedExp}">
                    <div>{{ expansion.abbreviation }}</div>
                    <div class="text-sm">
                        0 / {{ mobCount(expansion) }}
                    </div>
                    
                </div>
            </div>
            <div>Share</div>
        </nav>
        <main class="map-main-window">
            <div class="map-image-list order-2">
                <template v-for="zone in getMapsForExpansion()" :key="`mapblock-${zone.id}`">
                    <div class="map-container-block" v-for="i in zone.default_instances" :style="`--map-bg-image: url('/maps/${zone.map_id}.png')`">
                        <div class="absolute mob-list">
                            <ol class="block list-decimal pl-4">
                                <li v-for="(mob, index) in zone.mobs" :class="`mob-number-${index}`">
                                    {{ mob.name }}
                                </li>
                            </ol>
                        </div>
                        <div v-for="aetheryte in zone.aetherytes" class="text-white absolute h-[32px] w-[32px] aetheryte"
                        :style="{'left': convertCoordToPercent(aetheryte.x, zone), 'top': convertCoordToPercent(aetheryte.y, zone)}" :data-title="aetheryte.name">
                        </div>
                        <button v-for="point in zone.spawn_points" class=""
                        :style="{'left': convertCoordToPercent(point.x, zone), 'top': convertCoordToPercent(point.y, zone)}"
                         />
                        <div class="text-right font-semibold text-xl zone-name">
                            {{ zone.name }}
                            <span v-if="zone.default_instances > 1">{{ i }}</span>
                        </div>
                    </div>
                </template>
            </div>
            <aside class="sticky border border-gray-400 p-2 self-start order-1">
                Top | Share

            </aside>
        </main>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    expac: Array,
})

const defaultExp = ref(6)
const selectedExp = ref(6)

const setActiveExpac = function(expac_id) {
    selectedExp.value = expac_id
}

const convertCoordToPercent = function(coord, zone) {
    let c = (coord / (42 / (zone.size_factor / 100) )) * 100
    c = c.toString() + '%'
    return c
}

const getMapsForExpansion = function() {
    //return []
    let curExpac = props.expac.find((el) => el.id == selectedExp.value)
    //console.log(props.expac)
    if(curExpac.zones && curExpac.zones.length > 0) {
        return curExpac.zones
    }
    return []
}

const mobCount = function(expac) {
    let mCount = 0
    expac.zones.forEach( (el) => {
        mCount += el.total_mobs
    })
    return mCount
}


</script>