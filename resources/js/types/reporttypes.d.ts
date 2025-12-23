export interface ScoutReportData {
    id?: number,
    slug: string,
    title?: string,
    collaborator_password?: string,
    dead_mobs?: Array,
    instance_data?: Array,
    points?: ScoutPoint[],
    custom_points?: ScoutCustomPoint[],
    scouts?: Array,
    finalized_at?: Date,
}
export type ScoutReport = {
    id?: number,
    slug: string,
    collaborator_password?: string,
    created_at: Date,
    updated_at: Date,
    finalized_at?: Date,
    title: string,
    version: number
}

export type ScoutCustomPoint = {
    id: number,
    scout_id?: number,
    zone_id: number,
    x: number,
    y: number,
    created_at?: Date,
    updated_at?: Date,
    internal_id?: number
}

export type Scouter = {
    id?: number,
    scout_id?: number,
    scout_name: string
}

export type ScoutPoint = {
    id?: number,
    scout_id?: number,
    zone_id: number,
    point_type: 'spawn_point' | 'custom_spawn_point',
    point_id: number,
    instance_number: number,
    mob_id?: number,
    x?: number,
    y?: number,
    reporter?: string,
    created_at?: Date,
    updated_at?: Date,
}

export type DeadMob = {
    id?: number,
    scout_id?: number,
    mob_id: number,
    instance_number: number,
    created_at?: Date,
    updated_at?: Date
}
