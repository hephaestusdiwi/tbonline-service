<template>
    <AdminLayout title="User Management">

        <!-- ═══════════════════════════════════════════
             HERO HEADER
        ═══════════════════════════════════════════ -->
        <div class="relative mb-6 rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"></div>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10" style="background: white;"></div>

            <div class="relative px-7 py-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">Manajemen Sistem</p>
                    <h1 class="text-2xl font-bold text-white tracking-tight">User Management</h1>
                    <p class="text-red-200 text-xs mt-1.5">Kelola akun pengguna dan hak akses sistem</p>
                </div>
                <button
                    @click="openModal('create')"
                    class="flex items-center gap-2 text-xs font-semibold px-4 py-2.5 rounded-xl border border-white/30 bg-white/15 text-white hover:bg-white/25 transition-all"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah User
                </button>
            </div>

            <!-- Stats strip -->
            <div class="relative border-t border-white/10 px-7 py-3 flex flex-wrap items-center gap-6">
                <div>
                    <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Total User</p>
                    <p class="text-white text-lg font-bold tabular-nums">{{ users.length }}</p>
                </div>
                <div class="w-px h-8 bg-white/15"></div>
                <div>
                    <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Admin</p>
                    <p class="text-white text-lg font-bold tabular-nums">{{ countByRole('admin') }}</p>
                </div>
                <div class="w-px h-8 bg-white/15"></div>
                <div>
                    <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Manager</p>
                    <p class="text-white text-lg font-bold tabular-nums">{{ countByRole('manager') }}</p>
                </div>
                <div class="w-px h-8 bg-white/15"></div>
                <div>
                    <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Staff</p>
                    <p class="text-white text-lg font-bold tabular-nums">{{ countByRole('staff') }}</p>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">

            <!-- Toolbar -->
            <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Daftar Pengguna</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ filteredUsers.length }} dari {{ users.length }} pengguna</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama atau email..."
                            class="pl-9 pr-4 py-2 text-xs border border-gray-200 rounded-xl bg-gray-50/50 text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors w-56"
                        />
                    </div>
                    <select
                        v-model="filterRole"
                        class="text-xs border border-gray-200 rounded-xl px-3 py-2 bg-gray-50/50 text-gray-600 focus:outline-none focus:border-[#ED1F24] transition-colors"
                    >
                        <option value="">Semua Role</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="fetchLoading" class="p-20 flex items-center justify-center">
                <svg class="w-6 h-6 animate-spin text-[#ED1F24]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                </svg>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Pengguna</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Email</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Role</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Bergabung</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="filteredUsers.length === 0">
                            <td colspan="5" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a4 4 0 0 0-4-4h-1M9 20H4v-2a4 4 0 0 1 4-4h1m4-4a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-400">Tidak ada data pengguna</p>
                                </div>
                            </td>
                        </tr>
                        <tr
                            v-for="user in filteredUsers"
                            :key="user.id"
                            class="hover:bg-gray-50/60 transition-colors duration-150"
                        >
                            <!-- Pengguna -->
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold shrink-0"
                                        :class="avatarClass(user.role)"
                                    >
                                        {{ initials(user.name) }}
                                    </div>
                                    <span class="text-sm font-semibold text-gray-700">{{ user.name }}</span>
                                </div>
                            </td>

                            <!-- Email -->
                            <td class="px-6 py-3.5 text-sm text-gray-500">{{ user.email }}</td>

                            <!-- Role -->
                            <td class="px-6 py-3.5">
                                <span
                                    class="inline-flex items-center text-[10px] font-bold border px-2.5 py-0.5 rounded-full uppercase tracking-wider"
                                    :class="roleBadgeClass(user.role)"
                                >
                                    {{ roleLabel(user.role) }}
                                </span>
                            </td>

                            <!-- Bergabung -->
                            <td class="px-6 py-3.5 text-xs text-gray-400 tabular-nums">{{ formatDate(user.created_at) }}</td>

                            <!-- Aksi -->
                            <td class="px-6 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        @click="openModal('edit', user)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all"
                                        title="Edit"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteUser(user.id)"
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

            <!-- Table footer -->
            <div v-if="!fetchLoading && users.length" class="px-6 py-3 border-t border-gray-100 bg-gray-50/40 text-xs text-gray-400">
                Menampilkan {{ filteredUsers.length }} dari {{ users.length }} pengguna
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
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-gray-800">
                                        {{ modalMode === 'create' ? 'Tambah User Baru' : 'Edit User' }}
                                    </h3>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ modalMode === 'create' ? 'Isi detail akun pengguna baru' : 'Perbarui informasi akun pengguna' }}
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="closeModal"
                                class="p-1.5 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Error alert -->
                        <div v-if="errorMessage" class="mx-6 mt-4 flex items-start gap-2 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-xs">
                            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/>
                            </svg>
                            {{ errorMessage }}
                        </div>

                        <!-- Modal body -->
                        <div class="px-6 py-5 space-y-4">

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Nama Lengkap</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Masukkan nama lengkap"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50"
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="user@perusahaan.com"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50"
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">
                                    Password
                                    <span v-if="modalMode === 'edit'" class="normal-case font-normal text-gray-400 ml-1">· kosongkan jika tidak diubah</span>
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Min. 6 karakter"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50"
                                />
                            </div>

                            <!-- Role selector — card style, disesuaikan ke light theme -->
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Role</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="role in roles"
                                        :key="role.value"
                                        type="button"
                                        @click="form.role = role.value"
                                        :class="[
                                            'flex flex-col items-center gap-1.5 py-3.5 px-2 rounded-xl border text-center transition-all',
                                            form.role === role.value
                                                ? role.activeClass
                                                : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                                        ]"
                                    >
                                        <span class="text-xl">{{ role.icon }}</span>
                                        <span class="text-xs font-bold" :class="form.role === role.value ? role.textClass : 'text-gray-600'">{{ role.label }}</span>
                                        <span class="text-[10px] leading-tight" :class="form.role === role.value ? role.subTextClass : 'text-gray-400'">{{ role.desc }}</span>
                                    </button>
                                </div>
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
                                {{ loading ? 'Menyimpan...' : (modalMode === 'create' ? 'Buat Akun' : 'Simpan Perubahan') }}
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
    name: 'Users',
    components: { AdminLayout },

    data() {
        return {
            users:        [],
            fetchLoading: true,
            search:       '',
            filterRole:   '',
            showModal:    false,
            modalMode:    'create',
            selectedId:   null,
            loading:      false,
            errorMessage: '',
            form: {
                name:     '',
                email:    '',
                password: '',
                role:     'staff',
            },
            roles: [
                {
                    value:       'admin',
                    label:       'Admin',
                    desc:        'Akses penuh',
                    icon:        '🛡️',
                    activeClass: 'border-purple-200 bg-purple-50',
                    textClass:   'text-purple-600',
                    subTextClass:'text-purple-400',
                },
                {
                    value:       'manager',
                    label:       'Manager',
                    desc:        'Kelola tim',
                    icon:        '👔',
                    activeClass: 'border-emerald-200 bg-emerald-50',
                    textClass:   'text-emerald-600',
                    subTextClass:'text-emerald-400',
                },
                {
                    value:       'staff',
                    label:       'Staff',
                    desc:        'Akses dasar',
                    icon:        '👤',
                    activeClass: 'border-blue-200 bg-blue-50',
                    textClass:   'text-blue-600',
                    subTextClass:'text-blue-400',
                },
            ],
        }
    },

    computed: {
        filteredUsers() {
            return this.users.filter(user => {
                const matchSearch =
                    !this.search ||
                    user.name.toLowerCase().includes(this.search.toLowerCase()) ||
                    user.email.toLowerCase().includes(this.search.toLowerCase())
                const matchRole = !this.filterRole || user.role === this.filterRole
                return matchSearch && matchRole
            })
        },
    },

    mounted() {
        this.fetchUsers()
    },

    methods: {
        async fetchUsers() {
            this.fetchLoading = true
            try {
                const response = await axios.get('/users')
                this.users = response.data
            } catch (e) {
                console.error(e)
            } finally {
                this.fetchLoading = false
            }
        },

        countByRole(role) {
            return this.users.filter(u => u.role === role).length
        },

        initials(name) {
            return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
        },

        avatarClass(role) {
            return {
                admin:   'bg-purple-50 border border-purple-100 text-purple-600',
                manager: 'bg-emerald-50 border border-emerald-100 text-emerald-600',
                staff:   'bg-blue-50 border border-blue-100 text-blue-600',
            }[role] ?? 'bg-gray-100 border border-gray-200 text-gray-500'
        },

        roleBadgeClass(role) {
            return {
                admin:   'bg-purple-50 border-purple-100 text-purple-600',
                manager: 'bg-emerald-50 border-emerald-100 text-emerald-600',
                staff:   'bg-blue-50 border-blue-100 text-blue-600',
            }[role] ?? 'bg-gray-100 border-gray-200 text-gray-400'
        },

        roleLabel(role) {
            return { admin: 'Admin', manager: 'Manager', staff: 'Staff' }[role] ?? role
        },

        formatDate(dateStr) {
            if (!dateStr) return '-'
            return new Date(dateStr).toLocaleDateString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric',
            })
        },

        openModal(mode, user = null) {
            this.modalMode    = mode
            this.errorMessage = ''
            if (mode === 'edit' && user) {
                this.selectedId = user.id
                this.form = { name: user.name, email: user.email, password: '', role: user.role }
            } else {
                this.selectedId = null
                this.form = { name: '', email: '', password: '', role: 'staff' }
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
                    await axios.post('/users', this.form)
                } else {
                    await axios.put(`/users/${this.selectedId}`, this.form)
                }
                await this.fetchUsers()
                this.closeModal()
            } catch (e) {
                this.errorMessage = e.response?.data?.message ?? 'Terjadi kesalahan, coba lagi.'
            } finally {
                this.loading = false
            }
        },

        async deleteUser(id) {
            if (!confirm('Yakin ingin menghapus user ini?')) return
            try {
                await axios.delete(`/users/${id}`)
                await this.fetchUsers()
            } catch (e) {
                alert(e.response?.data?.message ?? 'Gagal menghapus user.')
            }
        },
    },
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>