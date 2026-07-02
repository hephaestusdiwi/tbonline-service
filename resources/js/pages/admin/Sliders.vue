<template>
    <AdminLayout title="Slider Management">

        <!-- ═══════════════════════════════════════════
             HERO HEADER
        ═══════════════════════════════════════════ -->
        <div class="relative mb-6 rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"></div>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10" style="background: white;"></div>

            <div class="relative px-7 py-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">Content Management</p>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Slider Management</h1>
                    <p class="text-red-200 text-xs mt-1.5">Kelola konten slider yang tampil di halaman utama</p>
                </div>
                <button @click="openModal('create')"
                        class="flex items-center gap-2 text-sm font-semibold px-4 py-2.5 rounded-xl border border-white/30 bg-white/15 text-white hover:bg-white/25 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Slider
                </button>
            </div>

            <!-- Stats strip -->
            <div class="relative border-t border-white/10 px-7 py-3 flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-6">
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Total</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ sliders.length }}</p>
                    </div>
                    <div class="w-px h-8 bg-white/15"></div>
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Aktif</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ activeCount }}</p>
                    </div>
                    <div class="w-px h-8 bg-white/15"></div>
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Gambar</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ imageCount }}</p>
                    </div>
                    <div class="w-px h-8 bg-white/15"></div>
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Video</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ videoCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white border border-gray-200/80 rounded-xl shadow-sm px-4 py-3 mb-4 flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input v-model="search" type="text" placeholder="Cari slider..."
                       class="w-full pl-9 pr-4 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:border-[#ED1F24] transition-colors" />
            </div>

            <!-- Filter pills -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <button v-for="f in filters" :key="f.value" @click="activeFilter = f.value"
                        class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-all border"
                        :class="activeFilter === f.value
                            ? 'bg-[#ED1F24] text-white border-[#ED1F24] shadow-sm'
                            : 'bg-white text-gray-500 border-gray-200 hover:border-gray-300 hover:text-gray-700'">
                    {{ f.label }}
                </button>
            </div>

            <!-- View toggle -->
            <div class="flex items-center gap-1 border border-gray-200 rounded-xl p-1 bg-gray-50 ml-auto">
                <button @click="viewMode = 'table'"
                        class="p-1.5 rounded-lg transition-all"
                        :class="viewMode === 'table' ? 'bg-white shadow-sm text-[#ED1F24]' : 'text-gray-400 hover:text-gray-600'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                </button>
                <button @click="viewMode = 'grid'"
                        class="p-1.5 rounded-lg transition-all"
                        :class="viewMode === 'grid' ? 'bg-white shadow-sm text-[#ED1F24]' : 'text-gray-400 hover:text-gray-600'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </button>
            </div>
        </div>

        <!-- TABLE VIEW -->
        <div v-if="viewMode === 'table'" class="bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-4 py-3 w-10"></th>
                            <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-4 py-3 w-28">Preview</th>
                            <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-4 py-3">Judul</th>
                            <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-4 py-3">Tipe</th>
                            <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-4 py-3">Status</th>
                            <th class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 px-4 py-3">Dibuat</th>
                            <th class="text-right text-[10px] font-bold uppercase tracking-widest text-gray-400 px-4 py-3 w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody ref="sortableTable" class="divide-y divide-gray-50">

                        <!-- Empty -->
                        <tr v-if="filteredSliders.length === 0">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-500">Belum ada slider</p>
                                    <p class="text-xs text-gray-400">Klik "+ Tambah Slider" untuk membuat slider baru</p>
                                </div>
                            </td>
                        </tr>

                        <tr v-for="slider in filteredSliders" :key="slider.id" :data-id="slider.id"
                            class="hover:bg-gray-50/60 transition-colors duration-150 group">
                            <!-- Drag handle -->
                            <td class="px-4 py-3">
                                <div class="sm-drag-handle flex items-center justify-center w-7 h-7 rounded-lg cursor-grab text-gray-300 hover:text-gray-500 hover:bg-gray-100 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="5" r="1.2"/><circle cx="9" cy="12" r="1.2"/><circle cx="9" cy="19" r="1.2"/><circle cx="15" cy="5" r="1.2"/><circle cx="15" cy="12" r="1.2"/><circle cx="15" cy="19" r="1.2"/></svg>
                                </div>
                            </td>

                            <!-- Preview -->
                            <td class="px-4 py-3">
                                <div class="relative w-20 h-12 rounded-lg overflow-hidden border border-gray-200 bg-gray-100 flex-shrink-0">
                                    <img v-if="slider.type === 'image'" :src="slider.file_url" class="w-full h-full object-cover" />
                                    <video v-else :src="slider.file_url" class="w-full h-full object-cover" />

                                    <!-- Badge processing -->
                                    <div v-if="slider.is_processing"
                                        class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                        <span class="text-[9px] font-bold text-white text-center leading-tight px-1">
                                            ⏳ Processing
                                        </span>
                                    </div>

                                    <div v-else class="absolute bottom-1 right-1 w-5 h-5 rounded-md flex items-center justify-center"
                                        :class="slider.type === 'image' ? 'bg-blue-500' : 'bg-amber-500'">
                                        <svg v-if="slider.type === 'image'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        <svg v-else class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                    </div>
                                </div>
                            </td>

                            <!-- Title -->
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-gray-700">{{ slider.title }}</span>
                            </td>

                            <!-- Type badge -->
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center text-[10px] font-bold border px-2 py-0.5 rounded-full uppercase tracking-wider"
                                      :class="slider.type === 'image'
                                          ? 'bg-blue-50 border-blue-100 text-blue-600'
                                          : 'bg-amber-50 border-amber-100 text-amber-600'">
                                    {{ slider.type === 'image' ? 'Gambar' : 'Video' }}
                                </span>
                            </td>

                            <!-- Status toggle -->
                            <td class="px-4 py-3">
                                <button @click="toggleActive(slider)"
                                        class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-xl border transition-all"
                                        :class="slider.is_active
                                            ? 'bg-emerald-50 border-emerald-100 text-emerald-600 hover:bg-emerald-100'
                                            : 'bg-gray-100 border-gray-200 text-gray-400 hover:bg-gray-200'">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                          :class="slider.is_active ? 'bg-emerald-400' : 'bg-gray-300'"></span>
                                    {{ slider.is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>

                            <!-- Date -->
                            <td class="px-4 py-3 text-xs text-gray-400">{{ formatDate(slider.created_at) }}</td>

                            <!-- Actions -->
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openPreview(slider)"
                                            class="flex items-center gap-1 text-xs font-semibold text-gray-500 border border-gray-200 hover:border-gray-300 hover:text-gray-700 hover:bg-gray-50 px-2.5 py-1.5 rounded-lg transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Lihat
                                    </button>
                                    <button @click="openModal('edit', slider)"
                                            class="flex items-center gap-1 text-xs font-semibold text-[#ED1F24] border border-[#ED1F24]/20 hover:border-[#ED1F24]/40 hover:bg-red-50 px-2.5 py-1.5 rounded-lg transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    <button @click="deleteSlider(slider.id)"
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

        <!-- GRID VIEW -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <!-- Empty -->
            <div v-if="filteredSliders.length === 0" class="col-span-full bg-white border border-gray-200/80 rounded-xl shadow-sm">
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                    </div>
                    <p class="text-sm font-bold text-gray-500">Belum ada slider</p>
                </div>
            </div>

            <div v-for="slider in filteredSliders" :key="slider.id"
                 class="bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden group hover:shadow-md hover:border-gray-300 transition-all duration-200">
                <!-- Media -->
                <div class="relative aspect-video bg-gray-100 overflow-hidden">
                    <img v-if="slider.type === 'image'" :src="slider.file_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                    <video v-else :src="slider.file_url" class="w-full h-full object-cover" />

                    <!-- Overlay actions -->
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all duration-200 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                        <button @click="openPreview(slider)"
                                class="w-9 h-9 rounded-xl bg-white text-gray-700 flex items-center justify-center hover:bg-gray-100 transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        <button @click="openModal('edit', slider)"
                                class="w-9 h-9 rounded-xl bg-[#ED1F24] text-white flex items-center justify-center hover:bg-[#C81A1E] transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button @click="deleteSlider(slider.id)"
                                class="w-9 h-9 rounded-xl bg-white text-red-500 flex items-center justify-center hover:bg-red-50 transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        </button>
                    </div>

                    <!-- Type badge -->
                    <span class="absolute top-2 left-2 text-[10px] font-bold border px-2 py-0.5 rounded-full uppercase tracking-wider"
                          :class="slider.type === 'image'
                              ? 'bg-blue-50 border-blue-100 text-blue-600'
                              : 'bg-amber-50 border-amber-100 text-amber-600'">
                        {{ slider.type === 'image' ? 'Gambar' : 'Video' }}
                    </span>

                    <!-- Status dot -->
                    <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full border-2 border-white shadow-sm"
                          :class="slider.is_active ? 'bg-emerald-400' : 'bg-gray-400'"></span>
                </div>

                <!-- Card body -->
                <div class="px-4 py-3 border-t border-gray-100">
                    <p class="text-sm font-semibold text-gray-700 truncate">{{ slider.title }}</p>
                    <div class="flex items-center justify-between mt-1.5">
                        <span class="text-xs text-gray-400">{{ formatDate(slider.created_at) }}</span>
                        <span class="text-[10px] font-bold"
                              :class="slider.is_active ? 'text-emerald-500' : 'text-gray-400'">
                            {{ slider.is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer info -->
        <div class="mt-4 text-xs text-gray-400 text-center">
            Menampilkan {{ filteredSliders.length }} dari {{ sliders.length }} slider
        </div>

        <!-- ═══════════════════════════════════
             MODAL: Tambah / Edit
        ═══════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showModal"
                     class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                     @click.self="closeModal">
                    <div class="bg-white border border-gray-200/80 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">

                        <!-- Modal Header -->
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                                 :class="modalMode === 'create'
                                     ? 'bg-[#ED1F24]/8 border border-[#ED1F24]/15'
                                     : 'bg-amber-50 border border-amber-100'">
                                <svg v-if="modalMode === 'create'" class="w-4 h-4 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <svg v-else class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800">{{ modalMode === 'create' ? 'Tambah Slider Baru' : 'Edit Slider' }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5">{{ modalMode === 'create' ? 'Isi detail untuk menambahkan slider baru' : 'Ubah informasi slider yang dipilih' }}</p>
                            </div>
                            <button @click="closeModal"
                                    class="ml-auto w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-700 flex items-center justify-center transition-all flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <!-- Error Alert -->
                        <div v-if="errorMessage" class="mx-6 mt-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-sm">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ errorMessage }}
                        </div>

                        <!-- Form Body -->
                        <div class="p-6 space-y-5">

                            <!-- Title -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                                    Judul Slider <span class="text-[#ED1F24]">*</span>
                                </label>
                                <input v-model="form.title" type="text" placeholder="Masukkan judul slider..."
                                       class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors" />
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                                    Tipe <span class="text-[#ED1F24]">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button v-for="t in typeOptions" :key="t.value"
                                            @click="modalMode !== 'edit' && (form.type = t.value)"
                                            class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl border text-sm font-semibold transition-all"
                                            :class="[
                                                form.type === t.value
                                                    ? 'bg-[#ED1F24]/8 border-[#ED1F24]/30 text-[#C81A1E]'
                                                    : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300',
                                                modalMode === 'edit' ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'
                                            ]">
                                        <span v-html="t.icon"></span>
                                        {{ t.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- File Upload -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                                    File {{ form.type === 'image' ? 'Gambar' : 'Video' }}
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
                                    <input ref="fileInput" type="file"
                                           :accept="form.type === 'image' ? 'image/*' : 'video/*'"
                                           @change="handleFileChange"
                                           class="hidden" />

                                    <div v-if="!previewUrl" class="flex flex-col items-center justify-center gap-3 py-8 px-4">
                                        <div class="w-11 h-11 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-sm text-gray-500"><span class="text-[#ED1F24] font-semibold">Pilih file</span> atau drag &amp; drop</p>
                                            <p class="text-xs text-gray-400 mt-1">{{ form.type === 'image' ? 'JPG, PNG, WEBP' : 'MP4, WEBM' }} — maks 200MB</p>
                                        </div>
                                    </div>

                                    <div v-else class="relative p-3">
                                        <img v-if="form.type === 'image'" :src="previewUrl" class="w-full h-44 object-cover rounded-lg" />
                                        <video v-else :src="previewUrl" controls class="w-full h-44 rounded-lg" />
                                        <button @click.stop="clearFile"
                                                class="absolute top-5 right-5 w-7 h-7 rounded-full bg-white border border-gray-200 shadow-sm text-gray-500 hover:text-red-500 hover:border-red-200 flex items-center justify-center transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Status (edit only) -->
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

                        <!-- Modal Footer -->
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
                                {{ loading ? 'Menyimpan...' : (modalMode === 'create' ? 'Tambah Slider' : 'Simpan Perubahan') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ═══════════════════════════════════
             MODAL: Preview
        ═══════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showPreview"
                     class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                     @click.self="closePreview">
                    <div class="bg-white border border-gray-200/80 rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden">

                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center text-[10px] font-bold border px-2 py-0.5 rounded-full uppercase tracking-wider"
                                      :class="previewSlider?.type === 'image'
                                          ? 'bg-blue-50 border-blue-100 text-blue-600'
                                          : 'bg-amber-50 border-amber-100 text-amber-600'">
                                    {{ previewSlider?.type === 'image' ? 'Gambar' : 'Video' }}
                                </span>
                                <span class="text-sm font-bold text-gray-800">{{ previewSlider?.title }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <a :href="previewSlider?.file_url" target="_blank"
                                   class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 border border-gray-200 hover:border-gray-300 hover:text-gray-700 px-3 py-1.5 rounded-xl transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    Buka di tab baru
                                </a>
                                <button @click="closePreview"
                                        class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-700 flex items-center justify-center transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Media -->
                        <div class="bg-gray-900 flex items-center justify-center" style="max-height: 70vh; overflow: hidden;">
                            <img v-if="previewSlider?.type === 'image'" :src="previewSlider?.file_url"
                                 class="w-full object-contain" style="max-height: 70vh;" :alt="previewSlider?.title" />
                            <video v-else :src="previewSlider?.file_url" controls autoplay
                                   class="w-full" style="max-height: 70vh;" />
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 bg-gray-50/50">
                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                <span>Status:
                                    <strong :class="previewSlider?.is_active ? 'text-emerald-600' : 'text-red-500'">
                                        {{ previewSlider?.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </strong>
                                </span>
                                <span>Dibuat: <strong class="text-gray-700">{{ formatDate(previewSlider?.created_at) }}</strong></span>
                                <span>Order: <strong class="text-gray-700">{{ previewSlider?.order }}</strong></span>
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
import axios from '../../axios.js'
import Sortable from 'sortablejs'

export default {
    name: 'Sliders',
    components: { AdminLayout },
    data() {
        return {
            sliders: [],
            showModal: false,
            modalMode: 'create',
            selectedId: null,
            loading: false,
            errorMessage: '',
            previewUrl: null,
            showPreview: false,
            previewSlider: null,
            isDragging: false,
            search: '',
            activeFilter: 'all',
            viewMode: 'table',
            filters: [
                { label: 'Semua',    value: 'all'      },
                { label: 'Aktif',    value: 'active'   },
                { label: 'Nonaktif', value: 'inactive' },
                { label: 'Gambar',   value: 'image'    },
                { label: 'Video',    value: 'video'    },
            ],
            typeOptions: [
                { value: 'image', label: 'Gambar', icon: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>' },
                { value: 'video', label: 'Video',  icon: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>' },
            ],
            form: {
                title: '',
                type: 'image',
                file: null,
                order: 0,
                is_active: true,
            }
        }
    },

    computed: {
        filteredSliders() {
            return this.sliders.filter(s => {
                const matchSearch = s.title.toLowerCase().includes(this.search.toLowerCase())
                const matchFilter =
                    this.activeFilter === 'all'      ? true :
                    this.activeFilter === 'active'   ? s.is_active :
                    this.activeFilter === 'inactive' ? !s.is_active :
                    this.activeFilter === 'image'    ? s.type === 'image' :
                    this.activeFilter === 'video'    ? s.type === 'video' : true
                return matchSearch && matchFilter
            })
        },
        activeCount() { return this.sliders.filter(s => s.is_active).length },
        imageCount()  { return this.sliders.filter(s => s.type === 'image').length },
        videoCount()  { return this.sliders.filter(s => s.type === 'video').length },
    },

    mounted() {
        document.title = 'Sliders - Two Brothers Vape System'
        this.fetchSliders()

        this._pollInterval = setInterval(() => {
            if (this.sliders.some(s => s.is_processing)) {
                this.fetchSliders(false) 
            }
        }, 5000)

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.showPreview) this.closePreview()
                else if (this.showModal) this.closeModal()
            }
        })
    },

    beforeUnmount() {
        clearInterval(this._pollInterval)
    },

    watch: {
        viewMode(val) {
            if (val === 'table') this.$nextTick(() => this.initSortable())
        }
    },

    methods: {
        openPreview(slider) {
            this.previewSlider = slider
            this.showPreview = true
        },
        closePreview() {
            this.showPreview = false
            setTimeout(() => { this.previewSlider = null }, 300)
        },

        initSortable() {
            if (!this.$refs.sortableTable) return
            if (this._sortable) this._sortable.destroy()
            this._sortable = Sortable.create(this.$refs.sortableTable, {
                animation: 150,
                handle: '.sm-drag-handle',
                ghostClass: 'opacity-30',
                chosenClass: 'bg-red-50',
                onEnd: (evt) => { this.saveOrder(evt.oldIndex, evt.newIndex) }
            })
        },

        async saveOrder(oldIndex, newIndex) {
            if (oldIndex === newIndex) return
            const snapshot = [...this.sliders]
            const reordered = [...this.sliders]
            const moved = reordered.splice(oldIndex, 1)[0]
            reordered.splice(newIndex, 0, moved)
            reordered.forEach((s, i) => { s.order = i })
            this.sliders = reordered
            try {
                await axios.post('/sliders/reorder', {
                    orders: reordered.map((s, i) => ({ id: s.id, order: i }))
                })
            } catch (e) {
                this.sliders = snapshot
                this.$nextTick(() => this.initSortable())
            }
        },

        async fetchSliders(reinitSortable = true) {
            try {
                const res = await axios.get('/sliders')
                this.sliders = res.data.data ?? res.data
                if (reinitSortable) this.$nextTick(() => this.initSortable())
            } catch (e) { console.error(e) }
        },

        formatDate(date) {
            if (!date) return '-'
            return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        },

        handleFileChange(e) {
            const file = e.target.files[0]
            if (!file) return
            this.form.file = file
            this.previewUrl = URL.createObjectURL(file)
        },
        handleDrop(e) {
            this.isDragging = false
            const file = e.dataTransfer.files[0]
            if (!file) return
            this.form.file = file
            this.previewUrl = URL.createObjectURL(file)
        },
        clearFile() {
            this.form.file = null
            this.previewUrl = this.modalMode === 'edit' && this.selectedId
                ? this.sliders.find(s => s.id === this.selectedId)?.file_url || null
                : null
            if (this.$refs.fileInput) this.$refs.fileInput.value = ''
        },

        openModal(mode, slider = null) {
            this.modalMode = mode
            this.errorMessage = ''
            this.previewUrl = null
            if (mode === 'edit' && slider) {
                this.selectedId = slider.id
                this.form = { title: slider.title, type: slider.type, file: null, order: slider.order, is_active: slider.is_active }
                this.previewUrl = slider.file_url
            } else {
                this.selectedId = null
                this.form = { title: '', type: 'image', file: null, order: 0, is_active: true }
            }
            this.showModal = true
        },
        closeModal() {
            this.showModal = false
            this.errorMessage = ''
            this.previewUrl = null
            this.isDragging = false
        },

        async toggleActive(slider) {
            try {
                const formData = new FormData()
                formData.append('title', slider.title)
                formData.append('order', slider.order)
                formData.append('is_active', slider.is_active ? 0 : 1)
                formData.append('_method', 'PUT')
                await axios.post(`/sliders/${slider.id}`, formData)
                await this.fetchSliders()
            } catch (e) { console.error(e) }
        },

        async submitForm() {
            this.loading = true
            this.errorMessage = ''
            try {
                const formData = new FormData()
                formData.append('title', this.form.title)
                formData.append('type', this.form.type)
                formData.append('order', this.form.order)
                formData.append('is_active', this.form.is_active ? 1 : 0)
                if (this.form.file) formData.append('file', this.form.file)

                if (this.modalMode === 'create') {
                    await axios.post('/sliders', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
                } else {
                    formData.append('_method', 'PUT')
                    await axios.post(`/sliders/${this.selectedId}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
                }
                await this.fetchSliders()
                this.closeModal()
            } catch (e) {
                this.errorMessage = e.response?.data?.message || 'Terjadi kesalahan, coba lagi.'
            } finally {
                this.loading = false
            }
        },

        async deleteSlider(id) {
            if (!confirm('Yakin ingin menghapus slider ini? Tindakan ini tidak dapat dibatalkan.')) return
            try {
                await axios.delete(`/sliders/${id}`)
                await this.fetchSliders()
            } catch (e) {
                alert('Gagal menghapus slider.')
            }
        }
    }
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

.sm-drag-handle { cursor: grab; }
.sm-drag-handle:active { cursor: grabbing; }
</style>