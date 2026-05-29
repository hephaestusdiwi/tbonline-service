<template>
    <div class="page-bg">
        <div class="card">

            <!-- ── LEFT PANEL ── -->
            <div class="left-panel">
                <!-- Ganti src dengan path foto/background kamu -->
                <img :src="'/storage/vector/tbm.png'" alt="Cover" class="cover-img" />

                <!-- Overlay gradient agar teks terbaca -->
                <div class="cover-overlay"></div>

                <!-- Logo di pojok kiri atas -->
                <div class="left-logo">
                    <!-- Ganti src dengan path logo kamu (versi putih/light) -->
                    <img :src="'/storage/logos/logo_1779259529.webp'" alt="Logo" class="left-logo-img" />
                </div>

                <!-- Testimonial / quote di pojok kiri bawah -->
                <div class="left-bottom">
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
                    <div class="form-header">
                        <h1>TB Workspace</h1>
                        <p>Platform kerja terintegrasi untuk seluruh operasional TB Store dalam ekosistem TB Group.</p>
                    </div>

                    <!-- Error alert -->
                    <div v-if="errorMessage" class="alert-error">
                        {{ errorMessage }}
                    </div>

                    <!-- Email -->
                    <div class="login-field" :class="{ 'has-value': form.email }">
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
                    <div class="login-field" :class="{ 'has-value': form.password }">
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
                                <svg v-if="!showPw" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <div class="forgot-row">
                            <a href="#" class="forgot-link">Forgot password?</a>
                        </div>
                    </div>

                    <!-- Remember me -->
                    <div class="remember-row">
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
                    <button class="btn-signin" @click="handleLogin" :disabled="loading">
                        <span v-if="loading" class="spinner"></span>
                        <span>{{ loading ? 'Signing in...' : 'Log in' }}</span>
                    </button>
                </div>

                <p class="footer-text">TB System · All rights reserved.</p>
            </div>

        </div>
    </div>
</template>

<script>
import axios from '../axios.js'

export default {
    name: 'Login',
    data() {
        return {
            form: { email: '', password: '' },
            errorMessage: '',
            loading: false,
            showPw: false,
            rememberMe: true,
        }
    },
    methods: {
        async handleLogin() {
            this.errorMessage = ''
            this.loading = true
            try {
                const response = await axios.post('/login', this.form)
                const { token, user } = response.data
                localStorage.setItem('token', token)
                localStorage.setItem('user', JSON.stringify(user))
                localStorage.setItem('permissions', JSON.stringify(user.permissions ?? []))
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
                this.$router.push('/admin/dashboard')
            } catch (error) {
                if (error.response?.status === 401) {
                    this.errorMessage = 'Email atau password salah!'
                } else {
                    this.errorMessage = 'Terjadi kesalahan, coba lagi.'
                }
            } finally {
                this.loading = false
            }
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

/* ─── Card — full viewport height, full width ─── */
.card {
    display: flex;
    background: #ffffff;
    width: 100%;
    min-height: 100vh;
    border-radius: 0;
    box-shadow: none;
    overflow: hidden;
}

/* ══════════════════════════
   LEFT PANEL — foto besar
══════════════════════════ */
.left-panel {
    width: 42%;
    flex-shrink: 0;
    position: relative;
    background: #1a1a1a;
    overflow: hidden;
}

/* Foto cover memenuhi seluruh panel */
.cover-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
}

/* Gradient gelap dari bawah agar teks terbaca */
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

/* Logo di kiri atas */
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
    /* Bisa tambah filter: brightness(0) invert(1); kalau logo gelap */
}

/* Quote di kiri bawah */
.left-bottom {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 2;
    padding: 28px 28px 32px;
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
   RIGHT PANEL — form
══════════════════════════ */
.right-panel {
    font-family: "Poppins", sans-serif;
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 64px 80px 48px;
    overflow-y: auto;
}

/* ─── Form body ─── */
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

/* ─── Alert ─── */
.alert-error {
    background: #fff0f0;
    border: 1px solid #fac8c8;
    color: #c0392b;
    font-size: 12px;
    padding: 10px 14px;
    border-radius: 10px;
}

/* ─── Fields — floating label ─── */
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
    transition: border-color 0.15s;
}

/* Floating label — default di tengah */
.login-field > label,
.login-field .password-wrap > label {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    color: #aaa;
    pointer-events: none;
    transition: all 0.15s ease;
}

/* Label naik saat focus */
.login-field:focus-within > label,
.login-field:focus-within .password-wrap > label {
    top: 10px;
    transform: translateY(0);
    font-size: 11px;
    color: #ED1F24;
}

/* Label naik saat ada value (via Vue :class) */
.login-field.has-value > label,
.login-field.has-value .password-wrap > label {
    top: 10px;
    transform: translateY(0);
    font-size: 11px;
    color: #aaa;
}

/* Border merah saat focus */
.login-field:focus-within > input,
.login-field:focus-within .password-wrap input {
    border-color: #ED1F24 !important;
    box-shadow: 0 0 0 3px rgba(237,31,36,0.08) !important;
}

/* Password wrap */
.password-wrap {
    position: relative;
}
.login-field input::placeholder { color: #ccc; }
.login-field input:-webkit-autofill,
.login-field input:-webkit-autofill:hover,
.login-field input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px #ffffff inset;
    -webkit-text-fill-color: #222;
    transition: background-color 5000s ease-in-out 0s;
}

/* Kill browser login-group highlight box */
.login-field input:not(:-webkit-autofill) {
    animation-name: onautofillcancel;
}
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
    transition: color 0.15s;
}
.eye-btn:hover { color: #ED1F24; }
.eye-btn svg { width: 16px; height: 16px; }

.forgot-row { display: flex; justify-content: flex-start; margin-top: 2px; }
.forgot-link {
    font-size: 12px;
    color: #ED1F24;
    text-decoration: none;
    font-weight: 500;
}
.forgot-link:hover { text-decoration: underline; }

/* ─── Remember me toggle ─── */
.remember-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.remember-label {
    font-size: 13px;
    color: #888;
}

.toggle-btn {
    width: 42px;
    height: 24px;
    border-radius: 100px;
    background: #e0e0e0;
    border: none;
    cursor: pointer;
    position: relative;
    transition: background 0.2s;
    padding: 0;
    flex-shrink: 0;
}

.toggle-btn.active {
    background: #ED1F24;
}

.toggle-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.18);
    transition: transform 0.2s;
}

.toggle-btn.active .toggle-thumb {
    transform: translateX(18px);
}

/* ─── Sign In Button ─── */
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
    transition: background 0.15s, transform 0.1s;
    margin-top: 4px;
    letter-spacing: 0.1px;
}
.btn-signin:hover:not(:disabled) {
    background: #c91519;
    transform: translateY(-1px);
}
.btn-signin:active:not(:disabled) { transform: translateY(0); }
.btn-signin:disabled { opacity: 0.6; cursor: not-allowed; }

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

/* ─── Register line ─── */
.register-line {
    font-size: 12.5px;
    color: #bbb;
    text-align: center;
}
.register-link {
    color: #ED1F24;
    font-weight: 600;
    text-decoration: none;
}
.register-link:hover { text-decoration: underline; }

/* ─── Footer ─── */
.footer-text {
    font-size: 10.5px;
    color: #ccc;
    text-align: center;
    margin-top: 24px;
    flex-shrink: 0;
}

/* ─── Responsive ─── */
@media (max-width: 768px) {
    .left-panel { display: none; }
    .right-panel { padding: 48px 32px; }
    .form-body { max-width: 100%; }
}
</style>