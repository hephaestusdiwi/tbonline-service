<template>
    <AdminLayout title="Laporan KPI Staff">

        <!-- ───────────────────────── HEADER ───────────────────────── -->
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Laporan KPI Staff</h1>
                        <span class="text-[10px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 border border-gray-200">Live Chat</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Rekap performa respons chat per staff
                        <span class="text-gray-300 mx-1">·</span>
                        <span class="font-medium text-gray-500">{{ periodLabel }}</span>
                        <span v-if="range.from" class="text-gray-400">({{ fmtDate(range.from) }} – {{ fmtDate(range.to) }})</span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="exportExcel"
                    :disabled="exporting || loading || !rows.length"
                    class="flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-100 transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg :class="['w-3.5 h-3.5', exporting && 'animate-spin']" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path v-if="!exporting" stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H8a2 2 0 01-2-2V5a2 2 0 012-2h6l6 6v11a2 2 0 01-2 2z"/>
                        <path v-else stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Export Excel
                </button>

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

                <div v-if="filters.period === 'custom'" class="flex items-end gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Dari</label>
                        <input v-model="filters.date_from" type="date"
                            class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all bg-white" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sampai</label>
                        <input v-model="filters.date_to" type="date"
                            class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all bg-white" />
                    </div>
                </div>

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
        <div class="grid grid-cols-2 xl:grid-cols-6 gap-3 mb-6">
            <template v-if="loading">
                <div v-for="i in 6" :key="i" class="bg-white rounded-xl p-5 animate-pulse h-28 border border-gray-200/80 shadow-sm"></div>
            </template>
            <template v-else>
                <div
                    v-for="card in kpiCards"
                    :key="card.label"
                    class="group bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md hover:border-gray-300/80 transition-all duration-200 overflow-hidden relative"
                >
                    <div class="absolute top-0 left-0 right-0 h-0.5 rounded-t-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300" :style="{ background: card.iconColor }"></div>
                    <div class="p-4 flex flex-col justify-between h-full">
                        <div class="flex items-start justify-between mb-3">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 leading-tight pr-2">{{ card.label }}</span>
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 border" :style="{ background: card.iconBg, borderColor: card.iconColor + '25' }">
                                <font-awesome-icon :icon="card.icon" :style="{ color: card.iconColor }" class="text-xs" />
                            </div>
                        </div>
                        <p class="text-lg font-bold leading-tight" :class="card.valueColor || 'text-gray-900'">{{ card.value }}</p>
                    </div>
                </div>
            </template>
        </div>

        <!-- ───────────────────────── PERINGKAT STAFF ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm mb-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-start justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Peringkat Beban Kerja</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Berdasarkan jumlah sesi yang ditangani</p>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#ED1F24]/8 text-[#ED1F24] border border-[#ED1F24]/15 uppercase tracking-wider">
                    {{ rows.length }} Staff
                </span>
            </div>
            <div class="p-6">
                <div v-if="loading" class="space-y-5">
                    <div v-for="i in 4" :key="i" class="h-8 bg-gray-100 rounded-lg animate-pulse"></div>
                </div>
                <div v-else class="space-y-5">
                    <div v-for="(r, i) in rows" :key="r.agent_id">
                        <div class="flex items-center gap-3 mb-1.5">
                            <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] font-bold shrink-0"
                                :style="i === 0 ? 'background:#ED1F24;color:white' : i === 1 ? 'background:#f3f4f6;color:#374151' : 'background:#f9fafb;color:#9ca3af'"
                            >#{{ i + 1 }}</span>
                            <span class="text-sm font-semibold text-gray-700 flex-1 truncate">{{ r.agent_name }}</span>
                            <div class="text-right shrink-0">
                                <span class="text-sm font-bold text-gray-800">{{ r.total_sessions }}</span>
                                <span class="text-xs text-gray-400 ml-1.5">sesi</span>
                            </div>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700"
                                :style="{ width: barWidth(r.total_sessions) + '%', background: COLORS[i % COLORS.length] }"></div>
                        </div>
                    </div>
                    <div v-if="!rows.length" class="text-center text-gray-400 text-sm py-8">Belum ada data di periode ini</div>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── TABEL DETAIL ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden mb-4">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Detail KPI per Staff</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ periodLabel }}</p>
            </div>
            <div v-if="loading" class="p-6 space-y-3">
                <div v-for="i in 5" :key="i" class="h-12 bg-gray-50 rounded-xl animate-pulse"></div>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm min-w-[720px]">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Staff</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400">Total Sesi</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400">Waktu Ambil Chat</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400">First Response</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400">Avg Response</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="r in rows" :key="r.agent_id" class="hover:bg-gray-50/60 transition-colors duration-150">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center text-[#ED1F24] text-xs font-bold shrink-0">
                                        {{ r.agent_name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="font-medium text-gray-700 text-sm">{{ r.agent_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-center font-bold text-gray-800 tabular-nums">{{ r.total_sessions }}</td>
                            <td class="px-6 py-3.5 text-center">
                                <span :class="durationBadgeClass(r.avg_time_to_assign_seconds, 120, 300)">{{ fmtDuration(r.avg_time_to_assign_seconds) }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span :class="durationBadgeClass(r.avg_first_response_seconds, 60, 180)">{{ fmtDuration(r.avg_first_response_seconds) }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span :class="durationBadgeClass(r.avg_response_seconds, 60, 180)">{{ fmtDuration(r.avg_response_seconds) }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <span v-if="r.avg_rating" class="font-semibold text-amber-500 text-sm">⭐ {{ r.avg_rating }}</span>
                                <span v-else class="text-gray-300 italic text-xs">-</span>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span class="text-gray-400 text-sm font-medium">Tidak ada data KPI dalam periode ini</span>
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
import axios from '@/axios.js'

const COLORS = ['#ED1F24', '#3B82F6', '#22C55E', '#F59E0B', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316']

export default {
    name: 'ChatStaffReport',
    components: { AdminLayout },

    data() {
        return {
            COLORS,
            loading: true,
            error:   null,
            rows:    [],
            summary: {},
            range:   {},
            exporting: false,

            filters: {
                period:    'today',
                date_from: '',
                date_to:   '',
            },

            periodPresets: [
                { label: 'Hari Ini',   value: 'today' },
                { label: 'Kemarin',    value: 'yesterday' },
                { label: 'Minggu Ini', value: 'week' },
                { label: 'Bulan Ini',  value: 'month' },
                { label: 'Custom',     value: 'custom' },
            ],
        }
    },

    computed: {
        periodLabel() {
            return this.periodPresets.find(p => p.value === this.filters.period)?.label || 'Custom'
        },

        kpiCards() {
            const s = this.summary
            return [
                {
                    label: 'Total Sesi', value: s.total_sessions ?? 0,
                    icon: 'headset', iconBg: 'rgba(237,31,36,0.07)', iconColor: '#ED1F24', valueColor: 'text-[#ED1F24]',
                },
                {
                    label: 'Staff Aktif', value: s.total_staff ?? 0,
                    icon: 'users', iconBg: 'rgba(59,130,246,0.07)', iconColor: '#3B82F6',
                },
                {
                    label: 'Avg Waktu Ambil', value: this.fmtDuration(s.avg_time_to_assign_seconds),
                    icon: 'stopwatch', iconBg: 'rgba(245,158,11,0.07)', iconColor: '#F59E0B',
                },
                {
                    label: 'Avg First Response', value: this.fmtDuration(s.avg_first_response_seconds),
                    icon: 'bolt', iconBg: 'rgba(139,92,246,0.07)', iconColor: '#8B5CF6',
                },
                {
                    label: 'Avg Response', value: this.fmtDuration(s.avg_response_seconds),
                    icon: 'reply', iconBg: 'rgba(20,184,166,0.07)', iconColor: '#14B8A6',
                },
                {
                    label: 'Avg Rating', value: s.avg_rating ? `⭐ ${s.avg_rating}` : '-',
                    icon: 'star', iconBg: 'rgba(34,197,94,0.07)', iconColor: '#22C55E',
                },
            ]
        },
    },

    methods: {
        async fetchReport() {
            this.loading = true
            this.error   = null
            try {
                const params = this.filters.period === 'custom'
                    ? { from: this.filters.date_from, to: this.filters.date_to }
                    : { preset: this.filters.period }

                const { data } = await axios.get('/chat-admin/report/staff', { params })
                this.rows    = data.data    || []
                this.summary = data.summary || {}
                this.range   = data.range   || {}
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

        barWidth(value) {
            const max = Math.max(...this.rows.map(r => r.total_sessions), 1)
            return Math.round((value / max) * 100)
        },

        durationBadgeClass(seconds, goodUnder, warnUnder) {
            const base = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold tabular-nums border'
            if (seconds === null || seconds === undefined) return `${base} bg-gray-100 text-gray-400 border-gray-200`
            if (seconds <= goodUnder) return `${base} bg-emerald-50 text-emerald-600 border-emerald-100`
            if (seconds <= warnUnder) return `${base} bg-amber-50 text-amber-600 border-amber-100`
            return `${base} bg-red-50 text-red-500 border-red-100`
        },

        fmtDuration(seconds) {
            if (seconds === null || seconds === undefined) return '-'
            if (seconds < 60) return `${seconds}d`
            const m = Math.floor(seconds / 60), s = seconds % 60
            if (m < 60) return `${m}m ${s}d`
            const h = Math.floor(m / 60)
            return `${h}j ${m % 60}m`
        },

        fmtDate(d) {
            if (!d) return '-'
            return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        },

        async exportExcel() {
            this.exporting = true
            try {
                const params = this.filters.period === 'custom'
                    ? { from: this.filters.date_from, to: this.filters.date_to }
                    : { preset: this.filters.period }

                const response = await axios.get('/chat-admin/report/staff/export-excel', {
                    params,
                    responseType: 'blob',
                })

                const blob = new Blob([response.data], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                })
                const url  = window.URL.createObjectURL(blob)
                const link = document.createElement('a')
                link.href = url
                link.download = `laporan-kpi-staff-${this.filters.period}-${Date.now()}.xlsx`
                document.body.appendChild(link)
                link.click()
                link.remove()
                window.URL.revokeObjectURL(url)
            } catch (err) {
                this.error = 'Gagal export laporan. ' + (err.response?.data?.message || err.message)
            } finally {
                this.exporting = false
            }
        },
    },

    mounted() {
        document.title = 'Laporan KPI Staff - TB Store'
        const today = new Date().toISOString().slice(0, 10)
        this.filters.date_from = today
        this.filters.date_to   = today
        this.fetchReport()
    },
}
</script>