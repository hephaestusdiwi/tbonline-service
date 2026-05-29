// stores/useContentStore.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/axios'

export const useContentStore = defineStore('content', () => {

  // ── State ──────────────────────────────────────────────────
  const items        = ref([])
  const currentItem  = ref(null)
  const meta         = ref({})          // pagination meta
  const loading      = ref(false)
  const saving       = ref(false)
  const error        = ref(null)

  const filters = ref({
    type:     '',
    status:   '',
    search:   '',
    per_page: 15,
    page:     1,
  })

  // ── Getters ────────────────────────────────────────────────
  const articles     = computed(() => items.value.filter(c => c.type === 'article'))
  const staticPages  = computed(() => items.value.filter(c => c.type !== 'article'))
  const isLoading    = computed(() => loading.value)
  const isSaving     = computed(() => saving.value)

  // ── Actions ────────────────────────────────────────────────

  async function fetchList(overrideFilters = {}) {
    loading.value = true
    error.value   = null
    try {
      const params = { ...filters.value, ...overrideFilters }
      // Hapus key kosong supaya query string bersih
      Object.keys(params).forEach(k => { if (!params[k]) delete params[k] })

      const { data } = await api.get('/admin/contents', { params })
      items.value = data.data
      meta.value  = data.meta
      return data 
    } catch (e) {
      error.value = e.response?.data?.message ?? 'Gagal memuat konten.'
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id) {
    loading.value    = true
    currentItem.value = null
    error.value      = null
    try {
      const { data } = await api.get(`/admin/contents/${id}`)
      currentItem.value = data.data
    } catch (e) {
      error.value = e.response?.data?.message ?? 'Konten tidak ditemukan.'
    } finally {
      loading.value = false
    }
  }

  async function fetchStatic(type) {
    loading.value    = true
    currentItem.value = null
    error.value      = null
    try {
      const { data } = await api.get(`/admin/contents/static/${type}`)
      currentItem.value = data.data
    } catch (e) {
      // 404 = belum ada konten, bukan error fatal
      if (e.response?.status !== 404) {
        error.value = e.response?.data?.message ?? 'Gagal memuat halaman.'
      }
    } finally {
      loading.value = false
    }
  }

  async function create(payload) {
    saving.value = true
    error.value  = null
    try {
      const formData = buildFormData(payload)
      const { data } = await api.post('/admin/contents', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      items.value.unshift(data.data)
      return data.data
    } catch (e) {
      error.value = e.response?.data?.message ?? 'Gagal menyimpan konten.'
      throw e
    } finally {
      saving.value = false
    }
  }

  async function update(id, payload) {
      saving.value = true
      error.value  = null
      try {
          let data

          // Kalau ada file thumbnail, pakai FormData + POST
          if (payload.thumbnail instanceof File) {
              const formData = buildFormData(payload)
              formData.append('_method', 'PUT')
              const res = await api.post(`/admin/contents/${id}`, formData, {
                  headers: { 'Content-Type': 'multipart/form-data' },
              })
              data = res.data
          } else {
              // Tidak ada file, kirim JSON biasa via PUT
              const json = { ...payload }
              delete json.thumbnail
              const res = await api.put(`/admin/contents/${id}`, json)
              data = res.data
          }

          const idx = items.value.findIndex(c => c.id === id)
          if (idx !== -1) items.value[idx] = data.data
          if (currentItem.value?.id === id) currentItem.value = data.data
          return data.data
      } catch (e) {
          error.value = e.response?.data?.message ?? 'Gagal menyimpan perubahan.'
          throw e
      } finally {
          saving.value = false
      }
  }

  async function togglePublish(id) {
    try {
      const { data } = await api.patch(`/admin/contents/${id}/publish`)
      const idx = items.value.findIndex(c => c.id === id)
      if (idx !== -1) items.value[idx] = data.data
      if (currentItem.value?.id === id) currentItem.value = data.data
      return data.data
    } catch (e) {
      error.value = e.response?.data?.message ?? 'Gagal mengubah status.'
      throw e
    }
  }

  async function remove(id) {
    try {
      await api.delete(`/admin/contents/${id}`)
      items.value = items.value.filter(c => c.id !== id)
    } catch (e) {
      error.value = e.response?.data?.message ?? 'Gagal menghapus konten.'
      throw e
    }
  }

  function setFilter(key, value) {
    filters.value[key] = value
    filters.value.page = 1
  }

  function resetFilters() {
    filters.value = { type: '', status: '', search: '', per_page: 15, page: 1 }
  }

  // ── Helpers ────────────────────────────────────────────────
  function buildFormData(payload) {
    const fd = new FormData()
    for (const [key, val] of Object.entries(payload)) {
      if (val === null || val === undefined) continue
      if (key === 'tags' && Array.isArray(val)) {
        val.forEach(t => fd.append('tags[]', t))
      } else if (val instanceof File) {
        fd.append(key, val)
      } else {
        fd.append(key, val)
      }
    }
    return fd
  }

  return {
    // State
    items, currentItem, meta, filters,
    loading, saving, error,
    // Getters
    articles, staticPages, isLoading, isSaving,
    // Actions
    fetchList, fetchOne, fetchStatic,
    create, update, togglePublish, remove,
    setFilter, resetFilters,
  }
})