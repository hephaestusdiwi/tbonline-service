// src/stores/chat.js — Pinia
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useChatStore = defineStore('chat', () => {
  const sessions    = ref({})   // { [uuid]: { session, messages, typingUsers } }
  const queueCount  = ref(0)

  function initSession(uuid) {
    if (!sessions.value[uuid]) {
      sessions.value[uuid] = { messages: [], typingUsers: {}, assignedAgent: null, status: null }
    }
  }

  function addMessage(uuid, message) {
    initSession(uuid)
    const exists = sessions.value[uuid].messages.find(m => m.id === message.id)
    if (!exists) sessions.value[uuid].messages.push(message)
  }

  function setMessages(uuid, messages) {
    initSession(uuid)
    sessions.value[uuid].messages = messages
  }

  function setTyping(uuid, userId, name) {
    initSession(uuid)
    sessions.value[uuid].typingUsers[userId] = name
  }

  function clearTyping(uuid, userId) {
    delete sessions.value[uuid]?.typingUsers[userId]
  }

  function setAssignedAgent(uuid, agent) {
    if (sessions.value[uuid]) sessions.value[uuid].assignedAgent = agent
  }

  function setSessionStatus(uuid, status) {
    if (sessions.value[uuid]) sessions.value[uuid].status = status
  }

  function markMessagesRead(uuid, userId) {
    sessions.value[uuid]?.messages.forEach(m => {
      if (m.sender_id !== userId) m.status = 'read'
    })
  }

  const typingLabels = (uuid) => computed(() => {
    const users = Object.values(sessions.value[uuid]?.typingUsers ?? {})
    if (!users.length) return ''
    return users.length === 1 ? `${users[0]} sedang mengetik...` : 'Beberapa orang sedang mengetik...'
  })

  return { sessions, queueCount, addMessage, setMessages, setTyping, clearTyping, setAssignedAgent, setSessionStatus, markMessagesRead, typingLabels }
})