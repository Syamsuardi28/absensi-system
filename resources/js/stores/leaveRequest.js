import { defineStore } from 'pinia'
import { ref } from 'vue'
import leaveApi from '../api/modules/leave'

export const useLeaveRequestStore = defineStore('leaveRequest', () => {
  const myRequests = ref([])
  const pendingRequests = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function submit(formData) {
    loading.value = true
    error.value = null
    try {
      const { data } = await leaveApi.submit(formData)
      await fetchMyRequests()
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
      await fetchPending()
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
      await fetchPending()
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal menolak izin'
      throw err
    } finally {
      loading.value = false
    }
  }

  return { myRequests, pendingRequests, loading, error, submit, fetchMyRequests, fetchPending, approve, reject }
})
