import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Navigation Guard to Protect Routes
router.beforeEach(async (to, from, next) => {
  const { useAuthStore } = await import('@/stores/auth') // Import dynamically inside the function
  const auth = useAuthStore()

  // If user is not fetched yet, try fetching from API
  if (!auth.user && auth.token) {
    await auth.fetchUser()
  }

  // Redirect to login if route requires auth and user is not logged in
  if (to.meta.requiresAuth && !auth.user) {
    next('/login')
  } else {
    next()
  }
})

export default function (app) {
  app.use(router)
}
export { router }
