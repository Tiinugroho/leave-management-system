<template>
  <div class="pagination-container" v-if="totalPages > 1">
    <button 
      class="page-btn" 
      :disabled="currentPage === 1" 
      @click="changePage(currentPage - 1)"
    >
      &laquo; Prev
    </button>
    
    <span class="page-info">
      Halaman <strong class="text-gray-900">{{ currentPage }}</strong> dari {{ totalPages }}
    </span>

    <button 
      class="page-btn" 
      :disabled="currentPage === totalPages" 
      @click="changePage(currentPage + 1)"
    >
      Next &raquo;
    </button>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  currentPage: { type: Number, required: true },
  totalPages: { type: Number, required: true }
});

const emit = defineEmits(['update:currentPage']);

const changePage = (page: number) => {
  if (page >= 1 && page <= props.totalPages) {
    emit('update:currentPage', page);
  }
};
</script>

<style scoped>
.pagination-container { display: flex; align-items: center; justify-content: space-between; padding: 1rem 0; border-top: 1px solid #e5e7eb; margin-top: 1rem; }
.page-btn { padding: 0.375rem 0.75rem; border-radius: 0.375rem; border: 1px solid #d1d5db; background: white; color: #374151; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: 0.2s; }
.page-btn:hover:not(:disabled) { background: #f9fafb; border-color: #9ca3af; }
.page-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.page-info { font-size: 0.875rem; color: #6b7280; }
.text-gray-900 { color: #111827; }
</style>