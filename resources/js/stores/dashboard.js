import { defineStore } from 'pinia'
import { ref } from 'vue'
import dashboardApi from '../api/modules/dashboard'

export const useDashboardStore = defineStore('dashboard', () => {
  const summary = ref(null)
  const loading = ref(false)

  async function fetchSummary() {
    loading.value = true
    try {
      const { data } = await dashboardApi.summary()
      summary.value = data.data
      return data.data
    } catch {
      summary.value = null
    } finally {
      loading.value = false
    }
  }

  return { summary, loading, fetchSummary }
})
