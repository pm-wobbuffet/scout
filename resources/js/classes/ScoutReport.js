export default class ScoutReport {

    title = 'A REALLY LONG Untitled Scout Report'
    custom_points = []
    point_data = []
    instance_data = {}
    scout_names = []
    mob_status = {}
    selected_expansion_id = 6
    /** @type Scouter */
    scouter_instance = null

    constructor(initial_data, scouter_instance) {

        this.scouter_instance = scouter_instance

        if('instance_data' in initial_data) {
            this.instance_data = initial_data.instance_data
        } else {
            this.instance_data = this.constructDefaultInstanceData()
        }
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

    isZoneScoutingComplete(zone_id, instance_number) {
        return false
    }

    getFoundMobCount(zone_id, instance_number) {
        return 0
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