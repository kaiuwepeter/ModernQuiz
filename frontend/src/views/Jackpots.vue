<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="card bg-gradient-to-r from-yellow-400 to-orange-500 text-white">
      <h1 class="text-3xl font-bold flex items-center space-x-2">
        <span>💎</span>
        <span>Jackpots</span>
      </h1>
      <p class="text-yellow-100 mt-2">Knacke die Jackpots und gewinne riesige Belohnungen!</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="card text-center py-12">
      <div class="spinner mx-auto mb-4"></div>
      <p class="text-gray-600">Lade Jackpots...</p>
    </div>

    <!-- Jackpots Grid -->
    <div v-else-if="jackpots.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="jackpot in jackpots"
        :key="jackpot.id"
        class="card"
        :class="{
          'bg-gradient-to-br from-purple-500 to-indigo-600 text-white': jackpot.type === 'global',
          'bg-gradient-to-br from-blue-500 to-cyan-600 text-white': jackpot.type === 'category',
          'bg-gradient-to-br from-green-500 to-emerald-600 text-white': jackpot.type === 'streak',
          'bg-gradient-to-br from-red-500 to-pink-600 text-white': jackpot.type === 'daily'
        }"
      >
        <!-- Jackpot Icon -->
        <div class="text-6xl text-center mb-3">{{ getJackpotIcon(jackpot.type) }}</div>

        <!-- Jackpot Name -->
        <h3 class="text-2xl font-bold text-center mb-2">{{ jackpot.name }}</h3>

        <!-- Current Value -->
        <div class="text-center mb-4">
          <div class="text-sm opacity-90 mb-1">Aktueller Wert</div>
          <div class="text-4xl font-bold">{{ formatCoins(jackpot.current_value) }}</div>
          <div class="text-sm opacity-90">Coins</div>
        </div>

        <!-- Condition -->
        <div class="bg-black bg-opacity-20 rounded-lg p-3 mb-3">
          <div class="text-xs opacity-90 mb-1">Bedingung:</div>
          <div class="text-sm font-medium">{{ getConditionText(jackpot) }}</div>
        </div>

        <!-- Stats -->
        <div class="flex justify-between text-sm opacity-90">
          <div>
            <div class="font-medium">Gewinner</div>
            <div>{{ jackpot.winners_count || 0 }}</div>
          </div>
          <div class="text-right">
            <div class="font-medium">Zuletzt gewonnen</div>
            <div>{{ jackpot.last_won_at ? formatRelativeTime(jackpot.last_won_at) : 'Nie' }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- No Jackpots -->
    <div v-else class="card text-center py-12">
      <div class="text-6xl mb-4">💎</div>
      <h3 class="text-xl font-bold text-gray-900 mb-2">Keine aktiven Jackpots</h3>
      <p class="text-gray-600">Schau später wieder vorbei!</p>
    </div>

    <!-- Recent Winners -->
    <div v-if="winners.length > 0" class="card">
      <h2 class="text-2xl font-bold mb-4 flex items-center space-x-2">
        <span>🏆</span>
        <span>Letzte Gewinner</span>
      </h2>

      <div class="space-y-2">
        <div
          v-for="winner in winners"
          :key="winner.id"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
        >
          <div class="flex items-center space-x-3">
            <div class="text-3xl">{{ getJackpotIcon(winner.jackpot_type) }}</div>
            <div>
              <div class="font-bold text-gray-900">{{ winner.username }}</div>
              <div class="text-sm text-gray-600">{{ winner.jackpot_name }}</div>
            </div>
          </div>
          <div class="text-right">
            <div class="text-lg font-bold text-green-600">+{{ formatCoins(winner.amount) }}</div>
            <div class="text-xs text-gray-500">{{ formatRelativeTime(winner.won_at) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- How it Works -->
    <div class="card bg-blue-50 border border-blue-200">
      <h2 class="text-2xl font-bold text-blue-900 mb-4">ℹ️ Wie funktionieren Jackpots?</h2>
      <div class="space-y-3 text-blue-800">
        <div class="flex items-start space-x-2">
          <span class="text-lg">🎯</span>
          <div>
            <strong>Global Jackpot:</strong> Beantworte eine bestimmte Anzahl Fragen richtig hintereinander
          </div>
        </div>
        <div class="flex items-start space-x-2">
          <span class="text-lg">📚</span>
          <div>
            <strong>Kategorie Jackpot:</strong> Spezialisiere dich auf eine Kategorie
          </div>
        </div>
        <div class="flex items-start space-x-2">
          <span class="text-lg">🔥</span>
          <div>
            <strong>Streak Jackpot:</strong> Erreiche eine bestimmte Streak-Länge
          </div>
        </div>
        <div class="flex items-start space-x-2">
          <span class="text-lg">📅</span>
          <div>
            <strong>Daily Jackpot:</strong> Wird täglich an einen zufälligen Spieler vergeben
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../utils/api'

const loading = ref(false)
const jackpots = ref([])
const winners = ref([])

onMounted(async () => {
  await loadJackpots()
  await loadWinners()
})

const loadJackpots = async () => {
  try {
    loading.value = true
    const response = await api.get('/jackpots')
    if (response.data.success) {
      jackpots.value = response.data.jackpots
    }
  } catch (err) {
    console.error('Failed to load jackpots:', err)
  } finally {
    loading.value = false
  }
}

const loadWinners = async () => {
  try {
    const response = await api.get('/jackpots/winners', { params: { limit: 10 } })
    if (response.data.success) {
      winners.value = response.data.winners
    }
  } catch (err) {
    console.error('Failed to load winners:', err)
  }
}

const getJackpotIcon = (type) => {
  const icons = {
    'global': '🌍',
    'category': '📚',
    'streak': '🔥',
    'daily': '📅',
    'mega': '💰'
  }
  return icons[type] || '💎'
}

const getConditionText = (jackpot) => {
  if (jackpot.condition_type === 'correct_answers') {
    return `${jackpot.condition_value} richtige Antworten in Folge`
  } else if (jackpot.condition_type === 'streak') {
    return `Streak von ${jackpot.condition_value} erreichen`
  } else if (jackpot.condition_type === 'category') {
    return `${jackpot.condition_value} Fragen in dieser Kategorie`
  } else if (jackpot.condition_type === 'daily') {
    return 'Täglich zufällig vergeben'
  }
  return jackpot.condition_type || 'Unbekannt'
}

const formatCoins = (coins) => {
  if (!coins) return '0'
  return parseFloat(coins).toLocaleString('de-DE', { maximumFractionDigits: 0 })
}

const formatRelativeTime = (dateString) => {
  if (!dateString) return 'Nie'

  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  const seconds = Math.floor(diff / 1000)
  const minutes = Math.floor(seconds / 60)
  const hours = Math.floor(minutes / 60)
  const days = Math.floor(hours / 24)

  if (days > 7) {
    return date.toLocaleDateString('de-DE')
  } else if (days > 0) {
    return `vor ${days} Tag${days > 1 ? 'en' : ''}`
  } else if (hours > 0) {
    return `vor ${hours} Stunde${hours > 1 ? 'n' : ''}`
  } else if (minutes > 0) {
    return `vor ${minutes} Minute${minutes > 1 ? 'n' : ''}`
  } else {
    return 'Gerade eben'
  }
}
</script>

<style scoped>
.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #f59e0b;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.card {
  @apply bg-white rounded-lg shadow p-6;
}
</style>
