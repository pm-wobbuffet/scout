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
Mousse Princess Mare Lamentorum ( 17.1  , 24.7 ) Z: 0.8
Lunatender Queen Mare Lamentorum ( 24.1  , 23.7 ) Z: 0.7
Lunatender Queen Mare Lamentorum ( 17.5  , 24.9 ) Z: 0.9
Mousse Princess Mare Lamentorum ( 24.0  , 23.4 ) Z: 0.7
Mousse Princess Mare Lamentorum ( 17.2  , 24.8 ) Z: 0.9
Lunatender Queen Mare Lamentorum ( 24.4  , 23.7 ) Z: 0.7
Hulder Labyrinthos ( 10.7  , 19.1 ) Z: 2.3
Storsie Labyrinthos ( 12.2  , 35.7 ) Z: 2.1
Sugriva Thavnair ( 17.5  , 16.6 ) Z: 0.9
Aegeiros Garlemald ( 29.0  , 20.8 ) Z: 0.5
Minerva Garlemald ( 9.8  , 11.3 ) Z: 0.5
Gurangatch Elpis ( 18.6  , 24.5 ) Z: 1.6
Petalodus Elpis ( 12.8  , 9.9 ) Z: 4.7

Nariphon Lakeland ( 35.6  , 27.0 ) Z: 0.3
Nuckelavee Lakeland ( 27.1  , 37.3 ) Z: 0.3
Huracan Kholusia ( 22.2  , 14.3 ) Z: 3.6
Li'l Murderer Kholusia ( 34.3  , 24.6 ) Z: 0.5
Sugaar Amh Araeng ( 32.4  , 33.5 ) Z: 0.4
O Poorest Pauldia Il Mheg ( 19.5  , 34.6 ) Z: 0.4
The Mudman Il Mheg ( 24.4  , 36.9 ) Z: 0.1
Grassman The Rak'tika Greatwood ( 9.4  , 19.0 ) Z: 0.3
Supay The Rak'tika Greatwood ( 12.0  , 35.8 ) Z: 0.1
Baal The Tempest ( 28.9  , 22.8 ) Z: -2.0
Rusalka The Tempest ( 37.5  , 15.9 ) Z: -1.4
`)

const importCoordinates = () => {
    //console.log(txtChatInput.value)
    failLines.value = ''
    const { fail, success } = parseLog(txtChatInput.value, scoutReport)
    if (fail && fail.length > 0) {
        fail.forEach((row) => {
            failLines.value += `${row.line}: ${row.reason}`
        })
    }
    success.forEach((mobSighting) => {
        scoutReport.value.importMobFromClipboard(mobSighting.info)
    })
}
</script>

<style lang="scss" scoped></style>