<template>
    <AdminLayout :title="pageMeta.title">

        <!-- ═══════════════════════════════════════════
             HERO HEADER
        ═══════════════════════════════════════════ -->
        <div class="relative mb-6 rounded-2xl overflow-hidden"
             style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"/>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"/>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10" style="background: white;"/>

            <div class="relative px-7 py-5 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <!-- Back link -->
                    <RouterLink to="/admin/pages"
                        class="flex items-center gap-1.5 text-xs font-semibold text-red-200 hover:text-white transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                        </svg>
                        Halaman Statis
                    </RouterLink>
                    <span class="text-red-300/50">/</span>
                    <div>
                        <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-0.5">Manajemen Konten</p>
                        <h1 class="text-2xl font-bold text-white tracking-tight">{{ pageMeta.title }}</h1>
                        <p class="text-red-200 text-xs mt-1">{{ pageMeta.description }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <!-- Status pill -->
                    <span v-if="store.currentItem"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border border-white/20"
                        :class="store.currentItem.status === 'published'
                            ? 'bg-emerald-500/20 text-emerald-100'
                            : 'bg-white/10 text-red-100'">
                        <span class="w-1.5 h-1.5 rounded-full"
                              :class="store.currentItem.status === 'published' ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"/>
                        {{ store.currentItem.status === 'published' ? 'Published' : 'Draft' }}
                    </span>

                    <!-- Toggle publish -->
                    <button v-if="store.currentItem && canPublish"
                        @click="onTogglePublish"
                        class="text-xs font-semibold px-4 py-2 rounded-xl border border-white/30 bg-white/15 text-white hover:bg-white/25 transition-all">
                        {{ store.currentItem.status === 'published' ? 'Jadikan Draft' : 'Publish' }}
                    </button>

                    <!-- Save -->
                    <button @click="handleSubmit" :disabled="store.isSaving"
                        class="flex items-center gap-2 text-sm font-semibold px-5 py-2.5 rounded-xl border border-white/30 bg-white text-[#ED1F24] hover:bg-red-50 transition-all disabled:opacity-50 shadow-sm">
                        <svg v-if="store.isSaving" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ store.isSaving ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </div>

            <!-- Stats strip -->
            <div class="relative border-t border-white/10 px-7 py-3 flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-6">
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Tipe</p>
                        <p class="text-white text-sm font-bold font-mono">{{ pageType }}</p>
                    </div>
                    <div v-if="store.currentItem" class="w-px h-8 bg-white/15"/>
                    <div v-if="store.currentItem">
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Diperbarui</p>
                        <p class="text-white text-sm font-bold">{{ formatDate(store.currentItem.updated_at) }}</p>
                    </div>
                    <div v-if="store.currentItem?.updated_by_user" class="w-px h-8 bg-white/15"/>
                    <div v-if="store.currentItem?.updated_by_user">
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Oleh</p>
                        <p class="text-white text-sm font-bold">{{ store.currentItem.updated_by_user.name }}</p>
                    </div>
                    <div class="w-px h-8 bg-white/15"/>
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Kata</p>
                        <p class="text-white text-sm font-bold tabular-nums">{{ wordCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── SUCCESS BANNER ─────────────────────────────────────────── -->
        <transition name="fade">
            <div v-if="successMessage"
                 class="mb-5 flex items-center gap-3 px-5 py-3 rounded-2xl border"
                 style="background: #f0fdf4; border-color: #bbf7d0;">
                <div class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-emerald-700">{{ successMessage }}</span>
                <button @click="successMessage = ''" class="ml-auto text-emerald-400 hover:text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </transition>

        <!-- ── LOADING ─────────────────────────────────────────────────── -->
        <div v-if="store.isLoading" class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-6">
            <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-8 animate-pulse space-y-4">
                    <div class="h-6 bg-gray-100 rounded w-1/4"/>
                    <div class="h-8 bg-gray-100 rounded w-3/4"/>
                    <div class="h-80 bg-gray-50 rounded-xl"/>
                </div>
            </div>
            <div class="h-48 bg-white rounded-2xl border border-gray-200/80 shadow-sm animate-pulse"/>
        </div>

        <!-- ── MAIN LAYOUT ─────────────────────────────────────────────── -->
        <div v-else class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-6">

            <!-- LEFT -->
            <div class="space-y-5 min-w-0">

                <!-- Title card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-6 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Judul Halaman</span>
                        <span class="text-xs font-bold" style="color: #ED1F24;">*</span>
                    </div>
                    <div class="px-6 py-5">
                        <input
                            v-model="form.title"
                            type="text"
                            :placeholder="`Judul ${pageMeta.title}...`"
                            class="w-full text-2xl font-bold text-gray-900 bg-transparent border-0 focus:outline-none placeholder-gray-200"
                        />
                        <p v-if="errors.title" class="text-xs mt-1.5" style="color: #ED1F24;">{{ errors.title }}</p>
                    </div>
                </div>

                <!-- Editor card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                                <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/>
                                </svg>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Konten</span>
                            <span class="text-xs font-bold" style="color: #ED1F24;">*</span>
                        </div>
                        <span class="text-xs text-gray-400 font-semibold">{{ wordCount }} kata</span>
                    </div>

                    <!-- Toolbar -->
                    <div v-if="editor" class="flex flex-wrap items-center gap-0.5 px-4 py-2.5 border-b border-gray-100 bg-gray-50/40">
                        <div class="flex items-center gap-0.5 pr-2 mr-2 border-r border-gray-200">
                            <button v-for="btn in toolbarGroup1" :key="btn.label"
                                @click="btn.action()" :title="btn.label" type="button"
                                class="w-8 h-8 flex items-center justify-center rounded-xl transition-all"
                                :style="btn.isActive() ? 'background: #ED1F24; color: #fff;' : 'color: #6b7280;'"
                                @mouseenter="e => { if (!btn.isActive()) e.currentTarget.style.background = '#f3f4f6' }"
                                @mouseleave="e => { if (!btn.isActive()) e.currentTarget.style.background = 'transparent' }">
                                <span v-html="btn.icon" class="w-3.5 h-3.5 flex items-center justify-center"/>
                            </button>
                        </div>
                        <div class="flex items-center gap-0.5 pr-2 mr-2 border-r border-gray-200">
                            <button v-for="btn in toolbarGroup2" :key="btn.label"
                                @click="btn.action()" :title="btn.label" type="button"
                                class="px-2 h-8 flex items-center justify-center rounded-xl text-xs font-bold transition-all"
                                :style="btn.isActive() ? 'background: #ED1F24; color: #fff;' : 'color: #6b7280;'"
                                @mouseenter="e => { if (!btn.isActive()) e.currentTarget.style.background = '#f3f4f6' }"
                                @mouseleave="e => { if (!btn.isActive()) e.currentTarget.style.background = 'transparent' }">
                                {{ btn.label }}
                            </button>
                        </div>
                        <div class="flex items-center gap-0.5">
                            <button v-for="btn in toolbarGroup3" :key="btn.label"
                                @click="btn.action()" :title="btn.label" type="button"
                                class="w-8 h-8 flex items-center justify-center rounded-xl transition-all"
                                :style="btn.isActive() ? 'background: #ED1F24; color: #fff;' : 'color: #6b7280;'"
                                @mouseenter="e => { if (!btn.isActive()) e.currentTarget.style.background = '#f3f4f6' }"
                                @mouseleave="e => { if (!btn.isActive()) e.currentTarget.style.background = 'transparent' }">
                                <span v-html="btn.icon" class="w-3.5 h-3.5 flex items-center justify-center"/>
                            </button>
                        </div>
                    </div>

                    <!-- Editor body -->
                    <div class="relative px-8 py-6 min-h-[500px]"
                         :class="errors.body ? 'ring-2 ring-inset ring-red-300' : ''"
                         @click="editor?.commands.focus()">
                        <editor-content :editor="editor" class="tiptap-editor focus:outline-none"/>
                        <div v-if="!form.body || form.body === '<p></p>'"
                             class="absolute top-6 left-8 pointer-events-none text-gray-300 text-base select-none">
                            Tulis konten {{ pageMeta.title }} di sini...
                        </div>
                    </div>
                    <p v-if="errors.body" class="px-6 pb-3 text-xs font-semibold" style="color: #ED1F24;">{{ errors.body }}</p>
                </div>

                <!-- SEO card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-6 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Search Engine Optimization</span>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <div class="flex justify-between mb-1.5">
                                <label class="text-xs font-bold text-gray-600">Meta Title</label>
                                <span class="text-xs text-gray-400">{{ form.meta_title?.length ?? 0 }}/60</span>
                            </div>
                            <input v-model="form.meta_title" type="text" maxlength="60"
                                placeholder="Judul untuk Google Search..."
                                class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none transition-colors text-gray-800 bg-gray-50/50"
                                @focus="e => e.target.style.borderColor = '#ED1F24'"
                                @blur="e => e.target.style.borderColor = '#e5e7eb'"
                            />
                        </div>
                        <div>
                            <div class="flex justify-between mb-1.5">
                                <label class="text-xs font-bold text-gray-600">Meta Description</label>
                                <span class="text-xs" :class="(form.meta_description?.length ?? 0) > 155 ? 'font-bold' : 'text-gray-400'"
                                      :style="(form.meta_description?.length ?? 0) > 155 ? 'color: #ED1F24' : ''">
                                    {{ form.meta_description?.length ?? 0 }}/160
                                </span>
                            </div>
                            <textarea v-model="form.meta_description" rows="2" maxlength="160"
                                placeholder="Deskripsi singkat untuk mesin pencari..."
                                class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none transition-colors resize-none text-gray-800 bg-gray-50/50"
                                @focus="e => e.target.style.borderColor = '#ED1F24'"
                                @blur="e => e.target.style.borderColor = '#e5e7eb'"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDEBAR -->
            <div class="space-y-4">

                <!-- Page info card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Info Halaman</span>
                    </div>
                    <div class="p-5">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl mb-4"
                             :style="{ background: pageMeta.iconBg }">
                            {{ pageMeta.icon }}
                        </div>
                        <h3 class="text-sm font-bold text-gray-800">{{ pageMeta.title }}</h3>
                        <p class="text-xs text-gray-400 mt-1">{{ pageMeta.description }}</p>

                        <div class="mt-4 pt-4 border-t border-gray-100 space-y-2.5">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-400">Tipe</span>
                                <span class="font-mono font-bold text-gray-700">{{ pageType }}</span>
                            </div>
                            <div v-if="store.currentItem" class="flex justify-between text-xs">
                                <span class="text-gray-400">Diperbarui</span>
                                <span class="font-semibold text-gray-700">{{ formatDate(store.currentItem.updated_at) }}</span>
                            </div>
                            <div v-if="store.currentItem?.updated_by_user" class="flex justify-between text-xs">
                                <span class="text-gray-400">Oleh</span>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold text-white shrink-0"
                                         style="background: linear-gradient(135deg, #ED1F24, #7f1d1d);">
                                        {{ (store.currentItem.updated_by_user.name || '?')[0].toUpperCase() }}
                                    </div>
                                    <span class="font-semibold text-gray-700">{{ store.currentItem.updated_by_user.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigasi halaman statis lain -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Halaman Lainnya</span>
                    </div>
                    <div class="p-3 space-y-1">
                        <RouterLink v-for="page in allPages" :key="page.type"
                            :to="`/admin/contents/${page.type}`"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all"
                            :style="page.type === pageType
                                ? 'background: rgba(237,31,36,0.06); color: #ED1F24; font-weight: 600;'
                                : 'color: #6b7280;'"
                            @mouseenter="e => { if (page.type !== pageType) e.currentTarget.style.background = '#f9fafb' }"
                            @mouseleave="e => { if (page.type !== pageType) e.currentTarget.style.background = 'transparent' }">
                            <span class="text-base shrink-0">{{ page.icon }}</span>
                            <span class="flex-1 text-sm">{{ page.label }}</span>
                            <svg v-if="page.type === pageType" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                        </RouterLink>
                    </div>
                </div>

                <!-- Tips card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                            <span class="text-sm">💡</span>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-widest" style="color: #ED1F24;">Tips Penulisan</span>
                    </div>
                    <ul class="p-5 text-xs text-gray-500 space-y-2.5">
                        <li class="flex gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0" style="background: #ED1F24;"/>
                            Gunakan H2/H3 untuk struktur konten yang jelas
                        </li>
                        <li class="flex gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0" style="background: #ED1F24;"/>
                            Isi meta description agar mudah ditemukan di Google
                        </li>
                        <li class="flex gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0" style="background: #ED1F24;"/>
                            Publish agar konten tampil di website publik
                        </li>
                        <li class="flex gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0" style="background: #ED1F24;"/>
                            Update berkala agar informasi selalu akurat
                        </li>
                    </ul>
                </div>

            </div>
        </div>

    </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import { useContentStore } from '../../store/useContentStore'
import { getUser } from '../../auth.js'

const route = useRoute()
const store = useContentStore()

const PAGE_META = {
    tos:           { title: 'Syarat & Ketentuan',           description: 'Aturan penggunaan layanan toko',        icon: '📋', iconBg: 'rgba(59,130,246,0.08)'  },
    shipping_info: { title: 'Informasi Pengiriman',          description: 'Kebijakan dan detail pengiriman',       icon: '🚚', iconBg: 'rgba(20,184,166,0.08)'  },
    return_policy: { title: 'Informasi Pengembalian Barang', description: 'Prosedur retur dan refund pelanggan',   icon: '↩️', iconBg: 'rgba(245,158,11,0.08)'  },
}

const ALL_PAGES = [
    { type: 'tos',           label: 'Syarat & Ketentuan', icon: '📋' },
    { type: 'shipping_info', label: 'Info Pengiriman',    icon: '🚚' },
    { type: 'return_policy', label: 'Info Pengembalian',  icon: '↩️' },
]

const pageType   = computed(() => route.params.type)
const pageMeta   = computed(() => PAGE_META[pageType.value] ?? { title: 'Halaman Statis', description: '', icon: '📄', iconBg: '#f3f4f6' })
const allPages   = ALL_PAGES

const user = computed(() => getUser() ?? {})
const userRole   = computed(() => user.value.role || '')
const canPublish = computed(() => ['admin', 'manager'].includes(userRole.value))

const form = ref({ title: '', body: '', meta_title: '', meta_description: '', status: 'draft' })
const errors         = ref({})
const successMessage = ref('')

const wordCount = computed(() => {
    const text = form.value.body.replace(/<[^>]*>/g, '').trim()
    return text ? text.split(/\s+/).length : 0
})

// ── Tiptap ────────────────────────────────────────────────────
const editor = useEditor({
    extensions: [
        StarterKit.configure({ heading: { levels: [2, 3, 4] } }),
        Link.configure({ openOnClick: false }),
    ],
    content: '',
    editorProps: {
        attributes: { class: 'focus:outline-none' },
    },
    onUpdate: ({ editor: e }) => {
        form.value.body = e.getHTML()
    },
})

onBeforeUnmount(() => editor.value?.destroy())

// ── Toolbar ────────────────────────────────────────────────────
const toolbarGroup1 = computed(() => [
    {
        label: 'Bold', isActive: () => editor.value?.isActive('bold') ?? false,
        action: () => editor.value?.chain().focus().toggleBold().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.744h-.753v8.25h7.125a4.125 4.125 0 0 0 0-8.25H6.75Zm0 0v8.25m0 0H6v8.25h7.875a4.125 4.125 0 0 0 0-8.25H6.75Z"/></svg>',
    },
    {
        label: 'Italic', isActive: () => editor.value?.isActive('italic') ?? false,
        action: () => editor.value?.chain().focus().toggleItalic().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.248 20.246H9.05m0 0h3.696m-3.696 0 5.893-16.502m0 0h-3.697m3.697 0h3.803"/></svg>',
    },
    {
        label: 'Strike', isActive: () => editor.value?.isActive('strike') ?? false,
        action: () => editor.value?.chain().focus().toggleStrike().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12h8m-8 0H4m8 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm0 6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>',
    },
])

const toolbarGroup2 = computed(() => [
    { label: 'H2', isActive: () => editor.value?.isActive('heading', { level: 2 }) ?? false, action: () => editor.value?.chain().focus().toggleHeading({ level: 2 }).run() },
    { label: 'H3', isActive: () => editor.value?.isActive('heading', { level: 3 }) ?? false, action: () => editor.value?.chain().focus().toggleHeading({ level: 3 }).run() },
    { label: 'H4', isActive: () => editor.value?.isActive('heading', { level: 4 }) ?? false, action: () => editor.value?.chain().focus().toggleHeading({ level: 4 }).run() },
])

const toolbarGroup3 = computed(() => [
    {
        label: 'Bullet List', isActive: () => editor.value?.isActive('bulletList') ?? false,
        action: () => editor.value?.chain().focus().toggleBulletList().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.008v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.008v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.008v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>',
    },
    {
        label: 'Ordered List', isActive: () => editor.value?.isActive('orderedList') ?? false,
        action: () => editor.value?.chain().focus().toggleOrderedList().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 0 1 1.909.13 1.125 1.125 0 0 1 0 1.076A1.125 1.125 0 0 1 3.126 12m-1.5 0h1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125H3.75a1.125 1.125 0 0 1-1.125-1.125v-1.5c0-.621.504-1.125 1.125-1.125Z"/></svg>',
    },
    {
        label: 'Blockquote', isActive: () => editor.value?.isActive('blockquote') ?? false,
        action: () => editor.value?.chain().focus().toggleBlockquote().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>',
    },
    {
        label: 'Code Block', isActive: () => editor.value?.isActive('codeBlock') ?? false,
        action: () => editor.value?.chain().focus().toggleCodeBlock().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6.75 7.5 3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>',
    },
    {
        label: 'Horizontal Rule', isActive: () => false,
        action: () => editor.value?.chain().focus().setHorizontalRule().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>',
    },
])

// ── Sync form ──────────────────────────────────────────────────
function syncForm(content) {
    if (!content) return
    form.value = {
        title:            content.title            ?? '',
        body:             content.body             ?? '',
        meta_title:       content.meta_title       ?? '',
        meta_description: content.meta_description ?? '',
        status:           content.status           ?? 'draft',
    }
    editor.value?.commands.setContent(content.body ?? '')
}

// ── Validate ───────────────────────────────────────────────────
function validate() {
    errors.value = {}
    if (!form.value.title.trim()) errors.value.title = 'Judul wajib diisi.'
    if (!form.value.body || form.value.body === '<p></p>') errors.value.body = 'Konten wajib diisi.'
    return Object.keys(errors.value).length === 0
}

// ── Submit ─────────────────────────────────────────────────────
async function handleSubmit() {
    if (!validate()) { window.scrollTo({ top: 0, behavior: 'smooth' }); return }
    successMessage.value = ''
    try {
        const payload = { ...form.value, type: pageType.value }
        if (store.currentItem) {
            await store.update(store.currentItem.id, payload)
        } else {
            await store.create(payload)
        }
        successMessage.value = 'Perubahan berhasil disimpan.'
        window.scrollTo({ top: 0, behavior: 'smooth' })
    } catch (e) {
        if (e.response?.status === 422) Object.assign(errors.value, e.response.data.errors ?? {})
    }
}

async function onTogglePublish() {
    if (!store.currentItem) return
    await store.togglePublish(store.currentItem.id)
}

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

// ── Load ───────────────────────────────────────────────────────
onMounted(async () => {
    store.currentItem = null
    await store.fetchStatic(pageType.value)
    syncForm(store.currentItem)
})

watch(() => route.params.type, async (newType) => {
    if (!newType) return
    successMessage.value = ''
    errors.value = {}
    form.value = { title: '', body: '', meta_title: '', meta_description: '', status: 'draft' }
    editor.value?.commands.setContent('')
    store.currentItem = null
    await store.fetchStatic(newType)
    syncForm(store.currentItem)
})
</script>

<style>
.tiptap-editor .ProseMirror {
    outline: none;
    min-height: 500px;
    font-size: 0.9375rem;
    line-height: 1.75;
    color: #1f2937;
}
.tiptap-editor .ProseMirror h2 {
    font-size: 1.5rem; font-weight: 700; color: #111827;
    margin: 1.75rem 0 0.75rem; padding-bottom: 0.5rem;
    border-bottom: 2px solid #f3f4f6;
}
.tiptap-editor .ProseMirror h3 {
    font-size: 1.2rem; font-weight: 700; color: #111827;
    margin: 1.5rem 0 0.5rem;
}
.tiptap-editor .ProseMirror h4 {
    font-size: 1rem; font-weight: 700; color: #374151;
    margin: 1.25rem 0 0.5rem;
}
.tiptap-editor .ProseMirror p { margin-bottom: 0.85rem; }
.tiptap-editor .ProseMirror p:last-child { margin-bottom: 0; }
.tiptap-editor .ProseMirror ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 0.85rem; }
.tiptap-editor .ProseMirror ol { list-style: decimal; padding-left: 1.5rem; margin-bottom: 0.85rem; }
.tiptap-editor .ProseMirror li { margin-bottom: 0.3rem; }
.tiptap-editor .ProseMirror blockquote {
    border-left: 3px solid #ED1F24;
    padding: 0.5rem 0 0.5rem 1rem;
    color: #6b7280; font-style: italic;
    margin: 1rem 0; background: rgba(237,31,36,0.03);
    border-radius: 0 8px 8px 0;
}
.tiptap-editor .ProseMirror code {
    background: #f3f4f6; color: #ED1F24;
    padding: 0.15em 0.4em; border-radius: 4px;
    font-size: 0.85em; font-family: monospace;
}
.tiptap-editor .ProseMirror pre {
    background: #1f2937; color: #f9fafb;
    padding: 1rem 1.25rem; border-radius: 12px;
    margin: 1rem 0; overflow-x: auto;
}
.tiptap-editor .ProseMirror pre code { background: none; color: inherit; padding: 0; }
.tiptap-editor .ProseMirror hr { border: none; border-top: 2px solid #f3f4f6; margin: 1.5rem 0; }
.tiptap-editor .ProseMirror a { color: #ED1F24; text-decoration: underline; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-8px); }
</style>