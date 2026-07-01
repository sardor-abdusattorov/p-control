<script setup>
import { router } from "@inertiajs/vue3";
import { pickBy } from "lodash";
import { computed, reactive, watchEffect } from "vue";
import Icon from "@/Components/Icon.vue";

const props = defineProps({
    links: Object,
    filters: Object,
});

const pageLinks = computed(() => {
    const current = props.links.current_page;
    const last = props.links.last_page;

    const pages = new Set();
    for (let p = 1; p <= Math.min(2, last); p++) pages.add(p);
    for (let p = Math.max(1, current - 1); p <= Math.min(last, current + 1); p++) pages.add(p);
    for (let p = Math.max(1, last - 1); p <= last; p++) pages.add(p);

    const sorted = [...pages].sort((a, b) => a - b);

    const result = [];
    let previous = null;
    for (const page of sorted) {
        if (previous !== null && page - previous > 1) {
            result.push({ page: null, label: "...", active: false });
        }
        result.push({
            page,
            label: String(page),
            active: page === current,
        });
        previous = page;
    }
    return result;
});

const gotoPage = (page) => {
    goto(`${props.links.path}?page=${page}`);
};

const data = reactive({
    params: {
        search: props.filters?.search,
        field: props.filters?.field,
        order: props.filters?.order,
        perPage: props.filters?.perPage,
    },
});

const goto = (link) => {
    let params = pickBy(data.params);
    router.get(link, params, {
        replace: true,
        preserveState: true,
        preserveScroll: true,
    });
};

watchEffect(() => {
    data.params.search = props.filters?.search;
    data.params.field = props.filters?.field;
    data.params.order = props.filters?.order;
    data.params.perPage = props.filters?.perPage;
});
</script>
<template>
    <div class="ml-2" v-if="links.data.length != 0">
        {{ links.from }}-{{ links.to }} {{ lang().label.of }} {{ links.total }}
    </div>
    <div
        class="flex flex-col space-y-2 mx-auto p-6 text-lg"
        v-if="links.data.length == 0"
    >
        <Icon :name="'nodata'" class="w-auto h-16" />
        <p>{{ lang().label.no_data }}</p>
    </div>
    <div v-if="links.last_page > 1">

        <ul
            class="flex justify-center items-center rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700"
        >
            <li v-for="(link, index) in pageLinks" :key="index">
                <button
                    v-if="link.page !== null"
                    v-on:click="gotoPage(link.page)"
                    class="px-4 py-2"
                    :class="link.active ? 'bg-primary text-white' : ''"
                    v-html="link.label"
                ></button>
                <span v-else class="px-4 py-2" v-html="link.label"></span>
            </li>
        </ul>
    </div>
</template>
