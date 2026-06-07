<template>
    <AdminLayout title="Artikel & Blog">

        <!-- ═══════════════════════════════════════════
             HERO HEADER
        ═══════════════════════════════════════════ -->
        <div class="relative mb-6 rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"></div>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10" style="background: white;"></div>

            <div class="relative px-7 py-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">Manajemen Konten</p>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Artikel & Blog</h1>
                    <p class="text-red-200 text-xs mt-1.5">Kelola semua konten artikel dan publikasi</p>
                </div>
                <RouterLink
                    to="/admin/articles/create"
                    class="flex items-center gap-2 text-sm font-semibold px-4 py-2.5 rounded-xl border border-white/30 bg-white/15 text-white hover:bg-white/25 transition-all"
                >
                    <font-awesome-icon :icon="['fas', 'plus']" class="w-4 h-4" />
                    Tulis Artikel
                </RouterLink>
            </div>

            <!-- Stats strip -->
            <div class="relative border-t border-white/10 px-7 py-3 flex flex-wrap items-center gap-6">
                <template v-if="loadingStats">
                    <div v-for="i in 4" :key="i" class="w-16 h-8 rounded-lg bg-white/10 animate-pulse"></div>
                </template>
                <template v-else>
                    <div class="flex items-center gap-6">
                        <div>
                            <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Total</p>
                            <p class="text-white text-lg font-bold tabular-nums">{{ kpiData.total }}</p>
                        </div>
                        <div class="w-px h-8 bg-white/15"></div>
                        <div>
                            <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Published</p>
                            <p class="text-white text-lg font-bold tabular-nums">{{ kpiData.published }}</p>
                        </div>
                        <div class="w-px h-8 bg-white/15"></div>
                        <div>
                            <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Draft</p>
                            <p class="text-white text-lg font-bold tabular-nums">{{ kpiData.draft }}</p>
                        </div>
                        <div class="w-px h-8 bg-white/15"></div>
                        <div>
                            <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Bulan Ini</p>
                            <p class="text-white text-lg font-bold tabular-nums">{{ kpiData.thisMonth }}</p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ── KPI CARDS ──────────────────────────────────────────────── -->
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
            <template v-if="loadingStats">
                <div v-for="i in 4" :key="i" class="bg-white rounded-2xl p-5 animate-pulse h-24 border border-gray-200/80 shadow-sm"/>
            </template>
            <template v-else>
                <div v-for="card in kpiCards" :key="card.label"
                     class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition-shadow duration-200">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center text-lg shrink-0" :style="{ background: card.iconBg }">
                        <font-awesome-icon :icon="['fas', card.icon]" class="text-lg" :style="{ color: card.iconColor }" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">{{ card.label }}</p>
                        <p class="text-xl font-bold" :class="card.highlight ? 'text-[#ED1F24]' : 'text-gray-800'">{{ card.value }}</p>
                    </div>
                </div>
            </template>
        </div>

        <!-- ── FILTER BAR ─────────────────────────────────────────────── -->
        <div class="bg-white border border-gray-200/80 rounded-xl shadow-sm px-5 py-4 mb-4 flex flex-wrap gap-4 items-end">

            <!-- Search -->
            <div class="flex-1 min-w-52">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Cari Artikel</label>
                <div class="relative">
                    <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        @input="onSearch"
                        type="text"
                        placeholder="Cari judul..."
                        class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-700 placeholder-gray-300 bg-gray-50/50 focus:outline-none focus:border-[#ED1F24] transition-colors"
                    />
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Status</label>
                <div class="flex gap-1.5">
                    <button
                        v-for="s in statusOptions" :key="s.value"
                        @click="selectedStatus = s.value; load()"
                        class="text-xs px-3 py-2 rounded-xl font-semibold border transition-all"
                        :class="selectedStatus === s.value
                            ? 'bg-[#ED1F24] text-white border-[#ED1F24] shadow-sm'
                            : 'bg-white text-gray-500 border-gray-200 hover:border-gray-300 hover:text-gray-700'"
                    >{{ s.label }}</button>
                </div>
            </div>

            <!-- Per page -->
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Per Halaman</label>
                <select
                    v-model="perPage"
                    @change="load"
                    class="text-sm border border-gray-200 rounded-xl px-3 py-2.5 text-gray-700 bg-gray-50/50 focus:outline-none focus:border-[#ED1F24] transition-colors"
                >
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                </select>
            </div>
        </div>

        <!-- ── TABLE ──────────────────────────────────────────────────── -->
        <div class="bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden">

            <!-- Table header bar -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Daftar Artikel</h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <template v-if="!store.isLoading && store.meta?.total">
                            {{ store.meta.total }} artikel ditemukan
                        </template>
                        <template v-else>Memuat data...</template>
                    </p>
                </div>
                <button @click="load"
                        class="flex items-center gap-1.5 text-xs font-semibold text-gray-400 hover:text-gray-600 border border-gray-200 hover:border-gray-300 px-3 py-1.5 rounded-xl transition-all">
                    <font-awesome-icon :icon="['fas', 'rotate-right']" :class="['w-3.5 h-3.5', store.isLoading && 'animate-spin']" />
                    Refresh
                </button>
            </div>

            <!-- Loading skeleton -->
            <div v-if="store.isLoading" class="p-6 space-y-3">
                <div v-for="i in 5" :key="i" class="h-14 bg-gray-100 rounded-xl animate-pulse"/>
            </div>

            <!-- Empty -->
            <div v-else-if="!store.items.length" class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 rounded-2xl bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-500">Belum ada artikel</p>
                <p class="text-xs text-gray-400 mt-1">Klik "Tulis Artikel" untuk mulai membuat konten</p>
                <RouterLink to="/admin/articles/create"
                            class="mt-4 flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-xl text-white bg-[#ED1F24] hover:bg-[#C81A1E] transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Buat Sekarang
                </RouterLink>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm min-w-[720px]">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Artikel</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 hidden md:table-cell">Penulis</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 hidden lg:table-cell">Tags</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 hidden sm:table-cell">Tanggal</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400">Status</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="article in store.items" :key="article.id"
                            class="hover:bg-gray-50/60 transition-colors duration-150">

                            <!-- Title + thumbnail -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl overflow-hidden shrink-0 bg-gray-100 flex items-center justify-center border border-gray-200">
                                        <img v-if="article.thumbnail" :src="article.thumbnail" :alt="article.title" class="w-full h-full object-cover"/>
                                        <svg v-else class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-800 truncate max-w-xs text-sm">{{ article.title }}</p>
                                        <p class="text-xs text-gray-400 font-mono truncate">/{{ article.slug }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Author -->
                            <td class="px-6 py-4 hidden md:table-cell">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white shrink-0"
                                         style="background: linear-gradient(135deg, #ED1F24, #7f1d1d);">
                                        {{ (article.author?.name || '?')[0].toUpperCase() }}
                                    </div>
                                    <span class="text-sm text-gray-600">{{ article.author?.name ?? '—' }}</span>
                                </div>
                            </td>

                            <!-- Tags -->
                            <td class="px-6 py-4 hidden lg:table-cell">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="tag in (article.tags || []).slice(0, 2)" :key="tag"
                                          class="text-[10px] font-bold px-2 py-0.5 rounded-full border"
                                          style="background: rgba(237,31,36,0.06); color: #ED1F24; border-color: rgba(237,31,36,0.15);">
                                        {{ tag }}
                                    </span>
                                    <span v-if="(article.tags || []).length > 2"
                                          class="text-[10px] text-gray-400">+{{ article.tags.length - 2 }}</span>
                                    <span v-if="!(article.tags || []).length" class="text-xs text-gray-300">—</span>
                                </div>
                            </td>

                            <!-- Date -->
                            <td class="px-6 py-4 hidden sm:table-cell">
                                <span class="text-xs text-gray-400">{{ formatDate(article.published_at ?? article.created_at) }}</span>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border"
                                      :class="article.status === 'published'
                                          ? 'bg-emerald-50 border-emerald-100 text-emerald-600'
                                          : 'bg-gray-100 border-gray-200 text-gray-400'">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                          :class="article.status === 'published' ? 'bg-emerald-400' : 'bg-gray-300'"/>
                                    {{ article.status === 'published' ? 'Published' : 'Draft' }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Publish toggle -->
                                    <button v-if="article.can?.publish"
                                            @click="onTogglePublish(article)"
                                            :title="article.status === 'published' ? 'Jadikan Draft' : 'Publish'"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-amber-500 hover:bg-amber-50 transition-all">
                                        <font-awesome-icon :icon="['fas', 'sun']" class="w-4 h-4" />
                                    </button>
                                    <!-- Edit -->
                                    <RouterLink v-if="article.can?.update"
                                                :to="`/admin/articles/${article.id}/edit`"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-[#ED1F24] hover:bg-[#ED1F24]/8 transition-all"
                                                title="Edit">
                                        <font-awesome-icon icon="edit" class="w-4 h-4" />
                                    </RouterLink>
                                    <!-- Delete -->
                                    <button v-if="article.can?.delete"
                                            @click="onDelete(article)"
                                            title="Hapus"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="store.meta?.last_page > 1"
                 class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                <p class="text-xs text-gray-400">
                    Menampilkan {{ store.meta.from }}–{{ store.meta.to }} dari {{ store.meta.total }} artikel
                </p>
                <div class="flex gap-1">
                    <button v-for="page in visiblePages" :key="page"
                            @click="goToPage(page)"
                            :disabled="page === '...'"
                            class="w-8 h-8 text-xs font-semibold rounded-xl transition-all border"
                            :class="currentPage === page
                                ? 'bg-[#ED1F24] text-white border-[#ED1F24] shadow-sm'
                                : page === '...'
                                    ? 'text-gray-300 border-transparent cursor-default'
                                    : 'text-gray-500 border-gray-200 hover:bg-gray-100 hover:border-gray-300'">
                        {{ page }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ── DELETE DIALOG ───────────────────────────────────────────── -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="deleteTarget"
                     class="fixed inset-0 z-50 flex items-center justify-center p-4"
                     @click.self="deleteTarget = null">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"/>
                    <div class="relative bg-white border border-gray-200/80 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
                        <div class="p-6">
                            <div class="w-11 h-11 rounded-xl bg-red-50 border border-red-200 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800 text-center mb-1">Hapus Artikel?</h3>
                            <p class="text-sm text-gray-400 text-center">"{{ deleteTarget.title }}" akan dipindahkan ke trash.</p>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <button @click="deleteTarget = null"
                                    class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-xl transition-all">
                                Batal
                            </button>
                            <button @click="confirmDelete"
                                    class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </AdminLayout>
</template>

<script>
import AdminLayout from '../../components/admin/AdminLayout.vue'
import { useContentStore } from '../../store/useContentStore'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { useDebounceFn } from '@vueuse/core'

export default {
    name: 'ArticleList',
    components: { AdminLayout },

    setup() {
        const store = useContentStore()
        return { store }
    },

    data() {
        return {
            searchQuery:    '',
            selectedStatus: '',
            perPage:        15,
            currentPage:    1,
            deleteTarget:   null,
            loadingStats:   true,
            kpiData:        { total: 0, published: 0, draft: 0, thisMonth: 0 },
            statusOptions: [
                { label: 'Semua',     value: '' },
                { label: 'Published', value: 'published' },
                { label: 'Draft',     value: 'draft' },
            ],
        }
    },

    computed: {
        kpiCards() {
            return [
                { label: 'Total Artikel', value: this.kpiData.total,     icon: 'newspaper',     iconBg: 'rgba(237,31,36,0.08)',  iconColor: '#ED1F24', highlight: true },
                { label: 'Published',     value: this.kpiData.published, icon: 'circle-check',  iconBg: 'rgba(34,197,94,0.08)',  iconColor: '#22c55e' },
                { label: 'Draft',         value: this.kpiData.draft,     icon: 'file-alt',      iconBg: 'rgba(245,158,11,0.08)', iconColor: '#f59e0b' },
                { label: 'Bulan Ini',     value: this.kpiData.thisMonth, icon: 'calendar-days', iconBg: 'rgba(59,130,246,0.08)', iconColor: '#3b82f6' },
            ]
        },
        visiblePages() {
            const total = this.store.meta?.last_page ?? 1
            const cur   = this.currentPage
            const pages = []
            for (let i = 1; i <= total; i++) {
                if (i === 1 || i === total || (i >= cur - 1 && i <= cur + 1)) pages.push(i)
                else if (pages[pages.length - 1] !== '...') pages.push('...')
            }
            return pages
        },
    },

    methods: {
        async load() {
            await this.store.fetchList({
                type:     'article',
                status:   this.selectedStatus,
                search:   this.searchQuery,
                per_page: this.perPage,
                page:     this.currentPage,
            })
            if (this.store.meta) {
                this.kpiData.total = this.store.meta.total ?? 0
            }
        },

        onSearch: useDebounceFn(function () {
            this.currentPage = 1
            this.load()
        }, 400),

        goToPage(page) {
            if (page === '...' || page === this.currentPage) return
            this.currentPage = page
            this.load()
        },

        async onTogglePublish(article) {
            await this.store.togglePublish(article.id)
        },

        onDelete(article) {
            this.deleteTarget = article
        },

        async confirmDelete() {
            await this.store.remove(this.deleteTarget.id)
            this.deleteTarget = null
        },

        formatDate(d) {
            if (!d) return '—'
            return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        },
    },

    async mounted() {
        document.title = 'Articles List - Two Brothers Vape System'

        const [all, pub, dft] = await Promise.all([
            this.store.fetchList({ type: 'article', per_page: 1, page: 1 }),
            this.store.fetchList({ type: 'article', status: 'published', per_page: 1, page: 1 }),
            this.store.fetchList({ type: 'article', status: 'draft', per_page: 1, page: 1 }),
        ])

        this.kpiData.total     = all?.meta?.total ?? 0
        this.kpiData.published = pub?.meta?.total ?? 0
        this.kpiData.draft     = dft?.meta?.total ?? 0

        await this.load()
        this.loadingStats = false
    },
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>