<template>
  <div id="app" class="app-container">
    <!-- Sidebar (only when authenticated) -->
    <Sidebar v-if="authStore.isAuthenticated" ref="sidebarRef" />

    <!-- Main Content Area -->
    <div :class="['main-wrapper', authStore.isAuthenticated ? 'main-wrapper-with-sidebar' : '']">
      <!-- Mobile Header (only when authenticated) -->
      <header v-if="authStore.isAuthenticated" class="mobile-header lg:hidden">
        <button @click="toggleSidebar" class="mobile-menu-btn">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <span class="text-xl font-bold text-indigo-600">🎮 ModernQuiz</span>
        <div class="w-6"></div> <!-- Spacer for centering -->
      </header>

      <!-- Main Content -->
      <main class="main-content">
        <router-view />
      </main>

      <!-- Global Notifications -->
      <Notifications />

      <!-- Global Chat (only for authenticated users) -->
      <Chat v-if="authStore.isAuthenticated" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from './store/auth'
import Sidebar from './components/Sidebar.vue'
import Chat from './components/Chat.vue'
import Notifications from './components/Notifications.vue'

const authStore = useAuthStore()
const sidebarRef = ref(null)

const toggleSidebar = () => {
  sidebarRef.value?.toggleMobileMenu()
}
</script>

<style>
/* Global Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  background-color: #f9fafb;
}

.app-container {
  @apply min-h-screen bg-gray-50;
}

.main-wrapper {
  @apply min-h-screen;
}

.main-wrapper-with-sidebar {
  @apply lg:ml-64; /* Sidebar width on desktop */
}

.mobile-header {
  @apply sticky top-0 z-20 bg-white shadow-sm px-4 py-3 flex items-center justify-between;
}

.mobile-menu-btn {
  @apply p-2 rounded-lg hover:bg-gray-100 transition-colors;
}

.main-content {
  @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8;
}

/* Card Utility Class */
.card {
  @apply bg-white rounded-lg shadow-md p-6;
}

/* Button Utility Classes */
.btn-primary {
  @apply px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium;
}

.btn-secondary {
  @apply px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors font-medium;
}

/* Input Utility Class */
.input {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none;
}
</style>
