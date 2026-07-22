<script setup>
import { onMounted, computed } from 'vue'
import { useDashboardStore } from '../../stores/dashboard'
import { useAuthStore } from '../../stores/auth'
import AdminLayout from '../../layouts/AdminLayout.vue'
import TeacherLayout from '../../layouts/TeacherLayout.vue'
import StudentLayout from '../../layouts/StudentLayout.vue'
import StatusBadge from '../../components/common/StatusBadge.vue'
import {
  CheckCircleIcon,
  ClockIcon,
  DocumentTextIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
  UsersIcon,
} from '@heroicons/vue/24/outline'

const dashboard = useDashboardStore()
const auth = useAuthStore()

const layout = computed(() => {
  if (auth.isAdmin) return AdminLayout
  if (auth.isTeacher) return TeacherLayout
  return StudentLayout
})

onMounted(() => {
  dashboard.fetchSummary()
})

const statsCards = [
  { label: 'Hadir', key: 'hadir', icon: CheckCircleIcon, bg: 'bg-emerald-50', iconBg: 'bg-emerald-100', iconColor: 'text-emerald-600', ring: 'ring-emerald-500/20' },
  { label: 'Terlambat', key: 'terlambat', icon: ClockIcon, bg: 'bg-amber-50', iconBg: 'bg-amber-100', iconColor: 'text-amber-600', ring: 'ring-amber-500/20' },
  { label: 'Izin', key: 'izin', icon: DocumentTextIcon, bg: 'bg-blue-50', iconBg: 'bg-blue-100', iconColor: 'text-blue-600', ring: 'ring-blue-500/20' },
  { label: 'Sakit', key: 'sakit', icon: ExclamationTriangleIcon, bg: 'bg-purple-50', iconBg: 'bg-purple-100', iconColor: 'text-purple-600', ring: 'ring-purple-500/20' },
  { label: 'Alpa', key: 'alpa', icon: XCircleIcon, bg: 'bg-red-50', iconBg: 'bg-red-100', iconColor: 'text-red-600', ring: 'ring-red-500/20' },
]
</script>

<template>
  <component :is="layout">
    <div class="mb-8">
      <h1 class="font-display text-3xl text-slate-900 tracking-tight">Dashboard</h1>
      <p class="text-sm text-slate-500 mt-1">Ringkasan kehadiran hari ini</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8 stagger">
      <div
        v-for="card in statsCards"
        :key="card.key"
        :class="[card.bg, card.ring, 'rounded-2xl p-5 ring-1']"
      >
        <div :class="[card.iconBg, 'p-2 rounded-lg inline-flex mb-3']">
          <component :is="card.icon" :class="[card.iconColor, 'w-5 h-5']" />
        </div>
        <p class="font-display text-3xl text-slate-900 tracking-tight">
          {{ dashboard.summary?.today?.[card.key] ?? 0 }}
        </p>
        <p class="text-sm text-slate-500 mt-1">{{ card.label }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h2 class="font-display text-lg text-slate-900 mb-4">Absensi Terbaru Hari Ini</h2>
        <div
          v-if="!dashboard.summary?.recent_attendances?.length"
          class="flex flex-col items-center justify-center py-12"
        >
          <ClockIcon class="w-12 h-12 text-slate-300 mb-3" />
          <p class="text-sm text-slate-400">Belum ada absensi hari ini.</p>
        </div>
        <ul v-else class="divide-y divide-slate-100">
          <li
            v-for="item in dashboard.summary.recent_attendances.slice(0, 10)"
            :key="item.id"
            class="flex items-center justify-between py-3 first:pt-0 last:pb-0"
          >
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 bg-slate-100 rounded-full flex items-center justify-center shrink-0">
                <span class="text-xs font-bold text-slate-600">{{ item.name?.charAt(0)?.toUpperCase() || '?' }}</span>
              </div>
              <div class="min-w-0">
                <p class="text-sm font-medium text-slate-900 truncate">{{ item.name }}</p>
                <p class="text-xs text-slate-500 font-mono">{{ item.time }}</p>
              </div>
            </div>
            <StatusBadge :variant="item.status">{{ item.status_label }}</StatusBadge>
          </li>
        </ul>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h2 class="font-display text-lg text-slate-900 mb-4">Informasi Umum</h2>
        <div class="space-y-4">
          <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-xl">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
              <UsersIcon class="w-5 h-5 text-blue-600" />
            </div>
            <div>
              <p class="text-2xl font-display text-slate-900 tracking-tight">{{ dashboard.summary?.total_students ?? 0 }}</p>
              <p class="text-xs text-slate-500">Total Siswa Terdaftar</p>
            </div>
          </div>
          <div class="p-4 bg-slate-50 rounded-xl">
            <p class="text-xs text-slate-500 mb-1">Tanggal</p>
            <p class="text-sm font-medium text-slate-900">
              {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </component>
</template>
