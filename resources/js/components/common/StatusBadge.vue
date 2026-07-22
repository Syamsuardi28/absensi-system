<script setup>
defineProps({
  variant: { type: String, default: 'default' },
})
</script>

<template>
  <span
    :class="{
      'bg-emerald-50 text-emerald-700 ring-emerald-600/10': variant === 'hadir' || variant === 'approved' || variant === 'active',
      'bg-amber-50 text-amber-700 ring-amber-600/10': variant === 'terlambat' || variant === 'pending',
      'bg-blue-50 text-blue-700 ring-blue-600/10': variant === 'izin',
      'bg-violet-50 text-violet-700 ring-violet-600/10': variant === 'sakit' || variant === 'cuti',
      'bg-rose-50 text-rose-700 ring-rose-600/10': variant === 'alpa' || variant === 'rejected',
      'bg-slate-100 text-slate-600 ring-slate-500/10': variant === 'inactive' || variant === 'default',
    }"
    class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
  >
    <span
      :class="{
        'bg-emerald-500': variant === 'hadir' || variant === 'approved' || variant === 'active',
        'bg-amber-500': variant === 'terlambat' || variant === 'pending',
        'bg-blue-500': variant === 'izin',
        'bg-violet-500': variant === 'sakit' || variant === 'cuti',
        'bg-rose-500': variant === 'alpa' || variant === 'rejected',
        'bg-slate-400': variant === 'inactive' || variant === 'default',
      }"
      class="w-1.5 h-1.5 rounded-full mr-1.5"
    />
    <slot />
  </span>
</template>
