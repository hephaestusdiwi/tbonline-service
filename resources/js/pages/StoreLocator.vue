<template>
    <div class="min-h-screen bg-white">
        <Navbar />

        <!-- ═══════════════════════════════════════
             HERO BANNER
        ════════════════════════════════════════ -->
        <div class="sl-page-wrap">
            <div class="sl-hero">
                <div class="sl-hero__inner">
                    <div class="sl-hero__text">
                        <span class="sl-hero__badge">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                            </svg>
                            Store Location
                        </span>
                        <h1 class="sl-hero__title">Store<br/><span>Terdekat Kami</span></h1>
                        <p class="sl-hero__sub">Kunjungi cabang kami yang tersebar di seluruh Indonesia.</p>
                    </div>
                    <button class="sl-nearest-btn" :disabled="geoLoading" @click="handleFindNearest">
                        <template v-if="!geoLoading">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
                            </svg>
                            Cabang Terdekat
                        </template>
                        <template v-else>
                            <div class="sl-btn-spinner"></div>
                            Mencari...
                        </template>
                    </button>
                </div>
            </div>
        </div>

        <!-- Error banner -->
        <transition name="sl-slide-down">
            <div class="sl-banner" v-if="geoError || error">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                {{ geoError || error }}
                <button @click="geoError = null; error = null">✕</button>
            </div>
        </transition>

        <!-- ═══════════════════════════════════════
             LOCATOR BODY
        ════════════════════════════════════════ -->
        <div class="sl-body">

            <!-- ─── SIDEBAR ─── -->
            <aside class="sl-sidebar">

                <div class="sl-sidebar__inner">
                    <!-- Search -->
                    <div class="sl-search" :class="{ 'sl-search--focused': searchFocused }">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama cabang atau kota..."
                            class="sl-search__input"
                            @focus="searchFocused = true"
                            @blur="searchFocused = false"
                        />
                        <button v-if="searchQuery" class="sl-search__clear"
                            @click="searchQuery = ''" type="button">
                            <svg width="11" height="11" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                                <path d="M18 6L6 18M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Filters -->
                    <div class="sl-filters">
                        <div class="sl-select-wrap">
                            <select v-model="selectedProvince" @change="selectedCity = ''" class="sl-select">
                                <option value="">Semua Provinsi</option>
                                <option v-for="p in provinces" :key="p" :value="p">{{ p }}</option>
                            </select>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                class="sl-select__arrow">
                                <path d="M6 9l6 6 6-6"/>
                            </svg>
                        </div>
                        <div class="sl-select-wrap">
                            <select v-model="selectedCity" class="sl-select">
                                <option value="">Semua Kota</option>
                                <option v-for="c in filteredCities" :key="c.city" :value="c.city">
                                    {{ c.city }}
                                </option>
                            </select>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                class="sl-select__arrow">
                                <path d="M6 9l6 6 6-6"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Result bar -->
                    <div class="sl-result-bar">
                        <span><b>{{ filteredBranches.length }}</b> cabang ditemukan</span>
                        <button v-if="searchQuery || selectedCity || selectedProvince"
                            class="sl-reset-btn" @click="resetFilters">Reset</button>
                    </div>
                </div>

                <!-- Branch list -->
                <div class="sl-list" ref="listEl">

                    <!-- Skeleton -->
                    <template v-if="loading">
                        <div v-for="i in 5" :key="i" class="sl-skeleton">
                            <div class="sl-skeleton__line" style="width:55%"></div>
                            <div class="sl-skeleton__line" style="width:35%"></div>
                            <div class="sl-skeleton__line" style="width:75%"></div>
                        </div>
                    </template>

                    <!-- Empty -->
                    <div v-else-if="!filteredBranches.length" class="sl-empty">
                        <svg width="44" height="44" viewBox="0 0 24 24"
                            fill="none" stroke="#ddd" stroke-width="1.5" stroke-linecap="round">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                        <p class="sl-empty__title">Cabang tidak ditemukan</p>
                        <p class="sl-empty__sub">Coba ubah kata kunci atau filter.</p>
                        <button class="sl-empty__btn" @click="resetFilters">Reset Pencarian</button>
                    </div>

                    <!-- Cards -->
                    <TransitionGroup v-else name="sl-list-anim" tag="div" class="sl-cards">
                        <button
                            v-for="branch in filteredBranches"
                            :key="branch.id"
                            class="sl-card"
                            :class="{ 'sl-card--active': activeBranch?.id === branch.id }"
                            @click="selectBranch(branch)"
                        >
                            <div class="sl-card__accent"></div>
                            <div class="sl-card__body">
                                <div class="sl-card__top">
                                    <div>
                                        <span class="sl-card__name">{{ branch.name }}</span>
                                        <span class="sl-card__city">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                            </svg>
                                            {{ branch.city }}, {{ branch.province }}
                                        </span>
                                    </div>
                                    <span v-if="branch.distance_km !== undefined" class="sl-card__dist">
                                        {{ branch.distance_km < 1 ? Math.round(branch.distance_km * 1000) + ' m' : branch.distance_km.toFixed(1) + ' km' }}
                                    </span>
                                </div>
                                <p class="sl-card__address">{{ branch.address }}</p>
                                <div class="sl-card__meta">
                                    <span v-if="branch.phone" class="sl-card__pill">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1C8.61 21 3 15.39 3 8.5c0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                                        </svg>
                                        {{ branch.phone }}
                                    </span>
                                    <span v-if="branch.operating_hours?.[0]" class="sl-card__pill">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/>
                                        </svg>
                                        {{ branch.operating_hours[0].open }} – {{ branch.operating_hours[0].close }}
                                    </span>
                                </div>
                            </div>
                            <svg class="sl-card__arrow" width="13" height="13" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <path d="M9 18l6-6-6-6"/>
                            </svg>
                        </button>
                    </TransitionGroup>
                </div>
            </aside>

            <!-- ─── MAP ─── -->
            <main class="sl-map-wrap">
                <div ref="mapEl" class="sl-map"></div>

                <!-- Map loading -->
                <transition name="sl-fade">
                    <div v-if="loading" class="sl-map-overlay">
                        <div class="sl-map-spinner">
                            <div class="sl-spinner-ring"></div>
                            <span>Memuat peta...</span>
                        </div>
                    </div>
                </transition>

                <!-- Badge count -->
                <div class="sl-map-badge" v-if="!loading && filteredBranches.length">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                    </svg>
                    {{ filteredBranches.length }} Cabang
                </div>
            </main>
        </div>

        <!-- ═══════════════════════════════════════
             MOBILE BOTTOM SHEET
        ════════════════════════════════════════ -->
        <transition name="sl-sheet">
            <div class="sl-sheet" v-if="activeBranch && isMobile">
                <div class="sl-sheet__handle" @click="activeBranch = null"></div>
                <div class="sl-sheet__body">
                    <div class="sl-sheet__head">
                        <div>
                            <h3 class="sl-sheet__name">{{ activeBranch.name }}</h3>
                            <span class="sl-sheet__city">{{ activeBranch.city }}, {{ activeBranch.province }}</span>
                        </div>
                        <button class="sl-sheet__close" @click="activeBranch = null">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <path d="M18 6L6 18M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p class="sl-sheet__address">{{ activeBranch.address }}</p>
                    <a v-if="activeBranch.phone" :href="`tel:${activeBranch.phone}`" class="sl-sheet__phone">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="#BD2028">
                            <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1C8.61 21 3 15.39 3 8.5c0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                        </svg>
                        {{ activeBranch.phone }}
                    </a>
                    <a :href="activeBranch.directions_url" target="_blank" rel="noopener" class="sl-sheet__dir">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="white">
                            <path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/>
                        </svg>
                        Petunjuk Arah (Google Maps)
                    </a>
                </div>
            </div>
        </transition>

        <FooterSection />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'
import 'leaflet.markercluster'
import axiosInstance from '../axios'
import { useHead } from '@vueuse/head'
import { useSiteSettings } from '../composables/useSiteSettings'

import Navbar      from '../components/Navbar.vue'
import FooterSection from '../components/FooterSection.vue'

// ── state ─────────────────────────────────────────────────────────────────────
const branches         = ref([])
const cities           = ref([])
const loading          = ref(false)
const error            = ref(null)
const geoError         = ref(null)
const geoLoading       = ref(false)
const activeBranch     = ref(null)
const userPosition     = ref(null)
const isMobile         = ref(false)
const searchFocused    = ref(false)
const searchQuery      = ref('')
const selectedCity     = ref('')
const selectedProvince = ref('')
const mapEl            = ref(null)
const listEl           = ref(null)
const { siteName, fetchSettings } = useSiteSettings()
let mapInitialized = false
let resizeObserver = null

// leaflet refs
let map          = null
let clusterGroup = null
let userMarker   = null
const markerMap  = new Map()

const BRAND = '#BD2028'

// ── computed ──────────────────────────────────────────────────────────────────

const filteredBranches = computed(() => {
    let list = branches.value
    const q  = searchQuery.value.trim().toLowerCase()
    if (q) list = list.filter(b =>
        b.name.toLowerCase().includes(q) ||
        b.city?.toLowerCase().includes(q) ||
        b.province?.toLowerCase().includes(q) ||
        b.address.toLowerCase().includes(q)
    )
    if (selectedCity.value)     list = list.filter(b => b.city === selectedCity.value)
    if (selectedProvince.value) list = list.filter(b => b.province === selectedProvince.value)
    return list
})

onMounted(async () => {
    checkMobile()
    window.addEventListener('resize', checkMobile)

    await fetchSettings()
    document.title = `Lokasi Toko Kami - ${siteName.value}`

    await nextTick()
    resizeObserver = new ResizeObserver((entries) => {
        const entry = entries[0]
        const { width, height } = entry.contentRect
        if (width > 0 && height > 0 && !mapInitialized) {
            mapInitialized = true
            initMap()
            resizeObserver.disconnect() 
        } else if (width > 0 && height > 0 && map) {
            map.invalidateSize()
        }
    })
    resizeObserver.observe(mapEl.value)

    await fetchBranches()
    fetchCities()
})

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile)
    resizeObserver?.disconnect()
    map?.remove()
})

// ── watchers ──────────────────────────────────────────────────────────────────
watch(filteredBranches, () => renderMarkers(), { deep: true })

watch(() => activeBranch.value, id => {
    markerMap.forEach((marker, branchId) => {
        marker.setIcon(createMarkerIcon(activeBranch.value?.id === branchId))
    })
    if (activeBranch.value) {
        const marker = markerMap.get(activeBranch.value.id)
        if (marker) {
            map?.flyTo(marker.getLatLng(), 15, { animate: true, duration: 0.8 })
            marker.openPopup()
        }
    }
})

watch(userPosition, pos => {
    if (!pos || !map) return
    if (userMarker) map.removeLayer(userMarker)
    userMarker = L.marker([pos.lat, pos.lng], { icon: createUserIcon(), zIndexOffset: 1000 })
        .addTo(map)
        .bindPopup('<strong>Lokasi kamu</strong>')
    map.flyTo([pos.lat, pos.lng], 13, { animate: true, duration: 1 })
})

watch(branches, (val) => {
}, { deep: true })

// ── API ───────────────────────────────────────────────────────────────────────
async function fetchBranches() {
    loading.value = true
    error.value   = null
    try {
        const { data } = await axiosInstance.get('/branches')
        const result = data?.data ?? data
        branches.value = Array.isArray(result) ? result : []
    } catch (e) {
        error.value = e?.response?.data?.message ?? 'Gagal memuat data cabang.'
        branches.value = []
    } finally {
        loading.value = false
        // hapus renderMarkers() dari sini — biar watcher yang handle
    }
}

async function fetchCities() {
    try {
        const { data } = await axiosInstance.get('/branches/cities')
        const result = data?.data ?? data
        if (Array.isArray(result)) {
            // filter yang null
            cities.value = result.filter(c => c.city && c.province)
        } else {
            cities.value = []
        }
    } catch {
        cities.value = []
    }
}

const provinces = computed(() => {
    if (!cities.value || !Array.isArray(cities.value)) return []
    return [...new Set(cities.value.map(c => c.province))].sort()
})

const filteredCities = computed(() => {
    if (!cities.value || !Array.isArray(cities.value)) return []
    if (!selectedProvince.value) return cities.value
    return cities.value.filter(c => c.province === selectedProvince.value)
})

async function fetchNearest(lat, lng) {
    const { data } = await axiosInstance.get('/branches/nearest', {
        params: { lat, lng, limit: 5 }
    })
    return data.data ?? data
}

// ── map ───────────────────────────────────────────────────────────────────────
function initMap() {
    if (!mapEl.value) return

    map = L.map(mapEl.value, {
        center: [-2.5, 118],
        zoom: 5,
        zoomControl: false,
        preferCanvas: true,
    })

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map)

    L.control.zoom({ position: 'bottomright' }).addTo(map)

    clusterGroup = L.markerClusterGroup({
        showCoverageOnHover: false,
        disableClusteringAtZoom: 14,
        maxClusterRadius: 60,
        iconCreateFunction(cluster) {
            return L.divIcon({
                className: '',
                iconSize: [42, 42],
                html: `<div style="
                    width:42px;height:42px;border-radius:50%;
                    background:${BRAND};color:#fff;
                    display:flex;align-items:center;justify-content:center;
                    font-size:13px;font-weight:800;
                    border:3px solid #fff;
                    box-shadow:0 4px 14px rgba(189,32,40,.4);
                ">${cluster.getChildCount()}</div>`,
            })
        },
    })
    map.addLayer(clusterGroup)

    nextTick(() => map.invalidateSize())
}

function renderMarkers() {
    if (!clusterGroup || !map) return
    if (!Array.isArray(filteredBranches.value)) return
    clusterGroup.clearLayers()
    markerMap.clear()

    filteredBranches.value.forEach(branch => {
        if (!branch.latitude || !branch.longitude) return

        const marker = L.marker(
            [branch.latitude, branch.longitude],
            { icon: createMarkerIcon(activeBranch.value?.id === branch.id) }
        )

        marker.bindPopup(buildPopupHtml(branch), {
            maxWidth: 310,
            className: 'sl-popup',
            autoPan: true,
            autoPanPaddingTopLeft: [0, 140],
            autoPanPaddingBottomRight: [0, 40]
        })
        marker.on('click', () => selectBranch(branch))
        clusterGroup.addLayer(marker)
        markerMap.set(branch.id, marker)
    })

    if (filteredBranches.value.length) {
        const bounds = L.latLngBounds(
            filteredBranches.value.map(b => [b.latitude, b.longitude])
        )
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 13 })
    }
}

function createMarkerIcon(active = false) {
    const size  = active ? 44 : 36
    const color = active ? BRAND : '#555'
    return L.divIcon({
        className: '',
        iconSize:    [size, size + 10],
        iconAnchor:  [size / 2, size + 10],
        popupAnchor: [0, -(size + 12)],
        html: `<div style="
            width:${size}px;height:${size}px;
            background:${color};border-radius:50% 50% 50% 0;
            transform:rotate(-45deg);
            display:flex;align-items:center;justify-content:center;
            border:3px solid white;
            box-shadow:0 4px 14px ${active ? 'rgba(189,32,40,.45)' : 'rgba(0,0,0,.22)'};
            transition:all .25s;
        ">
            <svg xmlns='http://www.w3.org/2000/svg'
                width='${Math.round(size*.46)}' height='${Math.round(size*.46)}'
                viewBox='0 0 24 24' fill='white' style='transform:rotate(45deg)'>
                <path d='M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z'/>
            </svg>
        </div>`,
    })
}

function createUserIcon() {
    return L.divIcon({
        className: '',
        iconSize: [22, 22],
        iconAnchor: [11, 11],
        html: `<div style="width:22px;height:22px;border-radius:50%;background:#2563eb;border:3px solid white;box-shadow:0 0 0 5px rgba(37,99,235,.2)"></div>`,
    })
}

function buildPopupHtml(branch) {
    const phone = branch.phone
        ? `<a href="tel:${branch.phone}" style="color:${BRAND};font-weight:700;text-decoration:none">${branch.phone}</a>`
        : '<span style="color:#bbb">—</span>'

    const hours = branch.operating_hours?.length
        ? branch.operating_hours.map(h => `
            <div style="display:flex;justify-content:space-between;gap:12px;font-size:12px;color:#555;padding:2px 0">
                <span>${h.days}</span>
                <span style="font-weight:700;color:#222">${h.open} – ${h.close}</span>
            </div>`).join('')
        : `<div style="font-size:12px;color:#bbb">Tidak tersedia</div>`

    return `
    <div style="font-family:'Poppins',system-ui,sans-serif;min-width:240px;max-width:300px">
        <div style="background:${BRAND};margin:-14px -20px 14px;padding:14px 18px;border-radius:8px 8px 0 0">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:rgba(255,255,255,.7);margin-bottom:3px">Cabang</div>
            <div style="font-size:15px;font-weight:800;color:white;line-height:1.3">${branch.name}</div>
            <div style="font-size:12px;color:rgba(255,255,255,.75);margin-top:2px">${branch.city ?? ''}, ${branch.province ?? ''}</div>
        </div>
        <div style="margin-bottom:10px">
            <div style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#bbb;margin-bottom:5px">Alamat</div>
            <div style="font-size:13px;color:#333;line-height:1.55">${branch.address}</div>
        </div>
        <div style="margin-bottom:10px">
            <div style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#bbb;margin-bottom:4px">Telepon</div>
            <div style="font-size:13px">${phone}</div>
        </div>
        <div style="margin-bottom:14px">
            <div style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#bbb;margin-bottom:6px">Jam Operasional</div>
            ${hours}
        </div>
        <a href="${branch.directions_url}" target="_blank" rel="noopener" style="
            display:flex;align-items:center;justify-content:center;gap:8px;
            background:${BRAND};color:white;text-decoration:none;
            padding:11px;border-radius:12px;font-size:13px;font-weight:700;
        ">
            <svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='white' viewBox='0 0 24 24'>
                <path d='M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z'/>
            </svg>
            Petunjuk Arah (Google Maps)
        </a>
    </div>`
}

// ── actions ───────────────────────────────────────────────────────────────────
function selectBranch(branch) {
    activeBranch.value = branch
}

function resetFilters() {
    searchQuery.value      = ''
    selectedCity.value     = ''
    selectedProvince.value = ''
}

async function handleFindNearest() {
    geoError.value = null
    if (!('geolocation' in navigator)) {
        geoError.value = 'Geolocation tidak didukung browser ini.'
        return
    }
    geoLoading.value = true
    try {
        const pos = await new Promise((resolve, reject) =>
            navigator.geolocation.getCurrentPosition(resolve, reject, { timeout: 10000 })
        )
        const coords = { lat: pos.coords.latitude, lng: pos.coords.longitude }
        userPosition.value = coords
        const nearest = await fetchNearest(coords.lat, coords.lng)
        if (nearest.length) selectBranch(nearest[0])
    } catch (e) {
        const messages = { 1: 'Izin lokasi ditolak.', 2: 'Posisi tidak dapat ditentukan.', 3: 'Waktu habis.' }
        geoError.value = messages[e.code] ?? 'Gagal mendapatkan lokasi.'
    } finally {
        geoLoading.value = false
    }
}

function checkMobile() {
    const was = isMobile.value
    isMobile.value = window.innerWidth < 768
    
    // Kalau status mobile berubah, invalidate map size
    if (was !== isMobile.value && map) {
        nextTick(() => {
            map.invalidateSize()
        })
    }
}
</script>

<style scoped>
/* ── hero ─────────────────────────────────────────────────────────────────── */
.sl-hero {
    background: linear-gradient(135deg, #BD2028 0%, #7a0000 100%);
    padding: 36px 24px;
    position: relative;
    overflow: hidden;
}
.sl-hero::after {
    content: '';
    position: absolute;
    width: 400px; height: 400px;
    background: rgba(255,255,255,.05);
    border-radius: 50%;
    top: -150px; right: -100px;
    pointer-events: none;
}
.sl-hero__inner {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    position: relative; z-index: 1;
}
.sl-hero__badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.25);
    color: white; font-size: 12px; font-weight: 700;
    padding: 5px 14px; border-radius: 20px;
    margin-bottom: 12px;
}
.sl-hero__title {
    font-family: 'Poppins', sans-serif;
    font-size: 28px; font-weight: 700;
    color: white; margin: 0 0 8px; line-height: 1.25;
}
.sl-hero__title span { opacity: .75; }
.sl-hero__sub { font-size: 14px; color: rgba(255,255,255,.75); margin: 0; }

/* nearest button */
.sl-nearest-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: white; color: #BD2028;
    border: none; border-radius: 12px;
    padding: 12px 20px; flex-shrink: 0;
    font-family: 'Poppins', sans-serif;
    font-size: 13px; font-weight: 800;
    cursor: pointer; white-space: nowrap;
    box-shadow: 0 4px 16px rgba(0,0,0,.15);
    transition: transform .2s, box-shadow .2s;
}
.sl-nearest-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(0,0,0,.2);
}
.sl-nearest-btn:disabled { opacity: .7; cursor: not-allowed; }
.sl-btn-spinner {
    width: 13px; height: 13px;
    border: 2px solid rgba(189,32,40,.25);
    border-top-color: #BD2028;
    border-radius: 50%;
    animation: sl-spin .75s linear infinite;
}

.sl-page-wrap {
    display: flex;
    flex-direction: column;
}
@keyframes sl-spin { to { transform: rotate(360deg); } }

/* banner */
.sl-banner {
    display: flex; align-items: center; gap: 8px;
    background: #fff5f5; border-bottom: 1px solid #ffd0d0;
    color: #c0392b; font-size: 13px; font-weight: 600;
    padding: 10px 24px;
}
.sl-banner button {
    margin-left: auto; background: none; border: none;
    cursor: pointer; color: inherit; font-size: 14px;
}

/* ── body layout ─────────────────────────────────────────────────────────── */
.sl-body {
    display: flex;
    height: calc(110vh - 180px); /* adjust sesuai tinggi Navbar + hero */
    overflow: hidden;
}

/* ── sidebar ─────────────────────────────────────────────────────────────── */
.sl-sidebar {
    width: 350px; min-width: 310px;
    background: #fff;
    border-right: 1px solid #ebebeb;
    display: flex; flex-direction: column;
    overflow: hidden; flex-shrink: 0;
}
.sl-sidebar__inner { padding: 14px 14px 0; flex-shrink: 0; }

/* search */
.sl-search {
    display: flex; align-items: center; gap: 9px;
    background: #f5f5f5;
    border: 1.5px solid transparent;
    border-radius: 13px; padding: 0 13px; height: 44px;
    margin-bottom: 10px;
    transition: border-color .2s, background .2s;
}
.sl-search--focused { background: #fff; border-color: #BD2028; box-shadow: 0 0 0 3px rgba(189,32,40,.1); }
.sl-search svg { color: #bbb; flex-shrink: 0; }
.sl-search--focused svg:first-child { color: #BD2028; }
.sl-search__input {
    flex: 1; border: none; background: transparent;
    font-size: 13.5px; font-family: inherit; color: #1a1a1a; outline: none;
}
.sl-search__input::placeholder { color: #c0c0c0; }
.sl-search__clear {
    display: flex; align-items: center; justify-content: center;
    width: 20px; height: 20px;
    background: #ddd; border: none; border-radius: 50%;
    cursor: pointer; color: #666; flex-shrink: 0;
}
.sl-search__clear:hover { background: #ccc; }

/* filters */
.sl-filters { display: flex; gap: 8px; margin-bottom: 10px; }
.sl-select-wrap { flex: 1; position: relative; }
.sl-select {
    width: 100%; appearance: none;
    background: #f5f5f5; border: 1.5px solid transparent;
    border-radius: 11px; padding: 9px 28px 9px 11px;
    font-family: inherit; font-size: 12.5px; color: #333;
    cursor: pointer; outline: none;
    transition: border-color .2s, background .2s;
}
.sl-select:focus { background: #fff; border-color: #BD2028; box-shadow: 0 0 0 3px rgba(189,32,40,.1); }
.sl-select__arrow {
    position: absolute; right: 8px; top: 50%;
    transform: translateY(-50%); pointer-events: none; color: #bbb;
}

/* result bar */
.sl-result-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 4px 0 10px; font-size: 12px; color: #999;
}
.sl-result-bar b { color: #1a1a1a; font-weight: 700; }
.sl-reset-btn {
    background: none; border: none;
    font-family: inherit; font-size: 12px; font-weight: 700;
    color: #BD2028; cursor: pointer;
    padding: 3px 8px; border-radius: 8px;
}
.sl-reset-btn:hover { background: rgba(189,32,40,.06); }

/* list */
.sl-list {
    flex: 1; overflow-y: auto; padding: 4px 10px 14px;
}
.sl-list::-webkit-scrollbar { width: 4px; }
.sl-list::-webkit-scrollbar-thumb { background: #e8e8e8; border-radius: 4px; }
.sl-cards { display: flex; flex-direction: column; gap: 7px; }

/* skeleton */
.sl-skeleton {
    background: #fff; border: 1.5px solid #f0f0f0;
    border-radius: 14px; padding: 15px; margin-bottom: 7px;
    display: flex; flex-direction: column; gap: 8px;
}
.sl-skeleton__line {
    height: 11px; border-radius: 6px;
    background: linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%);
    background-size: 200% 100%;
    animation: sl-shimmer 1.4s ease-in-out infinite;
}
@keyframes sl-shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* empty */
.sl-empty {
    display: flex; flex-direction: column; align-items: center;
    text-align: center; padding: 40px 20px; gap: 6px;
}
.sl-empty__title { font-size: 14px; font-weight: 700; color: #333; margin: 8px 0 0; }
.sl-empty__sub   { font-size: 13px; color: #999; margin: 0 0 10px; }
.sl-empty__btn {
    background: #BD2028; color: #fff; border: none; border-radius: 10px;
    padding: 10px 20px; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer;
}

/* card */
.sl-card {
    width: 100%; display: flex; align-items: stretch;
    text-align: left; background: #fff;
    border: 1.5px solid #f0f0f0; border-radius: 14px;
    cursor: pointer; overflow: hidden; padding: 0; font-family: inherit;
    transition: border-color .2s, box-shadow .2s, transform .2s;
}
.sl-card:hover { border-color: #fcc; box-shadow: 0 4px 18px rgba(189,32,40,.1); transform: translateY(-1px); }
.sl-card--active { border-color: #BD2028; box-shadow: 0 6px 22px rgba(189,32,40,.18); transform: translateY(-1px); }

.sl-card__accent { width: 4px; flex-shrink: 0; background: #f0f0f0; transition: background .2s; }
.sl-card:hover .sl-card__accent,
.sl-card--active .sl-card__accent { background: #BD2028; }

.sl-card__body { flex: 1; padding: 12px 10px 12px 14px; min-width: 0; }
.sl-card__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 4px; }
.sl-card__name {
    display: block; font-size: 13.5px; font-weight: 700; color: #1a1a1a;
    margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    transition: color .2s;
}
.sl-card--active .sl-card__name { color: #BD2028; }
.sl-card__city {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 10.5px; font-weight: 700; color: #BD2028;
    background: rgba(189,32,40,.08); padding: 2px 8px; border-radius: 20px;
}
.sl-card__dist {
    flex-shrink: 0; font-size: 10px; font-weight: 700;
    color: #fff; background: #BD2028; padding: 3px 8px; border-radius: 20px; white-space: nowrap;
}
.sl-card__address {
    font-size: 11.5px; color: #777; line-height: 1.5; margin: 0 0 7px;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.sl-card__meta { display: flex; flex-wrap: wrap; gap: 6px; }
.sl-card__pill { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: #888; }

.sl-card__arrow {
    display: flex; align-items: center; padding: 0 11px 0 4px;
    color: #ddd; flex-shrink: 0; transition: color .2s, transform .2s;
}
.sl-card:hover .sl-card__arrow,
.sl-card--active .sl-card__arrow { color: #BD2028; transform: translateX(2px); }

/* ── map ─────────────────────────────────────────────────────────────────── */
.sl-map-wrap { flex: 1; position: relative; overflow: hidden; }
.sl-map { width: 100%; height: 100%; min-height: 200px; }
.sl-map-overlay {
    position: absolute; inset: 0;
    background: rgba(255,255,255,.8);
    display: flex; align-items: center; justify-content: center; z-index: 1000;
}
.sl-map-spinner { display: flex; flex-direction: column; align-items: center; gap: 12px; font-size: 13px; color: #666; }
.sl-spinner-ring {
    width: 36px; height: 36px;
    border: 3px solid #f0f0f0; border-top-color: #BD2028;
    border-radius: 50%; animation: sl-spin .8s linear infinite;
}
.sl-map-badge {
    position: absolute; top: 14px; left: 14px; z-index: 500;
    display: inline-flex; align-items: center; gap: 5px;
    background: #fff; color: #BD2028;
    font-size: 11.5px; font-weight: 800;
    padding: 6px 12px; border-radius: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,.12);
}

/* ── mobile sheet ────────────────────────────────────────────────────────── */
.sl-sheet {
    position: fixed; bottom: 0; left: 0; right: 0;
    background: #fff; border-radius: 20px 20px 0 0;
    box-shadow: 0 -8px 36px rgba(0,0,0,.16); z-index: 600;
}
.sl-sheet__handle {
    width: 38px; height: 4px; background: #e0e0e0;
    border-radius: 2px; margin: 11px auto 0; cursor: pointer;
}
.sl-sheet__body { padding: 14px 20px 36px; }
.sl-sheet__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
.sl-sheet__name { font-size: 16px; font-weight: 800; color: #1a1a1a; margin: 0 0 4px; }
.sl-sheet__city { font-size: 11.5px; font-weight: 700; color: #BD2028; }
.sl-sheet__close {
    width: 30px; height: 30px; background: #f5f5f5; border: none;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: #666; flex-shrink: 0;
}
.sl-sheet__address { font-size: 13px; color: #666; line-height: 1.55; margin: 0 0 12px; }
.sl-sheet__phone {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 14px; font-weight: 700; color: #1a1a1a;
    text-decoration: none; margin-bottom: 14px;
}
.sl-sheet__dir {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    background: #BD2028; color: #fff; padding: 13px; border-radius: 14px;
    font-family: inherit; font-size: 13.5px; font-weight: 700; text-decoration: none;
    box-shadow: 0 4px 16px rgba(189,32,40,.32);
}

/* ── transitions ─────────────────────────────────────────────────────────── */
.sl-fade-enter-active, .sl-fade-leave-active { transition: opacity .3s; }
.sl-fade-enter-from, .sl-fade-leave-to { opacity: 0; }

.sl-slide-down-enter-active, .sl-slide-down-leave-active { transition: all .25s; }
.sl-slide-down-enter-from, .sl-slide-down-leave-to { transform: translateY(-100%); opacity: 0; }

.sl-sheet-enter-active, .sl-sheet-leave-active { transition: transform .35s cubic-bezier(.4,0,.2,1); }
.sl-sheet-enter-from, .sl-sheet-leave-to { transform: translateY(100%); }

.sl-list-anim-enter-active { transition: all .28s ease; }
.sl-list-anim-leave-active { transition: all .2s ease; }
.sl-list-anim-enter-from   { opacity: 0; transform: translateY(8px); }
.sl-list-anim-leave-to     { opacity: 0; }
.sl-list-anim-move         { transition: transform .28s ease; }

/* ── responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 767px) {
    .min-h-screen {
        height: 100dvh;
        overflow: hidden;
    }

    .sl-page-wrap {
        height: calc(100dvh - 0px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Hero compact */
    .sl-hero {
        padding: 14px 16px;
        flex-shrink: 0;
    }
    .sl-hero__inner {
        flex-direction: row;
        align-items: center;
        gap: 12px;
    }
    .sl-hero__badge { display: none; } 
    .sl-hero__title {
        font-size: 16px;
        margin-bottom: 2px;
    }
    .sl-hero__sub {
        font-size: 11px;
        margin: 0;
    }
    .sl-nearest-btn {
        flex-shrink: 0;
        white-space: nowrap;
        padding: 10px 14px;
        font-size: 12px;
        width: auto; 
    }

    .sl-body {
        flex: 1;
        flex-direction: column;
        height: auto;
        overflow: hidden;
        display: flex;
    }

    .sl-map {
        width: 100%;
        height: 58dvh;
        min-height: 200px; 
    }

    .sl-map-wrap {
        flex: 1;
        min-height: 200px;
        height: calc(58dvh - 0px);
    }

    /* Sidebar: fixed panel bawah, TIDAK ikut flow */
    .sl-sidebar {
        position: fixed;
        left: 0; right: 0; bottom: 0;
        width: 100%;
        height: 42dvh;
        min-width: unset;
        max-height: 42dvh;
        border-right: none;
        border-top: 1.5px solid #ebebeb;
        border-radius: 20px 20px 0 0;
        box-shadow: 0 -4px 24px rgba(0,0,0,.12);
        z-index: 200;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    /* Drag handle di atas sidebar */
    .sl-sidebar::before {
        content: '';
        display: block;
        width: 36px; height: 4px;
        background: #e0e0e0;
        border-radius: 2px;
        margin: 10px auto 6px;
        flex-shrink: 0;
    }

    .sl-sidebar__inner {
        padding: 6px 12px 0;
        flex-shrink: 0;
    }

    .sl-search {
        height: 38px;
        margin-bottom: 6px;
    }

    .sl-filters {
        gap: 6px;
        margin-bottom: 6px;
    }

    .sl-select {
        padding: 6px 24px 6px 10px;
        font-size: 11.5px;
    }

    .sl-result-bar {
        padding: 0 0 6px;
        font-size: 11px;
    }

    .sl-list {
        flex: 1;
        overflow-y: auto;
        padding: 4px 10px 16px;
        -webkit-overflow-scrolling: touch;
    }

    .sl-card__address {
        -webkit-line-clamp: 1;
    }

    .sl-card__body {
        padding: 9px 8px 9px 12px;
    }

    /* Sheet detail di atas sidebar */
    .sl-sheet {
        z-index: 500;
        max-height: 65dvh;
        overflow-y: auto;
    }

    .sl-sheet__body {
        padding: 12px 18px 48px;
    }

    .sl-map-badge {
        top: 10px; left: 10px;
        font-size: 11px; padding: 5px 10px;
    }

    /* Zoom control jangan tertutup sidebar */
    :deep(.leaflet-bottom.leaflet-right) {
        bottom: 44dvh !important;
    }
}

@media (max-width: 480px) {
    .sl-hero__title { font-size: 15px; }
    .sl-nearest-btn { padding: 9px 12px; font-size: 11px; }
    .sl-sidebar { height: 44dvh; max-height: 44dvh; }
}


/* popup global */
.sl-popup .leaflet-popup-content-wrapper {
    border-radius: 16px !important;
    box-shadow: 0 8px 40px rgba(0,0,0,.18) !important;
    padding: 0 !important; overflow: hidden;
}
.sl-popup .leaflet-popup-content { margin: 14px 20px 18px !important; }
.sl-popup .leaflet-popup-tip { background: white !important; }
.sl-popup .leaflet-popup-close-button {
    color: white !important; font-size: 18px !important;
    top: 10px !important; right: 12px !important; z-index: 10 !important;
}
.marker-cluster-small, .marker-cluster-medium, .marker-cluster-large,
.marker-cluster-small div, .marker-cluster-medium div, .marker-cluster-large div {
    background: transparent !important;
}
</style>
