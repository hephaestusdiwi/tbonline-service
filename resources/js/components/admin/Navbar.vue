<template>
    <div class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">

        <!-- page title -->
         <h2 class="text-lg font-semibold text-gray-700">{{ title }}</h2>

         <!-- right side -->
          <div class="flex items-center gap-4">
            <span class="text-sm text-gray-500">{{ user.name }}</span>
            <button
                @click="handleLogout"
                class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-1.5 rounded-lg transition">
                Logout
            </button>
          </div>

    </div>
</template>

<script>
import axios from '../../axios.js'
import { getUser, clearAuth } from '../../auth.js'

export default {
    name: 'Navbar',
    props: {
        title: {
            type: String,
            default: 'Dashboard'
        }
    },
    data() {
        return {
            user: getUser() ?? {}
        }
    },
    methods: {
        async handleLogout() {
            try {
                await axios.post('/logout')
            } catch (e) {
                // tetap logout meski error
            } finally {
                clearAuth()
                this.$router.push('/login')
            }
        }
    }
}
</script>