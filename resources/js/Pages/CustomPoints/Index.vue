<template>
    <div class="w-full min-h-[100vh]">
        <nav
                class="flex flex-wrap w-full items-center justify-between bg-slate-500 dark:bg-slate-900 text-slate-100 dark:text-slate-400 p-2 min-h-[3rem] main-nav flex-grow-1">
                <div class="shrink">
                    <a href="/"><img src="/turtleknife.png" height="40" width="90" class="inline"
                            alt="The turtle says to murder" /></a>
                </div>
                <div class="flex flex-row expac-list place-self-center m-auto">
                    <h1 class="text-xl font-bold">Custom Points</h1>
                </div>
        </nav>
        <main class="map-main-window">
            <div class="map-image-list order-2 justify-center flex-col items-center">
                <ZoneMapCustomPoints
                :zone="chosenZone" 
                />
            </div>
            <aside class="sticky top-0 border border-gray-400 ml-1 self-start order-1 bg-white dark:bg-slate-800 text-black dark:text-slate-300">
                <div class="font-bold bg-slate-300 p-1 dark:bg-slate-700 dark:text-slate-300">
                    Zones
                </div>
                <div v-for="zone in props.expac.zones" class="p-2 hover:bg-gray-300 dark:hover:bg-slate-700"
                :class="{'selected-zone': zone.id == chosenZone.id}">
                    <button type="button" class="block underline"
                    @click="setZone(zone)"
                    >{{ zone.name }}</button>
                </div>
            </aside>
        </main>
    </div>
</template>

<script setup>
import ZoneMapCustomPoints from '@/Components/Map/ZoneMapCustomPoints.vue';
import { useForm } from '@inertiajs/vue3';
import { onBeforeMount, onMounted, ref } from 'vue';


const chosenZone = ref(null)

const props = defineProps({
    expac: Object,
})

const setZone = function(zone) {
    chosenZone.value = zone
}

onBeforeMount(() => {
    chosenZone.value = props.expac.zones[0]
})
</script>