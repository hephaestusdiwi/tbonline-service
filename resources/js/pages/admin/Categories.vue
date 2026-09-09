<template>
    <AdminLayout title="Category Management">

        <!-- ═══════════════════════════════════════════
             HERO HEADER
        ═══════════════════════════════════════════ -->
        <div class="relative mb-6 rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"></div>

            <div class="relative px-7 py-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">Content Management</p>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Category Management</h1>
                    <p class="text-red-200 text-xs mt-1.5">Kelola list kategori produk yang tampil di halaman utama</p>
                </div>
                <button @click="openModal('create')"
                        class="flex items-center gap-2 text-sm font-semibold px-4 py-2.5 rounded-xl border border-white/30 bg-white/15 text-white hover:bg-white/25 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Kategori
                </button>
            </div>

            <div class="relative border-t border-white/10 px-7 py-3 flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-6">
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Total</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ categories.length }}</p>
                    </div>
                    <div class="w-px h-8 bg-white/15"></div>
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Aktif</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ activeCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white border border-gray-200/80 rounded-xl shadow-sm px-4 py-3 mb-4 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input v-model="search" type="text" placeholder="Cari kategori..."
                       class="w-full pl-9 pr-4 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:border-[#ED1F24] transition-colors" />
            </div>
            <p class="text-xs text-gray-400 ml-auto">Drag ikon <span class="font-semibold">⠿</span> di kiri buat urutin</p>
        </div>

        <!-- TABLE -->
        <div class="bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-4 py-3 w-10"></th>
                            <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-4 py-3 w-24">Foto</th>
                            <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-4 py-3">Nama Kategori</th>
                            <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-4 py-3">Status</th>
                            <th class="text-right text-[10px] font-bold uppercase tracking-widest text-gray-400 px-4 py-3 w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody ref="sortableTable" class="divide-y divide-gray-50">

                        <tr v-if="filteredCategories.length === 0">
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-500">Belum ada kategori</p>
                                    <p class="text-xs text-gray-400">Klik "+ Tambah Kategori" untuk membuat kategori baru</p>
                                </div>
                            </td>
                        </tr>

                        <tr v-for="cat in filteredCategories" :key="cat.id" :data-id="cat.id"
                            class="hover:bg-gray-50/60 transition-colors duration-150 group">
                            <td class="px-4 py-3">
                                <div class="cat-drag-handle flex items-center justify-center w-7 h-7 rounded-lg cursor-grab text-gray-300 hover:text-gray-500 hover:bg-gray-100 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="5" r="1.2"/><circle cx="9" cy="12" r="1.2"/><circle cx="9" cy="19" r="1.2"/><circle cx="15" cy="5" r="1.2"/><circle cx="15" cy="12" r="1.2"/><circle cx="15" cy="19" r="1.2"/></svg>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="w-14 h-14 rounded-lg overflow-hidden border border-gray-200 bg-gray-100 flex-shrink-0">
                                    <img v-if="cat.photo_url" :src="cat.photo_url" class="w-full h-full object-cover" />
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-gray-700">{{ cat.name }}</span>
                                <p class="text-[11px] text-gray-400 mt-0.5">
                                    {{ (cat.product_categories || []).join(', ') || '—' }}
                                </p>
                            </td>

                            <td class="px-4 py-3">
                                <button @click="toggleActive(cat)"
                                        class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-xl border transition-all"
                                        :class="cat.is_active
                                            ? 'bg-emerald-50 border-emerald-100 text-emerald-600 hover:bg-emerald-100'
                                            : 'bg-gray-100 border-gray-200 text-gray-400 hover:bg-gray-200'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="cat.is_active ? 'bg-emerald-400' : 'bg-gray-300'"></span>
                                    {{ cat.is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openModal('edit', cat)"
                                            class="flex items-center gap-1 text-xs font-semibold text-[#ED1F24] border border-[#ED1F24]/20 hover:border-[#ED1F24]/40 hover:bg-red-50 px-2.5 py-1.5 rounded-lg transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    <button @click="deleteCategory(cat.id)"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 text-xs text-gray-400 text-center">
            Menampilkan {{ filteredCategories.length }} dari {{ categories.length }} kategori
        </div>

        <!-- ═══════════════════════════════════
             MODAL: Tambah / Edit
        ═══════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showModal"
                     class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                     @click.self="closeModal">
                    <div class="bg-white border border-gray-200/80 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col">

                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                                 :class="modalMode === 'create'
                                     ? 'bg-[#ED1F24]/8 border border-[#ED1F24]/15'
                                     : 'bg-amber-50 border border-amber-100'">
                                <svg v-if="modalMode === 'create'" class="w-4 h-4 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <svg v-else class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800">{{ modalMode === 'create' ? 'Tambah Kategori Baru' : 'Edit Kategori' }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5">{{ modalMode === 'create' ? 'Isi detail untuk menambahkan kategori baru' : 'Ubah informasi kategori yang dipilih' }}</p>
                            </div>
                            <button @click="closeModal"
                                    class="ml-auto w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-700 flex items-center justify-center transition-all flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <div v-if="errorMessage" class="mx-6 mt-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-sm">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ errorMessage }}
                        </div>

                        <!-- Form Body — scrollable -->
                        <div class="p-6 space-y-5 overflow-y-auto flex-1">

                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                                    Nama Tampilan <span class="text-[#ED1F24]">*</span>
                                </label>
                                <input v-model="form.name" type="text"
                                       placeholder="Contoh: Liquid"
                                       class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors" />
                                <p class="text-[10px] text-gray-400 mt-1.5">
                                    Label yang tampil di tile homepage — bebas, nggak harus sama persis dengan nama kategori produk.
                                </p>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                                    Kategori Produk yang Termasuk <span class="text-[#ED1F24]">*</span>
                                </label>
                                <div class="flex flex-wrap gap-2 p-3 bg-gray-50/50 border border-gray-200 rounded-xl max-h-40 overflow-y-auto">
                                    <button
                                        v-for="pc in productCategories" :key="pc"
                                        type="button"
                                        @click="toggleProductCategory(pc)"
                                        class="text-xs font-semibold px-3 py-1.5 rounded-lg border transition-all"
                                        :class="form.product_categories.includes(pc)
                                            ? 'bg-[#ED1F24] border-[#ED1F24] text-white'
                                            : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300'">
                                        {{ pc }}
                                    </button>
                                    <p v-if="productCategories.length === 0" class="text-xs text-gray-400 italic">
                                        Belum ada kategori produk terdeteksi.
                                    </p>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1.5">
                                    Pilih 1 atau lebih — semua produk dari kategori yang dipilih bakal muncul kalau tile ini diklik.
                                    {{ form.product_categories.length }} dipilih.
                                </p>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                                    Foto Kategori
                                    <span v-if="modalMode === 'edit'" class="normal-case font-normal text-gray-400 ml-1">— kosongkan jika tidak diganti</span>
                                    <span v-else class="text-[#ED1F24]"> *</span>
                                </label>
                                <div class="relative rounded-xl border-2 border-dashed transition-all duration-200 cursor-pointer"
                                     :class="isDragging
                                         ? 'border-[#ED1F24] bg-red-50 scale-[1.01]'
                                         : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50/50'"
                                     @dragover.prevent="isDragging = true"
                                     @dragleave="isDragging = false"
                                     @drop.prevent="handleDrop"
                                     @click="$refs.fileInput.click()">
                                    <input ref="fileInput" type="file" accept="image/*" @change="handleFileChange" class="hidden" />

                                    <div v-if="!previewUrl" class="flex flex-col items-center justify-center gap-3 py-8 px-4">
                                        <div class="w-11 h-11 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-sm text-gray-500"><span class="text-[#ED1F24] font-semibold">Pilih file</span> atau drag &amp; drop</p>
                                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP — persegi lebih pas buat tile</p>
                                        </div>
                                    </div>

                                    <div v-else class="relative p-3">
                                        <img :src="previewUrl" class="w-32 h-32 mx-auto object-cover rounded-lg" />
                                        <button @click.stop="clearFile"
                                                class="absolute top-5 right-5 w-7 h-7 rounded-full bg-white border border-gray-200 shadow-sm text-gray-500 hover:text-red-500 hover:border-red-200 flex items-center justify-center transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="modalMode === 'edit'">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Status</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button @click="form.is_active = true"
                                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-semibold transition-all"
                                            :class="form.is_active
                                                ? 'bg-emerald-50 border-emerald-200 text-emerald-600'
                                                : 'bg-white border-gray-200 text-gray-400 hover:border-gray-300'">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        Aktif
                                    </button>
                                    <button @click="form.is_active = false"
                                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-semibold transition-all"
                                            :class="!form.is_active
                                                ? 'bg-red-50 border-red-200 text-red-500'
                                                : 'bg-white border-gray-200 text-gray-400 hover:border-gray-300'">
                                        <span class="w-2 h-2 rounded-full bg-red-400"></span>
                                        Nonaktif
                                    </button>
                                </div>
                            </div>

                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <button @click="closeModal"
                                    class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-xl transition-all">
                                Batal
                            </button>
                            <button @click="submitForm" :disabled="loading"
                                    class="flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                                {{ loading ? 'Menyimpan...' : (modalMode === 'create' ? 'Tambah Kategori' : 'Simpan Perubahan') }}
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
import axios from '../../axios.js'
import Sortable from 'sortablejs'

export default {
    name: 'Categories',
    components: { AdminLayout },
    data() {
        return {
            categories: [],
            productCategories: [],
            showModal: false,
            modalMode: 'create',
            selectedId: null,
            loading: false,
            errorMessage: '',
            previewUrl: null,
            isDragging: false,
            search: '',
            form: {
                name: '',
                product_categories: [],
                photo: null,
                order: 0,
                is_active: true,
            }
        }
    },

    computed: {
        filteredCategories() {
            return this.categories.filter(c => c.name.toLowerCase().includes(this.search.toLowerCase()))
        },
        activeCount() { return this.categories.filter(c => c.is_active).length },
    },

    mounted() {
        document.title = 'Categories - Two Brothers Vape System'
        this.fetchCategories()
        this.fetchProductCategories()
    },

    methods: {
        async fetchProductCategories() {
            try {
                const res = await axios.get('/products/categories')
                this.productCategories = res.data.data ?? res.data
            } catch (e) { /* opsional, gagal diem-diem aja — cuma buat pilihan multi-select */ }
        },

        toggleProductCategory(pc) {
            const idx = this.form.product_categories.indexOf(pc)
            if (idx === -1) this.form.product_categories.push(pc)
            else this.form.product_categories.splice(idx, 1)
        },

        initSortable() {
            if (!this.$refs.sortableTable) return
            if (this._sortable) this._sortable.destroy()
            this._sortable = Sortable.create(this.$refs.sortableTable, {
                animation: 150,
                handle: '.cat-drag-handle',
                ghostClass: 'opacity-30',
                chosenClass: 'bg-red-50',
                onEnd: (evt) => { this.saveOrder(evt.oldIndex, evt.newIndex) }
            })
        },

        async saveOrder(oldIndex, newIndex) {
            if (oldIndex === newIndex) return
            const snapshot = [...this.categories]
            const reordered = [...this.categories]
            const moved = reordered.splice(oldIndex, 1)[0]
            reordered.splice(newIndex, 0, moved)
            reordered.forEach((c, i) => { c.order = i })
            this.categories = reordered
            try {
                await axios.post('/categories/reorder', {
                    orders: reordered.map((c, i) => ({ id: c.id, order: i }))
                })
            } catch (e) {
                this.categories = snapshot
                this.$nextTick(() => this.initSortable())
            }
        },

        async fetchCategories() {
            try {
                const res = await axios.get('/categories')
                this.categories = res.data.data ?? res.data
                this.$nextTick(() => this.initSortable())
            } catch (e) { console.error(e) }
        },

        handleFileChange(e) {
            const file = e.target.files[0]
            if (!file) return
            this.form.photo = file
            this.previewUrl = URL.createObjectURL(file)
        },
        handleDrop(e) {
            this.isDragging = false
            const file = e.dataTransfer.files[0]
            if (!file) return
            this.form.photo = file
            this.previewUrl = URL.createObjectURL(file)
        },
        clearFile() {
            this.form.photo = null
            this.previewUrl = this.modalMode === 'edit' && this.selectedId
                ? this.categories.find(c => c.id === this.selectedId)?.photo_url || null
                : null
            if (this.$refs.fileInput) this.$refs.fileInput.value = ''
        },

        openModal(mode, cat = null) {
            this.modalMode = mode
            this.errorMessage = ''
            this.previewUrl = null
            if (mode === 'edit' && cat) {
                this.selectedId = cat.id
                this.form = {
                    name: cat.name,
                    product_categories: [...(cat.product_categories || [])],
                    photo: null,
                    order: cat.order,
                    is_active: cat.is_active,
                }
                this.previewUrl = cat.photo_url
            } else {
                this.selectedId = null
                this.form = { name: '', product_categories: [], photo: null, order: this.categories.length, is_active: true }
            }
            this.showModal = true
        },
        closeModal() {
            this.showModal = false
            this.errorMessage = ''
            this.previewUrl = null
            this.isDragging = false
        },

        async toggleActive(cat) {
            try {
                const formData = new FormData()
                formData.append('name', cat.name)
                formData.append('order', cat.order)
                formData.append('is_active', cat.is_active ? 0 : 1)
                formData.append('_method', 'PUT')
                await axios.post(`/categories/${cat.id}`, formData)
                await this.fetchCategories()
            } catch (e) { console.error(e) }
        },

        async submitForm() {
            if (this.form.product_categories.length === 0) {
                this.errorMessage = 'Pilih minimal 1 kategori produk.'
                return
            }

            this.loading = true
            this.errorMessage = ''
            try {
                const formData = new FormData()
                formData.append('name', this.form.name)
                formData.append('order', this.form.order)
                formData.append('is_active', this.form.is_active ? 1 : 0)
                this.form.product_categories.forEach(pc => formData.append('product_categories[]', pc))
                if (this.form.photo) formData.append('photo', this.form.photo)

                if (this.modalMode === 'create') {
                    await axios.post('/categories', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
                } else {
                    formData.append('_method', 'PUT')
                    await axios.post(`/categories/${this.selectedId}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
                }
                await this.fetchCategories()
                this.closeModal()
            } catch (e) {
                this.errorMessage = e.response?.data?.message || 'Terjadi kesalahan, coba lagi.'
            } finally {
                this.loading = false
            }
        },

        async deleteCategory(id) {
            if (!confirm('Yakin ingin menghapus kategori ini? Tindakan ini tidak dapat dibatalkan.')) return
            try {
                await axios.delete(`/categories/${id}`)
                await this.fetchCategories()
            } catch (e) {
                alert('Gagal menghapus kategori.')
            }
        }
    }
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

.cat-drag-handle { cursor: grab; }
.cat-drag-handle:active { cursor: grabbing; }
</style>