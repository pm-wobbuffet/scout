<template>

    <Head title="Zone Spawn Points" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-2">
            {{ props.zone.name }}
        </div>
        <div class="flex w-full gap-4">
            <ZoneMapPointSelector :zone="props.zone" v-model:selected_point="selectedPoint"
                @update:selected_point="updateFormDetails" />
            <Card class="rounded-xl w-full">
                <CardHeader class="px-2 pb-0 text-center">
                    <CardTitle class="text-xl">Point Details</CardTitle>
                    <CardDescription>
                        Modify details below and choose Save
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-2">
                    <form @submit.prevent="submitForm" v-if="form.id">
                        <div class="grid grid-cols-2 w-fit gap-x-4">
                            <div>Point ID</div>
                            <div><Input type="text" name="id" v-model="form.id" disabled /></div>
                            <div>X</div>
                            <div>
                                <Input type="text" name="x" v-model="form.x" />
                                <div v-if="form.errors.x" class="text-red-600">
                                    {{ form.errors.x }}
                                </div>
                            </div>
                            <div>Y</div>
                            <div><Input type="text" name="y" v-model="form.y" /></div>
                            <div>Valid Mobs</div>
                            <div>
                                <div v-for="mob in props.zone.mobs" class="mt-2">
                                    <Label class="flex items-center space-x-3">
                                        <Checkbox v-model="form.valid_mobs" :value="mob.id" />
                                        <span>{{ mob.name }}</span>
                                    </Label>
                                </div>
                            </div>
                        </div>
                        <div>
                            <Button variant="default">Submit Changes</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import ZoneMapPointSelector from '@/components/inputs/ZoneMapPointSelector.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Zone } from '@/types/gametypes';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, inject, ref } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import Label from '@/components/ui/label/Label.vue';

const props = defineProps<{
    zone: Zone
}>();
const selectedPoint = ref(null)
const Toast = inject('Toast')

const form = useForm({
    id: null,
    x: null,
    y: null,
    valid_mobs: null
})

const submitForm = (ev) => {
    form.patch(route('admin.zones.spawn_points.update', [props.zone.id, form.id]), {
        onSuccess: (page) => {
            if (page.props.flash?.message) {
                Toast.success(page.props.flash.message)
            }
        }
    })
}

const updateFormDetails = ((newValue) => {
    form.id = null
    form.x = null
    form.y = null
    const p = props.zone.spawn_points.find((pt) => {
        return pt.id === newValue
    })
    if (p) {
        form.id = p.id
        form.x = p.x
        form.y = p.y
    }
})

// const sPoint = computed(() => {

//     if (selectedPoint.value !== null) {
//         return props.zone.spawn_points.find((pt) => {
//             return pt.id === selectedPoint.value
//         })
//     }

//     return null
// })

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

</script>

<style scoped></style>