import { onMounted, onBeforeMount } from 'vue'
import axios from '../axios'

function getSessionId() {
    const match = document.cookie.match(/(?:^|;\s*)_vid=([^;]+)/)
    if (match) return match[1]

    const id = crypto.randomUUID
        ? crypto.randomUUID()
        : Math.random().toString(36).substring(2) + Date.now().toString(36)

     const exp = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toUTCString()
     document.cookie = `_vid=${id}; expires=${exp}; path=/; SameSite=Lax`
     return id
}

export function useVisitorTracker(options = {}) {
    let startTime = Date.now()
    let sessionId = null
    let currentPage = null
    let sent = false

    async function pingVisit() {
        sessionId   = getSessionId()
        currentPage = options.page ?? window.location.pathname

        try {
            await axios.post('/visitor/ping', {
                page:       currentPage,
                page_title: options.pageTitle ?? document.title,
                referrer:   document.referrer ?? '',
            })
        } catch {

        }
    }

    async function sendTime() {
        if (sent || !sessionId) return
        sent = true

        const seconds = Math.round((Date.now() - startTime) / 1000)
        if (seconds < 1) return

        const payload = JSON.stringify({
            session_id: sessionId,
            page:       currentPage,
            seconds,
            _method:    'PATCH',
        })

        const beaconUrl = (import.meta.env.VITE_API_BASE_URL ?? '/api') + '/visitor/time'

        if (navigator.sendBeacon) {
            navigator.sendBeacon(beaconUrl, new Blob([payload], { type: 'application/json' }))
        } else {
            // Fallback: axios (may be cancelled on page unload)
            try {
                await axios.patch('/visitor/time', { session_id: sessionId, page: currentPage, seconds })
            } catch {}
        }
    }

    function handleVisibility() {
        if (document.visibilityState === 'hidden') {
            sendTime()
        } else {
            startTime = Date.now()
            sent      = false
        }
    }

    onMounted(() => {
        pingVisit()
        document.addEventListener('visibilitychange', handleVisibility)
        window.addEventListener('beforeunload', sendTime)
    })

    onBeforeMount(() => {
        sendTime()
        document.removeEventListener('visibilitychange', handleVisibility)
        window.removeEventListener('beforeunload', sendTime)
    })
}