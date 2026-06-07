<template>
    <AdminLayout title="">

        <!-- ═══════════════════════════════════════════
             HERO HEADER
        ═══════════════════════════════════════════ -->
        <div class="relative mb-6 rounded-2xl overflow-hidden"
             style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"/>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"/>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10" style="background: white;"/>

            <div class="relative px-7 py-5 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <RouterLink to="/admin/articles"
                        class="flex items-center gap-1.5 text-xs font-semibold text-red-200 hover:text-white transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                        </svg>
                        Artikel
                    </RouterLink>
                    <span class="text-red-300/50">/</span>
                    <div>
                        <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-0.5">Manajemen Konten</p>
                        <h1 class="text-2xl font-bold text-white tracking-tight truncate max-w-md">
                            {{ form.title || (isEditing ? 'Edit Artikel' : 'Artikel Baru') }}
                        </h1>
                        <p class="text-red-200 text-xs mt-1">
                            {{ isEditing ? 'Edit dan perbarui artikel yang ada' : 'Buat artikel baru untuk dipublikasikan' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <!-- Status pill -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border border-white/20"
                          :class="form.status === 'published'
                              ? 'bg-emerald-500/20 text-emerald-100'
                              : 'bg-white/10 text-red-100'">
                        <span class="w-1.5 h-1.5 rounded-full"
                              :class="form.status === 'published' ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"/>
                        {{ form.status === 'published' ? 'Published' : 'Draft' }}
                    </span>

                    <!-- Save draft -->
                    <button @click="handleSubmit('draft')" :disabled="saving"
                        class="text-xs font-semibold px-4 py-2 rounded-xl border border-white/30 bg-white/15 text-white hover:bg-white/25 transition-all disabled:opacity-50">
                        Simpan Draft
                    </button>

                    <!-- Publish -->
                    <button v-if="canPublish" @click="handleSubmit('published')" :disabled="saving"
                        class="flex items-center gap-2 text-sm font-semibold px-5 py-2.5 rounded-xl border border-white/30 bg-white text-[#ED1F24] hover:bg-red-50 transition-all disabled:opacity-50 shadow-sm">
                        <svg v-if="saving" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                        </svg>
                        {{ saving ? 'Menyimpan...' : 'Publish' }}
                    </button>
                </div>
            </div>

            <!-- Stats strip -->
            <div class="relative border-t border-white/10 px-7 py-3 flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-6">
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Kata</p>
                        <p class="text-white text-sm font-bold tabular-nums">{{ wordCount }}</p>
                    </div>
                    <div class="w-px h-8 bg-white/15"/>
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Slug</p>
                        <p class="text-white text-sm font-bold font-mono truncate max-w-48">/{{ form.slug || '—' }}</p>
                    </div>
                    <div v-if="isEditing && contentStore.currentItem" class="w-px h-8 bg-white/15"/>
                    <div v-if="isEditing && contentStore.currentItem">
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Diperbarui</p>
                        <p class="text-white text-sm font-bold">{{ formatDate(contentStore.currentItem.updated_at) }}</p>
                    </div>
                    <div v-if="isEditing && contentStore.currentItem?.author" class="w-px h-8 bg-white/15"/>
                    <div v-if="isEditing && contentStore.currentItem?.author">
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">Penulis</p>
                        <p class="text-white text-sm font-bold">{{ contentStore.currentItem.author.name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── SUCCESS BANNER ─────────────────────────────────────────── -->
        <transition name="fade">
            <div v-if="successMessage"
                 class="mb-5 flex items-center gap-3 px-5 py-3 rounded-2xl border"
                 style="background: #f0fdf4; border-color: #bbf7d0;">
                <div class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-emerald-700">{{ successMessage }}</span>
                <button @click="successMessage = ''" class="ml-auto text-emerald-400 hover:text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </transition>

        <!-- ── MAIN LAYOUT ─────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-6">

            <!-- LEFT COLUMN -->
            <div class="space-y-5 min-w-0">

                <!-- TITLE -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-6 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Judul Artikel</span>
                        <span class="text-xs font-bold" style="color: #ED1F24;">*</span>
                    </div>
                    <div class="px-6 pt-5 pb-2">
                        <textarea
                            v-model="form.title"
                            @input="autoResizeTitle"
                            ref="titleRef"
                            rows="1"
                            placeholder="Tulis judul artikel yang menarik..."
                            class="w-full text-2xl font-bold text-gray-900 bg-transparent border-0 focus:outline-none resize-none placeholder-gray-200 leading-tight"
                            style="min-height: 40px;"
                        />
                        <p v-if="errors.title" class="text-xs mt-1" style="color: #ED1F24;">{{ errors.title }}</p>
                    </div>
                    <!-- Slug bar -->
                    <div class="flex items-center gap-2 px-6 py-3 border-t border-gray-100 mt-2" style="background: #fafafa;">
                        <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
                        </svg>
                        <span class="text-xs text-gray-300 font-mono shrink-0">/blog/</span>
                        <input
                            v-model="form.slug"
                            @input="slugEdited = true"
                            type="text"
                            placeholder="slug-artikel"
                            class="flex-1 text-xs font-mono text-gray-500 bg-transparent border-0 focus:outline-none placeholder-gray-200"
                            :class="errors.slug ? 'text-red-400' : ''"
                        />
                        <span v-if="form.slug" class="text-xs text-gray-300 shrink-0">{{ form.slug.length }} karakter</span>
                    </div>
                    <p v-if="errors.slug" class="px-6 pb-3 text-xs" style="color: #ED1F24;">{{ errors.slug }}</p>
                </div>

                <!-- EDITOR -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                                <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/>
                                </svg>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Konten</span>
                            <span class="text-xs font-bold" style="color: #ED1F24;">*</span>
                        </div>
                        <span class="text-xs text-gray-400 font-semibold">{{ wordCount }} kata</span>
                    </div>

                    <!-- Toolbar -->
                    <div v-if="editor" class="flex flex-wrap items-center gap-0.5 px-4 py-2.5 border-b border-gray-100 bg-gray-50/40">
                        <!-- Group 1: Text style -->
                        <div class="flex items-center gap-0.5 pr-2 mr-2 border-r border-gray-200">
                            <button v-for="btn in toolbarGroup1" :key="btn.label"
                                @click="btn.action()" :title="btn.label" type="button"
                                class="w-8 h-8 flex items-center justify-center rounded-xl transition-all"
                                :style="btn.isActive() ? 'background: #ED1F24; color: #fff;' : 'color: #6b7280;'"
                                @mouseenter="e => { if (!btn.isActive()) e.currentTarget.style.background = '#f3f4f6' }"
                                @mouseleave="e => { if (!btn.isActive()) e.currentTarget.style.background = 'transparent' }">
                                <span v-html="btn.icon" class="w-3.5 h-3.5 flex items-center justify-center"/>
                            </button>
                        </div>
                        <!-- Group 2: Headings -->
                        <div class="flex items-center gap-0.5 pr-2 mr-2 border-r border-gray-200">
                            <button v-for="btn in toolbarGroup2" :key="btn.label"
                                @click="btn.action()" :title="btn.label" type="button"
                                class="px-2 h-8 flex items-center justify-center rounded-xl text-xs font-bold transition-all"
                                :style="btn.isActive() ? 'background: #ED1F24; color: #fff;' : 'color: #6b7280;'"
                                @mouseenter="e => { if (!btn.isActive()) e.currentTarget.style.background = '#f3f4f6' }"
                                @mouseleave="e => { if (!btn.isActive()) e.currentTarget.style.background = 'transparent' }">
                                {{ btn.label }}
                            </button>
                        </div>
                        <!-- Group 3: Lists & blocks -->
                        <div class="flex items-center gap-0.5 pr-2 mr-2 border-r border-gray-200">
                            <button v-for="btn in toolbarGroup3" :key="btn.label"
                                @click="btn.action()" :title="btn.label" type="button"
                                class="w-8 h-8 flex items-center justify-center rounded-xl transition-all"
                                :style="btn.isActive() ? 'background: #ED1F24; color: #fff;' : 'color: #6b7280;'"
                                @mouseenter="e => { if (!btn.isActive()) e.currentTarget.style.background = '#f3f4f6' }"
                                @mouseleave="e => { if (!btn.isActive()) e.currentTarget.style.background = 'transparent' }">
                                <span v-html="btn.icon" class="w-3.5 h-3.5 flex items-center justify-center"/>
                            </button>
                        </div>
                        <!-- Group 4: Media -->
                        <div class="flex items-center gap-0.5">
                            <label class="w-8 h-8 flex items-center justify-center rounded-xl text-gray-400 hover:bg-gray-100 cursor-pointer transition-all" title="Upload Gambar">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                </svg>
                                <input type="file" accept="image/*" class="hidden" @change="uploadEditorImage"/>
                            </label>
                            <button @click="setLink" title="Insert Link" type="button"
                                class="w-8 h-8 flex items-center justify-center rounded-xl transition-all"
                                :style="editor.isActive('link') ? 'background: #ED1F24; color: #fff;' : 'color: #6b7280;'"
                                @mouseenter="e => { if (!editor.isActive('link')) e.currentTarget.style.background = '#f3f4f6' }"
                                @mouseleave="e => { if (!editor.isActive('link')) e.currentTarget.style.background = 'transparent' }">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Editor body -->
                    <div class="relative" :class="errors.body ? 'ring-2 ring-inset ring-red-300' : ''">
                        <editor-content
                            :editor="editor"
                            class="tiptap-editor px-8 py-6 min-h-[420px] focus:outline-none cursor-text"
                            @click="editor?.commands.focus()"
                        />
                        <div v-if="!form.body || form.body === '<p></p>'"
                             class="absolute top-6 left-8 pointer-events-none text-gray-300 text-base select-none">
                            Mulai menulis konten artikel di sini...
                        </div>
                    </div>
                    <p v-if="errors.body" class="px-6 pb-3 text-xs font-semibold" style="color: #ED1F24;">{{ errors.body }}</p>
                </div>

                <!-- EXCERPT -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                                <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h12"/>
                                </svg>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Ringkasan (Excerpt)</span>
                        </div>
                        <span class="text-xs" :class="(form.excerpt?.length ?? 0) > 450 ? 'font-bold' : 'text-gray-400'"
                              :style="(form.excerpt?.length ?? 0) > 450 ? 'color: #ED1F24' : ''">
                            {{ form.excerpt?.length ?? 0 }}/500
                        </span>
                    </div>
                    <div class="px-6 py-5">
                        <textarea
                            v-model="form.excerpt"
                            rows="3"
                            maxlength="500"
                            placeholder="Ringkasan singkat yang muncul di preview artikel..."
                            class="w-full text-sm text-gray-700 bg-transparent border-0 focus:outline-none resize-none placeholder-gray-300 leading-relaxed"
                        />
                    </div>
                </div>

                <!-- SEO -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-6 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Search Engine Optimization</span>
                    </div>

                    <div class="p-6 space-y-5">
                        <!-- Meta title -->
                        <div>
                            <div class="flex justify-between mb-1.5">
                                <label class="text-xs font-bold text-gray-600">Meta Title</label>
                                <span class="text-xs" :class="(form.meta_title?.length ?? 0) > 55 ? 'font-bold' : 'text-gray-400'"
                                      :style="(form.meta_title?.length ?? 0) > 55 ? 'color: #ED1F24' : ''">
                                    {{ form.meta_title?.length ?? 0 }}/60
                                </span>
                            </div>
                            <input v-model="form.meta_title" type="text" maxlength="60"
                                placeholder="Judul untuk Google Search..."
                                class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none transition-colors text-gray-800 bg-gray-50/50"
                                @focus="e => e.target.style.borderColor = '#ED1F24'"
                                @blur="e => e.target.style.borderColor = '#e5e7eb'"
                            />
                        </div>

                        <!-- Meta description -->
                        <div>
                            <div class="flex justify-between mb-1.5">
                                <label class="text-xs font-bold text-gray-600">Meta Description</label>
                                <span class="text-xs" :class="(form.meta_description?.length ?? 0) > 155 ? 'font-bold' : 'text-gray-400'"
                                      :style="(form.meta_description?.length ?? 0) > 155 ? 'color: #ED1F24' : ''">
                                    {{ form.meta_description?.length ?? 0 }}/160
                                </span>
                            </div>
                            <textarea v-model="form.meta_description" rows="2" maxlength="160"
                                placeholder="Deskripsi singkat untuk mesin pencari..."
                                class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none transition-colors resize-none text-gray-800 bg-gray-50/50"
                                @focus="e => e.target.style.borderColor = '#ED1F24'"
                                @blur="e => e.target.style.borderColor = '#e5e7eb'"
                            />
                        </div>

                        <!-- Google preview -->
                        <div class="rounded-xl border border-gray-100 p-4" style="background: #f8fafc;">
                            <p class="text-[10px] font-bold text-gray-400 mb-3 flex items-center gap-2 uppercase tracking-widest">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                </svg>
                                Preview Google
                            </p>
                            <div class="space-y-0.5">
                                <p class="text-base font-medium text-blue-600 truncate leading-tight">
                                    {{ form.meta_title || form.title || 'Judul Artikel Anda' }}
                                </p>
                                <p class="text-xs text-green-700">
                                    https://yourdomain.com/blog/<span class="font-medium">{{ form.slug || 'slug-artikel' }}</span>
                                </p>
                                <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">
                                    {{ form.meta_description || form.excerpt || 'Deskripsi artikel akan muncul di sini saat dibagikan ke Google Search.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT SIDEBAR -->
            <div class="space-y-4">

                <!-- PUBLISH CARD -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Publikasi</span>
                    </div>
                    <div class="p-5">
                        <!-- Draft/Published toggle -->
                        <div class="flex rounded-xl overflow-hidden border border-gray-200 mb-4">
                            <button @click="form.status = 'draft'" :disabled="isStaff" type="button"
                                class="flex-1 py-2.5 text-xs font-bold transition-all flex items-center justify-center gap-1.5"
                                :style="form.status === 'draft'
                                    ? 'background: #f3f4f6; color: #374151;'
                                    : 'background: #fff; color: #9ca3af;'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                </svg>
                                Draft
                            </button>
                            <button @click="form.status = 'published'" :disabled="isStaff" type="button"
                                class="flex-1 py-2.5 text-xs font-bold transition-all flex items-center justify-center gap-1.5 disabled:opacity-40 disabled:cursor-not-allowed"
                                :style="form.status === 'published'
                                    ? 'background: #ED1F24; color: #fff;'
                                    : 'background: #fff; color: #9ca3af;'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                Published
                            </button>
                        </div>

                        <p v-if="isStaff" class="text-xs text-amber-600 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2 mb-4">
                            ⚠️ Role staff hanya dapat menyimpan sebagai draft.
                        </p>

                        <button @click="handleSubmit('draft')" :disabled="saving"
                            class="w-full py-2.5 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all disabled:opacity-50 mb-2">
                            💾 Simpan Draft
                        </button>
                        <button v-if="canPublish" @click="handleSubmit('published')" :disabled="saving"
                            class="w-full py-2.5 text-xs font-bold text-white rounded-xl transition-all disabled:opacity-50 shadow-sm"
                            style="background: linear-gradient(135deg, #ED1F24, #b91c1c);">
                            🚀 {{ saving ? 'Menyimpan...' : 'Publish Sekarang' }}
                        </button>
                    </div>
                </div>

                <!-- THUMBNAIL CARD -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Thumbnail</span>
                    </div>
                    <div class="p-5">
                        <!-- Preview -->
                        <div v-if="thumbnailPreview" class="relative mb-3 group rounded-xl overflow-hidden border border-gray-100">
                            <img :src="thumbnailPreview" alt="Thumbnail" class="w-full aspect-video object-cover"/>
                            <div class="absolute inset-0 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all"
                                 style="background: rgba(0,0,0,0.45);">
                                <label class="cursor-pointer px-3 py-1.5 bg-white/90 rounded-lg text-xs font-bold text-gray-700 hover:bg-white transition-all">
                                    Ganti
                                    <input type="file" accept="image/*" class="hidden" @change="onThumbnailChange"/>
                                </label>
                                <button @click="removeThumbnail" type="button"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all"
                                    style="background: #ED1F24;">
                                    Hapus
                                </button>
                            </div>
                        </div>

                        <!-- Upload area -->
                        <label v-if="!thumbnailPreview" class="block cursor-pointer group">
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center transition-all group-hover:border-[#ED1F24]/40"
                                 style="background: #fafafa;"
                                 @dragover.prevent
                                 @drop.prevent="onDrop">
                                <div class="w-10 h-10 rounded-xl mx-auto mb-3 flex items-center justify-center" style="background: rgba(237,31,36,0.08);">
                                    <svg class="w-5 h-5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-gray-500 mb-0.5">Klik atau drag & drop</p>
                                <p class="text-xs text-gray-400">JPG, PNG, WebP · Maks. 2MB</p>
                                <p class="text-xs text-gray-400 mt-1">Rasio ideal 16:9</p>
                            </div>
                            <input type="file" accept="image/*" class="hidden" @change="onThumbnailChange"/>
                        </label>
                    </div>
                </div>

                <!-- TAGS CARD -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tags</span>
                        <span class="ml-auto text-xs text-gray-400 font-semibold">{{ form.tags.length }} tag</span>
                    </div>
                    <div class="p-5">
                        <div class="flex flex-wrap gap-1.5 mb-3 min-h-8">
                            <span v-for="(tag, i) in form.tags" :key="tag"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-all"
                                style="background: rgba(237,31,36,0.08); color: #ED1F24;">
                                # {{ tag }}
                                <button @click="removeTag(i)" type="button"
                                    class="w-3.5 h-3.5 rounded-full flex items-center justify-center hover:bg-red-200 transition-all text-xs font-bold leading-none">×</button>
                            </span>
                            <span v-if="!form.tags.length" class="text-xs text-gray-300 italic py-1">Belum ada tag...</span>
                        </div>
                        <div class="flex gap-2">
                            <input v-model="tagInput" @keydown.enter.prevent="addTag" @keydown.','="addTag"
                                type="text" placeholder="Ketik lalu Enter..."
                                class="flex-1 text-sm border border-gray-200 rounded-xl px-3 py-2 focus:outline-none transition-colors text-gray-700 placeholder-gray-300 bg-gray-50/50"
                                @focus="e => e.target.style.borderColor = '#ED1F24'"
                                @blur="e => e.target.style.borderColor = '#e5e7eb'"
                            />
                            <button @click="addTag" type="button"
                                class="w-9 h-9 flex items-center justify-center text-white rounded-xl font-bold text-lg transition-all shadow-sm"
                                style="background: #ED1F24;"
                                @mouseenter="e => e.currentTarget.style.background = '#b91c1c'"
                                @mouseleave="e => e.currentTarget.style.background = '#ED1F24'">+</button>
                        </div>
                    </div>
                </div>

                <!-- ARTICLE INFO (edit mode) -->
                <div v-if="isEditing && contentStore.currentItem" class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100" style="background: #fafafa;">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(237,31,36,0.1);">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Info Artikel</span>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-400">Dibuat</span>
                            <span class="font-semibold text-gray-700">{{ formatDate(contentStore.currentItem.created_at) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-400">Diperbarui</span>
                            <span class="font-semibold text-gray-700">{{ formatDate(contentStore.currentItem.updated_at) }}</span>
                        </div>
                        <div v-if="contentStore.currentItem.author" class="flex justify-between items-center text-xs">
                            <span class="text-gray-400">Penulis</span>
                            <div class="flex items-center gap-1.5">
                                <div class="w-5 h-5 rounded-full flex items-center justify-center text-white shrink-0"
                                     style="background: linear-gradient(135deg, #ED1F24, #7f1d1d); font-size: 9px; font-weight: 700;">
                                    {{ contentStore.currentItem.author.name[0] }}
                                </div>
                                <span class="font-semibold text-gray-700">{{ contentStore.currentItem.author.name }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-100 text-xs">
                            <span class="text-gray-400">ID Artikel</span>
                            <span class="font-mono font-semibold text-gray-400">#{{ contentStore.currentItem.id }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import Link from '@tiptap/extension-link'
import Placeholder from '@tiptap/extension-placeholder'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import { useContentStore } from '../../store/useContentStore'
import axios from '../../axios.js'
import { getUser } from '../../auth.js'

const route        = useRoute()
const router       = useRouter()
const contentStore = useContentStore()

const isEditing  = computed(() => !!route.params.id)
const user = computed(() => getUser() ?? {})
const userRole   = computed(() => user.value.role || '')
const isStaff    = computed(() => userRole.value === 'staff')
const canPublish = computed(() => ['admin', 'manager'].includes(userRole.value))

// ── Form ──────────────────────────────────────────────────────
const form = ref({
    type: 'article', title: '', slug: '', body: '',
    excerpt: '', tags: [], status: 'draft',
    meta_title: '', meta_description: '', thumbnail: null,
})
const errors           = ref({})
const successMessage   = ref('')
const saving           = ref(false)
const slugEdited       = ref(false)
const thumbnailPreview = ref(null)
const tagInput         = ref('')
const titleRef         = ref(null)

const wordCount = computed(() => {
    const text = form.value.body.replace(/<[^>]*>/g, '').trim()
    return text ? text.split(/\s+/).length : 0
})

// Auto slug
watch(() => form.value.title, (val) => {
    if (!isEditing.value && !slugEdited.value) {
        form.value.slug = val.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .slice(0, 80)
    }
})

function autoResizeTitle() {
    const el = titleRef.value
    if (!el) return
    el.style.height = 'auto'
    el.style.height = el.scrollHeight + 'px'
}

// ── Tiptap ────────────────────────────────────────────────────
const editor = useEditor({
    extensions: [
        StarterKit.configure({ heading: { levels: [2, 3, 4] } }),
        Image.configure({ inline: false, allowBase64: false }),
        Link.configure({ openOnClick: false }),
    ],
    content: '',
    editorProps: { attributes: { class: 'focus:outline-none' } },
    onUpdate: ({ editor: e }) => { form.value.body = e.getHTML() },
})

onBeforeUnmount(() => editor.value?.destroy())

// ── Toolbar groups ─────────────────────────────────────────────
const toolbarGroup1 = computed(() => [
    {
        label: 'Bold', isActive: () => editor.value?.isActive('bold') ?? false,
        action: () => editor.value?.chain().focus().toggleBold().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.744h-.753v8.25h7.125a4.125 4.125 0 0 0 0-8.25H6.75Zm0 0v8.25m0 0H6v8.25h7.875a4.125 4.125 0 0 0 0-8.25H6.75Z"/></svg>',
    },
    {
        label: 'Italic', isActive: () => editor.value?.isActive('italic') ?? false,
        action: () => editor.value?.chain().focus().toggleItalic().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.248 20.246H9.05m0 0h3.696m-3.696 0 5.893-16.502m0 0h-3.697m3.697 0h3.803"/></svg>',
    },
    {
        label: 'Underline', isActive: () => false,
        action: () => editor.value?.chain().focus().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.995 3.744v7.5a6 6 0 1 1-12 0v-7.5m-2.25 16.502h16.5"/></svg>',
    },
    {
        label: 'Strike', isActive: () => editor.value?.isActive('strike') ?? false,
        action: () => editor.value?.chain().focus().toggleStrike().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12h8m-8 0H4m8 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm0 6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>',
    },
    {
        label: 'Code', isActive: () => editor.value?.isActive('code') ?? false,
        action: () => editor.value?.chain().focus().toggleCode().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/></svg>',
    },
])

const toolbarGroup2 = computed(() => [
    { label: 'H2', isActive: () => editor.value?.isActive('heading', { level: 2 }) ?? false, action: () => editor.value?.chain().focus().toggleHeading({ level: 2 }).run() },
    { label: 'H3', isActive: () => editor.value?.isActive('heading', { level: 3 }) ?? false, action: () => editor.value?.chain().focus().toggleHeading({ level: 3 }).run() },
    { label: 'H4', isActive: () => editor.value?.isActive('heading', { level: 4 }) ?? false, action: () => editor.value?.chain().focus().toggleHeading({ level: 4 }).run() },
])

const toolbarGroup3 = computed(() => [
    {
        label: 'Bullet List', isActive: () => editor.value?.isActive('bulletList') ?? false,
        action: () => editor.value?.chain().focus().toggleBulletList().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.008v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.008v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.008v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>',
    },
    {
        label: 'Ordered List', isActive: () => editor.value?.isActive('orderedList') ?? false,
        action: () => editor.value?.chain().focus().toggleOrderedList().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 0 1 1.909.13 1.125 1.125 0 0 1 0 1.076A1.125 1.125 0 0 1 3.126 12m-1.5 0h1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125H3.75a1.125 1.125 0 0 1-1.125-1.125v-1.5c0-.621.504-1.125 1.125-1.125Z"/></svg>',
    },
    {
        label: 'Blockquote', isActive: () => editor.value?.isActive('blockquote') ?? false,
        action: () => editor.value?.chain().focus().toggleBlockquote().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>',
    },
    {
        label: 'Code Block', isActive: () => editor.value?.isActive('codeBlock') ?? false,
        action: () => editor.value?.chain().focus().toggleCodeBlock().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6.75 7.5 3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>',
    },
    {
        label: 'Horizontal Rule', isActive: () => false,
        action: () => editor.value?.chain().focus().setHorizontalRule().run(),
        icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>',
    },
])

function setLink() {
    const url = window.prompt('URL Link:')
    if (url) editor.value?.chain().focus().setLink({ href: url }).run()
    else editor.value?.chain().focus().unsetLink().run()
}

// ── Thumbnail ──────────────────────────────────────────────────
function onThumbnailChange(e) {
    const file = e.target.files[0]
    if (!file) return
    form.value.thumbnail = file
    thumbnailPreview.value = URL.createObjectURL(file)
}
function removeThumbnail() {
    form.value.thumbnail = null
    thumbnailPreview.value = null
}
function onDrop(e) {
    const file = e.dataTransfer.files[0]
    if (!file || !file.type.startsWith('image/')) return
    form.value.thumbnail = file
    thumbnailPreview.value = URL.createObjectURL(file)
}

// ── Tags ───────────────────────────────────────────────────────
function addTag() {
    const t = tagInput.value.replace(',', '').trim().toLowerCase()
    if (t && !form.value.tags.includes(t)) form.value.tags.push(t)
    tagInput.value = ''
}
function removeTag(i) { form.value.tags.splice(i, 1) }

// ── Upload image in editor ─────────────────────────────────────
async function uploadEditorImage(e) {
    const file = e.target.files[0]
    if (!file) return
    const fd = new FormData()
    fd.append('image', file)
    try {
        const { data } = await axios.post('/admin/images/upload', fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        editor.value?.chain().focus().setImage({ src: data.url }).run()
    } catch { alert('Gagal upload gambar.') }
}

// ── Validate ───────────────────────────────────────────────────
function validate() {
    errors.value = {}
    if (!form.value.title.trim()) errors.value.title = 'Judul wajib diisi.'
    if (!form.value.body || form.value.body === '<p></p>') errors.value.body = 'Konten wajib diisi.'
    if (form.value.slug && !/^[a-z0-9-]+$/.test(form.value.slug)) errors.value.slug = 'Hanya huruf kecil, angka, dan tanda hubung.'
    return Object.keys(errors.value).length === 0
}

// ── Submit ─────────────────────────────────────────────────────
async function handleSubmit(statusOverride = null) {
    if (!validate()) { window.scrollTo({ top: 0, behavior: 'smooth' }); return }

    saving.value = true
    successMessage.value = ''

    const payload = { ...form.value }
    if (statusOverride) payload.status = statusOverride
    if (isStaff.value)  payload.status = 'draft'

    try {
        if (isEditing.value) {
            await contentStore.update(route.params.id, payload)
            successMessage.value = 'Perubahan berhasil disimpan.'
            window.scrollTo({ top: 0, behavior: 'smooth' })
        } else {
            const created = await contentStore.create(payload)
            router.push(`/admin/articles/${created.id}/edit`)
        }
    } catch (e) {
        if (e.response?.status === 422) Object.assign(errors.value, e.response.data.errors ?? {})
    } finally {
        saving.value = false
    }
}

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

// ── Load ───────────────────────────────────────────────────────
onMounted(async () => {
    document.title = 'New Article - Two Brothers Vape System'
    if (isEditing.value) {
        await contentStore.fetchOne(route.params.id)
        const c = contentStore.currentItem
        if (c) {
            Object.assign(form.value, {
                title: c.title, slug: c.slug, body: c.body,
                excerpt: c.excerpt ?? '', tags: c.tags ?? [],
                status: c.status, meta_title: c.meta_title ?? '',
                meta_description: c.meta_description ?? '',
            })
            thumbnailPreview.value = c.thumbnail ?? null
            slugEdited.value = true
            await nextTick()
            editor.value?.commands.setContent(c.body ?? '')
            autoResizeTitle()
        }
    }
})
</script>

<style>
/* ── Tiptap editor styles ─────────────────────────── */
.tiptap-editor .ProseMirror {
    outline: none;
    min-height: 420px;
    font-size: 0.9375rem;
    line-height: 1.75;
    color: #1f2937;
}
.tiptap-editor .ProseMirror h2 {
    font-size: 1.5rem; font-weight: 700; color: #111827;
    margin: 1.75rem 0 0.75rem; padding-bottom: 0.5rem;
    border-bottom: 2px solid #f3f4f6;
}
.tiptap-editor .ProseMirror h3 {
    font-size: 1.2rem; font-weight: 700; color: #111827;
    margin: 1.5rem 0 0.5rem;
}
.tiptap-editor .ProseMirror h4 {
    font-size: 1rem; font-weight: 700; color: #374151;
    margin: 1.25rem 0 0.5rem;
}
.tiptap-editor .ProseMirror p { margin-bottom: 0.85rem; }
.tiptap-editor .ProseMirror p:last-child { margin-bottom: 0; }
.tiptap-editor .ProseMirror ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 0.85rem; }
.tiptap-editor .ProseMirror ol { list-style: decimal; padding-left: 1.5rem; margin-bottom: 0.85rem; }
.tiptap-editor .ProseMirror li { margin-bottom: 0.3rem; }
.tiptap-editor .ProseMirror blockquote {
    border-left: 3px solid #ED1F24;
    padding: 0.5rem 0 0.5rem 1rem;
    color: #6b7280; font-style: italic;
    margin: 1rem 0; background: rgba(237,31,36,0.03);
    border-radius: 0 8px 8px 0;
}
.tiptap-editor .ProseMirror code {
    background: #f3f4f6; color: #ED1F24;
    padding: 0.15em 0.4em; border-radius: 4px;
    font-size: 0.85em; font-family: monospace;
}
.tiptap-editor .ProseMirror pre {
    background: #1f2937; color: #f9fafb;
    padding: 1rem 1.25rem; border-radius: 12px;
    margin: 1rem 0; overflow-x: auto;
}
.tiptap-editor .ProseMirror pre code { background: none; color: inherit; padding: 0; }
.tiptap-editor .ProseMirror img {
    max-width: 100%; border-radius: 12px; margin: 1rem 0;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}
.tiptap-editor .ProseMirror a { color: #ED1F24; text-decoration: underline; }
.tiptap-editor .ProseMirror hr { border: none; border-top: 2px solid #f3f4f6; margin: 1.5rem 0; }
.tiptap-editor .ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left; color: #d1d5db; pointer-events: none; height: 0;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-8px); }
</style>