<template>
    <div>
        <!-- MOBILE TOPBAR -->
        <div class="md:hidden fixed top-0 left-0 right-0 z-40 flex items-center justify-between px-4 py-3 bg-white border-b border-gray-200/80 shadow-sm"
             style="height: 56px;">
            <div class="flex gap-2.5">
                <img :src="logoUrl" alt="Logo" class="h-8 w-auto object-contain" style="max-height: 35px;" />
            </div>
            <button @click="mobileOpen = !mobileOpen"
                    class="flex flex-col items-center justify-center w-9 h-9 rounded-xl gap-1.5 transition-all duration-200 border border-gray-200 bg-gray-50 hover:bg-gray-100">
                <span class="block w-5 h-px rounded-full transition-all duration-300"
                      :style="mobileOpen ? 'background:#ED1F24; transform: translateY(4px) rotate(45deg);' : 'background:#6b7280;'"></span>
                <span class="block w-5 h-px rounded-full transition-all duration-300"
                      :style="mobileOpen ? 'background:#ED1F24; opacity:0; transform: scaleX(0);' : 'background:#6b7280;'"></span>
                <span class="block w-5 h-px rounded-full transition-all duration-300"
                      :style="mobileOpen ? 'background:#ED1F24; transform: translateY(-4px) rotate(-45deg);' : 'background:#6b7280;'"></span>
            </button>
        </div>

        <div class="md:hidden h-14"></div>

        <!-- MOBILE OVERLAY -->
        <transition name="fade">
            <div v-if="mobileOpen"
                 class="md:hidden fixed inset-0 z-40"
                 style="background: rgba(0,0,0,0.3); backdrop-filter: blur(2px);"
                 @click="mobileOpen = false">
            </div>
        </transition>

        <!-- MOBILE DRAWER -->
        <transition name="slide-left">
            <div v-if="mobileOpen"
                 class="md:hidden fixed top-14 left-0 bottom-0 z-50 flex flex-col w-72 bg-white border-r border-gray-200/80 shadow-xl">

                <!-- top gradient accent -->
                <div class="absolute top-0 left-0 right-0 h-32 pointer-events-none rounded-b-3xl overflow-hidden">
                    <div style="background: linear-gradient(135deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%); opacity: 0.06; position: absolute; inset: 0;"></div>
                </div>

                <nav class="flex-1 px-3 py-4 overflow-y-auto custom-scroll relative">
                    <template v-for="section in filteredMenuSections" :key="section.label">
                        <div class="px-3 mb-1 mt-4 first:mt-0"
                             style="font-size: 0.62rem; font-weight: 700; letter-spacing: 0.12em; color: #9ca3af; text-transform: uppercase;">
                            {{ section.label }}
                        </div>
                        <template v-for="menu in section.items" :key="menu.label">
                            <div v-if="menu.children">
                                <button @click="toggleMenu(menu.label)"
                                        :class="['w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 group relative']"
                                        :style="isParentActive(menu)
                                            ? 'background: rgba(237,31,36,0.08); color: #ED1F24;'
                                            : 'background: transparent; color: #6b7280;'">
                                    <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center"
                                          :style="isParentActive(menu) ? 'color: #ED1F24;' : 'color: #9ca3af;'">
                                        <font-awesome-icon :icon="['fas', menu.icon]" class="text-sm" />
                                    </span>
                                    <span class="flex-1 text-left font-semibold" style="font-size: 0.8rem;">{{ menu.label }}</span>
                                    <span v-if="menu.badgeKey && getBadgeCount(menu.badgeKey) > 0"
                                          class="text-xs px-1.5 py-0.5 rounded-full font-bold"
                                          style="background: #FEE2E2; color: #ED1F24; font-size: 0.6rem;">
                                        {{ getBadgeCount(menu.badgeKey) }}
                                    </span>
                                    <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200"
                                         :style="openMenus[menu.label] ? 'transform: rotate(90deg); color: #ED1F24;' : 'color: #d1d5db;'"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                    <span v-if="isParentActive(menu)"
                                          class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 rounded-r-full"
                                          style="background: #ED1F24;"></span>
                                </button>
                                <div :style="`overflow: hidden; max-height: ${openMenus[menu.label] ? menu.children.length * 44 + 8 + 'px' : '0px'}; transition: max-height 0.25s ease;`">
                                    <div class="ml-4 mt-1 mb-1 pl-3 space-y-0.5" style="border-left: 2px solid #f3f4f6;">
                                        <router-link
                                            v-for="child in menu.children"
                                            :key="child.path"
                                            :to="child.path"
                                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-all duration-150"
                                            :style="isActive(child.path)
                                                ? 'background: rgba(237,31,36,0.08); color: #ED1F24;'
                                                : 'color: #9ca3af;'"
                                            @click="mobileOpen = false">
                                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                                  :style="isActive(child.path) ? 'background: #ED1F24;' : 'background: #d1d5db;'"></span>
                                            <span style="font-size: 0.78rem; font-weight: 500;">{{ child.label }}</span>
                                            <span v-if="child.badgeKey && getBadgeCount(child.badgeKey) > 0"
                                                  class="ml-auto text-xs px-1.5 py-0.5 rounded-full font-bold"
                                                  style="background: #FEE2E2; color: #ED1F24; font-size: 0.6rem;">
                                                {{ getBadgeCount(child.badgeKey) }}
                                            </span>
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                            <router-link
                                v-else
                                :to="menu.path"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 group relative"
                                :style="isActive(menu.path)
                                    ? 'background: rgba(237,31,36,0.08); color: #ED1F24;'
                                    : 'color: #6b7280;'"
                                @click="mobileOpen = false">
                                <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center"
                                      :style="isActive(menu.path) ? 'color: #ED1F24;' : 'color: #9ca3af;'">
                                    <font-awesome-icon :icon="['fas', menu.icon]" class="text-sm" />
                                </span>
                                <span class="flex-1 font-semibold" style="font-size: 0.8rem;">{{ menu.label }}</span>
                                <span v-if="isActive(menu.path)"
                                      class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 rounded-r-full"
                                      style="background: #ED1F24;"></span>
                            </router-link>
                        </template>
                    </template>
                </nav>

                <div class="relative px-3 py-3 border-t border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-white border border-gray-100 shadow-sm">
                        <div class="w-8 h-8 rounded-lg flex-shrink-0 overflow-hidden">
                            <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" class="w-full h-full object-cover" />
                            <div v-else class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                                 style="background: linear-gradient(135deg, #ED1F24, #7f1d1d);">
                                {{ userInitials }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-800 truncate" style="font-size: 0.8rem;">{{ user.name || 'Administrator' }}</p>
                            <p class="text-xs capitalize truncate text-gray-400" style="font-size: 0.7rem;">{{ user.role || 'Admin' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Logout Button Mobile  -->
                <div class="relative px-3 py-3 border-t border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-white border border-gray-100 shadow-sm">
                        <div class="w-8 h-8 rounded-lg flex-shrink-0 overflow-hidden">
                            <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" class="w-full h-full object-cover" />
                            <div v-else class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                                style="background: linear-gradient(135deg, #ED1F24, #7f1d1d);">
                                {{ userInitials }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-800 truncate" style="font-size: 0.8rem;">{{ user.name || 'Administrator' }}</p>
                            <p class="capitalize truncate text-gray-400" style="font-size: 0.7rem;">{{ user.role || 'Admin' }}</p>
                        </div>
                        <button @click="handleLogout" title="Logout"
                                class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-lg border border-red-100 bg-red-50 hover:bg-red-100 transition-all duration-200">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- DESKTOP SIDEBAR -->
        <div class="hidden md:flex flex-col transition-all duration-300 ease-in-out min-h-screen relative bg-white border-r border-gray-200/80 shadow-sm"
             :class="collapsed ? 'w-20' : 'w-72'">

            <!-- Hero gradient strip at top -->
            <div class="absolute top-0 left-0 right-0 h-1 z-10"
                 style="background: linear-gradient(90deg, #ED1F24 0%, #B01419 60%, #8B0F13 100%);"></div>

            <!-- Subtle red tint at top -->
            <div class="absolute top-0 left-0 right-0 h-40 pointer-events-none"
                 style="background: linear-gradient(180deg, rgba(237,31,36,0.04) 0%, transparent 100%);"></div>

            <!-- Logo area -->
            <div class="relative flex items-center justify-between px-5 py-5 border-b border-gray-100 mt-1">
                <div v-if="!collapsed" class="flex flex-1 min-w-0">
                    <img :src="logoUrl" alt="Logo" class="w-full h-auto object-contain" style="max-height: 35px;" />
                </div>
                <div v-else class="mx-auto">
                    <img :src="logoUrl" alt="Logo" class="w-10 h-10 object-contain" />
                </div>
                <button v-if="!collapsed" @click="collapsed = true"
                        class="flex items-center justify-center w-7 h-7 rounded-lg transition-all duration-200 flex-shrink-0 ml-2 border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>
            </div>

            <!-- Collapse expand button when collapsed -->
            <button v-if="collapsed" @click="collapsed = false"
                    class="mx-auto mt-3 flex items-center justify-center w-8 h-8 rounded-lg transition-all duration-200 border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>

            <!-- Nav -->
            <nav class="flex-1 px-3 py-4 overflow-y-auto overflow-x-hidden custom-scroll" style="scrollbar-gutter: stable;">
                <template v-for="section in filteredMenuSections" :key="section.label">
                    <!-- Section label -->
                    <div v-if="!collapsed" class="px-3 mb-1 mt-4 first:mt-0"
                         style="font-size: 0.62rem; font-weight: 700; letter-spacing: 0.12em; color: #9ca3af; text-transform: uppercase;">
                        {{ section.label }}
                    </div>
                    <div v-else class="mt-4 first:mt-0 flex justify-center mb-1">
                        <div class="w-4 border-t border-gray-200"></div>
                    </div>

                    <template v-for="menu in section.items" :key="menu.label">
                        <!-- Parent with children -->
                        <div v-if="menu.children">
                            <button @click="!collapsed && toggleMenu(menu.label)"
                                    :title="collapsed ? menu.label : ''"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 group relative"
                                    :style="isParentActive(menu)
                                        ? 'background: rgba(237,31,36,0.08); color: #C81A1E;'
                                        : 'background: transparent; color: #6b7280;'"
                                    @mouseenter="e => { if (!isParentActive(menu)) e.currentTarget.style.background = '#f9fafb'; e.currentTarget.style.color = '#374151' }"
                                    @mouseleave="e => { if (!isParentActive(menu)) { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.color = '#6b7280' } }">
                                <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center transition-colors duration-200"
                                      :style="isParentActive(menu) ? 'color: #ED1F24;' : 'color: #9ca3af;'">
                                    <font-awesome-icon :icon="['fas', menu.icon]" class="text-sm" />
                                </span>
                                <span v-if="!collapsed" class="flex-1 text-left font-semibold" style="font-size: 0.8rem;">{{ menu.label }}</span>
                                <span v-if="!collapsed && menu.badgeKey && getBadgeCount(menu.badgeKey) > 0"
                                      class="text-xs px-1.5 py-0.5 rounded-full font-bold"
                                      style="background: #FEE2E2; color: #ED1F24; font-size: 0.6rem;">
                                    {{ getBadgeCount(menu.badgeKey) }}
                                </span>
                                <svg v-if="!collapsed" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200"
                                     :style="openMenus[menu.label] ? 'transform: rotate(90deg); color: #ED1F24;' : 'color: #d1d5db;'"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                                <span v-if="isParentActive(menu)"
                                      class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 rounded-r-full"
                                      style="background: #ED1F24;"></span>
                            </button>

                            <!-- Children -->
                            <div v-if="!collapsed"
                                 :style="`overflow: hidden; max-height: ${openMenus[menu.label] ? menu.children.length * 44 + 8 + 'px' : '0px'}; transition: max-height 0.25s ease;`">
                                <div class="ml-4 mt-1 mb-1 pl-3 space-y-0.5" style="border-left: 2px solid #f3f4f6;">
                                    <router-link
                                        v-for="child in menu.children"
                                        :key="child.path"
                                        :to="child.path"
                                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-all duration-150 group"
                                        :style="isActive(child.path)
                                            ? 'background: rgba(237,31,36,0.08); color: #ED1F24;'
                                            : 'color: #9ca3af;'"
                                        @mouseenter="e => { if (!isActive(child.path)) { e.currentTarget.style.background = '#f9fafb'; e.currentTarget.style.color = '#374151' } }"
                                        @mouseleave="e => { if (!isActive(child.path)) { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.color = '#9ca3af' } }">
                                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 transition-colors duration-150"
                                              :style="isActive(child.path) ? 'background: #ED1F24;' : 'background: #e5e7eb;'"></span>
                                        <span style="font-size: 0.78rem; font-weight: 500;">{{ child.label }}</span>
                                        <span v-if="child.badgeKey && getBadgeCount(child.badgeKey) > 0"
                                              class="ml-auto text-xs px-1.5 py-0.5 rounded-full font-bold"
                                              style="background: #FEE2E2; color: #ED1F24; font-size: 0.6rem;">
                                            {{ getBadgeCount(child.badgeKey) }}
                                        </span>
                                    </router-link>
                                </div>
                            </div>
                        </div>

                        <!-- Single link -->
                        <router-link
                            v-else
                            :to="menu.path"
                            :title="collapsed ? menu.label : ''"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 group relative"
                            :style="isActive(menu.path)
                                ? 'background: rgba(237,31,36,0.08); color: #C81A1E;'
                                : 'color: #6b7280;'"
                            @mouseenter="e => { if (!isActive(menu.path)) { e.currentTarget.style.background = '#f9fafb'; e.currentTarget.style.color = '#374151' } }"
                            @mouseleave="e => { if (!isActive(menu.path)) { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.color = '#6b7280' } }">
                            <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center transition-colors duration-200"
                                  :style="isActive(menu.path) ? 'color: #ED1F24;' : 'color: #9ca3af;'">
                                <font-awesome-icon :icon="['fas', menu.icon]" class="text-sm" />
                            </span>
                            <span v-if="!collapsed" class="flex-1 font-semibold" style="font-size: 0.8rem;">{{ menu.label }}</span>
                            <span v-if="isActive(menu.path)"
                                  class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 rounded-r-full"
                                  style="background: #ED1F24;"></span>
                        </router-link>
                    </template>
                </template>
            </nav>

            <!-- User footer -->
            <div class="relative px-3 py-3 border-t border-gray-100 bg-gray-50/30">
                <div :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-all duration-200 border border-gray-100 bg-white shadow-sm hover:shadow-md hover:border-gray-200',
                              collapsed ? 'justify-center' : '']">
                    <div class="w-8 h-8 rounded-lg flex-shrink-0 overflow-hidden">
                        <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" class="w-full h-full object-cover" />
                        <div v-else class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                             style="background: linear-gradient(135deg, #ED1F24, #7f1d1d);">
                            {{ userInitials }}
                        </div>
                    </div>
                    <div v-if="!collapsed" class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate" style="font-size: 0.8rem;">{{ user.name || 'Administrator' }}</p>
                        <p class="text-xs capitalize truncate text-gray-400" style="font-size: 0.7rem;">{{ user.role || 'Admin' }}</p>
                    </div>
                    <svg v-if="!collapsed" class="w-3.5 h-3.5 flex-shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                    </svg>
                </div>

                <div class="relative px-3 py-3 border-t border-gray-100 bg-gray-50/30">
                    <div :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl border border-gray-100 bg-white shadow-sm hover:shadow-md hover:border-gray-200 transition-all duration-200',
                                collapsed ? 'justify-center' : '']">
                        <div class="w-8 h-8 rounded-lg flex-shrink-0 overflow-hidden">
                            <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" class="w-full h-full object-cover" />
                            <div v-else class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                                style="background: linear-gradient(135deg, #ED1F24, #7f1d1d);">
                                {{ userInitials }}
                            </div>
                        </div>
                        <div v-if="!collapsed" class="flex-1 min-w-0">
                            <p class="font-bold text-gray-800 truncate" style="font-size: 0.8rem;">{{ user.name || 'Administrator' }}</p>
                            <p class="capitalize truncate text-gray-400" style="font-size: 0.7rem;">{{ user.role || 'Admin' }}</p>
                        </div>
                        <button @click="handleLogout" :title="collapsed ? 'Logout' : ''"
                                :class="['flex-shrink-0 flex items-center justify-center rounded-lg border border-red-100 bg-red-50 hover:bg-red-100 transition-all duration-200',
                                        collapsed ? 'w-8 h-8' : 'w-7 h-7']">
                            <svg class="w-3.5 h-3.5" style="color: #ED1F24;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from '../../axios.js'
import { getUser, getPermissions, getToken, clearAuth } from '../../auth.js'
const POLL_INTERVAL = 30000

export default {
    name: 'Sidebar',
    data() {
        return {
            permissions: getPermissions(),
            collapsed: false,
            mobileOpen: false,
            openMenus: {},
            user: getUser() ?? {},

            logoUrl: window.location.origin + '/storage/logos/logo_1778005737.webp',

            badgeCounts: {
                pending_orders: 0,
            },

            pollTimer: null,

            menuSections: [
                {
                    label: 'Overview',
                    items: [
                        { path: '/admin/dashboard', icon: 'chart-line', label: 'Dashboard' },
                    ]
                },
                {
                    label: 'Catalog',
                    items: [
                        {
                            icon: 'box-open', label: 'Products',
                            permission: 'products_view',
                            children: [
                                { path: '/admin/products',          label: 'All Products',      permission: 'products_view' },
                                { path: '/admin/featured-products', label: 'Featured Products', permission: 'products_view' },
                            ]
                        },
                        {
                            icon: 'tag', label: 'Promotions',
                            permission: 'settings_view',
                            children: [
                                { path: '/admin/promo-codes',       label: 'Promo Codes',        permission: 'settings_view' },
                                { path: '/admin/promotions',        label: 'Promotion Banners',  permission: 'settings_view' },
                                { path: '/admin/announcements',     label: 'Announcement Bar',   permission: 'settings_view' },
                            ]
                        },
                    ]
                },
                {
                    label: 'Operations',
                    items: [
                        {
                            icon: 'shopping-cart', label: 'Orders',
                            permission: 'orders_view',
                            badgeKey: 'pending_orders',
                            children: [
                                { path: '/admin/orders',           label: 'All Orders', permission: 'orders_view' },
                                { path: '/admin/orders/pending',   label: 'Pending',    permission: 'orders_view', badgeKey: 'pending_orders' },
                            ]
                        },
                        {
                            icon: 'building', label: 'Branches',
                            permission: 'settings_view',
                            children: [
                                { path: '/admin/branches', label: 'All Branches', permission: 'settings_view' },
                            ]
                        },
                        {
                            icon: 'coins', label: 'Loyalty Points',
                            children: [
                                { path: '/admin/loyalty-points', label: 'Riwayat Point' },
                            ]
                        },
                        {
                            icon: 'chart-bar', label: 'Reports',
                            permission: 'reports_view',
                            children: [
                                { path: '/admin/sales-reports',   label: 'Sales Reports',   permission: 'reports_view' },
                                { path: '/admin/product-reports', label: 'Product Reports', permission: 'reports_view' },
                                { path: '/admin/visitor-stats', label: 'Traffic Status', permission: 'reports_view' },
                            ]
                        },
                        {
                            icon: 'comments', label: 'Live Chat',
                            children: [
                                { path: '/admin/chat', label: 'Chat Dashboard' },
                                { path: '/admin/complaints', label: 'Komplain'},
                            ]
                        },
                    ]
                },
                {
                    label: 'Content',
                    items: [
                        {
                            icon: 'newspaper', label: 'Content',
                            children: [
                                { path: '/admin/articles',               label: 'Artikel & Blog' },
                                { path: '/admin/articles/create',        label: 'Tulis Artikel' },
                                { path: '/admin/contents/tos',           label: 'Syarat & Ketentuan' },
                                { path: '/admin/contents/shipping_info', label: 'Info Pengiriman' },
                                { path: '/admin/contents/return_policy', label: 'Info Pengembalian' },
                            ]
                        },
                        {
                            icon: 'newspaper', label: 'Help Center',
                            permission: 'settings_view',
                            children: [
                                { path: '/admin/faqs', label: 'All FAQs', permission: 'settings_view' },
                                { path: '/admin/faqs/create', label: 'Create FAQ', permission: 'settings_view' },
                            ]
                        },
                    ],
                },
                {
                    label: 'Administration',
                    items: [
                        {
                            icon: 'users', label: 'Users',
                            permission: 'users_view',
                            children: [
                                { path: '/admin/users', label: 'All Users',           permission: 'users_view' },
                                { path: '/admin/roles', label: 'Roles & Permissions', permission: 'roles_view' },
                            ]
                        },
                        {
                            icon: 'gear', label: 'Settings',
                            permission: 'settings_view',
                            children: [
                                { path: '/admin/settings',      label: 'Site Settings', permission: 'settings_view' },
                                { path: '/admin/sliders',       label: 'Sliders',       permission: 'settings_view' },
                                { path: '/admin/navigations',   label: 'Navigation',    permission: 'settings_view' },
                                { path: '/admin/footer-links',  label: 'Footer Links',  permission: 'settings_view' },
                            ]
                        },
                    ]
                },
                {
                    label: 'Account',
                    items: [
                        { path: '/admin/profile', icon: 'user-circle', label: 'My Profile' },
                    ]
                },
            ],
        }
    },

    computed: {
        userInitials() {
            const name = this.user.name || 'Admin'
            return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
        },
        filteredMenuSections() {
            return this.menuSections
                .map(section => ({
                    ...section,
                    items: section.items
                        .filter(menu => !menu.permission || this.can(menu.permission))
                        .map(menu => ({
                            ...menu,
                            children: menu.children
                                ? menu.children.filter(child => !child.permission || this.can(child.permission))
                                : undefined
                        }))
                }))
                .filter(section => section.items.length > 0)
        }
    },

    methods: {
        can(permission) {
            return this.permissions.includes(permission)
        },
        toggleMenu(label) {
            this.openMenus[label] = !this.openMenus[label]
        },
        isActive(path) {
            return this.$route && this.$route.path === path
        },
        isParentActive(menu) {
            if (!menu.children) return false
            return menu.children.some(child => this.isActive(child.path))
        },
        autoOpenActiveMenus() {
            this.menuSections.forEach(section => {
                section.items.forEach(menu => {
                    if (menu.children && this.isParentActive(menu)) {
                        this.openMenus[menu.label] = true
                    }
                })
            })
        },
        async handleLogout() {
            try {
                await axios.post('/logout')
            } catch (e) {
                // tetap logout meski error
            } finally {
                clearAuth()
                this.$router.push('/login')
            }
        },
        handleKeydown(e) {
            if (e.key === 'Escape') this.mobileOpen = false
        },
        getBadgeCount(key) {
            return this.badgeCounts[key] || 0
        },
        refreshUser() {
            this.user = getUser() ?? {}
        },
        async fetchPendingOrders() {
            try {
                const token = getToken()
                const res = await fetch('/api/orders/pending-count', {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                })
                if (!res.ok) return
                const data = await res.json()
                this.badgeCounts.pending_orders = data.count ?? 0
            } catch (e) {
                console.warn('[Sidebar] Gagal fetch pending orders:', e)
            }
        },
        startPolling() {
            this.fetchPendingOrders()
            this.pollTimer = setInterval(this.fetchPendingOrders, POLL_INTERVAL)
        },
        stopPolling() {
            if (this.pollTimer) { clearInterval(this.pollTimer); this.pollTimer = null }
        }
    },

    watch: {
        '$route'() {
            this.mobileOpen = false
            this.autoOpenActiveMenus()
        },
        mobileOpen(val) {
            document.body.style.overflow = val ? 'hidden' : ''
        }
    },

    mounted() {
        this.autoOpenActiveMenus()
        window.addEventListener('keydown', this.handleKeydown)
        window.addEventListener('user-updated', this.refreshUser)
        this.startPolling()
    },
    beforeUnmount() {
        window.removeEventListener('keydown', this.handleKeydown)
        window.removeEventListener('user-updated', this.refreshUser)
        document.body.style.overflow = ''
        this.stopPolling()
    }
}
</script>

<style scoped>
.custom-scroll::-webkit-scrollbar { width: 4px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 999px; }
.custom-scroll::-webkit-scrollbar-thumb:hover { background: #d1d5db; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-left-enter-active, .slide-left-leave-active { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(-100%); }
</style>