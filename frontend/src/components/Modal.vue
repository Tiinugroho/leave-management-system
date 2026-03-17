<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="isOpen" class="modal-backdrop" @click.self="$emit('close')">
                <div class="modal-container">
                    
                    <div class="modal-header">
                        <h3 v-if="title" class="modal-title">{{ title }}</h3>
                        <button @click="$emit('close')" class="close-x">&times;</button>
                    </div>

                    <div class="modal-body">
                        <slot></slot>
                    </div>

                    <div v-if="showFooter" class="modal-footer">
                        <slot name="footer">
                            <button @click="$emit('close')" class="btn-primary-modal">Tutup</button>
                        </slot>
                    </div>
                    
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
    isOpen: boolean;
    title?: string;
    showFooter?: boolean;
}>(), {
    showFooter: true
});

defineEmits(['close']);
</script>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 99999; /* Z-index dimaksimalkan */
}

.modal-container {
    background: white;
    width: 90%;
    max-width: 450px;
    border-radius: 1rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.modal-header {
    padding: 1.5rem 1.5rem 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
}

.close-x {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #9ca3af;
    cursor: pointer;
}

.modal-body {
    padding: 1rem 1.5rem 1.5rem;
    color: #4b5563;
    line-height: 1.6;
}

.modal-footer {
    padding: 1rem 1.5rem;
    background-color: #f9fafb;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem; /* Tambah gap agar jika ada 2 tombol tidak dempet */
}

.btn-primary-modal {
    background-color: #4f46e5;
    color: white;
    padding: 0.6rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
}
</style>