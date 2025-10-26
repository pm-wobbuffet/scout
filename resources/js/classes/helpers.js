import dayjs from 'dayjs'

export const languages = [
    { 'abbrev': 'en', 'name': 'English' },
    { 'abbrev': 'ja', 'name': 'Japanese' },
    { 'abbrev': 'fr', 'name': 'French' },
    { 'abbrev': 'de', 'name': 'German' },
]

export const getDisplayName = function (obj, language) {
    if (obj && 'names' in obj) {
        if (language in obj['names']) {
            return obj['names'][language]
        }
        return obj['name']
    }
    if (obj && 'name' in obj) {
        return obj['name']
    }
    return 'Unknown'
}

export const formatCoordinate = function (coord) {
    // Should probably only return 1 decimal place like the in-game format
    return (Math.round(coord * 10) / 10).toFixed(1)
}

export const convertCoordToPercent = function (coord, zone) {
    let c = (coord - 1) / (zone.max_coord_size) * 100
    c = c.toString() + '%'
    return c
}

export const getScouterName = function () {
    const settings = JSON.parse(localStorage.getItem('userSettings') || '{}')
    if (!settings) return null
    return settings?.displayName
}

export const formatDateTime = function (dateTimeString, formatString) {
    const date = dayjs(dateTimeString)
    if (formatString == null || formatString == "") {
        formatString = 'YYYY MMM DD H:mm:ss'
    }
    return date.format(formatString)
}

export const instanceToIntMapping = {
    "": 1,
    "": 2,
    "": 3,
    "": 4,
    "": 5,
    "": 6,
}
export const intToInstanceMapping = Object.fromEntries(Object.entries(instanceToIntMapping).map(([key, value]) => [value, key]))
