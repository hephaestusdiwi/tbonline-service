<template>
  <div class="chat-window">

    <!-- Header -->
    <div class="chat-header">
      <div class="header-left">
        <div class="agent-avatar" :class="{ 'is-bot': !assignedAgent }">
          <img
            v-if="assignedAgent && assignedAgent.avatar"
            :src="assignedAgent.avatar"
            :alt="assignedAgent.name"
            class="avatar-img"
          />
          <span v-else-if="assignedAgent">{{ agentInitials }}</span>
          <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path
              d="M12 2a5 5 0 1 0 0 10A5 5 0 0 0 12 2zM3 21a9 9 0 0 1 18 0"
              stroke="white"
              stroke-width="1.8"
              stroke-linecap="round"
            />
          </svg>
        </div>

        <div class="header-info">
          <p class="header-name">
            {{ assignedAgent ? assignedAgent.name : 'Customer Service' }}
          </p>

          <p v-if="assignedAgent" class="header-sub">
            <span class="status-dot online"></span>
            Agen aktif
          </p>
          <p v-else-if="agentOnline" class="header-sub">
            <span class="status-dot online"></span>
            Online sekarang
          </p>
          <p v-else class="header-sub">
            <span class="status-dot offline"></span>
            Agen sedang offline
          </p>
        </div>
      </div>

      <div class="header-actions">
        <button class="header-btn" title="Info sesi" @click="showSessionInfo = !showSessionInfo">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
            <path d="M12 16v-4M12 8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          </svg>
        </button>
        <button class="header-btn" title="Tutup" @click="emit('close')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
            <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Offline banner -->
    <div v-if="!agentOnline && !assignedAgent" class="offline-banner">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
        <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
      </svg>
      Semua agen sedang offline. Pesan Anda tetap akan dibalas.
    </div>

    <!-- Messages -->
    <div class="chat-messages" ref="messagesList">

      <div class="date-divider">
        <span>{{ todayLabel }}</span>
      </div>

      <!-- Welcome + quick replies -->
      <div v-if="showQuickReplies" class="message-row row-left welcome-row">
        <div class="msg-avatar">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
            <path
              d="M12 2a5 5 0 1 0 0 10A5 5 0 0 0 12 2zM3 21a9 9 0 0 1 18 0"
              stroke="white"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>
        </div>
        <div class="bubble-wrap">
          <p class="sender-name">Two Brothers Services Bot</p>
          <span class="bubble-time">{{ formatTime(new Date().toISOString()) }}</span>
          <div class="quick-replies">
            <button
              v-for="topic in quickTopics"
              :key="topic.id"
              class="quick-reply-btn"
              :class="{ selected: selectedTopicId === topic.id }"
              :disabled="selectedTopicId !== null"
              @click="selectTopic(topic)"
            >
              <span class="qr-icon">{{ topic.icon }}</span>
              <span class="qr-label">{{ topic.label }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Message list -->
      <template v-for="(message, index) in messages" :key="message.id">

        <div
          v-if="shouldShowDateSep(message, messages[index - 1])"
          class="date-divider"
        >
          <span>{{ formatDate(message.sent_at) }}</span>
        </div>

        <div v-if="message.sender_type === 'system'" class="system-message">
          <span>{{ message.content }}</span>
        </div>

        <div
          v-else-if="!isBotMenuMessage(message) && !isPaymentOptionsMessage(message)"
          class="message-row"
          :class="{ 'row-left': isLeft(message), 'row-right': !isLeft(message) }"
        >
          <div
            v-if="isLeft(message)"
            class="msg-avatar"
            :title="message.sender ? message.sender.name : 'CS'"
          >
            <img
              v-if="message.sender && message.sender.avatar"
              :src="message.sender.avatar"
              :alt="message.sender.name"
              style="width:100%;height:100%;object-fit:cover;border-radius:50%;"
            />
            <span v-else-if="message.sender && message.sender.name">
              {{ initials(message.sender.name) }}
            </span>
            <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none">
              <path
                d="M12 2a5 5 0 1 0 0 10A5 5 0 0 0 12 2zM3 21a9 9 0 0 1 18 0"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
              />
            </svg>
          </div>

          <div class="bubble-wrap">
            <p
              v-if="isLeft(message) && shouldShowSender(message, messages[index - 1])"
              class="sender-name"
            >
              {{ message.sender ? message.sender.name : (message.sender_type === 'bot' ? 'Bot' : 'CS') }}
            </p>

            <div class="bubble" :class="isLeft(message) ? 'bubble-left' : 'bubble-right'">
              <p class="bubble-text">{{ message.content }}</p>

              <div v-if="message.attachments && message.attachments.length" class="mt-2">
                <template v-for="att in message.attachments" :key="att.id">
                  <img
                    v-if="att.mime_type && att.mime_type.startsWith('image/')"
                    :src="att.url"
                    class="rounded-lg max-w-full cursor-pointer"
                    style="max-height:200px;object-fit:cover;"
                    @click="openFile(att.url, att.mime_type)"
                  />
                  <a
                    v-else
                    :href="att.url"
                    target="_blank"
                    class="flex items-center gap-2 text-xs underline mt-1"
                  >
                    📄 {{ att.original_name }}
                  </a>
                </template>
              </div>
            </div>

            <span class="bubble-time" :class="{ 'time-right': !isLeft(message) }">
              {{ formatTime(message.sent_at) }}
              <span v-if="!isLeft(message)" class="read-tick">
                <svg width="14" height="10" viewBox="0 0 16 12" fill="none">
                  <path d="M1 6l4 4L14 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M5 6l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.5" />
                </svg>
              </span>
            </span>
          </div>
        </div>

      </template>

      <!-- Tombol metode pembayaran — muncul otomatis begitu bot nyampe node
           'payment_method' (dipicu server-side setelah checkout). Teks mentah
           "1. Bayar di Toko\n2. Transfer" dari bot tetap dikirim & tersimpan di
           riwayat (kepakai agent CS di AdminChat.vue), tapi disembunyikan di sini
           lewat isPaymentOptionsMessage() supaya nggak dobel sama tombol ini. -->
      <div v-if="showPaymentButtons" class="message-row row-left">
        <div class="msg-avatar">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
            <path
              d="M12 2a5 5 0 1 0 0 10A5 5 0 0 0 12 2zM3 21a9 9 0 0 1 18 0"
              stroke="white"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>
        </div>
        <div class="bubble-wrap">
          <div class="quick-replies">
            <button
              v-for="opt in paymentMethodOptions"
              :key="opt.id"
              class="quick-reply-btn"
              :class="{ selected: selectedPaymentId === opt.id }"
              :disabled="selectedPaymentId !== null"
              @click="selectPaymentMethod(opt)"
            >
              <span class="qr-icon">{{ opt.icon }}</span>
              <span class="qr-label">{{ opt.label }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Lightbox -->
      <teleport to="body">
        <div v-if="lightboxUrl" class="lightbox-overlay" @click="lightboxUrl = null">
          <button class="lightbox-close" @click="lightboxUrl = null">✕</button>
          <img :src="lightboxUrl" class="lightbox-img" @click.stop />
        </div>
      </teleport>

      <!-- Typing indicator -->
      <div v-if="isAgentTyping" class="message-row row-left">
        <div class="msg-avatar">
          <span v-if="assignedAgent">{{ agentInitials }}</span>
          <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none">
            <path
              d="M12 2a5 5 0 1 0 0 10A5 5 0 0 0 12 2zM3 21a9 9 0 0 1 18 0"
              stroke="white"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>
        </div>
        <div class="bubble-wrap">
          <div class="bubble bubble-left typing-bubble">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </div>
      </div>

      <!-- Skeleton loading -->
      <div v-if="isLoading" class="messages-loading">
        <div class="skeleton-msg left"></div>
        <div class="skeleton-msg right"></div>
        <div class="skeleton-msg left short"></div>
      </div>

      <!-- Queue banner -->
      <div v-if="queuePosition" class="queue-shopee">
        <span class="queue-dot"></span>
        <span>Antrian #{{ queuePosition }}</span>
        <span v-if="estimatedWait" class="queue-separator">·</span>
        <span v-if="estimatedWait">Estimasi {{ Math.ceil(estimatedWait / 60) }} menit</span>
      </div>

    </div>

    <!-- Rating Form -->
    <div v-if="showRating" class="rating-section">
        <div v-if="!ratingSubmitted" class="rating-card">
            <div class="rating-icon">⭐</div>
            <p class="rating-title">Bagaimana pengalaman Anda?</p>
            <p class="rating-subtitle">Berikan penilaian untuk layanan kami</p>

            <div class="rating-stars">
                <button
                    v-for="star in 5"
                    :key="star"
                    class="star-btn"
                    @click="submitRating(star)"
                    @mouseenter="ratingHover = star"
                    @mouseleave="ratingHover = 0"
                    :disabled="ratingLoading"
                >
                    <svg
                        width="32" height="32" viewBox="0 0 24 24"
                        :fill="star <= (ratingHover || ratingValue) ? '#f59e0b' : 'none'"
                        :stroke="star <= (ratingHover || ratingValue) ? '#f59e0b' : '#d1d5db'"
                        stroke-width="1.5"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                    </svg>
                </button>
            </div>

            <div class="rating-labels">
                <span>Sangat Buruk</span>
                <span>Sangat Bagus</span>
            </div>

            <button class="skip-btn" @click="showRating = false">
                Lewati
            </button>
        </div>

        <!-- Terima kasih -->
        <div v-else class="rating-thanks">
            <div class="thanks-icon">🎉</div>
            <p class="thanks-title">Terima kasih</p>
            <p class="thanks-subtitle">Penilaian Anda sangat berarti untuk meningkatkan mutu pelayanan kami kedepannya.</p>
            <div class="thanks-stars">
                <span v-for="star in ratingValue" :key="star">⭐</span>
            </div>
        </div>
    </div>

    <!-- Input area -->
    <div class="chat-input-area">
      <div class="input-wrap" :class="{ focused: inputFocused }">
        <textarea
          v-model="inputText"
          ref="inputRef"
          rows="1"
          placeholder="Ketik pesan..."
          :disabled="isSending"
          @keydown.enter.exact.prevent="send()"
          @focus="inputFocused = true"
          @blur="inputFocused = false"
          @input="autoResize"
        ></textarea>

        <label class="upload-btn" title="Kirim gambar/dokumen">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path
              d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>
          <input
            type="file"
            class="hidden"
            accept="image/*,.pdf,.doc,.docx"
            @change="sendAttachment"
          />
        </label>

        <button
          class="send-btn"
          :class="{ active: inputText.trim() }"
          :disabled="isSending || !inputText.trim()"
          aria-label="Kirim"
          @click="send()"
        >
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
            <path
              d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z"
              stroke="currentColor"
              stroke-width="2.2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </button>
      </div>
      <p class="input-hint">Enter kirim &nbsp;·&nbsp; Shift+Enter baris baru</p>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import axios from '@/axios.js'

const props = defineProps({
  sessionUuid: { type: String, required: true }
})

const emit = defineEmits(['new-message', 'close'])

const messages         = ref([])
const inputText        = ref('')
const isLoading        = ref(false)
const isSending        = ref(false)
const messagesList     = ref(null)
const inputRef         = ref(null)
const inputFocused     = ref(false)
const assignedAgent    = ref(null)
const isAgentTyping    = ref(false)
const showQuickReplies = ref(true)
const selectedTopicId  = ref(null)
const queuePosition    = ref(null)
const estimatedWait    = ref(null)
const sessionStatus    = ref(null)
const showSessionInfo  = ref(false)
const lightboxUrl      = ref(null)
const agentOnline      = ref(true)
const showRating       = ref(false)
const ratingValue      = ref(0)
const ratingHover      = ref(0)
const ratingSubmitted  = ref(false)
const ratingLoading    = ref(false)

let channel             = null
let agentStatusChannel  = null
let typingTimer         = null
let heartbeatInterval   = null

// ── Quick topics ─────────────────────────────────────────────────────────
// Daftar ini harus sinkron sama $flow['greeting']['options'] di ChatbotService.php
// (urutan & angka message harus sama persis, soalnya 'message' inilah yang dikirim sbg jawaban user)
const quickTopics = [
  { id: 1, icon: '🛍️', label: 'Pertanyaan Produk',       message: '1', displayText: 'Pertanyaan Produk'       },
  { id: 2, icon: '📦', label: 'Status Pesanan',           message: '2', displayText: 'Status Pesanan'           },
  { id: 3, icon: '⚠️', label: 'Komplain',                 message: '3', displayText: 'Komplain'                 },
  { id: 4, icon: '💬', label: 'Chat dengan CS',           message: '4', displayText: 'Chat dengan CS'           },
  { id: 5, icon: '🛒', label: 'Pembelian',                message: '5', displayText: 'Pembelian' },
]

// ── Payment method options ──────────────────────────────────────────────
// Harus sinkron sama $flow['payment_method']['options'] di ChatbotService.php
// (angka '1'/'2' inilah yang dikirim balik sbg jawaban user ke bot)
const paymentMethodOptions = [
  { id: 1, icon: '🏬', label: 'Bayar di Toko', message: '1', displayText: 'Bayar di Toko' },
  { id: 2, icon: '💳', label: 'Transfer',      message: '2', displayText: 'Transfer'      },
]
const chatbotNode      = ref(null)
const selectedPaymentId = ref(null)

// ── Computed ──────────────────────────────────────────────────────────────
const agentInitials = computed(() =>
  assignedAgent.value ? initials(assignedAgent.value.name) : ''
)

// Tombol pembayaran tampil kalau bot lagi nunggu jawaban di node 'payment_method'
// DAN customer belum klik salah satu (selectedPaymentId masih null) DAN sesi belum ditutup.
const showPaymentButtons = computed(() =>
  chatbotNode.value === 'payment_method' &&
  selectedPaymentId.value === null &&
  sessionStatus.value !== 'closed' &&
  sessionStatus.value !== 'resolved'
)

const todayLabel = computed(() =>
  new Date().toLocaleDateString('id-ID', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
  })
)

// ── Helpers ───────────────────────────────────────────────────────────────
function isLeft(msg) {
  return msg.sender_type === 'bot' || msg.sender_type === 'agent'
}

function initials(name) {
  if (!name) return '??'
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
}

function formatTime(ts) {
  if (!ts) return ''
  return new Date(ts).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

function formatDate(ts) {
  if (!ts) return ''
  return new Date(ts).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' })
}

function shouldShowDateSep(msg, prev) {
  if (!prev || !msg.sent_at) return false
  return new Date(prev.sent_at).toDateString() !== new Date(msg.sent_at).toDateString()
}

function shouldShowSender(msg, prev) {
  if (!prev) return true
  return prev.sender_type !== msg.sender_type ||
    (prev.sender && msg.sender && prev.sender.id !== msg.sender.id)
}

function isBotMenuMessage(msg) {
  if (msg.sender_type !== 'bot') return false
  return msg.content &&
    msg.content.includes('1.') &&
    msg.content.includes('2.') &&
    msg.content.includes('3.')
}

// Sembunyiin teks mentah opsi node 'payment_method' (cuma 2 opsi, jadi nggak
// kena isBotMenuMessage() di atas yang minimal 3) — digantikan tombol
// showPaymentButtons. Exact-match sengaja, harus sinkron sama label di
// $flow['payment_method']['options'] @ ChatbotService.php.
function isPaymentOptionsMessage(msg) {
  if (msg.sender_type !== 'bot') return false
  return msg.content === '1. Bayar di Toko\n2. Transfer'
}

function openFile(url, mimeType) {
  if (mimeType && mimeType.startsWith('image/')) {
    lightboxUrl.value = url
  } else {
    window.open(url, '_blank')
  }
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesList.value) {
      messagesList.value.scrollTop = messagesList.value.scrollHeight
    }
  })
}

function autoResize() {
  const el = inputRef.value
  if (!el) return
  el.style.height = 'auto'
  el.style.height = Math.min(el.scrollHeight, 120) + 'px'
}

function resetInputHeight() {
  if (inputRef.value) inputRef.value.style.height = 'auto'
}

// ── Agent status ──────────────────────────────────────────────────────────
async function fetchAgentStatus() {
  try {
    const { data } = await axios.get('/agents/status')
    agentOnline.value = data.any_online ?? true
  } catch {
    agentOnline.value = true
  }
}

function subscribeAgentStatusChannel() {
  if (!window.Echo) return
  agentStatusChannel = window.Echo
    .channel('agents.status')
    .listen('.status.changed', (e) => {
      agentOnline.value = e.any_online ?? true
    })
}

function unsubscribeAgentStatusChannel() {
  if (!window.Echo) return
  window.Echo.leave('agents.status')
  agentStatusChannel = null
}

// ── Session channel ───────────────────────────────────────────────────────
function subscribeChannel() {
  if (!window.Echo) return
  channel = window.Echo
    .channel('chat.session.' + props.sessionUuid)

    .listen('.message.sent', (e) => {
      if (!e.message || e.message.type === 'system_internal') return
      if (!messages.value.find(m => m.id === e.message.id)) {
        messages.value.push(e.message)
        scrollToBottom()
        emit('new-message')
      }
    })

    .listen('.session.assigned', (e) => {
      queuePosition.value = null
      estimatedWait.value  = null
      if (e.agent) {
        assignedAgent.value = e.agent
        agentOnline.value   = true
      }
    })

    .listen('.queue.position', (e) => {
      queuePosition.value = e.position
      estimatedWait.value  = e.estimated_wait
    })

    .listen('.typing.started', (e) => {
      if (e.sender_type === 'agent') {
        isAgentTyping.value = true
        clearTimeout(typingTimer)
        typingTimer = setTimeout(() => { isAgentTyping.value = false }, 3000)
      }
    })

    .listen('.typing.stopped', (e) => {
      if (e.sender_type === 'agent') {
        isAgentTyping.value = false
      }
    })

    .listen('.session.closed', () => {
        sessionStatus.value = 'closed'
        showRating.value = true  
    })
}

function leaveChannel() {
  if (!window.Echo) return
  window.Echo.leave('chat.session.' + props.sessionUuid)
  channel = null
}

// ── Heartbeat & leave ─────────────────────────────────────────────────────
async function sendPing() {
  try {
    await axios.post('/chat/sessions/' + props.sessionUuid + '/ping')
  } catch { /* silent */ }
}

async function submitRating(star) {
    ratingValue.value = star
    ratingLoading.value = true
    try {
        await axios.patch(`/chat/sessions/${props.sessionUuid}/rate`, { rating: star })
        ratingSubmitted.value = true
    } catch {
        console.error('Gagal submit rating')
    } finally {
        ratingLoading.value = false
    }
}

function startHeartbeat() {
  sendPing()
  heartbeatInterval = setInterval(sendPing, 10000)
}

function sendLeave() {
  const url = import.meta.env.VITE_API_URL + '/chat/sessions/' + props.sessionUuid + '/leave'
  const sent = navigator.sendBeacon(url)
  if (!sent) {
    fetch(url, {
      method: 'POST',
      keepalive: true,
      headers: { 'Content-Type': 'application/json' },
    }).catch(() => {})
  }
}

// ── Load messages ─────────────────────────────────────────────────────────
async function loadMessages() {
  isLoading.value = true
  try {
    const { data } = await axios.get('/chat/sessions/' + props.sessionUuid + '/messages')
    messages.value = (data.data ?? []).filter(m => m.type !== 'system_internal')

    const sessionRes = await axios.get('/chat/sessions/' + props.sessionUuid + '/by-token', {
      params: { guest_token: localStorage.getItem('chat_guest_token') }
    })
    const session = sessionRes.data.data ?? sessionRes.data

    // Node flow bot saat ini (mis. 'payment_method') — nentuin tombol
    // quick-reply mana yang harus ditampilkan (lihat showPaymentButtons).
    chatbotNode.value = session.chatbot_node ?? null

    if (session.status === 'queued' && session.queue_entry) {
      queuePosition.value = session.queue_entry.position
      estimatedWait.value  = session.queue_entry.estimated_wait_seconds
    }

    if (session.status === 'active' && session.assigned_agent) {
      assignedAgent.value = session.assigned_agent
      queuePosition.value = null
      estimatedWait.value  = null
    }

    // ← TAMBAHAN: cek sesi sudah closed/resolved saat pertama load
    if (session.status === 'closed' || session.status === 'resolved') {
      sessionStatus.value = session.status
      if (!session.rating) {
        showRating.value = true
      }
    }

    scrollToBottom()
  } catch (e) {
    console.error('Gagal memuat pesan:', e)
  } finally {
    isLoading.value = false
  }
}

// ── Send message ──────────────────────────────────────────────────────────
async function send(displayOverride) {
  const content = inputText.value.trim()
  if (!content || isSending.value) return

  inputText.value = ''
  isSending.value = true
  resetInputHeight()

  try {
    const { data } = await axios.post(
      '/chat/sessions/' + props.sessionUuid + '/messages',
      { content, type: 'text' }
    )
    if (data.data && data.data.id && !messages.value.find(m => m.id === data.data.id)) {
      messages.value.push({
        ...data.data,
        content: displayOverride !== undefined ? displayOverride : data.data.content
      })
    }
    scrollToBottom()
  } catch {
    inputText.value = content
  } finally {
    isSending.value = false
    nextTick(() => { if (inputRef.value) inputRef.value.focus() })
  }
}

async function selectTopic(topic) {
  selectedTopicId.value = topic.id
  showQuickReplies.value = false
  inputText.value = topic.message
  await send(topic.displayText)
}

async function selectPaymentMethod(opt) {
  selectedPaymentId.value = opt.id
  inputText.value = opt.message
  await send(opt.displayText)
  // Bot pindah node (payment_offline/transfer → handoff) begitu jawaban ini
  // diproses; nggak perlu nunggu chatbot_node lagi karena selectedPaymentId
  // udah cukup buat nyembunyiin tombolnya (lihat showPaymentButtons).
}

// ── Attachment ────────────────────────────────────────────────────────────
async function sendAttachment(e) {
  const file = e.target.files[0]
  if (!file) return

  const form = new FormData()
  form.append('file', file)

  try {
    const { data } = await axios.post(
      '/chat/sessions/' + props.sessionUuid + '/attachments',
      form,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )
    if (data.data && data.data.id && !messages.value.find(m => m.id === data.data.id)) {
      messages.value.push(data.data)
      scrollToBottom()
    }
  } catch {
    console.error('Gagal upload')
  }

  e.target.value = ''
}

// ── Lifecycle ─────────────────────────────────────────────────────────────
onMounted(async () => {
  await fetchAgentStatus()
  subscribeAgentStatusChannel()
  await loadMessages()
  subscribeChannel()
  startHeartbeat()
  window.addEventListener('beforeunload', sendLeave)
  nextTick(() => scrollToBottom())
})

onUnmounted(() => {
  clearInterval(heartbeatInterval)
  clearTimeout(typingTimer)
  window.removeEventListener('beforeunload', sendLeave)
  unsubscribeAgentStatusChannel()
  leaveChannel()
})
</script>

<style scoped>
.chat-window {
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 0;
  overflow: hidden;
  background: #f4f6fb;
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

/* Header */
.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 13px 15px;
  background: linear-gradient(135deg, #7A1016 35%, #B31217 70%, #ED1F24 100%);
  flex-shrink: 0;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.agent-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  overflow: hidden;
  position: relative;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.18);
  color: white;
  font-size: 13px;
  font-weight: 700;
}

.agent-avatar .avatar-img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  border-radius: 50%;
}

.header-info .header-name {
  margin: 0;
  font-size: 13.5px;
  font-weight: 700;
  color: white;
}

.header-info .header-sub {
  margin: 2px 0 0;
  font-size: 11px;
  color: rgba(255, 255, 255, 0.75);
  display: flex;
  align-items: center;
  gap: 4px;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  display: inline-block;
}

.status-dot.online  { background: #4ade80; animation: pulse-g 2s infinite; }
.status-dot.offline { background: #9ca3af; }

@keyframes pulse-g {
  0%, 100% { box-shadow: 0 0 0 2px rgba(74, 222, 128, 0.35); }
  50%       { box-shadow: 0 0 0 5px rgba(74, 222, 128, 0.1); }
}

.header-actions { display: flex; gap: 6px; }

.header-btn {
  width: 30px;
  height: 30px;
  border: none;
  background: rgba(255, 255, 255, 0.15);
  color: rgba(255, 255, 255, 0.85);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.15s;
}

.header-btn:hover { background: rgba(255, 255, 255, 0.26); }

/* Offline banner */
.offline-banner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 14px;
  background: #fef3c7;
  border-bottom: 1px solid #fde68a;
  font-size: 11.5px;
  color: #92400e;
  font-weight: 500;
  flex-shrink: 0;
}

/* Messages */
.chat-messages {
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  padding: 14px 14px 10px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  scroll-behavior: smooth;
}

.chat-messages::-webkit-scrollbar { width: 3px; }
.chat-messages::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

.date-divider {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 10px 0 8px;
}

.date-divider::before,
.date-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #e5e7eb;
}

.date-divider span {
  font-size: 10px;
  color: #9ca3af;
  white-space: nowrap;
  font-weight: 500;
  padding: 0 2px;
}

.system-message {
  display: flex;
  justify-content: center;
  margin: 8px 0;
}

.system-message span {
  font-size: 11px;
  color: #6b7280;
  background: #e5e7eb;
  padding: 3px 14px;
  border-radius: 999px;
  text-align: center;
  max-width: 80%;
}

.message-row {
  display: flex;
  gap: 8px;
  margin-bottom: 4px;
  align-items: flex-end;
}

.row-left  { align-self: flex-start; max-width: 90%; }
.row-right { align-self: flex-end;   max-width: 82%; flex-direction: row-reverse; }
.welcome-row { max-width: 94%; }

.msg-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: linear-gradient(135deg, #5A0E13 0%, #8F1117 45%, #C5161C 75%, #ED1F24 100%);
  color: white;
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-bottom: 16px;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
}

.bubble-wrap {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.sender-name {
  margin: 0 0 3px 4px;
  font-size: 10.5px;
  font-weight: 600;
  color: #6b7280;
  letter-spacing: 0.01em;
}

.bubble {
  padding: 10px 14px;
  border-radius: 18px;
  font-size: 13.5px;
  line-height: 1.5;
  word-break: break-word;
}

.bubble-left {
  background: white;
  color: #1f2937;
  border-bottom-left-radius: 5px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.07), 0 0 0 1px rgba(0, 0, 0, 0.03);
}

.bubble-right {
  background: linear-gradient(135deg, #7F1015 20%, #ED1F24 100%);
  color: white;
  border-bottom-right-radius: 5px;
  box-shadow: 0 2px 10px rgba(37, 99, 235, 0.35);
}

.bubble-text {
  font-family: 'Open Sans', sans-serif;
  margin: 0;
  white-space: pre-wrap;
}

.bubble-time {
  font-size: 10px;
  color: #9ca3af;
  padding: 0 5px;
  display: flex;
  align-items: center;
  gap: 3px;
}

.time-right { justify-content: flex-end; }

.read-tick {
  color: #3b82f6;
  display: flex;
  align-items: center;
}

/* Quick replies */
.quick-replies {
  display: grid;
  grid-template-columns: 1fr;
  gap: 7px;
  margin-top: 10px;
  max-width: min(280px, 100%);
}

.quick-reply-btn {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 9px 12px;
  background: white;
  border: 1.5px solid #dbeafe;
  border-radius: 12px;
  cursor: pointer;
  font-size: 12.5px;
  font-weight: 500;
  color: #1e40af;
  text-align: left;
  transition: all 0.18s ease;
  font-family: inherit;
  box-shadow: 0 1px 3px rgba(37, 99, 235, 0.1);
}

.quick-reply-btn:hover:not(:disabled) {
  background: #eff6ff;
  border-color: #3b82f6;
  box-shadow: 0 3px 10px rgba(37, 99, 235, 0.18);
  transform: translateY(-1px);
}

.quick-reply-btn.selected {
  background: linear-gradient(135deg, #7A1016 35%, #B31217 70%, #ED1F24 100%);
  border-color: transparent;
  color: white;
}

.quick-reply-btn:disabled:not(.selected) {
  opacity: 0.45;
  cursor: not-allowed;
}

.qr-icon { font-size: 16px; flex-shrink: 0; line-height: 1; }
.qr-label { line-height: 1.3; font-size: 12px; font-weight: 600; }

/* Typing */
.typing-bubble {
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 5px;
  min-width: 56px;
}

.typing-bubble span {
  width: 7px;
  height: 7px;
  background: #cbd5e1;
  border-radius: 50%;
  animation: typing-bounce 1.2s infinite ease-in-out;
}

.typing-bubble span:nth-child(2) { animation-delay: 0.18s; }
.typing-bubble span:nth-child(3) { animation-delay: 0.36s; }

@keyframes typing-bounce {
  0%, 60%, 100% { transform: translateY(0); background: #cbd5e1; }
  30%           { transform: translateY(-7px); background: #94a3b8; }
}

/* Skeleton */
.messages-loading {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 8px 0;
}

.skeleton-msg {
  height: 38px;
  border-radius: 16px;
  background: linear-gradient(90deg, #f0f4f8 25%, #e2e8f0 50%, #f0f4f8 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-msg.left  { width: 65%; align-self: flex-start; }
.skeleton-msg.right { width: 55%; align-self: flex-end; }
.skeleton-msg.short { width: 38%; }

@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Queue */
.queue-shopee {
  font-family: 'Poppins', sans-serif;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin: 10px auto 14px;
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.3);
  border: 1px solid #fff;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 500;
  color: #707070;
  align-self: center;
}

.queue-dot {
  width: 6px;
  height: 6px;
  border-radius: 999px;
  background: #707070;
  flex-shrink: 0;
}

.queue-separator { opacity: 0.5; }

/* Input */
.chat-input-area {
  padding: 10px 12px 8px;
  background: white;
  border-top: 1px solid #eef0f4;
  flex-shrink: 0;
}

.input-wrap {
  display: flex;
  align-items: flex-end;
  background: #f4f6fb;
  border: 1.5px solid #e5e7eb;
  border-radius: 16px;
  padding: 6px 6px 6px 14px;
  gap: 4px;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}

.input-wrap.focused {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
  background: white;
}

.input-wrap textarea {
  flex: 1;
  border: none;
  background: transparent;
  outline: none;
  font-size: 13.5px;
  color: #1f2937;
  resize: none;
  line-height: 1.48;
  max-height: 120px;
  font-family: inherit;
}

.input-wrap textarea::placeholder { color: #b0b7c3; }

.send-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: none;
  background: #e5e7eb;
  color: #b0b7c3;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: background 0.2s, color 0.2s, transform 0.15s, box-shadow 0.2s;
}

.send-btn.active {
  background: linear-gradient(135deg, #7A1016 35%, #B31217 70%, #ED1F24 100%);
  color: white;
  box-shadow: 0 3px 10px rgba(37, 99, 235, 0.4);
}

.send-btn.active:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.5);
}

.send-btn:disabled { cursor: not-allowed; opacity: 0.55; }

.input-hint {
  margin: 5px 0 0;
  font-size: 10px;
  color: #d1d5db;
  text-align: center;
}

/* Lightbox */
.lightbox-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: zoom-out;
  animation: fade-in 0.2s ease;
}

.lightbox-img {
  max-width: 90vw;
  max-height: 90vh;
  object-fit: contain;
  border-radius: 8px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  cursor: default;
  animation: zoom-in 0.2s ease;
}

.lightbox-close {
  position: absolute;
  top: 20px;
  right: 24px;
  background: rgba(255, 255, 255, 0.15);
  border: none;
  color: white;
  font-size: 18px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}

.lightbox-close:hover { background: rgba(255, 255, 255, 0.3); }

@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
@keyframes zoom-in { from { transform: scale(0.85); opacity: 0; } to { transform: scale(1); opacity: 1; } }

/* Upload */
.upload-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  color: #b0b7c3;
  transition: color 0.2s, background 0.2s;
}

.upload-btn:hover { color: #6b7280; background: #f0f0f0; }

.hidden { display: none; }

.rating-section {
    flex-shrink: 0;
    border-top: 1px solid #e5e7eb;
    background: white;
}

.rating-card {
    padding: 20px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-align: center;
}

.rating-icon {
    font-size: 28px;
    line-height: 1;
}

.rating-title {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
}

.rating-subtitle {
    margin: 0;
    font-size: 11.5px;
    color: #9ca3af;
}

.rating-stars {
    display: flex;
    gap: 6px;
    margin: 8px 0 4px;
}

.star-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 2px;
    transition: transform 0.15s ease;
    line-height: 1;
}

.star-btn:hover:not(:disabled) {
    transform: scale(1.2);
}

.star-btn:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.rating-labels {
    display: flex;
    justify-content: space-between;
    width: 100%;
    max-width: 200px;
    font-size: 10px;
    color: #9ca3af;
}

.skip-btn {
    margin-top: 4px;
    background: none;
    border: none;
    font-size: 11.5px;
    color: #9ca3af;
    cursor: pointer;
    text-decoration: underline;
    font-family: inherit;
}

.skip-btn:hover { color: #6b7280; }

.rating-thanks {
    padding: 20px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    text-align: center;
}

.thanks-icon { font-size: 28px; }

.thanks-title {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
}

.thanks-subtitle {
    margin: 0;
    padding: 0 30px;
    font-size: 11.5px;
    color: #9ca3af;
}

.thanks-stars {
    font-size: 18px;
    letter-spacing: 2px;
    margin-top: 4px;
}

.mt-2 { margin-top: 8px; }
.rounded-lg { border-radius: 8px; }
.max-w-full { max-width: 100%; }
.cursor-pointer { cursor: pointer; }
.flex { display: flex; }
.items-center { align-items: center; }
.gap-2 { gap: 8px; }
.text-xs { font-size: 12px; }
.underline { text-decoration: underline; }
.mt-1 { margin-top: 4px; }

@media (max-width: 480px) {
  .input-wrap textarea {
    font-size: 16px;
  }
}
</style>