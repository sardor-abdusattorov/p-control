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

const SIDE = 1;

const pages = computed(() => {
    const links = props.links;
    const current = links?.current_page;
    const last = links?.last_page;
    if (!last || last <= 1) return [];

    const urlByPage = {};
    (links.links ?? []).forEach((l) => {
        const n = parseInt(l.label, 10);
        if (!Number.isNaN(n)) urlByPage[n] = l.url;
    });

    const wanted = new Set([1, last]);
    for (let p = current - SIDE; p <= current + SIDE; p++) {
        if (p >= 1 && p <= last) wanted.add(p);
    }
    const nums = [...wanted].sort((a, b) => a - b);

    const result = [];
    let prev = 0;
    nums.forEach((n) => {
        if (n - prev > 1) {
            result.push({ label: "...", url: null, active: false });
        }
        result.push({
            label: String(n),
            url: urlByPage[n] ?? `${links.path}?page=${n}`,
            active: n === current,
        });
        prev = n;
    });
    return result;
});

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
