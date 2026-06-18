<template>
    <AdminLayout title="Promo Code Management">

        <!-- ───────────────────────── HEADER ───────────────────────── -->
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12V22H4V12M22 7H2v5h20V7zM12 22V7M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Promo Kode</h1>
                        <span class="text-[10px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 border border-gray-200">Marketing</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Kelola kode promo dan diskon untuk customer</p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <!-- Stats Pills -->
                <div class="hidden sm:flex items-center gap-0 bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-4 py-2.5 text-center border-r border-gray-100">
                        <p class="text-base font-bold text-gray-900 tabular-nums">{{ promoCodes.length }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total</p>
                    </div>
                    <div class="px-4 py-2.5 text-center border-r border-gray-100">
                        <p class="text-base font-bold text-emerald-600 tabular-nums">{{ activeCount }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aktif</p>
                    </div>
                    <div class="px-4 py-2.5 text-center">
                        <p class="text-base font-bold text-gray-400 tabular-nums">{{ expiredCount }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Expired</p>
                    </div>
                </div>

                <div class="w-px h-8 bg-gray-200 hidden sm:block"></div>

                <button @click="openModal('create')"
                    class="flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all duration-150 shadow-sm shadow-red-200 hover:shadow-md hover:shadow-red-200 active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Promo
                </button>
            </div>
        </div>

        <!-- ───────────────────────── FILTER BAR ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm mb-4 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Filter Promo Kode</span>
            </div>
            <div class="px-5 py-4 flex flex-wrap items-center gap-3">
                <!-- Search -->
                <div class="flex items-center gap-2 flex-1 min-w-[200px] border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus-within:border-[#ED1F24] focus-within:ring-2 focus-within:ring-[#ED1F24]/10 transition-all">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Cari kode atau deskripsi promo..."
                        class="text-sm text-gray-700 placeholder-gray-400 outline-none bg-transparent w-full"/>
                </div>

                <!-- Status -->
                <div class="relative">
                    <select v-model="filterStatus"
                        class="text-sm border border-gray-200 rounded-lg pl-3 pr-8 py-1.5 text-gray-700 bg-white focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all appearance-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                        <option value="expired">Expired</option>
                    </select>
                    <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                <!-- Type -->
                <div class="relative">
                    <select v-model="filterType"
                        class="text-sm border border-gray-200 rounded-lg pl-3 pr-8 py-1.5 text-gray-700 bg-white focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all appearance-none cursor-pointer">
                        <option value="">Semua Tipe</option>
                        <option value="percentage">Persentase</option>
                        <option value="fixed">Nominal</option>
                        <option value="free_shipping">Gratis Ongkir</option>
                    </select>
                    <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── TABLE ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden mb-4">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[860px]">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 cursor-pointer select-none" @click="sortBy('code')">
                                Kode Promo
                                <svg class="inline w-3 h-3 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                            </th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Tipe & Nilai</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Min. Pembelian</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 cursor-pointer select-none" @click="sortBy('used_count')">
                                Pemakaian
                                <svg class="inline w-3 h-3 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                            </th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Expired</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Status</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400 w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <!-- Empty -->
                        <tr v-if="paginatedCodes.length === 0">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12V22H4V12M22 7H2v5h20V7zM12 22V7"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-500">Belum ada promo kode</p>
                                    <span class="text-xs text-gray-400">Tambah kode promo baru untuk customer</span>
                                </div>
                            </td>
                        </tr>

                        <tr v-for="promo in paginatedCodes" :key="promo.id"
                            class="hover:bg-gray-50/60 transition-colors duration-150">

                            <!-- Kode -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 border"
                                        :class="discountIconClass(promo.discount_type)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <template v-if="promo.discount_type === 'percentage'"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></template>
                                            <template v-else-if="promo.discount_type === 'fixed'"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></template>
                                            <template v-else><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></template>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-mono font-bold text-sm text-gray-800 tracking-wide">{{ promo.code }}</span>
                                        <span v-if="promo.description" class="block text-xs text-gray-400 mt-0.5">{{ promo.description }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Tipe & Nilai -->
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border mb-1"
                                    :class="typeBadgeClass(promo.discount_type)">
                                    {{ labelType(promo.discount_type) }}
                                </span>
                                <p class="text-sm font-bold text-gray-800 tabular-nums">{{ formatValue(promo) }}</p>
                            </td>

                            <!-- Min Pembelian -->
                            <td class="px-5 py-4">
                                <span v-if="promo.min_purchase > 0" class="text-sm font-semibold text-gray-700 tabular-nums">
                                    Rp {{ Number(promo.min_purchase).toLocaleString('id-ID') }}
                                </span>
                                <span v-else class="text-xs text-gray-300">Tidak ada</span>
                            </td>

                            <!-- Pemakaian -->
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-1.5 min-w-[80px]">
                                    <p class="text-sm text-gray-700">
                                        <span class="font-bold">{{ promo.used_count }}</span>
                                        <span class="text-gray-400 text-xs">{{ promo.max_usage ? ' / ' + promo.max_usage : ' / ∞' }}</span>
                                    </p>
                                    <div v-if="promo.max_usage" class="h-1.5 bg-gray-100 rounded-full overflow-hidden w-24">
                                        <div class="h-full rounded-full transition-all duration-300"
                                            :class="usagePercent(promo) >= 100 ? 'bg-red-500' : usagePercent(promo) >= 75 ? 'bg-amber-400' : 'bg-[#ED1F24]'"
                                            :style="{ width: usagePercent(promo) + '%' }"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Expired -->
                            <td class="px-5 py-4">
                                <div v-if="promo.expired_at">
                                    <span class="text-xs font-medium block"
                                        :class="isExpired(promo.expired_at) ? 'text-gray-400 line-through' : daysUntilExpiry(promo.expired_at) <= 7 ? 'text-amber-600' : 'text-gray-600'">
                                        {{ formatDate(promo.expired_at) }}
                                    </span>
                                    <span v-if="!isExpired(promo.expired_at) && daysUntilExpiry(promo.expired_at) <= 7"
                                        class="inline-block mt-1 text-[10px] font-bold text-red-500 bg-red-50 border border-red-100 px-1.5 py-0.5 rounded-full">
                                        {{ daysUntilExpiry(promo.expired_at) }} hari lagi
                                    </span>
                                </div>
                                <span v-else class="text-xs text-gray-300">Tidak ada</span>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                                    :class="isExpired(promo.expired_at)
                                        ? 'bg-gray-100 text-gray-400 border-gray-200'
                                        : promo.is_active
                                            ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                                            : 'bg-red-50 text-red-400 border-red-100'">
                                    {{ isExpired(promo.expired_at) ? 'Expired' : promo.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openModal('view', promo)" title="Detail"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg border border-gray-200 bg-white text-gray-400 hover:text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button @click="openModal('edit', promo)" title="Edit"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg border border-amber-200 bg-amber-50 text-amber-500 hover:bg-amber-100 hover:border-amber-300 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button @click="deletePromo(promo.id)" title="Hapus"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg border border-red-100 bg-red-50 text-red-400 hover:bg-red-100 hover:border-red-200 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ───────────────────────── PAGINATION ───────────────────────── -->
        <div class="flex flex-wrap items-center justify-between gap-3 py-2">
            <span class="text-xs text-gray-400">
                Menampilkan <span class="font-semibold text-gray-600">{{ paginatedCodes.length }}</span>
                dari <span class="font-semibold text-gray-600">{{ filteredCodes.length }}</span> promo kode
            </span>
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
                        <option :value="10">10 / hal</option>
                        <option :value="20">20 / hal</option>
                        <option :value="50">50 / hal</option>
                    </select>
                    <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════ MODAL ═══════════════════════════ -->
        <Transition name="pc-modal">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                style="background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);"
                @click.self="closeModal">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg border border-gray-200/80 flex flex-col max-h-[90vh] overflow-hidden">

                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                :class="modalMode === 'create' ? 'bg-[#ED1F24]/10 border border-[#ED1F24]/20' : modalMode === 'edit' ? 'bg-amber-50 border border-amber-200' : 'bg-gray-100 border border-gray-200'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                                    :class="modalMode === 'create' ? 'text-[#ED1F24]' : modalMode === 'edit' ? 'text-amber-500' : 'text-gray-500'">
                                    <template v-if="modalMode === 'create'"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></template>
                                    <template v-else-if="modalMode === 'edit'"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></template>
                                    <template v-else><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></template>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800">
                                    {{ modalMode === 'create' ? 'Tambah Promo Kode' : modalMode === 'edit' ? 'Edit Promo Kode' : 'Detail Promo Kode' }}
                                </h3>
                                <p class="text-xs text-gray-400 font-mono font-semibold mt-0.5">{{ selectedPromo?.code || 'Promo baru' }}</p>
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

                    <!-- Body -->
                    <div class="overflow-y-auto flex-1 px-6 py-5 flex flex-col gap-6">

                        <!-- Section: Informasi Promo -->
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 12V22H4V12M22 7H2v5h20V7zM12 22V7"/></svg>
                                Informasi Promo
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Kode Promo <span class="text-[#ED1F24]">*</span></label>
                                    <input v-model="form.code" type="text" placeholder="cth: HEMAT10, FREESHIP"
                                        :disabled="modalMode === 'view' || modalMode === 'edit'"
                                        @input="form.code = form.code.toUpperCase()"
                                        class="w-full font-mono font-bold tracking-widest text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all uppercase"/>
                                    <p v-if="modalMode === 'create'" class="text-[10px] text-gray-400 mt-1">Kode otomatis diubah ke HURUF KAPITAL</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                                    <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                                        <button @click="modalMode !== 'view' && (form.is_active = true)" :disabled="modalMode === 'view'"
                                            class="flex-1 py-2 text-xs font-bold transition-all" :class="form.is_active ? 'bg-emerald-500 text-white' : 'bg-white text-gray-400'">Aktif</button>
                                        <button @click="modalMode !== 'view' && (form.is_active = false)" :disabled="modalMode === 'view'"
                                            class="flex-1 py-2 text-xs font-bold border-l border-gray-200 transition-all" :class="!form.is_active ? 'bg-red-500 text-white' : 'bg-white text-gray-400'">Nonaktif</button>
                                    </div>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Deskripsi</label>
                                    <input v-model="form.description" type="text" placeholder="cth: Diskon 10% untuk semua produk" :disabled="modalMode === 'view'"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all"/>
                                </div>
                                <!-- Toggle Show Popup -->
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                                        Tampilkan di Popup Homepage
                                    </label>
                                    <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                                :class="form.show_popup ? 'bg-[#ED1F24]/10 border border-[#ED1F24]/20' : 'bg-gray-100 border border-gray-200'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                                    :class="form.show_popup ? 'text-[#ED1F24]' : 'text-gray-400'">
                                                    <rect x="3" y="3" width="18" height="14" rx="2"/>
                                                    <path d="M8 21h8M12 17v4"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-semibold text-gray-700">Popup di Homepage</p>
                                                <p class="text-[10px] text-gray-400">Promo ini muncul sebagai popup saat visitor pertama kali buka toko</p>
                                            </div>
                                        </div>
                                        <!-- Toggle switch -->
                                        <button @click="modalMode !== 'view' && (form.show_popup = !form.show_popup)"
                                            :disabled="modalMode === 'view'"
                                            class="relative shrink-0 w-11 h-6 rounded-full transition-all duration-300 focus:outline-none disabled:cursor-not-allowed"
                                            :class="form.show_popup ? 'bg-[#ED1F24]' : 'bg-gray-300'">
                                            <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform duration-300"
                                                :class="form.show_popup ? 'translate-x-5' : 'translate-x-0'"></span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Popup Label (muncul hanya jika show_popup aktif) -->
                                <Transition name="slide-down">
                                    <div v-if="form.show_popup" class="col-span-2">
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                                            Label Grup Popup
                                            <span class="text-gray-300 normal-case font-normal ml-1">(opsional)</span>
                                        </label>
                                        <input v-model="form.popup_label" type="text"
                                            placeholder='cth: "KHUSUS PENGGUNA BARU" atau "PROMO RAMADAN"'
                                            :disabled="modalMode === 'view'"
                                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all"/>
                                        <p class="text-[10px] text-gray-400 mt-1">
                                            Promo dengan label sama akan dikelompokkan dalam satu grup di popup. Kosongkan untuk tampil tanpa grup.
                                        </p>
                                    </div>
                                </Transition>
                            </div>
                        </div>

                        <!-- Section: Pengaturan Diskon -->
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
                                Pengaturan Diskon
                            </p>

                            <!-- Tipe Diskon -->
                            <div class="mb-4">
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Tipe Diskon <span class="text-[#ED1F24]">*</span></label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button v-for="opt in discountTypeOptions" :key="opt.value"
                                        @click="modalMode !== 'view' && (form.discount_type = opt.value)"
                                        :disabled="modalMode === 'view'"
                                        class="flex flex-col items-center gap-1.5 p-3 border-2 rounded-xl transition-all duration-150 text-center"
                                        :class="form.discount_type === opt.value
                                            ? 'border-[#ED1F24] bg-[#ED1F24]/4'
                                            : 'border-gray-200 hover:border-gray-300 bg-white disabled:cursor-not-allowed'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                            :class="form.discount_type === opt.value ? 'text-[#ED1F24]' : 'text-gray-400'"
                                            v-html="opt.icon"></svg>
                                        <span class="text-xs font-bold" :class="form.discount_type === opt.value ? 'text-[#ED1F24]' : 'text-gray-600'">{{ opt.label }}</span>
                                        <span class="text-[10px] text-gray-400 leading-tight">{{ opt.desc }}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Nilai Diskon -->
                                <div v-if="form.discount_type !== 'free_shipping'">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                                        Nilai Diskon <span class="text-[#ED1F24]">*</span>
                                    </label>
                                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#ED1F24] focus-within:ring-2 focus-within:ring-[#ED1F24]/10 transition-all">
                                        <span class="px-3 py-2 bg-gray-50 text-xs font-bold text-gray-400 border-r border-gray-200 shrink-0">
                                            {{ form.discount_type === 'percentage' ? '%' : 'Rp' }}
                                        </span>
                                        <input v-model.number="form.discount_value" type="number" min="0"
                                            :max="form.discount_type === 'percentage' ? 100 : undefined"
                                            :placeholder="form.discount_type === 'percentage' ? 'cth: 10' : 'cth: 20000'"
                                            :disabled="modalMode === 'view'"
                                            class="flex-1 text-sm px-3 py-2 text-gray-700 outline-none bg-white disabled:bg-gray-50 disabled:text-gray-400"/>
                                    </div>
                                    <p v-if="form.discount_type === 'percentage'" class="text-[10px] text-gray-400 mt-1">Maksimal 100%</p>
                                </div>

                                <div v-else class="col-span-2">
                                    <div class="flex items-start gap-2.5 bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-3">
                                        <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                                        <p class="text-xs text-emerald-700">Ongkir ditanggung sepenuhnya oleh sistem. Nilai diskon = biaya ongkir yang dipilih customer.</p>
                                    </div>
                                </div>

                                <!-- Min Pembelian -->
                                <div :class="form.discount_type !== 'free_shipping' ? '' : 'hidden'">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Minimum Pembelian</label>
                                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#ED1F24] focus-within:ring-2 focus-within:ring-[#ED1F24]/10 transition-all">
                                        <span class="px-3 py-2 bg-gray-50 text-xs font-bold text-gray-400 border-r border-gray-200 shrink-0">Rp</span>
                                        <input v-model.number="form.min_purchase" type="number" min="0" placeholder="0 = tidak ada" :disabled="modalMode === 'view'"
                                            class="flex-1 text-sm px-3 py-2 text-gray-700 outline-none bg-white disabled:bg-gray-50 disabled:text-gray-400"/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Batas & Waktu -->
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                Batas Pemakaian & Waktu
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Maksimal Pemakaian</label>
                                    <input v-model.number="form.max_usage" type="number" min="1" placeholder="Kosongkan = unlimited" :disabled="modalMode === 'view'"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all"/>
                                    <p class="text-[10px] text-gray-400 mt-1">Sudah terpakai: <span class="font-bold text-gray-600">{{ selectedPromo?.used_count ?? 0 }}x</span></p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Tanggal Expired</label>
                                    <input v-model="form.expired_at" type="datetime-local" :disabled="modalMode === 'view'"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all"/>
                                    <p class="text-[10px] text-gray-400 mt-1">Kosongkan = tidak ada batas waktu</p>
                                </div>
                            </div>

                            <!-- Usage Progress -->
                            <div v-if="selectedPromo && selectedPromo.max_usage" class="mt-4 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs text-gray-500 font-medium">Progress Pemakaian</span>
                                    <span class="text-xs font-bold text-gray-700">{{ selectedPromo.used_count }} / {{ selectedPromo.max_usage }} kali</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500"
                                        :class="usagePercent(selectedPromo) >= 100 ? 'bg-red-500' : usagePercent(selectedPromo) >= 75 ? 'bg-amber-400' : 'bg-[#ED1F24]'"
                                        :style="{ width: Math.min(100, usagePercent(selectedPromo)) + '%' }"></div>
                                </div>
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
                                Edit Promo
                            </button>
                            <button v-else @click="submitForm" :disabled="loading"
                                class="flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all shadow-sm shadow-red-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                                <svg v-if="loading" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                                {{ loading ? 'Menyimpan...' : (modalMode === 'create' ? 'Tambah Promo' : 'Simpan Perubahan') }}
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
import { format, isPast, differenceInDays } from 'date-fns'
import { id } from 'date-fns/locale'

export default {
    name: 'PromoCodes',
    components: { AdminLayout },

    data() {
        return {
            promoCodes: [],
            loading: false,
            errorMessage: '',

            search: '',
            filterStatus: '',
            filterType: '',
            sortKey: 'code',
            sortDir: 'asc',

            currentPage: 1,
            perPage: 10,

            showModal: false,
            modalMode: 'create',
            selectedPromo: null,
            form: this.emptyForm(),

            discountTypeOptions: [
                { value: 'percentage',    label: 'Persentase',    desc: 'cth: 10% dari subtotal',   icon: '<line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>' },
                { value: 'fixed',         label: 'Nominal Tetap', desc: 'cth: potongan Rp 20.000',  icon: '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>' },
                { value: 'free_shipping', label: 'Gratis Ongkir', desc: 'ongkir ditanggung penuh',  icon: '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>' },
            ],
        }
    },

    computed: {
        filteredCodes() {
            const q = this.search.toLowerCase()
            return this.promoCodes
                .filter(p => {
                    const matchSearch = !q || p.code.toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q)
                    const matchStatus = !this.filterStatus ? true
                        : this.filterStatus === 'active'   ? (p.is_active && !this.isExpired(p.expired_at))
                        : this.filterStatus === 'inactive' ? !p.is_active
                        : this.filterStatus === 'expired'  ? this.isExpired(p.expired_at)
                        : true
                    const matchType = !this.filterType || p.discount_type === this.filterType
                    return matchSearch && matchStatus && matchType
                })
                .sort((a, b) => {
                    const aVal = a[this.sortKey] ?? ''
                    const bVal = b[this.sortKey] ?? ''
                    const cmp = typeof aVal === 'string' ? aVal.localeCompare(bVal) : aVal - bVal
                    return this.sortDir === 'asc' ? cmp : -cmp
                })
        },
        paginatedCodes() {
            const start = (this.currentPage - 1) * this.perPage
            return this.filteredCodes.slice(start, start + this.perPage)
        },
        totalPages()  { return Math.max(1, Math.ceil(this.filteredCodes.length / this.perPage)) },
        activeCount() { return this.promoCodes.filter(p => p.is_active && !this.isExpired(p.expired_at)).length },
        expiredCount(){ return this.promoCodes.filter(p => this.isExpired(p.expired_at)).length },
    },

    watch: {
        search()       { this.currentPage = 1 },
        filterStatus() { this.currentPage = 1 },
        filterType()   { this.currentPage = 1 },
        perPage()      { this.currentPage = 1 },
    },

    mounted() {
        document.title = 'Promo Codes - Two Brothers Vape System'
        this.fetchPromoCodes() 
        },

    methods: {
        emptyForm() {
            return { code: '', description: '', discount_type: 'percentage', discount_value: null, min_purchase: 0, max_usage: null, is_active: true, expired_at: '', show_popup: false, popup_label: '', }
        },

        async fetchPromoCodes() {
            try {
                const res = await axios.get('/promo-codes')
                const raw = res.data?.data ?? res.data ?? []
                this.promoCodes = Array.isArray(raw) ? raw : []
            } catch(e) { console.error('Gagal memuat promo kode:', e) }
        },

        sortBy(key) {
            if (this.sortKey === key) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc'
            else { this.sortKey = key; this.sortDir = 'asc' }
        },

        openModal(mode, promo = null) {
            this.modalMode = mode; this.errorMessage = ''
            if (promo) {
                this.selectedPromo = promo
                this.form = { ...this.emptyForm(), ...promo, expired_at: promo.expired_at ? promo.expired_at.slice(0, 16) : '' }
            } else { this.selectedPromo = null; this.form = this.emptyForm() }
            this.showModal = true
        },

        closeModal() { this.showModal = false; this.errorMessage = '' },

        async submitForm() {
            if (!this.form.code?.trim()) { this.errorMessage = 'Kode promo wajib diisi.'; return }
            if (this.form.discount_type !== 'free_shipping' && (!this.form.discount_value || this.form.discount_value <= 0)) { this.errorMessage = 'Nilai diskon wajib diisi dan harus lebih dari 0.'; return }
            if (this.form.discount_type === 'percentage' && this.form.discount_value > 100) { this.errorMessage = 'Nilai persentase tidak boleh lebih dari 100%.'; return }
            this.loading = true; this.errorMessage = ''
            try {
                const payload = { ...this.form, code: this.form.code.toUpperCase().trim(), discount_value: this.form.discount_type === 'free_shipping' ? 0 : this.form.discount_value, min_purchase: this.form.min_purchase || 0, max_usage: this.form.max_usage || null, expired_at: this.form.expired_at || null }
                if (this.modalMode === 'create') await axios.post('/promo-codes', payload)
                else await axios.put(`/promo-codes/${this.selectedPromo.id}`, payload)
                await this.fetchPromoCodes(); this.closeModal()
            } catch(e) {
                this.errorMessage = e.response?.data?.message || (e.response?.data?.errors ? Object.values(e.response.data.errors).flat().join(', ') : 'Terjadi kesalahan, coba lagi.')
            } finally { this.loading = false }
        },

        async deletePromo(id) {
            if (!confirm('Yakin ingin menghapus promo kode ini?')) return
            try { await axios.delete(`/promo-codes/${id}`); await this.fetchPromoCodes() } catch(e) { alert('Gagal menghapus promo kode.') }
        },

        labelType(type) { return { percentage: 'Persentase', fixed: 'Nominal', free_shipping: 'Gratis Ongkir' }[type] ?? type },
        formatValue(promo) {
            if (promo.discount_type === 'percentage') return `${promo.discount_value}%`
            if (promo.discount_type === 'free_shipping') return 'Gratis Ongkir'
            return 'Rp ' + Number(promo.discount_value).toLocaleString('id-ID')
        },
        discountIconClass(type) {
            return { percentage: 'bg-purple-50 border-purple-100 text-purple-600', fixed: 'bg-blue-50 border-blue-100 text-blue-600', free_shipping: 'bg-emerald-50 border-emerald-100 text-emerald-600' }[type] ?? 'bg-gray-100 border-gray-200 text-gray-500'
        },
        typeBadgeClass(type) {
            return { percentage: 'bg-purple-50 text-purple-600 border-purple-100', fixed: 'bg-blue-50 text-blue-600 border-blue-100', free_shipping: 'bg-emerald-50 text-emerald-600 border-emerald-100' }[type] ?? 'bg-gray-100 text-gray-500 border-gray-200'
        },
        isExpired(expiredAt)        { if (!expiredAt) return false; return isPast(new Date(expiredAt)) },
        daysUntilExpiry(expiredAt)  { if (!expiredAt) return Infinity; return differenceInDays(new Date(expiredAt), new Date()) },
        formatDate(dateStr)         { if (!dateStr) return '—'; return format(new Date(dateStr), 'd MMM yyyy, HH:mm', { locale: id }) },
        usagePercent(promo)         { if (!promo.max_usage) return 0; return Math.min(100, Math.round((promo.used_count / promo.max_usage) * 100)) },
    }
}
</script>

<style scoped>
.pc-modal-enter-active, .pc-modal-leave-active { transition: all .2s; }
.pc-modal-enter-from, .pc-modal-leave-to { opacity: 0; transform: scale(.97); }
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.2s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-6px); }
</style>