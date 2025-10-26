import { getScouterName } from "@/classes/helpers"
import emitter from "@/mitt"

export default class ScoutReport {

    title = ''
    custom_points = []
    point_data = []
    instance_data = {}
    scouts = []
    dead_mobs = []
    selected_expansion_id = 5
    emitter = null
    /** @type Scouter */
    scouter_instance = null

    constructor(initial_data, scouter_instance) {
        this.scouter_instance = scouter_instance
        this.handleDataFields(initial_data)
        if (this.point_data.length > 0) {
            this.getDefaultSelectedExpansion()
        }
        this.emitter = emitter
    }

    handleDataFields(data) {
        if ('instance_data' in data) {
            data.instance_data.forEach((el) => {
                this.instance_data[el.zone_id] = el.instance_count
            })
        } else {
            this.instance_data = this.constructDefaultInstanceData()
        }
        this.point_data = data.points ?? []
        this.dead_mobs = data.dead_mobs ?? []
        this.custom_points = data.custom_points ?? []
        this.title = data.title ?? ''
        this.scouts = data.scouts ?? []
    }

    processAJAXUpdate(data) {
        this.handleDataFields(data)
    }


    serialize() {
        return JSON.stringify({
            title: this.title,
            custom_points: this.custom_points,
            point_data: this.point_data,
            instance_data: this.instance_data,
            scouts: this.scouts,
            dead_mobs: this.dead_mobs
        })
    }

    unserialize(data) {
        const deets = JSON.parse(data)
        this.point_data = deets.point_data ?? []
        this.instance_data = deets.instance_data ?? this.constructDefaultInstanceData()
        this.dead_mobs = deets.dead_mobs ?? []
        this.scouts = deets.scouts ?? []
        this.title = deets.title ?? ''
        this.custom_points = deets.custom_points ?? []
    }

    // Clear a scout report to default status
    reset() {
        this.unserialize("{}")
    }
    // Used to determine if a scout report data string has actual data

    isEmpty(data) {
        const t = JSON.parse(data)
        if (
            (t.point_data && t.point_data.length > 0)
            || (t.dead_mobs && t.dead_mobs.length > 0)
            || (t.scouts && t.scouts.length > 0)
            || (t.title != '' && t.title !== null && t.title !== undefined)
        ) {
            return false
        }
        return true
    }

    updatePointDataForZone(zone_id, instance_number, new_points, custom_points) {
        if (custom_points) {
            this.custom_points.splice(0, this.custom_points.length, ...custom_points)
        }
        // Remove any previous points for this zone
        this.point_data = this.point_data.filter((el) => {
            return (el.zone_id != zone_id || (el.zone_id == zone_id && (el.instance_number != instance_number)))
        })

        if (new_points && new_points.length) {
            this.point_data = [...this.point_data, ...new_points]
            // new_points.forEach((el) => {
            //     this.point_data.push(el)
            // })
        }
    }

    /**
     * Return a key-value pair of a zone id with its current defualt_instances count from the DB
     * @returns Object
     */
    constructDefaultInstanceData() {
        let retVal = {}
        for (let [key, value] of Object.entries(this.scouter_instance.zone_data)) {
            if (value.default_instances > 1) {
                retVal[key] = value.default_instances
            }
        }
        return retVal
    }

    sendClearPointSignal(point, instance_number) {
        this.emitter.emit('point:clear', {
            ...point,
            instance_number: instance_number
        })
    }

    processCustomPointValues(custom_points) {
        const pt_mapping = {}
        custom_points.forEach((pt) => {
            pt_mapping[pt.internal_id] = pt.id
        })
        // Swap out negative IDs for positive IDs sent back from server
        this.custom_points.forEach((custom_pt) => {
            const curId = custom_pt.id
            if (curId < 0 && curId in pt_mapping) {
                custom_pt.id = pt_mapping[curId]
            }
        })
        this.point_data.forEach((pt) => {
            if (pt.point_type == 'custom_spawn_point' && pt.point_id in pt_mapping) {
                pt.point_id = pt_mapping[pt.point_id]
            }
        })

    }

    importMobFromClipboard(mobInfo) {
        let closestPoint = this.getClosestPoint(mobInfo.zone, mobInfo.x, mobInfo.y, 2)
        if (!closestPoint.point || closestPoint.distance >= 2) {
            // Need to decide on custom points here
            if (mobInfo.zone && mobInfo.zone.allow_custom_points) {
                // This zone allows custom points, go ahead and forge up one
                closestPoint = {
                    point: this.createCustomPoint(mobInfo.zone, mobInfo.x, mobInfo.y),
                    distance: 0,
                }
            } else {
                return {
                    status: 'failure',
                    reason: 'No closest point could be found',
                    mobInfo: mobInfo,
                }
            }
        }
        //console.log(`Getting mobs assigned for ${mobInfo.zone.id} instance ${mobInfo.instance}`)
        let mobsAssigned = this.getFoundMobsForZone(mobInfo.zone.id, mobInfo.instance)
        let mobOnPoint = this.getMobOnPoint(closestPoint.point, mobInfo.instance)
        if (mobOnPoint !== null) {
            // The point specified already has a mob on it, don't overwrite it for safety
            if (mobOnPoint.mob_id === mobInfo?.mob?.id) {
                return { status: 'success', mobInfo: mobInfo }
            }
            return {
                status: 'failure',
                reason: 'The specified point already contains an assigned mob',
                mobInfo: mobInfo,
            }
        }

        let validMobsForPoint = closestPoint.point.valid_mobs.filter((testMob) => {
            return !mobsAssigned.includes(testMob.id)
        })

        // If the text line did not include a mob id, we need to go fishing to find the mob
        if (!mobInfo.mob?.id) {
            let otherMobForZone = mobInfo.zone.mobs.find((m) => m.id == mobsAssigned[0])
            let currentMobToTest = mobInfo.zone.mobs.find((m) => m.id != mobsAssigned[0])

            if (otherMobForZone) {
                // Grab the point assignment data for the other mob in the zone
                // We'll need this info to check if it was assigned by import later
                otherMobForZone.point_assignment = this.point_data.find((el) => {
                    return el.zone_id == mobInfo.zone.id
                        && el.instance_number == mobInfo.instance
                        && el.mob_id == otherMobForZone.id
                })
            }
            if (
                validMobsForPoint.length < 1
                && otherMobForZone
                // Check to make sure the mob was assigned by import
                // so we don't overwrite a user-assigned mob
                && otherMobForZone.point_assignment?.assigned_by_import
                // make sure the other mob in the zone is still valid for here
                //&& validMobIdsForPoint.includes(otherMobForZone.id)
            ) {
                let otherPt = otherMobForZone.point_assignment
                this.removeMobFromPoint({ id: otherPt.point_id, point_type: otherPt.spawn_point_type }, mobInfo.instance)
                // Replace the old point's data with the new mob in its place
                this.point_data.push({
                    ...otherPt,
                    mob_id: currentMobToTest.id
                })
                // Recalculate valid mobs for the point
                mobsAssigned = this.getFoundMobsForZone(mobInfo.zone.id, mobInfo.instance)
                validMobsForPoint = closestPoint.point.valid_mobs.filter((testMob) => {
                    return !mobsAssigned.includes(testMob.id)
                })
            }

            if (validMobsForPoint.length < 1) {
                return {
                    status: 'failure',
                    reason: 'No more valid mobs remain to place on this point',
                    mobInfo: mobInfo,
                }
            }
            mobInfo.mob = validMobsForPoint[0]
        } else {
            if (mobsAssigned.includes(mobInfo.mob.id)) {
                // This mob was already on a point, remove it to prefer the pasted data
                this.removeMob(mobInfo.mob.id, mobInfo.instance)
            }

        }

        if (mobInfo.mob?.id && mobInfo.zone.id && mobInfo.x && mobInfo.y) {
            if (closestPoint.point) {
                const pData = {
                    point_type: closestPoint.point.point_type,
                    point_id: closestPoint.point.id,
                    instance_number: mobInfo.instance,
                    mob_id: mobInfo.mob.id,
                    zone_id: mobInfo.zone.id,
                    x: mobInfo.x,
                    y: mobInfo.y,
                    assigned_by_import: true,
                }
                this.point_data.push(pData)
                return {
                    status: 'success',
                    point_data: pData,
                }
            }
        }
        return {
            status: 'failure',
            reason: 'An unknown failure has occurred'
        }
    }

    getClosestPoint(zone, x, y, max_distance = 2) {
        const d = (point) => {
            return Math.pow(point.x - x, 2) + Math.pow(point.y - y, 2)
        }
        const trueDistance = (pointOne, pointTwo) => {
            return Math.sqrt(Math.pow(pointTwo.x - pointOne.x, 2) + Math.pow(pointTwo.y - pointOne.y, 2))
        }

        const spawnPts = this.getSpawnPointsForZone(zone)
        if (spawnPts.length > 0) {
            let closest = spawnPts.reduce((a, b) => {
                return d(a) < d(b) ? a : b
            })
            let distance = trueDistance({ x: x, y: y }, closest)
            if (distance > max_distance) {
                return {
                    'point': false,
                    'distance': false,
                }
            }
            return {
                'point': closest,
                'distance': distance,
            }
        }

        // return false to signal that the script should decide if a custom spawn point is required
        return {
            'point': false,
            'distance': false,
        }
    }

    createCustomPoint(zone, x, y) {
        // Add a slight amount of randomness to the point ID
        // since the point import dialog will likely try to 
        // create multiple points at the same instant
        const custom_point = {
            "id": Math.floor(-1 * Date.now() * (10 * Math.random())),
            "x": x,
            "y": y,
            "zone_id": zone.id,
            "scout_id": null,
            "valid_mobs": zone.mobs,
            "point_type": "custom_spawn_point",
        }
        this.custom_points.push(custom_point)
        return custom_point
    }

    cycleMobOnPoint(point, instance_number) {
        // Get the list of valid mobs for this point
        let valid_mobs = []
        if (point.valid_mobs) {
            valid_mobs = point.valid_mobs
        } else {
            // This is a custom point, by default we assume all A rank mobs
            // in a zone are valid for custom points
            valid_mobs = this.scouter_instance.getZoneById(point.zone_id).mobs
        }
        const remainingMobs = valid_mobs.filter((mob) => {
            if (this.isMobDead(mob.id, instance_number) || this.isMobAssigned(mob.id, instance_number)) return false
            return true
        })

        // Does a mob already exist on this point? 
        const curMob = this.getMobOnPoint(point, instance_number)
        if (curMob) {
            this.removeMobFromPoint(point, instance_number)
        }

        if (remainingMobs.length < 1) {
            this.sendClearPointSignal(point, instance_number)
            return
        }

        // If we're on the last mob of a particular zone, return early so we cycle back to a "blank" state
        const zoneMobs = this.scouter_instance.getMobsForZone(point.zone_id)
        if (curMob && curMob?.mob_id == zoneMobs[zoneMobs.length - 1].id) {
            // Send clear point 
            this.sendClearPointSignal(point, instance_number)
            return
        }

        this.assignMobToPoint(point, remainingMobs[0].id, point.zone_id, instance_number, point.point_type)
    }

    getDefaultSelectedExpansion() {
        this.scouter_instance.expansion_data.forEach((expac) => {
            if (this.getFoundMobCountForExpansion(expac.id) > 0) {
                this.selected_expansion_id = expac.id
            }
        })
    }

    /**
     * Get the instance count for a specific zone, if an override was specified
     * @param {Number} zone_id 
     * @returns 
     */
    getInstanceCountForZone(zone_id) {
        if (zone_id in this.instance_data) {
            return this.instance_data[zone_id]
        }
        return 1
    }

    setInstanceCountForZone(zone_id, count) {
        // Handle event for updating
        this.instance_data[zone_id] = count
        this.emitter.emit('instances:updated', {
            instance_data: this.instance_data
        })
    }

    /**
     * Return the ScoutPoint associated with a point in a specific instance
     * returns empty array if no mob was found on that point
     * Note, an occupied point will return an array with an element that has mob_id = NULL
     * @param {Object} point 
     * @param {Number} instance_number 
     * @returns Object
     */
    getMobOnPoint(point, instance_number) {
        return this.point_data.filter((mobpoint) => {
            return mobpoint.point_id == point.id && mobpoint.point_type == point.point_type && mobpoint.instance_number == instance_number
        })[0] ?? null
    }

    /**
     * 
     * @param {Number} mob_id The ID number of the mob to remove from a given scout report
     * @param {Number} instance_number The instance number to remove the mob from
     */
    removeMob(mob_id, instance_number) {
        this.point_data = this.point_data.filter((el) => {
            return !(el.mob_id == mob_id && el.instance_number == instance_number)
        })
    }

    /**
     * Remove an existing mob (if available) from the mob list
     * Works by filtering out any point matching point.id and instance
     * from the point_data[] array
     * @param {Object} point
     * @param {Number} point.id
     * @param {String} point.point_type 
     * @param {Number} instance_number 
     */
    removeMobFromPoint(point, instance_number) {
        console.log("Received mob removal request", point, instance_number)
        this.point_data = this.point_data.filter((pt) => {
            if (pt.point_type == (point.point_type ?? 'spawn_point')
                && pt.point_id == point.id
                && pt.instance_number == instance_number
            ) {
                return false
            }
            return true
        })
    }

    assignMobToPoint(point, mob_id, zone_id, instance_number, spawn_point_type, skip_emit = false) {
        this.point_data.push({
            'point_id': point.id,
            'mob_id': mob_id,
            'instance_number': instance_number,
            'zone_id': zone_id,
            'point_type': spawn_point_type,
            'reporter': getScouterName()
        })
        if (!skip_emit) {
            this.emitter.emit('point:assign-mob', {
                'point_id': point.id,
                'mob_id': mob_id,
                'instance_number': instance_number,
                'zone_id': zone_id,
                'point_type': spawn_point_type,
                'reporter': getScouterName(),
                'point': point,
            })
        }
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
            if (this.isMobDead(mob.id, instance_number)) {
                mobCount++
            }
        })

        return mobCount === expectedMobs.length
    }

    /**
     * Return the total number of mobs found for a given expansion during a scouting session.
     * This does not include mobs marked as dead.
     * Used for display in the top header and OpenGraph summaries in Discord embeds
     * TODO: Needs to handle custom points properly
     * @param {Number} expac_id The expansion ID to test
     * @returns Number
     */
    getFoundMobCountForExpansion(expac_id) {
        let foundCount = 0
        this.point_data.forEach((point) => {
            let z = this.scouter_instance.getZoneById(point.zone_id)
            if (
                // this.scouter_instance.spawn_points[point.point_id] &&
                // this.scouter_instance.spawn_points[point.point_id].expansion_id == expac_id &&
                z.expansion_id == expac_id &&
                // This line fixes an issue where if the instance count is lowered
                // on user update, phantom mobs were still counting toward the total.
                // For now I'm not removing the mobs from the point list just in case
                // the instance count change was accidental, they can recover their data
                point.instance_number <= this.getInstanceCountForZone(point.zone_id) &&
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
            if (
                point.zone_id == zone_id &&
                point.instance_number == instance_number &&
                point.mob_id !== null
            ) {
                foundCount += 1
            }
        })
        return foundCount
    }

    // Return an actual array of the found mob IDs
    getFoundMobsForZone(zone_id, instance_number) {
        return this.point_data.filter((pt) => {
            return (
                pt.zone_id == zone_id
                && pt.instance_number == instance_number
                && pt.mob_id !== null
            )
        }).map((x) => x.mob_id)
    }

    /**
     * Return all current point data for the specified zones
     * @param {Array} zoneInstanceList - list if zoneid+instance in "zoneid-instancenumber" format
     */
    getAllPointDataForZones(zoneInstanceList) {
        return this.point_data.filter((pt) => {
            return zoneInstanceList.includes(`${pt.zone_id}-${pt.instance_number}`)
        })
    }

    handleZoneOccupancyUpdate(e) {
        this.deleteAllPointDataForZone(e.zonelist)
        this.point_data = [...this.point_data, ...e.points]
        this.custom_points = e.custom_points
    }

    deleteAllPointDataForZone(zoneInstanceList) {
        this.point_data = this.point_data.filter((pt) => {
            return !zoneInstanceList.includes(`${pt.zone_id}-${pt.instance_number}`)
        })
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
        return [...zone.spawn_points, ...this.custom_points.filter((el) => el.zone_id == zone.id)]
    }

    getSpawnPointById(point_id, point_type) {
        if (point_type == 'spawn_point') {
            return this.scouter_instance.getSpawnPointById(point_id)
        }
        // Custom spawn points, we'll need to search through the entries specific to this report
        return this.custom_points.find((el) => {
            return el.id == point_id
        })
    }

    addDeadMobToList(mob_id, instance_number) {
        this.dead_mobs.push({
            'mob_id': mob_id,
            'instance_number': instance_number
        })
        this.addScout()
    }
    removeDeadMobFromList(mob_id, instance_number) {
        this.dead_mobs = this.dead_mobs.filter((mob) => {
            return !(mob.mob_id == mob_id && mob.instance_number == instance_number)
        })
        this.addScout()
    }

    addScout() {
        const name = getScouterName()
        if (!name) return
        if (!this.scouts.some((el) => {
            return el.scout_name === name
        })) {
            this.scouts.push({ 'scout_name': name })
            emitter.emit('meta:updated')
        }
    }

    toggleMobStatus(mob_id, instance_number) {
        if (this.isMobDead(mob_id, instance_number)) {
            // remove any line that matches this from the dead_mobs array
            this.removeDeadMobFromList(mob_id, instance_number)
            emitter.emit('mob:status', { mob_id: mob_id, instance_number: instance_number, is_dead: 0 })

        } else {
            // Add a new entry for the dead mob
            // Make sure the mob isn't already assigned to a point - in this case
            // a user needs to un-assign the mob first
            if (this.isMobAssigned(mob_id, instance_number)) {
                return false
            }
            this.addDeadMobToList(mob_id, instance_number)
            emitter.emit('mob:status', { mob_id: mob_id, instance_number: instance_number, is_dead: 1 })
        }
    }

    setOccupiedStatus(point, instance, is_occupied) {
        const rowData = {
            mob_id: null,
            instance_number: instance,
            point_id: point.id,
            point_type: point.point_type ?? 'spawn_point',
            zone_id: point.zone_id,
        }
        if (is_occupied) {
            // Wanting to mark the point as occupied
            // Make sure a mob isn't on the point
            if (this.getMobOnPoint(point, instance)) return
            this.point_data.push(rowData)
            emitter.emit('occupy:status', rowData)
        } else {
            this.point_data = this.point_data.filter((mobpoint) => {
                if (
                    mobpoint.point_id == point.id &&
                    mobpoint.zone_id == point.zone_id &&
                    mobpoint.instance_number == instance &&
                    mobpoint.mob_id === null
                ) {
                    return false
                }
                return true
            })
            delete rowData.mob_id
            emitter.emit('occupy:status', rowData)
        }
    }
}