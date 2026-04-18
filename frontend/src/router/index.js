import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const routes = [
  // --- Admin Area (Requires Login) ---
  {
    path: '/',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', name: 'dashboard', component: () => import('@/views/dashboard/Dashboard.vue') },

      // User Management
      { path: 'users', name: 'users', component: () => import('@/views/users/UserManagement.vue') },

      // Products & Inventory
      { path: 'products', name: 'products', component: () => import('@/views/Products/ProductList.vue') },

      // Sales & Returns
      { path: 'sales', name: 'sales', component: () => import('@/views/sales/SalesListView.vue') },
      { path: 'sales/return', name: 'return-create', component: () => import('@/views/sales/ReturnCreate.vue') },
      { path: 'sales/:id', name: 'sale-details', component: () => import('@/views/sales/SaleDetailsView.vue') },

      // Purchases
      { path: 'purchases', name: 'purchases', component: () => import('@/views/purchases/PurchaseList.vue') },
      { path: 'purchases/create', name: 'purchase-create', component: () => import('@/views/purchases/PurchaseCreate.vue') },
      { path: 'purchases/:id/edit', name: 'purchase-edit', component: () => import('@/views/purchases/PurchaseCreate.vue') },

      { path: 'suppliers', name: 'suppliers', component: () => import('@/views/suppliers/SupplierList.vue')},
      { path: 'warehouses', name: 'warehouses', component: () => import('@/views/warehouses/WarehouseList.vue')},

      // Stock Operations
      { path: 'inventory/transfer', name: 'stock-transfer', component: () => import('@/views/inventory/TransferCreate.vue') },

      // Financial Reports
      { path: 'financial', name: 'financial', component: () => import('@/views/financial/TrialBalance.vue') },

      // System Logs
      { path: 'audit-logs', name: 'audit-logs', component: () => import('@/views/settings/AuditLogList.vue') },
    ]
  },

  // --- POS System (Special Full-Screen View) ---
  {
    path: '/pos',
    name: 'pos',
    component: () => import('@/views/pos/POSView.vue'),
    meta: { requiresAuth: true }
  },

  // --- Auth Area (Public) ---
  {
    path: '/auth',
    component: () => import('@/layouts/AuthLayout.vue'),
    children: [
      { path: 'login', name: 'login', component: () => import('@/views/auth/LoginView.vue') }
    ]
  },

  // Redirect any unknown paths to Dashboard
  { path: '/:pathMatch(.*)*', redirect: '/' }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Security Gatekeeper: Check for auth token before each navigation
router.beforeEach((to, from, next) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.token) {
    next({ name: 'login' });
  } else if (to.name === 'login' && auth.token) {
    next({ name: 'dashboard' });
  } else {
    next();
  }
});

export default router;