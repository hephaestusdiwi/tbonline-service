<template>
    <AdminLayout :title="isEdit ? 'Edit Role' : 'Tambah Role'">

        <!-- ───────────────────────── HEADER ───────────────────────── -->
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <router-link to="/admin/roles"
                    class="flex items-center justify-center w-9 h-9 rounded-xl border border-gray-200 bg-white text-gray-400 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </router-link>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-gray-900 tracking-tight">
                                {{ isEdit ? 'Edit Role' : 'Buat Role Baru' }}
                            </h1>
                            <span class="text-[10px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 border border-gray-200">Akses</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ isEdit ? 'Perbarui nama, deskripsi, dan permission role' : 'Tentukan nama dan permission untuk role baru' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── TWO-COLUMN LAYOUT ───────────────────────── -->
        <div class="flex gap-6 items-start">

            <!-- LEFT: Main Form -->
            <div class="flex-1 min-w-0 space-y-4">

                <!-- Error Alert -->
                <div v-if="errorMessage" class="flex items-center gap-2.5 px-5 py-3 bg-red-50 border border-red-100 rounded-xl">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span class="text-xs font-medium text-red-600">{{ errorMessage }}</span>
                </div>

                <!-- Informasi Dasar -->
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Informasi Dasar</span>
                    </div>
                    <div class="px-5 py-5 grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                                Nama Role <span class="text-[#ED1F24]">*</span>
                            </label>
                            <input v-model="form.name" type="text" placeholder="cth: super-admin, editor, kasir"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all"/>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Deskripsi</label>
                            <textarea v-model="form.description" rows="3" placeholder="Jelaskan tanggung jawab role ini..."
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Permissions -->
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Permissions</span>
                            <span class="text-[10px] text-gray-400">— Pilih hak akses per modul</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="selectAll"
                                class="text-[10px] font-bold text-[#ED1F24] border border-[#ED1F24]/30 bg-[#ED1F24]/5 hover:bg-[#ED1F24]/10 px-2.5 py-1 rounded-lg transition-all">
                                Pilih Semua
                            </button>
                            <button type="button" @click="clearAll"
                                class="text-[10px] font-bold text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 px-2.5 py-1 rounded-lg transition-all">
                                Kosongkan
                            </button>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-50">
                        <div v-for="module in permissionModules" :key="module.key" class="px-5 py-4">

                            <!-- Module header -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base leading-none">{{ module.icon }}</span>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">{{ module.label }}</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">{{ module.description }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="toggleModule(module)"
                                    class="text-[10px] font-bold px-2.5 py-1 rounded-lg transition-all"
                                    :class="isModuleFullySelected(module)
                                        ? 'bg-[#ED1F24]/10 text-[#ED1F24] border border-[#ED1F24]/20 hover:bg-[#ED1F24]/15'
                                        : 'bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200'">
                                    {{ isModuleFullySelected(module) ? 'Hapus Semua' : 'Pilih Semua' }}
                                </button>
                            </div>

                            <!-- Permission checkboxes -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <label v-for="perm in module.permissions" :key="perm.key"
                                    class="flex items-center gap-2 cursor-pointer group select-none">
                                    <div class="relative shrink-0">
                                        <input type="checkbox" :value="perm.key" v-model="form.permissions" class="sr-only peer"/>
                                        <div class="w-4 h-4 rounded border border-gray-300 bg-white peer-checked:bg-[#ED1F24] peer-checked:border-[#ED1F24] transition-all flex items-center justify-center">
                                            <svg v-if="form.permissions.includes(perm.key)" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <span class="text-xs transition-colors"
                                        :class="form.permissions.includes(perm.key) ? 'text-[#ED1F24] font-semibold' : 'text-gray-500 group-hover:text-gray-700'">
                                        {{ perm.label }}
                                    </span>
                                </label>
                            </div>

                            <!-- Module progress bar -->
                            <div class="mt-3 h-1 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-300"
                                    :class="moduleProgress(module) >= 100 ? 'bg-emerald-500' : moduleProgress(module) > 0 ? 'bg-[#ED1F24]' : 'bg-transparent'"
                                    :style="{ width: moduleProgress(module) + '%' }"/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom spacer for mobile sticky bar -->
                <div class="h-20 lg:hidden"></div>
            </div>

            <!-- RIGHT: Summary Sidebar (desktop only) -->
            <div class="hidden lg:flex w-64 shrink-0 sticky top-6 flex-col gap-4">

                <!-- Ringkasan -->
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/60 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Ringkasan</span>
                    </div>
                    <div class="px-4 py-4 flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Total Permission</span>
                            <span class="text-sm font-bold text-[#ED1F24] tabular-nums">{{ form.permissions.length }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Modul Aktif</span>
                            <span class="text-sm font-bold text-gray-800 tabular-nums">{{ activeModulesCount }}</span>
                        </div>

                        <div class="h-px bg-gray-100"></div>

                        <div v-for="module in permissionModules" :key="module.key" class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs leading-none">{{ module.icon }}</span>
                                <span class="text-xs text-gray-500">{{ module.label }}</span>
                            </div>
                            <span class="text-xs font-bold tabular-nums"
                                :class="moduleSelectedCount(module) > 0 ? 'text-[#ED1F24]' : 'text-gray-300'">
                                {{ moduleSelectedCount(module) }}/{{ module.permissions.length }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Save button desktop -->
                <button @click="submitForm" :disabled="loading || !form.name.trim()"
                    class="w-full flex items-center justify-center gap-1.5 text-sm font-semibold py-2.5 rounded-xl bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all shadow-sm shadow-red-200 disabled:opacity-40 disabled:cursor-not-allowed active:scale-95">
                    <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                    {{ loading ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Buat Role') }}
                </button>

                <router-link to="/admin/roles"
                    class="block text-center text-xs text-gray-400 hover:text-gray-600 transition py-1">
                    Batal, kembali ke daftar
                </router-link>
            </div>
        </div>

        <!-- ───────────────────────── STICKY BOTTOM BAR (mobile) ───────────────────────── -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur border-t border-gray-200 px-4 py-3 flex items-center gap-3 shadow-lg">
            <router-link to="/admin/roles"
                class="flex-1 text-center text-sm font-semibold text-gray-500 border border-gray-200 py-2.5 rounded-lg hover:bg-gray-50 transition-all">
                Batal
            </router-link>
            <button @click="submitForm" :disabled="loading || !form.name.trim()"
                class="flex-[2] flex items-center justify-center gap-1.5 text-sm font-semibold py-2.5 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all shadow-sm shadow-red-200 disabled:opacity-40 disabled:cursor-not-allowed active:scale-95">
                <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                {{ loading ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Buat Role') }}
            </button>
        </div>

    </AdminLayout>
</template>

<script>
import AdminLayout from '../../components/admin/AdminLayout.vue'
import axios from '../../axios.js'

const PERMISSION_MODULES = [
    {
        key: 'users', label: 'Users', icon: '👥', description: 'Manajemen akun pengguna',
        permissions: [
            { key: 'users_view', label: 'Lihat' }, { key: 'users_create', label: 'Tambah' },
            { key: 'users_edit', label: 'Edit' },   { key: 'users_delete', label: 'Hapus' },
        ],
    },
    {
        key: 'roles', label: 'Roles', icon: '🔐', description: 'Manajemen role & permission',
        permissions: [
            { key: 'roles_view', label: 'Lihat' }, { key: 'roles_create', label: 'Tambah' },
            { key: 'roles_edit', label: 'Edit' },   { key: 'roles_delete', label: 'Hapus' },
        ],
    },
    {
        key: 'products', label: 'Produk', icon: '📦', description: 'Manajemen katalog produk',
        permissions: [
            { key: 'products_view', label: 'Lihat' }, { key: 'products_create', label: 'Tambah' },
            { key: 'products_edit', label: 'Edit' },   { key: 'products_delete', label: 'Hapus' },
        ],
    },
    {
        key: 'orders', label: 'Pesanan', icon: '🛒', description: 'Kelola transaksi & pesanan',
        permissions: [
            { key: 'orders_view',           label: 'Lihat' },
            { key: 'orders_update_status',  label: 'Update Status' },
            { key: 'orders_export',         label: 'Export' },
            { key: 'orders_delete',         label: 'Hapus' },
            { key: 'orders_revise',         label: 'Revisi Item' },
            { key: 'orders_revise_price',   label: 'Revisi Harga' },
            { key: 'orders_revise_courier', label: 'Revisi Kurir' },
        ],
    },
    {
        key: 'reports', label: 'Laporan', icon: '📊', description: 'Akses data & analitik',
        permissions: [
            { key: 'reports_view', label: 'Lihat' }, { key: 'reports_export', label: 'Export' },
        ],
    },
    {
        key: 'settings', label: 'Pengaturan', icon: '⚙️', description: 'Konfigurasi sistem',
        permissions: [
            { key: 'settings_view', label: 'Lihat' }, { key: 'settings_edit', label: 'Edit' },
        ],
    },
    {
        key: 'chat', label: 'Live Chat', icon: '💬', description: 'Manajemen sesi live chat',
        permissions: [
            { key: 'chat_view',   label: 'Lihat' },
            { key: 'chat_close',  label: 'Tutup Sesi' },
            { key: 'chat_reopen', label: 'Buka Kembali' },
            { key: 'chat_manage', label: 'Kelola & Assign' },
            { key: 'chat_admin',  label: 'Admin (Hapus)' },
        ],
    },
]

export default {
    name: 'CreateEditRole',
    components: { AdminLayout },
    props: {
        id: { type: [String, Number], default: null },
    },

    data() {
        return {
            permissionModules: PERMISSION_MODULES,
            loading: false,
            errorMessage: '',
            form: { name: '', description: '', permissions: [] },
        }
    },

    computed: {
        isEdit() { return !!this.id },
        activeModulesCount() {
            return this.permissionModules.filter(m => this.moduleSelectedCount(m) > 0).length
        },
    },

    mounted() {
        if (this.isEdit) this.fetchRole()
    },

    methods: {
        async fetchRole() {
            try {
                const res = await axios.get(`/roles/${this.id}`)
                const role = res.data
                this.form.name = role.name
                this.form.description = role.description ?? ''
                this.form.permissions = Array.isArray(role.permissions)
                    ? role.permissions.map(p => (typeof p === 'string' ? p : p.name))
                    : []
            } catch (e) {
                this.errorMessage = 'Gagal memuat data role.'
                console.error(e)
            }
        },

        moduleSelectedCount(module) {
            return module.permissions.filter(p => this.form.permissions.includes(p.key)).length
        },
        isModuleFullySelected(module) {
            return module.permissions.every(p => this.form.permissions.includes(p.key))
        },
        moduleProgress(module) {
            return Math.round((this.moduleSelectedCount(module) / module.permissions.length) * 100)
        },
        toggleModule(module) {
            if (this.isModuleFullySelected(module)) {
                const keys = module.permissions.map(p => p.key)
                this.form.permissions = this.form.permissions.filter(p => !keys.includes(p))
            } else {
                const current = new Set(this.form.permissions)
                module.permissions.forEach(p => current.add(p.key))
                this.form.permissions = [...current]
            }
        },
        selectAll() {
            this.form.permissions = this.permissionModules.flatMap(m => m.permissions.map(p => p.key))
        },
        clearAll() { this.form.permissions = [] },

        async submitForm() {
            if (!this.form.name.trim()) { this.errorMessage = 'Nama role tidak boleh kosong.'; return }
            this.loading = true; this.errorMessage = ''
            try {
                if (this.isEdit) await axios.put(`/roles/${this.id}`, this.form)
                else await axios.post('/roles', this.form)
                this.$router.push('/admin/roles')
            } catch (e) {
                this.errorMessage = e.response?.data?.message ?? 'Terjadi kesalahan, coba lagi.'
            } finally { this.loading = false }
        },
    },
}
</script>