<template>
  <section class="promo-section" v-if="!loading && promotions.length > 0">

    <!-- Header -->
    <div class="promo-container">
      <div class="promo-header">
        <h2 class="promo-title">{{ title }}</h2>
      </div>
    </div>

    <!-- Slider -->
    <div class="promo-clip">
      <div
        class="promo-scroller"
        ref="scrollerRef"
        :class="{ 'is-dragging': dragging }"
        @mousedown="onMouseDown"
        @touchstart.passive="onTouchStart"
        @touchmove.passive="onTouchMove"
        @touchend="onTouchEnd"
      >
        <div
          class="promo-track"
          :style="trackStyle"
        >
          <a
            v-for="(item, i) in promotions"
            :key="item.id"
            :href="item.link"
            target="_blank"
            rel="noopener noreferrer"
            class="promo-card"
            :style="{ width: cardPx + 'px' }"   
            @click.prevent="onCardClick(item, $event)"
          >
            <div class="promo-img-wrap">
              <img
                :src="item.image"
                :alt="item.title || 'Promosi'"
                class="promo-img"
                loading="lazy"
                draggable="false"
              />
            </div>
          </a>
        </div>
      </div>

      <!-- Arrows -->
      <button
        class="promo-arrow promo-arrow--prev"
        @click="prev"
        :class="{ 'is-hidden': currentIndex === 0 }"
        aria-label="Sebelumnya"
      >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
          <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <button
        class="promo-arrow promo-arrow--next"
        @click="next"
        :class="{ 'is-hidden': currentIndex >= maxIndex }"
        aria-label="Berikutnya"
      >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
          <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    <!-- Progress bar -->
    <div class="promo-container">
      <div class="promo-progress-bar">
        <div class="promo-progress-fill" :style="fillStyle"></div>
      </div>
    </div>

  </section>

  <!-- Skeleton saat loading -->
  <section class="promo-section" v-else-if="loading">
    <div class="promo-container">
      <div class="promo-header">
        <div class="skeleton skeleton-title"></div>
      </div>
    </div>
    <div class="promo-clip">
      <div class="promo-scroller">
        <div class="promo-track">
          <div
            v-for="n in 3"
            :key="n"
            class="promo-card skeleton-card"
            :style="{ width: '360px', flexBasis: '360px' }"
          >
            <div class="promo-img-wrap skeleton"></div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import { usePromotions } from '../composables/usePromotions'

const CONTAINER_MAX    = 1280
const GAP              = 12
const RESISTANCE       = 0.2   // elastisitas di ujung slide (0=kaku, 1=bebas)
const VELOCITY_THRESH  = 0.3   // px/ms — flick cepat langsung ganti slide
const CLICK_SLOP       = 5     // px — dibawah ini dianggap klik bukan drag

export default {
  name: 'PromotionSlider',

  props: {
    title:      { type: String, default: 'Promosi Terkini' },
    seeAllLink: { type: String, default: '' },
  },

  setup() {
    const { promotions, loading, error, fetchPromotions } = usePromotions()

    const scrollerRef  = ref(null)
    const currentIndex = ref(0)
    const cardPx       = ref(360)
    const visibleN     = ref(3)

    // ── Drag state (plain vars, tidak perlu reaktif) ───────
    let isDragging  = false
    let startX      = 0
    let lastX       = 0
    let lastTime    = 0
    let velocity    = 0       // px/ms
    let totalDeltaX = 0       // total px sejak mousedown/touchstart
    let didDrag     = false

    // Nilai offset yang dirender langsung ke DOM (tanpa Vue reaktivitas
    // agar tidak ada overhead saat drag berlangsung)
    let trackEl     = null    // ref ke DOM element .promo-track

    // ── Computed (hanya untuk state non-drag) ──────────────
    const maxIndex = computed(() =>
      Math.max(0, promotions.value.length - 1)
    )

    const snapOffset = computed(() =>
      currentIndex.value * (cardPx.value + GAP)
    )

    // trackStyle hanya dipakai untuk snap animation (saat tidak drag)
    const isDraggingRef = ref(false)
    const trackStyle = computed(() => ({
      transition: isDraggingRef.value
        ? 'none'
        : 'transform 0.38s cubic-bezier(0.25, 0.46, 0.45, 0.94)',
      willChange: 'transform',
    }))

    const fillStyle = computed(() => {
      const total = promotions.value.length
      if (!total) return { width: '0%', left: '0%' }
      const w = Math.min(100, ((visibleN.value + 1) / total) * 100)
      const l = (currentIndex.value / total) * 100
      return { width: w + '%', left: l + '%' }
    })

    // ── Apply transform langsung ke DOM saat drag ──────────
    function setTrackX(x) {
      if (trackEl) trackEl.style.transform = `translateX(${x}px)`
    }

    function snapAnimateTo(index) {
      isDraggingRef.value = false   // aktifkan transition CSS
      currentIndex.value  = Math.max(0, Math.min(index, maxIndex.value))
      // Set transform via computed (trackStyle tidak include transform saat snap,
      // jadi set manual supaya konsisten)
      nextTick(() => {
        setTrackX(-snapOffset.value)
      })
    }

    // ── Layout ─────────────────────────────────────────────
    function calcLayout() {
      if (!scrollerRef.value) return
      const containerW = Math.min(window.innerWidth, CONTAINER_MAX)

      let cols, peekFrac
      
      if (containerW < 540) {
        cols = 1
        peekFrac = 0.15
        cardPx.value = Math.floor(containerW * 0.85)
      } else if (containerW < 800) {
        cols = 2
        peekFrac = 0.25
        const usableW = containerW - 16
        cardPx.value = Math.floor((usableW - GAP * cols) / (cols + peekFrac))
      } else {
        cols = 3
        peekFrac = 0.30
        const usableW = containerW - 16
        cardPx.value = Math.floor((usableW - GAP * cols) / (cols + peekFrac))
      }

      if (currentIndex.value > maxIndex.value)
        currentIndex.value = Math.max(0, maxIndex.value)

      nextTick(() => setTrackX(-snapOffset.value))
    }

    // ── Navigation ─────────────────────────────────────────
    function prev() { snapAnimateTo(currentIndex.value - 1) }
    function next() { snapAnimateTo(currentIndex.value + 1) }

    // ── Drag core ──────────────────────────────────────────
    function dragStart(clientX) {
      isDragging      = true
      didDrag         = false
      startX          = clientX
      lastX           = clientX
      lastTime        = performance.now()
      velocity        = 0
      totalDeltaX     = 0
      isDraggingRef.value = true   // matikan transition
    }

    function dragMove(clientX) {
      if (!isDragging) return

      const now   = performance.now()
      const dt    = now - lastTime
      const dx    = clientX - lastX

      // Hitung velocity (exponential moving average untuk halus)
      if (dt > 0) velocity = dx / dt

      lastX    = clientX
      lastTime = now
      totalDeltaX = clientX - startX

      if (Math.abs(totalDeltaX) > CLICK_SLOP) didDrag = true

      // Posisi track = snap saat ini + delta drag
      let rawOffset = -snapOffset.value + totalDeltaX

      // Resistance di ujung kiri dan kanan
      const minOffset = -(maxIndex.value * (cardPx.value + GAP))
      if (rawOffset > 0) {
        // Ujung kiri
        rawOffset = rawOffset * RESISTANCE
      } else if (rawOffset < minOffset) {
        // Ujung kanan
        rawOffset = minOffset + (rawOffset - minOffset) * RESISTANCE
      }

      setTrackX(rawOffset)
    }

    function dragEnd() {
      if (!isDragging) return
      isDragging = false

      // Tentukan slide target berdasarkan velocity atau posisi
      let targetIndex = currentIndex.value

      if (Math.abs(velocity) > VELOCITY_THRESH) {
        // Flick — ikuti arah velocity
        targetIndex = velocity < 0
          ? currentIndex.value + 1
          : currentIndex.value - 1
      } else {
        // Pelan — snap ke slide terdekat berdasarkan posisi akhir
        const currentOffset = -snapOffset.value + totalDeltaX
        const raw = -currentOffset / (cardPx.value + GAP)
        targetIndex = Math.round(raw)
      }

      snapAnimateTo(targetIndex)
    }

    // ── Mouse events ───────────────────────────────────────
    function onMouseDown(e) {
      if (e.button !== 0) return
      e.preventDefault()
      dragStart(e.clientX)
      window.addEventListener('mousemove', onMouseMove)
      window.addEventListener('mouseup',   onMouseUp)
    }
    function onMouseMove(e) { dragMove(e.clientX) }
    function onMouseUp()    {
      window.removeEventListener('mousemove', onMouseMove)
      window.removeEventListener('mouseup',   onMouseUp)
      dragEnd()
    }

    // ── Touch events ───────────────────────────────────────
    let touchLocked    = false
    let touchStartY    = 0

    function onTouchStart(e) {
      const t    = e.touches[0]
      touchLocked = false
      touchStartY = t.clientY
      dragStart(t.clientX)
    }
    function onTouchMove(e) {
      const t  = e.touches[0]
      const dx = Math.abs(t.clientX - startX)
      const dy = Math.abs(t.clientY - touchStartY)

      if (!touchLocked) {
        if (dy > dx) {
          // Vertikal — batalkan drag slider sepenuhnya
          isDragging          = false
          isDraggingRef.value = false
          setTrackX(-snapOffset.value)
          return
        }
        touchLocked = true
      }

      dragMove(t.clientX)
    }
    function onTouchEnd() { dragEnd() }

    // ── Cegah link terbuka saat drag ───────────────────────
    function onCardClick(item, e) {
      if (didDrag) { e.preventDefault(); return }
      window.open(item.link, '_blank', 'noopener,noreferrer')
    }

    // ── Lifecycle ──────────────────────────────────────────
    watch(promotions, () => nextTick(() => {
      calcLayout()
      trackEl = scrollerRef.value?.querySelector('.promo-track')
    }))

    onMounted(async () => {
      await fetchPromotions()
      await nextTick()
      trackEl = scrollerRef.value?.querySelector('.promo-track')
      calcLayout()
      window.addEventListener('resize', calcLayout)
    })

    onBeforeUnmount(() => {
      window.removeEventListener('resize',    calcLayout)
      window.removeEventListener('mousemove', onMouseMove)
      window.removeEventListener('mouseup',   onMouseUp)
    })

    return {
      promotions, loading, error,
      scrollerRef, currentIndex,
      cardPx, visibleN, maxIndex,
      trackStyle, fillStyle,
      isDraggingRef,
      prev, next,
      onMouseDown,
      onTouchStart, onTouchMove, onTouchEnd,
      onCardClick,
    }
  }
}
</script>

<style scoped>
.promo-section {
  padding: 36px 0 0;
  background: #fff;
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  overflow-x: hidden;
}

.promo-container {
  width: 100%;
  max-width: 1280px;
  margin-left: auto;
  margin-right: auto;
  padding-left: 16px;
  padding-right: 16px;
}

/* ── Header ───────────────────────────────────────────────── */
.promo-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}
.promo-title {
  font-size: 1rem;
  font-weight: 600;
  color: #BD2028;
  margin: 0;
}
.promo-see-all {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.75rem;
  font-weight: 700;
  color: #111;
  text-decoration: none;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  border-bottom: 1.5px solid #111;
  padding-bottom: 1px;
  transition: opacity 0.2s;
  white-space: nowrap;
}
.promo-see-all:hover { opacity: 0.5; }

/* ── Clip & Scroller ──────────────────────────────────────── */
.promo-clip {
  position: relative;
  width: 100%;
}
.promo-scroller {
  overflow: hidden;
  padding-left: max(16px, calc((100% - 1280px) / 2 + 16px));
  user-select: none;
  -webkit-user-select: none;
  cursor: grab;
}
.promo-scroller.is-dragging {
  cursor: grabbing;
}

/* ── Track & Cards ────────────────────────────────────────── */
.promo-track {
  display: flex;
  gap: 12px;
  will-change: transform;
}
.promo-card {
  flex-shrink: 0;
  display: block;
  border-radius: 6px;
  overflow: hidden;
  text-decoration: none;
  transition: box-shadow 0.25s, transform 0.25s;
}
.promo-card:hover {
  box-shadow: 0 8px 28px rgba(0,0,0,0.13);
  transform: translateY(-2px);
}
.promo-scroller.is-dragging .promo-card:hover {
  box-shadow: none;
  transform: none;
}
.promo-scroller.is-dragging .promo-card:hover .promo-img {
  transform: none;
}
.promo-img-wrap {
  width: 100%;
  aspect-ratio: 323 / 553;
  border-radius: 16px;
  background: #f0f0f0;
  overflow: hidden;
}
.promo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  user-select: none;
  -webkit-user-drag: none;
}
.promo-card:hover .promo-img {
  transform: scale(1.03);
}

/* ── Arrows ───────────────────────────────────────────────── */
.promo-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 20;
  background: #fff;
  border: 1px solid #d0d0d0;
  border-radius: 50%;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 10px rgba(0,0,0,0.10);
  transition: background 0.2s, border-color 0.2s, box-shadow 0.2s, opacity 0.2s;
}
.promo-arrow:hover {
  background: #111;
  border-color: #111;
  box-shadow: 0 4px 16px rgba(0,0,0,0.20);
}
.promo-arrow:hover svg path { stroke: #fff; }
.promo-arrow.is-hidden { opacity: 0; pointer-events: none; }
.promo-arrow--prev { left: max(4px, calc((100% - 1280px) / 2 + 4px)); }
.promo-arrow--next { right: 10px; }

/* ── Progress bar ─────────────────────────────────────────── */
.promo-container .promo-progress-bar {
  margin-top: 20px;
  position: relative;
  height: 2px;
  background: #e0e0e0;
  border-radius: 2px;
  overflow: hidden;
}
.promo-progress-fill {
  position: absolute;
  top: 0;
  height: 100%;
  background: #111;
  border-radius: 2px;
  transition: left 0.45s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ── Skeleton loader ──────────────────────────────────────── */
.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 6px;
}
.skeleton-title {
  width: 160px;
  height: 18px;
  border-radius: 4px;
}
.skeleton-card {
  pointer-events: none;
}
@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ── Mobile ───────────────────────────────────────────────── */
@media (max-width: 640px) {
  .promo-scroller {
    padding-left: 16px;
    /* Di mobile, touch swipe sudah handle, tidak perlu overflow scroll native */
    overflow: hidden;
    cursor: default;
  }
  .promo-track  { /* transition tetap via :style */ }
  .promo-arrow  { display: none; }
}
</style>