import AlliedSealImage from '@images/currency/allied_seal.png';
import CenturioSealImage from '@images/currency/centurio_seal.png';
import SackOfNutsImage from '@images/currency/nuts.png';
import PoeticsImage from '@images/currency/poetics.png';
import UncappedTomeImage from '@images/currency/heliometry.png';
import CappedTomeImage from '@images/currency/mathematics.png';

const UNCAPPED_ENDGAME_TOME = 47
const CAPPED_ENDGAME_TOME = 48

const currencyList = {
    27: {
        "name": "Allied Seals",
        "max_stack": 4000,
        "emote": "<:alliedseal:711080429775749170>",
        "image": AlliedSealImage,
    },
    28: {
        "name": "Poetics",
        "max_stack": 2000,
        "emote": "<:poetics:1355014355590320248>",
        "image": PoeticsImage,
    },
    47: {
        "name": "Heliometry",
        "max_stack": 2000,
        "emote": "<:heliometry:1268127432158875691>",
        "image": UncappedTomeImage,
    },
    48: {
        "name": "Mathematics",
        "max_stack": 2000,
        "emote": "<:mathematics:1355013812230553712>",
        "image": CappedTomeImage,
    },
    10307: {
        "name": "Centurio Seals",
        "max_stack": 4000,
        "emote": "<:centurioseal:711080444728311898>",
        "image": CenturioSealImage,
    },
    26533: {
        "name": "Sack of Nuts",
        "max_stack": 4000,
        "emote": "<:sackofnuts:1117597415390846977>",
        "image": SackOfNutsImage,
    },
}

const expansionRewards = {
    2: [
        { currency: 27, amount: 40 },
        { currency: 10307, amount: 20 },
        { currency: 28, amount: 30 },
        { currency: UNCAPPED_ENDGAME_TOME, amount: 10 },
    ],
    3: [
        { currency: 10307, amount: 40 },
        { currency: 28, amount: 30 },
        { currency: UNCAPPED_ENDGAME_TOME, amount: 10 },
    ],
    4: [
        { currency: 10307, amount: 40 },
        { currency: 28, amount: 30 },
        { currency: UNCAPPED_ENDGAME_TOME, amount: 10 },
    ],
    5: [
        { currency: 26533, amount: 40 },
        { currency: 28, amount: 30 },
        { currency: UNCAPPED_ENDGAME_TOME, amount: 10 },
    ],
    6: [
        { currency: 26533, amount: 40 },
        { currency: 28, amount: 30 },
        { currency: UNCAPPED_ENDGAME_TOME, amount: 10 },
    ],
    7: [
        { currency: 26533, amount: 40 },
        { currency: 28, amount: 30 },
        { currency: UNCAPPED_ENDGAME_TOME, amount: 20 },
        { currency: CAPPED_ENDGAME_TOME, amount: 10 },
    ]
}

export const getRewardsForExpansion = (expansion_id, mobCount) => {
    const reward_list = expansionRewards[expansion_id]
    return reward_list.map((el) => {
        el.amount *= mobCount
        el.currency_info = currencyList[el.currency]
        return el
    })
}
