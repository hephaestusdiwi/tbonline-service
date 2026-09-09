<template>
    <AdminLayout title="Flash Sale Management">

        <!-- ═══════════════════════════════════════════
             HERO HEADER
        ═══════════════════════════════════════════ -->
        <div class="relative mb-6 rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full opacity-10" style="background: white;"></div>
            <div class="absolute -bottom-10 -right-24 w-64 h-64 rounded-full opacity-5" style="background: white;"></div>

            <div class="relative px-7 py-5">
                <p class="text-red-200 text-xs font-semibold tracking-widest uppercase mb-1">Content Management</p>
                <h1 class="text-2xl font-bold text-white tracking-tight">⚡ Flash Sale Management</h1>
                <p class="text-red-200 text-xs mt-1.5">Atur section Flash Sale di halaman utama</p>
            </div>
        </div>

        <div v-if="loading" class="text-center py-16 text-sm text-gray-400">Memuat...</div>

        <template v-else>
            <!-- ═══════ Settings: On/Off + Countdown ═══════ -->
            <div class="bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden mb-5">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800">Pengaturan Flash Sale</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Nyala/matiin section, dan atur kapan countdown-nya berakhir</p>
                </div>

                <div class="p-5">
                    <div v-if="settingsError" class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-500 px-3 py-2.5 rounded-xl text-xs mb-4">
                        {{ settingsError }}
                    </div>

                    <div class="flex flex-wrap items-center gap-6">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Status</label>
                            <button @click="settings.is_active = !settings.is_active"
                                    class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2.5 rounded-xl border transition-all"
                                    :class="settings.is_active
                                        ? 'bg-emerald-50 border-emerald-200 text-emerald-600 hover:bg-emerald-100'
                                        : 'bg-gray-100 border-gray-200 text-gray-400 hover:bg-gray-200'">
                                <span class="w-2 h-2 rounded-full" :class="settings.is_active ? 'bg-emerald-400' : 'bg-gray-300'"></span>
                                {{ settings.is_active ? 'Aktif — tampil di homepage' : 'Nonaktif — disembunyikan' }}
                            </button>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Berakhir Pada</label>
                            <input v-model="settingsEndsAtLocal" type="datetime-local"
                                   class="bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 focus:outline-none focus:border-[#ED1F24] transition-colors" />
                            <p class="text-[10px] text-gray-400 mt-1.5">
                                Countdown di homepage dihitung mundur ke waktu ini. Kalau sudah lewat, section otomatis hilang.
                            </p>
                        </div>
                    </div>

                    <button @click="saveSettings" :disabled="savingSettings"
                            class="mt-5 flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition shadow-sm">
                        <svg v-if="savingSettings" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                        </svg>
                        {{ savingSettings ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                    </button>
                </div>
            </div>

            <!-- ═══════ Product List ═══════ -->
            <div class="bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Produk Flash Sale</h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Drag ikon ⠿ buat urutin.
                            <span v-if="items.length > 0" class="font-semibold" :class="activeDiscountCount > 0 ? 'text-emerald-600' : 'text-amber-500'">
                                {{ activeDiscountCount }}/{{ items.length }} produk punya harga diskon aktif.
                            </span>
                        </p>
                    </div>
                    <button @click="saveProducts" :disabled="savingProducts"
                            class="flex items-center gap-2 bg-[#ED1F24] hover:bg-[#C81A1E] disabled:opacity-40 text-white text-xs font-semibold px-4 py-2 rounded-xl transition shadow-sm">
                        <svg v-if="savingProducts" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                        </svg>
                        {{ savingProducts ? 'Menyimpan...' : 'Simpan Daftar Produk' }}
                    </button>
                </div>

                <!-- Search & add -->
                <div class="p-5 border-b border-gray-100">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input v-model="searchQuery" @input="onSearchInput" type="text" placeholder="Cari produk buat ditambahin..."
                               class="w-full pl-9 pr-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:border-[#ED1F24] transition-colors" />
                    </div>

                    <div v-if="searchResults.length > 0" class="mt-2 border border-gray-100 rounded-xl divide-y divide-gray-50 max-h-64 overflow-y-auto">
                        <div v-for="p in searchResults" :key="p.id"
                             class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50/60">
                            <div class="w-10 h-10 rounded-lg overflow-hidden border border-gray-200 bg-gray-100 flex-shrink-0">
                                <img v-if="p.photo_1" :src="photoUrl(p.photo_1)" class="w-full h-full object-cover" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-700 truncate">{{ p.name }}</p>
                                <p class="text-[11px] text-gray-400">{{ formatPrice(p.sell_price) }} · Stok {{ p.stock_qty }}</p>
                            </div>
                            <button @click="addProduct(p)"
                                    :disabled="items.some(i => i.product_id === p.id)"
                                    class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-[#ED1F24]/20 text-[#ED1F24] hover:bg-red-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all flex-shrink-0">
                                {{ items.some(i => i.product_id === p.id) ? 'Ditambahkan' : '+ Tambah' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Current list -->
                <div ref="sortableList" class="divide-y divide-gray-50">
                    <div v-if="items.length === 0" class="px-6 py-12 text-center text-sm text-gray-400">
                        Belum ada produk. Cari & tambahin dari kolom di atas.
                    </div>

                    <div v-for="item in items" :key="item.product_id" :data-id="item.product_id"
                         class="flex items-center gap-3 px-4 py-3 group">
                        <div class="fs-drag-handle flex items-center justify-center w-7 h-7 rounded-lg cursor-grab text-gray-300 hover:text-gray-500 hover:bg-gray-100 transition-all flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="5" r="1.2"/><circle cx="9" cy="12" r="1.2"/><circle cx="9" cy="19" r="1.2"/><circle cx="15" cy="5" r="1.2"/><circle cx="15" cy="12" r="1.2"/><circle cx="15" cy="19" r="1.2"/></svg>
                        </div>

                        <div class="w-12 h-12 rounded-lg overflow-hidden border border-gray-200 bg-gray-100 flex-shrink-0">
                            <img v-if="item.photo" :src="photoUrl(item.photo)" class="w-full h-full object-cover" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-gray-700 truncate">{{ item.name }}</p>
                                <span v-if="hasActiveDiscount(item)"
                                      class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">
                                    -{{ discountPercent(item) }}%
                                </span>
                                <span v-else
                                      class="flex-shrink-0 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-400 border border-gray-200">
                                    Belum ada diskon
                                </span>
                            </div>
                            <p class="text-[11px] text-gray-400">Harga normal: {{ formatPrice(item.sell_price) }}</p>
                        </div>

                        <div class="w-40 flex-shrink-0">
                            <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Harga Flash Sale</label>
                            <input v-model.number="item.flash_price" type="number" min="0" placeholder="Kosongin = harga normal"
                                   class="w-full bg-gray-50/50 border rounded-lg px-3 py-1.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:border-[#ED1F24] transition-colors"
                                   :class="hasActiveDiscount(item) ? 'border-emerald-200' : 'border-gray-200'" />
                            <p v-if="item.flash_price && Number(item.flash_price) >= item.sell_price" class="text-[10px] text-amber-500 mt-1">
                                ⚠ Sama/lebih mahal dari harga normal
                            </p>
                        </div>

                        <button @click="removeProduct(item.product_id)"
                                class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </AdminLayout>
</template>

<script>
import AdminLayout from '../../components/admin/AdminLayout.vue'
import axios from '../../axios.js'
import Sortable from 'sortablejs'

export default {
    name: 'FlashSale',
    components: { AdminLayout },
    data() {
        return {
            loading: true,
            settings: { is_active: false, ends_at: null },
            settingsEndsAtLocal: '',
            settingsError: '',
            savingSettings: false,

            items: [],
            savingProducts: false,

            searchQuery: '',
            searchResults: [],
            _searchTimer: null,
        }
    },

    computed: {
        activeDiscountCount() {
            return this.items.filter(i => this.hasActiveDiscount(i)).length
        },
    },

    mounted() {
        document.title = 'Flash Sale - Two Brothers Vape System'
        this.fetchFlashSale()
    },

    methods: {
        async fetchFlashSale() {
            this.loading = true
            try {
                const { data } = await axios.get('/flash-sale')
                this.settings.is_active = data.is_active
                this.settings.ends_at   = data.ends_at
                this.settingsEndsAtLocal = this.toLocalInputValue(data.ends_at)
                this.items = (data.products || []).map(p => ({ ...p }))
                this.$nextTick(() => this.initSortable())
            } catch (e) {
                console.error(e)
            } finally {
                this.loading = false
            }
        },

        // Konversi ISO datetime dari backend ke format yang dimengerti <input type="datetime-local">
        toLocalInputValue(iso) {
            if (!iso) return ''
            const d = new Date(iso)
            const pad = (n) => String(n).padStart(2, '0')
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
        },

        async saveSettings() {
            this.savingSettings = true
            this.settingsError = ''
            try {
                const payload = {
                    is_active: this.settings.is_active,
                    ends_at: this.settingsEndsAtLocal || null,
                }
                await axios.put('/flash-sale/settings', payload)
            } catch (e) {
                this.settingsError = e.response?.data?.message || 'Gagal menyimpan pengaturan.'
            } finally {
                this.savingSettings = false
            }
        },

        initSortable() {
            if (!this.$refs.sortableList) return
            if (this._sortable) this._sortable.destroy()
            this._sortable = Sortable.create(this.$refs.sortableList, {
                animation: 150,
                handle: '.fs-drag-handle',
                ghostClass: 'opacity-30',
                chosenClass: 'bg-red-50',
                onEnd: (evt) => {
                    const moved = this.items.splice(evt.oldIndex, 1)[0]
                    this.items.splice(evt.newIndex, 0, moved)
                }
            })
        },

        onSearchInput() {
            clearTimeout(this._searchTimer)
            const q = this.searchQuery.trim()
            if (!q) { this.searchResults = []; return }
            this._searchTimer = setTimeout(async () => {
                try {
                    const { data } = await axios.get('/flash-sale/products/search', { params: { q } })
                    this.searchResults = data
                } catch (e) { console.error(e) }
            }, 300)
        },

        addProduct(p) {
            if (this.items.some(i => i.product_id === p.id)) return
            this.items.push({
                product_id: p.id,
                name: p.name,
                photo: p.photo_1,
                sell_price: p.sell_price,
                market_price: p.market_price,
                flash_price: null,
            })
            this.$nextTick(() => this.initSortable())
        },

        removeProduct(productId) {
            this.items = this.items.filter(i => i.product_id !== productId)
        },

        async saveProducts() {
            this.savingProducts = true
            try {
                await axios.put('/flash-sale/products', {
                    products: this.items.map((item, index) => ({
                        product_id: item.product_id,
                        flash_price: item.flash_price || null,
                        order: index,
                    }))
                })
                await this.fetchFlashSale()
            } catch (e) {
                console.error(e)
                alert('Gagal menyimpan daftar produk.')
            } finally {
                this.savingProducts = false
            }
        },

        formatPrice(p) {
            if (p === null || p === undefined) return '-'
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p)
        },

        hasActiveDiscount(item) {
            return item.flash_price !== null && item.flash_price !== undefined && item.flash_price !== ''
                && Number(item.flash_price) > 0 && Number(item.flash_price) < item.sell_price
        },
        discountPercent(item) {
            if (!this.hasActiveDiscount(item)) return 0
            return Math.round((1 - Number(item.flash_price) / item.sell_price) * 100)
        },

        photoUrl(path) {
            if (!path) return null
            if (path.startsWith('http://') || path.startsWith('https://')) return path
            return `${import.meta.env.VITE_APP_URL || window.location.origin}/storage/${path}`
        },
    }
}
</script>

<style scoped>
.fs-drag-handle { cursor: grab; }
.fs-drag-handle:active { cursor: grabbing; }
</style>