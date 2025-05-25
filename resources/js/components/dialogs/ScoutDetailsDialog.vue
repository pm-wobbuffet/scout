<template>
    <Dialog>
        <DialogTrigger as-child>
            <button type="button" class="bg-green-700 dark:bg-green-800">
                <ClipboardListIcon /> Details
            </button>
        </DialogTrigger>
        <DialogPortal>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Scout Report Details</DialogTitle>
                    <DialogDescription>Enter a title or scout list for this report.</DialogDescription>
                </DialogHeader>
                <div class="grid grid-cols-2 items-center gap-2 w-full" style="grid-template-columns: auto 1fr;">
                    <div>Title</div>
                    <div>
                        <Input :model-value="scoutReport.title" maxlength="100"
                            title="You can optionally enter a descriptive title for the scouting report" />
                    </div>

                    <div>Scouts</div>
                    <div>

                    </div>
                </div>
            </DialogContent>
        </DialogPortal>
    </Dialog>
</template>

<script setup>
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger
} from '@/components/ui/dialog';
import { ClipboardListIcon } from 'lucide-vue-next';
import { DialogPortal } from 'reka-ui';
import { inject } from 'vue';
import Input from '@/components/ui/input/Input.vue';

const scoutReport = inject('scoutReport')

</script>

<style lang="scss" scoped></style>