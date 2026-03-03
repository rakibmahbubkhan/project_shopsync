import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/authStore";

const routes = [
  {
    path: "/",
    component: () => import("@/layouts/AdminLayout.vue"),
    meta: { requiresAuth: true },
    children: [
      { path: "", name: "dashboard", component: () => import("@/views/dashboard/Dashboard.vue") },
      { path: "products", name: "products", component: () => import("@/views/Products/ProductList.vue") },
      { path: "sales", name: "sales", component: () => import("@/views/sales/SalesListView.vue") },
    ],
  },
  {
    path: "/login",
    // Use the new AuthLayout instead of MainLayout
    component: () => import("@/layouts/AuthLayout.vue"),
    children: [
      {
        path: "",
        name: "login",
        component: () => import("@/views/auth/LoginView.vue"),
      }
    ]
  },
  {
    path: "/pos",
    name: "pos",
    component: () => import("@/views/pos/POSView.vue"),
    meta: { requiresAuth: true }
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard to handle authentication
router.beforeEach((to, from, next) => {
  const auth = useAuthStore();
  
  // If the route requires auth and there is no token, redirect to login
  if (to.meta.requiresAuth && !auth.token) {
    next({ name: 'login' });
  } 
  // If a logged-in user tries to access the login page, redirect them to the dashboard
  else if (to.name === 'login' && auth.token) {
    next({ name: 'dashboard' });
  } 
  else {
    next();
  }
});

export default router;