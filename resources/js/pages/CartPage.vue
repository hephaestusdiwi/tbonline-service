<template>
  <div class="min-h-screen bg-white">
    <Navbar />
    <CartDrawer />

    <div class="cart-page">
      <div v-if="cart.state.items.length === 0" class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" width="64" height="64" class="empty-icon">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <h2>SHOPPING CART</h2>
        <p>Keranjangmu masih kosong</p>
        <button class="btn-checkout" @click="$router.push('/')">MULAI BELANJA</button>
      </div>

      <div v-else class="cart-container">
        <div class="cart-header">
          <h1 class="page-title">Shopping Cart</h1>
        </div>

        <div class="cart-layout">
          <!-- LEFT COLUMN -->
          <div class="cart-left">
            <div class="cart-items">
              <div v-for="item in cart.state.items" :key="item.id" class="cart-item">
                <!-- Delete button -->
                <button class="btn-delete" @click="cart.removeItem(item.id, item.variant_id)" title="Hapus">
                  <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                    <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6h14z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                  </svg>
                </button>

                <!-- Item image -->
                <div class="item-img">
                  <img :src="item.photo_1" :alt="item.name" v-if="item.photo_1">
                  <div v-else class="no-img"></div>
                </div>

                <!-- Item details -->
                <div class="item-details">
                  <div class="item-name">{{ item.name }}</div>
                    <div class="item-variants" v-if="item.variant_label">
                      <span
                        v-for="(variant, idx) in parseVariants(item.variant_label)"
                        :key="idx"
                        class="item-variant-line"
                      >
                        {{ variant.label }}: <span class="variant-value">{{ variant.value }}</span>
                      </span>
                    </div>
                  <div class="item-price">{{ formatPriceDot(item.sell_price) }}</div>
                </div>

                <!-- Qty control -->
                <div class="item-qty-wrap">
                  <div class="qty-control">
                    <button class="qty-btn" @click="cart.updateQty(item.id, item.qty - 1, item.variant_id)">−</button>
                    <span class="qty-value">{{ item.qty }}</span>
                    <button class="qty-btn" @click="cart.updateQty(item.id, item.qty + 1, item.variant_id)">+</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN -->
          <div class="cart-right">
            <div class="summary-box">
              <div class="summary-total-row">
                <span class="label">Estimasi total</span>
                <span class="value">Rp {{ formatPriceDot(cart.totalPrice) }}</span>
              </div>
              <p class="summary-tax-note">
                Diskon dan <a href="#">biaya pengiriman</a> dihitung saat checkout
              </p>

              <button class="btn-checkout" @click="$router.push('/checkout')">
                CHECK OUT
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              </button>
            </div>

            <!-- You may also like -->
            <div class="related-section">
              <div class="related-header">
                <h3>Lihat juga</h3>
                <div class="nav-arrows">
                  <button @click="prevRelated" :disabled="relatedIndex === 0">&lt;</button>
                  <button @click="nextRelated" :disabled="relatedIndex >= relatedProducts.length - 1">&gt;</button>
                </div>
              </div>

              <div v-if="relatedLoading" class="related-card">
                <div class="related-info">
                  <div class="rel-skeleton rel-skeleton-name"></div>
                  <div class="rel-skeleton rel-skeleton-price"></div>
                </div>
                <div class="rel-img rel-img-placeholder"></div>
              </div>

              <div v-else-if="relatedProducts.length > 0" class="related-card">
                <div class="related-info">
                  <div class="rel-name">{{ relatedProducts[relatedIndex].name }}</div>
                  <div class="rel-price">{{ formatPriceDot(relatedProducts[relatedIndex].sell_price) }}</div>
                  <button class="btn-add-cart" @click="addRelatedToCart(relatedProducts[relatedIndex])">
                    + KERANJANG
                  </button>
                </div>
                <div class="rel-img">
                  <img
                    v-if="relatedProducts[relatedIndex].photo_1"
                    :src="relatedProducts[relatedIndex].photo_1"
                    :alt="relatedProducts[relatedIndex].name"
                  />
                  <div v-else class="no-img"></div>
                </div>
              </div>

              <div v-else class="rel-empty">Tidak ada produk tersedia.</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <TopProducts />
    <CustomerChat />
    <FooterSection />
  </div>
</template>

<script>

import '../../css/cartpage.css'
import { cartStore } from '../store/cartStore'
import axios from '../axios'
import Navbar        from '../components/Navbar.vue'
import CartDrawer    from '../components/CartDrawer.vue'
import TopProducts   from '../components/TopProducts.vue'
import CustomerChat  from '../components/chat/ChatWidget.vue'
import FooterSection from '../components/FooterSection.vue'
import { useHead } from '@vueuse/head'
import { useSiteSettings } from '../composables/useSiteSettings'

export default {
  name: 'CartPage',

  components: {
    Navbar,
    CartDrawer,
    TopProducts,
    CustomerChat,
    FooterSection,
  },


  setup() {
    const { siteName } = useSiteSettings()
    useHead({ title: `Shopping Cart - ${siteName.value}` })
  },

  data() {
    return {
      relatedProducts: [],
      relatedIndex: 0,
      relatedLoading: false,
    }
  },

  computed: {
    cart() {
      return cartStore
    }
  },

  async mounted() {
    await this.fetchRelatedProducts()
  },

  methods: {
    formatPriceDot(val) {
      if (!val) return '0'
      return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(val)
    },

    /**
     * Parse variant_names string into label/value pairs.
     * Supports formats like:
     *   "Small / White"         → [{label:'Size', value:'Small'}, {label:'Color', value:'White'}]
     *   "Size: Small, Color: White"  → as-is
     *   "Small"                 → [{label:'Varian', value:'Small'}]
     * Adjust the label mapping to match your actual variant structure.
     */
    parseVariants(variantNames) {
      if (!variantNames) return []

      // If already in "Key: Value" format
      if (variantNames.includes(':')) {
        return variantNames.split(',').map(part => {
          const [label, ...rest] = part.split(':')
          return { label: label.trim(), value: rest.join(':').trim() }
        })
      }

      // If slash-separated (common Shopify-style)
      const parts = variantNames.split('/').map(s => s.trim()).filter(Boolean)
      const defaultLabels = ['Size', 'Color', 'Material', 'Style']
      return parts.map((val, i) => ({
        label: defaultLabels[i] || `Varian ${i + 1}`,
        value: val
      }))
    },

    photoUrl(path) {
      if (!path) return null
      if (path.startsWith('http://') || path.startsWith('https://')) return path
      const base = import.meta.env.VITE_APP_URL || window.location.origin
      return `${base}/storage/${path}`
    },

    async fetchRelatedProducts() {
      this.relatedLoading = true
      try {
        const { data } = await axios.get('/products', {
          params: { published: 1, per_page: 10, sort: 'newest' }
        })
        const cartIds = new Set(this.cart.state.items.map(i => i.id))
        this.relatedProducts = (data.data?.data ?? data.data ?? [])
          .filter(p => !cartIds.has(p.id))
          .map(p => ({          // ← tambah map ini
            ...p,
            photo_1: this.photoUrl(p.photo_1),
          }))
      } catch (e) {
        console.error('Failed fetch related products:', e)
      } finally {
        this.relatedLoading = false
      }
    },

    prevRelated() {
      if (this.relatedIndex > 0) this.relatedIndex--
    },

    nextRelated() {
      if (this.relatedIndex < this.relatedProducts.length - 1) this.relatedIndex++
    },

    addRelatedToCart(product) {
      this.cart.addItem({
        id: product.id,
        name: product.name,
        sell_price: product.sell_price,
        photo_1: product.photo_1,
        qty: 1,
        variant_id: null,
      })
    },
  }
}
</script>
