<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <div class="card bg-gradient-to-r from-purple-500 to-pink-600 text-white">
      <h1 class="text-3xl font-bold flex items-center space-x-2">
        <span>🎟️</span>
        <span>Gutscheine</span>
      </h1>
      <p class="text-purple-100 mt-2">Löse Gutscheincodes ein und erhalte Bonuscoins!</p>
    </div>

    <!-- Redeem Voucher -->
    <div class="card">
      <h2 class="text-2xl font-bold mb-4">Gutschein einlösen</h2>
      <div class="flex gap-3">
        <input
          v-model="voucherCode"
          type="text"
          placeholder="Gib deinen Gutscheincode ein..."
          class="input flex-1"
          @keyup.enter="redeemVoucher"
        />
        <button
          @click="redeemVoucher"
          :disabled="!voucherCode || loading"
          class="btn-primary"
        >
          {{ loading ? 'Wird eingelöst...' : 'Einlösen' }}
        </button>
      </div>

      <!-- Success Message -->
      <div v-if="success" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-center space-x-2 text-green-800">
          <span class="text-2xl">✅</span>
          <span class="font-medium">{{ success }}</span>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex items-center space-x-2 text-red-800">
          <span class="text-2xl">❌</span>
          <span class="font-medium">{{ error }}</span>
        </div>
      </div>
    </div>

    <!-- Info Section -->
    <div class="card bg-gray-50">
      <h3 class="font-bold mb-2">ℹ️ Wie funktionieren Gutscheine?</h3>
      <ul class="text-sm text-gray-600 space-y-2">
        <li>• Gutscheincodes können Coins oder Bonuscoins enthalten</li>
        <li>• Jeder Code kann nur einmal pro Benutzer eingelöst werden</li>
        <li>• Codes können ein Ablaufdatum haben</li>
        <li>• Achte auf offizielle Ankündigungen für neue Gutscheine!</li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../utils/api'

const voucherCode = ref('')
const loading = ref(false)
const success = ref(null)
const error = ref(null)

const redeemVoucher = async () => {
  if (!voucherCode.value) return

  loading.value = true
  success.value = null
  error.value = null

  try {
    const response = await api.post('/vouchers/redeem', {
      code: voucherCode.value.trim()
    })

    if (response.data.success) {
      success.value = response.data.message || 'Gutschein erfolgreich eingelöst!'
      voucherCode.value = ''
    } else {
      error.value = response.data.error || 'Gutschein konnte nicht eingelöst werden'
    }
  } catch (err) {
    error.value = err.response?.data?.error || 'Ein Fehler ist aufgetreten'
  } finally {
    loading.value = false
  }
}
</script>
