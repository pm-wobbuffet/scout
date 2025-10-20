<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { formatDateTime } from '@/classes/helpers';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
    },
];

const props = defineProps({
    total_scouts: Number,
    multi_zones: Number,
    last_day: Number,
    last_twenty: Array,
})
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                    <div class="absolute size-full flex items-center justify-center flex-col">
                        <h1 class="text-3xl">Scouting Reports</h1>
                        <div class="text-2xl font-bold">{{ props.total_scouts }}</div>
                    </div>
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                    <div class="absolute size-full flex items-center justify-center flex-col">
                        <h1 class="text-3xl">Multi-instanced Zones</h1>
                        <div class="text-2xl font-bold">{{ props.multi_zones }}</div>
                    </div>
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                    <div class="absolute size-full flex items-center justify-center flex-col">
                        <h1 class="text-3xl">Reports in Past 24 Hours</h1>
                        <div class="text-2xl font-bold">{{ props.last_day }}</div>
                    </div>
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <PlaceholderPattern />
                <div class="p-2">
                    <h1 class="text-xl font-bold">Latest Scouting Reports</h1>
                    <table class="table mx-auto">
                        <thead>
                            <tr>
                                <th>ID#</th>
                                <th>Title</th>
                                <th>Created At</th>
                                <th>Collab</th>
                                <th>Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="report in props.last_twenty" :key="`report-${report.id}`" class="border-b">
                                <td>{{ report.slug }}</td>
                                <td>{{ report.title }}</td>
                                <td>{{ formatDateTime(report.created_at) }}</td>
                                <td>
                                    <Link :href="route('scout.view', { scout: report.slug, password: report.password })"
                                        class="inline-block p-1 border-gray-600 border rounded-sm cursor-pointer">
                                    Collab</Link>
                                </td>
                                <td>
                                    <Link :href="route('scout.view', { scout: report.slug, password: null })"
                                        class="inline-block p-1 border-gray-600 border rounded-sm cursor-pointer">
                                    Share</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
