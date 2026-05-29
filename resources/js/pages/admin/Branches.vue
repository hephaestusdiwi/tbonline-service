<template>
    <AdminLayout title="Branch Management">

        <!-- ───────────────────────── HEADER ───────────────────────── -->
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#ED1F24]/10 border border-[#ED1F24]/20 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-[#ED1F24]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Cabang Toko</h1>
                        <span class="text-[10px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 border border-gray-200">Store Management</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Kelola alamat dan informasi cabang toko Anda</p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <!-- Stats Pills -->
                <div class="hidden sm:flex items-center gap-0 bg-white border border-gray-200/80 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-4 py-2.5 text-center border-r border-gray-100">
                        <p class="text-base font-bold text-gray-900 tabular-nums">{{ branches.length }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total</p>
                    </div>
                    <div class="px-4 py-2.5 text-center border-r border-gray-100">
                        <p class="text-base font-bold text-emerald-600 tabular-nums">{{ activeCount }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aktif</p>
                    </div>
                    <div class="px-4 py-2.5 text-center">
                        <p class="text-base font-bold text-gray-400 tabular-nums">{{ inactiveCount }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nonaktif</p>
                    </div>
                </div>

                <div class="w-px h-8 bg-gray-200 hidden sm:block"></div>

                <button @click="openModal('create')"
                    class="flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all duration-150 shadow-sm shadow-red-200 hover:shadow-md hover:shadow-red-200 active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Cabang
                </button>
            </div>
        </div>

        <!-- ───────────────────────── FILTER BAR ───────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm mb-4 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Filter Cabang</span>
            </div>
            <div class="px-5 py-4 flex flex-wrap items-center gap-3">
                <!-- Search -->
                <div class="flex items-center gap-2 flex-1 min-w-[200px] border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus-within:border-[#ED1F24] focus-within:ring-2 focus-within:ring-[#ED1F24]/10 transition-all">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Cari nama atau alamat cabang..."
                        class="text-sm text-gray-700 placeholder-gray-400 outline-none bg-transparent w-full"/>
                </div>

                <!-- Status -->
                <div class="relative">
                    <select v-model="filterStatus"
                        class="text-sm border border-gray-200 rounded-lg pl-3 pr-8 py-1.5 text-gray-700 bg-white focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 transition-all appearance-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                    <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                <!-- View toggle -->
                <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                    <button @click="viewMode = 'table'"
                        class="flex items-center justify-center w-7 h-7 rounded-md transition-all"
                        :class="viewMode === 'table' ? 'bg-white shadow-sm text-gray-700' : 'text-gray-400 hover:text-gray-600'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    </button>
                    <button @click="viewMode = 'grid'"
                        class="flex items-center justify-center w-7 h-7 rounded-md transition-all"
                        :class="viewMode === 'grid' ? 'bg-white shadow-sm text-gray-700' : 'text-gray-400 hover:text-gray-600'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── TABLE VIEW ───────────────────────── -->
        <div v-if="viewMode === 'table'" class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden mb-4">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[860px]">
                    <thead>
                        <tr class="bg-gray-50/60 border-b border-gray-100">
                            <th class="px-5 py-3 text-left w-10">
                                <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected"
                                    class="w-3.5 h-3.5 rounded accent-[#ED1F24] cursor-pointer"/>
                            </th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 cursor-pointer select-none" @click="sortBy('name')">
                                Nama Cabang
                                <svg class="inline w-3 h-3 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                            </th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Alamat</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Telepon</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Jam Operasional</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Maps</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Status</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400 w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="paginatedBranches.length === 0">
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-500">Belum ada cabang</p>
                                    <span class="text-xs text-gray-400">Tambah cabang toko baru</span>
                                </div>
                            </td>
                        </tr>

                        <tr v-for="branch in paginatedBranches" :key="branch.id"
                            class="hover:bg-gray-50/60 transition-colors duration-150"
                            :class="selectedIds.includes(branch.id) ? 'bg-[#ED1F24]/5' : ''">

                            <td class="px-5 py-4">
                                <input type="checkbox" :value="branch.id" v-model="selectedIds"
                                    class="w-3.5 h-3.5 rounded accent-[#ED1F24] cursor-pointer"/>
                            </td>

                            <!-- Nama -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#ED1F24] to-rose-400 flex items-center justify-center shrink-0 text-white text-sm font-bold">
                                        {{ branch.name?.charAt(0)?.toUpperCase() || '?' }}
                                    </div>
                                    <span class="font-semibold text-sm text-gray-800">{{ branch.name }}</span>
                                </div>
                            </td>

                            <!-- Alamat -->
                            <td class="px-5 py-4">
                                <span class="text-xs text-gray-500 line-clamp-2 max-w-[200px] block leading-relaxed">{{ branch.address || '—' }}</span>
                            </td>

                            <!-- Telepon -->
                            <td class="px-5 py-4">
                                <a v-if="branch.phone" :href="`tel:${branch.phone}`"
                                    class="flex items-center gap-1.5 text-xs text-[#ED1F24] font-medium hover:underline">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 014.69 13.1 19.79 19.79 0 011.61 4.53 2 2 0 013.6 2.36h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L7.91 9.91a16 16 0 006 6l.92-.92a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0121.73 17z"/></svg>
                                    {{ branch.phone }}
                                </a>
                                <span v-else class="text-xs text-gray-300">—</span>
                            </td>

                            <!-- Jam Operasional -->
                            <td class="px-5 py-4">
                                <div v-if="branch.operating_hours && branch.operating_hours.length" class="flex flex-wrap gap-1">
                                    <span v-for="(oh, i) in branch.operating_hours.slice(0, 2)" :key="i"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100">
                                        {{ formatDayShort(oh.days) }} {{ oh.open }}–{{ oh.close }}
                                    </span>
                                    <span v-if="branch.operating_hours.length > 2"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-400 border border-gray-200">
                                        +{{ branch.operating_hours.length - 2 }}
                                    </span>
                                </div>
                                <span v-else class="text-xs text-gray-300">—</span>
                            </td>

                            <!-- Maps -->
                            <td class="px-5 py-4">
                                <a v-if="branch.google_maps_url" :href="branch.google_maps_url" target="_blank"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 hover:bg-emerald-100 transition-all">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    Maps
                                </a>
                                <span v-else class="text-xs text-gray-300">—</span>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                                    :class="branch.is_active
                                        ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                                        : 'bg-red-50 text-red-400 border-red-100'">
                                    {{ branch.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openModal('view', branch)" title="Detail"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg border border-gray-200 bg-white text-gray-400 hover:text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button @click="openModal('edit', branch)" title="Edit"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg border border-amber-200 bg-amber-50 text-amber-500 hover:bg-amber-100 hover:border-amber-300 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button @click="deleteBranch(branch.id)" title="Hapus"
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

        <!-- ───────────────────────── GRID VIEW ───────────────────────── -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
            <div v-if="paginatedBranches.length === 0" class="col-span-full">
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-6 py-16 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-500">Belum ada cabang</p>
                    </div>
                </div>
            </div>

            <div v-for="branch in paginatedBranches" :key="branch.id"
                class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden hover:shadow-md hover:border-gray-300 transition-all duration-200">

                <!-- Card header -->
                <div class="px-4 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#ED1F24] to-rose-400 flex items-center justify-center shrink-0 text-white text-base font-bold">
                        {{ branch.name?.charAt(0)?.toUpperCase() || '?' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">{{ branch.name }}</p>
                        <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                            :class="branch.is_active
                                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                                : 'bg-red-50 text-red-400 border-red-100'">
                            {{ branch.is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button @click="openModal('edit', branch)" title="Edit"
                            class="flex items-center justify-center w-7 h-7 rounded-lg border border-amber-200 bg-amber-50 text-amber-500 hover:bg-amber-100 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button @click="deleteBranch(branch.id)" title="Hapus"
                            class="flex items-center justify-center w-7 h-7 rounded-lg border border-red-100 bg-red-50 text-red-400 hover:bg-red-100 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Card body -->
                <div class="px-4 py-3 flex flex-col gap-2.5">
                    <div v-if="branch.address" class="flex items-start gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span class="text-xs text-gray-500 leading-relaxed">{{ branch.address }}</span>
                    </div>
                    <div v-if="branch.phone" class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 014.69 13.1 19.79 19.79 0 011.61 4.53 2 2 0 013.6 2.36h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L7.91 9.91a16 16 0 006 6l.92-.92a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0121.73 17z"/></svg>
                        <a :href="`tel:${branch.phone}`" class="text-xs text-[#ED1F24] font-medium hover:underline">{{ branch.phone }}</a>
                    </div>
                    <div v-if="branch.operating_hours && branch.operating_hours.length" class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span class="text-xs text-gray-500">{{ branch.operating_hours[0].days }}, {{ branch.operating_hours[0].open }}–{{ branch.operating_hours[0].close }}</span>
                    </div>
                </div>

                <!-- Card foot -->
                <div v-if="branch.google_maps_url" class="px-4 py-3 border-t border-gray-100">
                    <a :href="branch.google_maps_url" target="_blank"
                        class="flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Buka di Google Maps
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- ───────────────────────── PAGINATION ───────────────────────── -->
        <div class="flex flex-wrap items-center justify-between gap-3 py-2">
            <div class="flex items-center gap-2">
                <template v-if="selectedIds.length > 0">
                    <span class="text-xs font-semibold text-[#ED1F24]">{{ selectedIds.length }} item dipilih</span>
                    <button @click="bulkDelete"
                        class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 transition-all">
                        Hapus Pilihan
                    </button>
                    <button @click="selectedIds = []"
                        class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 transition-all">
                        Batal
                    </button>
                </template>
                <span v-else class="text-xs text-gray-400">
                    Menampilkan <span class="font-semibold text-gray-600">{{ paginatedBranches.length }}</span>
                    dari <span class="font-semibold text-gray-600">{{ filteredBranches.length }}</span> cabang
                </span>
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
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl border border-gray-200/80 flex flex-col max-h-[90vh] overflow-hidden">

                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                :class="modalMode === 'create' ? 'bg-[#ED1F24]/10 border border-[#ED1F24]/20'
                                      : modalMode === 'edit'   ? 'bg-amber-50 border border-amber-200'
                                      :                          'bg-gray-100 border border-gray-200'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                                    :class="modalMode === 'create' ? 'text-[#ED1F24]' : modalMode === 'edit' ? 'text-amber-500' : 'text-gray-500'">
                                    <template v-if="modalMode === 'create'"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></template>
                                    <template v-else-if="modalMode === 'edit'"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></template>
                                    <template v-else><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></template>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800">
                                    {{ modalMode === 'create' ? 'Tambah Cabang' : modalMode === 'edit' ? 'Edit Cabang' : 'Detail Cabang' }}
                                </h3>
                                <p class="text-xs text-gray-400 font-semibold mt-0.5">{{ selectedBranch?.name || 'Cabang baru' }}</p>
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

                        <!-- Section: Informasi Cabang -->
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                Informasi Cabang
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Nama Cabang <span class="text-[#ED1F24]">*</span></label>
                                    <input v-model="form.name" type="text" placeholder="cth: Cabang Pusat, Cabang Bandung..."
                                        :disabled="modalMode === 'view'"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all"/>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Alamat Lengkap <span class="text-[#ED1F24]">*</span></label>
                                    <textarea v-model="form.address" rows="3" placeholder="Jl. Contoh No. 123, Kelurahan, Kecamatan, Kota, Provinsi..."
                                        :disabled="modalMode === 'view'"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all resize-none"></textarea>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Kota <span class="text-[#ED1F24]">*</span></label>
                                    <input v-model="form.city" type="text" placeholder="cth: Bandung"
                                        :disabled="modalMode === 'view'"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all"/>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Provinsi <span class="text-[#ED1F24]">*</span></label>
                                    <input v-model="form.province" type="text" placeholder="cth: Jawa Barat"
                                        :disabled="modalMode === 'view'"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all"/>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Latitude</label>
                                    <input v-model="form.latitude" type="number" step="any" placeholder="cth: -6.917464"
                                        :disabled="modalMode === 'view'"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all"/>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Longitude</label>
                                    <input v-model="form.longitude" type="number" step="any" placeholder="cth: 107.619125"
                                        :disabled="modalMode === 'view'"
                                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-gray-50 disabled:text-gray-400 transition-all"/>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Nomor Telepon</label>
                                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#ED1F24] focus-within:ring-2 focus-within:ring-[#ED1F24]/10 transition-all"
                                        :class="modalMode === 'view' ? 'bg-gray-50' : ''">
                                        <span class="px-3 py-2 bg-gray-50 border-r border-gray-200 shrink-0">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 014.69 13.1 19.79 19.79 0 011.61 4.53 2 2 0 013.6 2.36h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L7.91 9.91a16 16 0 006 6l.92-.92a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0121.73 17z"/></svg>
                                        </span>
                                        <input v-model="form.phone" type="text" placeholder="08xx-xxxx-xxxx"
                                            :disabled="modalMode === 'view'"
                                            class="flex-1 text-sm px-3 py-2 text-gray-700 outline-none bg-white disabled:bg-gray-50 disabled:text-gray-400"/>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Google Maps URL</label>
                                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden focus-within:border-[#ED1F24] focus-within:ring-2 focus-within:ring-[#ED1F24]/10 transition-all"
                                        :class="modalMode === 'view' ? 'bg-gray-50' : ''">
                                        <span class="px-3 py-2 bg-gray-50 border-r border-gray-200 shrink-0">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        </span>
                                        <input v-model="form.google_maps_url" type="url" placeholder="https://maps.google.com/..."
                                            :disabled="modalMode === 'view'"
                                            class="flex-1 text-sm px-3 py-2 text-gray-700 outline-none bg-white disabled:bg-gray-50 disabled:text-gray-400"/>
                                    </div>
                                    <a v-if="form.google_maps_url && modalMode === 'view'" :href="form.google_maps_url" target="_blank"
                                        class="text-[10px] text-[#ED1F24] font-semibold underline mt-1 block">Buka di Google Maps ↗</a>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                                    <div class="flex rounded-lg border border-gray-200 overflow-hidden w-48">
                                        <button @click="modalMode !== 'view' && (form.is_active = true)" :disabled="modalMode === 'view'"
                                            class="flex-1 py-2 text-xs font-bold transition-all"
                                            :class="form.is_active ? 'bg-emerald-500 text-white' : 'bg-white text-gray-400'">Aktif</button>
                                        <button @click="modalMode !== 'view' && (form.is_active = false)" :disabled="modalMode === 'view'"
                                            class="flex-1 py-2 text-xs font-bold border-l border-gray-200 transition-all"
                                            :class="!form.is_active ? 'bg-red-500 text-white' : 'bg-white text-gray-400'">Nonaktif</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Jam Operasional -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    Jam Operasional
                                </p>
                                <button v-if="modalMode !== 'view'" @click="addOperatingHour"
                                    class="flex items-center gap-1 text-[10px] font-bold text-[#ED1F24] border border-[#ED1F24]/30 bg-[#ED1F24]/5 hover:bg-[#ED1F24]/10 px-2.5 py-1 rounded-lg transition-all">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Tambah Jadwal
                                </button>
                            </div>

                            <!-- Empty hours -->
                            <div v-if="form.operating_hours.length === 0"
                                class="flex flex-col items-center gap-2 py-8 border-2 border-dashed border-gray-200 rounded-xl text-center">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <p class="text-xs font-semibold text-gray-400">Belum ada jadwal operasional</p>
                                <span v-if="modalMode !== 'view'" class="text-[11px] text-gray-300">Klik "Tambah Jadwal" untuk menambahkan</span>
                            </div>

                            <!-- Hour rows -->
                            <div v-else class="flex flex-col gap-3">
                                <div v-for="(oh, i) in form.operating_hours" :key="i"
                                    class="flex items-end gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-200 text-[10px] font-bold text-gray-500 shrink-0 mb-0.5">{{ i + 1 }}</span>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 flex-1">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Hari</label>
                                            <input v-model="oh.days" type="text" placeholder="cth: Senin – Jumat"
                                                :disabled="modalMode === 'view'"
                                                class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-white disabled:text-gray-400 transition-all"/>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Buka</label>
                                            <input v-model="oh.open" type="time"
                                                :disabled="modalMode === 'view'"
                                                class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-white disabled:text-gray-400 transition-all"/>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tutup</label>
                                            <input v-model="oh.close" type="time"
                                                :disabled="modalMode === 'view'"
                                                class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-white disabled:text-gray-400 transition-all"/>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Catatan</label>
                                            <input v-model="oh.note" type="text" placeholder="cth: Termasuk hari libur"
                                                :disabled="modalMode === 'view'"
                                                class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 text-gray-700 focus:outline-none focus:border-[#ED1F24] focus:ring-2 focus:ring-[#ED1F24]/10 disabled:bg-white disabled:text-gray-400 transition-all"/>
                                        </div>
                                    </div>
                                    <button v-if="modalMode !== 'view'" @click="removeOperatingHour(i)"
                                        class="flex items-center justify-center w-6 h-6 rounded-lg border border-red-100 bg-red-50 text-red-400 hover:bg-red-100 transition-all shrink-0 mb-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Quick fill -->
                            <div v-if="modalMode !== 'view'" class="flex items-center gap-2 flex-wrap mt-3">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Isi cepat:</span>
                                <button @click="quickFill('weekday')"
                                    class="text-[10px] font-bold px-2.5 py-1 rounded-full border border-gray-200 bg-gray-50 text-gray-600 hover:border-[#ED1F24]/30 hover:bg-[#ED1F24]/5 hover:text-[#ED1F24] transition-all">
                                    Senin–Jumat
                                </button>
                                <button @click="quickFill('everyday')"
                                    class="text-[10px] font-bold px-2.5 py-1 rounded-full border border-gray-200 bg-gray-50 text-gray-600 hover:border-[#ED1F24]/30 hover:bg-[#ED1F24]/5 hover:text-[#ED1F24] transition-all">
                                    Setiap Hari
                                </button>
                                <button @click="quickFill('weekend')"
                                    class="text-[10px] font-bold px-2.5 py-1 rounded-full border border-gray-200 bg-gray-50 text-gray-600 hover:border-[#ED1F24]/30 hover:bg-[#ED1F24]/5 hover:text-[#ED1F24] transition-all">
                                    Sabtu–Minggu
                                </button>
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
                                class="flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white transition-all active:scale-95">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Cabang
                            </button>
                            <button v-else @click="submitForm" :disabled="loading"
                                class="flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-[#ED1F24] hover:bg-[#C81A1E] text-white transition-all shadow-sm shadow-red-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                                <svg v-if="loading" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                                {{ loading ? 'Menyimpan...' : (modalMode === 'create' ? 'Tambah Cabang' : 'Simpan Perubahan') }}
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

export default {
    name: 'Branches',
    components: { AdminLayout },

    data() {
        return {
            branches: [],
            loading: false,
            errorMessage: '',

            search: '',
            filterStatus: '',
            viewMode: 'table',
            sortKey: 'name',
            sortDir: 'asc',

            currentPage: 1,
            perPage: 10,

            selectedIds: [],

            showModal: false,
            modalMode: 'create',
            selectedBranch: null,
            form: this.emptyForm(),
        }
    },

    computed: {
        filteredBranches() {
            return this.branches
                .filter(b => {
                    const q = this.search.toLowerCase()
                    const matchSearch = !q ||
                        (b.name || '').toLowerCase().includes(q) ||
                        (b.address || '').toLowerCase().includes(q) ||
                        (b.phone || '').toLowerCase().includes(q)
                    const matchStatus =
                        !this.filterStatus ? true :
                        this.filterStatus === 'active' ? b.is_active :
                        this.filterStatus === 'inactive' ? !b.is_active : true
                    return matchSearch && matchStatus
                })
                .sort((a, b) => {
                    const aVal = a[this.sortKey] ?? ''
                    const bVal = b[this.sortKey] ?? ''
                    const cmp = typeof aVal === 'string' ? aVal.localeCompare(bVal) : aVal - bVal
                    return this.sortDir === 'asc' ? cmp : -cmp
                })
        },
        paginatedBranches() {
            const start = (this.currentPage - 1) * this.perPage
            return this.filteredBranches.slice(start, start + this.perPage)
        },
        totalPages()    { return Math.max(1, Math.ceil(this.filteredBranches.length / this.perPage)) },
        activeCount()   { return this.branches.filter(b => b.is_active).length },
        inactiveCount() { return this.branches.filter(b => !b.is_active).length },
        isAllSelected() {
            return this.paginatedBranches.length > 0 &&
                this.paginatedBranches.every(b => this.selectedIds.includes(b.id))
        },
    },

    watch: {
        search()       { this.currentPage = 1 },
        filterStatus() { this.currentPage = 1 },
        perPage()      { this.currentPage = 1 },
    },

    mounted() { this.fetchBranches() },

    methods: {
        emptyForm() {
            return { name: '', address: '', city: '', province: '', latitude: '', longitude: '', phone: '', google_maps_url: '', is_active: true, operating_hours: [] }
        },

        async fetchBranches() {
            try {
                const res = await axios.get('/branches')
                const raw = res.data?.data ?? res.data ?? []
                this.branches = Array.isArray(raw)
                    ? raw.map((b, i) => ({ ...b, id: b.id ?? i, operating_hours: b.operating_hours ?? [] }))
                    : []
            } catch (e) { console.error('Gagal memuat cabang:', e) }
        },

        sortBy(key) {
            if (this.sortKey === key) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc'
            else { this.sortKey = key; this.sortDir = 'asc' }
        },

        toggleSelectAll(e) {
            if (e.target.checked) this.selectedIds = [...new Set([...this.selectedIds, ...this.paginatedBranches.map(b => b.id)])]
            else { const pageIds = this.paginatedBranches.map(b => b.id); this.selectedIds = this.selectedIds.filter(id => !pageIds.includes(id)) }
        },

        formatDayShort(days) {
            return (days || '').replace('Senin', 'Sen').replace('Selasa', 'Sel')
                .replace('Rabu', 'Rab').replace('Kamis', 'Kam')
                .replace('Jumat', 'Jum').replace('Sabtu', 'Sab').replace('Minggu', 'Min')
        },

        addOperatingHour() { this.form.operating_hours.push({ days: '', open: '09:00', close: '21:00', note: '' }) },
        removeOperatingHour(i) { this.form.operating_hours.splice(i, 1) },

        quickFill(type) {
            const map = {
                weekday:  { days: 'Senin – Jumat',   open: '09:00', close: '17:00', note: '' },
                everyday: { days: 'Senin – Minggu',  open: '09:00', close: '21:00', note: '' },
                weekend:  { days: 'Sabtu – Minggu',  open: '10:00', close: '20:00', note: '' },
            }
            this.form.operating_hours.push({ ...map[type] })
        },

        openModal(mode, branch = null) {
            this.modalMode = mode; this.errorMessage = ''
            if (branch) {
                this.selectedBranch = branch
                this.form = { ...this.emptyForm(), ...branch, operating_hours: branch.operating_hours ? branch.operating_hours.map(oh => ({ ...oh })) : [] }
            } else { this.selectedBranch = null; this.form = this.emptyForm() }
            this.showModal = true
        },

        closeModal() { this.showModal = false; this.errorMessage = '' },

        async submitForm() {
            if (!this.form.name?.trim())     { this.errorMessage = 'Nama cabang wajib diisi.'; return }
            if (!this.form.address?.trim())  { this.errorMessage = 'Alamat cabang wajib diisi.'; return }
            if (!this.form.city?.trim())     { this.errorMessage = 'Kota wajib diisi.'; return }
            if (!this.form.province?.trim()) { this.errorMessage = 'Provinsi wajib diisi.'; return }
            this.loading = true; this.errorMessage = ''
            try {
                if (this.modalMode === 'create') await axios.post('/branches', this.form)
                else await axios.put(`/branches/${this.selectedBranch.id}`, this.form)
                await this.fetchBranches(); this.closeModal()
            } catch (e) {
                this.errorMessage = e.response?.data?.message || 'Terjadi kesalahan, coba lagi.'
            } finally { this.loading = false }
        },

        async deleteBranch(id) {
            if (!confirm('Yakin ingin menghapus cabang ini?')) return
            try { await axios.delete(`/branches/${id}`); await this.fetchBranches() }
            catch { alert('Gagal menghapus cabang.') }
        },

        async bulkDelete() {
            if (!confirm(`Yakin ingin menghapus ${this.selectedIds.length} cabang yang dipilih?`)) return
            try { await axios.post('/branches/bulk-delete', { ids: this.selectedIds }); this.selectedIds = []; await this.fetchBranches() }
            catch { alert('Gagal menghapus cabang.') }
        },
    }
}
</script>

<style scoped>
.pc-modal-enter-active, .pc-modal-leave-active { transition: all .2s; }
.pc-modal-enter-from, .pc-modal-leave-to { opacity: 0; transform: scale(.97); }
</style>