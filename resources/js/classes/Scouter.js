/**
 * @class Scouter
 */
export default class Scouter {

    mob_data = {}
    expansion_data = []
    zone_data = {}
    spawn_points = {}

    constructor(data) {
        // Initialize sort order array
        const userSort = JSON.parse(localStorage.getItem('sortOrders') ?? '{}')
        this.expansion_data = data
        this.expansion_data.forEach((expansion) => {
            expansion.zones.forEach((zone) => {
                if (zone.id in userSort) {
                    zone.sort_priority = userSort[zone.id]
                }
                this.zone_data[zone.id] = zone
                zone.mobs.forEach((mob) => {
                    this.mob_data[mob.id] = mob
                })
                zone.spawn_points.forEach((spawn_point) => {
                    spawn_point['expansion_id'] = expansion.id
                    // If for some reason the spawn point type was omitted from
                    // the data array, default to 'spawn_point' since these are all
                    // verified spawn points
                    if (!spawn_point.point_type || spawn_point.point_type === null) {
                        spawn_point['point_type'] = 'spawn_point'
                    }
                    this.spawn_points[spawn_point.id] = spawn_point
                })
            })
        })
    }

    /**
     * Get an expansion by its internal ID number
     * @param {Number} id 
     * @returns 
     */
    getExpacById(id) {
        return this.expansion_data.find((val, idx) => {
            return val.id == id
        })
    }

    /**
     * Return the mob by a given ID or null if no mob is found
     * Null should be used to indicate occupied points in a final ZoneMap
     * @param {Number} id 
     * @returns Object
     */
    getMobById(id) {
        if (id in this.mob_data) {
            return this.mob_data[id]
        }
        return null
    }

    getMobsForZone(zone_id) {
        let ret = []
        for (let [mob_id, mob_data] of Object.entries(this.mob_data)) {
            if (mob_data.zone_id == zone_id) {
                ret.push(mob_data)
            }
        }
        return ret
    }

    getValidMobsForPoint(point) {

    }

    /**
     * Retrieve a zone's data by its primary ID
     * @param {Number} id 
     * @returns 
     */
    getZoneById(id) {
        return this.zone_data[id]
    }

    getZoneByName(zoneName) {
        for (let zone_id in this.zone_data) {
            let z = this.zone_data[zone_id]
            if (z.name == zoneName
                || z.names['en'] == zoneName
                || z.names['de'] == zoneName
                || z.names['ja'] == zoneName
                || z.names['fr'] == zoneName
            ) {
                return z
            }
        }
        return false
    }

    getZonesByExpansion(expansion_id) {
        let ret = []
        for (let [zone_id, zone_data] of Object.entries(this.zone_data)) {
            if (zone_data.expansion_id == expansion_id) {
                ret.push(zone_data)
            }
        }
        // Apply user sort
        return ret.sort((a, b) => {
            return a.sort_priority - b.sort_priority
        })
    }

    getSpawnPointById(point_id) {
        return this.spawn_points[point_id]
    }


}