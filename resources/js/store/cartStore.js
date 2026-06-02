// src/store/cartStore.js
import { reactive, computed } from 'vue'

const state = reactive({
  isOpen: false,
  items: JSON.parse(localStorage.getItem('cart') || '[]'),
})

function save() {
  localStorage.setItem('cart', JSON.stringify(state.items))
}

export const cartStore = {
  state,

  get totalItems() {
    return state.items.reduce((sum, i) => sum + i.qty, 0)
  },

  get totalPrice() {
    return state.items.reduce((sum, i) => sum + i.sell_price * i.qty, 0)
  },

  open()  { state.isOpen = true },
  close() { state.isOpen = false },
  toggle(){ state.isOpen = !state.isOpen },

  addItem(product) {
    console.log('addItem payload:', product)
    const found = state.items.find(
      i => i.id === product.id && i.variant_id === product.variant_id  // ✅
    )
    if (found) {
      found.qty += product.qty  // ✅ bukan found.qty++
    } else {
      state.items.push({ ...product })  // ✅ bukan qty: 1
    }
    save()
    state.isOpen = true
  },

  removeItem(id, variantId = null) {
    state.items = state.items.filter(
      i => !(i.id === id && i.variant_id === variantId)
    )
    save()
  },

  updateQty(id, qty, variantId = null) {
    if (qty < 1) return this.removeItem(id, variantId)
    const item = state.items.find(
      i => i.id === id && i.variant_id === variantId
    )
    if (item) { item.qty = qty; save() }
  },

  clearCart() {
    state.items = []
    save()
  }
}