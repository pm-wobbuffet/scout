<template>

    <Head title="Edit Zone Information" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-2 w-full">
            <form @submit.prevent="submit()">

            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import InputError from '@/components/InputError.vue';
import Label from '@/components/ui/label/Label.vue';
import { useToast } from 'vue-toastification';
import { onBeforeMount } from 'vue';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Zones',
        href: '/admin/zones'
    },
    {
        title: `Editing Zone Info: ${zone.name}`
    }
];

const toast = useToast()

const props = defineProps({
    zone: Object,
})

</script>

<style lang="scss" scoped></style>