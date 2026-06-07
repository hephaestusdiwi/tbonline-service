import { ref } from 'vue'
import axios from '@/axios.js'

let echoChannel = null
let titleInterval = null
let unreadCount = ref(0)

function playNotifSound() {
    try {
        const audio = new Audio('/sounds/notification.mp3')
        audio.volume = 0.5
        audio.play().catch(() => {})
    } catch {}
}

function startTitleBlink() {
    unreadCount.value++
    clearInterval(titleInterval)
    const original = document.title
    let show = true
    titleInterval = setInterval(() => {
        document.title = show ? `(${unreadCount.value}) 💬 Pesan Baru!` : original
        show = !show
    }, 1000)
}

function stopTitleBlink() {
    clearInterval(titleInterval)
    unreadCount.value = 0
    document.title = document.title.replace(/^\(\d+\) 💬 Pesan Baru! /, '')
}

function showBrowserNotif(message) {
    if (Notification.permission !== 'granted' || document.hasFocus()) return
    const notif = new Notification(`💬 ${message.sender_name || 'Visitor'}`, {
        body: message.content,
        icon: '/favicon.ico',
    })
    notif.onclick = () => { window.focus(); notif.close(); stopTitleBlink() }
    setTimeout(() => notif.close(), 5000)
}

export function useChatNotification() {

    async function subscribe() {
        console.log('🔔 subscribe dipanggil')
        if (!window.Echo) return

        if ('Notification' in window && Notification.permission === 'default') {
            await Notification.requestPermission()
        }

        try {
            const { data } = await axios.get('/chat/sessions')
            const sessions = data.data || data
            console.log('📋 sessions:', sessions.length)

            // Subscribe semua session, bukan cuma active/queued
            sessions.forEach(s => {
                console.log('📡 subscribe session:', s.uuid, s.status)
                subscribeSession(s.uuid)
            })
        } catch (e) {
            console.error('❌ gagal fetch sessions:', e)
        }

        window.Echo.channel('queue.admin')
            .listen('.customer.queued', (e) => {
                if (e.session?.uuid) subscribeSession(e.session.uuid)
                playNotifSound()
                startTitleBlink()
                showBrowserNotif({ sender_name: e.session?.guest_name, content: 'Visitor baru masuk antrian' })
            })
    }

    function subscribeSession(uuid) {
        if (!window.Echo) return
        window.Echo.channel(`chat.session.${uuid}`)
            .listen('.message.sent', (e) => {
                if (e.message?.sender_type === 'customer') {
                    playNotifSound()
                    startTitleBlink()
                    showBrowserNotif(e.message)
                }
            })
            .listen('.visitor.left', (e) => {
                playNotifSound()
                startTitleBlink()
                if (Notification.permission === 'granted' && !document.hasFocus()) {
                    const notif = new Notification(`👋 ${e.guest_name || 'Visitor'}`, {
                        body: 'Visitor telah meninggalkan obrolan',
                        icon: '/favicon.ico',
                    })
                    setTimeout(() => notif.close(), 5000)
                }
            })
    }

    function unsubscribe() {
        stopTitleBlink()
        window.Echo?.leave('queue.admin')
    }

    return { subscribe, unsubscribe, stopTitleBlink, unreadCount }
}