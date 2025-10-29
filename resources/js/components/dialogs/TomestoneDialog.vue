<template>
    <Dialog>
        <DialogTrigger as-child>
            <!-- <button
                class="flex rounded-md border border-black/50 hover:border-black/25 dark:border-slate-300/50 p-0 gap-0 overflow-hidden">
                <div class="bg-orange-400 dark:bg-orange-800">
                    <CircleDollarSign class="text-black dark:text-slate-300" />
                </div>
                <div class="text-black dark:text-slate-300">Tomes</div>
            </button> -->
            <ColorIconButton iconClass="bg-orange-400 dark:bg-orange-800">
                <template #icon>
                    <CircleDollarSign />
                </template>
                Tomes
            </ColorIconButton>
        </DialogTrigger>
        <DialogContent class="min-w-[80%]">
            <div class="grid sm:grid-cols-2">
                <div>
                    <template v-for="expansion in scout.scouter_instance.expansion_data" :key="`expac-${expansion.id}`">
                        {{ expansion.name }}
                    </template>
                </div>
                <div>
                    <fieldset>
                        <legend>Shout Macro</legend>
                        <textarea name="" id="" rows="3"></textarea>
                    </fieldset>
                    <fieldset>
                        <legend>Party Macro</legend>
                        <textarea name="" id="" rows="3"></textarea>
                    </fieldset>
                    <fieldset>
                        <legend>Discord Text</legend>
                        <textarea name="" id="" rows="3"></textarea>
                    </fieldset>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import ColorIconButton from '@/components/inputs/ColorIconButton.vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { ArrowUp, CircleDollarSign, FileLockIcon, ImportIcon, ShareIcon } from 'lucide-vue-next';
import { inject, onMounted } from 'vue';

const scout = inject('scoutReport')

</script>

<style scoped></style>