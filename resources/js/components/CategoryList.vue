<!-- src/components/CategoryList.vue -->
<template>
  <section class="cl-section" v-if="!loading && categories.length > 0">
    <div class="cl-container">
      <h2 class="cl-title">Kategori Produk</h2>

      <div class="cl-scroller">
        <router-link
          v-for="cat in categories"
          :key="cat.id"
          :to="{ path: '/products', query: { category: (cat.product_categories || [cat.name]).join(',') } }"
          class="cl-item"
        >
          <div class="cl-photo-wrap">
            <img
              v-if="cat.photo_url"
              :src="cat.photo_url"
              :alt="cat.name"
              class="cl-photo"
              loading="lazy"
            />
            <div v-else class="cl-photo-empty">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
          </div>
          <p class="cl-name">{{ cat.name }}</p>
        </router-link>
      </div>
    </div>
  </section>

  <section class="cl-section" v-else-if="loading">
    <div class="cl-container">
      <div class="skeleton skeleton-title" />
      <div class="cl-scroller">
        <div v-for="n in 6" :key="n" class="cl-item">
          <div class="cl-photo-wrap skeleton skeleton-photo" />
          <div class="skeleton skeleton-name" />
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import axiosInstance from '../axios'

export default {
  name: 'CategoryList',

  data() {
    return {
      categories: [],
      loading: true,
    }
  },

  async mounted() {
    try {
      const { data } = await axiosInstance.get('/categories')
      this.categories = (data.data ?? data).filter(c => c.is_active)
    } catch (e) {
      console.error('Failed to load categories:', e)
    } finally {
      this.loading = false
    }
  },
}
</script>

<style scoped>
.cl-section {
  padding: 14px 0 20px;
  font-family: "Poppins", sans-serif;
  background: #f1f2f4;
}

.cl-container {
  width: 100%;
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 16px;
  box-sizing: border-box;
}

.cl-title {
  color: #bd2028;
  font-weight: 600;
  font-size: 1.5rem;
  padding-top: 10px;
  line-height: 2;
  text-align: center;
  margin: 0 0 20px;
}

.cl-scroller {
  display: flex;
  align-items: flex-start;
  justify-content: center;
  gap: 42px;
  overflow-x: auto;
  scrollbar-width: none;
  -ms-overflow-style: none;
  padding: 0;
}

.cl-scroller::-webkit-scrollbar {
  display: none;
}

.cl-item {
  flex: 0 0 180px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 7px;
  width: 180px;
  text-decoration: none;
  cursor: pointer;
}

.cl-photo-wrap {
  width: 164px;
  height: 164px;
  overflow: hidden;
  border: none;
  flex-shrink: 0;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.cl-item:hover .cl-photo-wrap {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}

.cl-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.cl-photo-empty {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
}

.cl-photo-empty svg {
  width: 1.5rem;
  height: 1.5rem;
  color: #ccc;
}

.cl-name {
  width: 100%;
  margin: 0;
  color: #333;
  font-size: 1rem;
  font-weight: 600;
  line-height: 1.2;
  text-align: center;
  text-transform: uppercase;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.skeleton {
  background: linear-gradient(
    90deg,
    #e7e7e7 25%,
    #ddd 50%,
    #e7e7e7 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 6px;
}

.skeleton-title {
  width: 140px;
  height: 18px;
  margin: 0 auto 20px;
  border-radius: 4px;
}

.skeleton-photo {
  border-radius: 0;
}

.skeleton-name {
  width: 100%;
  height: 8px;
  border-radius: 4px;
  margin-top: 2px;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

@media (max-width: 900px) {
  .cl-scroller {
    gap: 28px;
  }
}

/* =========================
   MOBILE — SEPERTI REFERENSI
   ========================= */

@media (max-width: 640px) {
  .cl-section {
    padding: 74px 0 34px;
    background: #fff;
  }

  .cl-container {
    padding: 0 24px;
  }

  .cl-title {
    color: #222;
    font-size: 1.55rem;
    font-weight: 400;
    line-height: 1.25;
    text-align: left;
    padding-top: 0;
    margin: 0 0 42px;
  }

  .cl-scroller {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 22px;
    row-gap: 48px;

    width: 100%;
    overflow: visible;
    padding: 0;
  }

  .cl-item {
    width: 100%;
    min-width: 0;
    flex: none;

    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
  }

  .cl-photo-wrap {
    width: 100%;
    height: auto;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    background: transparent;
    border: none;
    box-shadow: none;
  }

  .cl-item:hover .cl-photo-wrap {
    transform: none;
    box-shadow: none;
  }

  .cl-photo {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }

  .cl-photo-empty {
    background: #f5f5f5;
  }

  .cl-name {
    width: 100%;
    color: #222;
    font-size: 0.95rem;
    font-weight: 400;
    line-height: 1.35;
    text-align: center;
    text-transform: none;

    white-space: normal;
    overflow: visible;
    text-overflow: clip;
  }
}

@media (max-width: 380px) {
  .cl-container {
    padding: 0 18px;
  }

  .cl-title {
    font-size: 1.45rem;
    margin-bottom: 36px;
  }

  .cl-scroller {
    column-gap: 14px;
    row-gap: 42px;
  }

  .cl-name {
    font-size: 0.88rem;
  }
}
</style>
