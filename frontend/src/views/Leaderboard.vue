<template>
  <div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="card">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">🏆 Leaderboard</h1>
          <p class="text-gray-600">Vergleiche dich mit den besten Spielern</p>
        </div>
        <div class="flex items-center gap-3">
          <!-- Auto-Refresh Toggle -->
          <button
            @click="leaderboardStore.toggleAutoRefresh()"
            class="px-4 py-2 rounded-lg border-2 transition-colors text-sm font-medium"
            :class="leaderboardStore.autoRefreshEnabled ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-300 text-gray-700'"
          >
            <span v-if="leaderboardStore.autoRefreshEnabled">🔄 Auto-Update AN</span>
            <span v-else>⏸️ Auto-Update AUS</span>
          </button>

          <!-- Manual Refresh -->
          <button
            @click="handleRefresh"
            :disabled="leaderboardStore.isLoading"
            class="btn-primary"
          >
            <span v-if="leaderboardStore.isLoading">Lädt...</span>
            <span v-else>🔄 Aktualisieren</span>
          </button>
        </div>
      </div>

      <!-- Last Update Time -->
      <div v-if="leaderboardStore.lastUpdate" class="mt-3 text-sm text-gray-500">
        Zuletzt aktualisiert: vor {{ leaderboardStore.timeSinceUpdate }}
      </div>
    </div>

    <!-- User Rank Card -->
    <div class="card bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm opacity-90 mb-1">Deine Position</div>
          <div class="text-3xl font-bold">
            {{ getCurrentRank() }}
          </div>
        </div>
        <div class="text-right">
          <div class="text-sm opacity-90 mb-1">{{ getActiveTabName() }}</div>
          <div class="text-lg">
            {{ isInTop10() ? '🎉 Top 10!' : 'Weiter so!' }}
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="card">
      <div class="flex gap-4 border-b border-gray-200">
        <button
          @click="activeTab = 'global'"
          class="px-4 py-2 font-medium transition-colors"
          :class="activeTab === 'global' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:text-gray-900'"
        >
          🌍 Global
        </button>
        <button
          @click="activeTab = 'weekly'"
          class="px-4 py-2 font-medium transition-colors"
          :class="activeTab === 'weekly' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:text-gray-900'"
        >
          📅 Wöchentlich
        </button>
        <button
          @click="activeTab = 'monthly'"
          class="px-4 py-2 font-medium transition-colors"
          :class="activeTab === 'monthly' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:text-gray-900'"
        >
          📆 Monatlich
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="leaderboardStore.isLoading && getCurrentLeaderboard().length === 0" class="card text-center py-12">
      <div class="spinner mx-auto mb-4"></div>
      <p class="text-gray-600">Lade Leaderboard...</p>
    </div>

    <!-- Leaderboard Table -->
    <div v-else-if="getCurrentLeaderboard().length > 0" class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Rang
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Spieler
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Punkte
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Quizzes
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Genauigkeit
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Level
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="(player, index) in getCurrentLeaderboard()"
              :key="player.user_id"
              class="hover:bg-gray-50 transition-colors"
              :class="player.is_current_user ? 'bg-indigo-50' : ''"
            >
              <!-- Rank -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <span class="text-2xl font-bold" :class="leaderboardStore.getRankColor(index + 1)">
                    {{ leaderboardStore.getRankEmoji(index + 1) }}
                  </span>
                </div>
              </td>

              <!-- Player -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                    {{ player.username.charAt(0).toUpperCase() }}
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">
                      {{ player.username }}
                      <span v-if="player.is_current_user" class="ml-2 text-indigo-600 font-bold">(Du)</span>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Points -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-bold text-indigo-600">
                  {{ formatNumber(player.total_points) }}
                </div>
              </td>

              <!-- Quizzes -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ player.quizzes_completed }}
                </div>
              </td>

              <!-- Accuracy -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="text-sm font-medium" :class="getAccuracyColor(player.accuracy)">
                    {{ player.accuracy }}%
                  </div>
                  <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                    <div
                      class="h-2 rounded-full transition-all"
                      :class="getAccuracyBarColor(player.accuracy)"
                      :style="{ width: player.accuracy + '%' }"
                    ></div>
                  </div>
                </div>
              </td>

              <!-- Level -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <span class="text-sm font-medium text-gray-900">{{ player.level }}</span>
                  <span class="ml-1 text-xs text-gray-500">{{ getLevelEmoji(player.level) }}</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="card text-center py-12">
      <div class="text-6xl mb-4">📊</div>
      <h3 class="text-xl font-bold text-gray-900 mb-2">Noch keine Daten</h3>
      <p class="text-gray-600 mb-4">Spiele Quizzes um im Leaderboard zu erscheinen!</p>
      <router-link to="/quiz" class="btn-primary">
        Quiz spielen
      </router-link>
    </div>

    <!-- Stats Footer -->
    <div v-if="getCurrentLeaderboard().length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="card text-center">
        <div class="text-3xl mb-2">👑</div>
        <div class="text-2xl font-bold text-gray-900">{{ getCurrentLeaderboard()[0]?.username || '-' }}</div>
        <div class="text-sm text-gray-600">Führender Spieler</div>
      </div>
      <div class="card text-center">
        <div class="text-3xl mb-2">🎯</div>
        <div class="text-2xl font-bold text-gray-900">{{ getAverageAccuracy() }}%</div>
        <div class="text-sm text-gray-600">Durchschnittliche Genauigkeit</div>
      </div>
      <div class="card text-center">
        <div class="text-3xl mb-2">🏃</div>
        <div class="text-2xl font-bold text-gray-900">{{ getTotalQuizzes() }}</div>
        <div class="text-sm text-gray-600">Gespielte Quizzes</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useLeaderboardStore } from '../store/leaderboard'

const leaderboardStore = useLeaderboardStore()
const activeTab = ref('global')

onMounted(() => {
  leaderboardStore.loadAllLeaderboards()
  leaderboardStore.startAutoRefresh()
})

onUnmounted(() => {
  leaderboardStore.stopAutoRefresh()
})

const getCurrentLeaderboard = () => {
  if (activeTab.value === 'global') return leaderboardStore.topGlobal
  if (activeTab.value === 'weekly') return leaderboardStore.topWeekly
  if (activeTab.value === 'monthly') return leaderboardStore.topMonthly
  return []
}

const getCurrentRank = () => {
  if (activeTab.value === 'global') {
    return leaderboardStore.userRank.global ? `#${leaderboardStore.userRank.global}` : '-'
  }
  if (activeTab.value === 'weekly') {
    return leaderboardStore.userRank.weekly ? `#${leaderboardStore.userRank.weekly}` : '-'
  }
  if (activeTab.value === 'monthly') {
    return leaderboardStore.userRank.monthly ? `#${leaderboardStore.userRank.monthly}` : '-'
  }
  return '-'
}

const isInTop10 = () => {
  if (activeTab.value === 'global') return leaderboardStore.isUserInTop10Global
  if (activeTab.value === 'weekly') return leaderboardStore.isUserInTop10Weekly
  if (activeTab.value === 'monthly') return leaderboardStore.isUserInTop10Monthly
  return false
}

const getActiveTabName = () => {
  if (activeTab.value === 'global') return 'Global'
  if (activeTab.value === 'weekly') return 'Diese Woche'
  if (activeTab.value === 'monthly') return 'Dieser Monat'
  return ''
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('de-DE').format(num)
}

const getAccuracyColor = (accuracy) => {
  if (accuracy >= 90) return 'text-green-600'
  if (accuracy >= 70) return 'text-yellow-600'
  return 'text-orange-600'
}

const getAccuracyBarColor = (accuracy) => {
  if (accuracy >= 90) return 'bg-green-500'
  if (accuracy >= 70) return 'bg-yellow-500'
  return 'bg-orange-500'
}

const getLevelEmoji = (level) => {
  if (level >= 50) return '🔥'
  if (level >= 30) return '⭐'
  if (level >= 10) return '✨'
  return '🌟'
}

const getAverageAccuracy = () => {
  const leaderboard = getCurrentLeaderboard()
  if (leaderboard.length === 0) return 0
  const sum = leaderboard.reduce((acc, player) => acc + player.accuracy, 0)
  return Math.round(sum / leaderboard.length)
}

const getTotalQuizzes = () => {
  const leaderboard = getCurrentLeaderboard()
  return leaderboard.reduce((acc, player) => acc + player.quizzes_completed, 0)
}

const handleRefresh = async () => {
  await leaderboardStore.refresh()
}
</script>

