<template>
  <div class="app-container">
    <slot />
  </div>
</template>

<style scoped>
.app-container {
  width: 100%;
  padding: 0 0rem;
}

@media (min-width: 768px) {
  .app-container {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 0;
  }
}
</style>