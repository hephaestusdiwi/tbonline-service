<template>
    <div class="min-h-screen bg-white">
        <Navbar />

        <!-- Loading -->
        <div v-if="loading" class="max-w-3xl mx-auto px-5 py-16 animate-pulse space-y-5">
            <div class="h-4 bg-gray-100 rounded w-24"/>
            <div class="h-8 bg-gray-100 rounded w-3/4"/>
            <div class="h-4 bg-gray-100 rounded w-1/3"/>
            <div class="aspect-video bg-gray-100 rounded-2xl"/>
            <div class="space-y-3">
                <div class="h-4 bg-gray-100 rounded"/>
                <div class="h-4 bg-gray-100 rounded w-5/6"/>
                <div class="h-4 bg-gray-100 rounded w-4/5"/>
            </div>
        </div>

        <!-- Not Found -->
        <div v-else-if="!article" class="max-w-3xl mx-auto px-5 py-32 text-center">
            <p class="text-6xl mb-4">😕</p>
            <h1 class="text-2xl font-black text-gray-800 mb-2" style="font-family: 'Poppins', sans-serif;">Artikel Tidak Ditemukan</h1>
            <p class="text-gray-400 text-sm mb-6">Artikel yang kamu cari mungkin telah dihapus atau dipindahkan.</p>
            <router-link to="/blog"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white transition-all"
                style="background: #BD2028;">
                ← Kembali ke Blog
            </router-link>
        </div>

        <template v-else>
            <!-- ── ARTICLE HERO ─────────────────────────────────────────── -->
            <div class="max-w-4xl mx-auto px-5 pt-10 pb-6">

                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-xs text-gray-400 mb-6" style="font-family: 'Poppins', sans-serif;">
                    <router-link to="/" class="hover:text-gray-600 transition-colors">Home</router-link>
                    <span>/</span>
                    <router-link to="/blog" class="hover:text-gray-600 transition-colors">Blog</router-link>
                    <span>/</span>
                    <span class="text-gray-600 truncate max-w-xs">{{ article.title }}</span>
                </nav>

                <!-- Tags -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <span v-for="tag in (article.tags || [])" :key="tag"
                        @click="$router.push(`/blog?tag=${tag}`)"
                        class="text-xs font-semibold px-3 py-1 rounded-full cursor-pointer transition-all"
                        style="background: rgba(189,32,40,0.08); color: #BD2028;">
                        # {{ tag }}
                    </span>
                </div>

                <!-- Title -->
                <h1 class="text-3xl md:text-4xl font-black text-gray-900 leading-tight mb-5"
                    style="font-family: 'Poppins', sans-serif; letter-spacing: -0.02em;">
                    {{ article.title }}
                </h1>

                <!-- Meta -->
                <div class="flex flex-wrap items-center gap-4 pb-6 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold"
                             style="background: #BD2028;">
                            {{ (article.author?.name || 'A')[0].toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800" style="font-family: 'Poppins', sans-serif;">{{ article.author?.name }}</p>
                            <p class="text-xs text-gray-400">{{ formatDate(article.published_at) }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 text-xs text-gray-400 ml-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        {{ readTime }} menit baca
                    </div>
                </div>
            </div>

            <!-- ── THUMBNAIL ───────────────────────────────────────────── -->
            <div v-if="article.thumbnail" class="max-w-4xl mx-auto px-5 mb-10">
                <img :src="article.thumbnail" :alt="article.title"
                     class="w-full aspect-video object-cover rounded-2xl shadow-sm"/>
            </div>

            <!-- ── ARTICLE BODY ─────────────────────────────────────────── -->
            <div class="max-w-3xl mx-auto px-5 mb-12">
                <!-- Excerpt -->
                <p v-if="article.excerpt" class="text-lg text-gray-500 leading-relaxed mb-8 pb-8 border-b border-gray-100 italic"
                   style="font-family: 'Poppins', sans-serif;">
                    {{ article.excerpt }}
                </p>

                <!-- Body content -->
                <div class="blog-content" v-html="article.body"/>

                <!-- Share buttons -->
                <div class="mt-12 pt-8 border-t border-gray-100">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Bagikan Artikel</p>
                    <div class="flex flex-wrap gap-2">
                        <a :href="shareUrls.whatsapp" target="_blank" rel="noopener"
                           class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white transition-all hover:opacity-90"
                           style="background: #25D366;">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            WhatsApp
                        </a>
                        <a :href="shareUrls.twitter" target="_blank" rel="noopener"
                           class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white transition-all hover:opacity-90"
                           style="background: #000;">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                            Twitter / X
                        </a>
                        <a :href="shareUrls.facebook" target="_blank" rel="noopener"
                           class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white transition-all hover:opacity-90"
                           style="background: #1877F2;">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Facebook
                        </a>
                        <button @click="copyLink"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
                            </svg>
                            {{ copied ? 'Tersalin!' : 'Salin Link' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── RELATED ARTICLES ─────────────────────────────────────── -->
            <div v-if="related.length" class="border-t border-gray-100 py-12" style="background: #fafafa;">
                <div class="max-w-7xl mx-auto px-5">
                    <h2 class="text-xl font-black text-gray-900 mb-6" style="font-family: 'Poppins', sans-serif;">
                        Artikel Terkait
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <article v-for="rel in related" :key="rel.id"
                            @click="$router.push(`/blog/${rel.slug}`)"
                            class="group cursor-pointer bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-300 hover:-translate-y-0.5">
                            <div class="aspect-video overflow-hidden bg-gray-50">
                                <img v-if="rel.thumbnail" :src="rel.thumbnail" :alt="rel.title"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                                <div v-else class="w-full h-full flex items-center justify-center text-3xl" style="background: rgba(189,32,40,0.04);">📝</div>
                            </div>
                            <div class="p-4">
                                <div class="flex gap-1 mb-2">
                                    <span v-for="tag in (rel.tags || []).slice(0,1)" :key="tag"
                                        class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                        style="background: rgba(189,32,40,0.07); color: #BD2028;"># {{ tag }}</span>
                                </div>
                                <h3 class="font-bold text-gray-800 text-sm leading-snug line-clamp-2 group-hover:text-[#BD2028] transition-colors mb-2"
                                    style="font-family: 'Poppins', sans-serif;">{{ rel.title }}</h3>
                                <p class="text-xs text-gray-400">{{ formatDate(rel.published_at) }}</p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>

            <!-- ── BACK TO BLOG ─────────────────────────────────────────── -->
            <div class="max-w-3xl mx-auto px-5 py-10 text-center">
                <router-link to="/blog"
                    class="inline-flex items-center gap-2 text-sm font-semibold transition-colors"
                    style="color: #BD2028;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke Blog
                </router-link>
            </div>
        </template>

        <FooterSection />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useHead } from '@vueuse/head'
import Navbar from '../components/Navbar.vue'
import FooterSection from '../components/FooterSection.vue'
import axios from '../axios'

const route   = useRoute()
const router  = useRouter()
const article = ref(null)
const related = ref([])
const loading = ref(true)
const copied  = ref(false)

const currentUrl = computed(() => window.location.href)

const readTime = computed(() => {
    if (!article.value?.body) return 1
    const words = article.value.body.replace(/<[^>]*>/g, '').split(/\s+/).length
    return Math.max(1, Math.ceil(words / 200))
})

const shareUrls = computed(() => {
    const url   = encodeURIComponent(currentUrl.value)
    const title = encodeURIComponent(article.value?.title || '')
    return {
        whatsapp: `https://wa.me/?text=${title}%20${url}`,
        twitter:  `https://twitter.com/intent/tweet?text=${title}&url=${url}`,
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
    }
})

useHead(computed(() => ({
    title: article.value?.meta_title || article.value?.title || 'Blog',
    meta: [
        { name: 'description', content: article.value?.meta_description || article.value?.excerpt || '' },
        { property: 'og:title', content: article.value?.title || '' },
        { property: 'og:description', content: article.value?.excerpt || '' },
        { property: 'og:image', content: article.value?.thumbnail || '' },
        { property: 'og:url', content: currentUrl.value },
    ],
})))

async function loadArticle() {
    loading.value = true
    article.value = null
    related.value = []
    try {
        const { data } = await axios.get(`/blog/${route.params.slug}`)
        article.value = data.data

        // Load related
        if (article.value.tags?.length) {
            const rel = await axios.get('/blog', {
                params: { type: 'article', status: 'published', tag: article.value.tags[0], per_page: 3 }
            })
            related.value = (rel.data.data || []).filter(a => a.id !== article.value.id).slice(0, 3)
        }
    } catch (e) {
        if (e.response?.status === 404) article.value = null
        else console.error(e)
    } finally {
        loading.value = false
    }
}

async function copyLink() {
    try {
        await navigator.clipboard.writeText(currentUrl.value)
        copied.value = true
        setTimeout(() => { copied.value = false }, 2000)
    } catch {}
}

function formatDate(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

onMounted(loadArticle)
watch(() => route.params.slug, loadArticle)
</script>

<style>
/* Blog content typography */
.blog-content {
    font-family: 'Poppins', sans-serif;
    font-size: 1rem;
    line-height: 1.85;
    color: #374151;
}
.blog-content h2 {
    font-size: 1.6rem;
    font-weight: 800;
    color: #111827;
    margin: 2.5rem 0 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f3f4f6;
    letter-spacing: -0.02em;
}
.blog-content h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 2rem 0 0.75rem;
}
.blog-content h4 {
    font-size: 1.05rem;
    font-weight: 700;
    color: #374151;
    margin: 1.5rem 0 0.5rem;
}
.blog-content p {
    margin-bottom: 1.25rem;
}
.blog-content ul {
    list-style: none;
    padding-left: 0;
    margin-bottom: 1.25rem;
}
.blog-content ul li {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 0.5rem;
}
.blog-content ul li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0.6em;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #BD2028;
}
.blog-content ol {
    list-style: decimal;
    padding-left: 1.5rem;
    margin-bottom: 1.25rem;
}
.blog-content ol li {
    margin-bottom: 0.5rem;
}
.blog-content blockquote {
    border-left: 4px solid #BD2028;
    padding: 1rem 1.25rem;
    margin: 1.5rem 0;
    background: rgba(189,32,40,0.04);
    border-radius: 0 12px 12px 0;
    color: #6b7280;
    font-style: italic;
    font-size: 1.05rem;
}
.blog-content code {
    background: #f3f4f6;
    color: #BD2028;
    padding: 0.15em 0.45em;
    border-radius: 5px;
    font-size: 0.875em;
    font-family: monospace;
}
.blog-content pre {
    background: #1f2937;
    color: #f9fafb;
    padding: 1.25rem 1.5rem;
    border-radius: 14px;
    overflow-x: auto;
    margin: 1.5rem 0;
    font-family: monospace;
    font-size: 0.875rem;
    line-height: 1.7;
}
.blog-content pre code {
    background: none;
    color: inherit;
    padding: 0;
}
.blog-content img {
    max-width: 100%;
    border-radius: 16px;
    margin: 1.5rem 0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.blog-content a {
    color: #BD2028;
    text-decoration: underline;
    text-underline-offset: 3px;
}
.blog-content a:hover { color: #7f1d1d; }
.blog-content hr {
    border: none;
    border-top: 2px solid #f3f4f6;
    margin: 2rem 0;
}
.blog-content strong { font-weight: 700; color: #111827; }
.blog-content em { font-style: italic; }
</style>