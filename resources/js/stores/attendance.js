import { defineStore } from 'pinia'
import { ref } from 'vue'
import attendanceApi from '../api/modules/attendance'

export const useAttendanceStore = defineStore('attendance', () => {
  const scanResult = ref(null)
  const history = ref([])
  const loading = ref(false)

  async function scan(qrToken) {
    loading.value = true
    try {
      const { data } = await attendanceApi.scan({ qr_token: qrToken })
      scanResult.value = data.data
      return data
    } finally {
      loading.value = false
    }
  }

  async function fetchHistory(params = {}) {
    loading.value = true
    try {
      const { data } = await attendanceApi.history(params)
      history.value = data.data?.data ?? []
      return data.data
    } catch {
      history.value = []
    } finally {
      loading.value = false
    }
  }

  return { scanResult, history, loading, scan, fetchHistory }
})
