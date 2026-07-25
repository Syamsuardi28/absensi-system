import { ref } from 'vue'
import attendanceApi from '../api/modules/attendance'

export function useAttendance() {
  const history = ref([])
  const scanResult = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function scan(qrToken) {
    loading.value = true
    error.value = null
    try {
      const { data } = await attendanceApi.scan({ qr_token: qrToken })
      scanResult.value = data.data
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal melakukan absensi'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchHistory(params = {}) {
    loading.value = true
    try {
      const { data } = await attendanceApi.history(params)
      history.value = data.data?.data ?? []
      return history.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal memuat riwayat'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function saveSession(sessionData) {
    loading.value = true
    error.value = null
    try {
      const { data } = await attendanceApi.session(sessionData)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal menyimpan absensi sesi'
      throw err
    } finally {
      loading.value = false
    }
  }

  function clearScanResult() {
    scanResult.value = null
    error.value = null
  }

  return { history, scanResult, loading, error, scan, fetchHistory, saveSession, clearScanResult }
}
