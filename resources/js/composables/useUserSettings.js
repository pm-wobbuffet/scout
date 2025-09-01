import { reactive, ref, toRefs, watch } from "vue";

export function useUserSettings() {

    const SETTINGS_KEY = 'userSettings'
    const defaultSettings = {
        lang: 'en',
        mobOneColor: '#00C951',
        mobTwoColor: '#2B7FFF',
        dayNightMode: null,
        displayName: null,
    }
    const storedSettings = JSON.parse(localStorage.getItem(SETTINGS_KEY)) || {}
    const settings = ref({ ...defaultSettings, ...storedSettings })

    watch(
        settings,
        (newSettings) => {
            localStorage.setItem(SETTINGS_KEY, JSON.stringify(newSettings))
        },
        { deep: true }
    )

    const updateSetting = (key, value) => {
        settings[key] = value
    }

    return { settings, updateSetting }
}