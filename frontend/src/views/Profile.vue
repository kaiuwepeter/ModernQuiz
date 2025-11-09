<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Loading -->
    <div v-if="loading" class="card text-center py-12">
      <div class="spinner mx-auto mb-4"></div>
      <p class="text-gray-600">Lade Profil...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="card bg-red-50 border border-red-200">
      <div class="flex items-center space-x-3">
        <div class="text-3xl">❌</div>
        <div>
          <h3 class="font-bold text-red-800">Fehler</h3>
          <p class="text-red-600">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Profile Content -->
    <div v-else-if="user">
      <!-- Header Card -->
      <div class="card bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <!-- Avatar -->
            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center text-4xl">
              {{ user.avatar || '👤' }}
            </div>
            <!-- User Info -->
            <div>
              <h1 class="text-3xl font-bold">{{ user.username }}</h1>
              <p class="text-indigo-100">{{ user.email }}</p>
              <div class="mt-2 flex items-center space-x-2">
                <span v-if="user.role === 'admin'" class="px-2 py-1 bg-red-500 rounded text-xs font-bold">
                  ⚡ ADMIN
                </span>
                <span class="px-2 py-1 bg-white bg-opacity-20 rounded text-xs">
                  Level {{ user.level }}
                </span>
              </div>
            </div>
          </div>
          <!-- Stats Preview -->
          <div class="text-right">
            <div class="text-sm opacity-90">Gesamtpunkte</div>
            <div class="text-4xl font-bold">{{ user.points?.toLocaleString() || 0 }}</div>
          </div>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Coins -->
        <div class="card bg-yellow-50 border border-yellow-200">
          <div class="flex items-center space-x-3">
            <div class="text-4xl">💰</div>
            <div>
              <div class="text-sm text-gray-600">Coins</div>
              <div class="text-2xl font-bold text-yellow-700">{{ formatNumber(user.coins) }}</div>
            </div>
          </div>
        </div>

        <!-- Points -->
        <div class="card bg-blue-50 border border-blue-200">
          <div class="flex items-center space-x-3">
            <div class="text-4xl">⭐</div>
            <div>
              <div class="text-sm text-gray-600">Punkte</div>
              <div class="text-2xl font-bold text-blue-700">{{ formatNumber(user.points) }}</div>
            </div>
          </div>
        </div>

        <!-- Level -->
        <div class="card bg-purple-50 border border-purple-200">
          <div class="flex items-center space-x-3">
            <div class="text-4xl">🎯</div>
            <div>
              <div class="text-sm text-gray-600">Level</div>
              <div class="text-2xl font-bold text-purple-700">{{ user.level }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Account Details -->
      <div class="card">
        <h2 class="text-2xl font-bold mb-4">Account Details</h2>
        <div class="space-y-3">
          <div class="flex justify-between py-2 border-b">
            <span class="text-gray-600">Benutzername</span>
            <span class="font-medium">{{ user.username }}</span>
          </div>
          <div class="flex justify-between py-2 border-b">
            <span class="text-gray-600">E-Mail</span>
            <span class="font-medium">{{ user.email }}</span>
          </div>
          <div class="flex justify-between py-2 border-b">
            <span class="text-gray-600">E-Mail verifiziert</span>
            <span :class="user.email_verified ? 'text-green-600' : 'text-red-600'">
              {{ user.email_verified ? '✓ Ja' : '✗ Nein' }}
            </span>
          </div>
          <div class="flex justify-between py-2 border-b">
            <span class="text-gray-600">Account Status</span>
            <span :class="user.is_active ? 'text-green-600' : 'text-red-600'">
              {{ user.is_active ? '✓ Aktiv' : '✗ Deaktiviert' }}
            </span>
          </div>
          <div class="flex justify-between py-2 border-b">
            <span class="text-gray-600">Rolle</span>
            <span class="font-medium capitalize">{{ user.role }}</span>
          </div>
          <div class="flex justify-between py-2 border-b">
            <span class="text-gray-600">Mitglied seit</span>
            <span class="font-medium">{{ formatDate(user.created_at) }}</span>
          </div>
          <div v-if="user.last_login" class="flex justify-between py-2">
            <span class="text-gray-600">Letzter Login</span>
            <span class="font-medium">{{ formatDate(user.last_login) }}</span>
          </div>
        </div>
      </div>

      <!-- Referral Section -->
      <div v-if="user.referral_code" class="card bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200">
        <h2 class="text-2xl font-bold mb-4 text-green-800">🎁 Dein Referral Code</h2>
        <div class="flex items-center justify-between bg-white rounded-lg p-4 mb-3">
          <code class="text-2xl font-mono font-bold text-green-600">{{ user.referral_code }}</code>
          <button
            @click="copyReferralCode"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
          >
            {{ copied ? '✓ Kopiert!' : '📋 Kopieren' }}
          </button>
        </div>
        <p class="text-sm text-green-700">
          Teile deinen Code mit Freunden und erhalte Bonuscoins für jeden geworbenen Spieler!
        </p>
      </div>

      <!-- Actions -->
      <div class="card">
        <h2 class="text-2xl font-bold mb-4">Aktionen</h2>
        <div class="space-y-3">
          <button
            @click="showPasswordChange = true"
            class="w-full text-left p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition-all flex items-center justify-between"
          >
            <span class="font-medium">🔐 Passwort ändern</span>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <button
            @click="refreshProfile"
            class="w-full text-left p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition-all flex items-center justify-between"
          >
            <span class="font-medium">🔄 Profil aktualisieren</span>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Password Change Modal (Placeholder) -->
    <div v-if="showPasswordChange" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4">Passwort ändern</h3>
        <p class="text-gray-600 mb-4">Diese Funktion ist noch in Entwicklung.</p>
        <button
          @click="showPasswordChange = false"
          class="w-full btn-primary"
        >
          Schließen
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../store/auth'
import api from '../utils/api'

const authStore = useAuthStore()
const user = ref(null)
const loading = ref(true)
const error = ref(null)
const copied = ref(false)
const showPasswordChange = ref(false)

onMounted(async () => {
  await loadProfile()
})

const loadProfile = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await api.get('/user/profile')

    if (response.data.success) {
      user.value = response.data.user
      // Update auth store
      authStore.user = response.data.user
    } else {
      error.value = response.data.error || 'Fehler beim Laden des Profils'
    }
  } catch (err) {
    console.error('Failed to load profile:', err)
    error.value = err.response?.data?.error || 'Fehler beim Laden des Profils'
  } finally {
    loading.value = false
  }
}

const refreshProfile = async () => {
  await loadProfile()
}

const copyReferralCode = () => {
  navigator.clipboard.writeText(user.value.referral_code)
  copied.value = true
  setTimeout(() => {
    copied.value = false
  }, 2000)
}

const formatNumber = (num) => {
  return num?.toLocaleString('de-DE') || '0'
}

const formatDate = (dateString) => {
  if (!dateString) return 'Nie'
  const date = new Date(dateString)
  return date.toLocaleDateString('de-DE', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #6366f1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
