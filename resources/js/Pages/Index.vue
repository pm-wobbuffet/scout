<template>
    <div class="w-full min-h-[100vh]">
        <nav class="flex flex-wrap w-full items-center justify-between bg-slate-500 text-slate-100 p-2 min-h-[3rem] main-nav">
            <div>Logo</div>
            <div class="flex expac-list">
                <div v-for="expansion in expac" :key="expansion.id" class="text-center border p-1 px-4 expac-list-item"
                :class="{'selected-expansion': expansion.id == defaultExp}">
                    <div>{{ expansion.abbreviation }}</div>
                    <div class="text-sm">
                        0 / {{ mobCount(expansion) }}
                    </div>
                    
                </div>
            </div>
            <div>Share</div>
        </nav>
        <main class="flex flex-wrap pt-2 px-2">
            <div class="order-2">
                MAPS
            </div>
            <aside class="sticky border border-gray-400 p-2 self-start order-1">
                Top | Share
            </aside>
        </main>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";

const mobCount = function(expac) {
    let mCount = 0
    expac.zones.forEach( (el) => {
        mCount += el.total_mobs
    })
    return mCount
}

const defaultExp = ref(6)

const props = defineProps({
    expac: Array,
})
</script>