<template>
    <div class="newsletter-bar">
        <div class="newsletter-container">
            <p class="newsletter-title">Jadi member sekarang dan dapatkan promo spesial</p>
            <form class="newsletter-form" @submit.prevent="sendWA">
                <span class="newsletter-prefix">+62</span>
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
    padding: 120px 0;
    margin-top: 60px;
}
.newsletter-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    text-align: center;
}
.newsletter-title {
    font-family: "Poppins", sans-serif;
    font-size: 2.1rem;
    font-weight: 700;
    padding: 0px 300px;
    color: #ffffff;
    margin: 0;
}
.newsletter-form {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 6px;
    overflow: hidden;
    width: 500px;
    max-width: 100%;
}
.newsletter-prefix {
    padding: 12px 12px;
    font-size: 13px;
    font-weight: 700;
    color: #BD2028;
    background: #fff5f5;
    border-right: 1px solid #ddd;
    flex-shrink: 0;
    line-height: 1;
}
.newsletter-input {
    flex: 1;
    border: none;
    outline: none;
    padding: 12px 8px;
    font-size: 13px;
    color: #333;
    background: transparent;
    min-width: 0;
    font-family: inherit;
}
.newsletter-input::placeholder { color: #999; }
.newsletter-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    color: #aaa;
    border: none;
    border-left: 1px solid #ddd;
    padding: 12px 16px;
    cursor: pointer;
    transition: color 0.15s ease, background 0.15s ease;
    flex-shrink: 0;
}
.newsletter-btn:hover:not(:disabled) { color: #333; background: #f5f5f5; }
.newsletter-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.newsletter-msg { font-size: 12px; margin: 0; }
.msg-error { color: #fecaca; }

@media (max-width: 540px) {
    .newsletter-title {
        font-size: 1.4rem;
        padding: 0 10px;
    }
}
</style>