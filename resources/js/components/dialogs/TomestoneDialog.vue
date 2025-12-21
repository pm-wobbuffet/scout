<template>
    <Dialog>
        <DialogTrigger as-child>
            <ScoutReportButton title="Show the currency calculator for this train"
                :style="{ '--bg-gradient-start': 'var(--color-purple-500)' }">
                Tomes
            </ScoutReportButton>
        </DialogTrigger>
        <DialogContent class="min-w-[80%]">
            <DialogHeader class="space-y-3">
                <DialogTitle>Currency Calculator</DialogTitle>
                <DialogDescription>
                    Enter the number of marks for each expansion and click the checkbox beside its name to
                    include it in the calculations
                </DialogDescription>
            </DialogHeader>
            <div class="grid md:grid-cols-[max-content_1fr] gap-2">
                <form @submit.prevent="() => { return false; }" class="w-auto pr-8">
                    <div v-for="expansion in scout.scouter_instance.expansion_data" :key="`expac-${expansion.id}`"
                        class="grid w-auto grid-cols-[max-content_auto_max-content] gap-2 border-b p-1 items-center">
                        <div>
                            <input type="checkbox" v-model="selectedExpansions" :value="expansion.id"
                                :id="`frmChk-${expansion.id}`" />
                        </div>
                        <div>
                            <label :for="`frmChk-${expansion.id}`">{{ expansion.name }}</label>
                        </div>
                        <div>
                            <Input type="number" class="max-w-[80px]" :model-value="mobCounts[expansion.id]" />
                        </div>
                    </div>
                    <div>
                        <h2 class="font-bold">Macro Generation Mode</h2>
                        <label class="mr-2"><input type="radio" name="generationMode" v-model="generationMode"
                                value="total_amount" /> Total Amount Generated</label>
                        <label><input type="radio" name="generationMode" v-model="generationMode" value="from_cap" />
                            Highest Amount Before Cap</label>
                    </div>
                    <div class="max-w-[400px] text-sm">
                        <b>Macro String</b>
                        <br />
                        Use the placeholder $c in your text and the list of currencies will be inserted at that
                        position.
                        <textarea rows="3" id="txtTotalAmountPlaceholder" v-if="generationMode == 'total_amount'"
                            v-model="userMacroString"></textarea>
                        <textarea rows="3" id="txtFromCapPlaceholder" v-if="generationMode == 'from_cap'"
                            v-model="userMacroString"></textarea>

                    </div>
                </form>
                <div class="ml-2">
                    <fieldset class="relative">
                        <legend>Shout Macro</legend>
                        <UseClipboard v-slot="{ copy, copied }" :source="() => generateShoutString()">
                            <textarea name="" id="" rows="3" v-text="generateShoutString()"></textarea>
                            <button type="button" @click="copy()"
                                class="absolute -bottom-2 right-0 bg-slate-800 text-white border p-1">{{ copied ?
                                    'Copied!'
                                    : 'Copy' }}</button>
                        </UseClipboard>
                    </fieldset>
                    <fieldset class="relative">
                        <legend>Party Macro</legend>
                        <UseClipboard v-slot="{ copy, copied }" :source="() => generatePartyString()">
                            <textarea name="" id="" rows="3" v-text="generatePartyString()"></textarea>
                            <button type="button" @click="copy()"
                                class="absolute -bottom-2 right-0 bg-slate-800 text-white border p-1">{{ copied ?
                                    'Copied!'
                                    : 'Copy' }}</button>
                        </UseClipboard>
                    </fieldset>
                    <fieldset class="relative">
                        <legend>Discord Text</legend>
                        <UseClipboard v-slot="{ copy, copied }" :source="() => generateDiscordString()">
                            <textarea name="" id="" rows="3" v-text="generateDiscordString()"></textarea>
                            <button type="button" @click="copy()"
                                class="absolute -bottom-2 right-0 bg-slate-800 text-white border p-1">{{ copied ?
                                    'Copied!'
                                    : 'Copy' }}</button>
                        </UseClipboard>
                    </fieldset>
                </div>
            </div>
            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="secondary"> Close </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import ScoutReportButton from '@/components/inputs/ScoutReportButton.vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import Button from '@/components/ui/button/Button.vue';
import type { Ref } from 'vue';
import { computed, inject, onMounted, ref, watch } from 'vue';
import { getCurrencyEmoteMap, getCurrencyInfo, getRewardsForExpansion, getSortKey } from '@/classes/currency';
import { UseClipboard } from '@vueuse/components';
import { useUserSettings } from '@/composables/useUserSettings';


const scout = inject('scoutReport')
const selectedExpansions = ref([])
const mobCounts = ref({})
// The currency display mode. total_amount = amount generated during the train
// from_cap = lowest amount you can have on hand without hitting cap during the train
const generationMode: Ref<'from_cap' | 'total_amount'> = ref('from_cap')
// the user's overriden macro text string
//const userMacroString = ref('')
const { settings, updateSetting } = useUserSettings()

const userMacroString = computed({

    get() {
        if (generationMode.value == 'from_cap') {
            return settings.value['tomestone_fromcap_string']
        }
        if (generationMode.value == 'total_amount') {
            return settings.value['tomestone_totalamount_string']
        }
        return "This train will generate $c."
    },

    set(v) {
        if (generationMode.value == 'from_cap') {
            updateSetting('tomestone_fromcap_string', v)
        }
        if (generationMode.value == 'total_amount') {
            updateSetting('tomestone_totalamount_string', v)
        }
    }


})

const generateShoutString = () => {
    if (selectedExpansions.value.length < 1) return ""
    return `/sh ${calculateFinalText.value}`
}

const generatePartyString = () => {
    if (selectedExpansions.value.length < 1) return ""
    return `/p ${calculateFinalText.value}`
}

const generateDiscordString = () => {
    if (selectedExpansions.value.length < 1) return ""
    let s = calculateFinalText.value
    const emotes = getCurrencyEmoteMap()
    for (const replacement in emotes) {
        s = s.replace(replacement, emotes[replacement])
    }
    return s
}

const calculateTotals = () => {
    const total_rewards = {}
    selectedExpansions.value.forEach((expac_id) => {
        getRewardsForExpansion(expac_id, mobCounts.value[expac_id]).forEach((r) => {
            if (total_rewards[r.currency]) {
                total_rewards[r.currency] += r.amount
            } else {
                total_rewards[r.currency] = r.amount
            }
        })
    })
    const sorted_rewards = []
    Object.keys(total_rewards).sort((a, b) => {
        return getSortKey(a) - getSortKey(b)
    }).forEach((currency_id) => {
        sorted_rewards.push({ "currency": currency_id, "total": total_rewards[currency_id] })
    })

    return sorted_rewards
}

const calculateFinalText = computed(() => {
    const macroString = getMacroString()
    return macroString.replace('$c', generateCurrencyString())
})

const generateCurrencyString = () => {
    let c = ""
    if (generationMode.value == 'from_cap') {
        // Need to calculate total rewards and subtract from max caps
        c = calculateTotals().map((curr_reward) => {
            const c = getCurrencyInfo(curr_reward.currency)
            let max_start = c.max_stack - curr_reward.total
            max_start = max_start < 0 ? 0 : max_start
            return `${max_start} ${c.name}`
        }).join(", ")
    } else {
        // Only need to sum the total rewards
        c = calculateTotals().map((curr_reward) => {
            const c = getCurrencyInfo(curr_reward.currency)
            return `${curr_reward.total} ${c.name}`
        }).join(', ')
    }
    if (c.lastIndexOf(",") > 0) {
        const lastCommaPos = c.lastIndexOf(",")
        c = c.substring(0, lastCommaPos) + ", and" + c.substring(lastCommaPos + 1)
    }
    return c
}

const getMacroString = () => {
    // If the user overrides the macro text with their own, just return that variant
    // TODO: consider maybe a separate saved macro depending on the mode
    if (userMacroString.value != "") {
        return userMacroString.value
    }
    if (generationMode.value == 'from_cap') {
        return settings['tomestone_fromcap_string'] ?? "Tome Check! Make sure to have less than $c to prevent overcapping!"
    }
    return "This train will generate $c."
}

const updateMobCounts = () => {
    //console.log(mobCounts.value, selectedExpansions.value)
    mobCounts.value = {}
    scout.value.scouter_instance.expansion_data.forEach((expac) => {
        if (scout.value.getFoundMobCountForExpansion(expac.id) > 0) {
            if (!selectedExpansions.value.includes(expac.id)) {
                selectedExpansions.value.push(expac.id)
            }
            mobCounts.value[expac.id] = scout.value.getFoundMobCountForExpansion(expac.id)
        } else {
            mobCounts.value[expac.id] = scout.value.getTotalMobCountForExpansion(expac)
        }
    })
}

onMounted(() => {
    selectedExpansions.value = []
    updateMobCounts()
})

watch(() => scout.value.point_data, () => updateMobCounts(), { deep: true })
watch(() => scout.value.instance_data, () => updateMobCounts(), { deep: true })

</script>

<style scoped>
@reference "../../../css/app.css";

fieldset {
    @apply mb-4;
}

legend {
    @apply border border-slate-900 border-b-0 px-2 rounded-t-md bg-slate-400 dark:bg-slate-800 font-bold;
}

textarea {
    @apply border border-slate-900 w-full bg-slate-200 dark:bg-slate-700 text-sm p-2;
}
</style>