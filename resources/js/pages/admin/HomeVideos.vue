<template>
    <AdminLayout title="Home Video Management">

        <!-- ═══════════════════════════════════════════
             HERO HEADER
        ═══════════════════════════════════════════ -->
        <div class="relative mb-6 rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"></div>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10" style="background: white;"></div>

            <div class="relative px-7 py-5">
                <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">Content Management</p>
                <h1 class="text-2xl font-bold text-white tracking-tight">Home Video Management</h1>
                <p class="text-red-200 text-xs mt-1.5">Kelola video yang tampil di halaman utama</p>
            </div>

            <!-- Stats strip -->
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

        <!-- Loading state -->
        <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div v-for="n in 2" :key="n" class="bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gray-100 animate-pulse"></div>
                    <div class="flex-1 space-y-1.5">
                        <div class="h-3 bg-gray-100 rounded w-1/2 animate-pulse"></div>
                        <div class="h-2.5 bg-gray-100 rounded w-1/3 animate-pulse"></div>
                    </div>
                </div>
                <div class="p-5">
                    <div class="h-40 bg-gray-100 rounded-xl animate-pulse"></div>
                </div>
            </div>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            <!-- ═══════ Slot 1: Video Saja ═══════ -->
            <div class="bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-[#ED1F24]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-gray-800 truncate">Video Section — Video Saja</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Tanpa judul & deskripsi</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button v-if="videoOnly.video_url" @click="openPreview('video_only')"
                                class="p-2 rounded-xl border border-gray-200 text-gray-400 hover:text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        <button @click="toggleActive('video_only')"
                                class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-xl border transition-all"
                                :class="videoOnly.is_active
                                    ? 'bg-emerald-50 border-emerald-100 text-emerald-600 hover:bg-emerald-100'
                                    : 'bg-gray-100 border-gray-200 text-gray-400 hover:bg-gray-200'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="videoOnly.is_active ? 'bg-emerald-400' : 'bg-gray-300'"></span>
                            {{ videoOnly.is_active ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    <div v-if="errors.video_only" class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-500 px-3 py-2.5 rounded-xl text-xs">
                        <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ errors.video_only }}
                    </div>
                    <div v-if="successMsg.video_only" class="flex items-start gap-2 bg-emerald-50 border border-emerald-200 text-emerald-600 px-3 py-2.5 rounded-xl text-xs">
                        <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                        {{ successMsg.video_only }}
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">File Video</label>
                            <span v-if="videoOnly.updated_at" class="text-[10px] text-gray-400">
                                Terakhir diupdate: {{ formatDate(videoOnly.updated_at) }}
                            </span>
                        </div>
                        <div class="relative rounded-xl border-2 border-dashed transition-all duration-200 cursor-pointer"
                             :class="dragging.video_only
                                 ? 'border-[#ED1F24] bg-red-50 scale-[1.01]'
                                 : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50/50'"
                             @dragover.prevent="dragging.video_only = true"
                             @dragleave="dragging.video_only = false"
                             @drop.prevent="e => handleDrop(e, 'video_only')"
                             @click="$refs.fileInputVideoOnly.click()">
                            <input ref="fileInputVideoOnly" type="file" accept="video/*"
                                   @change="e => handleFileChange(e, 'video_only')" class="hidden" />

                            <div v-if="!previews.video_only" class="flex flex-col items-center justify-center gap-3 py-10 px-4">
                                <div class="w-12 h-12 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-500"><span class="text-[#ED1F24] font-semibold">Pilih file</span> atau drag &amp; drop</p>
                                    <p class="text-xs text-gray-400 mt-1">MP4, WEBM, MOV — maks 200MB</p>
                                </div>
                            </div>
                            <div v-else class="relative p-3">
                                <video :src="previews.video_only" controls class="w-full h-44 rounded-lg bg-black"></video>
                                <button @click.stop="clearFile('video_only')"
                                        class="absolute top-5 right-5 w-7 h-7 rounded-full bg-white border border-gray-200 shadow-sm text-gray-500 hover:text-red-500 hover:border-red-200 flex items-center justify-center transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                        <p v-if="!videoOnly.video_url" class="text-[10px] text-amber-500 mt-1.5">
                            ⚠ Belum ada video — section ini nggak akan tampil di homepage sampai video di-upload.
                        </p>
                    </div>

                    <button @click="submit('video_only')" :disabled="saving.video_only"
                            class="w-full flex items-center justify-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition shadow-sm">
                        <svg v-if="saving.video_only" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                        {{ saving.video_only ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </div>

            <!-- ═══════ Slot 2: Video + Judul + Deskripsi ═══════ -->
            <div class="bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-gray-800 truncate">Video Section — Video + Teks</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Dengan judul & deskripsi</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button v-if="videoCaption.video_url" @click="openPreview('video_with_caption')"
                                class="p-2 rounded-xl border border-gray-200 text-gray-400 hover:text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        <button @click="toggleActive('video_with_caption')"
                                class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-xl border transition-all"
                                :class="videoCaption.is_active
                                    ? 'bg-emerald-50 border-emerald-100 text-emerald-600 hover:bg-emerald-100'
                                    : 'bg-gray-100 border-gray-200 text-gray-400 hover:bg-gray-200'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="videoCaption.is_active ? 'bg-emerald-400' : 'bg-gray-300'"></span>
                            {{ videoCaption.is_active ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    <div v-if="errors.video_with_caption" class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-500 px-3 py-2.5 rounded-xl text-xs">
                        <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ errors.video_with_caption }}
                    </div>
                    <div v-if="successMsg.video_with_caption" class="flex items-start gap-2 bg-emerald-50 border border-emerald-200 text-emerald-600 px-3 py-2.5 rounded-xl text-xs">
                        <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                        {{ successMsg.video_with_caption }}
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">File Video</label>
                            <span v-if="videoCaption.updated_at" class="text-[10px] text-gray-400">
                                Terakhir diupdate: {{ formatDate(videoCaption.updated_at) }}
                            </span>
                        </div>
                        <div class="relative rounded-xl border-2 border-dashed transition-all duration-200 cursor-pointer"
                             :class="dragging.video_with_caption
                                 ? 'border-[#ED1F24] bg-red-50 scale-[1.01]'
                                 : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50/50'"
                             @dragover.prevent="dragging.video_with_caption = true"
                             @dragleave="dragging.video_with_caption = false"
                             @drop.prevent="e => handleDrop(e, 'video_with_caption')"
                             @click="$refs.fileInputVideoCaption.click()">
                            <input ref="fileInputVideoCaption" type="file" accept="video/*"
                                   @change="e => handleFileChange(e, 'video_with_caption')" class="hidden" />

                            <div v-if="!previews.video_with_caption" class="flex flex-col items-center justify-center gap-3 py-10 px-4">
                                <div class="w-12 h-12 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-500"><span class="text-[#ED1F24] font-semibold">Pilih file</span> atau drag &amp; drop</p>
                                    <p class="text-xs text-gray-400 mt-1">MP4, WEBM, MOV — maks 200MB</p>
                                </div>
                            </div>
                            <div v-else class="relative p-3">
                                <video :src="previews.video_with_caption" controls class="w-full h-44 rounded-lg bg-black"></video>
                                <button @click.stop="clearFile('video_with_caption')"
                                        class="absolute top-5 right-5 w-7 h-7 rounded-full bg-white border border-gray-200 shadow-sm text-gray-500 hover:text-red-500 hover:border-red-200 flex items-center justify-center transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                        <p v-if="!videoCaption.video_url" class="text-[10px] text-amber-500 mt-1.5">
                            ⚠ Belum ada video — section ini nggak akan tampil di homepage sampai video di-upload.
                        </p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Judul</label>
                        <input v-model="videoCaption.title" type="text" placeholder="Masukkan judul..." maxlength="255"
                               class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Deskripsi</label>
                            <span class="text-[10px] text-gray-400">{{ (videoCaption.description || '').length }}/2000</span>
                        </div>
                        <textarea v-model="videoCaption.description" rows="3" maxlength="2000" placeholder="Masukkan deskripsi..."
                                  class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors resize-none"></textarea>
                    </div>

                    <button @click="submit('video_with_caption')" :disabled="saving.video_with_caption"
                            class="w-full flex items-center justify-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition shadow-sm">
                        <svg v-if="saving.video_with_caption" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                        {{ saving.video_with_caption ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </div>

        </div>

        <!-- ═══════════════════════════════════
             MODAL: Preview
        ═══════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="previewSlot"
                     class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                     @click.self="closePreview">
                    <div class="bg-white border border-gray-200/80 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <span class="text-sm font-bold text-gray-800">
                                {{ previewSlot === 'video_only' ? 'Video Section — Video Saja' : 'Video Section — Video + Teks' }}
                            </span>
                            <button @click="closePreview"
                                    class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-700 flex items-center justify-center transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                        <div class="bg-gray-900">
                            <video :src="previewSlot === 'video_only' ? videoOnly.video_url : videoCaption.video_url"
                                   controls autoplay class="w-full" style="max-height: 70vh;"></video>
                        </div>
                        <div v-if="previewSlot === 'video_with_caption' && (videoCaption.title || videoCaption.description)"
                             class="px-6 py-4 border-t border-gray-100">
                            <p v-if="videoCaption.title" class="text-sm font-bold text-gray-800">{{ videoCaption.title }}</p>
                            <p v-if="videoCaption.description" class="text-xs text-gray-500 mt-1">{{ videoCaption.description }}</p>
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
    components: { AdminLayout },
    data() {
        return {
            loading: true,
            slots: ['video_only', 'video_with_caption'],
            videoOnly:     { slot: 'video_only', video_path: null, video_url: null, is_active: true, updated_at: null },
            videoCaption:  { slot: 'video_with_caption', video_path: null, video_url: null, title: '', description: '', is_active: true, updated_at: null },
            files:    { video_only: null, video_with_caption: null },
            previews: { video_only: null, video_with_caption: null },
            dragging: { video_only: false, video_with_caption: false },
            saving:   { video_only: false, video_with_caption: false },
            errors:   { video_only: '', video_with_caption: '' },
            successMsg: { video_only: '', video_with_caption: '' },
            previewSlot: null,
        }
    },

    computed: {
        activeCount() {
            return [this.videoOnly, this.videoCaption].filter(v => v.is_active).length
        },
        filledCount() {
            return [this.videoOnly, this.videoCaption].filter(v => v.video_url).length
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
                }
                if (withCaption) {
                    this.videoCaption = withCaption
                    this.previews.video_with_caption = withCaption.video_url
                }
            } catch (e) {
                console.error(e)
            } finally {
                this.loading = false
            }
        },

        formatDate(iso) {
            if (!iso) return '-'
            return new Date(iso).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
        },

        openPreview(slot) { this.previewSlot = slot },
        closePreview() { this.previewSlot = null },

        handleFileChange(e, slot) {
            const file = e.target.files[0]
            if (!file) return
            this.files[slot] = file
            this.previews[slot] = URL.createObjectURL(file)
        },
        handleDrop(e, slot) {
            this.dragging[slot] = false
            const file = e.dataTransfer.files[0]
            if (!file) return
            this.files[slot] = file
            this.previews[slot] = URL.createObjectURL(file)
        },
        clearFile(slot) {
            this.files[slot] = null
            const existing = slot === 'video_only' ? this.videoOnly.video_url : this.videoCaption.video_url
            this.previews[slot] = existing || null
            const ref = slot === 'video_only' ? 'fileInputVideoOnly' : 'fileInputVideoCaption'
            if (this.$refs[ref]) this.$refs[ref].value = ''
        },

        async toggleActive(slot) {
            const target = slot === 'video_only' ? this.videoOnly : this.videoCaption
            const newValue = !target.is_active
            try {
                const formData = new FormData()
                formData.append('is_active', newValue ? 1 : 0)
                formData.append('_method', 'PUT')
                const res = await axios.post(`/home-videos/${slot}`, formData)
                target.is_active = res.data.is_active
            } catch (e) {
                console.error(e)
            }
        },

        async submit(slot) {
            this.saving[slot] = true
            this.errors[slot] = ''
            this.successMsg[slot] = ''
            try {
                const target = slot === 'video_only' ? this.videoOnly : this.videoCaption
                const formData = new FormData()
                formData.append('is_active', target.is_active ? 1 : 0)
                if (this.files[slot]) formData.append('video', this.files[slot])
                if (slot === 'video_with_caption') {
                    formData.append('title', this.videoCaption.title || '')
                    formData.append('description', this.videoCaption.description || '')
                }
                formData.append('_method', 'PUT')

                const res = await axios.post(`/home-videos/${slot}`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })

                if (slot === 'video_only') this.videoOnly = res.data
                else this.videoCaption = res.data
                this.previews[slot] = res.data.video_url
                this.files[slot] = null
                this.successMsg[slot] = 'Perubahan berhasil disimpan.'
                setTimeout(() => { this.successMsg[slot] = '' }, 3000)
            } catch (e) {
                this.errors[slot] = e.response?.data?.message || 'Terjadi kesalahan, coba lagi.'
            } finally {
                this.saving[slot] = false
            }
        },
    }
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>