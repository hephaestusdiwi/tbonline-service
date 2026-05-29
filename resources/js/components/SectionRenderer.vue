<template>
    <component
        v-if="component"
        :is="component"
        :payload="section.payload"
    />
    <!-- Kalau type tidak dikenali, skip (tidak render apa-apa) -->
</template>

<script>
import { defineAsyncComponent } from 'vue'

// Map type → component
// Tambahkan entry baru di sini saat membuat section type baru
// tanpa perlu ubah SectionRenderer atau Home.vue
const sectionMap = {
    hero_banner:       defineAsyncComponent(() => import('./sections/HeroBanner.vue')),
    promo_carousel:    defineAsyncComponent(() => import('./sections/PromoCarousel.vue')),
    featured_products: defineAsyncComponent(() => import('./sections/FeaturedProducts.vue')),
    category_slider:   defineAsyncComponent(() => import('./sections/CategorySlider.vue')),
    bundle_promo:      defineAsyncComponent(() => import('./sections/BundlePromo.vue')),
    social_campaign:   defineAsyncComponent(() => import('./sections/SocialCampaign.vue')),
    instagram_promo:   defineAsyncComponent(() => import('./sections/InstagramPromo.vue')),
}

export default {
    name: 'SectionRenderer',

    props: {
        section: {
            type: Object,
            required: true,
        },
    },

    computed: {
        component() {
            return sectionMap[this.section.type] ?? null
        },
    },
}
</script>