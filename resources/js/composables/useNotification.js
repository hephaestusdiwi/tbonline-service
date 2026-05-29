const SOUND_URL = '/sounds/notification.mp3'
let originalTitle = document.title
let titleInterval = null
let unreadCount = 0

// ── Suara ──────────────────────────────────────────────
function playSound() {
  try {
    const audio = new Audio(SOUND_URL)
    audio.volume = 0.5
    audio.play().catch(() => {}) // ignore autoplay block
  } catch {}
}

// ── Tab title badge ────────────────────────────────────
function startTitleBlink(message) {
  unreadCount++
  stopTitleBlink()
  let show = true
  titleInterval = setInterval(() => {
    document.title = show
      ? `(${unreadCount}) 💬 Pesan Baru!`
      : originalTitle
    show = !show
  }, 1000)
}

function stopTitleBlink() {
  clearInterval(titleInterval)
  titleInterval = null
  document.title = originalTitle
  unreadCount = 0
}

// ── Browser notification ───────────────────────────────
async function requestPermission() {
  if (!('Notification' in window)) return false
  if (Notification.permission === 'granted') return true
  if (Notification.permission === 'denied') return false
  const result = await Notification.requestPermission()
  return result === 'granted'
}

function showBrowserNotif(title, body, icon = '/favicon.ico') {
  if (Notification.permission !== 'granted') return
  const notif = new Notification(title, { body, icon })
  notif.onclick = () => {
    window.focus()
    notif.close()
    stopTitleBlink()
  }
  setTimeout(() => notif.close(), 5000)
}

// ── Main trigger ───────────────────────────────────────
async function notify({ title = 'Pesan Baru', body = '', icon }) {
  // Jangan notif kalau tab sedang aktif & focused
  if (document.hasFocus()) return

  playSound()
  startTitleBlink(body)
  await showBrowserNotif(title, body, icon)
}

export function useNotification() {
  return {
    notify,
    playSound,
    stopTitleBlink,
    requestPermission,
  }
}