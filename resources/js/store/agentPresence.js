import { defineStore } from 'pinia'
import axios from '@/axios.js'
import { getUser } from '@/auth.js'

export const useAgentPresenceStore = defineStore('agentPresence', {
    state: () => ({
        online:      false,
        initialized: false,
        pingInterval: null,
    }),

    actions: {
        canUsePresence() {
            try {
                return ['admin', 'manager', 'staff'].includes(getUser()?.role)
            } catch { return false }
        },

        async fetchStatus() {
            try {
                const { data } = await axios.get('/me')
                this.online = data.is_online ?? false
            } catch {}
        },

        async goOnline() {
            try {
                await axios.post('/agent/status/online')
                this.online = true
            } catch {}
        },

        async goOffline() {
            try {
                await axios.post('/agent/status/offline')
                this.online = false
            } catch {}
        },

        async start() {
            if (!this.canUsePresence() || this.initialized) return
            this.initialized = true

            await this.fetchStatus()
            if (!this.online) await this.goOnline()

            this.pingInterval = setInterval(() => {
                if (this.online) axios.post('/agent/status/online').catch(() => {})
            }, 60_000)

            // Offline cuma pas beneran nutup tab/browser, bukan pindah halaman admin
            window.addEventListener('beforeunload', () => {
                axios.post('/agent/status/offline').catch(() => {})
            })
        },

        async toggle() {
            if (this.online) await this.goOffline()
            else await this.goOnline()
        },
    },
})