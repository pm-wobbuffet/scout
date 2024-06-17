<template>
    <div class="w-full min-h-[100vh]">
        <nav class="flex w-full items-center justify-between bg-slate-500 p-2 min-h-[3rem]">
            <div>Logo</div>
            <div class="flex">
                <div v-for="expansion in expac" :key="expansion.id" class="text-center border p-1 px-4">
                    <div>{{ expansion.abbreviation }}</div>
                    <div class="text-sm">
                        0 / 12
                    </div>
                    
                </div>
            </div>
            <div>Share</div>
        </nav>
        <main class="flex flex-wrap pt-2">
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

const props = defineProps({
    expac: Array,
})
</script>