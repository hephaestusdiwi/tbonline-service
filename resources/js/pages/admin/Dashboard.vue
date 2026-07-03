<template>
    <AdminLayout title="Dashboard">

        <!-- ───────────────────────── GREETING HERO ───────────────────────── -->
        <div
            class="relative mb-8 rounded-2xl overflow-hidden anim-hero"
            :class="{ 'anim-hero--visible': mounted }"
            style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);"
        >
            <!-- Decorative circles -->
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"></div>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10 hidden sm:block" style="background: white;"></div>

            <div class="relative px-4 sm:px-7 py-5 sm:py-6 flex flex-wrap items-center justify-between gap-3 sm:gap-4">
                <div>
                    <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">{{ greeting }}</p>
                    <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Order Analytics</h1>
                    <p class="text-red-200 text-xs mt-1.5">Ringkasan performa penjualan &amp; pengiriman hari ini</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex items-center gap-2 text-xs font-semibold text-white bg-white/10 border border-white/20 px-2.5 sm:px-3 py-2 rounded-lg backdrop-blur-sm">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Live
                    </div>
                    <div class="text-right hidden sm:block">
                        <p class="text-white font-bold text-sm">{{ currentDate }}</p>
                        <p class="text-red-200 text-xs">{{ currentTime }}</p>
                    </div>
                </div>
            </div>

            <!-- Revenue strip inside hero -->
            <div class="relative border-t border-white/10 px-4 sm:px-7 py-4 flex flex-wrap items-center gap-4 sm:gap-6">
                <div>
                    <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest mb-0.5">Total Revenue</p>
                    <p class="text-white text-xl sm:text-2xl font-bold tabular-nums">
                        Rp {{ displayRevenue }}
                    </p>
                    <p class="text-red-200 text-xs mt-0.5">Dari order sukses</p>
                </div>
                <div class="flex-1 min-w-[140px] sm:min-w-[180px]">
                    <div v-if="loading" class="h-14 bg-white/10 rounded-xl animate-pulse"></div>
                    <apexchart v-else type="area" height="56" :options="sparklineOptions" :series="revenueLineSeries" />
                </div>
            </div>
        </div>

        <!-- ───────────────────────── KPI CARDS ───────────────────────── -->
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-2.5 sm:gap-3 mb-6">
            <template v-if="loading">
                <div v-for="i in 4" :key="i" class="bg-white rounded-xl p-4 sm:p-5 animate-pulse h-24 sm:h-28 border border-gray-200/80 shadow-sm"></div>
            </template>
            <template v-else>
                <div
                    v-for="(card, idx) in summaryCards"
                    :key="card.label"
                    class="group bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md hover:border-gray-300/80 transition-all duration-300 overflow-hidden relative anim-card"
                    :class="{ 'anim-card--visible': mounted }"
                    :style="{ transitionDelay: (0.05 + idx * 0.07) + 's' }"
                >
                    <div class="absolute top-0 left-0 right-0 h-0.5 rounded-t-xl transition-opacity duration-300" :style="{ background: card.accentColor }"></div>
                    <div class="p-3.5 sm:p-5">
                        <div class="flex items-start justify-between mb-2.5 sm:mb-3">
                            <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-gray-400 leading-tight pr-1.5 sm:pr-2">{{ card.label }}</span>
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center shrink-0 border transition-transform duration-300 group-hover:scale-110"
                                :style="{ background: card.iconBg, borderColor: card.accentColor + '25' }">
                                <font-awesome-icon :icon="['fas', card.icon]" :style="{ color: card.accentColor }" class="text-xs sm:text-sm" />
                            </div>
                        </div>
                        <p class="text-base sm:text-lg font-bold text-gray-900 leading-tight tabular-nums truncate">{{ card.value }}</p>
                        <p v-if="card.sub" class="text-[11px] sm:text-xs mt-1 sm:mt-1.5 truncate" :class="card.subColor">{{ card.sub }}</p>
                    </div>
                </div>
            </template>
        </div>

        <!-- ───────────────────────── ROW 1: Revenue Chart + Success Rate ───────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 mb-4">

            <!-- Revenue Line Chart -->
            <div
                class="lg:col-span-2 min-w-0 bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden anim-section"
                :class="{ 'anim-section--visible': mounted }"
                style="transition-delay: 0.28s"
            >
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Tren Revenue Bulanan</h3>
                        <p class="text-xs text-gray-400 mt-0.5">6 bulan terakhir</p>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <div v-if="loading" class="h-44 bg-gray-50 rounded-xl animate-pulse"></div>
                    <apexchart v-else type="area" height="180" :options="revenueLineOptions" :series="revenueLineSeries" />
                </div>
            </div>

            <!-- Success Rate Radial -->
            <div
                class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden anim-section"
                :class="{ 'anim-section--visible': mounted }"
                style="transition-delay: 0.35s"
            >
                <div class="px-4 sm:px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Success Rate</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Rasio order berhasil</p>
                </div>
                <div class="p-4 sm:p-5 flex flex-col items-center">
                    <div v-if="loading" class="w-36 h-36 rounded-full border-8 border-gray-100 animate-pulse my-4"></div>
                    <apexchart v-else type="radialBar" height="200" :options="radialOptions" :series="radialSeries" />
                    <p class="text-xs text-gray-400 -mt-2 text-center">
                        vs total <span class="font-bold text-gray-600">{{ Number(stats.summary.total_orders || 0).toLocaleString('id-ID') }}</span> order
                    </p>
                    <div class="w-full mt-4 grid grid-cols-3 gap-1.5 sm:gap-2">
                        <div class="text-center p-1.5 sm:p-2 bg-emerald-50 rounded-lg border border-emerald-100 transition-transform duration-200 hover:scale-105">
                            <p class="text-[9px] sm:text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Sukses</p>
                            <p class="text-xs sm:text-sm font-bold text-emerald-700 tabular-nums">{{ (stats.summary.total_success || 0).toLocaleString('id-ID') }}</p>
                        </div>
                        <div class="text-center p-1.5 sm:p-2 bg-amber-50 rounded-lg border border-amber-100 transition-transform duration-200 hover:scale-105">
                            <p class="text-[9px] sm:text-[10px] font-bold text-amber-500 uppercase tracking-wider">Pending</p>
                            <p class="text-xs sm:text-sm font-bold text-amber-700 tabular-nums">{{ (stats.summary.total_pending || 0).toLocaleString('id-ID') }}</p>
                        </div>
                        <div class="text-center p-1.5 sm:p-2 bg-red-50 rounded-lg border border-red-100 transition-transform duration-200 hover:scale-105">
                            <p class="text-[9px] sm:text-[10px] font-bold text-red-400 uppercase tracking-wider">Batal</p>
                            <p class="text-xs sm:text-sm font-bold text-red-600 tabular-nums">{{ (stats.summary.total_cancelled || 0).toLocaleString('id-ID') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── ROW 2: Monthly Trend + Top Couriers ───────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 mb-4">

            <!-- Monthly Trend Bar -->
            <div
                class="lg:col-span-2 min-w-0 bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden anim-section"
                :class="{ 'anim-section--visible': mounted }"
                style="transition-delay: 0.42s"
            >
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Tren Order Bulanan</h3>
                    <p class="text-xs text-gray-400 mt-0.5">6 bulan terakhir · stacked by status</p>
                </div>
                <div class="p-4 sm:p-6">
                    <div v-if="loading" class="h-56 bg-gray-50 rounded-xl animate-pulse"></div>
                    <apexchart v-else type="bar" height="240" :options="trendOptions" :series="trendSeries" />
                </div>
            </div>

            <!-- Top Couriers -->
            <div
                class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden anim-section"
                :class="{ 'anim-section--visible': mounted }"
                style="transition-delay: 0.49s"
            >
                <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Top Ekspedisi</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Berdasarkan jumlah order</p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#ED1F24]/8 text-[#ED1F24] border border-[#ED1F24]/15 uppercase tracking-wider shrink-0">
                        Top {{ stats.top_couriers?.length || 0 }}
                    </span>
                </div>
                <div class="p-4 sm:p-5">
                    <div v-if="loading" class="space-y-4">
                        <div v-for="i in 5" :key="i" class="h-8 bg-gray-100 rounded-lg animate-pulse"></div>
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="(courier, i) in stats.top_couriers"
                            :key="courier.courier"
                            class="courier-row"
                            :class="{ 'courier-row--visible': barsVisible }"
                            :style="{ transitionDelay: (i * 0.08) + 's' }"
                        >
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] font-bold shrink-0"
                                    :style="i === 0 ? 'background:#ED1F24;color:white' : i === 1 ? 'background:#f3f4f6;color:#374151' : 'background:#f9fafb;color:#9ca3af'"
                                >#{{ i + 1 }}</span>
                                <span class="text-sm font-semibold text-gray-700 flex-1 truncate">{{ courier.courier }}</span>
                                <span class="text-xs font-bold text-gray-600 tabular-nums shrink-0">{{ courier.total.toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full bar-fill"
                                    :style="{
                                        width: barsVisible ? courierBarWidth(courier.total) + '%' : '0%',
                                        background: courierColors[i],
                                        transitionDelay: (0.1 + i * 0.1) + 's'
                                    }"
                                ></div>
                            </div>
                        </div>
                        <div v-if="!stats.top_couriers?.length" class="text-center text-gray-400 text-sm py-6">
                            Belum ada data ekspedisi
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── ROW 3: TOP PRODUCTS + STATUS DONUT ───────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 mb-4">

            <!-- Top Products -->
            <div
                class="lg:col-span-2 min-w-0 bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden anim-section"
                :class="{ 'anim-section--visible': mounted }"
                style="transition-delay: 0.56s"
            >
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Produk Terlaris</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Berdasarkan qty terjual (order sukses)</p>
                    </div>
                    <a href="/admin/products" class="group flex items-center gap-1 text-xs font-semibold text-[#ED1F24] hover:text-[#C81A1E] transition-colors shrink-0">
                        Lihat semua
                        <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                <div v-if="loading" class="p-4 sm:p-6 space-y-3">
                    <div v-for="i in 5" :key="i" class="h-10 bg-gray-50 rounded-xl animate-pulse"></div>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[400px]">
                        <thead>
                            <tr class="bg-gray-50/60 border-b border-gray-100">
                                <th class="px-4 sm:px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Produk</th>
                                <th class="px-4 sm:px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Qty Terjual</th>
                                <th class="px-4 sm:px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400 w-36 hidden lg:table-cell">Proporsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="(product, i) in stats.top_products"
                                :key="product.product"
                                class="hover:bg-gray-50/60 transition-colors duration-150 product-row"
                                :class="{ 'product-row--visible': barsVisible }"
                                :style="{ transitionDelay: (i * 0.06) + 's' }"
                            >
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
                                            :style="i === 0 ? 'background:#ED1F24;color:white' : 'background:#f3f4f6;color:#6b7280'"
                                        >{{ i + 1 }}</div>
                                        <span class="text-sm text-gray-700 font-medium truncate max-w-[200px]">{{ product.product }}</span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-right font-bold text-gray-800 tabular-nums">
                                    {{ Number(product.total_sold).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 hidden lg:table-cell">
                                    <div class="flex items-center gap-2 justify-end">
                                        <div class="w-20 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div
                                                class="h-full rounded-full bg-[#ED1F24] bar-fill"
                                                :style="{
                                                    width: barsVisible ? productBarWidth(product.total_sold) + '%' : '0%',
                                                    transitionDelay: (0.15 + i * 0.08) + 's'
                                                }"
                                            ></div>
                                        </div>
                                        <span class="text-xs text-gray-400 w-8 text-right tabular-nums">{{ productBarWidth(product.total_sold) }}%</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!stats.top_products?.length">
                                <td colspan="3" class="px-4 sm:px-6 py-10 text-center text-gray-400 text-sm">Belum ada data produk</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order Status Donut -->
            <div
                class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden anim-section"
                :class="{ 'anim-section--visible': mounted }"
                style="transition-delay: 0.63s"
            >
                <div class="px-4 sm:px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Rasio Status Order</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Success · Pending · Cancelled</p>
                </div>
                <div class="p-4 sm:p-5">
                    <div v-if="loading" class="flex items-center justify-center py-8">
                        <div class="w-32 h-32 rounded-full border-8 border-gray-100 animate-pulse"></div>
                    </div>
                    <apexchart v-else type="donut" height="240" :options="donutOptions" :series="donutSeries" />
                </div>
            </div>
        </div>

        <!-- ───────────────────────── ROW 4: ALERTS ───────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 mb-4">

            <!-- Low Stock -->
            <div
                class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden anim-section"
                :class="{ 'anim-section--visible': mounted }"
                style="transition-delay: 0.70s"
            >
                <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse shrink-0"></div>
                    <h3 class="text-sm font-bold text-gray-800 flex-1">Stok Hampir Habis</h3>
                    <span class="text-xs font-bold bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full border border-amber-100 shrink-0">
                        {{ alerts.low_stock?.length || 0 }}
                    </span>
                </div>
                <div class="p-4 sm:p-5">
                    <div v-if="loading" class="space-y-3">
                        <div v-for="i in 3" :key="i" class="h-10 bg-gray-100 rounded-lg animate-pulse"></div>
                    </div>
                    <div v-else-if="alerts.low_stock?.length">
                        <div class="space-y-2 min-h-[100px]">
                            <div
                                v-for="item in pagedLowStock"
                                :key="item.id"
                                class="flex items-center justify-between bg-amber-50 border border-amber-100 rounded-lg px-3 py-2.5 transition-all duration-200 hover:shadow-sm hover:border-amber-200"
                            >
                                <span class="text-sm text-gray-700 font-medium truncate max-w-[65%]">{{ item.name }}</span>
                                <span class="text-xs font-bold text-amber-600 shrink-0">Sisa {{ item.stock }}</span>
                            </div>
                        </div>
                        <div v-if="alerts.low_stock.length > 3" class="flex items-center justify-between mt-3">
                            <button @click="lowStockPage--" :disabled="lowStockPage === 0"
                                class="text-xs px-2.5 py-1 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-all">← Prev</button>
                            <span class="text-xs text-gray-400 font-medium">{{ lowStockPage + 1 }} / {{ lowStockTotalPages }}</span>
                            <button @click="lowStockPage++" :disabled="lowStockPage >= lowStockTotalPages - 1"
                                class="text-xs px-2.5 py-1 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-all">Next →</button>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center py-6 gap-2">
                        <span class="text-2xl">✓</span>
                        <span class="text-gray-400 text-sm font-medium">Semua stok aman</span>
                    </div>
                </div>
            </div>

            <!-- Pending Orders -->
            <div
                class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden anim-section"
                :class="{ 'anim-section--visible': mounted }"
                style="transition-delay: 0.77s"
            >
                <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-blue-400 shrink-0"></div>
                    <h3 class="text-sm font-bold text-gray-800 flex-1">Order Pending</h3>
                    <span class="text-xs font-bold bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full border border-blue-100 shrink-0">
                        {{ alerts.pending_orders?.length || 0 }}
                    </span>
                </div>
                <div class="p-4 sm:p-5">
                    <div v-if="loading" class="space-y-3">
                        <div v-for="i in 3" :key="i" class="h-10 bg-gray-100 rounded-lg animate-pulse"></div>
                    </div>
                    <div v-else-if="alerts.pending_orders?.length" class="space-y-2">
                        <div
                            v-for="order in alerts.pending_orders"
                            :key="order.id"
                            class="flex items-center justify-between bg-blue-50 border border-blue-100 rounded-lg px-3 py-2.5 transition-all duration-200 hover:shadow-sm hover:border-blue-200"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-700 truncate">#{{ order.invoice_number }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ order.customer_name }}</p>
                            </div>
                            <span class="text-xs font-bold text-blue-600 shrink-0 bg-blue-100 px-2 py-0.5 rounded-md">{{ order.age }}</span>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center py-6 gap-2">
                        <span class="text-2xl"></span>
                        <span class="text-gray-400 text-sm font-medium">Tidak ada order pending</span>
                    </div>
                </div>
            </div>

            <!-- Promo Expiring -->
            <div
                class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden anim-section"
                :class="{ 'anim-section--visible': mounted }"
                style="transition-delay: 0.84s"
            >
                <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-[#ED1F24] shrink-0"></div>
                    <h3 class="text-sm font-bold text-gray-800 flex-1">Promo Hampir Expired</h3>
                    <span class="text-xs font-bold text-[#ED1F24] px-2 py-0.5 rounded-full border border-red-100 shrink-0" style="background: rgba(237,31,36,0.06);">
                        {{ alerts.expiring_promos?.length || 0 }}
                    </span>
                </div>
                <div class="p-4 sm:p-5">
                    <div v-if="loading" class="space-y-3">
                        <div v-for="i in 3" :key="i" class="h-10 bg-gray-100 rounded-lg animate-pulse"></div>
                    </div>
                    <div v-else-if="alerts.expiring_promos?.length" class="space-y-2">
                        <div
                            v-for="promo in alerts.expiring_promos"
                            :key="promo.id"
                            class="flex items-center justify-between rounded-lg px-3 py-2.5 border border-red-100 transition-all duration-200 hover:shadow-sm hover:border-red-200"
                            style="background: rgba(237,31,36,0.04);"
                        >
                            <span class="text-sm text-gray-700 font-medium truncate max-w-[65%]">{{ promo.name }}</span>
                            <span class="text-xs font-bold text-[#ED1F24] shrink-0 bg-red-50 border border-red-100 px-2 py-0.5 rounded-md">{{ promo.days_left }}h lagi</span>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center py-6 gap-2">
                        <span class="text-2xl">🏷️</span>
                        <span class="text-gray-400 text-sm font-medium">Tidak ada promo akan berakhir</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── RECENT ORDERS ───────────────────────── -->
        <div
            class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden mb-4 anim-section"
            :class="{ 'anim-section--visible': mounted }"
            style="transition-delay: 0.91s"
        >
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Order Terbaru</h3>
                    <p class="text-xs text-gray-400 mt-0.5">10 transaksi terakhir</p>
                </div>
                <a href="/admin/orders" class="group flex items-center gap-1 text-xs font-semibold text-[#ED1F24] hover:text-[#C81A1E] transition-colors shrink-0">
                    Lihat semua
                    <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div v-if="loading" class="p-4 sm:p-6 space-y-3">
                <div v-for="i in 5" :key="i" class="h-12 bg-gray-50 rounded-xl animate-pulse"></div>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm min-w-[560px]">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="px-4 sm:px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Order</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Pelanggan</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 hidden sm:table-cell">Tanggal</th>
                            <th class="px-4 sm:px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Total</th>
                            <th class="px-4 sm:px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="(order, i) in recentOrders"
                            :key="order.id"
                            class="hover:bg-gray-50/60 transition-colors duration-150 order-row"
                            :class="{ 'order-row--visible': barsVisible }"
                            :style="{ transitionDelay: (i * 0.04) + 's' }"
                        >
                            <td class="px-4 sm:px-6 py-3.5">
                                <span class="font-mono font-bold text-gray-700 text-xs bg-gray-100 px-2 py-0.5 rounded-md">#{{ order.invoice_number }}</span>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-[#ED1F24]/10 border border-[#ED1F24]/15 flex items-center justify-center text-xs font-bold text-[#ED1F24] shrink-0">
                                        {{ (order.customer_name || '?')[0].toUpperCase() }}
                                    </div>
                                    <span class="text-sm text-gray-700 font-medium">{{ order.customer_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5 hidden sm:table-cell">
                                <span class="text-xs text-gray-400 tabular-nums">{{ formatDate(order.created_at) }}</span>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5 text-right font-bold text-gray-900 tabular-nums">
                                Rp {{ Number(order.total_price).toLocaleString('id-ID') }}
                            </td>
                            <td class="px-4 sm:px-6 py-3.5 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border" :class="statusClass(order.status)">
                                    {{ order.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!recentOrders?.length">
                            <td colspan="5" class="px-4 sm:px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span class="text-gray-400 text-sm font-medium">Belum ada order</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ───────────────────────── ERROR ───────────────────────── -->
        <transition name="alert-slide">
            <div v-if="error" class="rounded-xl p-4 text-sm flex items-center gap-3 bg-red-50 border border-red-200">
                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <span class="text-red-600 font-medium flex-1">{{ error }}</span>
                <button @click="fetchAll" class="text-xs font-bold text-[#ED1F24] shrink-0 px-3 py-1 rounded-lg border border-red-200 hover:bg-red-50 transition-colors active:scale-95">
                    Coba lagi
                </button>
            </div>
        </transition>

    </AdminLayout>
</template>

<script>
import AdminLayout from '../../components/admin/AdminLayout.vue'
import VueApexCharts from 'vue3-apexcharts'
import axios from '../../axios.js'

const CHART_BASE = {
    chart: {
        background: 'transparent',
        fontFamily: 'inherit',
        toolbar: { show: false },
        animations: { enabled: true, easing: 'easeinout', speed: 600 },
    },
    grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
    tooltip: { theme: 'light' },
}

export default {
    name: 'Dashboard',
    components: { AdminLayout, apexchart: VueApexCharts },

    data() {
        return {
            userName: JSON.parse(localStorage.getItem('user') || '{}')?.name || 'Admin',
            loading: true,
            error: null,
            lowStockPage: 0,
            currentTime: '',
            currentDate: '',
            timeInterval: null,
            mounted: false,
            barsVisible: false,

            // Animated revenue counter
            displayRevenueValue: 0,
            revenueAnimFrame: null,

            stats: {
                summary: {},
                order_status_ratio: {},
                top_couriers: [],
                top_products: [],
                monthly_trend: [],
            },

            alerts: {
                low_stock: [],
                pending_orders: [],
                expiring_promos: [],
            },

            recentOrders: [],
            courierColors: ['#ED1F24', '#3B82F6', '#22C55E', '#F59E0B', '#8B5CF6'],
        }
    },

    computed: {
        greeting() {
            const hour = new Date().getHours()
            const name = JSON.parse(localStorage.getItem('user') || '{}')?.name || 'Admin'
            let salam = 'Selamat pagi'
            if (hour >= 11 && hour < 15) salam = 'Selamat siang'
            else if (hour >= 15 && hour < 19) salam = 'Selamat sore'
            else if (hour >= 19 || hour < 4)  salam = 'Selamat malam'
            return `${salam}, ${name} 👋`
        },

        displayRevenue() {
            return Number(this.displayRevenueValue).toLocaleString('id-ID')
        },

        summaryCards() {
            const s = this.stats.summary
            return [
                {
                    label: 'Total Revenue',
                    value: 'Rp ' + Number(s.total_revenue || 0).toLocaleString('id-ID'),
                    icon: 'money-bill-wave', iconBg: 'rgba(237,31,36,0.07)', accentColor: '#ED1F24',
                    sub: 'Dari order sukses', subColor: 'text-[#ED1F24] font-semibold',
                },
                {
                    label: 'Total Order',
                    value: (s.total_orders || 0).toLocaleString('id-ID'),
                    icon: 'box-open', iconBg: 'rgba(59,130,246,0.07)', accentColor: '#3B82F6',
                    sub: `+${s.total_orders ? Math.round((s.total_success / s.total_orders) * 100) : 0}% success rate`,
                    subColor: 'text-emerald-500 font-semibold',
                },
                {
                    label: 'Order Pending',
                    value: (s.total_pending || 0).toLocaleString('id-ID'),
                    icon: 'clock', iconBg: 'rgba(245,158,11,0.07)', accentColor: '#F59E0B',
                    sub: 'Menunggu proses', subColor: 'text-amber-500',
                },
                {
                    label: 'Produk Aktif',
                    value: (s.active_products || 0).toLocaleString('id-ID'),
                    icon: 'tag', iconBg: 'rgba(34,197,94,0.07)', accentColor: '#22C55E',
                    sub: `${s.low_stock_count || 0} stok kritis`, subColor: 'text-red-400',
                },
            ]
        },

        pagedLowStock() {
            const start = this.lowStockPage * 3
            return (this.alerts.low_stock || []).slice(start, start + 3)
        },
        lowStockTotalPages() {
            return Math.ceil((this.alerts.low_stock?.length || 0) / 3)
        },

        sparklineOptions() {
            return {
                chart: {
                    background: 'transparent',
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    sparkline: { enabled: true },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 },
                },
                colors: ['rgba(255,255,255,0.9)'],
                fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0, stops: [0, 100] } },
                stroke: { curve: 'smooth', width: 2 },
                tooltip: { enabled: false },
                grid: { show: false },
                xaxis: { labels: { show: false }, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { show: false },
                dataLabels: { enabled: false },
            }
        },

        revenueLineSeries() {
            return [{
                name: 'Revenue',
                data: this.stats.monthly_trend.map(m => m.revenue || m.success || 0),
            }]
        },
        revenueLineOptions() {
            const months = this.stats.monthly_trend.map(m => {
                const [y, mo] = m.month.split('-')
                return new Date(y, mo - 1).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' })
            })
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'area' },
                colors: ['#ED1F24'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.15, opacityTo: 0.01, stops: [0, 100] } },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: {
                    categories: months,
                    labels: { style: { fontSize: '10px', colors: '#9ca3af' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: {
                    labels: {
                        style: { fontSize: '10px', colors: '#9ca3af' },
                        formatter: v => 'Rp ' + Number(v).toLocaleString('id-ID'),
                    },
                },
                dataLabels: { enabled: false },
                tooltip: { theme: 'light', y: { formatter: v => 'Rp ' + Number(v).toLocaleString('id-ID') } },
            }
        },

        radialSeries() {
            const s = this.stats.summary
            const rate = s.total_orders ? Math.round((s.total_success / s.total_orders) * 100) : 0
            return [rate]
        },
        radialOptions() {
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'radialBar' },
                colors: ['#ED1F24'],
                plotOptions: {
                    radialBar: {
                        hollow: { size: '62%' },
                        track: { background: '#f3f4f6', strokeWidth: '100%' },
                        dataLabels: {
                            name: { fontSize: '10px', color: '#9ca3af', offsetY: 20 },
                            value: { fontSize: '26px', fontWeight: 700, color: '#111827', offsetY: -10, formatter: v => v + '%' },
                        },
                    },
                },
                labels: ['of total'],
                tooltip: { enabled: false },
            }
        },

        donutSeries() {
            const r = this.stats.order_status_ratio
            return [r.success || 0, r.pending || 0, r.cancelled || 0]
        },
        donutOptions() {
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'donut' },
                labels: ['Success', 'Pending', 'Cancelled'],
                colors: ['#22C55E', '#F59E0B', '#EF4444'],
                dataLabels: { enabled: false },
                stroke: { width: 0 },
                legend: {
                    position: 'bottom', fontSize: '11px',
                    labels: { colors: '#6b7280' }, markers: { radius: 4 },
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                total: {
                                    show: true, label: 'Total', fontSize: '11px', color: '#9ca3af',
                                    formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('id-ID'),
                                },
                                value: { color: '#111827', fontSize: '18px', fontWeight: 700 },
                            },
                        },
                    },
                },
                tooltip: { theme: 'light', y: { formatter: v => v.toLocaleString('id-ID') + ' order' } },
            }
        },

        trendSeries() {
            return [
                { name: 'Success',   data: this.stats.monthly_trend.map(m => m.success   || 0) },
                { name: 'Pending',   data: this.stats.monthly_trend.map(m => m.pending   || 0) },
                { name: 'Cancelled', data: this.stats.monthly_trend.map(m => m.cancelled || 0) },
            ]
        },
        trendOptions() {
            const months = this.stats.monthly_trend.map(m => {
                const [y, mo] = m.month.split('-')
                return new Date(y, mo - 1).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' })
            })
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'bar', stacked: true },
                colors: ['#22C55E', '#F59E0B', '#EF4444'],
                xaxis: {
                    categories: months,
                    labels: { style: { fontSize: '10px', colors: '#9ca3af' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: {
                    labels: { style: { fontSize: '10px', colors: '#9ca3af' }, formatter: v => v.toLocaleString('id-ID') },
                },
                dataLabels: { enabled: false },
                plotOptions: { bar: { borderRadius: 3, columnWidth: '48%' } },
                legend: { position: 'top', fontSize: '12px', labels: { colors: '#6b7280' }, markers: { radius: 4 } },
                tooltip: { theme: 'light', y: { formatter: v => v.toLocaleString('id-ID') + ' order' } },
            }
        },
    },

    methods: {
        async fetchAll() {
            this.loading = true
            this.error = null
            try {
                const [statsRes, ordersRes] = await Promise.all([
                    axios.get('/orders/stats'),
                    axios.get('/orders?per_page=10'),
                ])

                this.stats        = statsRes.data
                this.alerts       = statsRes.data.alerts ?? { low_stock: [], pending_orders: [], expiring_promos: [] }
                this.recentOrders = ordersRes.data.data ?? []

                const trend = statsRes.data.monthly_trend || []
                const now = new Date()
                const last6 = Array.from({ length: 6 }, (_, i) => {
                    const d = new Date(now.getFullYear(), now.getMonth() - 5 + i, 1)
                    const y = d.getFullYear()
                    const m = String(d.getMonth() + 1).padStart(2, '0')
                    return `${y}-${m}`
                })

                this.stats.monthly_trend = last6.map(month => {
                    const found = trend.find(t => t.month === month)
                    return found || { month, revenue: 0, success: 0, pending: 0, cancelled: 0 }
                })

                // Animate revenue counter after data loads
                this.$nextTick(() => {
                    this.animateRevenue(statsRes.data.summary?.total_revenue || 0)
                    // Trigger bar/row entrance animations
                    setTimeout(() => { this.barsVisible = true }, 200)
                })

            } catch (err) {
                this.error = 'Gagal memuat data dashboard. ' + (err.response?.data?.message || err.message)
            } finally {
                this.loading = false
            }
        },

        animateRevenue(target) {
            if (this.revenueAnimFrame) cancelAnimationFrame(this.revenueAnimFrame)
            const start = 0
            const duration = 1200
            const startTime = performance.now()
            const easeOut = t => 1 - Math.pow(1 - t, 3)

            const step = (now) => {
                const elapsed = now - startTime
                const progress = Math.min(elapsed / duration, 1)
                this.displayRevenueValue = Math.round(start + (target - start) * easeOut(progress))
                if (progress < 1) {
                    this.revenueAnimFrame = requestAnimationFrame(step)
                } else {
                    this.displayRevenueValue = target
                }
            }
            this.revenueAnimFrame = requestAnimationFrame(step)
        },

        updateClock() {
            const now = new Date()
            this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
            this.currentDate = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
        },

        courierBarWidth(total) {
            const max = Math.max(...(this.stats.top_couriers || []).map(c => c.total), 1)
            return Math.round((total / max) * 100)
        },

        productBarWidth(sold) {
            const max = Math.max(...(this.stats.top_products || []).map(p => p.total_sold), 1)
            return Math.round((sold / max) * 100)
        },

        statusClass(status) {
            const map = {
                success:   'bg-emerald-50 text-emerald-600 border-emerald-100',
                pending:   'bg-amber-50 text-amber-600 border-amber-100',
                cancelled: 'bg-red-50 text-red-500 border-red-100',
                failed:    'bg-red-50 text-red-500 border-red-100',
            }
            return map[status?.toLowerCase()] ?? 'bg-gray-100 text-gray-500 border-gray-200'
        },

        formatDate(dateStr) {
            if (!dateStr) return '-'
            return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        },
    },

    mounted() {
        document.title = 'Dashboard - Two Brothers Vape System'
        this.updateClock()
        this.timeInterval = setInterval(this.updateClock, 1000)
        this.fetchAll()

        // Trigger entrance animations
        requestAnimationFrame(() => {
            setTimeout(() => { this.mounted = true }, 60)
        })
    },

    beforeUnmount() {
        if (this.timeInterval) clearInterval(this.timeInterval)
        if (this.revenueAnimFrame) cancelAnimationFrame(this.revenueAnimFrame)
    },
}
</script>

<style scoped>
/* ─── Hero entrance ─── */
.anim-hero {
    opacity: 0;
    transform: translateY(-10px) scale(0.995);
    transition: opacity 0.6s cubic-bezier(0.22,1,0.36,1),
                transform 0.6s cubic-bezier(0.22,1,0.36,1);
}
.anim-hero--visible {
    opacity: 1;
    transform: translateY(0) scale(1);
}

/* ─── KPI card entrance ─── */
.anim-card {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.45s cubic-bezier(0.22,1,0.36,1),
                transform 0.45s cubic-bezier(0.22,1,0.36,1),
                box-shadow 0.2s,
                border-color 0.2s;
}
.anim-card--visible {
    opacity: 1;
    transform: translateY(0);
}

/* ─── Section panels entrance ─── */
.anim-section {
    opacity: 0;
    transform: translateY(18px);
    transition: opacity 0.5s cubic-bezier(0.22,1,0.36,1),
                transform 0.5s cubic-bezier(0.22,1,0.36,1);
}
.anim-section--visible {
    opacity: 1;
    transform: translateY(0);
}

/* ─── Animated progress bars ─── */
.bar-fill {
    transition: width 0.9s cubic-bezier(0.22,1,0.36,1);
}

/* ─── Courier row fade-in ─── */
.courier-row {
    opacity: 0;
    transform: translateX(-8px);
    transition: opacity 0.4s ease, transform 0.4s cubic-bezier(0.22,1,0.36,1);
}
.courier-row--visible {
    opacity: 1;
    transform: translateX(0);
}

/* ─── Product & order row fade-in ─── */
.product-row,
.order-row {
    opacity: 0;
    transform: translateY(6px);
    transition: opacity 0.35s ease, transform 0.35s cubic-bezier(0.22,1,0.36,1), background-color 0.15s;
}
.product-row--visible,
.order-row--visible {
    opacity: 1;
    transform: translateY(0);
}

/* ─── Alert slide transition ─── */
.alert-slide-enter-active { transition: all 0.35s cubic-bezier(0.22,1,0.36,1); }
.alert-slide-leave-active { transition: all 0.2s ease-in; }
.alert-slide-enter-from   { opacity: 0; transform: translateY(-8px); }
.alert-slide-leave-to     { opacity: 0; transform: translateY(-4px); }
</style>