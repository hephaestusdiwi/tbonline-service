<template>
    <AdminLayout title="Promotion Management">

        <!-- ───────────────────────── HEADER ───────────────────────── -->
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18M9 21V9"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Promosi</h1>
                        <span class="text-[10px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 border border-gray-200">Manajemen Konten</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Kelola banner promosi dan konten iklan untuk customer</p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <!-- Stats Pills -->
                <div class="hidden sm:flex items-center gap-0 bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-4 py-2.5 text-center border-r border-gray-100">
                        <p class="text-base font-bold text-gray-900 tabular-nums">{{ promotions.length }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total</p>
                    </div>
                    <div class="px-4 py-2.5 text-center border-r border-gray-100">
                        <p class="text-base font-bold text-emerald-600 tabular-nums">{{ promotions.filter(p => p.is_active).length }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aktif</p>
                    </div>
                    <div class="px-4 py-2.5 text-center">
                        <p class="text-base font-bold text-gray-400 tabular-nums">{{ promotions.filter(p => !p.is_active).length }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nonaktif</p>
                    </div>
                </div>

                <div class="w-px h-8 bg-gray-200 hidden sm:block"></div>

                <button @click="openCreate"
                    class="flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all duration-150 shadow-sm shadow-red-200 hover:shadow-md hover:shadow-red-200 active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Promosi
                </button>
            </div>
        </div>

        <!-- ───────────────────────── TABLE ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden mb-4">

            <!-- Loading skeleton -->
            <template v-if="listLoading">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60">
                    <div class="h-3 bg-gray-200 rounded-full w-32 animate-pulse"></div>
                </div>
                <div v-for="n in 3" :key="n" class="px-5 py-4 border-b border-gray-50 flex items-center gap-4">
                    <div class="w-20 h-12 rounded-lg bg-gray-100 animate-pulse shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-3 bg-gray-100 rounded-full w-48 animate-pulse"></div>
                        <div class="h-2.5 bg-gray-100 rounded-full w-32 animate-pulse"></div>
                    </div>
                    <div class="h-5 bg-gray-100 rounded-full w-20 animate-pulse"></div>
                    <div class="h-5 bg-gray-100 rounded-full w-14 animate-pulse"></div>
                </div>
            </template>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm min-w-[780px]">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 w-8"></th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Banner</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Judul & Link</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Tipe Link</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Urutan</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Status</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400 w-28">Aksi</th>
                        </tr>
                    </thead>
                    <!-- Empty state -->
                    <tbody v-if="promotions.length === 0">
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18M9 21V9"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-500">Belum ada promosi</p>
                                    <span class="text-xs text-gray-400">Tambah banner promosi baru untuk ditampilkan ke customer</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>

                    <draggable
                        v-else
                        v-model="promotions"
                        item-key="id"
                        handle=".drag-handle"
                        tag="tbody"
                        class="divide-y divide-gray-50"
                        animation="180"
                        @end="saveOrder">
                        <template #item="{ element: item, index: i }">
                            <tr class="hover:bg-gray-50/60 transition-colors duration-150"
                                :class="{ 'opacity-50': !item.is_active }">

                                <!-- Drag handle -->
                                <td class="px-3 py-4">
                                    <button class="drag-handle flex items-center justify-center w-6 h-6 rounded text-gray-300 hover:text-gray-500 hover:bg-gray-100 transition-all cursor-grab active:cursor-grabbing">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                            <circle cx="9" cy="6" r="1.2" fill="currentColor"/>
                                            <circle cx="15" cy="6" r="1.2" fill="currentColor"/>
                                            <circle cx="9" cy="12" r="1.2" fill="currentColor"/>
                                            <circle cx="15" cy="12" r="1.2" fill="currentColor"/>
                                            <circle cx="9" cy="18" r="1.2" fill="currentColor"/>
                                            <circle cx="15" cy="18" r="1.2" fill="currentColor"/>
                                        </svg>
                                    </button>
                                </td>

                                <!-- Banner thumbnail -->
                                <td class="px-5 py-4">
                                    <div class="w-20 h-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 shrink-0">
                                        <img :src="item.image_url" :alt="item.title" class="w-full h-full object-cover"/>
                                    </div>
                                </td>

                                <!-- Title + link -->
                                <td class="px-5 py-4">
                                    <span class="font-semibold text-sm text-gray-800 block">{{ item.title }}</span>
                                    <a :href="item.link" target="_blank"
                                        class="text-xs text-gray-400 hover:text-[#ED1F24] flex items-center gap-1 mt-0.5 transition-colors">
                                        {{ truncate(item.link, 45) }}
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                            <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                                        </svg>
                                    </a>
                                </td>

                                <!-- Link type badge -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                        :class="linkTypeBadgeClass(item.link_type)">
                                        {{ labelLinkType(item.link_type) }}
                                    </span>
                                </td>

                                <!-- Order -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-[10px] font-bold text-gray-500">
                                        {{ i + 1 }}
                                    </span>
                                </td>

                                <!-- Status toggle -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <button @click="toggleActive(item)"
                                            class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200 focus:outline-none"
                                            :class="item.is_active ? 'bg-emerald-500' : 'bg-gray-200'">
                                            <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform duration-200"
                                                :class="item.is_active ? 'translate-x-4' : 'translate-x-1'"></span>
                                        </button>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                                            :class="item.is_active
                                                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                                                : 'bg-red-50 text-red-400 border-red-100'">
                                            {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="openEdit(item)" title="Edit"
                                            class="flex items-center justify-center w-7 h-7 rounded-lg border border-amber-200 bg-amber-50 text-amber-500 hover:bg-amber-100 hover:border-amber-300 transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <button @click="confirmDelete(item)" title="Hapus"
                                            class="flex items-center justify-center w-7 h-7 rounded-lg border border-red-100 bg-red-50 text-red-400 hover:bg-red-100 hover:border-red-200 transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </draggable>
                </table>
            </div>
        </div>

        <!-- ═══════════════════════════ MODAL CREATE / EDIT ═══════════════════════════ -->
        <Transition name="pc-modal">
            <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                style="background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);"
                @click.self="closeModal">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg border border-gray-200/80 flex flex-col max-h-[90vh] overflow-hidden">

                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                :class="isEditing ? 'bg-amber-50 border border-amber-200' : 'bg-[#ED1F24]/10 border border-[#ED1F24]/20'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                                    :class="isEditing ? 'text-amber-500' : 'text-[#ED1F24]'">
                                    <template v-if="isEditing"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></template>
                                    <template v-else><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></template>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800">{{ isEditing ? 'Edit Promosi' : 'Tambah Promosi' }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5">{{ isEditing ? form.title || 'Promosi' : 'Banner baru' }}</p>
                            </div>
                        </div>
                        <button @click="closeModal" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <!-- Error banner -->
                    <div v-if="Object.keys(formErrors).length" class="flex items-center gap-2.5 px-6 py-3 bg-red-50 border-b border-red-100 shrink-0">
                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span class="text-xs font-medium text-red-600">{{ Object.values(formErrors)[0] }}</span>
                    </div>

                    <!-- Body -->
                    <div class="overflow-y-auto flex-1 px-6 py-5 flex flex-col gap-6">

                        <!-- Section: Informasi Promosi -->
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18M9 21V9"/></svg>
                                Informasi Promosi
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Judul <span class="text-[#ED1F24]">*</span></label>
                                    <input v-model="form.title" type="text" placeholder="cth: Promo Ramadan 2025"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all"
                                        :class="{ 'border-red-400': formErrors.title }"/>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">URL Tujuan <span class="text-[#ED1F24]">*</span></label>
                                    <input v-model="form.link" type="url" placeholder="https://..."
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all"
                                        :class="{ 'border-red-400': formErrors.link }"/>
                                </div>
                                <div>
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

                        <!-- Section: Tipe Link -->
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                Tipe Link
                            </p>
                            <div class="grid grid-cols-3 gap-2">
                                <button v-for="opt in linkTypeOptions" :key="opt.value"
                                    @click="form.link_type = opt.value"
                                    class="flex flex-col items-center gap-1.5 p-3 border-2 rounded-xl transition-all duration-150 text-center"
                                    :class="form.link_type === opt.value
                                        ? 'border-[#ED1F24] bg-[#ED1F24]/5'
                                        : 'border-gray-200 hover:border-gray-300 bg-white'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                        :class="form.link_type === opt.value ? 'text-[#ED1F24]' : 'text-gray-400'"
                                        v-html="opt.icon"></svg>
                                    <span class="text-xs font-bold" :class="form.link_type === opt.value ? 'text-[#ED1F24]' : 'text-gray-600'">{{ opt.label }}</span>
                                    <span class="text-[10px] text-gray-400 leading-tight">{{ opt.desc }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Section: Gambar Banner -->
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                Gambar Banner
                            </p>
                            <div
                                class="border-2 border-dashed rounded-xl overflow-hidden cursor-pointer transition-all duration-200 min-h-[120px] flex items-center justify-center"
                                :class="[dragging ? 'border-[#ED1F24] bg-[#ED1F24]/5' : previewUrl ? 'border-gray-200' : 'border-gray-200 hover:border-[#ED1F24] hover:bg-[#ED1F24]/5']"
                                @dragover.prevent="dragging = true"
                                @dragleave="dragging = false"
                                @drop.prevent="onDrop"
                                @click="$refs.fileInput.click()">
                                <img v-if="previewUrl" :src="previewUrl" class="w-full aspect-[16/7] object-cover block" alt="preview"/>
                                <div v-else class="flex flex-col items-center gap-2 py-8 px-4 text-center">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-500">Klik atau seret gambar ke sini</p>
                                    <span class="text-xs text-gray-400">JPG, PNG, WEBP — maks 5 MB &nbsp;|&nbsp; Rasio 16:10 disarankan</span>
                                </div>
                            </div>
                            <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileChange"/>
                            <p v-if="formErrors.image" class="text-[10px] text-red-500 mt-1">{{ formErrors.image }}</p>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex justify-between items-center shrink-0">
                        <button @click="closeModal" class="text-xs font-semibold text-gray-500 border border-gray-200 px-4 py-2 rounded-lg hover:bg-white transition-all">
                            Batal
                        </button>
                        <button @click="submitForm" :disabled="saving"
                            class="flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all shadow-sm shadow-red-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                            <svg v-if="saving" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                            {{ saving ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Tambah Promosi') }}
                        </button>
                    </div>

                </div>
            </div>
        </Transition>

        <!-- ═══════════════════════════ MODAL HAPUS ═══════════════════════════ -->
        <Transition name="pc-modal">
            <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                style="background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);"
                @click.self="deleteTarget = null">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm border border-gray-200/80 overflow-hidden">

                    <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-50 border border-red-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-800">Hapus Promosi?</h3>
                    </div>

                    <div class="px-6 py-5">
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Banner <span class="font-semibold text-gray-800">{{ deleteTarget?.title }}</span> akan dihapus secara permanen dan tidak dapat dikembalikan.
                        </p>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex justify-between items-center">
                        <button @click="deleteTarget = null" class="text-xs font-semibold text-gray-500 border border-gray-200 px-4 py-2 rounded-lg hover:bg-white transition-all">
                            Batal
                        </button>
                        <button @click="doDelete" :disabled="saving"
                            class="flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white transition-all disabled:opacity-50 active:scale-95">
                            <svg v-if="saving" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                            {{ saving ? 'Menghapus...' : 'Ya, Hapus' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ═══════════════════════════ TOAST ═══════════════════════════ -->
        <Transition name="toast">
            <div v-if="toast.show"
                class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[100] flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-semibold shadow-lg whitespace-nowrap"
                :class="toast.type === 'success' ? 'bg-gray-900 text-white' : 'bg-red-500 text-white'">
                <svg v-if="toast.type === 'success'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ toast.message }}
            </div>
        </Transition>

    </AdminLayout>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import draggable from 'vuedraggable'
import axiosInstance from '../../axios'
import AdminLayout from '../../components/admin/AdminLayout.vue'

export default {
    name: 'PromotionAdmin',
    components: { AdminLayout, draggable },

    setup() {
        const promotions   = ref([])
        const listLoading  = ref(false)
        const saving       = ref(false)
        const modalOpen    = ref(false)
        const isEditing    = ref(false)
        const editId       = ref(null)
        const deleteTarget = ref(null)
        const dragging     = ref(false)
        const fileInput    = ref(null)
        const previewUrl   = ref(null)
        const selectedFile = ref(null)

        const form = reactive({ title: '', link: '', link_type: 'instagram', is_active: true })
        const formErrors = reactive({})
        const toast = reactive({ show: false, message: '', type: 'success' })

        const linkTypeOptions = [
            { value: 'instagram', label: 'Instagram',   desc: 'Link ke post/profil',  icon: '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>' },
            { value: 'artikel',   label: 'Artikel/Blog', desc: 'Link ke artikel',      icon: '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>' },
            { value: 'other',     label: 'Lainnya',      desc: 'Link eksternal lain',  icon: '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>' },
        ]

        async function fetchList() {
            listLoading.value = true
            try {
                const { data } = await axiosInstance.get('/admin/promotions')
                promotions.value = data.data
            } catch {
                showToast('Gagal memuat data promosi.', 'error')
            } finally {
                listLoading.value = false
            }
        }

        function openCreate() {
            isEditing.value = false; editId.value = null
            Object.assign(form, { title: '', link: '', link_type: 'instagram', is_active: true })
            previewUrl.value = null; selectedFile.value = null
            clearErrors(); modalOpen.value = true
        }

        function openEdit(item) {
            isEditing.value = true; editId.value = item.id
            Object.assign(form, { title: item.title, link: item.link, link_type: item.link_type, is_active: item.is_active })
            previewUrl.value = item.image_url; selectedFile.value = null
            clearErrors(); modalOpen.value = true
        }

        function closeModal() { modalOpen.value = false }

        function onFileChange(e) {
            const file = e.target.files[0]
            if (file) applyFile(file)
        }

        function onDrop(e) {
            dragging.value = false
            const file = e.dataTransfer.files[0]
            if (file) applyFile(file)
        }

        function applyFile(file) {
            if (file.size > 5 * 1024 * 1024) { formErrors.image = 'Ukuran file maksimal 5 MB.'; return }
            selectedFile.value = file
            previewUrl.value = URL.createObjectURL(file)
            delete formErrors.image
        }

        function validate() {
            clearErrors()
            if (!form.title.trim()) formErrors.title = 'Judul wajib diisi.'
            if (!form.link.trim())  formErrors.link  = 'URL wajib diisi.'
            if (!isEditing.value && !selectedFile.value) formErrors.image = 'Gambar wajib dipilih.'
            return Object.keys(formErrors).length === 0
        }

        function clearErrors() { Object.keys(formErrors).forEach(k => delete formErrors[k]) }

        async function submitForm() {
            if (!validate()) return
            saving.value = true
            try {
                const fd = new FormData()
                fd.append('title', form.title); fd.append('link', form.link)
                fd.append('link_type', form.link_type); fd.append('is_active', form.is_active ? 1 : 0)
                if (selectedFile.value) fd.append('image', selectedFile.value)
                if (isEditing.value) {
                    await axiosInstance.post(`/admin/promotions/${editId.value}`, fd)
                    showToast('Promosi berhasil diperbarui.')
                } else {
                    await axiosInstance.post('/admin/promotions', fd)
                    showToast('Promosi berhasil ditambahkan.')
                }
                closeModal(); await fetchList()
            } catch (err) {
                const errors = err.response?.data?.errors
                if (errors) Object.assign(formErrors, Object.fromEntries(Object.entries(errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])))
                else showToast('Terjadi kesalahan. Coba lagi.', 'error')
            } finally { saving.value = false }
        }

        async function toggleActive(item) {
            const original = item.is_active
            item.is_active = !item.is_active
            try {
                const fd = new FormData()
                fd.append('is_active', item.is_active ? 1 : 0)
                // Hapus _method: PUT — route sudah POST
                await axiosInstance.post(`/admin/promotions/${item.id}`, fd)
                showToast(item.is_active ? 'Promosi diaktifkan.' : 'Promosi dinonaktifkan.')
            } catch {
                item.is_active = original
                showToast('Gagal mengubah status.', 'error')
            }
        }

        function confirmDelete(item) { deleteTarget.value = item }

        async function doDelete() {
            if (!deleteTarget.value) return
            saving.value = true
            try {
                await axiosInstance.delete(`/admin/promotions/${deleteTarget.value.id}`)
                showToast('Promosi berhasil dihapus.'); deleteTarget.value = null; await fetchList()
            } catch { showToast('Gagal menghapus promosi.', 'error') }
            finally { saving.value = false }
        }

        async function saveOrder() {
            const items = promotions.value.map((p, i) => ({ id: p.id, order: i }))
            try { await axiosInstance.patch('/admin/promotions/reorder', { items }); showToast('Urutan disimpan.') }
            catch { showToast('Gagal menyimpan urutan.', 'error') }
        }

        function showToast(message, type = 'success') {
            Object.assign(toast, { show: true, message, type })
            setTimeout(() => { toast.show = false }, 3000)
        }

        function truncate(str, n) { return str?.length > n ? str.slice(0, n) + '…' : str }

        function labelLinkType(type) {
            return { instagram: 'Instagram', artikel: 'Artikel', other: 'Lainnya' }[type] ?? type
        }

        function linkTypeBadgeClass(type) {
            return {
                instagram: 'bg-pink-50 text-pink-600 border-pink-100',
                artikel:   'bg-blue-50 text-blue-600 border-blue-100',
                other:     'bg-gray-100 text-gray-500 border-gray-200',
            }[type] ?? 'bg-gray-100 text-gray-500 border-gray-200'
        }

        onMounted(() => {
            document.title = 'Promotion Banners - Two Brothers Vape System'
            fetchList()
        })

        return {
            promotions, listLoading, saving, modalOpen, isEditing,
            deleteTarget, dragging, fileInput, previewUrl,
            form, formErrors, toast, linkTypeOptions,
            openCreate, openEdit, closeModal,
            onFileChange, onDrop,
            submitForm, toggleActive, confirmDelete, doDelete,
            saveOrder, truncate, labelLinkType, linkTypeBadgeClass,
        }
    }
}
</script>

<style scoped>
.pc-modal-enter-active, .pc-modal-leave-active { transition: all .2s; }
.pc-modal-enter-from, .pc-modal-leave-to { opacity: 0; transform: scale(.97); }

.toast-enter-active, .toast-leave-active { transition: opacity .25s, transform .25s; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(12px); }
</style>