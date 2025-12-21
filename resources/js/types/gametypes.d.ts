export interface LocalizedNames {
    en?: string,
    ja?: string,
    fr?: string,
    de?: string,
    ko?: string,
    cn?: string
}

export type Aetheryte = {
    id: number,
    zone_id: number,
    x: number,
    y: number,
    name: string,
    icon: number,
    created_at?: Date,
    updated_at?: Date,
    names: LocalizedNames
}

export type Mob = {
    id: number,
    bNpcBase?: number,
    mob_index: number,
    name: string,
    rank: 1 | 2 | 3 | 4,
    zone_id: number,
    names: LocalizedNames
}
export type SpawnPoint = {
    id?: number,
    point_type?: 'spawn_point' | 'custom_spawn_point',
    x: number,
    y: number,
    zone_id: number,
    is_active?: boolean,
    valid_mobs?: Mob[],
    created_at: Date,
    updated_at: Date,
    deleted_at: Date,
}
export type Zone = {
    id: number,
    name: string,
    default_instances: number,
    map_id: number,
    expansion_id: number,
    created_at: Date,
    updated_at: Date,
    size_factor: number,
    max_coord_size: number,
    allow_custom_points: boolean,
    sort_priority: number,
    names: LocalizedNames
    spawn_points?: SpawnPoint[],
    mobs?: Mob[],
    aetherytes?: Aetheryte[]
}
