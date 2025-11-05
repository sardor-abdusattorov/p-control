import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useMeta() {
    const page = usePage();

    // Получаем метатеги из shared props
    const meta = computed(() => {
        const metaData = page.props.meta || {};
        // Отладка - выведем что приходит
        console.log('Meta data from props:', metaData);
        return metaData;
    });

    // Вспомогательная функция для получения конкретного метатега
    const getMeta = (key, defaultValue = '') => {
        return meta.value[key] || defaultValue;
    };

    // Готовые computed свойства для частых метатегов
    const title = computed(() => getMeta('title'));
    const description = computed(() => getMeta('description'));
    const keywords = computed(() => getMeta('keywords'));
    const author = computed(() => getMeta('author'));
    const robots = computed(() => getMeta('robots'));

    // Open Graph
    const ogTitle = computed(() => getMeta('og_title'));
    const ogDescription = computed(() => getMeta('og_description'));
    const ogType = computed(() => getMeta('og_type'));
    const ogSiteName = computed(() => getMeta('og_site_name'));
    const ogLocale = computed(() => getMeta('og_locale'));

    // Twitter Card
    const twitterCard = computed(() => getMeta('twitter_card'));
    const twitterTitle = computed(() => getMeta('twitter_title'));
    const twitterDescription = computed(() => getMeta('twitter_description'));

    // Дополнительные
    const themeColor = computed(() => getMeta('theme_color'));
    const applicationName = computed(() => getMeta('application_name'));

    return {
        // Все метатеги
        meta,
        getMeta,

        // Основные
        title,
        description,
        keywords,
        author,
        robots,

        // Open Graph
        ogTitle,
        ogDescription,
        ogType,
        ogSiteName,
        ogLocale,

        // Twitter Card
        twitterCard,
        twitterTitle,
        twitterDescription,

        // Дополнительные
        themeColor,
        applicationName,
    };
}
