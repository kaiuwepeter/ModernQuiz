import { defineStore } from 'pinia'

export const useNotificationsStore = defineStore('notifications', {
  state: () => ({
    notifications: [],
    nextId: 1
  }),

  actions: {
    // Add a notification
    add(notification) {
      const id = this.nextId++
      const newNotification = {
        id,
        type: notification.type || 'info', // success, error, warning, info
        title: notification.title || '',
        message: notification.message,
        duration: notification.duration || 5000, // 5 seconds default
        createdAt: Date.now()
      }

      this.notifications.push(newNotification)

      // Auto-dismiss after duration
      if (newNotification.duration > 0) {
        setTimeout(() => {
          this.remove(id)
        }, newNotification.duration)
      }

      return id
    },

    // Remove notification by ID
    remove(id) {
      const index = this.notifications.findIndex(n => n.id === id)
      if (index > -1) {
        this.notifications.splice(index, 1)
      }
    },

    // Clear all notifications
    clear() {
      this.notifications = []
    },

    // Helper methods for different notification types
    success(message, title = 'Erfolg', duration = 5000) {
      return this.add({ type: 'success', title, message, duration })
    },

    error(message, title = 'Fehler', duration = 7000) {
      return this.add({ type: 'error', title, message, duration })
    },

    warning(message, title = 'Warnung', duration = 6000) {
      return this.add({ type: 'warning', title, message, duration })
    },

    info(message, title = 'Info', duration = 5000) {
      return this.add({ type: 'info', title, message, duration })
    },

    // Get notification icon
    getIcon(type) {
      const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
      }
      return icons[type] || '📢'
    },

    // Get notification colors
    getColors(type) {
      const colors = {
        success: {
          bg: 'bg-green-50',
          border: 'border-green-500',
          text: 'text-green-800',
          icon: 'text-green-500'
        },
        error: {
          bg: 'bg-red-50',
          border: 'border-red-500',
          text: 'text-red-800',
          icon: 'text-red-500'
        },
        warning: {
          bg: 'bg-yellow-50',
          border: 'border-yellow-500',
          text: 'text-yellow-800',
          icon: 'text-yellow-500'
        },
        info: {
          bg: 'bg-blue-50',
          border: 'border-blue-500',
          text: 'text-blue-800',
          icon: 'text-blue-500'
        }
      }
      return colors[type] || colors.info
    }
  }
})
