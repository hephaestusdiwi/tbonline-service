<template>
    <router-view />
</template>

<script>
export default {
    name: 'App',
    mounted() {
    let favicon = document.querySelector("link[rel='icon']")
    if (!favicon) {
        favicon = document.createElement('link')
        favicon.rel = 'icon'
        document.head.appendChild(favicon)
    }
    favicon.type = 'image/png'
    favicon.href = window.location.origin + '/storage/logos/tbgroup.png'
}
}
</script>