<template>
    <AdminLayout title="Product Management">

        <!-- ───────────────────────── HEADER ───────────────────────── -->
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Product Catalog</h1>
                        <span class="text-[10px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 border border-gray-200">Catalog</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Kelola produk dan katalog toko Anda</p>
                </div>
            </div>

            <!-- Stats + Actions -->
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Stats Pills -->
                <div class="hidden sm:flex items-center gap-0 bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-4 py-2.5 text-center border-r border-gray-100">
                        <p class="text-base font-bold text-gray-900 tabular-nums">{{ stats.total }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total</p>
                    </div>
                    <div class="px-4 py-2.5 text-center border-r border-gray-100">
                        <p class="text-base font-bold text-emerald-600 tabular-nums">{{ stats.publishedCount }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Published</p>
                    </div>
                    <div class="px-4 py-2.5 text-center border-r border-gray-100">
                        <p class="text-base font-bold text-amber-500 tabular-nums">{{ stats.lowStockCount }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Low Stock</p>
                    </div>
                    <div class="px-4 py-2.5 text-center">
                        <p class="text-base font-bold text-blue-500 tabular-nums">{{ stats.categoryCount }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kategori</p>
                    </div>
                </div>

                <div class="w-px h-8 bg-gray-200 hidden sm:block"></div>

                <!-- Import -->
                <button @click="openImportModal"
                    class="group flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg border border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-700 transition-all duration-150">
                    <svg class="w-3.5 h-3.5 group-hover:-translate-y-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import
                </button>
                <!-- Export -->
                <button @click="exportExcel"
                    class="group flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:border-emerald-300 hover:bg-emerald-50 transition-all duration-150">
                    <svg class="w-3.5 h-3.5 group-hover:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export
                </button>
                <!-- Tambah -->
                <button @click="openModal('create')"
                    class="flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all duration-150 shadow-sm shadow-red-200 hover:shadow-md hover:shadow-red-200 active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Produk
                </button>
            </div>
        </div>

        <!-- ───────────────────────── FILTER BAR ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm mb-4 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Filter Produk</span>
            </div>
            <div class="px-5 py-4 flex flex-wrap items-center gap-3">
                <!-- Search -->
                <div class="flex items-center gap-2 flex-1 min-w-[200px] border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus-within:border-[#ED1F24] focus-within:ring-2 focus-within:ring-[#ED1F24]/10 transition-all">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Cari nama produk, SKU..."
                        class="text-sm text-gray-700 placeholder-gray-400 outline-none bg-transparent w-full"/>
                </div>

                <!-- Category -->
                <div class="relative">
                    <select v-model="filterCategory"
                        class="text-sm border border-gray-200 rounded-lg pl-3 pr-8 py-1.5 text-gray-700 bg-white focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all appearance-none cursor-pointer">
                        <option value="">Semua Kategori</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                    <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                <!-- Status -->
                <div class="relative">
                    <select v-model="filterStatus"
                        class="text-sm border border-gray-200 rounded-lg pl-3 pr-8 py-1.5 text-gray-700 bg-white focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all appearance-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                        <option value="lowstock">Low Stock</option>
                    </select>
                    <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                <!-- View Toggle -->
                <div class="flex items-center gap-1 bg-gray-100/80 p-1 rounded-lg border border-gray-200/60 ml-auto">
                    <button @click="viewMode = 'table'" :class="['flex items-center justify-center w-7 h-7 rounded-md transition-all duration-150', viewMode === 'table' ? 'bg-white text-gray-700 shadow-sm border border-gray-200/80' : 'text-gray-400 hover:text-gray-600']">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                            <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                        </svg>
                    </button>
                    <button @click="viewMode = 'grid'" :class="['flex items-center justify-center w-7 h-7 rounded-md transition-all duration-150', viewMode === 'grid' ? 'bg-white text-gray-700 shadow-sm border border-gray-200/80' : 'text-gray-400 hover:text-gray-600']">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── TABLE VIEW ───────────────────────── -->
        <div v-if="viewMode === 'table'" class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden mb-4">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[900px]">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="px-4 py-3 w-10">
                                <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected" class="w-4 h-4 rounded accent-[#ED1F24] cursor-pointer"/>
                            </th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 w-16">Foto</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 cursor-pointer select-none" @click="sortBy('name')">
                                Nama Produk
                                <svg class="inline w-3 h-3 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                            </th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Kategori</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Varian</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 cursor-pointer select-none" @click="sortBy('sell_price')">Harga Jual</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Stok</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Status</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400 w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="paginatedProducts.length === 0">
                            <td colspan="9" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-500">Belum ada produk</p>
                                    <span class="text-xs text-gray-400">Tambah produk baru atau import dari Excel</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="product in paginatedProducts" :key="product.id"
                            class="hover:bg-gray-50/60 transition-colors duration-150"
                            :class="selectedIds.includes(product.id) ? 'bg-[#ED1F24]/3' : ''">
                            <td class="px-4 py-3.5">
                                <input type="checkbox" :value="product.id" v-model="selectedIds" class="w-4 h-4 rounded accent-[#ED1F24] cursor-pointer"/>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="w-11 h-11 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">
                                    <img v-if="product.photo_1" :src="photoUrl(product.photo_1)" :alt="product.name" class="w-full h-full object-cover" @error="handleImgError"/>
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="font-semibold text-gray-800 text-sm">{{ product.name }}</p>
                                <p v-if="product.sku" class="text-[10px] text-gray-400 mt-0.5 font-mono">SKU: {{ product.sku }}</p>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md font-medium">{{ product.category || '—' }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <template v-if="product.variants && product.variants.length">
                                    <span class="inline-block text-xs font-bold px-2 py-0.5 rounded-md bg-purple-50 text-purple-600 border border-purple-100">
                                        {{ product.variants.length }} varian
                                    </span>
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        {{ product.variants.slice(0, 2).map(v => v.label).join(', ') }}
                                        <span v-if="product.variants.length > 2" class="text-gray-300">+{{ product.variants.length - 2 }}</span>
                                    </p>
                                </template>
                                <span v-else class="text-xs text-gray-300">—</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="font-bold text-gray-800 text-sm tabular-nums">{{ formatCurrency(getMinPrice(product)) }}</p>
                                <p v-if="product.variants && product.variants.length > 1" class="text-[10px] text-gray-400">mulai dari</p>
                                <p v-if="product.buy_price" class="text-[10px] text-gray-400 tabular-nums">HPP: {{ formatCurrency(product.buy_price) }}</p>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-sm tabular-nums px-2 py-0.5 rounded-md" :class="getStockBadgeClass(product)">
                                        {{ getTotalStock(product) }}
                                    </span>
                                    <svg v-if="isLowStock(product)" class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                                    :class="product.published ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-gray-100 text-gray-400 border-gray-200'">
                                    {{ product.published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openModal('view', product)" title="Detail"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg border border-gray-200 bg-white text-gray-400 hover:text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                    <button @click="openModal('edit', product)" title="Edit"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg border border-amber-200 bg-amber-50 text-amber-500 hover:bg-amber-100 hover:border-amber-300 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </button>
                                    <button @click="deleteProduct(product.id)" title="Hapus"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg border border-red-100 bg-red-50 text-red-400 hover:bg-red-100 hover:border-red-200 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ───────────────────────── GRID VIEW ───────────────────────── -->
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 mb-4">
            <div v-if="paginatedProducts.length === 0" class="col-span-full py-16 text-center">
                <div class="flex flex-col items-center gap-2">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <rect x="2" y="3" width="20" height="14" rx="2"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-400">Belum ada produk</p>
                </div>
            </div>
            <div v-for="product in paginatedProducts" :key="product.id"
                class="group bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md hover:border-gray-300 transition-all duration-200 overflow-hidden">
                <!-- Image -->
                <div class="relative aspect-square bg-gray-100">
                    <img v-if="product.photo_1" :src="photoUrl(product.photo_1)" :alt="product.name" class="w-full h-full object-cover" @error="handleImgError"/>
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </div>
                    <!-- Overlay actions -->
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2">
                        <button @click="openModal('view', product)" class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        <button @click="openModal('edit', product)" class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button @click="deleteProduct(product.id)" class="w-8 h-8 rounded-lg bg-red-500/70 hover:bg-red-500 flex items-center justify-center text-white transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                        </button>
                    </div>
                    <!-- Status dot -->
                    <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full border-2 border-white shadow-sm" :class="product.published ? 'bg-emerald-400' : 'bg-gray-400'"></span>
                    <!-- Category -->
                    <span v-if="product.category" class="absolute top-2 left-2 text-[10px] font-bold bg-black/50 text-white px-1.5 py-0.5 rounded-md">{{ product.category }}</span>
                </div>
                <!-- Body -->
                <div class="p-3">
                    <p class="text-sm font-semibold text-gray-800 truncate leading-snug mb-0.5">{{ product.name }}</p>
                    <p v-if="product.variants && product.variants.length" class="text-[10px] text-gray-400 mb-2">{{ product.variants.length }} varian</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-[#ED1F24] tabular-nums">{{ formatCurrency(getMinPrice(product)) }}</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md" :class="getStockBadgeClass(product)">
                            Stok: {{ getTotalStock(product) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── FOOTER: BULK + PAGINATION ───────────────────────── -->
        <div class="flex flex-wrap items-center justify-between gap-3 py-2">
            <div class="flex items-center gap-2">
                <template v-if="selectedIds.length > 0">
                    <span class="text-xs font-bold text-[#ED1F24]">{{ selectedIds.length }} item dipilih</span>
                    <button @click="bulkDelete" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-red-50 border border-red-100 text-red-500 hover:bg-red-100 transition-colors">Hapus Pilihan</button>
                    <button @click="selectedIds = []" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 transition-colors">Batal</button>
                </template>
                <template v-else>
                    <span class="text-xs text-gray-400">Menampilkan <span class="font-semibold text-gray-600">{{ paginatedProducts.length }}</span> dari <span class="font-semibold text-gray-600">{{ totalItems }}</span> produk</span>
                </template>
            </div>
            <div class="flex items-center gap-2">
                <button @click="currentPage--" :disabled="currentPage <= 1"
                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <span class="text-xs font-semibold text-gray-600 px-1">{{ currentPage }} / {{ totalPages }}</span>
                <button @click="currentPage++" :disabled="currentPage >= totalPages"
                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <div class="relative">
                    <select v-model.number="perPage"
                        class="text-xs border border-gray-200 rounded-lg pl-2.5 pr-7 py-1.5 text-gray-600 bg-white focus:outline-none focus:border-[#ED1F24] appearance-none cursor-pointer">
                        <option :value="20">20 / hal</option>
                        <option :value="50">50 / hal</option>
                        <option :value="100">100 / hal</option>
                    </select>
                    <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════ IMPORT MODAL ═══════════════════════════ -->
        <Transition name="pm-modal">
            <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);" @click.self="closeImportModal">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-gray-200/80 flex flex-col max-h-[90vh]">

                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800">Import Produk dari Excel</h3>
                                <p class="text-xs text-gray-400 mt-0.5">Upload file .xlsx untuk mengimpor data produk</p>
                            </div>
                        </div>
                        <button @click="closeImportModal" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <div class="overflow-y-auto flex-1 px-6 py-5">

                        <!-- Step 1: Upload -->
                        <div v-if="importStep === 1">
                            <!-- Format Selector -->
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Format File Excel</p>
                            <div class="grid grid-cols-2 gap-2 mb-4">
                                <label v-for="fmt in [{value:'olsera',label:'Format Olsera',desc:'Ekspor langsung dari Olsera. Varian di-group otomatis.'},{value:'new',label:'Format Baru',desc:'Template kolom variant_options (JSON).'}]"
                                    :key="fmt.value"
                                    class="flex items-start gap-2.5 p-3 border-2 rounded-xl cursor-pointer transition-all duration-150"
                                    :class="importFormat === fmt.value ? 'border-[#ED1F24] bg-[#ED1F24]/3' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" v-model="importFormat" :value="fmt.value" class="mt-0.5 accent-[#ED1F24]"/>
                                    <div>
                                        <p class="text-xs font-bold" :class="importFormat === fmt.value ? 'text-[#ED1F24]' : 'text-gray-700'">{{ fmt.label }}</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5 leading-relaxed">{{ fmt.desc }}</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Tip -->
                            <div class="flex items-start gap-2.5 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 mb-4">
                                <svg class="w-3.5 h-3.5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <p class="text-xs text-blue-600">
                                    <span v-if="importFormat === 'olsera'">Upload file ekspor Olsera langsung — sistem otomatis mengelompokkan varian.</span>
                                    <span v-else>Gunakan format kolom yang sesuai. <a href="#" @click.prevent="downloadTemplate" class="font-bold underline">Download template Excel</a></span>
                                </p>
                            </div>

                            <!-- Kolom didukung -->
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Kolom yang dibaca</p>
                            <div class="flex flex-wrap gap-1 mb-4">
                                <span v-for="col in currentSupportedColumns" :key="col" class="text-[10px] font-mono bg-gray-100 border border-gray-200 text-gray-500 px-1.5 py-0.5 rounded">{{ col }}</span>
                            </div>

                            <!-- Dropzone -->
                            <div class="border-2 border-dashed rounded-xl cursor-pointer transition-all duration-150 overflow-hidden"
                                :class="importDragging ? 'border-[#ED1F24] bg-[#ED1F24]/3' : 'border-gray-200 hover:border-gray-300'"
                                @dragover.prevent="importDragging = true" @dragleave="importDragging = false"
                                @drop.prevent="handleImportDrop" @click="$refs.importFileInput.click()">
                                <input ref="importFileInput" type="file" accept=".xlsx,.xls,.csv" @change="handleImportFile" class="hidden"/>
                                <div v-if="!importFile" class="py-10 flex flex-col items-center gap-2 text-gray-400">
                                    <svg class="w-9 h-9 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/>
                                    </svg>
                                    <p class="text-sm font-medium text-gray-500">Klik atau drag & drop file Excel</p>
                                    <span class="text-xs text-gray-400">.xlsx, .xls, .csv — maks 10MB</span>
                                </div>
                                <div v-else class="px-5 py-4 flex items-center gap-3">
                                    <svg class="w-8 h-8 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate">{{ importFile.name }}</p>
                                        <p class="text-xs text-gray-400">{{ formatFileSize(importFile.size) }}</p>
                                    </div>
                                    <button @click.stop="clearImportFile" class="w-6 h-6 flex items-center justify-center rounded-full border border-gray-200 text-gray-400 hover:bg-gray-100 transition-all">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Import Options -->
                            <div class="mt-4 space-y-2">
                                <label v-for="opt in [{value:'skip',label:'Lewati duplikat',desc:'Produk dengan nama yang sama akan dilewati'},{value:'update',label:'Update jika ada duplikat',desc:'Produk yang ada akan diperbarui beserta variannya'},{value:'replace',label:'Ganti semua data',desc:'Hapus semua produk lama dan ganti dengan data baru',danger:true}]"
                                    :key="opt.value"
                                    class="flex items-start gap-2.5 p-3 border rounded-xl cursor-pointer transition-all"
                                    :class="importMode === opt.value ? 'border-[#ED1F24] bg-[#ED1F24]/3' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" v-model="importMode" :value="opt.value" class="mt-0.5 accent-[#ED1F24]"/>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-700">{{ opt.label }}</p>
                                        <p class="text-[10px] mt-0.5" :class="opt.danger ? 'text-red-400' : 'text-gray-400'">{{ opt.desc }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Step 2: Preview -->
                        <div v-if="importStep === 2">
                            <div class="grid grid-cols-4 gap-2 mb-4">
                                <div v-for="s in [{label:'Total',val:importPreview.total,cls:'bg-blue-50 text-blue-600'},{label:'Valid',val:importPreview.valid,cls:'bg-emerald-50 text-emerald-600'},{label:'Error',val:importPreview.errors,cls:'bg-red-50 text-red-500'},{label:'Duplikat',val:importPreview.duplicates,cls:'bg-amber-50 text-amber-600'}]"
                                    :key="s.label" class="rounded-xl p-3 text-center" :class="s.cls.split(' ')[0]">
                                    <p class="text-xl font-bold" :class="s.cls.split(' ')[1]">{{ s.val }}</p>
                                    <p class="text-[10px] font-bold uppercase tracking-wider mt-0.5 text-gray-500">{{ s.label }}</p>
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-xl overflow-auto max-h-64">
                                <table class="w-full text-xs min-w-[400px]">
                                    <thead><tr class="bg-gray-50 border-b border-gray-100"><th class="px-3 py-2 text-left font-bold text-gray-400 uppercase tracking-wider">#</th><th class="px-3 py-2 text-left font-bold text-gray-400 uppercase tracking-wider">Nama</th><th class="px-3 py-2 text-left font-bold text-gray-400 uppercase tracking-wider">Kategori</th><th class="px-3 py-2 text-left font-bold text-gray-400 uppercase tracking-wider">Harga</th><th class="px-3 py-2 text-left font-bold text-gray-400 uppercase tracking-wider">Status</th></tr></thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <tr v-for="(row, i) in importPreview.rows.slice(0, 20)" :key="i" :class="row._status === 'error' ? 'bg-red-50' : row._status === 'duplicate' ? 'bg-amber-50' : ''">
                                            <td class="px-3 py-2 text-gray-400">{{ i+1 }}</td>
                                            <td class="px-3 py-2 font-medium text-gray-700">{{ row.name }}</td>
                                            <td class="px-3 py-2 text-gray-500">{{ row.category }}</td>
                                            <td class="px-3 py-2 text-gray-700 tabular-nums">{{ formatCurrency(row.sell_price) }}</td>
                                            <td class="px-3 py-2">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold border"
                                                    :class="row._status === 'error' ? 'bg-red-50 text-red-500 border-red-100' : row._status === 'duplicate' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'">
                                                    {{ row._status === 'error' ? 'Error' : row._status === 'duplicate' ? 'Duplikat' : 'OK' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p v-if="importPreview.rows.length > 20" class="text-center text-xs text-gray-400 py-2">+ {{ importPreview.rows.length - 20 }} baris lainnya</p>
                            </div>
                            <div v-if="importPreview.errorMessages.length" class="mt-3 bg-red-50 border border-red-100 rounded-xl p-3">
                                <p class="text-xs font-bold text-red-600 mb-1.5">Daftar Error:</p>
                                <ul class="space-y-0.5"><li v-for="(err,i) in importPreview.errorMessages.slice(0,5)" :key="i" class="text-xs text-red-500">{{ err }}</li></ul>
                            </div>
                        </div>

                        <!-- Step 3: Result -->
                        <div v-if="importStep === 3" class="py-8 flex flex-col items-center gap-3 text-center">
                            <div v-if="importResult.success">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
                                </div>
                                <p class="text-base font-bold text-gray-800">Import Berhasil!</p>
                                <p class="text-sm text-gray-500 mt-1">{{ importResult.message }}</p>
                                <div class="flex gap-4 mt-3 justify-center flex-wrap text-xs text-gray-600">
                                    <span class="text-emerald-600 font-semibold">✓ {{ importResult.imported }} diimport</span>
                                    <span v-if="importResult.skipped">⊘ {{ importResult.skipped }} dilewati</span>
                                    <span v-if="importResult.updated">↻ {{ importResult.updated }} diperbarui</span>
                                    <span v-if="importResult.failed" class="text-red-500">✗ {{ importResult.failed }} gagal</span>
                                </div>
                            </div>
                            <div v-else>
                                <div class="w-14 h-14 rounded-2xl bg-red-50 border border-red-100 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                </div>
                                <p class="text-base font-bold text-gray-800">Import Gagal</p>
                                <p class="text-sm text-gray-500 mt-1">{{ importResult.message }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex justify-between items-center shrink-0">
                        <template v-if="importStep === 1">
                            <button @click="closeImportModal" class="text-xs font-semibold text-gray-500 border border-gray-200 px-4 py-2 rounded-lg hover:bg-white transition-all">Batal</button>
                            <button @click="previewImport" :disabled="!importFile || importLoading"
                                class="flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg v-if="importLoading" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                                {{ importLoading ? 'Memproses...' : 'Pratinjau Data' }}
                            </button>
                        </template>
                        <template v-else-if="importStep === 2">
                            <button @click="importStep = 1" class="text-xs font-semibold text-gray-500 border border-gray-200 px-4 py-2 rounded-lg hover:bg-white transition-all">← Kembali</button>
                            <button @click="executeImport" :disabled="importLoading || importPreview.valid === 0"
                                class="flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg v-if="importLoading" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                                {{ importLoading ? 'Mengimport...' : `Import ${importPreview.valid} Produk` }}
                            </button>
                        </template>
                        <template v-else>
                            <button @click="closeImportModal" class="text-xs font-semibold text-gray-500 border border-gray-200 px-4 py-2 rounded-lg hover:bg-white transition-all">Tutup</button>
                            <button v-if="importResult.success" @click="closeImportModal" class="text-xs font-semibold px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white transition-all">Selesai</button>
                        </template>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ═══════════════════════════ PRODUCT MODAL (Create/Edit/View) ═══════════════════════════ -->
        <Transition name="pm-modal">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);" @click.self="closeModal">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl border border-gray-200/80 flex flex-col max-h-[90vh] overflow-hidden">

                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                :class="modalMode === 'create' ? 'bg-[#ED1F24]/10 border border-[#ED1F24]/20' : modalMode === 'edit' ? 'bg-amber-50 border border-amber-200' : 'bg-gray-100 border border-gray-200'">
                                <svg v-if="modalMode === 'create'" class="w-4 h-4 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <svg v-else-if="modalMode === 'edit'" class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                <svg v-else class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800">{{ modalMode === 'create' ? 'Tambah Produk' : modalMode === 'edit' ? 'Edit Produk' : 'Detail Produk' }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5">{{ selectedProduct?.name || 'Produk baru' }}</p>
                            </div>
                        </div>
                        <button @click="closeModal" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <!-- Error -->
                    <div v-if="errorMessage" class="flex items-center gap-2.5 px-6 py-3 bg-red-50 border-b border-red-100 shrink-0">
                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span class="text-xs font-medium text-red-600">{{ errorMessage }}</span>
                    </div>

                    <!-- Tabs -->
                    <div class="flex border-b border-gray-100 shrink-0 px-6">
                        <button v-for="tab in formTabs" :key="tab.key" @click="activeTab = tab.key"
                            class="flex items-center gap-1.5 py-3 px-3 text-xs font-semibold border-b-2 transition-all duration-150 -mb-px"
                            :class="activeTab === tab.key ? 'border-[#ED1F24] text-[#ED1F24]' : 'border-transparent text-gray-400 hover:text-gray-600'">
                            {{ tab.label }}
                            <span v-if="tab.key === 'variants' && form.option_types.length"
                                class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-[#ED1F24] text-white">{{ form.option_types.length }}</span>
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="overflow-y-auto flex-1 px-6 py-5">

                        <!-- Basic -->
                        <div v-show="activeTab === 'basic'" class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Nama Produk <span class="text-[#ED1F24]">*</span></label>
                                <input v-model="form.name" type="text" placeholder="Nama produk..." :disabled="modalMode === 'view'"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all"/>
                            </div>
                            <div v-for="f in basicFields" :key="f.key" :class="f.full ? 'col-span-2' : ''">
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">{{ f.label }}</label>
                                <textarea v-if="f.type === 'textarea'" v-model="form[f.key]" rows="3" :placeholder="f.placeholder" :disabled="modalMode === 'view'"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all resize-none"></textarea>
                                <input v-else v-model="form[f.key]" :type="f.type || 'text'" :placeholder="f.placeholder" :disabled="modalMode === 'view'"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all"/>
                            </div>
                            <!-- Status Toggle -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                                <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                                    <button @click="modalMode !== 'view' && (form.published = 1)"
                                        class="flex-1 py-2 text-xs font-bold transition-all" :disabled="modalMode === 'view'"
                                        :class="form.published ? 'bg-emerald-500 text-white' : 'bg-white text-gray-400'">Published</button>
                                    <button @click="modalMode !== 'view' && (form.published = 0)"
                                        class="flex-1 py-2 text-xs font-bold transition-all border-l border-gray-200" :disabled="modalMode === 'view'"
                                        :class="!form.published ? 'bg-gray-500 text-white' : 'bg-white text-gray-400'">Draft</button>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div v-show="activeTab === 'pricing'">
                            <div v-if="form.option_types.length" class="flex items-start gap-2 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 mb-4">
                                <svg class="w-3.5 h-3.5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <p class="text-xs text-blue-600">Produk ini punya varian. Harga di sini adalah harga default. Atur harga per varian di tab <strong>Varian</strong>.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div v-for="f in pricingFields" :key="f.key">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">{{ f.label }}</label>
                                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#ED1F24] focus-within:ring-2 focus-within:ring-[#ED1F24]/10 transition-all">
                                        <span class="px-3 py-2 bg-gray-50 text-xs text-gray-400 border-r border-gray-200 shrink-0">Rp</span>
                                        <input v-model.number="form[f.key]" type="number" :disabled="modalMode === 'view'"
                                            class="flex-1 text-sm px-3 py-2 text-gray-700 outline-none bg-white disabled:bg-gray-50 disabled:text-gray-400"/>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Komisi</label>
                                    <input v-model.number="form.comission" type="number" :disabled="modalMode === 'view'" placeholder="0"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 transition-all"/>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Loyalty Points</label>
                                    <input v-model.number="form.loyalty_points" type="number" :disabled="modalMode === 'view'" placeholder="0"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 transition-all"/>
                                </div>
                            </div>
                        </div>

                        <!-- Variants -->
                        <div v-show="activeTab === 'variants'">
                            <!-- View mode -->
                            <template v-if="modalMode === 'view'">
                                <div v-if="!form.option_types.length" class="py-10 flex flex-col items-center gap-2 text-center">
                                    <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                                    <p class="text-sm text-gray-400 font-medium">Produk ini tidak memiliki varian</p>
                                </div>
                                <div v-else>
                                    <div v-for="(ot, i) in form.option_types" :key="i" class="mb-4">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">{{ ot.name }}</p>
                                        <div class="flex flex-wrap gap-1.5">
                                            <span v-for="v in ot.values" :key="v" class="text-xs font-medium bg-purple-50 border border-purple-100 text-purple-600 px-2.5 py-1 rounded-lg">{{ v }}</span>
                                        </div>
                                    </div>
                                    <div class="border border-gray-200 rounded-xl overflow-auto mt-4">
                                        <table class="w-full text-xs min-w-[400px]">
                                            <thead><tr class="bg-gray-50 border-b border-gray-100"><th class="px-3 py-2.5 text-left font-bold text-gray-400 uppercase tracking-wider">Varian</th><th class="px-3 py-2.5 text-left font-bold text-gray-400 uppercase tracking-wider">SKU</th><th class="px-3 py-2.5 text-left font-bold text-gray-400 uppercase tracking-wider">Harga</th><th class="px-3 py-2.5 text-left font-bold text-gray-400 uppercase tracking-wider">Stok</th><th class="px-3 py-2.5 text-left font-bold text-gray-400 uppercase tracking-wider">Status</th></tr></thead>
                                            <tbody class="divide-y divide-gray-50">
                                                <tr v-for="v in form.variants" :key="v.id ?? v._key" class="hover:bg-gray-50/60">
                                                    <td class="px-3 py-2.5 font-semibold text-gray-700">{{ v.label }}</td>
                                                    <td class="px-3 py-2.5 text-gray-400 font-mono">{{ v.sku || '—' }}</td>
                                                    <td class="px-3 py-2.5 text-gray-700">{{ v.sell_price ? formatCurrency(v.sell_price) : 'Default' }}</td>
                                                    <td class="px-3 py-2.5 font-bold text-gray-700 tabular-nums">{{ v.stock_qty ?? 0 }}</td>
                                                    <td class="px-3 py-2.5">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                                            :class="v.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-400 border-red-100'">
                                                            {{ v.is_active ? 'Aktif' : 'Nonaktif' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </template>

                            <!-- Edit/Create mode -->
                            <template v-else>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Tipe Opsi <span class="text-gray-300 font-normal normal-case">contoh: Nicotine, Warna, Ukuran</span></p>

                                <div v-for="(ot, otIdx) in form.option_types" :key="otIdx" class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-3">
                                    <div class="flex items-center gap-2 mb-3">
                                        <input v-model="ot.name" type="text" :placeholder="`Tipe opsi ${otIdx+1}`" @input="regenerateVariants"
                                            class="flex-1 text-sm font-semibold border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all"/>
                                        <button @click="removeOptionType(otIdx)" class="w-8 h-8 flex items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-400 hover:bg-red-100 transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                    <div class="space-y-2">
                                        <div v-for="(val, valIdx) in ot.values" :key="valIdx" class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300 shrink-0"></span>
                                            <input v-model="ot.values[valIdx]" type="text" :placeholder="`Nilai ${valIdx+1}`" @input="regenerateVariants"
                                                class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all"/>
                                            <button @click="removeOptionValue(otIdx, valIdx)" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-300 hover:border-gray-300 hover:text-gray-500 transition-all">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                        </div>
                                        <button @click="addOptionValue(otIdx)" class="flex items-center gap-1.5 text-xs text-gray-400 hover:text-[#ED1F24] border border-dashed border-gray-300 hover:border-[#ED1F24]/40 px-3 py-1.5 rounded-lg w-full justify-center transition-all">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            Tambah nilai
                                        </button>
                                    </div>
                                </div>

                                <button @click="addOptionType" class="flex items-center gap-2 w-full justify-center py-2.5 border-2 border-dashed border-gray-200 hover:border-[#ED1F24]/30 hover:bg-[#ED1F24]/3 rounded-xl text-xs text-gray-400 hover:text-[#ED1F24] font-semibold transition-all mb-4">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Tambah tipe opsi
                                </button>

                                <template v-if="form.variants.length">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Varian yang dihasilkan <span class="text-gray-300 font-normal">{{ form.variants.length }} kombinasi</span></p>
                                    <div class="border border-gray-200 rounded-xl overflow-auto">
                                        <table class="w-full text-xs min-w-[500px]">
                                            <thead><tr class="bg-gray-50 border-b border-gray-100"><th class="px-3 py-2.5 text-left font-bold text-gray-400 uppercase tracking-wider">Varian</th><th class="px-3 py-2.5 text-left font-bold text-gray-400 uppercase tracking-wider">SKU</th><th class="px-3 py-2.5 text-left font-bold text-gray-400 uppercase tracking-wider">Harga Jual</th><th class="px-3 py-2.5 text-left font-bold text-gray-400 uppercase tracking-wider">Stok</th><th class="px-3 py-2.5 text-center font-bold text-gray-400 uppercase tracking-wider">Aktif</th></tr></thead>
                                            <tbody class="divide-y divide-gray-50">
                                                <tr v-for="(variant, vi) in form.variants" :key="variant._key" class="hover:bg-gray-50/60">
                                                    <td class="px-3 py-2 font-semibold text-gray-700">{{ variant.label }}</td>
                                                    <td class="px-3 py-2"><input v-model="form.variants[vi].sku" type="text" placeholder="SKU..." class="w-full text-xs border border-gray-200 rounded-md px-2 py-1 focus:outline-none focus:border-[#ED1F24] transition-all"/></td>
                                                    <td class="px-3 py-2">
                                                        <div class="flex items-center border border-gray-200 rounded-md overflow-hidden focus-within:border-[#ED1F24] transition-all">
                                                            <span class="px-1.5 py-1 bg-gray-50 text-[10px] text-gray-400 border-r border-gray-200 shrink-0">Rp</span>
                                                            <input v-model.number="form.variants[vi].sell_price" type="number" placeholder="Default" class="w-20 text-xs px-2 py-1 outline-none bg-white"/>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-2"><input v-model.number="form.variants[vi].stock_qty" type="number" placeholder="0" class="w-16 text-xs border border-gray-200 rounded-md px-2 py-1 focus:outline-none focus:border-[#ED1F24] transition-all text-center"/></td>
                                                    <td class="px-3 py-2 text-center"><input type="checkbox" v-model="form.variants[vi].is_active" :true-value="1" :false-value="0" class="w-4 h-4 rounded accent-[#ED1F24] cursor-pointer"/></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>

                                <div v-else-if="!form.option_types.length" class="py-8 flex flex-col items-center gap-2 text-center">
                                    <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                                    <p class="text-sm text-gray-400 font-medium">Belum ada tipe opsi. Tambahkan untuk membuat varian.</p>
                                </div>
                            </template>
                        </div>

                        <!-- Photos -->
                        <div v-show="activeTab === 'photos'" class="grid grid-cols-3 gap-3">
                            <div v-for="n in 5" :key="n">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Foto {{ n }}</label>
                                <div class="aspect-square rounded-xl overflow-hidden border border-gray-200 bg-gray-50 mb-2">
                                    <img v-if="form[`photo_${n}`]" :src="photoUrl(form[`photo_${n}`])" class="w-full h-full object-cover" @error="handleImgError"/>
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    </div>
                                </div>
                                <input v-if="modalMode !== 'view'" v-model="form[`photo_${n}`]" type="text" placeholder="URL foto..."
                                    class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all"/>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex justify-between items-center shrink-0">
                        <button @click="closeModal" class="text-xs font-semibold text-gray-500 border border-gray-200 px-4 py-2 rounded-lg hover:bg-white transition-all">
                            {{ modalMode === 'view' ? 'Tutup' : 'Batal' }}
                        </button>
                        <div class="flex items-center gap-2">
                            <button v-if="modalMode === 'view'" @click="modalMode = 'edit'"
                                class="flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Produk
                            </button>
                            <button v-else @click="submitForm" :disabled="loading"
                                class="flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all shadow-sm shadow-red-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                                <svg v-if="loading" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                                {{ loading ? 'Menyimpan...' : (modalMode === 'create' ? 'Tambah Produk' : 'Simpan Perubahan') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </AdminLayout>
</template>

<script>
import AdminLayout from '../../components/admin/AdminLayout.vue'
import axios from '../../axios.js'
import * as XLSX from 'xlsx'
import _ from 'lodash'

export default {
    name: 'Products',
    components: { AdminLayout },

    data() {
        return {
            products: [],
            loading: false,
            errorMessage: '',
            totalItems: 0,

            stats: { total: 0, publishedCount: 0, lowStockCount: 0, categoryCount: 0 },

            search: '',
            filterCategory: '',
            filterStatus: '',
            viewMode: 'table',
            sortKey: 'name',
            sortDir: 'asc',

            currentPage: 1,
            perPage: 20,
            selectedIds: [],

            showModal: false,
            modalMode: 'create',
            selectedProduct: null,
            activeTab: 'basic',

            formTabs: [
                { key: 'basic',    label: 'Info Dasar' },
                { key: 'pricing',  label: 'Harga' },
                { key: 'variants', label: 'Varian' },
                { key: 'photos',   label: 'Foto' },
            ],

            basicFields: [
                { key: 'alternative_name', label: 'Nama Alternatif', placeholder: 'Nama lain...' },
                { key: 'category',         label: 'Kategori',        placeholder: 'Kategori...' },
                { key: 'brand',            label: 'Brand',           placeholder: 'Brand...' },
                { key: 'sku',              label: 'SKU',             placeholder: 'SKU produk utama...' },
                { key: 'barcode',          label: 'Barcode',         placeholder: 'Barcode...' },
                { key: 'weight_kg',        label: 'Berat (kg)',      placeholder: '0.00', type: 'number' },
                { key: 'collections',      label: 'Collections',     placeholder: '...' },
                { key: 'uom',              label: 'Satuan (UOM)',    placeholder: 'pcs, box, dll...' },
                { key: 'description',      label: 'Deskripsi',       placeholder: 'Deskripsi produk...', type: 'textarea', full: true },
            ],

            pricingFields: [
                { key: 'buy_price',       label: 'Harga Beli (HPP)' },
                { key: 'market_price',    label: 'Harga Pasar' },
                { key: 'sell_price',      label: 'Harga Jual Default' },
                { key: 'pos_sell_price',  label: 'Harga Jual POS' },
            ],

            form: this.emptyForm(),

            showImportModal: false,
            importFile: null,
            importDragging: false,
            importLoading: false,
            importMode: 'skip',
            importFormat: 'olsera',
            importStep: 1,
            importPreview: { total: 0, valid: 0, errors: 0, duplicates: 0, rows: [], errorMessages: [] },
            importResult:  { success: false, imported: 0, skipped: 0, updated: 0, failed: 0, message: '' },

            supportedColumns: [
                'name','alternative_name','category','brand','sku','barcode',
                'buy_price','market_price','sell_price','pos_sell_price',
                'uom','weight_kg','published','description',
                'photo_1','photo_2','photo_3',
                'variant_options (JSON)','variant_skus','variant_prices','variant_stocks',
            ],
        }
    },

    computed: {
        currentSupportedColumns() {
            if (this.importFormat === 'olsera') {
                return ['name','category','brand','variant_label','variant_names','sku','barcode','buy_price','market_price','sell_price','pos_sell_price','stock_qty','low_stock_alert','uom','weight_kg','published','description','photo_1 … photo_10']
            }
            return this.supportedColumns
        },
        filteredProducts() { return this.products },
        paginatedProducts() { return this.products },
        totalPages()     { return Math.max(1, Math.ceil(this.totalItems / this.perPage)) },
        categories()     { return [...new Set(this.products.map(p => p.category).filter(Boolean))].sort() },
        isAllSelected()  { return this.paginatedProducts.length > 0 && this.paginatedProducts.every(p => this.selectedIds.includes(p.id)) },
    },

    watch: {
        search:         _.debounce(function() { this.currentPage = 1; this.fetchProducts() }, 400),
        filterCategory() { this.currentPage = 1; this.fetchProducts() },
        filterStatus()   { this.currentPage = 1; this.fetchProducts() },
        perPage()        { this.currentPage = 1; this.fetchProducts() },
        currentPage()    { this.fetchProducts() },
    },

    mounted() {
        document.title = 'Products - Two Brothers Vape System'
        this.fetchProducts()
    },

    methods: {
        getTotalStock(product) {
            const variants = product.active_variants ?? product.variants ?? []
            if (variants.length) return variants.reduce((sum, v) => sum + (parseInt(v.stock_qty) || 0), 0)
            return 0
        },
        getMinPrice(product) {
            const variants = product.active_variants ?? product.variants ?? []
            if (variants.length) {
                const prices = variants.map(v => parseFloat(v.sell_price) || parseFloat(product.sell_price) || 0).filter(p => p > 0)
                return prices.length ? Math.min(...prices) : parseFloat(product.sell_price) || 0
            }
            return parseFloat(product.sell_price) || 0
        },
        isLowStock(product) {
            const variants = product.active_variants ?? product.variants ?? []
            if (variants.length) return variants.some(v => parseInt(v.stock_qty) <= (parseInt(v.low_stock_alert) || 2))
            return false
        },
        getStockBadgeClass(product) {
            const total = this.getTotalStock(product)
            if (total <= 0) return 'bg-red-50 text-red-500 border border-red-100'
            if (this.isLowStock(product)) return 'bg-amber-50 text-amber-600 border border-amber-100'
            return 'bg-emerald-50 text-emerald-600 border border-emerald-100'
        },

        emptyForm() {
            return {
                name:'', alternative_name:'', category:'', brand:'',
                sku:'', barcode:'', collections:'', uom:'',
                buy_price:null, market_price:null, sell_price:null,
                pos_sell_price:null, pos_sell_price_dynamic:0,
                comission:0, track_inventory:1, weight_kg:null,
                loyalty_points:0, published:1, pos_hidden:0,
                description:'', notes:'', classification_id:null,
                condition_id:'N', tax_free_item:'No',
                photo_1:'', photo_2:'', photo_3:'', photo_4:'', photo_5:'',
                option_types:[], variants:[],
            }
        },

        addOptionType()                    { this.form.option_types.push({ name:'', values:[''] }); this.regenerateVariants() },
        removeOptionType(idx)              { this.form.option_types.splice(idx, 1); this.regenerateVariants() },
        addOptionValue(otIdx)              { this.form.option_types[otIdx].values.push(''); this.regenerateVariants() },
        removeOptionValue(otIdx, valIdx)   { this.form.option_types[otIdx].values.splice(valIdx, 1); this.regenerateVariants() },

        photoUrl(path) {
            if (!path) return null
            if (path.startsWith('http://') || path.startsWith('https://')) return path
            return `${import.meta.env.VITE_APP_URL || window.location.origin}/storage/${path}`
        },

        regenerateVariants() {
            const existingByLabel = {}
            for (const v of this.form.variants) { if (v.label) existingByLabel[v.label] = v }
            const validTypes = this.form.option_types.filter(ot => ot.name.trim() && ot.values.some(v => v.trim()))
            if (!validTypes.length) { this.form.variants = []; return }
            const combinations = validTypes.reduce((acc, ot) => {
                const vals = ot.values.filter(v => v.trim())
                if (!vals.length) return acc
                if (!acc.length) return vals.map(v => [v])
                return acc.flatMap(combo => vals.map(v => [...combo, v]))
            }, [])
            this.form.variants = combinations.map((combo, idx) => {
                const label = combo.join(' / ')
                const existing = existingByLabel[label] || {}
                return { _key: label+'_'+idx, label, sku: existing.sku??'', sell_price: existing.sell_price??null, stock_qty: existing.stock_qty??0, is_active: existing.is_active??1, option_value_indexes: combo.map((_, ti) => idx % (validTypes[ti]?.values?.filter(v=>v.trim()).length||1)) }
            })
        },

        async openModal(mode, product = null) {
            this.modalMode = mode; this.errorMessage = ''; this.activeTab = 'basic'
            if (product) {
                this.selectedProduct = product
                let detail = product
                if (mode !== 'create' && product.id) {
                    try { const res = await axios.get(`/products/${product.id}`); detail = res.data.data ?? res.data } catch(e) { detail = product }
                }
                this.form = { ...this.emptyForm(), ...detail,
                    option_types: (detail.option_types||[]).map(ot => ({ name: ot.name, values: (ot.values||[]).map(v=>v.value) })),
                    variants: (detail.variants||detail.active_variants||[]).map((v,i) => ({ ...v, _key: v.label+'_'+i, option_value_indexes: v.option_value_indexes ?? (v.option_values??[]).map(()=>0) })),
                }
            } else { this.selectedProduct = null; this.form = this.emptyForm() }
            this.showModal = true
        },
        closeModal() { this.showModal = false; this.errorMessage = '' },

        async submitForm() {
            if (!this.form.name?.trim()) { this.errorMessage = 'Nama produk wajib diisi.'; return }
            this.loading = true; this.errorMessage = ''
            try {
                const payload = { ...this.form,
                    option_types: this.form.option_types.filter(ot => ot.name.trim() && ot.values.some(v=>v.trim())).map((ot,pos) => ({ name: ot.name.trim(), position: pos, values: ot.values.filter(v=>v.trim()) })),
                    variants: this.form.variants.map((v,pos) => ({ sku: v.sku||null, sell_price: v.sell_price||null, stock_qty: v.stock_qty??0, is_active: v.is_active??1, position: pos, option_value_indexes: v.option_value_indexes??[], id: v.id??null })),
                }
                if (this.modalMode === 'create') await axios.post('/products', payload)
                else await axios.put(`/products/${this.selectedProduct.id}`, payload)
                await this.fetchProducts(); this.closeModal()
            } catch(e) { this.errorMessage = e.response?.data?.message || 'Terjadi kesalahan, coba lagi.' }
            finally { this.loading = false }
        },

        async deleteProduct(id) {
            if (!confirm('Yakin ingin menghapus produk ini?')) return
            try { await axios.delete(`/products/${id}`); await this.fetchProducts() } catch(e) { alert('Gagal menghapus produk.') }
        },

        async bulkDelete() {
            if (!confirm(`Yakin ingin menghapus ${this.selectedIds.length} produk?`)) return
            try { await axios.post('/products/bulk-delete', { ids: this.selectedIds }); this.selectedIds = []; await this.fetchProducts() } catch(e) { alert('Gagal menghapus produk.') }
        },

        async fetchProducts() {
            try {
                const res = await axios.get('/products', { params: { with_variants:1, per_page:this.perPage, page:this.currentPage, search:this.search||undefined, category:this.filterCategory||undefined, published: this.filterStatus==='published'?1:this.filterStatus==='draft'?0:undefined } })
                const paginated = res.data.data
                if (paginated.data) { this.products = paginated.data; this.totalItems = paginated.total }
                else { this.products = paginated; this.totalItems = paginated.length }
                if (res.data.meta) { this.stats.total = res.data.meta.total??0; this.stats.publishedCount = res.data.meta.published_count??0; this.stats.lowStockCount = res.data.meta.low_stock_count??0; this.stats.categoryCount = res.data.meta.category_count??0 }
            } catch(e) { console.error(e) }
        },

        async exportExcel() {
            try {
                // Fetch semua produk tanpa pagination
                const res = await axios.get('/products', {
                    params: {
                        with_variants: 1,
                        per_page: 99999,
                        page: 1,
                        search: this.search || undefined,
                        category: this.filterCategory || undefined,
                        published: this.filterStatus === 'published' ? 1 : this.filterStatus === 'draft' ? 0 : undefined,
                    }
                })

                const paginated = res.data.data
                const allProducts = paginated.data ?? paginated

                const rows = []
                for (const p of allProducts) {
                    if (p.active_variants?.length) {
                        for (const v of p.active_variants) {
                            rows.push({
                                name: p.name,
                                category: p.category,
                                brand: p.brand,
                                sku: p.sku,
                                variant_label: v.label,
                                variant_sku: v.sku,
                                sell_price: v.sell_price ?? p.sell_price,
                                buy_price: p.buy_price,
                                stock_qty: v.stock_qty,
                                published: p.published ? 'Yes' : 'No',
                                photo_1: p.photo_1,
                            })
                        }
                    } else {
                        rows.push({
                            name: p.name,
                            category: p.category,
                            brand: p.brand,
                            sku: p.sku,
                            sell_price: p.sell_price,
                            buy_price: p.buy_price,
                            stock_qty: p.stock_qty ?? 0,
                            published: p.published ? 'Yes' : 'No',
                            photo_1: p.photo_1,
                        })
                    }
                }

                const ws = XLSX.utils.json_to_sheet(rows)
                const wb = XLSX.utils.book_new()
                XLSX.utils.book_append_sheet(wb, ws, 'Products')
                XLSX.writeFile(wb, `products_export_${new Date().toISOString().slice(0, 10)}.xlsx`)

            } catch (e) {
                alert('Gagal export produk.')
                console.error(e)
            }
        },

        openImportModal() { this.importStep=1; this.importFile=null; this.importMode='skip'; this.importFormat='olsera'; this.importPreview={total:0,valid:0,errors:0,duplicates:0,rows:[],errorMessages:[]}; this.importResult={success:false,imported:0,skipped:0,updated:0,failed:0,message:''}; this.showImportModal=true },
        closeImportModal() { this.showImportModal=false; if(this.importResult.success) this.fetchProducts() },
        clearImportFile()  { this.importFile=null; if(this.$refs.importFileInput) this.$refs.importFileInput.value='' },
        handleImportFile(e) { const f=e.target.files[0]; if(f) this.importFile=f },
        handleImportDrop(e) { this.importDragging=false; const f=e.dataTransfer.files[0]; if(f) this.importFile=f },

        downloadTemplate() {
            const sample = [{ name:'Contoh Produk A', alternative_name:'', category:'LIQUID 30ML', brand:'', sku:'', barcode:'', buy_price:50000, market_price:0, sell_price:70000, pos_sell_price:70000, uom:'', weight_kg:0.1, published:1, description:'', photo_1:'', variant_options:'[{"type":"Nicotine","values":["3mg","6mg","12mg"]}]', variant_skus:'3mg:LQ-3MG,6mg:LQ-6MG', variant_prices:'3mg:65000,6mg:65000', variant_stocks:'3mg:50,6mg:30' }]
            const ws=XLSX.utils.json_to_sheet(sample); const wb=XLSX.utils.book_new(); XLSX.utils.book_append_sheet(wb,ws,'Products'); XLSX.writeFile(wb,'template_produk.xlsx')
        },

        async previewImport() {
            if(!this.importFile) return; this.importLoading=true
            try {
                const data=await this.readExcelFile(this.importFile)
                const existingNames=new Set(this.products.map(p=>(p.name||'').toLowerCase()))
                let valid=0,errors=0,duplicates=0; const errorMessages=[]
                const rows=data.map((row,i)=>{ const rowNum=i+2; let status='ok'; if(!row.name){status='error';errorMessages.push(`Baris ${rowNum}: 'name' wajib diisi`);errors++}else if(existingNames.has((row.name||'').toLowerCase())){status='duplicate';duplicates++;valid++}else{valid++}; return{...row,_status:status} })
                this.importPreview={total:rows.length,valid,errors,duplicates,rows,errorMessages}; this.importStep=2
            } catch(e) { alert('Gagal membaca file: '+e.message) } finally { this.importLoading=false }
        },

        readExcelFile(file) {
            return new Promise((resolve,reject)=>{ const reader=new FileReader(); reader.onload=e=>{ try{const wb=XLSX.read(e.target.result,{type:'binary'}); const ws=wb.Sheets[wb.SheetNames[0]]; resolve(XLSX.utils.sheet_to_json(ws,{defval:''}))}catch(err){reject(err)} }; reader.onerror=()=>reject(new Error('Gagal membaca file')); reader.readAsBinaryString(file) })
        },

        async executeImport() {
            this.importLoading=true
            try {
                const validRows=this.importPreview.rows.filter(r=>r._status!=='error').map(({_status,...row})=>row)
                const endpoint=this.importFormat==='olsera'?'/products/import-olsera':'/products/import'
                const res=await axios.post(endpoint,{products:validRows,mode:this.importMode}); const result=res.data
                this.importResult={success:true,imported:result.imported??0,skipped:result.skipped??0,updated:result.updated??0,failed:result.failed??0,message:result.message??''}; this.importStep=3
            } catch(e) { this.importResult={success:false,message:e.response?.data?.message||'Terjadi kesalahan saat import.'}; this.importStep=3 } finally { this.importLoading=false }
        },

        formatCurrency(val) { if(!val && val!==0) return '—'; return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(val) },
        formatFileSize(bytes) { if(bytes<1024) return bytes+' B'; if(bytes<1048576) return (bytes/1024).toFixed(1)+' KB'; return (bytes/1048576).toFixed(1)+' MB' },
        handleImgError(e) { e.target.style.display='none' },

        sortBy(key) { if(this.sortKey===key) this.sortDir=this.sortDir==='asc'?'desc':'asc'; else{this.sortKey=key;this.sortDir='asc'} },
        toggleSelectAll(e) {
            if(e.target.checked) this.selectedIds=[...new Set([...this.selectedIds,...this.paginatedProducts.map(p=>p.id)])]
            else { const pageIds=this.paginatedProducts.map(p=>p.id); this.selectedIds=this.selectedIds.filter(id=>!pageIds.includes(id)) }
        },
    }
}
</script>

<style scoped>
.pm-modal-enter-active, .pm-modal-leave-active { transition: all .2s; }
.pm-modal-enter-from, .pm-modal-leave-to { opacity: 0; transform: scale(.97); }
</style>