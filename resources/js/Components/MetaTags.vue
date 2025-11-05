<script setup>
import { Head } from '@inertiajs/vue3';
import { useMeta } from '@/composables/useMeta';
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: null,
    },
    description: {
        type: String,
        default: null,
    },
});

const {
    title: defaultTitle,
    description: defaultDescription,
    keywords,
    author,
    robots,
    ogTitle,
    ogDescription,
    ogType,
    ogSiteName,
    ogLocale,
    twitterCard,
    twitterTitle,
    twitterDescription,
    themeColor,
    applicationName,
} = useMeta();

// Используем переданный title или default
const pageTitle = computed(() => props.title || defaultTitle.value);
const pageDescription = computed(() => props.description || defaultDescription.value);
</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>

        <!-- Basic Meta Tags -->
        <meta name="description" :content="pageDescription" />
        <meta name="keywords" :content="keywords" />
        <meta name="author" :content="author" />
        <meta name="robots" :content="robots" />
        <meta name="application-name" :content="applicationName" />
        <meta name="theme-color" :content="themeColor" />

        <!-- Open Graph Meta Tags -->
        <meta property="og:title" :content="props.title || ogTitle" />
        <meta property="og:description" :content="props.description || ogDescription" />
        <meta property="og:type" :content="ogType" />
        <meta property="og:site_name" :content="ogSiteName" />
        <meta property="og:locale" :content="ogLocale" />
        <meta property="og:image" content="/images/main-logo.png" />

        <!-- Twitter Card Meta Tags -->
        <meta name="twitter:card" :content="twitterCard" />
        <meta name="twitter:title" :content="props.title || twitterTitle" />
        <meta name="twitter:description" :content="props.description || twitterDescription" />
        <meta name="twitter:image" content="/images/main-logo.png" />
    </Head>
</template>
