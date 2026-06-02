<template>
    <div class="page-bg">
        <div class="card" :class="{ 'card--visible': cardVisible }">

            <!-- ── LEFT PANEL ── -->
            <div class="left-panel">
                <img :src="'/storage/vector/tbm.png'" alt="Cover" class="cover-img" />
                <div class="cover-overlay"></div>
                <div class="left-logo">
                    <img :src="'/storage/logos/logo_1779259529.webp'" alt="Logo" class="left-logo-img left-logo--animate" />
                </div>
                <div class="left-bottom left-bottom--animate">
                    <blockquote class="quote-text">
                        "TB Web Services hadir sebagai platform kerja terpadu untuk mengelola seluruh operasional TB Store secara lebih modern, terstruktur, dan efisien. Sebagai bagian dari TB Group, platform ini dibangun untuk mendukung kolaborasi, produktivitas, dan pertumbuhan bisnis yang berkelanjutan"
                    </blockquote>
                    <div class="quote-author">
                        <p class="author-name">TB Workspace</p>
                        <p class="author-title">Tim TB Group</p>
                    </div>
                </div>
            </div>

            <!-- ── RIGHT PANEL ── -->
            <div class="right-panel">
                <div class="form-body">
                    <div class="form-header form-header--animate">
                        <h1>TB Workspace</h1>
                        <p>Platform kerja terintegrasi untuk seluruh operasional TB Store dalam ekosistem TB Group.</p>
                    </div>

                    <!-- Error alert -->
                    <transition name="alert-slide">
                        <div v-if="errorMessage" class="alert-error" :class="{ 'shake': doShake }">
                            <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            {{ errorMessage }}
                        </div>
                    </transition>

                    <!-- Email -->
                    <div class="login-field field--animate field--delay-1" :class="{ 'has-value': form.email }">
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder=""
                            autocomplete="username"
                            @keyup.enter="handleLogin"
                            id="email"
                        />
                        <label for="email">Email</label>
                    </div>

                    <!-- Password -->
                    <div class="login-field field--animate field--delay-2" :class="{ 'has-value': form.password }">
                        <div class="password-wrap">
                            <input
                                v-model="form.password"
                                :type="showPw ? 'text' : 'password'"
                                placeholder=""
                                autocomplete="current-password"
                                @keyup.enter="handleLogin"
                                id="password"
                            />
                            <label for="password">Password</label>
                            <button type="button" class="eye-btn" @click="showPw = !showPw">
                                <transition name="icon-flip">
                                    <svg v-if="!showPw" key="closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg v-else key="open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                                    </svg>
                                </transition>
                            </button>
                        </div>
                    </div>

                    <!-- Remember me -->
                    <div class="remember-row field--animate field--delay-3">
                        <span class="remember-label">Remember sign in details</span>
                        <button
                            type="button"
                            class="toggle-btn"
                            :class="{ active: rememberMe }"
                            @click="rememberMe = !rememberMe"
                            :aria-checked="rememberMe"
                            role="switch"
                        >
                            <span class="toggle-thumb"></span>
                        </button>
                    </div>

                    <!-- Sign In Button -->
                    <button
                        class="btn-signin field--animate field--delay-4"
                        @click="handleLogin"
                        @mousedown="createRipple"
                        :disabled="loading"
                    >
                        <span class="btn-ripple-container" ref="rippleContainer"></span>
                        <span v-if="loading" class="spinner"></span>
                        <span>{{ loading ? 'Signing in...' : 'Log in' }}</span>
                    </button>
                </div>

                <p class="footer-text field--animate field--delay-5">TB System · All rights reserved.</p>
            </div>

        </div>
    </div>
</template>

<script>
import axios from '../axios.js'
import { saveAuth } from '../auth.js'

export default {
    name: 'Login',
    data() {
        return {
            form: { email: '', password: '' },
            errorMessage: '',
            loading: false,
            showPw: false,
            rememberMe: true,
            doShake: false,
            cardVisible: false,
        }
    },
    mounted() {
        // Trigger entrance animation after a brief delay
        requestAnimationFrame(() => {
            setTimeout(() => { this.cardVisible = true }, 60)
        })
    },
    methods: {
        async handleLogin() {
            this.errorMessage = ''
            this.loading = true
            try {
                const response = await axios.post('/login', this.form)
                const { token, user } = response.data
                saveAuth({ token, user, rememberMe: this.rememberMe })
                this.$router.push('/admin/dashboard')
            } catch (error) {
                if (error.response?.status === 401) {
                    this.errorMessage = 'Email atau password salah!'
                } else {
                    this.errorMessage = 'Terjadi kesalahan, coba lagi.'
                }
                this.$nextTick(() => {
                    this.doShake = true
                    setTimeout(() => { this.doShake = false }, 600)
                })
            } finally {
                this.loading = false
            }
        },
        createRipple(event) {
            const button = event.currentTarget
            const container = this.$refs.rippleContainer
            if (!container) return

            const ripple = document.createElement('span')
            const rect = button.getBoundingClientRect()
            const size = Math.max(rect.width, rect.height) * 2
            const x = event.clientX - rect.left - size / 2
            const y = event.clientY - rect.top - size / 2

            ripple.style.cssText = `
                position:absolute;
                width:${size}px;height:${size}px;
                left:${x}px;top:${y}px;
                border-radius:50%;
                background:rgba(255,255,255,0.25);
                transform:scale(0);
                animation:ripple-expand 0.55s ease-out forwards;
                pointer-events:none;
            `
            container.appendChild(ripple)
            ripple.addEventListener('animationend', () => ripple.remove())
        }
    }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

/* ─── Page background ─── */
.page-bg {
    min-height: 100vh;
    background: #f0f0f0;
    display: flex;
    align-items: stretch;
    justify-content: center;
    font-family: 'DM Sans', sans-serif;
}

/* ─── Card entrance ─── */
.card {
    display: flex;
    background: #ffffff;
    width: 100%;
    min-height: 100vh;
    border-radius: 0;
    box-shadow: none;
    overflow: hidden;
    opacity: 0;
    transform: translateY(12px);
    transition: opacity 0.55s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
}
.card--visible {
    opacity: 1;
    transform: translateY(0);
}

/* ══════════════════════════
   LEFT PANEL
══════════════════════════ */
.left-panel {
    width: 42%;
    flex-shrink: 0;
    position: relative;
    background: #1a1a1a;
    overflow: hidden;
}

.cover-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    transform: scale(1.04);
    transition: transform 8s ease-out;
}
.card--visible .cover-img {
    transform: scale(1);
}

.cover-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(0,0,0,0.15) 0%,
        rgba(0,0,0,0.05) 40%,
        rgba(0,0,0,0.70) 100%
    );
}

.left-logo {
    position: absolute;
    top: 24px;
    left: 24px;
    z-index: 2;
}

.left-logo-img {
    width: 176px;
    height: 176px;
    object-fit: contain;
}

/* Logo entrance */
.left-logo--animate {
    opacity: 0;
    transform: translateY(-10px);
    transition: opacity 0.6s ease 0.3s, transform 0.6s cubic-bezier(0.22,1,0.36,1) 0.3s;
}
.card--visible .left-logo--animate {
    opacity: 1;
    transform: translateY(0);
}

/* Quote entrance */
.left-bottom {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 2;
    padding: 28px 28px 32px;
}
.left-bottom--animate {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.7s ease 0.5s, transform 0.7s cubic-bezier(0.22,1,0.36,1) 0.5s;
}
.card--visible .left-bottom--animate {
    opacity: 1;
    transform: translateY(0);
}

.quote-text {
    font-family: "Poppins", sans-serif;
    font-size: 12px;
    font-weight: 400;
    font-style: italic;
    color: #ffffff;
    line-height: 1.55;
    margin-bottom: 20px;
    letter-spacing: -0.3px;
}
.author-name {
    font-size: 15px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 3px;
}
.author-title {
    font-size: 13px;
    color: rgba(255,255,255,0.65);
}

/* ══════════════════════════
   RIGHT PANEL
══════════════════════════ */
.right-panel {
    font-family: "Poppins", sans-serif;
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 64px 80px 48px;
    overflow-y: auto;
}

.form-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 20px;
    max-width: 420px;
    width: 100%;
    margin: 0 auto;
}

/* ─── Staggered entrance for form elements ─── */
.form-header--animate {
    opacity: 0;
    transform: translateY(14px);
    transition: opacity 0.55s ease 0.2s, transform 0.55s cubic-bezier(0.22,1,0.36,1) 0.2s;
}
.card--visible .form-header--animate {
    opacity: 1;
    transform: translateY(0);
}

.field--animate {
    opacity: 0;
    transform: translateY(12px);
    transition: opacity 0.5s ease, transform 0.5s cubic-bezier(0.22,1,0.36,1);
}
.card--visible .field--animate { opacity: 1; transform: translateY(0); }
.field--delay-1 { transition-delay: 0.30s; }
.field--delay-2 { transition-delay: 0.40s; }
.field--delay-3 { transition-delay: 0.50s; }
.field--delay-4 { transition-delay: 0.58s; }
.field--delay-5 { transition-delay: 0.66s; }

.form-header { margin-bottom: 8px; }
.form-header h1 {
    font-size: 30px;
    font-weight: 600;
    color: #111;
    letter-spacing: -0.6px;
    margin-bottom: 10px;
    line-height: 1.2;
}
.form-header p {
    font-size: 14px;
    color: #aaa;
    line-height: 1.6;
}

/* ─── Alert with slide transition + shake ─── */
.alert-error {
    background: #fff0f0;
    border: 1px solid #fac8c8;
    color: #c0392b;
    font-size: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.alert-icon {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    color: #c0392b;
}
.alert-slide-enter-active { transition: all 0.3s cubic-bezier(0.22,1,0.36,1); }
.alert-slide-leave-active { transition: all 0.2s ease-in; }
.alert-slide-enter-from { opacity: 0; transform: translateY(-8px) scale(0.97); }
.alert-slide-leave-to   { opacity: 0; transform: translateY(-4px); }

@keyframes shake {
    0%,100% { transform: translateX(0); }
    15%     { transform: translateX(-7px); }
    30%     { transform: translateX(6px); }
    45%     { transform: translateX(-5px); }
    60%     { transform: translateX(4px); }
    75%     { transform: translateX(-3px); }
    90%     { transform: translateX(2px); }
}
.shake { animation: shake 0.55s ease; }

/* ─── Fields ─── */
.login-field {
    position: relative;
    border: none;
    outline: none;
    box-shadow: none;
    background: transparent;
}

.login-field > input,
.login-field .password-wrap input {
    width: 100%;
    border: 1.5px solid #d0d0d0 !important;
    border-radius: 10px !important;
    padding: 22px 16px 8px !important;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    color: #222;
    background: #fff !important;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    box-shadow: none !important;
    transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
}

.login-field > label,
.login-field .password-wrap > label {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    color: #aaa;
    pointer-events: none;
    transition: all 0.2s cubic-bezier(0.22,1,0.36,1);
}

.login-field:focus-within > label,
.login-field:focus-within .password-wrap > label {
    top: 10px;
    transform: translateY(0);
    font-size: 11px;
    color: #ED1F24;
}

.login-field.has-value > label,
.login-field.has-value .password-wrap > label {
    top: 10px;
    transform: translateY(0);
    font-size: 11px;
    color: #aaa;
}

.login-field:focus-within > input,
.login-field:focus-within .password-wrap input {
    border-color: #ED1F24 !important;
    box-shadow: 0 0 0 3px rgba(237,31,36,0.08) !important;
}

.password-wrap { position: relative; }
.login-field input::placeholder { color: #ccc; }
.login-field input:-webkit-autofill,
.login-field input:-webkit-autofill:hover,
.login-field input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px #ffffff inset;
    -webkit-text-fill-color: #222;
    transition: background-color 5000s ease-in-out 0s;
}
.login-field input:not(:-webkit-autofill) { animation-name: onautofillcancel; }
@keyframes onautofillcancel {}
input:-internal-autofill-selected,
input:-internal-autofill-previewed {
    background-color: transparent !important;
    background-image: none !important;
    color: #222 !important;
    box-shadow: 0 0 0px 1000px #ffffff inset !important;
}
.login-field input:focus {
    border-color: #ED1F24 !important;
    box-shadow: 0 0 0 3px rgba(237,31,36,0.08) !important;
}

/* ─── Eye button + icon flip transition ─── */
.eye-btn {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #ccc;
    padding: 0;
    display: flex;
    align-items: center;
    transition: color 0.15s, transform 0.15s;
    width: 16px;
    height: 16px;
    overflow: visible;
}
.eye-btn:hover { color: #ED1F24; transform: translateY(-50%) scale(1.15); }
.eye-btn svg  { width: 16px; height: 16px; position: absolute; top:0; left:0; }

.icon-flip-enter-active { transition: opacity 0.15s, transform 0.18s; }
.icon-flip-leave-active { transition: opacity 0.1s, transform 0.12s; position:absolute; }
.icon-flip-enter-from   { opacity: 0; transform: rotateY(90deg); }
.icon-flip-leave-to     { opacity: 0; transform: rotateY(-90deg); }

.forgot-row { display: flex; justify-content: flex-start; margin-top: 2px; }
.forgot-link {
    font-size: 12px;
    color: #ED1F24;
    text-decoration: none;
    font-weight: 500;
    position: relative;
    transition: color 0.15s;
}
.forgot-link::after {
    content: '';
    position: absolute;
    bottom: -1px; left: 0; right: 100%;
    height: 1px;
    background: #ED1F24;
    transition: right 0.25s ease;
}
.forgot-link:hover::after { right: 0; }

/* ─── Remember toggle ─── */
.remember-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.remember-label { font-size: 13px; color: #888; }
.toggle-btn {
    width: 42px;
    height: 24px;
    border-radius: 100px;
    background: #e0e0e0;
    border: none;
    cursor: pointer;
    position: relative;
    transition: background 0.25s cubic-bezier(0.22,1,0.36,1);
    padding: 0;
    flex-shrink: 0;
}
.toggle-btn.active { background: #ED1F24; }
.toggle-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.18);
    transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1);
}
.toggle-btn.active .toggle-thumb { transform: translateX(18px); }

/* ─── Sign In Button with ripple ─── */
.btn-signin {
    width: 100%;
    background: #ED1F24;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 15px;
    font-size: 15px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    margin-top: 4px;
    letter-spacing: 0.1px;
    position: relative;
    overflow: hidden;
}
.btn-signin:hover:not(:disabled) {
    background: #c91519;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(237,31,36,0.32);
}
.btn-signin:active:not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(237,31,36,0.2);
}
.btn-signin:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-ripple-container {
    position: absolute;
    inset: 0;
    overflow: hidden;
    border-radius: inherit;
    pointer-events: none;
}

@keyframes ripple-expand {
    to { transform: scale(1); opacity: 0; }
}

.spinner {
    width: 13px;
    height: 13px;
    border: 2px solid rgba(255,255,255,0.35);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.65s linear infinite;
    flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

.footer-text {
    font-size: 10.5px;
    color: #ccc;
    text-align: center;
    margin-top: 24px;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .left-panel { display: none; }
    .right-panel { padding: 48px 32px; }
    .form-body { max-width: 100%; }
}
</style>