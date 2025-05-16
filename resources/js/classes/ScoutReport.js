export default class ScoutReport {

    title = 'Untitled Scout Report'
    custom_points = []
    point_data = []
    instance_data = {}
    scout_names = []
    dead_mobs = []
    selected_expansion_id = 6
    /** @type Scouter */
    scouter_instance = null

    constructor(initial_data, scouter_instance) {

        this.scouter_instance = scouter_instance

        if('instance_data' in initial_data) {
            initial_data.instance_data.forEach((el) => {
                this.instance_data[el.zone_id] = el.instance_count
            })
        } else {
            this.instance_data = this.constructDefaultInstanceData()
        }
        this.point_data = initial_data.points ?? []
        this.dead_mobs = initial_data.dead_mobs ?? []
        this.title = initial_data.title
    }


    /**
     * Return a key-value pair of a zone id with its current defualt_instances count from the DB
     * @returns Object
     */
    constructDefaultInstanceData() {
        let retVal = {}
        for (let [key, value] of Object.entries(this.scouter_instance.zone_data)) {
            retVal[key] = value.default_instances
        }
        return retVal
    }

    /**
     * Get the instance count for a specific zone, if an override was specified
     * @param {Number} zone_id 
     * @returns 
     */
    getInstanceCountForZone(zone_id) {
        if(zone_id in this.instance_data) {
            return this.instance_data[zone_id]
        }
        return 1
    }

    getMobOnPoint(point_id, instance) {
        return this.point_data.filter((mobpoint) => {
            return mobpoint.point_id == point_id && mobpoint.instance_number == instance
        })
    }

    isMobDead(mob_id, instance_number) {
        return this.dead_mobs.some((el) => {
            return el.mob_id == mob_id && el.instance_number == instance_number
        })
    }

    isZoneScoutingComplete(zone, instance_number) {
        // Get total mobs already found
        let mobCount = this.getFoundMobCountForZone(zone.id, instance_number)
        let expectedMobs = this.scouter_instance.getMobsForZone(zone.id)

        // Look for dead mobs to add to count
        expectedMobs.forEach((mob) => {
            if(this.isMobDead(mob.id, instance_number)) {
                mobCount++
            }
        })
        
        return mobCount === expectedMobs.length
    }

    getFoundMobCountForExpansion(expac_id) {
        let foundCount = 0
        this.point_data.forEach((point) => {
            if(
                this.scouter_instance.spawn_points[point.point_id] &&
                this.scouter_instance.spawn_points[point.point_id].expansion_id == expac_id &&
                point.mob_id !== null
            ) {
                foundCount += 1
            }
        })
        return foundCount
    }

    getFoundMobCountForZone(zone_id, instance_number) {
        let foundCount = 0
        this.point_data.forEach((point) => {
            if(
                point.zone_id == zone_id &&
                point.instance_number == instance_number &&
                point.mob_id !== null
            ) {
                foundCount += 1
            }
        })
        return foundCount
    }

    getSelectedExpansion() {
        return this.selected_expansion_id
    }

    getZonesByExpansion() {
        return this.scouter_instance.getZonesByExpansion(this.selected_expansion_id)
    }

    setSelectedExpansion(expansion_id) {
        this.selected_expansion_id = expansion_id
    }

    getSpawnPointsForZone(zone) {
        return zone.spawn_points
    }
}