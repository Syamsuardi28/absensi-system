<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
import {
  Bars3Icon, XMarkIcon, HomeIcon, QrCodeIcon, ClockIcon,
  DocumentTextIcon, ArrowLeftOnRectangleIcon, UsersIcon,
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const router = useRouter()
const sidebarOpen = ref(false)

let clockTimer = null

onMounted(() => {
  const update = () => {
    const now = new Date()
    const c = document.getElementById('teacher-clock')
    const d = document.getElementById('teacher-date')
    if (c) c.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
    if (d) {
      const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
      d.textContent = `${days[now.getDay()]}, ${now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`
    }
  }
  update()
  clockTimer = setInterval(update, 10000)
})

onUnmounted(() => {
  clearInterval(clockTimer)
})

const menuGroups = [
  {
    items: [
      { name: 'Dashboard', to: '/dashboard', icon: HomeIcon },
      { name: 'Scan Absensi', to: '/attendance/scan', icon: QrCodeIcon },
      { name: 'Riwayat', to: '/attendance/history', icon: ClockIcon },
      { name: 'Pengajuan Izin', to: '/leave/request', icon: DocumentTextIcon },
    ],
  },
  {
    label: 'Guru',
    items: [
      { name: 'Absensi Sesi', to: '/attendance/session', icon: QrCodeIcon },
      { name: 'Approval Izin', to: '/leave/approval', icon: DocumentTextIcon },
      { name: 'Laporan', to: '/reports', icon: UsersIcon },
    ],
  },
]

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="flex h-screen bg-slate-50">
    <Transition name="slide">
      <aside v-if="sidebarOpen" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 lg:hidden">
        <nav class="flex flex-col h-full">
          <div class="flex items-center justify-between h-16 px-5 border-b border-white/5">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                <QrCodeIcon class="w-4 h-4 text-white" />
              </div>
              <span class="text-sm font-semibold text-white">SIAP</span>
            </div>
            <button class="text-slate-400 hover:text-white" @click="sidebarOpen = false">
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>
          <div class="flex-1 px-3 py-4 overflow-y-auto space-y-5">
            <div v-for="(group, gi) in menuGroups" :key="gi">
              <p v-if="group.label" class="px-2 mb-2 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">{{ group.label }}</p>
              <div class="space-y-1">
                <RouterLink v-for="link in group.items" :key="link.to" :to="link.to"
                  class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-400 hover:bg-white/5 hover:text-white transition-all"
                  :class="{ 'bg-white/10 text-white font-medium': $route.path === link.to }"
                  @click="sidebarOpen = false">
                  <component :is="link.icon" class="w-5 h-5 shrink-0" />
                  {{ link.name }}
                </RouterLink>
              </div>
            </div>
          </div>
          <div class="px-3 py-3 border-t border-white/5">
            <button @click="handleLogout"
              class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm text-slate-400 hover:bg-white/5 hover:text-white transition-all">
              <ArrowLeftOnRectangleIcon class="w-5 h-5" /> Keluar
            </button>
          </div>
        </nav>
      </aside>
    </Transition>

    <aside class="hidden lg:flex flex-col w-64 bg-slate-900 shrink-0">
      <div class="flex items-center gap-3 h-16 px-5 border-b border-white/5">
        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm">
          <QrCodeIcon class="w-4 h-4 text-white" />
        </div>
        <div>
          <span class="text-sm font-semibold text-white tracking-tight">SIAP</span>
          <p class="text-[10px] text-slate-500 -mt-0.5">Absensi Sekolah</p>
        </div>
      </div>

      <nav class="flex-1 px-3 py-4 overflow-y-auto">
        <div class="pb-4 mb-5 border-b border-white/5">
          <p class="font-display text-3xl text-white tracking-tight leading-none tabular-nums" id="teacher-clock">--:--</p>
          <p class="text-slate-400 text-xs mt-1.5" id="teacher-date">---</p>
          <div class="flex items-center gap-2 mt-3">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse-subtle" />
            <span class="text-[11px] text-slate-500">Jam Sekolah</span>
          </div>
        </div>

        <div v-for="(group, gi) in menuGroups" :key="gi" :class="{ 'mt-5': gi > 0 }">
          <p v-if="group.label" class="px-2 mb-2 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">{{ group.label }}</p>
          <div class="space-y-1">
            <RouterLink v-for="link in group.items" :key="link.to" :to="link.to"
              class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-400 hover:bg-white/5 hover:text-white transition-all"
              :class="{ 'bg-white/10 text-white font-medium shadow-sm': $route.path === link.to }">
              <component :is="link.icon" class="w-5 h-5 shrink-0" />
              {{ link.name }}
            </RouterLink>
          </div>
        </div>
      </nav>

      <div class="px-3 py-3 border-t border-white/5">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center shrink-0 shadow-sm">
            <span class="text-xs font-bold text-white">{{ auth.user?.name?.charAt(0)?.toUpperCase() || '?' }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-white truncate">{{ auth.user?.name }}</p>
            <p class="text-[11px] text-slate-500 capitalize">{{ auth.user?.roles?.[0]?.replace(/_/g, ' ') }}</p>
          </div>
          <button @click="handleLogout" class="p-1.5 text-slate-500 hover:text-white rounded-lg hover:bg-white/10 transition-all">
            <ArrowLeftOnRectangleIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
    </aside>

    <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" />

    <div class="flex-1 flex flex-col min-w-0">
      <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-6 shrink-0">
        <button class="lg:hidden text-slate-500 hover:text-slate-700" @click="sidebarOpen = true">
          <Bars3Icon class="w-6 h-6" />
        </button>
        <div class="flex items-center gap-3 ml-auto">
          <span class="text-sm text-slate-600 hidden sm:inline">{{ auth.user?.name }}</span>
          <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center">
            <span class="text-xs font-semibold text-slate-600">{{ auth.user?.name?.charAt(0)?.toUpperCase() || '?' }}</span>
          </div>
        </div>
      </header>
      <main class="flex-1 p-4 lg:p-6 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<style>
.slide-enter-active, .slide-leave-active { transition: transform 0.25s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }
</style>
