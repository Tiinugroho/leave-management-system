<template>
  <label class="form-label">
    {{ text }}
    <span v-if="required" class="text-red-500 ml-1">*</span>
  </label>
</template>

<script setup lang="ts">
defineProps({
  text: { type: String, required: true },
  required: { type: Boolean, default: false }
});
</script>

<style scoped>
.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.375rem;
}
.text-red-500 { color: #ef4444; }
.ml-1 { margin-left: 0.25rem; }
</style>