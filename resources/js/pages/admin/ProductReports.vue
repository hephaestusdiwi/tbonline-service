<template>
    <AdminLayout title="Product Reports">

        <!-- ───────────────────────── HEADER ───────────────────────── -->
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Product Reports</h1>
                        <span class="text-[10px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 border border-gray-200">Analytics</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Laporan performa produk mendalam
                        <span class="text-gray-300 mx-1">·</span>
                        <span class="font-medium text-gray-500">{{ periodInfo.label }}</span>
                        <span v-if="periodInfo.from" class="text-gray-400">
                            ({{ fmtDate(periodInfo.from) }} – {{ fmtDate(periodInfo.to) }})
                        </span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Export CSV -->
                <button
                    @click="exportCsv"
                    class="group flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg border border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-700 transition-all duration-150"
                >
                    <svg class="w-3.5 h-3.5 group-hover:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    CSV
                </button>
                <!-- Export Excel -->
                <button
                    @click="exportExcel"
                    class="group flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:border-emerald-300 hover:bg-emerald-50 transition-all duration-150"
                >
                    <svg class="w-3.5 h-3.5 group-hover:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Excel
                </button>
                <!-- Divider -->
                <div class="w-px h-6 bg-gray-200"></div>
                <!-- Refresh -->
                <button
                    @click="fetchReport"
                    :class="['group flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg border transition-all duration-150',
                        loading ? 'border-gray-100 text-gray-300 cursor-not-allowed bg-gray-50' : 'border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-700']"
                    :disabled="loading"
                >
                    <svg :class="['w-3.5 h-3.5 transition-transform', loading ? 'animate-spin' : 'group-hover:rotate-180 duration-300']" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <!-- ───────────────────────── FILTER BAR ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm mb-6 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Filter & Periode</span>
            </div>
            <div class="p-5 flex flex-wrap gap-5 items-end">
                <!-- Period Presets -->
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Periode</label>
                    <div class="flex flex-wrap gap-1">
                        <button
                            v-for="p in periodPresets"
                            :key="p.value"
                            @click="selectPeriod(p.value)"
                            :class="['text-xs px-3 py-1.5 rounded-lg font-semibold border transition-all duration-150',
                                filters.period === p.value
                                    ? 'bg-[#ED1F24] text-white border-[#ED1F24] shadow-sm shadow-red-200'
                                    : 'border-gray-200 text-gray-500 hover:border-gray-300 hover:text-gray-700 bg-white']"
                        >{{ p.label }}</button>
                    </div>
                </div>

                <!-- Custom Date -->
                <div v-if="filters.period === 'custom'" class="flex items-end gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Dari</label>
                        <input
                            v-model="filters.date_from"
                            type="date"
                            class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all bg-white"
                        />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sampai</label>
                        <input
                            v-model="filters.date_to"
                            type="date"
                            class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all bg-white"
                        />
                    </div>
                </div>

                <!-- Separator -->
                <div class="w-px h-10 bg-gray-200 self-end hidden sm:block"></div>

                <!-- Category Filter -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kategori</label>
                    <div class="relative">
                        <select
                            v-model="filters.category"
                            class="text-sm border border-gray-200 rounded-lg pl-3 pr-8 py-1.5 text-gray-700 bg-white focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all appearance-none cursor-pointer"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="c in categoryList" :key="c" :value="c">{{ c }}</option>
                        </select>
                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status Order</label>
                    <div class="relative">
                        <select
                            v-model="filters.status"
                            class="text-sm border border-gray-200 rounded-lg pl-3 pr-8 py-1.5 text-gray-700 bg-white focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all appearance-none cursor-pointer"
                        >
                            <option value="all">Semua Status</option>
                            <option value="success">Success</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Sort By -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Urutkan</label>
                    <div class="relative">
                        <select
                            v-model="filters.sort_by"
                            class="text-sm border border-gray-200 rounded-lg pl-3 pr-8 py-1.5 text-gray-700 bg-white focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all appearance-none cursor-pointer"
                        >
                            <option value="revenue">Revenue</option>
                            <option value="qty">Qty Terjual</option>
                            <option value="orders">Jumlah Order</option>
                        </select>
                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Apply -->
                <button
                    @click="fetchReport"
                    :disabled="loading"
                    class="flex items-center gap-2 px-5 py-[7px] bg-[#ED1F24] hover:bg-[#C81A1E] text-white text-sm font-semibold rounded-lg transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm shadow-red-200 hover:shadow-md hover:shadow-red-200 active:scale-95"
                >
                    <svg v-if="loading" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Terapkan Filter
                </button>
            </div>
        </div>

        <!-- ───────────────────────── KPI CARDS ───────────────────────── -->
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
            <template v-if="loading">
                <div v-for="i in 8" :key="i" class="bg-white rounded-xl p-5 animate-pulse h-28 border border-gray-200/80 shadow-sm"></div>
            </template>
            <template v-else>
                <div
                    v-for="(card, idx) in kpiCards"
                    :key="card.label"
                    class="group bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md hover:border-gray-300/80 transition-all duration-200 overflow-hidden relative"
                >
                    <!-- Accent top border -->
                    <div class="absolute top-0 left-0 right-0 h-0.5 rounded-t-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300" :style="{ background: card.iconColor }"></div>
                    <div class="p-5 flex flex-col justify-between h-full">
                        <div class="flex items-start justify-between mb-3">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 leading-tight pr-2">{{ card.label }}</span>
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm shrink-0 border" :style="{ background: card.iconBg, borderColor: card.iconColor + '25' }">
                                <font-awesome-icon :icon="card.icon" :style="{ color: card.iconColor }" class="text-sm" />
                            </div>
                        </div>
                        <div>
                            <p class="text-lg font-bold leading-tight" :class="card.valueColor || 'text-gray-900'">{{ card.value }}</p>
                            <div v-if="card.growth !== undefined && card.growth !== null" class="flex items-center gap-1.5 mt-1.5">
                                <span
                                    :class="['text-[10px] font-bold px-1.5 py-0.5 rounded',
                                        card.growth >= 0 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-red-50 text-red-500 border border-red-100']"
                                >
                                    {{ card.growth >= 0 ? '↑' : '↓' }} {{ Math.abs(card.growth) }}%
                                </span>
                                <span class="text-[10px] text-gray-400">vs sebelumnya</span>
                            </div>
                            <p v-else-if="card.sub" class="text-xs mt-1.5" :class="card.subColor || 'text-gray-400'">{{ card.sub }}</p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- ───────────────────────── REVENUE TREND ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm mb-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Tren Revenue Produk</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ periodInfo.label }} · Top 5 produk terlaris</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex gap-1 bg-gray-100/80 p-1 rounded-lg border border-gray-200/60">
                        <button
                            v-for="t in ['area', 'bar', 'line']"
                            :key="t"
                            @click="trendChartType = t"
                            :class="['text-xs px-3 py-1 rounded-md font-semibold transition-all duration-150 capitalize',
                                trendChartType === t ? 'bg-white text-gray-800 shadow-sm border border-gray-200/80' : 'text-gray-400 hover:text-gray-600']"
                        >{{ t }}</button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div v-if="loading" class="h-64 bg-gray-50 rounded-xl animate-pulse"></div>
                <apexchart
                    v-else
                    :key="trendChartType"
                    :type="trendChartType === 'bar' ? 'bar' : 'line'"
                    height="280"
                    :options="trendOptions"
                    :series="trendSeries"
                />
            </div>
        </div>

        <!-- ───────────────────────── ROW 2: DONUT + TOP PRODUK ───────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

            <!-- Donut by Kategori -->
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Distribusi Kategori</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Proporsi revenue per kategori</p>
                </div>
                <div class="p-5">
                    <div v-if="loading" class="flex items-center justify-center py-8">
                        <div class="w-32 h-32 rounded-full border-8 border-gray-100 animate-pulse"></div>
                    </div>
                    <apexchart v-else type="donut" height="220" :options="categoryDonutOptions" :series="categoryDonutSeries" />
                    <div v-if="!loading" class="mt-4 space-y-2.5">
                        <div v-for="(cat, i) in (report.by_category || []).slice(0, 5)" :key="cat.category" class="flex items-center justify-between">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2 h-2 rounded-full shrink-0" :style="{ background: COLORS[i % COLORS.length] }"></span>
                                <span class="text-xs text-gray-600 font-medium truncate">{{ cat.category || 'Lainnya' }}</span>
                            </div>
                            <div class="text-right shrink-0 ml-2">
                                <span class="text-xs font-bold text-gray-700">Rp {{ fmtNum(cat.total_revenue) }}</span>
                                <span class="text-[10px] text-gray-400 ml-1.5">{{ cat.pct }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Produk Bar -->
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm lg:col-span-2 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Produk Terlaris</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Berdasarkan {{ sortLabel }}</p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#ED1F24]/8 text-[#ED1F24] border border-[#ED1F24]/15 uppercase tracking-wider">
                        Top {{ report.top_products?.length || 0 }}
                    </span>
                </div>
                <div class="p-6">
                    <div v-if="loading" class="space-y-5">
                        <div v-for="i in 5" :key="i" class="h-8 bg-gray-100 rounded-lg animate-pulse"></div>
                    </div>
                    <div v-else class="space-y-5">
                        <div
                            v-for="(p, i) in report.top_products"
                            :key="p.product_name"
                            class="group"
                        >
                            <div class="flex items-center gap-3 mb-1.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] font-bold shrink-0"
                                    :style="i === 0 ? 'background:#ED1F24;color:white' : i === 1 ? 'background:#f3f4f6;color:#374151' : 'background:#f9fafb;color:#9ca3af'"
                                >#{{ i + 1 }}</span>
                                <span class="text-sm font-semibold text-gray-700 flex-1 truncate">{{ p.product_name }}</span>
                                <div class="text-right shrink-0">
                                    <span class="text-sm font-bold text-gray-800">Rp {{ fmtNum(p.total_revenue) }}</span>
                                    <span class="text-xs text-gray-400 ml-2">{{ fmtNum(p.total_qty) }} unit</span>
                                </div>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-700"
                                    :style="{ width: productBarWidth(p) + '%', background: COLORS[i % COLORS.length] }"
                                ></div>
                            </div>
                        </div>
                        <div v-if="!report.top_products?.length" class="text-center text-gray-400 text-sm py-8">
                            Belum ada data produk
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── ROW 3: QTY + SLOW MOVING ───────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Top Produk by Qty Terjual</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Top 10 · berdasarkan jumlah unit terjual</p>
                </div>
                <div class="p-5">
                    <div v-if="loading" class="h-72 bg-gray-50 rounded-xl animate-pulse"></div>
                    <apexchart v-else type="bar" height="300" :options="qtyBarOptions" :series="qtyBarSeries" />
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Produk Slow Moving</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Produk dengan penjualan terendah dalam periode ini</p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 border border-gray-200 uppercase tracking-wider">Low Sales</span>
                </div>
                <div class="p-5">
                    <div v-if="loading" class="h-72 bg-gray-50 rounded-xl animate-pulse"></div>
                    <apexchart v-else type="bar" height="300" :options="slowMovingOptions" :series="slowMovingSeries" />
                </div>
            </div>
        </div>

        <!-- ───────────────────────── STOK MENIPIS ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm mb-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Stok Menipis</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Produk dengan stok di bawah ambang batas</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    <span class="text-xs font-bold text-amber-600">
                        {{ report.low_stock?.length || 0 }} produk perlu restock
                    </span>
                </div>
            </div>

            <div v-if="loading" class="p-6 space-y-3">
                <div v-for="i in 5" :key="i" class="h-10 bg-gray-50 rounded-xl animate-pulse"></div>
            </div>

            <div v-else-if="report.low_stock?.length">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[500px]">
                        <thead>
                            <tr class="bg-gray-50/60 border-b border-gray-100">
                                <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Produk</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 hidden md:table-cell">Kategori</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 hidden lg:table-cell">SKU</th>
                                <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Stok</th>
                                <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Terjual</th>
                                <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="p in pagedLowStock"
                                :key="p.id"
                                class="hover:bg-gray-50/60 transition-colors duration-150"
                            >
                                <td class="px-6 py-3.5">
                                    <span class="font-semibold text-gray-800 text-sm">{{ p.product_name }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-gray-400 text-xs hidden md:table-cell">{{ p.category || '-' }}</td>
                                <td class="px-6 py-3.5 hidden lg:table-cell">
                                    <span class="text-xs font-mono text-gray-400 bg-gray-100 px-2 py-0.5 rounded-md">{{ p.sku || '-' }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <span :class="['font-bold text-sm tabular-nums', p.stock === 0 ? 'text-red-500' : p.stock <= 5 ? 'text-red-400' : 'text-amber-500']">
                                        {{ fmtNum(p.stock) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right text-gray-500 text-sm tabular-nums">{{ fmtNum(p.total_qty_sold) }}</td>
                                <td class="px-6 py-3.5 text-right">
                                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border',
                                        p.stock === 0 ? 'bg-red-50 text-red-500 border-red-100' :
                                        p.stock <= 5  ? 'bg-red-50 text-red-400 border-red-100' :
                                                    'bg-amber-50 text-amber-600 border-amber-100']">
                                        {{ p.stock === 0 ? 'Habis' : p.stock <= 5 ? 'Kritis' : 'Menipis' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="lowStockTotalPages > 1" class="px-6 py-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-gray-400">
                        Menampilkan
                        <span class="font-semibold text-gray-600">
                            {{ lowStockPage * LOW_STOCK_PER_PAGE + 1 }}–{{ Math.min((lowStockPage + 1) * LOW_STOCK_PER_PAGE, report.low_stock.length) }}
                        </span>
                        dari <span class="font-semibold text-gray-600">{{ report.low_stock.length }}</span> produk
                    </span>
                    <div class="flex items-center gap-1.5">
                        <button
                            @click="lowStockPage--"
                            :disabled="lowStockPage === 0"
                            class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-all duration-150"
                        >← Prev</button>

                        <!-- Page numbers -->
                        <template v-for="p in lowStockTotalPages" :key="p">
                            <button
                                v-if="p - 1 === lowStockPage"
                                class="text-xs w-7 h-7 rounded-lg font-bold bg-[#ED1F24] text-white border border-[#ED1F24] shadow-sm"
                            >{{ p }}</button>
                            <button
                                v-else-if="Math.abs(p - 1 - lowStockPage) <= 1"
                                @click="lowStockPage = p - 1"
                                class="text-xs w-7 h-7 rounded-lg border border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-50 transition-all duration-150"
                            >{{ p }}</button>
                            <span
                                v-else-if="Math.abs(p - 1 - lowStockPage) === 2"
                                class="text-xs text-gray-300 px-0.5"
                            >…</span>
                        </template>

                        <button
                            @click="lowStockPage++"
                            :disabled="lowStockPage >= lowStockTotalPages - 1"
                            class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-all duration-150"
                        >Next →</button>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="px-6 py-12 text-center">
                <div class="flex flex-col items-center gap-2">
                    <span class="text-2xl">🎉</span>
                    <span class="text-gray-400 text-sm font-medium">Semua produk memiliki stok yang cukup</span>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── DETAIL TABEL ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden mb-4">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Performa Produk Detail</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Semua produk pada periode ini</p>
                </div>
                <a href="/admin/products" class="group flex items-center gap-1 text-xs font-semibold text-[#ED1F24] hover:text-[#C81A1E] transition-colors">
                    Kelola produk
                    <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div v-if="loading" class="p-6 space-y-3">
                <div v-for="i in 5" :key="i" class="h-12 bg-gray-50 rounded-xl animate-pulse"></div>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm min-w-[720px]">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 w-10">#</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Produk</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 hidden md:table-cell">Kategori</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Qty Terjual</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Jumlah Order</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Revenue</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400 hidden lg:table-cell">Kontribusi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="(p, i) in report.all_products"
                            :key="p.product_name"
                            class="hover:bg-gray-50/60 transition-colors duration-150"
                        >
                            <td class="px-6 py-3.5 text-xs text-gray-300 font-bold tabular-nums">{{ String(i + 1).padStart(2, '0') }}</td>
                            <td class="px-6 py-3.5">
                                <span class="font-semibold text-gray-800">{{ p.product_name }}</span>
                            </td>
                            <td class="px-6 py-3.5 hidden md:table-cell">
                                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-md font-medium">{{ p.category || '-' }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right font-semibold text-gray-700 tabular-nums">{{ fmtNum(p.total_qty) }}</td>
                            <td class="px-6 py-3.5 text-right text-gray-500 tabular-nums">{{ fmtNum(p.total_orders) }}</td>
                            <td class="px-6 py-3.5 text-right font-bold text-gray-900 tabular-nums">Rp {{ fmtNum(p.total_revenue) }}</td>
                            <td class="px-6 py-3.5 text-right hidden lg:table-cell">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-20 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-[#ED1F24] rounded-full transition-all duration-700"
                                            :style="{ width: revenueContributionWidth(p.total_revenue) + '%' }"
                                        ></div>
                                    </div>
                                    <span class="text-xs text-gray-400 w-8 text-right tabular-nums">{{ revenueContributionPct(p.total_revenue) }}%</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!report.all_products?.length">
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <span class="text-gray-400 text-sm font-medium">Tidak ada data produk dalam periode ini</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ───────────────────────── ERROR ───────────────────────── -->
        <div v-if="error" class="rounded-xl p-4 text-sm flex items-center gap-3 bg-red-50 border border-red-200">
            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <span class="text-red-600 font-medium flex-1">{{ error }}</span>
            <button @click="fetchReport" class="text-xs font-bold text-[#ED1F24] hover:underline shrink-0 px-3 py-1 rounded-lg border border-red-200 hover:bg-red-50 transition-colors">
                Coba lagi
            </button>
        </div>

    </AdminLayout>
</template>

<script>
import AdminLayout from '../../components/admin/AdminLayout.vue'
import VueApexCharts from 'vue3-apexcharts'
import axios from '../../axios.js'

const COLORS = ['#ED1F24', '#3B82F6', '#22C55E', '#F59E0B', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316']

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
    name: 'ProductReports',

    components: { AdminLayout, apexchart: VueApexCharts },

    data() {
        return {
            COLORS,
            loading: true,
            error: null,
            trendChartType: 'area',
            lowStockPage: 0,
            LOW_STOCK_PER_PAGE: 10,

            filters: {
                period:    'this_month',
                date_from: '',
                date_to:   '',
                status:    'all',
                category:  '',
                sort_by:   'revenue',
            },

            periodPresets: [
                { label: 'Hari Ini',   value: 'today' },
                { label: 'Kemarin',    value: 'yesterday' },
                { label: '7 Hari',     value: 'last_7' },
                { label: '30 Hari',    value: 'last_30' },
                { label: 'Bulan Ini',  value: 'this_month' },
                { label: 'Bulan Lalu', value: 'last_month' },
                { label: '90 Hari',    value: 'last_90' },
                { label: 'Tahun Ini',  value: 'this_year' },
                { label: 'Custom',     value: 'custom' },
            ],

            report: {
                period:           {},
                summary:          {},
                trend:            [],
                top_products:     [],
                by_category:      [],
                low_stock:        [],
                slow_moving:      [],
                all_products:     [],
                trend_by_product: [],
                trend_labels:     [],
            },

            categoryList: [],
            periodInfo:   {},
        }
    },

    computed: {
        sortLabel() {
            const m = { revenue: 'revenue tertinggi', qty: 'qty terjual', orders: 'jumlah order' }
            return m[this.filters.sort_by] || 'revenue'
        },

        kpiCards() {
            const s = this.report.summary || {}
            return [
                {
                    label: 'Total Revenue Produk',
                    value: 'Rp ' + this.fmtNum(s.total_revenue || 0),
                    icon: 'box-open', iconBg: 'rgba(237,31,36,0.07)', iconColor: '#ED1F24',
                    valueColor: 'text-[#ED1F24]',
                    growth: s.revenue_growth,
                },
                {
                    label: 'Total Qty Terjual',
                    value: this.fmtNum(s.total_qty || 0),
                    icon: 'cube', iconBg: 'rgba(59,130,246,0.07)', iconColor: '#3B82F6',
                    growth: s.qty_growth,
                },
                {
                    label: 'Produk Aktif',
                    value: this.fmtNum(s.total_products || 0),
                    icon: 'tag', iconBg: 'rgba(34,197,94,0.07)', iconColor: '#22C55E',
                    sub: `${this.fmtNum(s.total_categories || 0)} kategori`,
                    subColor: 'text-emerald-500',
                },
                {
                    label: 'Avg Revenue/Produk',
                    value: 'Rp ' + this.fmtNum(s.avg_revenue_per_product || 0),
                    icon: 'chart-bar', iconBg: 'rgba(139,92,246,0.07)', iconColor: '#8B5CF6',
                    sub: 'Rata-rata per SKU',
                    subColor: 'text-gray-400',
                },
                {
                    label: 'Produk Terlaris',
                    value: s.top_product_name ? s.top_product_name.substring(0, 20) + (s.top_product_name.length > 20 ? '…' : '') : '-',
                    icon: 'star', iconBg: 'rgba(245,158,11,0.07)', iconColor: '#F59E0B',
                    sub: `Rp ${this.fmtNum(s.top_product_revenue || 0)}`,
                    subColor: 'text-amber-500',
                },
                {
                    label: 'Total Order (Produk)',
                    value: this.fmtNum(s.total_orders || 0),
                    icon: 'file-alt', iconBg: 'rgba(20,184,166,0.07)', iconColor: '#14B8A6',
                    sub: 'Order dengan produk ini',
                    subColor: 'text-teal-500',
                },
                {
                    label: 'Stok Menipis',
                    value: this.fmtNum(s.low_stock_count || 0),
                    icon: 'exclamation-triangle', iconBg: 'rgba(239,68,68,0.07)', iconColor: '#EF4444',
                    sub: 'Produk perlu restock',
                    subColor: 'text-red-400',
                },
                {
                    label: 'Slow Moving',
                    value: this.fmtNum(s.slow_moving_count || 0),
                    icon: 'clock', iconBg: 'rgba(107,114,128,0.07)', iconColor: '#6B7280',
                    sub: 'Produk kurang diminati',
                    subColor: 'text-gray-400',
                },
            ]
        },

        pagedLowStock() {
            const start = this.lowStockPage * this.LOW_STOCK_PER_PAGE
            return (this.report.low_stock || []).slice(start, start + this.LOW_STOCK_PER_PAGE)
        },
        lowStockTotalPages() {
            return Math.ceil((this.report.low_stock?.length || 0) / this.LOW_STOCK_PER_PAGE)
        },

        trendSeries() {
            const isBar = this.trendChartType === 'bar'
            return (this.report.trend_by_product || []).map((p) => ({
                name: p.product_name,
                type: isBar ? 'bar' : this.trendChartType, // 'bar' | 'area' | 'line'
                data: p.data,
            }))
        },
        trendOptions() {
            const categories = (this.report.trend_labels || [])
            const isBar = this.trendChartType === 'bar'

            return {
                ...CHART_BASE,
                chart: {
                    ...CHART_BASE.chart,
                    type: isBar ? 'bar' : 'line', // root selalu 'line' untuk area/line mode
                    stacked: false,
                },
                colors: COLORS,
                fill: isBar
                    ? { type: 'solid' }
                    : {
                        type: this.trendChartType === 'area' ? 'gradient' : 'solid',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.2,
                            opacityTo: 0.02,
                            stops: [0, 90, 100],
                        },
                    },
                stroke: isBar
                    ? { show: false }
                    : { curve: 'smooth', width: 2 },
                xaxis: {
                    categories,
                    labels: { style: { fontSize: '10px', colors: '#9ca3af' }, rotate: -30 },
                    axisBorder: { show: false },
                    axisTicks:  { show: false },
                },
                yaxis: {
                    labels: {
                        style: { fontSize: '10px', colors: '#9ca3af' },
                        formatter: v => 'Rp ' + this.fmtNum(v),
                    },
                },
                dataLabels: { enabled: false },
                plotOptions: { bar: { borderRadius: 3, columnWidth: '55%' } },
                legend: {
                    position: 'top',
                    fontSize: '12px',
                    labels: { colors: '#6b7280' },
                    markers: { radius: 4 },
                },
                tooltip: {
                    theme: 'light',
                    shared: !isBar,       // bar tidak support shared
                    intersect: isBar,     // bar butuh intersect: true
                    y: { formatter: v => 'Rp ' + this.fmtNum(v) },
                },
            }
        },

        categoryDonutSeries() {
            return (this.report.by_category || []).slice(0, 6).map(c => Number(c.total_revenue) || 0)
        },
        categoryDonutOptions() {
            const labels = (this.report.by_category || []).slice(0, 6).map(c => c.category || 'Lainnya')
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'donut' },
                labels,
                colors: COLORS,
                dataLabels: { enabled: false },
                stroke: { width: 0 },
                legend: { show: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '11px',
                                    color: '#9ca3af',
                                    formatter: w => 'Rp ' + this.fmtNum(w.globals.seriesTotals.reduce((a, b) => a + b, 0)),
                                },
                                value: { color: '#111827', fontSize: '16px', fontWeight: 700 },
                            },
                        },
                    },
                },
                tooltip: { theme: 'light', y: { formatter: v => 'Rp ' + this.fmtNum(v) } },
            }
        },

        qtyBarSeries() {
            const top10 = [...(this.report.all_products || [])]
                .sort((a, b) => b.total_qty - a.total_qty)
                .slice(0, 10)
            return [{ name: 'Qty Terjual', data: top10.map(p => Number(p.total_qty) || 0) }]
        },
        qtyBarOptions() {
            const top10 = [...(this.report.all_products || [])]
                .sort((a, b) => b.total_qty - a.total_qty)
                .slice(0, 10)
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'bar' },
                colors: ['#3B82F6'],
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
                xaxis: {
                    categories: top10.map(p => p.product_name),
                    labels: { style: { fontSize: '10px', colors: '#9ca3af' } },
                    axisBorder: { show: false },
                    axisTicks:  { show: false },
                },
                yaxis: { labels: { style: { fontSize: '10px', colors: '#6b7280' }, maxWidth: 150 } },
                dataLabels: { enabled: false },
                tooltip: { theme: 'light', y: { formatter: v => this.fmtNum(v) + ' unit' } },
            }
        },

        slowMovingSeries() {
            return [{ name: 'Revenue', data: (this.report.slow_moving || []).map(p => Number(p.total_revenue) || 0) }]
        },
        slowMovingOptions() {
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'bar' },
                colors: ['#9CA3AF'],
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
                xaxis: {
                    categories: (this.report.slow_moving || []).map(p => p.product_name),
                    labels: {
                        style: { fontSize: '10px', colors: '#9ca3af' },
                        formatter: v => 'Rp ' + this.fmtNum(v),
                    },
                    axisBorder: { show: false },
                    axisTicks:  { show: false },
                },
                yaxis: { labels: { style: { fontSize: '10px', colors: '#6b7280' }, maxWidth: 150 } },
                dataLabels: { enabled: false },
                tooltip: { theme: 'light', y: { formatter: v => 'Rp ' + this.fmtNum(v) } },
            }
        },
    },

    methods: {
        async fetchReport() {
            this.loading = true
            this.error   = null
            try {
                const params = {
                    period:   this.filters.period,
                    status:   this.filters.status,
                    sort_by:  this.filters.sort_by,
                    category: this.filters.category || undefined,
                }
                if (this.filters.period === 'custom') {
                    params.date_from = this.filters.date_from
                    params.date_to   = this.filters.date_to
                }
                const { data } = await axios.get('/product-report', { params })
                this.report = {
                    period:           data.period           || {},
                    summary:          data.summary          || {},
                    top_products:     data.top_products     || [],
                    by_category:      data.by_category      || [],
                    low_stock:        data.low_stock        || [],
                    slow_moving:      data.slow_moving      || [],
                    all_products:     data.all_products     || [],
                    trend_by_product: data.trend_by_product || [],
                    trend_labels:     data.trend_labels     || [],
                }
                this.periodInfo   = data.period       || {}
                this.categoryList = data.category_list || []
            } catch (err) {
                this.error = 'Gagal memuat laporan produk. ' + (err.response?.data?.message || err.message)
            } finally {
                this.loading = false
            }
        },

        selectPeriod(val) {
            this.filters.period = val
            if (val !== 'custom') this.fetchReport()
        },

        exportCsv() {
            const params = new URLSearchParams({
                period:  this.filters.period,
                status:  this.filters.status,
                sort_by: this.filters.sort_by,
                export:  'csv',
                ...(this.filters.category && { category: this.filters.category }),
                ...(this.filters.period === 'custom' && {
                    date_from: this.filters.date_from,
                    date_to:   this.filters.date_to,
                }),
            })
            window.open(`/api/product-report?${params.toString()}`, '_blank')
        },

        async exportExcel() {
            try {
                const params = new URLSearchParams()
                params.append('period',  this.filters.period)
                params.append('status',  this.filters.status)
                params.append('sort_by', this.filters.sort_by)
                if (this.filters.category)  params.append('category',  this.filters.category)
                if (this.filters.period === 'custom') {
                    params.append('date_from', this.filters.date_from)
                    params.append('date_to',   this.filters.date_to)
                }
                const response = await axios.get(`/product-report/export-excel?${params.toString()}`, {
                    responseType: 'blob',
                })
                const url  = window.URL.createObjectURL(new Blob([response.data]))
                const link = document.createElement('a')
                link.href  = url
                link.setAttribute('download', `product-report-${this.filters.period}.xlsx`)
                document.body.appendChild(link)
                link.click()
                link.remove()
                window.URL.revokeObjectURL(url)
            } catch (err) {
                alert('Gagal export Excel: ' + (err.response?.data?.message || err.message))
            }
        },

        productBarWidth(product) {
            const max = Math.max(...(this.report.top_products || []).map(p => Number(p.total_revenue) || 0), 1)
            return Math.round((Number(product.total_revenue) / max) * 100)
        },

        revenueContributionWidth(revenue) {
            const max = Math.max(...(this.report.all_products || []).map(p => Number(p.total_revenue) || 0), 1)
            return Math.round((Number(revenue) / max) * 100)
        },

        revenueContributionPct(revenue) {
            const total = (this.report.all_products || []).reduce((sum, p) => sum + (Number(p.total_revenue) || 0), 0)
            if (!total) return 0
            return Math.round((Number(revenue) / total) * 100)
        },

        fmtNum(n) {
            return Number(n || 0).toLocaleString('id-ID')
        },
        fmtDate(d) {
            if (!d) return '-'
            return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        },
    },

    mounted() {
        const today = new Date().toISOString().slice(0, 10)
        this.filters.date_from = today
        this.filters.date_to   = today
        this.fetchReport()
    },
}
</script>