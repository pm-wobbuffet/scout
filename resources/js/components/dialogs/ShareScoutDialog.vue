<template>
    <Dialog v-on:update:open="preOpen" :default-open="newly_created">
        <DialogTrigger as-child>
            <button type="button" class="bg-blue-700 dark:bg-blue-800 text-white dark:text-slate-300"
                title="Share the scout report with others">
                <ShareIcon /> Share
            </button>
        </DialogTrigger>
        <DialogPortal>
            <DialogContent class="min-w-[50vw]">
                <DialogHeader class="space-y-3">
                    <DialogTitle>Share Scouting Report</DialogTitle>
                    <DialogDescription class="sr-only">Sharing Options for this scouting report</DialogDescription>
                    <h1 class="font-bold text-xl mb-1">Share View-Only Map</h1>
                    <p class="text-sm">This link provides a view only copy of the map. Users cannot submit changes
                        to
                        the map.</p>
                    <UseClipboard v-slot="{ copy, copied }" legacy="true"
                        :source="`${route('scout.view', { scout: scout.slug })}?${cacheBusterAppend}`">
                        <button type="button"
                            class="block w-full text-left bg-(--secondary) text-(--secondary-foreground) p-4 mb-4 relative cursor-pointer"
                            @click="copy()">
                            <span>{{ route('scout.view', { scout: scout.slug }) }}?{{ cacheBusterAppend }}</span>
                            <div class="absolute bottom-0 right-0.5 flex gap-1">
                                <div v-if="copied">Copied to clipboard!</div>
                                <CopyIcon />
                            </div>
                        </button>
                    </UseClipboard>
                    <template
                        v-if="scout.collaborator_password && scout.collaborator_password != null && !scout?.finalized_at">
                        <h1 class="font-bold text-xl mb-1">Share Editable Map</h1>
                        <p class="text-sm">This link will allow users to edit/add points to the map, so only give it
                            to
                            trusted users. <br>
                            <b>Important:</b> This is still a work in progress! It seems to work very well, but for
                            safety
                            you may wish to limit to one person working on a zone at a time.
                        </p>
                        <UseClipboard v-slot="{ copy, copied }" legacy="true"
                            :source="`${route('scout.view', { scout: scout.slug, password: scout.collaborator_password })}?${cacheBusterAppend}`">
                            <button type="button"
                                class="block w-full text-left bg-(--secondary) text-(--secondary-foreground) p-4 mb-4 relative cursor-pointer"
                                @click="copy()">
                                <span>{{ route('scout.view', {
                                    scout: scout.slug, password: scout.collaborator_password
                                })
                                    }}?{{ cacheBusterAppend }}</span>
                                <div class="absolute bottom-0 right-0.5 flex gap-1">
                                    <div v-if="copied">Copied to clipboard!</div>
                                    <CopyIcon />
                                </div>
                            </button>
                        </UseClipboard>
                    </template>
                </DialogHeader>
            </DialogContent>
        </DialogPortal>
    </Dialog>
</template>

<script setup>
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger
} from '@/components/ui/dialog';
import { CopyIcon, ShareIcon } from 'lucide-vue-next';
import { inject, onMounted, ref } from 'vue';
import { UseClipboard } from '@vueuse/components';
import { DialogPortal } from 'reka-ui';

const scout = inject('scout')
const cacheBusterAppend = ref(0)
const newly_created = inject('newly_created', false)

const preOpen = (isOpen) => {
    // the update:open event fires for both open AND close events
    // check to make sure it's true (indicating open) before incrementing cache buster
    if (isOpen) cacheBusterAppend.value += 1
}

onMounted(() => {
    if (newly_created === true) {
        cacheBusterAppend.value += 1
    }
})
</script>

<style lang="scss" scoped></style>