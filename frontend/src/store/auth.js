import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('session_token') || null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    username: (state) => state.user?.username || '',
  },

  actions: {
    async login(identifier, password) {
      try {
        const response = await axios.post('/api/auth/login', {
          identifier,
          password
        })

        if (response.data.success) {
          this.token = response.data.session_token
          this.user = response.data.user

          // Speichere Token im localStorage
          localStorage.setItem('session_token', this.token)

          // Setze Default Authorization Header
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

          return { success: true }
        }

        return { success: false, error: response.data.error }
      } catch (error) {
        console.error('Login error:', error)
        return { success: false, error: error.response?.data?.message || 'Login fehlgeschlagen' }
      }
    },

    async register(username, email, password, referralCode = null) {
      try {
        const response = await axios.post('/api/auth/register', {
          username,
          email,
          password,
          referral_code: referralCode
        })

        return response.data
      } catch (error) {
        console.error('Register error:', error)
        return { success: false, errors: ['Registrierung fehlgeschlagen'] }
      }
    },

    async logout() {
      try {
        await axios.post('/api/auth/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.token = null
        localStorage.removeItem('session_token')
        delete axios.defaults.headers.common['Authorization']
      }
    },

    async fetchUser() {
      if (!this.token) return

      try {
        const response = await axios.get('/api/user/profile')

        if (response.data.success) {
          this.user = response.data.user
        }
      } catch (error) {
        console.error('Fetch user error:', error)
        // Only logout on 401 if we don't have user data from login response
        // This prevents immediate logout after successful login
        if (error.response?.status === 401 && !this.user) {
          console.warn('Session invalid, logging out')
          this.logout()
        }
      }
    },

    // Initialize auth from localStorage
    init() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        // Only fetch if we have token but no user data
        if (!this.user) {
          this.fetchUser()
        }
      }
    }
  }
})
