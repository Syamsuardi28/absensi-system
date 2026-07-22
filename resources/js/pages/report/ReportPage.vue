<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import AdminLayout from '../../layouts/AdminLayout.vue'
import TeacherLayout from '../../layouts/TeacherLayout.vue'
import attendanceApi from '../../api/modules/attendance'
import classesApi from '../../api/modules/classes'
import StatusBadge from '../../components/common/StatusBadge.vue'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const layout = computed(() => (auth.isAdmin ? AdminLayout : TeacherLayout))

const classes = ref([])
const filters = ref({
  class_id: '',
  start_date: '',
  end_date: '',
  status: '',
})
const report = ref([])
const loading = ref(false)
const generating = ref(false)
const error = ref('')

const statusOptions = [
  { value: '', label: 'Semua Status' },
  { value: 'hadir', label: 'Hadir' },
  { value: 'terlambat', label: 'Terlambat' },
  { value: 'izin', label: 'Izin' },
  { value: 'sakit', label: 'Sakit' },
  { value: 'alpa', label: 'Alpa' },
]

onMounted(fetchClasses)

async function fetchClasses() {
  try {
    const { data } = await classesApi.list()
    classes.value = data.data?.data ?? []
  } catch {
    classes.value = []
  }
}

async function generateReport() {
  generating.value = true
  error.value = ''
  try {
    const params = {}
    if (filters.value.class_id) params.class_id = filters.value.class_id
    if (filters.value.start_date) params.start_date = filters.value.start_date
    if (filters.value.end_date) params.end_date = filters.value.end_date
    if (filters.value.status) params.status = filters.value.status
    const { data } = await attendanceApi.report(params)
    report.value = data.data?.data ?? []
  } catch {
    error.value = 'Gagal menghasilkan laporan.'
    report.value = []
  } finally {
    generating.value = false
  }
}

function formatDate(d) {
  if (!d) return '-'
  const date = new Date(d)
  if (isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatTime(d) {
  if (!d) return '-'
  const date = new Date(d)
  if (isNaN(date.getTime())) return '-'
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <component :is="layout">
    <div class="mb-6">
      <h1 class="font-display text-2xl text-slate-900">Laporan</h1>
      <p class="text-sm text-slate-500 mt-1">Laporan kehadiran siswa</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
      <div class="flex flex-wrap gap-3 items-end">
        <div class="w-full sm:w-auto">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Kelas</label>
          <select v-model="filters.class_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
            <option value="">Semua Kelas</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="w-full sm:w-auto">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Mulai</label>
          <input v-model="filters.start_date" type="date" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
        </div>
        <div class="w-full sm:w-auto">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Akhir</label>
          <input v-model="filters.end_date" type="date" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
        </div>
        <div class="w-full sm:w-auto">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
          <select v-model="filters.status" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
            <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>
        <div class="w-full sm:w-auto">
          <button
            @click="generateReport"
            :disabled="generating"
            class="rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md transition-all bg-blue-600 text-white disabled:opacity-50 text-sm inline-flex items-center gap-2"
          >
            <svg v-if="generating" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <MagnifyingGlassIcon v-else class="w-4 h-4" />
            {{ generating ? 'Memproses...' : 'Tampilkan' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="error" class="p-3.5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl mb-4">{{ error }}</div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div v-if="generating" class="flex items-center justify-center py-16">
        <div class="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
      </div>

      <div v-else-if="!report.length" class="py-16 text-center text-slate-400 text-sm">
        <p class="text-slate-500 font-medium">Belum ada data laporan</p>
        <p class="mt-1">Gunakan filter di atas untuk menampilkan laporan.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nama</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Jam</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kelas</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Tipe</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="item in report" :key="item.id" class="hover:bg-slate-50 transition-colors">
              <td class="px-6 py-3 text-sm text-slate-900 font-medium">{{ item.user?.name || item.student_name || '-' }}</td>
              <td class="px-6 py-3 text-sm text-slate-500">{{ formatDate(item.scan_time || item.date) }}</td>
              <td class="px-6 py-3 text-sm text-slate-500 font-mono">{{ formatTime(item.scan_time || item.time) }}</td>
              <td class="px-6 py-3">
                <StatusBadge :variant="item.status ?? 'default'">{{ item.status_label ?? item.status ?? '-' }}</StatusBadge>
              </td>
              <td class="px-6 py-3 text-sm text-slate-500">{{ item.class_name ?? item.class?.name ?? '-' }}</td>
              <td class="px-6 py-3 text-sm text-slate-500 capitalize">{{ item.type === 'self' ? 'Mandiri' : item.type === 'session' ? 'Sesi' : item.type || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </component>
</template>
