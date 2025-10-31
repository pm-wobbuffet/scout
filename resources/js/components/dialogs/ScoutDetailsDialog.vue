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
import { inject, ref, useTemplateRef } from 'vue';
import Input from '@/components/ui/input/Input.vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import ScoutReportButton from '@/components/inputs/ScoutReportButton.vue';

const scoutReport = inject('scoutReport')
const newScoutName = ref('')
const scoutInput = useTemplateRef('scoutref')
const emitter = inject('emitter')

const form = useForm({
    title: scoutReport.value.title ?? '',
    scouts: scoutReport.value.scouts ?? [],
})

const removeScout = (idx) => {
    scoutReport.value.scouts.splice(idx, 1)
}

const addScout = () => {
    scoutReport.value.scouts.push({
        'scout_name': newScoutName.value,
    })
    newScoutName.value = ''
    // if (scoutInput.value) {
    //     scoutInput.value.focus()
    // }
    document.getElementById('txtNewScout').focus()
}

const handleOpen = (isOpen) => {
    if (!isOpen) {
        emitter.emit('meta:updated')
    }
}

</script>

<template>
    <Dialog @update:open="handleOpen">
        <DialogTrigger as-child>
            <ScoutReportButton :style="{ '--bg-gradient-start': 'var(--color-green-700)' }"
                title="Modify details such as scouting report title or the list of scouts">
                Details
            </ScoutReportButton>
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
                        <Input maxlength="100" name="title" v-model="scoutReport.title"
                            title="You can optionally enter a descriptive title for the scouting report" />
                    </div>

                    <div class="self-start">Scouts</div>
                    <div>
                        <div v-for="(scout, index) in scoutReport.scouts" :key="`scoutrow-${index}`"
                            class="grid grid-cols-[1fr_80px] gap-1 mb-1">
                            <Input v-model="scoutReport.scouts[index].scout_name" />
                            <Button size="sm" variant="destructive" @click="removeScout(index)">Remove</Button>
                        </div>
                        <div class="grid grid-cols-[1fr_80px] gap-1">
                            <Input id="txtNewScout" v-model="newScoutName" ref="scoutref"
                                @keypress.enter.prevent="addScout" />
                            <Button size="sm" variant="default" @click="addScout">Add</Button>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </DialogPortal>
    </Dialog>
</template>

<style lang="scss" scoped></style>