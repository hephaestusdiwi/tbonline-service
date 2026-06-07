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
            <h2 class="card-title">Cara Terima</h2>
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

      </div>

      <!-- RIGHT: Summary -->
      <div class="right-col">
        <div class="summary-card">
          <h2 class="summary-title">Ringkasan Pesanan</h2>

          <div class="summary-items">
            <div v-for="item in cart.state.items" :key="item.id" class="sum-item">

              <!-- Image -->
              <div class="sum-img">
                <img v-if="item.photo_1" :src="item.photo_1" :alt="item.name"/>
                <div v-else class="sum-no-img">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                  </svg>
                </div>
                <!-- Qty badge di pojok bawah kiri -->
                <span class="sum-qty-corner">{{ item.qty }}</span>
              </div>

              <!-- Info -->
              <div class="sum-info">
                <p class="sum-name">{{ item.name }}</p>
                <p v-if="item.variant_label && item.variant_names" class="sum-subtitle">
                  {{ item.variant_label }}: {{ item.variant_names }}
                </p>
                <!-- Variant pills -->
                <div v-if="item.variant_names" class="sum-variant-pills">
                  <span
                    v-for="v in item.variant_names.split(',')"
                    :key="v"
                    class="sum-pill"
                  >{{ v.trim() }} ↓</span>
                </div>
                <!-- Qty row -->
                <div class="sum-qty-row">
                  <span class="sum-qty-label">Qty {{ item.qty }}</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10">
                    <polyline points="6 9 12 15 18 9"/>
                  </svg>
                </div>
              </div>

              <!-- Price -->
              <span class="sum-price">{{ formatPrice(item.sell_price * item.qty) }}</span>
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

          <!-- Promo — dipindah ke dalam summary card -->
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

          <template id="loyalty-widget-snippet">
 
            <!-- Loyalty Point Info Box -->
            <div class="loyalty-section">
              <div class="loyalty-header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
                <span class="loyalty-label">Loyalty Point</span>
              </div>
          
              <!-- Cek saldo: input nomor HP -->
              <div v-if="!loyaltyChecked" class="loyalty-check-row">
                <p class="loyalty-hint">Masukkan nomor HP untuk cek saldo point kamu</p>
                <div class="loyalty-input-row">
                  <div class="loyalty-field-wrap" :class="{ 'has-error': loyaltyError }">
                    <span class="input-prefix" style="font-size:12px">+62</span>
                    <input
                      v-model="loyaltyPhone"
                      type="tel"
                      class="loyalty-field"
                      placeholder="8xx xxxx xxxx"
                      :disabled="loyaltyLoading"
                      @input="loyaltyPhone = loyaltyPhone.replace(/\D/g, '')"
                      @keyup.enter="checkLoyaltyBalance"
                    />
                  </div>
                  <button
                    class="btn-check-loyalty"
                    @click="checkLoyaltyBalance"
                    :disabled="loyaltyPhone.length < 8 || loyaltyLoading"
                  >
                    <span v-if="loyaltyLoading" class="spinner"/>
                    <span v-else>Cek</span>
                  </button>
                </div>
                <p v-if="loyaltyError" class="loyalty-error">{{ loyaltyError }}</p>
              </div>
          
              <!-- Hasil: tampilkan saldo -->
              <div v-else class="loyalty-result">
                <div class="loyalty-balance-box">
                  <div class="loyalty-balance-left">
                    <span class="loyalty-balance-label">Saldo Point kamu</span>
                    <span class="loyalty-balance-phone">{{ loyaltyData.phone }}</span>
                  </div>
                  <div class="loyalty-balance-right">
                    <span class="loyalty-balance-value">{{ loyaltyData.balance.toLocaleString('id-ID') }}</span>
                    <span class="loyalty-balance-unit">point</span>
                  </div>
                </div>
          
                <!-- Alert: point segera kadaluarsa -->
                <div v-if="loyaltyData.expiring_soon > 0" class="loyalty-expiring-alert">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                  </svg>
                  <span>{{ loyaltyData.expiring_soon.toLocaleString('id-ID') }} point akan kadaluarsa dalam 30 hari</span>
                </div>
          
                <!-- Info point yang akan didapat dari order ini -->
                <div class="loyalty-earn-preview">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                    <line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/>
                  </svg>
                  <span>Dari pesanan ini kamu akan mendapat <strong>+{{ pointsWillEarn.toLocaleString('id-ID') }} point</strong> (berlaku 3 bulan)</span>
                </div>
          
                <!-- Info cara pakai -->
                <div class="loyalty-redeem-info">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                  </svg>
                  <span>Untuk menggunakan point, hubungi staff kami via WhatsApp. 1 point = Rp 1 diskon.</span>
                </div>
          
                <!-- Riwayat singkat (3 terakhir) -->
                <div v-if="loyaltyData.history && loyaltyData.history.length > 0" class="loyalty-history">
                  <p class="loyalty-history-title">Riwayat Terakhir</p>
                  <div
                    v-for="h in loyaltyData.history.slice(0, 3)"
                    :key="h.id"
                    class="loyalty-history-item"
                  >
                    <div class="loyalty-history-left">
                      <span class="loyalty-history-type" :class="h.type">
                        {{ h.type === 'earn' ? '+ Earn' : h.type === 'expire' ? '− Hangus' : '− Pakai' }}
                      </span>
                      <span class="loyalty-history-desc">{{ h.description }}</span>
                    </div>
                    <div class="loyalty-history-right">
                      <span class="loyalty-history-points" :class="h.points > 0 ? 'positive' : 'negative'">
                        {{ h.points > 0 ? '+' : '' }}{{ h.points.toLocaleString('id-ID') }}
                      </span>
                      <span class="loyalty-history-date">{{ h.created_at }}</span>
                    </div>
                  </div>
                </div>
          
                <!-- Tombol reset -->
                <button class="loyalty-reset-btn" @click="resetLoyalty">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                  </svg>
                  Ganti nomor
                </button>
              </div>
            </div>
          
          </template>

          <button class="btn-order" @click="submitOrder" :disabled="isSubmitting">
            <transition name="btn-fade" mode="out-in">
              <span v-if="isSubmitting" key="loading" class="btn-submitting">
                <span class="submit-spinner"/>
                <span class="submit-steps">
                  <span :class="{ active: submitStep >= 1 }">Menyimpan pesanan...</span>
                  <span v-if="submitStep >= 2" class="step-fade">Membuka WhatsApp...</span>
                </span>
              </span>
              <span v-else key="idle" class="btn-idle">
                <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                  <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.528 5.851L.057 23.547a.75.75 0 0 0 .916.919l5.808-1.517A11.943 11.943 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.708 9.708 0 0 1-4.953-1.354l-.355-.21-3.678.961.98-3.589-.23-.37A9.718 9.718 0 0 1 2.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/>
                </svg>
                Pesan via WhatsApp
              </span>
            </transition>
          </button>
          <p class="wa-note">Pesananmu akan tersimpan & dikirim ke WhatsApp kami untuk konfirmasi.</p>
        </div>
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
          <p class="modal-sub">Pesananmu sudah tersimpan dan kami akan segera menghubungimu via WhatsApp untuk konfirmasi.</p>
          <div class="modal-order-badge">
            <span class="order-badge-label">Nomor Invoice</span>
            <span class="order-badge-val">#{{ orderId }}</span>
          </div>
          <div class="modal-wa-hint">
            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
              <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.528 5.851L.057 23.547a.75.75 0 0 0 .916.919l5.808-1.517A11.943 11.943 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.708 9.708 0 0 1-4.953-1.354l-.355-.21-3.678.961.98-3.589-.23-.37A9.718 9.718 0 0 1 2.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/>
            </svg>
            WhatsApp terbuka otomatis di tab baru
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
const COURIERS = 'jne:jnt:sicepat:tiki:pos:anteraja'
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
      const { siteName, storeWhatsapp, fetchSettings } = useSiteSettings()
      useHead({ title: `Checkout - ${siteName.value}` })
      fetchSettings() 
      return { storeWhatsapp } 
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
      if (Array.isArray(hours)) return hours.map(h => `${h.day}: ${h.open}–${h.close}`).join(', ')
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
          courier: COURIERS,
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

    buildWhatsAppMessage(orderId) {
      const invoiceUrl = `${window.location.origin}/i/${orderId}`

      const message =
        `Halo admin, saya sudah melakukan pemesanan.\n` +
        `Invoice: #${orderId}\n\n` +
        `🔗 ${invoiceUrl}\n` +
        `Mohon diproses ya 🙏`

      return encodeURIComponent(message)
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
        const waNumber = this.storeWhatsapp || '6281293139223'
        window.open(`https://wa.me/${waNumber}?text=${this.buildWhatsAppMessage(orderId)}`, '_blank')

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