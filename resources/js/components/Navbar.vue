<template>
  <!--
    AnnouncementBar berada di luar navbar.
    Jadi AnnouncementBar ikut scroll normal dan tidak ikut fixed/sticky.
  -->
  <AnnouncementBar
    :enabled="showAnnouncement"
    bg-color="#000000"
    text-color="#ffffff"
    :interval="4000"
  />

  <nav class="navbar" :class="{ 'menu-open': mobileOpen }">

    <!-- Main Bar -->
    <div class="navbar-inner">

      <!-- Logo -->
      <router-link
        to="/"
        class="navbar-logo"
        @click="closeMobile"
      >
        <img
          v-if="siteLogo"
          :src="siteLogo"
          :alt="siteName"
          class="logo-img"
        />

        <span
          v-else
          class="logo-text"
        >
          {{ siteName }}
        </span>
      </router-link>

      <!-- Desktop Menu -->
      <div class="desktop-menu">
        <router-link
          v-for="menu in menus"
          :key="menu.id"
          :to="menu.url"
          class="desktop-link"
          active-class="desktop-link--active"
        >
          {{ menu.label }}
        </router-link>
      </div>

      <!-- Right Icons -->
      <div class="navbar-actions">

        <!-- Search: desktop only -->
        <div class="search-wrapper">
          <button
            ref="searchIconRef"
            class="icon-btn"
            aria-label="Search"
            @click="openSearch"
          >
            <font-awesome-icon
              :icon="['fas', 'magnifying-glass']"
            />
          </button>
        </div>

        <!-- Cart -->
        <button
          class="icon-btn cart-btn"
          aria-label="Cart"
          @click="cart.open()"
        >
          <font-awesome-icon
            :icon="['fas', 'cart-shopping']"
          />

          <span
            v-if="cart.totalItems > 0"
            class="cart-badge"
          >
            {{ cart.totalItems > 99 ? '99+' : cart.totalItems }}
          </span>
        </button>

        <!-- Hamburger — mobile only -->
        <button
          class="icon-btn hamburger"
          :class="{ 'is-open': mobileOpen }"
          :aria-label="mobileOpen ? 'Tutup menu' : 'Buka menu'"
          @click="toggleMobile"
        >
          <span class="bar bar-top"></span>
          <span class="bar bar-mid"></span>
          <span class="bar bar-bot"></span>
        </button>

      </div>
    </div>

    <!-- Mobile Full-Screen Overlay -->
    <div class="overlay-container">
      <Transition name="apple-menu">
        <div
          v-if="mobileOpen"
          class="apple-overlay"
        >

          <!-- Close button -->
          <button
            class="apple-close"
            aria-label="Tutup menu"
            @click="closeMobile"
          >
            <font-awesome-icon
              :icon="['fas', 'xmark']"
            />
          </button>

          <!-- Nav links -->
          <nav
            class="apple-links"
            aria-label="Mobile navigation"
          >
            <router-link
              v-for="(menu, i) in menus"
              :key="menu.id"
              :to="menu.url"
              class="apple-link"
              :style="{
                animationDelay: `${0.04 + i * 0.045}s`
              }"
              active-class="apple-link--active"
              @click="closeMobile"
            >
              {{ menu.label }}
            </router-link>
          </nav>

          <!-- Bottom utility row -->
          <div class="apple-utility">

            <button
              class="apple-util-btn"
              @click="openSearch(); closeMobile()"
            >
              <font-awesome-icon
                :icon="['fas', 'magnifying-glass']"
                class="util-icon"
              />

              <span>Search</span>
            </button>

            <button
              class="apple-util-btn"
              @click="cart.open(); closeMobile()"
            >
              <font-awesome-icon
                :icon="['fas', 'cart-shopping']"
                class="util-icon"
              />

              <span>Cart</span>

              <span
                v-if="cart.totalItems > 0"
                class="util-badge"
              >
                {{ cart.totalItems }}
              </span>
            </button>

          </div>
        </div>
      </Transition>
    </div>

    <SearchOverlay v-model="searchOpen" />

  </nav>
</template>

<script>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import axios from '../axios.js'
import AnnouncementBar from './AnnouncementBar.vue'
import { cartStore } from '../store/cartStore'
import SearchOverlay from './SearchOverlay.vue'
import { useSiteSettings } from '../composables/useSiteSettings'

export default {
  name: 'Navbar',

  components: {
    AnnouncementBar,
    SearchOverlay,
  },

  setup() {
    const searchOpen = ref(false)
    const mobileOpen = ref(false)
    const searchIconRef = ref(null)
    const searchIconRect = ref(null)

    const route = useRoute()

    const showAnnouncement = computed(
      () => !route.meta.hideAnnouncement
    )

    const {
      siteLogo,
      siteName,
      fetchSettings,
    } = useSiteSettings()

    fetchSettings()

    function openSearch() {
      searchIconRect.value =
        searchIconRef.value?.getBoundingClientRect() ?? null

      searchOpen.value = true
    }

    function toggleMobile() {
      mobileOpen.value = !mobileOpen.value

      document.body.style.overflow =
        mobileOpen.value ? 'hidden' : ''
    }

    function closeMobile() {
      mobileOpen.value = false
      document.body.style.overflow = ''
    }

    return {
      cart: cartStore,

      searchOpen,
      mobileOpen,

      searchIconRef,
      searchIconRect,

      openSearch,
      toggleMobile,
      closeMobile,

      siteLogo,
      siteName,
      showAnnouncement,
    }
  },

  data() {
    return {
      menus: [],
    }
  },

  async mounted() {
    await this.fetchMenus()
  },

  unmounted() {
    document.body.style.overflow = ''
  },

  methods: {
    async fetchMenus() {
      try {
        const response = await axios.get('/navigations')
        this.menus = response.data
      } catch (e) {
        console.error(e)
      }
    },
  },
}
</script>

<style scoped>

/* =========================================================
   NAVBAR BASE
   ========================================================= */

.navbar {
  background: #BD2028;
  position: sticky !important;
  top: 0 !important;
  z-index: 99999 !important;
}


/* =========================================================
   NAVBAR INNER
   ========================================================= */

.navbar-inner {
  max-width: 80rem;
  margin: 0 auto;
  padding: 1.25rem 1.25rem;

  display: flex;
  align-items: center;
  justify-content: space-between;

  gap: 1.5rem;
}


/* =========================================================
   LOGO
   ========================================================= */

.navbar-logo {
  flex-shrink: 0;
  text-decoration: none;
}

.logo-img {
  height: 2.5rem;
  width: auto;
  object-fit: contain;
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 900;
  letter-spacing: -0.02em;
  color: #fff;
}


/* =========================================================
   DESKTOP MENU
   ========================================================= */

.desktop-menu {
  display: none;
  align-items: center;
  gap: 2rem;
}

@media (min-width: 768px) {
  .desktop-menu {
    display: flex;
  }
}

.desktop-link {
  font-family: 'Poppins', sans-serif;
  font-size: 1.05rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.88);

  text-decoration: none;

  transition: color 0.2s;
}

.desktop-link:hover,
.desktop-link--active {
  color: #fff;
  font-weight: 600;
}


/* =========================================================
   ACTION ICONS
   ========================================================= */

.navbar-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.icon-btn {
  background: none;
  border: none;
  cursor: pointer;

  color: rgba(255, 255, 255, 0.9);

  transition: color 0.2s;

  padding: 0.25rem;

  font-size: 1.2rem;

  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-btn:hover {
  color: #fff;
}


/* Search */

.search-btn {
  display: none;
}

@media (min-width: 768px) {
  .search-btn {
    display: flex;
  }
}

.search-wrapper {
  display: none;
}

@media (min-width: 768px) {
  .search-wrapper {
    display: flex;
    align-items: center;
  }
}


/* Cart */

.cart-btn {
  position: relative;
}

.cart-badge {
  font-family: "Poppins", sans-serif;

  position: absolute;
  top: -7px;
  right: -10px;

  background: #fff;
  color: #BD2028;

  font-size: 0.65rem;
  font-weight: 800;

  min-width: 19px;
  height: 19px;

  border-radius: 999px;

  display: flex;
  align-items: center;
  justify-content: center;

  padding: 0 4px;

  line-height: 1;

  border: 2px solid #BD2028;

  box-shadow:
    0 2px 6px rgba(225, 29, 72, 0.5);
}


/* =========================================================
   HAMBURGER
   ========================================================= */

.hamburger {
  display: flex;

  flex-direction: column;
  justify-content: center;

  gap: 5px;

  width: 28px;
  height: 28px;

  padding: 0;
}

@media (min-width: 768px) {
  .hamburger {
    display: none;
  }
}

.bar {
  display: block;

  width: 22px;
  height: 1.5px;

  background: #fff;

  border-radius: 2px;

  transform-origin: center;

  transition:
    transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
    opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hamburger.is-open .bar-top {
  transform:
    translateY(6.5px)
    rotate(45deg);
}

.hamburger.is-open .bar-mid {
  opacity: 0;
  transform: scaleX(0);
}

.hamburger.is-open .bar-bot {
  transform:
    translateY(-6.5px)
    rotate(-45deg);
}


/* =========================================================
   APPLE STYLE MOBILE OVERLAY
   ========================================================= */

.apple-overlay {
  position: fixed;
  inset: 0;

  z-index: 49;

  background: #f5f5f7;

  padding: 5rem 2rem 2.5rem;

  padding-top:
    calc(
      5rem +
      env(safe-area-inset-top, 0px)
    );

  display: flex;
  flex-direction: column;

  overflow-y: auto;

  -webkit-overflow-scrolling: touch;
}


/* Close */

.apple-close {
  position: absolute;

  top:
    calc(
      1.25rem +
      env(safe-area-inset-top, 0px)
    );

  right: 1.5rem;

  width: 30px;
  height: 30px;

  background: #e0e0e5;

  border: none;
  border-radius: 50%;

  cursor: pointer;

  color: #1d1d1f;

  font-size: 0.875rem;

  display: flex;
  align-items: center;
  justify-content: center;

  transition: background 0.15s;

  -webkit-tap-highlight-color: transparent;
}

.apple-close:active {
  background: #c8c8cd;
}


/* =========================================================
   MOBILE NAV LINKS
   ========================================================= */

.apple-links {
  flex: 1;

  display: flex;
  flex-direction: column;

  padding-top: 0.5rem;
}

.apple-link {
  display: block;

  font-family:
    'SF Pro Display',
    'Poppins',
    -apple-system,
    BlinkMacSystemFont,
    sans-serif;

  font-size: 2rem;
  font-weight: 600;

  letter-spacing: -0.025em;

  line-height: 1.2;

  color: #1d1d1f;

  text-decoration: none;

  padding: 0.6rem 0;

  opacity: 0;

  transform: translateY(16px);

  animation:
    link-in
    0.4s
    cubic-bezier(0.22, 1, 0.36, 1)
    forwards;

  -webkit-tap-highlight-color: transparent;

  transition: color 0.15s;
}

.apple-link:active {
  color: #BD2028;
}

.apple-link--active {
  color: #BD2028;
}

@keyframes link-in {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}


/* =========================================================
   MOBILE UTILITY
   ========================================================= */

.apple-utility {
  display: flex;

  gap: 0.75rem;

  padding-top: 2rem;

  border-top: 1px solid #d2d2d7;

  margin-top: auto;
}

.apple-util-btn {
  flex: 1;

  display: flex;
  align-items: center;
  justify-content: center;

  gap: 0.5rem;

  padding: 0.75rem 1rem;

  background: #e8e8ed;

  border: none;
  border-radius: 12px;

  font-family:
    'SF Pro Text',
    'Poppins',
    -apple-system,
    sans-serif;

  font-size: 0.9375rem;
  font-weight: 500;

  color: #1d1d1f;

  cursor: pointer;

  transition: background 0.15s;

  -webkit-tap-highlight-color: transparent;
}

.apple-util-btn:active {
  background: #d1d1d6;
}

.util-icon {
  font-size: 1rem;
  color: #1d1d1f;
}

.util-badge {
  background: #BD2028;
  color: #fff;

  font-size: 0.65rem;
  font-weight: 700;

  min-width: 18px;
  height: 18px;

  border-radius: 999px;

  display: inline-flex;
  align-items: center;
  justify-content: center;

  padding: 0 3px;
}


/* =========================================================
   TRANSITION
   ========================================================= */

.apple-menu-enter-active {
  transition:
    opacity 0.28s ease,
    transform 0.32s
      cubic-bezier(0.22, 1, 0.36, 1);
}

.apple-menu-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.22s
      cubic-bezier(0.4, 0, 1, 1);
}

.apple-menu-enter-from,
.apple-menu-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.apple-overlay[style*="display: none"] {
  pointer-events: none;
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767px) {

  /*
   * MOBILE NAVBAR
   *
   * Sticky diganti fixed supaya Navbar benar-benar
   * menempel pada viewport saat halaman di-scroll.
   */
  .navbar {
    position: fixed !important;

    top: 0 !important;
    left: 0 !important;
    right: 0 !important;

    width: 100% !important;

    z-index: 99999 !important;
  }

  .navbar-inner {
    position: relative;
    min-height: 56px;
  }

  .navbar-logo {
    position: absolute;

    left: 60px;
    top: 50%;

    transform: translateY(-50%);
  }

  .navbar-actions {
    width: 100%;

    display: flex;

    justify-content: space-between;

    gap: 0;
  }

  .search-wrapper {
    display: none;
  }

  .hamburger {
    order: 1;
  }

  .cart-btn {
    order: 2;
  }
}


/* =========================================================
   DESKTOP
   ========================================================= */

@media (min-width: 768px) {
  .navbar {
    position: sticky !important;

    top: 0 !important;

    z-index: 99999 !important;
  }
}

</style>