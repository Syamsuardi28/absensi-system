import axios from '.'
import { useAuthStore } from '../stores/auth'

let isRedirecting = false

axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && !isRedirecting) {
      const auth = useAuthStore()
      auth.clearUser()

      const currentPath = window.location.pathname
      if (currentPath !== '/login') {
        isRedirecting = true
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)
