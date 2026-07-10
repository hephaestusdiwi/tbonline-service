<template>
    <AdminLayout title="Live Chat">

        <!-- ═══════════════════════════════════════════
             HERO HEADER — sama persis dengan Dashboard
        ═══════════════════════════════════════════ -->
        <div class="relative mb-6 rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"></div>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10" style="background: white;"></div>

            <div class="relative px-7 py-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">Customer Support</p>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Live Chat</h1>
                    <p class="text-red-200 text-xs mt-1.5">Kelola percakapan &amp; respons visitor secara real-time</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Agent toggle -->
                    <button
                        @click="toggleAgentStatus"
                        :class="['flex items-center gap-2 text-xs font-semibold px-3 py-2 rounded-xl border transition-all',
                            agentOnline
                                ? 'border-white/30 bg-white/15 text-white'
                                : 'border-white/20 bg-white/8 text-red-200']"
                    >
                        <span :class="['w-2 h-2 rounded-full', agentOnline ? 'bg-emerald-400 animate-pulse' : 'bg-red-300']"></span>
                        {{ agentOnline ? 'Agent Online' : 'Agent Offline' }}
                    </button>

                    <!-- Pending badge -->
                    <div v-if="pendingCount > 0" class="flex items-center gap-1.5 text-xs font-bold bg-white/20 border border-white/30 text-white px-3 py-2 rounded-xl">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        {{ pendingCount }} antrian
                    </div>
                </div>
            </div>

            <!-- Stats strip — mirip revenue strip di Dashboard -->
            <div class="relative border-t border-white/10 px-7 py-3 flex flex-wrap items-center gap-6">
                <div v-for="strip in heroStrips" :key="strip.label" class="flex items-center gap-2">
                    <div class="text-right">
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">{{ strip.label }}</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ strip.value }}</p>
                    </div>
                    <div class="w-px h-8 bg-white/15 last:hidden"></div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════
             MAIN CHAT AREA — 3-column layout
        ═══════════════════════════════════════════ -->
        <div class="flex gap-4 overflow-hidden" style="height: calc(100vh - 280px)">

            <!-- ── SIDEBAR KIRI ── -->
            <div class="w-72 shrink-0 flex flex-col bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">

                <!-- Search -->
                <div class="px-4 pt-4 pb-3 border-b border-gray-100">
                    <div class="relative mb-3">
                        <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama / pesan..."
                            class="w-full pl-8 pr-3 py-2 text-xs border border-gray-200 rounded-xl focus:outline-none focus:border-[#ED1F24] transition-colors bg-gray-50/50"
                        />
                    </div>

                    <!-- Tab filter -->
                    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl">
                        <button
                            v-for="tab in tabs"
                            :key="tab.value"
                            @click="activeTab = tab.value"
                            :class="['flex-1 text-[10px] py-1.5 rounded-lg font-bold transition-all',
                                activeTab === tab.value
                                    ? 'bg-white text-gray-800 shadow-sm'
                                    : 'text-gray-400 hover:text-gray-600']"
                        >{{ tab.label }}</button>
                    </div>
                </div>

                <!-- Session list -->
                <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
                    <div v-if="loadingSessions" class="p-4 space-y-3">
                        <div v-for="i in 5" :key="i" class="flex gap-3 animate-pulse">
                            <div class="w-9 h-9 rounded-full bg-gray-100 shrink-0"></div>
                            <div class="flex-1 space-y-1.5">
                                <div class="h-3 bg-gray-100 rounded w-3/4"></div>
                                <div class="h-2.5 bg-gray-100 rounded w-full"></div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-for="session in filteredSessions"
                        :key="session.uuid"
                        @click="selectSession(session)"
                        :class="['px-4 py-3 cursor-pointer hover:bg-gray-50/80 transition-colors flex gap-3 items-start group',
                            activeSession?.uuid === session.uuid
                                ? 'bg-red-50/60 border-l-2 border-[#ED1F24]'
                                : 'border-l-2 border-transparent']"
                    >
                        <!-- Avatar dengan initials -->
                        <div class="relative shrink-0">
                            <div class="w-9 h-9 rounded-xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center text-[#ED1F24] text-sm font-bold"
                                :class="activeSession?.uuid === session.uuid ? 'bg-[#ED1F24] text-white border-[#ED1F24]' : ''"
                            >
                                {{ (session.guest_name || session.customer_name || '?').charAt(0).toUpperCase() }}
                            </div>
                            <span v-if="session.visitor_left && session.status !== 'closed'"
                                class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-amber-400 border-2 border-white" />
                            <span v-else-if="session.status === 'active'"
                                class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-white" />
                            <span v-else-if="session.status === 'queued'"
                                class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-amber-400 border-2 border-white" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <p class="text-xs font-bold text-gray-800 truncate">
                                    {{ session.guest_name || session.customer_name || 'Guest' }}
                                </p>
                                <span class="text-[10px] text-gray-400 shrink-0">{{ fmtTime(session.last_message_at || session.created_at) }}</span>
                            </div>
                            <p class="text-xs text-gray-500 truncate mt-0.5">{{ session.last_message || 'Belum ada pesan' }}</p>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span :class="statusBadgeClass(session.status)">{{ statusLabel(session.status) }}</span>
                                <span v-if="session.visitor_left && session.status !== 'closed'"
                                    class="text-[10px] font-semibold bg-amber-50 text-amber-600 rounded-full px-1.5 py-0.5 border border-amber-100">
                                    Pergi
                                </span>
                                <span v-if="session.unread_count > 0"
                                    class="text-[10px] font-bold bg-[#ED1F24] text-white rounded-full px-1.5 py-0.5">
                                    {{ session.unread_count }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="!loadingSessions && filteredSessions.length === 0"
                        class="flex flex-col items-center justify-center py-16 text-gray-400 gap-2">
                        <div class="w-10 h-10 rounded-2xl bg-gray-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium">Tidak ada sesi {{ activeTab !== 'all' ? activeTabLabel : '' }}</p>
                    </div>
                </div>
            </div>

            <!-- ── AREA TENGAH — Chat Window ── -->
            <div class="flex-1 flex flex-col min-w-0 bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">

                <!-- Empty state -->
                <div v-if="!activeSession" class="flex-1 flex flex-col items-center justify-center text-gray-400 gap-4">
                    <div class="w-20 h-20 rounded-3xl bg-gray-100/80 border border-gray-200/80 flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-semibold text-gray-600">Pilih sesi untuk mulai chat</p>
                        <p class="text-xs text-gray-400 mt-1">{{ sessions.length }} sesi tersedia</p>
                    </div>
                </div>

                <template v-else>
                    <!-- Header chat aktif — mirip card header di Dashboard -->
                    <div class="bg-white border-b border-gray-100 px-5 py-3.5 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center text-[#ED1F24] text-sm font-bold shrink-0">
                                {{ (activeSession.guest_name || activeSession.customer_name || '?').charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ activeSession.guest_name || activeSession.customer_name || 'Guest' }}</p>
                                <div class="flex items-center gap-2 text-xs text-gray-400 mt-0.5">
                                    <span :class="statusBadgeClass(activeSession.status)">{{ statusLabel(activeSession.status) }}</span>
                                    <span v-if="activeSession.guest_phone">· {{ activeSession.guest_phone }}</span>
                                    <span v-if="customerTyping" class="text-emerald-500 font-medium animate-pulse">· mengetik...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <button
                                v-if="canManage && activeSession.status === 'queued'"
                                @click="assignToMyself"
                                class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl border border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-all"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Ambil Chat
                            </button>

                            <button
                                v-if="!isSessionEnded(activeSession.status)"
                                @click="closeSession"
                                class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl border border-gray-200 text-gray-600 hover:border-red-200 hover:text-[#ED1F24] hover:bg-red-50/50 transition-all"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Tutup Sesi
                            </button>

                            <button
                                v-if="isSessionEnded(activeSession.status)"
                                @click="reopenSession"
                                class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl border border-emerald-200 text-emerald-600 hover:bg-emerald-50 transition-all"
                            >
                                Buka Kembali
                            </button>

                            <button
                                v-if="isAdmin && isSessionEnded(activeSession.status)"
                                @click="deleteSession"
                                class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl border border-red-200 text-red-500 hover:bg-red-50 transition-all"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus
                            </button>

                            <!-- Detail panel toggle -->
                            <button
                                @click="showDetailPanel = !showDetailPanel"
                                :class="['p-2 rounded-xl border transition-all',
                                    showDetailPanel
                                        ? 'border-[#ED1F24]/30 text-[#ED1F24] bg-red-50/50'
                                        : 'border-gray-200 text-gray-400 hover:border-gray-300 hover:bg-gray-50']"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Pesan area -->
                    <div ref="msgContainer" class="flex-1 overflow-y-auto px-5 py-5 space-y-1 bg-gray-50/40">

                        <div v-if="loadingMessages" class="space-y-4">
                            <div v-for="i in 4" :key="i" :class="['flex gap-3', i % 2 === 0 ? 'flex-row-reverse' : '']">
                                <div class="w-8 h-8 rounded-xl bg-gray-200 animate-pulse shrink-0"></div>
                                <div :class="['h-10 rounded-2xl animate-pulse bg-gray-200', i % 2 === 0 ? 'w-48' : 'w-56']"></div>
                            </div>
                        </div>

                        <template v-else>
                            <template v-for="msg in messages" :key="msg.id">
                                <div v-if="msg && msg.id">

                                    <!-- System message -->
                                    <div v-if="msg.sender_type === 'system'" class="flex justify-center my-3">
                                        <span class="text-[10px] text-gray-400 bg-white border border-gray-100 px-3 py-1 rounded-full shadow-sm">
                                            {{ msg.content }}
                                        </span>
                                    </div>

                                    <!-- Chat bubble -->
                                    <div
                                        v-else
                                        class="flex gap-2.5 mb-2.5"
                                        :class="isOwnMessage(msg) ? 'flex-row-reverse' : 'flex-row'"
                                    >
                                        <div :class="['w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold shrink-0 mt-auto',
                                            isOwnMessage(msg)
                                                ? 'bg-[#ED1F24] text-white'
                                                : msg.sender_type === 'bot'
                                                    ? 'bg-purple-500 text-white'
                                                    : 'bg-gray-200 text-gray-600']">
                                            {{ avatarInitial(msg) }}
                                        </div>

                                        <div class="max-w-[65%] flex flex-col" :class="isOwnMessage(msg) ? 'items-end' : 'items-start'">
                                            <p class="text-[10px] text-gray-400 mb-1 px-1">
                                                {{ isOwnMessage(msg) ? 'Anda' : senderLabel(msg) }}
                                            </p>
                                            <div :class="[
                                                'px-4 py-2.5 text-sm leading-relaxed whitespace-pre-wrap break-words',
                                                isOwnMessage(msg)
                                                    ? 'bg-[#ED1F24] text-white rounded-2xl rounded-br-sm shadow-sm'
                                                    : msg.sender_type === 'bot'
                                                        ? 'bg-purple-50 text-purple-900 border border-purple-100 rounded-2xl rounded-bl-sm'
                                                        : 'bg-white text-gray-800 rounded-2xl rounded-bl-sm border border-gray-100 shadow-sm'
                                            ]">
                                                {{ msg.content }}

                                                <div v-if="msg.attachments?.length" class="mt-2">
                                                    <template v-for="att in msg.attachments" :key="att.id">
                                                        <img
                                                            v-if="att.mime_type?.startsWith('image/')"
                                                            :src="att.url"
                                                            class="rounded-xl max-w-full cursor-pointer mt-1"
                                                            style="max-height:200px; object-fit:cover;"
                                                            @click="openFile(att.url)"
                                                        />
                                                        <a v-else :href="att.url" target="_blank"
                                                            class="flex items-center gap-2 text-xs underline mt-1 opacity-80">
                                                            📄 {{ att.original_name }}
                                                        </a>
                                                    </template>
                                                </div>
                                            </div>
                                            <p class="text-[10px] text-gray-400 mt-1 px-1">
                                                {{ fmtTime(msg.sent_at || msg.created_at) }}
                                                <span v-if="isOwnMessage(msg) && msg.is_read" class="ml-1 text-emerald-500">✓✓</span>
                                                <span v-if="isOwnMessage(msg) && messageResponseTimes[msg.id] !== undefined" class="ml-1 text-gray-400">
                                                    · dibalas {{ fmtDuration(messageResponseTimes[msg.id]) }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </template>

                            <!-- Typing indicator -->
                            <div v-if="customerTyping" class="flex gap-2.5 items-end">
                                <div class="w-8 h-8 rounded-xl bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">C</div>
                                <div class="bg-white border border-gray-100 rounded-2xl rounded-bl-sm px-4 py-3 flex gap-1 items-center shadow-sm">
                                    <span v-for="i in 3" :key="i" class="w-1.5 h-1.5 rounded-full bg-gray-400 animate-bounce"
                                        :style="{ animationDelay: (i-1)*0.15 + 's' }"></span>
                                </div>
                            </div>
                        </template>

                        <div ref="msgBottom"></div>
                    </div>

                    <!-- Quick replies -->
                    <div v-if="!isSessionEnded(activeSession.status)" class="px-5 pt-2 pb-1 flex gap-2 flex-wrap shrink-0 bg-white border-t border-gray-50">
                        <button
                            v-for="qr in quickReplies"
                            :key="qr"
                            @click="newMessage = qr"
                            class="text-xs px-2.5 py-1 rounded-lg border border-gray-200 text-gray-500 hover:border-[#ED1F24]/40 hover:text-[#ED1F24] hover:bg-red-50/40 transition-all"
                        >{{ qr }}</button>
                    </div>

                    <!-- Input area -->
                    <div v-if="!isSessionEnded(activeSession.status) && activeSession.can_reply"
                        class="bg-white border-t border-gray-100 px-5 py-3 flex items-end gap-3 shrink-0">
                        <label class="text-gray-400 hover:text-[#ED1F24] cursor-pointer transition-colors shrink-0 mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <input type="file" class="hidden" @change="uploadAttachment" accept="image/*,.pdf,.doc,.docx" />
                        </label>

                        <textarea
                            v-model="newMessage"
                            @keydown.enter.exact.prevent="sendMessage"
                            @input="onAgentTyping"
                            rows="1"
                            placeholder="Tulis balasan... (Enter untuk kirim)"
                            class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:border-[#ED1F24] transition-colors max-h-32 overflow-y-auto bg-gray-50/50"
                            style="line-height:1.5"
                        ></textarea>

                        <button
                            @click="sendMessage"
                            :disabled="!newMessage.trim() || sending"
                            class="bg-[#ED1F24] hover:bg-[#C81A1E] text-white p-2.5 rounded-xl disabled:opacity-40 transition-colors shrink-0 shadow-sm"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </div>

                    <!-- Can't reply state -->
                    <div v-else-if="!isSessionEnded(activeSession.status) && !activeSession.can_reply"
                        class="bg-amber-50 border-t border-amber-100 px-5 py-4 text-center text-xs text-amber-600 shrink-0">
                        Klik <strong>"Ambil Chat"</strong> di atas untuk mulai membalas visitor ini
                    </div>

                    <!-- Sesi ditutup -->
                    <div v-else class="bg-gray-50 border-t border-gray-100 px-5 py-4 text-center text-xs text-gray-500 shrink-0">
                        Sesi ini sudah {{ activeSession.status === 'resolved' ? 'diselesaikan' : 'ditutup' }} ·
                        <button @click="reopenSession" class="font-semibold text-[#ED1F24] hover:underline ml-1">Buka kembali</button>
                    </div>
                </template>
            </div>

            <!-- ── PANEL KANAN — Detail — sama style dengan card Dashboard ── -->
            <transition name="slide-panel">
                <div v-if="showDetailPanel && activeSession"
                    class="w-60 shrink-0 flex flex-col bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden overflow-y-auto">

                    <!-- Profile header -->
                    <div class="p-5 border-b border-gray-100 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center text-[#ED1F24] text-xl font-bold mx-auto mb-3">
                            {{ (activeSession.guest_name || 'G').charAt(0).toUpperCase() }}
                        </div>
                        <p class="text-sm font-bold text-gray-800">{{ activeSession.guest_name || 'Guest' }}</p>
                        <p v-if="activeSession.guest_phone" class="text-xs text-gray-400 mt-0.5">{{ activeSession.guest_phone }}</p>
                        <span :class="['inline-block mt-2', statusBadgeClass(activeSession.status)]">{{ statusLabel(activeSession.status) }}</span>
                    </div>

                    <!-- Detail rows — mirip style info rows di Dashboard -->
                    <div class="p-4 border-b border-gray-100 space-y-2.5">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">Info Sesi</p>
                        <div v-if="activeSession.close_reason" class="flex justify-between text-xs">
                            <span class="text-gray-400">Alasan Tutup</span>
                            <span class="font-semibold text-red-500 text-right max-w-[60%] truncate">{{ activeSession.close_reason }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400">Channel</span>
                            <span class="font-semibold text-gray-700 capitalize">{{ activeSession.channel || 'web' }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400">Dibuka</span>
                            <span class="font-semibold text-gray-700">{{ fmtDate(activeSession.created_at) }}</span>
                        </div>
                        <div v-if="activeSession.last_seen_at" class="flex justify-between text-xs">
                            <span class="text-gray-400">Terakhir Online</span>
                            <span class="font-semibold text-gray-700">{{ fmtTime(activeSession.last_seen_at) }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400">Rating</span>
                            <span v-if="activeSession.rating" class="font-semibold text-amber-500">
                                {{ '⭐'.repeat(activeSession.rating) }} ({{ activeSession.rating }}/5)
                            </span>
                            <span v-else class="text-gray-400 italic">Belum ada rating</span>
                        </div>
                    </div>

                    <!-- Agent info -->
                    <div class="p-4 border-b border-gray-100">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">Agent</p>
                        <div v-if="activeSession.assigned_agent_name" class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold shrink-0">
                                {{ activeSession.assigned_agent_name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-700">{{ activeSession.assigned_agent_name }}</p>
                                <p class="text-[10px] text-emerald-500 font-semibold">● Online</p>
                            </div>
                        </div>
                        <p v-else class="text-xs text-gray-400 italic">Belum ada agent</p>
                    </div>

                    <div class="p-4 border-b border-gray-100">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">Performa Respons</p>
                        <div class="space-y-2.5">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-400">Waktu Ambil Chat</span>
                                <span class="font-semibold text-gray-700">{{ fmtDuration(sessionKpi?.time_to_assign_seconds) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-400">Respons Pertama</span>
                                <span class="font-semibold text-gray-700">{{ fmtDuration(sessionKpi?.first_response_seconds) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-400">Rata-rata Respons</span>
                                <span class="font-semibold text-gray-700">{{ fmtDuration(sessionKpi?.avg_response_seconds) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Session logs -->
                    <div class="p-4">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">Riwayat Sesi</p>
                        <div class="space-y-2.5">
                            <div v-for="log in sessionLogs" :key="log.id" class="flex gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-[#ED1F24]/40 shrink-0 mt-1.5"></div>
                                <div>
                                    <p class="text-xs text-gray-600">{{ log.description }}</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ fmtTime(log.created_at) }}</p>
                                </div>
                            </div>
                            <p v-if="!sessionLogs.length" class="text-xs text-gray-400 italic">Belum ada riwayat</p>
                        </div>
                    </div>
                </div>
            </transition>
        </div>

    </AdminLayout>
</template>

<script>
import AdminLayout from '../../components/admin/AdminLayout.vue'
import axios from '@/axios.js'
import { mapState, mapActions } from 'pinia'
import { useAgentPresenceStore } from '@/store/agentPresence.js'
import { getUser } from '@/auth.js'

export default {
    name: 'AdminChat',
    components: { AdminLayout },

    data() {
        return {
            sessions:        [],
            loadingSessions: true,
            search:          '',
            lightboxUrl:     null,
            activeTab:       'all',
            titleInterval:   null,
            unreadTabCount:  0,
            sessionKpi: null,

            // Tab sekarang berdasarkan KATEGORI handoff.
            // Status sesi (Antrian/Active/Closed/dll) tetap tampil sebagai badge di tiap baris sesi.
            tabs: [
                { label: 'Semua',     value: 'all'       },
                { label: 'Pembelian', value: 'purchase'  },
                { label: 'Komplain',  value: 'complaint' },
                { label: 'Chat CS',   value: 'cs'        },
            ],

            activeSession:   null,
            messages:        [],
            loadingMessages: false,
            newMessage:      '',
            sending:         false,
            showDetailPanel: false,
            sessionLogs:     [],
            pendingCount: 0,

            customerTyping: false,
            typingTimer:    null,
            sendingTyping:  false,

            echoChannels: {},

            quickReplies: [
                'Halo! Ada yang bisa saya bantu? 😊',
                'Mohon tunggu sebentar...',
                'Terima kasih sudah menghubungi kami!',
                'Maaf atas ketidaknyamanannya.',
                'Pesanan Anda sedang diproses.',
            ],
        }
    },

    computed: {
        canManage() {
            try {
                return ['admin', 'manager', 'staff'].includes(getUser()?.role)
            } catch { return false }
        },

        isAdmin() {
            try {
                return getUser()?.role === 'admin'
            } catch { return false }
        },

        filteredSessions() {
            let list = this.sessions
            if (this.activeTab !== 'all') list = list.filter(s => (s.inquiry_type || 'cs') === this.activeTab)
            if (this.search.trim()) {
                const q = this.search.toLowerCase()
                list = list.filter(s =>
                    (s.guest_name || '').toLowerCase().includes(q) ||
                    (s.last_message || '').toLowerCase().includes(q)
                )
            }
            return list
        },

        activeTabLabel() {
            return this.tabs.find(t => t.value === this.activeTab)?.label || ''
        },

        heroStrips() {
            const all    = this.sessions.length
            const active = this.sessions.filter(s => s.status === 'active').length
            const queued = this.sessions.filter(s => s.status === 'queued').length
            const closed = this.sessions.filter(s => ['closed', 'resolved'].includes(s.status)).length
            return [
                { label: 'Total Sesi',  value: all    },
                { label: 'Active',      value: active },
                { label: 'Antrian',     value: queued },
                { label: 'Selesai',     value: closed },
            ]
        },

        messageResponseTimes() {
            const map = {}
            let pendingCustomerAt = null

            for (const msg of this.messages) {
                if (!msg || !msg.id) continue
                if (msg.sender_type === 'customer') {
                    pendingCustomerAt = msg.sent_at || msg.created_at
                } else if (msg.sender_type === 'agent' && pendingCustomerAt) {
                    map[msg.id] = Math.round(
                        (new Date(msg.sent_at || msg.created_at) - new Date(pendingCustomerAt)) / 1000
                    )
                    pendingCustomerAt = null
                }
            }
            return map
        },

        ...mapState(useAgentPresenceStore, { agentOnline: 'online' }),
    },

    methods: {
        isSessionEnded(status) {
            return status === 'closed' || status === 'resolved'
        },

        async fetchSessions() {
            this.loadingSessions = true
            try {
                const { data } = await axios.get('/chat/sessions')
                this.sessions = data.data || data
                this.pendingCount = this.sessions.filter(s => s.status === 'queued').length 
                    .filter(s => ['queued', 'active'].includes(s.status))
                    .forEach(s => this.subscribeSessionChannel(s.uuid))
            } catch (e) {
                console.error('Gagal load sessions:', e)
            } finally {
                this.loadingSessions = false
            }
        },

        async fetchPendingCount() {
             // tidak perlu fetch, hitung dari sessions yang sudah ada
        },

        async fetchSessionKpi(uuid) {
            this.sessionKpi = null
            try {
                const { data } = await axios.get(`/chat/sessions/${uuid}/kpi`)
                this.sessionKpi = data.data
            } catch {}
        },

        fmtDuration(seconds) {
            if (seconds === null || seconds === undefined) return '-'
            if (seconds < 60) return `${seconds}d`
            const m = Math.floor(seconds / 60), s = seconds % 60
            if (m < 60) return `${m}m ${s}d`
            const h = Math.floor(m / 60)
            return `${h}j ${m % 60}m`
        },

        openFile(url, mimeType) {
            if (mimeType?.startsWith('image/')) this.lightboxUrl = url
            else window.open(url, '_blank')
        },

        playNotifSound() {
            try {
                const audio = new Audio('/sounds/notification.mp3')
                audio.volume = 0.5
                audio.play().catch(() => {})
            } catch {}
        },

        startTitleBlink() {
            this.unreadTabCount++
            clearInterval(this.titleInterval)
            const original = 'Live Chat — Admin'
            let show = true
            this.titleInterval = setInterval(() => {
                document.title = show ? `(${this.unreadTabCount}) 💬 Pesan Baru!` : original
                show = !show
            }, 1000)
        },

        stopTitleBlink() {
            clearInterval(this.titleInterval)
            document.title = 'Live Chat — Admin'
            this.unreadTabCount = 0
        },

        async selectSession(session) {
            this.stopTitleBlink()
            if (this.activeSession?.uuid === session.uuid) return
            this.activeSession   = session
            this.messages        = []
            this.sessionLogs     = []
            this.showDetailPanel = false
            this.loadingMessages = true

            try {
                const { data } = await axios.get(`/chat/sessions/${session.uuid}/messages`)
                this.messages = (data.data || data || []).filter(m => m && m.id)

                if (session.close_reason && this.isSessionEnded(session.status)) {
                    const alreadyHas = this.messages.some(m => m.sender_type === 'system' && m.content?.includes('ditutup'))
                    if (!alreadyHas) {
                        this.messages.push({
                            id: `sys-close-${session.uuid}`,
                            sender_type: 'system',
                            content: `Sesi ditutup — ${session.close_reason}`,
                            sent_at: session.closed_at || new Date().toISOString(),
                        })
                    }
                }

                if (session.visitor_left && !this.isSessionEnded(session.status)) {
                    const guestName = session.guest_name || session.customer_name || 'Visitor'
                    this.messages.push({
                        id: `sys-left-${session.uuid}`,
                        sender_type: 'system',
                        content: `${guestName} telah meninggalkan obrolan`,
                        sent_at: session.updated_at || new Date().toISOString(),
                    })
                }

                axios.post(`/chat/sessions/${session.uuid}/messages/read`).catch(() => {})
                const idx = this.sessions.findIndex(s => s.uuid === session.uuid)
                if (idx > -1) this.sessions[idx].unread_count = 0

                this.subscribeSessionChannel(session.uuid)
                this.fetchSessionKpi(session.uuid)
                this.$nextTick(() => this.scrollToBottom())
            } catch (e) {
                console.error('Gagal load messages:', e)
            } finally {
                this.loadingMessages = false
            }
        },

        async deleteSession() {
            if (!confirm('Yakin ingin menghapus sesi ini?')) return
            try {
                await axios.delete(`/chat/sessions/${this.activeSession.uuid}`)
                this.sessions = this.sessions.filter(s => s.uuid !== this.activeSession.uuid)
                this.activeSession = null
            } catch { alert('Gagal menghapus sesi.') }
        },

        subscribeSessionChannel(uuid) {
            if (!window.Echo) return
            Object.keys(this.echoChannels).forEach(key => {
                if (key !== uuid) { window.Echo.leave(`chat.session.${key}`); delete this.echoChannels[key] }
            })
            if (this.echoChannels[uuid]) return

            this.echoChannels[uuid] = window.Echo.channel(`chat.session.${uuid}`)
                .listen('.message.sent', (e) => {
                    if (e.message?.id && !this.messages.find(m => m.id === e.message.id)) {
                        this.messages.push(e.message)
                        this.$nextTick(() => this.scrollToBottom())
                        const idx = this.sessions.findIndex(s => s.uuid === uuid)
                        if (idx > -1) {
                            this.sessions[idx].last_message    = e.message.content
                            this.sessions[idx].last_message_at = e.message.sent_at
                        }
                        if (this.activeSession?.uuid === uuid) this.fetchSessionKpi(uuid)
                    }
                })
                .listen('.typing.started', (e) => {
                    if (e.sender_type === 'customer') {
                        this.customerTyping = true
                        clearTimeout(this.typingTimer)
                        this.typingTimer = setTimeout(() => { this.customerTyping = false }, 3000)
                    }
                })
                .listen('.typing.stopped', (e) => {
                    if (e.sender_type === 'customer') this.customerTyping = false
                })
                .listen('.session.closed', (e) => {
                    if (this.activeSession?.uuid === uuid)
                        this.activeSession = { ...this.activeSession, status: 'closed' }
                    if (e?.reason) {
                        this.messages.push({
                            id: `sys-close-${Date.now()}`,
                            sender_type: 'system',
                            content: `Sesi ditutup — ${e.reason}`,
                            sent_at: new Date().toISOString(),
                        })
                        this.$nextTick(() => this.scrollToBottom())
                    }
                    const idx = this.sessions.findIndex(s => s.uuid === uuid)
                    if (idx > -1) this.sessions[idx].status = 'closed'
                })
                .listen('.session.assigned', (e) => {
                    if (this.activeSession?.uuid === uuid)
                        this.activeSession = { ...this.activeSession, status: 'active', assigned_agent_name: e.agent_name || e.agent?.name }
                    const idx = this.sessions.findIndex(s => s.uuid === uuid)
                    if (idx > -1) { this.sessions[idx].status = 'active'; this.sessions[idx].assigned_agent_name = e.agent_name || e.agent?.name }
                })
                .listen('.visitor.left', (e) => {
                    const guestName = e.guest_name || this.activeSession?.guest_name || 'Visitor'
                    if (this.activeSession?.uuid === uuid) {
                        this.activeSession = { ...this.activeSession, visitor_left: true }
                        this.messages.push({
                            id: `sys-left-${Date.now()}`,
                            sender_type: 'system',
                            content: `${guestName} telah meninggalkan obrolan`,
                            sent_at: new Date().toISOString(),
                        })
                        this.$nextTick(() => this.scrollToBottom())
                    }
                    const idx = this.sessions.findIndex(s => s.uuid === uuid)
                    if (idx > -1) this.sessions[idx].visitor_left = true
                    this.playNotifSound()
                    this.startTitleBlink()
                    if (Notification.permission === 'granted' && !document.hasFocus()) {
                        const notif = new Notification(`👋 ${e.guest_name}`, { body: 'Visitor telah meninggalkan obrolan', icon: '/favicon.ico' })
                        setTimeout(() => notif.close(), 5000)
                    }
                })
        },

        subscribeAdminChannel() {
            if (!window.Echo) return
            window.Echo.channel('queue.admin')
                .listen('.customer.queued', (e) => {
                    if (!this.sessions.find(s => s.uuid === e.session?.uuid)) {
                        this.sessions.unshift(e.session)
                        this.pendingCount++
                    }
                })
        },

        async sendMessage() {
            if (!this.newMessage.trim() || this.sending || !this.activeSession) return
            const body = this.newMessage.trim()
            this.newMessage = ''
            this.sending    = true
            try {
                const { data } = await axios.post(`/chat/sessions/${this.activeSession.uuid}/messages`, { content: body })
                if (data.data && !this.messages.find(m => m.id === data.data.id)) {
                    this.messages.push(data.data)
                    this.$nextTick(() => this.scrollToBottom())
                    this.fetchSessionKpi(this.activeSession.uuid)  
                }
            } catch {
                this.newMessage = body
                alert('Gagal mengirim pesan')
            } finally {
                this.sending = false
            }
        },

        onAgentTyping() {
            if (this.sendingTyping || !this.activeSession) return
            this.sendingTyping = true
            axios.post(`/chat/sessions/${this.activeSession.uuid}/typing`).catch(() => {})
            setTimeout(() => { this.sendingTyping = false }, 2000)
        },

        async uploadAttachment(e) {
            const file = e.target.files[0]
            if (!file || !this.activeSession) return
            const form = new FormData()
            form.append('file', file)
            try {
                await axios.post(`/chat/sessions/${this.activeSession.uuid}/attachments`, form, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } catch { alert('Gagal upload lampiran') }
            e.target.value = ''
        },

        async closeSession() {
            const reason = prompt('Alasan menutup sesi (wajib):')
            if (!reason?.trim()) return
            try {
                await axios.patch(`/chat/sessions/${this.activeSession.uuid}/close`, { reason: reason.trim() })
                this.activeSession = { ...this.activeSession, status: 'closed', close_reason: reason.trim() }
                const idx = this.sessions.findIndex(s => s.uuid === this.activeSession.uuid)
                if (idx > -1) {
                    this.sessions[idx].status = 'closed'
                    this.sessions[idx].close_reason = reason.trim()
                }
            } catch { alert('Gagal menutup sesi') }
        },

        async reopenSession() {
            try {
                await axios.patch(`/chat/sessions/${this.activeSession.uuid}/reopen`)
                this.activeSession = { ...this.activeSession, status: 'queued' }
                const idx = this.sessions.findIndex(s => s.uuid === this.activeSession.uuid)
                if (idx > -1) this.sessions[idx].status = 'queued'
            } catch { alert('Gagal membuka sesi') }
        },

        async assignToMyself() {
            try {
                await axios.patch(`/chat/sessions/${this.activeSession.uuid}/take`)
                const user = getUser() ?? {}   // ← ganti dari localStorage
                this.activeSession = { ...this.activeSession, status: 'active', assigned_agent_name: user.name, is_mine: true, can_reply: true }
                const idx = this.sessions.findIndex(s => s.uuid === this.activeSession.uuid)
                if (idx > -1) { this.sessions[idx].assigned_agent_name = user.name; this.sessions[idx].status = 'active'; this.sessions[idx].is_mine = true }
            } catch (e) {
                if (e.response?.status === 409) alert('Session sudah diambil agent lain.')
                else alert('Gagal mengambil chat')
            }
        },

        async toggleAgentStatus() {
            const endpoint = this.agentOnline ? '/agent/status/offline' : '/agent/status/online'
            try {
                await axios.post(endpoint)
                this.agentOnline = !this.agentOnline
            } catch {}
        },

        isOwnMessage(msg)  { return msg.sender_type === 'agent' },
        avatarInitial(msg) {
            if (msg.sender_type === 'bot') return '🤖'
            return (msg.sender_name || '').charAt(0).toUpperCase() || (msg.sender_type === 'agent' ? 'A' : 'C')
        },
        senderLabel(msg) {
            if (msg.sender_type === 'bot') return 'Two Brothers Bot'
            return msg.sender_name || 'Customer'
        },

        statusLabel(status) {
            return { bot: 'Bot', queued: 'Antrian', active: 'Active', closed: 'Closed', resolved: 'Resolved' }[status] || status
        },

        statusBadgeClass(status) {
            return {
                bot:      'text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-500 border border-blue-100',
                queued:   'text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 border border-amber-100',
                active:   'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100',
                closed:   'text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-400 border border-gray-200',
                resolved: 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-50 text-purple-500 border border-purple-100',
            }[status] || 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-400 border border-gray-200'
        },

        scrollToBottom() {
            const container = this.$refs.msgContainer
            if (container) {
                container.scrollTop = container.scrollHeight
            }
        },

        fmtTime(iso) {
            if (!iso) return ''
            const d = new Date(iso), now = new Date()
            const time = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
            if (d.toDateString() === now.toDateString()) return time
            return `${d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })}, ${time}`
        },

        fmtDate(iso) {
            if (!iso) return '-'
            return new Date(iso).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        },

        startPolling() {
            this._pollInterval = setInterval(() => this.fetchSessions(), 30000)
        },
    },

    async mounted() {
        document.title = 'Live Chat - Two Brothers Vape System'

        await this.fetchSessions()
        this.fetchPendingCount()
        this.subscribeAdminChannel()
        this.startPolling()

        if ('Notification' in window && Notification.permission === 'default')
            await Notification.requestPermission()
    },

    beforeUnmount() {
        clearInterval(this._pollInterval)
        clearTimeout(this.typingTimer)
        Object.keys(this.echoChannels).forEach(uuid => window.Echo?.leave(`chat.session.${uuid}`))
        window.Echo?.leave('queue.admin')
    },
}
</script>

<style scoped>
.slide-panel-enter-active { transition: all .2s ease-out; }
.slide-panel-leave-active { transition: all .15s ease-in; }
.slide-panel-enter-from,
.slide-panel-leave-to     { opacity: 0; transform: translateX(16px); }
</style>