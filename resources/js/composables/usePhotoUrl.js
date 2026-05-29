export function usePhotoUrl() {
    const photoUrl = (path) => {
        if (!path) return null
        if (path.startsWith('http://') || path.startsWith('https://')) return path
        const base = import.meta.env.VITE_APP_URL || window.location.origin
        return `${base}/storage/${path}`
    }
    return { photoUrl }
}