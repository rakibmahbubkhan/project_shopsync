<template>
  <aside class="w-64 bg-white shadow-xl h-screen fixed left-0 top-0 flex flex-col border-r border-gray-100">
    <div class="p-6 border-b border-gray-50">
      <h2 class="text-2xl font-black text-blue-600 tracking-tight flex items-center gap-2">
        <span class="bg-blue-600 text-white p-1 rounded-lg text-lg">SS</span>
        ShopSync
      </h2>
    </div>
    
    <nav class="flex-1 mt-4 px-4 space-y-1 overflow-y-auto custom-scrollbar">
      <p class="px-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Main Menu</p>
      
      <router-link to="/dashboard" class="nav-item" active-class="active">
        <span class="icon">📊</span> Dashboard
      </router-link>

      <router-link to="/pos" class="nav-item" active-class="active">
        <span class="icon">🖥️</span> POS System
      </router-link>

      <router-link to="/products" class="nav-item" active-class="active">
        <span class="icon">🔧</span> Inventory
      </router-link>

      <div class="pt-4">
        <p class="px-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Operations</p>
        
        <router-link to="/sales" class="nav-item" active-class="active">
          <span class="icon">💰</span> Sales History
        </router-link>

        <router-link to="/purchases/create" class="nav-item" active-class="active">
          <span class="icon">📦</span> New Purchase
        </router-link>

        <router-link to="/inventory/transfer" class="nav-item" active-class="active">
          <span class="icon">🚚</span> Stock Transfer
        </router-link>
      </div>

      <div v-if="isAdmin" class="pt-4 border-t border-gray-50 mt-4">
        <p class="px-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Administration</p>
        
        <router-link to="/users" class="nav-item" active-class="active">
          <span class="icon">👥</span> Staff Management
        </router-link>

        <router-link to="/financial" class="nav-item" active-class="active">
          <span class="icon">📈</span> Financial Reports
        </router-link>

        <router-link to="/audit-logs" class="nav-item" active-class="active">
          <span class="icon">📜</span> System Logs
        </router-link>
      </div>
    </nav>
    
    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-bold text-lg">
          {{ auth.user?.name?.charAt(0).toUpperCase() || 'U' }}
        </div>
        <div class="overflow-hidden">
          <p class="text-sm font-bold text-gray-800 truncate">{{ auth.user?.name || 'Loading...' }}</p>
          <p class="text-[10px] text-gray-500 font-medium uppercase tracking-tighter">
            {{ auth.user?.role?.name || 'Guest' }}
          </p>
        </div>
      </div>
      <button @click="auth.logout" class="w-full mt-4 text-xs font-bold text-red-500 hover:text-red-700 transition-colors flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-red-50">
        🚪 Logout
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/authStore';

const auth = useAuthStore();

/**
 * Validates if the user has administrative privileges 
 * based on the role assigned in the database.
 */
const isAdmin = computed(() => {
  return auth.user?.role?.name === 'Admin';
});
</script>

<style scoped>
/* Tailwind v4 Reference Directive for Scoped Styles */
@reference "../assets/main.css"; 

.nav-item {
  @apply flex items-center px-4 py-3 text-sm font-semibold text-gray-600 rounded-xl transition-all duration-200 hover:bg-gray-100 hover:text-blue-600;
}

.nav-item.active {
  @apply bg-blue-600 text-white shadow-lg shadow-blue-200;
}

.icon {
  @apply mr-3 text-base filter grayscale brightness-150;
}

.nav-item.active .icon {
  @apply grayscale-0 brightness-100;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  @apply bg-gray-200 rounded-full;
}
</style>