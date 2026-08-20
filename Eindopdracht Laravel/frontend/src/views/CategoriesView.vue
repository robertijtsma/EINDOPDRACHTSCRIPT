<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

interface Category {
  id: number
  name: string
}

const categories = ref<Category[]>([])
const loading = ref(true)

const newCategoryName = ref('')
const editingCategoryId = ref<number | null>(null)
const editingCategoryName = ref('')

const submitting = ref(false)
const errorMessage = ref('')

onMounted(async () => {
  await fetchCategories()
})

const fetchCategories = async () => {
  loading.value = true
  try {
    const res = await axios.get('/categories')
    categories.value = res.data
  } catch (err) {
    console.error('Fout bij ophalen categorieën:', err)
  } finally {
    loading.value = false
  }
}

// Categorie aanmaken (KLA-10653)
const handleCreateCategory = async () => {
  if (!newCategoryName.value.trim()) return
  submitting.value = true
  errorMessage.value = ''

  try {
    await axios.post('/categories', { name: newCategoryName.value })
    newCategoryName.value = ''
    await fetchCategories()
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Aanmaken mislukt.'
  } finally {
    submitting.value = false
  }
}

// Bewerken starten
const startEditing = (category: Category) => {
  editingCategoryId.value = category.id
  editingCategoryName.value = category.name
}

// Bewerken annuleren
const cancelEditing = () => {
  editingCategoryId.value = null
  editingCategoryName.value = ''
}

// Categorie aanpassen (KLA-10654)
const handleUpdateCategory = async (id: number) => {
  if (!editingCategoryName.value.trim()) return

  try {
    await axios.put(`/categories/${id}`, { name: editingCategoryName.value })
    editingCategoryId.value = null
    await fetchCategories()
  } catch (err) {
    console.error('Fout bij bijwerken categorie:', err)
  }
}

// Categorie verwijderen (KLA-10655)
const handleDeleteCategory = async (id: number) => {
  if (!confirm('Weet je zeker dat je deze categorie wilt verwijderen?')) return

  try {
    await axios.delete(`/categories/${id}`)
    await fetchCategories()
  } catch (err) {
    console.error('Fout bij verwijderen categorie:', err)
  }
}
</script>

<template>
  <div class="container">
    <h2 class="mb-4">Categorieën Beheren (Admin)</h2>

    <div class="row">
      <!-- Linkerkolom: Nieuwe categorie toevoegen -->
      <div class="col-md-5 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title mb-3">Nieuwe Categorie</h5>

            <div v-if="errorMessage" class="alert alert-danger">
              {{ errorMessage }}
            </div>

            <form @submit.prevent="handleCreateCategory">
              <div class="mb-3">
                <label class="form-label">Naam van de categorie</label>
                <input
                  v-model="newCategoryName"
                  type="text"
                  class="form-control"
                  placeholder="Bijv. Hardware, Netwerk, Software"
                  required
                />
              </div>

              <button type="submit" class="btn btn-success w-100" :disabled="submitting">
                {{ submitting ? 'Toevoegen...' : '+ Categorie Toevoegen' }}
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Rechterkolom: Overzicht & Bewerken -->
      <div class="col-md-7">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title mb-3">Bestaande Categorieën</h5>

            <div v-if="loading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Laden...</span>
              </div>
            </div>

            <div v-else-if="categories.length === 0" class="alert alert-info">
              Nog geen categorieën aanwezig.
            </div>

            <ul v-else class="list-group list-group-flush">
              <li
                v-for="cat in categories"
                :key="cat.id"
                class="list-group-item d-flex justify-content-between align-items-center py-3"
              >
                <!-- Bewerkstand -->
                <template v-if="editingCategoryId === cat.id">
                  <input
                    v-model="editingCategoryName"
                    type="text"
                    class="form-control me-2"
                  />
                  <div class="btn-group">
                    <button class="btn btn-sm btn-success" @click="handleUpdateCategory(cat.id)">
                      Opslaan
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" @click="cancelEditing">
                      Annuleren
                    </button>
                  </div>
                </template>

                <!-- Normale stand -->
                <template v-else>
                  <span><strong>{{ cat.name }}</strong></span>
                  <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" @click="startEditing(cat)">
                      Bewerken
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="handleDeleteCategory(cat.id)">
                      Verwijderen
                    </button>
                  </div>
                </template>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>