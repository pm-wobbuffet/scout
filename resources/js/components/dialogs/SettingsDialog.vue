<template>
    <Dialog v-bind:open="dialogOpen">
        <DialogTrigger as-child>
            <button
                class="border rounded-sm ml-1 p-1 bg-[rgba(0,0,0,0.4)] hover:bg-[rgba(0,0,255,0.1)] cursor-pointer dark:border-slate-500 transition-colors duration-300"
                title="Manage site settings" @click="handleOpen">
                <SettingsIcon :size="18" />
            </button>
        </DialogTrigger>
        <DialogContent class="min-w-[700px] max-w-[100%]" @escape-key-down="dialogOpen = false"
            @pointer-down-outside="dialogOpen = false">
            <DialogHeader>
                <DialogTitle>Settings</DialogTitle>
                <DialogDescription>Edit the settings specified below to customize your Turtle Scout experience.
                </DialogDescription>
            </DialogHeader>

            <form id="frmSettings">
                <div class="settingRow">
                    <div class="setting">
                        <h1>Display Name</h1>
                        <span>Your name as displayed on the scout list.</span>
                    </div>
                    <div>
                        <Input name="displayName" />
                    </div>
                </div>
                <div class="settingRow">
                    <div class="setting">
                        <h1>Language</h1>
                        <span>The language of mobs/zones to display. In the future, may also apply to User Interface
                            elements.</span>
                    </div>
                    <div class="flex">
                        <ToggleGroupRoot class="m-auto inline-flex gap-1 rounded-lg" type="single">
                            <ToggleGroupItem class="toggleGroupItem" value="en">EN</ToggleGroupItem>
                            <ToggleGroupItem class="toggleGroupItem" value="de">DE</ToggleGroupItem>
                            <ToggleGroupItem class="toggleGroupItem" value="fr">FR</ToggleGroupItem>
                            <ToggleGroupItem class="toggleGroupItem" value="jp">JP</ToggleGroupItem>
                        </ToggleGroupRoot>
                    </div>
                </div>
                <div class="settingRow">
                    <div class="setting">
                        <h1>Light/Dark Mode</h1>
                        <span>Choose your preferred tone. Choose "System" to have it match your device settings.</span>
                    </div>
                    <div class="flex">
                        <AppearanceTabs class="m-auto" />
                    </div>
                </div>
                <div class="settingRow">
                    <div class="setting">
                        <h1>Mob 1 Background Color</h1>
                    </div>
                    <div>
                        <Input type="color" />
                    </div>
                </div>
                <div class="settingRow">
                    <div class="setting">
                        <h1>Mob 2 Background Color</h1>
                    </div>
                    <div>
                        <Input type="color" />
                    </div>
                </div>
                <div class="settingRow">
                    <div class="setting">
                        <h1>Show Quick Paste Bar</h1>
                        <span>Always show the quick paste bar at the bottom of the screen to quickly paste in
                            coordinates from chat.</span>
                    </div>
                    <div>

                    </div>
                </div>
            </form>

            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="secondary" @click="dialogOpen = false">Close</Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger
} from '@/components/ui/dialog';
import { ToggleGroupItem, ToggleGroupRoot } from 'reka-ui'
import Button from '@/components/ui/button/Button.vue';
//import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import { SettingsIcon } from 'lucide-vue-next';
import { ref } from 'vue';

const dialogOpen = ref(true)
const settings = ref({})

const handleOpen = () => {
    // Grab already saved settings for merge
    const userSettings = localStorage.getItem('userSettings')
    const defaultSettings = {
        lang: 'en',
        mobOneColor: null,
        mobTwoColor: null,
        dayNightMode: null,
        displayName: null,
    }
    settings.value = { ...defaultSettings, ...userSettings }

    dialogOpen.value = true
}
</script>

<style type="scss">
@reference "tailwindcss";

div.settingRow {
    @apply grid grid-cols-[auto_250px] items-center border-b py-1;
}

div.setting {
    h1 {
        @apply font-bold;
    }

    span {
        @apply text-sm ml-2 block;
    }
}

.toggleGroupItem {
    @apply flex items-center justify-center rounded-md px-2 py-1.5 transition-colors text-sm;
}
</style>