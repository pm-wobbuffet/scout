export default class Scouter {

    mob_data = {}
    expansion_data = []
    zone_data = {}
    spawn_points = {}
    
    constructor(data) {
        this.expansion_data = data
        this.expansion_data.forEach((expansion) => {
            expansion.zones.forEach((zone) => {
                this.zone_data[zone.id] = zone
                zone.mobs.forEach((mob) => {
                    this.mob_data[mob.id] = mob
                })
                zone.spawn_points.forEach((spawn_point) => {
                    spawn_point['expansion_id'] = expansion.id
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
        if(id in this.mob_data) {
            return this.mob_data[id]
        }
        return null
    }

    getMobsForZone(zone_id) {
        let ret = []
        for( let [mob_id, mob_data] of Object.entries(this.mob_data)) {
            //console.log(mob_data, zone_id)
            if(mob_data.zone_id == zone_id) {
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

    getZonesByExpansion(expansion_id) {
        let ret = []
        for( let [zone_id, zone_data] of Object.entries(this.zone_data) ) {
            if(zone_data.expansion_id == expansion_id) {
                ret.push(zone_data)
            }
        }
        return ret
    }


}