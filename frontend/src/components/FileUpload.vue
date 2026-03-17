<template>
  <div class="file-upload-container">
    <Label v-if="label" :text="label" :required="required" />
    
    <div 
      class="drop-zone" 
      :class="{ 'is-dragover': isDragging }"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      @click="triggerFileInput"
    >
      <input 
        type="file" 
        ref="fileInput" 
        class="hidden-input" 
        :accept="accept" 
        @change="handleFileSelect" 
      />
      
      <div v-if="!selectedFile" class="placeholder-content">
        <svg xmlns="http://www.w3.org/2000/svg" class="upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <p class="mt-2">Klik atau seret file ke sini</p>
        <p class="text-xs text-gray-400 mt-1">Maks. {{ maxSize }}MB ({{ accept }})</p>
      </div>

      <div v-else class="file-selected">
        <span class="file-name">{{ selectedFile.name }}</span>
        <button type="button" @click.stop="clearFile" class="clear-btn">✕</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Label from './Label.vue';

const props = defineProps({
  label: { type: String, default: '' },
  required: { type: Boolean, default: false },
  accept: { type: String, default: 'image/*,.pdf' },
  maxSize: { type: Number, default: 2 } // in MB
});

const emit = defineEmits(['update:modelValue', 'error']);
const fileInput = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const selectedFile = ref<File | null>(null);

const triggerFileInput = () => fileInput.value?.click();

const processFile = (file: File) => {
  if (file.size > props.maxSize * 1024 * 1024) {
    emit('error', `Ukuran file maksimal ${props.maxSize}MB`);
    return;
  }
  selectedFile.value = file;
  emit('update:modelValue', file);
};

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0]; // Ambil file pertama dengan opsional chaining
  
  if (file) { // Jika file benar-benar ada (bukan undefined), baru proses
    processFile(file);
  }
};

const handleDrop = (event: DragEvent) => {
  isDragging.value = false;
  const file = event.dataTransfer?.files?.[0]; 
  
  if (file) {
    processFile(file);
  }
};

const clearFile = () => {
  selectedFile.value = null;
  if (fileInput.value) fileInput.value.value = '';
  emit('update:modelValue', null);
};
</script>

<style scoped>
.hidden-input { display: none; }
.drop-zone { border: 2px dashed #cbd5e1; border-radius: 0.5rem; padding: 2rem 1rem; text-align: center; cursor: pointer; transition: all 0.2s; background-color: #f8fafc; }
.drop-zone:hover, .is-dragover { border-color: #4f46e5; background-color: #eff6ff; }
.upload-icon { width: 2.5rem; height: 2.5rem; color: #94a3b8; margin: 0 auto; }
.placeholder-content { color: #64748b; font-size: 0.875rem; }
.file-selected { display: flex; align-items: center; justify-content: space-between; background: white; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; }
.file-name { font-size: 0.875rem; color: #334155; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 90%; }
.clear-btn { background: none; border: none; color: #ef4444; font-weight: bold; cursor: pointer; }
.text-xs { font-size: 0.75rem; } .text-gray-400 { color: #9ca3af; } .mt-1 { margin-top: 0.25rem; } .mt-2 { margin-top: 0.5rem; }
</style>