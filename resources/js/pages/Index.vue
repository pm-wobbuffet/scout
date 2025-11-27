<template>

    <Head title="New Scout"></Head>
    <ScoutLayout>
        <ScoutContainer :scout-report="scout_report" :editmode="true" :defaultId="props.defaultId"></ScoutContainer>
        <Dialog :open="showingSavedDataDialogue">
            <DialogContent @escape-key-down="discardSaved">
                <DialogHeader>
                    <DialogTitle>Load Unsaved Data?</DialogTitle>
                    <DialogDescription>
                        You have previously unsaved data from a scout report saved. Would you like
                        to keep this data or start a fresh report?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" @click="loadData"> Keep </Button>
                    </DialogClose>

                    <Button variant="destructive">
                        <button type="submit" @click="discardSaved">Discard</button>
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ScoutLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import ScoutLayout from '@/layouts/ScoutLayout.vue';
import Scouter from '@/classes/Scouter';
import { onBeforeMount, onMounted, ref, watch, provide, inject, onBeforeUnmount } from 'vue';
import ScoutReport from '@/classes/ScoutReport';
import ScoutContainer from '@/components/ScoutContainer.vue';
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
import { Button } from '@/components/ui/button';

const props = defineProps({
    expac: Array,
    defaultId: Number,
})

// let scouter = null
const scouter = new Scouter(props.expac)
const scout_report = ref(null)
scout_report.value = new ScoutReport({}, scouter)
const editmode = ref(true)
const emitter = inject('emitter')
const showingSavedDataDialogue = ref(false)

provide('scoutReport', scout_report)
provide('editmode', editmode)

const form = useForm({})

const closeModal = () => {
    showingSavedDataDialogue.value = false
}

const loadData = () => {
    scout_report.value.unserialize(localStorage.getItem('scout-in-progress'))
    localStorage.removeItem('scout-in-progress')
    closeModal()
}

const discardSaved = () => {
    localStorage.removeItem('scout-in-progress')
    scout_report.value.reset()
    closeModal()
}

onBeforeMount(() => {
    // scouter = new Scouter(props.expac)
    // scout_report.value = new ScoutReport({}, scouter)

})
onMounted(() => {
    if (window && window.localStorage) {
        if (localStorage.getItem('scout-in-progress') != null
            && !scout_report.value.isEmpty(localStorage.getItem('scout-in-progress'))) {
            showingSavedDataDialogue.value = true
        }
    }
    watch(() => scout_report, () => {
        if (window && window.localStorage && !scout_report.value.isEmpty(scout_report.value.serialize())) {
            localStorage.setItem('scout-in-progress', scout_report.value.serialize())
        }
    }, { deep: true })
    emitter.on('scout:save', () => {
        form
            .transform((data) => ({
                ...data,
                title: scout_report.value?.title,
                points: scout_report.value?.point_data,
                instance_data: scout_report.value?.instance_data,
                scouts: scout_report.value?.scouts,
                dead_mobs: scout_report.value?.dead_mobs,
                custom_points: scout_report.value?.custom_points,
            }))
            .post(route('scout.store'), {
                preserveState: false
            })
    })
})
onBeforeUnmount(() => {
    emitter.off('scout:save')
})

</script>

<style lang="scss" scoped></style>