<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import AdminLayout from '../../layouts/AdminLayout.vue'
import TeacherLayout from '../../layouts/TeacherLayout.vue'
import StudentLayout from '../../layouts/StudentLayout.vue'
import leaveApi from '../../api/modules/leave'
import StatusBadge from '../../components/common/StatusBadge.vue'
import { DocumentTextIcon } from '@heroicons/vue/24/outline'

const auth = useAuthStore()

const layout = computed(() => {
  if (auth.isAdmin) return AdminLayout
  if (auth.isTeacher) return TeacherLayout
  return StudentLayout
})

const form = ref({ type: 'izin', start_date: '', end_date: '', reason: '', attachment: null })
const loading = ref(false)
const error = ref('')
const requests = ref([])
const loadingRequests = ref(false)

onMounted(fetchMyRequests)

async function fetchMyRequests() {
  loadingRequests.value = true
  try {
    const { data } = await leaveApi.myRequests()
    requests.value = data.data?.data ?? []
  } catch {
    requests.value = []
  } finally {
    loadingRequests.value = false
  }
}

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    const payload = new FormData()
    payload.append('type', form.value.type)
    payload.append('start_date', form.value.start_date)
    payload.append('end_date', form.value.end_date)
    payload.append('reason', form.value.reason)
    if (form.value.attachment) payload.append('attachment', form.value.attachment)
    await leaveApi.submit(payload)
    form.value = { type: 'izin', start_date: '', end_date: '', reason: '', attachment: null }
    fetchMyRequests()
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal mengirim pengajuan.'
  } finally {
    loading.value = false
  }
}

function formatDate(d) {
  if (!d) return '-'
  const date = new Date(d)
  if (isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
  <component :is="layout">
    <div class="mb-6">
      <h1 class="font-display text-2xl text-slate-900">Pengajuan Izin</h1>
      <p class="text-sm text-slate-500 mt-1">Ajukan izin, sakit, atau cuti</p>
    </div>

    <div class="max-w-lg bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div v-if="error" class="p-3.5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl">{{ error }}</div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Jenis</label>
          <select v-model="form.type" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
            <option value="izin">Izin</option>
            <option value="sakit">Sakit</option>
            <option value="cuti">Cuti</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Mulai</label>
            <input v-model="form.start_date" type="date" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Selesai</label>
            <input v-model="form.end_date" type="date" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Alasan</label>
          <textarea v-model="form.reason" required rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" placeholder="Tulis alasan ..." />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Lampiran (opsional)</label>
          <input type="file" @change="(e) => (form.attachment = e.target.files[0])" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md transition-all bg-blue-600 text-white disabled:opacity-50"
        >
          <span v-if="loading" class="inline-flex items-center gap-2">
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            Mengirim...
          </span>
          <span v-else>Kirim Pengajuan</span>
        </button>
      </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-200">
        <h2 class="font-display text-lg text-slate-900">Pengajuan Saya</h2>
      </div>

      <div v-if="loadingRequests" class="flex items-center justify-center py-16">
        <div class="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
      </div>

      <div v-else-if="!requests.length" class="py-16 text-center text-slate-400 text-sm">
        Belum ada pengajuan.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Tipe</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Alasan</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Lampiran</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="req in requests" :key="req.id" class="hover:bg-slate-50 transition-colors">
              <td class="px-6 py-3 text-sm text-slate-700">{{ req.type_label }}</td>
              <td class="px-6 py-3 text-sm text-slate-500">{{ formatDate(req.start_date) }} &mdash; {{ formatDate(req.end_date) }}</td>
              <td class="px-6 py-3 text-sm text-slate-600 max-w-xs truncate">{{ req.reason }}</td>
              <td class="px-6 py-3">
                <StatusBadge :variant="req.status">{{ req.status_label }}</StatusBadge>
              </td>
              <td class="px-6 py-3 text-sm">
                <a v-if="req.attachment_path" :href="req.attachment_path" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:underline">
                  <DocumentTextIcon class="w-4 h-4" /> Lihat
                </a>
                <span v-else class="text-slate-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </component>
</template>
