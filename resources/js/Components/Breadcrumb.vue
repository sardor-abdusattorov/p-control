<template>
    <div
        class="flex items-center justify-between py-4 px-4 sm:px-0 text-slate-500 dark:text-slate-300"
    >
        <div class="flex items-center space-x-2">
            <Link :href="route('dashboard')" v-show="breadcrumbs.length !== 0">
                {{ lang().label.dashboard }}
            </Link>
            <div
                v-for="(breadcrumb, index) in breadcrumbs"
                :key="index"
                class="flex items-center space-x-2"
            >
                <ChevronRightIcon class="w-3 h-3" />
                <template v-if="index < breadcrumbs.length - 1">
                    <Link :href="breadcrumb.href">
                        {{ breadcrumb.label }}
                    </Link>
                </template>
                <template v-else>
                    <span class="text-slate-900 dark:text-slate-100">
                        {{ breadcrumb.label }}
                    </span>
                </template>
            </div>
        </div>

        <p class="text-sm font-bold">
            {{ formattedDate }}
        </p>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { ChevronRightIcon } from "@heroicons/vue/24/solid";
import { Link } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";

const locale = usePage().props.locale || "ru";

const uzbekMonthsLatin = [
    "yanvar", "fevral", "mart", "aprel", "may", "iyun",
    "iyul", "avgust", "sentyabr", "oktyabr", "noyabr", "dekabr"
];

const uzbekWeekdaysLatin = [
    "Yakshanba", "Dushanba", "Seshanba", "Chorshanba", "Payshanba", "Juma", "Shanba"
];

const currentDate = ref(new Date());

const formattedDate = computed(() => {
    const date = currentDate.value;

    if (locale === "uz") {
        const day = date.getDate();
        const month = uzbekMonthsLatin[date.getMonth()];
        const year = date.getFullYear();
        const weekday = uzbekWeekdaysLatin[date.getDay()];
        const hours = date.getHours().toString().padStart(2, "0");
        const minutes = date.getMinutes().toString().padStart(2, "0");
        const seconds = date.getSeconds().toString().padStart(2, "0");

        return `${year} ${month} ${day}, ${weekday} ${hours}:${minutes}:${seconds}`;
    }

    return date.toLocaleString(locale, {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit"
    });
});
onMounted(() => {
    setInterval(() => {
        currentDate.value = new Date();
    }, 1000);
});

defineProps({
    title: String,
    breadcrumbs: Array,
});
</script>
