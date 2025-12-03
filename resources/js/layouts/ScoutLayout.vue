<template>
    <div :style="mobStyles">
        <slot />
    </div>
    <footer class="text-xs">
        FINAL FANTASY XIV © 2010 - {{ dayjs().get('year') }} SQUARE ENIX CO., LTD. All Rights Reserved. Logo thanks to
        the AHA Discord.
        Created by Kaiden Alenko of Adamantoise.
        Source available on <a href="https://github.com/pm-wobbuffet/scout">Github</a>.
    </footer>
</template>

<script setup>
import { useUserSettings } from '@/composables/useUserSettings';
import dayjs from 'dayjs';
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
