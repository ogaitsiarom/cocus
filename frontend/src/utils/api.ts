import { useAuthStore } from '@/stores/auth'
import router from '@/router'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'

/**
 * Represents a validation violation that occurs during data validation processes.s
 */
interface ValidationViolation {
  propertyPath: string
  title: string
  template: string
  parameters: Record<string, string>
  type: string
}

/**
 * Represents an API validation error that includes a status code and specific field violations.
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
  async request<T>(method: string, endpoint: string, data?: never): Promise<T> {
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

    if (response.status === 401) {
      await authStore.logout()
      await router.push('/login')

      throw new Error('Your session has expired. Please log in again.')
    }

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({ detail: 'Unknown error' }))

      if (response.status === 422 && errorData.violations) {
        throw new ApiValidationError(
          errorData.detail || 'Validation failed',
          response.status,
          errorData.violations
        )
      }

      throw new Error(errorData.detail || errorData.title || `API error: ${response.status}`)
    }

    if (response.status === 204) {
      return {} as T
    }

    try {
      return await response.json()
    } catch (error) {
      console.error('Error parsing API response:', error)
      throw new Error('Invalid response format')
    }
  }
}
