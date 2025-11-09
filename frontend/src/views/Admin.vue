<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="card bg-gradient-to-r from-red-500 to-pink-600 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold flex items-center space-x-2">
            <span>⚙️</span>
            <span>Admin Dashboard</span>
          </h1>
          <p class="text-red-100 mt-1">Verwaltung und Überwachung der ModernQuiz Plattform</p>
        </div>
        <div class="text-right">
          <div class="text-sm opacity-90">Eingeloggt als</div>
          <div class="text-2xl font-bold">{{ authStore.user?.username }}</div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="card bg-blue-50 border border-blue-200">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-gray-600">Gesamt User</div>
            <div class="text-3xl font-bold text-blue-700">{{ stats.totalUsers || '-' }}</div>
          </div>
          <div class="text-4xl">👥</div>
        </div>
      </div>

      <div class="card bg-green-50 border border-green-200">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-gray-600">Quiz Sessions</div>
            <div class="text-3xl font-bold text-green-700">{{ stats.totalQuizzes || '-' }}</div>
          </div>
          <div class="text-4xl">🎮</div>
        </div>
      </div>

      <div class="card bg-yellow-50 border border-yellow-200">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-gray-600">Fragen</div>
            <div class="text-3xl font-bold text-yellow-700">{{ stats.totalQuestions || '-' }}</div>
          </div>
          <div class="text-4xl">❓</div>
        </div>
      </div>

      <div class="card bg-purple-50 border border-purple-200">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-gray-600">Aktive Sessions</div>
            <div class="text-3xl font-bold text-purple-700">{{ stats.activeSessions || '-' }}</div>
          </div>
          <div class="text-4xl">🔥</div>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="card">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              activeTab === tab.id
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            {{ tab.icon }} {{ tab.name }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Tab Content -->
    <div v-if="activeTab === 'overview'" class="space-y-6">
      <div class="card">
        <h2 class="text-2xl font-bold mb-4">📊 Systemübersicht</h2>
        <p class="text-gray-600">Hier erscheinen Systemstatistiken und Überwachungsdaten.</p>
      </div>
    </div>

    <div v-else-if="activeTab === 'users'" class="space-y-6">
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-bold">👥 Benutzerverwaltung</h2>
          <button class="btn-primary">+ Neuer User</button>
        </div>
        <p class="text-gray-600 mb-4">User-Liste und Verwaltungstools</p>
        <div class="bg-gray-50 p-8 rounded-lg text-center">
          <div class="text-6xl mb-4">🚧</div>
          <p class="text-gray-600">User-Verwaltung wird implementiert...</p>
        </div>
      </div>
    </div>

    <div v-else-if="activeTab === 'quiz'" class="space-y-6">
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-bold">❓ Quiz-Verwaltung</h2>
          <button class="btn-primary">+ Neue Frage</button>
        </div>
        <p class="text-gray-600 mb-4">Fragen und Kategorien verwalten</p>
        <div class="bg-gray-50 p-8 rounded-lg text-center">
          <div class="text-6xl mb-4">🚧</div>
          <p class="text-gray-600">Quiz-Verwaltung wird implementiert...</p>
        </div>
      </div>
    </div>

    <div v-else-if="activeTab === 'vouchers'" class="space-y-6">
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-bold">🎟️ Gutschein-Verwaltung</h2>
          <button class="btn-primary">+ Neuer Gutschein</button>
        </div>
        <p class="text-gray-600 mb-4">Gutscheine erstellen und verwalten</p>
        <div class="bg-gray-50 p-8 rounded-lg text-center">
          <div class="text-6xl mb-4">🚧</div>
          <p class="text-gray-600">Gutschein-Verwaltung wird implementiert...</p>
        </div>
      </div>
    </div>

    <div v-else-if="activeTab === 'bank'" class="space-y-6">
      <div class="card">
        <h2 class="text-2xl font-bold mb-4">🏦 Bank-Verwaltung</h2>
        <p class="text-gray-600 mb-4">Einlagen und Transaktionen überwachen</p>
        <div class="bg-gray-50 p-8 rounded-lg text-center">
          <div class="text-6xl mb-4">🚧</div>
          <p class="text-gray-600">Bank-Verwaltung wird implementiert...</p>
        </div>
      </div>
    </div>

    <div v-else-if="activeTab === 'settings'" class="space-y-6">
      <div class="card">
        <h2 class="text-2xl font-bold mb-4">⚙️ Systemeinstellungen</h2>
        <div class="space-y-4">
          <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <div>
              <div class="font-medium">Wartungsmodus</div>
              <div class="text-sm text-gray-600">Aktiviert den Wartungsmodus für alle User</div>
            </div>
            <button class="px-4 py-2 bg-gray-300 rounded-lg">Deaktiviert</button>
          </div>

          <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <div>
              <div class="font-medium">Neue Registrierungen</div>
              <div class="text-sm text-gray-600">Erlaubt neue User-Registrierungen</div>
            </div>
            <button class="px-4 py-2 bg-green-600 text-white rounded-lg">Aktiv</button>
          </div>

          <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <div>
              <div class="font-medium">Debug-Modus</div>
              <div class="text-sm text-gray-600">Zeigt erweiterte Debug-Informationen</div>
            </div>
            <button class="px-4 py-2 bg-gray-300 rounded-lg">Deaktiviert</button>
          </div>
        </div>
      </div>
    </div>

    <!-- System Info -->
    <div class="card bg-gray-50">
      <h3 class="font-bold mb-2">ℹ️ System Info</h3>
      <div class="text-sm text-gray-600 space-y-1">
        <div>ModernQuiz Admin Panel v1.0</div>
        <div>Vue.js {{ vueVersion }}</div>
        <div>Letzter Build: {{ buildDate }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../store/auth'
import { version as vueVersion } from 'vue'

const authStore = useAuthStore()
const activeTab = ref('overview')
const stats = ref({
  totalUsers: null,
  totalQuizzes: null,
  totalQuestions: null,
  activeSessions: null
})

const tabs = [
  { id: 'overview', name: 'Übersicht', icon: '📊' },
  { id: 'users', name: 'Benutzer', icon: '👥' },
  { id: 'quiz', name: 'Quiz', icon: '❓' },
  { id: 'vouchers', name: 'Gutscheine', icon: '🎟️' },
  { id: 'bank', name: 'Bank', icon: '🏦' },
  { id: 'settings', name: 'Einstellungen', icon: '⚙️' }
]

const buildDate = new Date().toLocaleDateString('de-DE')

onMounted(async () => {
  await loadStats()
})

const loadStats = async () => {
  // TODO: Load stats from API
  // Placeholder values
  stats.value = {
    totalUsers: 42,
    totalQuizzes: 156,
    totalQuestions: 500,
    activeSessions: 12
  }
}
</script>
