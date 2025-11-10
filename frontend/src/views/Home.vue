<template>
  <div class="space-y-8">
    <!-- Welcome Banner -->
    <div class="card bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
      <h1 class="text-3xl font-bold mb-2">
        Willkommen zurück, {{ authStore.user?.username }}! 👋
      </h1>
      <p class="text-indigo-100">
        Bereit für eine Runde Quiz?
      </p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="card text-center">
        <div class="text-3xl mb-2">💰</div>
        <div class="text-2xl font-bold text-gray-900">
          {{ authStore.user?.coins || 0 }}
        </div>
        <div class="text-sm text-gray-600">Coins</div>
      </div>

      <div class="card text-center">
        <div class="text-3xl mb-2">🎯</div>
        <div class="text-2xl font-bold text-gray-900">
          {{ authStore.user?.total_points || 0 }}
        </div>
        <div class="text-sm text-gray-600">Punkte</div>
      </div>

      <div class="card text-center">
        <div class="text-3xl mb-2">📊</div>
        <div class="text-2xl font-bold text-gray-900">
          Lvl {{ authStore.user?.level || 1 }}
        </div>
        <div class="text-sm text-gray-600">Level</div>
      </div>

      <div class="card text-center">
        <div class="text-3xl mb-2">🔥</div>
        <div class="text-2xl font-bold text-gray-900">
          {{ authStore.user?.current_streak || 0 }}
        </div>
        <div class="text-sm text-gray-600">Streak</div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <router-link to="/quiz" class="card hover:shadow-lg transition-shadow cursor-pointer">
        <div class="flex items-center space-x-4">
          <div class="text-5xl">🎮</div>
          <div class="flex-1">
            <h3 class="text-xl font-bold text-gray-900">Quiz spielen</h3>
            <p class="text-gray-600">Starte ein neues Quiz und verdiene Coins!</p>
          </div>
          <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </div>
      </router-link>

      <router-link to="/shop" class="card hover:shadow-lg transition-shadow cursor-pointer">
        <div class="flex items-center space-x-4">
          <div class="text-5xl">🛍️</div>
          <div class="flex-1">
            <h3 class="text-xl font-bold text-gray-900">Shop</h3>
            <p class="text-gray-600">Kaufe Powerups und Boosts!</p>
          </div>
          <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </div>
      </router-link>
    </div>

    <!-- Recent Achievements (Placeholder) -->
    <div class="card">
      <h2 class="text-xl font-bold text-gray-900 mb-4">🏆 Letzte Achievements</h2>
      <div class="text-gray-600 text-center py-8">
        Noch keine Achievements freigeschaltet. Spiele Quizzes um welche zu bekommen!
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '../store/auth'

const authStore = useAuthStore()

// User data is already loaded from login response
// No need to fetch again - this was causing 401 and logout loop
</script>
