<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../../stores/auth'
import AdminLayout from '../../layouts/AdminLayout.vue'
import TeacherLayout from '../../layouts/TeacherLayout.vue'
import leaveApi from '../../api/modules/leave'
import StatusBadge from '../../components/common/StatusBadge.vue'

import { DocumentTextIcon } from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const layout = computed(() => (auth.isAdmin ? AdminLayout : TeacherLayout))

const requests = ref([])
const loading = ref(false)
const error = ref('')
const approving = ref(null)
const rejecting = ref(null)

onMounted(fetchPending)

async function fetchPending() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await leaveApi.pending()
    requests.value = data.data?.data ?? []
  } catch {
    requests.value = []
    error.value = 'Gagal memuat data pengajuan.'
  } finally {
    loading.value = false
  }
}

async function approve(id) {
  approving.value = id
  error.value = ''
  try {
    await leaveApi.approve(id, {})
    fetchPending()
  } catch {
    error.value = 'Gagal menyetujui pengajuan.'
  } finally {
    approving.value = null
  }
}

async function reject(id) {
  const note = prompt('Masukkan alasan penolakan:')
  if (!note) return
  rejecting.value = id
  error.value = ''
  try {
    await leaveApi.reject(id, { rejection_note: note })
    fetchPending()
  } catch {
    error.value = 'Gagal menolak pengajuan.'
  } finally {
    rejecting.value = null
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
      <h1 class="font-display text-2xl text-slate-900">Approval Izin</h1>
      <p class="text-sm text-slate-500 mt-1">Daftar pengajuan izin yang menunggu persetujuan</p>
    </div>

    <div v-if="error" class="p-3.5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl mb-4">{{ error }}</div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
      <div v-if="loading" class="flex items-center justify-center py-16">
        <div class="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
      </div>

      <div v-else-if="!requests.length" class="py-16 text-center text-slate-400 text-sm">
        Tidak ada pengajuan yang menunggu.
      </div>

      <div v-else class="divide-y divide-slate-100">
        <div v-for="req in requests" :key="req.id" class="p-6 hover:bg-slate-50 transition-colors">
          <div class="flex items-start justify-between">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center shrink-0">
                <span class="text-sm font-bold text-slate-600">{{ req.user?.name?.charAt(0)?.toUpperCase() }}</span>
              </div>
              <div>
                <p class="font-semibold text-slate-900">{{ req.user?.name }}</p>
                <div class="flex items-center gap-2 mt-1">
                  <StatusBadge :variant="req.type">{{ req.type_label }}</StatusBadge>
                  <span class="text-sm text-slate-500">{{ formatDate(req.start_date) }} &mdash; {{ formatDate(req.end_date) }}</span>
                </div>
                <p class="text-sm text-slate-600 mt-2">{{ req.reason }}</p>
                <a v-if="req.attachment_path" :href="req.attachment_path" target="_blank" class="text-sm text-blue-600 mt-1 inline-flex items-center gap-1 hover:underline">
                  <DocumentTextIcon class="w-4 h-4" /> Lihat lampiran
                </a>
              </div>
            </div>
          </div>
          <div class="flex gap-2 mt-4 pl-14">
            <button
              @click="approve(req.id)"
              :disabled="approving === req.id || rejecting === req.id"
              class="rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md transition-all bg-emerald-600 text-white disabled:opacity-50 text-sm"
            >
              <span v-if="approving === req.id" class="inline-flex items-center gap-1.5">
                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
              </span>
              <span v-else>Setujui</span>
            </button>
            <button
              @click="reject(req.id)"
              :disabled="approving === req.id || rejecting === req.id"
              class="rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md transition-all bg-red-600 text-white disabled:opacity-50 text-sm"
            >
              <span v-if="rejecting === req.id" class="inline-flex items-center gap-1.5">
                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
              </span>
              <span v-else>Tolak</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </component>
</template>
