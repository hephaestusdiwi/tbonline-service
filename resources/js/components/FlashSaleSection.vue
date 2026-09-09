<template>
  <section
    class="fs-section"
    v-if="!loading && isLive && products.length > 0"
  >
    <div class="fs-container">
      <!-- HEADER FLASH SALE -->
      <div class="fs-header">
        <div class="fs-title-wrap">
          <img
            :src="flashSaleLogo"
            alt="Flash Sale"
            class="fs-logo"
            draggable="false"
          />

          <div class="fs-timer">
            <span class="fs-timer-box">
              {{ pad(countdown.hours) }}
            </span>

            <span>:</span>

            <span class="fs-timer-box">
              {{ pad(countdown.minutes) }}
            </span>

            <span>:</span>

            <span class="fs-timer-box">
              {{ pad(countdown.seconds) }}
            </span>
          </div>
        </div>

        <router-link
          to="/products"
          class="fs-see-all"
        >
          LIHAT SEMUA
        </router-link>
      </div>

      <!-- PRODUCT SCROLLER -->
      <div
        class="fs-scroller"
        ref="fsScroller"
        :class="{ 'is-dragging': isDragging }"
        @mousedown="onSliderMouseDown"
        @touchstart.passive="onSliderTouchStart"
        @touchmove.passive="onSliderTouchMove"
        @touchend.passive="onSliderTouchEnd"
        @touchcancel.passive="onSliderTouchEnd"
      >
        <div
          class="fs-track"
          ref="fsTrack"
          :style="trackStyle"
        >
          <div
            v-for="p in products"
            :key="p.id"
            class="fs-card"
            @click="handleProductClick(p)"
          >
          <!-- PRODUCT IMAGE -->
          <div class="fs-img-wrap">
            <img
              v-if="p.photo"
              :src="photoUrl(p.photo)"
              :alt="p.name"
              class="fs-img"
              loading="lazy"
              draggable="false"
            />

            <div
              v-else
              class="fs-img-empty"
            >
              <svg
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1.5"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                />
              </svg>
            </div>
          </div>

          <!-- PRODUCT INFO -->
          <div class="fs-body">
            <h3 class="fs-product-name">
              {{
                p.name
                  .toLowerCase()
                  .replace(/\b\w/g, l => l.toUpperCase())
              }}
            </h3>

            <div class="fs-price-row">
              <span class="fs-price-main">
                {{ formatPrice(effectivePrice(p)) }}
              </span>

              <span
                v-if="hasDiscount(p)"
                class="fs-price-strike"
              >
                {{ formatPrice(p.sell_price) }}
              </span>
            </div>
          </div>

          <!-- CART BUTTON -->
          <div class="fs-footer">
            <button
              class="fs-btn-cart"
              @click.stop="goToProduct(p)"
            >
              <svg
                width="13"
                height="13"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
              >
                <circle cx="9" cy="21" r="1" />
                <circle cx="20" cy="21" r="1" />
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
              </svg>
              <span>KERANJANG</span>
            </button>
          </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import axiosInstance from '../axios'
import flashSaleLogo from '../assets/flashsale_logo_cropped.png'

export default {
  name: 'FlashSaleSection',

  data() {
    return {
      loading: true,
      isActive: false,
      endsAt: null,
      products: [],
      countdown: {
        hours: 0,
        minutes: 0,
        seconds: 0,
      },

      _tickInterval: null,
      flashSaleLogo,

      sliderX: 0,
      currentIndex: 0,
      isDragging: false,
      startX: 0,
      lastX: 0,
      lastTime: 0,
      velocity: 0,
      totalDeltaX: 0,
      movedEnough: false,
      touchStartY: 0,
      touchLocked: false,
      maxOffset: 0,
      suppressClick: false,
    }
  },

  computed: {
    isLive() {
      return (
        this.isActive &&
        this.endsAt &&
        new Date(this.endsAt).getTime() > Date.now()
      )
    },
    trackStyle() {
      return {
        transform: `translateX(${this.sliderX}px)`,
        transition: this.isDragging
          ? 'none'
          : 'transform 0.38s cubic-bezier(0.25, 0.46, 0.45, 0.94)',
        willChange: 'transform',
      }
    },
  },

  async mounted() {
    window.addEventListener('resize', this.calcSliderLayout)

    try {
      const { data } = await axiosInstance.get('/flash-sale')

      this.isActive = data.is_active
      this.endsAt = data.ends_at
      this.products = data.products || []
      this.currentIndex = 0
      this.sliderX = 0
      this.currentIndex = 0
      this.sliderX = 0
    } catch (e) {
      console.error('Failed to load flash sale:', e)
    } finally {
      this.loading = false
      await this.$nextTick()
      this.calcSliderLayout()

      if (this.isLive) {
        this.tick()

        this._tickInterval = setInterval(
          this.tick,
          1000
        )
      }
    }
  },

  beforeUnmount() {
    clearInterval(this._tickInterval)
    window.removeEventListener('resize', this.calcSliderLayout)
    window.removeEventListener('mousemove', this.onSliderMouseMove)
    window.removeEventListener('mouseup', this.onSliderMouseUp)
  },

  methods: {
    isDesktop() {
      return window.innerWidth >= 1024
    },

    calcSliderLayout() {
      if (this.isDesktop()) {
        this.currentIndex = 0
        this.sliderX = 0
        this.maxOffset = 0

        if (this.$refs.fsTrack) {
          this.$refs.fsTrack.style.transform = ''
          this.$refs.fsTrack.style.transition = ''
        }
        return
      }

      const slider = this.$refs.fsScroller
      const track = this.$refs.fsTrack
      if (!slider || !track) return

      const styles = window.getComputedStyle(slider)
      const paddingLeft = parseFloat(styles.paddingLeft) || 0
      const paddingRight = parseFloat(styles.paddingRight) || 0
      const viewportWidth = slider.clientWidth - paddingLeft - paddingRight

      this.maxOffset = Math.max(0, track.scrollWidth - viewportWidth)

      const step = this.getCardStep()
      const maxIndex = Math.max(0, Math.ceil(this.maxOffset / step))

      if (this.currentIndex > maxIndex) {
        this.currentIndex = maxIndex
      }

      this.setSliderX(
        -Math.min(this.currentIndex * step, this.maxOffset),
        false
      )
    },

    handleProductClick(product) {
      if (this.suppressClick || this.movedEnough) {
        this.suppressClick = false
        this.movedEnough = false
        return
      }
      this.goToProduct(product)
    },

    getSliderBounds() {
      return {
        min: -Math.max(0, this.maxOffset),
        max: 0,
      }
    },

    clampSliderX(value) {
      const { min, max } = this.getSliderBounds()
      return Math.min(max, Math.max(min, value))
    },

    setSliderX(value, withTransition = false) {
      this.sliderX = this.clampSliderX(value)

      if (this.$refs.fsTrack) {
        this.$refs.fsTrack.style.transform =
          `translateX(${this.sliderX}px)`
        this.$refs.fsTrack.style.transition = withTransition
          ? 'transform 0.38s cubic-bezier(0.25, 0.46, 0.45, 0.94)'
          : 'none'
      }
    },

    getCardStep() {
      const track = this.$refs.fsTrack
      if (!track || !track.children.length) {
        return 188
      }

      const firstCard = track.children[0]
      const styles = window.getComputedStyle(track)
      const gap = parseFloat(
        styles.columnGap || styles.gap || '8'
      ) || 8

      return firstCard.getBoundingClientRect().width + gap
    },

    getMaxIndex() {
      const step = this.getCardStep()
      return Math.max(0, Math.ceil(this.maxOffset / step))
    },

    snapTo(index) {
      this.isDragging = false

      const maxIndex = this.getMaxIndex()
      this.currentIndex = Math.max(0, Math.min(index, maxIndex))

      const step = this.getCardStep()
      const offset = Math.min(
        this.currentIndex * step,
        this.maxOffset
      )

      this.setSliderX(-offset, true)
    },

    startSliderDrag(clientX, clientY = null) {
      if (this.isDesktop()) return

      this.isDragging = true
      this.movedEnough = false
      this.startX = clientX
      this.lastX = clientX
      this.lastTime = performance.now()
      this.velocity = 0
      this.totalDeltaX = 0
      this.touchStartY = clientY ?? 0
      this.touchLocked = false

      if (this.$refs.fsTrack) {
        this.$refs.fsTrack.style.transition = 'none'
      }
    },

    moveSliderDrag(clientX) {
      if (!this.isDragging || this.isDesktop()) return

      const now = performance.now()
      const dt = now - this.lastTime
      const dx = clientX - this.lastX

      if (dt > 0) {
        this.velocity = dx / dt
      }

      this.lastX = clientX
      this.lastTime = now
      this.totalDeltaX = clientX - this.startX

      if (Math.abs(this.totalDeltaX) > 6) {
        this.movedEnough = true
      }

      let raw =
        -Math.min(
          this.currentIndex * this.getCardStep(),
          this.maxOffset
        ) + this.totalDeltaX

      const minOffset = -this.maxOffset

      if (raw > 0) {
        raw = raw * 0.2
      } else if (raw < minOffset) {
        raw = minOffset + (raw - minOffset) * 0.2
      }

      this.setSliderX(raw, false)
    },

    endSliderDrag() {
      if (!this.isDragging) return

      this.isDragging = false

      if (this.movedEnough) {
        this.suppressClick = true
        window.setTimeout(() => {
          this.suppressClick = false
        }, 120)
      }

      let target = this.currentIndex
      const step = this.getCardStep()
      const currentOffset = Math.min(
        this.currentIndex * step,
        this.maxOffset
      )

      if (Math.abs(this.velocity) > 0.3) {
        target =
          this.velocity < 0
            ? this.currentIndex + 1
            : this.currentIndex - 1
      } else {
        target = Math.round(
          -( -currentOffset + this.totalDeltaX ) / step
        )
      }

      this.snapTo(target)
    },

    onSliderMouseDown(e) {
      if (this.isDesktop() || e.button !== 0) return

      this.startSliderDrag(e.clientX, null)
      window.addEventListener('mousemove', this.onSliderMouseMove)
      window.addEventListener('mouseup', this.onSliderMouseUp)
    },

    onSliderMouseMove(e) {
      this.moveSliderDrag(e.clientX)
    },

    onSliderMouseUp() {
      window.removeEventListener('mousemove', this.onSliderMouseMove)
      window.removeEventListener('mouseup', this.onSliderMouseUp)
      this.endSliderDrag()
    },

    onSliderTouchStart(e) {
      if (this.isDesktop() || !e.touches.length) return

      const touch = e.touches[0]
      this.touchStartY = touch.clientY
      this.startSliderDrag(touch.clientX, touch.clientY)
    },

    onSliderTouchMove(e) {
      if (
        this.isDesktop() ||
        !e.touches.length ||
        !this.isDragging
      ) return

      const touch = e.touches[0]
      const dx = Math.abs(touch.clientX - this.startX)
      const dy = Math.abs(touch.clientY - this.touchStartY)

      if (!this.touchLocked) {
        if (dy > dx) {
          this.isDragging = false
          this.setSliderX(
            -Math.min(
              this.currentIndex * this.getCardStep(),
              this.maxOffset
            ),
            false
          )
          return
        }

        this.touchLocked = true
      }

      this.moveSliderDrag(touch.clientX)
    },

    onSliderTouchEnd() {
      if (this.isDesktop()) return
      this.endSliderDrag()
    },

    tick() {
      const diff = Math.max(
        0,
        new Date(this.endsAt).getTime() - Date.now()
      )

      if (diff <= 0) {
        clearInterval(this._tickInterval)
        this.isActive = false
        return
      }

      const totalSeconds = Math.floor(diff / 1000)

      this.countdown = {
        hours: Math.floor(totalSeconds / 3600),

        minutes: Math.floor(
          (totalSeconds % 3600) / 60
        ),

        seconds: totalSeconds % 60,
      }
    },

    pad(n) {
      return String(n).padStart(2, '0')
    },

    hasDiscount(p) {
      return (
        p.flash_price !== null &&
        p.flash_price !== undefined &&
        p.flash_price < p.sell_price
      )
    },

    effectivePrice(p) {
      return this.hasDiscount(p)
        ? p.flash_price
        : p.sell_price
    },

    formatPrice(p) {
      return new Intl.NumberFormat(
        'id-ID',
        {
          style: 'currency',
          currency: 'IDR',
          maximumFractionDigits: 0,
        }
      )
        .format(p)
        .replace('Rp\u00a0', 'Rp ')
    },

    photoUrl(path) {
      if (!path) return null

      if (
        path.startsWith('http://') ||
        path.startsWith('https://')
      ) {
        return path
      }

      return `${
        import.meta.env.VITE_APP_URL ||
        window.location.origin
      }/storage/${path}`
    },

    productSlug(p) {
      const base = p.name
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')

      return `${base}-${p.product_id}`
    },

    goToProduct(p) {
      this.$router.push({
        name: 'ProductDetail',
        params: {
          slug: this.productSlug(p),
        },
      })
    },
  },
}
</script>

<style scoped>
/* =========================================================
   FLASH SALE SECTION
========================================================= */

.fs-section {
  padding: 28px 0;
  font-family: "Poppins", sans-serif;
  background: #f1f2f4;
}

.fs-container {
  width: 100%;
  max-width: 1060px;
  margin: 0 auto;
  padding: 0;
  box-sizing: border-box;
}


/* =========================================================
   HEADER
========================================================= */

.fs-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0;
  margin: 0 0 16px;
  background: transparent;
  border-radius: 0;
  gap: 10px;
}

.fs-title-wrap {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
}

.fs-logo {
  width: 145px;
  height: auto;
  display: block;
  flex: 0 0 auto;
}

.fs-timer {
  display: flex;
  align-items: center;
  gap: 2px;
  color: #111;
  font-weight: 700;
  font-size: 0.9rem;
  font-variant-numeric: tabular-nums;
}

.fs-timer-box {
  background: transparent;
  border-radius: 0;
  padding: 0;
}

.fs-see-all {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 110px;
  height: 28px;
  padding: 0;
  background: #BD2028;
  color: #fff;
  border: none;
  border-radius: 6px;
  font-family: "Poppins", sans-serif;
  font-size: 0.62rem;
  line-height: 1;
  font-weight: 500;
  letter-spacing: 0;
  text-decoration: none;
  text-transform: uppercase;
  white-space: nowrap;
  box-sizing: border-box;
}

.fs-see-all:hover {
  background: #a91d24;
  color: #fff;
}


/* =========================================================
   SCROLLER
========================================================= */
.fs-scroller {
  width: 100%;
  overflow: hidden;
  box-sizing: border-box;
  padding: 16px 0;
  border-radius: 0 0 12px 12px;
  touch-action: pan-y;
  user-select: none;
  -webkit-user-select: none;
  cursor: grab;
}

.fs-scroller.is-dragging {
  cursor: grabbing;
}

.fs-track {
  display: flex;
  flex-wrap: nowrap;
  width: max-content;
  gap: 8px;
  padding: 0;
  box-sizing: border-box;
  will-change: transform;
}

.fs-scroller::-webkit-scrollbar {
  display: none;
}



/* =========================================================
   PRODUCT CARD
   POLA SAMA DENGAN TOP PRODUCTS
========================================================= */

.fs-card {
  flex: 0 0 170px;
  width: 170px;
  height: 221px;
  background: #fff;

  color: #111;

  border-radius: 5px;

  overflow: hidden;

  display: flex;
  flex-direction: column;

  cursor: pointer;

  box-sizing: border-box;

  transition:
    transform 0.2s,
    box-shadow 0.2s;
}

.fs-card:hover {
  transform: translateY(-3px);

  box-shadow:
    0 8px 20px rgba(0, 0, 0, 0.16);
}


/* =========================================================
   PRODUCT IMAGE
========================================================= */

.fs-img-wrap {
  width: calc(100% - 8px);
  height: 152px;

  margin: 4px 4px 0;

  border-radius: 3px;

  overflow: hidden;

  background: #eee;

  flex: 0 0 auto;
}

.fs-img {
  width: 100%;
  height: 100%;

  display: block;

  object-fit: cover;

  transition: transform 0.35s;
}

.fs-card:hover .fs-img {
  transform: scale(1.04);
}

.fs-img-empty {
  width: 100%;
  height: 100%;

  display: flex;

  align-items: center;
  justify-content: center;

  color: #aaa;
}

.fs-img-empty svg {
  width: 2rem;
  height: 2rem;
}


/* =========================================================
   PRODUCT BODY
========================================================= */

.fs-body {
  padding: 5px 5px 0;

  display: flex;
  flex-direction: column;

  gap: 1px;

  flex: 1;

  min-height: 0;

  text-align: center;
}

.fs-product-name {
  margin: 0;
  padding: 0;

  min-height: 0;

  font-size: 0.65rem;
  font-weight: 700;

  line-height: 1.55;

  color: #111;

  text-align: center;

  display: -webkit-box;

  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;

  overflow: hidden;
}


/* =========================================================
   PRICE
========================================================= */

.fs-price-row {
  display: flex;

  gap: 3px;

  align-items: center;
  justify-content: center;

  margin-top: auto;

  padding: 1px 0 2px;
}

.fs-price-main {
  font-size: 0.72rem;
  line-height: 1.1;
  font-weight: 700;
  color: #000;
}

.fs-price-strike {
  font-size: 0.48rem;

  color: #888;

  text-decoration: line-through;
}


/* =========================================================
   FOOTER / CART BUTTON
========================================================= */

.fs-footer {
  padding: 5px 13px 8px;
  margin-top: auto;
}

.fs-btn-cart {
  width: 100%;
  min-height: 20px;
  background: #BD2028;
  color: #fff;
  border: none;
  border-radius: 3px;
  padding: 8px 2px;
  font-size: 0.58rem;
  line-height: 1.1;
  font-family: "Poppins", sans-serif;
  font-weight: 600;
  letter-spacing: 0;
  cursor: pointer;
  transition:
    opacity 0.15s,
    background 0.15s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
}

.fs-btn-cart svg {
  width: 13px;
  height: 13px;
  flex: 0 0 13px;
  display: block;
}

.fs-btn-cart:hover {
  background: #C81A1F;
}

.fs-btn-cart:active {
  opacity: 0.85;
}


/* =========================================================
   DESKTOP — match ProductList
========================================================= */

@media (min-width: 1024px) {
  .fs-scroller {
    overflow: visible;
    cursor: default;
    user-select: auto;
    -webkit-user-select: auto;
  }

  .fs-track {
    justify-content: center;
    width: 100%;
    transform: none !important;
    transition: none !important;
    will-change: auto;
  }

  .fs-card {
    flex-basis: 170px;
    width: 170px;
    height: 221px;
  }

  .fs-img-wrap {
    width: calc(100% - 8px);
    height: 55%;
    flex-basis: auto;
  }

  .fs-footer {
    padding: 5px 13px 8px;
    margin-top: auto;
  }

  .fs-btn-cart {
    min-height: 20px;
    padding: 8px 2px;
    font-size: 0.58rem;
    font-weight: 600;
  }
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 1023px) {

  .fs-container {
    max-width: none;
    padding: 0 16px;
  }

  /* Match ProductList: viewport full-width, 16px inner content inset.
     The negative margin cancels the container's horizontal padding so the
     scroller itself reaches the same outer edges as the page viewport. */
  .fs-scroller {
    width: calc(100% + 32px);
    margin-left: -16px;
    padding: 0 16px;
  }

  .fs-card {
    flex-basis: 180px;
    width: 180px;
    height: 260px;
  }

  .fs-img-wrap {
    width: calc(100% - 8px);
    height: 55%;
    flex-basis: auto;
    margin: 5px 5px 0;
  }

  .fs-body {
    padding: 4px 5px 0;
  }

  .fs-product-name {
    font-size: 0.72rem;
  }

  .fs-price-main {
    font-size: 0.66rem;
  }

  .fs-footer {
    padding: 4px 5px 6px;
  }

  .fs-btn-cart {
    min-height: 22px;
    font-size: 0.5rem;
  }
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 799px) {
  .fs-card {
    flex-basis: 160px;
    width: 160px;
    height: 252px;
  }

  .fs-img-wrap {
    width: calc(100% - 8px);
    height: 150px;
    aspect-ratio: auto;
    flex-basis: auto;
  }
}

@media (max-width: 539px) {
  .fs-card {
    flex-basis: 165px;
    width: 165px;
    height: 252px;
  }

  .fs-img-wrap {
    width: calc(100% - 8px);
    height: 150px;
    aspect-ratio: auto;
    flex-basis: auto;
  }

  .fs-scroller {
    gap: 8px;
  }
}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 420px) {

  .fs-see-all {
    width: 92px;
    height: 26px;
    font-size: 0.46rem;
  }

  .fs-scroller {
    gap: 7px;
  }

  .fs-product-name {
    font-size: 0.68rem;
  }

  .fs-price-main {
    font-size: 0.62rem;
  }

  .fs-btn-cart {
    min-height: 21px;
    font-size: 0.46rem;
  }
}
</style>