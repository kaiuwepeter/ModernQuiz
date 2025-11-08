<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Category Selection (if not started) -->
    <div v-if="!quizStore.quizActive && !showResults" class="space-y-6">
      <div class="card">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Quiz spielen 🎮</h1>
        <p class="text-gray-600">Wähle eine Kategorie oder spiele ein gemischtes Quiz</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- All Categories -->
        <button
          @click="startQuiz(null)"
          class="card hover:shadow-lg transition-shadow text-left group"
        >
          <div class="text-4xl mb-2">🎲</div>
          <h3 class="font-bold text-gray-900 group-hover:text-indigo-600">Alle Kategorien</h3>
          <p class="text-sm text-gray-600">Gemischte Fragen</p>
        </button>

        <!-- Category Cards -->
        <button
          v-for="category in quizStore.categories"
          :key="category.id"
          @click="startQuiz(category.id)"
          class="card hover:shadow-lg transition-shadow text-left group"
        >
          <div class="text-4xl mb-2">{{ getCategoryIcon(category.name) }}</div>
          <h3 class="font-bold text-gray-900 group-hover:text-indigo-600">
            {{ category.name }}
          </h3>
          <p class="text-sm text-gray-600">{{ category.question_count || '50' }} Fragen</p>
        </button>
      </div>
    </div>

    <!-- Active Quiz -->
    <div v-else-if="quizStore.quizActive && !showResults" class="space-y-6">
      <!-- Progress Bar -->
      <div class="card">
        <div class="flex justify-between items-center mb-2">
          <span class="text-sm font-medium text-gray-700">
            Frage {{ quizStore.questionNumber }} / {{ quizStore.totalQuestions }}
          </span>
          <span class="text-sm font-bold text-indigo-600">
            {{ quizStore.score }} Punkte
          </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div
            class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
            :style="{ width: quizStore.progress + '%' }"
          ></div>
        </div>
      </div>

      <!-- Timer -->
      <div class="card bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="text-3xl">⏱️</div>
            <div>
              <div class="text-sm opacity-90">Verbleibende Zeit</div>
              <div class="text-3xl font-bold">{{ quizStore.timeLeft }}s</div>
            </div>
          </div>
          <div class="text-right">
            <div class="text-sm opacity-90">Genauigkeit</div>
            <div class="text-2xl font-bold">{{ quizStore.accuracy }}%</div>
          </div>
        </div>
      </div>

      <!-- Question -->
      <div v-if="quizStore.currentQuestion && !questionAnswered" class="card fade-in">
        <div class="mb-6">
          <div class="text-sm text-indigo-600 font-medium mb-2">
            {{ quizStore.currentQuestion.category }}
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">
            {{ quizStore.currentQuestion.question }}
          </h2>
          <div class="flex items-center space-x-4 text-sm text-gray-600">
            <span>{{ getDifficultyText(quizStore.currentQuestion.difficulty) }}</span>
            <span>•</span>
            <span>{{ quizStore.currentQuestion.points }} Punkte</span>
          </div>
        </div>

        <!-- Answers -->
        <div class="space-y-3">
          <button
            v-for="answer in quizStore.currentQuestion.answers"
            :key="answer.id"
            @click="handleAnswer(answer.id)"
            :disabled="quizStore.isLoading"
            class="w-full text-left p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition-all disabled:opacity-50"
          >
            {{ answer.answer_text }}
          </button>
        </div>
      </div>

      <!-- Answer Feedback -->
      <div v-else-if="questionAnswered && answerResult" class="card slide-up">
        <div :class="[
          'p-6 rounded-lg mb-4',
          answerResult.correct ? 'bg-green-50' : 'bg-red-50'
        ]">
          <div class="flex items-center space-x-3 mb-2">
            <div class="text-4xl">{{ answerResult.correct ? '✅' : '❌' }}</div>
            <div>
              <h3 class="text-xl font-bold" :class="answerResult.correct ? 'text-green-800' : 'text-red-800'">
                {{ answerResult.correct ? 'Richtig!' : 'Leider falsch' }}
              </h3>
              <p class="text-sm" :class="answerResult.correct ? 'text-green-600' : 'text-red-600'">
                +{{ answerResult.points_earned }} Punkte
              </p>
            </div>
          </div>

          <p v-if="answerResult.explanation" class="text-gray-700 mt-4">
            {{ answerResult.explanation }}
          </p>
        </div>

        <button
          @click="nextQuestion"
          class="w-full btn-primary"
        >
          {{ quizStore.questionNumber >= quizStore.totalQuestions ? 'Quiz beenden' : 'Nächste Frage' }}
        </button>
      </div>

      <!-- Loading -->
      <div v-if="quizStore.isLoading" class="card text-center py-12">
        <div class="spinner mx-auto mb-4"></div>
        <p class="text-gray-600">Lädt...</p>
      </div>
    </div>

    <!-- Results -->
    <div v-else-if="showResults && finalResults" class="space-y-6 slide-up">
      <!-- Bonus Notification (if unlocked) -->
      <div v-if="finalResults.bonusUnlocked" class="card bg-gradient-to-r from-yellow-400 to-orange-500 text-white">
        <div class="text-center py-6">
          <div class="text-6xl mb-4">🎉</div>
          <h2 class="text-3xl font-bold mb-2">Glückwunsch!</h2>
          <p class="text-xl mb-4">{{ finalResults.referral_bonus.message }}</p>
          <p class="text-2xl font-bold">+{{ finalResults.referral_bonus.bonus_paid }} Bonus Coins!</p>
        </div>
      </div>

      <!-- Results Card -->
      <div class="card text-center">
        <div class="text-6xl mb-4">{{ getResultsEmoji() }}</div>
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Quiz beendet!</h2>
        <p class="text-gray-600 mb-8">{{ getResultsMessage() }}</p>

        <div class="grid grid-cols-3 gap-4 mb-8">
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-3xl font-bold text-indigo-600">{{ quizStore.score }}</div>
            <div class="text-sm text-gray-600">Punkte</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-3xl font-bold text-green-600">{{ quizStore.correctAnswers }}</div>
            <div class="text-sm text-gray-600">Richtige</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-3xl font-bold text-orange-600">{{ quizStore.accuracy }}%</div>
            <div class="text-sm text-gray-600">Genauigkeit</div>
          </div>
        </div>

        <div class="flex gap-4">
          <button @click="playAgain" class="flex-1 btn-primary">
            Nochmal spielen
          </button>
          <router-link to="/" class="flex-1 btn-secondary">
            Zum Dashboard
          </router-link>
        </div>
      </div>

      <!-- Answer Review -->
      <div class="card">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Antworten-Übersicht</h3>
        <div class="space-y-3">
          <div
            v-for="(answer, index) in quizStore.answers"
            :key="index"
            class="flex items-center justify-between p-3 rounded-lg"
            :class="answer.correct ? 'bg-green-50' : 'bg-red-50'"
          >
            <div class="flex items-center space-x-3">
              <span class="text-2xl">{{ answer.correct ? '✅' : '❌' }}</span>
              <div>
                <div class="font-medium" :class="answer.correct ? 'text-green-800' : 'text-red-800'">
                  Frage {{ index + 1 }}
                </div>
                <div class="text-sm text-gray-600">{{ answer.explanation }}</div>
              </div>
            </div>
            <div class="font-bold" :class="answer.correct ? 'text-green-600' : 'text-red-600'">
              +{{ answer.points }} Punkte
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useQuizStore } from '../store/quiz'

const quizStore = useQuizStore()
const questionAnswered = ref(false)
const answerResult = ref(null)
const showResults = ref(false)
const finalResults = ref(null)
const startTime = ref(null)

onMounted(() => {
  quizStore.loadCategories()
})

onUnmounted(() => {
  quizStore.stopTimer()
})

const getCategoryIcon = (name) => {
  const icons = {
    'Allgemeinwissen': '🌍',
    'Geographie': '🗺️',
    'Geschichte': '📜',
    'Wissenschaft & Natur': '🔬',
    'Technologie & Computer': '💻',
    'Sport': '⚽',
    'Unterhaltung & Medien': '🎬',
    'Mathematik': '🔢',
    'Kunst & Kultur': '🎨',
    'Literatur': '📚',
    'Politik': '🏛️',
    'Wirtschaft': '💼',
    'Sprachen': '🗣️',
    'Essen & Trinken': '🍕',
    'Musik': '🎵'
  }
  return icons[name] || '❓'
}

const getDifficultyText = (difficulty) => {
  const texts = {
    'easy': '⭐ Einfach',
    'medium': '⭐⭐ Mittel',
    'hard': '⭐⭐⭐ Schwer'
  }
  return texts[difficulty] || difficulty
}

const startQuiz = async (categoryId) => {
  showResults.value = false
  finalResults.value = null
  await quizStore.startQuiz(categoryId)
  startTime.value = Date.now()
}

const handleAnswer = async (answerId) => {
  const timeTaken = Math.floor((Date.now() - startTime.value) / 1000)

  try {
    const result = await quizStore.submitAnswer(answerId, timeTaken)
    answerResult.value = result
    questionAnswered.value = true
  } catch (error) {
    console.error('Error submitting answer:', error)
  }
}

const nextQuestion = async () => {
  questionAnswered.value = false
  answerResult.value = null
  startTime.value = Date.now()

  if (quizStore.questionNumber >= quizStore.totalQuestions) {
    // End quiz
    const results = await quizStore.endQuiz()
    finalResults.value = results
    showResults.value = true
  } else {
    // Load next question
    await quizStore.loadNextQuestion()
  }
}

const getResultsEmoji = () => {
  const accuracy = quizStore.accuracy
  if (accuracy === 100) return '🏆'
  if (accuracy >= 80) return '🌟'
  if (accuracy >= 60) return '👍'
  if (accuracy >= 40) return '😊'
  return '💪'
}

const getResultsMessage = () => {
  const accuracy = quizStore.accuracy
  if (accuracy === 100) return 'Perfekt! Alle Fragen richtig beantwortet!'
  if (accuracy >= 80) return 'Ausgezeichnet! Sehr gute Leistung!'
  if (accuracy >= 60) return 'Gut gemacht! Weiter so!'
  if (accuracy >= 40) return 'Nicht schlecht! Übung macht den Meister!'
  return 'Weiter üben! Du schaffst das!'
}

const playAgain = () => {
  quizStore.resetQuiz()
  showResults.value = false
  finalResults.value = null
}
</script>
