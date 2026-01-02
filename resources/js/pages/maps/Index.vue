<template>

    <Head title="Turtle Scout Maps" />
    <ScoutLayout>
        <div class="min-w-full min-h-[100vh]">
            <nav class="main-nav shadow-md">
                <div class="shrink hidden md:block">
                    <a href="/"><img src="/turtleknife.png" height="40" width="90" class="inline"
                            alt="Turtle Scout Logo, friendly turtle with a knife" /></a>
                </div>
                <div class="flex flex-col items-center justify-center gap-0 grow">
                    <h1 class="text-xl font-bold block">Spawn Point Tracking</h1>
                    <div>Check the status of mapping out spawn points for new mobs!</div>
                </div>
            </nav>
            <main class="map-main-window bg-(--background-darker) min-h-[80vh]">
                <aside
                    class="md:left-auto ml-1 self-start order-1 bg-(--background) dark:bg-slate-800 text-nowrap whitespace-nowrap">
                    <div class="border border-gray-400 bg-(--background-lighter) shadow-sm">
                        <div class="font-bold bg-slate-300 pl-1 text-sm dark:bg-slate-700 dark:text-slate-300">
                            Zone List
                        </div>
                        <div>
                            <ul>
                                <li v-for="zone in props.expac.zones" :key="`zone-${zone.id}`"
                                    class="transition-all duration-300 hover:bg-(--background)"
                                    :class="{ 'selected-zone': zone.id == props.selected_zone }">
                                    <Link :href="route('maps.index', { zone: zone.id })" class="block p-1">{{
                                        getZoneDisplayName(zone) }}</Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </aside>
                <MapDetails class="order-2 self-start" :zone="selectedZone" />
            </main>
        </div>
    </ScoutLayout>
</template>

<script setup lang="ts">
import ScoutLayout from '@/layouts/ScoutLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import type { Zone } from '@/types/gametypes';
import MapDetails from './MapDetails.vue';
import { onBeforeMount, onMounted, provide, ref } from 'vue';
import { getZoneDisplayName } from '@/classes/helpers';
import { useUserSettings } from '@/composables/useUserSettings';

interface Props {
    expac: {
        id: number,
        abbreviation: string,
        name: string,
        zones: Zone[]
    },
    selected_zone?: number
}

const selectedZone = ref(null)
const { settings, setSetting } = useUserSettings()

provide('settings', settings)

const props = defineProps<Props>()
const page = usePage()

onBeforeMount(() => {
    selectedZone.value = props.expac.zones.find((el) => {
        return el.id == props.selected_zone
    })
})

onMounted(() => {

})

</script>

<style scoped>
@reference 'tailwindcss';

li {
    @apply cursor-pointer;
}

.selected-zone {
    @apply bg-(--background-darker) font-bold;
}
</style>