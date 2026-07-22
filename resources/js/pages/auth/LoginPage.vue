<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useRouter, useRoute } from 'vue-router'
import GuestLayout from '../../layouts/GuestLayout.vue'
import { EyeIcon, EyeSlashIcon, ChevronDownIcon, ChevronUpIcon } from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const error = ref('')
const showDemo = ref(false)

const demoAccounts = [
  { role: 'Admin', email: 'admin@sekolah.test', password: 'password', color: 'bg-brand-100 text-brand-600' },
  { role: 'Guru', email: 'guru@sekolah.test', password: 'password', color: 'bg-teal-100 text-teal-600' },
  { role: 'Siswa', email: 'siswa@sekolah.test', password: 'password', color: 'bg-amber-100 text-amber-600' },
]

function fillDemo(emailAddr) {
  email.value = emailAddr
  password.value = 'password'
}

async function handleLogin() {
  error.value = ''
  try {
    await auth.login(email.value, password.value)
    const redirect = route.query.redirect
    router.push(redirect?.startsWith('/') ? redirect : '/dashboard')
  } catch (err) {
    error.value = err.response?.data?.message || 'Login gagal. Periksa kembali email dan password.'
  }
}
</script>

<template>
  <GuestLayout>
    <h2 class="font-display text-xl text-slate-900 mb-1 tracking-tight">Masuk ke Akun</h2>
    <p class="text-sm text-slate-500 mb-6">Silakan masuk untuk melanjutkan ke sistem</p>

    <form @submit.prevent="handleLogin" class="space-y-4">
      <div
        v-if="error"
        class="p-3.5 text-sm text-brand-600 bg-brand-50 border border-brand-100 rounded-xl animate-scale-in flex items-start gap-2.5"
      >
        <svg class="w-5 h-5 shrink-0 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
        </svg>
        <span>{{ error }}</span>
      </div>

      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
        <div class="relative">
          <svg class="absolute left-3.5 top-3 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
          </svg>
          <input
            v-model="email"
            type="email"
            required
            class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition-all text-sm bg-slate-50 placeholder:text-slate-400 hover:border-slate-300"
            placeholder="nama@sekolah.test"
          />
        </div>
      </div>

      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
        <div class="relative">
          <svg class="absolute left-3.5 top-3 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
          </svg>
          <input
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            required
            class="w-full pl-10 pr-11 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition-all text-sm bg-slate-50 placeholder:text-slate-400 hover:border-slate-300"
            placeholder="&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;"
          />
          <button
            type="button"
            class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 transition-colors p-0.5"
            @click="showPassword = !showPassword"
          >
            <component :is="showPassword ? EyeSlashIcon : EyeIcon" class="w-4 h-4" />
          </button>
        </div>
      </div>

      <button
        type="submit"
        :disabled="auth.loading"
        class="w-full px-4 py-2.5 bg-brand text-white rounded-xl hover:bg-brand-600 disabled:opacity-60 font-semibold shadow-sm hover:shadow-md hover:shadow-brand/25 transition-all text-sm tracking-wide mt-6 relative overflow-hidden group"
      >
        <span v-if="auth.loading" class="inline-flex items-center gap-2">
          <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
          Memproses...
        </span>
        <span v-else class="inline-flex items-center gap-2">
          Masuk
          <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
          </svg>
        </span>
      </button>
    </form>

    <div class="mt-8 border border-slate-200 rounded-xl overflow-hidden">
      <button
        type="button"
        class="w-full flex items-center justify-between px-4 py-3 text-sm text-slate-600 hover:bg-slate-50 transition-colors font-medium"
        @click="showDemo = !showDemo"
      >
        <span class="flex items-center gap-2">
          <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
            <path d="M11.644 1.59a.75.75 0 01.712 0l9.75 5.25a.75.75 0 010 1.32l-9.75 5.25a.75.75 0 01-.712 0l-9.75-5.25a.75.75 0 010-1.32l9.75-5.25z" />
            <path d="M3.265 10.602l7.668 4.129a2.25 2.25 0 002.134 0l7.668-4.13 1.37.739a.75.75 0 010 1.32l-9.75 5.25a.75.75 0 01-.71 0l-9.75-5.25a.75.75 0 010-1.32l1.37-.738z" />
            <path d="M10.933 19.231l-7.668-4.13-1.37.739a.75.75 0 000 1.32l9.75 5.25c.221.12.489.12.71 0l9.75-5.25a.75.75 0 000-1.32l-1.37-.738-7.668 4.13a2.25 2.25 0 01-2.134-.001z" />
          </svg>
          Akun Demo
        </span>
        <component :is="showDemo ? ChevronUpIcon : ChevronDownIcon" class="w-4 h-4 text-slate-400 transition-transform duration-200" />
      </button>
      <div v-if="showDemo" class="px-4 pb-5 pt-1 space-y-2 animate-fade-in">
        <p class="text-xs text-slate-400 px-1">Klik akun untuk mengisi otomatis</p>
        <button
          v-for="account in demoAccounts"
          :key="account.email"
          type="button"
          class="w-full flex items-center justify-between text-xs bg-slate-50 rounded-xl px-3.5 py-2.5 hover:bg-slate-100 transition-colors group/row text-left"
          @click="fillDemo(account.email)"
        >
          <span class="flex items-center gap-2.5 min-w-0">
            <span :class="['shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-[10px] font-bold', account.color]">
              {{ account.role[0] }}
            </span>
            <span class="font-semibold text-slate-700">{{ account.role }}</span>
          </span>
          <div class="flex items-center gap-1.5 shrink-0 ml-4">
            <span class="font-mono text-[11px] text-slate-500">{{ account.email }}</span>
            <span class="text-slate-300">/</span>
            <span class="font-mono text-[11px] text-slate-400">{{ account.password }}</span>
            <svg class="w-3 h-3 text-slate-300 group-hover/row:text-brand transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
            </svg>
          </div>
        </button>
      </div>
    </div>
  </GuestLayout>
</template>
