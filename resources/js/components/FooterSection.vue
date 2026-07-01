<template>
    <footer class="footer-root">

        <div class="footer-divider"></div>

        <div class="footer-mid">
            <div class="footer-container footer-mid-inner">

                <!-- KIRI: Logo + Deskripsi + Kontak -->
                <div class="footer-brand">
                    <a href="/" class="footer-logo-wrap">
                        <img v-if="logoUrl" :src="logoUrl" :alt="siteName" class="footer-logo-img" />
                        <span v-else class="footer-logo-text">{{ siteName || 'Brand' }}</span>
                    </a>

                    <p v-if="siteDescription" class="brand-description">{{ siteDescription }}</p>

                    <div class="contact-list">
                        <div v-if="contact.address" class="contact-item">
                            <svg class="contact-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="contact-text">{{ contact.address }}</span>
                        </div>
                        <div v-if="contact.phone && contact.phoneVisible" class="contact-item">
                            <svg class="contact-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <a :href="'tel:' + contact.phone" class="contact-link">{{ contact.phone }}</a>
                        </div>
                        <div v-if="contact.email" class="contact-item">
                            <svg class="contact-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <a :href="'mailto:' + contact.email" class="contact-link">{{ contact.email }}</a>
                        </div>
                        <div v-if="contact.whatsapp && contact.whatsappVisible" class="contact-item">
                            <svg class="contact-icon" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            <a
                                :href="'https://wa.me/' + contact.whatsapp.replace(/\D/g, '')"
                                target="_blank" rel="noopener noreferrer"
                                class="contact-link"
                            >{{ contact.whatsapp }}</a>
                        </div>
                    </div>
                </div>

                <!-- KANAN: Link Grid -->
                <div class="footer-links-area">
                    <!-- Loading skeleton -->
                    <div class="footer-grid" v-if="loading">
                        <div v-for="i in 4" :key="i" class="footer-col">
                            <div class="skeleton skeleton-label"></div>
                            <div class="skeleton skeleton-link" v-for="j in 5" :key="j"></div>
                        </div>
                    </div>

                    <div class="footer-grid" v-else>
                        <div v-for="group in groupedLinks" :key="group.group_name" class="footer-col">
                            <p class="footer-group-label">{{ group.group_name }}</p>
                            <div class="footer-group-underline"></div>
                            <ul class="footer-link-list">
                                <li v-for="link in group.links" :key="link.id">
                                    <a
                                        :href="link.url"
                                        :target="link.open_new_tab ? '_blank' : '_self'"
                                        :rel="link.open_new_tab ? 'noopener noreferrer' : undefined"
                                        class="footer-link"
                                    >{{ link.label }}</a>
                                </li>
                            </ul>
                        </div>

                        <!-- Kolom PENGIRIMAN -->
                        <div class="footer-col footer-col-shipping">
                            <p class="footer-group-label">PENGIRIMAN</p>
                            <div class="footer-group-underline"></div>
                            <p class="shipping-desc">Kami meyediakan beberapa opsi pengiriman.</p>
                            <div class="shipping-logos">
                                <div
                                    v-for="courier in couriers"
                                    :key="courier.name"
                                    class="courier-logo-wrap"
                                    :title="courier.name"
                                >
                                    <img
                                        v-if="courier.logo"
                                        :src="courier.logo"
                                        :alt="courier.name"
                                        class="courier-logo"
                                        @error="courier.logo = null"
                                    />
                                    <span v-else class="courier-name-text">{{ courier.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── Baris Bawah: Copyright + Social Icons ── -->
        <div class="footer-bottom">
            <div class="footer-container footer-bottom-inner">
                <p class="copyright-text">© {{ currentYear }} {{ siteName || 'Company' }}. All rights reserved.</p>

                <div class="footer-socials">
                    <a
                        v-for="social in activeSocialLinks"
                        :key="social.key"
                        :href="social.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="social-icon-btn"
                        :title="social.label"
                    >
                        <span v-html="social.icon"></span>
                    </a>
                </div>
            </div>
        </div>

    </footer>
</template>

<script>
import axios from '../axios.js'

export default {
    name: 'FooterSection',

    data() {
        return {
            groupedLinks: [],
            loading: true,
            logoUrl: null,
            siteName: '',
            siteDescription: '',

            contact: { address: '', phone: '', email: '', whatsapp: '', phoneVisible: true, whatsappVisible: true },

            couriers: [],

            socialLinks: [
                { key: 'facebook',  label: 'Facebook',    url: '', icon: `<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>` },
                { key: 'instagram', label: 'Instagram',   url: '', icon: `<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>` },
                { key: 'tiktok',    label: 'TikTok',      url: '', icon: `<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.67a8.18 8.18 0 004.77 1.52V6.74a4.85 4.85 0 01-1-.05z"/></svg>` },
                { key: 'twitter',   label: 'Twitter / X', url: '', icon: `<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>` },
                { key: 'youtube',   label: 'YouTube',     url: '', icon: `<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>` },
                { key: 'linkedin',  label: 'LinkedIn',    url: '', icon: `<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>` },
            ],
        }
    },

    computed: {
        currentYear() { return new Date().getFullYear() },
        activeSocialLinks() { return this.socialLinks.filter(s => s.url && s.url.trim() !== '') },
    },

    async mounted() {
        await Promise.all([this.fetchFooterLinks(), this.fetchSiteSettings()])
    },

    methods: {
        async fetchFooterLinks() {
            try {
                const { data } = await axios.get('/footer-links/grouped')
                this.groupedLinks = data
            } catch (e) {
                console.error('Failed to load footer links:', e)
            } finally {
                this.loading = false
            }
        },

        async fetchSiteSettings() {
            try {
                const { data } = await axios.get('/settings')
                if (data.site_logo_footer?.value) this.logoUrl = data.site_logo_footer.value
                else if (data.site_logo?.value)   this.logoUrl = data.site_logo.value
                if (data.site_name?.value)        this.siteName         = data.site_name.value
                if (data.site_description?.value) this.siteDescription  = data.site_description.value
                if (data.contact_address?.value)  this.contact.address  = data.contact_address.value
                if (data.contact_phone?.value)    this.contact.phone    = data.contact_phone.value
                if (data.contact_email?.value)    this.contact.email    = data.contact_email.value
                if (data.contact_whatsapp?.value) this.contact.whatsapp = data.contact_whatsapp.value
                if (data.contact_phone_visible?.value !== undefined) {
                    this.contact.phoneVisible = data.contact_phone_visible.value !== '0'
                }
                if (data.contact_whatsapp_visible?.value !== undefined) {
                    this.contact.whatsappVisible = data.contact_whatsapp_visible.value !== '0'
                }
                if (data.shipping_couriers?.value) {
                    try {
                        const all = JSON.parse(data.shipping_couriers.value)
                        this.couriers = all.filter(c => c.active)
                    } catch (e) {
                        this.couriers = []
                    }
                }

                const socialKeys = ['facebook', 'instagram', 'tiktok', 'twitter', 'youtube', 'linkedin']
                socialKeys.forEach(key => {
                    const setting = data[`social_${key}`]
                    if (setting?.value) {
                        const found = this.socialLinks.find(s => s.key === key)
                        if (found) found.url = setting.value
                    }
                })
            } catch (e) {
                console.error('Failed to load site settings:', e)
            }
        },
    },
}
</script>

<style scoped>
.footer-root {
    background: #ffffff;
    font-family: "Poppins", sans-serif;
}
.footer-container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

/* ── Divider ── */
.footer-divider { height: 1px; background: #e8e8e8; }

/* ── Layout Utama ── */
.footer-mid { padding: 40px 0 36px; }
.footer-mid-inner {
    display: grid;
    grid-template-columns: 1fr 2.4fr;
    align-items: start;
    gap: 56px;
}

.footer-links-area {
    padding-top: 100px;
}

/* ── MOBILE: semua full width, satu kolom ── */
@media (max-width: 768px) {
    .footer-mid { padding: 32px 0 28px; }
    .footer-mid-inner {
        grid-template-columns: 1fr;
        gap: 28px;
    }
    .footer-links-area {
        width: 100%;
    }
    .footer-grid {
        display: grid !important;
        grid-template-columns: 1fr 1fr;
        gap: 28px 20px;
    }
    .footer-col-shipping {
        grid-column: 1 / -1;  /* shipping kolom full width di mobile */
    }
    .shipping-logos {
        grid-template-columns: repeat(4, 1fr) !important;
    }
}

/* Brand / Kiri */
.footer-brand { display: flex; flex-direction: column; gap: 16px; }
.footer-logo-wrap { display: inline-flex; align-items: center; text-decoration: none; margin-bottom: 4px; }
.footer-logo-img { height: 80px; width: auto; object-fit: contain; }
.footer-logo-text { font-size: 22px; font-weight: 800; color: #111; letter-spacing: -0.02em; }

.brand-description {
    font-size: 13px;
    color: #555;
    line-height: 1.7;
    margin: 0;
    max-width: 310px;
}

.contact-list { display: flex; flex-direction: column; gap: 12px; margin-top: 4px; }
.contact-item { display: flex; align-items: flex-start; gap: 9px; }
.contact-icon { width: 15px; height: 15px; color: #BD2028; flex-shrink: 0; margin-top: 2px; }
.contact-text { font-size: 12px; color: #333; line-height: 1.5; }
.contact-link { font-size: 13px; color: #333; text-decoration: none; transition: color 0.15s ease; line-height: 1.5; }
.contact-link:hover { color: #BD2028; }

/* Link Grid / Kanan */
.footer-grid { display: flex; flex-wrap: wrap; gap: 32px 40px; }
.footer-col  { min-width: 110px; flex: 1; }

.footer-group-label {
    font-family: "Poppins", sans-serif;
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    color: #111;
    margin: 0 0 10px;
}
.footer-group-underline {
    width: 28px;
    height: 2px;
    background: #BD2028;
    margin-bottom: 16px;
    border-radius: 2px;
}
.footer-link-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
.footer-link { font-size: 13px; color: #444; text-decoration: none; transition: color 0.15s ease; line-height: 1.4; }
.footer-link:hover { color: #111; }

/* Kolom Pengiriman */
.footer-col-shipping { flex: 1.5; min-width: 200px; }
.shipping-desc {
    font-size: 12px;
    color: #666;
    line-height: 1.6;
    margin: 0 0 16px;
}
.shipping-logos {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    align-items: center;
}
.courier-logo-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    padding: 6px 8px;
    height: 56px;
    width: 100%;        /* tambah ini */
    box-sizing: border-box; /* tambah ini */
    overflow: hidden;
}
.courier-logo {
    width: auto;        /* tambah ini */
    height: 100%;       /* ubah jadi 100% */
    object-fit: contain;
    filter: grayscale(20%);
    transition: filter 0.2s ease;
}
.courier-logo-wrap:hover .courier-logo { filter: grayscale(0%); }
.courier-name-text {
    font-size: 9px;
    font-weight: 700;
    color: #555;
    text-align: center;
    letter-spacing: 0.03em;
    line-height: 1.2;
}

/* ── Copyright + Social ── */
.footer-bottom { padding: 16px 0; border-top: 1px solid #e8e8e8; }
.footer-bottom-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.copyright-text { font-size: 12px; color: #999; margin: 0; }
.footer-socials { display: flex; align-items: center; gap: 14px; }
.social-icon-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    text-decoration: none;
    transition: color 0.15s ease;
}
.social-icon-btn:hover { color: #BD2028; }

/* Skeleton */
.skeleton { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 3px; }
.skeleton-label { height: 10px; width: 80px; margin-bottom: 14px; }
.skeleton-link  { height: 12px; width: 100px; margin-bottom: 10px; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
</style>