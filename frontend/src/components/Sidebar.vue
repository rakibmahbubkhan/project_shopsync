<template>
  <aside 
    class="h-screen fixed left-0 top-0 z-50 flex flex-col transition-all duration-300 ease-in-out"
    :class="[
      isCollapsed ? 'w-[72px]' : 'w-80',
      'bg-gradient-to-b from-slate-900 via-slate-800 to-indigo-900'
    ]"
  >
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-full blur-3xl animate-pulse-slow"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-full blur-3xl animate-pulse-slower"></div>
    </div>
    
    <!-- Main content with glass effect -->
    <div class="relative flex flex-col h-full backdrop-blur-xl bg-white/5">
      
      <!-- Decorative top gradient line -->
      <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-blue-500/50 to-transparent"></div>
      
      <!-- Logo Section -->
      <div class="relative p-4 border-b border-white/10 flex items-center justify-between min-h-[72px]">
        <!-- Logo -->
        <div class="flex items-center gap-3 overflow-hidden">
          <div class="relative flex-shrink-0 group">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl animate-ping-slow opacity-20"></div>
            <div class="relative w-10 h-10 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 text-white rounded-xl flex items-center justify-center font-black text-lg shadow-2xl">
              SS
            </div>
          </div>
          <div 
            class="transition-all duration-300 overflow-hidden whitespace-nowrap"
            :class="isCollapsed ? 'w-0 opacity-0' : 'w-auto opacity-100'"
          >
            <h2 class="font-bold text-xl">
              <span class="bg-gradient-to-r from-white via-blue-100 to-purple-100 bg-clip-text text-transparent">ShopSync</span>
            </h2>
          </div>
        </div>

        <!-- Collapse Toggle Button -->
        <button 
          @click="toggleSidebar"
          class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-slate-800/90 backdrop-blur-xl rounded-full border border-white/20 flex items-center justify-center hover:bg-slate-700 transition-all group shadow-lg hover:scale-110 hover:border-white/40 z-10"
          :class="{ 'rotate-180': !isCollapsed }"
        >
          <svg class="w-3 h-3 text-white/70 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
          </svg>
        </button>
      </div>
      
      <!-- Navigation -->
      <nav class="flex-1 mt-4 px-3 space-y-1 overflow-y-auto custom-scrollbar">
        <!-- Main Menu Section -->
        <div v-if="!isCollapsed" class="px-2 mb-2">
          <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider">Main Menu</p>
        </div>
        
        <!-- Dashboard -->
        <router-link 
          :to="'/'" 
          class="nav-item group" 
          active-class="active" 
          :class="{ 'justify-center': isCollapsed }"
        >
          <span class="icon text-lg">📊</span>
          <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm font-medium">Dashboard</span>
          <span v-if="!isCollapsed" class="badge-modern from-blue-500 to-indigo-500">New</span>
          <span v-if="isCollapsed" class="tooltip">Dashboard</span>
        </router-link>

        <!-- POS System -->
        <router-link 
          :to="'/pos'" 
          class="nav-item group" 
          active-class="active" 
          :class="{ 'justify-center': isCollapsed }"
        >
          <span class="icon text-lg">🖥️</span>
          <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm font-medium">POS System</span>
          <span v-if="!isCollapsed" class="badge-modern from-green-500 to-emerald-500">Live</span>
          <span v-if="isCollapsed" class="tooltip">POS System</span>
        </router-link>

        <!-- Inventory with Collapsible Submenu -->
        <div class="space-y-1">
          <button 
            @click="toggleInventoryMenu"
            class="nav-item group w-full flex items-center" 
            :class="{ 
              'justify-center': isCollapsed, 
              'bg-white/10 text-white': isInventoryOpen 
            }"
          >
            <span class="icon text-lg">🔧</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm text-left">Inventory</span>
            <span v-if="!isCollapsed && !isInventoryOpen" class="badge-modern from-orange-500 to-red-500">6</span>
            <svg 
              v-if="!isCollapsed" 
              class="w-4 h-4 transition-transform duration-300" 
              :class="{ 'rotate-180': isInventoryOpen }" 
              fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
            <span v-if="isCollapsed" class="tooltip">Inventory</span>
          </button>

          <!-- Submenu Items -->
          <div v-if="!isCollapsed && isInventoryOpen" class="pl-10 space-y-1 mt-1">
            <router-link to="/products" class="sub-nav-item" active-class="active-sub">
              All Products
            </router-link>
            <router-link to="/categories" class="sub-nav-item" active-class="active-sub">
              Categories
            </router-link>
            <router-link to="/brands" class="sub-nav-item" active-class="active-sub">
              Brands
            </router-link>
            <router-link to="/units" class="sub-nav-item" active-class="active-sub">
              Units
            </router-link>
            <router-link to="/taxes" class="sub-nav-item" active-class="active-sub">
              Taxes
            </router-link>
            <router-link to="/variants" class="sub-nav-item" active-class="active-sub">
              Variants
            </router-link>
          </div>
        </div>

        <!-- Customers with Collapsible Submenu -->
        <div class="space-y-1">
          <button 
            @click="toggleCustomersMenu"
            class="nav-item group w-full flex items-center" 
            :class="{ 
              'justify-center': isCollapsed, 
              'bg-white/10 text-white': isCustomersOpen 
            }"
          >
            <span class="icon text-lg">👥</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm text-left">Customers</span>
            <!-- Show badge with pending count if there are pending customers -->
            <span v-if="!isCollapsed && pendingCount > 0" class="badge-modern from-red-500 to-orange-500">{{ pendingCount }}</span>
            <svg 
              v-if="!isCollapsed" 
              class="w-4 h-4 transition-transform duration-300" 
              :class="{ 'rotate-180': isCustomersOpen }" 
              fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
            <span v-if="isCollapsed" class="tooltip">Customers</span>
          </button>

          <!-- Customer Submenu Items -->
          <div v-if="!isCollapsed && isCustomersOpen" class="pl-10 space-y-1 mt-1">
            <router-link to="/customers" class="sub-nav-item" active-class="active-sub">
              Customer List
            </router-link>
            <router-link to="/customers/pending" class="sub-nav-item" active-class="active-sub">
              <div class="flex items-center justify-between">
                <span>Pending Payments</span>
                <span v-if="pendingCount > 0" class="px-1.5 py-0.5 bg-red-500/20 text-red-400 rounded-full text-[10px] font-bold">
                  {{ pendingCount }}
                </span>
              </div>
            </router-link>
          </div>
        </div>

        <!-- Operations Section -->
        <div class="mt-6">
          <div v-if="!isCollapsed" class="px-2 mb-2">
            <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider">Operations</p>
          </div>
          
          <!-- Sales with Collapsible Submenu -->
          <div class="space-y-1">
            <button 
              @click="toggleSalesMenu"
              class="nav-item group w-full flex items-center" 
              :class="{ 
                'justify-center': isCollapsed, 
                'bg-white/10 text-white': isSalesOpen 
              }"
            >
              <span class="icon text-lg">💰</span>
              <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm text-left">Sales</span>
              <svg 
                v-if="!isCollapsed" 
                class="w-4 h-4 transition-transform duration-300" 
                :class="{ 'rotate-180': isSalesOpen }" 
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
              <span v-if="isCollapsed" class="tooltip">Sales</span>
            </button>

            <!-- Sales Submenu Items -->
            <div v-if="!isCollapsed && isSalesOpen" class="pl-10 space-y-1 mt-1">
              <router-link to="/sales" class="sub-nav-item" active-class="active-sub">
                Sales History
              </router-link>
              <router-link to="/sales/returns" class="sub-nav-item" active-class="active-sub">
                <div class="flex items-center justify-between">
                  <span>Sales Returns</span>
                </div>
              </router-link>
            </div>
          </div>

          <!-- Purchases -->
          <router-link 
            :to="'/purchases'" 
            class="nav-item group" 
            active-class="active" 
            :class="{ 'justify-center': isCollapsed }"
          >
            <span class="icon text-lg">📋</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm font-medium">Purchase List</span>
            <span v-if="isCollapsed" class="tooltip">Purchase List</span>
          </router-link>

          <router-link 
            :to="'/purchases/create'" 
            class="nav-item group" 
            active-class="active" 
            :class="{ 'justify-center': isCollapsed }"
          >
            <span class="icon text-lg">📦</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm font-medium">New Purchase</span>
            <span v-if="isCollapsed" class="tooltip">New Purchase</span>
          </router-link>

          <router-link 
            :to="'/suppliers'" 
            class="nav-item group" 
            active-class="active" 
            :class="{ 'justify-center': isCollapsed }"
          >
            <span class="icon text-lg">🏭</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm font-medium">Suppliers</span>
            <span v-if="isCollapsed" class="tooltip">Suppliers</span>
          </router-link>

          <router-link 
            :to="'/warehouses'" 
            class="nav-item group" 
            active-class="active" 
            :class="{ 'justify-center': isCollapsed }"
          >
            <span class="icon text-lg">🏪</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm font-medium">Warehouses</span>
            <span v-if="isCollapsed" class="tooltip">Warehouses</span>
          </router-link>

          <router-link 
            :to="'/inventory/transfer'" 
            class="nav-item group" 
            active-class="active" 
            :class="{ 'justify-center': isCollapsed }"
          >
            <span class="icon text-lg">🚚</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm font-medium">Stock Transfer</span>
            <span v-if="isCollapsed" class="tooltip">Stock Transfer</span>
          </router-link>
        </div>

        <!-- Administration Section (Admin Only) -->
        <div v-if="isAdmin" class="mt-6">
          <div v-if="!isCollapsed" class="px-2 mb-2">
            <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider">Administration</p>
          </div>
          
          <router-link 
            v-for="item in adminItems" 
            :key="item.to"
            :to="item.to" 
            class="nav-item group" 
            active-class="active" 
            :class="{ 'justify-center': isCollapsed }"
          >
            <span class="icon text-lg">{{ item.icon }}</span>
            <span v-if="!isCollapsed" class="flex-1 ml-3 text-sm font-medium">{{ item.label }}</span>
            <span v-if="isCollapsed" class="tooltip">{{ item.label }}</span>
          </router-link>
        </div>
      </nav>
      
      <!-- User Profile Section -->
      <div class="relative mt-auto border-t border-white/10 bg-gradient-to-t from-black/20 to-transparent backdrop-blur-xl">
        <!-- Collapsed Profile View -->
        <div v-if="isCollapsed" class="py-4 flex justify-center">
          <div class="relative group">
            <div class="relative">
              <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl blur-md opacity-50 group-hover:opacity-75 transition-opacity"></div>
              <div class="relative w-10 h-10 bg-gradient-to-br from-purple-500 via-pink-500 to-orange-500 text-white rounded-xl flex items-center justify-center font-bold text-base shadow-2xl cursor-pointer">
                {{ userInitial }}
              </div>
              <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 rounded-full border-2 border-slate-900"></div>
            </div>
            
            <div class="absolute left-full ml-3 bottom-0 w-48 opacity-0 group-hover:opacity-100 transition-all duration-300 pointer-events-none z-50">
              <div class="relative bg-slate-800/90 backdrop-blur-xl rounded-xl p-3 border border-white/20 shadow-2xl">
                <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800/90 rotate-45 border-l border-t border-white/20"></div>
                <p class="text-sm font-bold text-white truncate">{{ userName }}</p>
                <p class="text-xs text-white/60 mt-0.5">{{ userRole }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Expanded Profile View -->
        <div v-else class="p-3">
          <div class="flex items-center gap-3">
            <div class="relative group">
              <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl blur-md opacity-50 group-hover:opacity-75 transition-opacity"></div>
              <div class="relative w-10 h-10 bg-gradient-to-br from-purple-500 via-pink-500 to-orange-500 text-white rounded-xl flex items-center justify-center font-bold text-base shadow-2xl">
                {{ userInitial }}
              </div>
              <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 rounded-full border-2 border-slate-900"></div>
            </div>
            <div class="overflow-hidden flex-1">
              <p class="text-sm font-bold text-white truncate">{{ userName }}</p>
              <p class="text-xs text-white/60 font-medium flex items-center gap-1.5 mt-0.5">
                <span class="relative flex h-1.5 w-1.5">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
                </span>
                {{ userRole }}
              </p>
            </div>
          </div>
        </div>
        
        <!-- Logout Button -->
        <button 
          @click="handleLogout" 
          class="logout-btn group w-full flex items-center gap-2 py-3 px-4 transition-all duration-300"
          :class="{ 'justify-center': isCollapsed }"
        >
          <span class="text-lg transform group-hover:scale-110 transition-transform">🚪</span>
          <span v-if="!isCollapsed" class="text-sm font-medium">Logout</span>
          <span v-if="!isCollapsed" class="ml-auto opacity-0 group-hover:opacity-100 group-hover:translate-x-0 -translate-x-1 transition-all">→</span>
          <span v-if="isCollapsed" class="tooltip">Logout</span>
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useSidebarStore } from '@/stores/sidebarStore';
import api from "@/api/axios";

const emit = defineEmits(['toggle']);

const router = useRouter();
const auth = useAuthStore();
const sidebarStore = useSidebarStore();

// Sidebar collapse state
const isCollapsed = computed(() => sidebarStore.isCollapsed);

// Inventory submenu state
const isInventoryOpen = ref(false);

// Customers submenu state
const isCustomersOpen = ref(false);

// Sales submenu state
const isSalesOpen = ref(false);

// Pending customers count
const pendingCount = ref(0);

// Fetch pending customers count
const fetchPendingCount = async () => {
  try {
    const response = await api.get('/customers/pending-payments/count');
    pendingCount.value = response.data.count || 0;
  } catch (error) {
    console.warn("Could not fetch pending payments count:", error.message);
    pendingCount.value = 0;
  }
};

// Load saved states from localStorage
onMounted(() => {
  sidebarStore.loadState();
  
  // Load inventory menu state
  const savedInventoryState = localStorage.getItem('inventoryMenuOpen');
  if (savedInventoryState !== null) {
    isInventoryOpen.value = JSON.parse(savedInventoryState);
  }
  
  // Load customers menu state
  const savedCustomersState = localStorage.getItem('customersMenuOpen');
  if (savedCustomersState !== null) {
    isCustomersOpen.value = JSON.parse(savedCustomersState);
  }

  // Load sales menu state
  const savedSalesState = localStorage.getItem('salesMenuOpen');
  if (savedSalesState !== null) {
    isSalesOpen.value = JSON.parse(savedSalesState);
  }
  
  // Fetch pending count
  fetchPendingCount();
  
  // Refresh pending count every 30 seconds
  const interval = setInterval(fetchPendingCount, 30000);
  
  // Cleanup interval on component unmount
  return () => clearInterval(interval);
});

// Watch for changes and emit
watch(isCollapsed, (newVal) => {
  emit('toggle', !newVal);
});

// Toggle sidebar and save state
const toggleSidebar = () => {
  sidebarStore.toggleSidebar();
};

// Toggle inventory submenu
const toggleInventoryMenu = () => {
  if (!isCollapsed.value) {
    isInventoryOpen.value = !isInventoryOpen.value;
    localStorage.setItem('inventoryMenuOpen', JSON.stringify(isInventoryOpen.value));
  }
};

// Toggle customers submenu
const toggleCustomersMenu = () => {
  if (!isCollapsed.value) {
    isCustomersOpen.value = !isCustomersOpen.value;
    localStorage.setItem('customersMenuOpen', JSON.stringify(isCustomersOpen.value));
  }
};

// Toggle sales submenu
const toggleSalesMenu = () => {
  if (!isCollapsed.value) {
    isSalesOpen.value = !isSalesOpen.value;
    localStorage.setItem('salesMenuOpen', JSON.stringify(isSalesOpen.value));
  }
};

const isAdmin = computed(() => {
  return auth.user?.role_id === 1 || auth.user?.role === 'admin';
});

const userName = computed(() => auth.user?.name || 'User');
const userRole = computed(() => {
  if (auth.user?.role?.name) return auth.user.role.name;
  if (typeof auth.user?.role === 'string') return auth.user.role;
  return 'Guest';
});
const userInitial = computed(() => userName.value.charAt(0).toUpperCase());

// Menu items configuration
const adminItems = [
  { to: '/users', icon: '👥', label: 'Staff Management' },
  { to: '/financial', icon: '📈', label: 'Financial Reports' },
  { to: '/audit-logs', icon: '📜', label: 'System Logs' }
];

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
/* Navigation item styling - Modern Glass Effect */
.nav-item {
  position: relative;
  display: flex;
  align-items: center;
  padding: 0.625rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.6);
  border-radius: 0.75rem;
  transition: all 0.3s ease;
  overflow: hidden;
}

/* Hover effect with glass morphism */
.nav-item::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(to right, rgba(255,255,255,0), rgba(255,255,255,0.05), rgba(255,255,255,0));
  opacity: 0;
  transition: opacity 0.5s ease;
  transform: translateX(-100%);
}

.nav-item:hover::before {
  opacity: 1;
  transform: translateX(100%);
}

.nav-item:hover {
  color: white;
  background-color: rgba(255, 255, 255, 0.1);
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Active state with gradient */
.nav-item.active {
  color: white;
  background: linear-gradient(to right, rgba(37, 99, 235, 0.3), rgba(79, 70, 229, 0.3), rgba(147, 51, 234, 0.3));
  box-shadow: 0 4px 20px rgba(79, 70, 229, 0.15);
  border-left: 2px solid;
  border-image: linear-gradient(to bottom, #3b82f6, #8b5cf6) 1;
}

.nav-item.active .icon {
  transform: scale(1.1);
  filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.5));
}

/* Submenu item styling */
.sub-nav-item {
  position: relative;
  display: block;
  padding: 0.5rem 0.75rem;
  font-size: 0.813rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.5);
  border-radius: 0.5rem;
  transition: all 0.2s ease;
}

.sub-nav-item:hover {
  color: white;
  background-color: rgba(255, 255, 255, 0.08);
  transform: translateX(4px);
}

.sub-nav-item.active-sub {
  color: white;
  background: linear-gradient(to right, rgba(37, 99, 235, 0.2), rgba(79, 70, 229, 0.2));
  border-left: 2px solid #3b82f6;
  padding-left: calc(0.75rem - 2px);
}

/* Icon styling */
.icon {
  filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
  transition: all 0.3s ease;
  flex-shrink: 0;
}

/* Modern Badge Styles */
.badge-modern {
  padding: 0.125rem 0.375rem;
  font-size: 9px;
  font-weight: bold;
  color: white;
  border-radius: 9999px;
  margin-left: 0.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  animation: pulse 2s infinite;
  background: linear-gradient(to right, var(--tw-gradient-from), var(--tw-gradient-to));
}

/* Modern Tooltip */
.tooltip {
  position: absolute;
  left: 100%;
  margin-left: 0.5rem;
  padding: 0.25rem 0.5rem;
  background-color: rgb(30, 41, 59);
  backdrop-filter: blur(16px);
  color: white;
  font-size: 0.75rem;
  border-radius: 0.375rem;
  opacity: 0;
  transition: all 0.2s ease;
  white-space: nowrap;
  z-index: 50;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  pointer-events: none;
}

.tooltip::before {
  content: '';
  position: absolute;
  left: -0.25rem;
  top: 50%;
  transform: translateY(-50%) rotate(45deg);
  width: 0.375rem;
  height: 0.375rem;
  background-color: rgb(30, 41, 59);
  border-left: 1px solid rgba(255, 255, 255, 0.1);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.group:hover .tooltip {
  opacity: 1;
}

/* Logout button */
.logout-btn {
  position: relative;
  overflow: hidden;
  color: rgba(255, 255, 255, 0.6);
  transition: all 0.3s ease;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.logout-btn:hover {
  color: white;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(239, 68, 68, 0.1) 100%);
}

/* Custom scrollbar */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.2) rgba(255, 255, 255, 0.05);
}

.custom-scrollbar::-webkit-scrollbar {
  width: 3px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 4px;
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

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
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

/* Glass effect optimization */
.backdrop-blur-xl {
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
}

/* Center items when collapsed */
.justify-center {
  justify-content: center;
}
</style>