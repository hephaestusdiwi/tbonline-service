<template>
    <AdminLayout title="Announcement Bar">

        <!-- ───────────────────────── HEADER ───────────────────────── -->
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Announcement Bar</h1>
                        <span class="text-[10px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 border border-gray-200">Marketing</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Kelola teks pengumuman yang berputar di bagian atas website</p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <!-- Stats Pills -->
                <div class="hidden sm:flex items-center gap-0 bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-4 py-2.5 text-center border-r border-gray-100">
                        <p class="text-base font-bold text-gray-900 tabular-nums">{{ announcements.length }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total</p>
                    </div>
                    <div class="px-4 py-2.5 text-center">
                        <p class="text-base font-bold text-emerald-600 tabular-nums">{{ activeCount }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aktif</p>
                    </div>
                </div>

                <div class="w-px h-8 bg-gray-200 hidden sm:block"></div>

                <button @click="openModal('create')"
                    class="flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all duration-150 shadow-sm shadow-red-200 hover:shadow-md hover:shadow-red-200 active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Pengumuman
                </button>
            </div>
        </div>

        <!-- ───────────────────────── PREVIEW BAR ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm mb-4 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Preview Tampilan di Navbar</span>
            </div>
            <div class="bg-gray-900 flex items-center justify-center gap-3 px-5 py-3 min-h-[42px] relative overflow-hidden">
                <template v-if="activeAnnouncements.length">
                    <button v-if="activeAnnouncements.length > 1"
                        @click="prevPreview"
                        class="text-white/50 hover:text-white transition-colors p-1 shrink-0">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <div class="flex-1 text-center overflow-hidden relative">
                        <Transition name="ab-slide" mode="out-in">
                            <div :key="previewIdx" class="flex items-center justify-center gap-2">
                                <span class="text-white text-xs font-medium">{{ activeAnnouncements[previewIdx]?.text }}</span>
                                <span v-if="activeAnnouncements[previewIdx]?.link_url"
                                    class="text-white/60 text-[11px] font-bold underline underline-offset-2">
                                    {{ activeAnnouncements[previewIdx]?.link_label || 'Selengkapnya' }} ›
                                </span>
                            </div>
                        </Transition>
                    </div>
                    <button v-if="activeAnnouncements.length > 1"
                        @click="nextPreview"
                        class="text-white/50 hover:text-white transition-colors p-1 shrink-0">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </template>
                <span v-else class="text-white/40 text-xs">Tidak ada pengumuman aktif</span>
            </div>
        </div>

        <!-- ───────────────────────── LIST ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden mb-4">

            <!-- Drag hint -->
            <div v-if="announcements.length > 1" class="px-5 py-2.5 border-b border-gray-100 bg-gray-50/60 flex items-center gap-2">
                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Seret baris untuk mengubah urutan tampil</span>
            </div>

            <!-- Empty state -->
            <div v-if="announcements.length === 0" class="px-6 py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-500">Belum ada pengumuman</p>
                    <span class="text-xs text-gray-400">Tambah teks pengumuman untuk ditampilkan di navbar</span>
                </div>
            </div>

            <!-- Rows -->
            <div v-for="(item, idx) in announcements" :key="item.id"
                class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-50 transition-colors duration-150 last:border-b-0"
                :class="[
                    dragOverIdx === idx ? 'bg-[#ED1F24]/5 border-[#ED1F24]/20' : 'hover:bg-gray-50/60',
                    !item.is_active ? 'opacity-50' : ''
                ]"
                draggable="true"
                @dragstart="onDragStart(idx)"
                @dragover.prevent="onDragOver(idx)"
                @drop="onDrop(idx)"
                @dragend="onDragEnd">

                <!-- Drag handle -->
                <button class="flex items-center justify-center w-6 h-6 rounded text-gray-300 hover:text-gray-500 hover:bg-gray-100 transition-all cursor-grab active:cursor-grabbing shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                        <circle cx="9" cy="6" r="1.2" fill="currentColor"/>
                        <circle cx="15" cy="6" r="1.2" fill="currentColor"/>
                        <circle cx="9" cy="12" r="1.2" fill="currentColor"/>
                        <circle cx="15" cy="12" r="1.2" fill="currentColor"/>
                        <circle cx="9" cy="18" r="1.2" fill="currentColor"/>
                        <circle cx="15" cy="18" r="1.2" fill="currentColor"/>
                    </svg>
                </button>

                <!-- Nomor urut -->
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-[10px] font-bold text-gray-500 shrink-0">
                    {{ idx + 1 }}
                </span>

                <!-- Konten -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ item.text }}</p>
                    <div v-if="item.link_url" class="flex items-center gap-1 mt-0.5">
                        <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
                            <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
                        </svg>
                        <span class="text-xs text-gray-400 truncate">{{ item.link_label || 'Selengkapnya' }} → {{ item.link_url }}</span>
                    </div>
                </div>

                <!-- Status badge -->
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border shrink-0"
                    :class="item.is_active
                        ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                        : 'bg-gray-100 text-gray-400 border-gray-200'">
                    {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>

                <!-- Actions -->
                <div class="flex items-center gap-1 shrink-0">
                    <button @click="openModal('edit', item)" title="Edit"
                        class="flex items-center justify-center w-7 h-7 rounded-lg border border-amber-200 bg-amber-50 text-amber-500 hover:bg-amber-100 hover:border-amber-300 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button @click="deleteItem(item.id)" title="Hapus"
                        class="flex items-center justify-center w-7 h-7 rounded-lg border border-red-100 bg-red-50 text-red-400 hover:bg-red-100 hover:border-red-200 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                    </button>
                </div>
            </div>

            <!-- Simpan urutan bar -->
            <div v-if="orderChanged" class="flex items-center gap-3 px-5 py-3 bg-blue-50 border-t border-blue-100">
                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span class="text-xs font-semibold text-blue-700 flex-1">Urutan berubah — simpan sekarang?</span>
                <button @click="cancelReorder"
                    class="text-xs font-semibold text-gray-500 border border-gray-200 bg-white px-3 py-1.5 rounded-lg hover:bg-gray-50 transition-all">
                    Batalkan
                </button>
                <button @click="saveOrder" :disabled="saving"
                    class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all shadow-sm shadow-red-200 disabled:opacity-50 active:scale-95">
                    <svg v-if="saving" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                    {{ saving ? 'Menyimpan...' : 'Simpan Urutan' }}
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════ MODAL ═══════════════════════════ -->
        <Transition name="pc-modal">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                style="background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);"
                @click.self="closeModal">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg border border-gray-200/80 flex flex-col max-h-[90vh] overflow-hidden">

                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                :class="modalMode === 'create' ? 'bg-[#ED1F24]/10 border border-[#ED1F24]/20' : 'bg-amber-50 border border-amber-200'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                                    :class="modalMode === 'create' ? 'text-[#ED1F24]' : 'text-amber-500'">
                                    <template v-if="modalMode === 'create'"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></template>
                                    <template v-else><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></template>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800">
                                    {{ modalMode === 'create' ? 'Tambah Pengumuman' : 'Edit Pengumuman' }}
                                </h3>
                                <p class="text-xs text-gray-400 mt-0.5">Teks ditampilkan secara bergantian di navbar</p>
                            </div>
                        </div>
                        <button @click="closeModal" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <!-- Error -->
                    <div v-if="errorMessage" class="flex items-center gap-2.5 px-6 py-3 bg-red-50 border-b border-red-100 shrink-0">
                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span class="text-xs font-medium text-red-600">{{ errorMessage }}</span>
                    </div>

                    <!-- Body -->
                    <div class="overflow-y-auto flex-1 px-6 py-5 flex flex-col gap-6">

                        <!-- Preview realtime -->
                        <div class="rounded-xl overflow-hidden border border-gray-200">
                            <div class="px-4 py-2 border-b border-gray-100 bg-gray-50/60 flex items-center gap-1.5">
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Preview</span>
                            </div>
                            <div class="bg-gray-900 flex items-center justify-center px-4 py-2.5 min-h-[38px]">
                                <span class="text-white text-xs font-medium text-center">
                                    {{ form.text || 'Preview teks pengumuman...' }}
                                    <span v-if="form.link_url" class="text-white/60 font-bold underline underline-offset-2 ml-1">
                                        {{ form.link_label || 'Selengkapnya' }} ›
                                    </span>
                                </span>
                            </div>
                        </div>

                        <!-- Section: Teks Pengumuman -->
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                                Teks Pengumuman
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                                        Teks Pengumuman <span class="text-[#ED1F24]">*</span>
                                    </label>
                                    <input v-model="form.text" type="text" maxlength="255"
                                        placeholder="cth: Gratis Ongkir dengan Minimal Belanja Rp150.000"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all"/>
                                    <p class="text-[10px] text-gray-400 mt-1">{{ form.text.length }} / 255 karakter</p>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                                    <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                                        <button @click="form.is_active = true"
                                            class="flex-1 py-2 text-xs font-bold transition-all"
                                            :class="form.is_active ? 'bg-emerald-500 text-white' : 'bg-white text-gray-400'">Aktif</button>
                                        <button @click="form.is_active = false"
                                            class="flex-1 py-2 text-xs font-bold border-l border-gray-200 transition-all"
                                            :class="!form.is_active ? 'bg-red-500 text-white' : 'bg-white text-gray-400'">Nonaktif</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Link (opsional) -->
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
                                    <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
                                </svg>
                                Link <span class="normal-case font-medium text-gray-300 ml-1">(opsional)</span>
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">URL Tujuan</label>
                                    <input v-model="form.link_url" type="url"
                                        placeholder="cth: https://tbstore.id/syarat-ongkir"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all"/>
                                    <p class="text-[10px] text-gray-400 mt-1">Kosongkan jika tidak ada link</p>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Label Link</label>
                                    <input v-model="form.link_label" type="text"
                                        placeholder="cth: Lihat Syarat"
                                        :disabled="!form.link_url"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all"/>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex justify-between items-center shrink-0">
                        <button @click="closeModal" class="text-xs font-semibold text-gray-500 border border-gray-200 px-4 py-2 rounded-lg hover:bg-white transition-all">
                            Batal
                        </button>
                        <button @click="submitForm" :disabled="loading"
                            class="flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all shadow-sm shadow-red-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                            <svg v-if="loading" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                            {{ loading ? 'Menyimpan...' : (modalMode === 'create' ? 'Tambah Pengumuman' : 'Simpan Perubahan') }}
                        </button>
                    </div>

                </div>
            </div>
        </Transition>

    </AdminLayout>
</template>

<script>
import AdminLayout from '../../components/admin/AdminLayout.vue'
import axios from '../../axios.js'

export default {
    name: 'AnnouncementAdmin',
    components: { AdminLayout },

    data() {
        return {
            announcements: [],
            originalOrder: [],
            loading: false,
            saving: false,
            errorMessage: '',

            showModal: false,
            modalMode: 'create',
            selectedItem: null,
            form: this.emptyForm(),

            dragIdx: null,
            dragOverIdx: null,

            previewIdx: 0,
            previewTimer: null,
        }
    },

    computed: {
        activeCount() {
            return this.announcements.filter(a => a.is_active).length
        },
        activeAnnouncements() {
            return this.announcements.filter(a => a.is_active)
        },
        orderChanged() {
            if (this.originalOrder.length !== this.announcements.length) return false
            return this.announcements.some((a, i) => a.id !== this.originalOrder[i])
        },
    },

    async mounted() {
        document.title = 'Announcements Bar - Two Brothers Vape System'
        await this.fetchAnnouncements()
        this.startPreviewTimer()
    },

    beforeUnmount() {
        clearInterval(this.previewTimer)
    },

    methods: {
        emptyForm() {
            return { text: '', link_url: '', link_label: '', is_active: true }
        },

        async fetchAnnouncements() {
            try {
                const res = await axios.get('/admin/announcements')
                this.announcements = res.data ?? []
                this.originalOrder = this.announcements.map(a => a.id)
            } catch (e) {
                console.error('Gagal memuat announcements:', e)
            }
        },

        openModal(mode, item = null) {
            this.modalMode = mode
            this.errorMessage = ''
            if (item) {
                this.selectedItem = item
                this.form = { text: item.text, link_url: item.link_url ?? '', link_label: item.link_label ?? '', is_active: item.is_active }
            } else {
                this.selectedItem = null
                this.form = this.emptyForm()
            }
            this.showModal = true
        },

        closeModal() { this.showModal = false; this.errorMessage = '' },

        async submitForm() {
            if (!this.form.text?.trim()) { this.errorMessage = 'Teks pengumuman wajib diisi.'; return }
            this.loading = true; this.errorMessage = ''
            try {
                const payload = { text: this.form.text.trim(), link_url: this.form.link_url || null, link_label: this.form.link_label || null, is_active: this.form.is_active }
                if (this.modalMode === 'create') await axios.post('/admin/announcements', payload)
                else await axios.put(`/admin/announcements/${this.selectedItem.id}`, payload)
                await this.fetchAnnouncements()
                this.closeModal()
            } catch (e) {
                this.errorMessage = e.response?.data?.message ?? Object.values(e.response?.data?.errors ?? {}).flat().join(', ') ?? 'Terjadi kesalahan, coba lagi.'
            } finally { this.loading = false }
        },

        async deleteItem(id) {
            if (!confirm('Yakin ingin menghapus pengumuman ini?')) return
            try { await axios.delete(`/admin/announcements/${id}`); await this.fetchAnnouncements() }
            catch { alert('Gagal menghapus pengumuman.') }
        },

        onDragStart(idx) { this.dragIdx = idx },
        onDragOver(idx)  { this.dragOverIdx = idx },
        onDragEnd()      { this.dragOverIdx = null },

        onDrop(targetIdx) {
            if (this.dragIdx === null || this.dragIdx === targetIdx) return
            const arr = [...this.announcements]
            const [moved] = arr.splice(this.dragIdx, 1)
            arr.splice(targetIdx, 0, moved)
            this.announcements = arr
            this.dragIdx = null; this.dragOverIdx = null
        },

        async saveOrder() {
            this.saving = true
            try {
                await axios.post('/admin/announcements/reorder', { ids: this.announcements.map(a => a.id) })
                this.originalOrder = this.announcements.map(a => a.id)
            } catch { alert('Gagal menyimpan urutan.') }
            finally { this.saving = false }
        },

        cancelReorder() {
            const map = Object.fromEntries(this.announcements.map(a => [a.id, a]))
            this.announcements = this.originalOrder.map(id => map[id])
        },

        startPreviewTimer() {
            this.previewTimer = setInterval(() => {
                if (this.activeAnnouncements.length > 1)
                    this.previewIdx = (this.previewIdx + 1) % this.activeAnnouncements.length
            }, 3000)
        },

        nextPreview() {
            if (!this.activeAnnouncements.length) return
            this.previewIdx = (this.previewIdx + 1) % this.activeAnnouncements.length
        },

        prevPreview() {
            if (!this.activeAnnouncements.length) return
            this.previewIdx = (this.previewIdx - 1 + this.activeAnnouncements.length) % this.activeAnnouncements.length
        },
    },
}
</script>

<style scoped>
.pc-modal-enter-active, .pc-modal-leave-active { transition: all .2s; }
.pc-modal-enter-from, .pc-modal-leave-to { opacity: 0; transform: scale(.97); }

.ab-slide-enter-active, .ab-slide-leave-active { transition: all .3s ease; }
.ab-slide-enter-from  { transform: translateX(20px); opacity: 0; }
.ab-slide-leave-to    { transform: translateX(-20px); opacity: 0; }
</style>