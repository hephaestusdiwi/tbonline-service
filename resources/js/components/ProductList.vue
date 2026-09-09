<template>
    <section class="product-section">
        <AppContainer>
        <!-- Section Header -->
        <div class="section-header">
            <h2 class="section-title">Semua Produk</h2>
            <button class="all-products-btn" @click="goToAllProducts">
                Lihat Semua
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="products-grid">
            <div v-for="i in limit" :key="i" class="product-card skeleton-card">
                <div class="skeleton-img" />
                <div class="card-body">
                    <div class="skeleton-line" />
                    <div class="skeleton-line short" />
                    <div class="skeleton-line short" />
                    <div class="skeleton-btn" />
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="state-box error">
            <p>{{ error }}</p>
            <button class="retry-btn" @click="fetchProducts">Coba Lagi</button>
        </div>

        <!-- Product Grid -->
        <div
            v-else
            class="products-slider"
            ref="productsSlider"
            :class="{ 'is-dragging': isDragging }"
            @mousedown="onSliderMouseDown"
            @touchstart.passive="onSliderTouchStart"
            @touchmove.passive="onSliderTouchMove"
            @touchend.passive="onSliderTouchEnd"
        >
            <div
                class="products-grid products-track"
                ref="productsTrack"
                :style="trackStyle"
            >
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="product-card"
                    @click="handleProductClick(product)"
                >
                <!-- Image Area -->
                <div class="card-img-wrap">
                    <!-- Discount Badge -->
                    <span v-if="getDiscount(product)" class="discount-badge">
                        -{{ getDiscount(product) }}%
                    </span>

                    <!-- Product Image -->
                    <img
                        v-if="product.photo_1"
                        :src="photoUrl(product.photo_1)"
                        :alt="product.name"
                        class="card-img"
                        loading="lazy"
                        draggable="false"
                        @error="onImgError"
                    />
                    <div v-else class="card-img-empty">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    <!-- Out of Stock Overlay -->
                    <div v-if="isOutOfStock(product)" class="out-of-stock">
                        <span>Stok Habis</span>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="card-body">
                    <p class="product-name">
                        {{
                            product.name
                                .toLowerCase()
                                .replace(/\b\w/g, l => l.toUpperCase())
                        }}
                    </p>
                    <div class="price-row">
                        <span class="price-main" :class="{ discounted: product.market_price > product.sell_price }">
                            {{ formatPrice(product.sell_price) }}
                        </span>
                        <span v-if="product.market_price && product.market_price > product.sell_price" class="price-strike">
                            {{ formatPrice(product.market_price) }}
                        </span>
                    </div>
                </div>

                <!-- card-footer: TARUH btn-cart di sini -->
                <div class="card-footer">
                    <button
                        class="btn-cart"
                        @click.stop="hasVariants(product) ? goToProduct(product) : addToCart(product)"
                        :disabled="isOutOfStock(product)"
                    >
                        <span v-if="isOutOfStock(product)">HABIS</span>
                        <template v-else>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            <span v-if="hasVariants(product)">KERANJANG</span>
                            <span v-else>KERANJANG</span>
                        </template>
                    </button>
                </div>
            </div>
        </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && !error && products.length === 0" class="state-box">
            <p>Tidak ada produk ditemukan.</p>
        </div>
        </AppContainer>
    </section>
</template>

<script>
import { cartStore } from '../store/cartStore'
import AppContainer from './AppContainer.vue'

const GAP = 8
const RESISTANCE = 0.2
const VELOCITY_THRESH = 0.3
const CLICK_SLOP = 6

export default {
    name: 'ProductList',

    components: { AppContainer },

    setup() {
        return { cartStore }
    },

    props: {
        limit: { type: Number, default: 12 },
        searchQuery: { type: String, default: '' },
    },

    data() {
        return {
            products: [],
            loading: false,
            error: null,
            total: 0,

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

    watch: {
        searchQuery() {
            this.currentIndex = 0
            this.sliderX = 0
            this.fetchProducts()
        }
    },

    mounted() {
        this.fetchProducts()
        window.addEventListener('resize', this.calcSliderLayout)
    },

    beforeUnmount() {
        window.removeEventListener('resize', this.calcSliderLayout)
        window.removeEventListener('mousemove', this.onSliderMouseMove)
        window.removeEventListener('mouseup', this.onSliderMouseUp)
    },

    computed: {
        trackStyle() {
            return {
                transition: this.isDragging
                    ? 'none'
                    : 'transform 0.38s cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                willChange: 'transform',
            }
        },
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
                if (this.$refs.productsTrack) {
                    this.$refs.productsTrack.style.transform = ''
                    this.$refs.productsTrack.style.transition = ''
                }
                return
            }

            const slider = this.$refs.productsSlider
            const track = this.$refs.productsTrack
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

            this.setSliderX(-Math.min(this.currentIndex * step, this.maxOffset), false)
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
            if (this.$refs.productsTrack) {
                this.$refs.productsTrack.style.transform = `translateX(${this.sliderX}px)`
                this.$refs.productsTrack.style.transition = withTransition
                    ? 'transform 0.38s cubic-bezier(0.25, 0.46, 0.45, 0.94)'
                    : 'none'
            }
        },

        getCardStep() {
            const track = this.$refs.productsTrack
            if (!track || !track.children.length) {
                return 168
            }

            const firstCard = track.children[0]
            const styles = window.getComputedStyle(track)
            const gap = parseFloat(styles.columnGap || styles.gap || '8') || 8
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
            const offset = Math.min(this.currentIndex * step, this.maxOffset)
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

            if (this.$refs.productsTrack) {
                this.$refs.productsTrack.style.transition = 'none'
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

            if (Math.abs(this.totalDeltaX) > CLICK_SLOP) {
                this.movedEnough = true
            }

            let raw = -Math.min(this.currentIndex * (this.getCardStep()), this.maxOffset) + this.totalDeltaX
            const minOffset = -this.maxOffset

            if (raw > 0) {
                raw = raw * RESISTANCE
            } else if (raw < minOffset) {
                raw = minOffset + (raw - minOffset) * RESISTANCE
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
            const currentOffset = Math.min(this.currentIndex * step, this.maxOffset)

            if (Math.abs(this.velocity) > VELOCITY_THRESH) {
                target = this.velocity < 0
                    ? this.currentIndex + 1
                    : this.currentIndex - 1
            } else {
                target = Math.round(-( -currentOffset + this.totalDeltaX ) / step)
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
            if (this.isDesktop() || !e.touches.length || !this.isDragging) return

            const touch = e.touches[0]
            const dx = Math.abs(touch.clientX - this.startX)
            const dy = Math.abs(touch.clientY - this.touchStartY)

            if (!this.touchLocked) {
                if (dy > dx) {
                    this.isDragging = false
                    this.setSliderX(-Math.min(this.currentIndex * this.getCardStep(), this.maxOffset), false)
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

        async fetchProducts() {
            this.loading = true
            this.error = null

            try {
                const params = new URLSearchParams({
                    published: 1,
                    in_stock: 1,
                    page: 1,
                    per_page: this.limit,
                    ...(this.searchQuery && { search: this.searchQuery }),
                })

                const res = await fetch(`/api/products?${params}`)
                if (!res.ok) throw new Error('Gagal memuat produk.')

                const json = await res.json()
                const incoming = Array.isArray(json.data)
                    ? json.data
                    : (json.data?.data ?? [])

                const availableProducts = incoming.filter(product => !this.isOutOfStock(product))
                this.total = json.data?.total ?? availableProducts.length
                this.products = availableProducts
                this.currentIndex = 0
                this.sliderX = 0

                await this.$nextTick()
                this.calcSliderLayout()
            } catch (e) {
                this.error = e.message
            } finally {
                this.loading = false
                await this.$nextTick()
                this.calcSliderLayout()
            }
        },

        photoUrl(path) {
            if (!path) return null
            if (path.startsWith('http://') || path.startsWith('https://')) return path
            const base = import.meta.env.VITE_APP_URL || window.location.origin
            return `${base}/storage/${path}`
        },

        hasVariants(product) {
            return Array.isArray(product.active_variants) && product.active_variants.length > 0
        },

        isOutOfStock(product) {
            if (this.hasVariants(product)) {
                return product.active_variants.every(v => v.stock_qty === 0)
            }
            return product.stock_qty === 0
        },

        productSlug(product) {
            const base = product.name
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-')
            return `${base}-${product.id}`
        },

        goToProduct(product) {
            this.$router.push({
                name: 'ProductDetail',
                params: { slug: this.productSlug(product) }
            })
        },

        addToCart(product) {
            cartStore.addItem({
                id: product.id,
                name: product.name,
                category: product.category,
                sell_price: product.sell_price,
                market_price: product.market_price,
                photo_1: this.photoUrl(product.photo_1),
                qty: 1,
                variant_id: null,
            })
        },

        goToAllProducts() {
            this.$router.push({ name: 'Products' })
        },

        formatPrice(val) {
            if (!val && val !== 0) return '-'
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            }).format(val)
        },

        getDiscount(product) {
            if (!product.market_price || product.market_price <= product.sell_price) return null
            return Math.round((1 - product.sell_price / product.market_price) * 100)
        },

        onImgError(e) {
            e.target.style.display = 'none'
        },
    }
}
</script>

<style scoped>
.product-section {
    background: #f1f2f4;
    margin: 0 auto;
    padding: 28px 8px 48px;
    font-family: "Poppins", sans-serif;
}

/* Header */
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin: 0 0 18px;
    padding: 0;
    border: 0;
}

.section-title {
    font-family: "Poppins", sans-serif;
    font-size: 1.6rem;
    line-height: 1.2;
    font-weight: 600;
    color: #bd2028;
    margin: 0;
}

.all-products-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 110px;
    height: 28px;
    padding: 0;
    background: #BD2028;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-family: "Poppins", sans-serif;
    font-size: 0.62rem;
    line-height: 1;
    font-weight: 500;
    letter-spacing: 0;
    text-decoration: none;
    text-transform: uppercase;
    white-space: nowrap;
    box-sizing: border-box;
    cursor: pointer;
    transition: opacity 0.15s, background 0.15s;
}

.all-products-btn:hover {
    background: #a91d24;
    color: #fff;
}

/* Viewport + track */
.products-slider {
    width: 100%;
    overflow: hidden;
    box-sizing: border-box;
    touch-action: pan-y;
    user-select: none;
    -webkit-user-select: none;
    cursor: grab;
}

.products-slider.is-dragging {
    cursor: grabbing;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 10px;
}

.products-slider .products-track {
    display: flex;
    flex-wrap: nowrap;
    width: max-content;
    grid-template-columns: none;
    gap: 8px;
    padding: 0;
    box-sizing: border-box;
    will-change: transform;
}

@media (min-width: 1024px) {
    .section-header {
        position: relative;
        width: min(1060px, calc(100% - 32px));
        margin: 0 auto 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .section-title {
        margin: 0;
        text-align: center;
    }

    .all-products-btn {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
    }

    .products-slider {
        overflow: visible;
        cursor: default;
        user-select: auto;
        -webkit-user-select: auto;
    }

    .products-slider .products-track {
        display: grid;
        grid-template-columns: repeat(6, 170px);
        grid-auto-rows: 221px;
        justify-content: center;
        width: 100%;
        gap: 8px;
        transform: none !important;
        transition: none !important;
        will-change: auto;
    }
}

/* Card — match TopProducts */
.product-card {
    flex-shrink: 0;
    background: #fff;
    color: #111;
    border: none;
    border-radius: 5px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    box-sizing: border-box;
    box-shadow: none;
    transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.16);
}

.products-slider.is-dragging .product-card:hover {
    transform: none;
    box-shadow: none;
}

/* Image — match TopProducts */
.card-img-wrap {
    height: 55%;
    margin: 4px 4px 0;
    border-radius: 3px;
    overflow: hidden;
    aspect-ratio: 1 / 1;
    background: #eee;
    flex-shrink: 0;
}

.card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.35s;
    -webkit-user-drag: none;
    user-select: none;
}

.product-card:hover .card-img {
    transform: scale(1.04);
}

.products-slider.is-dragging .product-card:hover .card-img {
    transform: none;
}

.card-img-empty {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #aaa;
}

.card-img-empty svg {
    width: 2rem;
    height: 2rem;
}

.discount-badge {
    position: absolute;
    top: 4px;
    left: 4px;
    z-index: 3;
    background: #fff;
    color: #bd2028;
    font-size: 0.5rem;
    font-weight: 800;
    padding: 2px 4px;
    border-radius: 2px;
    letter-spacing: 0.01em;
    line-height: 1.2;
}

/* Out of stock — retained but filtered before render */
.out-of-stock {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 4;
}

.out-of-stock span {
    background: #000;
    color: #fff;
    font-size: 0.5rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    padding: 4px 8px;
    border-radius: 30px;
    text-transform: uppercase;
}

/* Text — match TopProducts */
.card-body {
    padding: 5px 5px 0;
    display: flex;
    flex-direction: column;
    gap: 1px;
    flex: 1;
    min-height: 0;
    text-align: center;
}

.product-name {
    margin: 0;
    padding: 0;
    min-height: 0;
    font-family: "Poppins", sans-serif;
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

.price-row {
    display: flex;
    gap: 3px;
    align-items: center;
    justify-content: center;
    margin-top: auto;
    padding: 1px 0 2px;
}

.price-main {
    font-size: 0.72rem;
    line-height: 1.1;
    font-weight: 700;
    color: #111;
}

.price-main.discounted {
    color: #ed1f24;
}

.price-strike {
    font-size: 0.48rem;
    color: #888;
    text-decoration: line-through;
}

/* Button — match TopProducts */
.card-footer {
    padding: 5px 13px 8px;
    margin-top: auto;
}

.btn-cart {
    width: 100%;
    min-height: 20px;
    background: #bd2028;
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
    transition: opacity 0.15s, background 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.btn-cart:hover:not(:disabled) {
    background: #a91d24;
}

.btn-cart:active:not(:disabled) {
    opacity: 0.85;
}

.btn-cart svg {
    width: 14px;
    height: 14px;
    flex: 0 0 14px;
    display: block;
    position: relative;
    top: -2px;
}

.btn-cart:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* Desktop: same card dimensions as TopProducts (160 x 221) */
@media (min-width: 1024px) {
    .products-slider .product-card {
        width: 170px;
        min-width: 170px;
        flex-basis: 170px;
        height: 221px;
    }

    .products-slider .card-img-wrap {
        width: auto;
        height: 55%;
        aspect-ratio: 1 / 1;
    }
}

/* Tablet / mobile: same dimensions as TopProducts breakpoints */
@media (max-width: 1023px) {
    .product-section {
        padding: 28px 0 44px;
    }

    .section-header {
        margin: 0 16px 14px;
    }

    .products-slider {
        padding-left: 16px;
        padding-right: 0;
    }

    .products-slider .products-track {
        gap: 8px;
        padding: 0 0 6px;
    }

    .products-slider .product-card {
        flex-basis: 180px;
        width: 180px;
        min-width: 180px;
        height: 260px;
    }

    .products-slider .card-img-wrap {
        height: 55%;
        margin: 5px 5px 0;
        aspect-ratio: 1 / 1;
    }

    .product-name {
        font-size: 0.72rem;
    }

    .price-main {
        font-size: 0.66rem;
    }

    .card-footer {
        padding: 4px 5px 6px;
    }

    .btn-cart {
        min-height: 22px;
        font-size: 0.5rem;
    }
}

@media (max-width: 799px) {
    .products-slider .product-card {
        flex-basis: 150px;
        width: 150px;
        min-width: 150px;
        height: 220px;
    }
}

@media (max-width: 539px) {
    .products-slider .product-card {
        flex-basis: max(112px, calc((100vw - 58px) / 2));
        width: max(112px, calc((100vw - 58px) / 2));
        min-width: max(112px, calc((100vw - 58px) / 2));
        height: 232px;
    }

    .products-slider .products-track {
        gap: 8px;
    }
}

@media (max-width: 420px) {
    .products-slider .products-track {
        gap: 7px;
    }

    .product-name {
        font-size: 0.68rem;
    }

    .price-main {
        font-size: 0.62rem;
    }

    .btn-cart {
        min-height: 21px;
        font-size: 0.46rem;
    }
}

/* State Boxes */
.state-box {
    text-align: center;
    padding: 80px 24px;
    color: #999;
    font-size: 0.9rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.state-box.error {
    color: #c00;
}

.retry-btn {
    padding: 10px 28px;
    background: #bd2028;
    color: #fff;
    border: none;
    font-family: "Poppins", sans-serif;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    letter-spacing: 0.06em;
    border-radius: 50px;
    transition: background 0.2s;
}

.retry-btn:hover {
    background: #000;
}

/* Skeleton */
.skeleton-card {
    background: #fff;
    border: 1px solid #ececec;
}

.skeleton-img,
.skeleton-line,
.skeleton-btn {
    background: linear-gradient(
        90deg,
        #f0f0f0 25%,
        #e4e4e4 50%,
        #f0f0f0 75%
    );
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
}

.skeleton-img {
    margin: 5px 5px 0;
    border-radius: 3px;
    aspect-ratio: 1 / 1;
}

.skeleton-line {
    height: 9px;
    border-radius: 4px;
    margin: 4px 8px;
}

.skeleton-line.short {
    width: 60%;
    margin-left: auto;
    margin-right: auto;
}

.skeleton-btn {
    height: 24px;
    border-radius: 3px;
    margin: 6px 8px 0;
}

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>