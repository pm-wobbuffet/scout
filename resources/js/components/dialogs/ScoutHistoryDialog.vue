<template>
    <Dialog>
        <DialogTrigger as-child>
            <ScoutReportButton title="Show the history of this scouting report"
                :style="{ '--bg-gradient-start': 'var(--color-yellow-300)' }" @click="loadHistory()">
                History
            </ScoutReportButton>
        </DialogTrigger>
        <DialogContent class="min-w-[80%] max-h-[80%] overflow-auto">
            <DialogHeader>
                <DialogTitle>Scouting Report History</DialogTitle>
                <DialogDescription>This is the history of this scouting report. When changes are made, a new version of
                    the report is created. You may revert to a previous version of the report by clicking the
                    <b>Revert</b> button on the row. Please note that, to save space, there is a 30 sec cooldown between
                    versions being saved.
                </DialogDescription>
            </DialogHeader>

            <div class="">
                <div v-if="form.errors" class="text-red-400">
                    {{ form.errors.version_number }}
                </div>
                <Deferred data="versions">
                    <template #fallback>
                        <div>Loading...</div>
                    </template>

                    <table class="mx-auto text-sm">
                        <thead>
                            <tr>
                                <th>Version #</th>
                                <th>Created Time</th>
                                <th>Details</th>
                                <th>User</th>
                                <th>Revert</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(version, idx) in page.props.versions.data"
                                :key="`versions-row-${version.version}`">
                                <td>{{ version.version }}</td>
                                <td>{{ formatVersionDate(version.created_at) }}</td>
                                <td>{{ version.update_details.name ?? "" }}</td>
                                <td>{{ version.user ?? "" }}</td>
                                <td>
                                    <Button variant="default" class="px-1 py-1 h-auto" v-if="idx > 0"
                                        @click="setVersion(version.version)">Revert</Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="page.props.versions.meta && page.props.versions.meta.links.length > 3"
                        class="flex mx-auto justify-center gap-1 mt-2">
                        <template v-for="(link, itemkey) in page.props.versions.meta.links">
                            <div v-if="link.url === null" :key="itemkey"
                                class="mb-1 mr-1 px-4 py-3 text-gray-400 text-sm leading-4 border rounded"
                                v-html="link.label" />
                            <Button v-else @click="setHistoryPage(link.url)" :key="`buttonfor-${itemkey}`"><span
                                    v-html="link.label"></span></Button>
                        </template>
                    </div>
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
import { Deferred, router, useForm, usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { inject, onBeforeUnmount, onMounted, ref } from 'vue';
import { useUserSettings } from '@/composables/useUserSettings';
// import { useToast } from 'vue-toastification';

dayjs.extend(relativeTime);
const loaded_at = ref(dayjs())
const page = usePage()
const scout = inject('scout')
const toast = inject('Toast')
let timerId = null

const { settings } = useUserSettings()

const form = useForm({
    version_number: null,
    update_user: settings.value.displayName,
})

const formatVersionDate = (dateStr) => {
    const yesterday = dayjs().subtract(1, 'day')
    const d = dayjs(dateStr)
    if (d < yesterday) {
        return d.format('D MMM h:mm A')
    }
    return d.from(loaded_at.value)
}

const setVersion = (version_number) => {
    form.version_number = version_number
    form.post(route('scout.revert', { scout: scout, password: scout.collaborator_password }), {
        onFinish: () => {
            toast.success("Scouting report successfully reverted. Refreshing.")
            router.visit(route('scout.view', { scout: scout, password: scout.collaborator_password }))
        }
    })
}

const setHistoryPage = (url) => {
    router.visit(url, {
        preserveState: true,
        only: ['versions']
    })
}

const loadHistory = () => {
    router.reload({ only: ['versions'] })
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

    td {
        @apply p-1 px-2 border;
    }
}
</style>