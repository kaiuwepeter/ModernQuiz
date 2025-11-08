<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600">
    <div class="card max-w-md w-full">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">🎮 ModernQuiz</h1>
        <p class="text-gray-600 mt-2">Melde dich an und spiele!</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Benutzername oder E-Mail
          </label>
          <input
            v-model="identifier"
            type="text"
            class="input"
            placeholder="username oder email@example.com"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Passwort
          </label>
          <input
            v-model="password"
            type="password"
            class="input"
            placeholder="••••••••"
            required
          />
        </div>

        <div v-if="error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm">
          {{ error }}
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full btn-primary"
        >
          {{ loading ? 'Anmelden...' : 'Anmelden' }}
        </button>
      </form>

      <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
          Noch kein Account?
          <router-link to="/register" class="text-indigo-600 hover:text-indigo-700 font-semibold">
            Registrieren
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'

const router = useRouter()
const authStore = useAuthStore()

const identifier = ref('')
const password = ref('')
const error = ref(null)
const loading = ref(false)

const handleLogin = async () => {
  error.value = null
  loading.value = true

  const result = await authStore.login(identifier.value, password.value)

  if (result.success) {
    router.push('/')
  } else {
    error.value = result.error || 'Login fehlgeschlagen'
  }

  loading.value = false
}
</script>
