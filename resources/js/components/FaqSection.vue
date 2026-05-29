<template>
    <section class="faq-section py-16 px-4">
        <div class="max-w-5xl mx-auto">

            <!-- Loading skeleton -->
            <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-10 animate-pulse">
                <div class="space-y-3">
                    <div class="h-8 bg-gray-200 rounded-xl w-3/4"/>
                    <div class="h-5 bg-gray-100 rounded-xl w-full"/>
                    <div class="h-4 bg-gray-100 rounded-xl w-2/3"/>
                    <div class="h-4 bg-gray-100 rounded-xl w-1/2"/>
                </div>
                <div class="space-y-3">
                    <div v-for="n in 5" :key="n" class="h-14 bg-gray-100 rounded-2xl"/>
                </div>
            </div>

            <!-- Main content -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-10 lg:gap-16 items-start">

                <!-- LEFT: Heading -->
                <div class="lg:sticky lg:top-8">
                    <h2 class="text-3xl font-bold text-gray-900 leading-tight mb-4">
                        Frequently Asked
                        <span style="color: #BD2028;">Questions</span>
                    </h2>
                    <p class="text-xs text-gray-500 leading-relaxed mb-6">
                        Temukan jawaban cepat atas pertanyaan umum seputar produk dan layanan kami.
                    </p>

                    <!-- Category tabs (if multiple categories) -->
                    <div v-if="categoryKeys.length > 1" class="flex flex-wrap gap-2">
                        <button
                            v-for="cat in ['Semua', ...categoryKeys]" :key="cat"
                            @click="activeCategory = cat"
                            class="text-xs font-semibold px-3 py-1.5 rounded-full border transition-all"
                            :style="activeCategory === cat
                                ? 'background: #BD2028; color: #fff; border-color: #BD2028;'
                                : 'background: #fff; color: #6b7280; border-color: #e5e7eb;'"
                        >
                            {{ cat }}
                        </button>
                    </div>

                    <!-- Contact prompt -->
                    <div class="mt-8 p-4 rounded-2xl border border-gray-100" style="background: #fafafa;">
                        <p class="text-xs font-semibold text-gray-700 mb-1">Tidak menemukan jawaban?</p>
                        <p class="text-xs text-gray-400 leading-relaxed mb-3">Tim kami siap membantu.</p>
                        <button @click="openChat" 
                            class="inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-xl text-white transition-all"
                            style="background: #BD2028;">
                            Hubungi Kami
                            <font-awesome-icon :icon="['fas', 'comments']" />
                        </button>
                    </div>
                </div>

                <!-- RIGHT: Accordion -->
                <div>
                    <!-- Empty state -->
                    <div v-if="!visibleFaqs.length" class="text-center py-12">
                        <p class="text-gray-400 text-sm">Belum ada FAQ tersedia.</p>
                    </div>

                    <div v-else class="space-y-2">
                        <div
                            v-for="(faq, idx) in visibleFaqs"
                            :key="faq.id"
                            class="faq-item rounded-2xl border overflow-hidden transition-all duration-200"
                            :class="openIndex === idx
                                ? 'border-gray-200 shadow-sm'
                                : 'border-gray-100 hover:border-gray-200'"
                            :style="openIndex === idx ? 'background: #fff;' : 'background: #fafafa;'"
                        >
                            <!-- Question row -->
                            <button
                                @click="toggle(idx)"
                                class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left transition-all"
                            >
                                <span class="text-sm font-semibold text-gray-800 leading-snug">
                                    {{ faq.question }}
                                </span>
                                <span
                                    class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center transition-all duration-300"
                                    :style="openIndex === idx
                                        ? 'background: #BD2028; color: #fff;'
                                        : 'background: #f3f4f6; color: #9ca3af;'"
                                >
                                    <svg
                                        class="w-3.5 h-3.5 transition-transform duration-300"
                                        :class="openIndex === idx ? 'rotate-180' : ''"
                                        fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                                    </svg>
                                </span>
                            </button>

                            <!-- Answer panel -->
                            <transition
                                enter-active-class="transition-all duration-300 ease-out"
                                leave-active-class="transition-all duration-200 ease-in"
                                enter-from-class="opacity-0 max-h-0"
                                enter-to-class="opacity-100 max-h-screen"
                                leave-from-class="opacity-100 max-h-screen"
                                leave-to-class="opacity-0 max-h-0"
                            >
                                <div v-if="openIndex === idx" class="overflow-hidden">
                                    <div class="px-6 pb-5 border-t border-gray-100">
                                        <div
                                            class="pt-4 text-sm text-gray-600 leading-relaxed faq-answer"
                                            v-html="faq.answer"
                                        />
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useFaqStore } from '../store/useFaqStore'

const faqStore = useFaqStore()

const loading        = ref(false)
const groupedFaqs    = ref({})   // { Umum: [...], Produk: [...] }
const openIndex      = ref(0)    // accordion: index yang terbuka
const activeCategory = ref('Semua')

const categoryKeys = computed(() => Object.keys(groupedFaqs.value))

const visibleFaqs = computed(() => {
    if (activeCategory.value === 'Semua') {
        // gabungkan semua kategori jadi satu list flat
        return Object.values(groupedFaqs.value).flat()
    }
    return groupedFaqs.value[activeCategory.value] ?? []
})

function toggle(idx) {
    openIndex.value = openIndex.value === idx ? null : idx
}

function openChat() {
    window.dispatchEvent(new CustomEvent('open-chat'))
}

onMounted(async () => {
    loading.value = true
    try {
        groupedFaqs.value = await faqStore.fetchPublic()
    } finally {
        loading.value = false
    }
})
</script>

<style scoped>
/* Answer content styles */
.faq-section {
    font-family: "Poppins", sans-serif;
}
.faq-answer :deep(p)  { margin-bottom: 0.6rem; }
.faq-answer :deep(p:last-child) { margin-bottom: 0; }
.faq-answer :deep(ul) { list-style: disc; padding-left: 1.25rem; margin-bottom: 0.6rem; }
.faq-answer :deep(ol) { list-style: decimal; padding-left: 1.25rem; margin-bottom: 0.6rem; }
.faq-answer :deep(li) { margin-bottom: 0.25rem; }
.faq-answer :deep(strong) { font-weight: 700; color: #1f2937; }
.faq-answer :deep(em) { font-style: italic; }
.faq-answer :deep(code) {
    background: #f3f4f6;
    color: #BD2028;
    padding: 0.1em 0.35em;
    border-radius: 4px;
    font-size: 0.85em;
    font-family: monospace;
}
.faq-answer :deep(a) { color: #BD2028; text-decoration: underline; }
</style>