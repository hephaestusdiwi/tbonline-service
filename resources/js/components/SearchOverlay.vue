<template>
  <Teleport to="body">
    <!-- Backdrop -->
    <Transition name="backdrop">
      <div v-if="modelValue" class="search-backdrop" @click="close" />
    </Transition>

    <!-- The search bar that expands FROM the navbar -->
    <Transition name="searchbar" @after-leave="onAfterLeave">
      <div
        v-if="modelValue"
        class="search-container"
        :style="containerStyle"
      >
        <!-- Inner bar: morphs from icon position -->
        <div class="search-bar" :class="{ 'is-expanded': isExpanded }">

          <!-- Input row -->
          <div class="search-bar__row">
            <svg
              class="search-bar__icon"
              viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
            >
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>

            <input
              ref="inputRef"
              v-model="query"
              type="text"
              class="search-bar__input"
              placeholder="Cari liquid, mod, atau pods.."
              autocomplete="off"
              spellcheck="false"
              @keydown.esc="close"
              @keydown.enter="goToSearch"
            />

            <!-- Loading spinner -->
            <Transition name="fade">
              <div v-if="isLoading" class="search-bar__spinner">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
                </svg>
              </div>
            </Transition>

            <!-- Clear -->
            <Transition name="fade">
              <button v-if="query && !isLoading" class="search-bar__clear" @click.stop="clearQuery">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
              </button>
            </Transition>

            <!-- Close -->
            <button class="search-bar__close" @click="close">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6 6 18M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Dropdown results panel -->
          <Transition name="dropdown">
            <div v-if="isExpanded && showDropdown" class="search-bar__dropdown">
              <div class="dropdown-body">
                <Transition name="fade" mode="out-in">

                  <!-- Empty query: popular + recent -->
                  <div v-if="!query.trim()" key="idle" class="dropdown-idle">
                    <div class="dropdown-section">
                      <span class="dropdown-label">Pencarian Populer</span>
                      <div class="chips">
                        <button
                          v-for="chip in popularKeywords"
                          :key="chip"
                          class="chip"
                          @click.stop="setQuery(chip)"
                        >{{ chip }}</button>
                      </div>
                    </div>

                    <div v-if="recentSearches.length" class="dropdown-section">
                      <div class="dropdown-label-row">
                        <span class="dropdown-label">Terakhir Dicari</span>
                        <button class="btn-link" @click.stop="clearRecent">Hapus semua</button>
                      </div>
                      <div class="recent-list">
                        <button
                          v-for="term in recentSearches"
                          :key="term"
                          class="recent-item"
                          @click.stop="setQuery(term)"
                        >
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="recent-icon">
                            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>
                          </svg>
                          <span>{{ term }}</span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Loading skeleton -->
                  <div v-else-if="isLoading" key="loading" class="dropdown-skeletons">
                    <div v-for="n in 4" :key="n" class="skeleton-row">
                      <div class="sk sk-img" />
                      <div class="sk-text">
                        <div class="sk sk-title" />
                        <div class="sk sk-sub" />
                      </div>
                      <div class="sk sk-price" />
                    </div>
                  </div>

                  <!-- Results -->
                  <div v-else-if="results.length" key="results">
                    <span class="dropdown-label">Produk</span>
                    <div class="results-list">
                      <button
                        v-for="(item, i) in results"
                        :key="item.id"
                        class="result-row"
                        :style="{ animationDelay: `${i * 40}ms` }"
                        @click.stop="selectProduct(item)"
                      >
                        <div class="result-thumb">
                          <img
                            v-if="item.photo_1"
                            :src="photoUrl(item.photo_1)"
                            :alt="item.name"
                          />
                          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <path d="M21 15l-5-5L5 21"/>
                          </svg>
                        </div>
                        <div class="result-meta">
                          <span class="result-name" v-html="highlight(item.name, query)" />
                          <span v-if="item.category" class="result-cat">{{ item.category }}</span>
                        </div>
                        <span class="result-price">{{ formatPrice(item.sell_price) }}</span>
                        <svg class="result-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                      </button>
                    </div>

                    <button class="view-all-btn" @click.stop="goToSearch">
                      Lihat semua hasil untuk <strong>"{{ query }}"</strong>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                      </svg>
                    </button>
                  </div>

                  <!-- No results -->
                  <div v-else key="empty" class="dropdown-empty">
                    <div class="empty-icon">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        <path d="M8 11h6"/>
                      </svg>
                    </div>
                    <p>Tidak ditemukan untuk <strong>"{{ query }}"</strong></p>
                    <span>Coba kata kunci yang berbeda</span>
                  </div>

                </Transition>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axiosInstance from '../axios'

const RECENT_KEY = 'search_recent'
const MAX_RECENT = 5

export default {
  name: 'SearchOverlay',

  props: {
    modelValue: { type: Boolean, default: false },
    /**
     * Opsional: DOMRect dari search icon di navbar.
     * Kirimkan dari Navbar.vue dengan:
     *   const iconRef = ref(null)
     *   const iconRect = ref(null)
     *   function openSearch() {
     *     iconRect.value = iconRef.value?.getBoundingClientRect()
     *     searchOpen.value = true
     *   }
     *   <SearchOverlay v-model="searchOpen" :iconRect="iconRect" />
     */
    iconRect: { type: Object, default: null }
  },

  emits: ['update:modelValue'],

  setup(props, { emit }) {
    const router     = useRouter()
    const inputRef   = ref(null)
    const query      = ref('')
    const results    = ref([])
    const isLoading  = ref(false)
    const isExpanded = ref(false)
    const showDropdown = ref(false)

    let debounceTimer = null

    const popularKeywords = ref([
      'Thermal', 'Foyu', 'Dokaco', 'Big Daddy', 'Liquid', 'Mod', 'Pod'
    ])

    const recentSearches = ref(
      JSON.parse(localStorage.getItem(RECENT_KEY) || '[]')
    )

    /**
     * Hitung margin kanan container agar search bar muncul
     * tepat di bawah / dari posisi icon.
     * Kalau iconRect tidak ada, fallback ke pojok kanan dengan margin default.
     */
    const containerStyle = computed(() => {
      if (!props.iconRect) return {}
      // Jarak dari kanan viewport ke tengah icon
      const rightEdge = window.innerWidth - props.iconRect.right
      return {
        '--icon-right': `${Math.max(0, rightEdge - 6)}px`,
      }
    })

    watch(() => props.modelValue, async (val) => {
      if (val) {
        document.body.style.overflow = 'hidden'
        await nextTick()
        // Sedikit delay agar mount() selesai dulu, baru trigger expand CSS
        requestAnimationFrame(() => {
          requestAnimationFrame(() => {
            isExpanded.value = true
            // Delay dropdown agar animasi bar selesai dulu
            setTimeout(() => { showDropdown.value = true }, 180)
          })
        })
        // Fokus input setelah expand
        setTimeout(() => inputRef.value?.focus(), 250)
      } else {
        showDropdown.value = false
        isExpanded.value = false
        document.body.style.overflow = ''
      }
    })

    watch(query, (val) => {
      clearTimeout(debounceTimer)
      if (!val.trim() || val.trim().length < 2) {
        results.value = []
        isLoading.value = false
        return
      }
      isLoading.value = true
      debounceTimer = setTimeout(() => fetchResults(val.trim()), 320)
    })

    async function fetchResults(q) {
      try {
        const { data } = await axiosInstance.get('/products/search', {
          params: { q, limit: 6 }
        })
        results.value = data.data ?? data
        console.log('SAMPLE:', results.value[0])
      } catch {
        results.value = []
      } finally {
        isLoading.value = false
      }
    }

    function photoUrl(path) {
        if (!path) return null
        if (path.startsWith('http://') || path.startsWith('https://')) return path
        const base = import.meta.env.VITE_APP_URL || window.location.origin
        return `${base}/storage/${path}`
    }

    function close() { emit('update:modelValue', false) }

    function onAfterLeave() {
      query.value = ''
      results.value = []
      isLoading.value = false
    }

    function clearQuery() { query.value = ''; inputRef.value?.focus() }

    function setQuery(term) { query.value = term; inputRef.value?.focus() }

    function saveRecent(term) {
      const list = [term, ...recentSearches.value.filter(r => r !== term)].slice(0, MAX_RECENT)
      recentSearches.value = list
      localStorage.setItem(RECENT_KEY, JSON.stringify(list))
    }

    function clearRecent() {
      recentSearches.value = []
      localStorage.removeItem(RECENT_KEY)
    }

    function goToSearch() {
      if (!query.value.trim()) return
      saveRecent(query.value.trim())
      close()
      router.push({ name: 'Products', query: { search: query.value.trim() } })
    }

    function selectProduct(product) {
      if (query.value.trim()) saveRecent(query.value.trim())
      router.push({ name: 'ProductDetail', params: { id: product.id } })
      close()
    }

    function highlight(text, q) {
      if (!q) return text
      const escaped = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
      return text.replace(new RegExp(`(${escaped})`, 'gi'), '<mark>$1</mark>')
    }

    function formatPrice(price) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
      }).format(price)
    }

    function onKeydown(e) {
      if (e.key === 'Escape' && props.modelValue) close()
    }

    onMounted(() => document.addEventListener('keydown', onKeydown))
    onUnmounted(() => {
      document.removeEventListener('keydown', onKeydown)
      document.body.style.overflow = ''
    })

    return {
      inputRef, query, results, isLoading,
      isExpanded, showDropdown, containerStyle,
      popularKeywords, recentSearches,
      close, onAfterLeave, clearQuery, setQuery, clearRecent,
      goToSearch, selectProduct, highlight, formatPrice,
      photoUrl 
    }
  }
}
</script>

<style scoped>
/* ──────────────────────────────────────────────
   CSS Custom Properties
────────────────────────────────────────────── */
.search-container {
  --icon-right: 64px;           /* fallback jika iconRect tidak dikirim */
  --bar-expand-duration: 0.38s;
  --bar-easing: cubic-bezier(0.22, 1, 0.36, 1);
}

/* ──────────────────────────────────────────────
   Backdrop
────────────────────────────────────────────── */
.search-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9997;
  background: rgba(0, 0, 0, 0.38);
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
}

/* ──────────────────────────────────────────────
   Container — wrapper tepat di atas navbar area
────────────────────────────────────────────── */
.search-container {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 9998;
  /* bar mulai dari kanan (posisi icon), lalu expand ke kiri */
  display: flex;
  justify-content: flex-end;
  pointer-events: none;
  /* Bar tidak boleh kliping sebelum expand */
  overflow: visible;
}

/* ──────────────────────────────────────────────
   The search bar — inti animasi
────────────────────────────────────────────── */
.search-bar {
  font-family: "Poppins", sans-serif;
  pointer-events: all;
  background: #fff;
  overflow: hidden;

  /*
   * COLLAPSED STATE:
   * Ukuran kecil, tepat di bawah / di posisi icon.
   * transform-origin: top right agar grow ke kiri & bawah.
   */
  width: 48px;
  max-height: 52px;
  margin-top: 10px;
  margin-right: var(--icon-right);
  border-radius: 28px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);

  transform-origin: top right;
  transform: scale(0.85);
  opacity: 0.6;

  transition:
    width              var(--bar-expand-duration) var(--bar-easing),
    max-height         calc(var(--bar-expand-duration) + 0.08s) var(--bar-easing),
    margin-top         var(--bar-expand-duration) var(--bar-easing),
    margin-right       var(--bar-expand-duration) var(--bar-easing),
    border-radius      0.28s ease,
    box-shadow         0.3s ease,
    transform          0.25s var(--bar-easing),
    opacity            0.2s ease;
}

/* EXPANDED STATE */
.search-bar.is-expanded {
  width: 100%;
  max-height: 85vh;
  margin-top: 0;
  margin-right: 0;
  border-radius: 0;
  box-shadow: 0 6px 24px rgba(0,0,0,0.1);
  transform: scale(1);
  opacity: 1;
}

/* ──────────────────────────────────────────────
   Input row
────────────────────────────────────────────── */
.search-bar__row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0 16px;
  height: 56px;
  border-bottom: 1.5px solid transparent;
  transition: border-color 0.25s ease, padding 0.3s ease;
  white-space: nowrap;
}

.search-bar.is-expanded .search-bar__row {
  border-bottom-color: #ebebeb;
  padding: 0 24px;
  height: 60px;
  max-width: 880px;
  margin: 0 auto;
  width: 100%;
}

/* Search icon */
.search-bar__icon {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  color: #555;
  transition: color 0.2s;
}
.search-bar.is-expanded .search-bar__icon { color: #111; }

/* Input text */
.search-bar__input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  font-size: 1rem;
  color: #111;
  font-family: "Poppins", sans-serif;
  min-width: 0;
  /* Tersembunyi saat collapsed, muncul saat expanded */
  opacity: 0;
  width: 0;
  transition: opacity 0.2s ease 0.18s, width 0.01s;
  caret-color: #111;
  letter-spacing: -0.01em;
}

.search-bar.is-expanded .search-bar__input {
  opacity: 1;
  width: auto;
}

.search-bar__input::placeholder { color: #bbb; }

/* Spinner */
.search-bar__spinner svg {
  width: 18px;
  height: 18px;
  color: #888;
  animation: spin 0.75s linear infinite;
  flex-shrink: 0;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Clear */
.search-bar__clear {
  flex-shrink: 0;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  border: none;
  background: #ddd;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.15s;
}
.search-bar__clear:hover { background: #bbb; }
.search-bar__clear svg { width: 10px; height: 10px; color: #444; }

/* Close */
.search-bar__close {
  flex-shrink: 0;
  background: none;
  border: none;
  cursor: pointer;
  color: #aaa;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  /* Tersembunyi saat collapsed */
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s ease 0.15s, color 0.15s, background 0.15s;
}
.search-bar__close svg { width: 17px; height: 17px; }
.search-bar__close:hover { color: #111; background: #f2f2f2; }
.search-bar.is-expanded .search-bar__close {
  opacity: 1;
  pointer-events: all;
}

/* ──────────────────────────────────────────────
   Dropdown
────────────────────────────────────────────── */
.search-bar__dropdown {
  overflow-y: auto;
  max-height: calc(85vh - 62px);
  -webkit-overflow-scrolling: touch;
  overscroll-behavior: contain;
}

.dropdown-body {
  max-width: 880px;
  margin: 0 auto;
  padding: 20px 24px 36px;
}

/* ──────────────────────────────────────────────
   Labels
────────────────────────────────────────────── */
.dropdown-label {
  display: block;
  font-family: "Poppins", sans-serif;
  font-size: 0.67rem;
  font-weight: 400;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #c0c0c0;
  margin-bottom: 12px;
}

.dropdown-label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}
.dropdown-label-row .dropdown-label { margin-bottom: 0; }

.btn-link {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 0.75rem;
  color: #aaa;
  text-decoration: underline;
  padding: 0;
  transition: color 0.15s;
}
.btn-link:hover { color: #ED1F24; }

.dropdown-section { margin-bottom: 28px; }

/* ──────────────────────────────────────────────
   Chips
────────────────────────────────────────────── */
.chips { display: flex; flex-wrap: wrap; gap: 8px; }

.chip {
  border: 1.5px solid #e0e0e0;
  border-radius: 999px;
  padding: 7px 18px;
  font-size: 0.82rem;
  font-weight: 600;
  color: #222;
  background: #fff;
  cursor: pointer;
  transition: border-color 0.15s, background 0.15s, color 0.15s;
  white-space: nowrap;
}
.chip:hover { border-color: #ED1F24; background: #ED1F24; color: #fff; }

/* ──────────────────────────────────────────────
   Recent
────────────────────────────────────────────── */
.recent-list { display: flex; flex-direction: column; }

.recent-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 10px;
  background: none;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  color: #444;
  font-size: 0.88rem;
  text-align: left;
  transition: background 0.12s;
}
.recent-item:hover { background: #f6f6f6; }
.recent-icon { width: 15px; height: 15px; color: #ccc; flex-shrink: 0; }

/* ──────────────────────────────────────────────
   Skeletons
────────────────────────────────────────────── */
.dropdown-skeletons { display: flex; flex-direction: column; gap: 6px; }

.skeleton-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 8px 10px;
}
.sk {
  background: #ececec;
  border-radius: 6px;
  animation: pulse 1.3s ease-in-out infinite;
}
.sk-img   { width: 50px; height: 50px; border-radius: 8px; flex-shrink: 0; }
.sk-text  { flex: 1; display: flex; flex-direction: column; gap: 6px; }
.sk-title { height: 13px; width: 60%; }
.sk-sub   { height: 10px; width: 35%; }
.sk-price { width: 58px; height: 13px; }

@keyframes pulse {
  0%,100% { opacity: 1; }
  50%      { opacity: 0.4; }
}

/* ──────────────────────────────────────────────
   Results
────────────────────────────────────────────── */
.results-list { display: flex; flex-direction: column; margin-bottom: 10px; }

.result-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 10px;
  background: none;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  text-align: left;
  width: 100%;
  animation: result-in 0.22s ease both;
  transition: background 0.12s;
}
.result-row:hover { background: #f6f6f6; }
.result-row:hover .result-arrow { opacity: 1; transform: translateX(0); }

@keyframes result-in {
  from { opacity: 0; transform: translateY(5px); }
  to   { opacity: 1; transform: none; }
}

.result-thumb {
  width: 52px;
  height: 52px;
  border-radius: 8px;
  background: #f2f2f2;
  flex-shrink: 0;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}
.result-thumb img { width: 100%; height: 100%; object-fit: cover; }
.result-thumb svg { width: 20px; height: 20px; color: #ccc; }

.result-meta { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px; }

.result-name {
  font-size: 0.9rem;
  font-weight: 500;
  color: #111;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.result-name :deep(mark) {
  background: #ffe566;
  color: #111;
  border-radius: 2px;
  padding: 0 2px;
  font-style: normal;
}

.result-cat {
  font-size: 0.72rem;
  color: #bbb;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.result-price { font-size: 0.88rem; font-weight: 700; color: #111; flex-shrink: 0; }

.result-arrow {
  width: 13px;
  height: 13px;
  color: #ccc;
  flex-shrink: 0;
  opacity: 0;
  transform: translateX(-5px);
  transition: opacity 0.15s, transform 0.15s;
}

/* ──────────────────────────────────────────────
   View all
────────────────────────────────────────────── */
.view-all-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 14px 20px;
  margin-top: 6px;
  background: #ED1F24;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
  letter-spacing: -0.01em;
}
.view-all-btn:hover { background: #333; }
.view-all-btn svg { width: 15px; height: 15px; flex-shrink: 0; }

/* ──────────────────────────────────────────────
   Empty
────────────────────────────────────────────── */
.dropdown-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 48px 16px;
  text-align: center;
  gap: 8px;
}
.empty-icon {
  width: 52px;
  height: 52px;
  background: #f5f5f5;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 4px;
}
.empty-icon svg { width: 26px; height: 26px; color: #ccc; }
.dropdown-empty p { margin: 0; font-size: 0.9rem; color: #444; }
.dropdown-empty p strong { color: #111; }
.dropdown-empty span { font-size: 0.78rem; color: #bbb; }

/* ──────────────────────────────────────────────
   Vue Transitions
────────────────────────────────────────────── */
/* Backdrop */
.backdrop-enter-active { transition: opacity 0.28s ease; }
.backdrop-leave-active { transition: opacity 0.22s ease 0.08s; }
.backdrop-enter-from,
.backdrop-leave-to { opacity: 0; }

/* Container wrapper (masuk/keluar instan, animasi ada di .search-bar) */
.searchbar-enter-active,
.searchbar-leave-active { transition: opacity 0.01s; }
.searchbar-enter-from,
.searchbar-leave-to { opacity: 1; } /* biarkan .search-bar yang handle */

/* Dropdown panel */
.dropdown-enter-active {
  transition: opacity 0.22s ease, transform 0.28s cubic-bezier(0.22,1,0.36,1);
}
.dropdown-leave-active {
  transition: opacity 0.15s ease, transform 0.18s ease;
}
.dropdown-enter-from { opacity: 0; transform: translateY(-6px); }
.dropdown-leave-to   { opacity: 0; transform: translateY(-4px); }

/* Fade umum */
.fade-enter-active { transition: opacity 0.15s ease; }
.fade-leave-active { transition: opacity 0.1s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }

/* ──────────────────────────────────────────────
   Responsive
────────────────────────────────────────────── */
@media (min-width: 768px) {
  .search-bar.is-expanded .search-bar__row {
    padding: 0 40px;
    height: 64px;
  }
  .dropdown-body {
    padding: 24px 40px 40px;
  }
}
</style>