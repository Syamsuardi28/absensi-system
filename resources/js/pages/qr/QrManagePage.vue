<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import studentsApi from '../../api/modules/students'
import teachersApi from '../../api/modules/teachers'
import qrApi from '../../api/modules/qr'
import { QrCodeIcon } from '@heroicons/vue/24/outline'

const students = ref([])
const teachers = ref([])
const activeTab = ref('students')
const activeQr = ref(null)
const loading = ref(false)
const error = ref('')
const generating = ref(null)

onMounted(fetchData)

async function fetchData() {
  loading.value = true
  try {
    const [{ data: s }, { data: t }] = await Promise.all([
      studentsApi.list(),
      teachersApi.list(),
    ])
    students.value = s.data?.data ?? []
    teachers.value = t.data?.data ?? []
  } catch {
    students.value = []
    teachers.value = []
  } finally {
    loading.value = false
  }
}

async function regenerateQr(userId) {
  generating.value = userId
  try {
    const { data } = await qrApi.regenerate(userId)
    activeQr.value = { image: data.data.qr_image, token: data.data.qr_token }
    fetchData()
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal meregenerasi QR Code'
  } finally {
    generating.value = null
    setTimeout(() => { error.value = '' }, 5000)
  }
}
</script>

<template>
  <AdminLayout>
    <div class="mb-6">
      <h1 class="font-display text-2xl text-slate-900">Kelola QR Code</h1>
      <p class="text-sm text-slate-500 mt-1">Generate QR Code untuk guru dan siswa</p>
    </div>

    <div v-if="error" class="p-3.5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl mb-4">{{ error }}</div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
      <div class="flex gap-1 mb-6 bg-slate-100 rounded-xl p-1 w-fit">
        <button
          :class="[
            'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
            activeTab === 'students' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700',
          ]"
          @click="activeTab = 'students'"
        >
          Siswa
        </button>
        <button
          :class="[
            'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
            activeTab === 'teachers' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700',
          ]"
          @click="activeTab = 'teachers'"
        >
          Guru
        </button>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
      </div>

      <template v-else>
        <div v-if="activeTab === 'students'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <div
            v-for="s in students"
            :key="s.id"
            class="relative border border-slate-200 rounded-xl p-4 flex items-center justify-between hover:border-slate-300 transition-colors"
          >
            <div class="relative z-0">
              <p class="font-medium text-sm text-slate-900">{{ s.user?.name }}</p>
              <p class="text-xs text-slate-400">{{ s.nis }} &middot; {{ s.class_name }}</p>
            </div>
            <button
              @click="regenerateQr(s.user_id)"
              :disabled="generating === s.user_id"
              class="relative z-0 flex items-center gap-1.5 px-3 py-1.5 text-sm bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors font-medium disabled:opacity-50"
            >
              <QrCodeIcon class="w-4 h-4" />
              {{ generating === s.user_id ? '...' : 'QR' }}
            </button>
            <div
              v-if="generating === s.user_id"
              class="absolute inset-0 z-10 bg-white/70 rounded-xl flex items-center justify-center"
            >
              <div class="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
            </div>
          </div>
        </div>

        <div v-if="activeTab === 'teachers'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <div
            v-for="t in teachers"
            :key="t.id"
            class="relative border border-slate-200 rounded-xl p-4 flex items-center justify-between hover:border-slate-300 transition-colors"
          >
            <div class="relative z-0">
              <p class="font-medium text-sm text-slate-900">{{ t.user?.name }}</p>
              <p class="text-xs text-slate-400">{{ t.nip }}</p>
            </div>
            <button
              @click="regenerateQr(t.user_id)"
              :disabled="generating === t.user_id"
              class="relative z-0 flex items-center gap-1.5 px-3 py-1.5 text-sm bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors font-medium disabled:opacity-50"
            >
              <QrCodeIcon class="w-4 h-4" />
              {{ generating === t.user_id ? '...' : 'QR' }}
            </button>
            <div
              v-if="generating === t.user_id"
              class="absolute inset-0 z-10 bg-white/70 rounded-xl flex items-center justify-center"
            >
              <div class="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
            </div>
          </div>
        </div>
      </template>
    </div>

    <Teleport to="body">
      <div
        v-if="activeQr"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
        @click.self="activeQr = null"
      >
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center max-w-sm w-full">
          <h3 class="font-display text-lg text-slate-900 mb-4">QR Code</h3>
          <img :src="activeQr.image" alt="QR" class="mx-auto mb-6 rounded-xl border border-slate-200" />
          <button
            @click="activeQr = null"
            class="rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md transition-all border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm"
          >
            Tutup
          </button>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
