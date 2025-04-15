// src/utils/api.ts
import { useAuthStore } from '@/stores/auth'
import router from '@/router'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'

/**
 * Symfony validation violation structure
 */
interface ValidationViolation {
  propertyPath: string
  title: string
  template: string
  parameters: Record<string, string>
  type: string
}

/**
 * Custom error class for API validation errors
 */
export class ApiValidationError extends Error {
  status: number
  violations: ValidationViolation[]

  constructor(message: string, status: number, violations: ValidationViolation[]) {
    super(message)
    this.name = 'ApiValidationError'
    this.status = status
    this.violations = violations
  }

  /**
   * Get errors mapped by field name for easy form integration
   */
  getFieldErrors(): Record<string, string> {
    const fieldErrors: Record<string, string> = {}

    this.violations.forEach(violation => {
      fieldErrors[violation.propertyPath] = violation.title
    })

    return fieldErrors
  }
}

/**
 * Global API client with standardized error handling and authentication
 */
export const api = {
  /**
   * Send a GET request
   */
  async get<T>(endpoint: string): Promise<T> {
    return this.request<T>('GET', endpoint)
  },

  /**
   * Send a POST request
   */
  async post<T>(endpoint: string, data?: any): Promise<T> {
    return this.request<T>('POST', endpoint, data)
  },

  /**
   * Send a PUT request
   */
  async put<T>(endpoint: string, data?: any): Promise<T> {
    return this.request<T>('PUT', endpoint, data)
  },

  /**
   * Send a DELETE request
   */
  async delete<T>(endpoint: string): Promise<T> {
    return this.request<T>('DELETE', endpoint)
  },

  /**
   * Base request method that handles authentication and errors
   */
  async request<T>(method: string, endpoint: string, data?: any): Promise<T> {
    // Don't use useAuthStore in composition API outside of setup
    const authStore = useAuthStore()

    const url = `${apiBaseUrl}${endpoint.startsWith('/') ? endpoint : '/' + endpoint}`
    const options: RequestInit = {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...authStore.getAuthHeader(),
      }
    }

    if (data) {
      options.body = JSON.stringify(data)
    }

    const response = await fetch(url, options)

    // Handle unauthorized response (401)
    if (response.status === 401) {
      // Clear auth data
      await authStore.logout()

      // Redirect to login
      router.push('/login')

      throw new Error('Your session has expired. Please log in again.')
    }

    // Special handling for error responses
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({ detail: 'Unknown error' }))

      // Handle Symfony validation errors (422)
      if (response.status === 422 && errorData.violations) {
        throw new ApiValidationError(
          errorData.detail || 'Validation failed',
          response.status,
          errorData.violations
        )
      }

      // Handle other API errors
      throw new Error(errorData.detail || errorData.title || `API error: ${response.status}`)
    }

    // Handle empty responses
    if (response.status === 204) {
      return {} as T
    }

    // Parse JSON response
    try {
      return await response.json()
    } catch (error) {
      console.error('Error parsing API response:', error)
      throw new Error('Invalid response format')
    }
  }
}
