<template>
  <div class="fixed top-4 right-4 z-50 space-y-3 max-w-sm">
    <transition-group name="notification">
      <div
        v-for="notification in notificationsStore.notifications"
        :key="notification.id"
        class="rounded-lg border-l-4 shadow-lg p-4 slide-up"
        :class="[
          notificationsStore.getColors(notification.type).bg,
          notificationsStore.getColors(notification.type).border
        ]"
      >
        <div class="flex items-start">
          <!-- Icon -->
          <div
            class="text-2xl mr-3"
            :class="notificationsStore.getColors(notification.type).icon"
          >
            {{ notificationsStore.getIcon(notification.type) }}
          </div>

          <!-- Content -->
          <div class="flex-1">
            <h4
              v-if="notification.title"
              class="font-bold mb-1"
              :class="notificationsStore.getColors(notification.type).text"
            >
              {{ notification.title }}
            </h4>
            <p
              class="text-sm"
              :class="notificationsStore.getColors(notification.type).text"
            >
              {{ notification.message }}
            </p>
          </div>

          <!-- Close Button -->
          <button
            @click="notificationsStore.remove(notification.id)"
            class="ml-3 text-gray-400 hover:text-gray-600 transition-colors"
          >
            <span class="text-xl">×</span>
          </button>
        </div>

        <!-- Progress Bar (if duration > 0) -->
        <div
          v-if="notification.duration > 0"
          class="mt-3 h-1 bg-gray-200 rounded-full overflow-hidden"
        >
          <div
            class="h-full transition-all"
            :class="getProgressBarColor(notification.type)"
            :style="{ width: getProgress(notification) + '%' }"
          ></div>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useNotificationsStore } from '../store/notifications'

const notificationsStore = useNotificationsStore()
const progressInterval = ref(null)

// Calculate progress for each notification
const getProgress = (notification) => {
  if (!notification.duration || notification.duration <= 0) return 100

  const elapsed = Date.now() - notification.createdAt
  const remaining = Math.max(0, notification.duration - elapsed)
  const progress = (remaining / notification.duration) * 100

  return Math.max(0, Math.min(100, progress))
}

const getProgressBarColor = (type) => {
  const colors = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    warning: 'bg-yellow-500',
    info: 'bg-blue-500'
  }
  return colors[type] || 'bg-blue-500'
}

// Update progress bars every 100ms
onMounted(() => {
  progressInterval.value = setInterval(() => {
    // Force re-render to update progress bars
    notificationsStore.notifications.forEach(n => {
      if (getProgress(n) <= 0) {
        notificationsStore.remove(n.id)
      }
    })
  }, 100)
})

onUnmounted(() => {
  if (progressInterval.value) {
    clearInterval(progressInterval.value)
  }
})
</script>

<style scoped>
/* Notification transitions */
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100px);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100px);
}

.notification-move {
  transition: transform 0.3s ease;
}
</style>
