<script setup>
import { ref, computed } from 'vue'
import { useAttendanceStore } from '../../stores/attendance'
import { useAuthStore } from '../../stores/auth'
import AdminLayout from '../../layouts/AdminLayout.vue'
import TeacherLayout from '../../layouts/TeacherLayout.vue'
import StudentLayout from '../../layouts/StudentLayout.vue'
import QrScanner from '../../components/attendance/QrScanner.vue'
import ScanResult from '../../components/attendance/ScanResult.vue'

const attendanceStore = useAttendanceStore()
const auth = useAuthStore()

const layout = computed(() => {
  if (auth.isAdmin) return AdminLayout
  if (auth.isTeacher) return TeacherLayout
  return StudentLayout
})

const phase = ref('scan')
const resultStatus = ref('')
const resultName = ref('')
const resultTime = ref('')
const scanError = ref(false)

async function handleScan(token) {
  try {
    const response = await attendanceStore.scan(token)
    scanError.value = false
    resultStatus.value = 'success'
    resultName.value = attendanceStore.scanResult?.user?.name || ''
    resultTime.value = attendanceStore.scanResult?.scan_time
      ? new Date(attendanceStore.scanResult.scan_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
      : ''
    phase.value = 'result'
  } catch (err) {
    scanError.value = true
    resultStatus.value = 'error'
    attendanceStore.scanResult = {
      message: err.response?.data?.message || 'Gagal memproses absensi.',
    }
    phase.value = 'result'
  }
}

function handleScannerError(err) {
  scanError.value = true
}

function handleRetry() {
  phase.value = 'scan'
  attendanceStore.scanResult = null
  scanError.value = false
}
</script>

<template>
  <component :is="layout">
    <div class="mb-6">
      <h1 class="font-display text-3xl text-slate-900 tracking-tight">Scan Absensi</h1>
      <p class="text-sm text-slate-500 mt-1">Arahkan QR Code ke kamera</p>
    </div>

    <div class="max-w-lg mx-auto">
      <ScanResult
        v-if="phase === 'result' && resultStatus === 'success'"
        status="success"
        :name="resultName"
        :time="resultTime"
        @retry="handleRetry"
      />

      <ScanResult
        v-else-if="phase === 'result' && resultStatus === 'error'"
        status="error"
        @retry="handleRetry"
      >
        {{ attendanceStore.scanResult?.message || 'Gagal memproses absensi.' }}
      </ScanResult>

      <QrScanner
        v-else
        @scan="handleScan"
        @error="handleScannerError"
      />

      <div v-if="scanError && phase !== 'result'" class="mt-4 p-3.5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl">
        Gagal mengakses kamera. Pastikan Anda telah memberikan izin kamera.
      </div>
    </div>
  </component>
</template>
