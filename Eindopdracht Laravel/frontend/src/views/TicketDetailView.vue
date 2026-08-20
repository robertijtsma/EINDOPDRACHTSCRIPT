<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

interface Ticket {
  id: number
  title: string
  description: string
  status: string
  category_id: number
  created_at: string
}

interface Comment {
  id: number
  user_id: number
  content: string
  created_at: string
}

interface Note {
  id: number
  user_id: number
  content: string
  created_at: string
}

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const ticketId = route.params.id
const ticket = ref<Ticket | null>(null)
const comments = ref<Comment[]>([])
const notes = ref<Note[]>([])

const loading = ref(true)
const newComment = ref('')
const newNote = ref('')
const selectedStatus = ref('')

const submittingComment = ref(false)
const submittingNote = ref(false)
const updatingStatus = ref(false)

onMounted(async () => {
  await loadTicketData()
})

const loadTicketData = async () => {
  loading.value = true
  try {
    const resTicket = await axios.get(`/tickets/${ticketId}`)
    ticket.value = resTicket.data
    selectedStatus.value = resTicket.data.status

    const resComments = await axios.get(`/tickets/${ticketId}/comments`)
    comments.value = resComments.data

    if (authStore.isAdmin) {
      const resNotes = await axios.get(`/tickets/${ticketId}/notes`)
      notes.value = resNotes.data
    }
  } catch (err) {
    console.error('Fout bij ophalen ticketdetails:', err)
  } finally {
    loading.value = false
  }
}

// Reactie toevoegen (KLA-10657)
const handleAddComment = async () => {
  if (!newComment.value.trim()) return
  submittingComment.value = true

  try {
    await axios.post(`/tickets/${ticketId}/comments`, {
      content: newComment.value,
    })
    newComment.value = ''
    const res = await axios.get(`/tickets/${ticketId}/comments`)
    comments.value = res.data
  } catch (err) {
    console.error('Fout bij plaatsen reactie:', err)
  } finally {
    submittingComment.value = false
  }
}

// Interne notitie toevoegen (KLA-10659 - Alleen Admin)
const handleAddNote = async () => {
  if (!newNote.value.trim()) return
  submittingNote.value = true

  try {
    await axios.post(`/tickets/${ticketId}/notes`, {
      content: newNote.value,
    })
    newNote.value = ''
    const res = await axios.get(`/tickets/${ticketId}/notes`)
    notes.value = res.data
  } catch (err) {
    console.error('Fout bij plaatsen notitie:', err)
  } finally {
    submittingNote.value = false
  }
}

// Status bijwerken (KLA-10650 - Alleen Admin)
const handleStatusChange = async () => {
  updatingStatus.value = true
  try {
    await axios.put(`/tickets/${ticketId}`, {
      status: selectedStatus.value,
    })
    if (ticket.value) {
      ticket.value.status = selectedStatus.value
    }
  } catch (err) {
    console.error('Fout bij aanpassen status:', err)
  } finally {
    updatingStatus.value = false
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
</script>

<template>
  <div class="container">
    <button class="btn btn-outline-secondary mb-3" @click="router.push('/tickets')">
      &larr; Terug naar overzicht
    </button>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Laden...</span>
      </div>
    </div>

    <div v-else-if="ticket" class="row">
      <!-- Linkerkolom: Ticket Details & Reacties -->
      <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <h3 class="card-title text-primary">{{ ticket.title }}</h3>
              <span class="badge bg-secondary">#{{ ticket.id }}</span>
            </div>
            <p class="card-text text-secondary" style="white-space: pre-line">
              {{ ticket.description }}
            </p>
            <hr />
            <small class="text-muted">Aangemaakt op: {{ formatDate(ticket.created_at) }}</small>
          </div>
        </div>

        <!-- Reacties (Comments) -->
        <h4 class="mb-3">Reacties</h4>
        <div v-if="comments.length === 0" class="alert alert-light border mb-4">
          Nog geen reacties geplaatst.
        </div>
        <div v-else class="mb-4">
          <div v-for="comment in comments" :key="comment.id" class="card shadow-sm border-0 mb-2">
            <div class="card-body py-2">
              <div class="d-flex justify-content-between">
                <strong class="text-dark">Gebruiker #{{ comment.user_id }}</strong>
                <small class="text-muted">{{ formatDate(comment.created_at) }}</small>
              </div>
              <p class="mb-0 mt-1">{{ comment.content }}</p>
            </div>
          </div>
        </div>

        <!-- Reactie Formulier -->
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-body">
            <h5 class="card-title mb-3">Plaats een reactie</h5>
            <form @submit.prevent="handleAddComment">
              <div class="mb-3">
                <textarea
                  v-model="newComment"
                  class="form-control"
                  rows="3"
                  placeholder="Typ hier je bericht..."
                  required
                ></textarea>
              </div>
              <button type="submit" class="btn btn-primary" :disabled="submittingComment">
                {{ submittingComment ? 'Verzenden...' : 'Reactie Verzenden' }}
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Rechterkolom: Status Beheer & Interne Notities (Admin) -->
      <div class="col-lg-4">
        <!-- Status Beheer -->
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-body">
            <h5 class="card-title mb-3">Status</h5>
            <div v-if="authStore.isAdmin" class="mb-3">
              <select
                v-model="selectedStatus"
                class="form-select mb-2"
                @change="handleStatusChange"
                :disabled="updatingStatus"
              >
                <option value="open">Open</option>
                <option value="in_behandeling">In behandeling</option>
                <option value="gesloten">Gesloten</option>
              </select>
              <small class="text-muted">Als admin kun je de status direct wijzigen.</small>
            </div>
            <div v-else>
              <span class="badge bg-primary fs-6">{{ ticket.status }}</span>
            </div>
          </div>
        </div>

        <!-- Interne Notities (Alleen zichtbaar voor Admin, KLA-10658 & KLA-10659) -->
        <div v-if="authStore.isAdmin" class="card shadow-sm border-0 bg-warning-subtle">
          <div class="card-body">
            <h5 class="card-title text-dark">
              🔒 Interne Notities <small class="fs-6 text-muted">(Alleen Admin)</small>
            </h5>

            <div v-if="notes.length === 0" class="small text-muted mb-3">
              Geen interne notities.
            </div>

            <div v-for="note in notes" :key="note.id" class="card mb-2 border-0 shadow-sm">
              <div class="card-body p-2">
                <small class="d-block text-muted">{{ formatDate(note.created_at) }}</small>
                <p class="mb-0 small">{{ note.content }}</p>
              </div>
            </div>

            <form @submit.prevent="handleAddNote" class="mt-3">
              <div class="mb-2">
                <textarea
                  v-model="newNote"
                  class="form-control form-control-sm"
                  rows="2"
                  placeholder="Interne notitie voor collega's..."
                  required
                ></textarea>
              </div>
              <button type="submit" class="btn btn-dark btn-sm w-100" :disabled="submittingNote">
                {{ submittingNote ? 'Opslaan...' : 'Notitie Toevoegen' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>