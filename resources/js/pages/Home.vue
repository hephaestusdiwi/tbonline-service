<template>
    <div class="min-h-screen bg-white">
        <Navbar />
        <CartDrawer />
        <HeroSlider />
        <TopProducts />
        <ProductList :search-query="activeSearchQuery" />
        <PromotionSlider
            title="Promotions"
            see-all-link="https://instagram.com/tokomu"
            :items="promoItems"
        />
        <NewsletterBar />
        <FaqSection />
        <CustomerChat />
        <FooterSection />
    </div>
</template>
 
<script>
import { ref, computed } from 'vue'
import { useHead }          from '@vueuse/head'
import { useSiteSettings }  from '../composables/useSiteSettings'
import { useSeoMeta } from '../composables/useSeoMeta.js'
import { useVisitorTracker } from '../composables/useVisitorTracker'
 
import Navbar          from '../components/Navbar.vue'
import HeroSlider      from '../components/HeroSlider.vue'
import ProductList     from '../components/ProductList.vue'
import CartDrawer      from '../components/CartDrawer.vue'
import CustomerChat    from '../components/chat/ChatWidget.vue'
import TopProducts     from '../components/TopProducts.vue'
import NewsletterBar   from '../components/NewsletterBar.vue'
import FooterSection   from '../components/FooterSection.vue'
import PromotionSlider from '../components/PromotionSlider.vue'
import FaqSection      from '../components/FaqSection.vue'
 
export default {
    name: 'Home',
    components: {
        Navbar, HeroSlider, TopProducts, ProductList, PromotionSlider,
        CartDrawer, CustomerChat, FooterSection, NewsletterBar, FaqSection,
    },
 
    setup() {
        const activeSearchQuery = ref('')
        const { siteName }      = useSiteSettings()
 
        useHead({ title: computed(() => siteName.value || 'TB Store') })

        useSeoMeta({
            title:       'Home',
            description: 'Belanja kebutuhan vape berkualitas di TB Store. Dijamin aman dan proses cepat',
        })
 
        useVisitorTracker({
            page:      '/',
            pageTitle: 'Home - TB Store',
        })
 
        function onSearch(query) {
            activeSearchQuery.value = query
        }
 
        return { activeSearchQuery, onSearch }
    },
 
    async mounted() {
        try {
            const { fetchSettings, settings } = useSiteSettings()
            await fetchSettings()
 
            const googleCode = settings.value?.google_site_verification?.value
            if (googleCode) {
                useHead({ meta: [{ name: 'google-site-verification', content: googleCode }] })
            }
        } catch (e) {
            console.error('Failed to load site settings:', e)
        }
    },
}
</script>

<style scoped>
.search-section {
    padding: 32px 16px 24px;
    background: #fff;
}

.search-section-inner {
    max-width: 720px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
}

.search-label {
    font-size: 0.875rem;
    color: #888;
    margin: 0;
    letter-spacing: 0.01em;
}

/* Pastikan SearchBar full-width dalam wrapper */
.search-section-inner :deep(.search-wrapper) {
    max-width: 100%;
    width: 100%;
}
</style>