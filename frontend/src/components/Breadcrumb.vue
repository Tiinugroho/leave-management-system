<template>
  <nav class="breadcrumb">
    <span v-for="(item, index) in items" :key="index" class="breadcrumb-item">
      <span v-if="index !== 0" class="separator">/</span>
      <span :class="{ 'active': index === items.length - 1 }">{{ item }}</span>
    </span>
  </nav>
</template>

<script setup lang="ts">
defineProps<{ items: string[] }>();
</script>

<style scoped>
.breadcrumb {
  font-size: 0.875rem;
  color: #6b7280;
  display: flex;
  align-items: center;
  /* Menghapus margin-bottom agar center di navbar */
}

.separator {
  margin: 0 0.5rem;
  color: #d1d5db;
}

.active {
  color: #111827;
  font-weight: 600;
}
</style>