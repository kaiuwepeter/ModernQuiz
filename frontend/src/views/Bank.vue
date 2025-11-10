<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="card">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">🏦 Bank</h1>
          <p class="text-gray-600">Lege deine Coins an und erhalte Zinsen</p>
        </div>
        <div class="text-right">
          <div class="bg-yellow-50 px-4 py-2 rounded-lg border border-yellow-200">
            <div class="text-xs text-yellow-600 font-medium">Verfügbare Coins</div>
            <div class="text-2xl font-bold text-yellow-700">{{ formatCoins(authStore.user?.coins) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bank Balance Card -->
    <div v-if="bankBalance" class="card bg-gradient-to-r from-green-500 to-emerald-600 text-white">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm opacity-90 mb-1">Bank-Guthaben</div>
          <div class="text-4xl font-bold">{{ formatCoins(bankBalance.total_deposited) }}</div>
          <div class="text-sm opacity-90 mt-2">
            Erwartete Zinsen: {{ formatCoins(bankBalance.expected_interest) }}
          </div>
        </div>
        <div class="text-6xl opacity-20">💰</div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="card">
      <div class="flex gap-4 border-b border-gray-200">
        <button
          @click="activeTab = 'deposit'"
          class="px-4 py-2 font-medium transition-colors"
          :class="activeTab === 'deposit' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:text-gray-900'"
        >
          💵 Einzahlen
        </button>
        <button
          @click="activeTab = 'deposits'"
          class="px-4 py-2 font-medium transition-colors"
          :class="activeTab === 'deposits' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:text-gray-900'"
        >
          📋 Meine Einlagen ({{ deposits.length }})
        </button>
      </div>
    </div>

    <!-- Deposit Tab -->
    <div v-if="activeTab === 'deposit'" class="card">
      <h2 class="text-2xl font-bold mb-4">Neue Einlage erstellen</h2>

      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="font-bold text-blue-900 mb-2">ℹ️ Zinssätze</h3>
        <ul class="text-sm text-blue-800 space-y-1">
          <li>• 7 Tage: <strong>5% Zinsen</strong> (Vorzeitig: 2%)</li>
          <li>• 14 Tage: <strong>12% Zinsen</strong> (Vorzeitig: 5%)</li>
          <li>• 30 Tage: <strong>30% Zinsen</strong> (Vorzeitig: 10%)</li>
        </ul>
      </div>

      <div class="space-y-4">
        <!-- Coins Input -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Coins einzahlen:</label>
          <input
            v-model.number="depositForm.coins"
            type="number"
            min="0"
            :max="authStore.user?.coins || 0"
            class="input"
            placeholder="0"
          />
          <button
            @click="depositForm.coins = authStore.user?.coins || 0"
            class="text-xs text-indigo-600 hover:underline mt-1"
          >
            Maximum: {{ formatCoins(authStore.user?.coins) }}
          </button>
        </div>

        <!-- Bonus Coins Input -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Bonus Coins einzahlen:</label>
          <input
            v-model.number="depositForm.bonusCoins"
            type="number"
            min="0"
            :max="authStore.user?.bonus_coins || 0"
            class="input"
            placeholder="0"
          />
        </div>
      </div>

      <!-- Submit Button -->
      <button
        @click="createDeposit"
        :disabled="!canCreateDeposit || loading"
        class="w-full mt-6 btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="loading">Wird erstellt...</span>
        <span v-else>💰 Einlage erstellen</span>
      </button>

      <!-- Error Message -->
      <div v-if="error" class="mt-4 card bg-red-50 border border-red-200">
        <p class="text-red-800">{{ error }}</p>
      </div>
    </div>

    <!-- Deposits List Tab -->
    <div v-else-if="activeTab === 'deposits'" class="space-y-4">
      <!-- Loading -->
      <div v-if="loading" class="card text-center py-12">
        <div class="spinner mx-auto mb-4"></div>
        <p class="text-gray-600">Lade Einlagen...</p>
      </div>

      <!-- Deposits Grid -->
      <div v-else-if="deposits.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
          v-for="deposit in deposits"
          :key="deposit.id"
          class="card"
        >
          <div class="text-lg font-bold">{{ formatCoins(deposit.total_amount) }}</div>
          <div class="text-sm text-gray-600">Status: {{ deposit.status }}</div>
        </div>
      </div>

      <!-- No Deposits -->
      <div v-else class="card text-center py-12">
        <div class="text-6xl mb-4">🏦</div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Keine Einlagen</h3>
        <p class="text-gray-600 mb-4">Erstelle deine erste Einlage und erhalte Zinsen!</p>
        <button @click="activeTab = 'deposit'" class="btn-primary">
          Jetzt einzahlen
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../store/auth'
import api from '../utils/api'

const authStore = useAuthStore()
const activeTab = ref('deposit')
const loading = ref(false)
const error = ref(null)

const bankBalance = ref(null)
const deposits = ref([])

const depositForm = ref({
  coins: 0,
  bonusCoins: 0
})

onMounted(async () => {
  await loadBankBalance()
  await loadDeposits()
})

const canCreateDeposit = computed(() => {
  const total = depositForm.value.coins + depositForm.value.bonusCoins
  return total > 0
})

const loadBankBalance = async () => {
  try {
    const response = await api.get('/bank/balance')
    if (response.data.success) {
      bankBalance.value = response.data.balance
    }
  } catch (err) {
    console.error('Failed to load bank balance:', err)
  }
}

const loadDeposits = async () => {
  try {
    loading.value = true
    const response = await api.get('/bank/deposits')
    if (response.data.success) {
      deposits.value = response.data.deposits
    }
  } catch (err) {
    console.error('Failed to load deposits:', err)
  } finally {
    loading.value = false
  }
}

const createDeposit = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await api.post('/bank/deposit', {
      coins: depositForm.value.coins,
      bonus_coins: depositForm.value.bonusCoins
    })

    if (response.data.success) {
      depositForm.value = { coins: 0, bonusCoins: 0 }
      // Update user coins from response
      if (response.data.user) {
        authStore.user = { ...authStore.user, ...response.data.user }
      }
      await loadBankBalance()
      await loadDeposits()
      activeTab.value = 'deposits'
    } else {
      error.value = response.data.error || 'Fehler beim Erstellen der Einlage'
    }
  } catch (err) {
    error.value = err.response?.data?.error || 'Fehler beim Erstellen der Einlage'
  } finally {
    loading.value = false
  }
}

const formatCoins = (coins) => {
  if (!coins) return '0'
  return parseFloat(coins).toLocaleString('de-DE', { maximumFractionDigits: 0 })
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

.input {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent;
}

.btn-primary {
  @apply px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium;
}

.card {
  @apply bg-white rounded-lg shadow p-6;
}
</style>
