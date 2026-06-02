// src/plugins/echo.js
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { getToken } from '../auth.js'

window.Pusher = Pusher

const echo = new Echo({
  broadcaster:   'pusher',
  key:           import.meta.env.VITE_PUSHER_KEY,
  cluster:       import.meta.env.VITE_PUSHER_CLUSTER,
  encrypted:     true,
  forceTLS:      true,
  authEndpoint:  '/broadcasting/auth',
  auth: {
    headers: {
      Authorization: `Bearer ${getToken()}`,
      Accept: 'application/json',
    },
  },
})

export default echo