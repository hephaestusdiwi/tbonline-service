import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '../axios.js'

export const useFaqStore = defineStore('faq', () => {

    // ── State ──────────────────────────────────────────────────
    const faqs        = ref([])
    const currentItem = ref(null)
    const categories  = ref([])
    const pagination  = ref({ current_page: 1, last_page: 1, total: 0 })
    const loading     = ref(false)
    const error       = ref(null)

    // ── Admin: fetch list ──────────────────────────────────────
    async function fetchAll(params = {}) {
        loading.value = true
        error.value   = null
        try {
            const { data } = await axios.get('/admin/faqs', { params })
            faqs.value      = data.data
            pagination.value = {
                current_page: data.current_page,
                last_page:    data.last_page,
                total:        data.total,
            }
        } catch (e) {
            error.value = e.response?.data?.message ?? 'Gagal memuat FAQ.'
            throw e
        } finally {
            loading.value = false
        }
    }

    // ── Admin: fetch single ────────────────────────────────────
    async function fetchOne(id) {
        loading.value = true
        error.value   = null
        try {
            const { data } = await axios.get(`/admin/faqs/${id}`)
            currentItem.value = data
        } catch (e) {
            error.value = e.response?.data?.message ?? 'Gagal memuat FAQ.'
            throw e
        } finally {
            loading.value = false
        }
    }

    // ── Admin: create ──────────────────────────────────────────
    async function create(payload) {
        const { data } = await axios.post('/admin/faqs', payload)
        faqs.value.unshift(data)
        return data
    }

    // ── Admin: update ──────────────────────────────────────────
    async function update(id, payload) {
        const { data } = await axios.put(`/admin/faqs/${id}`, payload)
        const idx = faqs.value.findIndex(f => f.id === id)
        if (idx !== -1) faqs.value[idx] = data
        currentItem.value = data
        return data
    }

    // ── Admin: toggle active ───────────────────────────────────
    async function toggleActive(id) {
        const faq = faqs.value.find(f => f.id === id)
        if (!faq) return
        const { data } = await axios.put(`/admin/faqs/${id}`, {
            ...faq,
            is_active: !faq.is_active,
        })
        const idx = faqs.value.findIndex(f => f.id === id)
        if (idx !== -1) faqs.value[idx] = data
        return data
    }

    // ── Admin: delete ──────────────────────────────────────────
    async function remove(id) {
        await axios.delete(`/admin/faqs/${id}`)
        faqs.value = faqs.value.filter(f => f.id !== id)
    }

    // ── Admin: reorder ─────────────────────────────────────────
    async function reorder(items) {
        await axios.patch('/admin/faqs/reorder', { items })
    }

    // ── Admin: fetch categories ────────────────────────────────
    async function fetchCategories() {
        const { data } = await axios.get('/admin/faqs/categories')
        categories.value = data
    }

    // ── Public ─────────────────────────────────────────────────
    async function fetchPublic() {
        loading.value = true
        error.value   = null
        try {
            const { data } = await axios.get('/faqs')
            return data // objek grouped by category
        } catch (e) {
            error.value = 'Gagal memuat FAQ.'
            throw e
        } finally {
            loading.value = false
        }
    }

    // ── Reset ──────────────────────────────────────────────────
    function resetCurrent() {
        currentItem.value = null
    }

    return {
        faqs, currentItem, categories, pagination, loading, error,
        fetchAll, fetchOne, create, update, toggleActive, remove,
        reorder, fetchCategories, fetchPublic, resetCurrent,
    }
})