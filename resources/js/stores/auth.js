import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authApi from '../api/modules/auth'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!user.value)
  const isAdmin = computed(() => {
    const roles = user.value?.roles ?? []
    return roles.includes('super_admin') || roles.includes('admin')
  })
  const isTeacher = computed(() => user.value?.roles?.includes('teacher'))
  const isStudent = computed(() => user.value?.roles?.includes('student'))

  async function login(email, password) {
    loading.value = true
    try {
      const { data } = await authApi.login({ email, password })
      user.value = data.data.user
      return data
    } finally {
      loading.value = false
    }
  }

  async function fetchUser() {
    try {
      const { data } = await authApi.me()
      user.value = data.data.user
    } catch {
      user.value = null
    }
  }

  async function logout() {
    try {
      await authApi.logout()
    } finally {
      user.value = null
    }
  }

  function clearUser() {
    user.value = null
  }

  return { user, loading, isAuthenticated, isAdmin, isTeacher, isStudent, login, fetchUser, logout, clearUser }
})
