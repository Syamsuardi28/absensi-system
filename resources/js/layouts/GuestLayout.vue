<script setup>
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-50 p-4 sm:p-6 relative overflow-hidden">
    <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(circle, currentColor 1px, transparent 1px); background-size: 24px 24px; color: #D64045;"></div>

    <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-brand/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md animate-fade-in-up relative">
      <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-brand rounded-2xl shadow-lg shadow-brand/25 mb-6 relative">
          <div class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-white rounded-full shadow-sm flex items-center justify-center">
            <div class="w-1.5 h-1.5 bg-brand rounded-full"></div>
          </div>
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
          </svg>
        </div>
        <h1 class="font-display text-3xl text-slate-900 tracking-tight">SIAP</h1>
        <p class="text-slate-500 mt-2 text-sm tracking-wide">Sistem Informasi Absensi Sekolah</p>
      </div>

      <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand via-brand to-brand/40"></div>
        <slot />
      </div>

      <p class="mt-8 text-center text-xs text-slate-400">
        &copy; {{ new Date().getFullYear() }} SIAP &mdash; Sistem Pencatatan Kehadiran Digital
      </p>
    </div>
  </div>
</template>
