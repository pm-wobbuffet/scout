<template>
    <Dialog>
        <DialogTrigger as-child>
            <ScoutReportButton title="Show the currency calculator for this train"
                :style="{ '--bg-gradient-start': 'var(--color-orange-400)' }">
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
                        {{ selectedExpansions }}
                    </div>
                </form>
                <div class="ml-2">
                    <fieldset>
                        <legend>Shout Macro</legend>
                        <textarea name="" id="" rows="3"></textarea>
                    </fieldset>
                    <fieldset>
                        <legend>Party Macro</legend>
                        <textarea name="" id="" rows="3"></textarea>
                    </fieldset>
                    <fieldset>
                        <legend>Discord Text</legend>
                        <textarea name="" id="" rows="3"></textarea>
                    </fieldset>
                </div>
            </div>
            <div>OUTPUT
                <img :src="AlliedSealImage" title="Allied Seals" />
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup>
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
import { computed, inject, onBeforeUpdate, onMounted, ref, watch } from 'vue';
import AlliedSealImage from '../../../images/currency/allied_seal.png';

const scout = inject('scoutReport')
const selectedExpansions = ref([])
const mobCounts = ref({})

const calculateTotals = computed(() => {
    return ''
})

const updateMobCounts = () => {
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

</script>

<style scoped>
@reference "../../../css/app.css";

fieldset {
    @apply mb-4;
}

legend {
    @apply border border-slate-900 border-b-0 px-2 rounded-t-md bg-slate-400 dark:bg-slate-800;
}

textarea {
    @apply border border-slate-900 w-full bg-slate-200 dark:bg-slate-700;
}
</style>