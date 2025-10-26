<template>
    <Dialog v-bind:open="dialogOpen">
        <DialogTrigger as-child>
            <button class="bg-slate-500 dark:bg-yellow-800" title="Import mob coordinates by pasting in chat logs"
                @click="dialogOpen = true">
                <ImportIcon /> Import
            </button>
        </DialogTrigger>
        <DialogContent class="min-w-[700px] max-w-[100%]" @escape-key-down="dialogOpen = false"
            @pointer-down-outside="dialogOpen = false">
            <DialogHeader>
                <DialogTitle>Import Points</DialogTitle>
                <DialogDescription>Paste in chat logs below to parse for coordinates.</DialogDescription>
            </DialogHeader>

            <form>
                <textarea rows="6" class="w-full p-1 font-mono border-2 rounded-md" v-model="txtChatInput"
                    @paste="handlePaste"></textarea>
            </form>

            <div class="text-red-600 font-mono" v-if="failLines">
                <ul>
                    <li v-for="line in failLines">
                        {{ line.line }}: {{ line.reason }}
                    </li>
                </ul>
            </div>
            <div v-if="true">
                {{ linesImportedMessage }}
            </div>

            <DialogFooter class="gap-2">
                <Button variant="default" @click="importCoordinates">Import</Button>
                <DialogClose as-child>
                    <Button variant="secondary" @click="linesImportedMessage = ''; dialogOpen = false">Close</Button>
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
const dialogOpen = ref(false)
const failLines = ref([])
const linesImportedMessage = ref('')
const txtChatInput = ref(``)

const handlePaste = (event) => {
    const pastedData = (event.clipboardData || window.clipboardData).getData('text')
    importCoordinates()
}

const importCoordinates = () => {
    //console.log(txtChatInput.value)
    failLines.value = []
    let updatedZones = {}
    const { fail, success } = parseLog(txtChatInput.value, scoutReport)

    success.forEach((mobSighting) => {
        const res = scoutReport.value.importMobFromClipboard(mobSighting.info)
        if (res.status && res.status == 'failure') {
            fail.push({
                line: mobSighting.line,
                reason: res.reason,
            })
        }
        // Track the zones we updated as part of this import to send server-side
        updatedZones[`${mobSighting.info.zone.id}-${mobSighting.info.instance}`] = 1
    })

    if (fail && fail.length > 0) {
        fail.forEach((row) => {
            failLines.value.push(row)
        })
    }

    txtChatInput.value = ''
    linesImportedMessage.value = `${success.length - fail.length} lines successfully imported.`
    if (success.length - fail.length > 0) {
        emitter.emit('import:zones-updated', {
            zonelist: Object.keys(updatedZones)
        })
    }
}
</script>

<style lang="scss" scoped></style>