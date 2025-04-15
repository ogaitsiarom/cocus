// src/stores/auth.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/utils/api'

interface LoginResponse {
  token: string
}

export const useAuthStore = defineStore('auth', () => {
  const isAuthenticated = ref(false)
  const username = ref('')
  const token = ref('')
  const error = ref<string | null>(null)
  const isLoading = ref(false)

  async function loginRequest(user: string, password: string): Promise<LoginResponse> {
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
    const response = await fetch(`${apiBaseUrl}/api/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({
        username: user,
        password: password,
      }),
    })

    if (!response.ok) {
      const errorText = await response.text()
      let errorMessage: string

      try {
        const errorData = JSON.parse(errorText)
        errorMessage = errorData.message || errorData.error || `Login failed: ${response.status}`
      } catch {
        errorMessage = errorText || `Login failed: ${response.status}`
      }

      throw new Error(errorMessage)
    }

    return await response.json()
  }

  async function login(user: string, password: string) {
    isLoading.value = true
    error.value = null

    try {
      const data = await loginRequest(user, password)

      isAuthenticated.value = true
      username.value = user

      sessionStorage.setItem('isAuthenticated', 'true')
      sessionStorage.setItem('username', user)

      if (data.token) {
        token.value = data.token // Set the reactive token value
        sessionStorage.setItem('token', data.token)
      }

      return true
    } catch (err) {
      console.error('Login error:', err)
      error.value = err instanceof Error ? err.message : 'Unknown error occurred'
      return false
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    isLoading.value = true
    error.value = null

    try {
      if (isAuthenticated.value) {
        try {
          await api.post('api/logout')
        } catch (logoutErr) {
          console.warn('Server logout failed, continuing with client logout:', logoutErr)
        }
      }

      isAuthenticated.value = false
      username.value = ''
      token.value = ''

      sessionStorage.removeItem('isAuthenticated')
      sessionStorage.removeItem('username')
      sessionStorage.removeItem('token')

      return true
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Unknown error occurred'
      return false
    } finally {
      isLoading.value = false
    }
  }

  async function fetchUserProfile() {
    if (!isAuthenticated.value) return null

    isLoading.value = true
    error.value = null

    try {
      const profile = await api.get('api/user/profile')
      return profile
    } catch (err) {
      console.error('Error fetching user profile:', err)
      error.value = err instanceof Error ? err.message : 'Failed to load user profile'
      return null
    } finally {
      isLoading.value = false
    }
  }

  const initializeFromSession = () => {
    if (sessionStorage.getItem('isAuthenticated') === 'true') {
      isAuthenticated.value = true
      username.value = sessionStorage.getItem('username') || ''
      token.value = sessionStorage.getItem('token') || ''
    }
  }

  initializeFromSession()

  function getAuthHeader() {
    return { Authorization: `Bearer ${token.value}` }
  }

  function hasValidToken() {
    return !!token.value || !!sessionStorage.getItem('token')
  }

  return {
    isAuthenticated,
    username,
    token,
    error,
    isLoading,
    login,
    logout,
    fetchUserProfile,
    getAuthHeader,
    hasValidToken,
  }
})
