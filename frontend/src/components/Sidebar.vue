<template>
  <aside 
    class="h-screen fixed left-0 top-0 z-50 flex flex-col transition-all duration-500 ease-in-out"
    :class="[
      isCollapsed ? 'w-20' : 'w-72',
      'bg-gradient-to-b from-slate-900/95 via-slate-800/95 to-indigo-900/95'
    ]"
  >
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-full blur-3xl animate-pulse-slow"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-full blur-3xl animate-pulse-slower"></div>
    </div>
    
    <!-- Main content with glass effect -->
    <div class="relative flex flex-col h-full backdrop-blur-2xl bg-white/5 border-r border-white/10">
      
      <!-- Decorative top gradient line -->
      <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-blue-500 to-transparent"></div>
      
      <!-- Logo Section with Collapse Toggle -->
      <div class="relative p-5 border-b border-white/10 flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center gap-3 overflow-hidden">
          <div class="relative flex-shrink-0 group">
            <!-- Animated rings -->
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl animate-ping-slow opacity-20"></div>
            <div class="relative w-10 h-10 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 text-white rounded-xl flex items-center justify-center font-black text-xl shadow-2xl">
              SS
            </div>
          </div>
          <h2 
            class="font-black text-2xl transition-all duration-500 whitespace-nowrap"
            :class="isCollapsed ? 'opacity-0 w-0' : 'opacity-100 w-auto'"
          >
            <span class="bg-gradient-to-r from-white via-blue-100 to-purple-100 bg-clip-text text-transparent">ShopSync</span>
          </h2>
        </div>

        <!-- Collapse Toggle Button -->
        <button 
          @click="toggleSidebar"
          class="absolute -right-3 top-1/2 -translate-y-1/2 w-7 h-7 bg-white/10 backdrop-blur-xl rounded-full border border-white/20 flex items-center justify-center hover:bg-white/20 transition-all group shadow-xl hover:scale-110 hover:border-white/40"
          :class="{ 'rotate-180': isCollapsed }"
        >
          <svg class="w-3.5 h-3.5 text-white/70 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
          </svg>
        </button>
      </div>
      
      <!-- Navigation -->
      <nav class="flex-1 mt-6 px-3 space-y-1 overflow-y-auto custom-scrollbar">
        <!-- Main Menu Section -->
        <div v-if="!isCollapsed" class="px-3 mb-3">
          <p class="text-[10px] font-bold text-white/40 uppercase tracking-wider">Main Menu</p>
        </div>
        
        <router-link to="/dashboard" class="nav-item group" active-class="active" :class="{ 'justify-center': isCollapsed }">
          <span class="icon">📊</span>
          <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm">Dashboard</span>
          <span v-if="!isCollapsed" class="badge-modern">New</span>
          <span v-else class="tooltip">Dashboard</span>
        </router-link>

        <router-link to="/pos" class="nav-item group" active-class="active" :class="{ 'justify-center': isCollapsed }">
          <span class="icon">🖥️</span>
          <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm">POS System</span>
          <span v-if="!isCollapsed" class="badge-live-modern">
            <span class="relative flex h-1.5 w-1.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
            </span>
            Live
          </span>
          <span v-else class="tooltip">POS System</span>
        </router-link>

        <router-link to="/products" class="nav-item group" active-class="active" :class="{ 'justify-center': isCollapsed }">
          <span class="icon">🔧</span>
          <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm">Inventory</span>
          <span v-if="!isCollapsed" class="badge-count-modern">3</span>
          <span v-else class="tooltip">Inventory</span>
        </router-link>

        <!-- Operations Section -->
        <div class="mt-8">
          <div v-if="!isCollapsed" class="px-3 mb-3">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-wider">Operations</p>
          </div>
          
          <router-link to="/sales" class="nav-item group" active-class="active" :class="{ 'justify-center': isCollapsed }">
            <span class="icon">💰</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm">Sales History</span>
            <span v-else class="tooltip">Sales History</span>
          </router-link>

          <router-link to="/purchases" class="nav-item group" active-class="active" :class="{ 'justify-center': isCollapsed }">
            <span class="icon">📋</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm">Purchase List</span>
            <span v-else class="tooltip">Purchase List</span>
          </router-link>

          <router-link to="/purchases/create" class="nav-item group" active-class="active" :class="{ 'justify-center': isCollapsed }">
            <span class="icon">📦</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm">New Purchase</span>
            <span v-else class="tooltip">New Purchase</span>
          </router-link>

          <!-- Suppliers Link -->
          <router-link to="/suppliers" class="nav-item group" active-class="active" :class="{ 'justify-center': isCollapsed }">
            <span class="icon">🏭</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm">Suppliers</span>
            <span v-else class="tooltip">Suppliers</span>
          </router-link>

          <!-- Warehouses Link -->
          <router-link to="/warehouses" class="nav-item group" active-class="active" :class="{ 'justify-center': isCollapsed }">
            <span class="icon">🏪</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm">Warehouses</span>
            <span v-else class="tooltip">Warehouses</span>
          </router-link>

          <router-link to="/inventory/transfer" class="nav-item group" active-class="active" :class="{ 'justify-center': isCollapsed }">
            <span class="icon">🚚</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm">Stock Transfer</span>
            <span v-else class="tooltip">Stock Transfer</span>
          </router-link>
        </div>

        <!-- Administration Section (Admin Only) -->
        <div v-if="isAdmin" class="mt-8">
          <div v-if="!isCollapsed" class="px-3 mb-3">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-wider">Administration</p>
          </div>
          
          <router-link to="/users" class="nav-item group" active-class="active" :class="{ 'justify-center': isCollapsed }">
            <span class="icon">👥</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm">Staff Management</span>
            <span v-else class="tooltip">Staff Management</span>
          </router-link>

          <router-link to="/financial" class="nav-item group" active-class="active" :class="{ 'justify-center': isCollapsed }">
            <span class="icon">📈</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm">Financial Reports</span>
            <span v-else class="tooltip">Financial Reports</span>
          </router-link>

          <router-link to="/audit-logs" class="nav-item group" active-class="active" :class="{ 'justify-center': isCollapsed }">
            <span class="icon">📜</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm">System Logs</span>
            <span v-else class="tooltip">System Logs</span>
          </router-link>
        </div>
      </nav>
      
      <!-- User Profile Section -->
      <div class="relative mt-auto border-t border-white/10 bg-gradient-to-t from-black/20 to-transparent backdrop-blur-xl">
        <!-- Collapsed Profile View -->
        <div v-if="isCollapsed" class="p-4 flex justify-center">
          <div class="relative group">
            <div class="relative">
              <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl blur-md opacity-50 group-hover:opacity-75 transition-opacity"></div>
              <div class="relative w-11 h-11 bg-gradient-to-br from-purple-500 via-pink-500 to-orange-500 text-white rounded-xl flex items-center justify-center font-bold text-xl shadow-2xl">
                {{ auth.user?.name?.charAt(0).toUpperCase() || 'U' }}
              </div>
              <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-slate-900"></div>
            </div>
            
            <div class="absolute left-full ml-3 bottom-0 w-48 opacity-0 group-hover:opacity-100 transition-all duration-300 pointer-events-none z-50">
              <div class="relative bg-white/10 backdrop-blur-xl rounded-xl p-3 border border-white/20 shadow-2xl">
                <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-white/10 backdrop-blur-xl rotate-45 border-l border-t border-white/20"></div>
                <p class="text-sm font-bold text-white">{{ auth.user?.name || 'User' }}</p>
                <p class="text-xs text-white/60 mt-0.5">{{ auth.user?.role?.name || 'Guest' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Expanded Profile View -->
        <div v-else class="p-4">
          <div class="flex items-center gap-3">
            <div class="relative group">
              <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl blur-md opacity-50 group-hover:opacity-75 transition-opacity"></div>
              <div class="relative w-12 h-12 bg-gradient-to-br from-purple-500 via-pink-500 to-orange-500 text-white rounded-xl flex items-center justify-center font-bold text-xl shadow-2xl">
                {{ auth.user?.name?.charAt(0).toUpperCase() || 'U' }}
              </div>
              <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-slate-900"></div>
            </div>
            <div class="overflow-hidden">
              <p class="text-sm font-bold text-white truncate">{{ auth.user?.name || 'Loading...' }}</p>
              <p class="text-xs text-white/60 font-medium flex items-center gap-1.5 mt-0.5">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                {{ auth.user?.role?.name || 'Guest' }}
              </p>
            </div>
          </div>
        </div>
        
        <!-- Logout Button -->
        <button @click="handleLogout" class="logout-btn group" :class="{ 'justify-center': isCollapsed }">
          <div class="absolute inset-0 bg-gradient-to-r from-red-500/0 to-red-500/0 group-hover:from-red-500/20 group-hover:to-orange-500/20 transition-all duration-500"></div>
          
          <span class="relative text-lg transform group-hover:scale-110 transition-transform">🚪</span>
          <span v-if="!isCollapsed" class="relative text-sm font-medium">Logout</span>
          <span v-if="!isCollapsed" class="relative right-4 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 translate-x-2 transition-all">→</span>
          <span v-else class="tooltip">Logout</span>
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useSidebarStore } from '@/stores/sidebarStore';

const emit = defineEmits(['toggle']);

const router = useRouter();
const auth = useAuthStore();
const sidebarStore = useSidebarStore();

// Sidebar collapse state
const isCollapsed = computed(() => sidebarStore.isCollapsed);

// Load saved state from localStorage
onMounted(() => {
  sidebarStore.loadState();
});

// Toggle sidebar and save state
const toggleSidebar = () => {
  sidebarStore.toggleSidebar();
};

const isAdmin = computed(() => {
  return auth.user?.role?.name === 'Admin' || auth.user?.role?.name === 'admin';
});

const handleLogout = async () => {
  try {
    await auth.logout();
    router.push('/login');
  } catch (error) {
    console.error('Logout failed:', error);
  }
};
</script>

<style scoped>
@reference "../assets/main.css"; 

/* Navigation item styling - Modern Glass Effect */
.nav-item {
  @apply relative flex items-center px-3 py-2.5 text-sm font-medium text-white/60 rounded-xl transition-all duration-300;
  position: relative;
  overflow: hidden;
}

/* Hover effect with glass morphism */
.nav-item::before {
  content: '';
  @apply absolute inset-0 bg-gradient-to-r from-white/0 via-white/5 to-white/0 opacity-0 transition-opacity duration-500;
  transform: translateX(-100%);
}

.nav-item:hover::before {
  @apply opacity-100;
  transform: translateX(100%);
}

.nav-item:hover {
  @apply text-white bg-white/10;
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Active state with gradient */
.nav-item.active {
  @apply text-white bg-gradient-to-r from-blue-600/30 via-indigo-600/30 to-purple-600/30;
  box-shadow: 0 4px 20px rgba(79, 70, 229, 0.15);
  border-left: 3px solid;
  border-image: linear-gradient(to bottom, #3b82f6, #8b5cf6) 1;
}

.nav-item.active .icon {
  @apply transform scale-110;
  filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.5));
}

/* Icon styling */
.icon {
  @apply text-xl filter drop-shadow-lg transition-all duration-300;
  flex-shrink: 0;
}

/* Modern Badge Styles */
.badge-modern {
  @apply px-1.5 py-0.5 text-[8px] font-bold bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-full ml-2 shadow-lg;
  animation: pulse 2s infinite;
}

.badge-live-modern {
  @apply px-1.5 py-0.5 text-[8px] font-bold bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-full ml-2 flex items-center gap-1 shadow-lg;
}

.badge-count-modern {
  @apply px-1.5 py-0.5 text-[8px] font-bold bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-full ml-2 shadow-lg;
}

/* Modern Tooltip */
.tooltip {
  @apply absolute left-full ml-3 px-3 py-1.5 bg-white/10 backdrop-blur-xl text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 border border-white/20 shadow-2xl;
}

.tooltip::before {
  content: '';
  @apply absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-white/10 backdrop-blur-xl rotate-45 border-l border-t border-white/20;
}

/* Logout button */
.logout-btn {
  @apply w-full relative overflow-hidden text-sm font-medium text-white/60 hover:text-white transition-all duration-300 flex items-center gap-2 py-3 px-4;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.logout-btn:hover {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(239, 68, 68, 0.1) 100%);
}

/* Custom scrollbar */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.2) rgba(255, 255, 255, 0.05);
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #2563eb, #7c3aed);
}

/* Animations */
@keyframes pulse-slow {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.1); }
}

@keyframes pulse-slower {
  0%, 100% { opacity: 0.2; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(1.2); }
}

@keyframes ping-slow {
  75%, 100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

.animate-pulse-slow {
  animation: pulse-slow 8s ease-in-out infinite;
}

.animate-pulse-slower {
  animation: pulse-slower 12s ease-in-out infinite;
}

.animate-ping-slow {
  animation: ping-slow 3s cubic-bezier(0, 0, 0.2, 1) infinite;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 500ms;
}

/* Glass effect optimization */
.backdrop-blur-2xl {
  backdrop-filter: blur(40px);
  -webkit-backdrop-filter: blur(40px);
}

/* Hover effects */
.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}

/* Active link indicator */
.nav-item.active {
  position: relative;
}

.nav-item.active::after {
  content: '';
  @apply absolute right-2 top-1/2 -translate-y-1/2 w-1.5 h-1.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full;
  animation: pulse 2s infinite;
}
</style>