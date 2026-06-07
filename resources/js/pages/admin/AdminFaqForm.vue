<template>
    <AdminLayout title="">

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
                    <RouterLink to="/admin/faqs"
                        class="flex items-center gap-1.5 text-xs font-semibold text-red-200 hover:text-white transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                        </svg>
                        FAQ
                    </RouterLink>
                    <span class="text-red-300/50">/</span>
                    <div>
                        <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-0.5">Manajemen Konten</p>
                        <h1 class="text-2xl font-bold text-white tracking-tight">
                            {{ isEditing ? 'Edit FAQ' : 'FAQ Baru' }}
                        </h1>
                        <p class="text-red-200 text-xs mt-1">
                            {{ isEditing ? 'Perbarui pertanyaan dan jawaban' : 'Tambahkan pertanyaan baru ke halaman publik' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <!-- Status pill -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border border-white/20"
                          :class="form.is_active ? 'bg-emerald-500/20 text-emerald-100' : 'bg-white/10 text-red-100'">
                        <span class="w-1.5 h-1.5 rounded-full" :class="form.is_active ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"/>
                        {{ form.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>

                    <!-- Save -->
                    <button @click="handleSubmit" :disabled="saving"
                        class="flex items-center gap-2 text-sm font-semibold px-5 py-2.5 rounded-xl border border-white/30 bg-white text-[#ED1F24] hover:bg-red-50 transition-all disabled:opacity-50 shadow-sm">
                        <svg v-if="saving" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        {{ saving ? 'Menyimpan...' : 'Simpan FAQ' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ── SUCCESS BANNER ─────────────────────────────────── -->
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

        <!-- ── MAIN LAYOUT ─────────────────────────────────────── -->
        <div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-6">

            <!-- LEFT COLUMN -->
            <div class="space-y-5 min-w-0">

                <!-- QUESTION -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-6 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pertanyaan</span>
                        <span class="text-xs font-bold" style="color: #ED1F24;">*</span>
                    </div>
                    <div class="px-6 pt-5 pb-4">
                        <textarea
                            v-model="form.question"
                            ref="questionRef"
                            @input="autoResize('questionRef')"
                            rows="2"
                            placeholder="Tulis pertanyaan yang sering ditanyakan..."
                            class="w-full text-xl font-bold text-gray-900 bg-transparent border-0 focus:outline-none resize-none placeholder-gray-200 leading-snug"
                            style="min-height: 56px;"
                        />
                        <p v-if="errors.question" class="text-xs mt-1 font-semibold" style="color: #ED1F24;">{{ errors.question }}</p>
                    </div>
                </div>

                <!-- ANSWER -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-6 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Jawaban</span>
                        <span class="text-xs font-bold" style="color: #ED1F24;">*</span>
                    </div>

                    <!-- Toolbar -->
                    <div v-if="editor" class="flex flex-wrap items-center gap-0.5 px-4 py-2.5 border-b border-gray-100 bg-gray-50/40">
                        <div class="flex items-center gap-0.5 pr-2 mr-2 border-r border-gray-200">
                            <button v-for="btn in toolbarBasic" :key="btn.label"
                                @click="btn.action()" :title="btn.label" type="button"
                                class="w-8 h-8 flex items-center justify-center rounded-xl transition-all"
                                :style="btn.isActive() ? 'background: #ED1F24; color: #fff;' : 'color: #6b7280;'"
                                @mouseenter="e => { if (!btn.isActive()) e.currentTarget.style.background = '#f3f4f6' }"
                                @mouseleave="e => { if (!btn.isActive()) e.currentTarget.style.background = 'transparent' }">
                                <span v-html="btn.icon" class="w-3.5 h-3.5 flex items-center justify-center"/>
                            </button>
                        </div>
                        <div class="flex items-center gap-0.5">
                            <button v-for="btn in toolbarList" :key="btn.label"
                                @click="btn.action()" :title="btn.label" type="button"
                                class="w-8 h-8 flex items-center justify-center rounded-xl transition-all"
                                :style="btn.isActive() ? 'background: #ED1F24; color: #fff;' : 'color: #6b7280;'"
                                @mouseenter="e => { if (!btn.isActive()) e.currentTarget.style.background = '#f3f4f6' }"
                                @mouseleave="e => { if (!btn.isActive()) e.currentTarget.style.background = 'transparent' }">
                                <span v-html="btn.icon" class="w-3.5 h-3.5 flex items-center justify-center"/>
                            </button>
                        </div>
                    </div>

                    <div class="relative" :class="errors.answer ? 'ring-2 ring-inset ring-red-300' : ''">
                        <editor-content
                            :editor="editor"
                            class="faq-editor px-8 py-6 min-h-[200px] focus:outline-none cursor-text"
                            @click="editor?.commands.focus()"
                        />
                        <div v-if="!form.answer || form.answer === '<p></p>'"
                             class="absolute top-6 left-8 pointer-events-none text-gray-300 text-sm select-none">
                            Tulis jawaban yang jelas dan informatif...
                        </div>
                    </div>
                    <p v-if="errors.answer" class="px-6 pb-3 text-xs font-semibold" style="color: #ED1F24;">{{ errors.answer }}</p>
                </div>

            </div>

            <!-- RIGHT SIDEBAR -->
            <div class="space-y-4">

                <!-- SETTINGS CARD -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pengaturan</span>
                    </div>

                    <div class="p-5 space-y-4">
                        <!-- Category -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Kategori</label>
                            <div class="flex gap-2">
                                <input v-model="form.category" list="cat-list" type="text"
                                    placeholder="cth: Umum, Produk, Pembayaran..."
                                    class="flex-1 text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none text-gray-700 placeholder-gray-300 bg-gray-50/50"
                                    @focus="e => e.target.style.borderColor = '#ED1F24'"
                                    @blur="e => e.target.style.borderColor = '#e5e7eb'"
                                />
                                <datalist id="cat-list">
                                    <option v-for="cat in faqStore.categories" :key="cat" :value="cat"/>
                                </datalist>
                            </div>
                            <p class="text-xs text-gray-400 mt-1.5">Ketik baru atau pilih yang sudah ada</p>
                        </div>

                        <!-- Order -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Urutan Tampil</label>
                            <input v-model.number="form.order" type="number" min="0"
                                placeholder="0"
                                class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none text-gray-700 bg-gray-50/50"
                                @focus="e => e.target.style.borderColor = '#ED1F24'"
                                @blur="e => e.target.style.borderColor = '#e5e7eb'"
                            />
                            <p class="text-xs text-gray-400 mt-1.5">Angka kecil = tampil lebih dulu</p>
                        </div>

                        <!-- Status toggle -->
                        <div class="flex items-center justify-between py-1">
                            <div>
                                <p class="text-xs font-bold text-gray-600">Tampilkan di publik</p>
                                <p class="text-xs text-gray-400 mt-0.5">FAQ akan muncul di halaman utama</p>
                            </div>
                            <button @click="form.is_active = !form.is_active" type="button"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200"
                                :style="form.is_active ? 'background: #ED1F24;' : 'background: #d1d5db;'">
                                <span class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                                      :class="form.is_active ? 'translate-x-6' : 'translate-x-1'"/>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SAVE CARD -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="p-5 space-y-2">
                        <button @click="handleSubmit" :disabled="saving"
                            class="w-full py-2.5 text-sm font-bold text-white rounded-xl transition-all disabled:opacity-50 shadow-sm"
                            style="background: linear-gradient(135deg, #ED1F24, #b91c1c);">
                            {{ saving ? 'Menyimpan...' : (isEditing ? '💾 Simpan Perubahan' : '✅ Buat FAQ') }}
                        </button>
                        <RouterLink to="/admin/faqs"
                            class="block w-full py-2.5 text-sm font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all text-center">
                            Batal
                        </RouterLink>
                    </div>
                </div>

                <!-- INFO CARD (edit mode) -->
                <div v-if="isEditing && faqStore.currentItem" class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Info</span>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-400">Dibuat</span>
                            <span class="font-semibold text-gray-700">{{ formatDate(faqStore.currentItem.created_at) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-400">Diperbarui</span>
                            <span class="font-semibold text-gray-700">{{ formatDate(faqStore.currentItem.updated_at) }}</span>
                        </div>
                        <div v-if="faqStore.currentItem.creator" class="flex justify-between items-center text-xs">
                            <span class="text-gray-400">Dibuat oleh</span>
                            <div class="flex items-center gap-1.5">
                                <div class="w-5 h-5 rounded-full flex items-center justify-center text-white text-[9px] font-bold shrink-0"
                                     style="background: linear-gradient(135deg, #ED1F24, #7f1d1d);">
                                    {{ faqStore.currentItem.creator.name[0] }}
                                </div>
                                <span class="font-semibold text-gray-700">{{ faqStore.currentItem.creator.name }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-100 text-xs">
                            <span class="text-gray-400">ID</span>
                            <span class="font-mono font-semibold text-gray-400">#{{ faqStore.currentItem.id }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { useFaqStore } from '../../store/useFaqStore'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import { getUser, getPermissions } from '../../auth.js'

const route    = useRoute()
const router   = useRouter()
const faqStore = useFaqStore()
const isEditing   = computed(() => !!route.params.id)

const user        = computed(() => getUser() ?? {})
const permissions = computed(() => getPermissions())
const canPublish  = computed(() => permissions.value.includes('content.publish'))

// ── Form ──────────────────────────────────────────────────────
const form = ref({
    question:  '',
    answer:    '',
    category:  'Umum',
    order:     0,
    is_active: true,
})
const errors         = ref({})
const successMessage = ref('')
const saving         = ref(false)
const questionRef    = ref(null)

// ── Tiptap ────────────────────────────────────────────────────
const editor = useEditor({
    extensions: [StarterKit.configure({ heading: false })],
    content: '',
    editorProps: { attributes: { class: 'focus:outline-none' } },
    onUpdate: ({ editor: e }) => { form.value.answer = e.getHTML() },
})

onBeforeUnmount(() => editor.value?.destroy())

// ── Toolbar ───────────────────────────────────────────────────
const toolbarBasic = computed(() => [
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
        label: 'Code', isActive: () => editor.value?.isActive('code') ?? false,
        action: () => editor.value?.chain().focus().toggleCode().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/></svg>',
    },
])

const toolbarList = computed(() => [
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
])

// ── Helpers ───────────────────────────────────────────────────
function autoResize(refName) {
    const el = refName === 'questionRef' ? questionRef.value : null
    if (!el) return
    el.style.height = 'auto'
    el.style.height = el.scrollHeight + 'px'
}

function validate() {
    errors.value = {}
    if (!form.value.question.trim()) errors.value.question = 'Pertanyaan wajib diisi.'
    if (!form.value.answer || form.value.answer === '<p></p>') errors.value.answer = 'Jawaban wajib diisi.'
    return Object.keys(errors.value).length === 0
}

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    })
}

// ── Submit ─────────────────────────────────────────────────────
async function handleSubmit() {
    if (!validate()) { window.scrollTo({ top: 0, behavior: 'smooth' }); return }

    saving.value = true
    successMessage.value = ''

    try {
        if (isEditing.value) {
            await faqStore.update(route.params.id, form.value)
            successMessage.value = 'FAQ berhasil diperbarui.'
            window.scrollTo({ top: 0, behavior: 'smooth' })
        } else {
            const created = await faqStore.create(form.value)
            router.push(`/admin/faqs/${created.id}/edit`)
        }
    } catch (e) {
        if (e.response?.status === 422) {
            Object.assign(errors.value, e.response.data.errors ?? {})
        }
    } finally {
        saving.value = false
    }
}

// ── Load ───────────────────────────────────────────────────────
onMounted(async () => {
    document.title = 'Add FAQ - Two Brothers Vape System'
    await faqStore.fetchCategories()

    if (isEditing.value) {
        await faqStore.fetchOne(route.params.id)
        const c = faqStore.currentItem
        if (c) {
            Object.assign(form.value, {
                question:  c.question,
                answer:    c.answer,
                category:  c.category ?? 'Umum',
                order:     c.order ?? 0,
                is_active: c.is_active,
            })
            await nextTick()
            editor.value?.commands.setContent(c.answer ?? '')
            autoResize('questionRef')
        }
    }
})
</script>

<style>
.faq-editor .ProseMirror {
    outline: none;
    min-height: 200px;
    font-size: 0.9375rem;
    line-height: 1.75;
    color: #1f2937;
}
.faq-editor .ProseMirror p { margin-bottom: 0.75rem; }
.faq-editor .ProseMirror p:last-child { margin-bottom: 0; }
.faq-editor .ProseMirror ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 0.75rem; }
.faq-editor .ProseMirror ol { list-style: decimal; padding-left: 1.5rem; margin-bottom: 0.75rem; }
.faq-editor .ProseMirror li { margin-bottom: 0.3rem; }
.faq-editor .ProseMirror code {
    background: #f3f4f6; color: #ED1F24;
    padding: 0.15em 0.4em; border-radius: 4px;
    font-size: 0.85em; font-family: monospace;
}
.faq-editor .ProseMirror strong { font-weight: 700; }
.faq-editor .ProseMirror em { font-style: italic; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-8px); }
</style>