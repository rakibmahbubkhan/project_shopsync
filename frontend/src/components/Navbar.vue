<template>
  <header class="bg-white/90 backdrop-blur-xl border-b border-gray-200/80 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 shadow-sm z-30 transition-all duration-300">
    <!-- Left Section -->
    <div class="flex items-center gap-3">
      <!-- Mobile Menu Toggle -->
      <button 
        @click="toggleMobileMenu"
        class="md:hidden relative w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition-all hover:scale-110 active:scale-95"
        :class="{ 'bg-gray-200': mobileMenuOpen }"
      >
        <svg 
          class="w-5 h-5 transition-transform duration-300" 
          :class="{ 'rotate-90': mobileMenuOpen }"
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M4 6h16M4 12h16m-7 6h7" 
          />
        </svg>
      </button>

      <!-- Page Title with Breadcrumb -->
      <div v-if="route.path !== '/dashboard'" class="hidden md:block">
  <div class="flex items-center gap-2">
    <h2 class="text-lg font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
      {{ pageTitle }}
    </h2>
    <span v-if="pageBadge" class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-600 rounded-full uppercase tracking-wider">
      {{ pageBadge }}
    </span>
  </div>
  <nav class="flex items-center gap-1.5 text-xs text-gray-500">
    <router-link to="/dashboard" class="hover:text-gray-700 transition-colors">Home</router-link>
    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="font-medium text-gray-700">{{ currentPage }}</span>
  </nav>
</div>
    </div>

    <!-- Right Section -->
    <div class="flex items-center gap-2 sm:gap-3">
      <!-- POS Button - Prominently Displayed -->
      <router-link 
        to="/pos" 
        class="pos-button group"
        active-class="pos-button-active"
      >
        <span class="pos-icon">🖥️</span>
        <span class="pos-text">POS</span>
        <span class="pos-badge">Live</span>
      </router-link>

      <!-- Search Bar -->
      <div class="relative hidden lg:block">
        <input 
          type="text"
          v-model="searchQuery"
          @focus="showSearchResults = true"
          @blur="hideSearchResults"
          placeholder="Search products, sales, customers... (Ctrl+K)"
          class="w-80 pl-10 pr-4 py-2 text-sm bg-gray-100 rounded-xl border border-gray-200 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
        />
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">🔍</span>
        
        <!-- Search Results Dropdown -->
        <div 
          v-if="showSearchResults && searchResults.length > 0"
          class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-50 animate-slide-down"
        >
          <div class="p-2 border-b bg-gray-50">
            <p class="text-xs font-medium text-gray-500">Quick Results</p>
          </div>
          <div 
            v-for="result in searchResults" 
            :key="result.id"
            @mousedown.prevent="navigateToResult(result)"
            class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b last:border-0 transition-colors"
          >
            <div class="flex items-center gap-3">
              <span class="text-lg">{{ result.icon }}</span>
              <div>
                <p class="text-sm font-medium text-gray-800">{{ result.title }}</p>
                <p class="text-xs text-gray-500">{{ result.type }}</p>
              </div>
            </div>
          </div>
          <div class="p-2 text-center border-t">
            <p class="text-xs text-gray-400">Press Enter to see all results</p>
          </div>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="hidden sm:flex items-center gap-1">
        <button 
          @click="quickActions.refresh"
          class="w-9 h-9 rounded-xl hover:bg-gray-100 text-gray-600 transition-all hover:scale-110 relative group"
          title="Refresh data"
        >
          <span class="text-lg transition-transform group-hover:rotate-180 inline-block">↻</span>
        </button>
        
        <button 
          @click="quickActions.fullscreen"
          class="w-9 h-9 rounded-xl hover:bg-gray-100 text-gray-600 transition-all hover:scale-110"
          title="Toggle fullscreen"
        >
          <span class="text-lg">⛶</span>
        </button>
      </div>

      <!-- Notifications -->
      <div class="relative">
        <button 
          @click="toggleNotifications" 
          class="notification-btn relative"
          :class="{ 'bg-blue-50 text-blue-600': showNotifications }"
        >
          <span class="text-lg">🔔</span>
          <span class="notification-badge">{{ unreadCount }}</span>
        </button>
        
        <!-- Notifications Dropdown -->
        <transition name="slide-down">
          <div 
            v-if="showNotifications" 
            v-click-outside="closeNotifications"
            class="absolute top-full right-0 mt-2 w-96 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-50"
          >
            <!-- Header -->
            <div class="p-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white">
              <div class="flex items-center justify-between">
                <h3 class="font-bold">Notifications</h3>
                <span class="text-xs bg-white/20 px-2 py-1 rounded-full">{{ unreadCount }} new</span>
              </div>
            </div>
            
            <!-- List -->
            <div class="max-h-96 overflow-y-auto">
              <div 
                v-for="notif in notifications" 
                :key="notif.id"
                class="notification-item"
                :class="{ 'unread': !notif.read }"
                @click="markAsRead(notif.id)"
              >
                <div class="flex gap-3">
                  <div class="notification-icon" :class="notif.type">
                    {{ notif.icon }}
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800">{{ notif.title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ notif.message }}</p>
                    <p class="text-[10px] text-gray-400 mt-1">{{ notif.time }}</p>
                  </div>
                  <span v-if="!notif.read" class="w-2 h-2 rounded-full bg-blue-500 mt-2"></span>
                </div>
              </div>
              
              <div v-if="notifications.length === 0" class="text-center py-8">
                <span class="text-4xl mb-3 block">🔕</span>
                <p class="text-gray-500 text-sm">No notifications</p>
              </div>
            </div>
            
            <!-- Footer -->
            <div class="p-3 border-t bg-gray-50 flex justify-between items-center">
              <button class="text-xs text-gray-600 hover:text-gray-800 transition-colors">
                Mark all as read
              </button>
              <router-link to="/notifications" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                View all
              </router-link>
            </div>
          </div>
        </transition>
      </div>

      <!-- User Menu -->
      <div class="relative">
        <button 
          @click="toggleUserMenu" 
          class="user-menu-btn group"
          :class="{ 'bg-gray-100': showUserMenu }"
        >
          <div class="relative">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-blue-500/20">
              {{ userInitial }}
            </div>
            <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
          </div>
          
          <div class="text-left hidden md:block">
            <p class="text-xs font-bold text-gray-800 flex items-center gap-1">
              {{ user?.name || 'User' }}
              <span class="px-1.5 py-0.5 text-[8px] font-bold bg-blue-100 text-blue-600 rounded-full">{{ user?.role || 'staff' }}</span>
            </p>
            <p class="text-[10px] text-gray-500">{{ user?.email || 'user@example.com' }}</p>
          </div>
          
          <svg 
            class="w-4 h-4 text-gray-400 transition-transform duration-200" 
            :class="{ 'rotate-180': showUserMenu }"
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- User Dropdown -->
        <transition name="slide-down">
          <div 
            v-if="showUserMenu" 
            v-click-outside="closeUserMenu"
            class="absolute top-full right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-50"
          >
            <!-- User Info -->
            <div class="p-4 bg-gradient-to-r from-gray-50 to-gray-100/50 border-b">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                  {{ userInitial }}
                </div>
                <div>
                  <p class="font-bold text-gray-800">{{ user?.name || 'User' }}</p>
                  <p class="text-xs text-gray-500">{{ user?.email || 'user@example.com' }}</p>
                </div>
              </div>
            </div>

            <!-- Menu Items -->
            <div class="p-2">
              <router-link 
                v-for="item in menuItems" 
                :key="item.to"
                :to="item.to"
                @click="closeUserMenu"
                class="menu-item"
              >
                <span class="text-lg">{{ item.icon }}</span>
                <span class="flex-1 text-sm text-gray-700">{{ item.label }}</span>
                <span v-if="item.badge" class="menu-badge">{{ item.badge }}</span>
              </router-link>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-100 my-1"></div>

            <!-- Logout -->
            <div class="p-2">
              <button @click="handleLogout" class="menu-item text-red-600 hover:bg-red-50 w-full">
                <span class="text-lg">🚪</span>
                <span class="flex-1 text-sm font-medium">Logout</span>
                <span class="text-xs opacity-50">Ctrl+Q</span>
              </button>
            </div>

            <!-- Version -->
            <div class="px-4 py-2 bg-gray-50/50 border-t">
              <p class="text-[10px] text-gray-400 text-center">Version 2.0.1 • ShopSync</p>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

// State
const mobileMenuOpen = ref(false);
const searchQuery = ref('');
const showSearchResults = ref(false);
const showNotifications = ref(false);
const showUserMenu = ref(false);

// Mock data
const user = computed(() => auth.user || {
  name: 'Admin User',
  email: 'admin@shopsync.com',
  role: 'Administrator'
});

const userInitial = computed(() => {
  return user.value?.name?.charAt(0).toUpperCase() || 'U';
});

const pageTitle = computed(() => {
  // Don't show title on dashboard to avoid duplication
  if (route.path === '/dashboard') {
    return ''; // or return null
  }
  
  const titles = {
    '/pos': 'ShopSync ERP | Point of Sale',
    '/products': 'ShopSync ERP | Inventory Management',
    '/sales': 'ShopSync ERP | Sales History',
    '/purchases': 'ShopSync ERP | Purchase Management',
    '/users': 'ShopSync ERP | Staff Management',
    '/financial': 'ShopSync ERP | Financial Reports',
    '/reports': 'ShopSync ERP | Analytics & Reports'
  };
  return titles[route.path] || 'Dashboard';
});

const currentPage = computed(() => {
  // Don't show breadcrumb on dashboard
  if (route.path === '/dashboard') {
    return '';
  }
  return route.path.split('/')[1]?.charAt(0).toUpperCase() + route.path.split('/')[1]?.slice(1) || 'Dashboard';
});

const pageBadge = computed(() => {
  const badges = {
    '/pos': 'LIVE',
    '/products': 'BETA',
    '/financial': 'PRO'
  };
  return badges[route.path];
});

// Menu items
const menuItems = [
  { icon: '👤', to: '/profile', label: 'My Profile' },
  { icon: '⚙️', to: '/settings', label: 'Settings' },
  { icon: '🔔', to: '/notifications', label: 'Notifications', badge: '3' },
  { icon: '📊', to: '/reports', label: 'Analytics' },
  { icon: '❓', to: '/help', label: 'Help & Support' },
];

// Notifications
const notifications = ref([
  { 
    id: 1, 
    icon: '📦', 
    type: 'info',
    title: 'Low Stock Alert', 
    message: 'Engine Oil Filter is running low (5 units left)',
    time: '5 minutes ago',
    read: false 
  },
  { 
    id: 2, 
    icon: '💰', 
    type: 'success',
    title: 'New Sale', 
    message: 'Sale of $1,250.75 completed successfully',
    time: '1 hour ago',
    read: false 
  },
  { 
    id: 3, 
    icon: '🔄', 
    type: 'warning',
    title: 'Stock Transfer', 
    message: 'Transfer #TR-2024-001 is pending approval',
    time: '2 hours ago',
    read: true 
  },
]);

const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read).length;
});

// Search results
const searchResults = ref([
  { id: 1, icon: '🔧', title: 'Engine Oil Filter', type: 'Product' },
  { id: 2, icon: '💰', title: 'Sale #INV-2024-001', type: 'Sale' },
  { id: 3, icon: '👤', title: 'John Smith', type: 'Customer' },
]);

// Quick actions
const quickActions = {
  refresh: () => {
    console.log('Refreshing data...');
    // Implement refresh logic
  },
  fullscreen: () => {
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen();
    } else {
      document.exitFullscreen();
    }
  }
};

// Methods
const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value;
  // Emit event for sidebar
};

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
  showUserMenu.value = false;
};

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value;
  showNotifications.value = false;
};

const closeNotifications = () => {
  showNotifications.value = false;
};

const closeUserMenu = () => {
  showUserMenu.value = false;
};

const markAsRead = (id) => {
  const notif = notifications.value.find(n => n.id === id);
  if (notif) notif.read = true;
};

const hideSearchResults = () => {
  setTimeout(() => {
    showSearchResults.value = false;
  }, 200);
};

const navigateToResult = (result) => {
  router.push(`/${result.type.toLowerCase()}/${result.id}`);
  showSearchResults.value = false;
  searchQuery.value = '';
};

const handleLogout = async () => {
  try {
    await auth.logout();
    router.push('/login');
  } catch (error) {
    console.error('Logout failed:', error);
  }
};

// Keyboard shortcuts
const handleKeyDown = (e) => {
  // Ctrl+K for search focus
  if (e.ctrlKey && e.key === 'k') {
    e.preventDefault();
    document.querySelector('input[type="text"]')?.focus();
  }
  
  // Ctrl+Q for logout
  if (e.ctrlKey && e.key === 'q') {
    e.preventDefault();
    handleLogout();
  }
  
  // Escape to close modals
  if (e.key === 'Escape') {
    showNotifications.value = false;
    showUserMenu.value = false;
    showSearchResults.value = false;
  }
};

// Lifecycle
onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

// Click outside directive
const vClickOutside = {
  mounted: (el, binding) => {
    el._clickOutside = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event);
      }
    };
    document.addEventListener('click', el._clickOutside);
  },
  unmounted: (el) => {
    document.removeEventListener('click', el._clickOutside);
  }
};
</script>

<style scoped>
@reference "../assets/main.css";

/* POS Button Styles */
.pos-button {
  @apply relative flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-medium transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/30 hover:scale-105 active:scale-95 mr-2;
}

.pos-button-active {
  @apply bg-gradient-to-r from-blue-700 to-blue-800 ring-2 ring-blue-300 ring-offset-2;
}

.pos-icon {
  @apply text-lg filter drop-shadow-lg;
}

.pos-text {
  @apply text-sm font-bold hidden sm:inline;
}

.pos-badge {
  @apply absolute -top-2 -right-2 px-1.5 py-0.5 text-[8px] font-bold bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-full border-2 border-white shadow-lg animate-pulse;
}

/* Rest of the styles remain the same */
.notification-btn {
  @apply relative w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition-all hover:scale-110 active:scale-95;
}

.notification-badge {
  @apply absolute -top-1 -right-1 min-w-[20px] h-5 px-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white shadow-lg;
}

.user-menu-btn {
  @apply flex items-center gap-2 p-1.5 pr-3 rounded-xl hover:bg-gray-100 transition-all;
}

.menu-item {
  @apply flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-50 transition-all cursor-pointer;
}

.menu-badge {
  @apply px-1.5 py-0.5 text-[8px] font-bold bg-blue-100 text-blue-600 rounded-full;
}

.notification-item {
  @apply p-4 border-b last:border-0 hover:bg-gray-50 cursor-pointer transition-colors;
}

.notification-item.unread {
  @apply bg-blue-50/30;
}

.notification-icon {
  @apply w-8 h-8 rounded-lg flex items-center justify-center text-sm;
}

.notification-icon.info {
  @apply bg-blue-100 text-blue-600;
}

.notification-icon.success {
  @apply bg-green-100 text-green-600;
}

.notification-icon.warning {
  @apply bg-orange-100 text-orange-600;
}

/* Transitions */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.2s ease-out;
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Custom scrollbar for dropdowns */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f1f5f9;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #cbd5e0;
  border-radius: 4px;
}
</style>