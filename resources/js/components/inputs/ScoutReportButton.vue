<template>
    <button
        class="flex flex-row items-center font-bold cursor-pointer hover:brightness-110 transition-all duration-250 rounded-sm overflow-hidden 
         border border-black/100 dark:border-slate-500/80 min-h-8 pl-[30%] text-black dark:text-neutral-300 dark:hover:text-neutral-200">
        <slot />
    </button>
</template>

<script setup>
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps({
    iconClass: String,
})

//const className = computed(() => cn('min-w-[10px] w-[1px]  h-full', props.iconClass));

</script>

<style scoped>
@reference "../../../css/app.css";

button:hover {
    @apply border-(--bg-gradient-start);
    --gradient-stop-pos: 20%;
}

button {
    --gradient-stop-pos: 20%;
    @apply box-content;
    background: linear-gradient(to right, var(--bg-gradient-start), var(--bg-gradient-start) var(--gradient-stop-pos), var(--color-white) var(--gradient-stop-pos), var(--color-white));

    @variant dark {
        background: linear-gradient(to right, --alpha(var(--bg-gradient-start)/75%), --alpha(var(--bg-gradient-start)/75%) var(--gradient-stop-pos), var(--color-slate-900) var(--gradient-stop-pos), var(--color-slate-900));
    }
}
</style>