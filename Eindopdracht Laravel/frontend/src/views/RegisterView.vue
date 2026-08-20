<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const errorMessage = ref('')
const loading = ref(false)

const router = useRouter()
const authStore = useAuthStore()

const handleRegister = async () => {
  errorMessage.value = ''

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'Wachtwoorden komen niet overeen.'
    return
  }

  loading.value = true

  try {
    const response = await axios.post('/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })

    authStore.setAuth(response.data.user, response.data.access_token)
    router.push('/tickets')
  } catch (error: any) {
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'Registratie mislukt. Probeer het opnieuw.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
          <div class="card-body p-4">
            <h3 class="card-title text-center mb-4">Account Aanmaken</h3>

            <div v-if="errorMessage" class="alert alert-danger" role="alert">
              {{ errorMessage }}
            </div>

            <form @submit.prevent="handleRegister">
              <div class="mb-3">
                <label for="name" class="form-label">Naam</label>
                <input
                  id="name"
                  v-model="name"
                  type="text"
                  class="form-control"
                  required
                  placeholder="Volledige naam"
                />
              </div>

              <div class="mb-3">
                <label for="email" class="form-label">E-mailadres</label>
                <input
                  id="email"
                  v-model="email"
                  type="email"
                  class="form-control"
                  required
                  placeholder="naam@voorbeeld.nl"
                />
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Wachtwoord</label>
                <input
                  id="password"
                  v-model="password"
                  type="password"
                  class="form-control"
                  required
                  minlength="8"
                />
              </div>

              <div class="mb-3">
                <label for="passwordConfirmation" class="form-label">Herhaal Wachtwoord</label>
                <input
                  id="passwordConfirmation"
                  v-model="passwordConfirmation"
                  type="password"
                  class="form-control"
                  required
                />
              </div>

              <button type="submit" class="btn btn-success w-100 py-2" :disabled="loading">
                {{ loading ? 'Account aanmaken...' : 'Registreren' }}
              </button>
            </form>

            <div class="mt-3 text-center">
              <small class="text-muted">
                Al een account?
                <router-link to="/login">Inloggen</router-link>
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>