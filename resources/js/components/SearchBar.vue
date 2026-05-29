<template>
    <div class="search-wrapper" :class="{ 'is-focused': isFocused, 'has-results': showDropdown }">
        <div class="search-container">
            <!-- Icon Search -->
            <div class="search-icon-wrap">
                <svg v-if="!isLoading" class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="M21 21l-4.35-4.35"/>
                </svg>
                <div v-else class="spinner"></div>
            </div>

            <!-- Input -->
            <input
                ref="inputRef"
                v-model="query"
                type="text"
                class="search-input"
                placeholder="Cari produk..."
                autocomplete="off"
                @focus="isFocused = true"
                @blur="handleBlur"
                @keydown.enter="goToSearch"
                @keydown.escape="closeDropdown"
                @keydown.arrow-down.prevent="navigateDown"
                @keydown.arrow-up.prevent="navigateUp"
            />

            <!-- Clear button -->
            <button v-if="query" class="clear-btn" @mousedown.prevent="clearSearch">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Dropdown Results -->
        <transition name="dropdown">
            <div v-if="showDropdown" class="search-dropdown">
                <!-- Results -->
                <template v-if="results.length > 0">
                    <div class="dropdown-section-label">Produk</div>
                    <a
                        v-for="(item, index) in results"
                        :key="item.id"
                        class="dropdown-item"
                        :class="{ 'is-active': activeIndex === index }"
                        @mousedown.prevent="selectProduct(item)"
                    >
                        <div class="item-image">
                            <img v-if="item.photo_1" :src="item.photo_1" :alt="item.name" />
                            <div v-else class="item-image-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <path d="M21 15l-5-5L5 21"/>
                                </svg>
                            </div>
                        </div>
                        <div class="item-info">
                            <span class="item-name" v-html="highlight(item.name, query)"></span>
                            <span v-if="item.category" class="item-category">{{ item.category }}</span>
                        </div>
                        <div class="item-price">
                            {{ formatPrice(item.sell_price) }}
                        </div>
                    </a>

                    <!-- View all -->
                    <button class="view-all-btn" @mousedown.prevent="goToSearch">
                        Lihat semua hasil untuk "<strong>{{ query }}</strong>"
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>
                </template>

                <!-- Empty state -->
                <div v-else-if="!isLoading" class="dropdown-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <p>Produk "<strong>{{ query }}</strong>" tidak ditemukan</p>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
import { ref, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import axiosInstance from '../axios'

export default {
    name: 'SearchBar',

    emits: ['search'],

    setup(_, { emit }) {
        const router = useRouter()
        const inputRef = ref(null)
        const query = ref('')
        const results = ref([])
        const isLoading = ref(false)
        const isFocused = ref(false)
        const activeIndex = ref(-1)
        let debounceTimer = null

        const showDropdown = computed(() => isFocused.value && query.value.trim().length >= 2)

        watch(query, (val) => {
            activeIndex.value = -1
            clearTimeout(debounceTimer)
            if (val.trim().length < 2) {
                results.value = []
                return
            }
            debounceTimer = setTimeout(() => fetchResults(val.trim()), 350)
        })

        async function fetchResults(q) {
            isLoading.value = true
            try {
                const { data } = await axiosInstance.get('/products/search', {
                    params: { q, limit: 6 }
                })
                results.value = data.data ?? data
            } catch (e) {
                console.error('Search error:', e)
                results.value = []
            } finally {
                isLoading.value = false
            }
        }

        function selectProduct(product) {
            query.value = ''
            results.value = []
            router.push({ name: 'ProductDetail', params: { id: product.id } })
        }

        function goToSearch() {
            if (!query.value.trim()) return
            // Gunakan param ?search= agar kompatibel dengan index() di ProductController
            router.push({ name: 'SearchResults', query: { search: query.value.trim() } })
            emit('search', query.value.trim())
            closeDropdown()
        }

        function closeDropdown() {
            isFocused.value = false
        }

        function handleBlur() {
            setTimeout(() => { isFocused.value = false }, 150)
        }

        function clearSearch() {
            query.value = ''
            results.value = []
            inputRef.value?.focus()
        }

        function navigateDown() {
            if (activeIndex.value < results.value.length - 1) activeIndex.value++
        }

        function navigateUp() {
            if (activeIndex.value > 0) activeIndex.value--
        }

        function highlight(text, q) {
            if (!q) return text
            const escaped = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
            return text.replace(new RegExp(`(${escaped})`, 'gi'), '<mark>$1</mark>')
        }

        function formatPrice(price) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(price)
        }

        return {
            inputRef, query, results, isLoading, isFocused,
            activeIndex, showDropdown,
            selectProduct, goToSearch, closeDropdown,
            handleBlur, clearSearch, navigateDown, navigateUp,
            highlight, formatPrice
        }
    }
}
</script>

<style scoped>
.search-wrapper {
    position: relative;
    width: 100%;
    max-width: 560px;
    font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
}

/* Container input */
.search-container {
    display: flex;
    align-items: center;
    background: #f5f5f5;
    border: 1.5px solid transparent;
    border-radius: 12px;
    padding: 0 14px;
    gap: 10px;
    transition: all 0.2s ease;
    height: 48px;
}

.search-wrapper.is-focused .search-container {
    background: #fff;
    border-color: #111;
    box-shadow: 0 0 0 3px rgba(17,17,17,0.08);
}

.search-wrapper.has-results .search-container {
    border-radius: 12px 12px 0 0;
}

/* Icons */
.search-icon-wrap {
    flex-shrink: 0;
    display: flex;
    align-items: center;
}

.search-icon {
    width: 18px;
    height: 18px;
    color: #888;
    transition: color 0.2s;
}

.search-wrapper.is-focused .search-icon {
    color: #111;
}

.spinner {
    width: 18px;
    height: 18px;
    border: 2px solid #ddd;
    border-top-color: #111;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Input */
.search-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 0.95rem;
    color: #111;
    outline: none;
    min-width: 0;
}

.search-input::placeholder {
    color: #aaa;
}

/* Clear */
.clear-btn {
    flex-shrink: 0;
    background: #e0e0e0;
    border: none;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s;
}

.clear-btn:hover { background: #ccc; }

.clear-btn svg {
    width: 12px;
    height: 12px;
    color: #555;
}

/* Dropdown */
.search-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1.5px solid #111;
    border-top: none;
    border-radius: 0 0 12px 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    z-index: 1000;
    overflow: hidden;
}

.dropdown-section-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #aaa;
    padding: 10px 16px 4px;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    cursor: pointer;
    transition: background 0.15s;
    text-decoration: none;
    color: inherit;
    border: none;
    width: 100%;
    background: transparent;
}

.dropdown-item:hover,
.dropdown-item.is-active {
    background: #f5f5f5;
}

.item-image {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    background: #f0f0f0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.item-image-placeholder svg {
    width: 20px;
    height: 20px;
    color: #ccc;
}

.item-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.item-name {
    font-size: 0.9rem;
    font-weight: 500;
    color: #111;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.item-name :deep(mark) {
    background: #FFE066;
    color: #111;
    border-radius: 2px;
    padding: 0 1px;
}

.item-category {
    font-size: 0.75rem;
    color: #888;
}

.item-price {
    font-size: 0.875rem;
    font-weight: 700;
    color: #111;
    flex-shrink: 0;
}

/* View all */
.view-all-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 16px;
    font-size: 0.85rem;
    color: #111;
    background: #f9f9f9;
    border: none;
    border-top: 1px solid #eee;
    cursor: pointer;
    transition: background 0.15s;
}

.view-all-btn:hover { background: #f0f0f0; }

.view-all-btn svg {
    width: 14px;
    height: 14px;
}

/* Empty state */
.dropdown-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 28px 16px;
    color: #999;
    font-size: 0.875rem;
    text-align: center;
}

.dropdown-empty svg {
    width: 32px;
    height: 32px;
    opacity: 0.4;
}

.dropdown-empty p { margin: 0; color: #666; }
.dropdown-empty strong { color: #333; }

/* Transition */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>