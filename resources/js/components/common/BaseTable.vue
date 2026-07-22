<script setup>
defineProps({
  headers: {
    type: Array,
    required: true,
    validator: (v) => v.every((h) => h && typeof h.key === 'string' && typeof h.label === 'string'),
  },
  items: {
    type: Array,
    required: true,
  },
  loading: Boolean,
  emptyText: { type: String, default: 'Tidak ada data.' },
  emptyIcon: { type: String, default: 'inbox' },
})

defineEmits(['row-click'])
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead>
        <tr class="border-b border-slate-200">
          <th v-for="h in headers" :key="h.key"
            :class="['px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider', h.class]">
            {{ h.label }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <tr v-if="loading">
          <td :colspan="headers.length" class="py-20 text-center">
            <div class="flex flex-col items-center gap-3">
              <div class="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
              <span class="text-sm text-slate-400">Memuat data...</span>
            </div>
          </td>
        </tr>
        <tr v-else-if="!items.length">
          <td :colspan="headers.length" class="py-20 text-center">
            <div class="flex flex-col items-center gap-3">
              <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="emptyIcon === 'inbox'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span class="text-sm text-slate-400">{{ emptyText }}</span>
            </div>
          </td>
        </tr>
        <tr v-for="(item, idx) in items" :key="item.id ?? idx"
          class="table-row cursor-pointer"
          @click="$emit('row-click', item)">
          <td v-for="h in headers" :key="h.key" :class="['px-5 py-3.5 text-sm', h.class]">
            <slot :name="`cell-${h.key}`" :item="item" :value="item[h.key]">
              {{ item[h.key] }}
            </slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
