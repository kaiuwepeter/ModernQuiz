<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="card">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">🛍️ Shop</h1>
          <p class="text-gray-600">Kaufe Powerups um deine Chancen im Quiz zu verbessern</p>
        </div>
        <div class="text-right">
          <div class="flex gap-4">
            <div class="bg-yellow-50 px-4 py-2 rounded-lg border border-yellow-200">
              <div class="text-xs text-yellow-600 font-medium">Coins</div>
              <div class="text-2xl font-bold text-yellow-700">{{ authStore.user?.coins || 0 }}</div>
            </div>
            <div class="bg-orange-50 px-4 py-2 rounded-lg border border-orange-200">
              <div class="text-xs text-orange-600 font-medium">Bonus Coins</div>
              <div class="text-2xl font-bold text-orange-700">{{ authStore.user?.bonus_coins || 0 }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Purchase Success Notification -->
    <div v-if="shopStore.lastPurchase" class="card bg-gradient-to-r from-green-500 to-emerald-600 text-white slide-up">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="text-4xl">✅</div>
          <div>
            <h3 class="text-lg font-bold">Kauf erfolgreich!</h3>
            <p class="text-sm opacity-90">
              {{ shopStore.lastPurchase.quantity }}x {{ shopStore.lastPurchase.powerup.name }} gekauft
            </p>
          </div>
        </div>
        <div class="text-right">
          <div class="text-2xl font-bold">-{{ shopStore.lastPurchase.cost }}</div>
          <div class="text-sm opacity-90">
            {{ getCurrencyName(shopStore.lastPurchase.currency) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs: Shop / Inventory -->
    <div class="card">
      <div class="flex gap-4 border-b border-gray-200">
        <button
          @click="activeTab = 'shop'"
          class="px-4 py-2 font-medium transition-colors"
          :class="activeTab === 'shop' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:text-gray-900'"
        >
          🛒 Powerups kaufen
        </button>
        <button
          @click="activeTab = 'inventory'"
          class="px-4 py-2 font-medium transition-colors"
          :class="activeTab === 'inventory' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:text-gray-900'"
        >
          🎒 Mein Inventar ({{ totalInventoryItems }})
        </button>
      </div>
    </div>

    <!-- Shop Tab -->
    <div v-if="activeTab === 'shop'" class="space-y-6">
      <!-- Loading -->
      <div v-if="shopStore.isLoading && shopStore.powerups.length === 0" class="card text-center py-12">
        <div class="spinner mx-auto mb-4"></div>
        <p class="text-gray-600">Lade Powerups...</p>
      </div>

      <!-- Powerups by Category -->
      <div v-else v-for="(powerups, category) in shopStore.powerupsByCategory" :key="category" class="space-y-4">
        <h2 class="text-xl font-bold text-gray-900">{{ getCategoryIcon(category) }} {{ category }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="powerup in powerups"
            :key="powerup.id"
            class="card hover:shadow-lg transition-all group cursor-pointer"
            @click="shopStore.openPurchaseModal(powerup)"
          >
            <!-- Powerup Icon/Image -->
            <div class="text-5xl mb-3 text-center">{{ getPowerupIcon(powerup.name) }}</div>

            <!-- Powerup Info -->
            <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">
              {{ powerup.name }}
            </h3>
            <p class="text-sm text-gray-600 mb-4">{{ powerup.description }}</p>

            <!-- Price & Inventory -->
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <div class="text-xl font-bold text-yellow-600">{{ powerup.price }}</div>
                <div class="text-sm text-gray-500">Coins</div>
              </div>
              <div v-if="shopStore.getInventoryCount(powerup.id) > 0" class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-medium">
                {{ shopStore.getInventoryCount(powerup.id) }}x im Inventar
              </div>
            </div>

            <!-- Effects/Stats -->
            <div v-if="powerup.effects" class="mt-3 pt-3 border-t border-gray-100">
              <div class="text-xs text-gray-500 space-y-1">
                <div v-if="powerup.effects.extra_time">⏱️ +{{ powerup.effects.extra_time }}s Zeit</div>
                <div v-if="powerup.effects.remove_answers">❌ {{ powerup.effects.remove_answers }} Antworten entfernen</div>
                <div v-if="powerup.effects.point_multiplier">✨ {{ powerup.effects.point_multiplier }}x Punkte</div>
                <div v-if="powerup.effects.skip_question">⏭️ Frage überspringen</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Powerups -->
      <div v-if="!shopStore.isLoading && shopStore.powerups.length === 0" class="card text-center py-12">
        <div class="text-6xl mb-4">🏪</div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Keine Powerups verfügbar</h3>
        <p class="text-gray-600">Schau später wieder vorbei!</p>
      </div>
    </div>

    <!-- Inventory Tab -->
    <div v-else-if="activeTab === 'inventory'" class="space-y-4">
      <!-- Loading -->
      <div v-if="shopStore.isLoading && shopStore.inventory.length === 0" class="card text-center py-12">
        <div class="spinner mx-auto mb-4"></div>
        <p class="text-gray-600">Lade Inventar...</p>
      </div>

      <!-- Inventory Grid -->
      <div v-else-if="shopStore.inventory.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="item in shopStore.inventory"
          :key="item.id"
          class="card"
        >
          <!-- Powerup Info -->
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center space-x-3">
              <div class="text-4xl">{{ getPowerupIcon(item.powerup_name) }}</div>
              <div>
                <h3 class="font-bold text-gray-900">{{ item.powerup_name }}</h3>
                <p class="text-sm text-gray-600">{{ item.powerup_description }}</p>
              </div>
            </div>
          </div>

          <!-- Quantity -->
          <div class="bg-indigo-50 rounded-lg p-3 text-center">
            <div class="text-3xl font-bold text-indigo-600">{{ item.quantity }}</div>
            <div class="text-sm text-indigo-700">Verfügbar</div>
          </div>

          <!-- Purchase Date -->
          <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
            Gekauft: {{ formatDate(item.purchased_at) }}
          </div>
        </div>
      </div>

      <!-- Empty Inventory -->
      <div v-else class="card text-center py-12">
        <div class="text-6xl mb-4">🎒</div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Dein Inventar ist leer</h3>
        <p class="text-gray-600 mb-4">Kaufe Powerups um deine Chancen zu verbessern!</p>
        <button @click="activeTab = 'shop'" class="btn-primary">
          Zum Shop
        </button>
      </div>
    </div>

    <!-- Purchase Modal -->
    <div v-if="shopStore.showPurchaseModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6 space-y-4 slide-up">
        <!-- Header -->
        <div class="flex items-start justify-between">
          <div class="flex items-center space-x-3">
            <div class="text-5xl">{{ getPowerupIcon(shopStore.selectedPowerup.name) }}</div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">{{ shopStore.selectedPowerup.name }}</h3>
              <p class="text-sm text-gray-600">{{ shopStore.selectedPowerup.description }}</p>
            </div>
          </div>
          <button @click="shopStore.closePurchaseModal" class="text-gray-400 hover:text-gray-600">
            <span class="text-2xl">×</span>
          </button>
        </div>

        <!-- Effects -->
        <div v-if="shopStore.selectedPowerup.effects" class="bg-gray-50 rounded-lg p-3">
          <div class="text-sm font-medium text-gray-700 mb-2">Effekte:</div>
          <div class="text-sm text-gray-600 space-y-1">
            <div v-if="shopStore.selectedPowerup.effects.extra_time">⏱️ +{{ shopStore.selectedPowerup.effects.extra_time }} Sekunden Zeit</div>
            <div v-if="shopStore.selectedPowerup.effects.remove_answers">❌ Entfernt {{ shopStore.selectedPowerup.effects.remove_answers }} falsche Antworten</div>
            <div v-if="shopStore.selectedPowerup.effects.point_multiplier">✨ {{ shopStore.selectedPowerup.effects.point_multiplier }}x Punkte-Multiplikator</div>
            <div v-if="shopStore.selectedPowerup.effects.skip_question">⏭️ Überspringt die Frage</div>
          </div>
        </div>

        <!-- Quantity Selector -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Anzahl:</label>
          <div class="flex items-center space-x-3">
            <button
              @click="shopStore.decrementQuantity"
              class="w-10 h-10 rounded-lg border-2 border-gray-300 hover:border-indigo-500 hover:bg-indigo-50 font-bold transition-colors"
              :disabled="shopStore.purchaseQuantity <= 1"
            >
              -
            </button>
            <input
              v-model.number="shopStore.purchaseQuantity"
              type="number"
              min="1"
              max="99"
              class="input text-center flex-1"
            />
            <button
              @click="shopStore.incrementQuantity"
              class="w-10 h-10 rounded-lg border-2 border-gray-300 hover:border-indigo-500 hover:bg-indigo-50 font-bold transition-colors"
              :disabled="shopStore.purchaseQuantity >= 99"
            >
              +
            </button>
          </div>
        </div>

        <!-- Currency Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Bezahlen mit:</label>
          <div class="grid grid-cols-3 gap-2">
            <button
              @click="shopStore.selectedCurrency = 'auto'"
              class="px-4 py-2 rounded-lg border-2 transition-colors text-sm font-medium"
              :class="shopStore.selectedCurrency === 'auto' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-700 hover:border-gray-300'"
            >
              🤖 Auto
            </button>
            <button
              @click="shopStore.selectedCurrency = 'coins'"
              class="px-4 py-2 rounded-lg border-2 transition-colors text-sm font-medium"
              :class="shopStore.selectedCurrency === 'coins' ? 'border-yellow-500 bg-yellow-50 text-yellow-700' : 'border-gray-200 text-gray-700 hover:border-gray-300'"
            >
              🪙 Coins
            </button>
            <button
              @click="shopStore.selectedCurrency = 'bonus_coins'"
              class="px-4 py-2 rounded-lg border-2 transition-colors text-sm font-medium"
              :class="shopStore.selectedCurrency === 'bonus_coins' ? 'border-orange-500 bg-orange-50 text-orange-700' : 'border-gray-200 text-gray-700 hover:border-gray-300'"
            >
              ⭐ Bonus
            </button>
          </div>
          <p class="text-xs text-gray-500 mt-2">
            Auto verwendet zuerst Bonus Coins, dann normale Coins
          </p>
        </div>

        <!-- Total Cost -->
        <div class="bg-indigo-50 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div class="font-medium text-gray-700">Gesamtpreis:</div>
            <div class="text-2xl font-bold text-indigo-600">{{ shopStore.totalCost }}</div>
          </div>
          <div class="text-sm text-gray-600 mt-1">
            {{ getCurrencyName(shopStore.selectedCurrency) }}
          </div>
        </div>

        <!-- Available Balance -->
        <div class="text-sm text-gray-600">
          <div class="flex justify-between">
            <span>Verfügbare Coins:</span>
            <span class="font-medium">{{ authStore.user?.coins || 0 }}</span>
          </div>
          <div class="flex justify-between">
            <span>Verfügbare Bonus Coins:</span>
            <span class="font-medium">{{ authStore.user?.bonus_coins || 0 }}</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
          <button @click="shopStore.closePurchaseModal" class="flex-1 btn-secondary">
            Abbrechen
          </button>
          <button
            @click="handlePurchase"
            :disabled="!shopStore.canAfford || shopStore.isLoading"
            class="flex-1 btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="shopStore.isLoading">Kaufen...</span>
            <span v-else-if="!shopStore.canAfford">Nicht genug Coins</span>
            <span v-else>Kaufen</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useShopStore } from '../store/shop'
import { useAuthStore } from '../store/auth'

const shopStore = useShopStore()
const authStore = useAuthStore()
const activeTab = ref('shop')

onMounted(() => {
  shopStore.loadPowerups()
  shopStore.loadInventory()
})

const totalInventoryItems = computed(() => {
  return shopStore.inventory.reduce((total, item) => total + item.quantity, 0)
})

const getCategoryIcon = (category) => {
  const icons = {
    'Zeit-Powerups': '⏱️',
    'Hilfs-Powerups': '💡',
    'Punkte-Powerups': '⭐',
    'Spezial-Powerups': '✨',
    'Sonstige': '🎁'
  }
  return icons[category] || '📦'
}

const getPowerupIcon = (name) => {
  // Map powerup names to emojis
  if (name.includes('Zeit') || name.includes('Time')) return '⏰'
  if (name.includes('50:50') || name.includes('Fifty')) return '🎯'
  if (name.includes('Punkte') || name.includes('Points')) return '⭐'
  if (name.includes('Skip') || name.includes('Überspringen')) return '⏭️'
  if (name.includes('Freeze') || name.includes('Einfrieren')) return '❄️'
  if (name.includes('Hint') || name.includes('Tipp')) return '💡'
  return '🎁'
}

const getCurrencyName = (currency) => {
  const names = {
    'auto': 'Auto (Bonus + Coins)',
    'coins': 'Coins',
    'bonus_coins': 'Bonus Coins'
  }
  return names[currency] || currency
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('de-DE', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const handlePurchase = async () => {
  try {
    await shopStore.purchase()
  } catch (error) {
    console.error('Purchase error:', error)
    alert('Fehler beim Kauf. Bitte versuche es erneut.')
  }
}
</script>

