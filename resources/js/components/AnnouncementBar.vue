<template>
  <div v-show="enabled && items.length" class="ab-bar" :style="barStyle">
    <div class="ab-track">
      <!--
        Strategi: kedua slot ada di normal flow (bukan absolute),
        yang hidden pakai visibility:hidden + position:absolute
        supaya tidak makan space tapi wrapper tetap setinggi slot visible.
        Ini mencegah bar melayang karena tinggi wrapper selalu mengikuti
        konten yang sedang ditampilkan.
      -->
      <div class="ab-transition-wrap">

        <div class="ab-item" :class="activeSlot === 'a' ? 'slot--on' : 'slot--off'">
          <span class="ab-text">{{ slotA.text }}</span>
          <a v-if="slotA.link_url" :href="slotA.link_url" target="_blank" rel="noopener" class="ab-link">
            {{ slotA.link_label || 'Selengkapnya' }}
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
          </a>
        </div>

        <div class="ab-item" :class="activeSlot === 'b' ? 'slot--on' : 'slot--off'">
          <span class="ab-text">{{ slotB.text }}</span>
          <a v-if="slotB.link_url" :href="slotB.link_url" target="_blank" rel="noopener" class="ab-link">
            {{ slotB.link_label || 'Selengkapnya' }}
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
          </a>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import axios from '../axios.js'

const EMPTY = { text: '', link_url: null, link_label: null }

export default {
  name: 'AnnouncementBar',

  props: {
    enabled:   { type: Boolean, default: true },
    bgColor:   { type: String,  default: '#000000' },
    textColor: { type: String,  default: '#ffffff' },
    interval:  { type: Number,  default: 4000 },
  },

  setup(props) {
    const items      = ref([])
    const current    = ref(0)
    const activeSlot = ref('a')
    const slotA      = ref({ ...EMPTY })
    const slotB      = ref({ ...EMPTY })
    let timer        = null

    const barStyle = {
      '--ab-bg':   props.bgColor,
      '--ab-text': props.textColor,
    }

    async function fetchAnnouncements() {
      try {
        const res = await axios.get('/announcements')
        items.value = res.data ?? []
        if (items.value.length) {
          slotA.value      = { ...items.value[0] }
          slotB.value      = { ...EMPTY }
          activeSlot.value = 'a'
        }
      } catch (e) {
        console.warn('Announcement fetch failed:', e)
      }
    }

    function advance() {
      if (!items.value.length) return
      current.value = (current.value + 1) % items.value.length
      const next = items.value[current.value]
      if (activeSlot.value === 'a') {
        slotB.value      = { ...next }
        activeSlot.value = 'b'
      } else {
        slotA.value      = { ...next }
        activeSlot.value = 'a'
      }
    }

    function startTimer() {
      stopTimer()
      if (items.value.length <= 1) return
      timer = setInterval(advance, props.interval)
    }

    function stopTimer() {
      if (timer) { clearInterval(timer); timer = null }
    }

    watch(() => props.enabled, (val) => {
      if (val) startTimer()
      else stopTimer()
    })

    onMounted(async () => {
      await fetchAnnouncements()
      if (props.enabled) startTimer()
    })

    onBeforeUnmount(stopTimer)

    return { items, activeSlot, slotA, slotB, barStyle }
  },
}
</script>

<style scoped>
.ab-bar {
  font-family: "Poppins", sans-serif;
  background: var(--ab-bg, #000);
  color: var(--ab-text, #fff);
  width: 100%;
  /* Tidak ada overflow:hidden — biar tinggi ikut konten */
}

.ab-track {
  width: 100%;
  padding: 8px 20px;
  box-sizing: border-box;
  text-align: center;
}

/*
  Wrapper pakai position:relative.
  Tingginya otomatis = tinggi slot yang sedang 'on' (in normal flow).
  Slot 'off' di-absolute supaya tidak nambahin tinggi wrapper.
*/
.ab-transition-wrap {
  position: relative;
  width: 100%;
}

/* Slot visible: normal flow, mendorong tinggi wrapper */
.slot--on {
  position: relative;
  opacity: 1;
  transform: translateX(0);
  pointer-events: auto;
  transition: opacity 0.35s ease, transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Slot hidden: absolute supaya tidak makan space, tapi tetap di DOM */
.slot--off {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  opacity: 0;
  transform: translateX(40%);
  pointer-events: none;
  transition: opacity 0.35s ease, transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.ab-item {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  flex-wrap: wrap;
  width: 100%;
}

.ab-text {
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 0.01em;
  white-space: normal;
  text-align: center;
  word-break: break-word;
  line-height: 1.5;
}

.ab-link {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-size: 11px;
  font-weight: 700;
  color: inherit;
  opacity: 0.8;
  text-decoration: underline;
  text-underline-offset: 2px;
  transition: opacity .15s;
  flex-shrink: 0;
}
.ab-link:hover { opacity: 1; }

@media (max-width: 480px) {
  .ab-track { padding: 10px 24px; }
  .ab-text  { font-size: 12px; }
}
</style>