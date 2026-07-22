<script setup>
defineProps({
  status: { type: String, required: true },
  name: { type: String, default: '' },
  time: { type: String, default: '' },
})

defineEmits(['retry'])
</script>

<template>
  <div v-if="status === 'success'" class="bg-emerald-50 rounded-2xl p-8 text-center border border-emerald-100 animate-scale-in">
    <div class="mx-auto w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mb-4">
      <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </div>
    <h2 class="font-display text-xl text-emerald-700 mb-1">Absensi Berhasil</h2>
    <p class="text-emerald-700 font-semibold text-lg">{{ name }}</p>
    <p class="text-emerald-600 text-sm mt-1 font-mono">{{ time }}</p>
    <button
      class="mt-6 px-6 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 font-medium transition-all shadow-sm hover:shadow-md"
      @click="$emit('retry')">
      Scan Lagi
    </button>
  </div>

  <div v-else-if="status === 'error'" class="bg-red-50 rounded-2xl p-8 text-center border border-red-100 animate-scale-in">
    <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
      <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </div>
    <h2 class="font-display text-xl text-red-700 mb-2">Gagal</h2>
    <p class="text-red-600 mb-6"><slot /></p>
    <button
      class="px-6 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 font-medium transition-all shadow-sm hover:shadow-md"
      @click="$emit('retry')">
      Coba Lagi
    </button>
  </div>
</template>
