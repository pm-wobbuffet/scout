import AlliedSealImage from '@images/currency/allied_seal.png';
import CenturioSealImage from '@images/currency/centurio_seal.png';
import SackOfNutsImage from '@images/currency/nuts.png';
import PoeticsImage from '@images/currency/poetics.png';
import UncappedTomeImage from '@images/currency/mathematics.png';
import CappedTomeImage from '@images/currency/mnemonics.png';

const UNCAPPED_ENDGAME_TOME = 48
const CAPPED_ENDGAME_TOME = 49

const currencyList = {
    27: {
        "name": "Allied Seals",
        "max_stack": 4000,
        "emote": "<:alliedseal:711080429775749170>",
        "image": AlliedSealImage,
        "sort": 1,
        "names": {
            "en": "Allied Seal",
            "jp": "同盟記章",
            "de": "Jagdabzeichen",
            "fr": "Insigne allié",
        },
    },
    28: {
        "name": "Poetics",
        "max_stack": 2000,
        "emote": "<:poetics:1355014355590320248>",
        "image": PoeticsImage,
        "sort": 4,
        "names": {
            "en": "Poetics",
            "jp": "poétique",
            "de": "Poesie",
            "fr": "詩学",
        },
    },
    // 47: {
    //     "name": "Heliometry",
    //     "max_stack": 2000,
    //     "emote": "<:heliometry:1268127432158875691>",
    //     "image": UncappedTomeImage,
    //     "sort": 50,
    //     "names": {
    //         "en": "Heliometry",
    //         "jp": "天道",
    //         "de": "Heliometrie",
    //         "fr": "héliologique",
    //     },
    // },
    48: {
        "name": "Mathematics",
        "max_stack": 2000,
        "emote": "<:mathematics:1355013812230553712>",
        "image": UncappedTomeImage,
        "sort": 60,
        "names": {
            "en": "Mathematics",
            "jp": "数理",
            "de": "Mathematik",
            "fr": "mathématique",
        },
    },
    49: {
        "name": "Mnemonics",
        "max_stack": 2000,
        "emote": "<:mnemonics:1450577101840187503>",
        "image": CappedTomeImage,
        "sort": 60,
        "names": {
            "en": "Mnemonics",
            "jp": "記憶",
            "de": "Mnemonik",
            "fr": "mnémonique",
        },
    },
    10307: {
        "name": "Centurio Seals",
        "max_stack": 4000,
        "emote": "<:centurioseal:711080444728311898>",
        "image": CenturioSealImage,
        "sort": 2,
        "names": {
            "en": "Centurio Seals",
            "jp": "セントリオ記章",
            "de": "Centurio-Abzeichen",
            "fr": "Insigne Centurio",
        },
    },
    26533: {
        "name": "Sacks of Nuts",
        "max_stack": 4000,
        "emote": "<:sackofnuts:1117597415390846977>",
        "image": SackOfNutsImage,
        "sort": 3,
        "names": {
            "en": "Sacks of Nuts",
            "jp": "モブハントの戦利品",
            "de": "Kupo-Trophäe",
            "fr": "Insigne de chasse",
        },
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
        const r = { ...el }
        r['amount'] = el.amount * mobCount
        return r
    })
}

export const getSortKey = (currency_id) => {
    if (currency_id in currencyList) {
        return currencyList[currency_id].sort
    }
    //Return some dummy high number just to push it to the end of the sort
    return 1000000;

}

export const getCurrencyInfo = (currency_id) => {
    return currencyList[currency_id]
}

export const getCurrencyEmoteMap = () => {
    return Object.keys(currencyList).reduce((prevVal, el) => {
        const curr = currencyList[el]
        prevVal[curr.name] = curr.emote
        return prevVal
    }, {})
}
