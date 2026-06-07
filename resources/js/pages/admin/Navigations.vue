<template>
    <AdminLayout title="Navigasi">

        <!-- ═══════════════════════════════════════════
             HERO HEADER
        ═══════════════════════════════════════════ -->
        <div class="relative mb-6 rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"></div>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10" style="background: white;"></div>

            <div class="relative px-7 py-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">Pengaturan Website</p>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Navigasi</h1>
                    <p class="text-red-200 text-xs mt-1.5">Kelola menu navigasi yang tampil di header website</p>
                </div>
                <button
                    @click="openModal('create')"
                    class="flex items-center gap-2 text-xs font-semibold px-4 py-2.5 rounded-xl border border-white/30 bg-white/15 text-white hover:bg-white/25 transition-all"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Menu
                </button>
            </div>

            <!-- Stats strip -->
            <div class="relative border-t border-white/10 px-7 py-3 flex items-center gap-6">
                <div>
                    <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Total Menu</p>
                    <p class="text-white text-lg font-bold tabular-nums">{{ navigations.length }}</p>
                </div>
                <div class="w-px h-8 bg-white/15"></div>
                <div>
                    <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Aktif</p>
                    <p class="text-white text-lg font-bold tabular-nums">{{ navigations.filter(n => n.is_active).length }}</p>
                </div>
                <div class="w-px h-8 bg-white/15"></div>
                <div>
                    <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Nonaktif</p>
                    <p class="text-white text-lg font-bold tabular-nums">{{ navigations.filter(n => !n.is_active).length }}</p>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="fetchLoading" class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-20 flex items-center justify-center">
            <svg class="w-6 h-6 animate-spin text-[#ED1F24]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
            </svg>
        </div>

        <!-- Empty state -->
        <div v-else-if="!navigations.length" class="bg-white rounded-xl border border-gray-200/80 shadow-sm">
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 rounded-2xl bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-600">Belum ada menu navigasi</p>
                <p class="text-xs text-gray-400 mt-1">Tambah menu baru untuk mulai mengelola navigasi website.</p>
                <button
                    @click="openModal('create')"
                    class="mt-4 flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Menu Pertama
                </button>
            </div>
        </div>

        <!-- Table -->
        <div v-else class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Daftar Menu</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ navigations.length }} menu terdaftar</p>
                </div>
                <a href="#" class="group flex items-center gap-1 text-xs font-semibold text-[#ED1F24] hover:text-[#C81A1E] transition-colors" onclick="return false">
                    <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Label</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">URL</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Urutan</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Status</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="nav in navigations"
                            :key="nav.id"
                            class="hover:bg-gray-50/60 transition-colors duration-150"
                        >
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-700">{{ nav.label }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-xs font-mono text-gray-500 bg-gray-100 px-2 py-1 rounded-lg">{{ nav.url }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md tabular-nums">{{ nav.order }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span
                                    class="inline-flex items-center text-[10px] font-bold border px-2 py-0.5 rounded-full uppercase tracking-wider"
                                    :class="nav.is_active
                                        ? 'bg-emerald-50 border-emerald-100 text-emerald-600'
                                        : 'bg-gray-100 border-gray-200 text-gray-400'"
                                >
                                    {{ nav.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        @click="openModal('edit', nav)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all"
                                        title="Edit"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteNav(nav.id)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all"
                                        title="Hapus"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══════════════════════
             MODAL: Tambah / Edit
        ══════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showModal"
                    class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                    @click.self="closeModal"
                >
                    <div class="bg-white border border-gray-200/80 rounded-2xl shadow-xl w-full max-w-md overflow-hidden">

                        <!-- Modal header -->
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800">
                                {{ modalMode === 'create' ? 'Tambah Menu Baru' : 'Edit Menu' }}
                            </h3>
                        </div>

                        <!-- Modal body -->
                        <div class="p-6 space-y-4">
                            <div v-if="errorMessage" class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-xs">
                                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/>
                                </svg>
                                {{ errorMessage }}
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Label</label>
                                <input
                                    v-model="form.label"
                                    type="text"
                                    placeholder="Contoh: Home"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50"
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">URL</label>
                                <input
                                    v-model="form.url"
                                    type="text"
                                    placeholder="Contoh: /belanja"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50 font-mono"
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Urutan Tampil</label>
                                <input
                                    v-model="form.order"
                                    type="number"
                                    placeholder="0"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50"
                                />
                                <p class="text-[10px] text-gray-400 mt-1.5">Angka lebih kecil tampil lebih dulu.</p>
                            </div>

                            <!-- Toggle aktif — hanya di mode edit -->
                            <div v-if="modalMode === 'edit'">
                                <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl border border-gray-100 hover:border-gray-200 hover:bg-gray-50/50 transition-all">
                                    <div class="relative shrink-0">
                                        <input v-model="form.is_active" type="checkbox" class="sr-only" />
                                        <div
                                            class="w-10 h-5 rounded-full transition-colors duration-200"
                                            :class="form.is_active ? 'bg-[#ED1F24]' : 'bg-gray-200'"
                                        ></div>
                                        <div
                                            class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow-sm transition-transform duration-200"
                                            :class="form.is_active ? 'translate-x-5' : 'translate-x-0'"
                                        ></div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700">Menu Aktif</p>
                                        <p class="text-xs text-gray-400">Menu akan ditampilkan di navigasi website</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <button
                                @click="closeModal"
                                class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-xl transition-all"
                            >Batal</button>
                            <button
                                @click="submitForm"
                                :disabled="loading"
                                class="flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm"
                            >
                                <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ loading ? 'Menyimpan...' : (modalMode === 'create' ? 'Tambah Menu' : 'Simpan Perubahan') }}
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

export default {
    name: 'Navigations',
    components: { AdminLayout },

    data() {
        return {
            navigations:  [],
            fetchLoading: true,
            showModal:    false,
            modalMode:    'create',
            selectedId:   null,
            loading:      false,
            errorMessage: '',
            form: {
                label:     '',
                url:       '',
                order:     0,
                is_active: true,
            },
        }
    },

    mounted() {
        document.title = 'Navigations - Two Brothers Vape System'
        this.fetchNavigations()
    },

    methods: {
        async fetchNavigations() {
            this.fetchLoading = true
            try {
                const response = await axios.get('/admin/navigations')
                this.navigations = response.data
            } catch (e) {
                console.error(e)
            } finally {
                this.fetchLoading = false
            }
        },

        openModal(mode, nav = null) {
            this.modalMode    = mode
            this.errorMessage = ''
            if (mode === 'edit' && nav) {
                this.selectedId = nav.id
                this.form = { label: nav.label, url: nav.url, order: nav.order, is_active: nav.is_active }
            } else {
                this.selectedId = null
                this.form = { label: '', url: '', order: 0, is_active: true }
            }
            this.showModal = true
        },

        closeModal() {
            this.showModal    = false
            this.errorMessage = ''
        },

        async submitForm() {
            this.loading      = true
            this.errorMessage = ''
            try {
                if (this.modalMode === 'create') {
                    await axios.post('/admin/navigations', this.form)
                } else {
                    await axios.put(`/admin/navigations/${this.selectedId}`, this.form)
                }
                await this.fetchNavigations()
                this.closeModal()
            } catch (e) {
                this.errorMessage = e.response?.data?.message ?? 'Terjadi kesalahan, coba lagi.'
            } finally {
                this.loading = false
            }
        },

        async deleteNav(id) {
            if (!confirm('Yakin ingin menghapus menu ini?')) return
            try {
                await axios.delete(`/admin/navigations/${id}`)
                await this.fetchNavigations()
            } catch {
                alert('Gagal menghapus menu')
            }
        },
    },
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>