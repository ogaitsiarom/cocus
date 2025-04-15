import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import NotesListView from '@/views/NotesListView.vue'
import NoteDetailView from '@/views/NoteDetailView.vue'

import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/login',
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/notes',
      name: 'notes',
      component: NotesListView,
      meta: { requiresAuth: true },
    },
    {
      path: '/notes/:id',
      name: 'note-detail',
      component: NoteDetailView,
      meta: { requiresAuth: true },
    },
  ],
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.hasValidToken()) {
    next('/login')
  } else if (to.path === '/login' && authStore.hasValidToken()) {
    next('/notes')
  } else {
    next()
  }
})

export default router
