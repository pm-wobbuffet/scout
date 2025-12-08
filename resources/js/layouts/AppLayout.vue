<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { onMounted } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    flash?: {
        message?: string,
    }
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    flash: () => { },
});

onMounted(() => {
    if (props.flash?.message) {
        const toast = inject('Toast')
        toast.success(props.flash.message)
    }
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
