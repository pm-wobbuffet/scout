<template>

    <Head title="Edit Zone Information" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <form @submit.prevent="submit" class="flex flex-col gap-4 min-w-md max-w-2xl mx-auto mt-4">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Default Name</Label>
                    <Input id="name" type="text" required autofocus :tabindex="1" v-model="form.zone.name"
                        placeholder="Zone Name" />
                    <InputError for="form.zone.name" />
                </div>
            </div>
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Instance Count</Label>
                    <Input id="default_instances" type="number" required tabindex="2"
                        v-model="form.zone.default_instances" min="1" max="10" />
                    <InputError for="form.zone.default_instances" />
                </div>
            </div>
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Expansion</Label>
                    <select id="expansion_id" v-model="form.zone.expansion_id">
                        <option v-for="(name, id) in props.expansions" :key="`exp-option-${id}`" :value="id">
                            {{ name }}
                        </option>
                    </select>
                    <InputError for="form.zone.expansion_id" />
                </div>
            </div>
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <h1 class="text-lg font-bold">Localized Names</h1>
                    <Label for="names.en">English</Label>
                    <Input id="names.en" type="text" :tabindex="3" v-model="form.zone.names.en" />
                    <InputError for="form.zone.names.en" />
                    <Label for="names.ja">Japanese</Label>
                    <Input id="names.ja" type="text" :tabindex="4" v-model="form.zone.names.ja" />
                    <InputError for="form.zone.names.ja" />
                    <Label for="names.de">German</Label>
                    <Input id="names.de" type="text" :tabindex="5" v-model="form.zone.names.de" />
                    <InputError for="form.zone.names.de" />
                    <Label for="names.fr">French</Label>
                    <Input id="names.fr" type="text" :tabindex="6" v-model="form.zone.names.fr" />
                    <InputError for="form.zone.names.fr" />
                    <Label for="names.ch">Chinese</Label>
                    <Input id="names.ch" type="text" :tabindex="7" v-model="form.zone.names.ch" />
                    <InputError for="form.zone.names.ch" />
                    <Label for="names.ko">Korean</Label>
                    <Input id="names.ko" type="text" :tabindex="8" v-model="form.zone.names.ko" />
                    <InputError for="form.zone.names.ko" />
                </div>
            </div>
        </form>
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


const toast = useToast()

const props = defineProps({
    zone: Object,
    expansions: Object,
})

const form = useForm({
    zone: props.zone,
})
const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Zones',
        href: '/admin/zones'
    }
];
onBeforeMount(() => {
    breadcrumbs.push({
        title: `Editing Zone: ${props.zone.name}`
    })
})

</script>

<style lang="scss" scoped></style>