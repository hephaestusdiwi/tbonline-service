<template>
    <Transition name="promo-popup">
        <div v-if="visible && promos.length > 0"
            class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4"
            style="background: rgba(0,0,0,0.55); backdrop-filter: blur(3px);"
            @click.self="close">

            <div class="bg-white w-full sm:max-w-sm sm:rounded-2xl shadow-2xl flex flex-col overflow-hidden max-h-[92dvh] sm:max-h-[85vh]"
                style="border-radius: 20px 20px 0 0;">

                <!-- ── Banner Header ── -->
                <div class="relative shrink-0 overflow-hidden"
                    style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 110px;">

                    <!-- Decorative circles -->
                    <div class="absolute -top-6 -right-6 w-32 h-32 rounded-full opacity-10" style="background: #ED1F24;"></div>
                    <div class="absolute -bottom-4 -left-4 w-24 h-24 rounded-full opacity-10" style="background: #ED1F24;"></div>

                    <div class="relative px-5 pt-5 pb-4">
                        <p class="text-[10px] font-bold tracking-[0.2em] uppercase text-white/40 mb-1">Penawaran Spesial</p>
                        <h2 class="text-xl font-black text-white leading-tight tracking-tight">
                            {{ popupTitle }}
                        </h2>
                        <p class="text-xs text-white/60 mt-1.5">
                            Klaim voucher & checkout sebelum berakhir!
                        </p>
                    </div>

                    <!-- Close button -->
                    <button @click="close"
                        class="absolute top-3 right-3 w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-all active:scale-90">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <!-- ── Promo List ── -->
                <div class="overflow-y-auto flex-1 px-4 py-3 flex flex-col gap-2">

                    <!-- Group by popup_label -->
                    <template v-for="(group, label) in groupedPromos" :key="label">

                        <!-- Label header (jika ada) -->
                        <div v-if="label !== '__default__'"
                            class="mt-2 first:mt-0 rounded-xl overflow-hidden border border-gray-800">
                            <div class="bg-gray-900 px-4 py-2.5 text-center">
                                <p class="text-[10px] font-black tracking-[0.15em] uppercase text-white">{{ label }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Pakai kode di bawah & dapatkan diskon eksklusif</p>
                            </div>

                            <!-- Promo items in dark group -->
                            <div class="bg-gray-900/5 divide-y divide-gray-100">
                                <div v-for="promo in group" :key="promo.id"
                                    class="bg-gray-900 px-4 py-3 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-black text-sm text-white tracking-wider font-mono">{{ promo.code }}</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5 truncate">{{ promoDesc(promo) }}</p>
                                    </div>
                                    <button @click="copyCode(promo.code)"
                                        class="shrink-0 flex items-center gap-1.5 text-[11px] font-bold px-3 py-1.5 rounded-lg transition-all active:scale-90"
                                        :class="copied === promo.code
                                            ? 'bg-emerald-500 text-white'
                                            : 'bg-[#ED1F24] hover:bg-[#C81A1E] text-white'">
                                        <svg v-if="copied !== promo.code" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                                        </svg>
                                        <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        {{ copied === promo.code ? 'Disalin!' : 'Salin' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Default group (tanpa label) -->
                        <div v-else class="flex flex-col gap-2">
                            <div v-for="promo in group" :key="promo.id"
                                class="flex items-center justify-between gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 hover:border-[#ED1F24]/30 transition-all">
                                <div class="min-w-0">
                                    <p class="font-black text-sm text-gray-800 tracking-wider font-mono">{{ promo.code }}</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5 truncate">{{ promoDesc(promo) }}</p>
                                </div>
                                <button @click="copyCode(promo.code)"
                                    class="shrink-0 flex items-center gap-1.5 text-[11px] font-bold px-3 py-1.5 rounded-lg transition-all active:scale-90"
                                    :class="copied === promo.code
                                        ? 'bg-emerald-500 text-white'
                                        : 'bg-[#ED1F24] hover:bg-[#C81A1E] text-white'">
                                    <svg v-if="copied !== promo.code" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                                    </svg>
                                    <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    {{ copied === promo.code ? 'Disalin!' : 'Salin' }}
                                </button>
                            </div>
                        </div>

                    </template>

                    <p class="text-center text-[10px] text-gray-400 pt-1 pb-2">*Syarat dan ketentuan berlaku</p>
                </div>

                <!-- ── CTA Button ── -->
                <div class="shrink-0 px-4 pb-6 pt-2 bg-white border-t border-gray-100">
                    <button @click="shopNow"
                        class="w-full py-3.5 rounded-xl font-black text-sm text-white tracking-widest uppercase transition-all active:scale-55 shadow-lg shadow-red-200"
                        style="background: linear-gradient(135deg, #ED1F24, #C81A1E);">
                         Belanja Sekarang
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
import axios from '../axios.js'

const SESSION_KEY = 'promo_popup_shown'

export default {
    name: 'PromoPopup',

    data() {
        return {
            visible: false,
            promos: [],
            copied: null,
            copyTimer: null,
        }
    },

    computed: {
        groupedPromos() {
            const groups = {}
            this.promos.forEach(p => {
                const key = p.popup_label?.trim() || '__default__'
                if (!groups[key]) groups[key] = []
                groups[key].push(p)
            })
            const ordered = {}
            if (groups['__default__']) ordered['__default__'] = groups['__default__']
            Object.keys(groups).filter(k => k !== '__default__').forEach(k => {
                ordered[k] = groups[k]
            })
            return ordered
        },

        popupTitle() {
            const labels = Object.keys(this.groupedPromos).filter(k => k !== '__default__')
            if (labels.length === 0) return 'Promo Spesial Hari Ini!'
            if (labels.length === 1) return labels[0]
            return 'Penawaran Terbaik Untukmu'
        },
    },

    async mounted() {
        if (sessionStorage.getItem(SESSION_KEY)) return

        try {
            const res = await axios.get('/promo-codes/popup')
            const data = res.data?.data ?? []
            if (!Array.isArray(data) || data.length === 0) return
            this.promos = data

            setTimeout(() => { this.visible = true }, 800)
        } catch (e) {
            console.error('PromoPopup: gagal load popup promos', e)
        }
    },

    methods: {
        close() {
            this.visible = false
            sessionStorage.setItem(SESSION_KEY, '1')
        },

        shopNow() {
            this.close()
            const el = document.querySelector('#products, .product-list, [data-section="products"]')
            if (el) el.scrollIntoView({ behavior: 'smooth' })
        },

        async copyCode(code) {
            try {
                await navigator.clipboard.writeText(code)
            } catch {
                const ta = document.createElement('textarea')
                ta.value = code
                document.body.appendChild(ta)
                ta.select()
                document.execCommand('copy')
                document.body.removeChild(ta)
            }
            this.copied = code
            clearTimeout(this.copyTimer)
            this.copyTimer = setTimeout(() => { this.copied = null }, 2000)
        },

        promoDesc(promo) {
            const val = promo.discount_type === 'percentage'
                ? `${promo.discount_value}%`
                : promo.discount_type === 'free_shipping'
                    ? 'Gratis Ongkir'
                    : `Rp${Number(promo.discount_value).toLocaleString('id-ID')}`

            let minText = ''
            if (promo.min_purchase > 0) {
                const min = Number(promo.min_purchase)
                if (min >= 1_000_000) {
                    const jt = min / 1_000_000
                    minText = `, min. belanja Rp${Number.isInteger(jt) ? jt : jt.toFixed(1)}jt`
                } else {
                    const rb = min / 1_000
                    minText = `, min. belanja Rp${Number.isInteger(rb) ? rb : rb.toFixed(1)}rb`
                }
            }

            return `*Diskon ${val}${minText}`
        },
    },
}
</script>

<style scoped>
.promo-popup {
  font-family: "Poppins", sans-serif;
}
.promo-popup-enter-active { transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
.promo-popup-leave-active { transition: all 0.2s ease-in; }

.promo-popup-enter-from  { opacity: 0; transform: translateY(40px); }
.promo-popup-leave-to    { opacity: 0; transform: translateY(20px); }

@media (min-width: 640px) {
    .promo-popup-enter-from { opacity: 0; transform: scale(0.95) translateY(0); }
    .promo-popup-leave-to   { opacity: 0; transform: scale(0.95) translateY(0); }
}
</style>