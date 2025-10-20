<template>

    <Head title="Zone Listing" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-2 w-full">
            <form @submit.prevent="submit()">
                <table class="m-auto">
                    <thead class="sticky top-0">
                        <tr class="bg-gray-300 dark:bg-gray-800">
                            <th class="p-1">Zone</th>
                            <th class="p-1">Instance Count</th>
                            <th class="p-1">Options</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <tr v-for="zone in props.zones" :key="`zone-${zone.id}`" class="w-full border-b">
                            <td class="text-left">
                                <Label :for="`instance-count-${zone.id}`">{{ zone.name }}</Label>
                            </td>
                            <td>
                                <Input :id="`instance-count-${zone.id}`" v-model="form.instance_counts[zone.id]"
                                    :default-value="zone.default_instances" type="number" min="1" @click="this.select()"
                                    class="max-w-[100px]" />
                                <InputError class="mt-2" :message="form.errors.instance_counts"></InputError>
                            </td>
                            <td class="p-1">
                                <Link :href="route('admin.zones.edit', { 'zone': zone })">Edit</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <Button variant="default" type="submit" class="mt-2 block mx-auto" :disabled="form.processing">
                    {{ form.processing ? 'Updating' : 'Update' }}
                    Instance
                    Counts</Button>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
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
    }
];

const toast = useToast()

const props = defineProps({
    zones: Array,
})

const form = useForm({
    instance_counts: {}
})
// Need to populate the form with the zone data beforehand, since it's not really reactive it seems
onBeforeMount(() => {
    props.zones.forEach((zone) => {
        form.instance_counts[zone.id] = zone.default_instances
    })
})

const submit = () => {
    form
        .patch(route('admin.zones.instances'), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Instance Counts Updated")
            },
            onError: () => {
                toast.error("Error updating instance Counts")
            }
        })
}
</script>

<style lang="scss" scoped></style>