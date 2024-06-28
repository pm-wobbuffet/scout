import os
import pysaintcoinach
import json
from pysaintcoinach import imaging
from pysaintcoinach.pack import PackCollection
from pysaintcoinach.imaging import IconHelper
from pysaintcoinach.ex.language import Language
from pathlib import Path
from typing import cast
from pprint import pprint
from dotenv import load_dotenv

load_dotenv()


# Look at doing this later. for now just manually extract 60453 from saintcoinach binary's ui command
def export_needed_icons(realm):
    pass
    # try:
    #     _SCRIPT_PATH = os.path.abspath(__path__)
    # except:
    #     _SCRIPT_PATH = os.path.abspath(os.path.dirname(__file__))

    # packs = PackCollection(os.getenv('FFXIV_DIR') + '/game/sqpack')
    # icons = [
    #     'ui/icon/060000/060453.tex', #Aetheryte
    #     ]
    # ICON_MAP = {
    #     'icon': [
    #         (60453, 'aetheryte.png'),  # Mini-map aetheryte
    #     ]
    # }

    # for subdir in ICON_MAP:
    #     if not os.path.isdir(os.path.join(_SCRIPT_PATH, 'output', 'images', subdir)):
    #         os.makedirs(os.path.join(_SCRIPT_PATH, 'output','images', subdir))
    #     for n, filename in ICON_MAP[subdir]:
    #         if not os.path.exists(os.path.join(_SCRIPT_PATH, 'output','images', subdir, filename)):
    #             icon = IconHelper.get_icon(packs, n)
    #             #logging.info('Extracting %s -> %s' % (icon, filename))
    #             icon.get_image().save(os.path.join(_SCRIPT_PATH, 'output','images', subdir, filename))
    # pass


def write_json_file(filename, contents):
    with open(filename, 'w', encoding='utf-8') as f:
        json.dump(contents, f, ensure_ascii=False, indent=4)

# Use the built-in map_coordinate_2d from map.py
# Round to the nearest tenth for display
def convert_map_coordinates_to_in_game(map, coord, offset = 0):
    return round(map.to_map_coordinate_2d(coord, offset), 1)

def get_aetherytes_for_map(map):
    markers = map_markers[map.map_marker_range]._source_sub_row
    ret = []
    for i in range(markers.sub_row_count):
        m = markers.get_sub_row(i)
        # Check for Icon 60453, which is the mini map Aetheryte icon
        if m.get_raw('Icon') == 60453:
            pn = str(m['Data{Key}'])
            #m['Data{Key}'].source_row['PlaceName'].source_row.get_raw('Name', LANGUAGES['fr'])
            ret.append({
                'type': 60453,
                'x': convert_map_coordinates_to_in_game(map, m['X'], map['Offset{X}']),
                'y': convert_map_coordinates_to_in_game(map, m['Y'], map['Offset{Y}']),
                'name': pn,
                'name_fr': m['Data{Key}'].source_row['PlaceName'].source_row.get_raw('Name', LANGUAGES['fr']),
                'name_ja': m['Data{Key}'].source_row['PlaceName'].source_row.get_raw('Name', LANGUAGES['ja']),
                'name_de': m['Data{Key}'].source_row['PlaceName'].source_row.get_raw('Name', LANGUAGES['de'])
            })
    return ret


def get_mobs_for_zone(terr):
    mobs = []

    if terr['NotoriousMonsters[3]'].key == 0:
        # 1 A Rank/ARR Zone, A rank is stored in NotoriousMonsters[1]
        #print(terr['NotoriousMonsters[1]']['BNpcName'].source_row[(2, LANGUAGES['ja'])].encode('utf-8'))
        print(str(terr['NotoriousMonsters[1]']['BNpcName'].source_row[(0, LANGUAGES['en'])]).capitalize(),)
        mobs.append({
            'id': terr['NotoriousMonsters[1]'].key,
            'name': str(terr['NotoriousMonsters[1]']['BNpcName'].source_row[(0, LANGUAGES['en'])]).capitalize(),
            'name_fr': str(terr['NotoriousMonsters[1]']['BNpcName'].source_row[(0, LANGUAGES['fr'])]).capitalize(),
            'name_ja': str(terr['NotoriousMonsters[1]']['BNpcName'].source_row.get_raw('Singular', LANGUAGES['ja'])),
            'name_de': str(terr['NotoriousMonsters[1]']['BNpcName'].source_row[(0, LANGUAGES['de'])]).capitalize(),
            'rank': terr['NotoriousMonsters[1]']['Rank'],
            'npcbase': terr['NotoriousMonsters[1]']['BNpcBase'].key,
            'mob_index': 1,
        })
    elif terr['NotoriousMonsters[5]'].key == 0:
        # 2 A Rank Zone with no SS (HW, SB), A Ranks in [2] and [3]
        mobs.append({
            'id': terr['NotoriousMonsters[2]'].key,
            'name': str(terr['NotoriousMonsters[2]']['BNpcName'].source_row[(0, LANGUAGES['en'])]).capitalize(),
            'name_fr': str(terr['NotoriousMonsters[2]']['BNpcName'].source_row[(0, LANGUAGES['fr'])]).capitalize(),
            'name_ja': str(terr['NotoriousMonsters[2]']['BNpcName'].source_row.get_raw('Singular', LANGUAGES['ja'])),
            'name_de': str(terr['NotoriousMonsters[2]']['BNpcName'].source_row[(0, LANGUAGES['de'])]).capitalize(),
            'rank': terr['NotoriousMonsters[2]']['Rank'],
            'npcbase': terr['NotoriousMonsters[2]']['BNpcBase'].key,
            'mob_index': 1,
        })
        mobs.append({
            'id': terr['NotoriousMonsters[3]'].key,
            'name': str(terr['NotoriousMonsters[3]']['BNpcName'].source_row[(0, LANGUAGES['en'])]).capitalize(),
            'name_fr': str(terr['NotoriousMonsters[3]']['BNpcName'].source_row[(0, LANGUAGES['fr'])]).capitalize(),
            'name_ja': str(terr['NotoriousMonsters[3]']['BNpcName'].source_row.get_raw('Singular', LANGUAGES['ja'])),
            'name_de': str(terr['NotoriousMonsters[3]']['BNpcName'].source_row[(0, LANGUAGES['de'])]).capitalize(),
            'rank': terr['NotoriousMonsters[3]']['Rank'],
            'npcbase': terr['NotoriousMonsters[3]']['BNpcBase'].key,
            'mob_index': 2,
        })
    elif terr['NotoriousMonsters[9]'].key != 0:
        # 2 A Rank Zone with SS (ShB, EW, Probably DT), A Ranks in [2] and [3]
        mobs.append({
            'id': terr['NotoriousMonsters[2]'].key,
            'name': str(terr['NotoriousMonsters[2]']['BNpcName'].source_row[(0, LANGUAGES['en'])]).capitalize(),
            'name_fr': str(terr['NotoriousMonsters[2]']['BNpcName'].source_row[(0, LANGUAGES['fr'])]).capitalize(),
            'name_ja': str(terr['NotoriousMonsters[2]']['BNpcName'].source_row.get_raw('Singular', LANGUAGES['ja'])),
            'name_de': str(terr['NotoriousMonsters[2]']['BNpcName'].source_row[(0, LANGUAGES['de'])]).capitalize(),
            'rank': terr['NotoriousMonsters[2]']['Rank'],
            'npcbase': terr['NotoriousMonsters[2]']['BNpcBase'].key,
            'mob_index': 1,
        })
        mobs.append({
            'id': terr['NotoriousMonsters[3]'].key,
            'name': str(terr['NotoriousMonsters[3]']['BNpcName'].source_row[(0, LANGUAGES['en'])]).capitalize(),
            'name_fr': str(terr['NotoriousMonsters[3]']['BNpcName'].source_row[(0, LANGUAGES['fr'])]).capitalize(),
            'name_ja': str(terr['NotoriousMonsters[3]']['BNpcName'].source_row.get_raw('Singular', LANGUAGES['ja'])),
            'name_de': str(terr['NotoriousMonsters[3]']['BNpcName'].source_row[(0, LANGUAGES['de'])]).capitalize(),
            'rank': terr['NotoriousMonsters[3]']['Rank'],
            'npcbase': terr['NotoriousMonsters[3]']['BNpcBase'].key,
            'mob_index': 2,
        })
    return mobs

realm = pysaintcoinach.ARealmReversed(os.getenv('FFXIV_DIR'), pysaintcoinach.ex.Language.english)

hunt_territories = filter(lambda row: int(row[42].key) != 0, realm.game_data.get_sheet('TerritoryType'))
hunt_territories = sorted(hunt_territories, key=lambda x: (x['PlaceName{Region}'].key, x.key))
map_markers = realm.game_data.get_sheet('MapMarker')

export_needed_icons(realm)

maps = []
global LANGUAGES
LANGUAGES = {
    'en' : Language.english,
    'fr' : Language.french,
    'ja' : Language.japanese,
    'de' : Language.german,
}

for zone in hunt_territories:
    # PlaceName is the name of the zone, map_marker_range is the link to MapMarkers
    # that contains things like aetherytes
    map_path = './output/images/maps/' + str(zone.map.key) + '.png'
    map_info = {
        'id': zone.key,
        'version': zone['ExVersion'].key,
        'name': zone['PlaceName'].name,
        'name_fr': zone['PlaceName'].source_row.get_raw('Name', LANGUAGES['fr']),
        'name_ja': zone['PlaceName'].source_row.get_raw('Name', LANGUAGES['ja']),
        'name_de': zone['PlaceName'].source_row.get_raw('Name', LANGUAGES['de']),
        'size_factor': zone.map.size_factor,
        'map': zone.map.key,
        'aetherytes': get_aetherytes_for_map(zone.map)
    }

    # Figure out the A Ranks for this zone

    # If this zone has hunts, the link to the NotoriousMonsterTerritory Sheet exists in the 42nd column
    hunt_ter = zone[42]
    map_info['mobs'] = get_mobs_for_zone(hunt_ter)
    maps.append(map_info)

    # If the map hasn't already been extracted, get a copy of the 1024x1024 version
    if not os.path.exists(map_path):
        with open(map_path, 'bw+') as f:
            zone.map.small_image.save(f)

write_json_file('./output/json/zones.json', maps)
