<template>
    <!-- ===== MODAL REVISI ORDER ===== -->
    <Transition name="or-modal">
        <div v-if="show" class="or-modal-backdrop" @click.self="handleClose">
            <div class="or-modal or-modal--revise">

                <!-- Header -->
                <div class="or-modal__header">
                    <div class="or-modal__header-left">
                        <div class="or-modal__icon or-modal__icon--purple">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="or-modal__title">Ubah pesanan</h3>
                            <p class="or-modal__subtitle">{{ order?.invoice_number }} — {{ order?.customer_name }}</p>
                        </div>
                    </div>
                    <button @click="handleClose" class="or-modal__close" :disabled="loading">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <!-- Alert error -->
                <div v-if="error" class="or-alert or-alert--error">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ error }}
                </div>

                <!-- Alert permission info -->
                <div v-if="!canChangePrice" class="or-alert or-alert--info">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Anda hanya bisa mengedit qty & produk. Perubahan harga satuan membutuhkan akses manager.
                </div>

                <div class="or-modal__body">

                    <!-- ── Section: Items ── -->
                    <div class="or-section">
                        <div class="or-revise-section-header">
                            <div class="or-section__title">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                                    <line x1="3" y1="6" x2="21" y2="6"/>
                                    <path d="M16 10a4 4 0 0 1-8 0"/>
                                </svg>
                                Item Pesanan
                            </div>
                            <button @click="addItem" class="or-btn-add-item" type="button">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                                Tambah Item
                            </button>
                        </div>

                        <!-- Item rows -->
                        <div class="or-revise-items">
                            <div
                                v-for="(item, idx) in form.items"
                                :key="item._key"
                                class="or-revise-item"
                                :class="{
                                    'or-revise-item--new': !item.id,
                                    'or-revise-item--stock-warn': item._stockWarn
                                }"
                            >
                                <!-- Badges -->
                                <span v-if="!item.id" class="or-item-badge or-item-badge--new">Baru</span>
                                <span v-else-if="isItemModified(item)" class="or-item-badge or-item-badge--edited">Diubah</span>
                                <!-- di hide sesuai request <span v-if="item._stockWarn" class="or-item-badge or-item-badge--warn">Stok kurang</span>-->

                                <!-- Baris 1: Product search + variant + remove -->
                                <div class="or-revise-item__top">
                                    <div class="or-revise-item__fields">

                                        <!-- Product search -->
                                        <div class="or-field or-field--grow or-field--relative">
                                            <label class="or-label">
                                                Produk <span class="or-label__req">*</span>
                                                <span v-if="item.sku" class="or-label-sku">SKU: {{ item.sku }}</span>
                                            </label>
                                            <input
                                                v-model="item.product_name"
                                                type="text"
                                                class="or-input"
                                                :class="{ 'or-input--searching': item._searching }"
                                                placeholder="Ketik nama / SKU produk..."
                                                autocomplete="off"
                                                @input="onProductSearch(item, idx)"
                                                @focus="onProductSearchFocus(item, idx)"
                                                @blur="closeDropdown(idx)"
                                            />
                                            <!-- Loading spinner inside input -->
                                            <div v-if="item._searching" class="or-input-spinner">
                                                <svg class="or-spin" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                                                </svg>
                                            </div>
                                            <!-- Dropdown hasil search -->
                                            <div
                                                v-if="item._showDropdown && item._searchResults.length"
                                                class="or-product-dropdown"
                                            >
                                                <div
                                                    v-for="p in item._searchResults"
                                                    :key="p.id"
                                                    class="or-product-option"
                                                    @mousedown.prevent="selectProduct(item, p)"
                                                >
                                                    <div class="or-product-option__name">{{ p.name }}</div>
                                                    <div class="or-product-option__meta">
                                                        <span class="or-product-option__sku">{{ p.sku }}</span>
                                                        <span class="or-product-option__price">{{ formatPrice(p.sell_price) }}</span>
                                                        <span class="or-product-option__stock">
                                                            Stok: {{ p.track_inventory ? p.available_stock : '∞' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <!-- Jika tidak ada hasil -->
                                                <div v-if="item._searchEmpty" class="or-product-option or-product-option--empty">
                                                    Produk tidak ditemukan
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Nama Varian -->
                                        <div class="or-field or-field--variant">
                                            <label class="or-label">Nama Varian</label>
                                            <input
                                                v-model="item.variant_names"
                                                type="text"
                                                class="or-input"
                                                placeholder="Silver, Carbon.."
                                                @input="markDirty"
                                            />
                                        </div>
                                    </div>

                                    <!-- Tombol hapus item -->
                                    <button
                                        @click="removeItem(idx)"
                                        class="or-revise-item__remove"
                                        title="Hapus item"
                                        type="button"
                                    >
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                            <path d="M10 11v6"/><path d="M14 11v6"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Baris 2: Qty, Harga, Subtotal -->
                                <div class="or-revise-item__bottom">
                                    <div class="or-field">
                                        <label class="or-label">Qty</label>
                                        <input
                                            v-model.number="item.qty"
                                            type="number"
                                            min="1"
                                            class="or-input or-input--num"
                                            @input="onQtyChange(item)"
                                        />
                                    </div>
                                    <div class="or-field">
                                        <label class="or-label">
                                            Harga Satuan
                                            <span v-if="!canChangePrice" class="or-label__locked">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                                </svg>
                                            </span>
                                        </label>
                                        <input
                                            v-model.number="item.sell_price"
                                            type="number"
                                            min="0"
                                            class="or-input or-input--num"
                                            :readonly="!canChangePrice"
                                            :class="{ 'or-input--locked': !canChangePrice }"
                                            @input="onPriceChange(item)"
                                        />
                                    </div>
                                    <div class="or-field">
                                        <label class="or-label">Subtotal</label>
                                        <div class="or-subtotal-display">
                                            {{ formatPrice(item.subtotal) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Stock warning inline -->
                                <!-- Di hide sesuai request untuk backup stock antar cabang
                                <div v-if="item._stockWarn" class="or-stock-warn">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                    </svg>
                                    Stok tersedia: {{ item._availableStock }}. Qty melebihi stok.
                                </div>
                                -->
                            </div>

                            <!-- Empty state -->
                            <div v-if="form.items.length === 0" class="or-revise-empty">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.3">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                                    <line x1="3" y1="6" x2="21" y2="6"/>
                                </svg>
                                <p>Belum ada item. Klik "Tambah Item" untuk menambahkan produk.</p>
                            </div>
                        </div>
                    </div>

                    <!-- ── Section: Pengiriman (hanya muncul jika punya permission) ── -->
                    <div v-if="canChangeCourier" class="or-section">
                        <div class="or-revise-section-header">
                            <div class="or-section__title">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="3" width="15" height="13" rx="1"/>
                                    <path d="M16 8h4l3 3v5h-7V8z"/>
                                    <circle cx="5.5" cy="18.5" r="2.5"/>
                                    <circle cx="18.5" cy="18.5" r="2.5"/>
                                </svg>
                                Pengiriman
                            </div>
                            <div class="or-courier-current">
                                Saat ini: <strong>{{ order?.shipping_courier?.toUpperCase() }} {{ order?.shipping_service }}</strong>
                            </div>
                        </div>

                        <div class="or-revise-courier-fields">
                            <div class="or-field or-field--grow">
                                <label class="or-label">
                                    Kurir & Layanan <span class="or-label__req">*</span>
                                </label>
                                <select
                                    :value="selectedCourierKey"
                                    @change="onCourierOptionChange($event.target.value)"
                                    class="or-input or-select"
                                    :disabled="courierOptionsLoading"
                                >
                                    <option value="">
                                        {{ courierOptionsLoading ? 'Memuat jasa pengiriman...' : '-- Pilih jasa pengiriman --' }}
                                    </option>
                                    <option
                                        v-for="opt in courierOptions"
                                        :key="opt.code + '|' + opt.service"
                                        :value="opt.code + '|' + opt.service"
                                    >
                                        {{ opt.name }}<template v-if="opt.service"> — {{ opt.service }}</template> ({{ opt.code.toUpperCase() }})
                                    </option>
                                </select>
                                <p v-if="!courierOptionsLoading && courierOptions.length === 0" class="or-help-text">
                                    Belum ada jasa pengiriman berkode di Site Settings → Jasa Pengiriman. Tambahkan dulu di sana.
                                </p>
                            </div>
                            <div class="or-field">
                                <label class="or-label">Ongkir (Rp)</label>
                                <input
                                    v-model.number="form.shipping_cost"
                                    type="number"
                                    min="0"
                                    class="or-input or-input--num"
                                    @input="markDirty"
                                />
                            </div>
                            <div class="or-field">
                                <label class="or-label">Estimasi</label>
                                <input
                                    v-model="form.shipping_etd"
                                    type="text"
                                    class="or-input"
                                    placeholder="2-3 hari..."
                                    @input="markDirty"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- ── Section: Catatan Revisi ── -->
                    <div class="or-section">
                        <div class="or-section__title">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                            Catatan Revisi <span class="or-label-optional">(opsional)</span>
                        </div>
                        <textarea
                            v-model="form.note"
                            class="or-textarea"
                            rows="2"
                            placeholder="Contoh: Item A habis, diganti Item B. Customer setuju via WA."
                            maxlength="1000"
                        />
                    </div>

                    <!-- ── Ringkasan Kalkulasi ── -->
                    <div class="or-revise-summary">
                        <div class="or-revise-summary__title">Preview Kalkulasi</div>
                        <div class="or-price-summary">
                            <div class="or-price-row">
                                <span>Subtotal Item</span>
                                <span>{{ formatPrice(computed_subtotal) }}</span>
                            </div>
                            <div class="or-price-row" v-if="order?.discount_amount > 0">
                                <span>Diskon ({{ order?.promo_code }})</span>
                                <span class="or-price-discount">-{{ formatPrice(order?.discount_amount || 0) }}</span>
                            </div>
                            <div class="or-price-row">
                                <span>Ongkir</span>
                                <span>{{ formatPrice(form.shipping_cost || 0) }}</span>
                            </div>
                            <div class="or-price-row or-price-row--total">
                                <span>Total</span>
                                <span :class="{ 'or-total--changed': computed_total !== originalTotal }">
                                    {{ formatPrice(computed_total) }}
                                    <span v-if="computed_total !== originalTotal" class="or-total-diff">
                                        ({{ computed_total > originalTotal ? '+' : '' }}{{ formatPrice(computed_total - originalTotal) }})
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="or-modal__footer">
                    <button @click="handleClose" class="or-btn or-btn--ghost" :disabled="loading">
                        Batal
                    </button>
                    <button
                        v-if="order?.revision_count > 0"
                        @click="openHistory"
                        class="or-btn or-btn--ghost or-btn--history"
                        :disabled="loading"
                        title="Lihat riwayat revisi"
                        type="button"
                    >
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/>
                        </svg>
                        Riwayat ({{ order?.revision_count }}x)
                    </button>
                    <button
                        @click="submitRevise"
                        :disabled="loading || !isDirty || form.items.length === 0 || hasStockWarning"
                        class="or-btn or-btn--primary"
                        type="button"
                    >
                        <svg v-if="loading" class="or-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                        </svg>
                        <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        {{ loading ? 'Menyimpan Revisi...' : 'Simpan Revisi' }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <!-- ===== MODAL RIWAYAT REVISI ===== -->
    <Transition name="or-modal">
        <div v-if="showHistory" class="or-modal-backdrop" @click.self="showHistory = false">
            <div class="or-modal or-modal--sm">
                <div class="or-modal__header">
                    <div class="or-modal__header-left">
                        <div class="or-modal__icon or-modal__icon--purple">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="or-modal__title">Riwayat Revisi</h3>
                            <p class="or-modal__subtitle">{{ order?.invoice_number }}</p>
                        </div>
                    </div>
                    <button @click="showHistory = false" class="or-modal__close">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <div class="or-modal__body">
                    <div v-if="historyLoading" class="or-loading">
                        <svg class="or-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                        </svg>
                        Memuat riwayat...
                    </div>
                    <div v-else-if="historyData.length === 0" class="or-empty">
                        <div class="or-empty__inner">
                            <p>Belum ada riwayat revisi</p>
                        </div>
                    </div>
                    <div v-else class="or-history-list">
                        <div v-for="rev in historyData" :key="rev.id" class="or-history-item">
                            <div class="or-history-item__header">
                                <span class="or-history-item__who">{{ rev.revised_by_name }}</span>
                                <span class="or-history-item__when">{{ formatDate(rev.created_at) }}</span>
                            </div>
                            <ul class="or-history-item__changes">
                                <li v-for="(ch, ci) in rev.changes_summary" :key="ci">{{ ch }}</li>
                            </ul>
                            <div v-if="rev.note" class="or-history-item__note">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                {{ rev.note }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="or-modal__footer">
                    <button @click="showHistory = false" class="or-btn or-btn--ghost">Tutup</button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
import axios from '../../axios.js'

export default {
    name: 'OrderReviseModal',

    props: {
        show:             { type: Boolean, default: false },
        order:            { type: Object,  default: null  },
        canChangePrice:   { type: Boolean, default: false },
        canChangeCourier: { type: Boolean, default: false },
    },

    emits: ['close', 'revised'],

    data() {
        return {
            form: {
                items:            [],
                shipping_courier: '',
                shipping_service: '',
                shipping_name:    '',
                shipping_cost:    0,
                shipping_etd:     '',
                note:             '',
            },

            // Snapshot original untuk deteksi perubahan
            originalItems: [],
            originalTotal: 0,

            isDirty:  false,
            loading:  false,
            error:    '',

            // History modal
            showHistory:    false,
            historyLoading: false,
            historyData:    [],

            // Counter key unik untuk item baru
            _keyCounter:  0,
            // Map debounce timers per item index
            _searchTimers: {},

            // Opsi kurir untuk dropdown revisi — dari Site Settings > Jasa Pengiriman
            // (BUKAN dari RajaOngkir)
            courierOptions:        [],
            courierOptionsLoading: false,
        }
    },

    computed: {
        computed_subtotal() {
            return this.form.items.reduce((sum, item) => sum + (item.subtotal || 0), 0)
        },

        computed_total() {
            const discount = this.order?.discount_amount || 0
            return Math.max(0, this.computed_subtotal + (this.form.shipping_cost || 0) - discount)
        },

        hasStockWarning() {
            return this.form.items.some(i => i._stockWarn)
        },

        // Value select saat ini, format "kode|layanan"
        selectedCourierKey() {
            if (!this.form.shipping_courier) return ''
            return `${this.form.shipping_courier}|${this.form.shipping_service || ''}`
        },
    },

    watch: {
        show(val) {
            if (val && this.order) {
                this.initForm()
            }
        },
        order(val) {
            if (val && this.show) {
                this.initForm()
            }
        },
    },

    methods: {
        // ─── Init ─────────────────────────────────────────────────────────────

        initForm() {
            this.error   = ''
            this.isDirty = false

            const items = (this.order.items || []).map(item => ({
                _key:            item.id,
                id:              item.id,
                product_id:      item.product_id,
                product_name:    item.product_name,
                sku:             item.sku || '',
                variant_label:   item.variant_label || '',
                variant_names:   item.variant_names || '',
                qty:             item.qty,
                sell_price:      item.sell_price,
                subtotal:        item.subtotal,
                // Snapshot untuk deteksi modifikasi
                _original_price: item.sell_price,
                _original_qty:   item.qty,
                // Search state
                _searching:      false,
                _showDropdown:   false,
                _searchResults:  [],
                _searchEmpty:    false,
                _stockWarn:      false,
                _availableStock: null,
                _trackInventory: false,
            }))

            this.form = {
                items,
                shipping_courier: this.order.shipping_courier || '',
                shipping_service: this.order.shipping_service || '',
                shipping_name:    this.order.shipping_name    || '',
                shipping_cost:    this.order.shipping_cost    || 0,
                shipping_etd:     this.order.shipping_etd     || '',
                note:             '',
            }

            this.originalItems = JSON.parse(JSON.stringify(items))
            this.originalTotal = this.order.total_price || 0

            if (this.canChangeCourier) {
                this.fetchCourierOptions()
            }
        },

        // ─── Kurir (dari Site Settings > Jasa Pengiriman) ───────────────────────

        async fetchCourierOptions() {
            this.courierOptionsLoading = true
            try {
                const res = await axios.get('/orders/revise/couriers')
                this.courierOptions = res.data || []
            } catch (e) {
                this.courierOptions = []
            } finally {
                this.courierOptionsLoading = false
            }
        },

        onCourierOptionChange(value) {
            if (!value) {
                this.form.shipping_courier = ''
                this.form.shipping_service = ''
                this.form.shipping_name    = ''
                this.markDirty()
                return
            }
            const [code, service] = value.split('|')
            const opt = this.courierOptions.find(
                o => o.code === code && (o.service || '') === (service || '')
            )
            this.form.shipping_courier = code
            this.form.shipping_service = service || ''
            this.form.shipping_name    = opt ? opt.name : ''
            this.markDirty()
        },

        // ─── Product Search ───────────────────────────────────────────────────

        onProductSearchFocus(item, idx) {
            // Kalau sudah ada hasil sebelumnya, tampilkan lagi
            if (item._searchResults.length) {
                item._showDropdown = true
            }
            // Kalau input sudah ada isi, langsung search
            if (item.product_name?.trim().length >= 2) {
                this.doSearch(item, idx)
            }
        },

        onProductSearch(item, idx) {
            item._showDropdown = false
            item._searchEmpty  = false
            this.markDirty()

            // Reset product_id karena user mengetik manual (belum pilih dari dropdown)
            item.product_id = null

            if (this._searchTimers[idx]) clearTimeout(this._searchTimers[idx])

            const q = item.product_name?.trim()
            if (!q || q.length < 2) {
                item._searching    = false
                item._searchResults = []
                return
            }

            this._searchTimers[idx] = setTimeout(() => this.doSearch(item, idx), 300)
        },

        async doSearch(item, idx) {
            item._searching = true
            try {
                const res = await axios.get('/orders/products/search', { params: { q: item.product_name?.trim() } })
                item._searchResults = res.data?.data || []
                item._searchEmpty   = item._searchResults.length === 0
                item._showDropdown  = true
            } catch {
                item._searchResults = []
                item._searchEmpty   = false
            } finally {
                item._searching = false
            }
        },

        selectProduct(item, product) {
            item.product_id      = product.id
            item.variant_id      = product.variant_id || null   
            item.product_name    = product.name
            item.sku             = product.sku || ''
            item.variant_label   = product.variant_label || ''
            item.variant_names   = product.variant_names || ''
            // Harga dari DB — bukan dari user input
            item.sell_price      = product.sell_price
            item._trackInventory = product.track_inventory
            item._availableStock = product.available_stock
            item._showDropdown   = false
            item._searchResults  = []
            item._searchEmpty    = false

            // Recalculate subtotal dengan harga dari DB
            item.subtotal = (item.qty || 1) * item.sell_price

            this.checkStockWarning(item)
            this.markDirty()
        },

        closeDropdown(idx) {
            // Delay 200ms agar mousedown pada option sempat terpanggil sebelum blur
            setTimeout(() => {
                if (this.form.items[idx]) {
                    this.form.items[idx]._showDropdown = false
                }
            }, 200)
        },

        checkStockWarning(item) {
            // Stok bisa backup antar cabang — warning dinonaktifkan
            item._stockWarn = false
        },

        // ─── Item Management ──────────────────────────────────────────────────

        addItem() {
            this._keyCounter++
            this.form.items.push({
                _key:            `new_${this._keyCounter}`,
                id:              null,
                product_id:      null,
                product_name:    '',
                sku:             '',
                variant_label:   '',
                variant_names:   '',
                qty:             1,
                sell_price:      0,
                subtotal:        0,
                _original_price: 0,
                _original_qty:   1,
                _searching:      false,
                _showDropdown:   false,
                _searchResults:  [],
                _searchEmpty:    false,
                _stockWarn:      false,
                _availableStock: null,
                _trackInventory: false,
            })
            this.markDirty()
            // Fokus ke input produk yang baru ditambahkan
            this.$nextTick(() => {
                const inputs = this.$el.querySelectorAll('.or-revise-item input[type="text"]')
                if (inputs.length) inputs[inputs.length - 2]?.focus()
            })
        },

        removeItem(idx) {
            if (this.form.items.length === 1) {
                this.error = 'Order harus memiliki minimal 1 item.'
                return
            }
            // Bersihkan timer search untuk index ini
            if (this._searchTimers[idx]) {
                clearTimeout(this._searchTimers[idx])
                delete this._searchTimers[idx]
            }
            this.form.items.splice(idx, 1)
            this.markDirty()
        },

        onQtyChange(item) {
            item.subtotal = Math.max(0, (item.qty || 0) * (item.sell_price || 0))
            this.checkStockWarning(item)
            this.markDirty()
        },

        onPriceChange(item) {
            item.subtotal = Math.max(0, (item.qty || 0) * (item.sell_price || 0))
            this.markDirty()
        },

        markDirty() {
            this.isDirty = true
            this.error   = ''
        },

        isItemModified(item) {
            if (!item.id) return false
            const orig = this.originalItems.find(o => o.id === item.id)
            if (!orig) return false
            return orig.qty           !== item.qty
                || orig.sell_price    !== item.sell_price
                || orig.product_name  !== item.product_name
                || orig.variant_names !== item.variant_names
        },

        // ─── Validation ───────────────────────────────────────────────────────

        validate() {
            if (this.form.items.length === 0) {
                this.error = 'Order harus memiliki minimal 1 item.'
                return false
            }

            for (let i = 0; i < this.form.items.length; i++) {
                const item = this.form.items[i]

                if (!item.product_name?.trim()) {
                    this.error = `Item #${i + 1}: nama produk wajib diisi.`
                    return false
                }
                if (!item.qty || item.qty < 1) {
                    this.error = `Item #${i + 1}: qty minimal 1.`
                    return false
                }
                if (item.sell_price < 0) {
                    this.error = `Item #${i + 1}: harga tidak boleh negatif.`
                    return false
                }
                /* di hide karena sesuai request
                if (item._stockWarn) {
                    this.error = `Item #${i + 1} (${item.product_name}): stok tidak mencukupi. Tersedia: ${item._availableStock}.`
                    return false
                }
                */

                // Autocorrect subtotal sebelum kirim
                item.subtotal = (item.qty || 0) * (item.sell_price || 0)
            }

            if (this.canChangeCourier) {
                if (!this.form.shipping_courier?.trim()) {
                    this.error = 'Pilih jasa pengiriman terlebih dahulu.'
                    return false
                }
                if ((this.form.shipping_cost || 0) < 0) {
                    this.error = 'Ongkir tidak boleh negatif.'
                    return false
                }
            }

            return true
        },

        // ─── Submit ───────────────────────────────────────────────────────────

        async submitRevise() {
            this.error = ''
            if (!this.validate()) return

            this.loading = true

            try {
                const payload = {
                    items: this.form.items.map(item => ({
                        id:            item.id    || null,
                        product_id:    item.product_id || null,
                        variant_id:    item.variant_id || null,
                        product_name:  item.product_name.trim(),
                        variant_label: item.variant_label?.trim() || null,
                        variant_names: item.variant_names?.trim() || null,
                        qty:           item.qty,
                        sell_price:    item.sell_price,
                        subtotal:      item.subtotal,
                    })),
                    note: this.form.note?.trim() || null,
                }

                // Hanya sertakan data kurir jika user punya permission
                if (this.canChangeCourier) {
                    payload.shipping_courier = this.form.shipping_courier
                    payload.shipping_service = this.form.shipping_service
                    payload.shipping_name    = this.form.shipping_name
                    payload.shipping_cost    = this.form.shipping_cost
                    payload.shipping_etd     = this.form.shipping_etd || null
                }

                const res = await axios.patch(`/orders/${this.order.id}/revise`, payload)

                this.$emit('revised', res.data?.data)
                this.$emit('close')

            } catch (e) {
                // Tampilkan error stok dari backend jika ada
                const backendErrors = e.response?.data?.errors
                if (backendErrors && Array.isArray(backendErrors)) {
                    this.error = backendErrors.map(e => e.message).join(' | ')
                } else {
                    this.error = e.response?.data?.message || 'Gagal menyimpan revisi. Coba lagi.'
                }
            } finally {
                this.loading = false
            }
        },

        // ─── History ──────────────────────────────────────────────────────────

        async openHistory() {
            this.showHistory    = true
            this.historyLoading = true
            this.historyData    = []

            try {
                const res        = await axios.get(`/orders/${this.order.id}/revisions`)
                this.historyData = res.data?.data || []
            } catch {
                this.historyData = []
            } finally {
                this.historyLoading = false
            }
        },

        // ─── Misc ─────────────────────────────────────────────────────────────

        handleClose() {
            if (this.loading) return
            if (this.isDirty) {
                if (!confirm('Ada perubahan yang belum disimpan. Yakin tutup?')) return
            }
            // Bersihkan semua timer sebelum tutup
            Object.values(this._searchTimers).forEach(t => clearTimeout(t))
            this._searchTimers = {}
            this.$emit('close')
        },

        formatPrice(val) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
            }).format(val || 0)
        },

        formatDate(val) {
            return new Date(val).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
            })
        },
    },
}
</script>

<style scoped>
/* ─── Backdrop & Modal shell ─────────────────────────────────────────────── */
.or-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(17, 24, 39, 0.55);
    backdrop-filter: blur(3px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.or-modal {
    width: 100%;
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow:
        0 25px 50px -12px rgba(0,0,0,.25),
        0 0 0 1px rgba(255,255,255,.05);
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 32px);
}

.or-modal--revise {
    width: min(720px, calc(100vw - 32px));
    max-width: 720px;
}

.or-modal--sm {
    width: min(560px, calc(100vw - 32px));
    max-width: 560px;
}

/* ─── Header ─────────────────────────────────────────────────────────────── */
.or-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px;
    border-bottom: 1px solid #e5e7eb;
    background: #ffffff;
    flex-shrink: 0;
}

.or-modal__header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.or-modal__icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.or-modal__icon--purple {
    background: #f5f3ff;
    color: #7c3aed;
}

.or-modal__title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}

.or-modal__subtitle {
    margin: 2px 0 0;
    font-size: 12px;
    color: #9ca3af;
}

.or-modal__close {
    width: 34px;
    height: 34px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #ffffff;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .15s;
    flex-shrink: 0;
}

.or-modal__close:hover {
    background: #f9fafb;
    color: #111827;
}

/* ─── Alerts ─────────────────────────────────────────────────────────────── */
.or-alert--info,
.or-alert--error {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    font-size: 13px;
    flex-shrink: 0;
}

.or-alert--info {
    background: #eff6ff;
    border-bottom: 1px solid #bfdbfe;
    color: #1e40af;
}

.or-alert--error {
    background: #fef2f2;
    border-bottom: 1px solid #fecaca;
    color: #991b1b;
}

/* ─── Body ───────────────────────────────────────────────────────────────── */
.or-modal__body {
    padding: 20px 22px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
    flex: 1;
}

/* ─── Footer ─────────────────────────────────────────────────────────────── */
.or-modal__footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 22px;
    border-top: 1px solid #e5e7eb;
    background: #ffffff;
    flex-shrink: 0;
}

/* ─── Buttons ────────────────────────────────────────────────────────────── */
.or-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    height: 38px;
    padding: 0 16px;
    border-radius: 10px;
    border: 1px solid transparent;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}

.or-btn:disabled {
    opacity: .55;
    cursor: not-allowed;
}

.or-btn--primary {
    background: #6366f1;
    color: #ffffff;
}

.or-btn--primary:hover:not(:disabled) {
    background: #4f46e5;
}

.or-btn--ghost {
    background: #ffffff;
    border-color: #e5e7eb;
    color: #374151;
}

.or-btn--ghost:hover:not(:disabled) {
    background: #f9fafb;
}

.or-btn--history {
    margin-right: auto;
}

.or-btn-add-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border: 1.5px dashed #6366f1;
    border-radius: 7px;
    background: #f0f0ff;
    color: #4f46e5;
    font-size: 12px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: all .15s;
}

.or-btn-add-item:hover {
    background: #e0e7ff;
    border-color: #4f46e5;
}

/* ─── Section ────────────────────────────────────────────────────────────── */
.or-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.or-section__title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6b7280;
}

.or-revise-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.or-courier-current {
    font-size: 12px;
    color: #9ca3af;
}

.or-courier-current strong {
    color: #374151;
}

/* ─── Form elements ──────────────────────────────────────────────────────── */
.or-field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.or-field--grow    { flex: 2; min-width: 150px; }
.or-field--variant { flex: 1; min-width: 100px; }

.or-field--relative {
    position: relative;
}

.or-label {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 4px;
}

.or-label__req {
    color: #dc2626;
}

.or-label__locked {
    display: inline-flex;
    align-items: center;
    color: #9ca3af;
}

.or-label-optional {
    font-size: 11px;
    color: #9ca3af;
    font-weight: 400;
    margin-left: 4px;
}

.or-label-sku {
    font-size: 10px;
    color: #9ca3af;
    font-weight: 400;
    font-family: monospace;
    margin-left: 4px;
}

.or-input {
    padding: 7px 10px;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
    font-size: 13px;
    color: #111827;
    background: #fff;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    transition: border-color .15s;
    font-family: inherit;
}

.or-input:focus {
    border-color: #6366f1;
}

.or-input--num {
    width: 100px;
    text-align: right;
}

.or-input--locked {
    background: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
}

.or-select {
    cursor: pointer;
    appearance: auto;
}

.or-select:disabled {
    background: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
}

.or-help-text {
    font-size: 11px;
    color: #d97706;
    margin: 2px 0 0;
}

.or-input--searching {
    padding-right: 30px;
}

.or-input-spinner {
    position: absolute;
    right: 10px;
    bottom: 10px;
    color: #9ca3af;
    pointer-events: none;
    line-height: 1;
}

.or-textarea {
    width: 100%;
    min-height: 80px;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #ffffff;
    color: #111827;
    font-size: 13px;
    font-family: inherit;
    resize: vertical;
    outline: none;
    transition: border-color .15s;
    box-sizing: border-box;
}

.or-textarea:focus {
    border-color: #6366f1;
}

.or-subtotal-display {
    padding: 7px 10px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 700;
    color: #111827;
    white-space: nowrap;
    min-width: 120px;
    text-align: right;
}

/* ─── Product dropdown ───────────────────────────────────────────────────── */
.or-product-dropdown {
    position: absolute;
    top: calc(100% + 2px);
    left: 0;
    right: 0;
    z-index: 200;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
    max-height: 240px;
    overflow-y: auto;
}

.or-product-option {
    padding: 9px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background .1s;
}

.or-product-option:last-child {
    border-bottom: none;
}

.or-product-option:hover {
    background: #f5f3ff;
}

.or-product-option--empty {
    font-size: 12px;
    color: #9ca3af;
    cursor: default;
    text-align: center;
}

.or-product-option--empty:hover {
    background: transparent;
}

.or-product-option__name {
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 3px;
}

.or-product-option__meta {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.or-product-option__sku {
    font-size: 11px;
    color: #9ca3af;
    font-family: monospace;
}

.or-product-option__price {
    font-size: 11px;
    font-weight: 600;
    color: #4f46e5;
}

.or-product-option__stock {
    font-size: 11px;
    color: #6b7280;
}

.or-product-option__stock--low {
    color: #dc2626;
    font-weight: 600;
}

/* ─── Item rows ──────────────────────────────────────────────────────────── */
.or-revise-items {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.or-revise-item {
    position: relative;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: border-color .15s;
}

.or-revise-item:hover {
    border-color: #d1d5db;
}

.or-revise-item--new {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.or-revise-item--stock-warn {
    border-color: #fbbf24;
}

.or-item-badge {
    position: absolute;
    top: -8px;
    left: 12px;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
}

/* Badges bisa lebih dari satu, geser yang kedua */
.or-item-badge + .or-item-badge {
    left: auto;
    right: 12px;
}

.or-item-badge--new    { background: #dcfce7; color: #166534; }
.or-item-badge--edited { background: #fef3c7; color: #92400e; }
.or-item-badge--warn   { background: #fef3c7; color: #92400e; }

.or-revise-item__top {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    padding-top: 4px; /* ruang untuk badge */
}

.or-revise-item__fields {
    display: flex;
    gap: 8px;
    flex: 1;
    flex-wrap: wrap;
}

.or-revise-item__remove {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #fca5a5;
    border-radius: 7px;
    background: #fff;
    color: #dc2626;
    cursor: pointer;
    transition: all .15s;
    margin-bottom: 1px;
}

.or-revise-item__remove:hover {
    background: #fef2f2;
}

.or-revise-item__bottom {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    flex-wrap: wrap;
}

.or-stock-warn {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #b45309;
    padding: 6px 10px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 7px;
}

/* ─── Courier fields ─────────────────────────────────────────────────────── */
.or-revise-courier-fields {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.or-revise-courier-fields .or-field {
    flex: 1;
    min-width: 130px;
}

/* ─── Summary ────────────────────────────────────────────────────────────── */
.or-revise-summary {
    background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
    border: 1px solid #ddd6fe;
    border-radius: 12px;
    padding: 16px;
}

.or-revise-summary__title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #6d28d9;
    margin-bottom: 10px;
}

.or-price-summary {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 14px;
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
}

.or-revise-summary .or-price-summary {
    background: transparent;
    border: none;
    padding: 0;
}

.or-price-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    font-size: 13px;
    color: #4b5563;
}

.or-price-row--total {
    padding-top: 10px;
    border-top: 1px dashed #d1d5db;
    font-size: 15px;
    font-weight: 700;
    color: #111827;
}

.or-price-discount {
    color: #dc2626;
}

.or-total--changed {
    color: #7c3aed;
    font-weight: 800;
}

.or-total-diff {
    font-size: 11px;
    color: #9ca3af;
    font-weight: 500;
    margin-left: 6px;
}

/* ─── Empty & loading states ─────────────────────────────────────────────── */
.or-revise-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 32px;
    background: #f9fafb;
    border: 1.5px dashed #e5e7eb;
    border-radius: 10px;
    text-align: center;
}

.or-revise-empty p {
    font-size: 13px;
    color: #9ca3af;
    margin: 0;
}

.or-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 32px;
    font-size: 13px;
    color: #6b7280;
}

.or-empty {
    padding: 28px;
}

.or-empty__inner {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 120px;
    border: 1.5px dashed #e5e7eb;
    border-radius: 12px;
    background: #f9fafb;
    text-align: center;
    color: #9ca3af;
    font-size: 13px;
}

/* ─── History list ───────────────────────────────────────────────────────── */
.or-history-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.or-history-item {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.or-history-item__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.or-history-item__who {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
}

.or-history-item__when {
    font-size: 11px;
    color: #9ca3af;
}

.or-history-item__changes {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.or-history-item__changes li {
    font-size: 12px;
    color: #6b7280;
    padding-left: 14px;
    position: relative;
}

.or-history-item__changes li::before {
    content: '•';
    position: absolute;
    left: 0;
    color: #6366f1;
}

.or-history-item__note {
    display: flex;
    align-items: flex-start;
    gap: 5px;
    font-size: 12px;
    color: #9ca3af;
    font-style: italic;
    margin-top: 2px;
}

.or-history-item__note svg {
    flex-shrink: 0;
    margin-top: 1px;
}

/* ─── Spinner ────────────────────────────────────────────────────────────── */
.or-spin {
    animation: or-spin .8s linear infinite;
}

@keyframes or-spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}

/* ─── Transition ─────────────────────────────────────────────────────────── */
.or-modal-enter-active,
.or-modal-leave-active {
    transition: all .18s ease;
}

.or-modal-enter-from,
.or-modal-leave-to {
    opacity: 0;
    transform: scale(.96);
}

/* ─── Responsive ─────────────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .or-modal--revise,
    .or-modal--sm {
        width: 100%;
        max-width: 100%;
        border-radius: 12px 12px 0 0;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        max-height: 92vh;
    }

    .or-modal-backdrop {
        align-items: flex-end;
        padding: 0;
    }

    .or-revise-item__fields {
        flex-direction: column;
    }

    .or-revise-item__bottom {
        flex-direction: column;
    }

    .or-input--num {
        width: 100%;
        text-align: left;
    }

    .or-revise-courier-fields {
        flex-direction: column;
    }

    .or-modal__footer {
        flex-wrap: wrap;
    }

    .or-btn--history {
        order: 3;
        width: 100%;
        justify-content: center;
    }
}
</style>