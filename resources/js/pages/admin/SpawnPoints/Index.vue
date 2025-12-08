<template>

    <Head title="Zone Spawn Points" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-2 text-2xl font-bold">
            {{ props.zone.name }}
        </div>
        <div class="flex w-full gap-4">
            <ZoneMapPointSelector :zone="props.zone" v-model:selected_point="selectedPoint"
                @update:selected_point="updateFormDetails" @dblclick="console.log('Double')" />
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
                                <div v-for="mob in props.zone.mobs" class="mt-2" :key="`mobcheck-${mob.id}`">
                                    <Label class="flex items-center space-x-3">
                                        <input type="checkbox" name="valid_mobs[]" v-model="form.valid_mobs"
                                            :value="mob.id" />
                                        <span>{{ mob.name }}</span>
                                    </Label>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Button variant="default">Submit Changes</Button>
                            <Button variant="outline" @click="cancelForm">Cancel</Button>
                        </div>
                    </form>
                    <Button variant="secondary" @click="addPointMode" v-else>Add Point</Button>
                </CardContent>
                <CardFooter>

                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import ZoneMapPointSelector from '@/components/inputs/ZoneMapPointSelector.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Zone } from '@/types/gametypes';
import { Head, useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import Label from '@/components/ui/label/Label.vue';
import CardFooter from '@/components/ui/card/CardFooter.vue';

const props = defineProps<{
    zone: Zone
}>();
const selectedPoint = ref(null)
const Toast = inject('Toast')

const form = useForm({
    id: null,
    x: null,
    y: null,
    valid_mobs: []
})

const submitForm = () => {
    if (form.id != -1) {
        form.patch(route('admin.zones.spawn_points.update', [props.zone.id, form.id]), {
            onSuccess: (page) => {
                if (page.props.flash?.message) {
                    Toast.success(page.props.flash.message)
                }
            }
        })
    } else {
        form.post(route('admin.zones.spawn_points.store', [props.zone.id]), {
            onSuccess: (page) => {
                if (page.props.flash?.message) {
                    Toast.success(page.props.flash.message)
                }
            }
        })
    }
}

const cancelForm = () => {
    form.id = null
    form.x = null
    form.y = null
    form.valid_mobs = []
    selectedPoint.value = null
}

const addPointMode = () => {
    form.id = -1
    form.x = 0
    form.y = 0
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
        form.valid_mobs = p.valid_mobs.map((el) => {
            return el.id
        })
    }
})

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Zones',
        href: '/admin/zones'
    },
    {
        title: props.zone.name,
        href: `/admin/zones`
    },
    {
        title: 'Spawn Points'
    }
];

</script>

<style scoped></style>