export default class ScoutReport {

    title = ''
    custom_points = []
    point_data = []
    instance_data = {}
    scout_names = []
    dead_mobs = []
    selected_expansion_id = 4   // TODO: calculate me
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

    /**
     * Return the ScoutPoint associated with a point in a specific instance
     * returns empty array if no mob was found on that point
     * Note, an occupied point will return an array with an element that has mob_id = NULL
     * @param {Number} point_id 
     * @param {Number} instance_number 
     * @returns Array
     */
    getMobOnPoint(point_id, instance_number) {
        return this.point_data.filter((mobpoint) => {
            return mobpoint.point_id == point_id && mobpoint.instance_number == instance_number
        })
    }

    /**
     * Whether a given mob in a particular instance is flagged as being assigned to a point anywhere
     * in the zone
     * @param {Number} mob_id - The Mob ID number of interest
     * @param {Number} instance_number - The specific instance number (1 should be used if default/no instance)
     * @returns Boolean
     */
    isMobAssigned(mob_id, instance_number) {
        return this.point_data.some((mobpoint) => {
            return mobpoint.mob_id == mob_id && mobpoint.instance_number == instance_number
        })
    }

    /**
     * Whether a given mob is marked as being sniped/dead.
     * Sniped/Dead mobs should count toward zone scouting completion.
     * @param {Number} mob_id - The Mob ID number of interest
     * @param {Number} instance_number The specific instance number (1 should be used if default/no instance)
     * @returns Boolean
     */
    isMobDead(mob_id, instance_number) {
        return this.dead_mobs.some((el) => {
            return el.mob_id == mob_id && el.instance_number == instance_number
        })
    }

    /**
     * Whether a specific zone+instance is considered as complete from a scouting standpoint.
     * A zone is complete if all mobs are accounted for, either via actual sighting or being marked
     * as sniped/dead
     * @param {Object} zone The zone ID of interest
     * @param {Number} instance_number The specific zone instance to test
     * @returns Boolean
     */
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

    /**
     * Return the total number of mobs found for a given expansion during a scouting session.
     * This does not include mobs marked as dead.
     * Used for display in the top header and OpenGraph summaries in Discord embeds
     * @param {Number} expac_id The expansion ID to test
     * @returns Number
     */
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

    /**
     * Returns the total number of mobs found in a given instanced zone.
     * Does not include mobs marked as dead or B/S rank occupied spots.
     * @param {Number} zone_id The specific zone of interest
     * @param {Number} instance_number The specific instance of a given zone to test
     * @returns Number
     */
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