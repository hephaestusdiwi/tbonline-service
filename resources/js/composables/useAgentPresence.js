import { ref } from 'vue'
import axios from '@/axios.js'
import { getUser } from '@/auth.js'

const agentOnline = ref(false)
let pingInterval  = null
let initialized   = false

function canUsePresence() {
    try {
        return ['admin', 'manager',  'staff'].includes(getUser()?.role)
    } catch { return false }
}

async function fetchStatus() {
    try {
        const { data } = await axios.get('/me')
        agentOnline.value = data.is_online ?? false
    } catch {}
}

async function goOnline() {
    try {
        await axios.post('/agent/status/online')
        agentOnline.value = true
    } catch {}
}

async function goOffline() {
    try {
        await axios.post('/agent/status/offline')
        agentOnline.value = false
    } catch {}
}

export function useAgentPresence() {
    async function start() {
        if (!canUsePresence() || initialized)

        pingInterval = setInterval(() => {
            if (agentOnline.value) axios.post('/agent/status/online').catch(() => {})
        }, 60_000)

        window.addEventListener('beforeunload', () => {
            axios.post('/agent/status/offline').catch(() => {})
        })
    }

    async function toggle() {
        if (agentOnline.value) await goOffline()
        else await goOnline()
    }

    return { agentOnline, start, toggle }
}