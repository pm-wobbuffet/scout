<template>
    <div v-if="props.paginator.links.length > 3">
        <div class="flex flex-wrap -mb-1 items-center">
            <template v-for="(link, itemkey) in props.paginator.links">
                <slot name="link-renderer" :link="link" :itemkey="itemkey">
                    <div v-if="link.url === null" :key="itemkey"
                        class="mb-1 mr-1 px-4 py-3 text-gray-400 text-sm leading-4 border rounded"
                        v-html="link.label" />
                    <Link v-else :key="`link-${itemkey}`"
                        class="mb-1 mr-1 px-4 py-3 focus:text-indigo-500 text-sm leading-4 hover:bg-white border focus:border-indigo-500 rounded"
                        :class="{ 'bg-white': link.active }" :href="link.url" v-html="link.label" />
                </slot>
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface paginator {
    data: Array,
    links?: paginatorLinks,
    meta?: paginatorMeta,
}

interface paginatorLinks {
    first?: string,
    last?: string,
    prev?: string,
    next?: string,
}

interface paginatorMeta {
    current_page?: number,
    from?: number,
    last_page?: number,
    links: paginatorMetaLinks[],
    path?: string,
    per_page?: number,
    to?: number,
    total?: number,
}

interface paginatorMetaLinks {
    url?: string,
    label?: string,
    page?: number,
    active?: boolean,
}

interface paginatorProps {
    paginator: paginatorMeta,
}

const props = defineProps<paginatorProps>()

</script>

<style lang="scss" scoped></style>