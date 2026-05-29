<template>
  <!-- Backdrop -->
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="$emit('cancel')">
      <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" />
      <!-- Dialog -->
      <div class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 animate-in fade-in zoom-in duration-200">
        <!-- Icon -->
        <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-4">
          <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
          </svg>
        </div>

        <h3 class="text-base font-semibold text-gray-900 text-center mb-1">{{ title }}</h3>
        <p class="text-sm text-gray-400 text-center mb-6">{{ description }}</p>

        <div class="flex gap-3">
          <button
            @click="$emit('cancel')"
            class="flex-1 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
          >{{ cancelLabel }}</button>
          <button
            @click="$emit('confirm')"
            :class="['flex-1 py-2.5 text-sm font-semibold rounded-xl transition-colors', confirmClass]"
          >{{ confirmLabel }}</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  title:        { type: String, default: 'Konfirmasi' },
  description:  { type: String, default: 'Apakah Anda yakin?' },
  confirmLabel: { type: String, default: 'Ya' },
  cancelLabel:  { type: String, default: 'Batal' },
  confirmClass: { type: String, default: 'bg-[#ED1F24] hover:bg-[#C81A1E] text-white' },
})

defineEmits(['confirm', 'cancel'])
</script>