<!-- src/views/admin/FeaturedProducts.vue -->
<template>
    <AdminLayout title="Featured Products">

        <!-- ───────────────────────── HEADER ───────────────────────── -->
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Featured Products</h1>
                        <span class="text-[10px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 border border-gray-200">Homepage</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Atur produk unggulan yang tampil di homepage · drag untuk mengubah urutan</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <!-- Save Order -->
                <button
                    v-if="isDirty"
                    @click="saveOrder"
                    :disabled="saving"
                    class="flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:border-emerald-300 hover:bg-emerald-50 transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg v-if="saving" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ saving ? 'Menyimpan...' : 'Simpan Urutan' }}
                </button>
                <div class="w-px h-6 bg-gray-200" v-if="isDirty"></div>
                <!-- Tambah Produk -->
                <button
                    @click="openAddModal"
                    class="flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all duration-150 shadow-sm shadow-red-200 hover:shadow-md hover:shadow-red-200 active:scale-95"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Produk
                </button>
            </div>
        </div>

        <!-- ───────────────────────── INFO BANNER ───────────────────────── -->
        <div class="bg-blue-50 border border-blue-100 rounded-xl px-5 py-4 mb-6 flex items-start gap-3">
            <div class="w-7 h-7 rounded-lg bg-blue-100 border border-blue-200 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-xs text-blue-600 font-medium leading-relaxed">
                Produk di bawah tampil di bagian <span class="font-bold">"Top Products"</span> homepage.
                Drag untuk mengubah urutan. Slot kosong akan diisi otomatis dari produk terlaris.
            </p>
        </div>

        <!-- ───────────────────────── PRODUCT LIST ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                        {{ featured.length }} Produk Manual
                    </span>
                </div>
                <div v-if="isDirty" class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <span class="text-xs text-amber-500 font-medium">Urutan belum disimpan</span>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="featured.length === 0 && !loading" class="py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-500 mb-1">Belum ada produk featured</p>
                <p class="text-xs text-gray-400">Tambahkan produk untuk dipromosikan di homepage</p>
                <button
                    @click="openAddModal"
                    class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all shadow-sm shadow-red-200"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Produk Pertama
                </button>
            </div>

            <!-- Skeleton -->
            <div v-else-if="loading" class="divide-y divide-gray-50">
                <div v-for="i in 4" :key="i" class="px-6 py-4 flex items-center gap-4 animate-pulse">
                    <div class="w-4 h-4 bg-gray-100 rounded"/>
                    <div class="w-6 h-6 bg-gray-100 rounded-full"/>
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex-shrink-0"/>
                    <div class="flex-1">
                        <div class="h-3.5 bg-gray-100 rounded w-48 mb-2"/>
                        <div class="h-3 bg-gray-100 rounded w-24"/>
                    </div>
                    <div class="w-16 h-6 bg-gray-100 rounded-lg"/>
                </div>
            </div>

            <!-- Draggable list -->
            <VueDraggable
                v-else
                v-model="featured"
                :animation="150"
                handle=".drag-handle"
                class="divide-y divide-gray-50"
                @update="onReorder"
            >
                <div
                    v-for="(item, index) in featured"
                    :key="item.id"
                    class="group px-6 py-4 flex items-center gap-4 hover:bg-gray-50/60 transition-colors duration-150"
                >
                    <!-- Drag handle -->
                    <svg class="drag-handle w-4 h-4 text-gray-300 group-hover:text-gray-400 cursor-grab active:cursor-grabbing flex-shrink-0 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16"/>
                    </svg>

                    <!-- Rank badge -->
                    <span class="w-6 h-6 rounded-md flex items-center justify-center text-[10px] font-bold flex-shrink-0"
                        :style="index === 0 ? 'background:#ED1F24;color:white' : index === 1 ? 'background:#f3f4f6;color:#374151' : 'background:#f9fafb;color:#9ca3af'"
                    >{{ index + 1 }}</span>

                    <!-- Product image -->
                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">
                        <img v-if="item.product.photo" :src="photoUrl(item.product.photo)" :alt="item.product.name"
                             class="w-full h-full object-cover"/>
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ item.product.name }}</p>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs text-gray-500 font-medium">{{ formatPrice(item.product.sell_price) }}</span>
                            <span class="text-gray-300">·</span>
                            <span class="text-[10px] font-medium text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-md">{{ item.product.category }}</span>
                        </div>
                    </div>

                    <!-- Remove -->
                    <button
                        @click="removeFeatured(item.id)"
                        class="opacity-0 group-hover:opacity-100 flex items-center gap-1.5 text-xs font-semibold text-red-500 border border-red-100 hover:border-red-200 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-all duration-150"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Hapus
                    </button>
                </div>
            </VueDraggable>
        </div>

        <!-- ───────────────────────── ADD PRODUCT MODAL ───────────────────────── -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showModal"
                     class="fixed inset-0 z-50 flex items-center justify-center p-4"
                     style="background: rgba(0,0,0,0.4); backdrop-filter: blur(4px);"
                     @click.self="showModal = false">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-gray-200/80">

                        <!-- Modal Header -->
                        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-gray-800">Pilih Produk Featured</h3>
                                    <p class="text-xs text-gray-400 mt-0.5">Cari dan pilih produk untuk dipromosikan</p>
                                </div>
                            </div>
                            <button @click="showModal = false"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Search -->
                        <div class="px-6 pt-5 pb-3">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                                </svg>
                                <input v-model="productSearch" type="text" placeholder="Cari nama produk..."
                                       class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 placeholder-gray-400 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all"/>
                            </div>
                        </div>

                        <!-- Product List -->
                        <div class="px-6 pb-4">
                            <div class="max-h-72 overflow-y-auto rounded-xl border border-gray-200/80 divide-y divide-gray-50">
                                <div v-if="filteredAvailable.length === 0" class="py-10 text-center">
                                    <svg class="w-8 h-8 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                                    </svg>
                                    <p class="text-sm text-gray-400 font-medium">Tidak ada produk tersedia</p>
                                </div>
                                <div
                                    v-for="product in filteredAvailable"
                                    :key="product.id"
                                    @click="toggleSelect(product)"
                                    class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50/80 cursor-pointer transition-colors duration-150"
                                    :class="selectedIds.has(product.id) ? 'bg-[#ED1F24]/4' : ''"
                                >
                                    <!-- Image -->
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">
                                        <img v-if="product.photo_1 || product.photo"
                                             :src="photoUrl(product.photo_1 ?? product.photo)"
                                             :alt="product.name"
                                             class="w-full h-full object-cover"/>
                                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate">{{ product.name }}</p>
                                        <p class="text-xs text-gray-400">{{ formatPrice(product.sell_price) }}</p>
                                    </div>
                                    <!-- Checkmark -->
                                    <div v-if="selectedIds.has(product.id)"
                                         class="w-5 h-5 rounded-full bg-[#ED1F24] flex items-center justify-center flex-shrink-0 shadow-sm shadow-red-200">
                                        <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div v-else class="w-5 h-5 rounded-full border-2 border-gray-200 flex-shrink-0"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span v-if="selectedIds.size > 0"
                                      class="text-xs font-bold px-2 py-0.5 rounded-md bg-[#ED1F24]/8 text-[#ED1F24] border border-[#ED1F24]/15">
                                    {{ selectedIds.size }} dipilih
                                </span>
                                <span v-else class="text-xs text-gray-400">Pilih produk untuk ditambahkan</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="showModal = false"
                                        class="text-xs font-semibold text-gray-500 border border-gray-200 px-4 py-2 rounded-lg hover:border-gray-300 hover:bg-white transition-all">
                                    Batal
                                </button>
                                <button @click="confirmAdd"
                                        :disabled="selectedIds.size === 0 || saving"
                                        class="flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all shadow-sm shadow-red-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                                    <svg v-if="saving" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                    </svg>
                                    {{ saving ? 'Menambahkan...' : 'Tambahkan' }}
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </Transition>
        </Teleport>

    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { VueDraggable } from 'vue-draggable-plus'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import axiosInstance from '../../axios'

const featured      = ref([])
const allProducts   = ref([])
const loading       = ref(true)
const saving        = ref(false)
const isDirty       = ref(false)
const showModal     = ref(false)
const productSearch = ref('')
const selectedIds   = ref(new Set())

const formatPrice = (price) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)

const featuredProductIds = computed(() => new Set(featured.value.map(f => f.product_id)))

const filteredAvailable = computed(() => {
    const q = productSearch.value.toLowerCase()
    return allProducts.value
        .filter(p => !featuredProductIds.value.has(p.id))
        .filter(p => !q || p.name.toLowerCase().includes(q))
})

async function fetchFeatured() {
    loading.value = true
    try {
        const { data } = await axiosInstance.get('/admin/featured-products')
        featured.value = data.data
    } finally {
        loading.value = false
    }
}

async function fetchAllProducts() {
    const { data } = await axiosInstance.get('/products')
    allProducts.value = data.data ?? data
}

function onReorder() {
    isDirty.value = true
}

async function saveOrder() {
    saving.value = true
    try {
        const payload = { ids: featured.value.map(f => f.id) }
        await axiosInstance.patch('/admin/featured-products/reorder', payload)
        isDirty.value = false
    } finally {
        saving.value = false
    }
}

async function removeFeatured(featuredId) {
    featured.value = featured.value.filter(f => f.id !== featuredId)
    await syncToBackend()
}

function toggleSelect(product) {
    const set = new Set(selectedIds.value)
    set.has(product.id) ? set.delete(product.id) : set.add(product.id)
    selectedIds.value = set
}

async function confirmAdd() {
    saving.value = true
    try {
        const currentIds = featured.value.map(f => f.product_id)
        const newIds = [...selectedIds.value]
        await axiosInstance.post('/admin/featured-products', {
            product_ids: [...currentIds, ...newIds]
        })
        await fetchFeatured()
        showModal.value = false
        selectedIds.value = new Set()
        productSearch.value = ''
    } finally {
        saving.value = false
    }
}

async function syncToBackend() {
    await axiosInstance.post('/admin/featured-products', {
        product_ids: featured.value.map(f => f.product_id)
    })
}

function photoUrl(path) {
    if (!path) return null
    if (path.startsWith('http://') || path.startsWith('https://')) return path
    const base = import.meta.env.VITE_APP_URL || window.location.origin
    return `${base}/storage/${path}`
}

function openAddModal() {
    selectedIds.value = new Set()
    productSearch.value = ''
    showModal.value = true
}

onMounted(() => {
    document.title = 'Featured Products - Two Brothers Vape System'
    const favicon = document.querySelector("link[rel='icon']")
    if (favicon) favicon.href = '/storage/logos/tbgroup.png'
    fetchFeatured()
    fetchAllProducts()
})
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>