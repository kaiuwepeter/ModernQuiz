import axios from 'axios'

// Basis-Konfiguration
const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json'
  }
})

// Request Interceptor - Fügt Token hinzu
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('session_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response Interceptor - Error Handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token ungültig - redirect zu Login
      localStorage.removeItem('session_token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// Helper Funktionen
export const quizAPI = {
  getCategories: () => api.get('/quiz/categories'),
  startSession: (categoryId) => api.post('/quiz/start', { category_id: categoryId }),
  getQuestion: (categoryId, exclude = []) => api.get('/quiz/question', {
    params: { category_id: categoryId, exclude: exclude.join(',') }
  }),
  submitAnswer: (sessionId, questionId, answerId, timeTaken) =>
    api.post('/quiz/answer', { session_id: sessionId, question_id: questionId, answer_id: answerId, time_taken: timeTaken }),
  endSession: (sessionId) => api.post('/quiz/end', { session_id: sessionId })
}

export const shopAPI = {
  getPowerups: () => api.get('/shop/powerups'),
  getInventory: () => api.get('/shop/inventory'),
  purchase: (powerupId, quantity, currency = 'auto') =>
    api.post('/shop/purchase', { powerup_id: powerupId, quantity, currency })
}

export const leaderboardAPI = {
  getGlobal: () => api.get('/leaderboard/global'),
  getWeekly: () => api.get('/leaderboard/weekly'),
  getMonthly: () => api.get('/leaderboard/monthly')
}

export const chatAPI = {
  getMessages: (limit = 50) => api.get('/chat/messages', { params: { limit } }),
  sendMessage: (message) => api.post('/chat/send', { message }),
  getUnreadCount: () => api.get('/chat/unread')
}

export default api
