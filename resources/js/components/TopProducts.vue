<!-- src/components/TopProducts.vue -->
<template>
  <section class="tp-section" v-if="!loading && products.length > 0">

    <div class="tp-container">
      <h2 class="tp-title">Produk yang lagi Hype!</h2>
    </div>

    <div class="tp-clip">
      <div
        class="tp-scroller"
        ref="scrollerRef"
        :class="{ 'is-dragging': isDraggingRef }"
        @mousedown="onMouseDown"
        @touchstart.passive="onTouchStart"
        @touchmove.passive="onTouchMove"
        @touchend.passive="onTouchEnd"
      >
        <div class="tp-track" ref="trackRef" :style="trackStyle">
          <div
              v-for="product in products"
              :key="product.id"
              class="tp-card"
              :style="{ 
                  width: cardPx + 'px', 
                  flexBasis: cardPx + 'px', 
                  height: cardHeight ? cardHeight + 'px' : 'auto'  // ← tambah kondisi
              }"
          >
            <div class="card-img-wrap" @click="handleCardClick(product)">
              <img
                v-if="product.photo"
                :src="photoUrl(product.photo)"
                :alt="product.name"
                class="card-img"
                loading="lazy"
                draggable="false"
              />
              <div v-else class="card-img-empty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </div>
            </div>

            <div class="card-body" @click="handleCardClick(product)">
              <p class="product-name">{{ product.name }}</p>

              <div v-if="product.rating" class="product-rating">
                <span v-for="s in 5" :key="s" class="star" :class="{ filled: s <= Math.round(product.rating) }">★</span>
                <span class="rating-count">({{ product.rating_count || 0 }})</span>
              </div>

              <p v-if="product.stock && product.stock <= 15" class="stock-warning">
                <span class="stock-dot" />Hanya tersisa {{ product.stock }}
              </p>

              <div class="price-row">
                <span class="price-main">{{ formatPrice(product.sell_price) }}</span>
                <span v-if="product.market_price && product.market_price > product.sell_price" class="price-strike">
                  {{ formatPrice(product.market_price) }}
                </span>
              </div>
            </div>

            <div class="card-footer">
              <button class="btn-cart" @click.stop="handleCardClick(product)">
                <span v-if="product.has_variants">PILIH OPSI</span>
                <span v-else>+ KERANJANG</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <button class="tp-arrow tp-arrow--prev" :class="{ 'is-hidden': currentIndex === 0 }" @click="prev">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
          <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <button class="tp-arrow tp-arrow--next" :class="{ 'is-hidden': currentIndex >= maxIndex }" @click="next">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
          <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    <div class="tp-container">
      <div class="tp-progress-bar">
        <div class="tp-progress-fill" :style="fillStyle" />
      </div>
    </div>

  </section>

  <section class="tp-section" v-else-if="loading">
    <div class="tp-container">
      <div class="skeleton skeleton-title" />
    </div>
    <div class="tp-clip">
      <div class="tp-scroller">
        <div class="tp-track">
          <div v-for="n in 5" :key="n" class="tp-card skeleton-card" style="width:220px;flex-basis:220px">
            <div class="skeleton-img" />
            <div class="card-body">
              <div class="skeleton-line" />
              <div class="skeleton-line short" />
              <div class="skeleton-btn" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import { useRouter } from 'vue-router'
import axiosInstance from '../axios'

const GAP             = 14
const RESISTANCE      = 0.2
const VELOCITY_THRESH = 0.3
const CLICK_SLOP      = 6

export default {
  name: 'TopProducts',

  setup() {
    const router = useRouter()

    const products     = ref([])
    const loading      = ref(true)
    const scrollerRef  = ref(null)
    const trackRef     = ref(null)
    const currentIndex = ref(0)
    const cardPx       = ref(220)
    const cardHeight   = ref(500)
    const visibleN     = ref(5)
    const isDraggingRef = ref(false)

    // plain drag vars — tidak perlu reaktif
    let isDragging  = false
    let startX      = 0
    let lastX       = 0
    let lastTime    = 0
    let velocity    = 0
    let totalDeltaX = 0
    let movedEnough = false   // true kalau ini drag, false kalau ini click
    let touchStartY = 0
    let touchLocked = false

    const maxIndex   = computed(() => Math.max(0, products.value.length - visibleN.value))
    const snapOffset = computed(() => currentIndex.value * (cardPx.value + GAP))

    const trackStyle = computed(() => ({
      transition: isDraggingRef.value ? 'none' : 'transform 0.38s cubic-bezier(0.25, 0.46, 0.45, 0.94)',
      willChange: 'transform',
    }))

    const fillStyle = computed(() => {
      const total = products.value.length
      if (!total) return { width: '0%', left: '0%' }
      const w = Math.min(100, (visibleN.value / total) * 100)
      const l = maxIndex.value > 0 ? (currentIndex.value / maxIndex.value) * (100 - w) : 0
      return { width: w + '%', left: l + '%' }
    })

    function setTrackX(x) {
      if (trackRef.value) trackRef.value.style.transform = `translateX(${x}px)`
    }

    function snapTo(index) {
      isDragging = false
      isDraggingRef.value = false
      currentIndex.value = Math.max(0, Math.min(index, maxIndex.value))
      nextTick(() => setTrackX(-snapOffset.value))
    }

    function calcLayout() {
      if (!scrollerRef.value) return
      const w  = scrollerRef.value.clientWidth
      const ww = window.innerWidth
      let cols
      if (ww < 540)       cols = 1
      else if (ww < 800)  cols = 2
      else if (ww < 1024) cols = 3
      else                cols = 5
      visibleN.value = cols

      if (ww < 540)       { cardPx.value = Math.floor((ww - 50) / 2) - 7; cardHeight.value = null }
      else if (ww < 800)  { cardPx.value = 240; cardHeight.value = 400 }
      else if (ww < 1024) { cardPx.value = 220; cardHeight.value = 420 }
      else                { cardPx.value = 240; cardHeight.value = 500 }

      if (currentIndex.value > maxIndex.value)
        currentIndex.value = Math.max(0, maxIndex.value)
      nextTick(() => setTrackX(-snapOffset.value))
    }

    function prev() { snapTo(currentIndex.value - 1) }
    function next() { snapTo(currentIndex.value + 1) }

    function productSlug(product) {
        const base = product.name
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
        return `${base}-${product.id}`
    }

    function handleCardClick(product) {
        if (movedEnough) return
        router.push({ name: 'ProductDetail', params: { slug: productSlug(product) } })
    }

    // ── Drag ──
    function dragStart(clientX) {
      isDragging      = true
      movedEnough     = false
      isDraggingRef.value = true
      startX          = clientX
      lastX           = clientX
      lastTime        = performance.now()
      velocity        = 0
      totalDeltaX     = 0
    }

    function dragMove(clientX) {
      if (!isDragging) return
      const now = performance.now()
      const dt  = now - lastTime
      const dx  = clientX - lastX
      if (dt > 0) velocity = dx / dt
      lastX       = clientX
      lastTime    = now
      totalDeltaX = clientX - startX

      if (Math.abs(totalDeltaX) > CLICK_SLOP) movedEnough = true

      let raw = -snapOffset.value + totalDeltaX
      const minOff = -(maxIndex.value * (cardPx.value + GAP))
      if (raw > 0)           raw = raw * RESISTANCE
      else if (raw < minOff) raw = minOff + (raw - minOff) * RESISTANCE
      setTrackX(raw)
    }

    function dragEnd() {
      if (!isDragging) return
      isDragging = false
      isDraggingRef.value = false

      let target = currentIndex.value
      if (Math.abs(velocity) > VELOCITY_THRESH) {
        target = velocity < 0 ? currentIndex.value + 1 : currentIndex.value - 1
      } else {
        target = Math.round(-(-snapOffset.value + totalDeltaX) / (cardPx.value + GAP))
      }
      snapTo(target)
    }

    // ── Mouse ──
    function onMouseDown(e) {
      if (e.button !== 0) return
      dragStart(e.clientX)
      window.addEventListener('mousemove', onMouseMove)
      window.addEventListener('mouseup',   onMouseUp)
    }
    function onMouseMove(e) { dragMove(e.clientX) }
    function onMouseUp() {
      window.removeEventListener('mousemove', onMouseMove)
      window.removeEventListener('mouseup',   onMouseUp)
      dragEnd()
    }

    // ── Touch ──
    function onTouchStart(e) {
      touchLocked = false
      touchStartY = e.touches[0].clientY
      dragStart(e.touches[0].clientX)
    }
    function onTouchMove(e) {
      const dx = Math.abs(e.touches[0].clientX - startX)
      const dy = Math.abs(e.touches[0].clientY - touchStartY)
      if (!touchLocked) {
        if (dy > dx) {
          isDragging = false
          isDraggingRef.value = false
          setTrackX(-snapOffset.value)
          return
        }
        touchLocked = true
      }
      dragMove(e.touches[0].clientX)
    }
    function onTouchEnd() { dragEnd() }

    watch(products, () => nextTick(calcLayout))

    onMounted(async () => {
      try {
        const { data } = await axiosInstance.get('/homepage/top-products')
        products.value = data.data
      } catch (e) {
        console.error('Failed to load top products:', e)
      } finally {
        loading.value = false
        await nextTick()
        calcLayout()
        window.addEventListener('resize', calcLayout)
      }
    })

    onBeforeUnmount(() => {
      window.removeEventListener('resize',    calcLayout)
      window.removeEventListener('mousemove', onMouseMove)
      window.removeEventListener('mouseup',   onMouseUp)
    })

    const formatPrice = (p) =>
      new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 })
        .format(p).replace('Rp\u00a0', '')

    function photoUrl(path) {
      if (!path) return null
      if (path.startsWith('http://') || path.startsWith('https://')) return path
      return `${import.meta.env.VITE_APP_URL || window.location.origin}/storage/${path}`
    }

    return {
      products, loading, scrollerRef, trackRef,
      currentIndex, cardPx, cardHeight,
      visibleN, maxIndex, isDraggingRef,
      trackStyle, fillStyle,
      prev, next,
      handleCardClick,
      productSlug,  
      onMouseDown, onTouchStart, onTouchMove, onTouchEnd,
      formatPrice, photoUrl,
    }
  }
}
</script>

<style scoped>
.tp-section {
  padding: 74px 0 0;
  font-family: "Poppins", sans-serif;
  overflow-x: clip;
}
.tp-container {
  width: 100%;
  max-width: 1180px;
  margin: 0 auto;
  padding: 0 16px;
}
.tp-title {
  color: #BD2028;
  text-align: center;
  font-weight: 600;
  font-size: 1.8rem;
  margin: 0 0 18px;
}

.tp-clip {
  position: relative;
  width: 100%;
}
.tp-scroller {
  overflow: hidden;
  padding-left: max(16px, calc((100% - 1180px) / 2 + 16px));
  user-select: none;
  -webkit-user-select: none;
  cursor: grab;
}
.tp-scroller.is-dragging { cursor: grabbing; }

.tp-track {
  display: flex;
  gap: 14px;
  will-change: transform;
}

/* Card — TIDAK ada pointer-events:none saat drag */
.tp-card {
  flex-shrink: 0;
  background: #BD2028;
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}
.tp-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(189,32,40,0.28); }
.tp-scroller.is-dragging .tp-card:hover { transform: none; box-shadow: none; }

.card-img-wrap {
  margin: 10px 10px 0;
  border-radius: 8px;
  overflow: hidden;
  aspect-ratio: 1 / 1;
  background: #BD2028;
  flex-shrink: 0;
}
.card-img { width: 100%; height: 90%; object-fit: cover; display: block; transition: transform 0.35s; }
.tp-card:hover .card-img { transform: scale(1.05); }
.tp-scroller.is-dragging .tp-card:hover .card-img { transform: none; }
.card-img-empty { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
.card-img-empty svg { width: 2.5rem; height: 2.5rem; color: rgba(255,255,255,0.4); }

.card-body {
  padding: 10px 12px 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
  min-height: 161px;
}

.card-footer {
  padding: 8px 12px 14px;
}

.product-name {
  font-size: 1.10rem;
  font-weight: 500;
  text-align: center;
  color: #fff;
  line-height: 1.4;
  padding: 10px 20px;
  min-height: 4em;
}
.product-rating { display: flex; align-items: center; gap: 1px; justify-content: center; }
.star { font-size: 0.78rem; color: rgba(255,255,255,0.3); }
.star.filled { color: #FFD700; }
.rating-count { font-size: 0.7rem; color: rgba(255,255,255,0.75); margin-left: 3px; }

.stock-warning { display: flex; align-items: center; gap: 5px; font-size: 0.7rem; color: #FFD580; font-weight: 600; justify-content: center; }
.stock-dot { width: 7px; height: 7px; border-radius: 50%; background: #FFD580; flex-shrink: 0; }

.price-row { display: flex; gap: 6px; align-items: center; justify-content: center; margin-top: auto; padding-bottom: 4px; }
.price-main { font-size: 0.95rem; font-weight: 600; color: #fff; }
.price-strike { font-size: 0.72rem; color: rgba(255,255,255,0.5); text-decoration: line-through; }

.btn-cart {
  width: 100%;
  background: transparent;
  color: #fff;
  border: 1.5px solid rgba(255,255,255,0.8);
  border-radius: 6px;
  padding: 18px 4px;
  font-size: 0.8rem;
  font-family: "Poppins", sans-serif;
  font-weight: 500;
  letter-spacing: 0.06em;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-cart:hover  { background: rgba(255,255,255,0.15); }
.btn-cart:active { background: rgba(255,255,255,0.25); }

.tp-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 20;
  background: #fff;
  border: 1px solid #d0d0d0;
  border-radius: 50%;
  width: 44px; height: 44px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 10px rgba(0,0,0,0.10);
  transition: background 0.2s, border-color 0.2s, opacity 0.2s;
  padding: 0;
  color: #333;
}
.tp-arrow:hover { background: #BD2028; border-color: #BD2028; color: #fff; }
.tp-arrow.is-hidden { opacity: 0; pointer-events: none; }
.tp-arrow--prev { left: max(4px, calc((100% - 1180px) / 2 + 4px)); }
.tp-arrow--next { right: 10px; }

.tp-progress-bar {
  margin-top: 20px;
  position: relative;
  height: 2px;
  background: rgba(189,32,40,0.15);
  border-radius: 2px;
  overflow: hidden;
}
.tp-progress-fill {
  position: absolute;
  top: 0; height: 100%;
  background: #BD2028;
  border-radius: 2px;
  transition: left 0.45s cubic-bezier(0.4,0,0.2,1), width 0.3s;
}

.skeleton {
  background: linear-gradient(90deg, #f5d0d0 25%, #eebdbd 50%, #f5d0d0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 6px;
}
.skeleton-title { width: 220px; height: 24px; margin: 0 auto 18px; border-radius: 4px; }
.skeleton-card { background: #e8a0a0 !important; cursor: default; pointer-events: none; }
.skeleton-img { margin: 10px 10px 0; border-radius: 8px; aspect-ratio: 1/1; background: rgba(255,255,255,0.3); animation: pulse 1.4s ease-in-out infinite; }
.skeleton-line { height: 11px; border-radius: 6px; background: rgba(255,255,255,0.3); animation: pulse 1.4s ease-in-out infinite; margin-bottom: 6px; }
.skeleton-line.short { width: 55%; }
.skeleton-btn { height: 34px; border-radius: 6px; background: rgba(255,255,255,0.2); margin-top: 8px; animation: pulse 1.4s ease-in-out infinite; }

@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

@media (max-width: 720px) {
    .product-name {
        padding: 5px 10px;
        font-size: 0.9rem;
    }
}

@media (max-width: 640px) {
  .tp-arrow { display: none; }
  .tp-scroller { padding-left: 25px; }
  .tp-container { padding: 0 25px }
}

@media (max-width: 420px) {
    .price-main   { font-size: 0.88rem; }
    .price-row {
      margin-top: 10px;
    }
    .tp-track { gap: 20px; }
    .btn-cart     { padding: 8px 4px; font-size: 0.72rem; }
    .product-card {
        height: 370px;
    }
}
</style>