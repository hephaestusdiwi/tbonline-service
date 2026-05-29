import { ref, computed } from 'vue'
import { useChatStore } from '@/store/chat'
import echo from '@/plugins/echo'
import api from '@/plugins/api'

export function useChat(sessionUuid) {
  const store      = useChatStore()
  const isLoading  = ref(false)
  const isSending  = ref(false)
  let typingTimer  = null

  // Subscribe to private session channel
  const channel = echo.private(`session.${sessionUuid}`)

  channel
    .listen('.message.sent', ({ message }) => {
      store.addMessage(sessionUuid, message)
      markReadIfVisible(message)
    })
    .listen('.typing.started', ({ user_id, name }) => {
      store.setTyping(sessionUuid, user_id, name)
    })
    .listen('.typing.stopped', ({ user_id }) => {
      store.clearTyping(sessionUuid, user_id)
    })
    .listen('.message.read', ({ user_id }) => {
      store.markMessagesRead(sessionUuid, user_id)
    })
    .listen('.chat.assigned', ({ agent }) => {
      store.setAssignedAgent(sessionUuid, agent)
    })
    .listen('.session.closed', () => {
      store.setSessionStatus(sessionUuid, 'closed')
    })

  async function loadMessages() {
    isLoading.value = true
    const { data } = await api.get(`/chat/sessions/${sessionUuid}/messages`)
    store.setMessages(sessionUuid, data.data)
    isLoading.value = false
  }

  async function sendMessage(content, attachments = []) {
    isSending.value = true

    const formData = new FormData()
    if (content) formData.append('content', content)
    attachments.forEach(f => formData.append('attachments[]', f))

    try {
      await api.post(`/chat/sessions/${sessionUuid}/messages`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      stopTyping()
    } finally {
      isSending.value = false
    }
  }

  function onTyping() {
    clearTimeout(typingTimer)
    api.post(`/chat/sessions/${sessionUuid}/typing`)
    typingTimer = setTimeout(() => stopTyping(), 2500)
  }

  function stopTyping() {
    clearTimeout(typingTimer)
    api.delete(`/chat/sessions/${sessionUuid}/typing`)
  }

  async function markRead() {
    await api.post(`/chat/sessions/${sessionUuid}/messages/read`)
  }

  function markReadIfVisible(message) {
    if (document.visibilityState === 'visible') markRead()
  }

  function leave() {
    echo.leave(`session.${sessionUuid}`)
    stopTyping()
  }

  return { isLoading, isSending, loadMessages, sendMessage, onTyping, stopTyping, markRead, leave }
}