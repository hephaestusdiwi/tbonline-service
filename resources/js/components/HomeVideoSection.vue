<!-- src/components/HomeVideoSection.vue -->
<template>
  <section
    class="hv-section"
    v-if="!loading && video && video.is_active && video.video_url"
  >
    <!-- =====================================================
         VIDEO ONLY
         ===================================================== -->
    <div
      v-if="slotName === 'video_only'"
      class="hv-container hv-container-only"
    >
      <div class="hv-video-wrap hv-video-wrap-only">
        <video
          ref="videoEl"
          :src="video.video_url"
          autoplay
          muted
          playsinline
          preload="metadata"
          class="hv-video hv-video-only"
          @play="handlePlay"
          @pause="handlePause"
          @ended="handleEnded"
        ></video>

        <!-- Custom pause/play -->
        <button
          class="hv-simple-control"
          type="button"
          @click="toggleVideo"
          :aria-label="isPlaying ? 'Pause video' : 'Play video'"
        >
          <span v-if="isPlaying" class="pause-icon">
            <span></span>
            <span></span>
          </span>

          <span v-else class="play-icon"></span>
        </button>
      </div>
    </div>

    <!-- =====================================================
         VIDEO WITH CAPTION
         ===================================================== -->
    <div
      v-else-if="slotName === 'video_with_caption'"
      class="hv-container hv-container-caption"
    >
      <div class="hv-caption-layout">

        <!-- VIDEO -->
        <div class="hv-video-wrap hv-video-wrap-caption">
          <video
            ref="videoEl"
            :src="video.video_url"
            autoplay
            muted
            playsinline
            preload="metadata"
            class="hv-video hv-video-caption"
            @loadedmetadata="handleMetadata"
            @timeupdate="handleTimeUpdate"
            @play="handlePlay"
            @pause="handlePause"
            @ended="handleEnded"
          ></video>

          <!-- Dark overlay -->
          <div class="hv-video-overlay"></div>

          <!-- Center controls -->
          <div class="hv-center-controls">
            <!-- Previous -->
            <button
              type="button"
              class="hv-control hv-control-side"
              @click="restartVideo"
              aria-label="Restart video"
            >
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <polygon points="19 20 9 12 19 4 19 20"></polygon>
                <line x1="5" y1="19" x2="5" y2="5"></line>
              </svg>
            </button>

            <!-- Play / Pause -->
            <button
              type="button"
              class="hv-control hv-control-main"
              @click="toggleVideo"
              :aria-label="isPlaying ? 'Pause video' : 'Play video'"
            >
              <span v-if="isPlaying" class="pause-icon large">
                <span></span>
                <span></span>
              </span>

              <span v-else class="play-icon large"></span>
            </button>

            <!-- Next -->
            <button
              type="button"
              class="hv-control hv-control-side"
              @click="restartVideo"
              aria-label="Replay video"
            >
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <polygon points="5 4 15 12 5 20 5 4"></polygon>
                <line x1="19" y1="5" x2="19" y2="19"></line>
              </svg>
            </button>
          </div>

          <!-- Progress -->
          <div class="hv-progress-wrap">
            <input
              type="range"
              class="hv-progress"
              min="0"
              :max="duration || 0"
              step="0.01"
              :value="currentTime"
              @input="seekVideo"
              aria-label="Video progress"
            />
          </div>
        </div>

        <!-- CAPTION -->
        <div
          v-if="video.title || video.description"
          class="hv-caption"
        >
          <h2
            v-if="video.title"
            class="hv-title"
          >
            {{ video.title }}
          </h2>

          <p
            v-if="video.description"
            class="hv-desc"
          >
            {{ video.description }}
          </p>
        </div>

      </div>
    </div>
  </section>
</template>

<script>
import axiosInstance from '../axios'

export default {
  name: 'HomeVideoSection',

  props: {
    slotName: {
      type: String,
      required: true,
      validator: (v) =>
        ['video_only', 'video_with_caption'].includes(v),
    },
  },

  data() {
    return {
      video: null,
      loading: true,

      isPlaying: false,
      currentTime: 0,
      duration: 0,
    }
  },

  async mounted() {
    try {
      const { data } = await axiosInstance.get('/home-videos')

      const list = data.data ?? data

      this.video =
        list.find((v) => v.slot === this.slotName) || null

      /*
       * Tunggu video masuk ke DOM.
       */
      this.$nextTick(() => {
        this.setupVideo()
      })
    } catch (e) {
      console.error('Failed to load home video:', e)
    } finally {
      this.loading = false
    }
  },

  methods: {
    setupVideo() {
      const video = this.$refs.videoEl

      if (!video) return

      /*
       * Pastikan muted untuk autoplay.
       */
      video.muted = true

      /*
       * Coba autoplay.
       * Browser tertentu tetap bisa menolak autoplay,
       * jadi kita tangkap error-nya tanpa merusak halaman.
       */
      video
        .play()
        .then(() => {
          this.isPlaying = true
        })
        .catch(() => {
          this.isPlaying = false
        })
    },

    toggleVideo() {
      const video = this.$refs.videoEl

      if (!video) return

      if (video.paused) {
        video
          .play()
          .then(() => {
            this.isPlaying = true
          })
          .catch(() => {
            this.isPlaying = false
          })
      } else {
        video.pause()
        this.isPlaying = false
      }
    },

    handlePlay() {
      this.isPlaying = true
    },

    handlePause() {
      this.isPlaying = false
    },

    handleEnded() {
      this.isPlaying = false
    },

    handleMetadata(event) {
      this.duration = event.target.duration || 0
    },

    handleTimeUpdate(event) {
      this.currentTime = event.target.currentTime || 0
    },

    seekVideo(event) {
      const video = this.$refs.videoEl

      if (!video) return

      video.currentTime = Number(event.target.value)
      this.currentTime = video.currentTime
    },

    restartVideo() {
      const video = this.$refs.videoEl

      if (!video) return

      video.currentTime = 0

      video
        .play()
        .then(() => {
          this.isPlaying = true
        })
        .catch(() => {
          this.isPlaying = false
        })
    },
  },
}
</script>

<style scoped>
/* =========================================================
   SECTION
   ========================================================= */

.hv-section {
  padding: 32px 0;
  font-family: "Poppins", sans-serif;
  background: #fff;
}

/* =========================================================
   CONTAINER
   ========================================================= */

.hv-container {
  width: 100%;
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 16px;
  box-sizing: border-box;
}

/* =========================================================
   VIDEO COMMON
   ========================================================= */

.hv-video-wrap {
  position: relative;
  overflow: hidden;
  border-radius: 16px;
  background: #000;
}

.hv-video {
  display: block;
  width: 100%;
}

/* =========================================================
   VIDEO ONLY
   Portrait / full height / no black background
   ========================================================= */

.hv-video-wrap-only {
  background: transparent;
}

.hv-video-wrap-only {
  width: 100%;
  aspect-ratio: 9 / 16;
  background: #000;
  overflow: hidden;
  border-radius: 24px;
}

.hv-video-only {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

@media (max-width: 768px) {
  .hv-container-only {
    padding: 0 12px;
  }

  .hv-video-wrap-only {
    width: 100%;
    aspect-ratio: 9 / 16;
    border-radius: 24px;
  }

  .hv-video-only {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}

/* Simple control kanan bawah */
.hv-simple-control {
  position: absolute;
  right: 22px;
  bottom: 22px;

  width: 56px;
  height: 56px;

  padding: 0;
  border: none;
  border-radius: 50%;

  background: rgba(255, 255, 255, 0.95);
  color: #222;

  display: flex;
  align-items: center;
  justify-content: center;

  cursor: pointer;

  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);

  z-index: 5;
}

/* =========================================================
   VIDEO WITH CAPTION
   ========================================================= */

.hv-container-caption {
  max-width: 1200px;
}

.hv-caption-layout {
  display: grid;
  grid-template-columns: minmax(0, 3fr) minmax(250px, 1fr);
  align-items: center;
  gap: 35px;
}

/* Video bawah */
.hv-video-wrap-caption {
  width: 100%;
  aspect-ratio: 16 / 9;
  background: #000;
}

.hv-video-caption {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* =========================================================
   VIDEO OVERLAY
   ========================================================= */

.hv-video-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;

  background: rgba(0, 0, 0, 0.08);
}

/* =========================================================
   CENTER CONTROLS
   ========================================================= */

.hv-center-controls {
  position: absolute;
  inset: 0;

  display: flex;
  align-items: center;
  justify-content: center;
  gap: 28px;

  pointer-events: none;
}

.hv-control {
  border: none;
  padding: 0;

  color: #fff;

  display: flex;
  align-items: center;
  justify-content: center;

  cursor: pointer;

  pointer-events: auto;
}

.hv-control-side {
  width: 52px;
  height: 52px;

  border-radius: 50%;

  background: rgba(0, 0, 0, 0.30);
}

.hv-control-side svg {
  width: 25px;
  height: 25px;
}

.hv-control-main {
  width: 72px;
  height: 72px;

  border-radius: 50%;

  background: rgba(0, 0, 0, 0.35);
}

/* =========================================================
   PLAY / PAUSE ICON
   ========================================================= */

.pause-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
}

.pause-icon span {
  width: 4px;
  height: 17px;
  border-radius: 1px;
  background: currentColor;
}

.pause-icon.large {
  gap: 7px;
}

.pause-icon.large span {
  width: 6px;
  height: 28px;
}

.play-icon {
  width: 0;
  height: 0;

  margin-left: 3px;

  border-top: 9px solid transparent;
  border-bottom: 9px solid transparent;
  border-left: 13px solid currentColor;
}

.play-icon.large {
  margin-left: 5px;

  border-top-width: 13px;
  border-bottom-width: 13px;
  border-left-width: 19px;
}

/* =========================================================
   PROGRESS BAR
   ========================================================= */

.hv-progress-wrap {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;

  height: 24px;

  display: flex;
  align-items: center;

  padding: 0 0;

  z-index: 4;
}

.hv-progress {
  width: 100%;
  height: 4px;

  margin: 0;

  appearance: none;
  -webkit-appearance: none;

  background: rgba(255, 255, 255, 0.45);

  cursor: pointer;
}

/* Chrome / Edge / Safari */
.hv-progress::-webkit-slider-runnable-track {
  height: 4px;
  background: linear-gradient(
    to right,
    #e51f3b 0%,
    #e51f3b var(--progress, 0%),
    rgba(255, 255, 255, 0.45) var(--progress, 0%),
    rgba(255, 255, 255, 0.45) 100%
  );
}

.hv-progress::-webkit-slider-thumb {
  appearance: none;
  -webkit-appearance: none;

  width: 16px;
  height: 16px;

  margin-top: -6px;

  border: none;
  border-radius: 50%;

  background: #e51f3b;

  cursor: pointer;
}

/* Firefox */
.hv-progress::-moz-range-track {
  height: 4px;
  background: rgba(255, 255, 255, 0.45);
}

.hv-progress::-moz-range-progress {
  height: 4px;
  background: #e51f3b;
}

.hv-progress::-moz-range-thumb {
  width: 16px;
  height: 16px;

  border: none;
  border-radius: 50%;

  background: #e51f3b;

  cursor: pointer;
}

/* =========================================================
   CAPTION
   ========================================================= */

.hv-caption {
  text-align: left;
}

.hv-title {
  font-size: 1.55rem;
  font-weight: 400;
  line-height: 1.25;
  color: #222;
  margin: 0 0 22px;
}

.hv-desc {
  font-size: 0.82rem;
  font-weight: 400;
  color: #555;
  line-height: 1.5;
  margin: 0;
}

/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 768px) {
  .hv-section {
    padding: 18px 0 24px;
  }

  .hv-container {
    padding: 0 12px;
  }

  /*
   * Mobile: video + caption ditumpuk rapat seperti layout Figma.
   */
  .hv-caption-layout {
    padding-top: 50px;
    grid-template-columns: 1fr;
    gap: 10px;
    align-items: start;
  }

  .hv-video-wrap-caption {
    width: 100%;
    aspect-ratio: 0.92 / 1;
    border-radius: 12px;
  }

  .hv-caption {
    text-align: left;
    padding: 30px 5px;
  }

  .hv-title {
    font-size: 0.98rem;
    font-weight: 500;
    line-height: 1.25;
    margin: 0 0 8px;
  }

  .hv-desc {
    font-size: 0.64rem;
    line-height: 1.55;
    color: #555;
    margin: 0;
  }

  .hv-control-side {
    width: 38px;
    height: 38px;
  }

  .hv-control-side svg {
    width: 18px;
    height: 18px;
  }

  .hv-control-main {
    width: 52px;
    height: 52px;
  }

  .pause-icon.large span {
    width: 4px;
    height: 19px;
  }

  .play-icon.large {
    border-top-width: 10px;
    border-bottom-width: 10px;
    border-left-width: 15px;
  }

  .hv-center-controls {
    gap: 14px;
  }

  .hv-progress-wrap {
    height: 18px;
  }

  .hv-progress {
    height: 3px;
  }

  .hv-progress::-webkit-slider-runnable-track {
    height: 3px;
  }

  .hv-progress::-webkit-slider-thumb {
    width: 12px;
    height: 12px;
    margin-top: -4.5px;
  }

  .hv-progress::-moz-range-track,
  .hv-progress::-moz-range-progress {
    height: 3px;
  }

  .hv-progress::-moz-range-thumb {
    width: 12px;
    height: 12px;
  }
}

@media (max-width: 480px) {
  .hv-caption-layout {
    gap: 9px;
  }

  .hv-video-wrap-caption {
    aspect-ratio: 0.90 / 1;
    border-radius: 10px;
  }

  .hv-title {
    font-size: 1.25rem;
    margin-bottom: 6px;
  }

  .hv-desc {
    font-size: 0.70rem;
    line-height: 1.7;
  }

  .hv-control-side {
    width: 34px;
    height: 34px;
  }

  .hv-control-side svg {
    width: 17px;
    height: 17px;
  }

  .hv-control-main {
    width: 48px;
    height: 48px;
  }

  .hv-center-controls {
    gap: 11px;
  }
}
</style>