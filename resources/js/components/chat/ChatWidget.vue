<template>
  <div>
    <!-- Toggle Button -->
    <transition name="btn-pop">
      <button
        v-if="!isOpen"
        @click="toggleChat"
        class="chat-toggle-btn"
        aria-label="Buka chat"
      >
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
            stroke="white" stroke-width="2" stroke-linejoin="round" fill="rgba(255,255,255,0.15)"/>
        </svg>
        <span v-if="unreadCount > 0" class="unread-badge">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
        <span class="toggle-ripple"></span>
      </button>
    </transition>

    <!-- Chat Popup -->
    <transition name="chat-slide">
      <div v-if="isOpen" class="chat-popup">

        <!-- Header -->
        <div class="popup-header" v-if="!sessionUuid">
          <div class="header-brand">
            <div class="brand-avatar">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                  stroke="white" stroke-width="2" stroke-linejoin="round" fill="rgba(255,255,255,0.2)"/>
              </svg>
            </div>
            <div>
              <p class="brand-name">Customer Support</p>
              <p class="brand-status">
                <span class="status-dot" :class="agentOnline ? 'dot-online' : 'dot-offline'"></span>
                {{ agentOnline ? 'Online sekarang' : 'Agen sedang offline' }}
              </p>
            </div>
          </div>
          <button @click="toggleChat" class="close-btn" aria-label="Tutup">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
              <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
          </button>
        </div>

        <!-- Guest Form -->
        <transition name="fade">
          <div v-if="!sessionUuid" class="chat-form-wrapper">
            <div class="form-hero">
              <!-- Online: emoji wave, Offline: logo toko -->
              <div class="hero-wave" :class="{ 'no-anim': !agentOnline }">
                <template v-if="agentOnline">👋</template>
                <img
                  v-else-if="storeLogo"
                  :src="storeLogo"
                  alt="Logo"
                  style="height:60px; width:auto; object-fit:contain; transition: opacity 0.3s ease;"
                />
                <span v-else>🕐</span>
              </div>

              <h3 class="hero-title">
                {{ agentOnline ? 'Halo! Ada yang bisa kami bantu?' : 'Hai kak!' }}
              </h3>

              <p class="hero-subtitle">
                {{ agentOnline
                  ? 'Isi data berikut untuk mulai mengobrol dengan tim kami.'
                  : 'Kami sedang di luar jam operasional. Silakan tinggalkan pesan, dan tim kami akan segera menghubungi Kakak saat jam kerja.'
                }}
              </p>
            </div>

            <div class="form-fields">
              <div class="field-group">
                <label class="field-label">Nama Lengkap</label>
                <div class="field-input-wrap" :class="{ 'focused': focusedField === 'name', 'has-error': formErrors.name }">
                  <svg class="field-icon" width="15" height="15" viewBox="0 0 24 24" fill="none">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"
                      stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                  </svg>
                  <input
                    v-model="form.name"
                    @focus="focusedField = 'name'"
                    @blur="focusedField = null; validateField('name')"
                    placeholder="Masukkan nama Anda"
                    type="text"
                    autocomplete="name"
                  />
                </div>
                <span v-if="formErrors.name" class="field-error">{{ formErrors.name }}</span>
              </div>

              <div class="field-group">
                <label class="field-label">Nomor WhatsApp / HP</label>
                <div class="field-input-wrap" :class="{ 'focused': focusedField === 'phone', 'has-error': formErrors.phone }">
                  <svg class="field-icon" width="15" height="15" viewBox="0 0 24 24" fill="none">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.77a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"
                      stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                  </svg>
                  <input
                    v-model="form.phone"
                    @focus="focusedField = 'phone'"
                    @blur="focusedField = null; validateField('phone')"
                    placeholder="Contoh: 08123456789"
                    type="tel"
                    autocomplete="tel"
                  />
                </div>
                <span v-if="formErrors.phone" class="field-error">{{ formErrors.phone }}</span>
              </div>

              <p v-if="error" class="submit-error">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                  <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                {{ error }}
              </p>

              <button @click="startSession" :disabled="isLoading" class="start-btn">
                <span v-if="!isLoading">Mulai Chat Sekarang</span>
                <span v-else class="loading-dots">
                  <span></span><span></span><span></span>
                </span>
              </button>

              <p class="privacy-note">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none">
                  <rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Data Anda aman &amp; tidak dibagikan ke pihak ketiga.
              </p>
            </div>
          </div>
        </transition>

        <!-- Chat Window -->
        <div v-if="sessionUuid" class="chat-window-wrapper">
          <ChatWindow
            :session-uuid="sessionUuid"
            @new-message="onNewMessage"
            @close="toggleChat"
          />
        </div>

      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from '@/axios.js'
import ChatWindow from './ChatWindow.vue'

const isOpen       = ref(false)
const isLoading    = ref(false)
const error        = ref('')
const sessionUuid  = ref(null)
const focusedField = ref(null)
const unreadCount  = ref(0)
const agentOnline = ref(true)
const storeLogo = ref(null)

const form       = ref({ name: '', phone: '' })
const formErrors = ref({ name: '', phone: '' })

onMounted(async () => {
  window.addEventListener('open-chat', () => {
    isOpen.value = true
    unreadCount.value = 0
  })

  
  await fetchAgentStatus()     
  await fetchStoreLogo() 
  subscribeAgentStatus() 

  const savedUuid  = localStorage.getItem('chat_session_uuid')
  const savedToken = localStorage.getItem('chat_guest_token')

  if (savedUuid && savedToken) {
    try {
      // Cek apakah session masih aktif/queued
      const { data } = await axios.get(`/chat/sessions/${savedUuid}/by-token`, {
        params: { guest_token: savedToken }
      })
      const session = data.data ?? data
      if (['queued', 'active', 'bot'].includes(session.status)) {
        sessionUuid.value = savedUuid // ← langsung masuk tanpa form
      } else {
        // Session sudah closed/resolved → clear storage, tampilkan form
        localStorage.removeItem('chat_session_uuid')
        localStorage.removeItem('chat_guest_token')
      }
    } catch {
      // Session tidak ditemukan → clear storage
      localStorage.removeItem('chat_session_uuid')
      localStorage.removeItem('chat_guest_token')
    }
  }
})

onBeforeUnmount(() => {  
  unsubscribeAgentStatus()
})

function toggleChat() {
  isOpen.value = !isOpen.value
  if (isOpen.value) unreadCount.value = 0
}

function validateField(field) {
  if (field === 'name') {
    formErrors.value.name = form.value.name.trim() ? '' : 'Nama wajib diisi.'
  }
  if (field === 'phone') {
    const phone = form.value.phone.trim()
    if (!phone) formErrors.value.phone = 'Nomor HP wajib diisi.'
    else if (!/^[0-9+\-\s()]{8,15}$/.test(phone)) formErrors.value.phone = 'Format nomor HP tidak valid.'
    else formErrors.value.phone = ''
  }
}

function validateAll() {
  validateField('name')
  validateField('phone')
  return !formErrors.value.name && !formErrors.value.phone
}

async function startSession() {
  if (!validateAll()) return
  isLoading.value = true
  error.value = ''
  try {
    const existingToken = localStorage.getItem('chat_guest_token') 
    const response = await axios.post('/chat/sessions', {
      guest_name:  form.value.name,
      guest_phone: form.value.phone,
    })
    localStorage.setItem('chat_guest_token', response.data.guest_token)
    localStorage.setItem('chat_session_uuid', response.data.data.uuid)
    sessionUuid.value = response.data.data.uuid
  } catch (err) {
    error.value = 'Gagal memulai sesi. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
}

async function fetchStoreLogo() {
  try {
    const { data } = await axios.get('/settings')
    const path = data.site_logo_footer?.value ?? null
    storeLogo.value = path ? `https://tbstore.id${path}` : null
  } catch { /* silent */ }
}

async function fetchAgentStatus() {
  try {
    const { data } = await axios.get('/agents/status')
    agentOnline.value = data.any_online ?? true
  } catch {
    agentOnline.value = true
  }
}

function subscribeAgentStatus() {
  if (!window.Echo) return
  window.Echo.channel('agents.status')
    .listen('.status.changed', (e) => {
      agentOnline.value = e.any_online ?? true
    })
}

function unsubscribeAgentStatus() {
  window.Echo?.leave('agents.status')
}

function onNewMessage() {
  if (!isOpen.value) unreadCount.value++
}
</script>

<style scoped>
.chat-window-wrapper {
  flex: 1;
  min-height: 0;
  display: flex;
}
/* ── Toggle Button ── */
.chat-toggle-btn {
  position: fixed;
  bottom: 20px;
  right: 16px;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(145deg, #ef4444, #ef4444);
  color: white;
  border: none;
  cursor: pointer;
  box-shadow: 0 6px 24px rgba(37, 99, 235, 0.5), 0 2px 8px rgba(0,0,0,0.12);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.22s cubic-bezier(.34,1.56,.64,1), box-shadow 0.2s ease;
  overflow: visible;
}

.chat-toggle-btn:hover {
  transform: scale(1.1) translateY(-2px);
  box-shadow: 0 10px 32px rgba(37, 99, 235, 0.55);
}

.toggle-ripple {
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  border: 2px solid rgba(59, 130, 246, 0.4);
  animation: ripple-out 2s ease-out infinite;
}

@keyframes ripple-out {
  0%   { transform: scale(0.9); opacity: 0.8; }
  100% { transform: scale(1.5); opacity: 0; }
}

.unread-badge {
  position: absolute;
  top: -3px;
  right: -3px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 700;
  min-width: 20px;
  height: 20px;
  padding: 0 4px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
  z-index: 1;
}

/* ── Popup ── */
.chat-popup {
  position: fixed;
  bottom: 90px;
  right: 16px;
  width: min(375px, calc(100vw - 32px));
  height: min(600px, calc(100svh - 110px));
  max-height: min(600px, calc(100svh - 110px));
  background: #fff;
  border-radius: 20px;
  box-shadow:
    0 20px 60px rgba(0, 0, 0, 0.15),
    0 4px 16px rgba(0, 0, 0, 0.08),
    0 0 0 1px rgba(0,0,0,0.04);
  z-index: 9998;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  font-family: "Poppins", sans-serif;
}

/* ── Header ── */
.popup-header {
  padding: 16px 18px;
  background: linear-gradient(135deg, #7A1016 35%,#B31217 70%,#ED1F24 100% );
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}

.header-brand {
  display: flex;
  align-items: center;
  gap: 11px;
}

.brand-avatar {
  width: 42px;
  height: 42px;
  background: rgba(255,255,255,0.18);
  border: 2px solid rgba(255,255,255,0.3);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.brand-name {
  margin: 0;
  font-size: 14px;
  font-weight: 700;
  color: white;
}

.brand-status {
  margin: 3px 0 0;
  font-size: 11px;
  color: rgba(255,255,255,0.8);
  display: flex;
  align-items: center;
  gap: 5px;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  display: inline-block;
}

.dot-online {
  background: #4ade80;
  animation: pulse-dot 2s infinite;
}

.dot-offline {
  background: #9ca3af;
}

@keyframes pulse-dot {
  0%,100% { box-shadow: 0 0 0 2px rgba(74,222,128,0.35); }
  50%     { box-shadow: 0 0 0 5px rgba(74,222,128,0.12); }
}

.close-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: rgba(255,255,255,0.15);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.15s, transform 0.15s;
  flex-shrink: 0;
}

.close-btn:hover {
  background: rgba(255,255,255,0.28);
  transform: rotate(90deg);
}

/* ── Form ── */
.chat-form-wrapper {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  scrollbar-width: thin;
  scrollbar-color: #e5e7eb transparent;
}

.form-hero {
  background: linear-gradient(180deg, #eff6ff 0%, #f8faff 60%, #ffffff 100%);
  padding: 26px 22px 20px;
  text-align: center;
  border-bottom: 1px solid #e8f0fe;
  min-height: 140px; 
  transition: all 0.3s ease;
}

.hero-wave {
  font-size: 36px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 60px;  
  transition: all 0.3s ease;
}

.hero-wave.no-anim {
  animation: none;
  font-size: unset;
}

.hero-wave img {
  display: block;
  margin: 0 auto 10px;
  animation: none;  /* matiin wave animation untuk img */
}

@keyframes wave {
  0%,100% { transform: rotate(0deg); }
  20%     { transform: rotate(-10deg); }
  40%     { transform: rotate(14deg); }
  60%     { transform: rotate(-8deg); }
  80%     { transform: rotate(10deg); }
}

.hero-title {
  margin: 0 0 6px;
  font-size: 18px;
  font-weight: 600;
  color: #111827;
}

.hero-subtitle {
  margin: 0;
  font-size: 12.5px;
  color: #6b7280;
  line-height: 1.55;
}

.form-fields {
  padding: 20px 20px 16px;
  display: flex;
  flex-direction: column;
  gap: 13px;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.hero-subtitle {
  margin: 0;
  font-size: 12.5px;
  color: #6b7280;
  line-height: 1.65;
}

.field-label {
  font-size: 11.5px;
  font-weight: 600;
  color: #374151;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

.field-input-wrap {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 10px 13px;
  border: 1.5px solid #e5e7eb;
  border-radius: 11px;
  background: #fafafa;
  transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
}

.field-input-wrap.focused {
  border-color: #3b82f6;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
}

.field-input-wrap.has-error {
  border-color: #ef4444;
  background: #fff8f8;
}

.field-icon {
  color: #9ca3af;
  flex-shrink: 0;
  transition: color 0.2s;
}

.field-input-wrap.focused .field-icon { color: #3b82f6; }
.field-input-wrap.has-error .field-icon { color: #ef4444; }

.field-input-wrap input {
  flex: 1;
  border: none;
  background: transparent;
  outline: none;
  font-size: 13px;
  color: #111827;
  font-family: inherit;
}

.field-input-wrap input::placeholder { color: #b0b7c3; }

.field-error {
  font-size: 11px;
  color: #ef4444;
  padding-left: 3px;
  display: flex;
  align-items: center;
  gap: 3px;
}

.submit-error {
  font-size: 12px;
  color: #dc2626;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 10px;
  padding: 9px 13px;
  margin: 0;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.start-btn {
  padding: 13px;
  background: linear-gradient(135deg, #7A1016 35%,#B31217 70%,#ED1F24 100% );
  color: white;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 0.02em;
  transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
  box-shadow: 0 4px 14px rgba(37,99,235,0.4);
  min-height: 46px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: inherit;
}

.start-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 7px 20px rgba(37,99,235,0.48);
}

.start-btn:active:not(:disabled) {
  transform: translateY(0);
}

.start-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.loading-dots {
  display: flex;
  gap: 5px;
  align-items: center;
}

.loading-dots span {
  width: 7px;
  height: 7px;
  background: white;
  border-radius: 50%;
  animation: dot-bounce 1.2s infinite ease-in-out;
}

.loading-dots span:nth-child(2) { animation-delay: 0.2s; }
.loading-dots span:nth-child(3) { animation-delay: 0.4s; }

@keyframes dot-bounce {
  0%,80%,100% { transform: scale(0.6); opacity: 0.5; }
  40% { transform: scale(1); opacity: 1; }
}

.privacy-note {
  margin: 0;
  font-size: 11px;
  color: #b0b7c3;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
}

/* ── Transitions ── */
.chat-slide-enter-active { animation: slide-up 0.32s cubic-bezier(0.34,1.4,0.64,1); }
.chat-slide-leave-active { animation: slide-down 0.2s ease-in; }

@keyframes slide-up {
  from { opacity: 0; transform: translateY(24px) scale(0.93); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes slide-down {
  from { opacity: 1; transform: translateY(0) scale(1); }
  to   { opacity: 0; transform: translateY(14px) scale(0.96); }
}

.btn-pop-enter-active { animation: pop-in 0.32s cubic-bezier(0.34,1.56,0.64,1) 0.1s both; }
.btn-pop-leave-active { animation: pop-out 0.18s ease-in; }

@keyframes pop-in  { from { opacity:0; transform: scale(0.4); } to { opacity:1; transform: scale(1); } }
@keyframes pop-out { from { opacity:1; transform: scale(1); } to { opacity:0; transform: scale(0.4); } }

.fade-enter-active { transition: opacity 0.25s ease; }
.fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
@media (max-width: 480px) {
  .chat-popup {
    right: 0;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 92svh;
    max-height: 92svh;
    border-radius: 20px 20px 0 0;
  }
  .chat-toggle-btn {
    bottom: 16px;
    right: 16px;
  }
}
</style>