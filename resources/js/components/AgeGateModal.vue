<template>
    <transition name="agegate-fade">
        <div v-if="visible" class="agegate-overlay">
            <div class="agegate-card">
                <!-- Logo -->
                <div class="agegate-logo-wrap">
                    <img v-if="logoUrl" :src="logoUrl" alt="Logo" class="agegate-logo" />
                    <span v-else class="agegate-logo-text">{{ siteName || 'Store' }}</span>
                </div>

                <!-- Heading -->
                <h2 class="agegate-title">Apakah kamu sudah berusia 21 tahun ke atas?</h2>
                <p class="agegate-desc">
                    Situs ini berisi produk dengan nikotin dan hanya untuk pengguna dewasa (21+).
                </p>

                <!-- Buttons -->
                <button class="agegate-btn agegate-btn--yes" @click="confirm">
                    YA, SAYA SUDAH 21+
                </button>
                <button class="agegate-btn agegate-btn--no" @click="deny">
                    SAYA BELUM 21
                </button>

                <hr class="agegate-divider" />

                <p class="agegate-disclaimer">
                    Dengan masuk, Anda menyatakan memahami bahwa produk di situs ini mengandung
                    nikotin yang bersifat adiktif.
                </p>
            </div>
        </div>
    </transition>
</template>

<script>
import { useSiteSettings } from '../composables/useSiteSettings'

const STORAGE_KEY = 'agegate_confirmed'

export default {
    name: 'AgeGateModal',

    data() {
        return {
            visible: false,
            logoUrl: null,
            siteName: '',
        }
    },

    async mounted() {
        // Hanya tampil di mobile
        if (window.innerWidth > 768) return

        // Kalau sudah pernah konfirmasi, skip
        if (sessionStorage.getItem(STORAGE_KEY)) return

        // Ambil logo & site name dari settings
        try {
            const { fetchSettings, settings } = useSiteSettings()
            await fetchSettings()
            const s = settings.value
            if (s?.site_logo_footer?.value)      this.logoUrl  = s.site_logo_footer.value
            else if (s?.site_logo?.value)        this.logoUrl  = s.site_logo.value
            if (s?.site_name?.value)             this.siteName = s.site_name.value
        } catch (e) {
            console.error('AgeGate: failed to load settings', e)
        }

        this.visible = true
        document.body.style.overflow = 'hidden'
    },

    methods: {
        confirm() {
            sessionStorage.setItem(STORAGE_KEY, '1')
            this.close()
        },

        deny() {
            // Redirect ke Google atau halaman lain
            window.location.href = 'https://www.google.com'
        },

        close() {
            this.visible = false
            document.body.style.overflow = ''
        },
    },
}
</script>

<style scoped>
.agegate-overlay {
    font-family: 'Poppins', sans-serif;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.agegate-card {
    background: #fff;
    border-radius: 16px;
    padding: 36px 28px 28px;
    width: 100%;
    max-width: 400px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    text-align: center;
}

.agegate-logo-wrap {
    margin-bottom: 4px;
}

.agegate-logo {
    height: 48px;
    width: auto;
    object-fit: contain;
}

.agegate-logo-text {
    font-size: 26px;
    font-weight: 800;
    color: #111;
    letter-spacing: -0.02em;
}

.agegate-title {
    font-size: 20px;
    font-weight: 600;
    color: #111;
    margin: 0;
    line-height: 1.35;
}

.agegate-desc {
    font-size: 13px;
    color: #555;
    margin: 0;
    line-height: 1.6;
}

.agegate-btn {
    width: 100%;
    padding: 15px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.04em;
    cursor: pointer;
    transition: opacity 0.15s ease;
    border: 1px solid transparent;
}

.agegate-btn:hover { opacity: 0.85; }

.agegate-btn--yes {
    background: #BD2028;
    color: #fff;
    border-color: #BD2028;
}

.agegate-btn--no {
    background: #fff;
    color: #111;
    border-color: #111;
}

.agegate-divider {
    width: 100%;
    border: none;
    border-top: 1px solid #e8e8e8;
    margin: 4px 0;
}

.agegate-disclaimer {
    font-size: 12px;
    color: #888;
    margin: 0;
    line-height: 1.6;
}

/* Transition */
.agegate-fade-enter-active,
.agegate-fade-leave-active {
    transition: opacity 0.25s ease;
}
.agegate-fade-enter-from,
.agegate-fade-leave-to {
    opacity: 0;
}
</style>