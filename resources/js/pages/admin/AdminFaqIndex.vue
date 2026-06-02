<template>
    <AdminLayout title="Manajemen FAQ">

        <!-- ═══════════════════════════════════════════
             HERO HEADER
        ═══════════════════════════════════════════ -->
        <div class="relative mb-6 rounded-2xl overflow-hidden"
             style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"/>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"/>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10" style="background: white;"/>

            <div class="relative px-7 py-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-0.5">Manajemen Konten</p>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Frequently Asked Questions</h1>
                    <p class="text-red-200 text-xs mt-1">Kelola pertanyaan & jawaban yang tampil di halaman publik</p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border border-white/20 bg-white/10 text-red-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"/>
                        {{ faqStore.pagination.total }} FAQ
                    </span>
                    <RouterLink v-if="canManage" to="/admin/faqs/create"
                        class="flex items-center gap-2 text-sm font-semibold px-5 py-2.5 rounded-xl border border-white/30 bg-white text-[#ED1F24] hover:bg-red-50 transition-all shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Tambah FAQ
                    </RouterLink>
                </div>
            </div>

            <!-- Stats strip -->
            <div class="relative border-t border-white/10 px-7 py-3 flex flex-wrap items-center gap-6">
                <div>
                    <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Total</p>
                    <p class="text-white text-sm font-bold tabular-nums">{{ faqStore.pagination.total }}</p>
                </div>
                <div class="w-px h-8 bg-white/15"/>
                <div>
                    <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Aktif</p>
                    <p class="text-white text-sm font-bold tabular-nums">{{ activeCount }}</p>
                </div>
                <div class="w-px h-8 bg-white/15"/>
                <div>
                    <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Kategori</p>
                    <p class="text-white text-sm font-bold tabular-nums">{{ faqStore.categories.length }}</p>
                </div>
            </div>
        </div>

        <!-- ── FILTERS ─────────────────────────────────────────── -->
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-center">
            <!-- Search -->
            <div class="relative flex-1 min-w-48">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <input v-model="filters.search" @input="debouncedFetch" type="text"
                    placeholder="Cari pertanyaan atau jawaban..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none text-gray-700 placeholder-gray-300 bg-gray-50/50"
                    @focus="e => e.target.style.borderColor = '#ED1F24'"
                    @blur="e => e.target.style.borderColor = '#e5e7eb'"
                />
            </div>

            <!-- Category filter -->
            <select v-model="filters.category" @change="fetchData"
                class="text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none text-gray-600 bg-gray-50/50 cursor-pointer"
                @focus="e => e.target.style.borderColor = '#ED1F24'"
                @blur="e => e.target.style.borderColor = '#e5e7eb'">
                <option value="">Semua Kategori</option>
                <option v-for="cat in faqStore.categories" :key="cat" :value="cat">{{ cat }}</option>
            </select>

            <!-- Status filter -->
            <select v-model="filters.is_active" @change="fetchData"
                class="text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none text-gray-600 bg-gray-50/50 cursor-pointer"
                @focus="e => e.target.style.borderColor = '#ED1F24'"
                @blur="e => e.target.style.borderColor = '#e5e7eb'">
                <option value="">Semua Status</option>
                <option value="true">Aktif</option>
                <option value="false">Nonaktif</option>
            </select>

            <!-- Reset -->
            <button v-if="hasFilters" @click="resetFilters"
                class="text-xs font-semibold px-3 py-2.5 rounded-xl text-gray-500 hover:bg-gray-100 transition-all border border-gray-200">
                Reset
            </button>
        </div>

        <!-- ── TABLE ───────────────────────────────────────────── -->
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">

            <!-- Loading -->
            <div v-if="faqStore.loading" class="flex items-center justify-center py-20">
                <div class="w-8 h-8 rounded-full border-2 border-gray-200 animate-spin" style="border-top-color: #ED1F24;"/>
            </div>

            <!-- Empty -->
            <div v-else-if="!faqStore.faqs.length" class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background: rgba(237,31,36,0.08);">
                    <svg class="w-8 h-8" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/>
                    </svg>
                </div>
                <p class="text-gray-600 font-semibold text-sm mb-1">Belum ada FAQ</p>
                <p class="text-gray-400 text-xs mb-4">{{ hasFilters ? 'Coba ubah filter pencarian' : 'Mulai tambahkan FAQ pertama Anda' }}</p>
                <RouterLink v-if="canManage && !hasFilters" to="/admin/faqs/create"
                    class="text-xs font-bold px-4 py-2 rounded-xl text-white transition-all"
                    style="background: #ED1F24;">
                    + Tambah FAQ
                </RouterLink>
            </div>

            <!-- Table -->
            <table v-else class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100" style="background: #fafafa;">
                        <th class="text-left px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest w-8">#</th>
                        <th class="text-left px-4 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pertanyaan</th>
                        <th class="text-left px-4 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest hidden md:table-cell">Kategori</th>
                        <th class="text-left px-4 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest hidden lg:table-cell">Dibuat</th>
                        <th class="text-center px-4 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="text-right px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="(faq, i) in faqStore.faqs" :key="faq.id"
                        class="hover:bg-gray-50/60 transition-colors group">
                        <!-- Order number -->
                        <td class="px-6 py-4 text-gray-300 text-xs font-mono tabular-nums">{{ faq.order }}</td>

                        <!-- Question + excerpt answer -->
                        <td class="px-4 py-4 max-w-xs">
                            <p class="font-semibold text-gray-800 text-sm leading-snug line-clamp-2">{{ faq.question }}</p>
                            <p class="text-xs text-gray-400 mt-1 line-clamp-1">{{ stripHtml(faq.answer) }}</p>
                        </td>

                        <!-- Category -->
                        <td class="px-4 py-4 hidden md:table-cell">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                  style="background: rgba(237,31,36,0.08); color: #ED1F24;">
                                {{ faq.category || 'Umum' }}
                            </span>
                        </td>

                        <!-- Created at -->
                        <td class="px-4 py-4 hidden lg:table-cell">
                            <p class="text-xs text-gray-500 font-semibold">{{ formatDate(faq.created_at) }}</p>
                            <p v-if="faq.creator" class="text-xs text-gray-400">{{ faq.creator.name }}</p>
                        </td>

                        <!-- Status toggle -->
                        <td class="px-4 py-4 text-center">
                            <button v-if="canManage" @click="handleToggle(faq.id)"
                                :title="faq.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200 focus:outline-none"
                                :style="faq.is_active ? 'background: #22c55e;' : 'background: #d1d5db;'">
                                <span class="inline-block h-3.5 w-3.5 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                                      :class="faq.is_active ? 'translate-x-4' : 'translate-x-1'"/>
                            </button>
                            <span v-else
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                :class="faq.is_active
                                    ? 'bg-emerald-50 text-emerald-600'
                                    : 'bg-gray-100 text-gray-400'">
                                {{ faq.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <RouterLink v-if="canManage" :to="`/admin/faqs/${faq.id}/edit`"
                                    class="w-8 h-8 flex items-center justify-center rounded-xl text-gray-400 hover:text-[#ED1F24] hover:bg-red-50 transition-all"
                                    title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Z"/>
                                    </svg>
                                </RouterLink>
                                <button v-if="canManage" @click="confirmDelete(faq)"
                                    class="w-8 h-8 flex items-center justify-center rounded-xl text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all"
                                    title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="faqStore.pagination.last_page > 1"
                 class="flex items-center justify-between px-6 py-4 border-t border-gray-100" style="background: #fafafa;">
                <p class="text-xs text-gray-400 font-semibold">
                    Halaman {{ faqStore.pagination.current_page }} dari {{ faqStore.pagination.last_page }}
                    &nbsp;·&nbsp; {{ faqStore.pagination.total }} total
                </p>
                <div class="flex items-center gap-1.5">
                    <button @click="goToPage(faqStore.pagination.current_page - 1)"
                        :disabled="faqStore.pagination.current_page === 1"
                        class="w-8 h-8 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                        </svg>
                    </button>
                    <button @click="goToPage(faqStore.pagination.current_page + 1)"
                        :disabled="faqStore.pagination.current_page === faqStore.pagination.last_page"
                        class="w-8 h-8 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── DELETE CONFIRM MODAL ────────────────────────────── -->
        <transition name="fade">
            <div v-if="deleteTarget"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 style="background: rgba(0,0,0,0.4);"
                 @click.self="deleteTarget = null">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4 mx-auto" style="background: rgba(237,31,36,0.1);">
                        <svg class="w-6 h-6" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-800 text-center mb-2">Hapus FAQ?</h3>
                    <p class="text-sm text-gray-500 text-center mb-6 leading-relaxed">
                        "<span class="font-semibold text-gray-700">{{ deleteTarget.question }}</span>"
                        <br>akan dihapus secara permanen.
                    </p>
                    <div class="flex gap-3">
                        <button @click="deleteTarget = null"
                            class="flex-1 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">
                            Batal
                        </button>
                        <button @click="handleDelete" :disabled="deleting"
                            class="flex-1 py-2.5 text-sm font-bold text-white rounded-xl transition-all disabled:opacity-50"
                            style="background: #ED1F24;">
                            {{ deleting ? 'Menghapus...' : 'Ya, Hapus' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>

    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useFaqStore } from '../../store/useFaqStore'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import { getUser, getPermissions } from '../../auth.js'

const faqStore = useFaqStore()

const user        = computed(() => getUser() ?? {})
const permissions = computed(() => getPermissions())
const canManage   = computed(() =>
    permissions.value.includes('content.create') ||
    permissions.value.includes('content.publish')
)

const filters = ref({ search: '', category: '', is_active: '' })
const hasFilters = computed(() =>
    filters.value.search || filters.value.category || filters.value.is_active
)

const deleteTarget = ref(null)
const deleting     = ref(false)
const activeCount  = computed(() => faqStore.faqs.filter(f => f.is_active).length)

let debounceTimer = null
function debouncedFetch() {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(fetchData, 350)
}

async function fetchData(page = 1) {
    const params = { page, per_page: 15 }
    if (filters.value.search)    params.search    = filters.value.search
    if (filters.value.category)  params.category  = filters.value.category
    if (filters.value.is_active !== '') params.is_active = filters.value.is_active
    await faqStore.fetchAll(params)
}

function resetFilters() {
    filters.value = { search: '', category: '', is_active: '' }
    fetchData()
}

function goToPage(page) {
    if (page < 1 || page > faqStore.pagination.last_page) return
    fetchData(page)
}

async function handleToggle(id) {
    try { await faqStore.toggleActive(id) } catch { /* silent */ }
}

function confirmDelete(faq) { deleteTarget.value = faq }

async function handleDelete() {
    if (!deleteTarget.value) return
    deleting.value = true
    try {
        await faqStore.remove(deleteTarget.value.id)
        deleteTarget.value = null
    } finally {
        deleting.value = false
    }
}

function stripHtml(html) {
    return html?.replace(/<[^>]*>/g, '') ?? ''
}

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric'
    })
}

onMounted(async () => {
    await Promise.all([fetchData(), faqStore.fetchCategories()])
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>