<template>
    <AdminLayout title="Visitor Statistics">

        <!-- ───────────────────────── HERO ───────────────────────── -->
        <div class="relative mb-8 rounded-2xl overflow-hidden"
            style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background:white;"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background:white;"></div>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10" style="background:white;"></div>

            <div class="relative px-7 py-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">Analytics</p>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Visitor Statistics</h1>
                    <p class="text-red-200 text-xs mt-1.5">Analitik pengunjung online store secara real-time</p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <!-- Period tabs -->
                    <div class="flex items-center gap-1 bg-white/10 border border-white/20 rounded-lg p-1 backdrop-blur-sm">
                        <button
                            v-for="p in periods"
                            :key="p.value"
                            @click="setPeriod(p.value)"
                            class="text-xs font-semibold px-3 py-1.5 rounded-md transition-all duration-150"
                            :class="period === p.value
                                ? 'bg-white text-[#ED1F24] shadow-sm'
                                : 'text-white/80 hover:text-white hover:bg-white/10'"
                        >{{ p.label }}</button>
                    </div>
                    <!-- Custom date range -->
                    <div v-if="period === 'custom'" class="flex items-center gap-2">
                        <input type="date" v-model="customFrom"
                            class="text-xs px-2 py-1.5 rounded-lg border border-white/20 bg-white/10 text-white placeholder-white/50 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-white/30" />
                        <span class="text-white/50 text-xs">→</span>
                        <input type="date" v-model="customTo"
                            class="text-xs px-2 py-1.5 rounded-lg border border-white/20 bg-white/10 text-white placeholder-white/50 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-white/30" />
                        <button @click="fetchAll"
                            class="text-xs font-bold px-3 py-1.5 bg-white text-[#ED1F24] rounded-lg hover:bg-red-50 transition-colors">Terapkan</button>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-white bg-white/10 border border-white/20 px-3 py-2 rounded-lg backdrop-blur-sm">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Live
                    </div>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── KPI CARDS ───────────────────────── -->
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
            <template v-if="loading">
                <div v-for="i in 4" :key="i" class="bg-white rounded-xl p-5 animate-pulse h-28 border border-gray-200/80 shadow-sm"></div>
            </template>
            <template v-else>
                <div
                    v-for="card in kpiCards" :key="card.label"
                    class="group bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md hover:border-gray-300/80 transition-all duration-200 overflow-hidden relative"
                >
                    <div class="absolute top-0 left-0 right-0 h-0.5 rounded-t-xl" :style="{ background: card.accent }"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-3">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 leading-tight pr-2">{{ card.label }}</span>
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 border"
                                :style="{ background: card.iconBg, borderColor: card.accent + '25' }">
                                <font-awesome-icon :icon="['fas', card.icon]" :style="{ color: card.accent }" class="text-sm" />
                            </div>
                        </div>
                        <p class="text-lg font-bold text-gray-900 leading-tight tabular-nums">{{ card.value }}</p>
                        <p v-if="card.delta !== null" class="text-xs mt-1.5 font-semibold flex items-center gap-1"
                            :class="card.delta >= 0 ? 'text-emerald-500' : 'text-red-400'">
                            <span>{{ card.delta >= 0 ? '↑' : '↓' }} {{ Math.abs(card.delta) }}%</span>
                            <span class="text-gray-400 font-normal">vs periode sebelumnya</span>
                        </p>
                        <p v-else-if="card.sub" class="text-xs mt-1.5 text-gray-400">{{ card.sub }}</p>
                    </div>
                </div>
            </template>
        </div>

        <!-- ───────────────────────── ROW 1: Daily Trend + New vs Returning ───────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

            <!-- Daily Trend -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Tren Kunjungan Harian</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Page views & unique visitors</p>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-[#ED1F24]/8 border border-[#ED1F24]/15 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    <div v-if="loading" class="h-44 bg-gray-50 rounded-xl animate-pulse"></div>
                    <apexchart v-else type="area" height="180" :options="dailyTrendOptions" :series="dailyTrendSeries" />
                </div>
            </div>

            <!-- New vs Returning Radial -->
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">New vs Returning</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Komposisi jenis visitor</p>
                </div>
                <div class="p-5 flex flex-col items-center">
                    <div v-if="loading" class="w-36 h-36 rounded-full border-8 border-gray-100 animate-pulse my-4"></div>
                    <apexchart v-else type="donut" height="200" :options="newVsReturningOptions" :series="newVsReturningSeries" />
                    <div class="w-full mt-2 grid grid-cols-2 gap-2">
                        <div class="text-center p-2 bg-[#ED1F24]/5 rounded-lg border border-[#ED1F24]/10">
                            <p class="text-[10px] font-bold text-[#ED1F24] uppercase tracking-wider">New</p>
                            <p class="text-sm font-bold text-gray-800 tabular-nums">{{ (data.overview?.new_visitors || 0).toLocaleString('id-ID') }}</p>
                        </div>
                        <div class="text-center p-2 bg-blue-50 rounded-lg border border-blue-100">
                            <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">Returning</p>
                            <p class="text-sm font-bold text-gray-800 tabular-nums">{{ (data.overview?.returning || 0).toLocaleString('id-ID') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── ROW 2: Device + Browser + OS ───────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

            <!-- Device Type Donut -->
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Tipe Perangkat</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Desktop · Mobile · Tablet</p>
                </div>
                <div class="p-5">
                    <div v-if="loading" class="h-44 bg-gray-50 rounded-xl animate-pulse"></div>
                    <apexchart v-else type="donut" height="180" :options="deviceOptions" :series="deviceSeries" />
                </div>
            </div>

            <!-- Browser Bar -->
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Browser</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Top browser yang digunakan</p>
                </div>
                <div class="p-5 space-y-3">
                    <div v-if="loading">
                        <div v-for="i in 5" :key="i" class="h-6 bg-gray-100 rounded animate-pulse mb-2"></div>
                    </div>
                    <template v-else>
                        <div v-for="(item, i) in (data.browsers || []).slice(0, 6)" :key="item.browser">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold text-gray-700 flex-1 truncate">{{ item.browser || 'Unknown' }}</span>
                                <span class="text-xs font-bold text-gray-500 tabular-nums">{{ item.count.toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700"
                                    :style="{ width: barPct(item.count, data.browsers) + '%', background: paletteAt(i) }"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- OS Bar -->
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Sistem Operasi</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Top OS yang digunakan</p>
                </div>
                <div class="p-5 space-y-3">
                    <div v-if="loading">
                        <div v-for="i in 5" :key="i" class="h-6 bg-gray-100 rounded animate-pulse mb-2"></div>
                    </div>
                    <template v-else>
                        <div v-for="(item, i) in (data.os || []).slice(0, 6)" :key="item.os">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold text-gray-700 flex-1 truncate">{{ item.os || 'Unknown' }}</span>
                                <span class="text-xs font-bold text-gray-500 tabular-nums">{{ item.count.toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700"
                                    :style="{ width: barPct(item.count, data.os) + '%', background: paletteAt(i) }"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── ROW 3: Referrers + Top Pages ───────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

            <!-- Referrer Sources -->
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Sumber Traffic</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Referrer & traffic sources</p>
                </div>
                <div class="p-5 space-y-3">
                    <div v-if="loading">
                        <div v-for="i in 5" :key="i" class="h-8 bg-gray-100 rounded-lg animate-pulse mb-2"></div>
                    </div>
                    <template v-else>
                        <div v-for="(item, i) in (data.referrers || []).slice(0, 8)" :key="item.referrer_source"
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                :style="{ background: paletteAt(i) + '18', border: '1px solid ' + paletteAt(i) + '30' }">

                                <!-- FontAwesome untuk mayoritas source -->
                                <font-awesome-icon
                                    v-if="referrerIconType(item.referrer_source) === 'fa'"
                                    :icon="[referrerIcon(item.referrer_source).prefix, referrerIcon(item.referrer_source).icon]"
                                    class="text-sm"
                                    :style="{ color: paletteAt(i) }"
                                />

                                <!-- SVG inline untuk Shopee & Tokopedia -->
                                <svg
                                    v-else
                                    viewBox="0 0 24 24"
                                    class="w-4 h-4"
                                    :style="{ fill: paletteAt(i) }"
                                >
                                    <path :d="referrerSvgPath(item.referrer_source)" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-700 capitalize truncate">{{ item.referrer_source }}</p>
                                <div class="h-1 bg-gray-100 rounded-full overflow-hidden mt-1">
                                    <div class="h-full rounded-full" :style="{ width: barPct(item.count, data.referrers) + '%', background: paletteAt(i) }"></div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-gray-600 tabular-nums shrink-0">{{ item.count.toLocaleString('id-ID') }}</span>
                        </div>
                        <div v-if="!data.referrers?.length" class="text-center text-gray-400 text-sm py-4">Belum ada data</div>
                    </template>
                </div>
            </div>

            <!-- Top Pages -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Halaman Terpopuler</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Berdasarkan total page views</p>
                </div>
                <div v-if="loading" class="p-6 space-y-3">
                    <div v-for="i in 5" :key="i" class="h-10 bg-gray-50 rounded-xl animate-pulse"></div>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[400px]">
                        <thead>
                            <tr class="bg-gray-50/60 border-b border-gray-100">
                                <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">#</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Halaman</th>
                                <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Views</th>
                                <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400 hidden lg:table-cell">Unique</th>
                                <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400 w-32 hidden lg:table-cell">Proporsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="(page, i) in (data.top_pages || [])" :key="page.page"
                                class="hover:bg-gray-50/60 transition-colors duration-150">
                                <td class="px-6 py-3.5">
                                    <div class="w-6 h-6 rounded-md flex items-center justify-center text-[10px] font-bold"
                                        :style="i === 0 ? 'background:#ED1F24;color:white' : 'background:#f3f4f6;color:#6b7280'">
                                        {{ i + 1 }}
                                    </div>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-xs font-mono text-gray-700 bg-gray-100 px-2 py-0.5 rounded-md">{{ page.page }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold text-gray-800 tabular-nums">{{ Number(page.views).toLocaleString('id-ID') }}</td>
                                <td class="px-6 py-3.5 text-right text-gray-500 tabular-nums hidden lg:table-cell">{{ Number(page.unique_visitors).toLocaleString('id-ID') }}</td>
                                <td class="px-6 py-3.5 hidden lg:table-cell">
                                    <div class="flex items-center gap-2 justify-end">
                                        <div class="w-20 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full bg-[#ED1F24]" :style="{ width: barPctArr(page.views, data.top_pages, 'views') + '%' }"></div>
                                        </div>
                                        <span class="text-xs text-gray-400 w-8 text-right tabular-nums">{{ barPctArr(page.views, data.top_pages, 'views') }}%</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!data.top_pages?.length">
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400 text-sm">Belum ada data halaman</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── ROW 4: Geo + Hourly Heatmap ───────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

            <!-- Top Countries -->
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Top Negara</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Berdasarkan visitor</p>
                </div>
                <div class="p-5 space-y-3">
                    <div v-if="loading">
                        <div v-for="i in 5" :key="i" class="h-7 bg-gray-100 rounded animate-pulse mb-2"></div>
                    </div>
                    <template v-else>
                        <div v-for="(item, i) in (data.countries || []).slice(0, 8)" :key="item.country_code"
                            class="flex items-center gap-2">
                            <span class="text-base shrink-0">{{ countryFlag(item.country_code) }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between mb-0.5">
                                    <span class="text-xs font-semibold text-gray-700 truncate">{{ item.country || item.country_code }}</span>
                                    <span class="text-xs font-bold text-gray-500 tabular-nums ml-2">{{ item.count.toLocaleString('id-ID') }}</span>
                                </div>
                                <div class="h-1 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" :style="{ width: barPct(item.count, data.countries) + '%', background: paletteAt(i) }"></div>
                                </div>
                            </div>
                        </div>
                        <div v-if="!data.countries?.length" class="text-center text-gray-400 text-sm py-4">Belum ada data geo</div>
                    </template>
                </div>
            </div>

            <!-- Hourly Heatmap Bar -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Jam Tersibuk</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Distribusi kunjungan per jam (0–23)</p>
                </div>
                <div class="p-6">
                    <div v-if="loading" class="h-32 bg-gray-50 rounded-xl animate-pulse"></div>
                    <apexchart v-else type="bar" height="140" :options="hourlyOptions" :series="hourlySeries" />
                </div>
            </div>
        </div>

        <!-- ───────────────────────── ROW 5: Monthly Trend ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden mb-4">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Tren Bulanan (6 Bulan Terakhir)</h3>
                <p class="text-xs text-gray-400 mt-0.5">Total views & unique visitors per bulan</p>
            </div>
            <div class="p-6">
                <div v-if="loading" class="h-44 bg-gray-50 rounded-xl animate-pulse"></div>
                <apexchart v-else type="bar" height="180" :options="monthlyOptions" :series="monthlySeries" />
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
            <button @click="fetchAll" class="text-xs font-bold text-[#ED1F24] shrink-0 px-3 py-1 rounded-lg border border-red-200 hover:bg-red-50 transition-colors">Coba lagi</button>
        </div>

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

const PALETTE = ['#ED1F24', '#3B82F6', '#22C55E', '#F59E0B', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316']

export default {
    name: 'VisitorStats',
    components: { AdminLayout, apexchart: VueApexCharts },

    data() {
        return {
            loading: true,
            error: null,
            period: '7d',
            customFrom: new Date(Date.now() - 7 * 86400000).toISOString().slice(0, 10),
            customTo:   new Date().toISOString().slice(0, 10),
            data: {
                overview:       {},
                daily_trend:    [],
                monthly_trend:  [],
                top_pages:      [],
                devices:        [],
                browsers:       [],
                os:             [],
                referrers:      [],
                countries:      [],
                cities:         [],
                hourly:         [],
                new_vs_returning: [],
            },
            periods: [
                { label: 'Hari Ini', value: 'today' },
                { label: '7 Hari',   value: '7d' },
                { label: '30 Hari',  value: '30d' },
                { label: 'Custom',   value: 'custom' },
            ],
        }
    },

    computed: {
        // ── KPI Cards ──────────────────────────────────────────────────────────
        kpiCards() {
            const o = this.data.overview
            const fmtTime = s => s >= 60 ? `${Math.floor(s / 60)}m ${s % 60}s` : `${s}s`
            return [
                {
                    label:   'Total Page Views',
                    value:   (o.total_views || 0).toLocaleString('id-ID'),
                    icon:    'eye',
                    accent:  '#ED1F24',
                    iconBg:  'rgba(237,31,36,0.07)',
                    delta:   o.view_delta ?? null,
                    sub:     null,
                },
                {
                    label:   'Unique Visitors',
                    value:   (o.unique_visitors || 0).toLocaleString('id-ID'),
                    icon:    'users',
                    accent:  '#3B82F6',
                    iconBg:  'rgba(59,130,246,0.07)',
                    delta:   o.unique_delta ?? null,
                    sub:     null,
                },
                {
                    label:   'Avg. Time on Page',
                    value:   fmtTime(o.avg_time_on_page || 0),
                    icon:    'clock',
                    accent:  '#22C55E',
                    iconBg:  'rgba(34,197,94,0.07)',
                    delta:   null,
                    sub:     'Per sesi',
                },
                {
                    label:   'Bounce Rate',
                    value:   (o.bounce_rate || 0) + '%',
                    icon:    'arrow-right-from-bracket',
                    accent:  '#F59E0B',
                    iconBg:  'rgba(245,158,11,0.07)',
                    delta:   null,
                    sub:     'Sesi satu halaman',
                },
            ]
        },

        // ── Daily Trend ────────────────────────────────────────────────────────
        dailyTrendSeries() {
            return [
                { name: 'Page Views',      data: this.data.daily_trend.map(d => d.views) },
                { name: 'Unique Visitors', data: this.data.daily_trend.map(d => d.unique_visitors) },
            ]
        },
        dailyTrendOptions() {
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'area' },
                colors: ['#ED1F24', '#3B82F6'],
                fill: { type: 'gradient', gradient: { opacityFrom: 0.15, opacityTo: 0.01 } },
                stroke: { curve: 'smooth', width: [2, 2] },
                xaxis: {
                    categories: this.data.daily_trend.map(d =>
                        new Date(d.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })
                    ),
                    labels: { style: { fontSize: '10px', colors: '#9ca3af' } },
                    axisBorder: { show: false }, axisTicks: { show: false },
                },
                yaxis: { labels: { style: { fontSize: '10px', colors: '#9ca3af' } } },
                dataLabels: { enabled: false },
                legend: { position: 'top', fontSize: '11px', labels: { colors: '#6b7280' }, markers: { radius: 4 } },
                tooltip: { theme: 'light', y: { formatter: v => v.toLocaleString('id-ID') } },
            }
        },

        // ── New vs Returning ───────────────────────────────────────────────────
        newVsReturningSeries() {
            const o = this.data.overview
            return [o.new_visitors || 0, o.returning || 0]
        },
        newVsReturningOptions() {
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'donut' },
                labels: ['New', 'Returning'],
                colors: ['#ED1F24', '#3B82F6'],
                stroke: { width: 0 },
                dataLabels: { enabled: false },
                legend: { position: 'bottom', fontSize: '11px', labels: { colors: '#6b7280' }, markers: { radius: 4 } },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
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
                tooltip: { theme: 'light', y: { formatter: v => v.toLocaleString('id-ID') + ' visitor' } },
            }
        },

        // ── Device ────────────────────────────────────────────────────────────
        deviceSeries() {
            return (this.data.devices || []).map(d => d.count)
        },
        deviceOptions() {
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'donut' },
                labels: (this.data.devices || []).map(d => d.device_type ?? 'Unknown'),
                colors: ['#ED1F24', '#3B82F6', '#22C55E', '#F59E0B'],
                stroke: { width: 0 },
                dataLabels: { enabled: false },
                legend: { position: 'bottom', fontSize: '11px', labels: { colors: '#6b7280' }, markers: { radius: 4 } },
                plotOptions: { pie: { donut: { size: '65%' } } },
                tooltip: { theme: 'light', y: { formatter: v => v.toLocaleString('id-ID') } },
            }
        },

        // ── Hourly ────────────────────────────────────────────────────────────
        hourlySeries() {
            return [{ name: 'Visits', data: (this.data.hourly || []).map(h => h.count) }]
        },
        hourlyOptions() {
            const maxCount = Math.max(...(this.data.hourly || []).map(h => h.count), 1)
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'bar' },
                colors: (this.data.hourly || []).map(h => {
                    const ratio = h.count / maxCount
                    return ratio > 0.7 ? '#ED1F24' : ratio > 0.4 ? '#F87171' : '#FCA5A5'
                }),
                plotOptions: { bar: { distributed: true, columnWidth: '80%', borderRadius: 2 } },
                xaxis: {
                    categories: (this.data.hourly || []).map(h => h.hour + ':00'),
                    labels: {
                        style: { fontSize: '8px', colors: '#9ca3af' },
                        rotate: 0,
                        formatter: (v) => v.split(':')[0],
                    },
                    axisBorder: { show: false }, axisTicks: { show: false },
                },
                yaxis: { labels: { style: { fontSize: '10px', colors: '#9ca3af' } } },
                dataLabels: { enabled: false },
                legend: { show: false },
                tooltip: { theme: 'light', x: { formatter: v => `Pukul ${v}` }, y: { formatter: v => v.toLocaleString('id-ID') + ' visits' } },
            }
        },

        // ── Monthly ───────────────────────────────────────────────────────────
        monthlySeries() {
            return [
                { name: 'Page Views',      data: (this.data.monthly_trend || []).map(m => m.views) },
                { name: 'Unique Visitors', data: (this.data.monthly_trend || []).map(m => m.unique_visitors) },
            ]
        },
        monthlyOptions() {
            return {
                ...CHART_BASE,
                chart: { ...CHART_BASE.chart, type: 'bar' },
                colors: ['#ED1F24', '#3B82F6'],
                plotOptions: { bar: { borderRadius: 3, columnWidth: '55%', grouped: true } },
                xaxis: {
                    categories: (this.data.monthly_trend || []).map(m => {
                        const [y, mo] = m.month.split('-')
                        return new Date(y, mo - 1).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' })
                    }),
                    labels: { style: { fontSize: '10px', colors: '#9ca3af' } },
                    axisBorder: { show: false }, axisTicks: { show: false },
                },
                yaxis: { labels: { style: { fontSize: '10px', colors: '#9ca3af' }, formatter: v => v.toLocaleString('id-ID') } },
                dataLabels: { enabled: false },
                legend: { position: 'top', fontSize: '12px', labels: { colors: '#6b7280' }, markers: { radius: 4 } },
                tooltip: { theme: 'light', y: { formatter: v => v.toLocaleString('id-ID') } },
            }
        },
    },

    methods: {
        async fetchAll() {
            this.loading = true
            this.error   = null
            try {
                const params = { period: this.period }
                if (this.period === 'custom') {
                    params.from = this.customFrom
                    params.to   = this.customTo
                }
                const res = await axios.get('/admin/visitor-stats', { params })
                this.data = res.data
            } catch (err) {
                this.error = 'Gagal memuat data statistik. ' + (err.response?.data?.message || err.message)
            } finally {
                this.loading = false
            }
        },

        setPeriod(val) {
            this.period = val
            if (val !== 'custom') this.fetchAll()
        },

        paletteAt(i) {
            return PALETTE[i % PALETTE.length]
        },

        barPct(count, arr) {
            const max = Math.max(...(arr || []).map(d => d.count ?? 0), 1)
            return Math.round((count / max) * 100)
        },

        barPctArr(val, arr, key) {
            const max = Math.max(...(arr || []).map(d => d[key] ?? 0), 1)
            return Math.round((val / max) * 100)
        },

        referrerIconType(source) {
            return ['shopee', 'tokopedia'].includes(source?.toLowerCase()) ? 'svg' : 'fa'
        },

        referrerIcon(source) {
            const map = {
                direct:     { prefix: 'fas', icon: 'link' },
                google:     { prefix: 'fab', icon: 'google' },
                bing:       { prefix: 'fas', icon: 'magnifying-glass' },
                duckduckgo: { prefix: 'fas', icon: 'magnifying-glass' },
                facebook:   { prefix: 'fab', icon: 'facebook' },
                instagram:  { prefix: 'fab', icon: 'instagram' },
                twitter:    { prefix: 'fab', icon: 'twitter' },
                tiktok:     { prefix: 'fab', icon: 'tiktok' },
                youtube:    { prefix: 'fab', icon: 'youtube' },
                whatsapp:   { prefix: 'fab', icon: 'whatsapp' },
                lazada:     { prefix: 'fas', icon: 'box' },
                internal:   { prefix: 'fas', icon: 'house' },
                other:      { prefix: 'fas', icon: 'globe' },
                shopee:     null,
                tokopedia:  null,
            }
            return map[source?.toLowerCase()] ?? { prefix: 'fas', icon: 'globe' }
        },

        referrerSvgPath(source) {
            const paths = {
                shopee: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z',
                tokopedia: 'M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm-.5 5h1v1.5c1.38.31 2.5 1.38 2.5 2.75 0 .41-.34.75-.75.75s-.75-.34-.75-.75c0-.69-.56-1.25-1.25-1.25H12c-.69 0-1.25.56-1.25 1.25 0 .6.43 1.08 1 1.22l2 .5c1.17.29 2 1.35 2 2.53 0 1.37-1.12 2.44-2.5 2.75V19h-1v-1.5C10.87 17.19 9.75 16.12 9.75 14.75c0-.41.34-.75.75-.75s.75.34.75.75c0 .69.56 1.25 1.25 1.25h.5c.69 0 1.25-.56 1.25-1.25 0-.6-.43-1.08-1-1.22l-2-.5C10.08 13.54 9.25 12.48 9.25 11.3c0-1.37 1.12-2.44 2.25-2.75V7z',
            }
            return paths[source?.toLowerCase()] ?? ''
        },

        countryFlag(code) {
            if (!code || code.length !== 2) return '🌍'
            const offset = 127397
            return String.fromCodePoint(...[...code.toUpperCase()].map(c => c.charCodeAt(0) + offset))
        },
    },

    mounted() {
        document.title = 'Visitor Statistics - Admin'
        this.fetchAll()

        // Auto-refresh setiap 5 menit
        this._interval = setInterval(this.fetchAll, 5 * 60 * 1000)
    },

    beforeUnmount() {
        if (this._interval) clearInterval(this._interval)
    },
}
</script>