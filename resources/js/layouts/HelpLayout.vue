<template>
    <div class="p-1">
        <div class="h-[90px] flex w-full items-center">
            <div>
                <Link href="/"><img src="/turtleknife.png" alt="Turtle Scout Logo" class="max-h-[90px]" /></Link>
            </div>
            <div class="flex-grow text-center">
                <h1 class="font-bold text-2xl">Turtle Scout Help</h1>
            </div>
        </div>
        <div class="flex">
            <div class="p-3">
                Sidebar
            </div>
            <slot />
        </div>

    </div>
    <footer class="text-sm">
        Created by Kaiden Alenko of Adamantoise.
        Source available on <a href="https://github.com/pm-wobbuffet/scout">Github</a>.
        <br>
        FINAL FANTASY XIV © 2010 - 2024 SQUARE ENIX CO., LTD. All Rights Reserved. Logo thanks to the AHA Discord.
    </footer>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<style scoped></style>