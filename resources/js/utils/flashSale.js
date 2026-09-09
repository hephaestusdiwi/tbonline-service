// resources/js/utils/flashSale.js
//
// Sumber tunggal buat ngecek harga flash sale, dipakai di semua halaman yang
// nampilin harga produk & bisa add-to-cart (ProductDetail, ProductsPage, dst)
// — biar harga flash sale KONSISTEN ke mana pun customer add-to-cart, bukan
// cuma tampil doang di card homepage terus balik ke harga normal begitu
// masuk halaman detail / listing.
//
// Di-cache dalam satu request/page-load (nggak fetch ulang berkali-kali per
// komponen); refresh browser bakal fetch ulang, itu cukup karena flash sale
// jarang berubah dalam hitungan detik.

import axiosInstance from '../axios'

let cachedPromise = null

/**
 * @returns {Promise<Object<number, number>>} map { product_id: flash_price }
 * cuma berisi produk yang flash sale-nya lagi aktif & waktunya belum abis.
 */
export function getFlashPriceMap() {
    if (!cachedPromise) {
        cachedPromise = axiosInstance.get('/flash-sale')
            .then(({ data }) => {
                const map = {}
                const isLive = data.is_active && data.ends_at && new Date(data.ends_at).getTime() > Date.now()
                if (isLive) {
                    for (const p of (data.products || [])) {
                        if (p.flash_price !== null && p.flash_price !== undefined && Number(p.flash_price) < p.sell_price) {
                            map[p.product_id] = Number(p.flash_price)
                        }
                    }
                }
                return map
            })
            .catch(() => ({}))
    }
    return cachedPromise
}