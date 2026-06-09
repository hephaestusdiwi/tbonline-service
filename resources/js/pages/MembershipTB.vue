<template>
  <div class="tbpoint-page">

    <!-- ===== DESKTOP LAYOUT ===== -->
    <div class="desktop-layout" v-if="!isMobile">

      <!-- Left Column: Topnav + Hero -->
      <div class="desktop-left">

        <!-- Desktop Topnav -->
        <div class="desktop-topnav">
          <button class="topnav-back" @click="goHome">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Kembali
          </button>

          <div class="topnav-brand">
            <img v-if="siteLogo" :src="siteLogo" :alt="siteName" class="topnav-logo-img" />
            <span v-else class="topnav-logo-text">{{ siteName }}</span>
          </div>

          <div class="topnav-right">
            <span class="topnav-badge">🏆 TB Point</span>
          </div>
        </div>

        <!-- Hero -->
        <div class="desktop-hero" ref="heroRef">
          <div class="hero-badge animate-item" style="--d:0ms">🏆 Program Loyalitas</div>
          <h1 class="hero-heading animate-item" style="--d:100ms">TB Point<br/><span>Membership</span></h1>
          <p class="hero-sub animate-item" style="--d:200ms">Dapatkan poin disetiap transaksi yang kamu lakukan.</p>

          <div class="desktop-benefits">
            <div
              class="benefit-row animate-item"
              v-for="(s, i) in slides" :key="s.title"
              :style="{ '--d': (300 + i * 80) + 'ms' }"
            >
              <div class="benefit-emoji">{{ s.emoji }}</div>
              <div>
                <div class="benefit-title">{{ s.title }}</div>
                <div class="benefit-desc">{{ s.description }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Action Card -->
      <div class="desktop-card-wrap">
        <div class="desktop-card animate-card">
          <div class="card-illustration">
            <div class="card-petals">
              <div v-for="i in 6" :key="i" class="petal" :style="petalStyle(i)"></div>
            </div>
            <img :src="slides[0].image" class="card-img" />
          </div>

          <h2 class="card-title">Jadi member itu mudah</h2>
          <p class="card-subtitle">Sudah punya membership? yuk cek poinnya<br/>Belum? yuk daftar sekarang!</p>

          <div class="card-actions">
            <button class="btn btn-primary" @click="openModal('check')">
              <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
              Lihat Poin Saya
            </button>
            <button class="btn btn-outline" @click="openModal('register')">
              <font-awesome-icon :icon="['fas', 'user-plus']" />
              Daftar Member
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- ===== MOBILE LAYOUT ===== -->
    <div class="mobile-layout" v-else>
      <!-- Hero -->
      <div class="mob-hero" :style="{ background: slides[currentSlide].gradient }">
        <div class="mob-topbar">
          <button class="back-btn-hero" @click="goHome">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
          <span class="mob-logo">TB Point</span>
          <div style="width:36px"></div>
        </div>

        <div class="mob-hero-art" @touchstart="handleTouchStart" @touchend="handleTouchEnd">
          <transition :name="slideDirection" mode="out-in">
            <div class="mob-art-inner" :key="currentSlide">
              <img :src="slides[currentSlide].image" class="mob-img-art" />
              <div class="sparkle sp1">✦</div>
              <div class="sparkle sp2">✦</div>
            </div>
          </transition>
        </div>
      </div>

      <!-- Bottom white panel -->
      <div class="mob-panel" :class="{ 'panel-ready': panelReady }">
        <div class="mob-dots">
          <button
            v-for="(s, i) in slides" :key="i"
            class="dot" :class="{ active: i === currentSlide }"
            @click="goToSlide(i)"
          ></button>
        </div>

        <transition :name="slideDirection" mode="out-in">
          <div class="mob-text" :key="currentSlide">
            <h2 class="mob-slide-title">{{ slides[currentSlide].title }}</h2>
            <p class="mob-slide-desc">{{ slides[currentSlide].description }}</p>
          </div>
        </transition>

        <div class="mob-actions" :class="{ 'actions-ready': panelReady }">
          <button class="btn btn-primary" @click="openModal('check')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Lihat Poin Saya
          </button>
          <button class="btn btn-ghost" @click="openModal('register')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M19 8v6M22 11h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Daftar Member Sekarang
          </button>
        </div>

        <p class="mob-tos">Dengan melanjutkan kamu menyetujui <a href="#">Syarat & Ketentuan</a> kami.</p>
        <div class="mob-safe"></div>
      </div>
    </div>

    <!-- ===== MODAL ===== -->
    <transition name="modal-fade">
      <div class="modal-overlay" v-if="modalOpen" @click.self="closeModal">
        <transition name="modal-slide">
          <div class="modal-sheet" v-if="modalOpen">
            <div class="modal-handle"></div>
            <div class="modal-content">
              <div class="modal-icon-wrap">
                <svg v-if="modalType==='check'" width="28" height="28" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="white" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                <svg v-else width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" stroke="white" stroke-width="2" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="white" stroke-width="2"/><path d="M19 8v6M22 11h-6" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
              </div>
              <h3 class="modal-title">{{ modalType === 'check' ? 'Lihat Poin' : 'Daftar Membership' }}</h3>
              <p class="modal-subtitle">
                {{ modalType === 'check'
                  ? 'Masukkan nomor HP yang terdaftar sebagai member untuk mengecek poin kamu.'
                  : 'Masukkan nomor HP kamu untuk mendaftar sebagai member dan dapatkan berbagai keuntungan eksklusif.' }}
              </p>
              <div class="input-group">
                <div class="input-prefix">+62</div>
                <input v-model="phoneNumber" type="tel" placeholder="812-3456-7890" class="phone-input" @input="formatPhone" maxlength="15"/>
              </div>
              <div class="modal-actions">
                <button class="btn btn-primary full-width" @click="modalType==='check' ? sendCheckWA() : sendRegisterWA()" :disabled="!isPhoneValid">
                  <font-awesome-icon :icon="['fab', 'whatsapp']" />
                  {{ modalType === 'check' ? 'Kirim ke WhatsApp Admin' : 'Daftar via WhatsApp' }}
                </button>
                <button class="btn-text" @click="closeModal">Batal</button>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useHead } from '@vueuse/head'
import { useSiteSettings } from '../composables/useSiteSettings'
import { useSeoMeta } from '../composables/useSeoMeta.js'

const router = useRouter()
const { siteName, siteLogo, fetchSettings, adminWhatsapp } = useSiteSettings()

useHead({
  title: computed(() =>
    siteName.value
      ? `TB Point | ${siteName.value}`
      : 'TB Point | TB Store'
  ),
})

useSeoMeta({
  title: 'TB Point',
  description: 'Dapatkan poin di setiap transaksi dan tukarkan dengan diskon eksklusif. Daftar membership TB Point sekarang dan nikmati berbagai keuntungan belanja di TB Store.',
})

const isMobile     = ref(false)
const currentSlide = ref(0)
const slideDirection = ref('slide-left')
const modalOpen    = ref(false)
const modalType    = ref('check')
const phoneNumber  = ref('')
const panelReady   = ref(false)
const heroRef      = ref(null)

let touchStartX = 0
let aoObserver  = null

const slides = [
  {
    emoji: '🏆',
    image: '/storage/vector/tbpoint.webp',
    gradient: 'linear-gradient(160deg, #BD2028 0%, #7a0000 100%)',
    title: 'Jadi member sekarang dan dapatkan penawaran spesial',
    description: 'Setiap pembelian kamu mendapatkan point yang bisa kamu gunakan dalam transaksi selanjutnya.',
  },
  {
    emoji: '🎁',
    image: '/storage/vector/tbpoint.webp',
    gradient: 'linear-gradient(160deg, #c0392b 0%, #6d1a1a 100%)',
    title: 'Nikmati potongan harga dengan menjadi member',
    description: 'Kamu bisa tukarkan point kamu di transaksi berikutnya.',
  },
  {
    emoji: '⭐',
    image: '/storage/vector/tbpoint.webp',
    gradient: 'linear-gradient(160deg, #922b21 0%, #5b0000 100%)',
    title: 'Makin banyak transaksi makin untung',
    description: 'Semakin banyak transaksi, maka akan makin banyak point juga yang kamu gunakan.',
  },
]

const isPhoneValid = computed(() => {
  const cleaned = phoneNumber.value.replace(/\D/g, '')
  return cleaned.length >= 9 && cleaned.length <= 13
})

// ── Navigation ──────────────────────────────────────────────
function goHome() {
  router.push('/')
}

// ── Responsive ──────────────────────────────────────────────
function checkMobile() {
  isMobile.value = window.innerWidth < 768
}

// ── Entrance animations ─────────────────────────────────────
function initDesktopAnimations() {
  nextTick(() => {
    const items = document.querySelectorAll('.animate-item, .animate-card')
    if (!items.length) return

    aoObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible')
          aoObserver.unobserve(entry.target)
        }
      })
    }, { threshold: 0.15 })

    items.forEach(el => aoObserver.observe(el))
  })
}

function initMobileAnimations() {
  setTimeout(() => { panelReady.value = true }, 150)
}

onMounted(async () => {
  await fetchSettings()
  checkMobile()
  window.addEventListener('resize', checkMobile)

  if (isMobile.value) {
    initMobileAnimations()
  } else {
    initDesktopAnimations()
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
  if (aoObserver) aoObserver.disconnect()
})

// ── Slides ───────────────────────────────────────────────────
function petalStyle(i) {
  const angle = (i - 1) * 60
  return { transform: `rotate(${angle}deg) translateY(-38px)`, animationDelay: `${i * 0.15}s` }
}

function goToSlide(i) {
  slideDirection.value = i > currentSlide.value ? 'slide-left' : 'slide-right'
  currentSlide.value = i
}

function handleTouchStart(e) { touchStartX = e.touches[0].clientX }
function handleTouchEnd(e) {
  const diff = touchStartX - e.changedTouches[0].clientX
  if (Math.abs(diff) > 40) {
    if (diff > 0 && currentSlide.value < slides.length - 1) { slideDirection.value = 'slide-left'; currentSlide.value++ }
    else if (diff < 0 && currentSlide.value > 0) { slideDirection.value = 'slide-right'; currentSlide.value-- }
  }
}

// ── Modal ────────────────────────────────────────────────────
function openModal(type) { modalType.value = type; phoneNumber.value = ''; modalOpen.value = true }
function closeModal() { modalOpen.value = false }
function formatPhone() {
  let val = phoneNumber.value.replace(/\D/g, '')
  if (val.startsWith('0')) val = val.slice(1)
  if (val.startsWith('62')) val = val.slice(2)
  phoneNumber.value = val
}
function buildWAUrl(msg) {
  return `https://wa.me/${adminWhatsapp.value}?text=${encodeURIComponent(msg)}`
}
function sendCheckWA() {
  if (!isPhoneValid.value) return
  window.open(buildWAUrl(`Halo Admin, saya ingin mengecek poin membership saya.\n\nNomor HP: +62${phoneNumber.value}\n\nMohon bantuannya, terima kasih! `), '_blank')
  closeModal()
}
function sendRegisterWA() {
  if (!isPhoneValid.value) return
  window.open(buildWAUrl(`Halo Admin, saya ingin mendaftar sebagai member TB Point.\n\nNomor HP: +62${phoneNumber.value}\n\nMohon bantuannya untuk proses pendaftaran, terima kasih! `), '_blank')
  closeModal()
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

* { box-sizing: border-box; }

.tbpoint-page {
  font-family: 'Plus Jakarta Sans', sans-serif;
  min-height: 100vh;
  overflow: hidden;
  background: #fff;
}

/* ============================================================
   DESKTOP ENTRANCE ANIMATIONS
   ============================================================ */
.animate-item {
  opacity: 0;
  transform: translateY(32px);
  transition: opacity 0.6s ease calc(var(--d, 0ms)), transform 0.6s cubic-bezier(0.22, 1, 0.36, 1) calc(var(--d, 0ms));
}
.animate-item.is-visible {
  opacity: 1;
  transform: translateY(0);
}

.animate-card {
  opacity: 0;
  transform: translateX(40px) scale(0.97);
  transition: opacity 0.7s ease 200ms, transform 0.7s cubic-bezier(0.22, 1, 0.36, 1) 200ms;
}
.animate-card.is-visible {
  opacity: 1;
  transform: translateX(0) scale(1);
}

/* ============================================================
   DESKTOP LAYOUT
   ============================================================ */
.desktop-layout {
  display: grid;
  grid-template-columns: 1fr 480px;
  min-height: 100vh;
}

/* Left column wraps topnav + hero, shares the red background */
.desktop-left {
  display: flex;
  flex-direction: column;
  background: linear-gradient(145deg, #BD2028 0%, #8b0000 100%);
  position: relative;
  overflow: hidden;
}

/* Decorative circles on left column */
.desktop-left::before {
  content: '';
  position: absolute;
  width: 500px; height: 500px;
  background: rgba(255,255,255,0.04);
  border-radius: 50%;
  top: -100px; right: -100px;
  pointer-events: none;
}
.desktop-left::after {
  content: '';
  position: absolute;
  width: 300px; height: 300px;
  background: rgba(255,255,255,0.05);
  border-radius: 50%;
  bottom: -60px; left: -60px;
  pointer-events: none;
}

/* ── Desktop Topnav ─────────────────────────────────────── */
.desktop-topnav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 64px;
  background: rgba(0, 0, 0, 0.12);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(8px);
  position: relative;
  z-index: 2;
  flex-shrink: 0;
}

.topnav-back {
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 10px;
  color: white;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 13px;
  font-weight: 600;
  padding: 8px 14px;
  cursor: pointer;
  transition: background 0.2s, transform 0.2s;
}
.topnav-back:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateX(-2px);
}
.topnav-back:active {
  transform: scale(0.97);
}

.topnav-brand {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
}

.topnav-logo-img {
  height: 32px;
  width: auto;
  object-fit: contain;
  filter: brightness(0) invert(1);
}

.topnav-logo-text {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 18px;
  font-weight: 900;
  color: white;
  letter-spacing: -0.03em;
}

.topnav-right {
  display: flex;
  align-items: center;
}

.topnav-badge {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 12px;
  font-weight: 700;
  color: white;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.25);
  padding: 6px 12px;
  border-radius: 20px;
}

/* ── Desktop Hero ───────────────────────────────────────── */
.desktop-hero {
  /* background handled by .desktop-left */
  background: transparent;
  padding: 64px 64px 80px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  position: relative;
  z-index: 1;
  flex: 1;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255,255,255,0.15);
  color: white;
  font-size: 13px; font-weight: 700;
  padding: 8px 16px; border-radius: 20px;
  border: 1px solid rgba(255,255,255,0.25);
  margin-bottom: 28px; width: fit-content;
}

.hero-heading {
  font-size: 56px; font-weight: 800;
  color: white; line-height: 1.1;
  margin: 0 0 20px; letter-spacing: -1px;
}
.hero-heading span { opacity: 0.75; }

.hero-sub {
  font-size: 16px; color: rgba(255,255,255,0.75);
  line-height: 1.7; margin: 0 0 48px; max-width: 440px;
}

.desktop-benefits {
  display: flex; flex-direction: column;
  gap: 20px; position: relative; z-index: 1;
}

.benefit-row {
  display: flex; align-items: flex-start; gap: 16px;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 16px; padding: 16px 20px;
  backdrop-filter: blur(4px);
  transition: background 0.2s, transform 0.2s;
}
.benefit-row:hover { background: rgba(255,255,255,0.15); transform: translateX(4px); }

.benefit-emoji { font-size: 28px; flex-shrink: 0; margin-top: 2px; }
.benefit-title { font-size: 15px; font-weight: 700; color: white; margin-bottom: 4px; }
.benefit-desc { font-size: 13px; color: rgba(255,255,255,0.65); line-height: 1.5; }

/* ── Desktop Card (Right Column) ────────────────────────── */
.desktop-card-wrap {
  background: #f7f7f7;
  display: flex; align-items: center; justify-content: center;
  padding: 48px 40px;
}

.desktop-card {
  background: white; border-radius: 28px;
  padding: 36px 32px;
  box-shadow: 0 8px 48px rgba(0,0,0,0.1);
  width: 100%; max-width: 360px; text-align: center;
}

.card-illustration {
  width: 160px; height: 160px;
  background: linear-gradient(160deg, #fff5f5, #fde8e8);
  border-radius: 50%;
  margin: 0 auto 24px;
  display: flex; align-items: center; justify-content: center;
  position: relative;
}

.card-petals { position: absolute; width: 160px; height: 160px; top: 0; left: 0; }

.card-img {
  width: 120px; height: 120px;
  object-fit: contain;
  position: relative; z-index: 2;
  filter: drop-shadow(0 8px 16px rgba(0,0,0,0.15));
  animation: floatArt 3s ease-in-out infinite;
}

.card-title { font-size: 22px; font-weight: 800; color: #1a1a1a; margin: 0 0 10px; letter-spacing: -0.3px; }
.card-subtitle { font-size: 14px; color: #888; line-height: 1.65; margin: 0 0 28px; }
.card-actions { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
.card-chips { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; }

/* ============================================================
   MOBILE ENTRANCE ANIMATIONS
   ============================================================ */
@keyframes heroImgIn {
  0%   { opacity: 0; transform: translateY(-30px) scale(0.85); }
  60%  { opacity: 1; transform: translateY(6px) scale(1.03); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}

.mob-panel {
  transform: translateY(60px);
  opacity: 0;
  transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.45s ease;
}
.mob-panel.panel-ready {
  transform: translateY(0);
  opacity: 1;
}

.mob-actions {
  opacity: 0;
  transform: translateY(16px);
  transition: opacity 0.4s ease 0.3s, transform 0.4s ease 0.3s;
}
.mob-actions.actions-ready {
  opacity: 1;
  transform: translateY(0);
}

/* ============================================================
   MOBILE LAYOUT
   ============================================================ */
.mobile-layout {
  display: flex; flex-direction: column;
  min-height: 100dvh; background: white;
}

.mob-hero {
  position: relative;
  height: 55dvh; min-height: 320px;
  display: flex; flex-direction: column;
  transition: background 0.6s ease;
  overflow: hidden;
}
.mob-hero::after {
  content: '';
  position: absolute; inset: 0;
  background: radial-gradient(ellipse at 60% 30%, rgba(255,255,255,0.12) 0%, transparent 70%);
  pointer-events: none;
}

.mob-topbar {
  display: flex; align-items: center;
  justify-content: space-between;
  padding: 22px 20px 0;
  position: relative; z-index: 2;
}

.back-btn-hero {
  width: 36px; height: 36px;
  background: rgba(255,255,255,0.2);
  border: none; border-radius: 50%; color: white;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; backdrop-filter: blur(8px);
  transition: background 0.2s, transform 0.2s;
}
.back-btn-hero:hover { background: rgba(255,255,255,0.3); transform: scale(1.1); }
.back-btn-hero:active { transform: scale(0.95); }

.mob-logo {
  color: white; font-size: 16px; font-weight: 800;
  display: flex; align-items: center; gap: 6px;
}

.mob-hero-art {
  flex: 1; display: flex; align-items: center;
  justify-content: center; position: relative; z-index: 2;
}

.mob-art-inner {
  position: relative; display: flex;
  align-items: center; justify-content: center;
}

.mob-img-art {
  width: 200px; height: 200px;
  object-fit: contain;
  filter: drop-shadow(0 20px 40px rgba(0,0,0,0.3));
  animation: heroImgIn 0.7s cubic-bezier(0.22, 1, 0.36, 1) both, floatArt 3s ease-in-out 0.7s infinite;
}

@keyframes floatArt {
  0%, 100% { transform: translateY(0px); }
  50%       { transform: translateY(-10px); }
}

.sparkle {
  position: absolute; color: white; font-size: 18px;
  animation: sparklePulse 2s ease-in-out infinite; opacity: 0.85;
}
.sp1 { top: -10px; right: -20px; animation-delay: 0s; font-size: 22px; }
.sp2 { bottom: 10px; left: -24px; animation-delay: 0.7s; font-size: 14px; }

@keyframes sparklePulse {
  0%, 100% { opacity: 0.6; transform: scale(1) rotate(0deg); }
  50%       { opacity: 1;   transform: scale(1.2) rotate(15deg); }
}

.mob-panel {
  flex: 1; background: white;
  border-radius: 28px 28px 0 0;
  margin-top: -44px; padding: 20px 28px 0;
  display: flex; flex-direction: column;
  position: relative; z-index: 3;
  box-shadow: 0 -4px 24px rgba(0,0,0,0.08);
}

.mob-dots {
  display: flex; justify-content: center;
  gap: 6px; padding: 0 0 16px; flex-shrink: 0;
}

.mob-text { text-align: center; flex: 1; }

.mob-slide-title {
  font-family: "Poppins", sans-serif;
  font-size: 22px; font-weight: 600; color: #1a1a1a;
  margin: 0 0 10px; line-height: 1.3; letter-spacing: -0.4px;
}

.mob-slide-desc {
  font-size: 14px; color: #888; line-height: 1.65; margin: 0;
}

.mob-actions {
  padding: 20px 0 0; display: flex;
  flex-direction: column; gap: 10px; flex-shrink: 0;
}

.mob-tos {
  text-align: center; font-size: 10px; color: #bbb;
  margin: 12px 0 0; line-height: 1.5;
}
.mob-tos a { color: #BD2028; text-decoration: none; font-weight: 600; }
.mob-safe { height: 28px; flex-shrink: 0; }

/* ============================================================
   SHARED COMPONENTS
   ============================================================ */
.btn {
  width: 100%; padding: 15px 20px; border-radius: 16px;
  font-family: "Poppins", sans-serif;
  font-size: 15px; font-weight: 600;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  gap: 8px; border: none; letter-spacing: 0.1px;
  transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.btn:active { transform: scale(0.97); }

.btn-primary {
  background: #BD2028; color: white;
  box-shadow: 0 6px 20px rgba(189,32,40,0.35);
}
.btn-primary:hover { background: #a81b22; box-shadow: 0 8px 24px rgba(189,32,40,0.45); transform: translateY(-1px); }
.btn-primary:disabled { background: #ccc; box-shadow: none; cursor: not-allowed; transform: none; }

.btn-outline {
  background: transparent; color: #BD2028; border: 1px solid #BD2028;
}
.btn-outline:hover { background: rgba(189,32,40,0.06); transform: translateY(-1px); }

.btn-ghost {
  background: transparent; color: #555; border: 1.5px solid #e0e0e0;
}
.btn-ghost:hover { background: #f7f7f7; transform: translateY(-1px); }

.full-width { width: 100%; }

.dot {
  width: 8px; height: 8px; border-radius: 50%; border: none;
  background: #e0e0e0; cursor: pointer; padding: 0;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.dot.active { background: #BD2028; width: 24px; border-radius: 4px; }

.chip {
  font-size: 12px; font-weight: 600; color: #BD2028;
  background: rgba(189,32,40,0.08); padding: 5px 12px;
  border-radius: 20px; border: 1px solid rgba(189,32,40,0.15);
}

.petal {
  position: absolute; width: 22px; height: 50px;
  background: rgba(189,32,40,0.08);
  border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
  top: 50%; left: 50%; transform-origin: center bottom;
  margin-left: -11px; margin-top: -25px;
  animation: petalPulse 3s ease-in-out infinite;
}
@keyframes petalPulse {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 0.85; }
}

/* ============================================================
   MODAL
   ============================================================ */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  display: flex; align-items: flex-end; justify-content: center;
  z-index: 1000;
}
.modal-sheet {
  width: 100%; max-width: 520px; background: white;
  border-radius: 28px 28px 0 0; padding: 16px 28px 40px;
}
.modal-handle {
  width: 40px; height: 4px; background: #e0e0e0;
  border-radius: 2px; margin: 0 auto 24px;
}
.modal-content {
  display: flex; flex-direction: column; align-items: center; text-align: center;
}
.modal-icon-wrap {
  width: 64px; height: 64px;
  background: linear-gradient(135deg, #BD2028, #e8464d);
  border-radius: 20px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 16px;
  box-shadow: 0 8px 24px rgba(189,32,40,0.35);
}
.modal-title { font-size: 20px; font-weight: 800; color: #1a1a1a; margin: 0 0 8px; }
.modal-subtitle { font-size: 13.5px; color: #888; line-height: 1.6; margin: 0 0 24px; }

.input-group {
  width: 100%; display: flex; align-items: center;
  border: 1px solid #f0f0f0; border-radius: 14px;
  overflow: hidden; background: #fafafa; margin-bottom: 16px;
  transition: border-color 0.2s;
}
.input-group:focus-within { border-color: #BD2028; background: white; }
.input-prefix {
  padding: 14px; font-size: 15px; font-weight: 700; color: #BD2028;
  background: rgba(189,32,40,0.06); border-right: 2px solid #f0f0f0;
}
.phone-input {
  flex: 1; border: none; outline: none; padding: 14px; font-size: 15px;
  font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 600;
  color: #1a1a1a; background: transparent; letter-spacing: 0.5px;
}
.phone-input::placeholder { color: #bbb; font-weight: 400; }

.benefit-chips { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-bottom: 16px; }
.modal-actions { width: 100%; display: flex; flex-direction: column; gap: 8px; }
.btn-text {
  background: none; border: none; color: #aaa;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 14px; font-weight: 600; cursor: pointer; padding: 8px;
}
.btn-text:hover { color: #888; }

/* ============================================================
   TRANSITIONS
   ============================================================ */
.slide-left-enter-active, .slide-left-leave-active,
.slide-right-enter-active, .slide-right-leave-active {
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-left-enter-from { opacity: 0; transform: translateX(50px); }
.slide-left-leave-to  { opacity: 0; transform: translateX(-50px); }
.slide-right-enter-from { opacity: 0; transform: translateX(-50px); }
.slide-right-leave-to   { opacity: 0; transform: translateX(50px); }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-slide-enter-active, .modal-slide-leave-active { transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
.modal-slide-enter-from, .modal-slide-leave-to { transform: translateY(100%); }
</style>