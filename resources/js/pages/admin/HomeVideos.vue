<template>
    <AdminLayout title="Home Video Management">

        <!-- ═══════════════════════════════════════════
             HERO HEADER — DIPERTAHANKAN
        ═══════════════════════════════════════════ -->
        <div
            class="relative mb-6 rounded-2xl overflow-hidden"
            style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);"
        >
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10 bg-white"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5 bg-white"></div>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10 bg-white"></div>

            <div class="relative px-7 py-5">
                <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">
                    Content Management
                </p>
                <h1 class="text-2xl font-bold text-white tracking-tight">
                    Home Video Management
                </h1>
                <p class="text-red-200 text-xs mt-1.5">
                    Kelola video yang tampil di halaman utama
                </p>
            </div>

            <div class="relative border-t border-white/10 px-7 py-3 flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-6">
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Total Slot</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ slots.length }}</p>
                    </div>

                    <div class="w-px h-8 bg-white/15"></div>

                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Section Aktif</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ activeCount }}/{{ slots.length }}</p>
                    </div>

                    <div class="w-px h-8 bg-white/15"></div>

                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Video Terpasang</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ filledCount }}/{{ slots.length }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════
             SECTION LIST
        ═══════════════════════════════════════════ -->
        <div class="space-y-4">
            <div class="flex items-end justify-between gap-4 px-1 mb-2">
                <div>
                    <h2 class="text-sm font-bold text-gray-800">Video Sections</h2>
                    <p class="text-xs text-gray-400 mt-1">
                        Pilih section untuk membuka detail dan uploader.
                    </p>
                </div>

                <div class="hidden sm:flex items-center gap-2 text-[10px] text-gray-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    {{ filledCount }} video terpasang
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="space-y-3">
                <div
                    v-for="n in 2"
                    :key="n"
                    class="bg-white border border-gray-200 rounded-2xl overflow-hidden"
                >
                    <div class="grid grid-cols-[190px_minmax(0,1fr)] min-h-[118px]">
                        <div class="bg-gray-100 animate-pulse"></div>
                        <div class="p-5 space-y-4">
                            <div class="h-3 bg-gray-100 rounded w-1/3 animate-pulse"></div>
                            <div class="h-2.5 bg-gray-100 rounded w-2/3 animate-pulse"></div>
                            <div class="h-2.5 bg-gray-100 rounded w-1/2 animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>

            <template v-else>
                <section
                    v-for="item in sectionItems"
                    :key="item.slot"
                    class="group bg-white border rounded-2xl overflow-hidden transition-all duration-300"
                    :class="activeSlot === item.slot
                        ? 'border-[#ED1F24]/30 shadow-[0_8px_30px_rgba(17,24,39,0.07)]'
                        : 'border-gray-200/80 shadow-sm hover:border-gray-300 hover:shadow-md'"
                >
                    <div class="grid grid-cols-1 lg:grid-cols-[190px_minmax(0,1fr)]">

                        <!-- LEFT SECTION PANEL -->
                        <button
                            type="button"
                            @click="toggleSection(item.slot)"
                            class="relative text-left overflow-hidden min-h-[132px] lg:min-h-full focus:outline-none"
                            :class="item.slot === 'video_only'
                                ? 'bg-gradient-to-br from-[#ED1F24] to-[#C8181E]'
                                : 'bg-gradient-to-br from-[#BD2028] to-[#98171D]'"
                        >
                            <div class="absolute inset-0 opacity-20">
                                <div class="absolute -right-8 -bottom-10 w-28 h-28 rounded-full bg-white/20"></div>
                                <div class="absolute -right-3 top-16 w-14 h-14 rounded-full bg-white/10"></div>
                            </div>

                            <div class="relative h-full p-5 flex flex-col justify-between">
                                <div class="flex items-center justify-between">
                                    <span class="text-[9px] font-bold uppercase tracking-[0.18em] text-white/70">
                                        Video Section
                                    </span>

                                    <span
                                        class="w-8 h-8 rounded-xl border border-white/15 bg-white/10 flex items-center justify-center transition-all duration-300"
                                        :class="activeSlot === item.slot ? 'rotate-180 bg-white/15' : ''"
                                    >
                                        <svg
                                            class="w-4 h-4 text-white"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path d="M6 9l6 6 6-6"/>
                                        </svg>
                                    </span>
                                </div>

                                <div class="mt-8 lg:mt-0">
                                    <div class="w-9 h-9 rounded-xl border border-white/15 bg-white/10 flex items-center justify-center mb-3">
                                        <svg
                                            class="w-4 h-4 text-white"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <polygon points="5 3 19 12 5 21 5 3"/>
                                        </svg>
                                    </div>

                                    <h3 class="text-[15px] font-bold text-white leading-tight">
                                        {{ item.title }}
                                    </h3>
                                    <p class="text-[10px] text-white/65 mt-1">
                                        {{ item.subtitle }}
                                    </p>
                                </div>
                            </div>
                        </button>

                        <!-- RIGHT CONTENT -->
                        <div class="min-w-0">
                            <!-- SUMMARY ROW -->
                            <button
                                type="button"
                                @click="toggleSection(item.slot)"
                                class="w-full text-left px-5 py-4 sm:px-6 lg:px-7 border-b border-gray-100 focus:outline-none"
                            >
                                <div class="grid grid-cols-2 xl:grid-cols-[1.05fr_0.8fr_1.35fr_1fr_auto] items-center gap-x-6 gap-y-4">
                                    <div>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Section</p>
                                        <p class="text-sm font-semibold text-gray-800 mt-1">{{ item.shortTitle }}</p>
                                    </div>

                                    <div>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Status</p>
                                        <span
                                            class="inline-flex items-center gap-1.5 mt-1 text-xs font-semibold"
                                            :class="item.data.is_active ? 'text-emerald-600' : 'text-gray-400'"
                                        >
                                            <span
                                                class="w-1.5 h-1.5 rounded-full"
                                                :class="item.data.is_active ? 'bg-emerald-400' : 'bg-gray-300'"
                                            ></span>
                                            {{ item.data.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>

                                    <div>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Video</p>
                                        <p
                                            class="text-xs mt-1 truncate"
                                            :class="item.data.video_url
                                                ? 'font-medium text-gray-700'
                                                : 'text-amber-500'"
                                        >
                                            {{ item.data.video_url ? 'Video sudah terpasang' : 'Belum ada video' }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Terakhir Update</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ formatDate(item.data.updated_at) }}
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 col-span-2 xl:col-span-1">
                                        <span
                                            class="w-9 h-9 rounded-xl border border-gray-200 bg-white flex items-center justify-center transition-colors"
                                            :class="item.data.video_url
                                                ? 'text-gray-400 hover:text-[#ED1F24]'
                                                : 'text-gray-200'"
                                            @click.stop="item.data.video_url && openPreview(item.slot)"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </span>

                                        <span
                                            class="w-9 h-9 rounded-xl border flex items-center justify-center transition-all duration-300"
                                            :class="activeSlot === item.slot
                                                ? 'border-red-100 bg-red-50 text-[#ED1F24] rotate-180'
                                                : 'border-gray-200 bg-white text-gray-400'"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path d="M6 9l6 6 6-6"/>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </button>

                            <!-- ACCORDION CONTENT -->
                            <Transition name="section-expand">
                                <div
                                    v-if="activeSlot === item.slot"
                                    class="section-expand-wrapper"
                                >
                                    <div class="border-b border-gray-100 bg-gray-50/55">
                                        <div class="p-5 sm:p-6 lg:p-7">

                                            <!-- PANEL HEADER -->
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                                                <div>
                                                    <p class="text-[9px] font-bold uppercase tracking-widest text-[#ED1F24]">
                                                        Section Settings
                                                    </p>
                                                    <h4 class="text-sm font-bold text-gray-800 mt-1">
                                                        {{ item.title }}
                                                    </h4>
                                                </div>

                                                <button
                                                    type="button"
                                                    @click.stop="toggleActive(item.slot)"
                                                    class="self-start inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border text-xs font-semibold transition-all"
                                                    :class="item.data.is_active
                                                        ? 'border-emerald-100 bg-emerald-50 text-emerald-600 hover:bg-emerald-100'
                                                        : 'border-gray-200 bg-white text-gray-400 hover:bg-gray-100'"
                                                >
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full"
                                                        :class="item.data.is_active ? 'bg-emerald-400' : 'bg-gray-300'"
                                                    ></span>
                                                    {{ item.data.is_active ? 'Section Aktif' : 'Section Nonaktif' }}
                                                </button>
                                            </div>

                                            <!-- FEEDBACK -->
                                            <div
                                                v-if="errors[item.slot]"
                                                class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-500 px-3.5 py-3 rounded-xl text-xs mb-4"
                                            >
                                                <svg class="w-3.5 h-3.5 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                                </svg>
                                                <span>{{ errors[item.slot] }}</span>
                                            </div>

                                            <div
                                                v-if="successMsg[item.slot]"
                                                class="flex items-start gap-2 bg-emerald-50 border border-emerald-200 text-emerald-600 px-3.5 py-3 rounded-xl text-xs mb-4"
                                            >
                                                <svg class="w-3.5 h-3.5 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>{{ successMsg[item.slot] }}</span>
                                            </div>

                                            <!-- CONTENT GRID -->
                                            <div
                                                class="grid gap-5"
                                                :class="item.slot === 'video_with_caption'
                                                    ? 'xl:grid-cols-[minmax(0,1.65fr)_minmax(300px,0.95fr)]'
                                                    : 'xl:grid-cols-[minmax(0,1.8fr)_minmax(260px,0.8fr)]'"
                                            >

                                                <!-- MEDIA -->
                                                <div class="min-w-0">
                                                    <div class="flex items-center justify-between mb-2.5">
                                                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                                            File Video
                                                        </label>

                                                        <span
                                                            v-if="item.data.updated_at"
                                                            class="text-[10px] text-gray-400"
                                                        >
                                                            Updated {{ formatDate(item.data.updated_at) }}
                                                        </span>
                                                    </div>

                                                    <div
                                                        class="relative rounded-2xl border-2 border-dashed transition-all duration-200 cursor-pointer"
                                                        :class="dragging[item.slot]
                                                            ? 'border-[#ED1F24] bg-red-50'
                                                            : 'border-gray-200 bg-white hover:border-red-200 hover:bg-red-50/20'"
                                                        @dragover.prevent="dragging[item.slot] = true"
                                                        @dragleave="dragging[item.slot] = false"
                                                        @drop.prevent="e => handleDrop(e, item.slot)"
                                                        @click="openFilePicker(item.slot)"
                                                    >
                                                        <input
                                                            :ref="setFileInputRef(item.slot)"
                                                            type="file"
                                                            accept="video/*"
                                                            class="hidden"
                                                            @change="e => handleFileChange(e, item.slot)"
                                                        />

                                                        <div
                                                            v-if="!previews[item.slot]"
                                                            class="min-h-[250px] flex flex-col items-center justify-center text-center px-6 py-10"
                                                        >
                                                            <div class="w-14 h-14 rounded-2xl bg-red-50 border border-red-100 flex items-center justify-center">
                                                                <svg class="w-6 h-6 text-[#ED1F24]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                                                </svg>
                                                            </div>

                                                            <p class="text-sm font-medium text-gray-600 mt-4">
                                                                <span class="text-[#ED1F24] font-semibold">Pilih file</span>
                                                                atau drag &amp; drop
                                                            </p>

                                                            <p class="text-xs text-gray-400 mt-1.5">
                                                                MP4, WEBM, MOV · maksimum 200MB
                                                            </p>

                                                            <div class="inline-flex items-center gap-2 mt-4 px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-100 text-[10px] text-gray-400">
                                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-width="1.8" d="M12 16V8m0 0-3 3m3-3 3 3M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"/>
                                                                </svg>
                                                                Upload video baru
                                                            </div>
                                                        </div>

                                                        <div v-else class="relative p-3">
                                                            <video
                                                                :src="previews[item.slot]"
                                                                controls
                                                                class="w-full h-[260px] xl:h-[300px] rounded-xl bg-black object-contain"
                                                                @click.stop
                                                            ></video>

                                                            <button
                                                                type="button"
                                                                @click.stop="clearFile(item.slot)"
                                                                class="absolute top-5 right-5 w-8 h-8 rounded-full bg-white border border-gray-200 shadow-sm text-gray-500 hover:text-red-500 hover:border-red-200 flex items-center justify-center transition-all"
                                                                title="Hapus file terpilih"
                                                            >
                                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <p
                                                        v-if="removeVideo[item.slot]"
                                                        class="text-[10px] text-red-500 mt-2"
                                                    >
                                                        Video ditandai untuk dihapus. Klik "Simpan Perubahan" untuk menerapkan.
                                                    </p>

                                                    <p
                                                        v-else-if="!item.data.video_url"
                                                        class="text-[10px] text-amber-500 mt-2"
                                                    >
                                                        Video belum terpasang. Section belum akan muncul di homepage.
                                                    </p>
                                                </div>

                                                <!-- DETAILS / METADATA -->
                                                <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">
                                                    <div class="pb-4 border-b border-gray-100">
                                                        <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">
                                                            Detail Section
                                                        </p>
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            Konfigurasi konten yang akan tampil di homepage.
                                                        </p>
                                                    </div>

                                                    <div
                                                        v-if="item.slot === 'video_with_caption'"
                                                        class="pt-4 space-y-4"
                                                    >
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                                                                Judul
                                                            </label>

                                                            <input
                                                                v-model="videoCaption.title"
                                                                type="text"
                                                                placeholder="Masukkan judul..."
                                                                maxlength="255"
                                                                class="w-full h-10 bg-gray-50 border border-gray-200 rounded-xl px-3.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/5 transition-colors"
                                                            />
                                                        </div>

                                                        <div>
                                                            <div class="flex items-center justify-between mb-2">
                                                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                                                    Deskripsi
                                                                </label>
                                                                <span class="text-[10px] text-gray-400">
                                                                    {{ (videoCaption.description || '').length }}/2000
                                                                </span>
                                                            </div>

                                                            <textarea
                                                                v-model="videoCaption.description"
                                                                rows="7"
                                                                maxlength="2000"
                                                                placeholder="Masukkan deskripsi..."
                                                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-3 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/5 transition-colors resize-none"
                                                            ></textarea>
                                                        </div>
                                                    </div>

                                                    <div
                                                        v-else
                                                        class="pt-4"
                                                    >
                                                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-4">
                                                            <div class="flex items-start gap-3">
                                                                <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                                                                    <svg class="w-4 h-4 text-[#ED1F24]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                                        <polygon points="5 3 19 12 5 21 5 3"/>
                                                                    </svg>
                                                                </div>

                                                                <div>
                                                                    <p class="text-xs font-semibold text-gray-700">
                                                                        Video only
                                                                    </p>
                                                                    <p class="text-[11px] text-gray-400 mt-1 leading-5">
                                                                        Section ini menampilkan video tanpa judul dan deskripsi.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-5 pt-4 border-t border-gray-100">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <div>
                                                                <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">
                                                                    Visibility
                                                                </p>
                                                                <p class="text-[11px] text-gray-400 mt-1">
                                                                    Atur apakah section tampil di homepage.
                                                                </p>
                                                            </div>

                                                            <button
                                                                type="button"
                                                                @click.stop="toggleActive(item.slot)"
                                                                class="relative shrink-0 w-11 h-6 rounded-full transition-colors duration-200"
                                                                :class="item.data.is_active ? 'bg-[#ED1F24]' : 'bg-gray-200'"
                                                                :aria-pressed="item.data.is_active"
                                                            >
                                                                <span
                                                                    class="absolute top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-transform duration-200"
                                                                    :class="item.data.is_active ? 'translate-x-6' : 'translate-x-1'"
                                                                ></span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- FOOTER ACTIONS -->
                                            <div class="mt-5 pt-5 border-t border-gray-200/80 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
                                                <div class="text-[10px] text-gray-400">
                                                    <span v-if="removeVideo[item.slot]">
                                                        Video akan dihapus dari storage dan section setelah perubahan disimpan.
                                                    </span>
                                                    <span v-else-if="item.data.video_url">
                                                        Video aktif akan tersedia di homepage sesuai status section.
                                                    </span>
                                                    <span v-else>
                                                        Upload video terlebih dahulu sebelum menyimpan.
                                                    </span>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <button
                                                        v-if="item.data.video_url"
                                                        type="button"
                                                        @click.stop="openPreview(item.slot)"
                                                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-xs font-semibold text-gray-600 hover:bg-gray-50 transition-all"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                            <circle cx="12" cy="12" r="3"/>
                                                        </svg>
                                                        Preview
                                                    </button>

                                                    <button
                                                        type="button"
                                                        @click.stop="submit(item.slot)"
                                                        :disabled="saving[item.slot]"
                                                        class="inline-flex items-center justify-center gap-2 min-w-[178px] px-5 py-2.5 rounded-xl bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 text-white text-xs font-semibold shadow-sm transition-all"
                                                    >
                                                        <svg
                                                            v-if="saving[item.slot]"
                                                            class="w-4 h-4 animate-spin"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                                                        </svg>

                                                        <svg
                                                            v-else
                                                            class="w-4 h-4"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                        >
                                                            <path d="M5 13l4 4L19 7"/>
                                                        </svg>

                                                        {{ saving[item.slot] ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </section>
            </template>
        </div>

        <!-- ═══════════════════════════════════
             MODAL: Preview
        ═══════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="previewSlot"
                    class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                    @click.self="closePreview"
                >
                    <div class="bg-white border border-gray-200/80 rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <div>
                                <p class="text-sm font-bold text-gray-800">
                                    {{ previewSlot === 'video_only'
                                        ? 'Video Section — Video Saja'
                                        : 'Video Section — Video + Teks'
                                    }}
                                </p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Live preview</p>
                            </div>

                            <button
                                type="button"
                                @click="closePreview"
                                class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-700 flex items-center justify-center transition-all"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>

                        <div class="bg-gray-950">
                            <video
                                :src="previewSlot === 'video_only'
                                    ? videoOnly.video_url
                                    : videoCaption.video_url"
                                controls
                                autoplay
                                class="w-full"
                                style="max-height: 70vh;"
                            ></video>
                        </div>

                        <div
                            v-if="previewSlot === 'video_with_caption' && (videoCaption.title || videoCaption.description)"
                            class="px-6 py-5 border-t border-gray-100"
                        >
                            <p v-if="videoCaption.title" class="text-sm font-bold text-gray-800">
                                {{ videoCaption.title }}
                            </p>

                            <p v-if="videoCaption.description" class="text-xs text-gray-500 mt-1 leading-5">
                                {{ videoCaption.description }}
                            </p>
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
    name: 'HomeVideos',

    components: {
        AdminLayout,
    },

    data() {
        return {
            loading: true,

            slots: [
                'video_only',
                'video_with_caption',
            ],

            activeSlot: null,

            videoOnly: {
                slot: 'video_only',
                video_path: null,
                video_url: null,
                is_active: true,
                updated_at: null,
            },

            videoCaption: {
                slot: 'video_with_caption',
                video_path: null,
                video_url: null,
                title: '',
                description: '',
                is_active: true,
                updated_at: null,
            },

            files: {
                video_only: null,
                video_with_caption: null,
            },

            // Menandai video lama yang ingin benar-benar dihapus saat Save.
            removeVideo: {
                video_only: false,
                video_with_caption: false,
            },

            previews: {
                video_only: null,
                video_with_caption: null,
            },

            dragging: {
                video_only: false,
                video_with_caption: false,
            },

            saving: {
                video_only: false,
                video_with_caption: false,
            },

            errors: {
                video_only: '',
                video_with_caption: '',
            },

            successMsg: {
                video_only: '',
                video_with_caption: '',
            },

            fileInputRefs: {
                video_only: null,
                video_with_caption: null,
            },

            previewSlot: null,
        }
    },

    computed: {
        activeCount() {
            return [this.videoOnly, this.videoCaption]
                .filter(v => v.is_active)
                .length
        },

        filledCount() {
            return [this.videoOnly, this.videoCaption]
                .filter(v => v.video_url)
                .length
        },

        sectionItems() {
            return [
                {
                    slot: 'video_only',
                    title: 'Video Saja',
                    shortTitle: 'Video Saja',
                    subtitle: 'Tanpa judul & deskripsi',
                    data: this.videoOnly,
                },
                {
                    slot: 'video_with_caption',
                    title: 'Video + Teks',
                    shortTitle: 'Video + Teks',
                    subtitle: 'Dengan judul & deskripsi',
                    data: this.videoCaption,
                },
            ]
        },
    },

    mounted() {
        document.title = 'Home Videos - Two Brothers Vape System'
        this.fetchVideos()
    },

    methods: {
        async fetchVideos() {
            this.loading = true

            try {
                const res = await axios.get('/home-videos')
                const data = res.data.data ?? res.data

                const only = data.find(v => v.slot === 'video_only')
                const withCaption = data.find(v => v.slot === 'video_with_caption')

                if (only) {
                    this.videoOnly = only
                    this.previews.video_only = only.video_url
                    this.removeVideo.video_only = false
                }

                if (withCaption) {
                    this.videoCaption = withCaption
                    this.previews.video_with_caption = withCaption.video_url
                    this.removeVideo.video_with_caption = false
                }
            } catch (e) {
                console.error(e)
            } finally {
                this.loading = false
            }
        },

        toggleSection(slot) {
            const nextSlot = this.activeSlot === slot ? null : slot

            this.activeSlot = nextSlot

            if (nextSlot) {
                this.errors[nextSlot] = ''
                this.successMsg[nextSlot] = ''
            }
        },

        getTarget(slot) {
            return slot === 'video_only'
                ? this.videoOnly
                : this.videoCaption
        },

        formatDate(iso) {
            if (!iso) return '-'

            return new Date(iso).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            })
        },

        openPreview(slot) {
            this.previewSlot = slot
        },

        closePreview() {
            this.previewSlot = null
        },

        setFileInputRef(slot) {
            return (el) => {
                this.fileInputRefs[slot] = el
            }
        },

        openFilePicker(slot) {
            this.fileInputRefs[slot]?.click()
        },

        handleFileChange(e, slot) {
            const file = e.target.files?.[0]

            if (!file) return

            this.setSelectedFile(file, slot)
        },

        handleDrop(e, slot) {
            this.dragging[slot] = false

            const file = e.dataTransfer.files?.[0]

            if (!file) return

            this.setSelectedFile(file, slot)
        },

        setSelectedFile(file, slot) {
            if (!file.type.startsWith('video/')) {
                this.errors[slot] = 'File yang dipilih harus berupa video.'
                return
            }

            if (file.size > 200 * 1024 * 1024) {
                this.errors[slot] = 'Ukuran video maksimal 200MB.'
                return
            }

            this.errors[slot] = ''
            this.successMsg[slot] = ''

            // Kalau sebelumnya menandai video lama untuk dihapus,
            // upload file baru otomatis membatalkan flag tersebut.
            this.removeVideo[slot] = false

            const oldPreview = this.previews[slot]

            if (oldPreview && oldPreview.startsWith('blob:')) {
                URL.revokeObjectURL(oldPreview)
            }

            this.files[slot] = file
            this.previews[slot] = URL.createObjectURL(file)
        },

        clearFile(slot) {
            const currentPreview = this.previews[slot]
            const existing = slot === 'video_only'
                ? this.videoOnly.video_url
                : this.videoCaption.video_url

            // Bila yang sedang tampil adalah preview lokal dari file baru,
            // cukup batalkan file tersebut dan kembalikan video lama.
            if (currentPreview && currentPreview.startsWith('blob:')) {
                URL.revokeObjectURL(currentPreview)
                this.files[slot] = null
                this.previews[slot] = existing || null
                this.removeVideo[slot] = false
            } else if (existing) {
                // Bila yang dihapus adalah video yang sudah tersimpan,
                // jangan langsung menghapus server. Tandai untuk dihapus
                // ketika user menekan "Simpan Perubahan".
                this.files[slot] = null
                this.previews[slot] = null
                this.removeVideo[slot] = true
                this.successMsg[slot] = ''
            } else {
                this.files[slot] = null
                this.previews[slot] = null
                this.removeVideo[slot] = false
            }

            if (this.fileInputRefs[slot]) {
                this.fileInputRefs[slot].value = ''
            }
        },

        async toggleActive(slot) {
            const target = this.getTarget(slot)
            const newValue = !target.is_active

            try {
                const formData = new FormData()

                formData.append('is_active', newValue ? 1 : 0)
                formData.append('_method', 'PUT')

                const res = await axios.post(`/home-videos/${slot}`, formData)

                target.is_active = res.data.is_active
            } catch (e) {
                console.error(e)
                this.errors[slot] = e.response?.data?.message || 'Gagal mengubah status section.'
            }
        },

        async submit(slot) {
            this.saving[slot] = true
            this.errors[slot] = ''
            this.successMsg[slot] = ''

            try {
                const target = this.getTarget(slot)
                const formData = new FormData()

                formData.append('is_active', target.is_active ? 1 : 0)

                if (this.files[slot]) {
                    formData.append('video', this.files[slot])
                }

                if (this.removeVideo[slot]) {
                    formData.append('remove_video', '1')
                }

                if (slot === 'video_with_caption') {
                    formData.append('title', this.videoCaption.title || '')
                    formData.append('description', this.videoCaption.description || '')
                }

                formData.append('_method', 'PUT')

                const res = await axios.post(`/home-videos/${slot}`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                })

                if (slot === 'video_only') {
                    this.videoOnly = res.data
                } else {
                    this.videoCaption = res.data
                }

                this.previews[slot] = res.data.video_url
                this.files[slot] = null
                this.removeVideo[slot] = false

                this.successMsg[slot] = 'Perubahan berhasil disimpan.'

                setTimeout(() => {
                    this.successMsg[slot] = ''
                }, 3000)
            } catch (e) {
                this.errors[slot] = e.response?.data?.message || 'Terjadi kesalahan, coba lagi.'
            } finally {
                this.saving[slot] = false
            }
        },
    },
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

/* Smooth single-open accordion */
.section-expand-wrapper {
    display: grid;
    grid-template-rows: 1fr;
    opacity: 1;
    transform: translateY(0);
}

.section-expand-wrapper > div {
    min-height: 0;
    overflow: hidden;
}

.section-expand-enter-active,
.section-expand-leave-active {
    transition:
        grid-template-rows 0.34s cubic-bezier(0.4, 0, 0.2, 1),
        opacity 0.24s ease,
        transform 0.34s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.section-expand-enter-from,
.section-expand-leave-to {
    grid-template-rows: 0fr;
    opacity: 0;
    transform: translateY(-8px);
}

.section-expand-enter-to,
.section-expand-leave-from {
    grid-template-rows: 1fr;
    opacity: 1;
    transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
    .section-expand-enter-active,
    .section-expand-leave-active {
        transition: opacity 0.15s ease;
    }
}
</style>
