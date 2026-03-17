<template>
  <div :class="['loader-wrapper', { 'fullscreen': fullScreen }]">
    <div class="loader-spinner"></div>
    <p v-if="text" class="loader-text">{{ text }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps({
  text: { type: String, default: 'Memuat data...' },
  fullScreen: { type: Boolean, default: false }
});
</script>

<style scoped>
.loader-wrapper { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; }
.fullscreen { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255, 255, 255, 0.8); z-index: 9999; }
.loader-spinner { width: 40px; height: 40px; border: 4px solid #e0e7ff; border-top-color: #4f46e5; border-radius: 50%; animation: spin 1s linear infinite; }
.loader-text { margin-top: 1rem; color: #6b7280; font-size: 0.875rem; font-weight: 500; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>