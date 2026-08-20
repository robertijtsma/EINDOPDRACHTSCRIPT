<script setup lang="ts">
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
import axios from 'axios'

const authStore = useAuthStore()
const router = useRouter()

const handleLogout = async () => {
  try {
    // KLA-10644: Uitloggen via API
    await axios.post('/logout')
  } catch (error) {
    console.error('Fout bij uitloggen:', error)
  } finally {
    authStore.logout()
    router.push('/login')
  }
}
</script>

<template>
  <div class="min-vh-100 bg-light">
    <!-- Navigatiebalk -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
      <div class="container">
        <router-link class="navbar-brand fw-bold" to="/"> TicketSysteem </router-link>

        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div id="navbarNav" class="collapse navbar-collapse">
          <!-- Links voor ingelogde gebruikers -->
          <ul v-if="authStore.isAuthenticated" class="navbar-nav me-auto">
            <li class="nav-item">
              <router-link class="nav-link" to="/tickets"> Mijn Tickets </router-link>
            </li>
            <li v-if="authStore.isAdmin" class="nav-item">
              <router-link class="nav-link" to="/categories">
                Categorieën (Admin)
              </router-link>
            </li>
          </ul>

          <!-- Rechterkant van de navbar -->
          <ul class="navbar-nav ms-auto">
            <template v-if="authStore.isAuthenticated">
              <li class="nav-item d-flex align-items-center me-3 text-light">
                <small>
                  Ingelogd als: <strong>{{ authStore.user?.name }}</strong>
                  <span v-if="authStore.isAdmin" class="badge bg-danger ms-1">Admin</span>
                </small>
              </li>
              <li class="nav-item">
                <button class="btn btn-outline-light btn-sm mt-1" @click="handleLogout">
                  Uitloggen
                </button>
              </li>
            </template>
            <template v-else>
              <li class="nav-item">
                <router-link class="nav-link" to="/login">Inloggen</router-link>
              </li>
              <li class="nav-item">
                <router-link class="nav-link" to="/register">Registreren</router-link>
              </li>
            </template>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Hier worden de views (pagina's) ingeladen -->
    <main class="py-4">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
/* Zorgt ervoor dat de actieve link netjes oplicht */
.router-link-active.nav-link {
  color: #fff !important;
  font-weight: bold;
}
</style>