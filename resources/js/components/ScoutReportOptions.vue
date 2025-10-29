<template>
    <div class="scout-report-options">
        <a href="#" class="bg-blue-400 dark:bg-blue-900 text-white dark:text-slate-300">
            <ArrowUp />
            Top
        </a>
        <ShareScoutDialog v-if="scout" />
        <button class="bg-blue-700 dark:bg-blue-800 text-white dark:text-slate-300" v-else :disabled="disableSubmit"
            @click.prevent="submitScout" title="Save the scout report to the database and share with others if desired">
            <ShareIcon /> {{ disableSubmit ? "Sharing..." : "Share" }}
        </button>
        <ImportPointsDialog v-if="editmode && ((!scout) || scout?.finalized_at === null)" />
        <Dialog v-model:open="showFinalizeDialog" v-if="editmode && scout && scout?.finalized_at === null">
            <DialogTrigger as-child>
                <!-- <button class="flex rounded-md bg-red-400 dark:bg-red-800">
                    <FileLockIcon /> Finalize
                </button> -->
                <ColorIconButton iconClass="bg-violet-400 dark:bg-violet-700">
                    <template #icon>
                        <FileLockIcon />
                    </template>
                    Finalize
                </ColorIconButton>
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
    </div>
</template>

<script setup>
import ImportPointsDialog from '@/components/dialogs/ImportPointsDialog.vue';
import ScoutDetailsDialog from '@/components/dialogs/ScoutDetailsDialog.vue';
import ShareScoutDialog from '@/components/dialogs/ShareScoutDialog.vue';
import { ArrowUp, CircleDollarSign, FileLockIcon, ImportIcon, ShareIcon } from 'lucide-vue-next';
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

<style lang="scss" scoped></style>