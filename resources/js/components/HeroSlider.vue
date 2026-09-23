<template>
    <div
        class="relative w-full overflow-hidden bg-gray-100
            h-[420px] sm:h-[15vh] md:h-[25vh] lg:h-[75vh]"
    >

        <!-- Loading -->
        <div
            v-if="loading"
            class="flex items-center justify-center h-full"
        >
            <p class="text-gray-400 text-sm">
                Memuat slider...
            </p>
        </div>

        <!-- Tidak ada slider -->
        <div
            v-else-if="sliders.length === 0"
            class="flex items-center justify-center h-full"
        >
            <p class="text-gray-400 text-sm">
                Belum ada slider
            </p>
        </div>

        <!-- Slider -->
        <div
            v-else
            class="relative w-full h-full"
        >

            <!-- Track -->
            <div
                class="flex h-full"
                :style="{
                    transform: `translateX(-${currentIndex * 100}%)`,
                    transition: 'transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94)'
                }"
            >
                <div
                    v-for="slider in sliders"
                    :key="slider.id"
                    class="min-w-full h-full flex-shrink-0 relative overflow-hidden"
                >

                    <!-- Image -->
                    <picture v-if="slider.type === 'image'">

                        <!-- Mobile image -->
                        <source
                            v-if="slider.file_url_mobile"
                            media="(max-width: 767px)"
                            :srcset="slider.file_url_mobile"
                        />

                        <img
                            :src="slider.file_url"
                            :alt="slider.title"
                            class="absolute inset-0 w-full h-full"
                            style="
                                object-fit: cover;
                                object-position: center;
                            "
                        />
                    </picture>

                    <!-- Video -->
                    <video
                        v-else
                        :ref="'video_' + slider.id"
                        :src="slider.file_url"
                        class="absolute inset-0 w-full h-full"
                        style="
                            object-fit: cover;
                            object-position: center;
                        "
                        autoplay
                        muted
                        playsinline
                        preload="auto"
                        @ended="next"
                    />
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- DESKTOP CONTROLS -->
            <!-- ========================================================= -->
            <div
                class="
                    hidden md:flex
                    absolute
                    bottom-6
                    left-1/2
                    -translate-x-1/2
                    items-center
                    gap-2
                    z-20
                "
            >

                <!-- Prev + Counter + Next -->
                <div
                    class="flex items-center rounded-full overflow-hidden"
                    style="
                        background: rgba(255,255,255,0.85);
                        backdrop-filter: blur(6px);
                        border: 1px solid rgba(0,0,0,0.08);
                    "
                >

                    <!-- Prev -->
                    <button
                        type="button"
                        @click="prev"
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            transition
                            hover:bg-black/5
                        "
                        aria-label="Slider sebelumnya"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="12"
                            height="12"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="#333333"
                            stroke-width="2.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </button>

                    <!-- Divider -->
                    <div
                        style="
                            width: 1px;
                            height: 16px;
                            background: rgba(0,0,0,0.12);
                        "
                    ></div>

                    <!-- Counter -->
                    <div
                        class="
                            text-xs
                            font-semibold
                            px-3
                            h-8
                            flex
                            items-center
                            justify-center
                        "
                        style="
                            color: #333333;
                            min-width: 42px;
                        "
                    >
                        {{ currentIndex + 1 }}/{{ sliders.length }}
                    </div>

                    <!-- Divider -->
                    <div
                        style="
                            width: 1px;
                            height: 16px;
                            background: rgba(0,0,0,0.12);
                        "
                    ></div>

                    <!-- Next -->
                    <button
                        type="button"
                        @click="next"
                        class="
                            w-8 h-8
                            flex items-center justify-center
                            transition
                            hover:bg-black/5
                        "
                        aria-label="Slider berikutnya"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="12"
                            height="12"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="#333333"
                            stroke-width="2.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Pause / Play -->
                <button
                    type="button"
                    @click="toggleAutoPlay"
                    class="
                        w-8 h-8
                        rounded-full
                        flex items-center justify-center
                        transition
                        hover:bg-black/5
                    "
                    style="
                        background: rgba(255,255,255,0.85);
                        backdrop-filter: blur(6px);
                        border: 1px solid rgba(0,0,0,0.08);
                    "
                    :aria-label="isPlaying ? 'Pause slider' : 'Putar slider'"
                >

                    <!-- Pause -->
                    <svg
                        v-if="isPlaying"
                        xmlns="http://www.w3.org/2000/svg"
                        width="11"
                        height="11"
                        fill="#333333"
                        viewBox="0 0 24 24"
                    >
                        <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                    </svg>

                    <!-- Play -->
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        width="11"
                        height="11"
                        fill="#333333"
                        viewBox="0 0 24 24"
                    >
                        <path d="M8 5v14l11-7z" />
                    </svg>

                </button>
            </div>


            <!-- ========================================================= -->
            <!-- MOBILE DOT INDICATOR -->
            <!-- ========================================================= -->
            <div
                class="
                    md:hidden
                    absolute
                    top-3
                    left-1/2
                    -translate-x-1/2
                    flex
                    items-center
                    gap-1.5
                    z-20
                "
            >
                <button
                    v-for="(slider, index) in sliders"
                    :key="slider.id"
                    type="button"
                    @click="goToSlide(index)"
                    class="
                        h-1.5
                        rounded-full
                        transition-all
                        duration-300
                    "
                    :class="
                        index === currentIndex
                            ? 'w-5 bg-white'
                            : 'w-1.5 bg-white/60'
                    "
                    :aria-label="`Ke slide ${index + 1}`"
                ></button>
            </div>

        </div>
    </div>
</template>


<script>
import axios from '../axios.js'
import '../../css/heroslider.css'

export default {
    name: 'HeroSlider',

    data() {
        return {
            sliders: [],
            currentIndex: 0,
            loading: true,

            timer: null,

            isPlaying: true,

            // Durasi image sebelum pindah ke slider berikutnya
            duration: 4000,
        }
    },

    watch: {
        currentIndex(newIndex) {
            const current = this.sliders[newIndex]

            if (!current || current.type !== 'video') {
                return
            }

            this.$nextTick(() => {
                const videoRef = this.$refs['video_' + current.id]

                const video = Array.isArray(videoRef)
                    ? videoRef[0]
                    : videoRef

                if (!video) {
                    return
                }

                video.currentTime = 0

                const playPromise = video.play()

                if (playPromise !== undefined) {
                    playPromise.catch(() => {
                        // Browser bisa menolak autoplay.
                    })
                }
            })
        },
    },

    async mounted() {
        await this.fetchSliders()

        if (this.sliders.length > 0) {
            this.startAutoPlay()
        }
    },

    beforeUnmount() {
        this.stopAutoPlay()
    },

    methods: {

        /**
         * Ambil data slider
         */
        async fetchSliders() {
            try {
                const response = await axios.get('/sliders')

                this.sliders = response.data.filter(
                    slider => slider.is_active
                )

                // Pastikan index tetap valid
                if (
                    this.currentIndex >= this.sliders.length
                ) {
                    this.currentIndex = 0
                }

            } catch (e) {
                console.error('Gagal mengambil slider:', e)
            } finally {
                this.loading = false
            }
        },


        /**
         * Slider berikutnya
         */
        next() {
            if (this.sliders.length === 0) {
                return
            }

            this.currentIndex =
                (this.currentIndex + 1) %
                this.sliders.length

            this.restartAutoPlay()
        },


        /**
         * Slider sebelumnya
         */
        prev() {
            if (this.sliders.length === 0) {
                return
            }

            this.currentIndex =
                (this.currentIndex - 1 + this.sliders.length) %
                this.sliders.length

            this.restartAutoPlay()
        },


        /**
         * Pindah langsung ke slide tertentu
         * Dipakai oleh dot indicator mobile
         */
        goToSlide(index) {
            if (
                this.sliders.length === 0 ||
                index < 0 ||
                index >= this.sliders.length
            ) {
                return
            }

            if (index === this.currentIndex) {
                return
            }

            this.currentIndex = index

            this.restartAutoPlay()
        },


        /**
         * Start autoplay
         *
         * Image:
         * pindah setiap 4 detik
         *
         * Video:
         * menunggu event @ended
         */
        startAutoPlay() {
            if (
                !this.isPlaying ||
                this.sliders.length === 0
            ) {
                return
            }

            this.stopAutoPlay()

            const current = this.sliders[this.currentIndex]

            // Video dikontrol melalui @ended
            if (current && current.type === 'video') {
                return
            }

            this.timer = setTimeout(() => {

                if (!this.isPlaying) {
                    return
                }

                if (this.sliders.length === 0) {
                    return
                }

                const current =
                    this.sliders[this.currentIndex]

                // Kalau sekarang video,
                // biarkan @ended yang memanggil next()
                if (
                    current &&
                    current.type === 'video'
                ) {
                    return
                }

                this.currentIndex =
                    (this.currentIndex + 1) %
                    this.sliders.length

                this.startAutoPlay()

            }, this.duration)
        },


        /**
         * Stop autoplay
         */
        stopAutoPlay() {
            if (this.timer) {
                clearTimeout(this.timer)
                this.timer = null
            }
        },


        /**
         * Restart autoplay
         */
        restartAutoPlay() {
            this.stopAutoPlay()

            if (this.isPlaying) {
                this.startAutoPlay()
            }
        },


        /**
         * Toggle play / pause
         */
        toggleAutoPlay() {
            this.isPlaying = !this.isPlaying

            if (this.isPlaying) {
                this.startAutoPlay()
            } else {
                this.stopAutoPlay()
            }
        },
    }
}
</script>