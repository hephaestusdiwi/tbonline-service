<template>
    <AdminLayout title="Manajemen Komplain">

        <!-- HERO HEADER -->
        <div class="relative mb-6 rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"></div>
            <div class="absolute top-4 right-32 w-20 h-20 rounded-full opacity-10" style="background: white;"></div>

            <div class="relative px-7 py-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">Customer Support</p>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Manajemen Komplain</h1>
                    <p class="text-red-200 text-xs mt-1.5">Pantau &amp; kelola semua komplain dari pelanggan</p>
                </div>

                <!-- Search + Tab filter -->
                <div class="flex flex-col gap-2 items-end">
                    <input
                        v-model="search"
                        @input="debouncedFetch"
                        type="text"
                        placeholder="Cari nama, WA, atau komplain..."
                        class="text-xs px-3 py-2 rounded-xl border border-white/20 bg-white/10 text-white placeholder-red-200 focus:outline-none focus:bg-white/20 w-64"
                    />
                    <div class="flex gap-2 flex-wrap">
                        <button
                            v-for="tab in tabs"
                            :key="tab.value"
                            @click="activeTab = tab.value; fetchComplaints()"
                            :class="[
                                'flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-xl border transition-all',
                                activeTab === tab.value
                                    ? 'border-white/40 bg-white/20 text-white shadow-sm'
                                    : 'border-white/20 bg-white/8 text-red-200 hover:bg-white/15 hover:text-white'
                            ]"
                        >
                            <span
                                v-if="tab.value !== 'all'"
                                :class="['w-1.5 h-1.5 rounded-full', {
                                    'bg-red-300':     tab.value === 'open',
                                    'bg-amber-300':   tab.value === 'in_progress',
                                    'bg-emerald-300': tab.value === 'resolved',
                                }]"
                            ></span>
                            {{ tab.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats strip -->
            <div class="relative border-t border-white/10 px-7 py-3 flex flex-wrap items-center gap-6">
                <div v-for="strip in heroStrips" :key="strip.label" class="flex items-center gap-3">
                    <div>
                        <p class="text-red-200 text-[10px] font-bold uppercase tracking-widest">{{ strip.label }}</p>
                        <p class="text-white text-lg font-bold tabular-nums">{{ strip.value }}</p>
                    </div>
                    <div class="w-px h-8 bg-white/15 last:hidden"></div>
                </div>
            </div>
        </div>

        <!-- LOADING -->
        <div v-if="loading" class="flex justify-center py-24">
            <div class="flex flex-col items-center gap-3">
                <div class="w-8 h-8 border-4 border-[#ED1F24] border-t-transparent rounded-full animate-spin"></div>
                <p class="text-xs text-gray-400 font-medium">Memuat komplain...</p>
            </div>
        </div>

        <!-- EMPTY -->
        <div v-else-if="!complaints?.length"
            class="flex flex-col items-center justify-center py-24 text-gray-400 gap-4 bg-white rounded-xl border border-gray-200/80 shadow-sm">
            <div class="w-16 h-16 rounded-3xl bg-gray-100/80 border border-gray-200/80 flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                </svg>
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-gray-600">Tidak ada komplain</p>
                <p class="text-xs text-gray-400 mt-1">
                    {{ activeTab !== 'all' ? `Tidak ada komplain dengan status "${statusLabel(activeTab)}"` : 'Semua komplain sudah ditangani' }}
                </p>
            </div>
        </div>

        <!-- TABLE -->
        <div v-else class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">No</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Customer</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Komplain</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Status</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Diterima</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Diselesaikan Oleh</th>
                        <th class="px-6 py-3.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr
                        v-for="(complaint, index) in complaints"
                        :key="complaint.id"
                        class="hover:bg-gray-50/80 transition-colors group"
                    >
                        <!-- No -->
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-gray-400 tabular-nums">
                                {{ (pagination.current_page - 1) * 10 + index + 1 }}
                            </span>
                        </td>

                        <!-- Customer -->
                        <td class="px-6 py-4">
                            <p class="text-xs font-semibold text-gray-700">{{ complaint.customer_name ?? '-' }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ complaint.customer_phone ?? '-' }}</p>
                        </td>

                        <!-- Komplain -->
                        <td class="px-6 py-4 max-w-xs">
                            <p class="text-xs text-gray-700 font-medium line-clamp-2 leading-relaxed">{{ complaint.complaint_text }}</p>
                            <button
                                v-if="complaint.resolution_note"
                                @click="viewDetail(complaint)"
                                class="text-[10px] text-[#ED1F24] hover:underline mt-1"
                            >
                                Lihat catatan resolusi →
                            </button>
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4">
                            <span :class="statusBadgeClass(complaint.status)">
                                {{ statusLabel(complaint.status) }}
                            </span>
                        </td>

                        <!-- Diterima -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-xs text-gray-500">{{ formatDate(complaint.created_at) }}</span>
                        </td>

                        <!-- Diselesaikan Oleh -->
                        <td class="px-6 py-4">
                            <template v-if="complaint.resolver">
                                <p class="text-xs font-semibold text-gray-700">{{ complaint.resolver.name }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ formatDate(complaint.resolved_at) }}</p>
                            </template>
                            <span v-else class="text-xs text-gray-300">-</span>
                        </td>

                        <!-- Aksi -->
                        <td class="px-6 py-4">
                            <button
                                @click="openModal(complaint)"
                                class="text-[11px] font-semibold px-3 py-1.5 rounded-xl border border-gray-200 text-gray-600 hover:border-[#ED1F24] hover:text-[#ED1F24] transition-all bg-gray-50/50"
                            >
                                Update
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                <p class="text-xs text-gray-400 font-medium">
                    Menampilkan
                    <span class="font-bold text-gray-600">{{ complaints?.length ?? 0 }}</span>
                    dari
                    <span class="font-bold text-gray-600">{{ pagination?.total ?? 0 }}</span>
                    komplain
                </p>
                <div class="flex gap-2">
                    <button
                        @click="changePage(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-xl border border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Prev
                    </button>
                    <button
                        @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-xl border border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                    >
                        Next
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL UPDATE STATUS -->
        <div v-if="modalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <!-- Modal header -->
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-800">Update Status Komplain</h2>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <!-- Info customer -->
                    <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-3">
                        <div class="w-8 h-8 rounded-full bg-[#ED1F24]/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-700">{{ selectedComplaint?.customer_name ?? 'Tidak diketahui' }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ selectedComplaint?.customer_phone ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Isi komplain -->
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1.5">Isi Komplain</p>
                        <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-700 leading-relaxed border border-gray-100">
                            {{ selectedComplaint?.complaint_text }}
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1.5">Status</p>
                        <select
                            v-model="modalStatus"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold focus:outline-none focus:border-[#ED1F24] focus:ring-1 focus:ring-[#ED1F24]/20 transition-colors"
                        >
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>

                    <!-- Catatan resolusi -->
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1.5">Catatan Resolusi</p>
                        <textarea
                            v-model="modalNote"
                            rows="3"
                            placeholder="Tulis catatan penyelesaian komplain..."
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-[#ED1F24] focus:ring-1 focus:ring-[#ED1F24]/20 transition-colors resize-none"
                        ></textarea>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="px-6 py-4 border-t border-gray-100 flex gap-2 justify-end">
                    <button
                        @click="modalOpen = false"
                        class="px-4 py-2 text-xs font-semibold border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-all"
                    >
                        Batal
                    </button>
                    <button
                        @click="submitUpdate"
                        :disabled="updating"
                        class="px-4 py-2 text-xs font-semibold rounded-xl text-white transition-all disabled:opacity-50"
                        style="background: linear-gradient(135deg, #ED1F24, #B01419)"
                    >
                        {{ updating ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL DETAIL RESOLUSI -->
        <div v-if="detailModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-800">Detail Komplain</h2>
                    <button @click="detailModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Customer</p>
                        <p class="text-xs font-semibold text-gray-700">{{ detailComplaint?.customer_name ?? '-' }}</p>
                        <p class="text-[10px] text-gray-400">{{ detailComplaint?.customer_phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Komplain</p>
                        <p class="text-xs text-gray-700 leading-relaxed">{{ detailComplaint?.complaint_text }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Catatan Resolusi</p>
                        <p class="text-xs text-gray-700 leading-relaxed">{{ detailComplaint?.resolution_note ?? '-' }}</p>
                    </div>
                    <div class="flex gap-6">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Diselesaikan Oleh</p>
                            <p class="text-xs font-semibold text-gray-700">{{ detailComplaint?.resolver?.name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Waktu Selesai</p>
                            <p class="text-xs text-gray-700">{{ detailComplaint?.resolved_at ? formatDate(detailComplaint.resolved_at) : '-' }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                    <button
                        @click="detailModalOpen = false"
                        class="px-4 py-2 text-xs font-semibold border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '../../components/admin/AdminLayout.vue'

const complaints        = ref([])
const loading           = ref(false)
const activeTab         = ref('all')
const search            = ref('')
const pagination        = ref({ current_page: 1, last_page: 1, total: 0 })

const modalOpen         = ref(false)
const selectedComplaint = ref(null)
const modalStatus       = ref('open')
const modalNote         = ref('')
const updating          = ref(false)

const detailModalOpen   = ref(false)
const detailComplaint   = ref(null)

const tabs = [
    { label: 'Semua',       value: 'all'         },
    { label: 'Open',        value: 'open'        },
    { label: 'In Progress', value: 'in_progress' },
    { label: 'Resolved',    value: 'resolved'    },
]

const heroStrips = computed(() => {
    const all        = pagination.value.total ?? 0
    const open       = complaints.value.filter(c => c.status === 'open').length
    const inProgress = complaints.value.filter(c => c.status === 'in_progress').length
    const resolved   = complaints.value.filter(c => c.status === 'resolved').length
    return [
        { label: 'Total Komplain', value: all        },
        { label: 'Open',           value: open       },
        { label: 'In Progress',    value: inProgress },
        { label: 'Resolved',       value: resolved   },
    ]
})

const statusLabel = (status) => ({
    open:        'Open',
    in_progress: 'In Progress',
    resolved:    'Resolved',
}[status] ?? status)

const statusBadgeClass = (status) => ({
    open:        'text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-500 border border-red-100',
    in_progress: 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 border border-amber-100',
    resolved:    'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100',
}[status] ?? 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-400 border border-gray-200')

const formatDate = (date) => new Date(date).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
})

let debounceTimer = null
const debouncedFetch = () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => fetchComplaints(), 400)
}

const fetchComplaints = async (page = 1) => {
    loading.value = true
    try {
        const { data } = await axios.get('/admin/complaints', {
            params: {
                status: activeTab.value !== 'all' ? activeTab.value : undefined,
                search: search.value || undefined,
                page,
            },
        })
        complaints.value = Array.isArray(data.data) ? data.data : []
        pagination.value = {
            current_page: data.current_page ?? 1,
            last_page:    data.last_page    ?? 1,
            total:        data.total        ?? 0,
        }
    } catch (e) {
        console.error(e)
        complaints.value = []
    } finally {
        loading.value = false
    }
}

const openModal = (complaint) => {
    selectedComplaint.value = complaint
    modalStatus.value       = complaint.status
    modalNote.value         = complaint.resolution_note ?? ''
    modalOpen.value         = true
}

const submitUpdate = async () => {
    updating.value = true
    try {
        const { data } = await axios.patch(`/admin/complaints/${selectedComplaint.value.id}/status`, {
            status:          modalStatus.value,
            resolution_note: modalNote.value,
        })
        const idx = complaints.value.findIndex(c => c.id === data.id)
        if (idx !== -1) complaints.value[idx] = data
        modalOpen.value = false
    } catch (e) {
        console.error(e)
    } finally {
        updating.value = false
    }
}

const viewDetail = (complaint) => {
    detailComplaint.value = complaint
    detailModalOpen.value = true
}

const changePage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return
    fetchComplaints(page)
}

onMounted(() => fetchComplaints())
</script>