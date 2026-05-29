<template>
    <AdminLayout title="Footer Links">

        <!-- ═══════════════════════════════════════════
             HERO HEADER — konsisten dengan Dashboard
        ═══════════════════════════════════════════ -->
        <div class="relative mb-6 rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"></div>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10" style="background: white;"></div>

            <div class="relative px-7 py-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">Pengaturan Website</p>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Footer Links</h1>
                    <p class="text-red-200 text-xs mt-1.5">Kelola grup dan link yang tampil di footer website</p>
                </div>
                <button
                    @click="openGroupModal()"
                    class="flex items-center gap-2 text-xs font-semibold px-4 py-2.5 rounded-xl border border-white/30 bg-white/15 text-white hover:bg-white/25 transition-all"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Grup
                </button>
            </div>

            <!-- Stats strip -->
            <div class="relative border-t border-white/10 px-7 py-3 flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-6">
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Total Grup</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ groups.length }}</p>
                    </div>
                    <div class="w-px h-8 bg-white/15"></div>
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Total Link</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ totalLinks }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-20 flex items-center justify-center">
            <svg class="w-6 h-6 animate-spin text-[#ED1F24]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
            </svg>
        </div>

        <!-- Empty state -->
        <div v-else-if="!groups.length" class="bg-white rounded-xl border border-gray-200/80 shadow-sm">
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 rounded-2xl bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-600">Belum ada grup footer</p>
                <p class="text-xs text-gray-400 mt-1">Tambah grup baru untuk mulai mengelola link footer.</p>
                <button
                    @click="openGroupModal()"
                    class="mt-4 flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Grup Pertama
                </button>
            </div>
        </div>

        <!-- Groups + Links -->
        <div v-else class="space-y-4">
            <div
                v-for="group in groups"
                :key="group.id"
                class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden"
            >
                <!-- Group Header — mirip card header di Dashboard -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">{{ group.name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ group.links?.length || 0 }} link</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="openLinkModal(group)"
                            class="flex items-center gap-1.5 text-xs font-semibold text-[#ED1F24] border border-[#ED1F24]/20 hover:border-[#ED1F24]/40 hover:bg-red-50/50 px-3 py-1.5 rounded-xl transition-all"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Link
                        </button>
                        <button
                            @click="openGroupModal(group)"
                            class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 border border-gray-200 hover:border-gray-300 hover:bg-gray-50 px-3 py-1.5 rounded-xl transition-all"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button
                            @click="confirmDeleteGroup(group)"
                            class="flex items-center gap-1.5 text-xs font-semibold text-red-500 border border-red-200 hover:border-red-300 hover:bg-red-50 px-3 py-1.5 rounded-xl transition-all"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                            Hapus
                        </button>
                    </div>
                </div>

                <!-- Links Table — mirip tabel recent orders di Dashboard -->
                <div v-if="group.links?.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50/60 border-b border-gray-100">
                                <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-6 py-3">Label</th>
                                <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-6 py-3">URL</th>
                                <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-6 py-3">Urutan</th>
                                <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-6 py-3">Tab Baru</th>
                                <th class="text-right text-[10px] font-bold uppercase tracking-widest text-gray-400 px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="link in group.links"
                                :key="link.id"
                                class="hover:bg-gray-50/60 transition-colors duration-150"
                            >
                                <td class="px-6 py-3.5 text-sm font-semibold text-gray-700">{{ link.label }}</td>
                                <td class="px-6 py-3.5">
                                    <a
                                        :href="link.url"
                                        target="_blank"
                                        class="text-[#ED1F24]/70 hover:text-[#ED1F24] text-xs font-mono truncate max-w-[220px] block transition-colors"
                                    >
                                        {{ link.url }}
                                    </a>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md tabular-nums">{{ link.sort_order }}</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span
                                        :class="link.open_new_tab
                                            ? 'bg-emerald-50 border-emerald-100 text-emerald-600'
                                            : 'bg-gray-100 border-gray-200 text-gray-400'"
                                        class="inline-flex items-center text-[10px] font-bold border px-2 py-0.5 rounded-full uppercase tracking-wider"
                                    >
                                        {{ link.open_new_tab ? 'Ya' : 'Tidak' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <button
                                            @click="openLinkModal(group, link)"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button
                                            @click="confirmDeleteLink(link)"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-6 py-8 text-center">
                    <p class="text-xs text-gray-400 mb-3">Belum ada link di grup ini.</p>
                    <button
                        @click="openLinkModal(group)"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#ED1F24] border border-[#ED1F24]/20 hover:border-[#ED1F24]/40 hover:bg-red-50/50 px-3 py-1.5 rounded-xl transition-all"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Link Pertama
                    </button>
                </div>
            </div>
        </div>

        <!-- ══════════════════════
             MODAL: Grup
        ══════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="groupModal.show"
                    class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                    @click.self="closeGroupModal"
                >
                    <div class="bg-white border border-gray-200/80 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
                        <!-- Modal header -->
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800">
                                {{ groupModal.editing ? 'Edit Grup' : 'Tambah Grup Baru' }}
                            </h3>
                        </div>

                        <!-- Modal body -->
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Nama Grup</label>
                                <input
                                    v-model="groupModal.form.name"
                                    type="text"
                                    placeholder="Contoh: Customer Service"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50"
                                    @keyup.enter="saveGroup"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Urutan Tampil</label>
                                <input
                                    v-model.number="groupModal.form.sort_order"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50"
                                />
                                <p class="text-[10px] text-gray-400 mt-1.5">Angka lebih kecil tampil lebih dulu (kiri).</p>
                            </div>
                            <div v-if="groupModal.error" class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-xs">
                                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>
                                {{ groupModal.error }}
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <button @click="closeGroupModal" class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-xl transition-all">Batal</button>
                            <button
                                @click="saveGroup"
                                :disabled="!groupModal.form.name || groupModal.saving"
                                class="flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm"
                            >
                                <svg v-if="groupModal.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ groupModal.saving ? 'Menyimpan...' : (groupModal.editing ? 'Simpan Perubahan' : 'Buat Grup') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ══════════════════════
             MODAL: Link
        ══════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="linkModal.show"
                    class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                    @click.self="closeLinkModal"
                >
                    <div class="bg-white border border-gray-200/80 rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                        <!-- Modal header -->
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800">
                                    {{ linkModal.editing ? 'Edit Link' : 'Tambah Link Baru' }}
                                </h3>
                                <p class="text-xs text-gray-400 mt-0.5">Grup: <span class="font-semibold text-gray-600">{{ linkModal.groupName }}</span></p>
                            </div>
                        </div>

                        <!-- Modal body -->
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Label</label>
                                <input
                                    v-model="linkModal.form.label"
                                    type="text"
                                    placeholder="Contoh: Kebijakan Privasi"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">URL</label>
                                <input
                                    v-model="linkModal.form.url"
                                    type="text"
                                    placeholder="/kebijakan-privasi atau https://..."
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50 font-mono"
                                />
                                <p class="text-[10px] text-gray-400 mt-1.5">Gunakan path relatif atau URL lengkap.</p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Urutan Tampil</label>
                                <input
                                    v-model.number="linkModal.form.sort_order"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50"
                                />
                            </div>

                            <!-- Toggle buka tab baru -->
                            <div>
                                <label class="flex items-center gap-3 cursor-pointer group p-3 rounded-xl border border-gray-100 hover:border-gray-200 hover:bg-gray-50/50 transition-all">
                                    <div class="relative shrink-0">
                                        <input v-model="linkModal.form.open_new_tab" type="checkbox" class="sr-only" />
                                        <div
                                            class="w-10 h-5 rounded-full transition-colors duration-200"
                                            :class="linkModal.form.open_new_tab ? 'bg-[#ED1F24]' : 'bg-gray-200'"
                                        ></div>
                                        <div
                                            class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow-sm transition-transform duration-200"
                                            :class="linkModal.form.open_new_tab ? 'translate-x-5' : 'translate-x-0'"
                                        ></div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700">Buka di Tab Baru</p>
                                        <p class="text-xs text-gray-400">Link akan terbuka di tab browser baru</p>
                                    </div>
                                </label>
                            </div>

                            <div v-if="linkModal.error" class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-xs">
                                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>
                                {{ linkModal.error }}
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <button @click="closeLinkModal" class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-xl transition-all">Batal</button>
                            <button
                                @click="saveLink"
                                :disabled="!linkModal.form.label || !linkModal.form.url || linkModal.saving"
                                class="flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm"
                            >
                                <svg v-if="linkModal.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ linkModal.saving ? 'Menyimpan...' : (linkModal.editing ? 'Simpan Perubahan' : 'Tambah Link') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ══════════════════════
             MODAL: Konfirmasi Hapus
        ══════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="deleteModal.show"
                    class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                    @click.self="deleteModal.show = false"
                >
                    <div class="bg-white border border-gray-200/80 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
                        <div class="p-6">
                            <div class="w-11 h-11 rounded-xl bg-red-50 border border-red-200 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800 mb-1">{{ deleteModal.title }}</h3>
                            <p class="text-sm text-gray-500">{{ deleteModal.message }}</p>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <button @click="deleteModal.show = false" class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-xl transition-all">Batal</button>
                            <button
                                @click="executeDelete"
                                :disabled="deleteModal.deleting"
                                class="flex items-center gap-2 bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm"
                            >
                                <svg v-if="deleteModal.deleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                                </svg>
                                {{ deleteModal.deleting ? 'Menghapus...' : 'Ya, Hapus' }}
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
    name: 'FooterLinks',
    components: { AdminLayout },

    data() {
        return {
            groups: [],
            loading: true,

            groupModal: {
                show: false,
                editing: null,
                saving: false,
                error: '',
                form: { name: '', sort_order: 0 },
            },

            linkModal: {
                show: false,
                editing: null,
                groupId: null,
                groupName: '',
                saving: false,
                error: '',
                form: { label: '', url: '', sort_order: 0, open_new_tab: false },
            },

            deleteModal: {
                show: false,
                type: null,
                target: null,
                title: '',
                message: '',
                deleting: false,
            },
        }
    },

    computed: {
        totalLinks() {
            return this.groups.reduce((sum, g) => sum + (g.links?.length || 0), 0)
        },
    },

    mounted() {
        this.fetchGroups()
    },

    methods: {
        async fetchGroups() {
            this.loading = true
            try {
                const { data } = await axios.get('/admin/footer-link-groups')
                this.groups = data
            } catch (e) {
                console.error('Failed to fetch footer groups:', e)
            } finally {
                this.loading = false
            }
        },

        openGroupModal(group = null) {
            this.groupModal.editing = group
            this.groupModal.form = group
                ? { name: group.name, sort_order: group.sort_order ?? 0 }
                : { name: '', sort_order: this.groups.length }
            this.groupModal.error = ''
            this.groupModal.show = true
        },
        closeGroupModal() { this.groupModal.show = false },
        async saveGroup() {
            if (!this.groupModal.form.name) return
            this.groupModal.saving = true
            this.groupModal.error = ''
            try {
                if (this.groupModal.editing) {
                    const { data } = await axios.put(`/admin/footer-link-groups/${this.groupModal.editing.id}`, this.groupModal.form)
                    const idx = this.groups.findIndex(g => g.id === this.groupModal.editing.id)
                    if (idx !== -1) this.groups[idx] = { ...this.groups[idx], ...data }
                } else {
                    const { data } = await axios.post('/admin/footer-link-groups', this.groupModal.form)
                    this.groups.push({ ...data, links: [] })
                }
                this.closeGroupModal()
            } catch (e) {
                this.groupModal.error = e.response?.data?.message ?? 'Gagal menyimpan grup.'
            } finally {
                this.groupModal.saving = false
            }
        },

        openLinkModal(group, link = null) {
            this.linkModal.groupId   = group.id
            this.linkModal.groupName = group.name
            this.linkModal.editing   = link
            this.linkModal.form = link
                ? { label: link.label, url: link.url, sort_order: link.sort_order ?? 0, open_new_tab: !!link.open_new_tab }
                : { label: '', url: '', sort_order: group.links?.length ?? 0, open_new_tab: false }
            this.linkModal.error = ''
            this.linkModal.show  = true
        },
        closeLinkModal() { this.linkModal.show = false },
        async saveLink() {
            if (!this.linkModal.form.label || !this.linkModal.form.url) return
            this.linkModal.saving = true
            this.linkModal.error  = ''
            try {
                const payload = { ...this.linkModal.form, group_id: this.linkModal.groupId }
                if (this.linkModal.editing) {
                    const { data } = await axios.put(`/admin/footer-links/${this.linkModal.editing.id}`, payload)
                    const group = this.groups.find(g => g.id === this.linkModal.groupId)
                    if (group) {
                        const idx = group.links.findIndex(l => l.id === this.linkModal.editing.id)
                        if (idx !== -1) group.links[idx] = data
                    }
                } else {
                    const { data } = await axios.post('/admin/footer-links', payload)
                    const group = this.groups.find(g => g.id === this.linkModal.groupId)
                    if (group) group.links.push(data)
                }
                this.closeLinkModal()
            } catch (e) {
                this.linkModal.error = e.response?.data?.message ?? 'Gagal menyimpan link.'
            } finally {
                this.linkModal.saving = false
            }
        },

        confirmDeleteGroup(group) {
            this.deleteModal = {
                show: true, type: 'group', target: group,
                title: `Hapus Grup "${group.name}"?`,
                message: `Grup beserta semua link di dalamnya (${group.links?.length || 0} link) akan dihapus permanen.`,
                deleting: false,
            }
        },
        confirmDeleteLink(link) {
            this.deleteModal = {
                show: true, type: 'link', target: link,
                title: `Hapus Link "${link.label}"?`,
                message: 'Link ini akan dihapus permanen dari footer website.',
                deleting: false,
            }
        },
        async executeDelete() {
            this.deleteModal.deleting = true
            try {
                if (this.deleteModal.type === 'group') {
                    await axios.delete(`/admin/footer-link-groups/${this.deleteModal.target.id}`)
                    this.groups = this.groups.filter(g => g.id !== this.deleteModal.target.id)
                } else {
                    await axios.delete(`/admin/footer-links/${this.deleteModal.target.id}`)
                    for (const group of this.groups) {
                        group.links = group.links?.filter(l => l.id !== this.deleteModal.target.id) ?? []
                    }
                }
                this.deleteModal.show = false
            } catch (e) {
                console.error('Failed to delete:', e)
            } finally {
                this.deleteModal.deleting = false
            }
        },
    },
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>