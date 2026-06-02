import { ref, readonly, computed } from 'vue'
import axiosInstance from '../axios'

const settings = ref({})
const loaded = ref(false)

export function useSiteSettings() {
    async function fetchSettings() {
        if (loaded.value) return
        try {
            const { data } = await axiosInstance.get('/settings')
            settings.value = data
            loaded.value = true

            // ── Dynamic favicon ──────────────────────────────────────────────
            const faviconUrl = data?.site_favicon?.value
            if (faviconUrl) {
                let link = document.querySelector("link[rel='icon']")
                if (!link) {
                    link = document.createElement('link')
                    link.rel = 'icon'
                    document.head.appendChild(link)
                }
                link.type = 'image/png'
                link.href = faviconUrl
            }

            // ── Dynamic page title ───────────────────────────────────────────
            const siteName = data?.site_name?.value
            if (siteName) {
                document.title = siteName
            }

        } catch (e) {
            console.error('Failed to load site settings:', e)
        }
    }

    const siteLogo = computed(() => settings.value?.site_logo?.value ?? null)
    const siteName = computed(() => settings.value?.site_name?.value ?? 'TB Store')
    const siteDescription = computed(() => settings.value?.site_description?.value ?? '')
    const siteFavicon = computed(() => settings.value?.site_favicon?.value ?? null)
    const adminWhatsapp   = computed(() => settings.value?.admin_whatsapp?.value   ?? '6281293139223')
    const storeWhatsapp   = computed(() => settings.value?.store_whatsapp?.value   ?? '6281293139223')

    return {
        settings: readonly(settings),
        siteLogo,
        siteName,
        siteDescription,
        siteFavicon,
        adminWhatsapp,
        storeWhatsapp,
        fetchSettings,
    }
}