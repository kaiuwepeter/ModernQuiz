import { defineStore } from 'pinia'
import { leaderboardAPI } from '../utils/api'

export const useLeaderboardStore = defineStore('leaderboard', {
  state: () => ({
    globalLeaderboard: [],
    weeklyLeaderboard: [],
    monthlyLeaderboard: [],
    userRank: {
      global: null,
      weekly: null,
      monthly: null
    },
    isLoading: false,
    lastUpdate: null,
    autoRefreshInterval: null,
    autoRefreshEnabled: true
  }),

  getters: {
    // Get top 10 players for each leaderboard
    topGlobal: (state) => state.globalLeaderboard.slice(0, 10),
    topWeekly: (state) => state.weeklyLeaderboard.slice(0, 10),
    topMonthly: (state) => state.monthlyLeaderboard.slice(0, 10),

    // Check if user is in top 10
    isUserInTop10Global: (state) => state.userRank.global && state.userRank.global <= 10,
    isUserInTop10Weekly: (state) => state.userRank.weekly && state.userRank.weekly <= 10,
    isUserInTop10Monthly: (state) => state.userRank.monthly && state.userRank.monthly <= 10,

    // Time since last update
    timeSinceUpdate: (state) => {
      if (!state.lastUpdate) return null
      const seconds = Math.floor((Date.now() - state.lastUpdate) / 1000)
      if (seconds < 60) return `${seconds}s`
      if (seconds < 3600) return `${Math.floor(seconds / 60)}m`
      return `${Math.floor(seconds / 3600)}h`
    }
  },

  actions: {
    async loadGlobalLeaderboard() {
      try {
        this.isLoading = true
        const response = await leaderboardAPI.getGlobal()
        this.globalLeaderboard = response.data.leaderboard
        this.userRank.global = response.data.user_rank
        this.lastUpdate = Date.now()
      } catch (error) {
        console.error('Failed to load global leaderboard:', error)
      } finally {
        this.isLoading = false
      }
    },

    async loadWeeklyLeaderboard() {
      try {
        this.isLoading = true
        const response = await leaderboardAPI.getWeekly()
        this.weeklyLeaderboard = response.data.leaderboard
        this.userRank.weekly = response.data.user_rank
        this.lastUpdate = Date.now()
      } catch (error) {
        console.error('Failed to load weekly leaderboard:', error)
      } finally {
        this.isLoading = false
      }
    },

    async loadMonthlyLeaderboard() {
      try {
        this.isLoading = true
        const response = await leaderboardAPI.getMonthly()
        this.monthlyLeaderboard = response.data.leaderboard
        this.userRank.monthly = response.data.user_rank
        this.lastUpdate = Date.now()
      } catch (error) {
        console.error('Failed to load monthly leaderboard:', error)
      } finally {
        this.isLoading = false
      }
    },

    async loadAllLeaderboards() {
      await Promise.all([
        this.loadGlobalLeaderboard(),
        this.loadWeeklyLeaderboard(),
        this.loadMonthlyLeaderboard()
      ])
    },

    // Auto-refresh leaderboards every 30 seconds
    startAutoRefresh() {
      if (this.autoRefreshInterval) {
        return
      }

      this.autoRefreshEnabled = true
      this.autoRefreshInterval = setInterval(() => {
        if (this.autoRefreshEnabled) {
          this.loadAllLeaderboards()
        }
      }, 30000) // 30 seconds
    },

    stopAutoRefresh() {
      if (this.autoRefreshInterval) {
        clearInterval(this.autoRefreshInterval)
        this.autoRefreshInterval = null
      }
      this.autoRefreshEnabled = false
    },

    toggleAutoRefresh() {
      this.autoRefreshEnabled = !this.autoRefreshEnabled
      if (this.autoRefreshEnabled && !this.autoRefreshInterval) {
        this.startAutoRefresh()
      }
    },

    // Manual refresh
    async refresh() {
      await this.loadAllLeaderboards()
    },

    getRankColor(rank) {
      if (rank === 1) return 'text-yellow-500' // Gold
      if (rank === 2) return 'text-gray-400' // Silver
      if (rank === 3) return 'text-orange-600' // Bronze
      return 'text-gray-700'
    },

    getRankEmoji(rank) {
      if (rank === 1) return '🥇'
      if (rank === 2) return '🥈'
      if (rank === 3) return '🥉'
      return `#${rank}`
    }
  }
})
