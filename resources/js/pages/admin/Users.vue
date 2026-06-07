<template>
  <AdminLayout title="User Management">

    <!-- ── HERO HEADER ─────────────────────────────────────────── -->
    <div class="relative mb-6 rounded-2xl overflow-hidden"
         style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
      <!-- decorative circles (sama seperti sebelumnya) -->
      <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10 bg-white"></div>
      <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5 bg-white"></div>
      <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10 bg-white"></div>

      <div class="relative px-7 py-5 flex flex-wrap items-center justify-between gap-4">
        <div>
          <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">Manajemen Sistem</p>
          <h1 class="text-2xl font-bold text-white tracking-tight">User Management</h1>
          <p class="text-red-200 text-xs mt-1.5">Kelola akun pengguna dan hak akses sistem</p>
        </div>
        <button @click="openCreateModal"
                class="flex items-center gap-2 text-xs font-semibold px-4 py-2.5 rounded-xl border border-white/30 bg-white/15 text-white hover:bg-white/25 transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah User
        </button>
      </div>

      <!-- Stats strip -->
      <div class="relative border-t border-white/10 px-7 py-3 flex flex-wrap items-center gap-6">
        <StatItem label="Total User"  :value="store.users.length" />
        <div class="w-px h-8 bg-white/15"></div>
        <StatItem label="Admin"       :value="store.countByRole('admin')" />
        <div class="w-px h-8 bg-white/15"></div>
        <StatItem label="Manager"     :value="store.countByRole('manager')" />
        <div class="w-px h-8 bg-white/15"></div>
        <StatItem label="Staff"       :value="store.countByRole('staff')" />
        <div class="w-px h-8 bg-white/15"></div>
        <StatItem label="Suspended"   :value="store.suspendedCount" />
      </div>
    </div>

    <!-- ── TABLE CARD ──────────────────────────────────────────── -->
    <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">

      <!-- Toolbar -->
      <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
        <div>
          <h3 class="text-sm font-bold text-gray-800">Daftar Pengguna</h3>
          <p class="text-xs text-gray-400 mt-0.5">{{ filteredUsers.length }} dari {{ store.users.length }} pengguna</p>
        </div>
        <div class="flex items-center gap-2">
          <!-- Search -->
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input v-model="search" type="text" placeholder="Cari nama atau email..."
                   class="pl-9 pr-4 py-2 text-xs border border-gray-200 rounded-xl bg-gray-50/50 text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors w-56" />
          </div>
          <!-- Role filter -->
          <select v-model="filterRole"
                  class="text-xs border border-gray-200 rounded-xl px-3 py-2 bg-gray-50/50 text-gray-600 focus:outline-none focus:border-[#ED1F24]">
            <option value="">Semua Role</option>
            <option value="admin">Admin</option>
            <option value="manager">Manager</option>
            <option value="staff">Staff</option>
          </select>
          <!-- Status filter -->
          <select v-model="filterStatus"
                  class="text-xs border border-gray-200 rounded-xl px-3 py-2 bg-gray-50/50 text-gray-600 focus:outline-none focus:border-[#ED1F24]">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="store.loading" class="p-20 flex items-center justify-center">
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
              <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Status</th>
              <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Last Login</th>
              <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <!-- Empty state -->
            <tr v-if="filteredUsers.length === 0">
              <td colspan="6" class="px-6 py-14 text-center">
                <div class="flex flex-col items-center gap-3">
                  <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17 20h5v-2a4 4 0 0 0-4-4h-1M9 20H4v-2a4 4 0 0 1 4-4h1m4-4a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                    </svg>
                  </div>
                  <p class="text-sm font-medium text-gray-400">Tidak ada data pengguna</p>
                </div>
              </td>
            </tr>

            <!-- Rows -->
            <tr v-for="user in filteredUsers" :key="user.id"
                class="hover:bg-gray-50/60 transition-colors duration-150"
                :class="{ 'opacity-60': !user.is_active }">

              <!-- Pengguna -->
              <td class="px-6 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-xl overflow-hidden shrink-0 flex-shrink-0" style="min-width:2rem; min-height:2rem;">
                    <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name"
                         class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-xs font-bold"
                         :class="avatarClass(user.role)">
                      {{ initials(user.name) }}
                    </div>
                  </div>
                  <span class="text-sm font-semibold text-gray-700">{{ user.name }}</span>
                </div>
              </td>

              <!-- Email -->
              <td class="px-6 py-3.5 text-sm text-gray-500">{{ user.email }}</td>

              <!-- Role badge -->
              <td class="px-6 py-3.5">
                <span class="inline-flex items-center text-[10px] font-bold border px-2.5 py-0.5 rounded-full uppercase tracking-wider"
                      :class="roleBadgeClass(user.role)">
                  {{ roleLabel(user.role) }}
                </span>
              </td>

              <!-- Status badge -->
              <td class="px-6 py-3.5">
                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold border px-2.5 py-0.5 rounded-full uppercase tracking-wider"
                      :class="user.is_active
                        ? 'bg-emerald-50 border-emerald-100 text-emerald-600'
                        : 'bg-red-50 border-red-100 text-red-500'">
                  <span class="w-1.5 h-1.5 rounded-full"
                        :class="user.is_active ? 'bg-emerald-500' : 'bg-red-400'"></span>
                  {{ user.is_active ? 'Aktif' : 'Suspended' }}
                </span>
              </td>

              <!-- Last Login -->
              <td class="px-6 py-3.5 text-xs text-gray-400 tabular-nums">
                {{ formatLastLogin(user.last_login_at) }}
              </td>

              <!-- Action Menu -->
              <td class="px-6 py-3.5">
                <div class="flex justify-end" v-click-outside="() => closeMenu(user.id)">
                  <div class="relative">
                    <button @click.stop="toggleMenu(user.id)"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                      </svg>
                    </button>

                    <!-- Dropdown -->
                    <Transition name="dropdown">
                      <div v-if="openMenuId === user.id"
                           class="absolute right-0 top-full mt-1 w-48 bg-white border border-gray-200 rounded-xl shadow-lg z-20 overflow-hidden py-1">

                        <button @click="openDetailDrawer(user)"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-gray-600 hover:bg-gray-50 transition-colors">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                          Lihat Detail
                        </button>

                        <button @click="openEditModal(user)"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-gray-600 hover:bg-gray-50 transition-colors">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                          </svg>
                          Edit User
                        </button>

                        <button @click="openResetPasswordModal(user)"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-gray-600 hover:bg-gray-50 transition-colors">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                          </svg>
                          Reset Password
                        </button>

                        <div class="border-t border-gray-100 my-1"></div>

                        <button v-if="user.is_active" @click="handleSuspend(user)"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-red-500 hover:bg-red-50 transition-colors">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                          </svg>
                          Suspend User
                        </button>

                        <button v-else @click="handleActivate(user)"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-emerald-600 hover:bg-emerald-50 transition-colors">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                          </svg>
                          Aktifkan User
                        </button>

                      </div>
                    </Transition>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Table footer -->
      <div v-if="!store.loading && store.users.length"
           class="px-6 py-3 border-t border-gray-100 bg-gray-50/40 text-xs text-gray-400">
        Menampilkan {{ filteredUsers.length }} dari {{ store.users.length }} pengguna
      </div>
    </div>

    <!-- ── MODAL: Tambah / Edit ───────────────────────────────── -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
             @click.self="closeModal">
          <div class="bg-white border border-gray-200/80 rounded-2xl shadow-xl w-full max-w-md overflow-hidden">

            <!-- Header -->
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
              <button @click="closeModal" class="p-1.5 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <!-- Error -->
            <div v-if="modalError" class="mx-6 mt-4 flex items-start gap-2 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-xs">
              <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/>
              </svg>
              {{ modalError }}
            </div>

            <!-- Body -->
            <div class="px-6 py-5 space-y-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Nama Lengkap</label>
                <input v-model="form.name" type="text" placeholder="Masukkan nama lengkap"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Email</label>
                <input v-model="form.email" type="email" placeholder="user@perusahaan.com"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50" />
              </div>
              <div v-if="modalMode === 'create'">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Password</label>
                <input v-model="form.password" type="password" placeholder="Min. 6 karakter"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50" />
              </div>
              <!-- Role selector -->
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Role</label>
                <div class="grid grid-cols-3 gap-2">
                  <button v-for="role in roles" :key="role.value" type="button" @click="form.role = role.value"
                          :class="['flex flex-col items-center gap-1.5 py-3.5 px-2 rounded-xl border text-center transition-all',
                            form.role === role.value ? role.activeClass : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50']">
                    <span class="text-xl">{{ role.icon }}</span>
                    <span class="text-xs font-bold" :class="form.role === role.value ? role.textClass : 'text-gray-600'">{{ role.label }}</span>
                    <span class="text-[10px] leading-tight" :class="form.role === role.value ? role.subTextClass : 'text-gray-400'">{{ role.desc }}</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50">
              <button @click="closeModal"
                      class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-xl transition-all">
                Batal
              </button>
              <button @click="submitForm" :disabled="modalLoading"
                      class="flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                <svg v-if="modalLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ modalLoading ? 'Menyimpan...' : (modalMode === 'create' ? 'Buat Akun' : 'Simpan Perubahan') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── MODAL: Reset Password ──────────────────────────────── -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showResetModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
             @click.self="showResetModal = false">
          <div class="bg-white border border-gray-200/80 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
              <h3 class="text-sm font-bold text-gray-800">Reset Password</h3>
              <button @click="showResetModal = false" class="p-1.5 rounded-xl text-gray-400 hover:bg-gray-100 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <div v-if="resetError" class="mx-6 mt-4 flex items-start gap-2 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-xs">
              {{ resetError }}
            </div>

            <div class="px-6 py-5 space-y-4">
              <p class="text-xs text-gray-500">
                Reset password untuk <strong>{{ selectedUser?.name }}</strong>. User akan dikeluarkan dari semua sesi aktif.
              </p>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Password Baru</label>
                <input v-model="resetForm.password" type="password" placeholder="Min. 6 karakter"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Konfirmasi Password</label>
                <input v-model="resetForm.password_confirmation" type="password" placeholder="Ulangi password"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50" />
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50">
              <button @click="showResetModal = false"
                      class="text-sm text-gray-500 border border-gray-200 px-4 py-2 rounded-xl transition-all hover:border-gray-300">
                Batal
              </button>
              <button @click="submitResetPassword" :disabled="resetLoading"
                      class="flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                <svg v-if="resetLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                </svg>
                {{ resetLoading ? 'Mereset...' : 'Reset Password' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── DRAWER: User Detail ────────────────────────────────── -->
    <Teleport to="body">
      <Transition name="drawer">
        <div v-if="showDrawer" class="fixed inset-0 z-50 flex">
          <!-- Overlay -->
          <div class="flex-1 bg-black/40 backdrop-blur-sm" @click="showDrawer = false"></div>

          <!-- Panel -->
          <div class="w-full max-w-md bg-white shadow-2xl flex flex-col overflow-hidden">

            <!-- Drawer header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
              <h3 class="text-sm font-bold text-gray-800">Detail Pengguna</h3>
              <button @click="showDrawer = false" class="p-1.5 rounded-xl text-gray-400 hover:bg-gray-100 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <!-- Loading state -->
            <div v-if="drawerLoading" class="flex-1 flex items-center justify-center">
              <svg class="w-6 h-6 animate-spin text-[#ED1F24]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
              </svg>
            </div>

            <div v-else-if="drawerUser" class="flex-1 overflow-y-auto">

              <!-- User card -->
              <div class="px-6 py-6 border-b border-gray-100">
                <div class="flex items-center gap-4">
                  <div class="w-14 h-14 rounded-2xl overflow-hidden shrink-0" style="min-width:3.5rem;">
                    <img v-if="drawerUser.avatar_url" :src="drawerUser.avatar_url" :alt="drawerUser.name"
                         class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-lg font-bold"
                         :class="avatarClass(drawerUser.role)">
                      {{ initials(drawerUser.name) }}
                    </div>
                  </div>
                  <div>
                    <p class="text-base font-bold text-gray-800">{{ drawerUser.name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ drawerUser.email }}</p>
                    <div class="flex items-center gap-2 mt-2">
                      <span class="inline-flex items-center text-[10px] font-bold border px-2 py-0.5 rounded-full uppercase tracking-wider"
                            :class="roleBadgeClass(drawerUser.role)">
                        {{ roleLabel(drawerUser.role) }}
                      </span>
                      <span class="inline-flex items-center gap-1 text-[10px] font-bold border px-2 py-0.5 rounded-full uppercase tracking-wider"
                            :class="drawerUser.is_active ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : 'bg-red-50 border-red-100 text-red-500'">
                        <span class="w-1.5 h-1.5 rounded-full" :class="drawerUser.is_active ? 'bg-emerald-500' : 'bg-red-400'"></span>
                        {{ drawerUser.is_active ? 'Aktif' : 'Suspended' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Info rows -->
              <div class="px-6 py-4 space-y-4 border-b border-gray-100">
                <InfoRow label="Bergabung sejak" :value="formatDate(drawerUser.created_at)" />
                <InfoRow label="Last Login" :value="formatLastLogin(drawerUser.last_login_at)" />
              </div>

              <!-- Audit log -->
              <div class="px-6 py-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">Riwayat Aktivitas</p>
                <div v-if="drawerUser.audit_logs?.length === 0" class="text-xs text-gray-400 py-4 text-center">
                  Belum ada aktivitas tercatat.
                </div>
                <div v-else class="space-y-3">
                  <div v-for="log in drawerUser.audit_logs" :key="log.id"
                       class="flex items-start gap-3">
                    <span class="mt-0.5 w-6 h-6 rounded-lg flex items-center justify-center text-[10px] shrink-0"
                          :class="auditBadgeClass(log.action)">
                      {{ auditIcon(log.action) }}
                    </span>
                    <div class="min-w-0">
                      <p class="text-xs text-gray-700 leading-relaxed">{{ log.description }}</p>
                      <p class="text-[10px] text-gray-400 mt-0.5">{{ formatDate(log.created_at) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </AdminLayout>
</template>

<script>
import AdminLayout from '../../components/admin/AdminLayout.vue'
import { useUserManagementStore } from '../../store/userManagement.js'
import { formatDistanceToNow, parseISO } from 'date-fns'
import { id as localeId } from 'date-fns/locale'

// Reusable mini-components
const StatItem = {
  props: ['label', 'value'],
  template: `<div><p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">{{ label }}</p><p class="text-white text-lg font-bold tabular-nums">{{ value }}</p></div>`
}

const InfoRow = {
  props: ['label', 'value'],
  template: `<div class="flex justify-between items-center"><span class="text-xs text-gray-400">{{ label }}</span><span class="text-xs font-semibold text-gray-700">{{ value }}</span></div>`
}

// v-click-outside directive
const vClickOutside = {
  mounted(el, binding) {
    document.title = 'Users - Two Brothers Vape System'
    el._clickOutside = (e) => { if (!el.contains(e.target)) binding.value(e) }
    document.addEventListener('click', el._clickOutside)
  },
  unmounted(el) { document.removeEventListener('click', el._clickOutside) },
}

export default {
  name: 'Users',
  components: { AdminLayout, StatItem, InfoRow },
  directives: { 'click-outside': vClickOutside },

  setup() {
    const store = useUserManagementStore()
    return { store }
  },

  data() {
    return {
      // Filters
      search:       '',
      filterRole:   '',
      filterStatus: '',

      // Action menu
      openMenuId: null,

      // Create/Edit modal
      showModal:    false,
      modalMode:    'create',
      modalLoading: false,
      modalError:   '',
      selectedUser: null,
      form: { name: '', email: '', password: '', role: 'staff' },

      // Reset password modal
      showResetModal: false,
      resetLoading:   false,
      resetError:     '',
      resetForm:      { password: '', password_confirmation: '' },

      // Detail drawer
      showDrawer:    false,
      drawerLoading: false,
      drawerUser:    null,

      roles: [
        { value: 'admin',   label: 'Admin',   desc: 'Akses penuh', icon: '🛡️', activeClass: 'border-purple-200 bg-purple-50', textClass: 'text-purple-600', subTextClass: 'text-purple-400' },
        { value: 'manager', label: 'Manager', desc: 'Kelola tim',  icon: '👔', activeClass: 'border-emerald-200 bg-emerald-50', textClass: 'text-emerald-600', subTextClass: 'text-emerald-400' },
        { value: 'staff',   label: 'Staff',   desc: 'Akses dasar', icon: '👤', activeClass: 'border-blue-200 bg-blue-50', textClass: 'text-blue-600', subTextClass: 'text-blue-400' },
      ],
    }
  },

  computed: {
    filteredUsers() {
      return this.store.users.filter(user => {
        const q = this.search.toLowerCase()
        const matchSearch = !q || user.name.toLowerCase().includes(q) || user.email.toLowerCase().includes(q)
        const matchRole   = !this.filterRole   || user.role === this.filterRole
        const matchStatus = !this.filterStatus || user.status === this.filterStatus
        return matchSearch && matchRole && matchStatus
      })
    },
  },

  mounted() {
    this.store.fetchUsers()
  },

  methods: {
    // ── Formatting ────────────────────────────────────────────────
    initials(name) {
      return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
    },
    formatDate(dateStr) {
      if (!dateStr) return '-'
      return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
    },
    formatLastLogin(dateStr) {
      if (!dateStr) return 'Belum pernah login'
      try {
        return formatDistanceToNow(parseISO(dateStr), { addSuffix: true, locale: localeId })
      } catch {
        return '-'
      }
    },

    // ── Badge classes ─────────────────────────────────────────────
    avatarClass(role) {
      return { admin: 'bg-purple-50 border border-purple-100 text-purple-600', manager: 'bg-emerald-50 border border-emerald-100 text-emerald-600', staff: 'bg-blue-50 border border-blue-100 text-blue-600' }[role] ?? 'bg-gray-100 border border-gray-200 text-gray-500'
    },
    roleBadgeClass(role) {
      return { admin: 'bg-purple-50 border-purple-100 text-purple-600', manager: 'bg-emerald-50 border-emerald-100 text-emerald-600', staff: 'bg-blue-50 border-blue-100 text-blue-600' }[role] ?? 'bg-gray-100 border-gray-200 text-gray-400'
    },
    roleLabel(role) {
      return { admin: 'Admin', manager: 'Manager', staff: 'Staff' }[role] ?? role
    },
    auditBadgeClass(action) {
      return { login: 'bg-blue-50 text-blue-500', suspend: 'bg-red-50 text-red-500', activate: 'bg-emerald-50 text-emerald-600', reset_password: 'bg-amber-50 text-amber-600', update: 'bg-gray-100 text-gray-500', create: 'bg-purple-50 text-purple-500' }[action] ?? 'bg-gray-100 text-gray-400'
    },
    auditIcon(action) {
      return { login: '🔑', suspend: '🚫', activate: '✅', reset_password: '🔒', update: '✏️', create: '➕' }[action] ?? '📋'
    },

    // ── Action menu ───────────────────────────────────────────────
    toggleMenu(id) { this.openMenuId = this.openMenuId === id ? null : id },
    closeMenu(id)  { if (this.openMenuId === id) this.openMenuId = null },

    // ── Create / Edit modal ───────────────────────────────────────
    openCreateModal() {
      this.openMenuId  = null
      this.modalMode   = 'create'
      this.selectedUser = null
      this.form        = { name: '', email: '', password: '', role: 'staff' }
      this.modalError  = ''
      this.showModal   = true
    },
    openEditModal(user) {
      this.openMenuId  = null
      this.modalMode   = 'edit'
      this.selectedUser = user
      this.form        = { name: user.name, email: user.email, password: '', role: user.role }
      this.modalError  = ''
      this.showModal   = true
    },
    closeModal() { this.showModal = false; this.modalError = '' },

    async submitForm() {
      this.modalLoading = true
      this.modalError   = ''
      try {
        if (this.modalMode === 'create') {
          await this.store.createUser(this.form)
        } else {
          await this.store.updateUser(this.selectedUser.id, this.form)
        }
        this.closeModal()
      } catch (e) {
        this.modalError = e.response?.data?.message ?? 'Terjadi kesalahan, coba lagi.'
      } finally {
        this.modalLoading = false
      }
    },

    // ── Reset Password modal ──────────────────────────────────────
    openResetPasswordModal(user) {
      this.openMenuId            = null
      this.selectedUser          = user
      this.resetForm             = { password: '', password_confirmation: '' }
      this.resetError            = ''
      this.showResetModal        = true
    },

    async submitResetPassword() {
      this.resetLoading = true
      this.resetError   = ''
      try {
        await this.store.resetPassword(this.selectedUser.id, this.resetForm)
        this.showResetModal = false
        alert(`Password ${this.selectedUser.name} berhasil direset.`)
      } catch (e) {
        this.resetError = e.response?.data?.message ?? 'Gagal mereset password.'
      } finally {
        this.resetLoading = false
      }
    },

    // ── Suspend / Activate ────────────────────────────────────────
    async handleSuspend(user) {
      this.openMenuId = null
      if (!confirm(`Yakin ingin mensuspend ${user.name}? User akan dikeluarkan dari semua sesi aktif.`)) return
      try {
        await this.store.suspendUser(user.id)
      } catch (e) {
        alert(e.response?.data?.message ?? 'Gagal mensuspend user.')
      }
    },

    async handleActivate(user) {
      this.openMenuId = null
      if (!confirm(`Aktifkan kembali akun ${user.name}?`)) return
      try {
        await this.store.activateUser(user.id)
      } catch (e) {
        alert(e.response?.data?.message ?? 'Gagal mengaktifkan user.')
      }
    },

    // ── Detail Drawer ─────────────────────────────────────────────
    async openDetailDrawer(user) {
      this.openMenuId   = null
      this.drawerUser   = null
      this.drawerLoading = true
      this.showDrawer   = true
      try {
        this.drawerUser = await this.store.fetchUserDetail(user.id)
      } catch (e) {
        this.showDrawer = false
        alert('Gagal memuat detail user.')
      } finally {
        this.drawerLoading = false
      }
    },
  },
}
</script>

<style scoped>
/* Modal transition */
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

/* Drawer transition — slide from right */
.drawer-enter-active, .drawer-leave-active { transition: transform 0.25s ease; }
.drawer-enter-from, .drawer-leave-to { transform: translateX(100%); }

/* Dropdown transition */
.dropdown-enter-active, .dropdown-leave-active { transition: opacity 0.1s ease, transform 0.1s ease; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-4px); }
</style>