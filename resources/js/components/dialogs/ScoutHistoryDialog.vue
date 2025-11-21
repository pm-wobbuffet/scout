<template>
    <Dialog>
        <DialogTrigger as-child>
            <ScoutReportButton title="Show the history of this scouting report"
                :style="{ '--bg-gradient-start': 'var(--color-yellow-300)' }" @click="loadHistory()">
                History
            </ScoutReportButton>
        </DialogTrigger>
        <DialogContent class="min-w-[80%]">
            <DialogHeader>
                <DialogTitle>Scouting Report History</DialogTitle>
                <DialogDescription>This is the history of this scouting report. When changes are made, a new version of
                    the report is created. You may revert to a previous version of the report by clicking the
                    <b>Revert</b> button on the row. Please note that, to save space, there is a 30 sec cooldown between
                    versions being saved.
                </DialogDescription>
            </DialogHeader>

            <div>
                <Deferred data="versions">
                    <template #fallback>
                        <div>Loading...</div>
                    </template>

                    <table class="mx-auto">
                        <thead>
                            <tr>
                                <th>Version #</th>
                                <th>Created Time</th>
                                <th>Details</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="version in page.props.versions" :key="`versions-row-${version.version}`">
                                <td>{{ version.version }}</td>
                                <td>{{ formatVersionDate(version.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </Deferred>
            </div>

            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="default">Close</Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import ScoutReportButton from '@/components/inputs/ScoutReportButton.vue';
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
import Button from '@/components/ui/button/Button.vue';
import { Deferred, usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { onBeforeUnmount, onMounted, ref } from 'vue';

dayjs.extend(relativeTime);
const loaded_at = ref(dayjs())
const page = usePage()
let timerId = null

const formatVersionDate = function (dateStr) {
    const yesterday = dayjs().subtract(1, 'day')
    const d = dayjs(dateStr)
    if (d < yesterday) {
        return d.format('D MMM h:mm A')
    }
    return d.from(loaded_at.value)
}

onMounted(() => {
    timerId = setInterval(() => {
        loaded_at.value = dayjs()
    }, 2000)
})

onBeforeUnmount(() => {
    if (timerId) clearInterval(timerId)
})

</script>

<style scoped>
@reference "../../../css/app.css";

table>tbody>tr {
    @apply even:bg-slate-100 dark:even:bg-slate-600;
}
</style>