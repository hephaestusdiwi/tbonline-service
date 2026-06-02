<template>
  <!-- Tidak dirender kalau tidak ada pengumuman -->
  <div v-if="items.length" class="ab-bar" :style="barStyle">

    <!-- Tombol prev (hanya muncul kalau > 1 item) -->
    <button v-if="items.length > 1" class="ab-arrow" @click="prev" aria-label="Sebelumnya">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
    </button>

    <!-- Teks berputar -->
    <div class="ab-track">
      <div class="ab-transition-wrap">
        <Transition :name="transitionName" mode="out-in">
          <div :key="current" class="ab-item">
            <span class="ab-text">{{ items[current].text }}</span>
            <a
              v-if="items[current].link_url"
              :href="items[current].link_url"
              target="_blank"
              rel="noopener"
              class="ab-link"
            >
              {{ items[current].link_label || 'Selengkapnya' }}
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
            </a>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Tombol next -->
    <button v-if="items.length > 1" class="ab-arrow" @click="next" aria-label="Selanjutnya">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="9 18 15 12 9 6"/>
      </svg>
    </button>

    <!-- Dots indikator -->
    <div v-if="items.length > 1" class="ab-dots">
      <button
        v-for="(_, i) in items"
        :key="i"
        :class="['ab-dot', i === current ? 'ab-dot--active' : '']"
        @click="goTo(i)"
        :aria-label="`Pengumuman ${i + 1}`"
      />
    </div>

  </div>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import axios from '../axios.js'

export default {
  name: 'AnnouncementBar',

  props: {
    bgColor:   { type: String, default: '#000000' },
    textColor: { type: String, default: '#ffffff' },
    interval:  { type: Number, default: 4000 },
  },

  setup(props) {
    const items          = ref([])
    const current        = ref(0)
    const transitionName = ref('ab-slide-left')
    let timer            = null

    const barStyle = computed(() => ({
      '--ab-bg':   props.bgColor,
      '--ab-text': props.textColor,
    }))

    async function fetchAnnouncements() {
      try {
        const res = await axios.get('/announcements')
        items.value = res.data ?? []
      } catch (e) {
        console.warn('Announcement fetch failed:', e)
      }
    }

    function startTimer() {
      stopTimer() // pastikan tidak double timer
      if (items.value.length <= 1) return
      timer = setInterval(() => {
        // Guard: jangan update kalau items kosong
        if (!items.value.length) return
        transitionName.value = 'ab-slide-left'
        current.value = (current.value + 1) % items.value.length
      }, props.interval)
    }

    function stopTimer() {
      if (timer) {
        clearInterval(timer)
        timer = null
      }
    }

    function next() {
      stopTimer()
      transitionName.value = 'ab-slide-left'
      current.value = (current.value + 1) % items.value.length
      startTimer()
    }

    function prev() {
      stopTimer()
      transitionName.value = 'ab-slide-right'
      current.value = (current.value - 1 + items.value.length) % items.value.length
      startTimer()
    }

    function goTo(i) {
      stopTimer()
      transitionName.value = i > current.value ? 'ab-slide-left' : 'ab-slide-right'
      current.value = i
      startTimer()
    }

    onMounted(async () => {
      await fetchAnnouncements()
      startTimer()
    })

    // onBeforeUnmount lebih reliable di Composition API
    onBeforeUnmount(() => {
      stopTimer()
    })

    return {
      items, current, transitionName, barStyle,
      next, prev, goTo,
    }
  },
}
</script>

<style scoped>
/* ─── Container ─────────────────────────────────────────── */
.ab-bar {
  font-family: "Poppins", sans-serif;
  background: var(--ab-bg, #000);
  color: var(--ab-text, #fff);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 7px 16px;
  position: relative;
  min-height: 36px;
  overflow: hidden;
}

/* ─── Track & Wrapper ────────────────────────────────────── */
.ab-track {
  flex: 1;
  text-align: center;
  overflow: hidden;
}

.ab-transition-wrap {
  position: relative;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ─── Item ───────────────────────────────────────────────── */
.ab-item {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  flex-wrap: wrap;
}

.ab-text {
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 0.01em;
  white-space: nowrap;
  overflow: hidden;
  white-space: normal;      /* ← ubah dari nowrap ke normal */
  overflow: visible;        /* ← ubah dari hidden ke visible */
  text-overflow: unset;     /* ← hapus ellipsis */
  text-align: center;       /* ← pastikan center */
  word-break: break-word;   /* ← tambahkan ini */
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

/* ─── Panah ──────────────────────────────────────────────── */
.ab-arrow {
  background: transparent;
  border: none;
  cursor: pointer;
  color: var(--ab-text, #fff);
  opacity: 0.6;
  padding: 4px;
  display: flex;
  align-items: center;
  transition: opacity .15s;
  flex-shrink: 0;
}
.ab-arrow:hover { opacity: 1; }

/* ─── Dots ───────────────────────────────────────────────── */
.ab-dots {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  gap: 4px;
}

.ab-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: var(--ab-text, #fff);
  opacity: 0.35;
  border: none;
  cursor: pointer;
  padding: 0;
  transition: opacity .2s, transform .2s;
}
.ab-dot--active {
  opacity: 1;
  transform: scale(1.3);
}

/* ─── Transisi slide — SATU definisi saja ────────────────── */
.ab-slide-left-enter-active,
.ab-slide-left-leave-active,
.ab-slide-right-enter-active,
.ab-slide-right-leave-active {
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  /* TIDAK pakai position: absolute */
}

.ab-slide-left-enter-from  { transform: translateX(60%);  opacity: 0; }
.ab-slide-left-leave-to    { transform: translateX(-60%); opacity: 0; }
.ab-slide-right-enter-from { transform: translateX(-60%); opacity: 0; }
.ab-slide-right-leave-to   { transform: translateX(60%);  opacity: 0; }

/* ─── Mobile ─────────────────────────────────────────────── */
@media (max-width: 480px) {
  .ab-bar {
    height: auto;          /* hapus fixed height */
    padding: 10px 12px;     /* beri ruang atas bawah */
  }
  .ab-dots  { display: none; }
  .ab-text  { font-size: 13px; } /* ← turunkan sedikit dari 14px */
  .ab-arrow { display: none; }
}
</style>