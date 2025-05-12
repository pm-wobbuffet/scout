export default class Scouter {

    mob_data = {}
    expansion_data = []
    zone_data = {}
    spawn_points = {}
    
    constructor(data) {
        this.expansion_data = data
        this.expansion_data.forEach((el) => {
            el.zones.forEach((zone) => {
                this.zone_data[zone.id] = zone
                zone.mobs.forEach((mob) => {
                    this.mob_data[mob.id] = mob
                })
                zone.spawn_points.forEach((spawn_point) => {
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