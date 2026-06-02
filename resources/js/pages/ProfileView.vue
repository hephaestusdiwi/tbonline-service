<template>
    <AdminLayout title="Profil Saya">

        <div class="pr-page">

            <!-- ───────────────────────── HERO HEADER ───────────────────────── -->
            <div class="pr-hero">
                <div class="pr-hero__circle pr-hero__circle--1"></div>
                <div class="pr-hero__circle pr-hero__circle--2"></div>
                <div class="pr-hero__circle pr-hero__circle--3"></div>

                <div class="pr-hero__inner">
                    <div>
                        <p class="pr-hero__eyebrow">Pengaturan Akun</p>
                        <h1 class="pr-hero__title">Profil Saya</h1>
                        <p class="pr-hero__subtitle">Kelola informasi akun dan keamanan Anda</p>
                    </div>
                    <div class="pr-hero__right">
                        <div v-if="profile" class="pr-hero__status" :class="profile.is_active ? 'pr-hero__status--active' : 'pr-hero__status--inactive'">
                            <span class="pr-hero__status-dot" :class="profile.is_active ? 'pr-hero__status-dot--active' : ''"></span>
                            {{ profile.is_active ? 'Akun Aktif' : 'Akun Nonaktif' }}
                        </div>
                    </div>
                </div>

                <!-- Stats strip -->
                <div class="pr-hero__strip" v-if="profile">
                    <div class="pr-hero__stat">
                        <p class="pr-hero__stat-label">Nama</p>
                        <p class="pr-hero__stat-value">{{ profile.name }}</p>
                    </div>
                    <div class="pr-hero__stat">
                        <p class="pr-hero__stat-label">Role</p>
                        <p class="pr-hero__stat-value pr-hero__stat-value--amber">{{ (profile.roles || []).join(', ') }}</p>
                    </div>
                    <div class="pr-hero__stat">
                        <p class="pr-hero__stat-label">Bergabung</p>
                        <p class="pr-hero__stat-value pr-hero__stat-value--muted">{{ joinedDate }}</p>
                    </div>
                </div>
            </div>

            <!-- Loading skeleton -->
            <div v-if="loading" class="pr-skeleton-grid">
                <div class="pr-skeleton pr-skeleton--tall"></div>
                <div class="pr-skeleton pr-skeleton--tall"></div>
            </div>

            <!-- Content -->
            <div v-else-if="profile" class="pr-grid">

                <!-- ── KOLOM KIRI: Avatar + Status ── -->
                <div class="pr-left">

                    <!-- Avatar Card -->
                    <div class="pr-card pr-avatar-card">
                        <!-- Gradient bar -->
                        <div class="pr-avatar-card__bar"></div>

                        <div class="pr-avatar-card__body">
                            <!-- Avatar -->
                            <div class="pr-avatar-wrap">
                                <img
                                    v-if="profile.avatar_url"
                                    :src="profile.avatar_url"
                                    :alt="profile.name"
                                    class="pr-avatar-img"
                                />
                                <div v-else class="pr-avatar-fallback">
                                    <span>{{ initials }}</span>
                                </div>

                                <!-- Upload button -->
                                <label class="pr-avatar-upload" title="Ganti foto">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <input type="file" accept="image/jpeg,image/png,image/webp" class="pr-avatar-input" @change="onAvatarSelected"/>
                                </label>
                            </div>

                            <!-- Name & roles -->
                            <p class="pr-avatar-name">{{ profile.name }}</p>
                            <div class="pr-role-badges">
                                <span v-for="role in profile.roles" :key="role" class="pr-role-badge">{{ role }}</span>
                            </div>

                            <div class="pr-divider"></div>

                            <!-- Meta -->
                            <div class="pr-meta">
                                <div class="pr-meta__item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span>{{ profile.email }}</span>
                                </div>
                                <div class="pr-meta__item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Bergabung {{ joinedDate }}</span>
                                </div>
                            </div>

                            <!-- Delete avatar -->
                            <button
                                v-if="profile.avatar_url"
                                @click="confirmDeleteAvatar = true"
                                class="pr-delete-avatar-btn"
                            >
                                Hapus Foto Profil
                            </button>
                        </div>
                    </div>

                    <!-- Status Card -->
                    <div class="pr-card pr-status-card">
                        <div class="pr-status-card__header">
                            <div class="pr-status-card__icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <p class="pr-status-card__label">Status Akun</p>
                        </div>
                        <div class="pr-status-badge" :class="profile.is_active ? 'pr-status-badge--active' : 'pr-status-badge--inactive'">
                            <span class="pr-status-badge__dot" :class="profile.is_active ? 'pr-status-badge__dot--pulse' : ''"></span>
                            <div>
                                <p class="pr-status-badge__title">{{ profile.is_active ? 'Aktif' : 'Nonaktif' }}</p>
                                <p class="pr-status-badge__desc">
                                    {{ profile.is_active ? 'Akun Anda dapat mengakses semua fitur.' : 'Akun Anda sedang dinonaktifkan. Hubungi admin.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ── KOLOM KANAN: Tab Card ── -->
                <div class="pr-right">
                    <div class="pr-card pr-tab-card">

                        <!-- Tab header -->
                        <div class="pr-tabs">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                :class="['pr-tab', activeTab === tab.id ? 'pr-tab--active' : '']"
                            >
                                <span v-html="tab.icon"></span>
                                {{ tab.label }}
                            </button>
                        </div>

                        <!-- ── TAB: Info Profil ── -->
                        <div v-if="activeTab === 'info'" class="pr-tab-body">

                            <div v-if="error" class="pr-alert pr-alert--red">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                {{ error }}
                            </div>
                            <div v-if="success" class="pr-alert pr-alert--green">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                {{ success }}
                            </div>

                            <div class="pr-fields">
                                <!-- Nama -->
                                <div class="pr-field">
                                    <label class="pr-label">Nama Lengkap <span class="pr-req">*</span></label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Nama lengkap Anda"
                                        class="pr-input"
                                        :class="fieldErrors.name ? 'pr-input--error' : ''"
                                    />
                                    <p v-if="fieldErrors.name" class="pr-field-error">{{ fieldErrors.name[0] }}</p>
                                </div>

                                <!-- Email (read-only) -->
                                <div class="pr-field">
                                    <label class="pr-label">
                                        Email
                                        <span class="pr-readonly-tag">Read-only</span>
                                    </label>
                                    <div class="pr-input-readonly">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span>{{ profile.email }}</span>
                                    </div>
                                </div>

                                <!-- Role (read-only) -->
                                <div class="pr-field">
                                    <label class="pr-label">
                                        Role
                                        <span class="pr-readonly-tag">Read-only</span>
                                    </label>
                                    <div class="pr-role-readonly">
                                        <span v-for="role in profile.roles" :key="role" class="pr-role-badge">{{ role }}</span>
                                    </div>
                                    <p class="pr-field-hint">Role hanya dapat diubah oleh Administrator sistem.</p>
                                </div>
                            </div>

                            <!-- Footer actions -->
                            <div class="pr-tab-footer">
                                <p class="pr-tab-footer__info">Bergabung sejak <span>{{ joinedDate }}</span></p>
                                <div class="pr-tab-footer__actions">
                                    <button @click="resetForm" class="pr-btn pr-btn--ghost">Reset</button>
                                    <button @click="handleUpdateProfile" :disabled="saving" class="pr-btn pr-btn--primary">
                                        <svg v-if="saving" class="pr-spin" width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56" stroke="currentColor" stroke-width="2"/></svg>
                                        <svg v-else width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-width="2"/></svg>
                                        {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ── TAB: Ganti Password ── -->
                        <div v-if="activeTab === 'password'" class="pr-tab-body">

                            <div v-if="pwError" class="pr-alert pr-alert--red">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                {{ pwError }}
                            </div>
                            <div v-if="pwSuccess" class="pr-alert pr-alert--green">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-width="2"/></svg>
                                {{ pwSuccess }}
                            </div>

                            <!-- Info notice -->
                            <div class="pr-info-box">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z"/></svg>
                                <p>Password baru minimal <strong>8 karakter</strong>. Gunakan kombinasi huruf, angka, dan simbol untuk keamanan lebih baik.</p>
                            </div>

                            <div class="pr-fields">
                                <!-- Current password -->
                                <div class="pr-field">
                                    <label class="pr-label">Password Saat Ini</label>
                                    <div class="pr-input-wrap">
                                        <input
                                            v-model="pwForm.current_password"
                                            :type="showPw.current ? 'text' : 'password'"
                                            placeholder="••••••••"
                                            class="pr-input pr-input--pw"
                                        />
                                        <button type="button" @click="showPw.current = !showPw.current" class="pr-pw-toggle">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path v-if="!showPw.current" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle v-if="!showPw.current" cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>
                                                <path v-if="showPw.current" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p v-if="pwFieldErrors.current_password" class="pr-field-error">{{ pwFieldErrors.current_password[0] }}</p>
                                </div>

                                <!-- New passwords -->
                                <div class="pr-field-grid">
                                    <div class="pr-field">
                                        <label class="pr-label">Password Baru</label>
                                        <div class="pr-input-wrap">
                                            <input
                                                v-model="pwForm.new_password"
                                                :type="showPw.new ? 'text' : 'password'"
                                                placeholder="••••••••"
                                                class="pr-input pr-input--pw"
                                            />
                                            <button type="button" @click="showPw.new = !showPw.new" class="pr-pw-toggle">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>
                                            </button>
                                        </div>
                                        <!-- Strength bar -->
                                        <div class="pr-strength-bar">
                                            <div
                                                v-for="i in 4" :key="i"
                                                class="pr-strength-segment"
                                                :class="passwordStrength >= i ? strengthColor : 'pr-strength-segment--empty'"
                                            ></div>
                                        </div>
                                        <p class="pr-strength-label" :class="strengthTextColor">{{ strengthLabel }}</p>
                                        <p v-if="pwFieldErrors.new_password" class="pr-field-error">{{ pwFieldErrors.new_password[0] }}</p>
                                    </div>

                                    <div class="pr-field">
                                        <label class="pr-label">Konfirmasi Password</label>
                                        <div class="pr-input-wrap">
                                            <input
                                                v-model="pwForm.new_password_confirmation"
                                                :type="showPw.confirm ? 'text' : 'password'"
                                                placeholder="••••••••"
                                                class="pr-input pr-input--pw"
                                                :class="pwForm.new_password_confirmation && pwForm.new_password !== pwForm.new_password_confirmation ? 'pr-input--error' : ''"
                                            />
                                            <button type="button" @click="showPw.confirm = !showPw.confirm" class="pr-pw-toggle">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>
                                            </button>
                                        </div>
                                        <p v-if="pwForm.new_password_confirmation && pwForm.new_password !== pwForm.new_password_confirmation" class="pr-field-error">Password tidak cocok.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="pr-tab-footer pr-tab-footer--right">
                                <button @click="resetPwForm" class="pr-btn pr-btn--ghost">Batal</button>
                                <button
                                    @click="handleChangePassword"
                                    :disabled="pwSaving || pwForm.new_password !== pwForm.new_password_confirmation"
                                    class="pr-btn pr-btn--primary"
                                >
                                    <svg v-if="pwSaving" class="pr-spin" width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56" stroke="currentColor" stroke-width="2"/></svg>
                                    <svg v-else width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    {{ pwSaving ? 'Menyimpan...' : 'Perbarui Password' }}
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <!-- ===== MODAL HAPUS AVATAR ===== -->
        <Teleport to="body">
            <Transition name="pr-modal">
                <div v-if="confirmDeleteAvatar" class="pr-modal-backdrop" @click.self="confirmDeleteAvatar = false">
                    <div class="pr-modal">
                        <div class="pr-modal__body">
                            <div class="pr-modal__icon-wrap">
                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                            </div>
                            <h3 class="pr-modal__title">Hapus Foto Profil?</h3>
                            <p class="pr-modal__desc">Foto profil Anda akan dihapus dan digantikan dengan inisial nama.</p>
                        </div>
                        <div class="pr-modal__footer">
                            <button @click="confirmDeleteAvatar = false" class="pr-btn pr-btn--ghost">Batal</button>
                            <button @click="handleDeleteAvatar" :disabled="avatarDeleting" class="pr-btn pr-btn--danger">
                                <svg v-if="avatarDeleting" class="pr-spin" width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56" stroke="currentColor" stroke-width="2"/></svg>
                                {{ avatarDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </AdminLayout>
</template>

<script>
import AdminLayout from '../components/admin/AdminLayout.vue'
import { useProfile } from '../composables/useProfile.js'
import { getUser, saveAuth, getToken } from '../auth.js'

export default {
    name: 'ProfileView',
    components: { AdminLayout },

    setup() {
        const {
            profile, loading, saving, error, success,
            form,
            fetchProfile, updateProfile,
            uploadAvatar, deleteAvatar,
            changePassword,
        } = useProfile()

        return {
            profile, loading, saving, error, success,
            form,
            fetchProfile, updateProfile,
            uploadAvatar, deleteAvatar,
            changePassword,
        }
    },

    data() {
        return {
            activeTab: 'info',
            fieldErrors: {},

            pwForm: {
                current_password: '',
                new_password: '',
                new_password_confirmation: '',
            },
            pwSaving: false,
            pwError: '',
            pwSuccess: '',
            pwFieldErrors: {},
            showPw: { current: false, new: false, confirm: false },

            confirmDeleteAvatar: false,
            avatarDeleting: false,

            tabs: [
                {
                    id: 'info',
                    label: 'Info Profil',
                    icon: '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                },
                {
                    id: 'password',
                    label: 'Ganti Password',
                    icon: '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
                },
            ],
        }
    },

    computed: {
        initials() {
            if (!this.profile?.name) return '?'
            return this.profile.name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase()
        },
        joinedDate() {
            if (!this.profile?.created_at) return '—'
            return new Date(this.profile.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
        },
        passwordStrength() {
            const p = this.pwForm.new_password
            if (!p) return 0
            let score = 0
            if (p.length >= 8)  score++
            if (p.length >= 12) score++
            if (/[A-Z]/.test(p) && /[a-z]/.test(p)) score++
            if (/[0-9]/.test(p) && /[^a-zA-Z0-9]/.test(p)) score++
            return score
        },
        strengthColor() {
            return ['pr-strength-segment--red', 'pr-strength-segment--orange', 'pr-strength-segment--yellow', 'pr-strength-segment--green'][this.passwordStrength - 1] ?? 'pr-strength-segment--red'
        },
        strengthTextColor() {
            return ['pr-strength-text--red', 'pr-strength-text--orange', 'pr-strength-text--yellow', 'pr-strength-text--green'][this.passwordStrength - 1] ?? 'pr-strength-text--gray'
        },
        strengthLabel() {
            return ['', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'][this.passwordStrength] ?? ''
        },
    },

    mounted() {
        this.fetchProfile()
    },

    methods: {
        resetForm() {
            if (!this.profile) return
            this.form.name = this.profile.name ?? ''
            this.error = ''
            this.success = ''
            this.fieldErrors = {}
        },

        async handleUpdateProfile() {
            this.fieldErrors = {}
            try {
                await this.updateProfile()
            } catch (e) {
                if (e.response?.status === 422) this.fieldErrors = e.response.data.errors ?? {}
            }
        },

        async onAvatarSelected(e) {
            const file = e.target.files[0]
            if (!file) return
            if (file.size > 2 * 1024 * 1024) { this.error = 'Ukuran foto melebihi 2MB.'; return }
            try {
                const result = await this.uploadAvatar(file)
                this.success = 'Foto profil berhasil diperbarui.'
                const isRemembered = !!localStorage.getItem('token')
                const user = { ...getUser(), avatar_url: result.avatar_url }
                saveAuth({ token: getToken(), user, rememberMe: isRemembered })
                window.dispatchEvent(new Event('user-updated'))
            } catch (err) {
                this.error = err.response?.data?.message ?? 'Gagal mengunggah foto.'
            }
        },

        async handleDeleteAvatar() {
            this.avatarDeleting = true
            try {
                await this.deleteAvatar()
                this.confirmDeleteAvatar = false
                this.success = 'Foto profil berhasil dihapus.'
            } catch (err) {
                this.error = err.response?.data?.message ?? 'Gagal menghapus foto.'
                this.confirmDeleteAvatar = false
            } finally {
                this.avatarDeleting = false
            }
        },

        resetPwForm() {
            this.pwForm = { current_password: '', new_password: '', new_password_confirmation: '' }
            this.pwError = ''
            this.pwSuccess = ''
            this.pwFieldErrors = {}
        },

        async handleChangePassword() {
            this.pwSaving = true
            this.pwError = ''
            this.pwSuccess = ''
            this.pwFieldErrors = {}
            try {
                const data = await this.changePassword(this.pwForm)
                this.pwSuccess = data.message
                this.resetPwForm()
            } catch (e) {
                if (e.response?.status === 422) {
                    this.pwFieldErrors = e.response.data.errors ?? {}
                    this.pwError = e.response.data.message ?? 'Validasi gagal.'
                } else {
                    this.pwError = e.response?.data?.message ?? 'Gagal memperbarui password.'
                }
            } finally {
                this.pwSaving = false
            }
        },
    },
}
</script>

<style scoped>
.pr-page { display: flex; flex-direction: column; gap: 20px; }

/* ═══════════════════════════════════════════════════════
   HERO HEADER
═══════════════════════════════════════════════════════ */
.pr-hero {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);
}
.pr-hero__circle { position: absolute; border-radius: 50%; background: white; pointer-events: none; }
.pr-hero__circle--1 { width: 192px; height: 192px; top: -32px; right: -32px; opacity: .10; }
.pr-hero__circle--2 { width: 256px; height: 256px; bottom: -40px; right: -96px; opacity: .05; }
.pr-hero__circle--3 { width: 80px; height: 80px; top: 16px; right: 128px; opacity: .10; }

.pr-hero__inner {
    position: relative;
    display: flex; flex-wrap: wrap; align-items: center;
    justify-content: space-between; gap: 16px;
    padding: 24px 28px;
}
.pr-hero__eyebrow { color: rgba(255,255,255,.65); font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; margin: 0 0 4px; }
.pr-hero__title { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 2px; letter-spacing: -.3px; }
.pr-hero__subtitle { font-size: 12px; color: rgba(255,255,255,.65); margin: 0; }

.pr-hero__right { display: flex; align-items: center; }
.pr-hero__status {
    display: flex; align-items: center; gap: 8px;
    font-size: 12px; font-weight: 600;
    padding: 6px 14px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,.2);
}
.pr-hero__status--active   { background: rgba(52,211,153,.15); color: #6ee7b7; }
.pr-hero__status--inactive { background: rgba(255,255,255,.10); color: rgba(255,255,255,.7); }
.pr-hero__status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.pr-hero__status-dot--active { animation: pr-pulse 2s ease-in-out infinite; }
@keyframes pr-pulse { 0%,100% { opacity: 1; } 50% { opacity: .4; } }

.pr-hero__strip {
    position: relative;
    border-top: 1px solid rgba(255,255,255,.12);
    padding: 14px 28px;
    display: flex; flex-wrap: wrap;
}
.pr-hero__stat { display: flex; flex-direction: column; padding: 0 20px; }
.pr-hero__stat + .pr-hero__stat { border-left: 1px solid rgba(255,255,255,.15); }
.pr-hero__stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: rgba(255,255,255,.6); margin: 0 0 2px; }
.pr-hero__stat-value { font-size: 15px; font-weight: 700; color: #fff; margin: 0; line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }
.pr-hero__stat-value--amber { color: #fbbf24; }
.pr-hero__stat-value--muted { color: rgba(255,255,255,.7); font-size: 13px; }

/* ═══════════════════════════════════════════════════════
   SKELETON
═══════════════════════════════════════════════════════ */
.pr-skeleton-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
.pr-skeleton { background: #f3f4f6; border-radius: 12px; animation: pr-shimmer 1.5s ease-in-out infinite; }
.pr-skeleton--tall { height: 320px; }
@keyframes pr-shimmer { 0%,100% { opacity: 1; } 50% { opacity: .5; } }

/* ═══════════════════════════════════════════════════════
   LAYOUT GRID
═══════════════════════════════════════════════════════ */
.pr-grid { display: grid; grid-template-columns: 300px 1fr; gap: 20px; align-items: start; }
.pr-left  { display: flex; flex-direction: column; gap: 16px; }
.pr-right { display: flex; flex-direction: column; }

/* ═══════════════════════════════════════════════════════
   CARD BASE
═══════════════════════════════════════════════════════ */
.pr-card {
    background: #fff;
    border: 1px solid rgba(229,231,235,.8);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}

/* ═══════════════════════════════════════════════════════
   AVATAR CARD
═══════════════════════════════════════════════════════ */
.pr-avatar-card { overflow: hidden; }
.pr-avatar-card__bar {
    height: 72px;
    background: linear-gradient(135deg, rgba(237,31,36,.15) 0%, rgba(237,31,36,.06) 100%);
    border-bottom: 1px solid rgba(237,31,36,.08);
    position: relative;
}
.pr-avatar-card__bar::after {
    content: '';
    position: absolute; inset: 0;
    background-image: repeating-conic-gradient(rgba(237,31,36,.05) 0% 25%, transparent 0% 50%);
    background-size: 20px 20px;
}
.pr-avatar-card__body { padding: 0 20px 20px; }

.pr-avatar-wrap { position: relative; width: 80px; height: 80px; margin: -40px 0 14px; }
.pr-avatar-img {
    width: 80px; height: 80px; border-radius: 16px;
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
}
.pr-avatar-fallback {
    width: 80px; height: 80px; border-radius: 16px;
    border: 4px solid #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    background: linear-gradient(135deg, rgba(237,31,36,.15) 0%, rgba(237,31,36,.08) 100%);
    display: flex; align-items: center; justify-content: center;
}
.pr-avatar-fallback span { font-size: 24px; font-weight: 800; color: #ED1F24; }
.pr-avatar-upload {
    position: absolute; bottom: -6px; right: -6px;
    width: 28px; height: 28px; border-radius: 8px;
    background: #ED1F24; color: white;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(237,31,36,.4);
    transition: background .15s;
}
.pr-avatar-upload:hover { background: #C81A1E; }
.pr-avatar-input { display: none; }

.pr-avatar-name { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 8px; }

.pr-role-badges { display: flex; flex-wrap: wrap; gap: 6px; }
.pr-role-badge {
    font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
    padding: 3px 9px; border-radius: 20px;
    background: rgba(237,31,36,.08);
    border: 1px solid rgba(237,31,36,.18);
    color: #ED1F24;
}

.pr-divider { border: none; border-top: 1px solid #f3f4f6; margin: 14px 0; }

.pr-meta { display: flex; flex-direction: column; gap: 10px; }
.pr-meta__item { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #6b7280; }
.pr-meta__item svg { color: #9ca3af; flex-shrink: 0; }
.pr-meta__item span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.pr-delete-avatar-btn {
    margin-top: 14px; width: 100%;
    font-size: 12px; font-weight: 600;
    color: #dc2626; background: transparent;
    border: 1px solid rgba(220,38,38,.2);
    border-radius: 8px; padding: 7px 0;
    cursor: pointer; transition: all .15s;
}
.pr-delete-avatar-btn:hover { background: rgba(220,38,38,.05); border-color: rgba(220,38,38,.4); }

/* ═══════════════════════════════════════════════════════
   STATUS CARD
═══════════════════════════════════════════════════════ */
.pr-status-card { padding: 16px; }
.pr-status-card__header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.pr-status-card__icon {
    width: 28px; height: 28px; border-radius: 8px;
    background: #f9fafb; border: 1px solid #e5e7eb;
    display: flex; align-items: center; justify-content: center; color: #6b7280;
}
.pr-status-card__label { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .06em; }

.pr-status-badge {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px; border-radius: 10px;
    border: 1px solid transparent;
}
.pr-status-badge--active   { background: #f0fdf4; border-color: #bbf7d0; }
.pr-status-badge--inactive { background: #fef2f2; border-color: #fecaca; }
.pr-status-badge__dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.pr-status-badge--active   .pr-status-badge__dot { background: #22c55e; }
.pr-status-badge--inactive .pr-status-badge__dot { background: #ef4444; }
.pr-status-badge__dot--pulse { animation: pr-pulse 2s ease-in-out infinite; }
.pr-status-badge__title { font-size: 13px; font-weight: 700; margin: 0 0 2px; }
.pr-status-badge--active   .pr-status-badge__title { color: #166534; }
.pr-status-badge--inactive .pr-status-badge__title { color: #991b1b; }
.pr-status-badge__desc { font-size: 11px; color: #6b7280; margin: 0; line-height: 1.4; }

/* ═══════════════════════════════════════════════════════
   TAB CARD
═══════════════════════════════════════════════════════ */
.pr-tab-card { overflow: hidden; }

.pr-tabs {
    display: flex; gap: 2px;
    padding: 14px 20px 0;
    border-bottom: 1px solid #f3f4f6;
}
.pr-tab {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 8px 14px; font-size: 13px; font-weight: 600;
    border-radius: 8px 8px 0 0;
    border: none; border-bottom: 2px solid transparent;
    cursor: pointer; background: transparent;
    color: #9ca3af; margin-bottom: -1px;
    transition: all .15s;
}
.pr-tab:hover { color: #374151; background: #f9fafb; }
.pr-tab--active { color: #ED1F24; border-bottom-color: #ED1F24; background: rgba(237,31,36,.04); }
.pr-tab--active svg { color: #ED1F24; }

.pr-tab-body { padding: 24px; display: flex; flex-direction: column; gap: 20px; }

/* ═══════════════════════════════════════════════════════
   ALERTS
═══════════════════════════════════════════════════════ */
.pr-alert {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 14px; border-radius: 10px;
    font-size: 13px; border: 1px solid transparent;
}
.pr-alert--red   { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
.pr-alert--green { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }

/* ═══════════════════════════════════════════════════════
   FORM FIELDS
═══════════════════════════════════════════════════════ */
.pr-fields { display: flex; flex-direction: column; gap: 18px; }
.pr-field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.pr-field { display: flex; flex-direction: column; gap: 6px; }

.pr-label {
    display: flex; align-items: center; gap: 8px;
    font-size: 11px; font-weight: 700;
    color: #374151; text-transform: uppercase; letter-spacing: .06em;
}
.pr-req { color: #ef4444; }

.pr-readonly-tag {
    font-size: 10px; font-weight: 500; text-transform: none; letter-spacing: 0;
    background: #f3f4f6; border: 1px solid #e5e7eb;
    color: #9ca3af; padding: 1px 7px; border-radius: 20px;
}

.pr-input {
    padding: 9px 13px;
    border: 1px solid #e5e7eb; border-radius: 8px;
    font-size: 13px; color: #111827;
    outline: none; transition: border .15s;
    font-family: inherit; width: 100%; box-sizing: border-box;
}
.pr-input:focus { border-color: #ED1F24; box-shadow: 0 0 0 3px rgba(237,31,36,.08); }
.pr-input--error { border-color: #fca5a5; }
.pr-input--pw { padding-right: 40px; }

.pr-input-readonly {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 13px;
    background: #f9fafb; border: 1px solid #e5e7eb;
    border-radius: 8px; font-size: 13px; color: #9ca3af;
}
.pr-input-readonly svg { color: #d1d5db; flex-shrink: 0; }

.pr-role-readonly { display: flex; flex-wrap: wrap; gap: 8px; padding: 8px 0; }

.pr-input-wrap { position: relative; }
.pr-pw-toggle {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; color: #9ca3af;
    display: flex; align-items: center; transition: color .15s;
}
.pr-pw-toggle:hover { color: #6b7280; }

.pr-field-error { font-size: 11px; color: #dc2626; margin: 0; }
.pr-field-hint  { font-size: 11px; color: #9ca3af; margin: 0; }

/* Strength bar */
.pr-strength-bar { display: flex; gap: 4px; margin-top: 6px; }
.pr-strength-segment { height: 4px; flex: 1; border-radius: 4px; transition: background .3s; }
.pr-strength-segment--empty   { background: #e5e7eb; }
.pr-strength-segment--red     { background: #ef4444; }
.pr-strength-segment--orange  { background: #f97316; }
.pr-strength-segment--yellow  { background: #eab308; }
.pr-strength-segment--green   { background: #22c55e; }
.pr-strength-label { font-size: 11px; margin: 2px 0 0; }
.pr-strength-text--gray   { color: #9ca3af; }
.pr-strength-text--red    { color: #ef4444; }
.pr-strength-text--orange { color: #f97316; }
.pr-strength-text--yellow { color: #eab308; }
.pr-strength-text--green  { color: #22c55e; }

/* Info box */
.pr-info-box {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 12px 14px;
    background: #eff6ff; border: 1px solid #bfdbfe;
    border-radius: 10px; font-size: 13px; color: #1e40af; line-height: 1.5;
}
.pr-info-box svg { flex-shrink: 0; margin-top: 1px; color: #3b82f6; }
.pr-info-box p { margin: 0; }

/* ═══════════════════════════════════════════════════════
   TAB FOOTER
═══════════════════════════════════════════════════════ */
.pr-tab-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding-top: 18px; border-top: 1px solid #f3f4f6; gap: 12px;
}
.pr-tab-footer--right { justify-content: flex-end; }
.pr-tab-footer__info { font-size: 12px; color: #9ca3af; margin: 0; }
.pr-tab-footer__info span { color: #6b7280; font-weight: 500; }
.pr-tab-footer__actions { display: flex; gap: 8px; }

/* ═══════════════════════════════════════════════════════
   BUTTONS
═══════════════════════════════════════════════════════ */
.pr-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .15s; }
.pr-btn--primary       { background: #ED1F24; color: #fff; }
.pr-btn--primary:hover { background: #C81A1E; }
.pr-btn--primary:disabled { opacity: .5; cursor: not-allowed; }
.pr-btn--danger        { background: #dc2626; color: #fff; }
.pr-btn--danger:hover  { background: #b91c1c; }
.pr-btn--danger:disabled { opacity: .5; cursor: not-allowed; }
.pr-btn--ghost         { background: transparent; color: #6b7280; border: 1px solid #e5e7eb; }
.pr-btn--ghost:hover   { background: #f9fafb; }

/* ═══════════════════════════════════════════════════════
   MODAL
═══════════════════════════════════════════════════════ */
.pr-modal-backdrop {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45); backdrop-filter: blur(3px);
    z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;
}
.pr-modal {
    background: #fff; border-radius: 16px; width: 100%; max-width: 400px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    border: 1px solid rgba(229,231,235,.5);
    overflow: hidden;
}
.pr-modal__body { padding: 24px 24px 20px; }
.pr-modal__icon-wrap {
    width: 44px; height: 44px; border-radius: 12px;
    background: #fef2f2; border: 1px solid #fecaca;
    display: flex; align-items: center; justify-content: center;
    color: #dc2626; margin-bottom: 14px;
}
.pr-modal__title { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 6px; }
.pr-modal__desc  { font-size: 13px; color: #6b7280; margin: 0; line-height: 1.5; }
.pr-modal__footer {
    display: flex; justify-content: flex-end; gap: 8px;
    padding: 14px 24px;
    border-top: 1px solid #f3f4f6;
    background: #f9fafb;
}

/* ═══════════════════════════════════════════════════════
   UTILS
═══════════════════════════════════════════════════════ */
.pr-spin { animation: pr-spin 1s linear infinite; }
@keyframes pr-spin { to { transform: rotate(360deg); } }

.pr-modal-enter-active, .pr-modal-leave-active { transition: all .2s; }
.pr-modal-enter-from, .pr-modal-leave-to { opacity: 0; transform: scale(.97); }

/* ═══════════════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════════════ */
@media (max-width: 900px) {
    .pr-grid { grid-template-columns: 1fr; }
    .pr-skeleton-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .pr-hero__circle--2 { display: none; }
    .pr-hero__stat:nth-child(n+3) { display: none; }
    .pr-field-grid { grid-template-columns: 1fr; }
    .pr-tab-footer { flex-direction: column; align-items: flex-start; }
    .pr-tab-footer__actions { width: 100%; justify-content: flex-end; }
}
</style>