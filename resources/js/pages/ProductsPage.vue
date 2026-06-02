<template>
    <div class="min-h-screen bg-white">
        <Navbar />
        <CartDrawer />

        <!-- Shop Layout -->
        <div class="products-page">

        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'sidebar-open': mobileFilterOpen }">
            <div class="sidebar-overlay" @click="mobileFilterOpen = false"></div>
            <div class="sidebar-inner">

                <div class="sidebar-header-mobile">
                    <span>Filters</span>
                    <button class="close-btn" @click="mobileFilterOpen = false">✕</button>
                </div>

                <div class="sidebar-title-row">
                    <span class="sidebar-title">Filters</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <line x1="4" y1="6" x2="20" y2="6"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                        <line x1="10" y1="18" x2="14" y2="18"/>
                    </svg>
                </div>

                <!-- Categories -->
                <div class="filter-section">
                    <div class="filter-section-header" @click="toggleSection('categories')">
                        <span>Categories</span>
                        <svg class="chevron" :class="{ rotated: openSections.categories }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13">
                            <polyline points="18 15 12 9 6 15"/>
                        </svg>
                    </div>
                    <div class="filter-section-body" v-show="openSections.categories">
                        <ul class="cat-list">
                            <li
                                v-for="cat in allCategories"
                                :key="cat.name"
                                class="cat-item"
                                :class="{ active: activeCategory === cat.name }"
                                @click="selectCategory(cat.name)"
                            >
                                <span class="cat-name">{{ cat.name }}</span>
                                <span class="cat-count" v-if="cat.count">{{ cat.count }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="filter-divider"></div>

                <!-- Price -->
                <div class="filter-section">
                    <div class="filter-section-header" @click="toggleSection('price')">
                        <span>Price</span>
                        <svg class="chevron" :class="{ rotated: openSections.price }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13">
                            <polyline points="18 15 12 9 6 15"/>
                        </svg>
                    </div>
                    <div class="filter-section-body" v-show="openSections.price">

                        <!-- Dual Range Slider -->
                        <div class="price-slider-wrap" ref="sliderWrap">
                            <div class="price-track">
                                <div
                                    class="price-fill"
                                    :style="{
                                        left: minThumbPct + '%',
                                        width: (maxThumbPct - minThumbPct) + '%'
                                    }"
                                ></div>
                            </div>
                            <input
                                type="range"
                                class="price-range price-range-min"
                                :min="PRICE_ABSOLUTE_MIN"
                                :max="PRICE_ABSOLUTE_MAX"
                                :step="priceStep"
                                v-model.number="sliderMin"
                                @input="onSliderMinInput"
                                @change="applyFilters"
                            />
                            <input
                                type="range"
                                class="price-range price-range-max"
                                :min="PRICE_ABSOLUTE_MIN"
                                :max="PRICE_ABSOLUTE_MAX"
                                :step="priceStep"
                                v-model.number="sliderMax"
                                @input="onSliderMaxInput"
                                @change="applyFilters"
                            />
                        </div>

                        <!-- Price Labels -->
                        <div class="price-badge-row">
                            <div class="price-badge">
                                <span class="price-badge-label">Min</span>
                                <span class="price-badge-value">{{ formatShort(sliderMin) }}</span>
                            </div>
                            <div class="price-badge-sep">—</div>
                            <div class="price-badge">
                                <span class="price-badge-label">Max</span>
                                <span class="price-badge-value">{{ sliderMax >= PRICE_ABSOLUTE_MAX ? '∞' : formatShort(sliderMax) }}</span>
                            </div>
                        </div>

                        <!-- Quick Presets -->
                        <div class="price-presets">
                            <button
                                v-for="preset in pricePresets"
                                :key="preset.label"
                                class="preset-chip"
                                :class="{ active: sliderMin === preset.min && sliderMax === preset.max }"
                                @click="applyPreset(preset)"
                            >{{ preset.label }}</button>
                        </div>

                    </div>
                </div>

                <div class="filter-divider"></div>

                <button class="apply-filter-btn" @click="applyFilters">Apply Filter</button>
                <button class="reset-link" @click="resetFilters">Reset</button>

            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">

            <!-- Breadcrumb -->
            <div class="breadcrumb-row">
                <span class="breadcrumb-item">Home</span>
                <span class="breadcrumb-sep">›</span>
                <span class="breadcrumb-item active">All Products</span>
            </div>

            <!-- Top Bar -->
            <div class="top-bar">
                <div class="top-bar-left">
                    <button class="mobile-filter-btn" @click="mobileFilterOpen = true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                            <line x1="4" y1="6" x2="20" y2="6"/>
                            <line x1="8" y1="12" x2="20" y2="12"/>
                            <line x1="12" y1="18" x2="20" y2="18"/>
                        </svg>
                        Filter
                    </button>
                    <span class="result-count" v-if="!loading">
                        Showing {{ products.length ? (currentPage - 1) * perPage + 1 : 0 }}–{{ Math.min(currentPage * perPage, total) }} of {{ total }} Products
                    </span>
                </div>
                <div class="top-bar-right">
                    <label class="sort-label">Sort by</label>
                    <select class="sort-select" v-model="sortBy" @change="applyFilters">
                        <option value="">Terbaru</option>
                        <option value="price_asc">Harga: Terendah</option>
                        <option value="price_desc">Harga: Tertinggi</option>
                        <option value="name_asc">Nama: A–Z</option>
                        <option value="name_desc">Nama: Z–A</option>
                        <option value="discount">Diskon Terbesar</option>
                        <option value="best_seller">Terlaris</option>
                    </select>
                    <div class="view-toggle">
                        <button :class="{ active: viewMode === 'grid4' }" @click="viewMode = 'grid4'" title="4 kolom">
                            <svg viewBox="0 0 16 16" width="12" height="12" fill="currentColor">
                                <rect x="0" y="0" width="6" height="6" rx="1"/><rect x="10" y="0" width="6" height="6" rx="1"/>
                                <rect x="0" y="10" width="6" height="6" rx="1"/><rect x="10" y="10" width="6" height="6" rx="1"/>
                            </svg>
                        </button>
                        <button :class="{ active: viewMode === 'grid3' }" @click="viewMode = 'grid3'" title="3 kolom">
                            <svg viewBox="0 0 22 16" width="12" height="12" fill="currentColor">
                                <rect x="0" y="0" width="5" height="6" rx="1"/><rect x="8.5" y="0" width="5" height="6" rx="1"/><rect x="17" y="0" width="5" height="6" rx="1"/>
                                <rect x="0" y="10" width="5" height="6" rx="1"/><rect x="8.5" y="10" width="5" height="6" rx="1"/><rect x="17" y="10" width="5" height="6" rx="1"/>
                            </svg>
                        </button>
                        <button :class="{ active: viewMode === 'grid2' }" @click="viewMode = 'grid2'" title="2 kolom">
                            <svg viewBox="0 0 14 16" width="12" height="12" fill="currentColor">
                                <rect x="0" y="0" width="5" height="16" rx="1"/><rect x="9" y="0" width="5" height="16" rx="1"/>
                            </svg>
                        </button>
                        <button :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'" title="List">
                            <svg viewBox="0 0 16 14" width="12" height="12" fill="currentColor">
                                <rect x="0" y="0" width="16" height="3.5" rx="1"/>
                                <rect x="0" y="5" width="16" height="3.5" rx="1"/>
                                <rect x="0" y="10" width="16" height="3.5" rx="1"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Active Filter Tags -->
            <div class="active-filters" v-if="hasActiveFilters">
                <span class="filter-tag" v-if="activeCategory !== 'Semua'">
                    {{ activeCategory }}
                    <button @click="selectCategory('Semua')">✕</button>
                </span>
                <span class="filter-tag" v-if="priceMin">
                    Min Rp{{ formatShort(priceMin) }}
                    <button @click="priceMin = null; sliderMin = PRICE_ABSOLUTE_MIN; applyFilters()">✕</button>
                </span>
                <span class="filter-tag" v-if="priceMax && priceMax < PRICE_ABSOLUTE_MAX">
                    Max Rp{{ formatShort(priceMax) }}
                    <button @click="priceMax = null; sliderMax = PRICE_ABSOLUTE_MAX; applyFilters()">✕</button>
                </span>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="state-box">
                <div class="spinner"></div>
                <p>Memuat produk...</p>
            </div>

            <!-- Error -->
            <div v-else-if="error" class="state-box error">
                <p>{{ error }}</p>
                <button class="retry-btn" @click="fetchProducts">Coba Lagi</button>
            </div>

            <!-- Search Result Heading -->
            <div v-if="searchKeyword" class="search-heading">
                <span>Hasil pencarian untuk</span>
                <strong>"{{ searchKeyword }}"</strong>
                <span class="search-count" v-if="!loading">{{ total }} produk ditemukan</span>
                <button class="clear-search-btn" @click="clearSearch">✕ Hapus pencarian</button>
            </div>

            <!-- Product Grid -->
            <div v-if="!loading && !error" class="product-grid" :class="viewMode">
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="product-card"
                >
                    <!-- Badges -->
                    <div class="badge-row">
                        <span v-if="isBestSeller(product)" class="badge badge-hot">BESTSELLER</span>
                        <span v-if="getDiscount(product)" class="badge badge-sale">-{{ getDiscount(product) }}%</span>
                        <span v-if="isLowStock(product)" class="badge badge-stock">FEW LEFT</span>
                    </div>

                    <!-- Wishlist -->
                    <button
                        class="wishlist-btn"
                        :class="{ active: wishlisted.has(product.id) }"
                        @click.stop="toggleWishlist(product.id)"
                        aria-label="Wishlist"
                    >
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </button>

                    <!-- Image -->
                    <div class="image-wrap" @click="goToProduct(product)">
                        <img
                            v-if="product.photo_1"
                            :src="photoUrl(product.photo_1)"
                            :alt="product.name"
                            class="product-img"
                            loading="lazy"
                            @error="onImgError"
                        />
                        <div v-else class="no-image">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" width="40" height="40">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="product-info" @click="goToProduct(product)">
                        <p class="product-brand" v-if="product.brand">{{ product.brand }}</p>
                        <p class="product-name">{{ product.name }}</p>
                        <div class="bottom-row">
                            <div class="price-block">
                                <span class="sell-price">{{ formatPrice(product.sell_price) }}</span>
                                <span v-if="product.market_price > product.sell_price" class="market-price">
                                    {{ formatPrice(product.market_price) }}
                                </span>
                            </div>
                            <div class="stars" v-if="getStarRating(product) > 0">
                                <svg
                                    v-for="i in 5"
                                    :key="i"
                                    viewBox="0 0 12 12"
                                    width="10"
                                    height="10"
                                    :fill="i <= getStarRating(product) ? '#BD2028' : '#E0E0E0'"
                                >
                                    <polygon points="6,1 7.5,4.5 11,5 8.5,7.5 9,11 6,9.5 3,11 3.5,7.5 1,5 4.5,4.5"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!loading && !error && products.length === 0" class="state-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="44" height="44">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <p>Tidak ada produk yang sesuai filter.</p>
                <button class="retry-btn" @click="resetFilters">Reset Filter</button>
            </div>

            <!-- Pagination -->
            <div v-if="!loading && totalPages > 1" class="pagination">
                <button class="page-btn" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">‹</button>
                <template v-for="p in paginationRange" :key="p">
                    <span v-if="p === '...'" class="page-ellipsis">…</span>
                    <button v-else class="page-btn" :class="{ active: p === currentPage }" @click="goToPage(p)">{{ p }}</button>
                </template>
                <button class="page-btn" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">›</button>
            </div>

        </main>
        </div>

        <CustomerChat />
        <FooterSection />
    </div>
</template>

<script>
import { cartStore } from '../store/cartStore'
import Navbar from '../components/Navbar.vue'
import CartDrawer from '../components/CartDrawer.vue'
import CustomerChat from '../components/chat/ChatWidget.vue'
import FooterSection from '../components/FooterSection.vue'

const PRICE_ABSOLUTE_MIN = 0
const PRICE_ABSOLUTE_MAX = 10_000_000
const PRICE_STEP         = 50_000

export default {
    name: 'ProductsPage',

    components: {
        Navbar, CartDrawer, CustomerChat, FooterSection
    },

    setup() {
        return { cartStore }
    },

    data() {
        return {
            // Price slider constants (exposed so template can access via this.*)
            PRICE_ABSOLUTE_MIN,
            PRICE_ABSOLUTE_MAX,
            priceStep: PRICE_STEP,

            products: [],
            allCategories: [],
            searchKeyword: '',
            activeCategory: 'Semua',
            priceMin: null,
            priceMax: null,
            // slider internal state (synced to priceMin/Max on apply)
            sliderMin: PRICE_ABSOLUTE_MIN,
            sliderMax: PRICE_ABSOLUTE_MAX,
            sortBy: '',
            viewMode: 'grid3',
            loading: false,
            error: null,
            currentPage: 1,
            perPage: 12,
            total: 0,
            wishlisted: new Set(),
            mobileFilterOpen: false,
            openSections: {
                categories: true,
                price: true,
            },
            pricePresets: [
                { label: '< 100rb',    min: PRICE_ABSOLUTE_MIN, max: 100_000 },
                { label: '100–500rb',  min: 100_000,            max: 500_000 },
                { label: '500rb–1jt',  min: 500_000,            max: 1_000_000 },
                { label: '1jt–5jt',   min: 1_000_000,          max: 5_000_000 },
            ],
        }
    },

    computed: {
        totalPages() {
            return Math.ceil(this.total / this.perPage)
        },

        hasActiveFilters() {
            return this.activeCategory !== 'Semua'
                || this.priceMin
                || (this.priceMax && this.priceMax < PRICE_ABSOLUTE_MAX)
        },

        // Percentage positions for the track fill
        minThumbPct() {
            return ((this.sliderMin - PRICE_ABSOLUTE_MIN) / (PRICE_ABSOLUTE_MAX - PRICE_ABSOLUTE_MIN)) * 100
        },
        maxThumbPct() {
            return ((this.sliderMax - PRICE_ABSOLUTE_MIN) / (PRICE_ABSOLUTE_MAX - PRICE_ABSOLUTE_MIN)) * 100
        },

        paginationRange() {
            const total = this.totalPages
            const cur = this.currentPage
            const delta = 2
            const range = []
            const rangeWithDots = []
            let l

            for (let i = 1; i <= total; i++) {
                if (i === 1 || i === total || (i >= cur - delta && i <= cur + delta)) {
                    range.push(i)
                }
            }

            for (let i of range) {
                if (l) {
                    if (i - l === 2) rangeWithDots.push(l + 1)
                    else if (i - l > 2) rangeWithDots.push('...')
                }
                rangeWithDots.push(i)
                l = i
            }

            return rangeWithDots
        }
    },

    mounted() {
        const q = this.$route?.query
        if (q) {
            if (q.category) this.activeCategory = q.category
            if (q.search)   this.searchKeyword = q.search
            if (q.page)     this.currentPage = parseInt(q.page) || 1
        }
        this.fetchProducts()
        this.fetchFilterOptions()
    },

    methods: {
        // ── Slider handlers ──────────────────────────────────────────────────
        onSliderMinInput() {
            if (this.sliderMin >= this.sliderMax - PRICE_STEP) {
                this.sliderMin = this.sliderMax - PRICE_STEP
            }
            this.priceMin = this.sliderMin > PRICE_ABSOLUTE_MIN ? this.sliderMin : null
        },
        onSliderMaxInput() {
            if (this.sliderMax <= this.sliderMin + PRICE_STEP) {
                this.sliderMax = this.sliderMin + PRICE_STEP
            }
            this.priceMax = this.sliderMax < PRICE_ABSOLUTE_MAX ? this.sliderMax : null
        },
        applyPreset(preset) {
            this.sliderMin = preset.min
            this.sliderMax = preset.max
            this.priceMin  = preset.min > PRICE_ABSOLUTE_MIN ? preset.min : null
            this.priceMax  = preset.max < PRICE_ABSOLUTE_MAX ? preset.max : null
            this.applyFilters()
        },
        clearSearch() {
            this.searchKeyword = ''
            this.$router.replace({ name: 'Products', query: {} })
            this.applyFilters()
        },

        // ── Data fetching ─────────────────────────────────────────────────────
        async fetchProducts() {
            this.loading = true
            this.error = null
            window.scrollTo({ top: 0, behavior: 'smooth' })

            try {
                const params = new URLSearchParams({
                    published: 1,
                    in_stock: 1,
                    page: this.currentPage,
                    per_page: this.perPage,
                })

                if (this.activeCategory !== 'Semua') params.append('category', this.activeCategory)
                if (this.priceMin)                  params.append('price_min', this.priceMin)
                if (this.priceMax)                  params.append('price_max', this.priceMax)
                if (this.sortBy)                    params.append('sort', this.sortBy)
                if (this.searchKeyword) params.append('search', this.searchKeyword)

                const res = await fetch(`/api/products?${params}`)
                if (!res.ok) throw new Error('Gagal memuat produk.')

                const json = await res.json()
                this.products = Array.isArray(json.data) ? json.data : (json.data?.data ?? [])
                this.total    = json.data?.total ?? this.products.length
            } catch (e) {
                this.error = e.message
            } finally {
                this.loading = false
            }
        },

        async fetchFilterOptions() {
            try {
                const catRes = await fetch('/api/products/categories')
                if (catRes.ok) { const j = await catRes.json(); this.allCategories = j.data ?? [] }
            } catch {}
        },

        // ── Helpers ───────────────────────────────────────────────────────────
        photoUrl(path) {
            if (!path) return null
            if (path.startsWith('http://') || path.startsWith('https://')) return path
            const base = import.meta.env.VITE_APP_URL || window.location.origin
            return `${base}/storage/${path}`
        },

        toggleSection(key) {
            this.openSections[key] = !this.openSections[key]
        },

        selectCategory(cat) {
            this.activeCategory = cat
            this.currentPage = 1
            this.fetchProducts()
        },

        applyFilters() {
            this.currentPage = 1
            this.fetchProducts()
        },

        resetFilters() {
            this.activeCategory = 'Semua'
            this.priceMin       = null
            this.priceMax       = null
            this.sliderMin      = PRICE_ABSOLUTE_MIN
            this.sliderMax      = PRICE_ABSOLUTE_MAX
            this.sortBy         = ''
            this.currentPage    = 1
            this.fetchProducts()
        },

        goToPage(page) {
            if (page < 1 || page > this.totalPages) return
            this.currentPage = page
            this.fetchProducts()
        },

        goToProduct(product) {
            this.$router.push({ name: 'ProductDetail', params: { id: product.id } })
        },

        addToCart(product) {
            this.cartStore.add(product)
        },

        toggleWishlist(id) {
            const next = new Set(this.wishlisted)
            next.has(id) ? next.delete(id) : next.add(id)
            this.wishlisted = next
        },

        isBestSeller(product) {
            return (product.qty_fast_moving || 0) >= 50
        },

        isLowStock(product) {
            return product.stock > 0 && product.stock <= 5
        },

        getDiscount(product) {
            if (!product.market_price || product.market_price <= product.sell_price) return null
            return Math.round((1 - product.sell_price / product.market_price) * 100)
        },

        getStarRating(product) {
            const qty = product.qty_fast_moving || 0
            if (qty >= 100) return 5
            if (qty >= 50)  return 4
            if (qty >= 20)  return 3
            if (qty >= 5)   return 2
            return qty > 0 ? 1 : 0
        },

        formatPrice(val) {
            if (!val && val !== 0) return '-'
            return new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR',
                minimumFractionDigits: 0, maximumFractionDigits: 0,
            }).format(val)
        },

        formatShort(val) {
            if (!val && val !== 0) return '0'
            if (val >= 1_000_000) return (val / 1_000_000).toFixed(val % 1_000_000 === 0 ? 0 : 1) + 'jt'
            if (val >= 1_000)     return (val / 1_000).toFixed(0) + 'rb'
            return val
        },

        onImgError(e) {
            e.target.style.display = 'none'
        },
    }
}
</script>

<style scoped>

/* ─── Root Layout ─── */
.products-page {
    display: flex;
    align-items: flex-start;
    max-width: 1380px;
    margin: 0 auto;
    padding: 28px 24px 80px;
    gap: 0;
    font-family: "Poppins", sans-serif;
    box-sizing: border-box;
}

/* ─── Sidebar ─── */
.sidebar {
    width: 220px;
    flex-shrink: 0;
    padding-right: 20px;
}

.sidebar-overlay { display: none; }
.sidebar-header-mobile { display: none; }

.sidebar-inner {
    position: sticky;
    top: 80px;
    background: #fff;
    border-radius: 14px;
    padding: 20px 18px 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
}

.sidebar-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.sidebar-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #BD2028;
}

/* ─── Filter Sections ─── */
.filter-section { margin-bottom: 2px; }

.filter-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    cursor: pointer;
    font-size: 0.82rem;
    font-weight: 600;
    color: #222;
    user-select: none;
}

.filter-section-header:hover { color: #000; }

.chevron { transition: transform 0.2s ease; color: #aaa; }
.chevron.rotated { transform: rotate(180deg); }

.filter-section-body { padding-bottom: 10px; }

.filter-divider {
    border: none;
    border-top: 1px solid #f0f0f0;
    margin: 2px 0 4px;
}

/* ─── Category List ─── */
.cat-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.cat-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.12s;
    font-size: 0.8rem;
    color: #333;
}

.cat-item:hover { background: #f5f5f5; }

.cat-item.active {
    background: #FFF0F0;
    color: #BD2028;
    font-weight: 600;
}

.cat-item.active::after {
    content: '';
    display: block;
    width: 3px;
    height: 14px;
    background: #BD2028;
    border-radius: 99px;
    flex-shrink: 0;
}

.cat-name { flex: 1; }

.cat-count {
    font-size: 0.68rem;
    color: #aaa;
    background: #f0f0f0;
    padding: 1px 7px;
    border-radius: 99px;
    margin-right: 8px;
}

.cat-item.active .cat-count {
    background: #FFD9D9;
    color: #BD2028;
}

/* ─── Price Slider ─── */
.price-slider-wrap {
    position: relative;
    height: 36px;
    display: flex;
    align-items: center;
    margin: 8px 4px 0;
}

.price-track {
    position: absolute;
    left: 0; right: 0;
    height: 4px;
    background: #EBEBEB;
    border-radius: 99px;
    pointer-events: none;
}

.price-fill {
    position: absolute;
    height: 100%;
    background: #BD2028;
    border-radius: 99px;
}

/* Two overlapping range inputs */
.price-range {
    position: absolute;
    width: 100%;
    height: 4px;
    background: transparent;
    -webkit-appearance: none;
    appearance: none;
    pointer-events: none;
    outline: none;
}

.price-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    border: 2.5px solid #BD2028;
    box-shadow: 0 1px 6px rgba(237,31,36,0.25);
    cursor: pointer;
    pointer-events: all;
    transition: transform 0.15s, box-shadow 0.15s;
}

.price-range::-webkit-slider-thumb:hover,
.price-range::-webkit-slider-thumb:active {
    transform: scale(1.2);
    box-shadow: 0 2px 10px rgba(237,31,36,0.35);
}

.price-range::-moz-range-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    border: 2.5px solid #BD2028;
    box-shadow: 0 1px 6px rgba(237,31,36,0.25);
    cursor: pointer;
    pointer-events: all;
}

.price-range-min { z-index: 3; }
.price-range-max { z-index: 4; }

/* ─── Price Badges (Min / Max display) ─── */
.price-badge-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    margin-top: 14px;
}

.price-badge {
    flex: 1;
    background: #FFF5F5;
    border: 1.5px solid #FCDADA;
    border-radius: 8px;
    padding: 6px 10px;
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.price-badge-label {
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: #aaa;
    text-transform: uppercase;
}

.price-badge-value {
    font-size: 0.78rem;
    font-weight: 700;
    color: #BD2028;
}

.price-badge-sep {
    color: #ccc;
    font-size: 0.85rem;
    flex-shrink: 0;
}

/* ─── Price Presets ─── */
.price-presets {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 10px;
}

.preset-chip {
    padding: 4px 9px;
    border-radius: 20px;
    border: 1.5px solid #e8e8e8;
    background: #fff;
    font-size: 0.68rem;
    font-weight: 500;
    color: #777;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
    line-height: 1.4;
}

.preset-chip:hover { border-color: #BD2028; color: #BD2028; }
.preset-chip.active { background: #BD2028; border-color: #BD2028; color: #fff; }

/* ─── Apply / Reset ─── */
.apply-filter-btn {
    width: 100%;
    padding: 10px;
    background: #BD2028;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    cursor: pointer;
    margin-top: 14px;
    transition: background 0.18s;
}

.apply-filter-btn:hover { background: #333; }

.reset-link {
    display: block;
    width: 100%;
    padding: 7px;
    background: transparent;
    border: none;
    font-size: 0.72rem;
    color: #999;
    cursor: pointer;
    text-align: center;
    margin-top: 4px;
    transition: color 0.15s;
}

.reset-link:hover { color: #444; }

/* ─── Main Content ─── */
.main-content { flex: 1; min-width: 0; }

/* ─── Breadcrumb ─── */
.breadcrumb-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 14px;
    font-size: 0.76rem;
    color: #aaa;
}

.breadcrumb-item { cursor: pointer; }
.breadcrumb-item:hover { color: #555; }
.breadcrumb-item.active { color: #333; font-weight: 500; }
.breadcrumb-sep { color: #ccc; }

/* ─── Top Bar ─── */
.top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
    gap: 12px;
    flex-wrap: wrap;
}

.top-bar-left { display: flex; align-items: center; gap: 12px; }
.result-count { font-size: 0.78rem; color: #888; }

.mobile-filter-btn {
    display: none;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border: 1.5px solid #ddd;
    border-radius: 8px;
    background: #fff;
    font-size: 0.76rem;
    font-weight: 600;
    cursor: pointer;
}

.top-bar-right { display: flex; align-items: center; gap: 10px; }
.sort-label { font-size: 0.76rem; color: #888; white-space: nowrap; }

.sort-select {
    padding: 6px 28px 6px 10px;
    border: 1.5px solid #e0e0e0;
    border-radius: 8px;
    background: #fff;
    font-size: 0.78rem;
    font-weight: 500;
    color: #333;
    cursor: pointer;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23999'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 8px center;
}

/* ─── View Toggle ─── */
.view-toggle {
    display: flex;
    gap: 2px;
    background: #fff;
    border: 1.5px solid #e0e0e0;
    border-radius: 8px;
    padding: 3px;
}

.view-toggle button {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    color: #bbb;
    transition: all 0.15s;
}

.view-toggle button.active { background: #BD2028; color: #fff; }
.view-toggle button:hover:not(.active) { color: #555; background: #f5f5f5; }

/* ─── Active Filter Tags ─── */
.active-filters { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff;
    border: 1.5px solid #e0e0e0;
    border-radius: 20px;
    padding: 4px 10px;
    font-size: 0.72rem;
    font-weight: 500;
    color: #444;
}

.filter-tag button {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.65rem;
    color: #bbb;
    padding: 0;
    line-height: 1;
    transition: color 0.15s;
}

.filter-tag button:hover { color: #e00; }

/* ─── Product Grid ─── */
.product-grid { display: grid; gap: 20px; }

.product-grid.grid4 { grid-template-columns: repeat(4, 1fr); }
.product-grid.grid3 { grid-template-columns: repeat(3, 1fr); }
.product-grid.grid2 { grid-template-columns: repeat(2, 1fr); }
.product-grid.list  { grid-template-columns: 1fr; }

@media (max-width: 1100px) {
    .product-grid.grid4 { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 860px) {
    .product-grid.grid4,
    .product-grid.grid3 { grid-template-columns: repeat(2, 1fr); }
}

/* ─── Product Card ─── */
.product-card {
    position: relative;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    transition: transform 0.28s cubic-bezier(.22,.68,0,1.2), box-shadow 0.28s ease;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 40px rgba(0,0,0,0.11);
}

/* List mode */
.product-grid.list .product-card { flex-direction: row; }
.product-grid.list .image-wrap   { width: 200px; flex-shrink: 0; aspect-ratio: unset; min-height: 200px; }
.product-grid.list .product-info { justify-content: center; }

/* ─── Badges ─── */
.badge-row {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 3;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.badge {
    display: inline-block;
    font-family: "Poppins", sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    padding: 3px 8px;
    border-radius: 3px;
    line-height: 1.4;
}

.badge-hot   { background: #333; color: #fff; }
.badge-sale  { background: #BD2028; color: #fff; }
.badge-stock { background: #fff; color: #BD2028; border: 1px solid #BD2028; }

/* ─── Wishlist ─── */
.wishlist-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 3;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.92);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ccc;
    transition: color 0.18s, transform 0.18s;
    backdrop-filter: blur(6px);
}

.wishlist-btn:hover      { color: #BD2028; transform: scale(1.12); }
.wishlist-btn.active     { color: #BD2028; }
.wishlist-btn.active svg { fill: #BD2028; stroke: #BD2028; }

/* ─── Image ─── */
.image-wrap {
    position: relative;
    aspect-ratio: 1 / 1;
    background: #F8F8F8;
    overflow: hidden;
}

.product-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 22px;
    box-sizing: border-box;
    transition: transform 0.42s cubic-bezier(.22,.68,0,1.1);
}

.product-card:hover .product-img { transform: scale(1.06); }

.no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ddd;
}

/* ─── Info ─── */
.product-info {
    background: linear-gradient(180deg, #FFFFFF 0%, #FAFAFA 100%);
    padding: 14px 16px 16px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 1;
}

.product-brand {
    margin: 0;
    font-family: "Poppins", sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.14em;
    color: #aaa;
    text-transform: uppercase;
}

.product-name {
    margin: 0;
    font-family: "Poppins", sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.15s;
}

.product-card:hover .product-name { color: #BD2028; }

/* ─── Bottom Row ─── */
.bottom-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 6px;
}

.price-block {
    display: flex;
    align-items: baseline;
    gap: 6px;
}

.sell-price {
    font-family: "Poppins", sans-serif;
    font-size: 1rem;
    font-weight: 700;
    color: #333;
    letter-spacing: -0.01em;
}

.market-price {
    font-family: "Poppins", sans-serif;
    font-size: 11px;
    color: #bbb;
    text-decoration: line-through;
}

.stars { display: flex; align-items: center; gap: 1px; }

/* ─── State Box ─── */
.state-box {
    text-align: center;
    padding: 80px 24px;
    color: #aaa;
    font-size: 0.88rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
}

.state-box.error { color: #c00; }

.retry-btn {
    padding: 9px 24px;
    background: #BD2028;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.18s;
}

.retry-btn:hover { background: #333; }

/* ─── Spinner ─── */
.spinner {
    width: 36px;
    height: 36px;
    border: 3px solid #e8e8e8;
    border-top-color: #BD2028;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

.search-heading {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
  font-size: 0.9rem;
  color: #555;
  flex-wrap: wrap;
}

.search-heading strong {
  color: #111;
  font-size: 1rem;
}

.search-count {
  color: #aaa;
  font-size: 0.8rem;
}

.clear-search-btn {
  margin-left: auto;
  background: none;
  border: 1.5px solid #ddd;
  border-radius: 20px;
  padding: 4px 12px;
  font-size: 0.75rem;
  color: #888;
  cursor: pointer;
  transition: all 0.15s;
}

.clear-search-btn:hover {
  border-color: #BD2028;
  color: #BD2028;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Pagination ─── */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    margin-top: 48px;
}

.page-btn {
    min-width: 36px;
    height: 36px;
    padding: 0 8px;
    background: #fff;
    border: 1.5px solid #e0e0e0;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    color: #555;
}

.page-btn:hover:not(:disabled) { border-color: #BD2028; color: #BD2028; }
.page-btn.active { background: #BD2028; border-color: #BD2028; color: #fff; }
.page-btn:disabled { opacity: 0.3; cursor: not-allowed; }

.page-ellipsis { padding: 0 4px; color: #bbb; font-size: 0.9rem; }

/* ─── Responsive ─── */
@media (max-width: 768px) {
    .products-page { padding: 16px 14px 60px; }

    .sidebar {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        z-index: 200;
        pointer-events: none;
        padding: 0;
    }

    .sidebar.sidebar-open { pointer-events: all; }

    .sidebar-overlay {
        display: block;
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.35);
        opacity: 0;
        transition: opacity 0.2s;
    }

    .sidebar.sidebar-open .sidebar-overlay { opacity: 1; }

    .sidebar-inner {
        position: absolute;
        left: 0; top: 0;
        width: 280px; height: 100%;
        border-radius: 0;
        padding: 0 20px 40px;
        overflow-y: auto;
        transform: translateX(-100%);
        transition: transform 0.25s ease;
    }

    .sidebar.sidebar-open .sidebar-inner { transform: translateX(0); }

    .sidebar-header-mobile {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 0 16px;
        font-size: 0.82rem;
        font-weight: 700;
        border-bottom: 1.5px solid #f0f0f0;
        margin-bottom: 16px;
    }

    .close-btn { background: none; border: none; font-size: 1rem; cursor: pointer; color: #BD2028; }

    .mobile-filter-btn { display: inline-flex; }

    .view-toggle { display: none; }

    .product-grid.grid3,
    .product-grid.grid4 { grid-template-columns: repeat(2, 1fr); gap: 12px; }

    .product-grid.list .product-card { flex-direction: column; }
    .product-grid.list .image-wrap   { width: 100%; aspect-ratio: 1/1; min-height: unset; }
}

@media (max-width: 420px) {
    .product-grid.grid3,
    .product-grid.grid4,
    .product-grid.grid2 { grid-template-columns: repeat(2, 1fr); }
}
</style>