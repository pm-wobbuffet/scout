<template>
    <div :style="mobStyles">
        <slot />
    </div>
    <footer class="text-xs flex">
        <div>
            FINAL FANTASY XIV © 2010 - 2026 SQUARE ENIX CO., LTD. All Rights Reserved.
            Created by Kaiden Alenko of Adamantoise.
            Source on <a href="https://github.com/pm-wobbuffet/scout">Github</a>.
        </div>
        <div class="ml-auto">
            <ul class="flex gap-x-2 items-center justify-center">
                <li class="border-r">
                    <Link :href="route('help.index')" class="px-2">Help</Link>
                </li>
                <li>
                    <Link :href="route('maps.index')" class="px-2">Spawn Point Tracking</Link>
                </li>
            </ul>
        </div>
    </footer>
</template>

<script setup>
import { useUserSettings } from '@/composables/useUserSettings';
import { Link } from '@inertiajs/vue3';
import { computed, provide } from 'vue';

const { settings } = useUserSettings()
provide('settings', settings)

const mobStyles = computed(() => {
    return {
        '--mob-one-color': settings.value.mobOneColor,
        '--mob-two-color': settings.value.mobTwoColor,
    }
})

</script>
