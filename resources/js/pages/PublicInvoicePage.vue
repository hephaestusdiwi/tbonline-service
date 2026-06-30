<template>
  <div class="inv-page-wrap">

    <!-- Loading -->
    <div v-if="loading" class="inv-loading">
      <svg class="inv-spin" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
      </svg>
      Memuat invoice...
    </div>

    <!-- Not found -->
    <div v-else-if="!invoiceData" class="inv-notfound">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.3">
        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
        <line x1="3" y1="6" x2="21" y2="6"/>
        <path d="M16 10a4 4 0 0 1-8 0"/>
      </svg>
      <p>Invoice tidak ditemukan.</p>
    </div>

    <!-- Invoice -->
    <div v-else class="inv-shell">

      <!-- Toolbar -->
      <div class="inv-toolbar no-print">
        <div class="inv-toolbar__left">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
            <rect x="6" y="14" width="12" height="8"/>
          </svg>
          Invoice {{ invoiceData.invoice_number }}
        </div>
        <div class="inv-toolbar__actions">
          <button class="inv-print-btn inv-print-btn--ghost" @click="printInvoice">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6 9 6 2 18 2 18 9"/>
              <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
              <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Cetak
          </button>
          <button class="inv-print-btn" :disabled="downloadingPdf" @click="downloadInvoicePDF">
            <svg v-if="downloadingPdf" class="inv-spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            <svg v-else width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            {{ downloadingPdf ? 'Mengunduh...' : 'Download PDF' }}
          </button>
        </div>
      </div>

      <!-- Invoice Doc -->
      <div class="or-invoice-doc" id="invoice-print-area">

        <div class="inv-header">
          <div class="inv-header__brand">
            <div class="inv-logo">
              <img v-if="storeLogo" :src="storeLogo" :alt="storeName" class="inv-logo__img" crossorigin="anonymous"/>
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
              <tbody>
                <tr>
                  <td class="inv-meta__key">Penjual</td>
                  <td class="inv-meta__sep">:</td>
                  <td class="inv-meta__val inv-meta__val--bold">{{ storeName }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="inv-meta__col">
            <div class="inv-meta__heading">UNTUK</div>
            <table class="inv-meta__table">
              <tbody>
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
              </tbody>
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
                <div v-if="item.variant_label && item.variant_names" class="inv-product-variant">
                  {{ item.variant_label }}: {{ item.variant_names }}
                </div>
                <div class="inv-product-meta">
                  Kurir: {{ invoiceData.shipping_courier?.toUpperCase() }} {{ invoiceData.shipping_service }}
                </div>
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
              <span>Ongkos Kirim ({{ invoiceData.shipping_courier?.toUpperCase() }} {{ invoiceData.shipping_service }})</span>
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
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="1" y="3" width="15" height="13" rx="1"/>
              <path d="M16 8h4l3 3v5h-7V8z"/>
              <circle cx="5.5" cy="18.5" r="2.5"/>
              <circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
            {{ invoiceData.shipping_name }}
          </div>
          <div class="inv-courier-right">
            <span class="inv-courier-label">Status Pesanan:</span>
            <span :class="['inv-status-pill', 'inv-status-pill--' + invoiceData.status]">
              {{ statusLabel(invoiceData.status) }}
            </span>
          </div>
        </div>

        <div class="inv-footer">
          <p>Invoice ini sah dan diproses oleh komputer.</p>
          <p v-if="storePhone">Hubungi kami di <strong>{{ storePhone }}</strong> apabila Anda membutuhkan bantuan.</p>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import axiosInstance from '../axios'
import jsPDF from 'jspdf'
import html2canvas from 'html2canvas'

export default {
  name: 'PublicInvoicePage',

  data() {
    return {
      invoiceData: null,
      storeName:   '',
      storeLogo:   '',
      storePhone:  '',
      loading:     true,
      downloadingPdf: false,
    }
  },

  async created() {
    try {
      const [invoiceRes, settingsRes] = await Promise.all([
        axiosInstance.get(`/orders/public/${this.$route.params.invoice_number}`),
        axiosInstance.get('/settings'),
      ])
      this.invoiceData = invoiceRes.data?.data || null
      const s = settingsRes.data || {}
      this.storeName  = s.site_name?.value  || 'TB Store'
      this.storeLogo  = s.site_logo?.value  || ''
      this.storePhone = s.site_phone?.value || ''
    } catch {
      this.invoiceData = null
    } finally {
      this.loading = false
    }
  },

  methods: {
    formatPrice(val) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR',
        minimumFractionDigits: 0, maximumFractionDigits: 0,
      }).format(val)
    },
    formatDateLong(val) {
      return new Date(val).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric',
      })
    },
    statusLabel(status) {
      return { pending: 'Pending', success: 'Lunas', cancelled: 'Dibatalkan' }[status] || status
    },
    async downloadInvoicePDF() {
      if (this.downloadingPdf) return
      this.downloadingPdf = true
      try {
        const element = document.getElementById('invoice-print-area')
        const canvas = await html2canvas(element, {
          scale: 2,
          useCORS: true,
          backgroundColor: '#ffffff',
          windowWidth: 800,
        })
        const imgData = canvas.toDataURL('image/jpeg', 0.95)

        const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })
        const pageWidth  = pdf.internal.pageSize.getWidth()
        const pageHeight = pdf.internal.pageSize.getHeight()
        const imgWidth   = pageWidth
        const imgHeight  = (canvas.height * imgWidth) / canvas.width

        let heightLeft = imgHeight
        let position = 0

        pdf.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight)
        heightLeft -= pageHeight

        while (heightLeft > 0) {
          position = heightLeft - imgHeight
          pdf.addPage()
          pdf.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight)
          heightLeft -= pageHeight
        }

        const inv   = this.invoiceData?.invoice_number || 'INVOICE'
        const store = (this.storeName || 'TB').replace(/\s+/g, '').toUpperCase()
        pdf.save(`${store}-INV-${inv.replace('INV', '')}.pdf`)
      } catch (e) {
        console.error('Gagal membuat PDF:', e)
        alert('Gagal membuat PDF, coba lagi.')
      } finally {
        this.downloadingPdf = false
      }
    },
  },
}
</script>

<style scoped>
/* ─── Page wrap ─── */
.inv-page-wrap {
  min-height: 100vh;
  background: #f3f4f6;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 24px 16px 48px;
}

/* ─── Loading & Not found ─── */
.inv-loading, .inv-notfound {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 80px 24px;
  color: #6b7280;
  font-size: 14px;
}
.inv-spin { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Shell ─── */
.inv-shell {
  width: 100%;
  max-width: 780px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* ─── Toolbar ─── */
.inv-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #1e293b;
  color: #e2e8f0;
  border-radius: 12px;
  padding: 12px 18px;
  font-size: 13px;
  font-weight: 600;
  gap: 12px;
}
.inv-toolbar__left { display: flex; align-items: center; gap: 8px; }
.inv-print-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #ED1F24;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background .15s;
}
.inv-print-btn:hover { background: #C81A1E; }
.inv-toolbar__actions { display: flex; gap: 8px; }
.inv-print-btn--ghost {
  background: transparent;
  border: 1px solid rgba(255,255,255,.2);
  color: #e2e8f0;
}
.inv-print-btn--ghost:hover { background: rgba(255,255,255,.08); }
.inv-print-btn:disabled { opacity: .6; cursor: not-allowed; }

/* ─── Invoice Doc ─── */
.or-invoice-doc {
  background: #fff;
  border-radius: 12px;
  padding: 48px 52px;
  box-shadow: 0 4px 24px rgba(0,0,0,.12);
  font-family: 'Segoe UI', Arial, sans-serif;
  color: #1a1a1a;
  font-size: 13px;
  line-height: 1.5;
}
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
  .inv-logo__img   { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}

/* ─── Responsive ─── */
@media (max-width: 640px) {
  .or-invoice-doc { padding: 24px 20px; }
  .inv-meta { grid-template-columns: 1fr; }
  .inv-summary { flex-direction: column; }
  .inv-summary__right { min-width: unset; width: 100%; }
}
</style>