<template>
    <AdminLayout title="Roles & Permissions">

        <!-- ───────────────────────── HEADER ───────────────────────── -->
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Roles & Permissions</h1>
                        <span class="text-[10px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 border border-gray-200">Akses</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Kelola role dan hak akses untuk setiap level pengguna</p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <!-- Stats Pills -->
                <div class="hidden sm:flex items-center gap-0 bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-4 py-2.5 text-center border-r border-gray-100">
                        <p class="text-base font-bold text-gray-900 tabular-nums">{{ roles.length }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Role</p>
                    </div>
                    <div class="px-4 py-2.5 text-center border-r border-gray-100">
                        <p class="text-base font-bold text-gray-900 tabular-nums">{{ totalUsers }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pengguna</p>
                    </div>
                    <div class="px-4 py-2.5 text-center">
                        <p class="text-base font-bold text-gray-900 tabular-nums">{{ totalPermissions }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Permission</p>
                    </div>
                </div>

                <div class="w-px h-8 bg-gray-200 hidden sm:block"></div>

                <router-link
                    to="/admin/roles/create"
                    class="flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all duration-150 shadow-sm shadow-red-200 hover:shadow-md hover:shadow-red-200 active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Role
                </router-link>
            </div>
        </div>

        <!-- ───────────────────────── FILTER BAR ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm mb-4 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Filter Role</span>
            </div>
            <div class="px-5 py-4 flex flex-wrap items-center gap-3">
                <!-- Search -->
                <div class="flex items-center gap-2 flex-1 min-w-[200px] border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus-within:border-[#ED1F24] focus-within:ring-2 focus-within:ring-[#ED1F24]/10 transition-all">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Cari role atau deskripsi..."
                        class="text-sm text-gray-700 placeholder-gray-400 outline-none bg-transparent w-full"/>
                </div>
                <span class="text-xs text-gray-400 shrink-0">
                    <span class="font-semibold text-gray-600">{{ filteredRoles.length }}</span> role ditemukan
                </span>
            </div>
        </div>

        <!-- ───────────────────────── TABLE ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden mb-4">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[640px]">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Role</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 hidden md:table-cell">Deskripsi</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400">Permissions</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400">Total User</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400 w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">

                        <!-- Empty state -->
                        <tr v-if="filteredRoles.length === 0">
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-500">Tidak ada role ditemukan</p>
                                    <span class="text-xs text-gray-400">Coba ubah kata kunci pencarian</span>
                                </div>
                            </td>
                        </tr>

                        <tr v-for="role in filteredRoles" :key="role.id"
                            class="hover:bg-gray-50/60 transition-colors duration-150">

                            <!-- Role Name -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base shrink-0 border"
                                        :style="{ background: (role.color || '#ED1F24') + '18', borderColor: (role.color || '#ED1F24') + '30', color: role.color || '#ED1F24' }">
                                        {{ role.icon || '🔐' }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-gray-800">{{ role.name }}</p>
                                        <span v-if="role.is_system"
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#ED1F24]/10 text-[#ED1F24] mt-0.5">
                                            System Role
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Description -->
                            <td class="px-5 py-4 hidden md:table-cell">
                                <span class="text-xs text-gray-400 line-clamp-2 max-w-xs block leading-relaxed">{{ role.description || '—' }}</span>
                            </td>

                            <!-- Permissions Count -->
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border bg-purple-50 text-purple-600 border-purple-100">
                                    {{ countPermissions(role) }} permission
                                </span>
                            </td>

                            <!-- Users Count -->
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border bg-blue-50 text-blue-600 border-blue-100">
                                    {{ role.users_count ?? 0 }} user
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <router-link :to="`/admin/roles/${role.id}/edit`"
                                        class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-600 text-xs font-semibold hover:bg-amber-100 hover:border-amber-300 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </router-link>
                                    <button @click="deleteRole(role)" :disabled="role.is_system"
                                        class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-red-100 bg-red-50 text-red-400 text-xs font-semibold hover:bg-red-100 hover:border-red-200 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table footer -->
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/60">
                <span class="text-xs text-gray-400">
                    Menampilkan <span class="font-semibold text-gray-600">{{ filteredRoles.length }}</span>
                    dari <span class="font-semibold text-gray-600">{{ roles.length }}</span> role
                </span>
            </div>
        </div>

    </AdminLayout>
</template>

<script>
import AdminLayout from '../../components/admin/AdminLayout.vue'
import axios from '../../axios'

export default {
    name: 'RolesList',
    components: { AdminLayout },

    data() {
        return {
            roles: [],
            search: '',
        }
    },

    computed: {
        filteredRoles() {
            if (!this.search) return this.roles
            const q = this.search.toLowerCase()
            return this.roles.filter(r =>
                r.name.toLowerCase().includes(q) ||
                (r.description ?? '').toLowerCase().includes(q)
            )
        },
        totalUsers() {
            return this.roles.reduce((sum, r) => sum + (r.users_count ?? 0), 0)
        },
        totalPermissions() {
            const allPermissions = this.roles.flatMap(r => r.permissions || [])
            return [...new Set(allPermissions)].length
        },
    },

    mounted() {
        document.title = 'Roles & Permissions - Two Brothers Vape System'
        this.fetchRoles()
    },

    methods: {
        async fetchRoles() {
            try {
                const res = await axios.get('/roles')
                this.roles = res.data
            } catch (e) {
                console.error(e)
            }
        },

        countPermissions(role) {
            if (!role.permissions) return 0
            return Array.isArray(role.permissions) ? role.permissions.length : 0
        },

        async deleteRole(role) {
            if (role.is_system) return
            if (!confirm(`Yakin hapus role "${role.name}"? User dengan role ini akan terpengaruh.`)) return
            try {
                await axios.delete(`/roles/${role.id}`)
                await this.fetchRoles()
            } catch (e) {
                alert(e.response?.data?.message ?? 'Gagal menghapus role.')
            }
        },
    }
}
</script>