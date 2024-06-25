
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