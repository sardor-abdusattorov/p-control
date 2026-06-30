<script setup>
import { router } from "@inertiajs/vue3";
import { pickBy } from "lodash";
import { computed, reactive, watchEffect } from "vue";
import Icon from "@/Components/Icon.vue";

const props = defineProps({
    links: Object,
    filters: Object,
});

const data = reactive({
    params: {
        search: props.filters?.search,
        field: props.filters?.field,
        order: props.filters?.order,
        perPage: props.filters?.perPage,
    },
});

const goto = (link) => {
    if (!link) return;
    let params = pickBy(data.params);
    router.get(link, params, {
        replace: true,
        preserveState: true,
        preserveScroll: true,
    });
};

// Laravel paginator returns links.links as:
// [{ Previous }, { 1 }, { 2 }, ..., { Next }]
// We render only the numbered pages (and "..." separators) between
// the prev/next buttons.
const pages = computed(() =>
    (props.links?.links ?? []).slice(1, -1)
);

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
    <div v-if="links.links.length > 3">
        <ul
            class="flex justify-center items-center rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 divide-x divide-slate-200 dark:divide-slate-700"
        >
            <li>
                <button
                    v-on:click="goto(links.prev_page_url)"
                    class="px-4 py-2 transition-colors enabled:hover:bg-slate-100 dark:enabled:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed"
                    v-html="'&laquo;'"
                    :disabled="links.prev_page_url == null"
                ></button>
            </li>
            <li v-for="(page, index) in pages" :key="index">
                <span
                    v-if="page.url == null"
                    class="px-4 py-2 inline-block select-none text-slate-400 dark:text-slate-500"
                    v-html="page.label"
                ></span>
                <button
                    v-else
                    v-on:click="goto(page.url)"
                    class="px-4 py-2 transition-colors"
                    :class="
                        page.active
                            ? 'bg-primary text-white'
                            : 'enabled:hover:bg-slate-100 dark:enabled:hover:bg-slate-700'
                    "
                    v-html="page.label"
                ></button>
            </li>
            <li>
                <button
                    v-on:click="goto(links.next_page_url)"
                    class="px-4 py-2 transition-colors enabled:hover:bg-slate-100 dark:enabled:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed"
                    v-html="'&raquo;'"
                    :disabled="links.next_page_url == null"
                ></button>
            </li>
        </ul>
    </div>
</template>
