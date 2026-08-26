<template>
    <AdminLayout title="Site Settings">

        <!-- ═══════════════════════════════════════════
             HERO HEADER — konsisten dengan FooterLinks
        ═══════════════════════════════════════════ -->
        <div class="relative mb-6 rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"></div>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10" style="background: white;"></div>

            <div class="relative px-7 py-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">Pengaturan Website</p>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Site Settings</h1>
                    <p class="text-red-200 text-xs mt-1.5">Kelola identitas dan konfigurasi tampilan website</p>
                </div>
                <div class="flex items-center gap-2 text-xs text-red-100 bg-white/15 border border-white/20 px-3 py-1.5 rounded-xl">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                    Auto-saved
                </div>
            </div>

            <!-- Stats strip -->
            <div class="relative border-t border-white/10 px-7 py-3 flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-6">
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Seksi Aktif</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ activeSectionData?.label }}</p>
                    </div>
                    <div class="w-px h-8 bg-white/15"></div>
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Versi</p>
                        <p class="text-white text-lg font-bold tabular-nums">v2.1.0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6">

            <!-- Sidebar Navigation -->
            <div class="col-span-3">
                <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm sticky top-6">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kategori</p>
                    </div>
                    <nav class="p-2 space-y-0.5">
                        <button
                            v-for="section in sections"
                            :key="section.id"
                            @click="activeSection = section.id"
                            :class="[
                                'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all text-left',
                                activeSection === section.id
                                    ? 'bg-[#ED1F24]/8 text-[#ED1F24] border border-[#ED1F24]/15'
                                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 border border-transparent'
                            ]"
                        >
                            <span class="text-base flex-shrink-0" v-html="section.icon"></span>
                            <div class="min-w-0">
                                <p class="font-semibold truncate text-sm">{{ section.label }}</p>
                                <p class="text-xs truncate" :class="activeSection === section.id ? 'text-[#ED1F24]/60' : 'text-gray-400'">
                                    {{ section.desc }}
                                </p>
                            </div>
                            <svg v-if="activeSection === section.id" class="w-3.5 h-3.5 ml-auto flex-shrink-0 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </nav>
                    <div class="px-4 py-3 border-t border-gray-100">
                        <p class="text-xs text-gray-400">v2.1.0 &middot; Last updated 2 days ago</p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-span-9 space-y-4">

                <!-- ── BRANDING ── -->
                <template v-if="activeSection === 'branding'">

                    <!-- ── Logo Card ── -->
                    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Logo Website</p>
                                    <p class="text-xs text-gray-400">Logo utama yang tampil di header dan halaman publik</p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 border border-gray-200 text-gray-500">
                                JPEG · PNG · SVG · WebP
                            </span>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-5 gap-6 items-start">
                                <!-- Preview -->
                                <div class="col-span-2">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Preview</p>
                                    <div class="relative aspect-video rounded-xl border border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center">
                                        <div v-if="!currentLogoUrl && !previewUrl" class="absolute inset-0 opacity-[0.04]"
                                             style="background-image: repeating-conic-gradient(#000 0% 25%, transparent 0% 50%); background-size: 16px 16px;"></div>
                                        <img v-if="previewUrl || currentLogoUrl" :src="previewUrl || currentLogoUrl"
                                             alt="Logo preview" class="max-w-[75%] max-h-[60%] object-contain drop-shadow-sm transition-opacity duration-300"
                                             :class="previewUrl ? 'opacity-80' : 'opacity-100'" />
                                        <div v-else class="flex flex-col items-center gap-2 text-gray-300">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-xs">Belum ada logo</span>
                                        </div>
                                        <div v-if="previewUrl" class="absolute top-2 right-2">
                                            <span class="text-[10px] font-bold bg-amber-400 text-white px-2 py-0.5 rounded-full">PREVIEW</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <div class="rounded-lg bg-gray-900 border border-gray-700 h-10 flex items-center justify-center px-3">
                                            <img v-if="previewUrl || currentLogoUrl" :src="previewUrl || currentLogoUrl" class="max-h-5 object-contain opacity-90" />
                                            <span v-else class="text-[10px] text-gray-600">Dark</span>
                                        </div>
                                        <div class="rounded-lg bg-white border border-gray-200 h-10 flex items-center justify-center px-3">
                                            <img v-if="previewUrl || currentLogoUrl" :src="previewUrl || currentLogoUrl" class="max-h-5 object-contain opacity-90" />
                                            <span v-else class="text-[10px] text-gray-300">Light</span>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-2 text-center">Tampilan di background gelap &amp; terang</p>
                                </div>

                                <!-- Upload -->
                                <div class="col-span-3 flex flex-col gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Unggah Logo</p>
                                        <div class="relative rounded-xl border-2 border-dashed transition-all duration-200 cursor-pointer"
                                             :class="isDragging ? 'border-[#ED1F24] bg-red-50 scale-[1.01]' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50/50'"
                                             @click="$refs.fileInput.click()"
                                             @dragover.prevent="isDragging = true"
                                             @dragleave.prevent="isDragging = false"
                                             @drop.prevent="onDrop">
                                            <div class="flex flex-col items-center justify-center gap-3 py-8 px-4">
                                                <div class="w-11 h-11 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                    </svg>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-sm text-gray-500"><span class="text-[#ED1F24] font-semibold">Pilih file</span> atau drag &amp; drop</p>
                                                    <p class="text-xs text-gray-400 mt-1">PNG, JPG, SVG, WebP — maks. 2MB</p>
                                                </div>
                                            </div>
                                            <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png,.svg,.webp,.gif" class="hidden" @change="onFileSelected" />
                                        </div>
                                    </div>

                                    <div v-if="selectedFile" class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-gray-700 truncate">{{ selectedFile.name }}</p>
                                            <p class="text-xs text-gray-400">{{ formatBytes(selectedFile.size) }} &middot; akan disimpan sebagai <span class="text-amber-500 font-semibold">.webp</span></p>
                                        </div>
                                        <button @click="clearSelection" class="text-gray-400 hover:text-gray-600 transition p-1 rounded flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="rounded-xl bg-gray-50 border border-gray-100 divide-y divide-gray-100">
                                        <div class="flex items-center justify-between px-4 py-2.5 text-xs">
                                            <span class="text-gray-500">Format output</span>
                                            <span class="font-semibold text-amber-500">WebP (auto-convert)</span>
                                        </div>
                                        <div class="flex items-center justify-between px-4 py-2.5 text-xs">
                                            <span class="text-gray-500">Ukuran maks.</span>
                                            <span class="font-semibold text-gray-600">2 MB</span>
                                        </div>
                                        <div class="flex items-center justify-between px-4 py-2.5 text-xs">
                                            <span class="text-gray-500">Rekomendasi ukuran</span>
                                            <span class="font-semibold text-gray-600">400 × 120 px</span>
                                        </div>
                                        <div class="flex items-center justify-between px-4 py-2.5 text-xs">
                                            <span class="text-gray-500">Transparansi</span>
                                            <span class="font-semibold text-emerald-500">Didukung</span>
                                        </div>
                                    </div>

                                    <div v-if="logo.error" class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-sm">
                                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>
                                        {{ logo.error }}
                                    </div>
                                    <div v-if="logo.success" class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-sm">
                                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        {{ logo.success }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <div>
                                <button v-if="currentLogoUrl" @click="confirmDelete = true"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-500 border border-red-200 hover:border-red-300 hover:bg-red-50 px-3 py-1.5 rounded-xl transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    Hapus Logo
                                </button>
                            </div>
                            <div class="flex items-center gap-2">
                                <button v-if="selectedFile" @click="clearSelection"
                                        class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-xl transition-all">
                                    Batal
                                </button>
                                <button @click="uploadLogo" :disabled="!selectedFile || logo.uploading"
                                        class="inline-flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                    <svg v-if="logo.uploading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/></svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    {{ logo.uploading ? 'Mengunggah...' : 'Simpan Logo' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ── Logo Footer Card ── -->
                    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Logo Footer</p>
                                    <p class="text-xs text-gray-400">Logo yang tampil di bagian footer halaman publik</p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 border border-gray-200 text-gray-500">JPEG · PNG · SVG · WebP</span>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-5 gap-6 items-start">
                                <!-- Preview -->
                                <div class="col-span-2">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Preview</p>
                                    <div class="relative aspect-video rounded-xl border border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center">
                                        <div v-if="!currentLogoFooterUrl && !logoFooter.previewUrl" class="absolute inset-0 opacity-[0.04]"
                                            style="background-image: repeating-conic-gradient(#000 0% 25%, transparent 0% 50%); background-size: 16px 16px;"></div>
                                        <img v-if="logoFooter.previewUrl || currentLogoFooterUrl"
                                            :src="logoFooter.previewUrl || currentLogoFooterUrl"
                                            alt="Logo footer preview"
                                            class="max-w-[75%] max-h-[60%] object-contain drop-shadow-sm transition-opacity duration-300"
                                            :class="logoFooter.previewUrl ? 'opacity-80' : 'opacity-100'" />
                                        <div v-else class="flex flex-col items-center gap-2 text-gray-300">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-xs">Belum ada logo footer</span>
                                        </div>
                                        <div v-if="logoFooter.previewUrl" class="absolute top-2 right-2">
                                            <span class="text-[10px] font-bold bg-amber-400 text-white px-2 py-0.5 rounded-full">PREVIEW</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <div class="rounded-lg bg-gray-900 border border-gray-700 h-10 flex items-center justify-center px-3">
                                            <img v-if="logoFooter.previewUrl || currentLogoFooterUrl" :src="logoFooter.previewUrl || currentLogoFooterUrl" class="max-h-5 object-contain opacity-90" />
                                            <span v-else class="text-[10px] text-gray-600">Dark</span>
                                        </div>
                                        <div class="rounded-lg bg-white border border-gray-200 h-10 flex items-center justify-center px-3">
                                            <img v-if="logoFooter.previewUrl || currentLogoFooterUrl" :src="logoFooter.previewUrl || currentLogoFooterUrl" class="max-h-5 object-contain opacity-90" />
                                            <span v-else class="text-[10px] text-gray-300">Light</span>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-2 text-center">Tampilan di background gelap &amp; terang</p>
                                </div>

                                <!-- Upload -->
                                <div class="col-span-3 flex flex-col gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Unggah Logo Footer</p>
                                        <div class="relative rounded-xl border-2 border-dashed transition-all duration-200 cursor-pointer"
                                            :class="logoFooter.dragging ? 'border-[#ED1F24] bg-red-50 scale-[1.01]' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50/50'"
                                            @click="$refs.logoFooterInput.click()"
                                            @dragover.prevent="logoFooter.dragging = true"
                                            @dragleave.prevent="logoFooter.dragging = false"
                                            @drop.prevent="onLogoFooterDrop">
                                            <div class="flex flex-col items-center justify-center gap-3 py-8 px-4">
                                                <div class="w-11 h-11 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-sm text-gray-500"><span class="text-[#ED1F24] font-semibold">Pilih file</span> atau drag &amp; drop</p>
                                                    <p class="text-xs text-gray-400 mt-1">PNG, JPG, SVG, WebP — maks. 2MB</p>
                                                </div>
                                            </div>
                                            <input ref="logoFooterInput" type="file" accept=".jpg,.jpeg,.png,.svg,.webp,.gif" class="hidden" @change="onLogoFooterSelected" />
                                        </div>
                                    </div>

                                    <div v-if="logoFooter.file" class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                                        <div class="w-8 h-8 rounded-lg bg-violet-50 border border-violet-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-gray-700 truncate">{{ logoFooter.file.name }}</p>
                                            <p class="text-xs text-gray-400">{{ formatBytes(logoFooter.file.size) }} &middot; akan disimpan sebagai <span class="text-amber-500 font-semibold">.webp</span></p>
                                        </div>
                                        <button @click="clearLogoFooter" class="text-gray-400 hover:text-gray-600 transition p-1 rounded flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <div v-if="logoFooter.error" class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-sm">
                                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>
                                        {{ logoFooter.error }}
                                    </div>
                                    <div v-if="logoFooter.success" class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-sm">
                                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        {{ logoFooter.success }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <div>
                                <button v-if="currentLogoFooterUrl" @click="confirmDeleteLogoFooter = true"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-500 border border-red-200 hover:border-red-300 hover:bg-red-50 px-3 py-1.5 rounded-xl transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    Hapus Logo Footer
                                </button>
                            </div>
                            <div class="flex items-center gap-2">
                                <button v-if="logoFooter.file" @click="clearLogoFooter"
                                        class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-xl transition-all">Batal</button>
                                <button @click="uploadLogoFooter" :disabled="!logoFooter.file || logoFooter.uploading"
                                        class="inline-flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                    <svg v-if="logoFooter.uploading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/></svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    {{ logoFooter.uploading ? 'Mengunggah...' : 'Simpan Logo Footer' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ── Site Description Card ── -->
                    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h10"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Deskripsi Website</p>
                                <p class="text-xs text-gray-400">Tampil di bawah logo pada footer halaman publik</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Deskripsi Singkat</label>
                                <textarea
                                    v-model="siteDescription.input"
                                    rows="3"
                                    placeholder="Contoh: Toko vape terpercaya dengan produk original, harga terbaik, dan pelayanan terbaik."
                                    class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors resize-none"
                                    @input="siteDescription.error = ''; siteDescription.success = ''"
                                ></textarea>
                                <p class="text-[10px] text-gray-400 mt-1.5">Maksimal 200 karakter. Tampil sebagai tagline toko di footer website.</p>
                            </div>
                            <div class="flex justify-end">
                                <span class="text-[10px]" :class="siteDescription.input.length > 200 ? 'text-red-500' : 'text-gray-400'">
                                    {{ siteDescription.input.length }} / 200
                                </span>
                            </div>
                            <div v-if="siteDescription.error" class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-sm">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>
                                {{ siteDescription.error }}
                            </div>
                            <div v-if="siteDescription.success" class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-sm">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ siteDescription.success }}
                            </div>
                        </div>
                        <div class="flex items-center justify-end px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <button @click="saveSiteDescription" :disabled="!siteDescription.input || siteDescription.input.length > 200 || siteDescription.saving"
                                    class="inline-flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                <svg v-if="siteDescription.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/></svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ siteDescription.saving ? 'Menyimpan...' : 'Simpan Deskripsi' }}
                            </button>
                        </div>
                    </div>

                    <!-- ── Site Name Card ── -->
                    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Nama Website</p>
                                <p class="text-xs text-gray-400">Tampil di browser tab dan navbar halaman publik</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Nama Website</label>
                                <input
                                    v-model="siteName.input"
                                    type="text"
                                    placeholder="Contoh: TB CloudSuite"
                                    class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors"
                                    @input="siteName.error = ''; siteName.success = ''"
                                />
                                <p class="text-[10px] text-gray-400 mt-1.5">Nama ini akan muncul sebagai judul di browser tab dan di navbar halaman publik.</p>
                            </div>
                            <!-- Browser tab preview -->
                            <div class="rounded-xl bg-gray-50 border border-gray-100 p-3">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Browser Tab Preview</p>
                                <div class="flex items-center gap-2 bg-gray-200 rounded-t-lg px-3 py-1.5 w-fit">
                                    <img v-if="currentFaviconUrl" :src="currentFaviconUrl" class="w-3.5 h-3.5 object-contain rounded-sm flex-shrink-0" />
                                    <div v-else class="w-3.5 h-3.5 rounded-sm bg-gray-400 flex-shrink-0"></div>
                                    <span class="text-xs text-gray-600 truncate max-w-[160px]">{{ siteName.input || 'Nama Website' }}</span>
                                    <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </div>
                            </div>
                            <div v-if="siteName.error" class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-sm">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>
                                {{ siteName.error }}
                            </div>
                            <div v-if="siteName.success" class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-sm">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ siteName.success }}
                            </div>
                        </div>
                        <div class="flex items-center justify-end px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <button @click="saveSiteName" :disabled="!siteName.input || siteName.saving"
                                    class="inline-flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                <svg v-if="siteName.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/></svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ siteName.saving ? 'Menyimpan...' : 'Simpan Nama' }}
                            </button>
                        </div>
                    </div>

                    <!-- ── Favicon Card ── -->
                    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Favicon</p>
                                    <p class="text-xs text-gray-400">Ikon kecil yang tampil di browser tab</p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 border border-gray-200 text-gray-500">PNG · maks 512KB</span>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-5 gap-6 items-start">
                                <!-- Preview -->
                                <div class="col-span-2">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Preview</p>
                                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 flex flex-col items-center justify-center gap-4">
                                        <div class="w-16 h-16 rounded-xl border border-gray-200 bg-white flex items-center justify-center overflow-hidden shadow-sm">
                                            <img v-if="favicon.previewUrl || currentFaviconUrl" :src="favicon.previewUrl || currentFaviconUrl" class="w-10 h-10 object-contain" />
                                            <svg v-else class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                                        </div>
                                        <div class="w-full">
                                            <p class="text-[10px] text-gray-400 mb-1.5 text-center">Di browser tab</p>
                                            <div class="flex items-center gap-1.5 bg-gray-200 rounded-t-md px-2.5 py-1.5 w-full">
                                                <img v-if="favicon.previewUrl || currentFaviconUrl" :src="favicon.previewUrl || currentFaviconUrl" class="w-3.5 h-3.5 object-contain flex-shrink-0" />
                                                <div v-else class="w-3.5 h-3.5 rounded-sm bg-gray-400 flex-shrink-0"></div>
                                                <span class="text-[10px] text-gray-500 truncate">{{ siteName.input || 'Nama Website' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rounded-xl bg-gray-50 border border-gray-100 divide-y divide-gray-100 mt-3">
                                        <div class="flex items-center justify-between px-4 py-2.5 text-xs"><span class="text-gray-500">Format</span><span class="font-semibold text-gray-600">PNG</span></div>
                                        <div class="flex items-center justify-between px-4 py-2.5 text-xs"><span class="text-gray-500">Ukuran ideal</span><span class="font-semibold text-gray-600">32 × 32 px</span></div>
                                        <div class="flex items-center justify-between px-4 py-2.5 text-xs"><span class="text-gray-500">Maks. file</span><span class="font-semibold text-gray-600">512 KB</span></div>
                                    </div>
                                </div>
                                <!-- Upload -->
                                <div class="col-span-3 flex flex-col gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Unggah Favicon</p>
                                        <div class="relative rounded-xl border-2 border-dashed transition-all duration-200 cursor-pointer"
                                             :class="favicon.dragging ? 'border-[#ED1F24] bg-red-50 scale-[1.01]' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50/50'"
                                             @click="$refs.faviconInput.click()"
                                             @dragover.prevent="favicon.dragging = true"
                                             @dragleave.prevent="favicon.dragging = false"
                                             @drop.prevent="onFaviconDrop">
                                            <div class="flex flex-col items-center justify-center gap-3 py-8 px-4">
                                                <div class="w-11 h-11 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-sm text-gray-500"><span class="text-[#ED1F24] font-semibold">Pilih file</span> atau drag &amp; drop</p>
                                                    <p class="text-xs text-gray-400 mt-1">PNG — rekomendasi 32×32 px, maks. 512KB</p>
                                                </div>
                                            </div>
                                            <input ref="faviconInput" type="file" accept=".png,.jpg,.jpeg" class="hidden" @change="onFaviconSelected" />
                                        </div>
                                    </div>
                                    <div v-if="favicon.file" class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                                        <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-gray-700 truncate">{{ favicon.file.name }}</p>
                                            <p class="text-xs text-gray-400">{{ formatBytes(favicon.file.size) }} &middot; disimpan sebagai <span class="text-emerald-500 font-semibold">.png</span></p>
                                        </div>
                                        <button @click="clearFavicon" class="text-gray-400 hover:text-gray-600 transition p-1 rounded flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div v-if="favicon.error" class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-sm">
                                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>
                                        {{ favicon.error }}
                                    </div>
                                    <div v-if="favicon.success" class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-sm">
                                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        {{ favicon.success }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <button v-if="currentFaviconUrl" @click="confirmDeleteFavicon = true"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-500 border border-red-200 hover:border-red-300 hover:bg-red-50 px-3 py-1.5 rounded-xl transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Hapus Favicon
                            </button>
                            <div class="flex items-center gap-2 ml-auto">
                                <button v-if="favicon.file" @click="clearFavicon"
                                        class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-xl transition-all">Batal</button>
                                <button @click="uploadFavicon" :disabled="!favicon.file || favicon.uploading"
                                        class="inline-flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                    <svg v-if="favicon.uploading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/></svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    {{ favicon.uploading ? 'Mengunggah...' : 'Simpan Favicon' }}
                                </button>
                            </div>
                        </div>
                    </div>

                </template>

                <!-- ── SEO & META ── -->
                <template v-else-if="activeSection === 'seo'">
                    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Google Search Console</p>
                                    <p class="text-xs text-gray-400">Verifikasi kepemilikan website untuk Google Webmaster Tools</p>
                                </div>
                            </div>
                            <span v-if="seo.googleVerification"
                                  class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                Terverifikasi
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full bg-gray-100 border border-gray-200 text-gray-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                Belum diset
                            </span>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="rounded-xl bg-blue-50 border border-blue-100 p-4">
                                <p class="text-xs font-bold text-blue-600 mb-2 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Cara mendapatkan kode verifikasi
                                </p>
                                <ol class="space-y-1.5 text-xs text-blue-600/70">
                                    <li class="flex items-start gap-2">
                                        <span class="flex-shrink-0 w-4 h-4 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center text-[10px] font-bold mt-0.5">1</span>
                                        Buka <a href="https://search.google.com/search-console" target="_blank" class="text-blue-500 underline underline-offset-2 hover:text-blue-700">Google Search Console</a> dan login.
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="flex-shrink-0 w-4 h-4 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center text-[10px] font-bold mt-0.5">2</span>
                                        Klik <strong class="text-blue-600">Add Property</strong>, masukkan URL website, pilih metode <strong class="text-blue-600">HTML tag</strong>.
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="flex-shrink-0 w-4 h-4 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center text-[10px] font-bold mt-0.5">3</span>
                                        Salin <strong class="text-blue-600">hanya nilai content</strong> dari meta tag. Contoh: <code class="bg-blue-100 px-1.5 py-0.5 rounded text-blue-600 font-mono">AbCdEfGh1234...</code>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="flex-shrink-0 w-4 h-4 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center text-[10px] font-bold mt-0.5">4</span>
                                        Tempel ke kolom di bawah, klik <strong class="text-blue-600">Simpan</strong>, lalu klik <strong class="text-blue-600">Verify</strong> di Google.
                                    </li>
                                </ol>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Meta Tag yang Akan Di-inject</p>
                                <div class="rounded-xl bg-gray-900 border border-gray-700 px-4 py-3 font-mono text-xs">
                                    <span class="text-gray-400">&lt;meta name=</span><span class="text-emerald-400">"google-site-verification"</span>
                                    <span class="text-gray-400"> content=</span>
                                    <span class="text-amber-400">"{{ seo.googleVerification || 'YOUR_CODE_HERE' }}"</span>
                                    <span class="text-gray-400"> /&gt;</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1.5">Tag ini akan otomatis muncul di &lt;head&gt; halaman Home.vue saat kode disimpan.</p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                                    Kode Verifikasi
                                    <span class="normal-case font-normal text-gray-400 ml-1">(nilai content dari meta tag)</span>
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="seo.googleVerificationInput"
                                        type="text"
                                        placeholder="Contoh: AbCdEfGhIjKlMnOpQr1234567890..."
                                        class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors font-mono"
                                        @input="seo.error = ''; seo.success = ''"
                                    />
                                    <button v-if="seo.googleVerificationInput" @click="seo.googleVerificationInput = ''"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1.5">Jangan masukkan seluruh tag HTML — cukup nilai <code class="bg-gray-100 px-1 py-0.5 rounded">content</code>-nya saja.</p>
                            </div>
                            <div v-if="seo.error" class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-sm">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>
                                {{ seo.error }}
                            </div>
                            <div v-if="seo.success" class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-sm">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ seo.success }}
                            </div>
                        </div>
                        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <div>
                                <button v-if="seo.googleVerification" @click="clearGoogleVerification" :disabled="seo.saving"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-500 border border-red-200 hover:border-red-300 hover:bg-red-50 px-3 py-1.5 rounded-xl transition-all disabled:opacity-40">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    Hapus Verifikasi
                                </button>
                            </div>
                            <button @click="saveGoogleVerification" :disabled="!seo.googleVerificationInput || seo.saving"
                                    class="inline-flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                <svg v-if="seo.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/></svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ seo.saving ? 'Menyimpan...' : 'Simpan & Aktifkan' }}
                            </button>
                        </div>
                    </div>

                    <!-- Placeholder SEO lainnya -->
                    <div class="grid grid-cols-2 gap-4">
                        <div v-for="p in seoPlaceholders" :key="p.key"
                             class="bg-white border border-gray-200/80 rounded-xl p-5 opacity-50 cursor-not-allowed shadow-sm">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-8 h-8 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center" v-html="p.icon"></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-600">{{ p.label }}</p>
                                    <p class="text-xs text-gray-400">{{ p.desc }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold bg-gray-100 border border-gray-200 text-gray-400 px-2 py-0.5 rounded-full uppercase tracking-wider">Coming Soon</span>
                        </div>
                    </div>
                </template>

                <!-- ── CONTACT INFO ── -->
                <template v-else-if="activeSection === 'contact'">
                    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Informasi Kontak</p>
                                <p class="text-xs text-gray-400">Tampil di bagian kiri footer website</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-6">

                            <!-- Alamat -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Alamat</label>
                                <textarea v-model="contact.address.input" rows="3" placeholder="Contoh: Jl. Sudirman No. 123, Jakarta Pusat 10220"
                                    class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors resize-none"
                                    @input="contact.address.error = ''; contact.address.success = ''"></textarea>
                                <div v-if="contact.address.error" class="flex items-start gap-2 mt-2 text-red-500 text-xs"><svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>{{ contact.address.error }}</div>
                                <div v-if="contact.address.success" class="flex items-start gap-2 mt-2 text-emerald-600 text-xs"><svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>{{ contact.address.success }}</div>
                                <div class="flex justify-end mt-2">
                                    <button @click="saveContactField('address')" :disabled="contact.address.saving" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 text-white px-3 py-1.5 rounded-xl transition shadow-sm">
                                        <svg v-if="contact.address.saving" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                                        {{ contact.address.saving ? 'Menyimpan...' : 'Simpan Alamat' }}
                                    </button>
                                </div>
                            </div>

                            <div class="border-t border-gray-100"></div>

                            <!-- Nomor HP -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nomor HP / Telepon</label>
                                    <button @click="toggleContactVisibility('phone')" :disabled="contact.phone.visibleSaving"
                                        class="flex items-center gap-1.5 text-[10px] font-semibold"
                                        :class="contact.phone.visible ? 'text-emerald-600' : 'text-gray-400'">
                                        <span class="relative w-8 h-4.5 rounded-full transition-colors duration-200"
                                            :class="contact.phone.visible ? 'bg-[#ED1F24]' : 'bg-gray-200'">
                                            <span class="absolute top-0.5 left-0.5 w-3.5 h-3.5 bg-white rounded-full shadow transition-transform duration-200"
                                                :class="contact.phone.visible ? 'translate-x-3.5' : 'translate-x-0'"></span>
                                        </span>
                                        {{ contact.phone.visible ? 'Tampil di footer' : 'Disembunyikan' }}
                                    </button>
                                </div>
                                <input v-model="contact.phone.input" type="text" placeholder="Contoh: +62 21 1234 5678"
                                    class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors"
                                    @input="contact.phone.error = ''; contact.phone.success = ''" />
                                <div v-if="contact.phone.error" class="flex items-start gap-2 mt-2 text-red-500 text-xs"><svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>{{ contact.phone.error }}</div>
                                <div v-if="contact.phone.success" class="flex items-start gap-2 mt-2 text-emerald-600 text-xs"><svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>{{ contact.phone.success }}</div>
                                <div class="flex justify-end mt-2">
                                    <button @click="saveContactField('phone')" :disabled="contact.phone.saving" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 text-white px-3 py-1.5 rounded-xl transition shadow-sm">
                                        <svg v-if="contact.phone.saving" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                                        {{ contact.phone.saving ? 'Menyimpan...' : 'Simpan Nomor HP' }}
                                    </button>
                                </div>
                            </div>

                            <div class="border-t border-gray-100"></div>

                            <!-- Email -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Email</label>
                                <input v-model="contact.email.input" type="email" placeholder="Contoh: info@perusahaan.com"
                                    class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors"
                                    @input="contact.email.error = ''; contact.email.success = ''" />
                                <div v-if="contact.email.error" class="flex items-start gap-2 mt-2 text-red-500 text-xs"><svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>{{ contact.email.error }}</div>
                                <div v-if="contact.email.success" class="flex items-start gap-2 mt-2 text-emerald-600 text-xs"><svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>{{ contact.email.success }}</div>
                                <div class="flex justify-end mt-2">
                                    <button @click="saveContactField('email')" :disabled="contact.email.saving" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 text-white px-3 py-1.5 rounded-xl transition shadow-sm">
                                        <svg v-if="contact.email.saving" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                                        {{ contact.email.saving ? 'Menyimpan...' : 'Simpan Email' }}
                                    </button>
                                </div>
                            </div>

                            <div class="border-t border-gray-100"></div>

                            <!-- WhatsApp -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">WhatsApp</label>
                                    <button @click="toggleContactVisibility('whatsapp')" :disabled="contact.whatsapp.visibleSaving"
                                        class="flex items-center gap-1.5 text-[10px] font-semibold"
                                        :class="contact.whatsapp.visible ? 'text-emerald-600' : 'text-gray-400'">
                                        <span class="relative w-8 h-4.5 rounded-full transition-colors duration-200"
                                            :class="contact.whatsapp.visible ? 'bg-[#ED1F24]' : 'bg-gray-200'">
                                            <span class="absolute top-0.5 left-0.5 w-3.5 h-3.5 bg-white rounded-full shadow transition-transform duration-200"
                                                :class="contact.whatsapp.visible ? 'translate-x-3.5' : 'translate-x-0'"></span>
                                        </span>
                                        {{ contact.whatsapp.visible ? 'Tampil di footer' : 'Disembunyikan' }}
                                    </button>
                                </div>
                                <input v-model="contact.whatsapp.input" type="text" placeholder="Contoh: +6281234567890 (format internasional)"
                                    class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors"
                                    @input="contact.whatsapp.error = ''; contact.whatsapp.success = ''" />
                                <p class="text-[10px] text-gray-400 mt-1.5">Gunakan format internasional tanpa tanda + di depan untuk link wa.me, contoh: <code class="bg-gray-100 px-1 rounded">6281234567890</code></p>
                                <div v-if="contact.whatsapp.error" class="flex items-start gap-2 mt-2 text-red-500 text-xs"><svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>{{ contact.whatsapp.error }}</div>
                                <div v-if="contact.whatsapp.success" class="flex items-start gap-2 mt-2 text-emerald-600 text-xs"><svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>{{ contact.whatsapp.success }}</div>
                                <div class="flex justify-end mt-2">
                                    <button @click="saveContactField('whatsapp')" :disabled="contact.whatsapp.saving" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 text-white px-3 py-1.5 rounded-xl transition shadow-sm">
                                        <svg v-if="contact.whatsapp.saving" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                                        {{ contact.whatsapp.saving ? 'Menyimpan...' : 'Simpan WhatsApp' }}
                                    </button>
                                </div>
                            </div>

                            <div class="border-t border-gray-100"></div>

                            <!-- WhatsApp Admin Membership -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                                    WhatsApp Admin Membership
                                    <span class="normal-case font-normal text-gray-400 ml-1">(TB Point)</span>
                                </label>
                                <input v-model="contact.admin_whatsapp.input" type="text"
                                    placeholder="Contoh: 6281234567890"
                                    class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors"
                                    @input="contact.admin_whatsapp.error = ''; contact.admin_whatsapp.success = ''" />
                                <p class="text-[10px] text-gray-400 mt-1.5">
                                    Nomor ini dipakai di halaman <strong>TB Point</strong> untuk cek & daftar membership.
                                    Format tanpa <code class="bg-gray-100 px-1 rounded">+</code>, contoh: <code class="bg-gray-100 px-1 rounded">6281234567890</code>
                                </p>
                                <div v-if="contact.admin_whatsapp.error" class="flex items-start gap-2 mt-2 text-red-500 text-xs">
                                    <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/>
                                    </svg>
                                    {{ contact.admin_whatsapp.error }}
                                </div>
                                <div v-if="contact.admin_whatsapp.success" class="flex items-start gap-2 mt-2 text-emerald-600 text-xs">
                                    <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ contact.admin_whatsapp.success }}
                                </div>
                                <div class="flex justify-end mt-2">
                                    <button @click="saveContactField('admin_whatsapp')" :disabled="contact.admin_whatsapp.saving"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 text-white px-3 py-1.5 rounded-xl transition shadow-sm">
                                        <svg v-if="contact.admin_whatsapp.saving" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                        </svg>
                                        {{ contact.admin_whatsapp.saving ? 'Menyimpan...' : 'Simpan WA Membership' }}
                                    </button>
                                </div>
                            </div>

                            <div class="border-t border-gray-100"></div>

                            <!-- WhatsApp Store Checkout -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                                    WhatsApp Toko
                                    <span class="normal-case font-normal text-gray-400 ml-1">(Checkout / Order)</span>
                                </label>
                                <input v-model="contact.store_whatsapp.input" type="text"
                                    placeholder="Contoh: 6281293139223"
                                    class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors"
                                    @input="contact.store_whatsapp.error = ''; contact.store_whatsapp.success = ''" />
                                <p class="text-[10px] text-gray-400 mt-1.5">
                                    Nomor ini dipakai di halaman <strong>Checkout</strong> untuk mengirim detail pesanan via WhatsApp.
                                    Format tanpa <code class="bg-gray-100 px-1 rounded">+</code>, contoh: <code class="bg-gray-100 px-1 rounded">6281293139223</code>
                                </p>
                                <div v-if="contact.store_whatsapp.error" class="flex items-start gap-2 mt-2 text-red-500 text-xs">
                                    <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/>
                                    </svg>
                                    {{ contact.store_whatsapp.error }}
                                </div>
                                <div v-if="contact.store_whatsapp.success" class="flex items-start gap-2 mt-2 text-emerald-600 text-xs">
                                    <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ contact.store_whatsapp.success }}
                                </div>
                                <div class="flex justify-end mt-2">
                                    <button @click="saveContactField('store_whatsapp')" :disabled="contact.store_whatsapp.saving"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 text-white px-3 py-1.5 rounded-xl transition shadow-sm">
                                        <svg v-if="contact.store_whatsapp.saving" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                        </svg>
                                        {{ contact.store_whatsapp.saving ? 'Menyimpan...' : 'Simpan WA Toko' }}
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </template>

                <!-- ── SOCIAL MEDIA ── -->
                <template v-else-if="activeSection === 'social'">
                    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-pink-50 border border-pink-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Media Sosial</p>
                                <p class="text-xs text-gray-400">URL yang tampil di bagian "Temukan Kami" pada footer. Kosongkan untuk menyembunyikan.</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">

                            <div v-for="(platform, key) in socialPlatforms" :key="key">
                                <label class="flex items-center gap-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2" v-html="platform.label"></label>
                                <input v-model="social.links[key]" type="url" :placeholder="platform.placeholder"
                                    class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors" />
                            </div>

                            <div v-if="social.error" class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-sm">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>
                                {{ social.error }}
                            </div>
                            <div v-if="social.success" class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-sm">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ social.success }}
                            </div>
                        </div>
                        <div class="flex items-center justify-end px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <button @click="saveSocial" :disabled="social.saving"
                                    class="inline-flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                <svg v-if="social.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ social.saving ? 'Menyimpan...' : 'Simpan Semua Media Sosial' }}
                            </button>
                        </div>
                    </div>
                </template>

                <!-- ── SHIPPING ── -->
                <template v-else-if="activeSection === 'shipping'">
                    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Opsi Pengiriman</p>
                                <p class="text-xs text-gray-400">Narik tarif data pengiriman dari API RajaOngkir.</p>
                            </div>
                        </div>

                        <!-- Loading skeleton -->
                        <div v-if="rajaongkirCouriers.loading" class="p-6 space-y-2">
                            <div v-for="i in 4" :key="i" class="h-11 rounded-xl bg-gray-100 border border-gray-200 animate-pulse"></div>
                        </div>

                        <div v-else class="p-6">
                            <div class="grid grid-cols-2 gap-2">
                                <label
                                    v-for="courier in rajaongkirCouriers.list"
                                    :key="courier.code"
                                    class="flex items-center gap-3 rounded-xl border px-3 py-2.5 cursor-pointer transition-all"
                                    :class="courier.active ? 'bg-[#ED1F24]/5 border-[#ED1F24]/20' : 'bg-gray-50 border-gray-200 hover:border-gray-300'"
                                >
                                    <input type="checkbox" v-model="courier.active" class="w-4 h-4 rounded accent-[#ED1F24] flex-shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold truncate" :class="courier.active ? 'text-[#ED1F24]' : 'text-gray-600'">{{ courier.name }}</p>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wide">{{ courier.code }}</p>
                                    </div>
                                </label>
                            </div>

                            <div v-if="rajaongkirCouriers.error" class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-sm mt-4">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>
                                {{ rajaongkirCouriers.error }}
                            </div>
                            <div v-if="rajaongkirCouriers.success" class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-sm mt-4">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ rajaongkirCouriers.success }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <p class="text-xs text-gray-400">
                                <span class="text-gray-700 font-bold">{{ rajaongkirCouriers.list.filter(c => c.active).length }}</span>
                                dari
                                <span class="text-gray-700 font-bold">{{ rajaongkirCouriers.list.length }}</span>
                                kurir aktif untuk checkout
                            </p>
                            <button @click="saveRajaOngkirCouriers" :disabled="rajaongkirCouriers.saving || rajaongkirCouriers.loading"
                                    class="inline-flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                <svg v-if="rajaongkirCouriers.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/></svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ rajaongkirCouriers.saving ? 'Menyimpan...' : 'Simpan Kurir Checkout' }}
                            </button>
                        </div>
                    </div>
                    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Jasa Pengiriman</p>
                                <p class="text-xs text-gray-400">Kelola ekspedisi yang tampil di footer. Toggle untuk aktif/nonaktif.</p>
                            </div>
                        </div>

                        <!-- Loading skeleton -->
                        <div v-if="shipping.loading" class="p-6 space-y-3">
                            <div v-for="i in 4" :key="i" class="h-14 rounded-xl bg-gray-100 border border-gray-200 animate-pulse"></div>
                        </div>

                        <div v-else class="p-6 space-y-3">
                            <div v-if="shipping.couriers.length === 0" class="flex flex-col items-center justify-center py-10 text-center text-gray-400">
                                <svg class="w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                                <p class="text-sm">Belum ada kurir. Tambahkan di bawah.</p>
                            </div>

                            <div v-for="(courier, index) in shipping.couriers" :key="index"
                                class="flex items-center gap-3 rounded-xl px-4 py-3 border transition-all"
                                :class="courier.active ? 'bg-white border-gray-200' : 'bg-gray-50 border-gray-100 opacity-60'">
                                <div class="w-14 h-9 rounded-lg bg-white flex items-center justify-center flex-shrink-0 overflow-hidden border border-gray-200 px-1.5 shadow-sm">
                                    <img v-if="courier.logo" :src="courier.logo" :alt="courier.name" class="max-w-full max-h-full object-contain" @error="e => e.target.style.display = 'none'" />
                                    <span v-else class="text-[9px] font-bold text-gray-400 text-center leading-tight">{{ courier.name }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-700 truncate">
                                        {{ courier.name }}
                                        <span v-if="courier.service" class="text-gray-400 font-normal">— {{ courier.service }}</span>
                                    </p>
                                    <p class="text-[11px] text-gray-400 truncate">
                                        <span v-if="courier.code" class="uppercase font-semibold text-gray-500">{{ courier.code }}</span>
                                        <span v-else class="text-amber-500">tanpa kode — tidak muncul di revisi order</span>
                                    </p>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full flex-shrink-0 border"
                                    :class="courier.active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-gray-100 text-gray-400 border-gray-200'">
                                    {{ courier.active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                <button @click="courier.active = !courier.active"
                                    class="relative w-9 h-5 rounded-full flex-shrink-0 transition-colors duration-200 focus:outline-none"
                                    :class="courier.active ? 'bg-[#ED1F24]' : 'bg-gray-200'">
                                    <span class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200" :class="courier.active ? 'translate-x-4' : 'translate-x-0'"></span>
                                </button>
                                <button @click="removeCourier(index)" class="p-1 text-gray-300 hover:text-red-500 transition flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                </button>
                            </div>

                            <!-- Form tambah kurir baru -->
                            <div v-if="shipping.addingNew" class="rounded-xl border border-dashed border-[#ED1F24]/30 bg-red-50/30 px-4 py-4 space-y-3">
                                <p class="text-xs font-bold text-[#ED1F24]">Tambah Kurir Baru</p>
                                <div>
                                    <label class="block text-[10px] text-gray-400 uppercase tracking-wider mb-1">Nama Kurir *</label>
                                    <input v-model="shipping.newCourier.name" type="text" placeholder="Contoh: JNE Reguler"
                                        class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors" />
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] text-gray-400 uppercase tracking-wider mb-1">Kode Kurir</label>
                                        <input v-model="shipping.newCourier.code" type="text" placeholder="jne, jnt, sicepat..."
                                            class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-400 uppercase tracking-wider mb-1">Layanan</label>
                                        <input v-model="shipping.newCourier.service" type="text" placeholder="REG, YES, OKE..."
                                            class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors" />
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-400 -mt-1">Isi kode kurir agar kurir ini muncul di pilihan dropdown saat revisi pesanan.</p>
                                <div>
                                    <label class="block text-[10px] text-gray-400 uppercase tracking-wider mb-1">Logo Kurir</label>
                                    <div v-if="!shipping.newCourier.logoFile"
                                        class="relative rounded-xl border-2 border-dashed transition-all duration-200 cursor-pointer"
                                        :class="shipping.newCourier.logoDragging ? 'border-[#ED1F24] bg-red-50 scale-[1.01]' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'"
                                        @click="$refs.courierLogoInput.click()"
                                        @dragover.prevent="shipping.newCourier.logoDragging = true"
                                        @dragleave.prevent="shipping.newCourier.logoDragging = false"
                                        @drop.prevent="onCourierLogoDrop">
                                        <div class="flex flex-col items-center justify-center gap-2 py-5 px-4">
                                            <div class="w-9 h-9 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-xs text-gray-500"><span class="text-[#ED1F24] font-semibold">Pilih file</span> atau drag &amp; drop</p>
                                                <p class="text-[10px] text-gray-400 mt-0.5">PNG, JPG, SVG, WebP — maks. 2MB · disimpan sebagai <span class="text-amber-500 font-semibold">.webp</span></p>
                                            </div>
                                        </div>
                                        <input ref="courierLogoInput" type="file" accept=".jpg,.jpeg,.png,.svg,.webp,.gif" class="hidden" @change="onCourierLogoSelected" />
                                    </div>
                                    <div v-else class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-3 py-2.5">
                                        <div class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center px-1 overflow-hidden flex-shrink-0 shadow-sm">
                                            <img v-if="shipping.newCourier.logoPreview" :src="shipping.newCourier.logoPreview" class="max-w-full max-h-full object-contain" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold text-gray-700 truncate">{{ shipping.newCourier.logoFile.name }}</p>
                                            <p class="text-[10px] text-gray-400">{{ formatBytes(shipping.newCourier.logoFile.size) }} · <span class="text-amber-500 font-semibold">akan dikonversi ke .webp</span></p>
                                        </div>
                                        <button @click="clearCourierLogo" class="text-gray-400 hover:text-red-500 transition p-1 rounded flex-shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div v-if="shipping.newCourier.uploading" class="flex items-center gap-2 mt-2 text-xs text-[#ED1F24]">
                                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                                        Mengupload logo...
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 justify-end pt-1">
                                    <button @click="cancelAddCourier" class="text-xs text-gray-500 border border-gray-200 hover:border-gray-300 px-3 py-1.5 rounded-xl transition-all">Batal</button>
                                    <button @click="addCourier" :disabled="!shipping.newCourier.name.trim() || shipping.newCourier.uploading"
                                        class="text-xs bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold px-3 py-1.5 rounded-xl transition shadow-sm">
                                        Tambahkan
                                    </button>
                                </div>
                            </div>

                            <button v-if="!shipping.addingNew" @click="shipping.addingNew = true"
                                class="w-full flex items-center justify-center gap-2 border border-dashed border-gray-200 hover:border-[#ED1F24]/30 hover:bg-red-50/30 rounded-xl py-3 text-xs font-semibold text-gray-400 hover:text-[#ED1F24] transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Kurir Baru
                            </button>

                            <div v-if="shipping.error" class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-500 px-4 py-3 rounded-xl text-sm">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>
                                {{ shipping.error }}
                            </div>
                            <div v-if="shipping.success" class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-sm">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ shipping.success }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <p class="text-xs text-gray-400">
                                <span class="text-gray-700 font-bold">{{ shipping.couriers.filter(c => c.active).length }}</span>
                                dari
                                <span class="text-gray-700 font-bold">{{ shipping.couriers.length }}</span>
                                kurir aktif
                            </p>
                            <button @click="saveShipping" :disabled="shipping.saving || shipping.loading"
                                    class="inline-flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                <svg v-if="shipping.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/></svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ shipping.saving ? 'Menyimpan...' : 'Simpan Pengiriman' }}
                            </button>
                        </div>
                    </div>
                </template>

            </div>
        </div>

        <!-- Delete Logo Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="confirmDelete" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="confirmDelete = false">
                    <div class="bg-white border border-gray-200/80 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
                        <div class="p-6">
                            <div class="w-11 h-11 rounded-xl bg-red-50 border border-red-200 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800 mb-1">Hapus Logo?</h3>
                            <p class="text-sm text-gray-500">Logo akan dihapus permanen. Website akan tampil tanpa logo.</p>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <button @click="confirmDelete = false" class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-xl transition-all">Batal</button>
                            <button @click="deleteLogo" :disabled="logo.deleting"
                                    class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                <svg v-if="logo.deleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/></svg>
                                {{ logo.deleting ? 'Menghapus...' : 'Ya, Hapus Logo' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Delete Favicon Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="confirmDeleteFavicon" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="confirmDeleteFavicon = false">
                    <div class="bg-white border border-gray-200/80 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
                        <div class="p-6">
                            <div class="w-11 h-11 rounded-xl bg-red-50 border border-red-200 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800 mb-1">Hapus Favicon?</h3>
                            <p class="text-sm text-gray-500">Favicon akan dihapus permanen. Browser tab akan tampil tanpa ikon.</p>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <button @click="confirmDeleteFavicon = false" class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-xl transition-all">Batal</button>
                            <button @click="deleteFavicon" :disabled="favicon.deleting"
                                    class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                <svg v-if="favicon.deleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/></svg>
                                {{ favicon.deleting ? 'Menghapus...' : 'Ya, Hapus Favicon' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Delete Logo Footer Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="confirmDeleteLogoFooter" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="confirmDeleteLogoFooter = false">
                    <div class="bg-white border border-gray-200/80 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
                        <div class="p-6">
                            <div class="w-11 h-11 rounded-xl bg-red-50 border border-red-200 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800 mb-1">Hapus Logo Footer?</h3>
                            <p class="text-sm text-gray-500">Logo footer akan dihapus permanen. Footer website akan tampil tanpa logo.</p>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            <button @click="confirmDeleteLogoFooter = false" class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-xl transition-all">Batal</button>
                            <button @click="deleteLogoFooter" :disabled="logoFooter.deleting"
                                    class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white text-sm font-semibold px-5 py-2 rounded-xl transition shadow-sm">
                                <svg v-if="logoFooter.deleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/></svg>
                                {{ logoFooter.deleting ? 'Menghapus...' : 'Ya, Hapus Logo Footer' }}
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
    name: 'SiteSettings',
    components: { AdminLayout },

    data() {
        return {
            activeSection: 'branding',

            sections: [
                {
                    id: 'branding', label: 'Branding', desc: 'Logo, favicon, nama',
                    icon: '<svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>',
                },
                {
                    id: 'seo', label: 'SEO & Meta', desc: 'Google Search Console, meta tag',
                    icon: '<svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>',
                },
                {
                    id: 'contact', label: 'Kontak', desc: 'Alamat, telepon, email, WA',
                    icon: '<svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>',
                },
                {
                    id: 'social', label: 'Media Sosial', desc: 'Facebook, IG, TikTok, dll',
                    icon: '<svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>',
                },
                {
                    id: 'shipping', label: 'Pengiriman', desc: 'Jasa ekspedisi di footer',
                    icon: '<svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>',
                },
            ],

            seoPlaceholders: [
                { key: 'meta_title',       label: 'Meta Title',       desc: 'Judul halaman di hasil pencarian', icon: '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7"/></svg>' },
                { key: 'meta_description', label: 'Meta Description', desc: 'Deskripsi singkat di Google',     icon: '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' },
            ],

            socialPlatforms: {
                facebook:  { placeholder: 'https://facebook.com/namapage',          label: '<svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-blue-500 inline-block mr-1"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg> Facebook' },
                instagram: { placeholder: 'https://instagram.com/namaakun',         label: '<svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-pink-500 inline-block mr-1"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg> Instagram' },
                tiktok:    { placeholder: 'https://tiktok.com/@namaakun',            label: '<svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-gray-700 inline-block mr-1"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.67a8.18 8.18 0 004.77 1.52V6.74a4.85 4.85 0 01-1-.05z"/></svg> TikTok' },
                twitter:   { placeholder: 'https://x.com/namaakun',                 label: '<svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-gray-700 inline-block mr-1"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg> Twitter / X' },
                youtube:   { placeholder: 'https://youtube.com/@namasaluran',        label: '<svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-red-500 inline-block mr-1"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg> YouTube' },
                linkedin:  { placeholder: 'https://linkedin.com/company/namaperusahaan', label: '<svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-blue-600 inline-block mr-1"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg> LinkedIn' },
            },

            contact: {
                address:  { value: '', input: '', saving: false, error: '', success: '' },
                phone:    { value: '', input: '', saving: false, error: '', success: '', visible: true, visibleSaving: false },
                email:    { value: '', input: '', saving: false, error: '', success: '' },
                whatsapp: { value: '', input: '', saving: false, error: '', success: '', visible: true, visibleSaving: false },
                admin_whatsapp: { value: '', input: '', saving: false, error: '', success: '' },
                store_whatsapp: { value: '', input: '', saving: false, error: '', success: '' },
            },

            shipping: {
                loading: true,
                saving: false,
                error: '',
                success: '',
                couriers: [],
                addingNew: false,
                newCourier: { name: '', code: '', service: '', logoFile: null, logoPreview: null, logoDragging: false, uploading: false },
            },

            rajaongkirCouriers: {
                loading: true,
                saving: false,
                error: '',
                success: '',
                list: [],
            },

            social: {
                saving: false, error: '', success: '',
                links: { facebook: '', instagram: '', tiktok: '', twitter: '', youtube: '', linkedin: '' },
            },

            currentLogoUrl: null,
            currentLogoFooterUrl: null,
            confirmDeleteLogoFooter: false,
            selectedFile: null,
            previewUrl: null,
            isDragging: false,
            confirmDelete: false,
            logo: { uploading: false, deleting: false, error: '', success: '' },
            logoFooter: { uploading: false, deleting: false, error: '', success: '', file: null, previewUrl: null, dragging: false },

            currentFaviconUrl: null,
            confirmDeleteFavicon: false,
            favicon: { file: null, previewUrl: null, dragging: false, uploading: false, deleting: false, error: '', success: '' },

            siteName: { value: '', input: '', saving: false, error: '', success: '' },
            siteDescription: { value: '', input: '', saving: false, error: '', success: '' },

            seo: { googleVerification: '', googleVerificationInput: '', saving: false, error: '', success: '' },
        }
    },

    computed: {
        activeSectionData() {
            return this.sections.find(s => s.id === this.activeSection)
        },
    },

    async mounted() {
        document.title = 'Site Settings - Two Brothers Vape System'
        await Promise.all([this.fetchSettings(), this.fetchShippingCouriers(), this.fetchRajaOngkirCouriers()])
    },

    methods: {
        async fetchSettings() {
            try {
                const { data } = await axios.get('/settings')
                if (data.site_logo?.value)                { this.currentLogoUrl = data.site_logo.value }
                if (data.site_favicon?.value)             { this.currentFaviconUrl = data.site_favicon.value }
                if (data.site_name?.value)                { this.siteName.value = data.site_name.value; this.siteName.input = data.site_name.value }
                if (data.site_description?.value)         { this.siteDescription.value = data.site_description.value; this.siteDescription.input = data.site_description.value }
                if (data.google_site_verification?.value) { this.seo.googleVerification = data.google_site_verification.value; this.seo.googleVerificationInput = data.google_site_verification.value }
                if (data.contact_address?.value)          { this.contact.address.value  = data.contact_address.value;  this.contact.address.input  = data.contact_address.value  }
                if (data.contact_phone?.value)            { this.contact.phone.value    = data.contact_phone.value;    this.contact.phone.input    = data.contact_phone.value    }
                if (data.contact_email?.value)            { this.contact.email.value    = data.contact_email.value;    this.contact.email.input    = data.contact_email.value    }
                if (data.contact_whatsapp?.value)         { this.contact.whatsapp.value = data.contact_whatsapp.value; this.contact.whatsapp.input = data.contact_whatsapp.value }
                if (data.site_logo_footer?.value)         { this.currentLogoFooterUrl = data.site_logo_footer.value }
                if (data.admin_whatsapp?.value) {
                    this.contact.admin_whatsapp.value = data.admin_whatsapp.value
                    this.contact.admin_whatsapp.input = data.admin_whatsapp.value
                }
                if (data.store_whatsapp?.value) {
                    this.contact.store_whatsapp.value = data.store_whatsapp.value
                    this.contact.store_whatsapp.input = data.store_whatsapp.value 
                }
                if (data.contact_phone_visible?.value !== undefined) {
                    this.contact.phone.visible = data.contact_phone_visible.value !== '0'
                }
                if (data.contact_whatsapp_visible?.value !== undefined) {
                    this.contact.whatsapp.visible = data.contact_whatsapp_visible.value !== '0'
                }
                const socialKeys = ['facebook', 'instagram', 'tiktok', 'twitter', 'youtube', 'linkedin']
                socialKeys.forEach(key => { if (data[`social_${key}`]?.value) this.social.links[key] = data[`social_${key}`].value })
            } catch (e) { console.error('Failed to fetch settings:', e) }
        },

        async fetchShippingCouriers() {
            this.shipping.loading = true
            try {
                const { data } = await axios.get('/settings/shipping-couriers')
                this.shipping.couriers = data
            } catch (e) { console.error('Gagal load kurir:', e) }
            finally { this.shipping.loading = false }
        },

        async fetchRajaOngkirCouriers() {
            this.rajaongkirCouriers.loading = true
            try {
                const { data } = await axios.get('/settings/rajaongkir-couriers')
                this.rajaongkirCouriers.list = data
            } catch (e) { console.error('Gagal load kurir RajaOngkir:', e) }
            finally { this.rajaongkirCouriers.loading = false }
        },

        async saveRajaOngkirCouriers() {
            this.rajaongkirCouriers.saving = true
            this.rajaongkirCouriers.error = ''
            this.rajaongkirCouriers.success = ''
            try {
                const activeCodes = this.rajaongkirCouriers.list.filter(c => c.active).map(c => c.code)
                const { data } = await axios.put('/settings/rajaongkir-couriers', { active_couriers: activeCodes })
                this.rajaongkirCouriers.success = 'Kurir aktif untuk checkout berhasil disimpan.'
            } catch (e) {
                this.rajaongkirCouriers.error = e.response?.data?.message ?? 'Gagal menyimpan.'
            } finally {
                this.rajaongkirCouriers.saving = false
            }
        },

        onCourierLogoSelected(e) { const f = e.target.files[0]; if (f) this.processCourierLogo(f) },
        onCourierLogoDrop(e) { this.shipping.newCourier.logoDragging = false; const f = e.dataTransfer.files[0]; if (f) this.processCourierLogo(f) },
        processCourierLogo(file) {
            this.shipping.error = ''
            const allowed = ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml']
            if (!allowed.includes(file.type)) { this.shipping.error = 'Format file tidak didukung.'; return }
            if (file.size > 2 * 1024 * 1024)  { this.shipping.error = 'Ukuran file melebihi batas 2MB.'; return }
            this.shipping.newCourier.logoFile    = file
            this.shipping.newCourier.logoPreview = URL.createObjectURL(file)
        },
        clearCourierLogo() {
            this.shipping.newCourier.logoFile = null; this.shipping.newCourier.logoPreview = null
            if (this.$refs.courierLogoInput) this.$refs.courierLogoInput.value = ''
        },
        cancelAddCourier() {
            this.shipping.addingNew = false; this.shipping.error = ''
            this.shipping.newCourier = { name: '', code: '', service: '', logoFile: null, logoPreview: null, logoDragging: false, uploading: false }
            if (this.$refs.courierLogoInput) this.$refs.courierLogoInput.value = ''
        },
        async addCourier() {
            const name    = this.shipping.newCourier.name.trim()
            const code    = this.shipping.newCourier.code.trim().toLowerCase()
            const service = this.shipping.newCourier.service.trim().toUpperCase()
            if (!name) return
            if (this.shipping.couriers.find(c => c.name.toLowerCase() === name.toLowerCase() && (c.service || '').toUpperCase() === service)) {
                this.shipping.error = `Kurir "${name}"${service ? ' - ' + service : ''} sudah ada.`; return
            }
            let logoUrl = ''
            if (this.shipping.newCourier.logoFile) {
                this.shipping.newCourier.uploading = true; this.shipping.error = ''
                try {
                    const fd = new FormData(); fd.append('logo', this.shipping.newCourier.logoFile)
                    const { data } = await axios.post('/settings/courier-logo', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
                    logoUrl = data.url
                } catch (e) { this.shipping.error = e.response?.data?.message ?? 'Gagal mengupload logo.'; this.shipping.newCourier.uploading = false; return }
                finally { this.shipping.newCourier.uploading = false }
            }
            this.shipping.couriers.push({ name, code, service, logo: logoUrl, active: true })
            this.cancelAddCourier()
        },
        removeCourier(index) { this.shipping.couriers.splice(index, 1) },
        async saveShipping() {
            this.shipping.saving = true; this.shipping.error = ''; this.shipping.success = ''
            try {
                const { data } = await axios.put('/settings/shipping-couriers', { couriers: this.shipping.couriers })
                this.shipping.couriers = data.couriers; this.shipping.success = 'Data pengiriman berhasil disimpan.'
            } catch (e) { this.shipping.error = e.response?.data?.message ?? 'Gagal menyimpan.' }
            finally { this.shipping.saving = false }
        },
        async toggleContactVisibility(field) {
            const state = this.contact[field]
            const newVisible = !state.visible
            state.visibleSaving = true
            try {
                await axios.put(`/settings/contact_${field}_visible`, { value: newVisible })
                state.visible = newVisible
            } catch (e) {
                console.error(`Gagal update visibility ${field}:`, e)
            } finally {
                state.visibleSaving = false
            }
        },

        onFileSelected(e) { const f = e.target.files[0]; if (f) this.processFile(f) },
        onDrop(e) { this.isDragging = false; const f = e.dataTransfer.files[0]; if (f) this.processFile(f) },
        processFile(file) {
            this.logo.error = ''; this.logo.success = ''
            const allowed = ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml']
            if (!allowed.includes(file.type)) { this.logo.error = 'Format file tidak didukung.'; return }
            if (file.size > 2 * 1024 * 1024)  { this.logo.error = 'Ukuran file melebihi batas 2MB.'; return }
            this.selectedFile = file; this.previewUrl = URL.createObjectURL(file)
        },
        onLogoFooterSelected(e) { const f = e.target.files[0]; if (f) this.processLogoFooter(f) },
        onLogoFooterDrop(e)     { this.logoFooter.dragging = false; const f = e.dataTransfer.files[0]; if (f) this.processLogoFooter(f) },
        processLogoFooter(file) {
            this.logoFooter.error = ''; this.logoFooter.success = ''
            const allowed = ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml']
            if (!allowed.includes(file.type)) { this.logoFooter.error = 'Format file tidak didukung.'; return }
            if (file.size > 2 * 1024 * 1024)  { this.logoFooter.error = 'Ukuran file melebihi batas 2MB.'; return }
            this.logoFooter.file = file; this.logoFooter.previewUrl = URL.createObjectURL(file)
        },
        clearLogoFooter() {
            this.logoFooter.file = null; this.logoFooter.previewUrl = null
            this.logoFooter.error = ''; this.logoFooter.success = ''
            if (this.$refs.logoFooterInput) this.$refs.logoFooterInput.value = ''
        },
        async uploadLogoFooter() {
            if (!this.logoFooter.file) return
            this.logoFooter.uploading = true; this.logoFooter.error = ''; this.logoFooter.success = ''
            try {
                const fd = new FormData(); fd.append('logo_footer', this.logoFooter.file)
                const { data } = await axios.post('/settings/logo-footer', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
                this.currentLogoFooterUrl = data.url; this.logoFooter.success = 'Logo footer berhasil diperbarui.'
                this.clearLogoFooter()
            } catch (e) { this.logoFooter.error = e.response?.data?.message ?? 'Gagal mengunggah logo footer.' }
            finally     { this.logoFooter.uploading = false }
        },
        async deleteLogoFooter() {
            this.logoFooter.deleting = true
            try {
                await axios.delete('/settings/logo-footer')
                this.currentLogoFooterUrl = null; this.confirmDeleteLogoFooter = false; this.logoFooter.success = 'Logo footer berhasil dihapus.'
            } catch (e) { this.logoFooter.error = e.response?.data?.message ?? 'Gagal menghapus logo footer.'; this.confirmDeleteLogoFooter = false }
            finally { this.logoFooter.deleting = false }
        },
        clearSelection() {
            this.selectedFile = null; this.previewUrl = null; this.logo.error = ''; this.logo.success = ''
            if (this.$refs.fileInput) this.$refs.fileInput.value = ''
        },
        async uploadLogo() {
            if (!this.selectedFile) return
            this.logo.uploading = true; this.logo.error = ''; this.logo.success = ''
            try {
                const fd = new FormData(); fd.append('logo', this.selectedFile)
                const { data } = await axios.post('/settings/logo', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
                this.currentLogoUrl = data.url; this.logo.success = 'Logo berhasil diperbarui.'; this.clearSelection()
            } catch (e) { this.logo.error = e.response?.data?.message ?? 'Gagal mengunggah logo.' }
            finally     { this.logo.uploading = false }
        },
        async deleteLogo() {
            this.logo.deleting = true
            try {
                await axios.delete('/settings/logo')
                this.currentLogoUrl = null; this.confirmDelete = false; this.logo.success = 'Logo berhasil dihapus.'
            } catch (e) { this.logo.error = e.response?.data?.message ?? 'Gagal menghapus logo.'; this.confirmDelete = false }
            finally     { this.logo.deleting = false }
        },

        async saveContactField(field) {
            const state = this.contact[field]
            if (!state.input && !state.value) return
            state.saving = true; state.error = ''; state.success = ''

            const noPrefix = ['admin_whatsapp', 'store_whatsapp']
            const key = noPrefix.includes(field) ? field : `contact_${field}`

            try {
                await axios.put(`/settings/${key}`, { value: state.input })
                state.value = state.input
                state.success = 'Berhasil disimpan.'
            } catch (e) {
                state.error = e.response?.data?.message ?? 'Gagal menyimpan.'
            } finally {
                state.saving = false
            }
        },

        async saveSocial() {
            this.social.saving = true; this.social.error = ''; this.social.success = ''
            try {
                const keys = ['facebook', 'instagram', 'tiktok', 'twitter', 'youtube', 'linkedin']
                await Promise.all(keys.map(key => axios.put(`/settings/social_${key}`, { value: this.social.links[key] || null })))
                this.social.success = 'Media sosial berhasil disimpan.'
            } catch (e) { this.social.error = e.response?.data?.message ?? 'Gagal menyimpan media sosial.' }
            finally { this.social.saving = false }
        },

        onFaviconSelected(e) { const f = e.target.files[0]; if (f) this.processFavicon(f) },
        onFaviconDrop(e)     { this.favicon.dragging = false; const f = e.dataTransfer.files[0]; if (f) this.processFavicon(f) },
        processFavicon(file) {
            this.favicon.error = ''; this.favicon.success = ''
            const allowed = ['image/png','image/jpeg']
            if (!allowed.includes(file.type)) { this.favicon.error = 'Format harus PNG atau JPG.'; return }
            if (file.size > 512 * 1024)        { this.favicon.error = 'Ukuran file melebihi batas 512KB.'; return }
            this.favicon.file = file; this.favicon.previewUrl = URL.createObjectURL(file)
        },
        clearFavicon() {
            this.favicon.file = null; this.favicon.previewUrl = null; this.favicon.error = ''; this.favicon.success = ''
            if (this.$refs.faviconInput) this.$refs.faviconInput.value = ''
        },
        async uploadFavicon() {
            if (!this.favicon.file) return
            this.favicon.uploading = true; this.favicon.error = ''; this.favicon.success = ''
            try {
                const fd = new FormData(); fd.append('favicon', this.favicon.file)
                const { data } = await axios.post('/settings/favicon', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
                this.currentFaviconUrl = data.url; this.favicon.success = 'Favicon berhasil diperbarui.'; this.clearFavicon()
            } catch (e) { this.favicon.error = e.response?.data?.message ?? 'Gagal mengunggah favicon.' }
            finally     { this.favicon.uploading = false }
        },
        async deleteFavicon() {
            this.favicon.deleting = true
            try {
                await axios.delete('/settings/favicon')
                this.currentFaviconUrl = null; this.confirmDeleteFavicon = false; this.favicon.success = 'Favicon berhasil dihapus.'
            } catch (e) { this.favicon.error = e.response?.data?.message ?? 'Gagal menghapus favicon.'; this.confirmDeleteFavicon = false }
            finally     { this.favicon.deleting = false }
        },

        async saveSiteName() {
            if (!this.siteName.input) return
            this.siteName.saving = true; this.siteName.error = ''; this.siteName.success = ''
            try {
                await axios.put('/settings/site_name', { value: this.siteName.input })
                this.siteName.value = this.siteName.input; this.siteName.success = 'Nama website berhasil disimpan.'
            } catch (e) { this.siteName.error = e.response?.data?.message ?? 'Gagal menyimpan nama website.' }
            finally     { this.siteName.saving = false }
        },

        async saveSiteDescription() {
            if (!this.siteDescription.input || this.siteDescription.input.length > 200) return
            this.siteDescription.saving = true; this.siteDescription.error = ''; this.siteDescription.success = ''
            try {
                await axios.put('/settings/site_description', { value: this.siteDescription.input })
                this.siteDescription.value = this.siteDescription.input; this.siteDescription.success = 'Deskripsi website berhasil disimpan.'
            } catch (e) { this.siteDescription.error = e.response?.data?.message ?? 'Gagal menyimpan deskripsi.' }
            finally { this.siteDescription.saving = false }
        },

        async saveGoogleVerification() {
            if (!this.seo.googleVerificationInput) return
            this.seo.saving = true; this.seo.error = ''; this.seo.success = ''
            try {
                await axios.put('/settings/google_site_verification', { value: this.seo.googleVerificationInput })
                this.seo.googleVerification = this.seo.googleVerificationInput
                this.seo.success = 'Kode verifikasi berhasil disimpan. Sekarang klik "Verify" di Google Search Console.'
            } catch (e) { this.seo.error = e.response?.data?.message ?? 'Gagal menyimpan kode verifikasi.' }
            finally     { this.seo.saving = false }
        },
        async clearGoogleVerification() {
            this.seo.saving = true; this.seo.error = ''; this.seo.success = ''
            try {
                await axios.put('/settings/google_site_verification', { value: null })
                this.seo.googleVerification = ''; this.seo.googleVerificationInput = ''; this.seo.success = 'Kode verifikasi berhasil dihapus.'
            } catch (e) { this.seo.error = e.response?.data?.message ?? 'Gagal menghapus kode verifikasi.' }
            finally     { this.seo.saving = false }
        },

        formatBytes(bytes) {
            if (bytes < 1024) return bytes + ' B'
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB'
        },
    },
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>