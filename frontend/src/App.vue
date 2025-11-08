<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav v-if="authStore.isAuthenticated" class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <!-- Logo -->
            <router-link to="/" class="flex items-center">
              <span class="text-2xl font-bold text-indigo-600">🎮 ModernQuiz</span>
            </router-link>

            <!-- Main Navigation -->
            <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
              <router-link
                to="/quiz"
                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 border-b-2 border-transparent hover:border-indigo-500"
              >
                Quiz spielen
              </router-link>

              <router-link
                to="/leaderboard"
                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 border-b-2 border-transparent hover:border-indigo-500"
              >
                🏆 Leaderboard
              </router-link>

              <router-link
                to="/shop"
                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 border-b-2 border-transparent hover:border-indigo-500"
              >
                🛍️ Shop
              </router-link>
            </div>
          </div>

          <!-- User Menu -->
          <div class="flex items-center space-x-4">
            <!-- Coins Display -->
            <div class="flex items-center space-x-2 bg-yellow-50 px-3 py-1 rounded-full">
              <span class="text-yellow-600 font-semibold">💰</span>
              <span class="font-bold text-gray-900">{{ authStore.user?.coins || 0 }}</span>
            </div>

            <!-- User Dropdown -->
            <div class="relative">
              <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-700">{{ authStore.user?.username }}</span>
                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>

              <!-- Dropdown Menu -->
              <div v-if="userMenuOpen" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                <router-link to="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Profil
                </router-link>
                <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <router-view />
    </main>

    <!-- Notification Toast (für später) -->
    <div v-if="notification" class="fixed bottom-4 right-4 bg-white shadow-lg rounded-lg p-4 max-w-sm">
      <p class="text-sm font-medium text-gray-900">{{ notification }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from './store/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()
const userMenuOpen = ref(false)
const notification = ref(null)

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}

// Close dropdown when clicking outside
onMounted(() => {
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) {
      userMenuOpen.value = false
    }
  })
})
</script>
