<template>
    <section class="product-section">
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
        <div v-else class="products-grid">
            <div
                v-for="product in products"
                :key="product.id"
                class="product-card"
                @click="goToProduct(product)"
            >
                <!-- Image Area -->
                <div class="card-img-wrap">
                    <!-- Discount Badge -->
                    <span v-if="getDiscount(product)" class="discount-badge">
                        -{{ getDiscount(product) }}%
                    </span>

                    <!-- Wishlist Button -->
                    <button
                        class="wishlist-btn"
                        :class="{ active: wishlist.has(product.id) }"
                        @click.stop="toggleWishlist(product)"
                        title="Wishlist"
                    >
                        <svg viewBox="0 0 24 24" width="15" height="15"
                            :fill="wishlist.has(product.id) ? 'currentColor' : 'none'"
                            stroke="currentColor" stroke-width="2.2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </button>

                    <!-- Product Image -->
                    <img
                        v-if="product.photo_1"
                        :src="photoUrl(product.photo_1)"
                        :alt="product.name"
                        class="card-img"
                        loading="lazy"
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
                    <p class="product-name">{{ product.name }}</p>

                    <!-- Stock Warning -->
                    <p v-if="product.stock_qty && product.stock_qty <= 15 && !isOutOfStock(product)" class="stock-warning">
                        <span class="stock-dot" />
                        Hanya tersisa {{ product.stock_qty }}
                    </p>

                    <!-- Price -->
                    <div class="price-row">
                        <span class="price-main" :class="{ discounted: product.market_price > product.sell_price }">
                            {{ formatPrice(product.sell_price) }}
                        </span>
                        <span
                            v-if="product.market_price && product.market_price > product.sell_price"
                            class="price-strike"
                        >{{ formatPrice(product.market_price) }}</span>
                    </div>

                    <!-- CTA Button -->
                    <button
                        class="btn-cart"
                        @click.stop="hasVariants(product) ? goToProduct(product) : addToCart(product)"
                        :disabled="isOutOfStock(product)"
                    >
                        <span v-if="isOutOfStock(product)">HABIS</span>
                        <span v-else-if="hasVariants(product)">PILIH OPSI</span>
                        <span v-else>+ KERANJANG</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && !error && products.length === 0" class="state-box">
            <p>Tidak ada produk ditemukan.</p>
        </div>
    </section>
</template>

<script>
import { cartStore } from '../store/cartStore'

export default {
    name: 'ProductList',

    setup() {
        return { cartStore }
    },

    props: {
        limit: { type: Number, default: 10 },
        searchQuery: { type: String, default: '' },
    },


    data() {
        return {
            products: [],
            loading: false,
            error: null,
            total: 0,
            wishlist: new Set(),
        }
    },

    watch: {                         
        searchQuery(val) {
            this.fetchProducts()
        }
    },

    mounted() {
        this.fetchProducts()
    },
    

    methods: {
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
                const incoming = Array.isArray(json.data) ? json.data : (json.data?.data ?? [])
                this.total = json.data?.total ?? incoming.length
                this.products = incoming
            } catch (e) {
                this.error = e.message
            } finally {
                this.loading = false
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
            cartStore.addItem(product)
        },

        toggleWishlist(product) {
            const next = new Set(this.wishlist)
            if (next.has(product.id)) {
                next.delete(product.id)
            } else {
                next.add(product.id)
            }
            this.wishlist = next
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
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

/* ─── Section ─── */
.product-section {
    max-width: 1280px;
    margin: 0 auto;
    padding: 56px 24px 80px;
    font-family: "Poppins", sans-serif;
}

/* ─── Header ─── */
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 36px;
    padding-bottom: 18px;
    border-bottom: 1.5px solid #ebebeb;
}

.section-title {
    font-family: "Poppins", sans-serif;
    font-size: 1rem;
    font-weight: 600;
    color: #BD2028;
    margin: 0;
}

.all-products-btn {
    padding: 8px 22px;
    background: #BD2028;
    color: #fff;
    border: none;
    font-family: "Poppins", sans-serif;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    cursor: pointer;
    border-radius: 5px;
    transition: background 0.2s, color 0.2s;
}

.all-products-btn:hover {
    background: #000000;
    color: #ffffff;
}

/* ─── Grid ─── */
.products-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
}

@media (max-width: 1100px) { .products-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 720px)  { .products-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; } }
@media (max-width: 420px)  { .products-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; } }

/* ─── Card ─── */
.product-card {
    height: 480px;
    background: #BD2028;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    transition: transform 0.18s, box-shadow 0.18s;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(189, 32, 40, 0.35);
}

/* ─── Image Area ─── */
.card-img-wrap {
    position: relative;
    margin: 10px 10px 0 10px;
    border-radius: 6px;
    overflow: hidden;
    aspect-ratio: 1 / 1;
    background: rgba(255, 255, 255, 0.15);
    flex-shrink: 0;
}

.card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s;
}

.product-card:hover .card-img {
    transform: scale(1.05);
}

.card-img-empty {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-img-empty svg {
    width: 2.5rem;
    height: 2.5rem;
    color: rgba(255, 255, 255, 0.5);
}

/* Discount Badge */
.discount-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    z-index: 3;
    background: #fff;
    color: #BD2028;
    font-size: 0.68rem;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 4px;
    letter-spacing: 0.04em;
    line-height: 1.4;
}

/* Wishlist Button */
.wishlist-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    z-index: 3;
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.85);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #c0c0c0;
    transition: color 0.2s, background 0.2s, transform 0.18s;
}

.wishlist-btn:hover {
    background: #ffffff;
    color: #BD2028;
    transform: scale(1.12);
}

.wishlist-btn.active {
    color: #BD2028;
    background: #ffffff;
}

/* Out of Stock */
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
    background: #000000;
    color: #ffffff;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    padding: 7px 18px;
    border-radius: 50px;
    text-transform: uppercase;
}

/* ─── Card Body ─── */
.card-body {
    font-family: "Poppins", sans-serif;
    padding: 10px 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 1;
}

.product-name {
    font-family: "Poppins", sans-serif;
    font-size: 1.10rem;
    padding: 20px 20px 10px 20px;
    font-weight: 500;
    text-align: center;
    color: #fff;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Stock Warning */
.stock-warning {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    font-size: 0.7rem;
    color: #FFD580;
    font-weight: 600;
    margin: 0;
}

.stock-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #FFD580;
    flex-shrink: 0;
}

/* Price */
.price-row {
    display: flex;
    gap: 6px;
    align-items: center;
    justify-content: center;
    margin-top: 2px;
}

.price-main {
    font-size: 1rem;
    font-weight: 400;
    text-align: center;
    color: #fff;
}

.price-main.discounted {
    color: #FFD580;
}

.price-strike {
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.5);
    text-decoration: line-through;
}

/* CTA Button */
.btn-cart {
    font-family: "Poppins", sans-serif;
    margin-top: auto;
    width: 100%;
    background: transparent;
    color: #fff;
    border: 1px solid #fff;
    border-radius: 4px;
    padding: 16px 4px;
    font-size: 0.85rem;
    font-weight: 500;
    letter-spacing: 0.06em;
    cursor: pointer;
    transition: background 0.15s;
}

.btn-cart:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.15);
    border-color: #fff;
}

.btn-cart:active:not(:disabled) {
    background: rgba(255, 255, 255, 0.25);
}

.btn-cart:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ─── State Boxes ─── */
.state-box {
    text-align: center;
    padding: 80px 24px;
    color: #999999;
    font-size: 0.9rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.state-box.error { color: #c00; }

.retry-btn {
    padding: 10px 28px;
    background: #BD2028;
    color: #ffffff;
    border: none;
    font-family: "Poppins", sans-serif;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    letter-spacing: 0.06em;
    border-radius: 50px;
    transition: background 0.2s;
}

.retry-btn:hover { background: #000000; }

/* ─── Skeleton ─── */
.skeleton-card {
    background: #d63a42;
}

.skeleton-img {
    margin: 10px 10px 0;
    border-radius: 6px;
    aspect-ratio: 1 / 1;
    background: rgba(255, 255, 255, 0.2);
    animation: pulse 1.4s ease-in-out infinite;
}

.skeleton-line {
    height: 11px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.2);
    animation: pulse 1.4s ease-in-out infinite;
    margin: 4px 12px;
}

.skeleton-line.short {
    width: 60%;
    margin-left: auto;
    margin-right: auto;
}

.skeleton-btn {
    height: 36px;
    border-radius: 4px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    margin: 6px 12px 0;
    animation: pulse 1.4s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.5; }
}

/* ─── Mobile ─── */
@media (max-width: 420px) {
    .product-name { font-size: 1rem; padding: 15px 10px 10px 10px;}
    .price-main   { font-size: 0.88rem; }
    .btn-cart     { padding: 8px 4px; font-size: 0.72rem; }
    .product-card {
        height: 370px;
    }
}
</style>