<template>
    <!-- ── Floating Button ─────────────────────────────────── -->
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3">

        <!-- Unread badge + pulse -->
        <transition name="bounce">
            <div
                v-if="!isOpen && unreadCount > 0"
                class="bg-[#ED1F24] text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center absolute -top-1.5 -left-1.5 z-10 shadow"
            >{{ unreadCount > 9 ? '9+' : unreadCount }}</div>
        </transition>

        <!-- Chat Window -->
        <transition name="slide-up">
            <div
                v-if="isOpen"
                class="w-[360px] max-w-[calc(100vw-2rem)] h-[520px] bg-white rounded-2xl shadow-2xl border border-gray-100 flex flex-col overflow-hidden mb-2"
                style="box-shadow: 0 20px 60px rgba(0,0,0,0.15);"
            >
                <!-- Header -->
                <div class="bg-[#ED1F24] px-4 py-3 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white leading-tight">Customer Service</p>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                                <p class="text-xs text-white/80">Online · Siap membantu</p>
                            </div>
                        </div>
                    </div>
                    <button @click="isOpen = false" class="text-white/70 hover:text-white transition-colors p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form: belum ada sesi -->
                <div v-if="!sessionUuid && !sessionLoading" class="flex-1 flex flex-col justify-center px-6 py-8 gap-5">
                    <div class="text-center">
                        <div class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-800">Halo! Ada yang bisa kami bantu?</h3>
                        <p class="text-xs text-gray-400 mt-1">Isi data di bawah untuk memulai chat</p>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 mb-1 block">Nama Anda</label>
                            <input
                                v-model="guestForm.name"
                                type="text"
                                placeholder="Masukkan nama Anda"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#ED1F24] transition-colors"
                            />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 mb-1 block">Nomor WhatsApp <span class="text-gray-300 font-normal">(opsional)</span></label>
                            <input
                                v-model="guestForm.phone"
                                type="tel"
                                placeholder="08xxxxxxxxxx"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#ED1F24] transition-colors"
                            />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 mb-1 block">Pesan awal</label>
                            <textarea
                                v-model="guestForm.first_message"
                                rows="2"
                                placeholder="Tulis pertanyaan Anda..."
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#ED1F24] transition-colors resize-none"
                            ></textarea>
                        </div>
                    </div>

                    <button
                        @click="startSession"
                        :disabled="!guestForm.name.trim() || !guestForm.first_message.trim() || sessionLoading"
                        class="w-full bg-[#ED1F24] hover:bg-[#C81A1E] text-white text-sm font-bold py-3 rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                    >
                        Mulai Chat
                    </button>
                </div>

                <!-- Loading sesi -->
                <div v-if="sessionLoading" class="flex-1 flex flex-col items-center justify-center gap-3 text-gray-400">
                    <svg class="w-8 h-8 animate-spin text-[#ED1F24]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <p class="text-sm">Menghubungkan...</p>
                </div>

                <!-- Chat area: sudah ada sesi -->
                <template v-if="sessionUuid && !sessionLoading">
                    <!-- Status bar -->
                    <div v-if="sessionClosed" class="bg-gray-50 border-b border-gray-100 px-4 py-2 text-center">
                        <p class="text-xs text-gray-400 font-medium">Sesi ini telah ditutup · <button @click="resetSession" class="text-[#ED1F24] font-semibold hover:underline">Chat baru</button></p>
                    </div>
                    <div v-else-if="agentName" class="bg-emerald-50 border-b border-emerald-100 px-4 py-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <p class="text-xs text-emerald-700 font-medium">Terhubung dengan <strong>{{ agentName }}</strong></p>
                    </div>
                    <div v-else class="bg-amber-50 border-b border-amber-100 px-4 py-2 text-center">
                        <p class="text-xs text-amber-600 font-medium">⏳ Menunggu agent tersedia...</p>
                    </div>

                    <!-- Messages -->
                    <div ref="msgContainer" class="flex-1 overflow-y-auto px-4 py-4 space-y-3 scroll-smooth">
                        <div
                            v-for="msg in messages"
                            :key="msg.id || msg._tempId"
                            class="flex gap-2"
                            :class="msg.sender_type === 'customer' ? 'flex-row-reverse' : 'flex-row'"
                        >
                            <!-- Avatar agent/bot -->
                            <div v-if="msg.sender_type !== 'customer'" class="w-7 h-7 rounded-full bg-[#ED1F24] flex items-center justify-center text-white text-xs font-bold shrink-0 mt-auto">
                                {{ (msg.sender_name || 'A').charAt(0).toUpperCase() }}
                            </div>

                            <div :class="[
                                'max-w-[75%] px-3 py-2 rounded-2xl text-sm leading-relaxed',
                                msg.sender_type === 'customer'
                                    ? 'bg-[#ED1F24] text-white rounded-br-sm'
                                    : 'bg-gray-100 text-gray-800 rounded-bl-sm'
                            ]">
                                <p class="whitespace-pre-wrap break-words">{{ msg.body }}</p>
                                <p :class="['text-xs mt-1', msg.sender_type === 'customer' ? 'text-white/60 text-right' : 'text-gray-400']">
                                    {{ fmtTime(msg.created_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Typing indicator -->
                        <div v-if="agentTyping" class="flex gap-2 items-end">
                            <div class="w-7 h-7 rounded-full bg-[#ED1F24] flex items-center justify-center text-white text-xs font-bold shrink-0">A</div>
                            <div class="bg-gray-100 rounded-2xl rounded-bl-sm px-4 py-3 flex gap-1 items-center">
                                <span v-for="i in 3" :key="i" class="w-1.5 h-1.5 rounded-full bg-gray-400 animate-bounce" :style="{ animationDelay: (i-1)*0.15 + 's' }"></span>
                            </div>
                        </div>

                        <div ref="msgBottom"></div>
                    </div>

                    <!-- Rating (jika sesi closed) -->
                    <div v-if="sessionClosed && !rated" class="border-t border-gray-100 px-4 py-3 bg-gray-50">
                        <p class="text-xs text-center text-gray-500 mb-2 font-medium">Bagaimana pengalaman chat Anda?</p>
                        <div class="flex justify-center gap-2">
                            <button v-for="star in 5" :key="star" @click="submitRating(star)" class="text-xl hover:scale-110 transition-transform">
                                {{ star <= hoverRating ? '⭐' : '☆' }}
                            </button>
                        </div>
                    </div>
                    <div v-else-if="rated" class="border-t border-gray-100 px-4 py-3 bg-gray-50 text-center">
                        <p class="text-xs text-gray-500">Terima kasih atas penilaian Anda! ⭐</p>
                    </div>

                    <!-- Input -->
                    <div v-if="!sessionClosed" class="border-t border-gray-100 px-3 py-3 flex items-end gap-2 shrink-0">
                        <textarea
                            v-model="newMessage"
                            @keydown.enter.exact.prevent="sendMessage"
                            @input="onTyping"
                            rows="1"
                            placeholder="Ketik pesan..."
                            class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm resize-none focus:outline-none focus:border-[#ED1F24] transition-colors max-h-24 overflow-y-auto"
                            style="line-height:1.5"
                        ></textarea>
                        <button
                            @click="sendMessage"
                            :disabled="!newMessage.trim() || sending"
                            class="bg-[#ED1F24] hover:bg-[#C81A1E] text-white p-2.5 rounded-xl disabled:opacity-50 transition-colors shrink-0"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </transition>

        <!-- Toggle Button -->
        <button
            @click="toggleChat"
            class="w-14 h-14 rounded-full bg-[#ED1F24] hover:bg-[#C81A1E] shadow-lg flex items-center justify-center transition-all duration-300 hover:scale-105 active:scale-95"
            style="box-shadow: 0 8px 24px rgba(237,31,36,0.35);"
        >
            <transition name="spin-swap" mode="out-in">
                <svg v-if="!isOpen" key="chat" class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <svg v-else key="close" class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </transition>
        </button>
    </div>
</template>

<script>
import axios from '@/axios.js'

export default {
    name: 'CustomerChat',

    data() {
        return {
            isOpen: false,
            sessionUuid: null,
            sessionLoading: false,
            sessionClosed: false,
            agentName: null,
            agentTyping: false,
            typingTimer: null,
            sendingTyping: false,

            guestForm: {
                name: '',
                phone: '',
                first_message: '',
            },

            messages: [],
            newMessage: '',
            sending: false,
            unreadCount: 0,

            rated: false,
            hoverRating: 0,

            echoChannel: null,
        }
    },

    methods: {
        toggleChat() {
            this.isOpen = !this.isOpen
            if (this.isOpen) {
                this.unreadCount = 0
                this.$nextTick(() => this.scrollToBottom())
            }
        },

        async startSession() {
            if (!this.guestForm.name.trim() || !this.guestForm.first_message.trim()) return
            this.sessionLoading = true
            try {
                const { data } = await axios.post('/chat/sessions', {
                    guest_name:    this.guestForm.name,
                    guest_phone:   this.guestForm.phone,
                    first_message: this.guestForm.first_message,
                    channel:       'web',
                })
                this.sessionUuid = data.session.uuid
                this.messages    = data.messages || []

                // Simpan ke localStorage supaya refresh tidak hilang sesi
                localStorage.setItem('cs_session_uuid', this.sessionUuid)
                localStorage.setItem('cs_guest_name',   this.guestForm.name)

                this.subscribeEcho()
                this.$nextTick(() => this.scrollToBottom())
            } catch (e) {
                alert('Gagal memulai sesi: ' + (e.response?.data?.message || e.message))
            } finally {
                this.sessionLoading = false
            }
        },

        async resumeSession(uuid) {
            this.sessionLoading = true
            try {
                const { data } = await axios.get(`/chat/sessions/${uuid}`)
                this.sessionUuid   = uuid
                this.sessionClosed = data.session.status === 'closed'
                this.agentName     = data.session.agent_name || null
                this.messages      = data.messages || []
                this.subscribeEcho()
                this.$nextTick(() => this.scrollToBottom())
            } catch {
                // Sesi tidak valid, reset
                localStorage.removeItem('cs_session_uuid')
            } finally {
                this.sessionLoading = false
            }
        },

        subscribeEcho() {
            if (!window.Echo) return
            this.echoChannel = window.Echo.private(`chat.session.${this.sessionUuid}`)
                .listen('.message.sent', (e) => {
                    // Hindari duplikat
                    if (!this.messages.find(m => m.id === e.message.id)) {
                        this.messages.push(e.message)
                        if (!this.isOpen) this.unreadCount++
                        this.$nextTick(() => this.scrollToBottom())
                    }
                })
                .listen('.typing.started', (e) => {
                    if (e.sender_type !== 'customer') {
                        this.agentTyping = true
                        clearTimeout(this.typingTimer)
                        this.typingTimer = setTimeout(() => { this.agentTyping = false }, 3000)
                    }
                })
                .listen('.typing.stopped', (e) => {
                    if (e.sender_type !== 'customer') this.agentTyping = false
                })
                .listen('.session.closed', () => {
                    this.sessionClosed = true
                })
                .listen('.session.assigned', (e) => {
                    this.agentName = e.agent_name
                })
        },

        async sendMessage() {
            if (!this.newMessage.trim() || this.sending) return
            const body = this.newMessage.trim()
            this.newMessage = ''
            this.sending = true

            // Optimistic UI
            const tempMsg = {
                _tempId:     Date.now(),
                body,
                sender_type: 'customer',
                created_at:  new Date().toISOString(),
            }
            this.messages.push(tempMsg)
            this.$nextTick(() => this.scrollToBottom())

            try {
                const { data } = await axios.post(`/chat/sessions/${this.sessionUuid}/messages`, { body })
                // Replace temp dengan real
                const idx = this.messages.findIndex(m => m._tempId === tempMsg._tempId)
                if (idx > -1) this.messages.splice(idx, 1, data.message)
            } catch (e) {
                // Hapus optimistic jika gagal
                this.messages = this.messages.filter(m => m._tempId !== tempMsg._tempId)
                this.newMessage = body
                alert('Gagal mengirim pesan')
            } finally {
                this.sending = false
            }
        },

        onTyping() {
            if (this.sendingTyping || !this.sessionUuid) return
            this.sendingTyping = true
            axios.post(`/chat/sessions/${this.sessionUuid}/typing`).catch(() => {})
            setTimeout(() => { this.sendingTyping = false }, 2000)
        },

        async submitRating(star) {
            this.hoverRating = star
            try {
                await axios.patch(`/chat/sessions/${this.sessionUuid}/rate`, { rating: star })
                this.rated = true
            } catch {}
        },

        resetSession() {
            localStorage.removeItem('cs_session_uuid')
            if (this.echoChannel) window.Echo?.leave(`chat.session.${this.sessionUuid}`)
            this.sessionUuid   = null
            this.sessionClosed = false
            this.agentName     = null
            this.messages      = []
            this.guestForm     = { name: '', phone: '', first_message: '' }
            this.rated         = false
        },

        scrollToBottom() {
            this.$refs.msgBottom?.scrollIntoView({ behavior: 'smooth' })
        },

        fmtTime(iso) {
            if (!iso) return ''
            return new Date(iso).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
        },
    },

    mounted() {
        // Coba resume sesi sebelumnya
        const savedUuid = localStorage.getItem('cs_session_uuid')
        if (savedUuid) this.resumeSession(savedUuid)
    },

    beforeUnmount() {
        if (this.echoChannel && this.sessionUuid) {
            window.Echo?.leave(`chat.session.${this.sessionUuid}`)
        }
        clearTimeout(this.typingTimer)
    },
}
</script>

<style scoped>
.slide-up-enter-active { transition: all .25s cubic-bezier(.34,1.56,.64,1); }
.slide-up-leave-active { transition: all .2s ease-in; }
.slide-up-enter-from  { opacity: 0; transform: translateY(16px) scale(.97); }
.slide-up-leave-to    { opacity: 0; transform: translateY(8px) scale(.98); }

.bounce-enter-active  { animation: bounceIn .3s; }
@keyframes bounceIn {
    0%   { transform: scale(0); }
    60%  { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.spin-swap-enter-active, .spin-swap-leave-active { transition: all .2s; }
.spin-swap-enter-from  { opacity: 0; transform: rotate(-90deg) scale(.7); }
.spin-swap-leave-to    { opacity: 0; transform: rotate(90deg) scale(.7); }
</style>