import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/tickets',
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
    },
    {
      path: '/tickets',
      name: 'tickets',
      // We laden dit later in
      component: () => import('../views/TicketsView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/tickets/:id',
      name: 'ticket-detail',
      component: () => import('../views/TicketDetailView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/categories',
      name: 'categories',
      component: () => import('../views/CategoriesView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
  ],
})

// Route Guard: Controleert bij elke pagina-wissel of de gebruiker mag kijken
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // 1. Pagina vereist inloggen, maar gebruiker heeft geen token -> Stuur naar /login
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  }
  // 2. Pagina vereist Admin rechten, maar gebruiker is geen admin -> Stuur naar /tickets
  else if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next('/tickets')
  }
  // 3. Gebruiker is al ingelogd en probeert naar /login of /register te gaan -> Stuur naar /tickets
  else if ((to.path === '/login' || to.path === '/register') && authStore.isAuthenticated) {
    next('/tickets')
  }
  // Alles oké? Doorlaten!
  else {
    next()
  }
})

export default router