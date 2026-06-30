<template>
    <AdminLayout title="Sales Report">

        <!-- ───────────────────────── HEADER ───────────────────────── -->
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Sales Report</h1>
                        <span class="text-[10px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 border border-gray-200">Analytics</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Laporan penjualan mendalam
                        <span class="text-gray-300 mx-1">·</span>
                        <span class="font-medium text-gray-500">{{ periodInfo.label }}</span>
                        <span v-if="periodInfo.from" class="text-gray-400">
                            ({{ fmtDate(periodInfo.from) }} – {{ fmtDate(periodInfo.to) }})
                        </span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button
                    @click="exportExcel"
                    class="group flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:border-emerald-300 hover:bg-emerald-50 transition-all duration-150"
                >
                    <svg class="w-3.5 h-3.5 group-hover:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Excel
                </button>
                <div class="w-px h-6 bg-gray-200"></div>
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

                <div class="w-px h-10 bg-gray-200 self-end hidden sm:block"></div>

                <!-- Status Filter -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</label>
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

                <!-- Courier Filter -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Ekspedisi</label>
                    <div class="relative">
                        <select
                            v-model="filters.courier"
                            class="text-sm border border-gray-200 rounded-lg pl-3 pr-8 py-1.5 text-gray-700 bg-white focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all appearance-none cursor-pointer"
                        >
                            <option value="">Semua Ekspedisi</option>
                            <option v-for="c in courierList" :key="c" :value="c">{{ c }}</option>
                        </select>
                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Group By -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Grup Tren</label>
                    <div class="relative">
                        <select
                            v-model="filters.group_by"
                            class="text-sm border border-gray-200 rounded-lg pl-3 pr-8 py-1.5 text-gray-700 bg-white focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all appearance-none cursor-pointer"
                        >
                            <option value="">Auto</option>
                            <option value="hour">Per Jam</option>
                            <option value="day">Per Hari</option>
                            <option value="week">Per Minggu</option>
                            <option value="month">Per Bulan</option>
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
                    v-for="card in kpiCards"
                    :key="card.label"
                    class="group bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md hover:border-gray-300/80 transition-all duration-200 overflow-hidden relative"
                >
                    <div class="absolute top-0 left-0 right-0 h-0.5 rounded-t-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300" :style="{ background: card.iconColor }"></div>
                    <div class="p-5 flex flex-col justify-between h-full">
                        <div class="flex items-start justify-between mb-3">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 leading-tight pr-2">{{ card.label }}</span>
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 border" :style="{ background: card.iconBg, borderColor: card.iconColor + '25' }">
                                <font-awesome-icon :icon="card.icon" :style="{ color: card.iconColor }" class="text-sm" />
                            </div>
                        </div>
                        <div>
                            <p class="text-lg font-bold leading-tight" :class="card.valueColor || 'text-gray-900'">{{ card.value }}</p>
                            <div v-if="card.growth !== undefined && card.growth !== null" class="flex items-center gap-1.5 mt-1.5">
                                <span
                                    :class="['text-[10px] font-bold px-1.5 py-0.5 rounded border',
                                        card.growth >= 0 ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-500 border-red-100']"
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

        <!-- ───────────────────────── TREND CHART ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm mb-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Tren Revenue & Order</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ periodInfo.label }} · dikelompokkan per {{ groupByLabel }}</p>
                </div>
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

        <!-- ───────────────────────── ROW 2: DONUT + COURIERS ───────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

            <!-- Status Donut -->
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Distribusi Status</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Proporsi order per status</p>
                </div>
                <div class="p-5">
                    <div v-if="loading" class="flex items-center justify-center py-8">
                        <div class="w-32 h-32 rounded-full border-8 border-gray-100 animate-pulse"></div>
                    </div>
                    <apexchart v-else type="donut" height="220" :options="statusDonutOptions" :series="statusDonutSeries" />
                    <div v-if="!loading" class="mt-4 space-y-2.5">
                        <div v-for="s in statusRows" :key="s.key" class="flex items-center justify-between">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2 h-2 rounded-full shrink-0" :style="{ background: s.color }"></span>
                                <span class="text-xs text-gray-600 font-medium">{{ s.label }}</span>
                            </div>
                            <div class="text-right shrink-0 ml-2">
                                <span class="text-xs font-bold text-gray-700">{{ fmtNum(s.count) }}</span>
                                <span class="text-[10px] text-gray-400 ml-1.5">{{ s.pct }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Couriers -->
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm lg:col-span-2 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Top Ekspedisi</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Berdasarkan revenue dari order sukses</p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#ED1F24]/8 text-[#ED1F24] border border-[#ED1F24]/15 uppercase tracking-wider">
                        Top {{ report.top_couriers?.length || 0 }}
                    </span>
                </div>
                <div class="p-6">
                    <div v-if="loading" class="space-y-5">
                        <div v-for="i in 5" :key="i" class="h-8 bg-gray-100 rounded-lg animate-pulse"></div>
                    </div>
                    <div v-else class="space-y-5">
                        <div v-for="(c, i) in report.top_couriers" :key="c.courier">
                            <div class="flex items-center gap-3 mb-1.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] font-bold shrink-0"
                                    :style="i === 0 ? 'background:#ED1F24;color:white' : i === 1 ? 'background:#f3f4f6;color:#374151' : 'background:#f9fafb;color:#9ca3af'"
                                >#{{ i + 1 }}</span>
                                <span class="text-sm font-semibold text-gray-700 flex-1 truncate">{{ c.courier }}</span>
                                <div class="text-right shrink-0">
                                    <span class="text-sm font-bold text-gray-800">Rp {{ fmtNum(c.total_revenue) }}</span>
                                    <span class="text-xs text-gray-400 ml-2">{{ fmtNum(c.total_orders) }} order</span>
                                </div>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-700"
                                    :style="{ width: courierBarWidth(c.total_revenue) + '%', background: COLORS[i % COLORS.length] }"
                                ></div>
                            </div>
                        </div>
                        <div v-if="!report.top_couriers?.length" class="text-center text-gray-400 text-sm py-8">
                            Belum ada data ekspedisi
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── ROW 3: TOP PRODUCTS + PROVINCE ───────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Produk Terlaris</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Top 10 · berdasarkan revenue</p>
                </div>
                <div class="p-5">
                    <div v-if="loading" class="h-72 bg-gray-50 rounded-xl animate-pulse"></div>
                    <apexchart v-else type="bar" height="300" :options="productOptions" :series="productSeries" />
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Revenue per Provinsi</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Top 10 · berdasarkan revenue</p>
                </div>
                <div class="p-5">
                    <div v-if="loading" class="h-72 bg-gray-50 rounded-xl animate-pulse"></div>
                    <apexchart v-else type="bar" height="300" :options="provinceOptions" :series="provinceSeries" />
                </div>
            </div>
        </div>

        <!-- ───────────────────────── HEATMAP ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm mb-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-start justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Heatmap Waktu Order</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Distribusi jumlah order per jam & hari — semakin gelap semakin ramai</p>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 border border-gray-200 uppercase tracking-wider">24 Jam</span>
            </div>
            <div class="p-6">
                <div v-if="loading" class="h-36 bg-gray-50 rounded-xl animate-pulse"></div>
                <div v-else class="overflow-x-auto">
                    <div class="min-w-[600px]">
                        <!-- Hour labels -->
                        <div class="flex items-center mb-2 ml-12">
                            <span
                                v-for="h in heatmapHours"
                                :key="h"
                                class="flex-1 text-center text-[9px] text-gray-300 font-medium"
                            >{{ h % 3 === 0 ? h + ':00' : '' }}</span>
                        </div>
                        <!-- Rows -->
                        <div v-for="row in report.heatmap" :key="row.day" class="flex items-center mb-1">
                            <span class="w-10 text-xs text-gray-400 font-semibold shrink-0 text-right pr-2">{{ row.day }}</span>
                            <div class="flex-1 flex gap-0.5">
                                <div
                                    v-for="(count, h) in row.hours"
                                    :key="h"
                                    :title="`${row.day} ${h}:00 — ${count} order`"
                                    class="flex-1 h-7 rounded-sm transition-colors duration-200 cursor-default"
                                    :style="{ background: heatColor(count) }"
                                ></div>
                            </div>
                        </div>
                        <!-- Legend -->
                        <div class="flex items-center gap-2 mt-4 ml-12">
                            <span class="text-[10px] text-gray-400 font-medium">Sepi</span>
                            <div class="flex gap-0.5">
                                <div v-for="v in [0, 0.2, 0.4, 0.6, 0.8, 1]" :key="v" class="w-6 h-3 rounded-sm" :style="{ background: heatColor(v * heatmapMax) }"></div>
                            </div>
                            <span class="text-[10px] text-gray-400 font-medium">Ramai</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── RECENT ORDERS TABLE ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden mb-4">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Order dalam Periode Ini</h3>
                    <p class="text-xs text-gray-400 mt-0.5">20 transaksi terbaru</p>
                </div>
                <a href="/admin/orders" class="group flex items-center gap-1 text-xs font-semibold text-[#ED1F24] hover:text-[#C81A1E] transition-colors">
                    Lihat semua
                    <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div v-if="loading" class="p-6 space-y-3">
                <div v-for="i in 5" :key="i" class="h-12 bg-gray-50 rounded-xl animate-pulse"></div>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm min-w-[640px]">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Invoice</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Pelanggan</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 hidden md:table-cell">Ekspedisi</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 hidden sm:table-cell">Tanggal</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Total</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="o in report.recent_orders"
                            :key="o.id"
                            class="hover:bg-gray-50/60 transition-colors duration-150"
                        >
                            <td class="px-6 py-3.5">
                                <span class="font-mono font-bold text-gray-700 text-xs bg-gray-100 px-2 py-0.5 rounded-md">#{{ o.invoice_number }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-gray-700 text-sm font-medium">{{ o.customer_name }}</td>
                            <td class="px-6 py-3.5 hidden md:table-cell">
                                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-md font-medium">{{ o.shipping_courier }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-gray-400 text-xs hidden sm:table-cell tabular-nums">{{ fmtDatetime(o.created_at) }}</td>
                            <td class="px-6 py-3.5 text-right font-bold text-gray-900 text-sm tabular-nums">
                                Rp {{ Number(o.total_price).toLocaleString('id-ID') }}
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border" :class="statusClass(o.status)">
                                    {{ o.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!report.recent_orders?.length">
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span class="text-gray-400 text-sm font-medium">Tidak ada order dalam periode ini</span>
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
    name: 'SalesReport',

    components: { AdminLayout, apexchart: VueApexCharts },

    data() {
        return {
            COLORS,
            loading: true,
            error: null,
            trendChartType: 'area',

            filters: {
                period:    'this_month',
                date_from: '',
                date_to:   '',
                status:    'all',
                courier:   '',
                group_by:  '',
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
                period:        {},
                summary:       {},
                trend:         [],
                top_products:  [],
                top_couriers:  [],
                status_dist:   {},
                by_province:   [],
                heatmap:       [],
                recent_orders: [],
            },

            courierList: [],
            periodInfo:  {},
        }
    },

    computed: {
        groupByLabel() {
            const m = { hour: 'jam', day: 'hari', week: 'minggu', month: 'bulan' }
            return m[this.report.period?.group_by] ?? 'periode'
        },

        kpiCards() {
            const s = this.report.summary
            return [
                {
                    label: 'Total Revenue',
                    value: 'Rp ' + this.fmtNum(s.total_revenue || 0),
                    icon: 'money-bill-wave', iconBg: 'rgba(237,31,36,0.07)', iconColor: '#ED1F24',
                    valueColor: 'text-[#ED1F24]',
                    growth: s.revenue_growth,
                },
                {
                    label: 'Total Order',
                    value: this.fmtNum(s.total_orders || 0),
                    icon: 'box-open', iconBg: 'rgba(59,130,246,0.07)', iconColor: '#3B82F6',
                    growth: s.orders_growth,
                },
                {
                    label: 'Success Rate',
                    value: (s.success_rate || 0) + '%',
                    icon: 'circle-check', iconBg: 'rgba(34,197,94,0.07)', iconColor: '#22C55E',
                    sub: `${this.fmtNum(s.total_success || 0)} order sukses`,
                    subColor: 'text-emerald-500',
                },
                {
                    label: 'Avg Order Value',
                    value: 'Rp ' + this.fmtNum(s.avg_order_value || 0),
                    icon: 'chart-line', iconBg: 'rgba(139,92,246,0.07)', iconColor: '#8B5CF6',
                    sub: 'Rata-rata per transaksi sukses',
                    subColor: 'text-gray-400',
                },
                {
                    label: 'Order Sukses',
                    value: this.fmtNum(s.total_success || 0),
                    icon: 'circle-check', iconBg: 'rgba(34,197,94,0.06)', iconColor: '#22C55E',
                    sub: `Revenue Rp ${this.fmtNum(s.total_revenue || 0)}`,
                    subColor: 'text-emerald-500',
                },
                {
                    label: 'Order Pending',
                    value: this.fmtNum(s.total_pending || 0),
                    icon: 'clock', iconBg: 'rgba(245,158,11,0.07)', iconColor: '#F59E0B',
                    sub: 'Menunggu proses',
                    subColor: 'text-amber-500',
                },
                {
                    label: 'Dibatalkan',
                    value: this.fmtNum(s.total_cancelled || 0),
                    icon: 'circle-xmark', iconBg: 'rgba(239,68,68,0.07)', iconColor: '#EF4444',
                    sub: 'Order cancelled',
                    subColor: 'text-red-400',
                },
                {
                    label: 'Biaya Pengiriman',
                    value: 'Rp ' + this.fmtNum(s.total_shipping || 0),
                    icon: 'truck', iconBg: 'rgba(20,184,166,0.07)', iconColor: '#14B8A6',
                    sub: 'Total ongkir terkumpul',
                    subColor: 'text-teal-500',
                },
            ]
        },

        trendSeries() {
            const isBar = this.trendChartType === 'bar'
            return [
                {
                    name: 'Revenue',
                    type: isBar ? 'bar' : this.trendChartType,
                    data: this.report.trend.map(t => t.revenue),
                },
                {
                    name: 'Order Count',
                    type: isBar ? 'bar' : 'line',
                    data: this.report.trend.map(t => t.total_orders),
                },
            ]
        },
        trendOptions() {
            const isBar = this.trendChartType === 'bar'
            return {
                ...CHART_BASE,
                chart: {
                    ...CHART_BASE.chart,
                    type: isBar ? 'bar' : 'line',
                    stacked: false,
                },
                colors: ['#ED1F24', '#3B82F6'],
                stroke: isBar
                    ? { show: false }
                    : { curve: 'smooth', width: [2, 2] },
                fill: isBar
                    ? { type: 'solid' }
                    : {
                        type: this.trendChartType === 'area' ? 'gradient' : 'solid',
                        gradient: { opacityFrom: 0.15, opacityTo: 0.01, stops: [0, 90, 100] },
                    },
                xaxis: {
                    categories: this.report.trend.map(t => t.period),
                    labels: { style: { fontSize: '10px', colors: '#9ca3af' }, rotate: -30 },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: [
                    {
                        seriesName: 'Revenue',
                        labels: {
                            style: { fontSize: '10px', colors: '#9ca3af' },
                            formatter: v => 'Rp ' + this.fmtNum(v),
                        },
                    },
                    {
                        seriesName: 'Order Count',
                        opposite: true,
                        labels: {
                            style: { fontSize: '10px', colors: '#9ca3af' },
                            formatter: v => this.fmtNum(v) + ' order',
                        },
                    },
                ],
                dataLabels: { enabled: false },
                plotOptions: { bar: { borderRadius: 3, columnWidth: '45%' } },
                legend: {
                    position: 'top',
                    fontSize: '12px',
                    labels: { colors: '#6b7280' },
                    markers: { radius: 4 },
                },
                tooltip: {
                    theme: 'light',
                    shared: !isBar,
                    intersect: isBar,
                    y: [
                        { formatter: v => 'Rp ' + this.fmtNum(v) },
                        { formatter: v => this.fmtNum(v) + ' order' },
                    ],
                },
            }
        },

        statusDonutSeries() {
            const d = this.report.status_dist
            return [
                d.success?.count   || 0,
                d.pending?.count   || 0,
                d.cancelled?.count || 0,
            ]
        },
        statusDonutOptions() {
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'donut' },
                labels: ['Success', 'Pending', 'Cancelled'],
                colors: ['#22C55E', '#F59E0B', '#EF4444'],
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
                                    formatter: w => this.fmtNum(w.globals.seriesTotals.reduce((a, b) => a + b, 0)),
                                },
                                value: { color: '#111827', fontSize: '16px', fontWeight: 700 },
                            },
                        },
                    },
                },
                tooltip: { theme: 'light', y: { formatter: v => this.fmtNum(v) + ' order' } },
            }
        },
        statusRows() {
            const d   = this.report.status_dist
            const tot = (d.success?.count || 0) + (d.pending?.count || 0) + (d.cancelled?.count || 0)
            const pct = n => tot > 0 ? Math.round((n / tot) * 100) : 0
            return [
                { key: 'success',   label: 'Success',   color: '#22C55E', count: d.success?.count   || 0, pct: pct(d.success?.count   || 0) },
                { key: 'pending',   label: 'Pending',   color: '#F59E0B', count: d.pending?.count   || 0, pct: pct(d.pending?.count   || 0) },
                { key: 'cancelled', label: 'Cancelled', color: '#EF4444', count: d.cancelled?.count || 0, pct: pct(d.cancelled?.count || 0) },
            ]
        },

        productSeries() {
            return [{ name: 'Revenue', data: this.report.top_products.map(p => p.total_revenue) }]
        },
        productOptions() {
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'bar' },
                colors: ['#ED1F24'],
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
                xaxis: {
                    categories: this.report.top_products.map(p => p.product),
                    labels: {
                        style: { fontSize: '10px', colors: '#9ca3af' },
                        formatter: v => 'Rp ' + this.fmtNum(v),
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: { labels: { style: { fontSize: '10px', colors: '#6b7280' }, maxWidth: 150 } },
                dataLabels: { enabled: false },
                tooltip: { theme: 'light', y: { formatter: v => 'Rp ' + this.fmtNum(v) } },
            }
        },

        provinceSeries() {
            return [{ name: 'Revenue', data: this.report.by_province.map(p => p.total_revenue) }]
        },
        provinceOptions() {
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'bar' },
                colors: ['#3B82F6'],
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
                xaxis: {
                    categories: this.report.by_province.map(p => p.province),
                    labels: {
                        style: { fontSize: '10px', colors: '#9ca3af' },
                        formatter: v => 'Rp ' + this.fmtNum(v),
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: { labels: { style: { fontSize: '10px', colors: '#6b7280' }, maxWidth: 150 } },
                dataLabels: { enabled: false },
                tooltip: { theme: 'light', y: { formatter: v => 'Rp ' + this.fmtNum(v) } },
            }
        },

        heatmapHours() {
            return Array.from({ length: 24 }, (_, i) => i)
        },
        heatmapMax() {
            let max = 1
            for (const row of (this.report.heatmap || [])) {
                for (const c of row.hours) if (c > max) max = c
            }
            return max
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
                    courier:  this.filters.courier  || undefined,
                    group_by: this.filters.group_by || undefined,
                }
                if (this.filters.period === 'custom') {
                    params.date_from = this.filters.date_from
                    params.date_to   = this.filters.date_to
                }
                const { data } = await axios.get('/sales-report', { params })
                this.report      = data
                this.periodInfo  = data.period
                this.courierList = data.courier_list || []
            } catch (err) {
                this.error = 'Gagal memuat laporan. ' + (err.response?.data?.message || err.message)
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
                export:  'csv',
                ...(this.filters.courier  && { courier:   this.filters.courier }),
                ...(this.filters.period === 'custom' && {
                    date_from: this.filters.date_from,
                    date_to:   this.filters.date_to,
                }),
            })
            window.open(`/api/sales-report?${params.toString()}`, '_blank')
        },

        async exportExcel() {
            try {
                const params = new URLSearchParams()
                params.append('period', this.filters.period)
                params.append('status', this.filters.status)
                if (this.filters.courier)   params.append('courier',   this.filters.courier)
                if (this.filters.group_by)  params.append('group_by',  this.filters.group_by)
                if (this.filters.period === 'custom') {
                    params.append('date_from', this.filters.date_from)
                    params.append('date_to',   this.filters.date_to)
                }
                const response = await axios.get(`/sales-report/export-excel?${params.toString()}`, {
                    responseType: 'blob',
                })
                const url  = window.URL.createObjectURL(new Blob([response.data]))
                const link = document.createElement('a')
                link.href  = url
                link.setAttribute('download', `sales-report-${this.filters.period}.xlsx`)
                document.body.appendChild(link)
                link.click()
                link.remove()
                window.URL.revokeObjectURL(url)
            } catch (err) {
                alert('Gagal export Excel: ' + (err.response?.data?.message || err.message))
            }
        },

        courierBarWidth(revenue) {
            const max = Math.max(...(this.report.top_couriers || []).map(c => c.total_revenue), 1)
            return Math.round((revenue / max) * 100)
        },

        heatColor(count) {
            if (!count) return '#f9fafb'
            const pct = Math.min(count / this.heatmapMax, 1)
            const r = 237
            const g = Math.round(31  + (1 - pct) * (220 - 31))
            const b = Math.round(36  + (1 - pct) * (220 - 36))
            return `rgba(${r},${g},${b},${0.12 + pct * 0.88})`
        },

        statusClass(status) {
            const map = {
                success:   'bg-emerald-50 text-emerald-600 border-emerald-100',
                pending:   'bg-amber-50 text-amber-600 border-amber-100',
                cancelled: 'bg-red-50 text-red-500 border-red-100',
            }
            return map[status?.toLowerCase()] ?? 'bg-gray-100 text-gray-500 border-gray-200'
        },

        fmtNum(n) {
            return Number(n || 0).toLocaleString('id-ID')
        },
        fmtDate(d) {
            if (!d) return '-'
            return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        },
        fmtDatetime(d) {
            if (!d) return '-'
            return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
        },
    },

    mounted() {
        document.title = 'Sales Reports - Two Brothers Vape System'
        const today = new Date().toISOString().slice(0, 10)
        this.filters.date_from = today
        this.filters.date_to   = today
        this.fetchReport()
    },
}
</script>