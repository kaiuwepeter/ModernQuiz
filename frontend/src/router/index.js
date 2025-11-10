import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../store/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'Home',
      component: () => import('../views/Home.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/login',
      name: 'Login',
      component: () => import('../views/Login.vue'),
      meta: { guest: true }
    },
    {
      path: '/register',
      name: 'Register',
      component: () => import('../views/Register.vue'),
      meta: { guest: true }
    },
    {
      path: '/quiz',
      name: 'Quiz',
      component: () => import('../views/Quiz.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/leaderboard',
      name: 'Leaderboard',
      component: () => import('../views/Leaderboard.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/shop',
      name: 'Shop',
      component: () => import('../views/Shop.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/profile',
      name: 'Profile',
      component: () => import('../views/Profile.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/admin',
      name: 'Admin',
      component: () => import('../views/Admin.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/bank',
      name: 'Bank',
      component: () => import('../views/Bank.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/vouchers',
      name: 'Vouchers',
      component: () => import('../views/Vouchers.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/jackpots',
      name: 'Jackpots',
      component: () => import('../views/Jackpots.vue'),
      meta: { requiresAuth: true }
    }
  ]
})

// Navigation Guards
router.beforeEach((to, from, next) => {
  // Check auth directly from localStorage for reliability
  const token = localStorage.getItem('session_token')
  const isAuthenticated = !!token

  // For admin check, we need the store
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && isAuthenticated) {
    next('/')
  } else if (to.meta.requiresAdmin) {
    // Check if user is admin
    const isAdmin = authStore.user?.role === 'admin' || authStore.user?.is_admin === true
    if (!isAdmin) {
      next('/')  // Redirect to home if not admin
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router
