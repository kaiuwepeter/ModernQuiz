import { defineStore } from 'pinia'
import { quizAPI } from '../utils/api'

export const useQuizStore = defineStore('quiz', {
  state: () => ({
    sessionId: null,
    currentQuestion: null,
    questionNumber: 0,
    totalQuestions: 10,
    score: 0,
    correctAnswers: 0,
    answers: [],
    categories: [],
    selectedCategory: null,
    isLoading: false,
    quizActive: false,
    timeLeft: 30,
    timerInterval: null,
    excludedQuestions: []
  }),

  getters: {
    progress: (state) => (state.questionNumber / state.totalQuestions) * 100,
    accuracy: (state) =>
      state.answers.length > 0
        ? Math.round((state.correctAnswers / state.answers.length) * 100)
        : 0
  },

  actions: {
    async loadCategories() {
      try {
        const response = await quizAPI.getCategories()
        this.categories = response.data
      } catch (error) {
        console.error('Failed to load categories:', error)
      }
    },

    async startQuiz(categoryId = null) {
      try {
        this.isLoading = true
        this.selectedCategory = categoryId
        this.questionNumber = 0
        this.score = 0
        this.correctAnswers = 0
        this.answers = []
        this.excludedQuestions = []

        // Start session
        const sessionResponse = await quizAPI.startSession(categoryId)
        this.sessionId = sessionResponse.data.session_id

        // Load first question
        await this.loadNextQuestion()
        this.quizActive = true
      } catch (error) {
        console.error('Failed to start quiz:', error)
        this.isLoading = false
      }
    },

    async loadNextQuestion() {
      try {
        this.isLoading = true

        const response = await quizAPI.getQuestion(
          this.selectedCategory,
          this.excludedQuestions
        )

        if (!response.data) {
          // No more questions
          await this.endQuiz()
          return
        }

        this.currentQuestion = response.data
        this.excludedQuestions.push(response.data.id)
        this.questionNumber++
        this.timeLeft = response.data.time_limit || 30
        this.startTimer()
        this.isLoading = false
      } catch (error) {
        console.error('Failed to load question:', error)
        this.isLoading = false
      }
    },

    async submitAnswer(answerId, timeTaken) {
      try {
        this.stopTimer()
        this.isLoading = true

        const response = await quizAPI.submitAnswer(
          this.sessionId,
          this.currentQuestion.id,
          answerId,
          timeTaken
        )

        const result = response.data

        // Save answer
        this.answers.push({
          questionId: this.currentQuestion.id,
          answerId: answerId,
          correct: result.correct,
          points: result.points_earned,
          explanation: result.explanation
        })

        if (result.correct) {
          this.correctAnswers++
          this.score += result.points_earned
        }

        this.isLoading = false

        // Check if quiz should end
        if (this.questionNumber >= this.totalQuestions) {
          await this.endQuiz()
        }

        return result
      } catch (error) {
        console.error('Failed to submit answer:', error)
        this.isLoading = false
        throw error
      }
    },

    async endQuiz() {
      try {
        this.stopTimer()

        const response = await quizAPI.endSession(this.sessionId)
        this.quizActive = false

        // Check for referral bonus (10th quiz)
        if (response.data.referral_bonus) {
          return {
            ...response.data,
            bonusUnlocked: true
          }
        }

        return response.data
      } catch (error) {
        console.error('Failed to end quiz:', error)
        this.quizActive = false
      }
    },

    startTimer() {
      this.stopTimer()
      this.timerInterval = setInterval(() => {
        if (this.timeLeft > 0) {
          this.timeLeft--
        } else {
          // Time's up - auto-submit wrong answer
          this.handleTimeout()
        }
      }, 1000)
    },

    stopTimer() {
      if (this.timerInterval) {
        clearInterval(this.timerInterval)
        this.timerInterval = null
      }
    },

    async handleTimeout() {
      this.stopTimer()
      // Submit with first answer (will be wrong)
      if (this.currentQuestion?.answers?.[0]) {
        await this.submitAnswer(
          this.currentQuestion.answers[0].id,
          this.currentQuestion.time_limit
        )
      }
    },

    resetQuiz() {
      this.stopTimer()
      this.sessionId = null
      this.currentQuestion = null
      this.questionNumber = 0
      this.score = 0
      this.correctAnswers = 0
      this.answers = []
      this.quizActive = false
      this.excludedQuestions = []
    }
  }
})
