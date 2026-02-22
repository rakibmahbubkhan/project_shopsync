import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/authStore";

const routes = [
  {
    path: "/",
    component: () => import("@/layouts/AdminLayout.vue"),
    meta: { requiresAuth: true },
    children: [
      { path: "", component: () => import("@/views/dashboard/Dashboard.vue") },
      { path: "products", component: () => import("@/views/products/ProductList.vue") },
      { path: "sales", component: () => import("@/views/sales/SaleList.vue") },
      { path: "financial", component: () => import("@/views/financial/TrialBalance.vue") },
    ],
  },
  {
    path: "/login",
    component: () => import("@/layouts/AuthLayout.vue"),
  },
  {
    path: "/pos",
    component: () => import("@/views/pos/POSView.vue"),
    meta: { requiresAuth: true }
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const auth = useAuthStore();
  if (to.meta.requiresAuth && !auth.token) {
    next("/login");
  } else {
    next();
  }
});

export default router;
