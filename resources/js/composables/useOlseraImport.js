import { ref } from 'vue'
import api from '@/services/api'

export function useOlseraImport() {
  const progress = ref(null)
  const isImporting = ref(false)
  let pollInterval = null

  async function startImport(products, mode) {
    isImporting.value = true
    const { data } = await api.post('/products/import-olsera', { products, mode })
    pollProgress(data.import_id)
  }

  function pollProgress(importId) {
    pollInterval = setInterval(async () => {
      const { data } = await api.get(`/products/import-olsera/status/${importId}`)
      progress.value = data

      if (data.status === 'completed') {
        clearInterval(pollInterval)
        isImporting.value = false
      }
    }, 2000)
  }

  return { progress, isImporting, startImport }
}