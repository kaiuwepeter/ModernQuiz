<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600">
    <div class="card max-w-md w-full">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Registrierung</h1>
        <p class="text-gray-600 mt-2">Erstelle deinen Account</p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Benutzername
          </label>
          <input
            v-model="username"
            type="text"
            class="input"
            placeholder="deinusername"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            E-Mail
          </label>
          <input
            v-model="email"
            type="email"
            class="input"
            placeholder="email@example.com"
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
            placeholder="Min. 8 Zeichen"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Referral-Code (Optional)
          </label>
          <input
            v-model="referralCode"
            type="text"
            class="input"
            placeholder="USER1-ABC123"
          />
          <p class="text-xs text-gray-500 mt-1">
            300 Bonus Coins nach 10 Quizzes für dich und den Werber!
          </p>
        </div>

        <div v-if="errors.length > 0" class="bg-red-50 p-3 rounded-lg">
          <ul class="text-sm text-red-600 list-disc list-inside">
            <li v-for="err in errors" :key="err">{{ err }}</li>
          </ul>
        </div>

        <div v-if="success" class="bg-green-50 text-green-600 p-3 rounded-lg text-sm">
          {{ success }}
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full btn-primary"
        >
          {{ loading ? 'Registrieren...' : 'Registrieren' }}
        </button>
      </form>

      <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
          Bereits registriert?
          <router-link to="/login" class="text-indigo-600 hover:text-indigo-700 font-semibold">
            Anmelden
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

const username = ref('')
const email = ref('')
const password = ref('')
const referralCode = ref('')
const errors = ref([])
const success = ref(null)
const loading = ref(false)

const handleRegister = async () => {
  errors.value = []
  success.value = null
  loading.value = true

  const result = await authStore.register(
    username.value,
    email.value,
    password.value,
    referralCode.value || null
  )

  if (result.success) {
    success.value = result.message
    setTimeout(() => router.push('/login'), 2000)
  } else {
    errors.value = result.errors || ['Registrierung fehlgeschlagen']
  }

  loading.value = false
}
</script>
