<template>
    <div>
        <div v-if="connectionStatus === 'connected'">
            <div class="inline-block rounded-full bg-green-300 w-[10px] h-[10px] mr-1"></div>
            Connected
        </div>
        <div v-else-if="connectionStatus === 'disconnected'"
            title="Websocket connection disconnected. Collab functions will be slower">
            <div class="inline-block rounded-full bg-red-300 w-[10px] h-[10px] mr-1"></div>
            Degraded
        </div>
        <div v-else-if="connectionStatus === 'connecting'">
            <div class="inline-block rounded-full bg-yellow-300 w-[10px] h-[10px] mr-1"></div>
            Connecting
        </div>
        <div v-else-if="connectionStatus === 'initializing'">
            <div class="inline-block rounded-full bg-yellow-300 w-[10px] h-[10px] mr-1"></div>
            Initializing
        </div>
        <div v-else-if="connectionStatus === 'unavailable'"
            title="Websocket connection unavailable. Collab functions will be slower">
            <div class="inline-block rounded-full bg-red-600 w-[10px] h-[10px] mr-1"></div>
            Unavailable
        </div>
        <div v-else>
            {{ connectionStatus }}
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    connectionStatus: String
})
</script>

<style lang="scss" scoped></style>