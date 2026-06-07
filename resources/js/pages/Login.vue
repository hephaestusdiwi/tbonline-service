<template>
    <div class="page-bg">
        <div class="card" :class="{ 'card--visible': cardVisible }">

            <!-- ── LEFT PANEL ── -->
            <div class="left-panel">
                <img :src="'/storage/vector/tbm.png'" alt="Cover" class="cover-img" />
                <div class="cover-overlay"></div>
                <div class="left-noise"></div>
                <div class="left-logo">
                    <img :src="'/storage/logos/logo_1779259529.webp'" alt="Logo" class="left-logo-img left-logo--animate" />
                </div>
                <div class="left-bottom left-bottom--animate">
                    <div class="quote-line"></div>
                    <blockquote class="quote-text">
                        "TB Web Services hadir sebagai platform kerja terpadu untuk mengelola seluruh operasional TB Store secara lebih modern, terstruktur, dan efisien."
                    </blockquote>
                    <div class="quote-author">
                        <div class="author-avatar">TB</div>
                        <div>
                            <p class="author-name">TB Workspace</p>
                            <p class="author-title">Tim TB Group</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── RIGHT PANEL ── -->
            <div class="right-panel">
                <div class="form-body">
                    <div class="form-header form-header--animate">
                        <h1>TB Workspace</h1>
                        <p>Platform kerja terintegrasi untuk operasional TB Store dalam ekosistem TB Group</p>
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
                        <span>{{ loading ? 'Verifying...' : 'Sign In to Workspace' }}</span>
                        <svg v-if="!loading" class="btn-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <div class="footer-row field--animate field--delay-5">
                    <div class="footer-dots">
                        <span class="footer-dot footer-dot--active"></span>
                        <span class="footer-dot"></span>
                        <span class="footer-dot"></span>
                    </div>
                    <p class="footer-text">© {{ new Date().getFullYear() }} TB Group · All rights reserved</p>
                </div>
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
                background:rgba(255,255,255,0.2);
                transform:scale(0);
                animation:ripple-expand 0.6s ease-out forwards;
                pointer-events:none;
            `
            container.appendChild(ripple)
            ripple.addEventListener('animationend', () => ripple.remove())
        }
    }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

/* ─── Page background ─── */
.page-bg {
    min-height: 100vh;
    background: #0f0f0f;
    display: flex;
    align-items: stretch;
    justify-content: center;
    font-family: 'Inter', 'DM Sans', sans-serif;
}

/* ─── Card ─── */
.card {
    display: flex;
    background: #ffffff;
    width: 100%;
    min-height: 100vh;
    overflow: hidden;
    opacity: 0;
    transform: scale(0.985) translateY(8px);
    transition: opacity 0.6s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}
.card--visible {
    opacity: 1;
    transform: scale(1) translateY(0);
}

/* ══════════════════════════
   LEFT PANEL
══════════════════════════ */
.left-panel {
    width: 44%;
    flex-shrink: 0;
    position: relative;
    background: #111;
    overflow: hidden;
}

.cover-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    transform: scale(1.06);
    transition: transform 10s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    filter: saturate(0.85) brightness(0.92);
}
.card--visible .cover-img { transform: scale(1); }

.cover-overlay {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(180deg, rgba(0,0,0,0.28) 0%, rgba(0,0,0,0) 35%, rgba(0,0,0,0.72) 100%),
        linear-gradient(90deg, rgba(0,0,0,0.18) 0%, rgba(0,0,0,0) 60%);
}

/* Subtle grain texture */
.left-noise {
    position: absolute;
    inset: 0;
    opacity: 0.035;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    background-size: 200px 200px;
    pointer-events: none;
}

.left-logo {
    position: absolute;
    top: 28px;
    left: 28px;
    z-index: 2;
}
.left-logo-img {
    width: 160px;
    height: 160px;
    object-fit: contain;
    filter: drop-shadow(0 2px 12px rgba(0,0,0,0.3));
}
.left-logo--animate {
    opacity: 0;
    transform: translateY(-8px);
    transition: opacity 0.7s ease 0.35s, transform 0.7s cubic-bezier(0.22,1,0.36,1) 0.35s;
}
.card--visible .left-logo--animate { opacity: 1; transform: translateY(0); }

.left-bottom {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    z-index: 2;
    padding: 32px 32px 40px;
}
.left-bottom--animate {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.8s ease 0.55s, transform 0.8s cubic-bezier(0.22,1,0.36,1) 0.55s;
}
.card--visible .left-bottom--animate { opacity: 1; transform: translateY(0); }

.quote-line {
    width: 32px;
    height: 2px;
    background: rgba(255,255,255,0.5);
    margin-bottom: 16px;
    border-radius: 2px;
}

.quote-text {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 400;
    font-style: italic;
    color: rgba(255,255,255,0.82);
    line-height: 1.7;
    margin-bottom: 20px;
    letter-spacing: 0.1px;
}

.quote-author {
    display: flex;
    align-items: center;
    gap: 12px;
}
.author-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(237,31,36,0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    color: #fff;
    letter-spacing: 0.5px;
    flex-shrink: 0;
    border: 1.5px solid rgba(255,255,255,0.2);
}
.author-name {
    font-size: 13px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 2px;
    letter-spacing: 0.1px;
}
.author-title {
    font-size: 11px;
    color: rgba(255,255,255,0.5);
    letter-spacing: 0.3px;
}

/* ══════════════════════════
   RIGHT PANEL
══════════════════════════ */
.right-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 56px 72px 44px;
    overflow-y: auto;
    background: #fafafa;
    position: relative;
}

/* Subtle top border accent */
.right-panel::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #ED1F24 0%, #ff6b6b 50%, transparent 100%);
}

.form-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 18px;
    max-width: 400px;
    width: 100%;
    margin: 0 auto;
}

/* ─── Badge ─── */
.form-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff0f0;
    border: 1px solid #fac8c8;
    color: #c0392b;
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    padding: 5px 10px;
    border-radius: 6px;
    width: fit-content;
    margin-bottom: 4px;
}
.form-badge::before {
    content: '';
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #ED1F24;
    flex-shrink: 0;
    animation: pulse-dot 2s ease-in-out infinite;
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.75); }
}

/* ─── Stagger animations ─── */
.form-header--animate {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.6s ease 0.22s, transform 0.6s cubic-bezier(0.22,1,0.36,1) 0.22s;
}
.card--visible .form-header--animate { opacity: 1; transform: translateY(0); }

.field--animate {
    opacity: 0;
    transform: translateY(10px);
    transition: opacity 0.5s ease, transform 0.5s cubic-bezier(0.22,1,0.36,1);
}
.card--visible .field--animate { opacity: 1; transform: translateY(0); }
.field--delay-1 { transition-delay: 0.32s; }
.field--delay-2 { transition-delay: 0.42s; }
.field--delay-3 { transition-delay: 0.52s; }
.field--delay-4 { transition-delay: 0.60s; }
.field--delay-5 { transition-delay: 0.68s; }

.form-header { margin-bottom: 6px; }
.form-header h1 {
    font-size: 26px;
    font-weight: 600;
    color: #0f0f0f;
    letter-spacing: -0.7px;
    margin-bottom: 8px;
    line-height: 1.2;
}
.form-header p {
    font-size: 13.5px;
    color: #9a9a9a;
    line-height: 1.6;
    font-weight: 400;
}

/* ─── Divider ─── */
.form-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 2px 0;
}
.form-divider::before,
.form-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #ebebeb;
}
.form-divider span {
    font-size: 11px;
    color: #bbb;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

/* ─── Alert ─── */
.alert-error {
    background: #fff8f8;
    border: 1px solid #f5d0d0;
    color: #b91c1c;
    font-size: 12px;
    font-weight: 500;
    padding: 11px 14px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: 0.1px;
}
.alert-icon { width: 14px; height: 14px; flex-shrink: 0; }
.alert-slide-enter-active { transition: all 0.3s cubic-bezier(0.22,1,0.36,1); }
.alert-slide-leave-active { transition: all 0.2s ease-in; }
.alert-slide-enter-from { opacity: 0; transform: translateY(-6px) scale(0.98); }
.alert-slide-leave-to   { opacity: 0; transform: translateY(-3px); }

@keyframes shake {
    0%,100% { transform: translateX(0); }
    15%     { transform: translateX(-6px); }
    30%     { transform: translateX(5px); }
    45%     { transform: translateX(-4px); }
    60%     { transform: translateX(3px); }
    75%     { transform: translateX(-2px); }
    90%     { transform: translateX(1px); }
}
.shake { animation: shake 0.5s ease; }

/* ─── Fields ─── */
.login-field {
    position: relative;
    background: transparent;
}

.login-field > input,
.login-field .password-wrap input {
    width: 100%;
    border: 1.5px solid #e4e4e4 !important;
    border-radius: 11px !important;
    padding: 22px 16px 8px !important;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    color: #111;
    background: #fff !important;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04) !important;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.login-field > label,
.login-field .password-wrap > label {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 13.5px;
    color: #b0b0b0;
    pointer-events: none;
    transition: all 0.2s cubic-bezier(0.22,1,0.36,1);
    font-family: 'Inter', sans-serif;
}

.login-field:focus-within > label,
.login-field:focus-within .password-wrap > label {
    top: 10px;
    transform: translateY(0);
    font-size: 10.5px;
    color: #ED1F24;
    font-weight: 500;
    letter-spacing: 0.3px;
}
.login-field.has-value > label,
.login-field.has-value .password-wrap > label {
    top: 10px;
    transform: translateY(0);
    font-size: 10.5px;
    color: #bbb;
    font-weight: 400;
    letter-spacing: 0.3px;
}

.login-field:focus-within > input,
.login-field:focus-within .password-wrap input {
    border-color: #ED1F24 !important;
    box-shadow: 0 0 0 4px rgba(237,31,36,0.07) !important;
}

.password-wrap { position: relative; }

.login-field input:-webkit-autofill,
.login-field input:-webkit-autofill:hover,
.login-field input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px #ffffff inset;
    -webkit-text-fill-color: #111;
    transition: background-color 5000s ease-in-out 0s;
}

/* ─── Eye button ─── */
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
.eye-btn:hover { color: #ED1F24; transform: translateY(-50%) scale(1.12); }
.eye-btn svg  { width: 16px; height: 16px; position: absolute; top:0; left:0; }

.icon-flip-enter-active { transition: opacity 0.15s, transform 0.18s; }
.icon-flip-leave-active { transition: opacity 0.1s, transform 0.12s; position:absolute; }
.icon-flip-enter-from   { opacity: 0; transform: rotateY(90deg); }
.icon-flip-leave-to     { opacity: 0; transform: rotateY(-90deg); }

/* ─── Remember toggle ─── */
.remember-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2px;
}
.remember-label {
    font-size: 12.5px;
    color: #888;
    font-weight: 400;
}
.toggle-btn {
    width: 40px;
    height: 22px;
    border-radius: 100px;
    background: #e4e4e4;
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
    top: 3px; left: 3px;
    width: 16px; height: 16px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.2);
    transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1);
}
.toggle-btn.active .toggle-thumb { transform: translateX(18px); }

/* ─── Sign In Button ─── */
.btn-signin {
    width: 100%;
    background: #ED1F24;
    color: #fff;
    border: none;
    border-radius: 11px;
    padding: 15px 20px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    margin-top: 6px;
    letter-spacing: 0.2px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(237,31,36,0.25);
}
.btn-signin:hover:not(:disabled) {
    background: #d01518;
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(237,31,36,0.35);
}
.btn-signin:active:not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(237,31,36,0.2);
}
.btn-signin:disabled { opacity: 0.55; cursor: not-allowed; box-shadow: none; }

.btn-arrow {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    transition: transform 0.2s;
}
.btn-signin:hover:not(:disabled) .btn-arrow { transform: translateX(3px); }

.btn-ripple-container {
    position: absolute;
    inset: 0;
    overflow: hidden;
    border-radius: inherit;
    pointer-events: none;
}
@keyframes ripple-expand { to { transform: scale(1); opacity: 0; } }

.spinner {
    width: 13px; height: 13px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.65s linear infinite;
    flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Footer ─── */
.footer-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 32px;
    flex-shrink: 0;
    max-width: 400px;
    width: 100%;
    margin-left: auto;
    margin-right: auto;
}
.footer-dots { display: flex; align-items: center; gap: 5px; }
.footer-dot {
    width: 5px; height: 5px;
    border-radius: 50%;
    background: #ddd;
}
.footer-dot--active { background: #ED1F24; width: 16px; border-radius: 3px; }
.footer-text {
    font-size: 10.5px;
    color: #ccc;
    letter-spacing: 0.2px;
}

@media (max-width: 768px) {
    .left-panel { display: none; }
    .right-panel { padding: 44px 28px; background: #fafafa; }
    .right-panel::before { display: block; }
    .form-body { max-width: 100%; }
    .footer-row { max-width: 100%; }
}
</style>