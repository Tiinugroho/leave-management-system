<template>
  <button 
    :type="type" 
    :class="['base-btn', `btn-${variant}`, `btn-${size}`, { 'is-loading': isLoading }]"
    :disabled="disabled || isLoading"
  >
    <svg v-if="isLoading" class="spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>

    <span v-if="$slots.iconLeft && !isLoading" class="icon-left">
      <slot name="iconLeft"></slot>
    </span>

    <span :class="{ 'opacity-0': isLoading }">
      <slot>{{ text }}</slot>
    </span>
  </button>
</template>

<script setup lang="ts">
defineProps({
  text: { type: String, default: 'Button' },
  type: { type: String as () => 'button' | 'submit' | 'reset', default: 'button' },
  variant: { type: String, default: 'primary' }, // primary, secondary, danger, outline, text-danger
  size: { type: String, default: 'md' },         // sm, md, lg
  isLoading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false }
});
// Emit 'click' dihapus karena Vue 3 otomatis menangani Fallthrough Attributes
</script>

<style scoped>
.base-btn { display: inline-flex; align-items: center; justify-content: center; font-weight: 600; cursor: pointer; transition: all 0.2s; border: 1px solid transparent; position: relative; }
.base-btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* --- Ukuran --- */
.btn-sm { padding: 0.25rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; }
.btn-md { padding: 0.5rem 1.25rem; border-radius: 0.5rem; font-size: 0.875rem; }
.btn-lg { padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-size: 1rem; }

/* --- Varian Warna --- */
.btn-primary { background-color: #4f46e5; color: white; }
.btn-primary:hover:not(:disabled) { background-color: #4338ca; }

.btn-secondary { background-color: white; color: #374151; border-color: #d1d5db; }
.btn-secondary:hover:not(:disabled) { background-color: #f9fafb; border-color: #9ca3af; }

.btn-danger { background-color: #ef4444; color: white; }
.btn-danger:hover:not(:disabled) { background-color: #dc2626; }

.btn-outline { background-color: transparent; color: #4f46e5; border-color: #4f46e5; }
.btn-outline:hover:not(:disabled) { background-color: #e0e7ff; }

/* Varian khusus untuk Hapus/Cancel di dalam Tabel */
.btn-text-danger { background-color: transparent; color: #ef4444; border-color: transparent; padding: 0.25rem 0.5rem; }
.btn-text-danger:hover:not(:disabled) { text-decoration: underline; background-color: #fef2f2; }

/* --- Utilities --- */
.spinner { position: absolute; width: 1.25rem; height: 1.25rem; animation: spin 1s linear infinite; }
.opacity-0 { opacity: 0; }
.opacity-25 { opacity: 0.25; }
.opacity-75 { opacity: 0.75; }
.icon-left { margin-right: 0.5rem; display: flex; align-items: center; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>