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
      { path: 'customers', name: 'customers', component: () => import('@/views/customers/CustomerList.vue') },
      { path: '/customers/pending', name: 'PendingPayments', component: () => import('@/views/customers/PendingPayments.vue'), meta: { requiresAuth: true, layout: 'AdminLayout' } },

      // Products & Inventory
      { path: 'products', name: 'products', component: () => import('@/views/Products/ProductList.vue') },
      { path: '/products/damaged', name: 'DamagedProducts', component: () => import('@/views/Products/DamagedProductList.vue'), meta: { requiresAuth: true } },
      { path: 'categories', name: 'categories', component: () => import('@/views/inventory/CategoryList.vue')},
      { path: 'brands', name: 'brands', component: () => import('@/views/inventory/BrandList.vue')},
      { path: 'units', name: 'units', component: () => import('@/views/inventory/UnitList.vue')},
      { path: 'taxes',name: 'Taxes',component: () => import('@/views/inventory/TaxList.vue'),meta: { requiresAuth: true }},
      { path: 'variants',name: 'Variants',component: () => import('@/views/inventory/VariantList.vue'),meta: { requiresAuth: true }},

      // Sales & Returns
      { path: 'sales', name: 'sales', component: () => import('@/views/sales/SalesListView.vue') },
      { path: 'sales/returns', name: 'sales-returns', component: () => import('@/views/sales/ReturnListView.vue') },
      { path: 'sales/:id', name: 'sale-details', component: () => import('@/views/sales/SaleDetailsView.vue') },
      { path: '/sales/returns/create',name: 'return-create',component: () => import('@/views/sales/ReturnCreate.vue'), meta: { requiresAuth: true } },

      // Purchases
      { path: 'purchases', name: 'purchases', component: () => import('@/views/purchases/PurchaseList.vue') },
      { path: 'purchases/create', name: 'purchase-create', component: () => import('@/views/purchases/PurchaseCreate.vue') },
      { path: 'purchases/:id/edit', name: 'purchase-edit', component: () => import('@/views/purchases/PurchaseCreate.vue') },
      // Add these routes
      { path: '/purchases/returns', name: 'purchase-returns', component: () => import('@/views/purchases/PurchaseReturnList.vue'), meta: { requiresAuth: true } },
      { path: '/purchases/returns/create', name: 'purchase-return-create', component: () => import('@/views/purchases/PurchaseReturnCreate.vue'), meta: { requiresAuth: true } },

      { path: 'suppliers', name: 'suppliers', component: () => import('@/views/suppliers/SupplierList.vue')},
      { path: 'warehouses', name: 'warehouses', component: () => import('@/views/warehouses/WarehouseList.vue')},

      // Stock Operations
      { path: 'inventory/transfer/create', name: 'create-stock-transfer', component: () => import('@/views/inventory/TransferCreate.vue') },
      { path: 'inventory/transfer', name: 'stock-transfer', component: () => import('@/views/inventory/TransferList.vue') },

      // Financial Reports
      { path: 'financial', name: 'financial', component: () => import('@/views/financial/TrialBalance.vue') },

      // System Logs
      { path: 'audit-logs', name: 'audit-logs', component: () => import('@/views/settings/AuditLogList.vue') },

      // ... under children array of layout path: '/' ...
      // Expenses Module
      { path: 'expenses/categories', name: 'expense-categories', component: () => import('@/views/expenses/ExpenseCategoryList.vue'), meta: { requiresAuth: true } },
      { path: 'expenses', name: 'expenses-list', component: () => import('@/views/expenses/ExpenseList.vue'), meta: { requiresAuth: true } },
      { path: 'expenses/create', name: 'expense-create', component: () => import('@/views/expenses/ExpenseCreate.vue'), meta: { requiresAuth: true } },
      { path: 'expenses/:id/edit', name: 'expense-edit', component: () => import('@/views/expenses/ExpenseCreate.vue'), meta: { requiresAuth: true } },
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