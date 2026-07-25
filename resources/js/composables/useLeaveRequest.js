import { ref } from 'vue'
import leaveApi from '../api/modules/leave'

export function useLeaveRequest() {
  const myRequests = ref([])
  const pendingRequests = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function submit(formData) {
    loading.value = true
    error.value = null
    try {
      const { data } = await leaveApi.submit(formData)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal mengajukan izin'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchMyRequests(params = {}) {
    loading.value = true
    try {
      const { data } = await leaveApi.myRequests(params)
      myRequests.value = data.data?.data ?? []
      return myRequests.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal memuat pengajuan'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchPending(params = {}) {
    loading.value = true
    try {
      const { data } = await leaveApi.pending(params)
      pendingRequests.value = data.data?.data ?? []
      return pendingRequests.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal memuat pending'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function approve(id, note) {
    loading.value = true
    error.value = null
    try {
      const { data } = await leaveApi.approve(id, { note })
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal menyetujui izin'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function reject(id, rejectionNote) {
    loading.value = true
    error.value = null
    try {
      const { data } = await leaveApi.reject(id, { rejection_note: rejectionNote })
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal menolak izin'
      throw err
    } finally {
      loading.value = false
    }
  }

  return { myRequests, pendingRequests, loading, error, submit, fetchMyRequests, fetchPending, approve, reject }
}
