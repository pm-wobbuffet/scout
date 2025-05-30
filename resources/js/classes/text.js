/**
 * Text functions related to coordinate import
 */

import ScoutReport from "@/classes/ScoutReport"

// mapping of Unicode characters used in-game for Instance markers
const instanceToIntMapping = {
    "": 1,
    "": 2,
    "": 3,
    "": 4,
    "": 5,
    "": 6,
}
// reverse mapping
const intToInstanceMapping = Object.fromEntries(Object.entries(instanceToIntMapping).map(([key, value]) => [value, key]))

// Create variable to externally track whether we're parsing (for disabling buttons, etc)
export let currentlyParsing = false

/**
 * Take an input string and parse out valid coordinate/mob combos from it
 * Returns a list of failed and succeeded parses
 * @param {string} logLines 
 * @param {ScoutReport} scoutReport
 */
export const parseLog = (logLines, scoutReport) => {
    currentlyParsing = true
    const scouter = scoutReport.value.scouter_instance

    // Initialize a container to hold each row that passed/failed, for reporting to the original script
    const assignments = {
        fail: [],
        success: []
    }

    //\uE0B1 = Instance 1 , \uE0B2 = 2 , \uE0B3 = 3  \uE0BB = start of flag marker
    const re = /[\uE0BB]([^\uE0B1-\uE0B6]*)([\uE0B1-\uE0B6]?) \( ([0-9\.]+)\W+,\W+([0-9\.]+)\W+\).*/
    const instanceCheck = /(instance.|i)([0-6])/i
    const linesArr = logLines.split(/\r?\n/);
    linesArr.forEach((line) => {
        let found = line.match(re)
        let instance = 1
        if (found) {
            let zoneName = found[1]
            let x = parseFloat(found[3])
            let y = parseFloat(found[4])
            let zone = scouter.getZoneByName(zoneName)
            let point = null

            if (found[2]) {
                // Check for instance information
                instance = instanceToIntMapping[found[2]] ?? 1
            }
            // Allow user to override instance calculation by specifying it manually in text
            // can either be "instance 2" or "i2", etc.
            let instOverride = line.match(instanceCheck)
            if (instOverride) {
                instance = Number(instOverride[2])
            }
            if (!found[2] && !instOverride && scoutReport.value.getInstanceCountForZone(zone.id) > 1) {
                assignments.fail.push({
                    line: line,
                    reason: 'No instance marker was found, but the zone expects them.'
                })
                return false
            }
            if (instance > scoutReport.value.getInstanceCountForZone(zone.id)) {
                assignments.fail.push({
                    'line': line,
                    'reason': 'Invalid instance number detected (too large).'
                })
                return false
            }
            let mob = zone.mobs.find((el) => {
                let lc = line.toLowerCase()
                if (lc.includes(el.name.toLowerCase())) {
                    return true
                }
                for (let lang in el.names) {
                    if (lc.includes(el.names[lang].toLowerCase())) {
                        return true
                    }
                }
                return false
            })
            assignments.success.push({
                line: line,
                info: {
                    zone: zone,
                    mob: mob,
                    x: x,
                    y: y,
                    instance: instance,
                }
            })
        }
    })
    currentlyParsing = false
    return assignments
}

