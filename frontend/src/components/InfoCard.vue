<template>
  <div class="info-card">
    <div class="info-content">
      <h4 class="info-title">{{ title }}</h4>
      <p class="info-value">{{ value }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ title: string; value: string | number }>();
</script>

<style scoped>
.info-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #0ea5e9; flex: 1; }
.info-title { margin: 0; font-size: 0.875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.info-value { margin: 0.5rem 0 0; font-size: 1.5rem; font-weight: bold; color: #0f172a; }
</style>