<template>
    <Transition name="mo-modal">
        <div v-if="show" class="mo-backdrop" :style="loading ? 'pointer-events:none' : ''">
            <div class="mo-modal">
                <div class="mo-header">
                    <div class="mo-header__left">
                        <div class="mo-header__icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </div>
                        <div>
                            <h3 class="mo-header__title">Tambah Order Manual</h3>
                            <p class="mo-header__subtitle">Input order offline / titipan customer</p>
                        </div>
                    </div>
                    <button @click="handleClose" class="mo-close" :disabled="loading">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <div v-if="error" class="mo-alert">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ error }}
                </div>

                <div class="mo-body">
                    <!-- Data Pelanggan -->
                    <div class="mo-section">
                        <div class="mo-section__title">Data Pelanggan</div>
                        <div class="mo-grid-2">
                            <div class="mo-field">
                                <label class="mo-label">Nama <span class="mo-req">*</span></label>
                                <input v-model="form.customer_name" type="text" class="mo-input" placeholder="Nama pelanggan"/>
                            </div>
                            <div class="mo-field">
                                <label class="mo-label">No. WhatsApp <span class="mo-req">*</span></label>
                                <input v-model="form.customer_phone" type="text" class="mo-input" placeholder="08xxxxxxxxxx"/>
                            </div>
                            <div class="mo-field mo-grid-2__span">
                                <label class="mo-label">Email (opsional)</label>
                                <input v-model="form.customer_email" type="email" class="mo-input" placeholder="email@contoh.com"/>
                            </div>
                        </div>
                    </div>

                    <!-- Produk -->
                    <div class="mo-section">
                        <div class="mo-section__title">Produk</div>
                        <div class="mo-search-box">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input v-model="productSearch" type="text" placeholder="Cari nama atau SKU produk..." class="mo-search-input" @input="onProductSearch"/>
                        </div>
                        <div v-if="searching" class="mo-search-loading">Mencari...</div>
                        <div v-else-if="productResults.length" class="mo-search-results">
                            <div v-for="p in productResults" :key="`${p.id}-${p.variant_id}`" class="mo-search-item">
                                <div class="mo-search-item__info">
                                    <span class="mo-search-item__name">
                                        {{ p.name }}<span v-if="p.variant_label" class="mo-search-item__variant"> — {{ p.variant_label }}</span>
                                    </span>
                                    <span class="mo-search-item__meta">
                                        {{ formatPrice(p.sell_price) }}
                                        <template v-if="p.track_inventory"> · stok {{ p.available_stock }}</template>
                                    </span>
                                </div>
                                <button class="mo-btn-add" :disabled="p.track_inventory && p.available_stock <= 0" @click="addItem(p)">
                                    {{ (p.track_inventory && p.available_stock <= 0) ? 'Stok habis' : '+ Tambah' }}
                                </button>
                            </div>
                        </div>
                        <div v-else-if="productSearch.trim() && !searching" class="mo-search-loading">Produk tidak ditemukan</div>

                        <!-- Cart -->
                        <div class="mo-cart" v-if="items.length">
                            <div v-for="(item, idx) in items" :key="item.key" class="mo-cart-item">
                                <div class="mo-cart-item__info">
                                    <span class="mo-cart-item__name">{{ item.product_name }}</span>
                                    <span v-if="item.variant_label" class="mo-cart-item__variant">{{ item.variant_label }}</span>
                                </div>
                                <div class="mo-cart-item__qty">
                                    <button @click="changeQty(idx, -1)" class="mo-qty-btn">−</button>
                                    <input type="number" v-model.number="item.qty" min="1" :max="item.stock_qty" class="mo-qty-input" @change="clampQty(idx)"/>
                                    <button @click="changeQty(idx, 1)" class="mo-qty-btn">+</button>
                                </div>
                                <span class="mo-cart-item__price">{{ formatPrice(item.sell_price * item.qty) }}</span>
                                <button @click="removeItem(idx)" class="mo-cart-item__remove">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                        <div v-else class="mo-cart-empty">Belum ada produk ditambahkan</div>
                    </div>

                    <!-- Fulfillment -->
                    <div class="mo-section">
                        <div class="mo-section__title">Pengiriman</div>
                        <div class="mo-tabs">
                            <button :class="['mo-tab', form.fulfillment_type === 'delivery' && 'mo-tab--active']" @click="form.fulfillment_type = 'delivery'">Dikirim</button>
                            <button :class="['mo-tab', form.fulfillment_type === 'pickup' && 'mo-tab--active']" @click="form.fulfillment_type = 'pickup'; fetchBranches()">Pickup</button>
                        </div>

                        <template v-if="form.fulfillment_type === 'pickup'">
                            <div class="mo-field">
                                <label class="mo-label">Cabang <span class="mo-req">*</span></label>
                                <select v-model.number="form.branch_id" class="mo-input">
                                    <option value="">Pilih cabang</option>
                                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                            </div>
                        </template>
                        <template v-else>
                            <div class="mo-field">
                                <label class="mo-label">Alamat <span class="mo-req">*</span></label>
                                <textarea v-model="form.address" class="mo-textarea" rows="2" placeholder="Alamat lengkap"/>
                            </div>
                            <div class="mo-grid-3">
                                <div class="mo-field"><label class="mo-label">Kecamatan</label><input v-model="form.subdistrict" type="text" class="mo-input"/></div>
                                <div class="mo-field"><label class="mo-label">Kota</label><input v-model="form.city" type="text" class="mo-input"/></div>
                                <div class="mo-field"><label class="mo-label">Provinsi</label><input v-model="form.province" type="text" class="mo-input"/></div>
                            </div>
                            <div class="mo-grid-3">
                                <div class="mo-field"><label class="mo-label">Kurir <span class="mo-req">*</span></label><input v-model="form.shipping_courier" type="text" class="mo-input" placeholder="JNE, J&T, dll"/></div>
                                <div class="mo-field"><label class="mo-label">Layanan <span class="mo-req">*</span></label><input v-model="form.shipping_service" type="text" class="mo-input" placeholder="REG, YES, dll"/></div>
                                <div class="mo-field"><label class="mo-label">Ongkir <span class="mo-req">*</span></label><input v-model.number="form.shipping_cost" type="number" min="0" class="mo-input"/></div>
                            </div>
                            <div class="mo-field">
                                <label class="mo-label">Nama Pengiriman <span class="mo-req">*</span></label>
                                <input v-model="form.shipping_name" type="text" class="mo-input" placeholder="Contoh: JNE Reguler"/>
                            </div>
                        </template>
                    </div>

                    <!-- Status & Diskon -->
                    <div class="mo-section">
                        <div class="mo-section__title">Status Order</div>
                        <div class="mo-grid-3">
                            <label :class="['mo-status-opt', form.status === 'pending' && 'mo-status-opt--pending']">
                                <input type="radio" v-model="form.status" value="pending" class="mo-radio"/> Pending
                            </label>
                            <label :class="['mo-status-opt', form.status === 'success' && 'mo-status-opt--success']">
                                <input type="radio" v-model="form.status" value="success" class="mo-radio"/> Sukses
                            </label>
                            <label :class="['mo-status-opt', form.status === 'cancelled' && 'mo-status-opt--cancelled']">
                                <input type="radio" v-model="form.status" value="cancelled" class="mo-radio"/> Dibatalkan
                            </label>
                        </div>
                        <div v-if="form.status === 'cancelled'" class="mo-field">
                            <label class="mo-label">Alasan Pembatalan <span class="mo-req">*</span></label>
                            <textarea v-model="form.cancel_reason" class="mo-textarea" rows="2"/>
                        </div>
                        <div class="mo-field">
                            <label class="mo-label">Diskon (opsional)</label>
                            <input v-model.number="form.discount_amount" type="number" min="0" class="mo-input" placeholder="0"/>
                        </div>
                        <div class="mo-field">
                            <label class="mo-label">Catatan (opsional)</label>
                            <textarea v-model="form.notes" class="mo-textarea" rows="2"/>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="mo-summary">
                        <div class="mo-summary__row"><span>Subtotal</span><span>{{ formatPrice(subtotal) }}</span></div>
                        <div class="mo-summary__row" v-if="form.fulfillment_type === 'delivery'"><span>Ongkir</span><span>{{ formatPrice(form.shipping_cost || 0) }}</span></div>
                        <div class="mo-summary__row" v-if="form.discount_amount > 0"><span>Diskon</span><span>-{{ formatPrice(form.discount_amount) }}</span></div>
                        <div class="mo-summary__row mo-summary__row--total"><span>Total</span><span>{{ formatPrice(total) }}</span></div>
                    </div>
                </div>

                <div class="mo-footer">
                    <button @click="handleClose" class="mo-btn mo-btn--ghost" :disabled="loading">Batal</button>
                    <button @click="submit" class="mo-btn mo-btn--primary" :disabled="loading || !canSubmit">
                        <svg v-if="loading" class="mo-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        {{ loading ? 'Menyimpan...' : 'Simpan Order' }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
import axios from '../../axios.js'

export default {
    name: 'OrderManualModal',
    props: { show: { type: Boolean, default: false } },
    emits: ['close', 'created'],

    data() {
        return {
            form: this.defaultForm(),
            items: [],
            productSearch: '',
            productResults: [],
            searching: false,
            searchTimeout: null,
            branches: [],
            loading: false,
            error: '',
        }
    },

    computed: {
        subtotal() {
            return this.items.reduce((sum, i) => sum + (i.sell_price * i.qty), 0)
        },
        total() {
            const shipping = this.form.fulfillment_type === 'delivery' ? (Number(this.form.shipping_cost) || 0) : 0
            const discount = Number(this.form.discount_amount) || 0
            return this.subtotal + shipping - discount
        },
        canSubmit() {
            if (!this.form.customer_name || !this.form.customer_phone) return false
            if (!this.items.length) return false
            if (this.form.status === 'cancelled' && !this.form.cancel_reason?.trim()) return false
            if (this.form.fulfillment_type === 'pickup' && !this.form.branch_id) return false
            if (this.form.fulfillment_type === 'delivery' && (!this.form.address || !this.form.shipping_courier || !this.form.shipping_service || !this.form.shipping_name)) return false
            return true
        },
    },

    watch: {
        show(val) { if (val) { this.reset() } },
    },

    methods: {
        defaultForm() {
            return {
                customer_name: '', customer_phone: '', customer_email: '',
                fulfillment_type: 'delivery',
                branch_id: '',
                address: '', subdistrict: '', district: '', city: '', province: '', postal_code: '',
                shipping_courier: '', shipping_service: '', shipping_name: '', shipping_cost: 0, shipping_etd: '',
                status: 'pending', cancel_reason: '',
                discount_amount: 0, notes: '',
            }
        },
        reset() {
            this.form = this.defaultForm()
            this.items = []
            this.productSearch = ''
            this.productResults = []
            this.error = ''
        },
        formatPrice(val) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0)
        },
        onProductSearch() {
            clearTimeout(this.searchTimeout)
            if (!this.productSearch.trim()) { this.productResults = []; return }
            this.searchTimeout = setTimeout(async () => {
                this.searching = true
                try {
                    const res = await axios.get('/orders/products/search', { params: { q: this.productSearch } })
                    this.productResults = res.data?.data || []
                } catch (e) { console.error('Gagal mencari produk:', e) }
                finally { this.searching = false }
            }, 400)
        },
        addItem(p) {
            // p = 1 row hasil /orders/products/search (sudah flat per varian)
            const key = `${p.id}-${p.variant_id}`
            const existing = this.items.find(i => i.key === key)
            // track_inventory false berarti stok tidak dibatasi
            const stock = p.track_inventory ? p.available_stock : Infinity

            if (existing) {
                if (existing.qty < stock) existing.qty++
                return
            }
            this.items.push({
                key,
                product_id: p.id,
                variant_id: p.variant_id,
                product_name: p.name,
                variant_label: p.variant_label || null,
                sell_price: p.sell_price,
                stock_qty: stock,
                qty: 1,
            })
        },
        removeItem(idx) { this.items.splice(idx, 1) },
        changeQty(idx, delta) {
            const item = this.items[idx]
            const next = item.qty + delta
            if (next < 1 || next > item.stock_qty) return
            item.qty = next
        },
        clampQty(idx) {
            const item = this.items[idx]
            if (item.qty < 1) item.qty = 1
            if (item.qty > item.stock_qty) item.qty = item.stock_qty
        },
        async fetchBranches() {
            if (this.branches.length) return
            try {
                const res = await axios.get('/branches')
                this.branches = res.data?.data || res.data || []
            } catch (e) { console.error('Gagal memuat cabang:', e) }
        },
        handleClose() {
            if (this.loading) return
            this.$emit('close')
        },
        async submit() {
            if (!this.canSubmit) return
            this.loading = true
            this.error = ''
            try {
                const payload = {
                    ...this.form,
                    items: this.items.map(i => ({ product_id: i.product_id, variant_id: i.variant_id, qty: i.qty })),
                }
                await axios.post('/orders/manual', payload)
                this.$emit('created')
            } catch (e) {
                this.error = e.response?.data?.message || 'Gagal membuat order.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>

<style scoped>
.mo-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.6); backdrop-filter: blur(4px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 24px; }
.mo-modal { background: #fff; border-radius: 16px; width: 100%; max-width: 640px; max-height: calc(100vh - 48px); display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 32px 64px rgba(0,0,0,.16); }
.mo-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #f3f4f6; background: #fafafa; }
.mo-header__left { display: flex; align-items: center; gap: 12px; }
.mo-header__icon { width: 40px; height: 40px; border-radius: 10px; background: #fef2f2; color: #ED1F24; border: 1px solid #fecaca; display: flex; align-items: center; justify-content: center; }
.mo-header__title { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 2px; }
.mo-header__subtitle { font-size: 12px; color: #9ca3af; margin: 0; }
.mo-close { width: 36px; height: 36px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #9ca3af; }
.mo-close:hover:not(:disabled) { background: #f3f4f6; color: #374151; }
.mo-alert { display: flex; align-items: center; gap: 10px; padding: 12px 24px; background: #fef2f2; border-bottom: 1px solid #fecaca; font-size: 13px; color: #991b1b; font-weight: 500; }
.mo-body { padding: 20px 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; }
.mo-section { display: flex; flex-direction: column; gap: 10px; }
.mo-section__title { font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .08em; padding-bottom: 6px; border-bottom: 1px solid #f3f4f6; }
.mo-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.mo-grid-2__span { grid-column: span 2; }
.mo-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }
.mo-field { display: flex; flex-direction: column; gap: 5px; }
.mo-label { font-size: 12px; font-weight: 600; color: #374151; }
.mo-req { color: #ef4444; }
.mo-input, .mo-textarea { padding: 8px 10px; border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 13px; color: #111827; outline: none; font-family: inherit; width: 100%; box-sizing: border-box; }
.mo-input:focus, .mo-textarea:focus { border-color: #ED1F24; box-shadow: 0 0 0 3px rgba(237,31,36,.08); }
.mo-textarea { resize: vertical; }

.mo-search-box { display: flex; align-items: center; gap: 8px; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 8px 10px; }
.mo-search-box svg { color: #9ca3af; flex-shrink: 0; }
.mo-search-input { border: none; outline: none; font-size: 13px; width: 100%; }
.mo-search-loading { font-size: 12px; color: #9ca3af; padding: 4px 2px; }
.mo-search-results { display: flex; flex-direction: column; gap: 6px; max-height: 220px; overflow-y: auto; border: 1px solid #f3f4f6; border-radius: 8px; padding: 6px; }
.mo-search-item { padding: 8px; border-radius: 6px; background: #f9fafb; display: flex; flex-direction: column; gap: 6px; }
.mo-search-item__info { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
.mo-search-item__name { font-size: 13px; font-weight: 600; color: #111827; }
.mo-search-item__meta { font-size: 11px; color: #6b7280; white-space: nowrap; }
.mo-variant-list { display: flex; flex-direction: column; gap: 4px; }
.mo-variant-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 4px 6px; background: #fff; border-radius: 6px; }
.mo-variant-row__label { font-size: 12px; font-weight: 600; color: #374151; }
.mo-variant-row__meta { font-size: 11px; color: #9ca3af; }
.mo-btn-add { font-size: 11px; font-weight: 700; color: #ED1F24; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 4px 8px; cursor: pointer; white-space: nowrap; }
.mo-btn-add:hover:not(:disabled) { background: #fecaca; }
.mo-btn-add:disabled { opacity: .5; cursor: not-allowed; }

.mo-cart { display: flex; flex-direction: column; gap: 6px; }
.mo-cart-item { display: flex; align-items: center; gap: 10px; padding: 8px 10px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; }
.mo-cart-item__info { display: flex; flex-direction: column; flex: 1; min-width: 0; }
.mo-cart-item__name { font-size: 12px; font-weight: 600; color: #111827; }
.mo-cart-item__variant { font-size: 11px; color: #9ca3af; }
.mo-cart-item__qty { display: flex; align-items: center; gap: 4px; }
.mo-qty-btn { width: 22px; height: 22px; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; font-size: 13px; }
.mo-qty-input { width: 36px; text-align: center; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 12px; padding: 2px; }
.mo-cart-item__price { font-size: 12px; font-weight: 700; color: #111827; white-space: nowrap; width: 90px; text-align: right; }
.mo-cart-item__remove { color: #9ca3af; background: none; border: none; cursor: pointer; display: flex; }
.mo-cart-item__remove:hover { color: #dc2626; }
.mo-cart-empty { font-size: 12px; color: #9ca3af; text-align: center; padding: 12px; }

.mo-tabs { display: flex; gap: 8px; }
.mo-tab { flex: 1; padding: 8px; border-radius: 8px; border: 1.5px solid #e5e7eb; background: #fff; font-size: 12px; font-weight: 600; color: #6b7280; cursor: pointer; }
.mo-tab--active { border-color: #ED1F24; background: #fef2f2; color: #ED1F24; }

.mo-status-opt { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px; border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 12px; font-weight: 600; color: #6b7280; cursor: pointer; }
.mo-radio { display: none; }
.mo-status-opt--pending { border-color: #f59e0b; background: #fffbeb; color: #92400e; }
.mo-status-opt--success { border-color: #22c55e; background: #f0fdf4; color: #15803d; }
.mo-status-opt--cancelled { border-color: #ef4444; background: #fef2f2; color: #b91c1c; }

.mo-summary { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; gap: 6px; }
.mo-summary__row { display: flex; justify-content: space-between; font-size: 13px; color: #6b7280; }
.mo-summary__row--total { padding-top: 8px; border-top: 1px dashed #e5e7eb; font-weight: 700; color: #111827; font-size: 15px; }

.mo-footer { display: flex; justify-content: flex-end; gap: 8px; padding: 16px 24px; border-top: 1px solid #f3f4f6; background: #fafafa; }
.mo-btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; }
.mo-btn:disabled { opacity: .55; cursor: not-allowed; }
.mo-btn--primary { background: #ED1F24; color: #fff; }
.mo-btn--primary:hover:not(:disabled) { background: #C81A1E; }
.mo-btn--ghost { background: transparent; color: #6b7280; border: 1px solid #e5e7eb; }
.mo-btn--ghost:hover:not(:disabled) { background: #f9fafb; }
.mo-spin { animation: mo-spin-anim 1s linear infinite; }
@keyframes mo-spin-anim { to { transform: rotate(360deg); } }

.mo-modal-enter-active { transition: all .22s cubic-bezier(0.34, 1.2, 0.64, 1); }
.mo-modal-leave-active { transition: all .15s ease-in; }
.mo-modal-enter-from, .mo-modal-leave-to { opacity: 0; transform: scale(.97) translateY(6px); }
</style>