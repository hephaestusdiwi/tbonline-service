<template>
    <div class="min-h-screen bg-white">
        <Navbar />

        <!-- Loading -->
        <div v-if="loading" class="max-w-3xl mx-auto px-5 py-16 animate-pulse space-y-5">
            <div class="h-4 bg-gray-100 rounded w-24"/>
            <div class="h-8 bg-gray-100 rounded w-2/3"/>
            <div class="h-4 bg-gray-100 rounded w-1/3"/>
            <div class="space-y-3 mt-8">
                <div v-for="i in 8" :key="i" class="h-4 bg-gray-100 rounded" :style="{ width: i % 3 === 0 ? '75%' : '100%' }"/>
            </div>
        </div>

        <!-- Not Found -->
        <div v-else-if="!content" class="max-w-3xl mx-auto px-5 py-32 text-center">
            <h1 class="text-2xl font-black text-gray-800 mb-2" style="font-family: 'Poppins', sans-serif;">
                Halaman Belum Tersedia
            </h1>
            <p class="text-gray-400 text-sm mb-6">Konten halaman ini sedang dalam proses pembuatan.</p>
            <router-link to="/"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white"
                style="background: #BD2028;">
                ← Kembali ke Home
            </router-link>
        </div>

        <template v-else>
            <!-- ── HERO ────────────────────────────────────────────────── -->
            <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #BD2028 0%, #7f1d1d 100%);">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 right-0 w-96 h-96 rounded-full" style="background: #fff; transform: translate(30%, -30%);"/>
                    <div class="absolute bottom-0 left-0 w-64 h-64 rounded-full" style="background: #fff; transform: translate(-30%, 30%);"/>
                </div>
                <div class="relative max-w-7xl mx-auto px-5 py-14 md:py-20">
                    <!-- Breadcrumb -->
                    <nav class="flex items-center gap-2 text-xs text-red-200 mb-6">
                        <router-link to="/" class="hover:text-white transition-colors">Home</router-link>
                        <span>/</span>
                        <span class="text-white font-semibold">{{ pageMeta.title }}</span>
                    </nav>

                    <div class="flex items-center gap-4">
                        <div>
                            <h1 class="text-2xl md:text-4xl font-black text-white leading-tight"
                                style="font-family: 'Poppins', sans-serif; letter-spacing: -0.02em;">
                                {{ content.title || pageMeta.title }}
                            </h1>
                            <p class="text-red-200 text-sm mt-1">
                                Terakhir diperbarui: {{ formatDate(content.updated_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── CONTENT ─────────────────────────────────────────────── -->
            <div class="max-w-7xl mx-auto px-5 py-12">
                <div class="grid grid-cols-1 lg:grid-cols-[1fr_260px] gap-10">

                    <!-- Body -->
                    <div class="blog-content" v-html="content.body"/>

                    <!-- Sidebar -->
                    <div class="space-y-4">

                        <!-- Navigasi halaman statis lain -->
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden sticky top-24">
                            <div class="px-5 py-4 border-b border-gray-100" style="background: #fafafa;">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Informasi Lainnya</p>
                            </div>
                            <div class="p-3 space-y-1">
                                <router-link
                                    v-for="page in otherPages" :key="page.path"
                                    :to="page.path"
                                    class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm transition-all group"
                                    :style="$route.path === page.path
                                        ? 'background: rgba(189,32,40,0.08); color: #BD2028; font-weight: 600;'
                                        : 'color: #6b7280;'"
                                    @mouseenter="e => { if ($route.path !== page.path) e.currentTarget.style.background = '#f9fafb' }"
                                    @mouseleave="e => { if ($route.path !== page.path) e.currentTarget.style.background = 'transparent' }"
                                >
                                    <span class="text-lg shrink-0">
                                        <font-awesome-icon :icon="page.icon" />
                                    </span>
                                    <span class="flex-1 leading-tight" style="font-family: 'Poppins', sans-serif; font-size: 0.8rem;">{{ page.label }}</span>
                                    <svg class="w-3.5 h-3.5 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity"
                                         :style="$route.path === page.path ? 'opacity: 1; color: #BD2028;' : 'color: #9ca3af;'"
                                         fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                    </svg>
                                </router-link>
                            </div>

                            <!-- Butuh bantuan -->
                            <div class="mx-3 mb-3 p-4 rounded-xl" style="background: rgba(189,32,40,0.05);">
                                <p class="text-xs font-bold mb-1" style="color: #BD2028;">Butuh Bantuan?</p>
                                <p class="text-xs text-gray-500 mb-3">Tim kami siap membantu kamu 24/7.</p>
                                <a href="https://wa.me/" target="_blank"
                                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-white transition-all"
                                   style="background: #25D366;">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                    Hubungi WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <FooterSection />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useHead } from '@vueuse/head'
import Navbar from '../components/Navbar.vue'
import FooterSection from '../components/FooterSection.vue'
import axios from '../axios'

const route   = useRoute()
const content = ref(null)
const loading = ref(true)

// Map path → type & meta
const PAGE_MAP = {
    '/syarat-ketentuan':       { type: 'tos',           title: 'Syarat & Ketentuan',            icon: 'file-contract' },
    '/informasi-pengiriman':   { type: 'shipping_info', title: 'Informasi Pengiriman',          icon: 'truck' },
    '/informasi-pengembalian': { type: 'return_policy', title: 'Informasi Pengembalian Barang', icon: 'undo' },
}

const otherPages = [
    { path: '/syarat-ketentuan',       label: 'Syarat & Ketentuan',            icon: 'file-contract' },
    { path: '/informasi-pengiriman',   label: 'Informasi Pengiriman',          icon: 'truck' },
    { path: '/informasi-pengembalian', label: 'Informasi Pengembalian Barang', icon: 'undo' },
]

const pageMeta = computed(() => PAGE_MAP[route.path] ?? { type: '', title: 'Halaman Informasi', icon: 'fa-file' })

useHead(computed(() => ({
    title: content.value?.meta_title || pageMeta.value.title,
    meta: [
        { name: 'description', content: content.value?.meta_description || '' },
    ],
})))

async function loadContent() {
    loading.value = true
    content.value = null
    try {
        const { data } = await axios.get(`/static-pages/${pageMeta.value.type}`)
        content.value = data.data
    } catch (e) {
        if (e.response?.status !== 404) console.error(e)
    } finally {
        loading.value = false
    }
}

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

onMounted(loadContent)
watch(() => route.path, loadContent)
</script>

<style>
.blog-content {
    font-family: 'Poppins', sans-serif;
    font-size: 0.9375rem;
    line-height: 1.85;
    color: #374151;
}
.blog-content h2 {
    font-size: 1.4rem; font-weight: 800; color: #111827;
    margin: 2.5rem 0 1rem; padding-bottom: 0.5rem;
    border-bottom: 2px solid #f3f4f6;
    letter-spacing: -0.02em;
}
.blog-content h3 {
    font-size: 1.15rem; font-weight: 700; color: #111827;
    margin: 2rem 0 0.75rem;
}
.blog-content h4 {
    font-size: 1rem; font-weight: 700; color: #374151;
    margin: 1.5rem 0 0.5rem;
}
.blog-content p { margin-bottom: 1.1rem; }
.blog-content ul {
    list-style: none; padding-left: 0; margin-bottom: 1.1rem;
}
.blog-content ul li {
    position: relative; padding-left: 1.5rem; margin-bottom: 0.5rem;
}
.blog-content ul li::before {
    content: ''; position: absolute; left: 0; top: 0.6em;
    width: 6px; height: 6px; border-radius: 50%; background: #BD2028;
}
.blog-content ol { list-style: decimal; padding-left: 1.5rem; margin-bottom: 1.1rem; }
.blog-content ol li { margin-bottom: 0.5rem; }
.blog-content blockquote {
    border-left: 4px solid #BD2028;
    padding: 1rem 1.25rem; margin: 1.5rem 0;
    background: rgba(189,32,40,0.04);
    border-radius: 0 12px 12px 0;
    color: #6b7280; font-style: italic;
}
.blog-content code {
    background: #f3f4f6; color: #BD2028;
    padding: 0.15em 0.45em; border-radius: 5px;
    font-size: 0.875em; font-family: monospace;
}
.blog-content a { color: #BD2028; text-decoration: underline; text-underline-offset: 3px; }
.blog-content a:hover { color: #7f1d1d; }
.blog-content hr { border: none; border-top: 2px solid #f3f4f6; margin: 2rem 0; }
.blog-content strong { font-weight: 700; color: #111827; }
</style>