import { ref, watch } from "vue";

export function useUserSettings() {

    const SETTINGS_KEY = 'userSettings'
    const defaultSettings = {
        lang: 'en',
        mobOneColor: '#00C951',
        mobTwoColor: '#2B7FFF',
        dayNightMode: null,
        displayName: null,
        tomestone_fromcap_string: "Tome Check! Make sure to have less than $c to prevent overcapping!",
        tomestone_totalamount_string: "This train will generate $c."
    }
    let storedSettings = {}
    if (typeof window !== 'undefined' && window.localStorage) {
        storedSettings = JSON.parse(localStorage.getItem(SETTINGS_KEY)) || {}
    }

    const settings = ref({ ...defaultSettings, ...storedSettings })

    watch(
        settings,
        (newSettings) => {
            if (typeof window !== 'undefined' && window.localStorage) localStorage.setItem(SETTINGS_KEY, JSON.stringify(newSettings))
        },
        { deep: true }
    )

    const updateSetting = (key, value) => {
        settings.value[key] = value
    }

    return { settings, updateSetting }
}