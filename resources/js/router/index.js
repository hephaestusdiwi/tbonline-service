import { createRouter, createWebHistory } from 'vue-router'
import { isAuthenticated } from '../auth.js'

import Home from '../pages/Home.vue'
import ProductsPage from '../pages/ProductsPage.vue'
import Login from '../pages/Login.vue'
import Dashboard from '../pages/admin/Dashboard.vue'
import Users from '../pages/admin/Users.vue'
import Sliders from '../pages/admin/Sliders.vue'
import Navigations from '../pages/admin/Navigations.vue'
import Products from '../pages/admin/Products.vue'
import ProductDetail from '../pages/ProductDetail.vue'
import Branches from '../pages/admin/Branches.vue'
import CartPage     from '../pages/CartPage.vue'
import CheckoutPage from '../pages/CheckoutPage.vue'
import Orders from '../pages/admin/Orders.vue'
import OrdersPending from '../pages/admin/OrdersPending.vue'
import PromoCodes from '../pages/admin/PromoCodes.vue'
import SalesReports from '../pages/admin/SalesReports.vue'
import ProductReports from '../pages/admin/ProductReports.vue'
import FeaturedProducts from '../pages/admin/FeaturedProducts.vue'
import SiteSettings from '../pages/admin/SiteSettings.vue'
import ProfileView from '../pages/ProfileView.vue'
import Footerlinks from '../pages/admin/Footerlinks.vue'
import TbPointPage from '../pages/MembershipTB.vue'
import AdminFaqIndex from '../pages/admin/AdminFaqIndex.vue'
import AdminFaqForm  from '../pages/admin/AdminFaqForm.vue'

const routes = [
    { path: '/', component: Home },
    { path: '/products', name: 'Products', component: ProductsPage },
    { path: '/products/:id', name: 'ProductDetail', component: ProductDetail },
    { path: '/login', component: Login },
    { path: '/admin/dashboard', component: Dashboard, meta: { requiresAuth: true } },
    { path: '/admin/users', component: Users, meta: { requiresAuth: true } },
    { path: '/admin/sliders', component: Sliders, meta: { requiresAuth: true } },
    { path: '/admin/navigations', component: Navigations, meta: { requiresAuth: true } },
    { path: '/admin/products', component: Products, meta: { requiresAuth: true } },
    { path: '/admin/featured-products', component: FeaturedProducts, meta: { requiresAuth: true } },
    { path: '/admin/branches', component: Branches, meta: { requiresAuth: true } },
    { path: '/admin/orders', component: Orders, meta: { requiresAuth: true } },
    { path: '/admin/orders/pending', component: OrdersPending, meta: { requiresAuth: true } },
    { path: '/admin/profile', component: ProfileView, meta: { requiresAuth: true } },
    { path: '/admin/promo-codes', component: PromoCodes, meta: { requiresAuth: true } },
    { path: '/admin/footer-links', component: () => import('../pages/admin/Footerlinks.vue'), meta: { requiresAuth: true } },
    { path: '/admin/settings', component: () => import('../pages/admin/SiteSettings.vue'), meta: { requiresAuth: true } },
    { path: '/admin/roles', component: () => import('../pages/admin/RolesList.vue'), meta: { requiresAuth: true } },
    { path: '/admin/roles/create', component: () => import('../pages/admin/CreateEditRole.vue'), meta: { requiresAuth: true } },
    { path: '/admin/roles/:id/edit', component: () => import('../pages/admin/CreateEditRole.vue'), props: true, meta: { requiresAuth: true } },
    { path: '/admin/sales-reports', component: () => import('../pages/admin/SalesReports.vue'), meta: { requiresAuth: true } },
    { path: '/admin/product-reports', component: () => import('../pages/admin/ProductReports.vue'), meta: { requiresAuth: true } },
    { path: '/cart',     component: () => import('../pages/CartPage.vue') },
    { path: '/checkout', component: () => import('../pages/CheckoutPage.vue') },
    { path: '/admin/chat', name: 'admin.chat', component: () => import('../pages/admin/AdminChat.vue'), meta: { requiresAuth: true } },
    { path: '/admin/promotions', component: () => import('../pages/admin/PromotionAdmin.vue'), meta: { requiresAuth: true } },
    { path: '/admin/announcements', component: () => import('../pages/admin/AnnouncementAdmin.vue'), meta: { requiresAuth: true } }, 
    { path: '/admin/articles', component: () => import('../pages/admin/ArticleList.vue'), meta: { requiresAuth: true } },
    { path: '/admin/articles/create', component: () => import('../pages/admin/ArticleForm.vue'), meta: { requiresAuth: true } },
    { path: '/admin/articles/:id/edit', component: () => import('../pages/admin/ArticleForm.vue'), meta: { requiresAuth: true } },
    { path: '/admin/contents/:type', component: () => import('../pages/admin/StaticPageEditor.vue'), meta: { requiresAuth: true } },
    { path: '/admin/loyalty-points', component: () => import('../pages/admin/LoyaltyPoints.vue'), meta: { requiresAuth: true } },
    { path: '/blog',       component: () => import('../pages/BlogPage.vue') },
    { path: '/blog/:slug', component: () => import('../pages/BlogDetail.vue') },
    { path: '/syarat-ketentuan',       component: () => import('../pages/StaticContentPage.vue') },
    { path: '/informasi-pengiriman',   component: () => import('../pages/StaticContentPage.vue') },
    { path: '/informasi-pengembalian', component: () => import('../pages/StaticContentPage.vue') },
    { path: '/tb-point', name: 'TbPoint', component: () => import('../pages/MembershipTB.vue') },
    { path: '/store-location', name: 'StoreLocator', component: () => import('../pages/StoreLocator.vue') },
    { path: '/admin/articles/:id/edit', component: () => import('../pages/admin/ArticleForm.vue'), meta: { requiresAuth: true } },
    { path: '/admin/faqs',          component: AdminFaqIndex, meta: { requiresAuth: true } },
    { path: '/admin/faqs/create',   component: AdminFaqForm,  meta: { requiresAuth: true } },
    { path: '/admin/faqs/:id/edit', component: AdminFaqForm,  meta: { requiresAuth: true } },
    { path: '/admin/visitor-stats', name: 'AdminVisitorStats', component: () => import('../pages/admin/VisitorStats.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/i/:invoice_number', component: () => import('../pages/PublicInvoicePage.vue') },
    { path: '/admin/complaints', component: () => import('../pages/admin/Complaints.vue'), meta: { requiresAuth: true } },
]

const router = createRouter({
    history: createWebHistory(),
    routes,

    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition
        }
        return { top: 0, behavior: 'smooth' }
    },
})

router.beforeEach((to, from, next) => {
    if (to.meta.requiresAuth && !isAuthenticated()) {
        next('/login')
    } else {
        next()
    }
})

export default router