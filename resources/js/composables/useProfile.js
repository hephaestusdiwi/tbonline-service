import { ref, reactive } from 'vue'
import axios from '../axios.js'

export function useProfile() {
    const profile   = ref(null)
    const loading   = ref(false)
    const saving    = ref(false)
    const error     = ref('')
    const success   = ref('')

    const form = reactive({
        name: '',
    })

    // fetch
    async function fetchProfile() {
        loading.value   = true
        error.value     = ''
        try {
            const { data } = await axios.get('/profile')
            profile.value = data
            form.name = data.name ?? ''
        } catch (e) {
            error.value = e.response?.data?.message ?? 'Gagal memuat profile'
        } finally {
            loading.value = false
        }
    }

    // update profile info
    async function updateProfile() {
        saving.value    = true
        error.value     = ''
        success.value   = ''
        try {
            const { data } = await axios.put('/profile', form)
            profile.value = { ...profile.value, ...data.user }
            success.value = data.message
        } catch (e) {
            error.value = e.response?.data?.message ?? 'Gagal menyimpan profile.'
        } finally {
            saving.value = false
        }
    }

    // upload avatar
    async function uploadAvatar(file) {
        const fd = new FormData()
        fd.append('avatar', file)
        const { data } = await axios.post('/profile/avatar', fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        if (profile.value) profile.value.avatar_url = data.avatar_url
        return data
    }

    // delete user
    async function deleteAvatar() {
        await axios.delete('/profile/avatar')
        if (profile.value) profile.value.avatar_url = null
    }

    // change password
    async function changePassword(payload) {
        // payload : current_password, new_password, new_password_confirmation
        const { data } = await axios.put('/profile/password', payload)
        return data
    }

    return {
        profile, loading, saving, error, success,
        form,
        fetchProfile, updateProfile,
        uploadAvatar, deleteAvatar,
        changePassword,
    }
}