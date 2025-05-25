<template>
    <Dialog v-on:update:open="preOpen">
        <DialogTrigger as-child>
            <button type="button" class="bg-blue-700 dark:bg-blue-800 text-white dark:text-slate-300"
                title="Share the scout report with others">
                <ShareIcon /> Share
            </button>
        </DialogTrigger>
        <DialogContent class="min-w-[50vw]">
            <DialogHeader class="space-y-3">
                <DialogTitle>Share Scouting Report</DialogTitle>
                <DialogDescription>
                    <h1 class="font-bold text-xl mb-1">Share View-Only Map</h1>
                    <p class="text-sm">This link provides a view only copy of the map. Users cannot submit changes to
                        the map.</p>
                    <div class="bg-(--secondary) text-(--secondary-foreground) p-4 mb-4 relative cursor-pointer">
                        <span>{{ route('scout.view', { scout: scout.slug }) }}?{{ cacheBusterAppend }}</span>
                        <div class="absolute bottom-0 right-0.5">
                            <CopyIcon />
                        </div>
                    </div>
                    <template
                        v-if="scout.collaborator_password && scout.collaborator_password != null && !scout?.finalized_at">
                        <h1 class="font-bold text-xl mb-4">Share Editable Map</h1>
                        <p class="text-sm">This link will allow users to edit/add points to the map, so only give it to
                            trustedusers. <br>
                            <b>Important:</b> This is still a work in progress! It seems to work very well, but for
                            safety
                            you may wish to limit to one person working on a zone at a time.
                        </p>
                        <div class="bg-(--secondary) text-(--secondary-foreground) p-4 mb-4 relative cursor-pointer">
                            <span>{{ route('scout.view', { scout: scout.slug, password: scout.collaborator_password })
                            }}?{{ cacheBusterAppend }}</span>
                            <div class="absolute bottom-0 right-0.5">
                                <CopyIcon />
                            </div>
                        </div>
                    </template>
                </DialogDescription>
            </DialogHeader>
        </DialogContent>
    </Dialog>
</template>

<script setup>
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
import { CopyIcon, ShareIcon } from 'lucide-vue-next';
import { inject, ref } from 'vue';

const emitter = inject('emitter')
const scout = inject('scout')
const cacheBusterAppend = ref(1)

const preOpen = (isOpen) => {
    if (isOpen) cacheBusterAppend.value += 1
}
</script>

<style lang="scss" scoped></style>