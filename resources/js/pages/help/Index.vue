<template>

    <Head title="Turtle Scout Help" />
    <ScoutLayout>
        <!-- <img src="@resources/images/help/overview.webp" class="max-w-[600px]" alt="Site Overview" /> -->
        <div class="min-w-full">
            <nav class="main-nav shadow-md">
                <div class="shrink hidden md:block">
                    <a href="/"><img src="/turtleknife.png" height="40" width="90" class="inline"
                            alt="Turtle Scout Logo, friendly turtle with a knife" /></a>
                </div>
                <div class="flex flex-col items-center justify-center gap-0 grow">
                    <h1 class="text-xl font-bold block">Help Center</h1>
                    <div>How do I use this thing?</div>
                </div>
            </nav>

            <main class="map-main-window">
                <aside
                    class="md:left-auto ml-1 self-start order-1 bg-(--background) dark:bg-slate-800 text-nowrap whitespace-nowrap">
                    <div class="border border-gray-400 bg-(--background-lighter) shadow-sm">
                        <div class="font-bold bg-slate-300 pl-1 text-sm dark:bg-slate-700 dark:text-slate-300">
                            Help Sections
                        </div>
                        <div>
                            <ul>
                                <li class="transition-all duration-300 hover:bg-(--background)">
                                    Overview
                                </li>
                                <li class="transition-all duration-300 hover:bg-(--background)">
                                    Using The Map
                                </li>
                                <li class="transition-all duration-300 hover:bg-(--background)">
                                    Sharing Reports
                                </li>
                                <li class="transition-all duration-300 hover:bg-(--background)">
                                    etc
                                </li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </main>
        </div>
    </ScoutLayout>
</template>

<script setup lang="ts">
import ScoutLayout from '@/layouts/ScoutLayout.vue';
import { Head } from '@inertiajs/vue3';

</script>

<style scoped></style>