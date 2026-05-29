<template>
  <div class="app-shell">
    <Navbar />
    <CartDrawer />

    <main class="pd-page">

      <!-- ── Loading ── -->
      <div v-if="loading" class="pd-state">
        <div class="pd-spinner"></div>
        <p class="pd-state__text">Memuat produk...</p>
      </div>

      <!-- ── Error ── -->
      <div v-else-if="error" class="pd-state pd-state--error">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <p class="pd-state__text">{{ error }}</p>
        <button class="pd-retry" @click="fetchProduct">Coba Lagi</button>
      </div>

      <!-- ── Content ── -->
      <template v-else-if="product">
        <div class="pd-layout">

          <!-- ══ LEFT: Gallery ══ -->
          <div class="pd-gallery">
            <div
              class="pd-main-photo"
              @touchstart="onTouchStart"
              @touchend="onTouchEnd"
            >
              <transition :name="slideDir" mode="out-in">
                <img
                  v-if="currentPhoto"
                  :key="currentPhoto"
                  :src="currentPhoto"
                  :alt="product.name"
                  class="pd-main-img"
                  @error="onImgError"
                />
                <div v-else class="pd-no-photo">
                  <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                  </svg>
                </div>
              </transition>

              <!-- Discount badge -->
              <span v-if="discount" class="pd-badge">-{{ discount }}%</span>

              <!-- Dots (mobile) -->
              <div v-if="photos.length > 1" class="pd-dots">
                <span
                  v-for="(p, i) in photos"
                  :key="i"
                  :class="['pd-dot', activePhoto === i && 'pd-dot--active']"
                  @click="setPhoto(i)"
                ></span>
              </div>
            </div>

            <!-- Thumbnails (desktop) -->
            <div v-if="photos.length > 1" class="pd-thumbs">
              <button
                v-for="(p, i) in photos"
                :key="i"
                :class="['pd-thumb', activePhoto === i && 'pd-thumb--active']"
                @click="setPhoto(i)"
              >
                <img :src="p" :alt="`Foto ${i + 1}`" @error="onImgError" />
              </button>
            </div>
          </div>

          <!-- ══ RIGHT: Info ══ -->
          <div class="pd-info">

            <!-- Breadcrumb -->
            <nav class="pd-breadcrumb">
              <span @click="$router.push('/')" class="pd-breadcrumb__link">Beranda</span>
              <span class="pd-breadcrumb__sep">/</span>
              <span v-if="product.category" @click="$router.push(`/?category=${product.category}`)" class="pd-breadcrumb__link">{{ product.category }}</span>
              <span v-if="product.category" class="pd-breadcrumb__sep">/</span>
              <span class="pd-breadcrumb__current">{{ product.name }}</span>
            </nav>

            <!-- Name + Share -->
            <div class="pd-name-row">
              <h1 class="pd-name">{{ product.name }}</h1>
              <button class="pd-share-btn" @click="shareProduct" title="Bagikan">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                  <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                  <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                </svg>
              </button>
            </div>

            <p v-if="product.alternative_name" class="pd-alt-name">{{ product.alternative_name }}</p>

            <!-- Divider -->
            <div class="pd-divider"></div>

            <!-- Price -->
            <div class="pd-price-block">
              <span class="pd-price" :class="discount && 'pd-price--sale'">
                {{ formatPrice(effectivePrice) }}
              </span>
              <span v-if="effectiveMarketPrice > effectivePrice" class="pd-market-price">
                {{ formatPrice(effectiveMarketPrice) }}
              </span>
              <span v-if="discount" class="pd-save-badge">
                Hemat {{ formatPrice(effectiveMarketPrice - effectivePrice) }}
              </span>
            </div>

            <!-- Stock -->
            <div class="pd-stock-row">
              <template v-if="selectedVariant">
                <span v-if="selectedVariant.stock_qty > 0" class="pd-stock pd-stock--ok">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                  Stok tersedia
                </span>
                <span v-else class="pd-stock pd-stock--empty">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                  Stok Habis
                </span>
              </template>
              <template v-else-if="!hasVariants">
                <span class="pd-stock pd-stock--ok">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                  Tersedia
                </span>
              </template>
            </div>

            <!-- Variant Selector -->
            <div v-if="hasVariants" class="pd-variants">
              <div
                v-for="optType in uniqueOptionTypes"
                :key="optType.id"
                class="pd-variant-group"
              >
                <div class="pd-variant-label">
                  {{ optType.name }}
                  <span v-if="selectedOptions[optType.id]" class="pd-variant-chosen">
                    — {{ selectedOptions[optType.id] }}
                  </span>
                </div>
                <div class="pd-chips">
                  <template v-for="val in optType.values" :key="val.id">
                    <button
                      v-if="isValueAvailable(optType.id, val.value)"
                      :class="[
                        'pd-chip',
                        selectedOptions[optType.id] === val.value && 'pd-chip--active',
                      ]"
                      @click="selectOption(optType.id, val.value)"
                    >
                      {{ val.value }}
                    </button>
                  </template>
                </div>
              </div>
            </div>

            <!-- Quantity -->
            <div class="pd-qty-block">
              <span class="pd-qty-label">Jumlah</span>
              <div class="pd-qty-control">
                <button class="pd-qty-btn" @click="decreaseQty" :disabled="qty <= 1">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                  </svg>
                </button>
                <span class="pd-qty-val">{{ qty }}</span>
                <button class="pd-qty-btn" @click="increaseQty" :disabled="maxQty !== null && qty >= maxQty">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- CTA -->
            <div class="pd-cta">
              <button
                class="pd-cart-btn"
                @click="addToCart"
                :disabled="(hasVariants && !selectedVariant) || (selectedVariant && selectedVariant.stock_qty === 0)"
              >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                  <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                {{ hasVariants && !selectedVariant ? 'Pilih Varian Dulu' : 'Tambah ke Keranjang' }}
              </button>
            </div>

            <!-- Description accordion -->
            <div v-if="product.description" class="pd-accordion">
              <button class="pd-accordion__head" @click="showDesc = !showDesc">
                <span>Deskripsi Produk</span>
                <svg
                  :class="['pd-accordion__arrow', showDesc && 'pd-accordion__arrow--open']"
                  width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                >
                  <polyline points="6 9 12 15 18 9"/>
                </svg>
              </button>
              <transition name="acc">
                <div v-show="showDesc" class="pd-accordion__body">
                  <p>{{ product.description }}</p>
                </div>
              </transition>
            </div>

            <!-- Meta -->
            <div class="pd-meta">
              <div v-if="product.sku" class="pd-meta__row">
                <span class="pd-meta__key">SKU</span>
                <span class="pd-meta__val">{{ selectedVariant?.sku || product.sku }}</span>
              </div>
              <div v-if="product.brand" class="pd-meta__row">
                <span class="pd-meta__key">Brand</span>
                <span class="pd-meta__val">{{ product.brand }}</span>
              </div>
              <div v-if="product.weight_kg" class="pd-meta__row">
                <span class="pd-meta__key">Berat</span>
                <span class="pd-meta__val">{{ product.weight_kg }} kg</span>
              </div>
            </div>

          </div><!-- /pd-info -->
        </div><!-- /pd-layout -->

        <!-- ══ Mungkin Kamu Suka ══ -->
        <section v-if="suggestedProducts.length" class="pd-suggestions">
          <h2 class="pd-suggestions__title">Mungkin Kamu Juga Suka</h2>

          <div class="pd-suggestions__grid">
            <div
              v-for="item in suggestedProducts"
              :key="item.id"
              class="pd-sug-card"
              @click="$router.push({ name: 'ProductDetail', params: { id: item.id } })"
            >
              <!-- Badges -->
              <div class="pd-sug-badges">
                <span v-if="getSugDiscount(item)" class="pd-sug-badge pd-sug-badge--sale">
                  -{{ getSugDiscount(item) }}%
                </span>
                <span v-if="isSugBestSeller(item)" class="pd-sug-badge pd-sug-badge--hot">
                  BESTSELLER
                </span>
              </div>

              <!-- Wishlist -->
              <button
                class="pd-sug-wish"
                :class="{ active: sugWishlisted.has(item.id) }"
                @click.stop="toggleSugWish(item.id)"
                aria-label="Wishlist"
              >
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
              </button>

              <!-- Image -->
              <div class="pd-sug-img-wrap">
                <img
                  v-if="item.photo_1"
                  :src="photoUrl(item.photo_1)"
                  :alt="item.name"
                  class="pd-sug-img"
                  loading="lazy"
                  @error="onImgError"
                />
                <div v-else class="pd-sug-no-img">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" width="36" height="36">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                  </svg>
                </div>
              </div>

              <!-- Info -->
              <div class="pd-sug-info">
                <p v-if="item.brand" class="pd-sug-brand">{{ item.brand }}</p>
                <p class="pd-sug-name">{{ item.name }}</p>
                <div class="pd-sug-bottom">
                  <div class="pd-sug-prices">
                    <span class="pd-sug-price">{{ formatPrice(item.sell_price) }}</span>
                    <span v-if="item.market_price > item.sell_price" class="pd-sug-market">
                      {{ formatPrice(item.market_price) }}
                    </span>
                  </div>
                  <!-- Stars berdasarkan qty_fast_moving -->
                  <div v-if="getSugStars(item) > 0" class="pd-sug-stars">
                    <svg
                      v-for="i in 5" :key="i"
                      viewBox="0 0 12 12" width="10" height="10"
                      :fill="i <= getSugStars(item) ? '#ED1F24' : '#E0E0E0'"
                    >
                      <polygon points="6,1 7.5,4.5 11,5 8.5,7.5 9,11 6,9.5 3,11 3.5,7.5 1,5 4.5,4.5"/>
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </template>

    </main>

    <!-- Toast notification -->
    <transition name="toast">
      <div v-if="showToast" class="pd-toast">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        {{ toastMessage }}
      </div>
    </transition>

    <CustomerChat />
    <FooterSection />
  </div>
</template>

<script>
import '../../css/productdetail.css'
import { cartStore } from '../store/cartStore'
import { useHead } from '@vueuse/head'
import { useSiteSettings } from '../composables/useSiteSettings'
import { useSeoMeta } from '../composables/useSeoMeta.js'
import Navbar        from '../components/Navbar.vue'
import CartDrawer    from '../components/CartDrawer.vue'
import CustomerChat  from '../components/chat/ChatWidget.vue'
import FooterSection from '../components/FooterSection.vue'

export default {
  name: 'ProductDetail',

  components: {
    Navbar,
    CartDrawer,
    CustomerChat,
    FooterSection,
  },

  setup() {
    const { siteName } = useSiteSettings() 
    return { cartStore, siteName }  
  },

  data() {
    return {
      product:         null,
      loading:         true,
      error:           null,
      activePhoto:     0,
      slideDir:        'slide-left',
      qty:             1,
      selectedOptions: {},
      showDesc:        false,
      showToast:       false,
      toastMessage:    '',
      touchStartX:     0,
      suggestedProducts: [],
      sugWishlisted: new Set(),
    }
  },

  computed: {
    photos() {
        if (!this.product) return []
        return [
            this.product.photo_1, this.product.photo_2, this.product.photo_3,
            this.product.photo_4, this.product.photo_5,
        ].filter(Boolean).map(p => this.photoUrl(p))  // ← tambah .map()
    },

    uniqueOptionTypes() {
    if (!this.product?.option_types) return []
    const seen = new Set()
    return this.product.option_types.filter(opt => {
        if (seen.has(opt.name)) return false
        seen.add(opt.name)
        return true
    })
    },

    currentPhoto() {
      return this.photos[this.activePhoto] || null
    },

    hasVariants() {
        return this.uniqueOptionTypes?.length > 0
    },

    selectedVariant() {
        if (!this.hasVariants || !this.product?.variants) return null
        const selectedValues = Object.values(this.selectedOptions)
        if (selectedValues.length === 0) return null

        // ✅ Pakai uniqueOptionTypes bukan product.option_types
        if (selectedValues.length < this.uniqueOptionTypes.length) return null

        return this.product.variants.find(v => {
            const variantValues = (v.option_values || []).map(ov => ov.value)
            return selectedValues.every(sv => variantValues.includes(sv))
        }) || null
    },

    effectivePrice() {
      if (this.selectedVariant?.sell_price) return this.selectedVariant.sell_price
      return this.product?.sell_price || 0
    },

    effectiveMarketPrice() {
      if (this.selectedVariant?.market_price) return this.selectedVariant.market_price
      return this.product?.market_price || 0
    },

    discount() {
      if (!this.effectiveMarketPrice || this.effectiveMarketPrice <= this.effectivePrice) return null
      return Math.round((1 - this.effectivePrice / this.effectiveMarketPrice) * 100)
    },

    maxQty() {
      if (this.selectedVariant) {
        // Stok ada → qty bebas (null = no limit), stok 0 → blocked
        return this.selectedVariant.stock_qty > 0 ? null : 0
      }
      return null
    },
  },

    watch: {
    '$route.params.id'(newId, oldId) {
      if (newId !== oldId) {
        this.suggestedProducts = []
        this.fetchProduct().then(() => {
          if (this.product) {
            this.setSeoMeta()
            this.fetchSuggestions()
          }
        })
      }
    }
  },

  async mounted() {
      await this.fetchProduct()

      if (this.product) {
        this.setSeoMeta()
        await this.fetchSuggestions()
      }
  },

  methods: {
    async fetchProduct() {
      this.loading = true
      this.error   = null
      try {
        const id  = this.$route.params.id
        const res = await fetch(`/api/products/${id}`)
        if (!res.ok) throw new Error('Produk tidak ditemukan.')
        const json   = await res.json()
        this.product = json.data ?? json
      } catch (e) {
        this.error = e.message
      } finally {
        this.loading = false
      }
    },

    async fetchSuggestions() {
      if (!this.product) return
      try {
        let results = []

        // Tahap 1: Coba ambil dari kategori + brand yang sama
        if (this.product.category && this.product.brand) {
          results = await this.fetchSugPool({
            category: this.product.category,
            brand: this.product.brand,
          })
        }

        // Tahap 2: Kalau kurang dari 6, tambah dari kategori yang sama
        if (results.length < 6 && this.product.category) {
          const more = await this.fetchSugPool({ category: this.product.category })
          const existingIds = new Set(results.map(p => p.id))
          results = [
            ...results,
            ...more.filter(p => !existingIds.has(p.id)),
          ]
        }

        // Tahap 3: Kalau masih kurang, ambil produk terlaris secara umum
        if (results.length < 6) {
          const more = await this.fetchSugPool({ sort: 'best_seller' })
          const existingIds = new Set(results.map(p => p.id))
          results = [
            ...results,
            ...more.filter(p => !existingIds.has(p.id)),
          ]
        }

        // Exclude produk saat ini, ambil 6
        this.suggestedProducts = results
          .filter(p => p.id !== this.product.id)
          .slice(0, 6)

      } catch {}
    },

    setSeoMeta() {
      const p = this.product
      if (!p) return

      const image = this.photoUrl(p.photo_1) || '/images/og-default.jpg'

      useSeoMeta({
        title:        p.name,
        description:  p.description
            ? p.description.slice(0, 155)
            : `Beli ${p.name} di TB Store. Dijamin aman, proses cepat`,
          image,
          type: 'product',
          jsonLd: {
            '@context': 'https://schema.org',
            '@type':    'Product',
            name:       p.name,
            image:      image,
            description: p.description || `Beli ${p.name} di TB Store`,
            sku:        p.sku || undefined,
            brand:  p.brand ? {
              '@type':  'Brand',
              name:     p.brand,
            } : undefined,
            offers: {
              '@type':        'Offer',
              price:          this.effectivePrice,
              priceCurrency:  'IDR',
              availability:   'https://schema.org/' +
                (this.selectedVariant
                  ? (this.selectedVariant.stock_qty > 0 ? 'InStock' : 'OutOfStock')
                  : 'InStock'),
                url: window.location.href,
            },
          },
      })
    },

    // Helper: fetch dengan params tertentu
    async fetchSugPool(filters = {}) {
      const params = new URLSearchParams({
        published: 1,
        in_stock: 1,
        per_page: 12,
        ...filters,
      })
      const res = await fetch(`/api/products?${params}`)
      if (!res.ok) return []
      const json = await res.json()
      return Array.isArray(json.data) ? json.data : (json.data?.data ?? [])
    },

    getSugDiscount(item) {
      if (!item.market_price || item.market_price <= item.sell_price) return null
      return Math.round((1 - item.sell_price / item.market_price) * 100)
    },

    isSugBestSeller(item) {
      return (item.qty_fast_moving || 0) >= 50
    },

    getSugStars(item) {
      const qty = item.qty_fast_moving || 0
      if (qty >= 100) return 5
      if (qty >= 50)  return 4
      if (qty >= 20)  return 3
      if (qty >= 5)   return 2
      return qty > 0 ? 1 : 0
    },

    toggleSugWish(id) {
      const next = new Set(this.sugWishlisted)
      next.has(id) ? next.delete(id) : next.add(id)
      this.sugWishlisted = next
    },

    photoUrl(path) {
        if (!path) return null
        if (path.startsWith('http://') || path.startsWith('https://')) return path
        const base = import.meta.env.VITE_APP_URL || window.location.origin
        return `${base}/storage/${path}`
    },

    setPhoto(i) {
      this.slideDir    = i > this.activePhoto ? 'slide-left' : 'slide-right'
      this.activePhoto = i
    },

    onTouchStart(e) {
      this.touchStartX = e.touches[0].clientX
    },

    onTouchEnd(e) {
      const diff = this.touchStartX - e.changedTouches[0].clientX
      if (Math.abs(diff) < 40) return
      if (diff > 0 && this.activePhoto < this.photos.length - 1) this.setPhoto(this.activePhoto + 1)
      else if (diff < 0 && this.activePhoto > 0) this.setPhoto(this.activePhoto - 1)
    },

    selectOption(typeId, value) {
      this.selectedOptions = { ...this.selectedOptions, [typeId]: value }
      this.qty = 1
    },

    isValueAvailable(typeId, value) {
      if (!this.product?.variants) return true
      return this.product.variants.some(v => {
        const hasValue = (v.option_values || []).some(ov => ov.value === value)
        return hasValue && v.is_active && v.stock_qty > 0
      })
    },

    increaseQty() {
      if (this.maxQty === null || this.qty < this.maxQty) this.qty++
    },

    decreaseQty() {
      if (this.qty > 1) this.qty--
    },

    addToCart() {
    if (!this.product) return

    // Bangun label varian dari selectedOptions
    const variantLabel = this.selectedVariant
        ? this.uniqueOptionTypes
            .map(opt => `${opt.name}: ${this.selectedOptions[opt.id]}`)
            .join(', ')
        : null

    const item = {
        id:         this.product.id,
        name:       this.product.name,
        photo_1:    this.photoUrl(this.product.photo_1),
        sell_price: this.effectivePrice,
        qty:        this.qty,
        variant_id: this.selectedVariant?.id || null,
        variant_label: variantLabel, // ← "Size: Small, Color: White"
    }
    this.cartStore.addItem(item)
    this.triggerToast('Produk ditambahkan ke keranjang!')
    },

    async shareProduct() {
      const url = window.location.href
      if (navigator.share) {
        try { await navigator.share({ title: this.product.name, url }) } catch {}
      } else {
        navigator.clipboard.writeText(url)
        this.triggerToast('Link berhasil disalin!')
      }
    },

    triggerToast(msg) {
      this.toastMessage = msg
      this.showToast    = true
      setTimeout(() => { this.showToast = false }, 2500)
    },

    formatPrice(val) {
      if (!val && val !== 0) return '-'
      return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR',
        minimumFractionDigits: 0, maximumFractionDigits: 0,
      }).format(val)
    },

    onImgError(e) {
      e.target.style.display = 'none'
    },
  },
}
</script>
<style scoped>
/* ══════════════════════════════════════
   MUNGKIN KAMU SUKA — Suggestion Section
═══════════════════════════════════════ */
.pd-suggestions {
  max-width: 1200px;
  margin: 56px auto 0;
  padding: 0 24px 80px;
}

.pd-suggestions__title {
  font-family: "Poppins", sans-serif;
  font-size: 1.1rem;
  font-weight: 700;
  color: #222;
  margin: 0 0 20px;
  padding-bottom: 12px;
  border-bottom: 2px solid #f0f0f0;
  text-transform: uppercase;
  position: relative;
}

.pd-suggestions__title::after {
  content: '';
  position: absolute;
  left: 0; bottom: -2px;
  width: 60px; height: 2px;
  background: #ED1F24;
  border-radius: 2px;
}

/* Grid: 6 kolom desktop → 4 → 3 → 2 */
.pd-suggestions__grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 16px;
}

@media (max-width: 1200px) {
  .pd-suggestions__grid { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 860px) {
  .pd-suggestions__grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 560px) {
  .pd-suggestions__grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
  .pd-suggestions { padding: 0 14px 60px; }
}

/* ── Card ── */
.pd-sug-card {
  position: relative;
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
  transition: transform 0.28s cubic-bezier(.22,.68,0,1.2),
              box-shadow 0.28s ease;
}

.pd-sug-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0,0,0,0.10);
}

/* ── Badges ── */
.pd-sug-badges {
  position: absolute;
  top: 8px; left: 8px;
  z-index: 3;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.pd-sug-badge {
  font-family: "Poppins", sans-serif;
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 0.07em;
  padding: 2px 6px;
  border-radius: 3px;
  line-height: 1.5;
}

.pd-sug-badge--sale { background: #ED1F24; color: #fff; }
.pd-sug-badge--hot  { background: #333; color: #fff; }

/* ── Wishlist button ── */
.pd-sug-wish {
  position: absolute;
  top: 8px; right: 8px;
  z-index: 3;
  width: 28px; height: 28px;
  border-radius: 50%;
  background: rgba(255,255,255,0.90);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ccc;
  transition: color 0.18s, transform 0.18s;
  backdrop-filter: blur(4px);
}

.pd-sug-wish:hover      { color: #ED1F24; transform: scale(1.1); }
.pd-sug-wish.active     { color: #ED1F24; }
.pd-sug-wish.active svg { fill: #ED1F24; stroke: #ED1F24; }

/* ── Image ── */
.pd-sug-img-wrap {
  position: relative;
  aspect-ratio: 1 / 1;
  background: #F8F8F8;
  overflow: hidden;
}

.pd-sug-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  padding: 16px;
  box-sizing: border-box;
  transition: transform 0.38s cubic-bezier(.22,.68,0,1.1);
}

.pd-sug-card:hover .pd-sug-img { transform: scale(1.06); }

.pd-sug-no-img {
  width: 100%; height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ddd;
}

/* ── Info ── */
.pd-sug-info {
  padding: 10px 12px 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
  background: linear-gradient(180deg, #fff 0%, #fafafa 100%);
}

.pd-sug-brand {
  margin: 0;
  font-family: "Poppins", sans-serif;
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 0.13em;
  color: #aaa;
  text-transform: uppercase;
}

.pd-sug-name {
  margin: 0;
  font-family: "Poppins", sans-serif;
  font-size: 12px;
  font-weight: 600;
  color: #333;
  line-height: 1.35;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: color 0.15s;
}

.pd-sug-card:hover .pd-sug-name { color: #ED1F24; }

/* ── Bottom row ── */
.pd-sug-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 4px;
}

.pd-sug-prices {
  display: flex;
  align-items: baseline;
  gap: 4px;
  flex-wrap: wrap;
}

.pd-sug-price {
  font-family: "Poppins", sans-serif;
  font-size: 0.82rem;
  font-weight: 700;
  color: #333;
}

.pd-sug-market {
  font-family: "Poppins", sans-serif;
  font-size: 10px;
  color: #bbb;
  text-decoration: line-through;
}

.pd-sug-stars {
  display: flex;
  align-items: center;
  gap: 1px;
  flex-shrink: 0;
}
</style>