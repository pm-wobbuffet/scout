<template>
    <div :style="mobStyles">
        <slot />
    </div>
    <footer class="text-sm">
        Created by Kaiden Alenko of Adamantoise.
        Source available on <a href="https://github.com/pm-wobbuffet/scout">Github</a>.
        <br>
        FINAL FANTASY XIV © 2010 - 2024 SQUARE ENIX CO., LTD. All Rights Reserved. Logo thanks to the AHA Discord.
    </footer>
</template>

<script setup>
import { useUserSettings } from '@/composables/useUserSettings';
import { computed, provide } from 'vue';

const { settings, updateSetting } = useUserSettings()
provide('settings', settings)

const mobStyles = computed(() => {
    return {
        '--mob-one-color': settings.value.mobOneColor,
        '--mob-two-color': settings.value.mobTwoColor,
    }
})

</script>
