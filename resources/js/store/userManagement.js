// stores/userManagement.js
import { defineStore } from 'pinia'
import axios from '../axios.js'

export const useUserManagementStore = defineStore('userManagement', {
  state: () => ({
    users:       [],
    loading:     false,
    error:       null,
  }),

  getters: {
    countByRole: (state) => (role) =>
      state.users.filter(u => u.role === role).length,

    activeCount:    (state) => state.users.filter(u => u.is_active).length,
    suspendedCount: (state) => state.users.filter(u => !u.is_active).length,
  },

  actions: {
    async fetchUsers() {
      this.loading = true
      this.error   = null
      try {
        const { data } = await axios.get('/users')
        this.users = data
      } catch (e) {
        this.error = e.response?.data?.message ?? 'Gagal memuat data pengguna.'
      } finally {
        this.loading = false
      }
    },

    async createUser(payload) {
      const { data } = await axios.post('/users', payload)
      this.users.push(data)
      return data
    },

    async updateUser(id, payload) {
      const { data } = await axios.put(`/users/${id}`, payload)
      const idx = this.users.findIndex(u => u.id === id)
      if (idx !== -1) this.users[idx] = data
      return data
    },

    async resetPassword(id, payload) {
      await axios.post(`/users/${id}/reset-password`, payload)
    },

    async suspendUser(id) {
      const { data } = await axios.post(`/users/${id}/suspend`)
      const idx = this.users.findIndex(u => u.id === id)
      if (idx !== -1) this.users[idx] = data.user
      return data
    },

    async activateUser(id) {
      const { data } = await axios.post(`/users/${id}/activate`)
      const idx = this.users.findIndex(u => u.id === id)
      if (idx !== -1) this.users[idx] = data.user
      return data
    },

    async fetchUserDetail(id) {
      const { data } = await axios.get(`/users/${id}`)
      return data
    },

    async deleteUser(id) {
      await axios.delete(`/users/${id}`)
      this.users = this.users.filter(u => u.id !== id)
    },
  },
})