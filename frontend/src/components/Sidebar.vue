<template>
  <aside 
    class="bg-gray-900 text-white flex-col transition-all duration-300
           hidden md:flex md:w-64 lg:w-72"
  >
    <div class="p-6 text-xl font-bold border-b border-gray-800 tracking-tight">
      ShopSync
    </div>

    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
      <router-link to="/" class="nav-item">Dashboard</router-link>
      <router-link to="/pos" class="nav-item">POS System</router-link>
      <router-link to="/products" class="nav-item">Products</router-link>
      <router-link to="/sales" class="nav-item">Sales List</router-link>
    </nav>

    <div class="p-4 border-t border-gray-800 text-xs text-gray-500">
      v1.2.0 | Stable
    </div>
  </aside>
</template>

<style scoped>
.nav-item {
  @apply flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors;
}
.nav-item:hover {
  @apply bg-gray-800 text-white;
}
.router-link-active {
  @apply bg-primary text-white shadow-lg shadow-blue-900/20;
}
</style>