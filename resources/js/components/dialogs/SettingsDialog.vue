<template>
    <Dialog v-bind:open="dialogOpen">
        <DialogTrigger as-child>
            <button
                class="border rounded-sm ml-1 p-1 bg-[rgba(0,0,0,0.4)] hover:bg-[rgba(0,0,255,0.1)] cursor-pointer dark:border-slate-500 transition-colors duration-300"
                title="Manage site settings" @click="handleOpen">
                <SettingsIcon :size="18" />
            </button>
        </DialogTrigger>
        <DialogContent class="" @escape-key-down="dialogOpen = false" @pointer-down-outside="dialogOpen = false">
            <DialogHeader>
                <DialogTitle>Settings</DialogTitle>
                <DialogDescription>Edit the settings specified below to customize your Turtle Scout experience.
                </DialogDescription>
            </DialogHeader>

            <form id="frmSettings" onsubmit="return false">
                <div class="settingRow">
                    <div class="setting">
                        <h1>Display Name</h1>
                        <span>Your name as displayed on the scout list.</span>
                    </div>
                    <div>
                        <Input name="displayName" v-model="settings.displayName" />
                    </div>
                </div>
                <div class="settingRow">
                    <div class="setting">
                        <h1>Language</h1>
                        <span>The language of mobs/zones to display. In the future, may also apply to User Interface
                            elements.</span>
                    </div>
                    <div class="flex">
                        <LanguageSelectorTabs v-model="settings.lang" />
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
                    <div class="flex gap-x-1">
                        <Input type="color" v-model="settings.mobOneColor" />
                        <Button variant="default" class="text-[#00C951]"
                            @click="settings.mobOneColor = '#00C951'">Default</Button>
                    </div>
                </div>
                <div class="settingRow">
                    <div class="setting">
                        <h1>Mob 2 Background Color</h1>
                    </div>
                    <div class="flex gap-x-1">
                        <Input type="color" v-model="settings.mobTwoColor" />
                        <Button variant="default" @click="settings.mobTwoColor = '#2B7FFF'"
                            class="text-[#2B7FFF]">Default</Button>
                    </div>
                </div>
                <div class="settingRow">
                    <div class="setting">
                        <h1>Hide Zone Name Spoilers</h1>
                        <span>During the first 6 weeks of an expansion, this will replace the name of any
                            zones not announced during Live Letters or in promotional material with placeholder names.
                        </span>
                    </div>
                    <div>
                        <div class="flex gap-2 items-center justify-center">
                            <SwitchRoot v-model="settings.hideZoneSpoilers" id="hide-spoilers"
                                class="w-[32px] h-[20px] shadow-sm flex data-[state=unchecked]:bg-stone-300 data-[state=checked]:bg-stone-800 dark:data-[state=unchecked]:bg-stone-800 dark:data-[state=checked]:bg-stone-700 border border-stone-300 data-[state=checked]:border-stone-700  dark:border-stone-700 rounded-full relative transition-[background] focus-within:outline-none focus-within:shadow-[0_0_0_1px] focus-within:border-stone-800 focus-within:shadow-stone-800">
                                <SwitchThumb
                                    class="w-3.5 h-3.5 my-auto bg-white text-xs flex items-center justify-center shadow-xl rounded-full transition-transform translate-x-0.5 will-change-transform data-[state=checked]:translate-x-full" />
                            </SwitchRoot>
                        </div>
                    </div>
                </div>
                <!-- <div class="settingRow">
                    <div class="setting">
                        <h1>Show Quick Paste Bar</h1>
                        <span>Always show the quick paste bar at the bottom of the screen to quickly paste in
                            coordinates from chat.</span>
                    </div>
                    <div>

                    </div>
                </div> -->
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
import { SwitchRoot, SwitchThumb } from 'reka-ui';
import Button from '@/components/ui/button/Button.vue';
//import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import { SettingsIcon } from 'lucide-vue-next';
import { inject, ref } from 'vue';
import LanguageSelectorTabs from '@/components/inputs/LanguageSelectorTabs.vue';

const dialogOpen = ref(false)
const settings = inject('settings')

const handleOpen = () => {
    dialogOpen.value = true
}
</script>

<style type="scss">
@reference "tailwindcss";

div.settingRow {
    @apply grid grid-cols-1 lg:grid-cols-[auto_250px] items-center border-b py-1;
}

div.setting {
    h1 {
        @apply font-bold;
    }

    span {
        @apply text-xs ml-2 block;
    }
}

.toggleGroupItem {
    @apply flex items-center justify-center rounded-md px-2 py-1.5 transition-colors text-sm;
}
</style>