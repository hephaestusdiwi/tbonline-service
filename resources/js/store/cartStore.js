import { reactive, computed } from 'vue'

function normalizeItem(item) {
  return {
    ...item,
    qty: Number.isFinite(Number(item.qty)) && Number(item.qty) > 0 ? Number(item.qty) : 1,
    sell_price: Number.isFinite(Number(item.sell_price)) ? Number(item.sell_price) : 0,
    variant_id: item.variant_id ?? null,
  }
}

function loadInitialItems() {
  try {
    const raw = JSON.parse(localStorage.getItem('cart') || '[]')
    if (!Array.isArray(raw)) return []
    return raw.filter(i => i && i.id != null).map(normalizeItem)
  } catch (e) {
    console.error('Gagal parse cart dari localStorage, reset ke kosong:', e)
    return []
  }
}

const state = reactive({
  isOpen: false,
  items: loadInitialItems(),
})

function save() {
  localStorage.setItem('cart', JSON.stringify(state.items))
}

save()

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
    const item = normalizeItem(product)
    const found = state.items.find(
      i => i.id === item.id && i.variant_id === item.variant_id
    )
    if (found) {
      found.qty += item.qty
    } else {
      state.items.push(item)
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