<template>
  <div v-if="loading" class="inv-loading">Memuat invoice...</div>
  <div v-else-if="order" class="or-invoice-doc" id="invoice-print-area">
    <div class="or-invoice-doc" id="invoice-print-area" v-if="invoiceData">
        <div class="inv-header">
            <div class="inv-header__brand">
                <div class="inv-logo">
                    <img v-if="storeLogo" :src="storeLogo" :alt="storeName" class="inv-logo__img"/>
                    <span v-else>{{ storeName }}</span>
                </div>
            </div>
            <div class="inv-header__right">
                <div class="inv-title">INVOICE</div>
                <div class="inv-number">{{ invoiceData.invoice_number }}</div>
            </div>
        </div>

        <div class="inv-meta">
            <div class="inv-meta__col">
                <div class="inv-meta__heading">DITERBITKAN ATAS NAMA</div>
                <table class="inv-meta__table">
                    <tr>
                        <td class="inv-meta__key">Penjual</td>
                        <td class="inv-meta__sep">:</td>
                        <td class="inv-meta__val inv-meta__val--bold">{{ storeName }}</td>
                    </tr>
                </table>
            </div>
            <div class="inv-meta__col">
                <div class="inv-meta__heading">UNTUK</div>
                <table class="inv-meta__table">
                    <tr>
                        <td class="inv-meta__key">Pembeli</td>
                        <td class="inv-meta__sep">:</td>
                        <td class="inv-meta__val inv-meta__val--bold">{{ invoiceData.customer_name }}</td>
                    </tr>
                    <tr>
                        <td class="inv-meta__key">Tanggal Pembelian</td>
                        <td class="inv-meta__sep">:</td>
                        <td class="inv-meta__val inv-meta__val--bold">{{ formatDateLong(invoiceData.created_at) }}</td>
                    </tr>
                    <tr>
                        <td class="inv-meta__key">Alamat Pengiriman</td>
                        <td class="inv-meta__sep">:</td>
                        <td class="inv-meta__val">
                            <template v-if="invoiceData.shipping_courier?.toLowerCase() === 'pickup'">
                                <span class="inv-meta__val--bold">Pickup di cabang</span><br>
                                {{ invoiceData.branch?.name || '-' }}
                                <template v-if="invoiceData.branch?.address"><br>{{ invoiceData.branch.address }}</template>
                            </template>
                            <template v-else>
                                <span class="inv-meta__val--bold">{{ invoiceData.customer_name }} ({{ invoiceData.customer_phone }})</span><br>
                                {{ invoiceData.address }}, {{ invoiceData.subdistrict }}, {{ invoiceData.district }},
                                {{ invoiceData.city }}, {{ invoiceData.province }} {{ invoiceData.postal_code }}
                            </template>
                        </td>
                    </tr>
                    <tr v-if="invoiceData.customer_email">
                        <td class="inv-meta__key">Email</td>
                        <td class="inv-meta__sep">:</td>
                        <td class="inv-meta__val">{{ invoiceData.customer_email }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="inv-table">
            <thead>
                <tr>
                    <th class="inv-th inv-th--product">INFO PRODUK</th>
                    <th class="inv-th inv-th--center">JUMLAH</th>
                    <th class="inv-th inv-th--right">HARGA SATUAN</th>
                    <th class="inv-th inv-th--right">TOTAL HARGA</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in invoiceData.items" :key="item.id" class="inv-tr">
                    <td class="inv-td inv-td--product">
                        <div class="inv-product-name">{{ item.product_name }}</div>
                        <div v-if="item.variant_label && item.variant_names" class="inv-product-variant">{{ item.variant_label }}: {{ item.variant_names }}</div>
                        <div class="inv-product-meta">Kurir: {{ invoiceData.shipping_courier.toUpperCase() }} {{ invoiceData.shipping_service }}</div>
                    </td>
                    <td class="inv-td inv-td--center">{{ item.qty }}</td>
                    <td class="inv-td inv-td--right">{{ formatPrice(item.sell_price) }}</td>
                    <td class="inv-td inv-td--right">{{ formatPrice(item.subtotal) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="inv-summary">
            <div class="inv-summary__left">
                <div v-if="invoiceData.status === 'success'" class="inv-lunas">LUNAS</div>
                <div v-else-if="invoiceData.status === 'cancelled'" class="inv-batal">BATAL</div>
                <div v-else class="inv-pending">PENDING</div>
                <div v-if="invoiceData.notes" class="inv-notes">
                    <div class="inv-notes__label">Catatan:</div>
                    <div class="inv-notes__text">{{ invoiceData.notes }}</div>
                </div>
            </div>
            <div class="inv-summary__right">
                <div class="inv-price-row">
                    <span>SUBTOTAL HARGA BARANG</span>
                    <span class="inv-price-row__val--bold">{{ formatPrice(invoiceData.subtotal) }}</span>
                </div>
                <div v-if="invoiceData.discount_amount > 0" class="inv-price-row">
                    <span>Diskon Promo ({{ invoiceData.promo_code }})</span>
                    <span class="inv-price-row__val--discount">-{{ formatPrice(invoiceData.discount_amount) }}</span>
                </div>
                <div class="inv-price-row">
                    <span>Ongkos Kirim ({{ invoiceData.shipping_courier.toUpperCase() }} {{ invoiceData.shipping_service }})</span>
                    <span>{{ formatPrice(invoiceData.shipping_cost) }}</span>
                </div>
                <div v-if="invoiceData.shipping_etd" class="inv-price-row inv-price-row--sub">
                    <span>Estimasi tiba</span>
                    <span>{{ invoiceData.shipping_etd }}</span>
                </div>
                <div class="inv-price-row inv-price-row--total">
                    <span>TOTAL BELANJA</span>
                    <span>{{ formatPrice(invoiceData.total_price) }}</span>
                </div>
            </div>
        </div>

        <div class="inv-courier-row">
            <div class="inv-courier-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                {{ invoiceData.shipping_name }}
            </div>
            <div class="inv-courier-right">
                <span class="inv-courier-label">Status Pesanan:</span>
                <span :class="['inv-status-pill', 'inv-status-pill--' + invoiceData.status]">{{ statusLabel(invoiceData.status) }}</span>
            </div>
        </div>

        <div class="inv-footer">
            <p>Invoice ini sah dan diproses oleh komputer.</p>
            <p v-if="printedBy">Diterbitkan oleh: <strong>{{ printedBy }}</strong> pada {{ formatDateLong(new Date().toISOString()) }}</p>
            <p v-if="storePhone">Hubungi kami di <strong>{{ storePhone }}</strong> apabila Anda membutuhkan bantuan.</p>
        </div>
    </div>
    <button onclick="window.print()" class="no-print">🖨 Cetak / Download PDF</button>
  </div>
  <div v-else>Invoice tidak ditemukan.</div>
</template>

<script>
import axiosInstance from '../axios'

export default {
  name: 'PublicInvoicePage',
  data() {
    return { order: null, store: null, loading: true }
  },
  async created() {
    try {
      const res = await axiosInstance.get(`/orders/public/${this.$route.params.invoice_number}`)
      this.order = res.data.data
      this.store = res.data.store
    } catch {
      this.order = null
    } finally {
      this.loading = false
    }
  },
}
</script>

<style scoped>
.or-invoice-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.55); backdrop-filter: blur(4px); z-index: 1100; display: flex; flex-direction: column; align-items: center; padding: 20px; overflow-y: auto; }
.or-invoice-shell { width: 100%; max-width: 780px; display: flex; flex-direction: column; gap: 12px; }
.or-invoice-toolbar { display: flex; justify-content: space-between; align-items: center; background: #1e293b; color: #e2e8f0; border-radius: 12px; padding: 12px 18px; font-size: 13px; font-weight: 600; gap: 12px; }
.or-invoice-toolbar__left { display: flex; align-items: center; gap: 8px; }
.or-invoice-toolbar__right { display: flex; gap: 8px; }
.or-invoice-loading { display: flex; align-items: center; justify-content: center; gap: 12px; background: #fff; border-radius: 12px; padding: 60px; color: #6b7280; font-size: 14px; }

.or-invoice-doc { background: #ffffff; border-radius: 12px; padding: 48px 52px; box-shadow: 0 4px 24px rgba(0,0,0,.12); font-family: 'Segoe UI', Arial, sans-serif; color: #1a1a1a; font-size: 13px; line-height: 1.5; }
.inv-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; }
.inv-logo { font-size: 26px; font-weight: 300; color: #ED1F24; letter-spacing: -0.5px; }
.inv-logo__img { height: 52px; width: auto; max-width: 200px; object-fit: contain; display: block; }
.inv-header__right { text-align: right; }
.inv-title  { font-size: 36px; font-weight: 500; color: #1a1a1a; letter-spacing: 2px; }
.inv-number { font-size: 13px; color: #ED1F24; font-weight: 600; margin-top: 2px; }
.inv-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; padding-bottom: 24px; border-bottom: 1px solid #1a1a1a; }
.inv-meta__heading { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #1a1a1a; margin-bottom: 8px; }
.inv-meta__table { border-collapse: collapse; width: 100%; }
.inv-meta__key { font-size: 12px; color: #555; white-space: nowrap; padding-right: 6px; vertical-align: top; padding-bottom: 4px; }
.inv-meta__sep { padding: 0 6px; color: #555; vertical-align: top; }
.inv-meta__val { font-size: 12px; color: #1a1a1a; vertical-align: top; padding-bottom: 4px; }
.inv-meta__val--bold { font-weight: 700; }
.inv-table { width: 100%; border-collapse: collapse; margin-top: 0; border-top: 1px solid #e0e0e0; }
.inv-th { padding: 12px 10px; font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: #1a1a1a; border-bottom: 1px solid #1a1a1a; text-align: left; }
.inv-th--center { text-align: center; }
.inv-th--right  { text-align: right; }
.inv-th--product { width: 55%; }
.inv-tr { border-bottom: 1px solid #f0f0f0; }
.inv-tr:last-child { border-bottom: none; }
.inv-td { padding: 14px 10px; vertical-align: top; font-size: 13px; color: #333; }
.inv-td--center { text-align: center; }
.inv-td--right  { text-align: right; white-space: nowrap; }
.inv-td--product { padding-right: 20px; }
.inv-product-name    { font-weight: 700; color: #ED1F24; font-size: 13px; line-height: 1.4; margin-bottom: 3px; }
.inv-product-variant { font-size: 12px; color: #555; margin-bottom: 2px; }
.inv-product-meta    { font-size: 11px; color: #999; }
.inv-summary { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; padding: 20px 0; border-top: 1px solid #e0e0e0; }
.inv-lunas, .inv-batal, .inv-pending { font-size: 42px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; opacity: .12; transform: rotate(-15deg); margin-bottom: 8px; user-select: none; pointer-events: none; line-height: 1; }
.inv-lunas   { color: #ED1F24; }
.inv-batal   { color: #dc2626; }
.inv-pending { color: #d97706; }
.inv-notes { margin-top: 10px; }
.inv-notes__label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #999; letter-spacing: .05em; margin-bottom: 3px; }
.inv-notes__text  { font-size: 12px; color: #555; line-height: 1.5; }
.inv-summary__right { min-width: 300px; }
.inv-price-row { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; font-size: 13px; color: #555; gap: 12px; }
.inv-price-row--sub   { font-size: 11px; color: #aaa; }
.inv-price-row--total { border-top: 1px solid #1a1a1a; margin-top: 8px; padding-top: 10px; font-weight: 500; font-size: 15px; color: #1a1a1a; }
.inv-price-row__val--bold     { font-weight: 700; color: #1a1a1a; }
.inv-price-row__val--discount { color: #dc2626; font-weight: 600; }
.inv-courier-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; background: #f8f8f8; border-radius: 8px; margin-top: 4px; gap: 12px; }
.inv-courier-badge { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: #333; }
.inv-courier-right { display: flex; align-items: center; gap: 8px; }
.inv-courier-label { font-size: 11px; color: #999; }
.inv-status-pill { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.inv-status-pill--success   { background: #dcfce7; color: #166534; }
.inv-status-pill--cancelled { background: #fef2f2; color: #991b1b; }
.inv-status-pill--pending   { background: #fef3c7; color: #92400e; }
.inv-footer { margin-top: 28px; padding-top: 20px; border-top: 1px solid #e0e0e0; font-size: 12px; color: #999; line-height: 1.7; }
.inv-footer p { margin: 0; }

/* ─── Print ─── */
@media print {
    body > * { display: none !important; }
    #invoice-print-area { display: block !important; position: fixed; inset: 0; margin: 0; padding: 32px 40px; box-shadow: none; border-radius: 0; font-size: 12px; }
    .no-print { display: none !important; }
    .inv-lunas, .inv-batal, .inv-pending { opacity: .07; }
    .inv-courier-row { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .inv-logo__img { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}

/* ─── Responsive ─── */
@media (max-width: 640px) {
    .or-hero__circle--2 { display: none; }
    .or-hero__stat:nth-child(n+3) { display: none; }
    .or-detail-grid { grid-template-columns: 1fr; }
    .or-status-options { flex-direction: column; }
    .inv-meta { grid-template-columns: 1fr; }
    .inv-summary { flex-direction: column; }
    .inv-summary__right { min-width: unset; width: 100%; }
    .or-invoice-doc { padding: 24px 20px; }
}
</style>