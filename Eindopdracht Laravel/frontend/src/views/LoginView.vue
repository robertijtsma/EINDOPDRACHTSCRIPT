<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)

const router = useRouter()
const authStore = useAuthStore()

const handleLogin = async () => {
  errorMessage.value = ''
  loading.value = true

  try {
    const response = await axios.post('/login', {
      email: email.value,
      password: password.value,
    })

    authStore.setAuth(response.data.user, response.data.access_token)
    router.push('/tickets')
  } catch (error: any) {
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'Inloggen mislukt. Controleer je gegevens.'
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
            <h3 class="card-title text-center mb-4 font-weight-bold">Inloggen</h3>

            <div v-if="errorMessage" class="alert alert-danger" role="alert">
              {{ errorMessage }}
            </div>

            <form @submit.prevent="handleLogin">
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
                />
              </div>

              <button type="submit" class="btn btn-primary w-100 py-2" :disabled="loading">
                {{ loading ? 'Bezig met inloggen...' : 'Inloggen' }}
              </button>
            </form>

            <div class="mt-3 text-center">
              <small class="text-muted">
                Nog geen account?
                <router-link to="/register">Registreer hier</router-link>
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>