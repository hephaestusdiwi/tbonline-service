<template>
  <div>
    <Navbar />
    <CartDrawer />
  <div class="checkout-page">

    <!-- Empty guard -->
    <div v-if="cart.state.items.length === 0" class="empty-guard container">
      <div class="empty-cart-box">
        <div class="empty-cart-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
        </div>
        <h2 class="empty-cart-title">Keranjangmu kosong</h2>
        <p class="empty-cart-sub">Belum ada produk yang ditambahkan ke keranjang. Yuk, mulai belanja!</p>
        <button class="btn-empty-shop" @click="$router.push('/')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
          </svg>
          Kembali Belanja
        </button>
      </div>
    </div>

    <div v-else class="container main-grid">

      <!-- LEFT: Form -->
      <div class="left-col">

        <!-- STEP 1: Fulfillment Toggle -->
        <div class="form-card">
          <div class="card-head">
            <h2 class="card-title">Metode Pembelian</h2>
          </div>
          <div class="fulfillment-toggle">
            <button
              class="fulfillment-btn"
              :class="{ active: fulfillmentType === 'delivery' }"
              @click="setFulfillment('delivery')"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18">
                <rect x="1" y="3" width="15" height="13" rx="1"/>
                <path d="M16 8h4l3 3v5h-7V8z"/>
                <circle cx="5.5" cy="18.5" r="2.5"/>
                <circle cx="18.5" cy="18.5" r="2.5"/>
              </svg>
              Kirim
            </button>
            <button
              class="fulfillment-btn"
              :class="{ active: fulfillmentType === 'pickup' }"
              @click="setFulfillment('pickup')"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
              Ambil di Tempat
            </button>
          </div>
        </div>

        <!-- STEP 2: Delivery — Alamat + Pengiriman -->
        <template v-if="fulfillmentType === 'delivery'">
          <div class="form-card">
            <div class="card-head">
              <h2 class="card-title">Alamat Pengiriman</h2>
            </div>
            <div class="form-grid">
              <div class="form-group full autocomplete-wrap no-float">
                <label class="form-label-static">Kelurahan / Kecamatan <span class="req">*</span></label>
                <div class="search-input-wrap" :class="{ error: errors.destination_id }">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15" class="search-icon">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                  </svg>
                  <input
                    v-model="destinationSearch"
                    type="text"
                    class="form-input search-field"
                    placeholder="Cari kelurahan, kecamatan, atau kota..."
                    @input="onDestinationInput"
                    @focus="showDestinationDropdown = destinationResults.length > 0"
                    @blur="onDestinationBlur"
                    autocomplete="off"
                  />
                  <button v-if="selectedDestination" class="clear-btn" @mousedown.prevent="clearDestination">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13">
                      <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                  </button>
                  <span v-else-if="loadingDestination" class="search-spinner"/>
                </div>
                <span v-if="errors.destination_id" class="err-msg">{{ errors.destination_id }}</span>
                <div v-if="showDestinationDropdown && destinationResults.length > 0" class="destination-dropdown">
                  <div
                    v-for="item in destinationResults"
                    :key="item.id"
                    class="destination-item"
                    @mousedown.prevent="selectDestination(item)"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="14" height="14" class="dest-icon">
                      <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                    <div class="dest-text">
                      <span class="dest-main">{{ item.subdistrict_name }}, {{ item.district_name }}</span>
                      <span class="dest-sub">{{ item.city_name }}, {{ item.province_name }} {{ item.zip_code }}</span>
                    </div>
                  </div>
                </div>
                <div v-if="selectedDestination" class="destination-selected">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  {{ selectedDestination.label }}
                </div>
              </div>
              <div class="form-group full">
                <label class="form-label">Alamat Lengkap <span class="req">*</span></label>
                <textarea v-model="form.address" class="form-input textarea" :class="{ error: errors.address }" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, dsb."/>
                <span v-if="errors.address" class="err-msg">{{ errors.address }}</span>
              </div>
              <div class="form-group">
                <label class="form-label">Kode Pos</label>
                <input v-model="form.postal_code" type="text" class="form-input" placeholder="Otomatis terisi" readonly style="background:#f9f9f9;color:#888"/>
              </div>
            </div>
          </div>

          <div class="form-card">
            <div class="card-head">
              <h2 class="card-title">Pilih Pengiriman</h2>
            </div>
            <div v-if="!selectedDestination" class="shipping-placeholder">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32">
                <rect x="1" y="3" width="15" height="13" rx="1"/>
                <path d="M16 8h4l3 3v5h-7V8z"/>
                <circle cx="5.5" cy="18.5" r="2.5"/>
                <circle cx="18.5" cy="18.5" r="2.5"/>
              </svg>
              <p>Pilih kelurahan tujuan terlebih dahulu untuk melihat pilihan kurir.</p>
               <div style="margin-top: 16px; border-top: 1px dashed #e0e0e0; padding-top: 14px;">
                <label class="service-item" :class="{ selected: isCustomShipping }" @click="selectCustomShipping">
                  <input type="radio" :checked="isCustomShipping" class="service-radio" readonly/>
                  <div class="service-info">
                    <div class="service-name-wrap">
                      <span class="service-name">Atur Sendiri</span>
                      <span class="service-desc">Diskusi dengan staff kami</span>
                    </div>
                    <div class="service-right">
                      <span class="service-cost" style="color: #888">TBD</span>
                      <span class="service-etd">via WhatsApp</span>
                    </div>
                  </div>
                </label>
                <div v-if="isCustomShipping" style="padding: 10px 12px 4px;">
                  <textarea
                    v-model="customShippingNote"
                    class="form-input textarea"
                    rows="2"
                    placeholder="Ceritakan kebutuhannya, misal: luar kota, barang besar, atau area terpencil..."
                  />
                </div>
              </div>
            </div>
            <div v-else-if="loadingRates" class="shipping-loading">
              <div class="loading-dots"><span/><span/><span/></div>
              <p>Menghitung ongkos kirim...</p>
            </div>
            <div v-else-if="ratesError" class="shipping-error">
              <p>{{ ratesError }}</p>
              <button class="btn-retry" @click="fetchShippingRates">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                  <polyline points="23 4 23 10 17 10"/>
                  <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                </svg>
                Coba Lagi
              </button>
            </div>
            <div v-else-if="shippingRates.length > 0" class="shipping-options">
              <div class="service-list">
                <label
                  v-for="svc in limitedRates"
                  :key="`${svc.code}-${svc.service}`"
                  class="service-item"
                  :class="{ selected: selectedShipping && selectedShipping.service === svc.service && selectedShipping.code === svc.code }"
                >
                  <input type="radio" :value="svc" v-model="selectedShipping" class="service-radio"/>
                  <div class="service-info">
                    <div class="service-name-wrap">
                      <span class="service-name">{{ svc.description || svc.service }}</span>
                      <span class="service-desc">{{ svc.code.toUpperCase() }}</span>
                    </div>
                    <div class="service-right">
                      <span class="service-cost">{{ formatPrice(svc.cost) }}</span>
                      <span class="service-etd">est. {{ svc.etd }}</span>
                    </div>
                  </div>
                </label>
              </div>

              <div class="service-list" style="margin-top: 10px; border-top: 1px dashed #e0e0e0; padding-top: 10px;">
                <label
                  class="service-item"
                  :class="{ selected: isCustomShipping }"
                  @click="selectCustomShipping"
                >
                  <input type="radio" :checked="isCustomShipping" class="service-radio" readonly/>
                  <div class="service-info">
                    <div class="service-name-wrap">
                      <span class="service-name">Atur Sendiri</span>
                      <span class="service-desc">Diskusi dengan staff kami</span>
                    </div>
                    <div class="service-right">
                      <span class="service-cost" style="color: #888">TBD</span>
                      <span class="service-etd">via WhatsApp</span>
                    </div>
                  </div>
                </label>
                <div v-if="isCustomShipping" style="padding: 10px 12px 4px;">
                  <textarea
                    v-model="customShippingNote"
                    class="form-input textarea"
                    rows="2"
                    placeholder="Ceritakan kebutuhanmu, misal: luar kota, barang besar, atau area terpencil..."
                    style="font-size: 13px;"
                  />
                </div>
              </div>

              <span v-if="errors.shipping" class="err-msg" style="margin-top:8px;display:block">{{ errors.shipping }}</span>
            </div>
            <div v-else-if="selectedDestination && !loadingRates" class="shipping-placeholder">
              <p>Tidak ada layanan pengiriman tersedia untuk tujuan ini.</p>
            </div>
          </div>
        </template>

        <!-- STEP 2: Pickup — Pilih Cabang -->
        <div v-if="fulfillmentType === 'pickup'" class="form-card">
          <div class="card-head"> 
            <h2 class="card-title">Pilih Cabang Pickup</h2>
          </div>
          <div v-if="loadingBranches" class="shipping-loading">
            <div class="loading-dots"><span/><span/><span/></div>
            <p>Memuat cabang...</p>
          </div>
          <div v-else-if="branches.length === 0" class="shipping-placeholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <p>Tidak ada cabang tersedia saat ini.</p>
          </div>
          <div v-else>
            <p class="branch-count-label">Item tersedia di {{ branches.length }} lokasi</p>
            <div class="branch-list">
              <label
                v-for="branch in branches"
                :key="branch.id"
                class="branch-item"
                :class="{ selected: selectedBranch && selectedBranch.id === branch.id }"
              >
                <input type="radio" :value="branch" v-model="selectedBranch" class="branch-radio"/>
                <div class="branch-info">
                  <div class="branch-header-row">
                    <span class="branch-name">{{ branch.name }}</span>
                  </div>
                  <div class="branch-address">{{ branch.address }}</div>
                  <div class="branch-meta-row">
                    <div v-if="branch.operating_hours" class="branch-hours">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                      </svg>
                      {{ formatOperatingHours(branch.operating_hours) }}
                    </div>
                    <div v-if="branch.phone" class="branch-phone">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.6 3.41 2 2 0 0 1 3.57 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.52a16 16 0 0 0 6.06 6.06l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                      </svg>
                      {{ branch.phone }}
                    </div>
                  </div>
                  <a
                    v-if="branch.google_maps_url"
                    :href="branch.google_maps_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="branch-maps-link"
                    @click.stop
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11">
                      <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                    Lihat di Maps
                  </a>
                </div>
                <div v-if="selectedBranch && selectedBranch.id === branch.id" class="branch-check">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                </div>
              </label>
            </div>
            <span v-if="errors.branch_id" class="err-msg" style="margin-top:10px;display:block">{{ errors.branch_id }}</span>
          </div>
        </div>

        <!-- STEP 3: Data Pembeli -->
        <div class="form-card">
          <div class="card-head">
            <h2 class="card-title">Data Pembeli</h2>
          </div>
          <div class="form-grid">
            <div class="form-group full">
              <label class="form-label">Nama Lengkap <span class="req">*</span></label>
              <input v-model="form.name" type="text" class="form-input" :class="{ error: errors.name }" placeholder="Masukkan nama lengkap"/>
              <span v-if="errors.name" class="err-msg">{{ errors.name }}</span>
            </div>
            <div class="form-group no-float">
              <label class="form-label">No. WhatsApp <span class="req">*</span></label>
              <div class="input-prefix-wrap" :class="{ 'error-wrap': errors.phone }">
                <span class="input-prefix">+62</span>
                <input v-model="form.phone" type="tel" class="form-input prefix" placeholder="8xx xxxx xxxx" @input="form.phone = form.phone.replace(/\D/g, '')"/>
              </div>
              <span v-if="errors.phone" class="err-msg">{{ errors.phone }}</span>
            </div>
            <div class="form-group no-float">
              <label class="form-label">Email <span class="opt">(opsional)</span></label>
              <input v-model="form.email" type="email" class="form-input" placeholder="email@contoh.com"/>
            </div>
          </div>
        </div>

        <!-- STEP 4: Catatan -->
        <div class="form-card">
          <div class="card-head">
            <h2 class="card-title">Catatan Pesanan</h2>
          </div>
          <div class="form-group full" style="padding:0">
            <textarea v-model="form.notes" class="form-input textarea" rows="3" placeholder="Warna, ukuran spesifik, instruksi khusus, dsb. (opsional)"/>
          </div>
        </div>

        <!-- Mobile-only order button — HARUS DI SINI, paling bawah left-col -->
        <div class="mobile-order-btn-wrap">
          <button class="btn-order" @click="submitOrder" :disabled="isSubmitting">
            <transition name="btn-fade" mode="out-in">
              <span v-if="isSubmitting" key="loading" class="btn-submitting">
                <span class="submit-spinner"/>
                <span class="submit-steps">
                  <span :class="{ active: submitStep >= 1 }">Menyimpan pesanan...</span>
                  <span v-if="submitStep >= 2" class="step-fade">Menghubungkan ke Live Chat...</span>
                </span>
              </span>
              <span v-else key="idle" class="btn-idle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke-linejoin="round"/>
                </svg>
                Pesan &amp; Live Chat
              </span>
            </transition>
          </button>
          <p class="wa-note">Pesananmu akan tersimpan & langsung terhubung ke live chat kami untuk konfirmasi.</p>
        </div>

      </div>

      <!-- RIGHT: Summary -->
      <div class="right-col">
        <div class="summary-card">

          <!-- Header toggle (hanya aktif di mobile) -->
          <div class="summary-header" @click="summaryExpanded = !summaryExpanded">
            <div class="summary-header-left">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"
                class="summary-chevron" :class="{ open: summaryExpanded }">
                <polyline points="6 9 12 15 18 9"/>
              </svg>
              <span class="summary-toggle-label">Ringkasan Pesanan</span>
            </div>
            <span class="summary-header-total">{{ formatPrice(grandTotal) }}</span>
          </div>

          <!-- COLLAPSIBLE BODY -->
          <div class="summary-body" :class="{ expanded: summaryExpanded }">

            <h2 class="summary-title">Ringkasan Pesanan</h2>

            <div class="summary-items">
              <div v-for="item in cart.state.items" :key="item.id" class="sum-item">
                <div class="sum-img">
                  <img v-if="item.photo_1" :src="item.photo_1" :alt="item.name"/>
                  <div v-else class="sum-no-img">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18">
                      <rect x="3" y="3" width="18" height="18" rx="2"/>
                      <circle cx="8.5" cy="8.5" r="1.5"/>
                      <polyline points="21 15 16 10 5 21"/>
                    </svg>
                  </div>
                  <span class="sum-qty-corner">{{ item.qty }}</span>
                </div>
                <div class="sum-info">
                  <p class="sum-name">{{ item.name }}</p>
                  <p v-if="item.variant_label && item.variant_names" class="sum-subtitle">
                    {{ item.variant_label }}: {{ item.variant_names }}
                  </p>
                  <div v-if="item.variant_names" class="sum-variant-pills">
                    <span
                      v-for="v in item.variant_names.split(',')"
                      :key="v"
                      class="sum-pill"
                    >{{ v.trim() }} ↓</span>
                  </div>
                  <div class="sum-qty-row">
                    <span class="sum-qty-label">Qty {{ item.qty }}</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10">
                      <polyline points="6 9 12 15 18 9"/>
                    </svg>
                  </div>
                </div>
                <span class="sum-price">{{ formatPrice(item.sell_price * item.qty) }}</span>
              </div>
            </div>

            <!-- Promo -->
            <div class="summary-promo">
              <div v-if="!appliedPromo" class="promo-input-row">
                <div class="promo-field-wrap" :class="{ 'has-error': promoError }">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="promo-icon">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                  </svg>
                  <input
                    v-model="promoCode"
                    type="text"
                    class="promo-field"
                    placeholder="Kode promo"
                    :disabled="isCheckingPromo"
                    @keyup.enter="applyPromo"
                    style="text-transform: uppercase"
                  />
                </div>
                <button class="btn-apply-promo" @click="applyPromo" :disabled="!promoCode.trim() || isCheckingPromo">
                  <span v-if="isCheckingPromo" class="spinner"/>
                  <span v-else>Pakai</span>
                </button>
              </div>
              <p v-if="promoError" class="promo-error">{{ promoError }}</p>
              <div v-if="appliedPromo" class="promo-applied">
                <div class="promo-applied-left">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                  </svg>
                  <span class="promo-applied-code">{{ appliedPromo.code }}</span>
                  <span class="promo-applied-save">hemat {{ formatPrice(discountAmount) }}</span>
                </div>
                <button class="promo-remove-btn" @click="removePromo">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                  </svg>
                </button>
              </div>
            </div>

            <div class="summary-divider"/>

            <div class="price-rows">
              <div class="price-row">
                <span>Subtotal</span>
                <span>{{ formatPrice(cart.totalPrice) }}</span>
              </div>
              <div class="price-row">
                <span>{{ fulfillmentType === 'pickup' ? 'Ambil di toko' : 'Ongkos Kirim' }}</span>
                <template v-if="fulfillmentType === 'pickup'">
                  <span class="pickup-free-badge">GRATIS</span>
                </template>
                <template v-else-if="isCustomShipping">
                  <span class="ongkir-note">Dikonfirmasi via WA</span>
                </template>
                <template v-else>
                  <span v-if="selectedShipping" class="shipping-cost-val">{{ formatPrice(selectedShipping.cost) }}</span>
                  <span v-else class="ongkir-note">Belum dipilih</span>
                </template>
              </div>
              <div v-if="appliedPromo" class="price-row discount-row">
                <span>Diskon ({{ appliedPromo.code }})</span>
                <span class="discount-val">− {{ formatPrice(discountAmount) }}</span>
              </div>
              <div v-if="fulfillmentType === 'delivery' && selectedShipping && !isCustomShipping" class="price-row courier-detail-row">
                <span class="courier-detail-label">{{ selectedShipping.code.toUpperCase() }} {{ selectedShipping.service }}</span>
                <span class="courier-detail-etd">est. {{ selectedShipping.etd }}</span>
              </div>
              <div v-if="fulfillmentType === 'delivery' && isCustomShipping" class="price-row courier-detail-row">
                <span class="courier-detail-label">Atur Sendiri</span>
                <span class="courier-detail-etd">via WhatsApp</span>
              </div>
              <div v-if="fulfillmentType === 'pickup' && selectedBranch" class="price-row courier-detail-row">
                <span class="courier-detail-label">{{ selectedBranch.name }}</span>
                <span class="courier-detail-etd">Ambil langsung</span>
              </div>
            </div>

            <div class="summary-divider"/>

            <div class="total-row">
              <span class="total-label">Total</span>
              <span class="total-val">{{ formatPrice(grandTotal) }}</span>
            </div>
            <p v-if="fulfillmentType === 'delivery' && !selectedShipping && !isCustomShipping" class="total-note">
              *Belum termasuk ongkos kirim
            </p>
            <p v-if="fulfillmentType === 'delivery' && isCustomShipping" class="total-note">
              *Belum termasuk ongkos kirim (dikonfirmasi via WhatsApp)
            </p>

          </div>
          <!-- end summary-body -->

          <!-- SELALU TAMPIL: tombol order -->
          <div class="desktop-order-btn-wrap">
            <button class="btn-order" @click="submitOrder" :disabled="isSubmitting">
              <transition name="btn-fade" mode="out-in">
                <span v-if="isSubmitting" key="loading" class="btn-submitting">
                  <span class="submit-spinner"/>
                  <span class="submit-steps">
                    <span :class="{ active: submitStep >= 1 }">Menyimpan pesanan...</span>
                    <span v-if="submitStep >= 2" class="step-fade">Menghubungkan ke Live Chat...</span>
                  </span>
                </span>
                <span v-else key="idle" class="btn-idle">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke-linejoin="round"/>
                  </svg>
                  Pesan &amp; Live Chat
                </span>
              </transition>
            </button>
            <p class="wa-note">Pesananmu akan tersimpan & langsung terhubung ke live chat kami untuk konfirmasi.</p>
          </div>

        </div>
        <!-- end summary-card -->
      </div>
    </div>

    <CustomerChat />
    <FooterSection />

    <!-- Success Modal -->
    <transition name="modal-fade">
      <div v-if="showSuccess" class="modal-overlay">
        <div class="modal-box">
          <div class="modal-success-anim">
            <div class="success-circle">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="32" height="32">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <div class="success-ripple r1"/>
            <div class="success-ripple r2"/>
          </div>
          <h2 class="modal-title">Pesanan Terkirim!</h2>
          <p class="modal-sub">Pesananmu sudah tersimpan dan kami akan segera membalas lewat live chat untuk konfirmasi.</p>
          <div class="modal-order-badge">
            <span class="order-badge-label">Nomor Invoice</span>
            <span class="order-badge-val">#{{ orderId }}</span>
          </div>
          <div class="modal-wa-hint">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke-linejoin="round"/>
            </svg>
            Live chat customer service sudah terbuka otomatis
          </div>
          <button class="btn-primary modal-back-btn" @click="finishOrder">Kembali ke Home</button>
        </div>
      </div>
    </transition>
  </div>
  </div>
</template>

<script>
import Navbar from '../components/Navbar.vue'
import CartDrawer from '../components/CartDrawer.vue'
import CustomerChat from '../components/chat/ChatWidget.vue'
import FooterSection from '../components/FooterSection.vue'
import { cartStore } from '../store/cartStore'
import axiosInstance from '../axios'
import { useHead } from '@vueuse/head'
import { useSiteSettings } from '../composables/useSiteSettings'
import { divide } from 'lodash'

const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
const ORIGIN_ID = import.meta.env.VITE_RAJAONGKIR_ORIGIN_ID || '25998'
const DEFAULT_WEIGHT = 1000

export default {
  name: 'CheckoutPage',

  components: {
    Navbar,
    CartDrawer,
    CustomerChat,
    FooterSection,
  },

  setup() {
      const { siteName, fetchSettings } = useSiteSettings()
      useHead({ title: `Checkout - ${siteName.value}` })
      fetchSettings() 
      return {} 
  },


  data() {
    return {
      fulfillmentType: 'delivery',
      branches: [],
      selectedBranch: null,
      loadingBranches: false,
      submitStep: 0,
      form: { name: '', phone: '', email: '', address: '', postal_code: '', notes: '' },
      errors: {},
      destinationSearch: '',
      destinationResults: [],
      selectedDestination: null,
      showDestinationDropdown: false,
      loadingDestination: false,
      searchTimeout: null,
      shippingRates: [],
      selectedShipping: null,
      selectedCourier: '',
      loadingRates: false,
      ratesError: null,
      promoCode: '',
      appliedPromo: null,
      discountAmount: 0,
      isCheckingPromo: false,
      promoError: '',
      isSubmitting: false,
      showSuccess: false,
      orderId: null,
      isCustomShipping: false,
      customShippingNote: '',
      loyaltyPhone:   '',
      loyaltyLoading: false,
      loyaltyError:   '',
      loyaltyChecked: false,
      loyaltyData:    null,
      summaryExpanded: false,
    }
  },

  computed: {
    cart() { return cartStore },
    limitedRates() {
      // Grup per kurir, ambil 3 termurah per kurir, lalu flatten dan sort by cost
      const groups = {}
      for (const rate of this.shippingRates) {
        if (!groups[rate.code]) groups[rate.code] = []
        groups[rate.code].push(rate)
      }
      const result = []
      for (const code in groups) {
        const sorted = groups[code].sort((a, b) => a.cost - b.cost)
        result.push(...sorted.slice(0, 2))
      }
      return result.sort((a, b) => a.cost - b.cost)
    },
    pointsWillEarn() {
      // Preview point dari subtotal cart saat ini
      // Setiap kelipatan Rp 100.000 = 3.000 point
      return Math.floor(this.cart.totalPrice / 100000) * 3000
    },
    grandTotal() {
      const shippingCost = this.fulfillmentType === 'pickup'
        ? 0
        : this.isCustomShipping
          ? 0
          : (this.selectedShipping?.cost || 0)
      return cartStore.totalPrice + shippingCost - this.discountAmount
    },
    availableCouriers() { return [...new Set(this.shippingRates.map(r => r.code))] },
    filteredServices() {
      if (!this.selectedCourier) return this.shippingRates
      return this.shippingRates.filter(r => r.code === this.selectedCourier)
    },
  },

  created() {
    this.fetchBranches()
  },

  methods: {
    formatPrice(val) {
      return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(val)
    },

    setFulfillment(type) {
      if (this.fulfillmentType === type) return
      this.fulfillmentType = type
      this.errors = {}
    },

    async fetchBranches() {
      this.loadingBranches = true
      try {
        const res = await axiosInstance.get('/branches')  // ← pakai axiosInstance
        this.branches = res.data?.data || []
      } catch (err) {
        this.branches = []
      } finally {
        this.loadingBranches = false
      }
    },

    async checkLoyaltyBalance() {
    if (this.loyaltyPhone.length < 8) return
    this.loyaltyLoading = true
    this.loyaltyError = ''
    try {
      const res = await axiosInstance.get(`/loyalty/check`, {
        params: { phone: `+62${this.loyaltyPhone}` }
      })
      this.loyaltyData = res.data
      this.loyaltyChecked = true
    } catch (err) {
      this.loyaltyError = err?.response?.data?.message || 'Gagal mengecek saldo point.'
    } finally {
      this.loyaltyLoading = false
    }
  },
 
  resetLoyalty() {
    this.loyaltyPhone   = ''
    this.loyaltyError   = ''
    this.loyaltyChecked = false
    this.loyaltyData    = null
  },

    formatOperatingHours(hours) {
      if (!hours) return ''
      if (typeof hours === 'string') return hours
      if (Array.isArray(hours)) return hours.map(h => `${h.days}: ${h.open}–${h.close}`).join(', ')
      return Object.entries(hours).map(([k, v]) => `${k}: ${v}`).join(', ')
    },

    onDestinationInput() {
      this.selectedDestination = null
      this.shippingRates = []
      this.selectedShipping = null
      this.selectedCourier = ''
      this.ratesError = null
      clearTimeout(this.searchTimeout)
      if (this.destinationSearch.length < 3) {
        this.destinationResults = []
        this.showDestinationDropdown = false
        return
      }
      this.loadingDestination = true
      this.searchTimeout = setTimeout(async () => {
        try {
          const res = await axiosInstance.get(`/rajaongkir/search-destination`, {
            params: { search: this.destinationSearch, limit: 8 }
          })
          this.destinationResults = res.data?.data || []
          this.showDestinationDropdown = this.destinationResults.length > 0
        } catch (err) {
          console.error('Search error:', err)
          this.destinationResults = []
        } finally {
          this.loadingDestination = false
        }
      }, 400)
    },

    selectDestination(item) {
      this.selectedDestination = item
      this.destinationSearch = item.label
      this.showDestinationDropdown = false
      this.form.postal_code = item.zip_code || ''
      this.fetchShippingRates()
    },

    clearDestination() {
      this.selectedDestination = null
      this.destinationSearch = ''
      this.destinationResults = []
      this.form.postal_code = ''
      this.shippingRates = []
      this.selectedShipping = null
      this.selectedCourier = ''
      this.ratesError = null
    },

    onDestinationBlur() {
      setTimeout(() => { this.showDestinationDropdown = false }, 150)
    },

    async fetchShippingRates() {
      if (!this.selectedDestination) return
      this.loadingRates = true
      this.ratesError = null
      this.shippingRates = []
      this.selectedShipping = null
      this.selectedCourier = ''
      const totalWeight = cartStore.state.items.reduce((sum, item) => {
        return sum + ((item.weight || DEFAULT_WEIGHT) * item.qty)
      }, 0) || DEFAULT_WEIGHT
      try {
        const res = await axiosInstance.post(`/rajaongkir/shipping-cost`, {
          origin: ORIGIN_ID,
          destination: this.selectedDestination.id,
          weight: totalWeight,
          price: 'lowest',
        })
        const rates = res.data?.data || []
        this.shippingRates = rates
        if (rates.length > 0) {
          this.selectedCourier = rates[0].code
        } else {
          this.ratesError = 'Tidak ada layanan pengiriman tersedia untuk tujuan ini.'
        }
      } catch (err) {
        this.ratesError = 'Gagal memuat ongkos kirim. Silakan coba lagi.'
      } finally {
        this.loadingRates = false
      }
    },

    async applyPromo() {
      if (!this.promoCode.trim()) return
      if (!this.form.phone || this.form.phone.length < 8) {
        this.promoError = 'Isi nomor WhatsApp terlebih dahulu sebelum menggunakan kode promo.'
        return
      }
      this.isCheckingPromo = true
      this.promoError = ''
      this.appliedPromo = null
      this.discountAmount = 0
      const shippingCost = this.fulfillmentType === 'pickup' ? 0 : (this.selectedShipping?.cost || 0)
      try {
        const res = await axiosInstance.post(`/promo-codes/validate`, {
          code: this.promoCode.trim().toUpperCase(),
          subtotal: cartStore.totalPrice,
          shipping_cost: shippingCost,
          phone: this.form.phone ? `+62${this.form.phone}` : null,
        })
        this.appliedPromo = res.data
        this.discountAmount = res.data.discount_amount
      } catch (err) {
        this.promoError = err?.response?.data?.message || 'Kode promo tidak valid.'
      } finally {
        this.isCheckingPromo = false
      }
    },

    removePromo() {
      this.appliedPromo = null
      this.discountAmount = 0
      this.promoCode = ''
      this.promoError = ''
    },

    selectCustomShipping() {
      this.isCustomShipping = true
      this.selectedShipping = null
    },

    validate() {
      const e = {}
      if (!this.form.name.trim())          e.name  = 'Nama wajib diisi'
      if (!this.form.phone.trim())         e.phone = 'No. WhatsApp wajib diisi'
      else if (this.form.phone.length < 8) e.phone = 'No. WhatsApp tidak valid'
      if (this.fulfillmentType === 'delivery') {
        if (!this.selectedDestination && !this.isCustomShipping) e.destination_id = 'Pilih kelurahan tujuan'
        if (!this.form.address.trim())                           e.address        = 'Alamat lengkap wajib diisi'
        if (!this.selectedShipping && !this.isCustomShipping)   e.shipping       = 'Pilih salah satu layanan pengiriman'
      } else {
        if (!this.selectedBranch)          e.branch_id = 'Pilih cabang pickup'
      }
      this.errors = e
      return Object.keys(e).length === 0
    },

    buildFulfillmentNote() {
      if (this.fulfillmentType === 'pickup') {
        const b = this.selectedBranch
        return (
          `Metode: Ambil di Tempat\n` +
          `Cabang: ${b?.name || '-'}\n` +
          `Alamat Cabang: ${b?.address || '-'}`
        )
      }

      if (this.isCustomShipping) {
        return (
          `Metode: Kirim (Request)\n` +
          `Alamat: ${this.form.address}\n` +
          `Catatan Pengiriman: ${this.customShippingNote || '-'}`
        )
      }

      const d = this.selectedDestination
      const s = this.selectedShipping
      return (
        `Metode: Kirim dengan ${s?.code ? s.code.toUpperCase() : '-'} ${s?.service || ''} (est. ${s?.etd || '-'})\n` +
        `Alamat: ${this.form.address}${d?.label ? `, ${d.label}` : ''}`
      )
    },

    buildOrderChatMessage(orderId) {
      const invoiceUrl = `${window.location.origin}/i/${orderId}`
      const itemsList = cartStore.state.items
        .map(item => `- ${item.name}${item.variant_names ? ` (${item.variant_names})` : ''} x${item.qty}`)
        .join('\n')
      const fulfillmentNote = this.buildFulfillmentNote()

      return (
        `Halo, saya baru saja melakukan pemesanan.\n` +
        `Invoice: #${orderId}\n\n` +
        `${itemsList}\n\n` +
        `${fulfillmentNote}\n\n` +
        `Total: ${this.formatPrice(this.grandTotal)}\n` +
        `Link invoice: ${invoiceUrl}\n\n` +
        `Mohon diproses ya 🙏`
      )
    },

    async sendOrderToChat(orderId) {
      try {
        const { data } = await axiosInstance.post(`/chat/sessions/order`, {
          guest_name:    this.form.name,
          guest_phone:   `0${this.form.phone}`,
          order_message: this.buildOrderChatMessage(orderId),
          subject:       `Pesanan #${orderId}`,
        })

        localStorage.setItem('chat_guest_token', data.guest_token)
        localStorage.setItem('chat_session_uuid', data.data.uuid)

        window.dispatchEvent(new CustomEvent('open-chat', {
          detail: { sessionUuid: data.data.uuid, guestToken: data.guest_token }
        }))
      } catch (err) {
        console.error('Gagal mengirim pesanan ke live chat:', err)
        // Order tetap sukses meski koneksi chat gagal — buka widget biar customer tetap bisa chat manual
        window.dispatchEvent(new CustomEvent('open-chat'))
      }
    },

    async submitOrder() {
      if (!this.validate()) {
        this.$nextTick(() => {
          const el = document.querySelector('.error, .error-wrap')
          if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
        })
        return
      }

      if (this.appliedPromo) {
        const shippingCost = this.fulfillmentType === 'pickup' ? 0 : (this.selectedShipping?.cost || 0)
        try {
          const res = await axiosInstance.post(`/promo-codes/validate`, {
            code: this.appliedPromo.code,
            subtotal: cartStore.totalPrice,
            shipping_cost: shippingCost,
            phone: `+62${this.form.phone}`,
          })
          this.discountAmount = res.data.discount_amount
        } catch (err) {
          this.promoError = err?.response?.data?.message || 'Kode promo tidak valid untuk nomor ini.'
          this.appliedPromo = null
          this.discountAmount = 0
          this.promoCode = ''
          this.$nextTick(() => {
            const el = document.querySelector('.promo-wrap')
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
          })
          return
        }
      }

      this.isSubmitting = true
      this.submitStep = 1

      try {
        let payload = {
          customer_name:    this.form.name,
          customer_phone:   `+62${this.form.phone}`,
          customer_email:   this.form.email || null,
          notes:            this.form.notes || null,
          subtotal:         cartStore.totalPrice,
          total_price:      this.grandTotal,
          promo_code:       this.appliedPromo?.code || null,
          fulfillment_type: this.fulfillmentType,
          items: cartStore.state.items.map(item => ({
            product_id:    item.id,
            variant_id:    item.variant_id || null,
            product_name:  item.name,
            variant_label: item.variant_label || null,
            variant_names: item.variant_names || null,
            qty:           item.qty,
            sell_price:    item.sell_price,
            subtotal:      item.sell_price * item.qty,
          })),
        }

        if (this.fulfillmentType === 'pickup') {
          payload = {
            ...payload,
            branch_id:        this.selectedBranch.id,
            shipping_courier: 'pickup',
            shipping_service: 'PICKUP',
            shipping_name:    'Ambil di Tempat',
            shipping_cost:    0,
          }
        } else {
          const isCustom = this.isCustomShipping
          const d = this.selectedDestination
          const s = this.selectedShipping
          payload = {
            ...payload,
            address:              this.form.address,
            subdistrict:          isCustom ? null : d.subdistrict_name,
            district:             isCustom ? null : d.district_name,
            city:                 isCustom ? null : d.city_name,
            province:             isCustom ? null : d.province_name,
            postal_code:          isCustom ? null : d.zip_code,
            destination_id:       isCustom ? null : d.id,
            shipping_courier:     isCustom ? 'custom' : s.code,
            shipping_service:     isCustom ? 'CUSTOM' : s.service,
            shipping_name:        isCustom ? 'Atur Sendiri' : s.name,
            shipping_cost:        isCustom ? 0 : s.cost,
            shipping_etd:         isCustom ? null : s.etd,
            shipping_custom_note: isCustom ? this.customShippingNote : null,
          }
        }

        const response = await axiosInstance.post(`/orders`, payload)
        const orderId = response.data?.data?.invoice_number || response.data?.data?.id || 'N/A'
        this.orderId = orderId

        this.submitStep = 2
        await this.sendOrderToChat(orderId)

        cartStore.clearCart()
        this.showSuccess = true

      } catch (err) {
        alert(err?.response?.data?.message || 'Terjadi kesalahan. Coba lagi.')
      } finally {
        this.isSubmitting = false
        this.submitStep = 0
      }
    },

    finishOrder() {
      this.showSuccess = false
      this.$router.push('/')
    },
  },
}
</script>

<style src="/resources/css/admin/checkout.css"></style>