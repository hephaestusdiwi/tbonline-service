import { ref } from 'vue'
import axiosInstance from '../axios'

export function usePromotions()  {
    const promotions = ref([])
    const loading    = ref(false)
    const error      = ref(null)

    async function fetchPromotions() {
        loading.value = true
        error.value   = null
        try {
            const { data } = await axiosInstance.get('/promotions')
            // Map response ke format yang dipakai PromotionSlider
            promotions.value = data.data.map(p => ({
                id:       p.id,
                image:    p.image_url,
                title:    p.title,
                link:     p.link,
                type:     p.link_type,   // 'instagram' | 'artikel' | 'other'
            }))
        } catch (err) {
            console.error('[usePromotions] fetch error:', err)
            error.value = 'Gagal memuat promosi'
        } finally {
            loading.value = false
        }
    }

    return { promotions, loading, error, fetchPromotions }
}