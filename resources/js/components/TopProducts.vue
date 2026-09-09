<!-- src/components/TopProducts.vue -->
<template>
  <section class="tp-section" v-if="!loading && products.length > 0">

    <div class="tp-container">
      <h2 class="tp-title">Trending di Two Brothers</h2>
    </div>

    <div class="tp-clip">
      <div
        class="tp-scroller"
        ref="scrollerRef"
        :class="{ 'is-dragging': isDraggingRef, 'is-flat': isDesktop }"
        @mousedown="onMouseDown"
        @touchstart.passive="onTouchStart"
        @touchmove.passive="onTouchMove"
        @touchend.passive="onTouchEnd"
      >
        <div class="tp-track" ref="trackRef" :class="{ 'is-flat': isDesktop }" :style="trackStyle">
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
              <h3 class="product-name">
                {{ product.name.toLowerCase().replace(/\b\w/g, l => l.toUpperCase()) }}
              </h3>

              <div v-if="product.rating" class="product-rating">
                <span v-for="s in 5" :key="s" class="star" :class="{ filled: s <= Math.round(product.rating) }">★</span>
                <span class="rating-count">({{ product.rating_count || 0 }})</span>
              </div>

              <p v-if="product.stock && product.stock <= 15" class="stock-warning">
                <span class="stock-dot" />Hanya tersisa {{ product.stock }}
              </p>

              <div class="price-row">
                <span class="price-main">Rp {{ formatPrice(product.sell_price) }}</span>
                <span v-if="product.market_price && product.market_price > product.sell_price" class="price-strike">
                  {{ formatPrice(product.market_price) }}
                </span>
              </div>
            </div>

            <div class="card-footer">
              <button class="btn-cart" @click.stop="handleCardClick(product)">
              <svg
                class="cart-icon"
                width="11"
                height="11"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <circle cx="9" cy="20" r="1" />
                <circle cx="19" cy="20" r="1" />
                <path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 8H6" />
              </svg>

              <span v-if="product.has_variants">KERANJANG</span>
              <span v-else>+ KERANJANG</span>
            </button>
            </div>
          </div>
        </div>
      </div>

      <button v-if="!isDesktop" class="tp-arrow tp-arrow--prev" :class="{ 'is-hidden': currentIndex === 0 }" @click="prev">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
          <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <button v-if="!isDesktop" class="tp-arrow tp-arrow--next" :class="{ 'is-hidden': currentIndex >= maxIndex }" @click="next">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
          <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    <div v-if="!isDesktop" class="tp-container">
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
          <div v-for="n in 5" :key="n" class="tp-card skeleton-card">
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
import { ShoppingCart } from '@lucide/vue'

const GAP             = 8
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
    // Desktop (≥1024px) → grid flat, tanpa slider mechanics sama sekali.
    // Di bawah itu (mobile/tablet) → tetap slider draggable kayak sekarang.
    const isDesktop     = ref(false)

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
    const maxOffset  = ref(0)

    const snapOffset = computed(() => {
      const offset = currentIndex.value * (cardPx.value + GAP)
      return Math.min(offset, maxOffset.value)
    })

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

    function updateMaxOffset() {
      if (!scrollerRef.value || !trackRef.value || isDesktop.value) {
        maxOffset.value = 0
        return
      }

      const scroller = scrollerRef.value
      const track = trackRef.value
      const styles = window.getComputedStyle(scroller)

      const paddingLeft = parseFloat(styles.paddingLeft) || 0
      const paddingRight = parseFloat(styles.paddingRight) || 0
      const viewportWidth =
        scroller.clientWidth - paddingLeft - paddingRight

      maxOffset.value = Math.max(
        0,
        track.scrollWidth - viewportWidth
      )
    }

    function setTrackX(x) {
      if (isDesktop.value) return   // flat grid — biarin CSS flex-wrap yang atur posisi
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
      isDesktop.value = ww >= 1024

      let cols
      if (ww < 540)       cols = 1
      else if (ww < 800)  cols = 2
      else if (ww < 1024) cols = 3
      else                cols = 5
      visibleN.value = cols

      if (ww < 540) {
        cardPx.value = Math.max(112, Math.floor((ww - 58) / 2))
        cardHeight.value = 232
      } else if (ww < 800) {
        cardPx.value = 150
        cardHeight.value = 220
      } else if (ww < 1024) {
        cardPx.value = 180
        cardHeight.value = 250
      } else {
        cardPx.value = 160
        cardHeight.value = 221
      }

      if (isDesktop.value) {
        // Flat grid: reset transform & index, biar CSS wrap yang kerja, bukan JS.
        currentIndex.value = 0
        if (trackRef.value) trackRef.value.style.transform = ''
        return
      }

      if (currentIndex.value > maxIndex.value) {
        currentIndex.value = Math.max(0, maxIndex.value)
      }

      nextTick(() => {
        updateMaxOffset()
        setTrackX(-snapOffset.value)
      })
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
      const dt = now - lastTime
      const dx = clientX - lastX

      if (dt > 0) velocity = dx / dt

      lastX = clientX
      lastTime = now
      totalDeltaX = clientX - startX

      if (Math.abs(totalDeltaX) > CLICK_SLOP) {
        movedEnough = true
      }

      let raw = -snapOffset.value + totalDeltaX
      const minOff = -maxOffset.value

      if (raw > 0) {
        raw = raw * RESISTANCE
      } else if (raw < minOff) {
        raw = minOff + (raw - minOff) * RESISTANCE
      }

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
      if (isDesktop.value) return
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
      if (isDesktop.value) return
      touchLocked = false
      touchStartY = e.touches[0].clientY
      dragStart(e.touches[0].clientX)
    }
    function onTouchMove(e) {
      if (isDesktop.value) return
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
    function onTouchEnd() {
      if (isDesktop.value) return
      dragEnd()
    }

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
      visibleN, maxIndex, maxOffset, isDraggingRef, isDesktop,
      trackStyle, fillStyle,
      prev, next,
      handleCardClick,
      productSlug,  
      onMouseDown, onTouchStart, onTouchMove, onTouchEnd,
      formatPrice, photoUrl,
      ShoppingCart,
    }
  }
}
</script>

<style scoped>
.tp-section { padding: 58px 0 48px; font-family: "Poppins", sans-serif; overflow-x: clip; background: #BD2028; }
.tp-container { width: 100%; max-width: 1180px; margin: 0 auto; padding: 0 16px; }
.tp-title { color: #fff; text-align: center; font-weight: 600; font-size: 1.8rem; line-height: 1.25; margin: 0 0 26px; }
.tp-clip { position: relative; width: 100%; }
.tp-scroller { overflow: hidden; padding-left: max(16px, calc((100% - 1180px) / 2 + 16px)); padding-right: 16px; user-select: none; -webkit-user-select: none; cursor: grab; }
.tp-scroller.is-dragging { cursor: grabbing; }
.tp-scroller.is-flat { overflow: visible; padding-left: 16px; padding-right: 16px; cursor: default; user-select: auto; -webkit-user-select: auto; }
.tp-track { display: flex; gap: 8px; width: max-content; will-change: transform; }
.tp-track.is-flat { flex-wrap: nowrap; justify-content: center; width: 100%; transform: none !important; }
.tp-card { flex-shrink: 0; background: #fff; color: #111; border-radius: 5px; overflow: hidden; display: flex; flex-direction: column; cursor: pointer; box-sizing: border-box; transition: transform 0.2s, box-shadow 0.2s; }
.tp-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,.16); }
.tp-scroller.is-dragging .tp-card:hover { transform: none; box-shadow: none; }
.card-img-wrap { height: 55%; margin: 4px 4px 0; border-radius: 3px; overflow: hidden; aspect-ratio: 1 / 1; background: #eee; flex-shrink: 0; }
.card-img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .35s; }
.tp-card:hover .card-img { transform: scale(1.04); }
.tp-scroller.is-dragging .tp-card:hover .card-img { transform: none; }
.card-img-empty { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #aaa; }
.card-img-empty svg { width: 2rem; height: 2rem; }
.card-body { padding: 5px 5px 0; display: flex; flex-direction: column; gap: 1px; flex: 1; min-height: 0; text-align: center; }
.product-name { margin: 0; padding: 0; min-height: 0; font-size: .65rem; font-weight: 700; line-height: 1.55; color: #111; text-align: center; }
.product-rating { display: flex; align-items: center; gap: 1px; justify-content: center; }
.star { font-size: .52rem; color: #d5d5d5; }
.star.filled { color: #f5b800; }
.rating-count { font-size: .48rem; color: #777; margin-left: 2px; }
.stock-warning { display: flex; align-items: center; gap: 3px; justify-content: center; margin: 0; font-size: .48rem; color: #d98b00; font-weight: 600; }
.stock-dot { width: 4px; height: 4px; border-radius: 50%; background: #d98b00; flex-shrink: 0; }
.price-row { display: flex; gap: 3px; align-items: center; justify-content: center; margin-top: auto; padding: 1px 0 2px; }
.price-main { font-size: .72rem; line-height: 1.1; font-weight: 700; color: #111; }
.price-strike { font-size: .48rem; color: #888; text-decoration: line-through; }
.card-footer {
  padding: 5px 12px 8px;
}
.btn-cart {
  width: 100%;
  min-height: 20px;
  background: #BD2028;
  color: #fff;
  border: none;
  border-radius: 3px;
  padding: 8px 2px;
  font-size: .58rem;
  line-height: 1.1;
  font-family: "Poppins", sans-serif;
  font-weight: 600;
  letter-spacing: 0;
  cursor: pointer;
  transition: opacity .15s, background .15s;

  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
}
.btn-cart:hover { background: #a91d24; }
.btn-cart:active { opacity: .85; }
.cart-icon {
  width: 14px;
  height: 14px;
  flex: 0 0 14px;
  display: block;
  position: relative;
  top: -2px;
}
.tp-arrow { position: absolute; top: 50%; transform: translateY(-50%); z-index: 20; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #ddd; border-radius: 50%; color: #333; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,.12); padding: 0; }
.tp-arrow:hover { background: #f5f5f5; }
.tp-arrow.is-hidden { opacity: 0; pointer-events: none; }
.tp-arrow--prev { left: 8px; }
.tp-arrow--next { right: 8px; }
.tp-progress-bar { margin: 20px 0 0; position: relative; height: 2px; background: rgba(255,255,255,.28); border-radius: 2px; overflow: hidden; }
.tp-progress-fill { position: absolute; top: 0; height: 100%; background: rgba(255,255,255,.8); border-radius: 2px; transition: left .45s cubic-bezier(.4,0,.2,1), width .3s; }
.skeleton { background: linear-gradient(90deg,#f5d0d0 25%,#eebdbd 50%,#f5d0d0 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 4px; }
.skeleton-title { width: 220px; height: 24px; margin: 0 auto 26px; }
.skeleton-card { width: 112px; flex-basis: 112px; height: 172px !important; background: #fff !important; cursor: default; pointer-events: none; }
.skeleton-img { margin: 4px 4px 0; border-radius: 3px; aspect-ratio: 1/1; background: #eee; animation: pulse 1.4s ease-in-out infinite; }
.skeleton-line { height: 7px; border-radius: 4px; background: #eee; animation: pulse 1.4s ease-in-out infinite; margin: 4px 5px 2px; }
.skeleton-line.short { width: 55%; }
.skeleton-btn { height: 20px; border-radius: 3px; background: #eee; margin: auto 5px 5px; animation: pulse 1.4s ease-in-out infinite; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .5; } }
@media (max-width: 1023px) {
  .tp-section { padding-top: 38px; padding-bottom: 34px; }
  .tp-title { font-size: 1.35rem; margin-bottom: 22px; }
  .tp-scroller { padding-left: 16px; padding-right: 0; }
  .tp-track { gap: 8px; }
  .card-img-wrap { margin: 5px 5px 0; }
  .card-body { padding: 4px 5px 0; }
  .product-name { font-size: .72rem; }
  .price-main { font-size: .66rem; }
  .card-footer { padding: 4px 5px 6px; }
  .btn-cart { min-height: 22px; font-size: .5rem; }
  .tp-progress-bar { margin-left: 8px; margin-right: 8px; }
}
@media (max-width: 640px) {
  .tp-section { padding-top: 35px; }
  .tp-title { font-size: 1.15rem; margin-bottom: 20px; }
  .tp-container { padding-left: 24px; padding-right: 24px; }
  .tp-scroller { padding-left: 16px; padding-right: 0; }
  .tp-arrow { display: none; }
  .tp-track { gap: 8px; }
  .tp-progress-bar { margin-top: 18px; }
}
@media (max-width: 420px) {
  .tp-title { font-size: 1.05rem; }
  .tp-container { padding-left: 24px; padding-right: 24px; }
  .tp-scroller { padding-left: 16px; }
  .tp-track { gap: 7px; }
  .product-name { font-size: .68rem; }
  .price-main { font-size: .62rem; }
  .btn-cart { min-height: 21px; font-size: .46rem; }
}
</style>
