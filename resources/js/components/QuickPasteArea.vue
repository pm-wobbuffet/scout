<template>
    <div class="mt-2 border border-gray-400">
        <div class="font-bold bg-slate-300 pl-1 text-sm dark:bg-slate-700 dark:text-slate-300">Quick Paste</div>
        <div>
            <form class="text-sm" @submit.prevent="">
                <textarea placeholder="Paste chat log here" class="w-full border border-(--accent)" rows="1"
                    @paste="pastedLine" />
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { parseChatCoordinates } from '@/classes/text'
import { inject } from 'vue'


const scoutReport = inject('scoutReport')
const toast = inject('Toast')
const emitter = inject('emitter')

const pastedLine = (ev: ClipboardEvent) => {
    ev.preventDefault()
    const pastedTxt = ev.clipboardData.getData('text')

    // parseLog(pastedTxt, scoutReport)
    const results = parseChatCoordinates(pastedTxt, scoutReport, emitter)
    if (results.successful > 0 && results.failed > 0) {
        toast.warning(`${results.successful} lines imported; ${results.failed} lines failed`)
    } else if (results.successful > 0) {
        toast.success(`${results.successful} lines imported`)
    } else if (results.failed > 0) {
        toast.error('No lines were imported')
    }
}

</script>

<style scoped></style>