<script setup>
defineProps({
  show: Boolean,
  title: { type: String, default: '' },
  maxWidth: {
    type: String,
    default: 'max-w-lg',
    validator: (v) => ['max-w-sm', 'max-w-md', 'max-w-lg', 'max-w-xl', 'max-w-2xl', 'max-w-3xl', 'max-w-full'].includes(v),
  },
})

defineEmits(['close'])
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="$emit('close')"
      >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" />

        <div :class="['relative bg-white rounded-2xl shadow-xl w-full modal-content', maxWidth]">
          <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="font-display text-lg text-slate-900">{{ title }}</h3>
            <button
              @click="$emit('close')"
              class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="px-6 py-5">
            <slot />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active { transition: all 0.2s ease; }
.modal-leave-active { transition: all 0.15s ease; }
.modal-enter-from { opacity: 0; }
.modal-enter-from .modal-content { transform: scale(0.95); opacity: 0; }
.modal-leave-to { opacity: 0; }
.modal-leave-to .modal-content { transform: scale(0.95); opacity: 0; }
</style>
