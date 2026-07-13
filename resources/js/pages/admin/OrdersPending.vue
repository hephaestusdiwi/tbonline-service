<template>
    <AdminLayout title="Pending Orders">
        <div class="or-page">

            <!-- HERO HEADER -->
            <div class="or-hero">
                <div class="or-hero__circle or-hero__circle--1"></div>
                <div class="or-hero__circle or-hero__circle--2"></div>
                <div class="or-hero__circle or-hero__circle--3"></div>
                <div class="or-hero__inner">
                    <div>
                        <p class="or-hero__eyebrow">Order Management</p>
                        <h1 class="or-hero__title">Pesanan Pending</h1>
                        <p class="or-hero__subtitle">Pesanan yang menunggu konfirmasi dan tindakan</p>
                    </div>
                    <div class="or-hero__right">
                        <div class="or-hero__live">
                            <span class="or-hero__live-dot"></span>
                            Live · Refresh setiap 30 detik
                        </div>
                        <button @click="fetchOrders(meta.current_page)" :disabled="loading" class="or-hero__refresh-btn">
                            <svg :class="['or-hero__refresh-icon', loading ? 'or-spin' : '']" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/>
                                <path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/>
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>
                <div class="or-hero__strip">
                    <div class="or-hero__stat">
                        <p class="or-hero__stat-label">Total Pending</p>
                        <p class="or-hero__stat-value or-hero__stat-value--amber">{{ meta.total }}</p>
                    </div>
                    <div class="or-hero__stat">
                        <p class="or-hero__stat-label">Dipilih</p>
                        <p class="or-hero__stat-value">{{ selectedIds.length }}</p>
                    </div>
                    <div class="or-hero__stat">
                        <p class="or-hero__stat-label">Hari Ini</p>
                        <p class="or-hero__stat-value or-hero__stat-value--green">{{ todayCount }}</p>
                    </div>
                    <div class="or-hero__stat">
                        <p class="or-hero__stat-label">Urgent (&gt;2 jam)</p>
                        <p class="or-hero__stat-value or-hero__stat-value--red">{{ urgentCount }}</p>
                    </div>
                </div>
                <div class="or-hero__progress">
                    <div class="or-hero__progress-bar" :style="{ width: progressWidth + '%' }"></div>
                </div>
            </div>

            <!-- BULK ACTION BAR -->
            <Transition name="or-bulk">
                <div v-if="selectedIds.length > 0" class="or-bulk-bar">
                    <div class="or-bulk-bar__left">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span><strong>{{ selectedIds.length }}</strong> order dipilih</span>
                    </div>
                    <div class="or-bulk-bar__right">
                        <button @click="bulkDiproses" class="or-btn or-btn--sm or-btn--blue">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Tandai Diproses
                        </button>
                        <button @click="bulkSuccess" class="or-btn or-btn--sm or-btn--primary">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Tandai Sukses
                        </button>
                        <button @click="bulkCancel" class="or-btn or-btn--sm or-btn--danger">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Batalkan
                        </button>
                        <button @click="selectedIds = []" class="or-btn or-btn--sm or-btn--ghost">Batal Pilih</button>
                    </div>
                </div>
            </Transition>

            <!-- FILTER BAR -->
            <div class="or-card or-filterbar">
                <div class="or-filterbar__search">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="or-filterbar__icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input v-model="search" type="text" placeholder="Cari nama, nomor HP, atau ID order..." class="or-filterbar__input" @input="onSearch"/>
                </div>
                <div class="or-filterbar__selects">
                    <select v-model="filterKurir" class="or-select" @change="fetchOrders()">
                        <option value="">Semua Kurir</option>
                        <option value="jne">JNE</option>
                        <option value="jnt">J&T</option>
                        <option value="sicepat">SiCepat</option>
                        <option value="anteraja">AnterAja</option>
                        <option value="pos">Pos Indonesia</option>
                        <option value="tiki">TIKI</option>
                    </select>
                    <select v-model="sortBy" class="or-select" @change="fetchOrders()">
                        <option value="newest">Terbaru</option>
                        <option value="oldest">Terlama</option>
                        <option value="highest">Nilai Tertinggi</option>
                        <option value="lowest">Nilai Terendah</option>
                    </select>
                </div>
            </div>

            <!-- TABLE -->
            <div class="or-card or-table-wrap">
                <div class="or-table-scroll">
                    <table class="or-table">
                        <thead>
                            <tr>
                                <th class="or-th or-th--check">
                                    <label class="or-checkbox">
                                        <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" class="or-checkbox__input"/>
                                        <span class="or-checkbox__box"></span>
                                    </label>
                                </th>
                                <th class="or-th">Order ID</th>
                                <th class="or-th">Pelanggan</th>
                                <th class="or-th">Tujuan</th>
                                <th class="or-th">Kurir</th>
                                <th class="or-th">Total</th>
                                <th class="or-th">Masuk</th>
                                <th class="or-th">Durasi</th>
                                <th class="or-th or-th--action">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading && orders.length === 0">
                                <td colspan="9" class="or-loading">
                                    <svg class="or-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                    Memuat data...
                                </td>
                            </tr>
                            <tr v-else-if="orders.length === 0">
                                <td colspan="9" class="or-empty">
                                    <div class="or-empty__inner">
                                        <div class="or-empty__icon">
                                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        </div>
                                        <p>Tidak ada pesanan pending</p>
                                        <span>Semua pesanan sudah diproses 🎉</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-else v-for="order in orders" :key="order.id"
                                :class="['or-tr', selectedIds.includes(order.id) ? 'or-tr--selected' : '', isUrgent(order) ? 'or-tr--urgent' : '']">
                                <td class="or-td or-td--check">
                                    <label class="or-checkbox">
                                        <input type="checkbox" :checked="selectedIds.includes(order.id)" @change="toggleSelect(order.id)" class="or-checkbox__input"/>
                                        <span class="or-checkbox__box"></span>
                                    </label>
                                </td>
                                <td class="or-td">
                                    <div class="or-order-id-wrap">
                                        <span class="or-order-id">{{ order.invoice_number }}</span>
                                        <span v-if="isUrgent(order)" class="or-badge or-badge--urgent">
                                            <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                            Urgent
                                        </span>
                                        <span v-if="order.revision_count > 0" class="or-badge-rev">Rev.{{ order.revision_count }}</span>
                                    </div>
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
                                        <span class="or-destination__main">{{ order.subdistrict }}, {{ order.district }}</span>
                                        <span class="or-destination__sub">{{ order.city }}, {{ order.province }}</span>
                                    </div>
                                </td>
                                <td class="or-td">
                                    <div class="or-courier">
                                        <span class="or-courier__name">{{ order.shipping_courier?.toUpperCase() }}</span>
                                        <span class="or-courier__service">{{ order.shipping_service }}</span>
                                    </div>
                                </td>
                                <td class="or-td"><span class="or-total">{{ formatPrice(order.total_price) }}</span></td>
                                <td class="or-td"><span class="or-date">{{ formatDate(order.created_at) }}</span></td>
                                <td class="or-td">
                                    <span :class="['or-duration', getDurationClass(order.created_at)]">{{ getDuration(order.created_at) }}</span>
                                </td>
                                <td class="or-td or-td--action">
                                    <OrderActionMenu :primary-action="getPrimaryAction(order)" :menu-groups="getMenuGroups(order)" menu-position="left"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINATION -->
            <div class="or-footer">
                <span class="or-footer__info">Menampilkan {{ orders.length }} dari {{ meta.total }} pesanan pending</span>
                <div class="or-pagination">
                    <button @click="changePage(meta.current_page - 1)" :disabled="meta.current_page <= 1" class="or-page-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <span class="or-page-info">{{ meta.current_page }} / {{ meta.last_page }}</span>
                    <button @click="changePage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page" class="or-page-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                    <select v-model.number="perPage" class="or-select or-select--sm" @change="fetchOrders()">
                        <option :value="15">15</option><option :value="30">30</option><option :value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- MODAL DETAIL -->
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
                        <button @click="showDetail = false" class="or-modal__close">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <div class="or-modal__body" v-if="selectedOrder">
                        <div class="or-status-banner or-status-banner--amber">
                            <div class="or-status-banner__left">
                                <span class="or-status-banner__dot"></span>
                                <span class="or-status-banner__label">Pending</span>
                            </div>
                            <span class="or-status-banner__date">{{ formatDate(selectedOrder.created_at) }}</span>
                        </div>
                        <div :class="['or-duration-box', getDurationClass(selectedOrder.created_at)]">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Menunggu selama <strong>{{ getDuration(selectedOrder.created_at) }}</strong></span>
                        </div>
                        <div class="or-section">
                            <div class="or-section__title"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Data Pelanggan</div>
                            <div class="or-detail-grid">
                                <div class="or-detail-item"><span class="or-detail-label">Nama</span><span class="or-detail-value">{{ selectedOrder.customer_name }}</span></div>
                                <div class="or-detail-item"><span class="or-detail-label">WhatsApp</span><a :href="`https://wa.me/${selectedOrder.customer_phone.replace(/\D/g,'')}`" target="_blank" class="or-detail-link">{{ selectedOrder.customer_phone }}</a></div>
                                <div v-if="selectedOrder.customer_email" class="or-detail-item"><span class="or-detail-label">Email</span><span class="or-detail-value">{{ selectedOrder.customer_email }}</span></div>
                            </div>
                        </div>
                        <div class="or-section">
                            <div class="or-section__title"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> Alamat Pengiriman</div>
                            <div class="or-address-box">
                                <p>{{ selectedOrder.address }}</p>
                                <p>{{ selectedOrder.subdistrict }}, {{ selectedOrder.district }}</p>
                                <p>{{ selectedOrder.city }}, {{ selectedOrder.province }} {{ selectedOrder.postal_code }}</p>
                            </div>
                        </div>
                        <div class="or-section">
                            <div class="or-section__title"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg> Pengiriman</div>
                            <div class="or-detail-grid">
                                <div class="or-detail-item"><span class="or-detail-label">Kurir</span><span class="or-detail-value">{{ selectedOrder.shipping_name }}</span></div>
                                <div class="or-detail-item"><span class="or-detail-label">Layanan</span><span class="or-detail-value">{{ selectedOrder.shipping_service }}</span></div>
                                <div class="or-detail-item"><span class="or-detail-label">Ongkir</span><span class="or-detail-value">{{ formatPrice(selectedOrder.shipping_cost) }}</span></div>
                                <div class="or-detail-item"><span class="or-detail-label">Estimasi</span><span class="or-detail-value">{{ selectedOrder.shipping_etd }}</span></div>
                            </div>
                        </div>
                        <div class="or-section">
                            <div class="or-section__title"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg> Detail Produk</div>
                            <div class="or-items">
                                <div v-for="item in selectedOrder.items" :key="item.id" class="or-item">
                                    <div class="or-item__info">
                                        <span class="or-item__name">{{ item.product_name }}</span>
                                        <span v-if="item.variant_label && item.variant_names" class="or-item__variant">{{ item.variant_label }}: {{ item.variant_names }}</span>
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
                            <div class="or-section__title"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> Catatan</div>
                            <div class="or-notes-box">{{ selectedOrder.notes }}</div>
                        </div>
                        <div class="or-price-summary">
                            <div class="or-price-row"><span>Subtotal</span><span>{{ formatPrice(selectedOrder.subtotal) }}</span></div>
                            <div v-if="selectedOrder.discount_amount > 0" class="or-price-row"><span>Diskon ({{ selectedOrder.promo_code }})</span><span class="or-price-discount">-{{ formatPrice(selectedOrder.discount_amount) }}</span></div>
                            <div class="or-price-row"><span>Ongkir ({{ selectedOrder.shipping_courier?.toUpperCase() }} {{ selectedOrder.shipping_service }})</span><span>{{ formatPrice(selectedOrder.shipping_cost) }}</span></div>
                            <div class="or-price-row or-price-row--total"><span>Total</span><span>{{ formatPrice(selectedOrder.total_price) }}</span></div>
                        </div>
                    </div>
                    <div class="or-modal__footer">
                        <button @click="showDetail = false" class="or-btn or-btn--ghost">Tutup</button>
                        <a v-if="selectedOrder" :href="getWaLink(selectedOrder)" target="_blank" class="or-btn or-btn--wa">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.528 5.851L.057 23.547a.75.75 0 0 0 .916.919l5.808-1.517A11.943 11.943 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.708 9.708 0 0 1-4.953-1.354l-.355-.21-3.678.961.98-3.589-.23-.37A9.718 9.718 0 0 1 2.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/></svg>
                            Hubungi WA
                        </a>
                        <button @click="openInvoiceFromDetail" class="or-btn or-btn--invoice">Invoice</button>
                        <button @click="openUpdateStatusFromDetail" class="or-btn or-btn--primary">Update Status</button>
                        <button v-if="canRevise" @click="openReviseFromDetail(selectedOrder)" class="or-btn or-btn--revise">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Revisi Item
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- MODAL UPDATE STATUS -->
        <Transition name="or-modal">
            <div v-if="showStatusModal" class="or-modal-backdrop" @click.self="showStatusModal = false">
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
                        <button @click="showStatusModal = false" class="or-modal__close"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                    </div>
                    <div v-if="statusError" class="or-alert"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ statusError }}</div>
                    <div class="or-modal__body">
                        <div class="or-section">
                            <div class="or-field">
                                <label class="or-label">Pilih Status Baru <span class="or-label__req">*</span></label>
                                <div class="or-status-options">
                                    <label class="or-status-opt" :class="{ 'or-status-opt--diproses': statusForm.status === 'diproses' }">
                                        <input type="radio" v-model="statusForm.status" value="diproses" class="or-status-radio"/>
                                        <div class="or-status-opt__icon or-status-opt__icon--diproses"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                                        <div class="or-status-opt__text"><span class="or-status-opt__label">Diproses</span><span class="or-status-opt__desc">Pesanan sedang diproses</span></div>
                                    </label>
                                    <label class="or-status-opt" :class="{ 'or-status-opt--success': statusForm.status === 'success' }">
                                        <input type="radio" v-model="statusForm.status" value="success" class="or-status-radio"/>
                                        <div class="or-status-opt__icon or-status-opt__icon--success"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg></div>
                                        <div class="or-status-opt__text"><span class="or-status-opt__label">Sukses</span><span class="or-status-opt__desc">Pesanan selesai & diterima</span></div>
                                    </label>
                                    <label class="or-status-opt" :class="{ 'or-status-opt--cancelled': statusForm.status === 'cancelled' }">
                                        <input type="radio" v-model="statusForm.status" value="cancelled" class="or-status-radio"/>
                                        <div class="or-status-opt__icon or-status-opt__icon--cancelled"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
                                        <div class="or-status-opt__text"><span class="or-status-opt__label">Dibatalkan</span><span class="or-status-opt__desc">Batalkan pesanan ini</span></div>
                                    </label>
                                </div>
                            </div>
                            <div v-if="statusForm.status === 'cancelled'" class="or-field">
                                <label class="or-label">Alasan Pembatalan <span class="or-label__req">*</span></label>
                                <textarea v-model="statusForm.cancel_reason" class="or-textarea" rows="3" placeholder="Tulis alasan pembatalan order ini..."/>
                            </div>
                        </div>
                    </div>
                    <div class="or-modal__footer">
                        <button @click="showStatusModal = false" class="or-btn or-btn--ghost">Batal</button>
                        <button @click="submitStatus" :disabled="statusLoading || !statusForm.status" :class="['or-btn', statusForm.status === 'cancelled' ? 'or-btn--danger' : (statusForm.status === 'diproses' ? 'or-btn--blue' : 'or-btn--primary')]">
                            <svg v-if="statusLoading" class="or-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            {{ statusLoading ? 'Menyimpan...' : 'Simpan Status' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- MODAL BULK CANCEL -->
        <Transition name="or-modal">
            <div v-if="showBulkCancelModal" class="or-modal-backdrop" @click.self="showBulkCancelModal = false">
                <div class="or-modal or-modal--sm">
                    <div class="or-modal__header">
                        <div class="or-modal__header-left">
                            <div class="or-modal__icon or-modal__icon--red"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
                            <div><h3 class="or-modal__title">Batalkan {{ selectedIds.length }} Order</h3><p class="or-modal__subtitle">Semua order yang dipilih akan dibatalkan</p></div>
                        </div>
                        <button @click="showBulkCancelModal = false" class="or-modal__close"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                    </div>
                    <div v-if="bulkError" class="or-alert">{{ bulkError }}</div>
                    <div class="or-modal__body">
                        <div class="or-field">
                            <label class="or-label">Alasan Pembatalan <span class="or-label__req">*</span></label>
                            <textarea v-model="bulkCancelReason" class="or-textarea" rows="3" placeholder="Alasan pembatalan untuk semua order yang dipilih..."/>
                        </div>
                    </div>
                    <div class="or-modal__footer">
                        <button @click="showBulkCancelModal = false" class="or-btn or-btn--ghost">Batal</button>
                        <button @click="submitBulkCancel" :disabled="bulkLoading" class="or-btn or-btn--danger">
                            <svg v-if="bulkLoading" class="or-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            {{ bulkLoading ? 'Membatalkan...' : `Batalkan ${selectedIds.length} Order` }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- MODAL DELETE (Admin) -->
        <Transition name="or-modal">
            <div v-if="showDeleteModal" class="or-modal-backdrop" :style="deleteLoading ? 'pointer-events:none' : ''" @click.self="showDeleteModal = false">
                <div class="or-modal or-modal--sm or-modal--danger">
                    <div class="or-modal__header or-modal__header--danger">
                        <div class="or-modal__header-left">
                            <div class="or-modal__icon or-modal__icon--red"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></div>
                            <div><h3 class="or-modal__title">Hapus Order Permanen</h3><p class="or-modal__subtitle">Tindakan ini tidak dapat dibatalkan</p></div>
                        </div>
                        <button @click="showDeleteModal = false" class="or-modal__close" :disabled="deleteLoading"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                    </div>
                    <div v-if="deleteError" class="or-alert">{{ deleteError }}</div>
                    <div class="or-modal__body">
                        <div class="or-delete-confirm">
                            <div class="or-delete-confirm__icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
                            <div class="or-delete-confirm__content">
                                <p class="or-delete-confirm__text">Yakin ingin menghapus order <strong>{{ orderToDelete?.invoice_number }}</strong> atas nama <strong>{{ orderToDelete?.customer_name }}</strong>?</p>
                                <p class="or-delete-confirm__sub">Semua data termasuk item produk akan terhapus permanen dan tidak dapat dipulihkan.</p>
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

        <!-- MODAL AJUKAN HAPUS (Staff/Manager) -->
        <Transition name="or-modal">
            <div v-if="showRequestDeleteModal" class="or-modal-backdrop" @click.self="showRequestDeleteModal = false">
                <div class="or-modal or-modal--sm">
                    <div class="or-modal__header">
                        <div class="or-modal__header-left">
                            <div class="or-modal__icon or-modal__icon--orange"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
                            <div><h3 class="or-modal__title">Ajukan Penghapusan</h3><p class="or-modal__subtitle">{{ orderToRequestDelete?.invoice_number }} — {{ orderToRequestDelete?.customer_name }}</p></div>
                        </div>
                        <button @click="showRequestDeleteModal = false" class="or-modal__close"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                    </div>
                    <div v-if="requestDeleteError" class="or-alert">{{ requestDeleteError }}</div>
                    <div class="or-modal__body">
                        <div class="or-info-box">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <p>Pengajuan ini akan dikirim ke admin untuk ditinjau. Order <strong>tidak akan langsung terhapus</strong> sampai admin menyetujuinya.</p>
                        </div>
                        <div class="or-field">
                            <label class="or-label">Alasan Pengajuan <span class="or-label__req">*</span></label>
                            <textarea v-model="requestDeleteForm.reason" class="or-textarea" rows="4" placeholder="Contoh: Pelanggan konfirmasi cancel via WhatsApp, order duplikat, dll" maxlength="500"/>
                            <span class="or-char-count">{{ requestDeleteForm.reason.length }}/500</span>
                        </div>
                    </div>
                    <div class="or-modal__footer">
                        <button @click="showRequestDeleteModal = false" class="or-btn or-btn--ghost">Batal</button>
                        <button @click="submitRequestDelete" :disabled="requestDeleteLoading" class="or-btn or-btn--orange">
                            <svg v-if="requestDeleteLoading" class="or-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            {{ requestDeleteLoading ? 'Mengirim...' : 'Kirim Pengajuan ke Admin' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <OrderReviseModal :show="showReviseModal" :order="orderToRevise" :can-change-price="canRevisePrice" :can-change-courier="canReviseCourier" @close="onReviseModalClose" @revised="onRevised"/>
    </AdminLayout>
</template>

<script>
import AdminLayout from '../../components/admin/AdminLayout.vue'
import OrderReviseModal from './OrderReviseModal.vue'
import OrderActionMenu from './OrderActionMenu.vue'
import axios from '../../axios.js'
import { getPermissions } from '../../auth.js'

const POLL_INTERVAL = 30000
const URGENT_HOURS  = 2

export default {
    name: 'OrdersPending',
    components: { AdminLayout, OrderReviseModal, OrderActionMenu },

    data() {
        return {
            orders: [], loading: false,
            meta: { total: 0, current_page: 1, last_page: 1, per_page: 15 },
            todayCount: 0, urgentCount: 0,
            search: '', filterKurir: '', sortBy: 'newest', perPage: 15, searchTimeout: null,
            selectedIds: [], showBulkCancelModal: false, bulkCancelReason: '', bulkLoading: false, bulkError: '',
            showDetail: false, selectedOrder: null,
            showStatusModal: false, statusForm: { status: '', cancel_reason: '' }, statusLoading: false, statusError: '',
            showDeleteModal: false, orderToDelete: null, deleteLoading: false, deleteError: '',
            showRequestDeleteModal: false, orderToRequestDelete: null, requestDeleteForm: { reason: '' }, requestDeleteLoading: false, requestDeleteError: '',
            showReviseModal: false, orderToRevise: null,
            showInvoice: false, invoiceData: null, invoiceLoading: false, storeName: '', storePhone: '', storeLogo: '',
            pollTimer: null, progressTimer: null, progressWidth: 0, pollStarted: null,
            _svg: {
                eye:   `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`,
                check: `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>`,
                print: `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>`,
                edit:  `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`,
                trash: `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>`,
                flag:  `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>`,
                wa:    `<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.528 5.851L.057 23.547a.75.75 0 0 0 .916.919l5.808-1.517A11.943 11.943 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.708 9.708 0 0 1-4.953-1.354l-.355-.21-3.678.961.98-3.589-.23-.37A9.718 9.718 0 0 1 2.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/></svg>`,
                clock: `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`,
            },
        }
    },

    computed: {
        isAllSelected() { return this.orders.length > 0 && this.orders.every(o => this.selectedIds.includes(o.id)) },
        canDelete()        { try { return getPermissions().includes('orders_delete') } catch { return false } },
        canRequestDelete() { try { const p = getPermissions(); return p.includes('orders_view') && !p.includes('orders_delete') } catch { return false } },
        canRevise()        { try { return getPermissions().includes('orders_revise') } catch { return false } },
        canRevisePrice()   { try { return getPermissions().includes('orders_revise_price') } catch { return false } },
        canReviseCourier() { try { return getPermissions().includes('orders_revise_courier') } catch { return false } },
    },

    mounted() {
        document.title = 'Orders Pending - Two Brothers Vape System'
        this.fetchOrders()
        this.startPolling()
    },
    beforeUnmount() { this.stopPolling() },

    methods: {
        formatPrice(val) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val) },
        formatDate(val)  { return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) },
        formatDateLong(val) { return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) },

        getDuration(createdAt) {
            const diff = Math.floor((Date.now() - new Date(createdAt)) / 1000)
            if (diff < 60) return `${diff} detik`
            if (diff < 3600) return `${Math.floor(diff / 60)} menit`
            if (diff < 86400) return `${Math.floor(diff / 3600)} jam`
            return `${Math.floor(diff / 86400)} hari`
        },
        getDurationClass(createdAt) {
            const hours = (Date.now() - new Date(createdAt)) / 3600000
            if (hours >= 24) return 'or-duration--critical'
            if (hours >= URGENT_HOURS) return 'or-duration--urgent'
            return 'or-duration--ok'
        },
        isUrgent(order) { return (Date.now() - new Date(order.created_at)) / 3600000 >= URGENT_HOURS },
        isToday(dateStr) { const d = new Date(dateStr), n = new Date(); return d.getFullYear()===n.getFullYear() && d.getMonth()===n.getMonth() && d.getDate()===n.getDate() },
        getWaLink(order) {
            const phone = order.customer_phone?.replace(/\D/g, '')
            const msg = encodeURIComponent(`Halo ${order.customer_name}, kami mengkonfirmasi pesanan Anda dengan nomor ${order.invoice_number}. Apakah pesanan sudah siap dikonfirmasi?`)
            return `https://wa.me/${phone}?text=${msg}`
        },
        getPrimaryAction(order) {
            return order.status === 'pending'
                ? { label: 'Update status', icon: this._svg.check, variant: 'success', handler: () => this.openUpdateStatus(order) }
                : { label: 'Detail', icon: this._svg.eye, variant: 'default', handler: () => this.openDetail(order) }
        },
        getMenuGroups(order) {
            const s = this._svg, groups = []
            const info = [
                { key: 'detail',  label: 'Lihat detail',       desc: 'Info lengkap pesanan', icon: s.eye,   handler: () => this.openDetail(order) },
                { key: 'invoice', label: 'Cetak invoice',       desc: 'Buka & print invoice', icon: s.print, handler: () => this.openInvoice(order) },
            ]
            if (order.customer_phone) {
                const phone = order.customer_phone.replace(/\D/g, '')
                const msg = encodeURIComponent(`Halo ${order.customer_name}, pesanan Anda dengan nomor ${order.invoice_number} sedang kami proses`)
                info.push({ key: 'wa', label: 'Hubungi via WhatsApp', desc: order.customer_phone, icon: s.wa, handler: () => window.open(`https://wa.me/${phone}?text=${msg}`, '_blank') })
            }
            groups.push(info)
            if (order.status === 'pending') {
                const ops = [{ key: 'status', label: 'Update Status', desc: 'Ubah status pesanan', icon: s.check, handler: () => this.openUpdateStatus(order) }]
                if (this.canRevise) ops.push({ key: 'revise', label: 'Revisi Item', desc: 'Edit produk, qty, atau harga', icon: s.edit, badge: order.revision_count > 0 ? `Rev.${order.revision_count}` : null, handler: () => this.openRevise(order) })
                groups.push(ops)
            }
            const danger = []
            if (this.canDelete) danger.push({ key: 'delete', label: 'Hapus Order', desc: 'Permanen, tidak bisa dibatalkan', icon: s.trash, destructive: true, handler: () => this.openDelete(order) })
            else if (this.canRequestDelete) danger.push({ key: 'req-delete', label: 'Ajukan Penghapusan', desc: 'Kirim ke admin untuk disetujui', icon: s.flag, destructive: true, handler: () => this.openRequestDelete(order) })
            if (danger.length) groups.push(danger)
            return groups
        },
        async fetchOrders(page = 1) {
            this.loading = true
            try {
                const sortMap = { newest: { sort: 'created_at', order: 'desc' }, oldest: { sort: 'created_at', order: 'asc' }, highest: { sort: 'total_price', order: 'desc' }, lowest: { sort: 'total_price', order: 'asc' } }
                const { sort, order } = sortMap[this.sortBy] || sortMap.newest
                const res = await axios.get('/orders', { params: { page, per_page: this.perPage, status: 'pending', search: this.search || undefined, shipping_courier: this.filterKurir || undefined, sort, order } })
                this.orders = res.data?.data || []
                this.meta   = res.data?.meta || this.meta
                this.todayCount  = this.orders.filter(o => this.isToday(o.created_at)).length
                this.urgentCount = this.orders.filter(o => this.isUrgent(o)).length
                this.selectedIds = this.selectedIds.filter(id => this.orders.some(o => o.id === id))
            } catch (e) { console.error('Gagal memuat pending orders:', e) }
            finally { this.loading = false }
        },
        onSearch()          { clearTimeout(this.searchTimeout); this.searchTimeout = setTimeout(() => this.fetchOrders(), 400) },
        changePage(page)    { if (page < 1 || page > this.meta.last_page) return; this.fetchOrders(page) },
        startPolling() {
            this.resetProgress()
            this.pollTimer     = setInterval(() => { this.fetchOrders(this.meta.current_page); this.resetProgress() }, POLL_INTERVAL)
            this.progressTimer = setInterval(() => { this.progressWidth = Math.min(((Date.now() - this.pollStarted) / POLL_INTERVAL) * 100, 100) }, 200)
        },
        stopPolling()       { clearInterval(this.pollTimer); clearInterval(this.progressTimer) },
        resetProgress()     { this.pollStarted = Date.now(); this.progressWidth = 0 },
        toggleSelect(id)    { const idx = this.selectedIds.indexOf(id); idx === -1 ? this.selectedIds.push(id) : this.selectedIds.splice(idx, 1) },
        toggleSelectAll()   { this.isAllSelected ? this.selectedIds = [] : this.selectedIds = this.orders.map(o => o.id) },
        bulkSuccess()       { if (!confirm(`Tandai ${this.selectedIds.length} order sebagai Sukses?`)) return; this.submitBulkStatus('success', '') },
        bulkDiproses()      { if (!confirm(`Tandai ${this.selectedIds.length} order sebagai Diproses?`)) return; this.submitBulkStatus('diproses', '') },
        bulkCancel()        { this.bulkCancelReason = ''; this.bulkError = ''; this.showBulkCancelModal = true },
        async submitBulkCancel() { if (!this.bulkCancelReason.trim()) { this.bulkError = 'Alasan pembatalan wajib diisi.'; return }; await this.submitBulkStatus('cancelled', this.bulkCancelReason); this.showBulkCancelModal = false },
        async submitBulkStatus(status, cancelReason) {
            this.bulkLoading = true; this.bulkError = ''
            try { await Promise.all(this.selectedIds.map(id => axios.patch(`/orders/${id}/status`, { status, cancel_reason: cancelReason || null }))); this.selectedIds = []; await this.fetchOrders(this.meta.current_page) }
            catch (e) { this.bulkError = e.response?.data?.message || 'Gagal memproses beberapa order.' }
            finally { this.bulkLoading = false }
        },
        async openDetail(order) {
            try { const res = await axios.get(`/orders/${order.id}`); this.selectedOrder = res.data?.data || order } catch { this.selectedOrder = order }
            this.showDetail = true
        },
        openUpdateStatus(order)      { this.selectedOrder = order; this.statusForm = { status: '', cancel_reason: '' }; this.statusError = ''; this.showStatusModal = true },
        openUpdateStatusFromDetail() { this.statusForm = { status: '', cancel_reason: '' }; this.statusError = ''; this.showDetail = false; this.showStatusModal = true },
        async submitStatus() {
            if (!this.statusForm.status) { this.statusError = 'Pilih status terlebih dahulu.'; return }
            if (this.statusForm.status === 'cancelled' && !this.statusForm.cancel_reason.trim()) { this.statusError = 'Alasan pembatalan wajib diisi.'; return }
            this.statusLoading = true; this.statusError = ''
            try { await axios.patch(`/orders/${this.selectedOrder.id}/status`, { status: this.statusForm.status, cancel_reason: this.statusForm.cancel_reason || null }); this.showStatusModal = false; await this.fetchOrders(this.meta.current_page) }
            catch (e) { this.statusError = e.response?.data?.message || 'Terjadi kesalahan, coba lagi.' }
            finally { this.statusLoading = false }
        },
        openDelete(order)   { this.orderToDelete = order; this.deleteError = ''; this.showDeleteModal = true },
        async submitDelete() {
            this.deleteLoading = true; this.deleteError = ''
            try { await axios.delete(`/orders/${this.orderToDelete.id}`); this.showDeleteModal = false; this.orderToDelete = null; await this.fetchOrders(this.meta.current_page) }
            catch (e) { this.deleteError = e.response?.data?.message || 'Gagal menghapus order.' }
            finally { this.deleteLoading = false }
        },
        openRequestDelete(order) { this.orderToRequestDelete = order; this.requestDeleteForm = { reason: '' }; this.requestDeleteError = ''; this.showRequestDeleteModal = true },
        async submitRequestDelete() {
            if (!this.requestDeleteForm.reason.trim()) { this.requestDeleteError = 'Alasan wajib diisi.'; return }
            if (this.requestDeleteForm.reason.trim().length < 10) { this.requestDeleteError = 'Minimal 10 karakter.'; return }
            this.requestDeleteLoading = true; this.requestDeleteError = ''
            try { await axios.post(`/orders/${this.orderToRequestDelete.id}/request-delete`, { reason: this.requestDeleteForm.reason }); this.showRequestDeleteModal = false; this.orderToRequestDelete = null; alert('Pengajuan hapus berhasil dikirim. Menunggu persetujuan admin.') }
            catch (e) { this.requestDeleteError = e.response?.data?.message || 'Gagal mengirim.' }
            finally { this.requestDeleteLoading = false }
        },
        openRevise(order) { this.orderToRevise = order; this.showReviseModal = true },
        async openReviseFromDetail(order) {
            try { const res = await axios.get(`/orders/${order.id}`); this.orderToRevise = res.data?.data || order } catch { this.orderToRevise = order }
            this.showDetail = false; await this.$nextTick(); this.showReviseModal = true
        },
        onRevised()          { this.fetchOrders(this.meta.current_page) },
        onReviseModalClose() { this.showReviseModal = false; this.orderToRevise = null },
        async openInvoice(order) {
            this.showInvoice = true; this.invoiceData = null; this.invoiceLoading = true
            try {
                const [invoiceRes, settingsRes] = await Promise.all([axios.get(`/orders/${order.id}/invoice`), axios.get('/settings')])
                this.invoiceData = invoiceRes.data?.data || null
                this.storeName   = settingsRes.data?.site_name?.value  || 'Toko Kami'
                this.storePhone  = settingsRes.data?.site_phone?.value || ''
                this.storeLogo   = settingsRes.data?.site_logo?.value  || ''
            } catch { this.invoiceData = order; this.storeName = 'Toko Kami' }
            finally { this.invoiceLoading = false }
        },
        openInvoiceFromDetail() { this.showDetail = false; this.$nextTick(() => this.openInvoice(this.selectedOrder)) },
        printInvoice() { window.print() },
    }
}
</script>

<style scoped>
.or-page { display: flex; flex-direction: column; gap: 20px; }

/* ═══ HERO ═══ */
.or-hero { position: relative; border-radius: 16px; overflow: hidden; background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%); margin-bottom: 4px; }
.or-hero__circle { position: absolute; border-radius: 50%; background: white; pointer-events: none; }
.or-hero__circle--1 { width: 192px; height: 192px; top: -32px; right: -32px; opacity: .10; }
.or-hero__circle--2 { width: 256px; height: 256px; bottom: -40px; right: -96px; opacity: .05; }
.or-hero__circle--3 { width: 80px; height: 80px; top: 16px; right: 128px; opacity: .10; }
.or-hero__inner { position: relative; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; padding: 24px 28px; }
.or-hero__eyebrow  { color: rgba(255,255,255,.65); font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; margin: 0 0 4px; }
.or-hero__title    { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 2px; letter-spacing: -.3px; }
.or-hero__subtitle { font-size: 12px; color: rgba(255,255,255,.65); margin: 0; }
.or-hero__right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.or-hero__live { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; color: #fff; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2); padding: 6px 12px; border-radius: 8px; }
.or-hero__live-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #34d399; animation: or-live-pulse 1.8s ease-in-out infinite; flex-shrink: 0; }
@keyframes or-live-pulse { 0%,100% { box-shadow: 0 0 0 0 rgba(52,211,153,.5); } 50% { box-shadow: 0 0 0 5px rgba(52,211,153,0); } }
.or-hero__refresh-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 13px; background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25); border-radius: 8px; font-size: 12px; font-weight: 600; color: #fff; cursor: pointer; transition: all .15s; }
.or-hero__refresh-btn:hover:not(:disabled) { background: rgba(255,255,255,.22); }
.or-hero__refresh-btn:disabled { opacity: .55; cursor: not-allowed; }
.or-hero__refresh-icon { flex-shrink: 0; }
.or-hero__strip { position: relative; border-top: 1px solid rgba(255,255,255,.12); padding: 14px 28px; display: flex; flex-wrap: wrap; gap: 0; }
.or-hero__stat { display: flex; flex-direction: column; padding: 0 20px; }
.or-hero__stat + .or-hero__stat { border-left: 1px solid rgba(255,255,255,.15); }
.or-hero__stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: rgba(255,255,255,.6); margin: 0 0 2px; }
.or-hero__stat-value { font-size: 22px; font-weight: 700; color: #fff; margin: 0; line-height: 1.2; }
.or-hero__stat-value--amber { color: #fbbf24; }
.or-hero__stat-value--green { color: #34d399; }
.or-hero__stat-value--red   { color: #fca5a5; }
.or-hero__progress { height: 3px; background: rgba(0,0,0,.15); }
.or-hero__progress-bar { height: 100%; background: rgba(255,255,255,.45); transition: width .2s linear; }

/* ═══ BULK BAR ═══ */
.or-bulk-bar { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; padding: 10px 16px; background: #fffbeb; border: 1px solid #fcd34d; border-radius: 12px; }
.or-bulk-bar__left { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #78350f; }
.or-bulk-bar__right { display: flex; align-items: center; gap: 8px; }
.or-bulk-enter-active, .or-bulk-leave-active { transition: all .2s; }
.or-bulk-enter-from, .or-bulk-leave-to { opacity: 0; transform: translateY(-8px); }

/* ═══ CARD / FILTER ═══ */
.or-card { background: #fff; border: 1px solid rgba(229,231,235,.8); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.04); transition: box-shadow .2s, border-color .2s; }
.or-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.07); border-color: rgba(209,213,219,.8); }
.or-filterbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 10px 16px; }
.or-filterbar__search { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 200px; }
.or-filterbar__icon   { color: #9ca3af; flex-shrink: 0; }
.or-filterbar__input  { border: none; outline: none; font-size: 13px; color: #111827; width: 100%; background: transparent; }
.or-filterbar__input::placeholder { color: #9ca3af; }
.or-filterbar__selects { display: flex; gap: 8px; }
.or-select    { padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 12px; color: #374151; background: #f9fafb; cursor: pointer; outline: none; transition: border-color .15s; }
.or-select:hover { border-color: #d1d5db; }
.or-select--sm { padding: 4px 8px; }

/* ═══ TABLE ═══ */
.or-table-wrap { overflow: visible; position: relative; }
.or-table-scroll { overflow-x: auto; }
.or-table { width: 100%; border-collapse: collapse; min-width: 920px; }
.or-th { padding: 10px 16px; font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .06em; text-align: left; background: rgba(249,250,251,.6); border-bottom: 1px solid #f3f4f6; white-space: nowrap; }
.or-th--check { width: 44px; }
.or-th--action { width: 140px; text-align: right; }
.or-td { padding: 11px 16px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.or-td--check  { width: 44px; }
.or-td--action { text-align: right; white-space: nowrap; }
.or-tr { transition: background .15s; }
.or-tr:hover { background: rgba(249,250,251,.6); }
.or-tr--selected { background: #fffbeb !important; }
.or-tr--urgent   { border-left: 3px solid #f59e0b; }

/* Checkbox */
.or-checkbox { display: inline-flex; align-items: center; cursor: pointer; }
.or-checkbox__input { display: none; }
.or-checkbox__box { width: 16px; height: 16px; border-radius: 4px; border: 1.5px solid #d1d5db; background: #fff; display: flex; align-items: center; justify-content: center; transition: all .15s; flex-shrink: 0; }
.or-checkbox__input:checked + .or-checkbox__box { background: #f59e0b; border-color: #f59e0b; }
.or-checkbox__input:checked + .or-checkbox__box::after { content: ''; display: block; width: 9px; height: 5px; border-left: 2px solid #fff; border-bottom: 2px solid #fff; transform: rotate(-45deg) translateY(-1px); }

/* Order ID / Badges */
.or-order-id-wrap { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; }
.or-order-id { font-family: ui-monospace, monospace; font-weight: 700; color: #ED1F24; font-size: 12px; background: rgba(237,31,36,.06); border: 1px solid rgba(237,31,36,.12); padding: 2px 7px; border-radius: 6px; }
.or-badge { display: inline-flex; align-items: center; gap: 3px; padding: 2px 7px; border-radius: 20px; font-size: 10px; font-weight: 700; border: 1px solid transparent; white-space: nowrap; }
.or-badge--urgent { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.or-badge-rev     { display: inline-block; padding: 2px 6px; border-radius: 20px; font-size: 10px; font-weight: 700; background: #ede9fe; color: #7c3aed; vertical-align: middle; }

/* Row cells */
.or-customer { display: flex; flex-direction: column; gap: 3px; }
.or-customer__name  { font-weight: 600; color: #111827; font-size: 13px; }
.or-customer__phone { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: #ED1F24; font-weight: 500; text-decoration: none; }
.or-customer__phone:hover { text-decoration: underline; }
.or-destination { display: flex; flex-direction: column; gap: 2px; }
.or-destination__main { font-size: 12px; font-weight: 600; color: #374151; }
.or-destination__sub  { font-size: 11px; color: #9ca3af; }
.or-courier { display: flex; flex-direction: column; gap: 2px; }
.or-courier__name    { font-size: 12px; font-weight: 700; color: #111827; }
.or-courier__service { font-size: 11px; color: #9ca3af; }
.or-total { font-weight: 700; color: #111827; font-size: 13px; }
.or-date  { font-size: 12px; color: #6b7280; white-space: nowrap; }
.or-duration { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; }
.or-duration--ok       { background: #f0fdf4; color: #166534; }
.or-duration--urgent   { background: #fef3c7; color: #92400e; }
.or-duration--critical { background: #fef2f2; color: #991b1b; animation: or-blink .9s ease-in-out infinite; }
@keyframes or-blink { 0%,100% { opacity:1; } 50% { opacity:.6; } }

/* Loading / Empty */
.or-loading { text-align: center; padding: 40px; color: #9ca3af; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px; }
.or-empty   { padding: 48px; text-align: center; }
.or-empty__inner { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.or-empty__icon  { width: 64px; height: 64px; border-radius: 50%; background: #f0fdf4; display: flex; align-items: center; justify-content: center; color: #16a34a; margin-bottom: 4px; }
.or-empty__inner p    { font-weight: 600; color: #374151; margin: 0; }
.or-empty__inner span { font-size: 13px; color: #9ca3af; }

/* Footer */
.or-footer      { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; padding: 12px 0; }
.or-footer__info { font-size: 13px; color: #9ca3af; }
.or-pagination  { display: flex; align-items: center; gap: 6px; }
.or-page-btn    { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #6b7280; transition: all .15s; }
.or-page-btn:hover:not(:disabled) { background: #f9fafb; border-color: #d1d5db; }
.or-page-btn:disabled { opacity: .4; cursor: not-allowed; }
.or-page-info   { font-size: 13px; color: #6b7280; padding: 0 4px; }

/* ═══ MODAL ═══ */
.or-modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.6); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 24px; }
.or-modal { background: #fff; border-radius: 16px; width: 100%; max-width: 600px; box-shadow: 0 0 0 1px rgba(0,0,0,.06), 0 8px 24px rgba(0,0,0,.12), 0 32px 64px rgba(0,0,0,.16); display: flex; flex-direction: column; max-height: calc(100vh - 48px); overflow: hidden; animation: or-modal-in .2s cubic-bezier(0.34,1.56,0.64,1); }
.or-modal--sm     { max-width: 460px; }
.or-modal--danger { box-shadow: 0 0 0 1px rgba(220,38,38,.15), 0 8px 24px rgba(0,0,0,.12), 0 32px 64px rgba(0,0,0,.16); }
.or-modal--danger::before { content: ''; display: block; height: 3px; background: linear-gradient(90deg,#dc2626,#ef4444); flex-shrink: 0; }
@keyframes or-modal-in { from { opacity:0; transform: scale(.96) translateY(8px); } to { opacity:1; transform: scale(1) translateY(0); } }
.or-modal__header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #f3f4f6; flex-shrink: 0; background: #fafafa; }
.or-modal__header--danger { background: #fef2f2; border-bottom-color: #fecaca; }
.or-modal__header-left { display: flex; align-items: center; gap: 12px; }
.or-modal__icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.or-modal__icon--gray   { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
.or-modal__icon--blue   { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.or-modal__icon--red    { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.or-modal__icon--orange { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
.or-modal__title    { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 2px; letter-spacing: -.15px; }
.or-modal__subtitle { font-size: 12px; color: #9ca3af; margin: 0; font-family: ui-monospace, monospace; }
.or-modal__close { width: 36px; height: 36px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #9ca3af; transition: all .15s; flex-shrink: 0; }
.or-modal__close:hover:not(:disabled) { background: #f3f4f6; color: #374151; border-color: #d1d5db; }
.or-modal__close:disabled { opacity: .4; cursor: not-allowed; }
.or-modal__body { padding: 24px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 20px; }
.or-modal__footer { display: flex; justify-content: flex-end; gap: 8px; padding: 16px 24px; border-top: 1px solid #f3f4f6; flex-shrink: 0; background: #fafafa; }
.or-alert { display: flex; align-items: center; gap: 10px; padding: 12px 24px; background: #fef2f2; border-bottom: 1px solid #fecaca; font-size: 13px; color: #991b1b; font-weight: 500; }

/* Status banner */
.or-status-banner { display: flex; justify-content: space-between; align-items: center; padding: 11px 14px; border-radius: 10px; gap: 8px; }
.or-status-banner__left  { display: flex; align-items: center; gap: 8px; }
.or-status-banner__dot   { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.or-status-banner--amber { background: #fffbeb; border: 1px solid #fde68a; }
.or-status-banner--amber .or-status-banner__dot   { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.2); }
.or-status-banner--amber .or-status-banner__label { color: #92400e; }
.or-status-banner__label { font-size: 13px; font-weight: 700; }
.or-status-banner__date  { font-size: 12px; color: #6b7280; }

/* Duration box */
.or-duration-box { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 10px; font-size: 13px; }
.or-duration-box.or-duration--ok       { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.or-duration-box.or-duration--urgent   { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.or-duration-box.or-duration--critical { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

/* Sections */
.or-section { display: flex; flex-direction: column; gap: 12px; }
.or-section__title { display: flex; align-items: center; gap: 6px; font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .08em; padding-bottom: 8px; border-bottom: 1px solid #f3f4f6; }
.or-section__title svg { color: #9ca3af; }
.or-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.or-detail-item { display: flex; flex-direction: column; gap: 3px; }
.or-detail-label { font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
.or-detail-value { font-size: 13px; color: #111827; font-weight: 500; }
.or-detail-link  { font-size: 13px; color: #ED1F24; font-weight: 600; text-decoration: none; }
.or-detail-link:hover { text-decoration: underline; }
.or-address-box  { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; }
.or-address-box p { margin: 0 0 3px; font-size: 13px; color: #374151; line-height: 1.5; }
.or-address-box p:last-child { margin: 0; }
.or-items { display: flex; flex-direction: column; gap: 6px; }
.or-item  { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 10px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; }
.or-item__info    { display: flex; flex-direction: column; gap: 2px; flex: 1; }
.or-item__name    { font-size: 13px; font-weight: 600; color: #111827; }
.or-item__variant { font-size: 11px; color: #9ca3af; }
.or-item__price   { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.or-item__qty     { font-size: 12px; color: #9ca3af; }
.or-item__subtotal { font-size: 13px; font-weight: 700; color: #111827; }
.or-notes-box    { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; font-size: 13px; color: #374151; line-height: 1.6; }
.or-price-summary { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px 16px; display: flex; flex-direction: column; gap: 8px; }
.or-price-row     { display: flex; justify-content: space-between; font-size: 13px; color: #6b7280; }
.or-price-row--total { padding-top: 10px; border-top: 1px dashed #e5e7eb; font-weight: 700; color: #111827; font-size: 15px; margin-top: 2px; }
.or-price-discount { color: #ED1F24; }

/* Delete confirm */
.or-delete-confirm { display: flex; align-items: flex-start; gap: 16px; padding: 4px 0; }
.or-delete-confirm__icon { width: 52px; height: 52px; border-radius: 12px; background: #fef2f2; border: 1px solid #fecaca; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0; }
.or-delete-confirm__content { display: flex; flex-direction: column; gap: 6px; padding-top: 4px; }
.or-delete-confirm__text { font-size: 14px; color: #111827; line-height: 1.6; margin: 0; }
.or-delete-confirm__sub  { font-size: 12px; color: #9ca3af; margin: 0; line-height: 1.5; }

/* Form */
.or-field  { display: flex; flex-direction: column; gap: 8px; }
.or-label  { font-size: 12px; font-weight: 600; color: #374151; }
.or-label__req { color: #ef4444; }
.or-status-options { display: flex; gap: 10px; }
.or-status-opt { flex: 1; display: flex; align-items: center; gap: 12px; padding: 14px; border: 1.5px solid #e5e7eb; border-radius: 10px; cursor: pointer; transition: all .15s; background: #fff; }
.or-status-opt:hover { border-color: #d1d5db; background: #f9fafb; }
.or-status-opt--success   { border-color: #22c55e; background: #f0fdf4; }
.or-status-opt--cancelled { border-color: #ef4444; background: #fef2f2; }
.or-status-radio { display: none; }
.or-status-opt__icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.or-status-opt__icon--success   { background: #dcfce7; color: #16a34a; }
.or-status-opt__icon--cancelled { background: #fef2f2; color: #dc2626; }
.or-status-opt--success   .or-status-opt__icon--success   { background: #22c55e; color: #fff; }
.or-status-opt--cancelled .or-status-opt__icon--cancelled { background: #ef4444; color: #fff; }
.or-status-opt__text  { display: flex; flex-direction: column; gap: 2px; }
.or-status-opt__label { font-size: 13px; font-weight: 700; color: #111827; }
.or-status-opt__desc  { font-size: 11px; color: #9ca3af; }
.or-status-opt--success   .or-status-opt__label { color: #15803d; }
.or-status-opt--cancelled .or-status-opt__label { color: #b91c1c; }
.or-status-opt--diproses { border-color: #3b82f6; background: #eff6ff; }
.or-status-opt__icon--diproses { background: #dbeafe; color: #2563eb; }
.or-status-opt--diproses .or-status-opt__icon--diproses { background: #3b82f6; color: #fff; }
.or-status-opt--diproses .or-status-opt__label { color: #1d4ed8; }

.or-btn--blue { background: #2563eb; color: #fff; box-shadow: 0 1px 3px rgba(37,99,235,.3); }
.or-btn--blue:hover:not(:disabled) { background: #1d4ed8; }
.or-textarea { padding: 10px 12px; border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 13px; color: #111827; resize: vertical; outline: none; font-family: inherit; width: 100%; box-sizing: border-box; transition: border-color .15s, box-shadow .15s; background: #fff; line-height: 1.6; }
.or-textarea:focus { border-color: #ED1F24; box-shadow: 0 0 0 3px rgba(237,31,36,.08); }
.or-info-box { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; font-size: 13px; color: #1e40af; line-height: 1.5; }
.or-info-box svg { flex-shrink: 0; margin-top: 1px; color: #3b82f6; }
.or-info-box p  { margin: 0; }
.or-char-count  { font-size: 11px; color: #9ca3af; text-align: right; }

/* Buttons */
.or-btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .15s; white-space: nowrap; letter-spacing: -.1px; }
.or-btn--sm { padding: 6px 12px; font-size: 12px; }
.or-btn:disabled { opacity: .55; cursor: not-allowed; }
.or-btn--primary        { background: #ED1F24; color: #fff; box-shadow: 0 1px 3px rgba(237,31,36,.3); }
.or-btn--primary:hover:not(:disabled)  { background: #C81A1E; }
.or-btn--danger         { background: #dc2626; color: #fff; box-shadow: 0 1px 3px rgba(220,38,38,.3); }
.or-btn--danger:hover:not(:disabled)   { background: #b91c1c; }
.or-btn--ghost          { background: transparent; color: #6b7280; border: 1px solid #e5e7eb; }
.or-btn--ghost:hover:not(:disabled)    { background: #f9fafb; color: #374151; border-color: #d1d5db; }
.or-btn--invoice        { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.or-btn--invoice:hover:not(:disabled)  { background: #dbeafe; }
.or-btn--revise         { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
.or-btn--revise:hover:not(:disabled)   { background: #ede9fe; }
.or-btn--wa             { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.or-btn--wa:hover:not(:disabled)       { background: #dcfce7; }
.or-btn--orange         { background: #ea580c; color: #fff; }
.or-btn--orange:hover:not(:disabled)   { background: #c2410c; }

.or-spin { animation: or-spin-anim 1s linear infinite; }
@keyframes or-spin-anim { to { transform: rotate(360deg); } }
.or-modal-enter-active { transition: all .22s cubic-bezier(0.34,1.2,0.64,1); }
.or-modal-leave-active { transition: all .15s ease-in; }
.or-modal-enter-from, .or-modal-leave-to { opacity: 0; transform: scale(.97) translateY(6px); }

@media (max-width: 640px) {
    .or-hero__circle--2 { display: none; }
    .or-hero__stat:nth-child(n+3) { display: none; }
    .or-detail-grid { grid-template-columns: 1fr; }
    .or-status-options { flex-direction: column; }
    .or-modal__body { padding: 16px; }
    .or-modal__footer { padding: 12px 16px; }
    .or-modal__header { padding: 16px; }
}
</style>