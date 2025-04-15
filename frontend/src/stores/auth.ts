import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/utils/api'

interface LoginResponse {
  token: string
}

/**
 * A Pinia store for managing authentication states and actions.
 */
export const useAuthStore = defineStore('auth', () => {
  const isAuthenticated = ref(false)
  const username = ref('')
  const token = ref('')
  const error = ref<string | null>(null)
  const isLoading = ref(false)

  /**
   * Sends a login request to the server with the provided user credentials.
   *
   * @param {string} user - The username of the user attempting to log in.
   * @param {string} password - The password of the user attempting to log in.
   * @return {Promise<LoginResponse>} A promise that resolves to the server's response containing login details or user authentication information.
   * @throws {Error} Throws an error if the server response is not successful or if the response contains an error message.
   */
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

  /**
   * Authenticates a user with the provided credentials and updates the application's session state.
   *
   * @param {string} user - The username of the user attempting to log in.
   * @param {string} password - The password of the user attempting to log in.
   * @return {Promise<boolean>} A promise that resolves to `true` if the login was successful, or `false` if it failed.
   */
  async function login(user: string, password: string): Promise<boolean> {
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

  /**
   * Logs out the current user by clearing authentication data and resetting user state.
   *
   * @return {Promise<boolean>} A promise that resolves to `true` if the logout is successful (client-side state is cleared), or `false` if an error occurs.
   */
  async function logout(): Promise<boolean> {
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

  /**
   * A function that initializes application state based on session storage data.
   * It checks whether the user is authenticated and retrieves stored values.
   */
  const initializeFromSession = () => {
    if (sessionStorage.getItem('isAuthenticated') === 'true') {
      isAuthenticated.value = true
      username.value = sessionStorage.getItem('username') || ''
      token.value = sessionStorage.getItem('token') || ''
    }
  }

  initializeFromSession()

  /**
   * Generates an authorization header containing a Bearer token.
   *
   * @return {Object} An object representing the authorization header with the Bearer token.
   */
  function getAuthHeader() : Object {
    return { Authorization: `Bearer ${token.value}` }
  }

  /**
   * Checks whether there is a valid token available either in the token object
   * or stored in the session storage.
   *
   * @return {boolean} Returns true if a valid token exists, otherwise false.
   */
  function hasValidToken(): boolean {
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
    getAuthHeader,
    hasValidToken,
  }
})
