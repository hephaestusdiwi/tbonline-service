<template>
  <AdminLayout title="Halaman Statis">

    <div class="mb-6">
      <h1 class="text-2xl font-semibold text-gray-900">Halaman Statis</h1>
      <p class="text-sm text-gray-500 mt-0.5">Kelola konten halaman informasi toko</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <RouterLink
        v-for="page in staticPages"
        :key="page.type"
        :to="`/admin/pages/${page.type}`"
        class="bg-white rounded-2xl border border-gray-100 p-6 flex items-start gap-4 hover:border-gray-200 hover:shadow-sm transition-all group"
      >
        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :style="{ background: page.iconBg }">
          <span v-html="page.icon" class="w-5 h-5" :style="{ color: page.iconColor }" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-800 group-hover:text-[#ED1F24] transition-colors">{{ page.title }}</p>
          <p class="text-xs text-gray-400 mt-0.5">{{ page.description }}</p>
          <div class="flex items-center gap-1.5 mt-3">
            <span :class="page.status === 'published' ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400'" class="text-xs font-semibold px-2 py-0.5 rounded-full">
              {{ page.status === 'published' ? 'Published' : 'Draft' }}
            </span>
            <span class="text-xs text-gray-300">· diperbarui {{ page.updatedAt }}</span>
          </div>
        </div>
        <svg class="w-4 h-4 text-gray-300 group-hover:text-[#ED1F24] shrink-0 mt-0.5 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
        </svg>
      </RouterLink>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/components/admin/AdminLayout.vue'
import api from '@/axios'

const staticPages = ref([
  {
    type: 'tos',
    title: 'Syarat & Ketentuan',
    description: 'Aturan penggunaan layanan toko',
    iconBg: 'rgba(59,130,246,0.08)', iconColor: '#3B82F6',
    icon: '<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>',
    status: 'draft', updatedAt: '—',
  },
  {
    type: 'shipping_info',
    title: 'Informasi Pengiriman',
    description: 'Kebijakan dan detail pengiriman',
    iconBg: 'rgba(20,184,166,0.08)', iconColor: '#14B8A6',
    icon: '<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>',
    status: 'draft', updatedAt: '—',
  },
  {
    type: 'return_policy',
    title: 'Pengembalian Barang',
    description: 'Prosedur retur dan refund',
    iconBg: 'rgba(245,158,11,0.08)', iconColor: '#F59E0B',
    icon: '<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>',
    status: 'draft', updatedAt: '—',
  },
])

// Load status dari API
onMounted(async () => {
  for (const page of staticPages.value) {
    try {
      const { data } = await api.get(`/admin/contents/static/${page.type}`)
      page.status    = data.data.status
      page.updatedAt = data.data.updated_at
        ? new Date(data.data.updated_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        : '—'
    } catch {
      // 404 = belum ada konten → tetap draft
    }
  }
})
</script>