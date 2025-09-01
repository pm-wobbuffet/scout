<template>
    <div class="inline-flex m-auto gap-1 rounded-lg">
        <button v-for="{ value, title, label } in languages" :key="value" :title="title" @click="update(value)" :class="[
            'flex items-center justify-center rounded-md px-2 py-1.5 transition-colors',
            model === value
                ? 'bg-blue-200/25 shadow-xs dark:bg-blue-200/25 dark:text-neutral-100'
                : 'bg-[rgba(0,0,0,0.4)] dark:bg-slate-700 text-neutral-100 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-slate-600',
        ]">{{ label }}</button>
    </div>
</template>

<script setup>

const model = defineModel();

const languages = [
    { value: 'en', label: 'EN', title: 'English' },
    { value: 'de', label: 'DE', title: 'German' },
    { value: 'fr', label: 'FR', title: 'French' },
    { value: 'jp', label: 'JP', title: 'Japanese' },
];

const update = (value) => {
    model.value = value
}

</script>

<style lang="scss" scoped></style>