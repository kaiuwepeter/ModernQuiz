<template>
  <div>
    <!-- Floating Chat Button -->
    <button
      v-if="!chatStore.isOpen"
      @click="chatStore.openChat()"
      class="fixed bottom-6 right-6 w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full shadow-lg hover:shadow-xl transition-all flex items-center justify-center text-white z-40"
    >
      <span class="text-2xl">💬</span>
      <!-- Unread Badge -->
      <div v-if="chatStore.unreadCount > 0" class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 rounded-full flex items-center justify-center text-xs font-bold">
        {{ chatStore.unreadCount > 9 ? '9+' : chatStore.unreadCount }}
      </div>
    </button>

    <!-- Chat Panel -->
    <div
      v-if="chatStore.isOpen"
      class="fixed bottom-6 right-6 w-96 h-[32rem] bg-white rounded-lg shadow-2xl flex flex-col z-50 slide-up"
    >
      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-4 rounded-t-lg flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <span class="text-2xl">💬</span>
          <div>
            <h3 class="font-bold">Global Chat</h3>
            <p class="text-xs opacity-90">{{ chatStore.messages.length }} Nachrichten</p>
          </div>
        </div>
        <button
          @click="chatStore.closeChat()"
          class="w-8 h-8 hover:bg-white/20 rounded-full transition-colors flex items-center justify-center"
        >
          <span class="text-xl">×</span>
        </button>
      </div>

      <!-- Messages Container -->
      <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
        <!-- Loading -->
        <div v-if="chatStore.isLoading && chatStore.messages.length === 0" class="text-center py-8">
          <div class="spinner mx-auto mb-2"></div>
          <p class="text-sm text-gray-600">Lade Nachrichten...</p>
        </div>

        <!-- Messages -->
        <div v-else-if="chatStore.sortedMessages.length > 0">
          <div
            v-for="message in chatStore.sortedMessages"
            :key="message.id"
            class="flex"
            :class="chatStore.isOwnMessage(message) ? 'justify-end' : 'justify-start'"
          >
            <!-- Message Bubble -->
            <div
              class="max-w-[80%] rounded-lg p-3 shadow-sm"
              :class="chatStore.isOwnMessage(message)
                ? 'bg-indigo-500 text-white'
                : 'bg-white text-gray-900'"
            >
              <!-- Username (if not own message) -->
              <div
                v-if="!chatStore.isOwnMessage(message)"
                class="text-xs font-bold mb-1 text-indigo-600"
              >
                {{ message.username }}
              </div>

              <!-- Message Text -->
              <div class="text-sm break-words">
                {{ message.message }}
              </div>

              <!-- Timestamp -->
              <div
                class="text-xs mt-1"
                :class="chatStore.isOwnMessage(message) ? 'text-indigo-100' : 'text-gray-500'"
              >
                {{ chatStore.formatTime(message.created_at) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="text-5xl mb-3">💬</div>
          <p class="text-gray-600 text-sm">Noch keine Nachrichten</p>
          <p class="text-gray-500 text-xs mt-1">Schreibe die erste Nachricht!</p>
        </div>
      </div>

      <!-- Input Area -->
      <div class="p-4 border-t border-gray-200 bg-white rounded-b-lg">
        <form @submit.prevent="handleSendMessage" class="flex gap-2">
          <input
            v-model="chatStore.messageInput"
            type="text"
            placeholder="Nachricht schreiben..."
            maxlength="500"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            :disabled="chatStore.isLoading"
          />
          <button
            type="submit"
            :disabled="!chatStore.messageInput.trim() || chatStore.isLoading"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="chatStore.isLoading">...</span>
            <span v-else>📤</span>
          </button>
        </form>
        <div class="text-xs text-gray-500 mt-2 text-right">
          {{ chatStore.messageInput.length }}/500
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useChatStore } from '../store/chat'

const chatStore = useChatStore()
const messagesContainer = ref(null)

onMounted(() => {
  // Start auto-refresh
  chatStore.startAutoRefresh()

  // Load unread count on mount
  chatStore.loadUnreadCount()
})

onUnmounted(() => {
  chatStore.stopAutoRefresh()
})

// Scroll to bottom when messages change
watch(() => chatStore.messages.length, async () => {
  await nextTick()
  scrollToBottom()
})

// Scroll to bottom when chat opens
watch(() => chatStore.isOpen, async (isOpen) => {
  if (isOpen) {
    await nextTick()
    scrollToBottom()
  }
})

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const handleSendMessage = async () => {
  try {
    await chatStore.sendMessage()
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Send message error:', error)
    alert('Fehler beim Senden der Nachricht')
  }
}
</script>

<style scoped>
/* Custom scrollbar for messages */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}
</style>
