export const routes = [
  { path: '/', redirect: '/dashboard' },
  {
    path: '/',
    component: () => import('@/layouts/default.vue'),
    children: [
      {
        path: 'dashboard',
        component: () => import('@/pages/dashboard.vue'),
        meta: { requiresAuth: true },
      },
      {
        path: 'posts/create',
        component: () => import('@/pages/posts/create.vue'),
        meta: { requiresAuth: true },
      },
      {
        path: 'posts/scheduled',
        component: () => import('@/pages/posts/scheduled.vue'),
        meta: { requiresAuth: true },
      },
      {
        path: 'posts/scheduler',
        component: () => import('@/pages/posts/scheduler.vue'),
      },
      {
        path: 'account-config',
        component: () => import('@/pages/social/config.vue'),
      },
      {
        path: 'calendar',
        component: () => import('@/pages/calendar.vue'),
      },
      {
        path: 'account-settings',
        component: () => import('@/pages/account-settings.vue'),
      },
      {
        path: 'typography',
        component: () => import('@/pages/typography.vue'),
      },
      {
        path: 'icons',
        component: () => import('@/pages/icons.vue'),
      },
      {
        path: 'cards',
        component: () => import('@/pages/cards.vue'),
      },
      {
        path: 'tables',
        component: () => import('@/pages/tables.vue'),
      },
      {
        path: 'form-layouts',
        component: () => import('@/pages/form-layouts.vue'),
      },
    ],
  },
  {
    path: '/',
    component: () => import('@/layouts/blank.vue'),
    children: [
      {
        path: '/auth/callback',
        component: () => import('@/pages/AuthCallback.vue'),
      },
      {
        path: 'login',
        component: () => import('@/pages/login.vue'),
      },
      {
        path: 'register',
        component: () => import('@/pages/register.vue'),
      },
      {
        path: '/:pathMatch(.*)*',
        component: () => import('@/pages/[...error].vue'),
      },
    ],
  },
]
