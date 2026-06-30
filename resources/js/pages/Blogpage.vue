<template>
    <div class="min-h-screen bg-white">
        <Navbar />

        <!-- ── HERO ──────────────────────────────────────────────────── -->
        <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #BD2028 0%, #7f1d1d 100%);">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-96 h-96 rounded-full" style="background: #fff; transform: translate(30%, -30%);"/>
                <div class="absolute bottom-0 left-0 w-64 h-64 rounded-full" style="background: #fff; transform: translate(-30%, 30%);"/>
            </div>
            <div class="relative max-w-7xl mx-auto px-5 py-16 md:py-24 text-center">
                <p class="text-xs font-bold tracking-widest text-red-200 uppercase mb-3">Blog & Artikel</p>
                <h1 class="text-3xl md:text-5xl font-black text-white mb-4" style="font-family: 'Poppins', sans-serif; letter-spacing: -0.02em;">
                    Whats'on TB Store
                </h1>
                <p class="text-red-100 text-sm md:text-base max-w-xl mx-auto mb-8">
                    Our stories, latest updates, and exclusive promos. <br>Find anything you want to know about us.
                </p>

                <!-- Search bar -->
                <div class="max-w-lg mx-auto relative">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input
                        v-model="searchQuery"
                        @input="onSearch"
                        type="text"
                        placeholder="Cari artikel..."
                        class="searchbar w-full pl-11 pr-4 py-3.5 rounded-2xl text-sm text-gray-800 bg-white shadow-lg focus:outline-none"
                        style="font-family: 'Poppins', sans-serif;"
                    />
                </div>
            </div>
        </section>

        <!-- ── MAIN CONTENT ───────────────────────────────────────────── -->
        <div class="max-w-7xl mx-auto px-5 py-12">

            <!-- Tag filter -->
            <div v-if="allTags.length" class="flex flex-wrap gap-2 mb-8">
                <button
                    @click="selectedTag = ''; loadArticles()"
                    class="px-4 py-1.5 rounded-full text-xs font-semibold border transition-all"
                    :style="selectedTag === ''
                        ? 'background: #BD2028; color: #fff; border-color: #BD2028;'
                        : 'background: #fff; color: #6b7280; border-color: #e5e7eb;'"
                >Semua</button>
                <button
                    v-for="tag in allTags" :key="tag"
                    @click="selectedTag = tag; loadArticles()"
                    class="px-4 py-1.5 rounded-full text-xs font-semibold border transition-all"
                    :style="selectedTag === tag
                        ? 'background: #BD2028; color: #fff; border-color: #BD2028;'
                        : 'background: #fff; color: #6b7280; border-color: #e5e7eb;'"
                >{{ tag }}</button>
            </div>

            <!-- Loading skeleton -->
            <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="i in 6" :key="i" class="bg-white rounded-2xl border border-gray-100 overflow-hidden animate-pulse">
                    <div class="aspect-video bg-gray-100"/>
                    <div class="p-5 space-y-3">
                        <div class="h-3 bg-gray-100 rounded w-1/4"/>
                        <div class="h-5 bg-gray-100 rounded w-3/4"/>
                        <div class="h-3 bg-gray-100 rounded"/>
                        <div class="h-3 bg-gray-100 rounded w-2/3"/>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else-if="!articles.length" class="text-center py-24">
                <div class="w-20 h-20 rounded-3xl flex items-center justify-center text-4xl mx-auto mb-5" style="background: rgba(189,32,40,0.06)">📭</div>
                <p class="text-lg font-bold text-gray-700" style="font-family: 'Poppins', sans-serif;">Artikel tidak ditemukan</p>
                <p class="text-sm text-gray-400 mt-2">Coba kata kunci lain atau lihat semua artikel</p>
                <button @click="searchQuery = ''; selectedTag = ''; loadArticles()"
                    class="mt-5 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all"
                    style="background: #BD2028;">
                    Lihat Semua Artikel
                </button>
            </div>

            <!-- Article grid -->
            <div v-else>
                <!-- Featured article (first item) -->
                <div v-if="!searchQuery && !selectedTag && currentPage === 1 && articles[0]"
                     @click="goToDetail(articles[0].slug)"
                     class="group cursor-pointer mb-8 bg-white rounded-3xl border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 md:flex">
                    <div class="md:w-1/2 aspect-video md:aspect-auto overflow-hidden bg-gray-100 relative">
                        <img v-if="articles[0].thumbnail" :src="articles[0].thumbnail"
                             :alt="articles[0].title"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                        <div v-else class="w-full h-full flex items-center justify-center text-6xl" style="background: rgba(189,32,40,0.06)">📝</div>
                        <div class="absolute top-4 left-4">
                            <span class="featured-badge text-xs font-bold px-3 py-1 rounded-full text-white" style="background: #BD2028;">Featured</span>
                        </div>
                    </div>
                    <div class="md:w-1/2 p-8 flex flex-col justify-center">
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            <span v-for="tag in (articles[0].tags || []).slice(0,3)" :key="tag"
                                class="text-xs font-semibold px-2.5 py-0.5 rounded-full"
                                style="background: rgba(189,32,40,0.08); color: #BD2028;">{{ tag }}</span>
                        </div>
                        <h2 class="title-article text-2xl font-black text-gray-900 mb-3 group-hover:text-[#BD2028] transition-colors leading-tight" style="font-family: 'Poppins', sans-serif;">
                            {{ articles[0].title }}
                        </h2>
                        <p class="text-sm text-gray-500 leading-relaxed mb-5 line-clamp-3">
                            {{ articles[0].excerpt || stripHtml(articles[0].body) }}
                        </p>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                 style="background: #BD2028;">
                                {{ (articles[0].author?.name || 'A')[0].toUpperCase() }}
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-700">{{ articles[0].author?.name }}</p>
                                <p class="text-xs text-gray-400">{{ formatDate(articles[0].published_at) }}</p>
                            </div>
                            <div class="ml-auto flex items-center gap-1 text-xs font-semibold" style="color: #BD2028;">
                                Baca selengkapnya
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid articles -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <article
                        v-for="(article, i) in gridArticles" :key="article.id"
                        @click="goToDetail(article.slug)"
                        class="group cursor-pointer bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 hover:-translate-y-1 flex flex-col"
                    >
                        <!-- Thumbnail -->
                        <div class="aspect-video overflow-hidden bg-gray-50 relative">
                            <img v-if="article.thumbnail" :src="article.thumbnail" :alt="article.title"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                            <div v-else class="w-full h-full flex items-center justify-center text-4xl" style="background: rgba(189,32,40,0.04);">📝</div>
                        </div>

                        <!-- Content -->
                        <div class="p-5 flex flex-col flex-1">
                            <!-- Tags -->
                            <div class="flex flex-wrap gap-1 mb-2">
                                <span v-for="tag in (article.tags || []).slice(0,2)" :key="tag"
                                    class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                    style="background: rgba(189,32,40,0.07); color: #BD2028;">{{ tag }}</span>
                            </div>

                            <!-- Title -->
                            <h3 class="font-black text-gray-900 text-base leading-tight mb-2 group-hover:text-[#BD2028] transition-colors line-clamp-2" style="font-family: 'Poppins', sans-serif;">
                                {{ article.title }}
                            </h3>

                            <!-- Excerpt -->
                            <p class="text-xs text-gray-500 leading-relaxed line-clamp-2 flex-1">
                                {{ article.excerpt || stripHtml(article.body) }}
                            </p>

                            <!-- Footer -->
                            <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-50">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                     style="background: #BD2028; font-size: 9px;">
                                    {{ (article.author?.name || 'A')[0].toUpperCase() }}
                                </div>
                                <span class="text-xs text-gray-500">{{ article.author?.name }}</span>
                                <span class="text-gray-200">·</span>
                                <span class="text-xs text-gray-400">{{ formatDate(article.published_at) }}</span>
                                <div class="ml-auto">
                                    <svg class="w-4 h-4 text-gray-300 group-hover:text-[#BD2028] group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Pagination -->
                <div v-if="meta.last_page > 1" class="flex items-center justify-center gap-2 mt-12">
                    <button
                        @click="goToPage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="w-10 h-10 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:border-red-300 hover:text-[#BD2028] disabled:opacity-30 disabled:cursor-not-allowed transition-all"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                        </svg>
                    </button>

                    <button v-for="page in visiblePages" :key="page"
                        @click="page !== '...' && goToPage(page)"
                        :disabled="page === '...'"
                        class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-semibold border transition-all"
                        :style="page === currentPage
                            ? 'background: #BD2028; color: #fff; border-color: #BD2028;'
                            : page === '...'
                                ? 'color: #d1d5db; border-color: transparent; cursor: default;'
                                : 'background: #fff; color: #6b7280; border-color: #e5e7eb;'"
                    >{{ page }}</button>

                    <button
                        @click="goToPage(currentPage + 1)"
                        :disabled="currentPage === meta.last_page"
                        class="w-10 h-10 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:border-red-300 hover:text-[#BD2028] disabled:opacity-30 disabled:cursor-not-allowed transition-all"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <FooterSection />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useDebounceFn } from '@vueuse/core'
import { useHead } from '@vueuse/head'
import Navbar from '../components/Navbar.vue'
import FooterSection from '../components/FooterSection.vue'
import axios from '../axios.js'

const router       = useRouter()
const articles     = ref([])
const allTags      = ref([])
const meta         = ref({})
const loading      = ref(true)
const searchQuery  = ref('')
const selectedTag  = ref('')
const currentPage  = ref(1)

useHead({
    title: 'Blog & Artikel',
    meta: [{ name: 'description', content: 'Baca artikel terbaru seputar tips belanja, tren fashion, dan inspirasi gaya hidup.' }],
})

// Articles selain featured
const gridArticles = computed(() => {
    if (!searchQuery.value && !selectedTag.value && currentPage.value === 1) {
        return articles.value.slice(1)
    }
    return articles.value
})

const visiblePages = computed(() => {
    const total = meta.value.last_page ?? 1
    const cur   = currentPage.value
    const pages = []
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= cur - 1 && i <= cur + 1)) pages.push(i)
        else if (pages[pages.length - 1] !== '...') pages.push('...')
    }
    return pages
})

async function loadArticles() {
    loading.value = true
    try {
        const params = {
            type:     'article',
            status:   'published',
            per_page: 7,
            page:     currentPage.value,
        }
        if (searchQuery.value) params.search = searchQuery.value
        if (selectedTag.value) params.tag    = selectedTag.value

        const { data } = await axios.get('/blog', { params })
        articles.value = data.data
        meta.value     = data.meta ?? {}

        // Kumpulkan semua tag unik
        if (!allTags.value.length) {
            const tags = new Set()
            data.data.forEach(a => (a.tags || []).forEach(t => tags.add(t)))
            allTags.value = [...tags]
        }
    } catch (e) {
        console.error('Failed to load articles:', e)
    } finally {
        loading.value = false
    }
}

const onSearch = useDebounceFn(() => {
    currentPage.value = 1
    loadArticles()
}, 400)

function goToPage(page) {
    if (page < 1 || page > meta.value.last_page) return
    currentPage.value = page
    loadArticles()
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function goToDetail(slug) {
    router.push(`/blog/${slug}`)
}

function formatDate(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

function stripHtml(html) {
    if (!html) return ''
    return html.replace(/<[^>]*>/g, '').slice(0, 150)
}

onMounted(loadArticles)
</script>
<style scoped>
.searchbar {
    border-radius: 20px;
}

.featured-badge {
    border-radius: 15px;
}

.title-article {
    font-weight: 700;
    font-family: "Poppins", sans-serif;
}
</style>