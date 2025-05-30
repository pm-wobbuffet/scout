<template>
    <Dialog>
        <DialogTrigger as-child>
            <button class="bg-slate-500 dark:bg-yellow-800" title="Import mob coordinates by pasting in chat logs"
                @click.prevent="showImportDialog">
                <ImportIcon /> Import
            </button>
        </DialogTrigger>
        <DialogContent class="min-w-[700px] max-w-[100%]">
            <DialogHeader>
                <DialogTitle>Import Points</DialogTitle>
                <DialogDescription>Paste in chat logs below to parse for coordinates.</DialogDescription>
            </DialogHeader>

            <form>
                <textarea rows="6" class="w-full p-1 font-mono border-2 rounded-md" v-model="txtChatInput"></textarea>
            </form>

            <div class="text-red-600" v-if="failLines != ''">
                {{ failLines }}
            </div>

            <DialogFooter class="gap-2">
                <Button variant="default" @click="importCoordinates">Import</Button>
                <DialogClose as-child>
                    <Button variant="secondary">Close</Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { parseLog } from '@/classes/text';
import Button from '@/components/ui/button/Button.vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger
} from '@/components/ui/dialog';
import { ImportIcon } from 'lucide-vue-next';
import { inject, ref } from 'vue';


const scoutReport = inject('scoutReport')
const emitter = inject('emitter')
const failLines = ref('')
const txtChatInput = ref(`
Lakeland ( 35.6  , 27.0 ) Z: 0.3
Lakeland ( 27.1  , 37.3 ) Z: 0.3
Kholusia ( 22.2  , 14.3 ) Z: 3.6
Kholusia ( 34.3  , 24.6 ) Z: 0.5
`)

const importCoordinates = () => {
    //console.log(txtChatInput.value)
    failLines.value = ''
    const { fail, success } = parseLog(txtChatInput.value, scoutReport)

    success.forEach((mobSighting) => {
        //console.log(mobSighting)
        const res = scoutReport.value.importMobFromClipboard(mobSighting.info)
        if (res.status && res.status == 'failure') {
            fail.push({
                line: mobSighting.line,
                reason: res.reason,
            })
        }
    })

    if (fail && fail.length > 0) {
        fail.forEach((row) => {
            failLines.value += `${row.line}: ${row.reason}`
        })
    }
}
</script>

<style lang="scss" scoped></style>