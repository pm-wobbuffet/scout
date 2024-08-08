
export const languages = [
    {'abbrev': 'en', 'name': 'English'},
    {'abbrev': 'ja', 'name': 'Japanese'},
    {'abbrev': 'fr', 'name': 'French'},
    {'abbrev': 'de', 'name': 'German'},
]

export const getDisplayName = function(obj, language) {
    if('names' in obj) {
        if(language in obj['names']) {
            return obj['names'][language]
        }
        return obj['name']
    }
    return obj['name']
}

export const formatCoordinate = function(coord) {
    // Should probably only return 1 decimal place like the in-game format
    return (Math.round(coord * 10) / 10).toFixed(1)
}
