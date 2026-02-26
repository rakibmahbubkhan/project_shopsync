<template>
  <div class="flex h-screen bg-gray-100">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col">

      <div class="p-4 text-xl font-bold border-b border-gray-700">
        ERP System
      </div>

      <nav class="flex-1 p-3 space-y-2 text-sm">

        <RouterLink to="/dashboard" class="nav-item">Dashboard</RouterLink>
        <RouterLink to="/pos" class="nav-item">POS</RouterLink>
        <RouterLink to="/sales" class="nav-item">Sales</RouterLink>
        <RouterLink to="/products" class="nav-item">Products</RouterLink>
        <RouterLink to="/inventory" class="nav-item">Inventory</RouterLink>
        <RouterLink to="/warehouses" class="nav-item">Warehouses</RouterLink>
        <RouterLink to="/customers" class="nav-item">Customers</RouterLink>
        <RouterLink to="/reports" class="nav-item">Reports</RouterLink>

      </nav>

      <div class="p-4 border-t border-gray-700 text-xs">
        v1.0
      </div>

    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">

      <!-- Topbar -->
      <header class="bg-white shadow px-6 py-3 flex justify-between">

        <div>
          <h2 class="font-semibold">Welcome Back</h2>
        </div>

        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-600">
            Admin
          </span>
          <button class="text-red-600 text-sm">
            Logout
          </button>
        </div>

      </header>

      <!-- Page Content -->
      <main class="flex-1 p-6 overflow-auto">
        <router-view />
      </main>

    </div>

  </div>
</template>

<style>
.nav-item {
  display: block;
  padding: 8px 12px;
  border-radius: 6px;
}
.nav-item:hover {
  background: #374151;
}
</style>