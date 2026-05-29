<template>
    <!-- overlay -->
    <transition name="fade">
        <div
         v-if="cart.state.isOpen"
         class="cart-overlay"
         @click="cart.close()"
        />
    </transition>

    <!-- drawer -->
    <transition name="slide">
        <div v-if="cart.state.isOpen" class="cart-drawer">

            <!-- header -->
            <div class="cart-header">
                <div class="cart-header-left">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="20" height="20">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <span class="cart-title">Keranjang</span>
                    <span class="cart-count">{{ cart.totalItems }}</span>
                </div>
                <button class="cart-close" @click="cart.close()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- Shipping bar
            <div class="shipping-bar">
                <div v-if="remainingForFreeShipping > 0">
                    <p class="shipping-text">
                        Tambah <strong>{{ formatPrice(remainingForFreeShipping) }}</strong> lagi untuk gratis ongkir!
                    </p>
                    <div class="shipping-progress-bg">
                        <div class="shipping-progress-fill" :style="{ width: shippingProgress + '%' }"/>
                    </div>
                </div>
                <div v-else class="shipping-achieved">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Selamat! Kamu dapat gratis ongkir!
                </div>
            </div>
             -->

            <!-- items -->
            <div class="cart-items" ref="itemsContainer">
                <div v-if="cart.state.items.length === 0" class="cart-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" width="56" height="56">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <p>Keranjangmu kosong</p>
                    <button class="btn-shop" @click="cart.close()">Mulai belanja</button>
                </div>

                <transition-group name="item" tag="div" class="item-list">
                    <div v-for="item in cart.state.items" 
                        :key="`${item.id}-${item.variant_id}`" 
                        class="cart-item">
                        <!-- product image -->
                        <div class="item-img">
                            <img v-if="item.photo_1" :src="item.photo_1" :alt="item.name"/>
                            <div v-else class="item-no-img">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="24" height="24">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                            </div>
                        </div>

                        <!-- info -->
                        <div class="item-info">
                            <p class="item-name">{{ item.name }}</p>
                            <p class="item-category">{{ item.category }}</p>
                            <p v-if="item.variant_label && item.variant_names" class="item-variant">
                                {{ item.variant_label }}: {{ item.variant_names }}
                            </p>

                            <div class="item-bottom">
                                <!-- Qty control -->
                                <div class="qty-control">
                                    <button class="qty-btn" @click="cart.updateQty(item.id, item.qty - 1, item.variant_id)">−</button>
                                    <span class="qty-value">{{ item.qty }}</span>
                                    <button class="qty-btn" @click="cart.updateQty(item.id, item.qty + 1, item.variant_id)">+</button>
                                </div>
                                <!-- price -->
                                <span class="item-price">{{ formatPrice(item.sell_price * item.qty) }}</span>
                            </div>
                        </div>

                        <!-- remove -->
                        <button class="item-remove" @click="cart.removeItem(item.id, item.variant_id)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6"/>
                                <path d="M9 6V4h6v2"/>
                            </svg>
                        </button>
                    </div>
                </transition-group>
            </div>

            <!-- Footer -->
            <div v-if="cart.state.items.length > 0" class="cart-footer">
                <div class="subtotal-row">
                    <span class="subtotal-label">Subtotal</span>
                    <span class="subtotal-value">{{ formatPrice(cart.totalPrice) }}</span>
                </div>
                <p class="subtotal-note">Belum termasuk ongkir</p>
                <button class="btn-checkout" @click="goToCheckout">
                    Lanjut Checkout
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </button>
                <button class="btn-view-cart" @click="goToCart">Lihat Keranjang</button>
            </div>

        </div>
    </transition>
</template>

<script>

import '../../css/CartDrawer.css'
import { cartStore } from '../store/cartStore'

export default {
    name: 'CartDrawer',
    setup() {
        return { cart: cartStore }
    },
    computed: {
        // remainingForFreeShipping() {
        //     return Math.max(0, 150000 - this.cart.totalPrice)
        // },
        // shippingProgress() {
        //     return Math.min(100, (this.cart.totalPrice / 150000) * 100)
        // },
    },
    methods: {
        formatPrice(val) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR',
                minimumFractionDigits: 0, maximumFractionDigits: 0,
            }).format(val)
        },
        goToCheckout() {
            this.cart.close()
            this.$router.push('/checkout')
        },
        goToCart() {
            this.cart.close()
            this.$router.push('/cart')
        }
    }
}
</script>