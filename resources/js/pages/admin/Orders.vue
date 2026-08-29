<template>
    <AdminLayout title="Order Management">

        <div class="or-page">

            <!-- ───────────────────────── HERO HEADER ───────────────────────── -->
            <div class="or-hero">
                <div class="or-hero__circle or-hero__circle--1"></div>
                <div class="or-hero__circle or-hero__circle--2"></div>
                <div class="or-hero__circle or-hero__circle--3"></div>

                <div class="or-hero__inner">
                    <div>
                        <p class="or-hero__eyebrow">Order Management</p>
                        <h1 class="or-hero__title">Pesanan Masuk</h1>
                        <p class="or-hero__subtitle">Kelola dan perbarui status pesanan pelanggan</p>
                    </div>
                    <div class="or-hero__right">
                        <button v-if="canCreateOrder" @click="showManualModal = true" class="or-hero__add-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Tambah Order
                        </button>
                        <div class="or-hero__live">
                            <span class="or-hero__live-dot"></span>
                            Live
                        </div>
                    </div>
                </div>

                <div class="or-hero__strip">
                    <div class="or-hero__stat">
                        <p class="or-hero__stat-label">Total Order</p>
                        <p class="or-hero__stat-value">{{ stats.total }}</p>
                    </div>
                    <div class="or-hero__stat">
                        <p class="or-hero__stat-label">Pending</p>
                        <p class="or-hero__stat-value or-hero__stat-value--amber">{{ stats.pending }}</p>
                    </div>
                    <div class="or-hero__stat">
                        <p class="or-hero__stat-label">Diproses</p>
                        <p class="or-hero__stat-value or-hero__stat-value--blue">{{ stats.diproses }}</p>
                    </div>
                    <div class="or-hero__stat">
                        <p class="or-hero__stat-label">Sukses</p>
                        <p class="or-hero__stat-value or-hero__stat-value--green">{{ stats.success }}</p>
                    </div>
                    <div class="or-hero__stat">
                        <p class="or-hero__stat-label">Dibatalkan</p>
                        <p class="or-hero__stat-value or-hero__stat-value--red">{{ stats.cancelled }}</p>
                    </div>
                </div>
            </div>

            <!-- ───────────────────────── FILTER BAR ───────────────────────── -->
            <div class="or-card or-filterbar">
                <div class="or-filterbar__search">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="or-filterbar__icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input v-model="search" type="text" placeholder="Cari nama, nomor HP, atau ID order..." class="or-filterbar__input" @input="onSearch"/>
                </div>
                <div class="or-filterbar__selects">
                    <select v-model="filterStatus" class="or-select" @change="fetchOrders">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="diproses">Diproses</option>
                        <option value="success">Sukses</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
            </div>

            <!-- ───────────────────────── TABLE ───────────────────────── -->
            <div class="or-card or-table-wrap">
                <div class="or-table-scroll">
                    <table class="or-table">
                        <thead>
                            <tr>
                                <th class="or-th">Order ID</th>
                                <th class="or-th">Pelanggan</th>
                                <th class="or-th">Tujuan</th>
                                <th class="or-th">Kurir</th>
                                <th class="or-th">Total</th>
                                <th class="or-th">Tanggal</th>
                                <th class="or-th">Status</th>
                                <th class="or-th or-th--action">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="8" class="or-loading">
                                    <svg class="or-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                    Memuat data...
                                </td>
                            </tr>
                            <tr v-else-if="orders.length === 0">
                                <td colspan="8" class="or-empty">
                                    <div class="or-empty__inner">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.3"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                        <p>Belum ada pesanan</p>
                                        <span>Pesanan dari pelanggan akan muncul di sini</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-else v-for="order in orders" :key="order.id" class="or-tr">
                                <td class="or-td">
                                    <span class="or-order-id">{{ order.invoice_number }}</span>
                                </td>
                                <td class="or-td">
                                    <div class="or-customer">
                                        <span class="or-customer__name">{{ order.customer_name }}</span>
                                        <a :href="`https://wa.me/${order.customer_phone.replace(/\D/g,'')}`" target="_blank" class="or-customer__phone">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.528 5.851L.057 23.547a.75.75 0 0 0 .916.919l5.808-1.517A11.943 11.943 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.708 9.708 0 0 1-4.953-1.354l-.355-.21-3.678.961.98-3.589-.23-.37A9.718 9.718 0 0 1 2.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/></svg>
                                            {{ order.customer_phone }}
                                        </a>
                                    </div>
                                </td>
                                <td class="or-td">
                                    <div class="or-destination">
                                        <template v-if="order.shipping_courier?.toLowerCase() === 'pickup'">
                                            <span class="or-destination__pickup-badge">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                                Pickup
                                            </span>
                                            <span class="or-destination__main">{{ order.branch?.name || 'Branch tidak diset' }}</span>
                                        </template>
                                        <template v-else>
                                            <span class="or-destination__main">{{ order.subdistrict }}, {{ order.district }}</span>
                                            <span class="or-destination__sub">{{ order.city }}, {{ order.province }}</span>
                                        </template>
                                    </div>
                                </td>
                                <td class="or-td">
                                    <div class="or-courier">
                                        <span class="or-courier__name">{{ order.shipping_courier.toUpperCase() }}</span>
                                        <span class="or-courier__service">{{ order.shipping_service }}</span>
                                    </div>
                                </td>
                                <td class="or-td">
                                    <span class="or-total">{{ formatPrice(order.total_price) }}</span>
                                </td>
                                <td class="or-td">
                                    <span class="or-date">{{ formatDate(order.created_at) }}</span>
                                </td>
                                <td class="or-td">
                                    <span :class="['or-badge', statusClass(order.status)]">
                                        {{ statusLabel(order.status) }}
                                    </span>
                                    <span v-if="order.revision_count > 0" class="or-badge-rev" :title="`Direvisi ${order.revision_count}x`">
                                        Rev.{{ order.revision_count }}
                                    </span>
                                    <span v-if="order.payment_method" class="or-badge-payment" :title="paymentMethodLabel(order.payment_method)">
                                        {{ order.payment_method === 'transfer' ? '💳' : '🏬' }}
                                    </span>
                                </td>
                                <td class="or-td or-td--action">
                                    <OrderActionMenu
                                        :primary-action="getPrimaryAction(order)"
                                        :menu-groups="getMenuGroups(order)"
                                        menu-position="left"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ───────────────────────── PAGINATION ───────────────────────── -->
            <div class="or-footer">
                <span class="or-footer__info">Menampilkan {{ orders.length }} dari {{ meta.total }} pesanan</span>
                <div class="or-pagination">
                    <button @click="changePage(meta.current_page - 1)" :disabled="meta.current_page <= 1" class="or-page-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <span class="or-page-info">{{ meta.current_page }} / {{ meta.last_page }}</span>
                    <button @click="changePage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page" class="or-page-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                    <select v-model.number="perPage" class="or-select or-select--sm" @change="fetchOrders">
                        <option :value="15">15</option>
                        <option :value="30">30</option>
                        <option :value="50">50</option>
                    </select>
                </div>
            </div>

        </div>

        <!-- ===== MODAL DETAIL ===== -->
        <Transition name="or-modal">
            <div v-if="showDetail" class="or-modal-backdrop" @click.self="showDetail = false">
                <div class="or-modal">
                    <div class="or-modal__header">
                        <div class="or-modal__header-left">
                            <div class="or-modal__icon or-modal__icon--gray">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </div>
                            <div>
                                <h3 class="or-modal__title">Detail Pesanan</h3>
                                <p class="or-modal__subtitle">{{ selectedOrder?.invoice_number }}</p>
                            </div>
                        </div>
                        <button @click="showDetail = false" class="or-modal__close" title="Tutup">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <div class="or-modal__body" v-if="selectedOrder">
                        <div :class="['or-status-banner', statusBannerClass(selectedOrder.status)]">
                            <div class="or-status-banner__left">
                                <span class="or-status-banner__dot"></span>
                                <span class="or-status-banner__label">{{ statusLabel(selectedOrder.status) }}</span>
                            </div>
                            <span class="or-status-banner__date">{{ formatDate(selectedOrder.created_at) }}</span>
                        </div>

                        <div v-if="selectedOrder.status === 'cancelled' && selectedOrder.cancel_reason" class="or-cancel-reason">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <div>
                                <p class="or-cancel-reason__label">Alasan Pembatalan</p>
                                <p class="or-cancel-reason__text">{{ selectedOrder.cancel_reason }}</p>
                            </div>
                        </div>

                        <div class="or-section">
                            <div class="or-section__title">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Data Pelanggan
                            </div>
                            <div class="or-detail-grid">
                                <div class="or-detail-item">
                                    <span class="or-detail-label">Nama</span>
                                    <span class="or-detail-value">{{ selectedOrder.customer_name }}</span>
                                </div>
                                <div class="or-detail-item">
                                    <span class="or-detail-label">WhatsApp</span>
                                    <a :href="`https://wa.me/${selectedOrder.customer_phone.replace(/\D/g,'')}`" target="_blank" class="or-detail-link">{{ selectedOrder.customer_phone }}</a>
                                </div>
                                <div class="or-detail-item" v-if="selectedOrder.customer_email">
                                    <span class="or-detail-label">Email</span>
                                    <span class="or-detail-value">{{ selectedOrder.customer_email }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="or-section">
                            <div class="or-section__title">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ selectedOrder.shipping_courier?.toLowerCase() === 'pickup' ? 'Lokasi Pickup' : 'Alamat Pengiriman' }}
                            </div>
                            <div v-if="selectedOrder.shipping_courier?.toLowerCase() === 'pickup'" class="or-pickup-box">
                                <div class="or-pickup-box__badge">Ambil di cabang</div>
                                <div class="or-pickup-box__branch">{{ selectedOrder.address }}</div>
                            </div>
                            <div v-else class="or-address-box">
                                <p>{{ selectedOrder.address }}</p>
                                <p>{{ selectedOrder.subdistrict }}, {{ selectedOrder.district }}</p>
                                <p>{{ selectedOrder.city }}, {{ selectedOrder.province }} {{ selectedOrder.postal_code }}</p>
                            </div>
                        </div>

                        <div class="or-section">
                            <div class="or-section__title">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                                Pengiriman
                            </div>
                            <div class="or-detail-grid">
                                <div class="or-detail-item">
                                    <span class="or-detail-label">Kurir</span>
                                    <span class="or-detail-value">{{ selectedOrder.shipping_name }}</span>
                                </div>
                                <div class="or-detail-item">
                                    <span class="or-detail-label">Layanan</span>
                                    <span class="or-detail-value">{{ selectedOrder.shipping_service }}</span>
                                </div>
                                <div class="or-detail-item">
                                    <span class="or-detail-label">Ongkir</span>
                                    <span class="or-detail-value">{{ formatPrice(selectedOrder.shipping_cost) }}</span>
                                </div>
                                <div class="or-detail-item">
                                    <span class="or-detail-label">Estimasi</span>
                                    <span class="or-detail-value">{{ selectedOrder.shipping_etd }}</span>
                                </div>
                                <div class="or-detail-item">
                                    <span class="or-detail-label">Metode Pembayaran</span>
                                    <span v-if="selectedOrder.payment_method" class="or-detail-value">
                                        {{ paymentMethodLabel(selectedOrder.payment_method) }}
                                    </span>
                                    <span v-else class="or-detail-value or-detail-value--muted">Belum dipilih</span>
                                </div>
                            </div>
                        </div>

                        <div class="or-section">
                            <div class="or-section__title">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                Detail Produk
                            </div>
                            <div class="or-items">
                                <div v-for="item in selectedOrder.items" :key="item.id" class="or-item">
                                    <div class="or-item__info">
                                        <span class="or-item__name">{{ item.product_name }}</span>
                                        <span v-if="item.variant_label && item.variant_names" class="or-item__variant">
                                            {{ item.variant_label }}: {{ item.variant_names }}
                                        </span>
                                    </div>
                                    <div class="or-item__price">
                                        <span class="or-item__qty">{{ item.qty }}×</span>
                                        <span>{{ formatPrice(item.sell_price) }}</span>
                                        <span class="or-item__subtotal">{{ formatPrice(item.subtotal) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="selectedOrder.notes" class="or-section">
                            <div class="or-section__title">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Catatan
                            </div>
                            <div class="or-notes-box">{{ selectedOrder.notes }}</div>
                        </div>

                        <div class="or-section" v-if="selectedOrder?.confirmed_by_name || selectedOrder?.revised_by_name">
                            <div class="or-section__title">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Ditangani Oleh
                            </div>
                            <div class="or-detail-grid">
                                <div class="or-detail-item" v-if="selectedOrder?.confirmed_by_name">
                                    <span class="or-detail-label">Konfirmasi</span>
                                    <span class="or-detail-value">{{ selectedOrder.confirmed_by_name }}</span>
                                    <span class="or-detail-meta">{{ formatDate(selectedOrder.confirmed_at) }}</span>
                                </div>
                                <div class="or-detail-item" v-if="selectedOrder?.revised_by_name">
                                    <span class="or-detail-label">Revisi Terakhir</span>
                                    <span class="or-detail-value">{{ selectedOrder.revised_by_name }}</span>
                                    <span class="or-detail-meta">{{ formatDate(selectedOrder.revised_at) }}</span>
                                </div>
                            </div>
                            <div v-if="selectedOrder?.last_revision_note || selectedOrder?.last_revision_changes?.length"
                                class="or-revision-box">
                                <span class="or-revision-box__label">Ringkasan Revisi Terakhir</span>
                                <p v-if="selectedOrder.last_revision_note" class="or-revision-box__note">
                                    {{ selectedOrder.last_revision_note }}
                                </p>
                                <ul v-if="selectedOrder.last_revision_changes?.length" class="or-revision-box__list">
                                    <li v-for="(change, i) in selectedOrder.last_revision_changes" :key="i">{{ change }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="or-price-summary">
                            <div class="or-price-row">
                                <span>Subtotal</span>
                                <span>{{ formatPrice(selectedOrder.subtotal) }}</span>
                            </div>
                            <div class="or-price-row" v-if="selectedOrder.discount_amount > 0">
                                <span>Diskon ({{ selectedOrder.promo_code }})</span>
                                <span class="or-price-discount">-{{ formatPrice(selectedOrder.discount_amount) }}</span>
                            </div>
                            <div class="or-price-row">
                                <span>Ongkir ({{ selectedOrder.shipping_courier.toUpperCase() }} {{ selectedOrder.shipping_service }})</span>
                                <span>{{ formatPrice(selectedOrder.shipping_cost) }}</span>
                            </div>
                            <div class="or-price-row or-price-row--total">
                                <span>Total</span>
                                <span>{{ formatPrice(selectedOrder.total_price) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="or-modal__footer">
                        <button @click="showDetail = false" class="or-btn or-btn--ghost">Tutup</button>
                        <button @click="openInvoiceFromDetail" class="or-btn or-btn--invoice">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            Invoice
                        </button>
                        <button v-if="selectedOrder && !isFinalStatus(selectedOrder.status)" @click="openUpdateStatusFromDetail" class="or-btn or-btn--primary">
                            Update Status
                        </button>
                        <button
                            v-if="selectedOrder && canRevise && isRevisableStatus(selectedOrder.status)"
                            @click="openReviseFromDetail(selectedOrder)"
                            class="or-btn or-btn--revise"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Revisi Item
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ===== MODAL UPDATE STATUS ===== -->
        <Transition name="or-modal">
            <div v-if="showStatusModal" class="or-modal-backdrop" :style="statusLoading ? 'pointer-events:none' : ''">
                <div class="or-modal or-modal--md">
                    <div class="or-modal__header">
                        <div class="or-modal__header-left">
                            <div class="or-modal__icon or-modal__icon--blue">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <div>
                                <h3 class="or-modal__title">Update Status Pesanan</h3>
                                <p class="or-modal__subtitle">{{ selectedOrder?.invoice_number }} — {{ selectedOrder?.customer_name }}</p>
                            </div>
                        </div>
                        <button @click="confirmClose(statusFormDirty, () => showStatusModal = false)" class="or-modal__close" title="Tutup">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <div v-if="statusError" class="or-alert">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ statusError }}
                    </div>

                    <div class="or-modal__body">
                        <div class="or-section">
                            <div class="or-field">
                                <label class="or-label">Pilih Status Baru <span class="or-label__req">*</span></label>
                                <div class="or-status-options">
                                    <label
                                        v-for="opt in statusOptionsForModal"
                                        :key="opt.value"
                                        class="or-status-opt"
                                        :class="{ [`or-status-opt--${opt.value}`]: statusForm.status === opt.value }"
                                    >
                                        <input type="radio" v-model="statusForm.status" :value="opt.value" class="or-status-radio"/>
                                        <div class="or-status-opt__icon" :class="`or-status-opt__icon--${opt.value}`" v-html="opt.icon"></div>
                                        <div class="or-status-opt__text">
                                            <span class="or-status-opt__label">{{ opt.label }}</span>
                                            <span class="or-status-opt__desc">{{ opt.desc }}</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <Transition name="or-slide">
                                <div v-if="statusForm.status === 'cancelled'" class="or-field">
                                    <label class="or-label">Alasan Pembatalan <span class="or-label__req">*</span></label>
                                    <textarea v-model="statusForm.cancel_reason" class="or-textarea" rows="3" placeholder="Tulis alasan pembatalan order ini..."/>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <div class="or-modal__footer">
                        <button @click="confirmClose(statusFormDirty, () => showStatusModal = false)" class="or-btn or-btn--ghost" :disabled="statusLoading">Batal</button>
                        <button @click="submitStatus" :disabled="statusLoading || !statusForm.status" :class="['or-btn', statusForm.status === 'cancelled' ? 'or-btn--danger' : (statusForm.status === 'diproses' ? 'or-btn--blue' : 'or-btn--primary')]">
                            <svg v-if="statusLoading" class="or-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            {{ statusLoading ? 'Menyimpan...' : 'Simpan Status' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ===== MODAL HAPUS (Admin Only) ===== -->
        <Transition name="or-modal">
            <div v-if="showDeleteModal" class="or-modal-backdrop" :style="deleteLoading ? 'pointer-events:none' : ''">
                <div class="or-modal or-modal--sm or-modal--danger">
                    <div class="or-modal__header or-modal__header--danger">
                        <div class="or-modal__header-left">
                            <div class="or-modal__icon or-modal__icon--red">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </div>
                            <div>
                                <h3 class="or-modal__title">Hapus Order Permanen</h3>
                                <p class="or-modal__subtitle">Tindakan ini tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        <button @click="showDeleteModal = false" class="or-modal__close" :disabled="deleteLoading" title="Tutup">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <div v-if="deleteError" class="or-alert">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ deleteError }}
                    </div>

                    <div class="or-modal__body">
                        <div class="or-delete-confirm">
                            <div class="or-delete-confirm__icon">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            </div>
                            <div class="or-delete-confirm__content">
                                <p class="or-delete-confirm__text">
                                    Yakin ingin menghapus order <strong>{{ orderToDelete?.invoice_number }}</strong> atas nama <strong>{{ orderToDelete?.customer_name }}</strong>?
                                </p>
                                <p class="or-delete-confirm__sub">Semua data termasuk item produk akan terhapus permanen dari sistem dan tidak dapat dipulihkan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="or-modal__footer">
                        <button @click="showDeleteModal = false" class="or-btn or-btn--ghost" :disabled="deleteLoading">Batal, jangan hapus</button>
                        <button @click="submitDelete" :disabled="deleteLoading" class="or-btn or-btn--danger">
                            <svg v-if="deleteLoading" class="or-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            {{ deleteLoading ? 'Menghapus...' : 'Ya, Hapus Permanen' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ===== MODAL INVOICE ===== -->
        <Transition name="or-modal">
            <div v-if="showInvoice" class="or-invoice-backdrop" @click.self="showInvoice = false">
                <div class="or-invoice-shell">
                    <div class="or-invoice-toolbar no-print">
                        <div class="or-invoice-toolbar__left">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            Invoice {{ invoiceData?.invoice_number }}
                        </div>
                        <div class="or-invoice-toolbar__right">
                            <button @click="printInvoice" class="or-btn or-btn--ghost">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                Cetak
                            </button>
                            <button @click="downloadInvoicePDF" :disabled="downloadingPdf" class="or-btn or-btn--primary">
                                <svg v-if="downloadingPdf" class="or-spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                <svg v-else width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                {{ downloadingPdf ? 'Mengunduh...' : 'Download PDF' }}
                            </button>
                            <button @click="showInvoice = false" class="or-btn or-btn--ghost">Tutup</button>
                        </div>
                    </div>

                    <div class="or-invoice-doc" id="invoice-print-area" v-if="invoiceData">
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
                                <div v-else-if="invoiceData.status === 'diproses'" class="inv-diproses">DIPROSES</div>
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

                    <div v-if="invoiceLoading" class="or-invoice-loading">
                        <svg class="or-spin" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        <span>Memuat invoice...</span>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ===== MODAL REQUEST DELETE ===== -->
        <Transition name="or-modal">
            <div v-if="showRequestDeleteModal" class="or-modal-backdrop" :style="requestDeleteLoading ? 'pointer-events:none' : ''">
                <div class="or-modal or-modal--sm">
                    <div class="or-modal__header">
                        <div class="or-modal__header-left">
                            <div class="or-modal__icon or-modal__icon--orange">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            </div>
                            <div>
                                <h3 class="or-modal__title">Ajukan Penghapusan</h3>
                                <p class="or-modal__subtitle">{{ orderToRequestDelete?.invoice_number }} — {{ orderToRequestDelete?.customer_name }}</p>
                            </div>
                        </div>
                        <button @click="confirmClose(requestDeleteFormDirty, () => showRequestDeleteModal = false)" class="or-modal__close" :disabled="requestDeleteLoading" title="Tutup">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <div v-if="requestDeleteError" class="or-alert">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ requestDeleteError }}
                    </div>

                    <div class="or-modal__body">
                        <div class="or-info-box">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <p>Pengajuan ini akan dikirim ke admin untuk ditinjau. Order <strong>tidak akan langsung terhapus</strong> sampai admin menyetujuinya.</p>
                        </div>
                        <div class="or-field">
                            <label class="or-label">Alasan Pengajuan <span class="or-label__req">*</span></label>
                            <textarea v-model="requestDeleteForm.reason" class="or-textarea" rows="4" placeholder="Contoh: Pelanggan konfirmasi cancel via WhatsApp, order duplikat, data salah input, dll" maxlength="500"/>
                            <span class="or-char-count">{{ requestDeleteForm.reason.length }}/500</span>
                        </div>
                    </div>

                    <div class="or-modal__footer">
                        <button @click="confirmClose(requestDeleteFormDirty, () => showRequestDeleteModal = false)" class="or-btn or-btn--ghost" :disabled="requestDeleteLoading">Batal</button>
                        <button @click="submitRequestDelete" :disabled="requestDeleteLoading" class="or-btn or-btn--orange">
                            <svg v-if="requestDeleteLoading" class="or-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            {{ requestDeleteLoading ? 'Mengirim...' : 'Kirim Pengajuan ke Admin' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <OrderReviseModal
            :show="showReviseModal"
            :order="orderToRevise"
            :can-change-price="canRevisePrice"
            :can-change-courier="canReviseCourier"
            @close="onReviseModalClose"
            @revised="onRevised"
        />
        <OrderManualModal
            :show="showManualModal"
            @close="showManualModal = false"
            @created="onManualOrderCreated"
        />
    </AdminLayout>
</template>

<script>
import jsPDF from 'jspdf'
import html2canvas from 'html2canvas'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import OrderReviseModal from './OrderReviseModal.vue'
import OrderActionMenu from './OrderActionMenu.vue'
import OrderManualModal from './OrderManualModal.vue'
import axios from '../../axios.js'
import { getPermissions } from '../../auth.js'

export default {
    name: 'Orders',
    components: { AdminLayout, OrderReviseModal, OrderActionMenu, OrderManualModal },

    data() {
        return {
            orders: [],
            storeLogo: '',
            loading: false,
            meta: { total: 0, current_page: 1, last_page: 1, per_page: 15 },
            stats: { total: 0, pending: 0, diproses: 0, success: 0, cancelled: 0 },

            search: '',
            filterStatus: '',
            perPage: 15,
            searchTimeout: null,

            showDetail: false,
            selectedOrder: null,

            showStatusModal: false,
            statusForm: { status: 'success', cancel_reason: '' },
            statusFormDirty: false,
            statusLoading: false,
            statusError: '',

            showDeleteModal: false,
            orderToDelete: null,
            deleteLoading: false,
            deleteError: '',

            showRequestDeleteModal: false,
            orderToRequestDelete: null,
            requestDeleteForm: { reason: '' },
            requestDeleteFormDirty: false,
            requestDeleteLoading: false,
            requestDeleteError: '',

            showManualModal: false,
            showReviseModal: false,
            orderToRevise: null,

            showInvoice: false,
            invoiceData: null,
            invoiceLoading: false,
            downloadingPdf: false,
            printedBy: '',
            storeName: '',
            storePhone: '',

            _svg: {
                eye:   `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`,
                check: `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>`,
                print: `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>`,
                edit:  `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`,
                trash: `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>`,
                flag:  `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>`,
                wa:    `<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.528 5.851L.057 23.547a.75.75 0 0 0 .916.919l5.808-1.517A11.943 11.943 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.708 9.708 0 0 1-4.953-1.354l-.355-.21-3.678.961.98-3.589-.23-.37A9.718 9.718 0 0 1 2.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/></svg>`,
                clock: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`,
                x:     `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
            },
        }
    },

    watch: {
        'statusForm.status'()        { this.statusFormDirty = true },
        'statusForm.cancel_reason'() { this.statusFormDirty = true },
        'requestDeleteForm.reason'() { this.requestDeleteFormDirty = true },
    },

    computed: {
        canCreateOrder() {
            try { return getPermissions().includes('orders_create') } catch { return false }
        },
        canDelete() {
            try { return getPermissions().includes('orders_delete') } catch { return false }
        },
        canRequestDelete() {
            try {
                const p = getPermissions()
                return p.includes('orders_view') && !p.includes('orders_delete')
            } catch { return false }
        },
        canRevise() {
            try { return getPermissions().includes('orders_revise') } catch { return false }
        },
        canRevisePrice() {
            try { return getPermissions().includes('orders_revise_price') } catch { return false }
        },
        canReviseCourier() {
            try { return getPermissions().includes('orders_revise_courier') } catch { return false }
        },
        statusOptionsForModal() {
            const current = this.selectedOrder?.status
            const all = [
                { value: 'diproses',  label: 'Diproses',   desc: 'Pesanan sedang diproses',    icon: this._svg.clock },
                { value: 'success',   label: 'Sukses',     desc: 'Pesanan selesai & diterima', icon: this._svg.check },
                { value: 'cancelled', label: 'Dibatalkan', desc: 'Batalkan pesanan ini',       icon: this._svg.x },
            ]
            return all.filter(opt => opt.value !== current)
        },
    },

    mounted() {
        document.title = 'Orders - Two Brothers Vape System'
        this.fetchOrders()
        this.fetchStats()
        window.addEventListener('keydown', this.handleEscape)
    },

    beforeUnmount() {
        window.removeEventListener('keydown', this.handleEscape)
    },

    methods: {
        handleEscape(e) {
            if (e.key !== 'Escape') return
            if (this.showStatusModal) {
                this.confirmClose(this.statusFormDirty, () => { this.showStatusModal = false })
            } else if (this.showRequestDeleteModal) {
                this.confirmClose(this.requestDeleteFormDirty, () => { this.showRequestDeleteModal = false })
            } else if (this.showDeleteModal) {
                if (!this.deleteLoading) this.showDeleteModal = false
            } else if (this.showInvoice) {
                this.showInvoice = false
            } else if (this.showDetail) {
                this.showDetail = false
            }
        },

        onManualOrderCreated() {
            this.showManualModal = false
            this.fetchOrders(this.meta.current_page)
            this.fetchStats()
        },

        confirmClose(dirtyFlag, closeFn) {
            if (dirtyFlag) {
                if (!confirm('Ada perubahan yang belum disimpan. Yakin ingin menutup?')) return
            }
            closeFn()
        },

        formatPrice(val) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val)
        },
        formatDate(val) {
            return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
        },
        formatDateLong(val) {
            return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
        },
        statusLabel(status) {
            return { pending: 'Pending', diproses: 'Diproses', success: 'Sukses', cancelled: 'Dibatalkan' }[status] || status
        },
        paymentMethodLabel(method) {
            return { offline: 'Bayar di Toko', transfer: 'Transfer Bank' }[method] || method
        },
        statusClass(status) {
            return { pending: 'or-badge--amber', diproses: 'or-badge--blue', success: 'or-badge--green', cancelled: 'or-badge--red' }[status] || ''
        },
        statusBannerClass(status) {
            return { pending: 'or-status-banner--amber', diproses: 'or-status-banner--blue', success: 'or-status-banner--green', cancelled: 'or-status-banner--red' }[status] || ''
        },
        isFinalStatus(status) {
            return ['success', 'cancelled'].includes(status)
        },
        isRevisableStatus(status) {
            return ['pending', 'diproses'].includes(status)
        },

        getPrimaryAction(order) {
            const s = this._svg
            if (order.status === 'pending') {
                return { label: 'Update status', icon: s.check, variant: 'warning', handler: () => this.openUpdateStatus(order) }
            }
            if (order.status === 'diproses') {
                return { label: 'Update status', icon: s.check, variant: 'blue', handler: () => this.openUpdateStatus(order) }
            }
            return { label: 'Detail', icon: s.eye, variant: 'default', handler: () => this.openDetail(order) }
        },

        getMenuGroups(order) {
            const s = this._svg
            const groups = []
            const info = [
                { key: 'detail', label: 'Lihat detail', desc: 'Info lengkap pesanan', icon: s.eye, handler: () => this.openDetail(order) },
                { key: 'invoice', label: 'Cetak invoice', desc: 'Buka & print invoice', icon: s.print, handler: () => this.openInvoice(order) },
            ]
            if (order.customer_phone) {
                const phone = order.customer_phone.replace(/\D/g, '')
                const msg = encodeURIComponent(`Halo ${order.customer_name}, pesanan Anda dengan nomor ${order.invoice_number} sedang kami proses.`)
                info.push({ key: 'wa', label: 'Hubungi via WhatsApp', desc: order.customer_phone, icon: s.wa, handler: () => window.open(`https://wa.me/${phone}?text=${msg}`, '_blank') })
            }
            groups.push(info)
            if (!this.isFinalStatus(order.status)) {
                const ops = [{ key: 'status', label: 'Update status', desc: 'Ubah status pesanan', icon: s.check, handler: () => this.openUpdateStatus(order) }]
                if (this.canRevise && this.isRevisableStatus(order.status)) {
                    ops.push({ key: 'revise', label: 'Ubah item', desc: 'Edit produk, qty, atau harga', icon: s.edit, badge: order.revision_count > 0 ? `Rev.${order.revision_count}` : null, handler: () => this.openRevise(order) })
                }
                groups.push(ops)
            }
            const danger = []
            if (this.canDelete) {
                danger.push({ key: 'delete', label: 'Hapus order', desc: 'Permanen, tidak bisa dibatalkan', icon: s.trash, destructive: true, handler: () => this.openDelete(order) })
            } else if (this.canRequestDelete) {
                danger.push({ key: 'req-delete', label: 'Ajukan penghapusan', desc: 'Kirim ke admin untuk disetujui', icon: s.flag, destructive: true, handler: () => this.openRequestDelete(order) })
            }
            if (danger.length) groups.push(danger)
            return groups
        },

        openRequestDelete(order) {
            this.orderToRequestDelete = order
            this.requestDeleteForm = { reason: '' }
            this.requestDeleteFormDirty = false
            this.requestDeleteError = ''
            this.showRequestDeleteModal = true
        },

        async submitRequestDelete() {
            if (!this.requestDeleteForm.reason.trim()) { this.requestDeleteError = 'Alasan wajib diisi'; return }
            if (this.requestDeleteForm.reason.trim().length < 10) { this.requestDeleteError = 'Minimal 10 karakter'; return }
            this.requestDeleteLoading = true
            this.requestDeleteError = ''
            try {
                await axios.post(`/orders/${this.orderToRequestDelete.id}/request-delete`, { reason: this.requestDeleteForm.reason })
                this.showRequestDeleteModal = false
                this.orderToRequestDelete = null
                alert('Pengajuan hapus berhasil dikirim. Menunggu persetujuan admin')
            } catch (e) {
                this.requestDeleteError = e.response?.data?.message || 'Gagal mengirim'
            } finally {
                this.requestDeleteLoading = false
            }
        },

        openRevise(order) { this.orderToRevise = order; this.showReviseModal = true },

        async openReviseFromDetail(order) {
            try { const res = await axios.get(`/orders/${order.id}`); this.orderToRevise = res.data?.data || order }
            catch { this.orderToRevise = order }
            this.showDetail = false
            await this.$nextTick()
            this.showReviseModal = true
        },

        onRevised() { this.fetchOrders(this.meta.current_page); this.fetchStats() },
        onReviseModalClose() { this.showReviseModal = false; this.orderToRevise = null },

        async fetchOrders(page = 1) {
            this.loading = true
            try {
                const res = await axios.get('/orders', { params: { page, per_page: this.perPage, status: this.filterStatus || undefined, search: this.search || undefined } })
                this.orders = res.data?.data || []
                this.meta = res.data?.meta || this.meta
            } catch (e) { console.error('Gagal memuat orders:', e) }
            finally { this.loading = false }
        },

        async fetchStats() {
            try {
                const res = await axios.get('/orders/stats')
                const s = res.data?.summary || {}
                this.stats = {
                    total: s.total_orders ?? 0,
                    pending: s.total_pending ?? 0,
                    diproses: s.total_diproses ?? 0,
                    success: s.total_success ?? 0,
                    cancelled: s.total_cancelled ?? 0,
                }
            } catch (e) { console.error('Gagal memuat stats:', e) }
        },

        onSearch() { clearTimeout(this.searchTimeout); this.searchTimeout = setTimeout(() => this.fetchOrders(), 400) },
        changePage(page) { if (page < 1 || page > this.meta.last_page) return; this.fetchOrders(page) },

        async openDetail(order) {
            try {
                const orderRes = await axios.get(`/orders/${order.id}`)
                const orderData = orderRes.data?.data || order
                try {
                    const revisionRes = await axios.get(`/orders/${order.id}/revisions`)
                    const revisions = revisionRes.data?.data || []
                    if (revisions.length) {
                        const latest = revisions[0]
                        orderData.last_revision_note = latest.note || null
                        orderData.last_revision_changes = latest.changes_summary || []
                        orderData.last_revision_by = latest.revised_by_name || null
                    }
                } catch(e) {
                    orderData.last_revision_note = null
                    orderData.last_revision_changes = []
                }
                this.selectedOrder = orderData
            }
            catch { this.selectedOrder = order }
            this.showDetail = true
        },

        openUpdateStatus(order) {
            this.selectedOrder = order
            this.statusForm = { status: '', cancel_reason: '' }
            this.statusFormDirty = false
            this.statusError = ''
            this.showStatusModal = true
        },
        openUpdateStatusFromDetail() {
            this.statusForm = { status: '', cancel_reason: '' }
            this.statusFormDirty = false
            this.statusError = ''
            this.showDetail = false
            this.showStatusModal = true
        },

        async submitStatus() {
            if (!this.statusForm.status) { this.statusError = 'Pilih status terlebih dahulu.'; return }
            if (this.statusForm.status === 'cancelled' && !this.statusForm.cancel_reason.trim()) { this.statusError = 'Alasan pembatalan wajib diisi.'; return }
            this.statusLoading = true; this.statusError = ''
            try {
                await axios.patch(`/orders/${this.selectedOrder.id}/status`, { status: this.statusForm.status, cancel_reason: this.statusForm.cancel_reason || null })
                this.showStatusModal = false
                this.statusFormDirty = false
                await this.fetchOrders(this.meta.current_page)
                await this.fetchStats()
            } catch (e) { this.statusError = e.response?.data?.message || 'Terjadi kesalahan, coba lagi.' }
            finally { this.statusLoading = false }
        },

        openDelete(order) { this.orderToDelete = order; this.deleteError = ''; this.showDeleteModal = true },

        async submitDelete() {
            this.deleteLoading = true; this.deleteError = ''
            try {
                await axios.delete(`/orders/${this.orderToDelete.id}`)
                this.showDeleteModal = false; this.orderToDelete = null
                await this.fetchOrders(this.meta.current_page)
                await this.fetchStats()
            } catch (e) { this.deleteError = e.response?.data?.message || 'Gagal menghapus order.' }
            finally { this.deleteLoading = false }
        },

        async openInvoice(order) {
            this.showInvoice = true; this.invoiceData = null; this.invoiceLoading = true
            try {
                const [invoiceRes, settingsRes] = await Promise.all([axios.get(`/orders/${order.id}/invoice`), axios.get(`/settings`)])
                this.invoiceData = invoiceRes.data?.data || null
                this.printedBy = invoiceRes.data?.printed_by || ''
                this.storeName = settingsRes.data?.site_name?.value || 'Two Brothers'
                this.storePhone = settingsRes.data?.site_phone?.value || ''
                this.storeLogo = settingsRes.data?.site_logo_footer?.value || settingsRes.data?.site_logo?.value || ''
            } catch { this.invoiceData = order; this.storeName = 'Toko Kami' }
            finally { this.invoiceLoading = false }
        },

        openInvoiceFromDetail() { this.showDetail = false; this.$nextTick(() => this.openInvoice(this.selectedOrder)) },

        printInvoice() {
                    const invoiceNumber = this.invoiceData?.invoice_number || 'INVOICE'
                    const storeName = (this.storeName || 'TB').replace(/\s+/g, '').toUpperCase()
                    const filename = `${storeName}INV${invoiceNumber.replace('INV', '')}`
                    const originalTitle = document.title
                    document.title = filename
                    window.print()
                    document.title = originalTitle
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
                const pageWidth = pdf.internal.pageSize.getWidth()
                const pageHeight = pdf.internal.pageSize.getHeight()
                const imgWidth = pageWidth
                const imgHeight = (canvas.height * imgWidth) / canvas.width

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

                const invoiceNumber = this.invoiceData?.invoice_number || 'INVOICE'
                const storeName = (this.storeName || 'TB').replace(/\s+/g, '').toUpperCase()
                pdf.save(`${storeName}-INV-${invoiceNumber.replace('INV', '')}.pdf`)
            } catch (e) {
                console.error('Gagal membuat PDF:', e)
                alert('Gagal membuat PDF, coba lagi.')
            } finally {
                this.downloadingPdf = false
            }
        },
    }
}
</script>

<style scoped>
.or-page { display: flex; flex-direction: column; gap: 20px; }

/* ═══════════════════════════════════════════════════════
   HERO HEADER
═══════════════════════════════════════════════════════ */
.or-hero {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);
    margin-bottom: 4px;
}
.or-hero__circle { position: absolute; border-radius: 50%; background: white; pointer-events: none; }
.or-hero__circle--1 { width: 192px; height: 192px; top: -32px; right: -32px; opacity: .10; }
.or-hero__circle--2 { width: 256px; height: 256px; bottom: -40px; right: -96px; opacity: .05; }
.or-hero__circle--3 { width: 80px; height: 80px; top: 16px; right: 128px; opacity: .10; }
.or-hero__inner { position: relative; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; padding: 24px 28px; }
.or-hero__eyebrow { color: rgba(255,255,255,.65); font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; margin: 0 0 4px; }
.or-hero__title { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 2px; letter-spacing: -.3px; }
.or-hero__subtitle { font-size: 12px; color: rgba(255,255,255,.65); margin: 0; }
.or-hero__right { display: flex; align-items: center; gap: 12px; }
.or-hero__add-btn { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #ED1F24; background: #fff; border: none; padding: 8px 14px; border-radius: 8px; cursor: pointer; }
.or-hero__add-btn:hover { background: #f9fafb; }
.or-hero__live { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; color: #fff; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2); padding: 6px 12px; border-radius: 8px; }
.or-hero__live-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #34d399; animation: or-pulse 2s ease-in-out infinite; }
@keyframes or-pulse { 0%,100% { opacity: 1; } 50% { opacity: .4; } }
.or-hero__strip { position: relative; border-top: 1px solid rgba(255,255,255,.12); padding: 14px 28px; display: flex; flex-wrap: wrap; gap: 0; }
.or-hero__stat { display: flex; flex-direction: column; padding: 0 20px; }
.or-hero__stat + .or-hero__stat { border-left: 1px solid rgba(255,255,255,.15); }
.or-hero__stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: rgba(255,255,255,.6); margin: 0 0 2px; }
.or-hero__stat-value { font-size: 20px; font-weight: 700; color: #fff; margin: 0; line-height: 1.2; }
.or-hero__stat-value--amber { color: #fbbf24; }
.or-hero__stat-value--green { color: #34d399; }
.or-hero__stat-value--red   { color: #fca5a5; }
.or-hero__stat-value--blue { color: #93c5fd; }
.or-badge--blue { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }

.or-status-banner--blue { background: #eff6ff; border: 1px solid #bfdbfe; }
.or-status-banner--blue .or-status-banner__dot { background: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.2); }
.or-status-banner--blue .or-status-banner__label { color: #1d4ed8; }

.or-status-opt--diproses { border-color: #3b82f6; background: #eff6ff; }
.or-status-opt__icon--diproses { background: #dbeafe; color: #2563eb; }
.or-status-opt--diproses .or-status-opt__icon--diproses { background: #3b82f6; color: #fff; }
.or-status-opt--diproses .or-status-opt__label { color: #1d4ed8; }

.or-btn--blue { background: #2563eb; color: #fff; box-shadow: 0 1px 3px rgba(37,99,235,.3); }
.or-btn--blue:hover:not(:disabled) { background: #1d4ed8; }

/* ═══════════════════════════════════════════════════════
   CARD BASE
═══════════════════════════════════════════════════════ */
.or-card { background: #fff; border: 1px solid rgba(229,231,235,.8); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.04); transition: box-shadow .2s, border-color .2s; }
.or-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.07); border-color: rgba(209,213,219,.8); }

/* ─── Filter Bar ─── */
.or-filterbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 10px 16px; }
.or-filterbar__search { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 200px; }
.or-filterbar__icon { color: #9ca3af; flex-shrink: 0; }
.or-filterbar__input { border: none; outline: none; font-size: 13px; color: #111827; width: 100%; background: transparent; }
.or-filterbar__input::placeholder { color: #9ca3af; }
.or-select { padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 12px; color: #374151; background: #f9fafb; cursor: pointer; outline: none; transition: border-color .15s; }
.or-select:hover { border-color: #d1d5db; }
.or-select--sm { padding: 4px 8px; font-size: 12px; }

/* ─── Table ─── */
.or-table-wrap { overflow: visible; position: relative; }
.or-table-scroll { overflow-x: auto; }
.or-table { width: 100%; border-collapse: collapse; min-width: 820px; }
.or-th { padding: 10px 16px; font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .06em; text-align: left; background: rgba(249,250,251,.6); border-bottom: 1px solid #f3f4f6; white-space: nowrap; }
.or-th--action { width: 140px; text-align: right; }
.or-td--action  { text-align: right; white-space: nowrap; }
.or-tr { transition: background .15s; }
.or-tr:hover { background: rgba(249,250,251,.6); }
.or-td { padding: 11px 16px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.or-order-id { font-family: ui-monospace, monospace; font-weight: 700; color: #ED1F24; font-size: 12px; background: rgba(237,31,36,.06); border: 1px solid rgba(237,31,36,.12); padding: 2px 7px; border-radius: 6px; }
.or-customer { display: flex; flex-direction: column; gap: 3px; }
.or-customer__name { font-weight: 600; color: #111827; font-size: 13px; }
.or-customer__phone { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: #ED1F24; font-weight: 500; text-decoration: none; }
.or-customer__phone:hover { text-decoration: underline; }
.or-destination { display: flex; flex-direction: column; gap: 2px; }
.or-destination__main { font-size: 12px; font-weight: 600; color: #374151; }
.or-destination__sub  { font-size: 11px; color: #9ca3af; }
.or-destination__pickup-badge { display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; color: #7c3aed; background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 20px; padding: 1px 6px; margin-bottom: 2px; }
.or-courier { display: flex; flex-direction: column; gap: 2px; }
.or-courier__name    { font-size: 12px; font-weight: 700; color: #111827; }
.or-courier__service { font-size: 11px; color: #9ca3af; }
.or-total { font-weight: 700; color: #111827; font-size: 13px; }
.or-date  { font-size: 12px; color: #6b7280; white-space: nowrap; }
.or-badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; border: 1px solid transparent; }
.or-badge--amber { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.or-badge--green { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
.or-badge--red   { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
.or-badge-rev { display: inline-block; padding: 2px 6px; border-radius: 20px; font-size: 10px; font-weight: 700; background: #ede9fe; color: #7c3aed; margin-left: 4px; vertical-align: middle; }
.or-badge-payment { display: inline-block; margin-left: 4px; font-size: 12px; vertical-align: middle; cursor: default; }
.or-loading { text-align: center; padding: 40px; color: #9ca3af; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px; }
.or-empty { padding: 48px; text-align: center; }
.or-empty__inner { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.or-empty__inner p    { font-weight: 600; color: #374151; margin: 0; }
.or-empty__inner span { font-size: 13px; color: #9ca3af; }
.or-footer { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; padding: 12px 0; }
.or-footer__info { font-size: 13px; color: #9ca3af; }
.or-pagination { display: flex; align-items: center; gap: 6px; }
.or-page-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #6b7280; transition: all .15s; }
.or-page-btn:hover:not(:disabled) { background: #f9fafb; border-color: #d1d5db; }
.or-page-btn:disabled { opacity: .4; cursor: not-allowed; }
.or-page-info { font-size: 13px; color: #6b7280; padding: 0 4px; }

/* ═══════════════════════════════════════════════════════
   MODAL SYSTEM — Enterprise Grade
═══════════════════════════════════════════════════════ */

/* Backdrop: lebih gelap dan tidak dismissible via klik (kecuali detail) */
.or-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.or-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 600px;
    box-shadow:
        0 0 0 1px rgba(0,0,0,.06),
        0 8px 24px rgba(0,0,0,.12),
        0 32px 64px rgba(0,0,0,.16);
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 48px);
    overflow: hidden;
    animation: or-modal-in .2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.or-modal--sm { max-width: 460px; }

/* Danger modal: border merah tipis di atas */
.or-modal--danger {
    box-shadow:
        0 0 0 1px rgba(220,38,38,.15),
        0 8px 24px rgba(0,0,0,.12),
        0 32px 64px rgba(0,0,0,.16);
}
.or-modal--danger::before {
    content: '';
    display: block;
    height: 3px;
    background: linear-gradient(90deg, #dc2626, #ef4444);
    flex-shrink: 0;
}

@keyframes or-modal-in {
    from { opacity: 0; transform: scale(.96) translateY(8px); }
    to   { opacity: 1; transform: scale(1)  translateY(0); }
}

/* ─── Modal Header ─── */
.or-modal__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #f3f4f6;
    flex-shrink: 0;
    background: #fafafa;
}
.or-modal__header--danger {
    background: #fef2f2;
    border-bottom-color: #fecaca;
}
.or-modal__header-left { display: flex; align-items: center; gap: 12px; }

.or-modal__icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.or-modal__icon--gray   { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
.or-modal__icon--blue   { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.or-modal__icon--red    { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.or-modal__icon--orange { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }

.or-modal__title    { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 2px; letter-spacing: -.15px; }
.or-modal__subtitle { font-size: 12px; color: #9ca3af; margin: 0; font-family: ui-monospace, monospace; }

/* Tombol close: explicit, tidak halus — user harus sadar menutup */
.or-modal__close {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    transition: all .15s;
    flex-shrink: 0;
}
.or-modal__close:hover:not(:disabled) { background: #f3f4f6; color: #374151; border-color: #d1d5db; }
.or-modal__close:disabled { opacity: .4; cursor: not-allowed; }

/* ─── Modal Body ─── */
.or-modal__body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 20px;
    /* Scroll indicator shadow */
    background:
        linear-gradient(white 30%, transparent),
        linear-gradient(transparent, white 70%) 0 100%,
        radial-gradient(farthest-side at 50% 0, rgba(0,0,0,.08), transparent),
        radial-gradient(farthest-side at 50% 100%, rgba(0,0,0,.08), transparent) 0 100%;
    background-repeat: no-repeat;
    background-size: 100% 40px, 100% 40px, 100% 8px, 100% 8px;
    background-attachment: local, local, scroll, scroll;
}

/* ─── Modal Footer ─── */
.or-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 16px 24px;
    border-top: 1px solid #f3f4f6;
    flex-shrink: 0;
    background: #fafafa;
}

/* ─── Alert ─── */
.or-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    background: #fef2f2;
    border-bottom: 1px solid #fecaca;
    font-size: 13px;
    color: #991b1b;
    font-weight: 500;
}

/* ─── Status Banner ─── */
.or-status-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 11px 14px;
    border-radius: 10px;
    gap: 8px;
}
.or-status-banner__left { display: flex; align-items: center; gap: 8px; }
.or-status-banner__dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}
.or-status-banner--amber { background: #fffbeb; border: 1px solid #fde68a; }
.or-status-banner--amber .or-status-banner__dot { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.2); }
.or-status-banner--amber .or-status-banner__label { color: #92400e; }
.or-status-banner--green  { background: #f0fdf4; border: 1px solid #bbf7d0; }
.or-status-banner--green .or-status-banner__dot  { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.2); }
.or-status-banner--green  .or-status-banner__label { color: #166534; }
.or-status-banner--red    { background: #fef2f2; border: 1px solid #fecaca; }
.or-status-banner--red .or-status-banner__dot    { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.2); }
.or-status-banner--red    .or-status-banner__label { color: #991b1b; }
.or-status-banner__label { font-size: 13px; font-weight: 700; }
.or-status-banner__date  { font-size: 12px; color: #6b7280; }

/* ─── Cancel Reason ─── */
.or-cancel-reason { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; }
.or-cancel-reason svg { color: #dc2626; flex-shrink: 0; margin-top: 2px; }
.or-cancel-reason__label { font-size: 11px; font-weight: 700; color: #dc2626; text-transform: uppercase; letter-spacing: .05em; margin: 0 0 3px; }
.or-cancel-reason__text  { font-size: 13px; color: #374151; margin: 0; }

/* ─── Sections ─── */
.or-section { display: flex; flex-direction: column; gap: 12px; }
.or-section__title { display: flex; align-items: center; gap: 6px; font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .08em; padding-bottom: 8px; border-bottom: 1px solid #f3f4f6; }
.or-section__title svg { color: #9ca3af; }

.or-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.or-detail-item { display: flex; flex-direction: column; gap: 3px; }
.or-detail-label { font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
.or-detail-value { font-size: 13px; color: #111827; font-weight: 500; }
.or-detail-value--muted { color: #9ca3af; font-style: italic; font-weight: 400; }
.or-detail-meta  { font-size: 11px; color: #9ca3af; }
.or-detail-link  { font-size: 13px; color: #ED1F24; font-weight: 600; text-decoration: none; }
.or-detail-link:hover { text-decoration: underline; }

.or-address-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; }
.or-address-box p { margin: 0 0 3px; font-size: 13px; color: #374151; line-height: 1.5; }
.or-address-box p:last-child { margin: 0; }

.or-pickup-box { background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 8px; padding: 12px 14px; display: flex; flex-direction: column; gap: 4px; }
.or-pickup-box__badge  { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #7c3aed; }
.or-pickup-box__branch { font-size: 14px; font-weight: 700; color: #111827; }

.or-items { display: flex; flex-direction: column; gap: 6px; }
.or-item { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 10px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; }
.or-item__info { display: flex; flex-direction: column; gap: 2px; flex: 1; }
.or-item__name    { font-size: 13px; font-weight: 600; color: #111827; }
.or-item__variant { font-size: 11px; color: #9ca3af; }
.or-item__price   { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.or-item__qty     { font-size: 12px; color: #9ca3af; }
.or-item__subtotal { font-size: 13px; font-weight: 700; color: #111827; }

.or-notes-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; font-size: 13px; color: #374151; line-height: 1.6; }

.or-revision-box { background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 8px; padding: 12px 14px; margin-top: 4px; }
.or-revision-box__label { font-size: 10px; font-weight: 700; color: #7c3aed; text-transform: uppercase; letter-spacing: .05em; }
.or-revision-box__note  { margin: 6px 0 0; font-size: 13px; color: #374151; }
.or-revision-box__list  { margin: 6px 0 0; padding-left: 16px; font-size: 12px; color: #6b7280; }

.or-price-summary { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px 16px; display: flex; flex-direction: column; gap: 8px; }
.or-price-row { display: flex; justify-content: space-between; font-size: 13px; color: #6b7280; }
.or-price-row--total { padding-top: 10px; border-top: 1px dashed #e5e7eb; font-weight: 700; color: #111827; font-size: 15px; margin-top: 2px; }
.or-price-discount { color: #ED1F24; }

/* ─── Delete Confirm ─── */
.or-delete-confirm {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 4px 0;
}
.or-delete-confirm__icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #dc2626;
    flex-shrink: 0;
}
.or-delete-confirm__content { display: flex; flex-direction: column; gap: 6px; padding-top: 4px; }
.or-delete-confirm__text { font-size: 14px; color: #111827; line-height: 1.6; margin: 0; }
.or-delete-confirm__sub  { font-size: 12px; color: #9ca3af; margin: 0; line-height: 1.5; }

/* ─── Status Form ─── */
.or-field { display: flex; flex-direction: column; gap: 8px; }
.or-label { font-size: 12px; font-weight: 600; color: #374151; }
.or-label__req { color: #ef4444; }

/* Status options: lebih besar dan informatif */
.or-status-options { display: flex; gap: 10px; }
.or-status-opt {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    cursor: pointer;
    transition: all .15s;
    background: #fff;
}
.or-status-opt:hover { border-color: #d1d5db; background: #f9fafb; }
.or-status-opt--success   { border-color: #22c55e; background: #f0fdf4; }
.or-status-opt--cancelled { border-color: #ef4444; background: #fef2f2; }
.or-status-radio { display: none; }

.or-status-opt__icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.or-status-opt__icon--success   { background: #dcfce7; color: #16a34a; }
.or-status-opt__icon--cancelled { background: #fef2f2; color: #dc2626; }
.or-status-opt--success   .or-status-opt__icon--success   { background: #22c55e; color: #fff; }
.or-status-opt--cancelled .or-status-opt__icon--cancelled { background: #ef4444; color: #fff; }

.or-status-opt__text { display: flex; flex-direction: column; gap: 2px; }
.or-status-opt__label { font-size: 13px; font-weight: 700; color: #111827; }
.or-status-opt__desc  { font-size: 11px; color: #9ca3af; }
.or-status-opt--success   .or-status-opt__label { color: #15803d; }
.or-status-opt--cancelled .or-status-opt__label { color: #b91c1c; }

.or-textarea {
    padding: 10px 12px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    color: #111827;
    resize: vertical;
    outline: none;
    font-family: inherit;
    width: 100%;
    box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
    background: #fff;
    line-height: 1.6;
}
.or-textarea:focus {
    border-color: #ED1F24;
    box-shadow: 0 0 0 3px rgba(237,31,36,.08);
}

/* ─── Info Box ─── */
.or-info-box { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; font-size: 13px; color: #1e40af; line-height: 1.5; }
.or-info-box svg { flex-shrink: 0; margin-top: 1px; color: #3b82f6; }
.or-info-box p { margin: 0; }

.or-char-count { font-size: 11px; color: #9ca3af; text-align: right; }

/* ─── Buttons ─── */
.or-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all .15s;
    white-space: nowrap;
    letter-spacing: -.1px;
}
.or-btn:disabled { opacity: .55; cursor: not-allowed; transform: none !important; }

.or-btn--primary       { background: #ED1F24; color: #fff; box-shadow: 0 1px 3px rgba(237,31,36,.3); }
.or-btn--primary:hover:not(:disabled) { background: #C81A1E; box-shadow: 0 2px 6px rgba(237,31,36,.35); }
.or-btn--danger        { background: #dc2626; color: #fff; box-shadow: 0 1px 3px rgba(220,38,38,.3); }
.or-btn--danger:hover:not(:disabled)  { background: #b91c1c; box-shadow: 0 2px 6px rgba(220,38,38,.35); }
.or-btn--ghost         { background: transparent; color: #6b7280; border: 1px solid #e5e7eb; }
.or-btn--ghost:hover:not(:disabled)   { background: #f9fafb; color: #374151; border-color: #d1d5db; }
.or-btn--invoice       { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.or-btn--invoice:hover:not(:disabled) { background: #dbeafe; }
.or-btn--orange        { background: #ea580c; color: #fff; box-shadow: 0 1px 3px rgba(234,88,12,.3); }
.or-btn--orange:hover:not(:disabled)  { background: #c2410c; }
.or-btn--revise        { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
.or-btn--revise:hover:not(:disabled)  { background: #ede9fe; }

/* ─── Spin & Transitions ─── */
.or-spin { animation: or-spin-anim 1s linear infinite; }
@keyframes or-spin-anim { to { transform: rotate(360deg); } }

.or-modal-enter-active { transition: all .22s cubic-bezier(0.34, 1.2, 0.64, 1); }
.or-modal-leave-active { transition: all .15s ease-in; }
.or-modal-enter-from, .or-modal-leave-to { opacity: 0; transform: scale(.97) translateY(6px); }

.or-slide-enter-active { transition: all .2s ease-out; }
.or-slide-leave-active { transition: all .15s ease-in; }
.or-slide-enter-from, .or-slide-leave-to { opacity: 0; transform: translateY(-6px); }

/* ═══════════════════════════════════════════════════════
   INVOICE MODAL
═══════════════════════════════════════════════════════ */
.or-invoice-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.6); backdrop-filter: blur(4px); z-index: 1100; display: flex; flex-direction: column; align-items: center; padding: 20px; overflow-y: auto; }
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
.inv-lunas, .inv-batal, .inv-pending, .inv-diproses { font-size: 42px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; opacity: .12; transform: rotate(-15deg); margin-bottom: 8px; user-select: none; pointer-events: none; line-height: 1; }
.inv-lunas    { color: #ED1F24; }
.inv-batal    { color: #dc2626; }
.inv-pending  { color: #d97706; }
.inv-diproses { color: #2563eb; }
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
.inv-status-pill--diproses  { background: #dbeafe; color: #1d4ed8; }
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
    .or-modal__body { padding: 16px; }
    .or-modal__footer { padding: 12px 16px; }
    .or-modal__header { padding: 16px; }
}
</style>