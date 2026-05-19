import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useMeta() {
    const page = usePage();

    const meta = computed(() => page.props.meta || {});

    const getMeta = (key, defaultValue = '') => {
        return meta.value[key] || defaultValue;
    };

    const title = computed(() => getMeta('title'));
    const description = computed(() => getMeta('description'));
    const keywords = computed(() => getMeta('keywords'));
    const author = computed(() => getMeta('author'));
    const robots = computed(() => getMeta('robots'));

    const ogTitle = computed(() => getMeta('og_title'));
    const ogDescription = computed(() => getMeta('og_description'));
    const ogType = computed(() => getMeta('og_type'));
    const ogSiteName = computed(() => getMeta('og_site_name'));
    const ogLocale = computed(() => getMeta('og_locale'));

    const twitterCard = computed(() => getMeta('twitter_card'));
    const twitterTitle = computed(() => getMeta('twitter_title'));
    const twitterDescription = computed(() => getMeta('twitter_description'));

    const themeColor = computed(() => getMeta('theme_color'));
    const applicationName = computed(() => getMeta('application_name'));

    return {
        meta,
        getMeta,

        title,
        description,
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
    };
}
