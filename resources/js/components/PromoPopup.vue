<template>
    <Transition name="promo-popup">
        <div v-if="visible && promos.length > 0"
            class="fixed inset-0 z-[60] flex items-end justify-center"
            style="background: rgba(0,0,0,0.6); backdrop-filter: blur(3px);"
            @click.self="close">

            <!-- Auto height sesuai konten, max 80dvh -->
            <div class="bg-white w-full flex flex-col overflow-hidden"
                style="border-radius: 20px 20px 0 0; max-height: 80dvh; max-width: 500px;">

                <!-- ── Banner Header ── -->
                <div class="relative shrink-0 overflow-hidden"
                    style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);">

                    <div class="absolute -top-6 -right-6 w-32 h-32 rounded-full opacity-20" style="background: #ED1F24;"></div>
                    <div class="absolute -bottom-4 -left-4 w-24 h-24 rounded-full opacity-10" style="background: #ED1F24;"></div>

                    <div class="relative px-6 pt-5 pb-5">
                        <p class="text-[10px] font-bold tracking-[0.25em] uppercase text-white/40 mb-1">Penawaran Spesial</p>
                        <h2 class="text-xl font-black text-white leading-tight tracking-tight uppercase">
                            {{ popupTitle }}
                        </h2>
                        <p class="text-xs text-white/60 mt-1.5">
                            Klaim voucher & checkout sebelum berakhir!
                        </p>
                    </div>

                    <button @click="close"
                        class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-all active:scale-90">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <!-- ── Promo List ── -->
                <div class="overflow-y-auto px-4 py-4 flex flex-col gap-3">

                    <template v-for="(group, label) in groupedPromos" :key="label">

                        <!-- Default group (tanpa label) -->
                        <div v-if="label === '__default__'" class="flex flex-col gap-3">
                            <div v-for="promo in group" :key="promo.id"
                                class="flex items-center justify-between gap-3 bg-white border border-gray-200 rounded-2xl px-4 py-3.5 shadow-sm">
                                <div class="min-w-0">
                                    <p class="font-black text-base text-gray-900 tracking-wider font-mono">{{ promo.code }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ promoDesc(promo) }}</p>
                                </div>
                                <button @click="copyCode(promo.code)"
                                    class="shrink-0 flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-xl transition-all active:scale-90"
                                    :class="copied === promo.code
                                        ? 'bg-emerald-500 text-white'
                                        : 'bg-[#ED1F24] hover:bg-[#C81A1E] text-white'">
                                    <svg v-if="copied !== promo.code" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                                    </svg>
                                    <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    {{ copied === promo.code ? 'Disalin!' : 'Salin' }}
                                </button>
                            </div>
                        </div>

                        <!-- Label group — style dark -->
                        <div v-else class="rounded-2xl overflow-hidden border border-gray-800">
                            <div class="bg-gray-900 px-4 py-3 text-center">
                                <p class="text-xs font-black tracking-[0.15em] uppercase text-white">{{ label }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Pakai kode di bawah & dapatkan diskon eksklusif</p>
                            </div>
                            <div class="divide-y divide-gray-800">
                                <div v-for="promo in group" :key="promo.id"
                                    class="bg-gray-900 px-4 py-3.5 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-black text-base text-white tracking-wider font-mono">{{ promo.code }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ promoDesc(promo) }}</p>
                                    </div>
                                    <button @click="copyCode(promo.code)"
                                        class="shrink-0 flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-xl transition-all active:scale-90"
                                        :class="copied === promo.code
                                            ? 'bg-emerald-500 text-white'
                                            : 'bg-white hover:bg-gray-100 text-gray-900'">
                                        <svg v-if="copied !== promo.code" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                                        </svg>
                                        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        {{ copied === promo.code ? 'Disalin!' : 'Salin' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                    </template>

                    <p class="text-center text-[11px] text-gray-400 italic pb-1">*Syarat dan ketentuan berlaku</p>
                </div>

                <!-- ── CTA Button ── -->
                <div class="shrink-0 px-4 pt-3 bg-white border-t border-gray-100"
                    style="padding-bottom: max(1.25rem, env(safe-area-inset-bottom));">
                    <button @click="shopNow"
                        class="w-full py-3.5 rounded-2xl font-black text-sm text-white tracking-widest uppercase transition-all active:scale-95"
                        style="background: linear-gradient(135deg, #ED1F24, #C81A1E); box-shadow: 0 8px 24px rgba(237,31,36,0.3);">
                        BELANJA SEKARANG
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
                ta.style.position = 'fixed'
                ta.style.opacity = '0'
                document.body.appendChild(ta)
                ta.focus()
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
.promo-popup-enter-active { transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
.promo-popup-leave-active { transition: all 0.2s ease-in; }
.promo-popup-enter-from  { opacity: 0; transform: translateY(100%); }
.promo-popup-leave-to    { opacity: 0; transform: translateY(40px); }
</style>