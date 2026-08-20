<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

interface Category {
  id: number
  name: string
}

interface Ticket {
  id: number
  title: string
  description: string
  status: string
  category_id: number
  created_at: string
}

const router = useRouter()
const authStore = useAuthStore()

const tickets = ref<Ticket[]>([])
const categories = ref<Category[]>([])
const loading = ref(true)

// Filter & Zoek variabelen
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedStatus = ref('')

// Modal zichtbaarheid vlag (Vue-native)
const showModal = ref(false)

// Formulier variabelen voor nieuw ticket
const newTitle = ref('')
const newDescription = ref('')
const newCategoryId = ref('')
const submitting = ref(false)
const errorMessage = ref('')

onMounted(async () => {
  await fetchCategories()
  await fetchTickets()
})

const fetchCategories = async () => {
  try {
    const res = await axios.get('/categories')
    categories.value = res.data
  } catch (err) {
    console.error('Fout bij ophalen categorieën:', err)
  }
}

const fetchTickets = async () => {
  loading.value = true
  try {
    const res = await axios.get('/tickets')
    tickets.value = res.data
  } catch (err) {
    console.error('Fout bij ophalen tickets:', err)
  } finally {
    loading.value = false
  }
}

const filteredTickets = computed(() => {
  return tickets.value.filter((ticket) => {
    const matchesSearch =
      ticket.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      ticket.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory =
      selectedCategory.value === '' || ticket.category_id === Number(selectedCategory.value)
    const matchesStatus = selectedStatus.value === '' || ticket.status === selectedStatus.value

    return matchesSearch && matchesCategory && matchesStatus
  })
})

const openModal = () => {
  errorMessage.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  newTitle.value = ''
  newDescription.value = ''
  newCategoryId.value = ''
  errorMessage.value = ''
}

const handleCreateTicket = async () => {
  errorMessage.value = ''
  submitting.value = true

  try {
    await axios.post('/tickets', {
      title: newTitle.value,
      description: newDescription.value,
      category_id: newCategoryId.value,
    })

    // Sluit de modal netjes via Vue state
    closeModal()

    // Herlaad direct de tickets in de tabel
    await fetchTickets()
  } catch (err: any) {
    if (err.response?.data?.errors) {
      const firstErrorKey = Object.keys(err.response.data.errors)[0]
      errorMessage.value = err.response.data.errors[firstErrorKey][0]
    } else {
      errorMessage.value = err.response?.data?.message || 'Er is iets misgegaan bij het aanmaken.'
    }
  } finally {
    submitting.value = false
  }
}

const getCategoryName = (categoryId: number) => {
  const cat = categories.value.find((c) => c.id === categoryId)
  return cat ? cat.name : 'Onbekend'
}

const getStatusBadge = (status: string) => {
  switch (status) {
    case 'open':
      return 'bg-success'
    case 'in_behandeling':
      return 'bg-warning text-dark'
    case 'gesloten':
      return 'bg-secondary'
    default:
      return 'bg-primary'
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('nl-NL', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const goToDetail = (id: number) => {
  router.push(`/tickets/${id}`)
}
</script>

<template>
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>
        {{ authStore.isAdmin ? 'Alle Tickets (Beheerder Overzicht)' : 'Mijn Tickets' }}
      </h2>
      <button class="btn btn-primary" @click="openModal">+ Nieuw Ticket Aanmaken</button>
    </div>

    <!-- Filter & Zoekbalk -->
    <div class="card shadow-sm mb-4 border-0">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-5">
            <input
              v-model="searchQuery"
              type="text"
              class="form-control"
              placeholder="Zoek op titel of omschrijving..."
            />
          </div>
          <div class="col-md-3">
            <select v-model="selectedCategory" class="form-select">
              <option value="">Alle Categorieën</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>
          <div class="col-md-4">
            <select v-model="selectedStatus" class="form-select">
              <option value="">Alle Statussen</option>
              <option value="open">Open</option>
              <option value="in_behandeling">In behandeling</option>
              <option value="gesloten">Gesloten</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Tickets Tabel -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Laden...</span>
      </div>
    </div>

    <div v-else-if="filteredTickets.length === 0" class="alert alert-info text-center">
      Geen tickets gevonden.
    </div>

    <div v-else class="card shadow-sm border-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Titel</th>
              <th>Categorie</th>
              <th>Status</th>
              <th>Aangemaakt op</th>
              <th class="text-end">Actie</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ticket in filteredTickets" :key="ticket.id">
              <td>
                <strong>#{{ ticket.id }}</strong>
              </td>
              <td>{{ ticket.title }}</td>
              <td>
                <span class="badge bg-light text-dark border">
                  {{ getCategoryName(ticket.category_id) }}
                </span>
              </td>
              <td>
                <span :class="['badge', getStatusBadge(ticket.status)]">
                  {{ ticket.status }}
                </span>
              </td>
              <td>
                <small class="text-muted">{{ formatDate(ticket.created_at) }}</small>
              </td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" @click="goToDetail(ticket.id)">
                  Bekijken
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Vue-Native Modal voor Nieuw Ticket -->
    <div
      v-if="showModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(0, 0, 0, 0.5)"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Nieuw Ticket Aanmaken</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <form @submit.prevent="handleCreateTicket">
            <div class="modal-body">
              <div v-if="errorMessage" class="alert alert-danger">
                {{ errorMessage }}
              </div>

              <div class="mb-3">
                <label class="form-label">Titel</label>
                <input
                  v-model="newTitle"
                  type="text"
                  class="form-control"
                  required
                  placeholder="Bijv. Wachtwoord reset werkt niet"
                />
              </div>

              <div class="mb-3">
                <label class="form-label">Categorie</label>
                <select v-model="newCategoryId" class="form-select" required>
                  <option value="" disabled>Kies een categorie...</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Omschrijving van het probleem</label>
                <textarea
                  v-model="newDescription"
                  class="form-control"
                  rows="4"
                  required
                  placeholder="Leg hier uit wat er aan de hand is..."
                ></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeModal">
                Annuleren
              </button>
              <button type="submit" class="btn btn-primary" :disabled="submitting">
                {{ submitting ? 'Verzenden...' : 'Ticket Aanmaken' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>