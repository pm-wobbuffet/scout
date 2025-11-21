<template>
    <div class="scout-report-options">
        <ShareScoutDialog v-if="scout" />
        <ScoutReportButton v-else :disabled="disableSubmit" @click.prevent="submitScout"
            :style="{ '--bg-gradient-start': 'rgba(0, 0, 255)' }"
            title="Save the scout report to the database and share with others if desired">
            {{ disableSubmit ? "Sharing..." : "Share" }}
        </ScoutReportButton>
        <ImportPointsDialog v-if="editmode && ((!scout) || scout?.finalized_at === null)" />
        <Dialog v-model:open="showFinalizeDialog" v-if="editmode && scout && scout?.finalized_at === null">
            <DialogTrigger as-child>
                <ScoutReportButton :style="{ '--bg-gradient-start': 'var(--color-red-400)' }"
                    title="Finalize this scouting report and prevent further edits">
                    Finalize
                </ScoutReportButton>
            </DialogTrigger>
            <DialogContent>
                <form class="space-y-6" @submit.prevent="finalizeReport()">
                    <DialogHeader class="space-y-3">
                        <DialogTitle>Are you sure you want to finalize this report?</DialogTitle>
                        <DialogDescription>
                            Once the report is finalized, no further changes may be made to the report.
                            If you need to make changes after that point, you must <b>Duplicate</b> the report
                            into a new one.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary"> Cancel </Button>
                        </DialogClose>

                        <Button variant="destructive">
                            <button type="submit">Finalize</button>
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
        <ScoutDetailsDialog />
        <TomestoneDialog />
        <ScoutHistoryDialog v-if="scout" />
    </div>
</template>

<script setup>
import ImportPointsDialog from '@/components/dialogs/ImportPointsDialog.vue';
import ScoutDetailsDialog from '@/components/dialogs/ScoutDetailsDialog.vue';
import ShareScoutDialog from '@/components/dialogs/ShareScoutDialog.vue';
import { inject, ref } from 'vue';
import { Button } from '@/components/ui/button';
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
import TomestoneDialog from '@/components/dialogs/TomestoneDialog.vue';
import ColorIconButton from '@/components/inputs/ColorIconButton.vue';
import ScoutReportButton from '@/components/inputs/ScoutReportButton.vue';
import ScoutHistoryDialog from '@/components/dialogs/ScoutHistoryDialog.vue';

const scout = inject('scout', null)
const editmode = inject('editmode', false)
const emitter = inject('emitter')
const disableSubmit = ref(false)
const showFinalizeDialog = ref(false)

const submitScout = () => {
    disableSubmit.value = true
    emitter.emit('scout:save')
}

const finalizeReport = () => {
    emitter.emit('scout:finalize')
    showFinalizeDialog.value = false
}

</script>

<style scoped>
@reference('tailwindcss');
</style>