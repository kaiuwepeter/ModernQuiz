<template>
  <!-- Sidebar Container -->
  <div class="sidebar-container">
    <!-- Mobile Overlay -->
    <div
      v-if="isMobileMenuOpen"
      @click="closeMobileMenu"
      class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
    ></div>

    <!-- Sidebar -->
    <aside
      :class="[
        'sidebar',
        isMobileMenuOpen ? 'sidebar-mobile-open' : 'sidebar-mobile-closed'
      ]"
    >
      <!-- Logo -->
      <div class="sidebar-header">
        <router-link to="/" class="sidebar-logo">
          <span class="text-3xl">🎮</span>
          <span class="font-bold text-xl">ModernQuiz</span>
        </router-link>
      </div>

      <!-- Navigation -->
      <nav class="sidebar-nav">
        <router-link
          v-for="item in navigationItems"
          :key="item.path"
          :to="item.path"
          :class="[
            'nav-item',
            isActive(item.path) ? 'nav-item-active' : ''
          ]"
          @click="closeMobileMenu"
        >
          <span class="text-xl">{{ item.icon }}</span>
          <span>{{ item.name }}</span>
          <span v-if="item.badge" class="nav-badge">{{ item.badge }}</span>
        </router-link>

        <!-- Admin Section (only for admins) -->
        <div v-if="isAdmin" class="nav-divider"></div>
        <router-link
          v-if="isAdmin"
          to="/admin"
          :class="[
            'nav-item nav-item-admin',
            isActive('/admin') ? 'nav-item-active' : ''
          ]"
          @click="closeMobileMenu"
        >
          <span class="text-xl">⚙️</span>
          <span>Admin Panel</span>
        </router-link>
      </nav>

      <!-- User Section -->
      <div class="sidebar-footer">
        <div class="user-card">
          <!-- User Info -->
          <div class="user-info">
            <div class="user-avatar">
              {{ authStore.user?.avatar || '👤' }}
            </div>
            <div class="user-details">
              <div class="user-name">{{ authStore.user?.username || 'User' }}</div>
              <div class="user-role">{{ authStore.user?.role || 'user' }}</div>
            </div>
          </div>

          <!-- Coins Display -->
          <div class="coins-display">
            <span class="text-yellow-500 text-lg">💰</span>
            <span class="font-bold">{{ formatCoins(authStore.user?.coins) }}</span>
          </div>

          <!-- Logout Button -->
          <button
            @click="handleLogout"
            class="logout-btn"
            title="Logout"
          >
            <span>🚪</span>
            <span>Logout</span>
          </button>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const isMobileMenuOpen = ref(false)

// Check if user is admin
const isAdmin = computed(() => {
  return authStore.user?.role === 'admin' || authStore.user?.is_admin === true
})

// Navigation items
const navigationItems = computed(() => {
  const items = [
    { path: '/', name: 'Dashboard', icon: '🏠' },
    { path: '/quiz', name: 'Quiz spielen', icon: '🎮' },
    { path: '/shop', name: 'Shop', icon: '🛍️' },
    { path: '/leaderboard', name: 'Bestenliste', icon: '🏆' },
    { path: '/bank', name: 'Bank', icon: '🏦' },
    { path: '/vouchers', name: 'Gutscheine', icon: '🎟️' },
    { path: '/jackpots', name: 'Jackpots', icon: '💎' },
    { path: '/profile', name: 'Profil', icon: '👤' }
  ]

  return items
})

const isActive = (path) => {
  if (path === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(path)
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const formatCoins = (coins) => {
  if (!coins) return '0'
  return parseFloat(coins).toLocaleString('de-DE', { maximumFractionDigits: 0 })
}

// Expose toggle for parent component
defineExpose({
  toggleMobileMenu: () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value
  }
})
</script>

<style scoped>
.sidebar-container {
  @apply relative;
}

.sidebar {
  @apply fixed top-0 left-0 h-full w-64 bg-gradient-to-b from-indigo-900 to-purple-900 text-white z-40 flex flex-col shadow-2xl;
  transition: transform 0.3s ease-in-out;
}

/* Mobile: Hidden by default */
@media (max-width: 1023px) {
  .sidebar-mobile-closed {
    @apply -translate-x-full;
  }

  .sidebar-mobile-open {
    @apply translate-x-0;
  }
}

/* Desktop: Always visible */
@media (min-width: 1024px) {
  .sidebar {
    @apply translate-x-0;
  }
}

.sidebar-header {
  @apply p-6 border-b border-white border-opacity-10;
}

.sidebar-logo {
  @apply flex items-center space-x-3 text-white hover:text-indigo-200 transition-colors;
}

.sidebar-nav {
  @apply flex-1 overflow-y-auto py-4 px-3;
}

.nav-item {
  @apply flex items-center space-x-3 px-4 py-3 rounded-lg mb-2 text-white text-opacity-80 hover:text-opacity-100 hover:bg-white hover:bg-opacity-10 transition-all cursor-pointer relative;
}

.nav-item-active {
  @apply bg-white bg-opacity-20 text-white text-opacity-100 font-semibold;
}

.nav-item-admin {
  @apply bg-red-600 bg-opacity-20 hover:bg-red-600 hover:bg-opacity-30 border border-red-400 border-opacity-30;
}

.nav-badge {
  @apply ml-auto px-2 py-1 bg-red-500 text-white text-xs rounded-full font-bold;
}

.nav-divider {
  @apply my-4 border-t border-white border-opacity-20;
}

.sidebar-footer {
  @apply p-4 border-t border-white border-opacity-10;
}

.user-card {
  @apply space-y-3;
}

.user-info {
  @apply flex items-center space-x-3;
}

.user-avatar {
  @apply w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-2xl;
}

.user-details {
  @apply flex-1 min-w-0;
}

.user-name {
  @apply font-semibold text-sm truncate;
}

.user-role {
  @apply text-xs text-white text-opacity-60 capitalize;
}

.coins-display {
  @apply flex items-center justify-center space-x-2 bg-white bg-opacity-10 rounded-lg py-2 px-3;
}

.logout-btn {
  @apply w-full flex items-center justify-center space-x-2 py-2 px-4 bg-red-600 bg-opacity-50 hover:bg-opacity-70 rounded-lg transition-colors;
}
</style>
