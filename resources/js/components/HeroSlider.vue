<template>
    <div
        class="relative w-full overflow-hidden bg-gray-100
                h-auto sm:h-[65vh] md:h-[75vh] lg:h-[75vh]"
        :style="{ aspectRatio: currentRatio }"
        >

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center h-full">
            <p class="text-gray-400 text-sm">Memuat slider...</p>
        </div>

        <!-- Tidak ada slider -->
        <div v-else-if="sliders.length === 0" class="flex items-center justify-center h-full">
            <p class="text-gray-400 text-sm">Belum ada slider</p>
        </div>

        <!-- Slider -->
        <div v-else class="relative w-full h-full">

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
                    <img
                        v-if="slider.type === 'image'"
                        :src="slider.file_url"
                        :alt="slider.title"
                        class="absolute inset-0 w-full h-full"
                        style="object-fit: cover; object-position: center;"
                        @load="onMediaLoad($event, slider, 'img')"
                    />

                    <!-- Video -->
                    <video
                        v-else
                        :ref="'video_' + slider.id"
                        :src="slider.file_url"
                        class="absolute inset-0 w-full h-full"
                        style="object-fit: cover; object-position: center;"
                        autoplay
                        muted
                        playsinline
                        preload="auto"
                        @ended="next"
                        @loadedmetadata="onMediaLoad($event, slider, 'video')"
                    />
                </div>
            </div>

            <!-- Bottom Controls -->
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20">

                <!-- Pill: Prev + Counter + Next -->
                <div
                    class="flex items-center rounded-full overflow-hidden"
                    style="background: rgba(255,255,255,0.85); backdrop-filter: blur(6px); border: 1px solid rgba(0,0,0,0.08);"
                >
                    <!-- Prev -->
                    <button
                        @click="prev"
                        class="w-8 h-8 flex items-center justify-center transition hover:bg-black hover:bg-opacity-5"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#333333" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Divider -->
                    <div style="width:1px; height:16px; background: rgba(0,0,0,0.12);"></div>

                    <!-- Counter -->
                    <div class="text-xs font-semibold px-3 h-8 flex items-center justify-center" style="color: #333333; min-width: 42px;">
                        {{ currentIndex + 1 }}/{{ sliders.length }}
                    </div>

                    <!-- Divider -->
                    <div style="width:1px; height:16px; background: rgba(0,0,0,0.12);"></div>

                    <!-- Next -->
                    <button
                        @click="next"
                        class="w-8 h-8 flex items-center justify-center transition hover:bg-black hover:bg-opacity-5"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#333333" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- Pause/Play pill terpisah -->
                <button
                    @click="toggleAutoPlay"
                    class="w-8 h-8 rounded-full flex items-center justify-center transition hover:bg-black hover:bg-opacity-5"
                    style="background: rgba(255,255,255,0.85); backdrop-filter: blur(6px); border: 1px solid rgba(0,0,0,0.08);"
                >
                    <svg v-if="isPlaying" xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="#333333" viewBox="0 0 24 24">
                        <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="#333333" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </button>

            </div>

            <!-- Progress Bar -->
            <div class="absolute bottom-0 left-0 w-full h-1 bg-white bg-opacity-20">
                <div
                    class="h-full bg-white transition-all ease-linear"
                    :style="{ width: progressWidth + '%', transitionDuration: isPlaying ? '50ms' : '0ms' }"
                />
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
            progressTimer: null,
            isPlaying: true,
            progress: 0,
            duration: 4000,
            ratios: {},          
            defaultRatio: 16 / 9 
        }
    },
    computed: {
        progressWidth() {
            return (this.progress / this.duration) * 100
        },
        currentRatio() {
            const current = this.sliders[this.currentIndex]
            if (!current) return this.defaultRatio
            return this.ratios[current.id] || this.defaultRatio
        }
    },
    watch: {
        currentIndex(newIndex) {
            const current = this.sliders[newIndex]
            if (current && current.type === 'video') {
                this.$nextTick(() => {
                    const videoRef = this.$refs['video_' + current.id]
                    const video = Array.isArray(videoRef) ? videoRef[0] : videoRef
                    if (video) {
                        video.currentTime = 0
                        video.play()
                    }
                })
            }
        }
    },
    async mounted() {
        await this.fetchSliders()
        this.startAutoPlay()
    },
    beforeUnmount() {
        this.stopAutoPlay()
    },
    methods: {
        async fetchSliders() {
            try {
                const response = await axios.get('/sliders')
                this.sliders = response.data.filter(s => s.is_active)
            } catch (e) {
                console.error(e)
            } finally {
                this.loading = false
            }
        },

        next() {
            this.currentIndex = (this.currentIndex + 1) % this.sliders.length
            this.restartAutoPlay()
        },

        prev() {
            this.currentIndex = (this.currentIndex - 1 + this.sliders.length) % this.sliders.length
            this.restartAutoPlay()
        },

        startAutoPlay() {
            if (!this.isPlaying) return
            this.progress = 0

            this.progressTimer = setInterval(() => {
                const current = this.sliders[this.currentIndex]
                if (current && current.type === 'video') return
                this.progress += 50
            }, 50)

            this.timer = setInterval(() => {
                const current = this.sliders[this.currentIndex]
                if (current && current.type === 'video') return
                this.currentIndex = (this.currentIndex + 1) % this.sliders.length
                this.progress = 0
            }, this.duration)
        },

        stopAutoPlay() {
            clearInterval(this.timer)
            clearInterval(this.progressTimer)
            this.progress = 0
        },

        restartAutoPlay() {
            this.stopAutoPlay()
            if (this.isPlaying) this.startAutoPlay()
        },

        toggleAutoPlay() {
            this.isPlaying = !this.isPlaying
            if (this.isPlaying) {
                this.startAutoPlay()
            } else {
                this.stopAutoPlay()
            }
        },

        onMediaLoad(e, slider, type) {
            const el = e.target
            const ratio = type === 'video'
                ? el.videoWidth / el.videoHeight
                : el.naturalWidth / el.naturalHeight
            if (ratio) this.ratios[slider.id] = ratio
        }
    }
}
</script>