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

const scoutReport = inject('scoutReport')
const newScoutName = ref('')
const scoutInput = useTemplateRef('scoutref')

const form = useForm({
    title: scoutReport.value.title ?? '',
    scouts: scoutReport.value.scouts ?? [],
})

const removeScout = (idx) => {
    form.scouts.splice(idx, 1)
}

const addScout = () => {
    form.scouts.push({
        'scout_name': newScoutName.value,
    })
    newScoutName.value = ''
    // if (scoutInput.value) {
    //     scoutInput.value.focus()
    // }
    document.getElementById('txtNewScout').focus()
}

</script>

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
                        <Input maxlength="100" name="title" v-model="form.title" :default-value="form.title"
                            title="You can optionally enter a descriptive title for the scouting report" />
                    </div>

                    <div>Scouts</div>
                    <div>
                        <div v-for="(scout, index) in form.scouts" :key="`scoutrow-${index}`"
                            class="grid grid-cols-[1fr_80px] gap-1 mb-1">
                            <Input v-model="form.scouts[index].scout_name" />
                            <button title="Remove this scout from the report" type="button" class="text-sm"
                                @click="removeScout(index)">Remove</button>
                        </div>
                        <div class="grid grid-cols-[1fr_80px] gap-1">
                            <Input id="txtNewScout" v-model="newScoutName" ref="scoutref"
                                @keypress.enter.prevent="addScout" />
                            <button type="button" title="Add Scout" class="text-sm" @click="addScout">Add</button>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </DialogPortal>
    </Dialog>
</template>

<style lang="scss" scoped></style>