<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
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
                        <div class="text-2xl font-bold">99</div>
                    </div>
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                    <div class="absolute size-full flex items-center justify-center flex-col">
                        <h1 class="text-3xl">Multi-instanced Zones</h1>
                        <div class="text-2xl font-bold">1</div>
                    </div>
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                    <div class="absolute size-full flex items-center justify-center flex-col">
                        <h1 class="text-3xl">Reports in Past 24 Hours</h1>
                        <div class="text-2xl font-bold">10</div>
                    </div>
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <PlaceholderPattern />
                <div class="p-2">
                    <h1 class="text-xl font-bold">Latest Scouting Reports</h1>
                    <table class="table w-full">
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

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
