<template>
    <div class="newsletter-bar">
        <div class="newsletter-container">
            <p class="newsletter-title">Jadi member sekarang dan dapatkan promo spesial</p>
            <form class="newsletter-form" @submit.prevent="subscribeNewsletter">
                <span class="newsletter-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </span>
                <input
                    v-model="email"
                    type="email"
                    placeholder="Nomor handphone"
                    class="newsletter-input"
                    required
                />
                <button type="submit" class="newsletter-btn" :disabled="subscribing">
                    <svg v-if="!subscribing" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                    <span v-else>...</span>
                </button>
            </form>
            <p v-if="subscribeMsg" class="newsletter-msg" :class="subscribeSuccess ? 'msg-success' : 'msg-error'">
                {{ subscribeMsg }}
            </p>
        </div>
    </div>
</template>

<script>
import axios from '../axios.js'

export default {
    name: 'NewsletterBar',

    data() {
        return {
            email: '',
            subscribing: false,
            subscribeMsg: '',
            subscribeSuccess: false,
        }
    },

    methods: {
        async subscribeNewsletter() {
            if (!this.email) return
            this.subscribing = true
            this.subscribeMsg = ''
            try {
                await axios.post('/newsletter/subscribe', { email: this.email })
                this.subscribeSuccess = true
                this.subscribeMsg = 'Terima kasih! Kamu sudah terdaftar.'
                this.email = ''
            } catch (e) {
                this.subscribeSuccess = false
                this.subscribeMsg = e.response?.data?.message ?? 'Gagal mendaftar, coba lagi.'
            } finally {
                this.subscribing = false
            }
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
    border: 1px solid #fff;
    border-radius: 6px;
    overflow: hidden;
    width: 500px;
    max-width: 100%;
}
.newsletter-icon {
    display: flex;
    align-items: center;
    padding: 0 12px;
    color: #888;
    flex-shrink: 0;
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
    font-family: inherit;
    flex-shrink: 0;
}
.newsletter-btn:hover:not(:disabled) { color: #333; background: #f5f5f5; }
.newsletter-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.newsletter-msg { font-size: 12px; margin: 0; }
.msg-success { color: #a7f3d0; }
.msg-error   { color: #fecaca; }

@media (max-width: 540px) {
    .newsletter-title {
        font-size: 1.4rem;
        padding: 0 10px;
    }
}
</style>