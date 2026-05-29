<template>
    <AdminLayout title="Loyalty Points">

        <div class="lp-page">

            <!-- ───────────────────────── HERO HEADER ───────────────────────── -->
            <div class="lp-hero">
                <div class="lp-hero__circle lp-hero__circle--1"></div>
                <div class="lp-hero__circle lp-hero__circle--2"></div>
                <div class="lp-hero__circle lp-hero__circle--3"></div>

                <div class="lp-hero__inner">
                    <div>
                        <p class="lp-hero__eyebrow">Customer Loyalty</p>
                        <h1 class="lp-hero__title">Loyalty Points</h1>
                        <p class="lp-hero__subtitle">Riwayat dan saldo point pelanggan</p>
                    </div>
                    <div class="lp-hero__right">
                        <div class="lp-hero__live">
                            <span class="lp-hero__live-dot"></span>
                            Live
                        </div>
                    </div>
                </div>

                <!-- Stats strip -->
                <div class="lp-hero__strip">
                    <div class="lp-hero__stat">
                        <p class="lp-hero__stat-label">Pelanggan</p>
                        <p class="lp-hero__stat-value">{{ formatNumber(stats.total_customers) }}</p>
                    </div>
                    <div class="lp-hero__stat">
                        <p class="lp-hero__stat-label">Point Aktif</p>
                        <p class="lp-hero__stat-value lp-hero__stat-value--green">{{ formatNumber(stats.total_points_active) }}</p>
                    </div>
                    <div class="lp-hero__stat">
                        <p class="lp-hero__stat-label">Total Earned</p>
                        <p class="lp-hero__stat-value lp-hero__stat-value--amber">{{ formatNumber(stats.total_points_earned) }}</p>
                    </div>
                    <div class="lp-hero__stat">
                        <p class="lp-hero__stat-label">Total Pakai</p>
                        <p class="lp-hero__stat-value lp-hero__stat-value--muted">{{ formatNumber(stats.total_points_deducted) }}</p>
                    </div>
                </div>
            </div>

            <!-- ───────────────────────── SEARCH BAR ───────────────────────── -->
            <div class="lp-card lp-filterbar">
                <div class="lp-filterbar__search">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="lp-filterbar__icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input
                        v-model="searchPhone"
                        type="tel"
                        placeholder="Cari nomor HP pelanggan... (contoh: 0812 atau +62812)"
                        class="lp-filterbar__input"
                        @keyup.enter="searchCustomer"
                        @input="searchPhone = searchPhone.replace(/[^0-9+]/g, '')"
                    />
                    <button v-if="searchPhone" @click="clearSearch" class="lp-clear-btn" aria-label="Hapus pencarian">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <button @click="searchCustomer" :disabled="searchPhone.length < 5 || searching" class="lp-btn lp-btn--primary">
                    <svg v-if="searching" class="lp-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Cari
                </button>
                <button @click="openDeductModal" class="lp-btn lp-btn--ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Potong Point
                </button>
            </div>

            <!-- ───────────────────────── ERROR STATE ───────────────────────── -->
            <div v-if="searchError" class="lp-error-state">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ searchError }}
            </div>

            <!-- ───────────────────────── CUSTOMER PANEL ───────────────────────── -->
            <div v-if="customer" class="lp-customer-panel lp-card">

                <!-- Customer info header -->
                <div class="lp-customer-header">
                    <div class="lp-customer-avatar">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div class="lp-customer-info">
                        <span class="lp-customer-phone">{{ customer.phone }}</span>
                        <span class="lp-customer-meta">{{ customer.history.length }} transaksi point</span>
                    </div>
                    <div class="lp-customer-balance">
                        <span class="lp-balance-label">Saldo Point</span>
                        <span class="lp-balance-value">{{ formatNumber(customer.balance) }}</span>
                        <span class="lp-balance-sub">≈ Rp {{ formatNumber(customer.balance) }}</span>
                    </div>
                    <div v-if="customer.expiring_soon > 0" class="lp-expiring-badge">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ formatNumber(customer.expiring_soon) }} point kadaluarsa dalam 30 hari
                    </div>
                </div>

                <!-- History table -->
                <div class="lp-table-wrap">
                    <table class="lp-table">
                        <thead>
                            <tr>
                                <th class="lp-th">Tanggal</th>
                                <th class="lp-th">Tipe</th>
                                <th class="lp-th">Keterangan</th>
                                <th class="lp-th">Order</th>
                                <th class="lp-th">Kadaluarsa</th>
                                <th class="lp-th lp-th--right">Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="customer.history.length === 0">
                                <td colspan="6" class="lp-empty-row">Belum ada riwayat point untuk nomor ini.</td>
                            </tr>
                            <tr
                                v-for="h in customer.history"
                                :key="h.id"
                                class="lp-tr"
                                :class="{ 'lp-tr--earn': h.type === 'earn', 'lp-tr--expire': h.type === 'expire', 'lp-tr--deduct': h.type === 'deduct' }"
                            >
                                <td class="lp-td lp-td--date">{{ h.created_at }}</td>
                                <td class="lp-td">
                                    <span :class="['lp-type-badge', `lp-type-badge--${h.type}`]">
                                        <svg v-if="h.type === 'earn'" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                                        <svg v-else-if="h.type === 'expire'" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                        <svg v-else width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        {{ h.type === 'earn' ? 'Earn' : h.type === 'expire' ? 'Hangus' : 'Pakai' }}
                                    </span>
                                </td>
                                <td class="lp-td lp-td--desc">{{ h.description || '—' }}</td>
                                <td class="lp-td">
                                    <a v-if="h.order_invoice" :href="`/admin/orders?search=${h.order_invoice}`" class="lp-order-link">#{{ h.order_invoice }}</a>
                                    <span v-else class="lp-td--muted">—</span>
                                </td>
                                <td class="lp-td">
                                    <span v-if="h.expired_at" :class="['lp-expiry', h.is_expired ? 'lp-expiry--past' : 'lp-expiry--future']">
                                        {{ h.expired_at }}
                                        <span v-if="h.is_expired" class="lp-expiry-tag">Kedaluwarsa</span>
                                    </span>
                                    <span v-else class="lp-td--muted">—</span>
                                </td>
                                <td class="lp-td lp-td--points">
                                    <span :class="['lp-points', h.points > 0 ? 'lp-points--pos' : 'lp-points--neg']">
                                        {{ h.points > 0 ? '+' : '' }}{{ formatNumber(h.points) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ───────────────────────── EMPTY STATE ───────────────────────── -->
            <div v-else-if="!searching && !searchError" class="lp-empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.25">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
                <p>Masukkan nomor HP pelanggan untuk melihat saldo dan riwayat point.</p>
                <span>Contoh: 081234567890 atau +6281234567890</span>
            </div>

            <!-- ───────────────────────── RECENT TRANSACTIONS ───────────────────────── -->
            <div v-if="!customer" class="lp-card lp-recent">
                <div class="lp-recent__header">
                    <div>
                        <h2 class="lp-recent__title">Transaksi Terbaru</h2>
                        <p class="lp-recent__sub">50 transaksi terakhir</p>
                    </div>
                    <div class="lp-recent__badge">
                        <span class="lp-live-dot"></span>
                        Live
                    </div>
                </div>

                <div v-if="loadingRecent" class="lp-loading">
                    <svg class="lp-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    Memuat data...
                </div>
                <div v-else class="lp-table-wrap">
                    <table class="lp-table">
                        <thead>
                            <tr>
                                <th class="lp-th">Tanggal</th>
                                <th class="lp-th">Nomor HP</th>
                                <th class="lp-th">Tipe</th>
                                <th class="lp-th">Keterangan</th>
                                <th class="lp-th">Order</th>
                                <th class="lp-th">Kadaluarsa</th>
                                <th class="lp-th lp-th--right">Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="recentTransactions.length === 0">
                                <td colspan="7" class="lp-empty-row">Belum ada transaksi point.</td>
                            </tr>
                            <tr
                                v-for="t in recentTransactions"
                                :key="t.id"
                                class="lp-tr"
                                :class="{ 'lp-tr--earn': t.type === 'earn', 'lp-tr--expire': t.type === 'expire', 'lp-tr--deduct': t.type === 'deduct' }"
                            >
                                <td class="lp-td lp-td--date">{{ t.created_at }}</td>
                                <td class="lp-td">
                                    <button class="lp-phone-link" @click="quickSearch(t.phone)">{{ t.phone }}</button>
                                </td>
                                <td class="lp-td">
                                    <span :class="['lp-type-badge', `lp-type-badge--${t.type}`]">
                                        <svg v-if="t.type === 'earn'" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                                        <svg v-else-if="t.type === 'expire'" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                        <svg v-else width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        {{ t.type === 'earn' ? 'Earn' : t.type === 'expire' ? 'Hangus' : 'Pakai' }}
                                    </span>
                                </td>
                                <td class="lp-td lp-td--desc">{{ t.description || '—' }}</td>
                                <td class="lp-td">
                                    <a v-if="t.order_invoice" :href="`/admin/orders?search=${t.order_invoice}`" class="lp-order-link">#{{ t.order_invoice }}</a>
                                    <span v-else class="lp-td--muted">—</span>
                                </td>
                                <td class="lp-td">
                                    <span v-if="t.expired_at" :class="['lp-expiry', t.is_expired ? 'lp-expiry--past' : 'lp-expiry--future']">{{ t.expired_at }}</span>
                                    <span v-else class="lp-td--muted">—</span>
                                </td>
                                <td class="lp-td lp-td--points">
                                    <span :class="['lp-points', t.points > 0 ? 'lp-points--pos' : 'lp-points--neg']">
                                        {{ t.points > 0 ? '+' : '' }}{{ formatNumber(t.points) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ===== MODAL POTONG POINT ===== -->
        <Transition name="lp-modal">
            <div v-if="showDeductModal" class="lp-modal-backdrop" @click.self="closeDeductModal">
                <div class="lp-modal">
                    <div class="lp-modal__header">
                        <div class="lp-modal__header-left">
                            <div class="lp-modal__icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </div>
                            <div>
                                <h3 class="lp-modal__title">Potong Point Manual</h3>
                                <p class="lp-modal__subtitle">Digunakan setelah pelanggan redeem point via WhatsApp</p>
                            </div>
                        </div>
                        <button @click="closeDeductModal" class="lp-modal__close">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <div class="lp-modal__body">
                        <div v-if="deductError" class="lp-alert lp-alert--red">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ deductError }}
                        </div>

                        <div class="lp-form-group">
                            <label class="lp-label">Nomor HP Pelanggan <span class="lp-req">*</span></label>
                            <div class="lp-input-prefix">
                                <span>+62</span>
                                <input
                                    v-model="deductForm.phone"
                                    type="tel"
                                    class="lp-input lp-input--prefixed"
                                    placeholder="81234567890"
                                    @input="deductForm.phone = deductForm.phone.replace(/\D/g, ''); checkDeductBalance()"
                                />
                            </div>
                        </div>

                        <div v-if="deductBalance !== null" class="lp-balance-preview">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            Saldo saat ini: <strong>{{ formatNumber(deductBalance) }} point</strong>
                            <span v-if="deductBalance === 0" class="lp-balance-zero">(tidak ada saldo)</span>
                        </div>

                        <div class="lp-form-group">
                            <label class="lp-label">Jumlah Point yang Dipotong <span class="lp-req">*</span></label>
                            <input v-model.number="deductForm.points" type="number" min="1" class="lp-input" placeholder="Contoh: 5000"/>
                            <span v-if="deductBalance !== null && deductForm.points > 0" class="lp-input-hint">
                                Sisa setelah potong: <strong>{{ formatNumber(Math.max(0, deductBalance - deductForm.points)) }} point</strong>
                            </span>
                        </div>

                        <div class="lp-form-group">
                            <label class="lp-label">Keterangan</label>
                            <input v-model="deductForm.description" type="text" class="lp-input" placeholder="Misal: Redeem WhatsApp - Order #INV-2024-001"/>
                        </div>
                    </div>

                    <div class="lp-modal__footer">
                        <button @click="closeDeductModal" class="lp-btn lp-btn--ghost">Batal</button>
                        <button
                            @click="submitDeduct"
                            :disabled="!deductForm.phone || !deductForm.points || deductForm.points < 1 || deductLoading"
                            class="lp-btn lp-btn--primary"
                        >
                            <svg v-if="deductLoading" class="lp-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            {{ deductLoading ? 'Memproses...' : 'Potong Point' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

    </AdminLayout>
</template>

<script>
import AdminLayout from '../../components/admin/AdminLayout.vue'
import axios from '../../axios.js'

export default {
    name: 'LoyaltyPoints',
    components: { AdminLayout },

    data() {
        return {
            searchPhone: '',
            searching: false,
            searchError: '',
            customer: null,

            recentTransactions: [],
            loadingRecent: false,

            stats: {
                total_customers: 0,
                total_points_active: 0,
                total_points_earned: 0,
                total_points_deducted: 0,
            },

            showDeductModal: false,
            deductForm: { phone: '', points: null, description: '' },
            deductBalance: null,
            deductError: '',
            deductLoading: false,
            deductBalanceTimeout: null,
        }
    },

    mounted() {
        document.title = 'Loyalty Points - Admin'
        const favicon = document.querySelector("link[rel='icon']")
        if (favicon) favicon.href = '/storage/logos/favicon.webp'
        this.fetchStats()
        this.fetchRecentTransactions()
    },

    methods: {
        formatNumber(val) {
            if (val === null || val === undefined) return '0'
            return new Intl.NumberFormat('id-ID').format(val)
        },

        async fetchStats() {
            try {
                const res = await axios.get('/loyalty/stats')
                this.stats = res.data
            } catch (e) {}
        },

        async fetchRecentTransactions() {
            this.loadingRecent = true
            try {
                const res = await axios.get('/loyalty/recent')
                this.recentTransactions = res.data.data || []
            } catch (e) {
                this.recentTransactions = []
            } finally {
                this.loadingRecent = false
            }
        },

        async searchCustomer() {
            if (this.searchPhone.length < 5) return
            this.searching = true
            this.searchError = ''
            this.customer = null
            try {
                const res = await axios.get('/loyalty/check', { params: { phone: this.searchPhone } })
                this.customer = res.data
            } catch (e) {
                this.searchError = e.response?.data?.message || 'Nomor tidak ditemukan atau terjadi kesalahan.'
            } finally {
                this.searching = false
            }
        },

        quickSearch(phone) { this.searchPhone = phone; this.searchCustomer() },
        clearSearch() { this.searchPhone = ''; this.customer = null; this.searchError = '' },

        openDeductModal() {
            if (this.customer) {
                this.deductForm.phone = this.customer.phone.replace('+62', '')
                this.deductBalance = this.customer.balance
            } else {
                this.deductForm.phone = ''
                this.deductBalance = null
            }
            this.deductForm.points = null
            this.deductForm.description = ''
            this.deductError = ''
            this.showDeductModal = true
        },

        closeDeductModal() { this.showDeductModal = false; this.deductError = '' },

        checkDeductBalance() {
            clearTimeout(this.deductBalanceTimeout)
            this.deductBalance = null
            if (this.deductForm.phone.length < 8) return
            this.deductBalanceTimeout = setTimeout(async () => {
                try {
                    const res = await axios.get('/loyalty/check', { params: { phone: `+62${this.deductForm.phone}` } })
                    this.deductBalance = res.data.balance
                } catch { this.deductBalance = null }
            }, 600)
        },

        async submitDeduct() {
            if (!this.deductForm.phone || !this.deductForm.points) return
            this.deductLoading = true
            this.deductError = ''
            try {
                await axios.post('/loyalty/deduct', {
                    phone: `+62${this.deductForm.phone}`,
                    points: this.deductForm.points,
                    description: this.deductForm.description || null,
                })
                this.closeDeductModal()
                await this.fetchRecentTransactions()
                await this.fetchStats()
                if (this.customer && this.customer.phone === `+62${this.deductForm.phone}`) {
                    await this.searchCustomer()
                }
            } catch (e) {
                this.deductError = e.response?.data?.message || 'Gagal memotong point.'
            } finally {
                this.deductLoading = false
            }
        },
    },
}
</script>

<style scoped>
.lp-page { display: flex; flex-direction: column; gap: 20px; }

/* ═══════════════════════════════════════════════════════
   HERO HEADER
═══════════════════════════════════════════════════════ */
.lp-hero {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);
}
.lp-hero__circle {
    position: absolute;
    border-radius: 50%;
    background: white;
    pointer-events: none;
}
.lp-hero__circle--1 { width: 192px; height: 192px; top: -32px; right: -32px; opacity: .10; }
.lp-hero__circle--2 { width: 256px; height: 256px; bottom: -40px; right: -96px; opacity: .05; }
.lp-hero__circle--3 { width: 80px; height: 80px; top: 16px; right: 128px; opacity: .10; }

.lp-hero__inner {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 24px 28px;
}
.lp-hero__eyebrow { color: rgba(255,255,255,.65); font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; margin: 0 0 4px; }
.lp-hero__title { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 2px; letter-spacing: -.3px; }
.lp-hero__subtitle { font-size: 12px; color: rgba(255,255,255,.65); margin: 0; }

.lp-hero__right { display: flex; align-items: center; }
.lp-hero__live {
    display: flex; align-items: center; gap: 8px;
    font-size: 12px; font-weight: 600; color: #fff;
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
    padding: 6px 12px; border-radius: 8px;
}
.lp-hero__live-dot {
    display: inline-block; width: 6px; height: 6px; border-radius: 50%;
    background: #34d399; animation: lp-pulse 2s ease-in-out infinite;
}
@keyframes lp-pulse { 0%,100% { opacity: 1; } 50% { opacity: .4; } }

.lp-hero__strip {
    position: relative;
    border-top: 1px solid rgba(255,255,255,.12);
    padding: 14px 28px;
    display: flex; flex-wrap: wrap; gap: 0;
}
.lp-hero__stat { display: flex; flex-direction: column; padding: 0 20px; }
.lp-hero__stat + .lp-hero__stat { border-left: 1px solid rgba(255,255,255,.15); }
.lp-hero__stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: rgba(255,255,255,.6); margin: 0 0 2px; }
.lp-hero__stat-value { font-size: 20px; font-weight: 700; color: #fff; margin: 0; line-height: 1.2; }
.lp-hero__stat-value--green { color: #34d399; }
.lp-hero__stat-value--amber { color: #fbbf24; }
.lp-hero__stat-value--muted { color: rgba(255,255,255,.7); }

/* ═══════════════════════════════════════════════════════
   CARD BASE
═══════════════════════════════════════════════════════ */
.lp-card {
    background: #fff;
    border: 1px solid rgba(229,231,235,.8);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
    transition: box-shadow .2s, border-color .2s;
}

/* ═══════════════════════════════════════════════════════
   FILTER BAR
═══════════════════════════════════════════════════════ */
.lp-filterbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 10px 16px; }
.lp-filterbar__search { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 240px; }
.lp-filterbar__icon { color: #9ca3af; flex-shrink: 0; }
.lp-filterbar__input { border: none; outline: none; font-size: 13px; color: #111827; width: 100%; background: transparent; }
.lp-filterbar__input::placeholder { color: #9ca3af; }
.lp-clear-btn {
    display: flex; align-items: center; justify-content: center;
    width: 20px; height: 20px; border-radius: 50%;
    border: 1px solid #e5e7eb; background: #f3f4f6; color: #9ca3af;
    cursor: pointer; flex-shrink: 0; transition: all .15s;
}
.lp-clear-btn:hover { background: #e5e7eb; color: #374151; }

/* ═══════════════════════════════════════════════════════
   BUTTONS
═══════════════════════════════════════════════════════ */
.lp-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .15s; white-space: nowrap; }
.lp-btn--primary       { background: #ED1F24; color: #fff; }
.lp-btn--primary:hover { background: #C81A1E; }
.lp-btn--primary:disabled { opacity: .5; cursor: not-allowed; }
.lp-btn--ghost         { background: transparent; color: #6b7280; border: 1px solid #e5e7eb; }
.lp-btn--ghost:hover   { background: #f9fafb; }

/* ═══════════════════════════════════════════════════════
   CUSTOMER PANEL
═══════════════════════════════════════════════════════ */
.lp-customer-panel { overflow: hidden; }

.lp-customer-header {
    display: flex; align-items: center; gap: 14px;
    padding: 16px 20px;
    background: linear-gradient(135deg, rgba(237,31,36,.04) 0%, rgba(237,31,36,.08) 100%);
    border-bottom: 1px solid rgba(237,31,36,.12);
    flex-wrap: wrap;
}
.lp-customer-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: rgba(237,31,36,.10);
    display: flex; align-items: center; justify-content: center;
    color: #ED1F24; flex-shrink: 0;
    border: 1px solid rgba(237,31,36,.15);
}
.lp-customer-info { display: flex; flex-direction: column; gap: 2px; flex: 1; }
.lp-customer-phone { font-size: 15px; font-weight: 700; color: #111827; }
.lp-customer-meta  { font-size: 12px; color: #ED1F24; font-weight: 500; }

.lp-customer-balance { display: flex; flex-direction: column; align-items: flex-end; gap: 1px; margin-left: auto; }
.lp-balance-label { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; }
.lp-balance-value { font-size: 28px; font-weight: 800; color: #ED1F24; line-height: 1; }
.lp-balance-sub   { font-size: 11px; color: rgba(237,31,36,.5); }

.lp-expiring-badge {
    display: flex; align-items: center; gap: 6px;
    background: #fef3c7; border: 1px solid #fde68a;
    color: #92400e; font-size: 11px; font-weight: 600;
    padding: 5px 10px; border-radius: 20px; flex-shrink: 0;
}

/* ═══════════════════════════════════════════════════════
   TABLE — seragam dengan Orders.vue
═══════════════════════════════════════════════════════ */
.lp-table-wrap { overflow: auto; }
.lp-table { width: 100%; border-collapse: collapse; min-width: 700px; font-size: 13px; }
.lp-th {
    padding: 10px 16px;
    font-size: 10px; font-weight: 700;
    color: #9ca3af; text-transform: uppercase; letter-spacing: .06em;
    text-align: left; white-space: nowrap;
    background: rgba(249,250,251,.6);
    border-bottom: 1px solid #f3f4f6;
}
.lp-th--right { text-align: right; }

.lp-tr { transition: background .1s; }
.lp-tr:hover           { background: rgba(249,250,251,.6); }
.lp-tr--earn:hover     { background: #f0fdf4; }
.lp-tr--expire:hover   { background: #fef2f2; }
.lp-tr--deduct:hover   { background: #fffbeb; }

.lp-td { padding: 11px 16px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.lp-td--date  { font-size: 12px; color: #6b7280; white-space: nowrap; }
.lp-td--desc  { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lp-td--muted { color: #d1d5db; }
.lp-td--points { text-align: right; }
.lp-empty-row { text-align: center; padding: 40px; color: #9ca3af; font-size: 13px; }

/* ═══════════════════════════════════════════════════════
   TYPE BADGE
═══════════════════════════════════════════════════════ */
.lp-type-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px; border-radius: 20px;
    font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
    border: 1px solid transparent;
}
.lp-type-badge--earn   { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
.lp-type-badge--expire { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
.lp-type-badge--deduct { background: #fef3c7; color: #92400e; border-color: #fde68a; }

/* ═══════════════════════════════════════════════════════
   POINTS & LINKS
═══════════════════════════════════════════════════════ */
.lp-points { font-size: 14px; font-weight: 700; }
.lp-points--pos { color: #16a34a; }
.lp-points--neg { color: #dc2626; }

.lp-order-link { color: #ED1F24; font-weight: 600; font-size: 12px; text-decoration: none; }
.lp-order-link:hover { text-decoration: underline; }

.lp-phone-link {
    background: none; border: none;
    color: #ED1F24; font-weight: 600; font-size: 13px;
    cursor: pointer; padding: 0;
    text-decoration: underline; text-underline-offset: 2px;
    transition: color .15s;
}
.lp-phone-link:hover { color: #C81A1E; }

.lp-expiry { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; }
.lp-expiry--future { color: #6b7280; }
.lp-expiry--past   { color: #9ca3af; text-decoration: line-through; }
.lp-expiry-tag     { background: #fef2f2; color: #dc2626; font-size: 10px; font-weight: 600; padding: 1px 6px; border-radius: 4px; }

/* ═══════════════════════════════════════════════════════
   EMPTY / ERROR
═══════════════════════════════════════════════════════ */
.lp-empty-state {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    padding: 48px 20px; text-align: center;
    background: #fff;
    border: 1px solid rgba(229,231,235,.8);
    border-radius: 12px;
}
.lp-empty-state p    { font-size: 14px; color: #6b7280; margin: 0; font-weight: 500; }
.lp-empty-state span { font-size: 12px; color: #9ca3af; }

.lp-error-state {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 16px;
    background: #fef2f2; border: 1px solid #fecaca;
    border-radius: 10px; font-size: 13px; color: #991b1b;
}

.lp-loading { display: flex; align-items: center; gap: 10px; padding: 40px; justify-content: center; color: #9ca3af; font-size: 13px; }

/* ═══════════════════════════════════════════════════════
   RECENT SECTION
═══════════════════════════════════════════════════════ */
.lp-recent { overflow: hidden; }
.lp-recent__header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px;
    border-bottom: 1px solid #f3f4f6;
}
.lp-recent__title { font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 2px; }
.lp-recent__sub   { font-size: 12px; color: #9ca3af; margin: 0; }
.lp-recent__badge {
    display: flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 600; color: #ED1F24;
    background: rgba(237,31,36,.06);
    border: 1px solid rgba(237,31,36,.15);
    padding: 4px 10px; border-radius: 20px;
}
.lp-live-dot {
    display: inline-block; width: 6px; height: 6px; border-radius: 50%;
    background: #ED1F24; animation: lp-pulse 2s ease-in-out infinite;
}

/* ═══════════════════════════════════════════════════════
   MODAL
═══════════════════════════════════════════════════════ */
.lp-modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.45); backdrop-filter: blur(3px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.lp-modal {
    background: #fff; border-radius: 16px; width: 100%; max-width: 480px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    border: 1px solid rgba(229,231,235,.5);
    display: flex; flex-direction: column;
}
.lp-modal__header { display: flex; justify-content: space-between; align-items: center; padding: 18px 22px; border-bottom: 1px solid #f3f4f6; }
.lp-modal__header-left { display: flex; align-items: center; gap: 12px; }
.lp-modal__icon {
    width: 36px; height: 36px; border-radius: 10px;
    background: rgba(237,31,36,.08); color: #ED1F24;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    border: 1px solid rgba(237,31,36,.15);
}
.lp-modal__title    { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 2px; }
.lp-modal__subtitle { font-size: 12px; color: #9ca3af; margin: 0; }
.lp-modal__close {
    width: 32px; height: 32px; border-radius: 8px;
    border: 1px solid #e5e7eb; background: #fff;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: #9ca3af; transition: all .15s;
}
.lp-modal__close:hover { background: #f9fafb; color: #374151; border-color: #d1d5db; }
.lp-modal__body   { padding: 20px 22px; display: flex; flex-direction: column; gap: 16px; }
.lp-modal__footer { display: flex; justify-content: flex-end; gap: 8px; padding: 16px 22px; border-top: 1px solid #f3f4f6; }

/* ═══════════════════════════════════════════════════════
   FORM
═══════════════════════════════════════════════════════ */
.lp-form-group { display: flex; flex-direction: column; gap: 5px; }
.lp-label { font-size: 12px; font-weight: 600; color: #374151; }
.lp-req   { color: #ef4444; }

.lp-input {
    padding: 9px 12px; border: 1px solid #e5e7eb; border-radius: 8px;
    font-size: 13px; color: #111827; outline: none; transition: border .15s;
    font-family: inherit;
}
.lp-input:focus { border-color: #ED1F24; }

.lp-input-prefix { display: flex; align-items: center; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; transition: border .15s; }
.lp-input-prefix:focus-within { border-color: #ED1F24; }
.lp-input-prefix > span { padding: 9px 12px; background: #f9fafb; font-size: 12px; color: #9ca3af; border-right: 1px solid #e5e7eb; white-space: nowrap; }
.lp-input--prefixed { border: none; border-radius: 0; flex: 1; padding: 9px 12px; font-size: 13px; color: #111827; outline: none; background: transparent; font-family: inherit; }

.lp-input-hint { font-size: 12px; color: #6b7280; }

.lp-balance-preview {
    display: flex; align-items: center; gap: 6px;
    background: rgba(237,31,36,.04); border: 1px solid rgba(237,31,36,.15);
    border-radius: 8px; padding: 8px 12px; font-size: 13px; color: #991b1b;
}
.lp-balance-preview svg { color: #ED1F24; flex-shrink: 0; }
.lp-balance-zero { color: #9ca3af; font-weight: 400; }

.lp-alert { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 8px; font-size: 13px; }
.lp-alert--red { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

/* ═══════════════════════════════════════════════════════
   UTILS
═══════════════════════════════════════════════════════ */
.lp-spin { animation: lp-spin 1s linear infinite; }
@keyframes lp-spin { to { transform: rotate(360deg); } }

.lp-modal-enter-active, .lp-modal-leave-active { transition: all .2s; }
.lp-modal-enter-from, .lp-modal-leave-to { opacity: 0; transform: scale(.97); }

/* ═══════════════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════════════ */
@media (max-width: 640px) {
    .lp-hero__circle--2 { display: none; }
    .lp-hero__stat:nth-child(n+3) { display: none; }
    .lp-customer-header { flex-direction: column; align-items: flex-start; }
    .lp-customer-balance { align-items: flex-start; margin-left: 0; }
}
</style>