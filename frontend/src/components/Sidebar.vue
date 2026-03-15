<template>
  <aside class="w-64 bg-gray-900 text-white h-screen flex flex-col shadow-xl">
    <div class="p-6 border-b border-gray-800">
      <h1 class="text-xl font-bold text-blue-400 tracking-tight">ShopSync</h1>
    </div>
    
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
      <router-link to="/" class="nav-link">Dashboard</router-link>
      <router-link to="/pos" class="nav-link">POS System</router-link>
      <router-link to="/products" class="nav-link">Products</router-link>
      <router-link to="/sales" class="nav-link">Sales History</router-link>
      
      <div v-if="isAdmin" class="pt-4 mt-4 border-t border-gray-800">
        <p class="px-3 text-xs font-semibold text-gray-500 uppercase mb-2">Administration</p>
        <router-link to="/users" class="nav-link">User Management</router-link>
        <router-link to="/financial" class="nav-link">Financial Reports</router-link>
      </div>
    </nav>

    <div class="p-4 border-t border-gray-800 text-xs text-gray-500">
      Logged in as: <span class="text-gray-300 font-medium">{{ auth.user?.name }}</span>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/authStore';

const auth = useAuthStore();

// Restrict visibility to Admin role
const isAdmin = computed(() => {
  return auth.user?.role?.name === 'Admin';
});
</script>

<style scoped>
.nav-link {
  @apply block px-4 py-2.5 text-sm font-medium rounded-lg transition-colors text-gray-400 hover:text-white hover:bg-gray-800;
}
.router-link-active {
  @apply bg-blue-600 text-white shadow-lg shadow-blue-900/20;
}
</style>