import { defineStore } from 'pinia'
import { chatAPI } from '../utils/api'
import { useAuthStore } from './auth'

export const useChatStore = defineStore('chat', {
  state: () => ({
    messages: [],
    isOpen: false,
    isLoading: false,
    unreadCount: 0,
    messageInput: '',
    autoRefreshInterval: null,
    autoRefreshEnabled: true
  }),

  getters: {
    // Get messages sorted by time (newest last)
    sortedMessages: (state) => {
      return [...state.messages].sort((a, b) =>
        new Date(a.created_at) - new Date(b.created_at)
      )
    },

    // Check if message is from current user
    isOwnMessage: (state) => (message) => {
      const authStore = useAuthStore()
      return message.user_id === authStore.user?.id
    }
  },

  actions: {
    async loadMessages() {
      try {
        this.isLoading = true
        const response = await chatAPI.getMessages(50)
        this.messages = response.data

        // Mark messages as read when loading
        this.unreadCount = 0
      } catch (error) {
        console.error('Failed to load messages:', error)
      } finally {
        this.isLoading = false
      }
    },

    async sendMessage() {
      if (!this.messageInput.trim()) {
        return
      }

      const messageText = this.messageInput.trim()

      try {
        this.isLoading = true
        const response = await chatAPI.sendMessage(messageText)

        // Add new message to list
        this.messages.push(response.data)

        // Clear input
        this.messageInput = ''

        // Scroll to bottom (will be handled by component)
      } catch (error) {
        console.error('Failed to send message:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async loadUnreadCount() {
      try {
        const response = await chatAPI.getUnreadCount()
        this.unreadCount = response.data.unread_count
      } catch (error) {
        console.error('Failed to load unread count:', error)
      }
    },

    toggleChat() {
      this.isOpen = !this.isOpen

      // Load messages when opening chat
      if (this.isOpen) {
        this.loadMessages()
      }
    },

    openChat() {
      this.isOpen = true
      this.loadMessages()
    },

    closeChat() {
      this.isOpen = false
    },

    // Auto-refresh messages every 5 seconds when chat is open
    startAutoRefresh() {
      if (this.autoRefreshInterval) {
        return
      }

      this.autoRefreshEnabled = true
      this.autoRefreshInterval = setInterval(() => {
        if (this.autoRefreshEnabled && this.isOpen) {
          this.loadMessages()
        }

        // Always check unread count even when closed
        if (!this.isOpen) {
          this.loadUnreadCount()
        }
      }, 5000) // 5 seconds
    },

    stopAutoRefresh() {
      if (this.autoRefreshInterval) {
        clearInterval(this.autoRefreshInterval)
        this.autoRefreshInterval = null
      }
      this.autoRefreshEnabled = false
    },

    // Format timestamp
    formatTime(timestamp) {
      const date = new Date(timestamp)
      const now = new Date()
      const diffMinutes = Math.floor((now - date) / 60000)

      if (diffMinutes < 1) return 'Gerade eben'
      if (diffMinutes < 60) return `vor ${diffMinutes}m`

      const diffHours = Math.floor(diffMinutes / 60)
      if (diffHours < 24) return `vor ${diffHours}h`

      const diffDays = Math.floor(diffHours / 24)
      if (diffDays < 7) return `vor ${diffDays}d`

      return date.toLocaleDateString('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      })
    }
  }
})
