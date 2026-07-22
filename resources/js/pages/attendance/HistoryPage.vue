<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAttendanceStore } from '../../stores/attendance'
import { useAuthStore } from '../../stores/auth'
import AdminLayout from '../../layouts/AdminLayout.vue'
import TeacherLayout from '../../layouts/TeacherLayout.vue'
import StudentLayout from '../../layouts/StudentLayout.vue'
import StatusBadge from '../../components/common/StatusBadge.vue'
import { ClockIcon, FunnelIcon } from '@heroicons/vue/24/outline'

const attendance = useAttendanceStore()
const auth = useAuthStore()

const layout = computed(() => {
  if (auth.isAdmin) return AdminLayout
  if (auth.isTeacher) return TeacherLayout
  return StudentLayout
})

const now = new Date()
const selectedMonth = ref(String(now.getMonth() + 1).padStart(2, '0'))
const selectedYear = ref(String(now.getFullYear()))

const months = [
  { value: '01', label: 'Januari' },
  { value: '02', label: 'Februari' },
  { value: '03', label: 'Maret' },
  { value: '04', label: 'April' },
  { value: '05', label: 'Mei' },
  { value: '06', label: 'Juni' },
  { value: '07', label: 'Juli' },
  { value: '08', label: 'Agustus' },
  { value: '09', label: 'September' },
  { value: '10', label: 'Oktober' },
  { value: '11', label: 'November' },
  { value: '12', label: 'Desember' },
]

const years = (() => {
  const currentYear = new Date().getFullYear()
  const result = []
  for (let y = currentYear - 2; y <= currentYear + 1; y++) {
    result.push(y)
  }
  return result
})()

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return '-'
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatTime(dateStr) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return '-'
  return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

function getAbsenceType(item) {
  return item.type === 'self' ? 'Mandiri' : 'Sesi'
}

onMounted(loadHistory)

async function loadHistory() {
  try {
    await attendance.fetchHistory({ month: selectedMonth.value, year: selectedYear.value })
  } catch {
    attendance.history = []
  }
}
</script>

<template>
  <component :is="layout">
    <div class="mb-6">
      <h1 class="font-display text-3xl text-slate-900 tracking-tight">Riwayat Absensi</h1>
      <p class="text-sm text-slate-500 mt-1">Riwayat kehadiran Anda</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="p-6 border-b border-slate-100">
        <div class="flex flex-wrap items-end gap-3">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Bulan</label>
            <select
              v-model="selectedMonth"
              class="px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 outline-none bg-slate-50"
            >
              <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Tahun</label>
            <select
              v-model="selectedYear"
              class="px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 outline-none bg-slate-50"
            >
              <option v-for="y in years" :key="y" :value="String(y)">{{ y }}</option>
            </select>
          </div>
          <button
            @click="loadHistory"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-medium shadow-sm hover:shadow-md transition-all"
          >
            <FunnelIcon class="w-4 h-4" />
            Filter
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-left">Tanggal</th>
              <th class="px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-left">Jam</th>
              <th class="px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-left">Tipe</th>
              <th class="px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-left">Status</th>
              <th class="px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-left">Mapel</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-if="attendance.loading">
              <td colspan="5" class="px-5 py-3.5">
                <div class="flex flex-col items-center justify-center py-12">
                  <svg class="animate-spin w-8 h-8 text-blue-600 mb-3" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                  <p class="text-sm text-slate-400">Memuat data...</p>
                </div>
              </td>
            </tr>

            <tr v-else-if="!attendance.history.length">
              <td colspan="5" class="px-5 py-3.5">
                <div class="flex flex-col items-center justify-center py-12 animate-fade-in-up">
                  <ClockIcon class="w-12 h-12 text-slate-300 mb-3" />
                  <p class="text-sm text-slate-400">Belum ada riwayat absensi.</p>
                </div>
              </td>
            </tr>

            <template v-else>
              <tr
                v-for="item in attendance.history"
                :key="item.id"
                class="hover:bg-slate-50 transition-colors"
              >
                <td class="px-5 py-3.5 text-sm text-slate-700">{{ formatDate(item.scan_time) }}</td>
                <td class="px-5 py-3.5 text-sm">
                  <span class="font-mono text-slate-500">{{ formatTime(item.scan_time) }}</span>
                </td>
                <td class="px-5 py-3.5 text-sm text-slate-500">{{ getAbsenceType(item) }}</td>
                <td class="px-5 py-3.5">
                  <StatusBadge :variant="item.status">{{ item.status_label }}</StatusBadge>
                </td>
                <td class="px-5 py-3.5 text-sm text-slate-500">{{ item.schedule?.subject?.name || '-' }}</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </component>
</template>
