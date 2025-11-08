import { defineStore } from 'pinia'
import { shopAPI } from '../utils/api'
import { useAuthStore } from './auth'

export const useShopStore = defineStore('shop', {
  state: () => ({
    powerups: [],
    inventory: [],
    isLoading: false,
    selectedPowerup: null,
    showPurchaseModal: false,
    purchaseQuantity: 1,
    selectedCurrency: 'auto', // auto, coins, bonus_coins
    lastPurchase: null
  }),

  getters: {
    // Get powerups grouped by category
    powerupsByCategory: (state) => {
      const grouped = {}
      state.powerups.forEach(powerup => {
        const category = powerup.category || 'Sonstige'
        if (!grouped[category]) {
          grouped[category] = []
        }
        grouped[category].push(powerup)
      })
      return grouped
    },

    // Get inventory item count for a powerup
    getInventoryCount: (state) => (powerupId) => {
      const item = state.inventory.find(i => i.powerup_id === powerupId)
      return item?.quantity || 0
    },

    // Calculate total cost for current selection
    totalCost: (state) => {
      if (!state.selectedPowerup) return 0
      return state.selectedPowerup.price * state.purchaseQuantity
    },

    // Check if user can afford the purchase
    canAfford: (state) => {
      const authStore = useAuthStore()
      if (!state.selectedPowerup || !authStore.user) return false

      const totalCost = state.selectedPowerup.price * state.purchaseQuantity

      if (state.selectedCurrency === 'coins') {
        return authStore.user.coins >= totalCost
      } else if (state.selectedCurrency === 'bonus_coins') {
        return authStore.user.bonus_coins >= totalCost
      } else if (state.selectedCurrency === 'auto') {
        // Auto: Try bonus coins first, then coins
        return (authStore.user.bonus_coins + authStore.user.coins) >= totalCost
      }

      return false
    }
  },

  actions: {
    async loadPowerups() {
      try {
        this.isLoading = true
        const response = await shopAPI.getPowerups()
        this.powerups = response.data
      } catch (error) {
        console.error('Failed to load powerups:', error)
      } finally {
        this.isLoading = false
      }
    },

    async loadInventory() {
      try {
        const response = await shopAPI.getInventory()
        this.inventory = response.data
      } catch (error) {
        console.error('Failed to load inventory:', error)
      }
    },

    openPurchaseModal(powerup) {
      this.selectedPowerup = powerup
      this.purchaseQuantity = 1
      this.selectedCurrency = 'auto'
      this.showPurchaseModal = true
    },

    closePurchaseModal() {
      this.showPurchaseModal = false
      this.selectedPowerup = null
      this.purchaseQuantity = 1
      this.selectedCurrency = 'auto'
    },

    async purchase() {
      if (!this.canAfford || !this.selectedPowerup) {
        return
      }

      try {
        this.isLoading = true
        const response = await shopAPI.purchase(
          this.selectedPowerup.id,
          this.purchaseQuantity,
          this.selectedCurrency
        )

        // Update user data (coins/bonus_coins)
        const authStore = useAuthStore()
        await authStore.fetchUser()

        // Reload inventory
        await this.loadInventory()

        // Save last purchase for notification
        this.lastPurchase = {
          powerup: this.selectedPowerup,
          quantity: this.purchaseQuantity,
          cost: response.data.total_cost,
          currency: response.data.currency_used
        }

        // Close modal
        this.closePurchaseModal()

        // Clear notification after 5 seconds
        setTimeout(() => {
          this.lastPurchase = null
        }, 5000)

        return response.data
      } catch (error) {
        console.error('Purchase failed:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    incrementQuantity() {
      if (this.purchaseQuantity < 99) {
        this.purchaseQuantity++
      }
    },

    decrementQuantity() {
      if (this.purchaseQuantity > 1) {
        this.purchaseQuantity--
      }
    }
  }
})
