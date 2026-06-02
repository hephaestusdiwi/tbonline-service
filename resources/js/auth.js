const KEYS = {
    token:       'token',
    user:        'user',
    permissions: 'permissions',
}

export function saveAuth({ token, user, rememberMe }) {
    const storage = rememberMe ? localStorage : sessionStorage
    clearAuth()
    storage.setItem(KEYS.token,       token)
    storage.setItem(KEYS.user,        JSON.stringify(user))
    storage.setItem(KEYS.permissions, JSON.stringify(user.permissions ?? []))
}

export function getToken() {
    return localStorage.getItem(KEYS.token)
        ?? sessionStorage.getItem(KEYS.token)
        ?? null
}

export function getUser() {
    const raw = localStorage.getItem(KEYS.user)
             ?? sessionStorage.getItem(KEYS.user)
    try { return raw ? JSON.parse(raw) : null } catch { return null }
}

export function getPermissions() {
    const raw = localStorage.getItem(KEYS.permissions)
             ?? sessionStorage.getItem(KEYS.permissions)
    try { return raw ? JSON.parse(raw) : [] } catch { return [] }
}

export function clearAuth() {
    ;[localStorage, sessionStorage].forEach(s => {
        s.removeItem(KEYS.token)
        s.removeItem(KEYS.user)
        s.removeItem(KEYS.permissions)
    })
}

export function isAuthenticated() {
    return !!getToken()
}