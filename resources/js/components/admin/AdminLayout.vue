<template>
    <div class="flex h-screen bg-white overflow-hidden">
        <Sidebar />
        <div class="flex-1 flex flex-col overflow-hidden">
            <Navbar :title="title"/>
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script>
import Sidebar from './Sidebar.vue'
import Navbar from './Navbar.vue'
import { useChatNotification } from '@/composables/useChatNotification.js'
import { getUser } from '@/auth.js'

export default {
    name: 'AdminLayout',
    components: { Sidebar, Navbar },
    props: {
        title: { type: String, default: 'Dashboard' }
    },

    mounted() {
        const user = getUser()
        if (['admin', 'manager', 'staff'].includes(user?.role)) {
            const { subscribe } = useChatNotification()
            subscribe()
        }
    },

    beforeUnmount() {
        const { unsubscribe } = useChatNotification()
        unsubscribe()
    }
}
</script>