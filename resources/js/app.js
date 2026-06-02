import { createApp } from 'vue'
import { createHead } from '@vueuse/head'
import App from './App.vue'
import router from './router/index.js'
import { createPinia } from 'pinia'
import './axios.js'
import { getToken } from './auth.js'

import '@fontsource/open-sans/400.css'
import '@fontsource/open-sans/500.css'
import '@fontsource/open-sans/600.css'
import '@fontsource/open-sans/700.css'

import '@fontsource/poppins/400.css'
import '@fontsource/poppins/500.css'
import '@fontsource/poppins/600.css'
import '@fontsource/poppins/700.css'

import '@fontsource/roboto/400.css'
import '@fontsource/roboto/500.css'
import '@fontsource/roboto/600.css'
import '@fontsource/roboto/700.css'

import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher
window.Echo = new Echo({
    broadcaster:  'pusher',
    key:          import.meta.env.VITE_PUSHER_KEY,
    cluster:      import.meta.env.VITE_PUSHER_CLUSTER,
    encrypted:    true,
    forceTLS:     true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${getToken()}`,
            Accept:        'application/json',
        },
    },
})


import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faGoogle, faFacebook, faInstagram, faTwitter, faTiktok, faYoutube, faWhatsapp, } from '@fortawesome/free-brands-svg-icons'

import {
  faChartLine,
  faUsers,
  faImages,
  faCompass,
  faBoxOpen,
  faBuilding,
  faMagnifyingGlass,
  faUser,
  faCartShopping,
  faTag,
  faGear,
  faChartBar,
  faMoneyBillWave,
  faClock,
  faCircleCheck,
  faCircleXmark,
  faTruck,
  faUserCircle,
  faComments,
  faNewspaper,
  faCoins,
  faFileContract,
  faUndo,
  faCube,
  faStar,
  faFileAlt,
  faExclamationTriangle,
  faUserPlus,
  faXmark,
  faSun,
  faPen,
  faPencil,
  faPlus,
  faRotate,
  faRotateRight,
  faCalendarDays,
  faEdit,
  faLink,
  faHome,
  faSearch,
  faGlobe,
  faEye,
  faArrowRight,
  faArrowRightFromBracket
} from '@fortawesome/free-solid-svg-icons'


import VueApexCharts from 'vue3-apexcharts'

library.add(
  faChartLine,
  faUsers,
  faImages,
  faCompass,
  faBoxOpen,
  faBuilding,
  faMagnifyingGlass,
  faUser,
  faCartShopping,
  faTag,
  faGear,
  faChartBar,
  faMoneyBillWave,
  faClock,
  faCircleCheck,
  faCircleXmark,
  faTruck,
  faUserCircle,
  faComments,
  faNewspaper,
  faCoins,
  faFileContract,
  faUndo,
  faCube,
  faStar,
  faFileAlt,
  faExclamationTriangle,
  faUserPlus,
  faWhatsapp,
  faXmark,
  faSun,
  faPen,
  faPlus,
  faRotateRight,
  faCalendarDays,
  faEdit,
  faLink,
  faHome,
  faSearch,
  faGlobe,
  faEye,
  faArrowRightFromBracket
)
const head = createHead()
const pinia = createPinia()

createApp(App)
  .use(router)
  .use(head)
  .use(pinia)
  .use(VueApexCharts)
  .component('font-awesome-icon', FontAwesomeIcon)
  .mount('#app')