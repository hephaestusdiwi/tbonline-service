<template>
    <div class="newsletter-bar">
        <div class="newsletter-container">

            <div class="newsletter-card">
                <h2 class="newsletter-title">Gabung Member Sekarang,<br />Dapatkan Promo Spesial</h2>
                <p class="newsletter-subtitle">Hanya dengan nomor telfon saja, yuk daftar</p>

                <form class="newsletter-form" @submit.prevent="sendWA">
                    <span class="newsletter-prefix">62</span>
                    <input
                        v-model="phone"
                        type="tel"
                        placeholder="812-3456-7890"
                        class="newsletter-input"
                        @input="formatPhone"
                        maxlength="13"
                        required
                    />
                    <button type="submit" class="newsletter-btn" :disabled="!isPhoneValid">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>
                <p v-if="errorMsg" class="newsletter-msg msg-error">{{ errorMsg }}</p>
            </div>

            <div class="newsletter-features">
                <div class="feature-item">
                    <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 18v-6a9 9 0 0118 0v6"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>
                    </svg>
                    <div class="feature-text">
                        <p class="feature-title">GREAT SERVICE</p>
                        <p class="feature-desc">Always here to help</p>
                    </div>
                </div>

                <div class="feature-item">
                    <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9l1.5-5h15L21 9M3 9v10a1 1 0 001 1h16a1 1 0 001-1V9M3 9h18M9 21v-6h6v6"/>
                    </svg>
                    <div class="feature-text">
                        <p class="feature-title">MULTIPLE STORES</p>
                        <p class="feature-desc">Find us near you</p>
                    </div>
                </div>

                <div class="feature-item">
                    <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                    </svg>
                    <div class="feature-text">
                        <p class="feature-title">SAFE &amp; TRUSTED</p>
                        <p class="feature-desc">Shop with confidence</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
import { useSiteSettings } from '../composables/useSiteSettings'

export default {
    name: 'NewsletterBar',

    setup() {
        const { adminWhatsapp } = useSiteSettings()
        return { adminWhatsapp }
    },

    data() {
        return {
            phone: '',
            errorMsg: '',
        }
    },

    computed: {
        isPhoneValid() {
            const cleaned = this.phone.replace(/\D/g, '')
            return cleaned.length >= 9 && cleaned.length <= 13
        },
    },

    methods: {
        formatPhone() {
            let val = this.phone.replace(/\D/g, '')
            if (val.startsWith('62')) val = val.slice(2)
            if (val.startsWith('0')) val = val.slice(1)
            this.phone = val
        },

        sendWA() {
            if (!this.isPhoneValid) {
                this.errorMsg = 'Nomor handphone tidak valid.'
                return
            }
            this.errorMsg = ''
            const msg = `Halo Admin, saya ingin mendaftar sebagai member TB Point.\n\nNomor HP: +62${this.phone}\n\nMohon bantuannya untuk proses pendaftaran, terima kasih! 🙏`
            window.open(`https://wa.me/${this.adminWhatsapp}?text=${encodeURIComponent(msg)}`, '_blank')
            this.phone = ''
        },
    },
}
</script>

<style scoped>
.newsletter-bar {
    background: linear-gradient(135deg, #BD2028 0%, #B31217 100%);
    padding: 64px 24px;
}
.newsletter-container {
    max-width: 1000px;
    margin: 0 auto;
}

/* ─── Card hitam ─── */
.newsletter-card {
    background: #141414;
    border-radius: 10px;
    padding: 44px 40px;
    text-align: center;
    max-width: 720px;
    margin: 0 auto;
}
.newsletter-title {
    font-family: "Poppins", sans-serif;
    font-size: 1.55rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 8px;
    line-height: 1.35;
}
.newsletter-subtitle {
    font-family: "Poppins", sans-serif;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.55);
    margin: 0 0 22px;
}

.newsletter-form {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    max-width: 420px;
    margin: 0 auto;
}
.newsletter-prefix {
    padding: 13px 14px;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    border-right: 1px solid #e5e5e5;
    flex-shrink: 0;
    line-height: 1;
}
.newsletter-input {
    flex: 1;
    border: none;
    outline: none;
    padding: 13px 10px;
    font-size: 14px;
    color: #333;
    background: transparent;
    min-width: 0;
    font-family: inherit;
}
.newsletter-input::placeholder { color: #b0b0b0; }
.newsletter-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    color: #141414;
    border: none;
    padding: 13px 16px;
    cursor: pointer;
    transition: opacity 0.15s ease;
    flex-shrink: 0;
}
.newsletter-btn:hover:not(:disabled) { opacity: 0.6; }
.newsletter-btn:disabled { opacity: 0.3; cursor: not-allowed; }
.newsletter-msg { font-size: 12px; margin: 12px 0 0; }
.msg-error { color: #ff8080; }

/* ─── Feature badges di bawah card ─── */
.newsletter-features {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 36px;
    margin-top: 36px;
}
.feature-item {
    display: flex;
    align-items: center;
    gap: 10px;
}
.feature-icon {
    width: 26px;
    height: 26px;
    color: #fff;
    flex-shrink: 0;
}
.feature-title {
    font-family: "Poppins", sans-serif;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    color: #fff;
    margin: 0;
}
.feature-desc {
    font-family: "Poppins", sans-serif;
    font-size: 0.72rem;
    color: rgba(255,255,255,0.65);
    margin: 1px 0 0;
}

@media (max-width: 640px) {
    .newsletter-card { padding: 32px 22px; }
    .newsletter-title { font-size: 1.25rem; }
    .newsletter-features { gap: 22px; }
    .feature-icon { width: 22px; height: 22px; }
}
</style>