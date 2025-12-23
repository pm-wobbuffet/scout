<template>
    <div class="mt-2 border border-gray-400">
        <div class="font-bold bg-slate-300 pl-1 text-sm dark:bg-slate-700 dark:text-slate-300">Quick Paste</div>
        <div>
            <form class="text-sm">
                <textarea placeholder="Paste chat log coordinates here" class="w-full border-(--accent)"
                    @paste="pastedLine" />
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">

const pastedLine = (ev: ClipboardEvent) => {
    ev.preventDefault()
    const pastedTxt = ev.clipboardData.getData('text')
    console.log(pastedTxt)
}

</script>

<style scoped></style>