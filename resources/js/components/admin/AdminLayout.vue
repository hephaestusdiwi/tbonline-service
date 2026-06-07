<template>
    <div class="flex h-screen bg-white overflow-hidden">

        <!-- sidebar -->
        <Sidebar />

        <!-- main content -->
         <div class="flex-1 flex flex-col overflow-hidden">

            <!-- navbar -->
             <Navbar :title="title"/>

             <!-- page content -->
              <main class="flex-1 overflow-y-auto p-6" :class="mainClass">
                    <slot />
              </main>
         </div>
    </div>
</template>

<script>
import Sidebar from './Sidebar.vue'
import Navbar from './Navbar.vue'

export default {
    name: 'AdminLayout',
    components: {
        Sidebar,
        Navbar
     },
     props: {
        title: { type: String, default: 'Dashboard' },
        mainClass: { type: String, default: 'overflow-y-auto' }  
    }
}
</script>