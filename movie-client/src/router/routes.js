// movie-client/src/router/routes.js
const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [{ path: '', component: () => import('pages/IndexPage.vue') }],
  },
  {
    path: '/movie/:id',
    component: () => import('layouts/MainLayout.vue'),
    children: [{ path: '', component: () => import('pages/MovieDetailPage.vue') }],
  },
  {
    path: '/login',
    component: () => import('pages/UserLogin.vue'), // ← Fixed
  },
  {
    path: '/register',
    component: () => import('pages/UserRegister.vue'), // ← Fixed
  },
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/UserError404.vue'), // ← Now exists
  },
]

export default routes
