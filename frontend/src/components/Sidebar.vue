<template>
  <!-- Mobile Floating Menu Button (visible only on mobile when sidebar is closed) -->
  <button
    v-if="isMobile && !mobileMenuOpen"
    @click="mobileMenuOpen = true"
    class="sidebar__mobile-menu-btn"
    aria-label="Open menu"
  >
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </button>

  <!-- Sidebar Container -->
  <aside
    class="sidebar"
    :class="{
      'sidebar--desktop-collapsed': !isMobile && isCollapsed,
      'sidebar--desktop-expanded': !isMobile && !isCollapsed,
      'sidebar--mobile-open': isMobile && mobileMenuOpen,
      'sidebar--mobile-closed': isMobile && !mobileMenuOpen
    }"
  >
    <!-- Animated Orb Background (only desktop subtle) -->
    <div class="sidebar__bg">
      <div class="sidebar__orb sidebar__orb--1"></div>
      <div class="sidebar__orb sidebar__orb--2"></div>
      <div class="sidebar__orb sidebar__orb--3"></div>
    </div>

    <!-- Glass Panel / Main Content -->
    <div class="sidebar__panel">
      <!-- Header with Logo & Close (Mobile) / Toggle (Desktop) -->
      <div class="sidebar__header">
        <div class="sidebar__logo-wrapper">
          <div class="sidebar__logo-icon">
            <span class="sidebar__logo-text">SS</span>
          </div>
          <transition name="fade-slide">
            <h2 v-if="!isMobile && !isCollapsed" class="sidebar__logo-title">
              <span>ShopSync</span>
            </h2>
          </transition>
        </div>

        <!-- Desktop toggle button -->
        <button
          v-if="!isMobile"
          @click="toggleSidebar"
          class="sidebar__toggle"
          :class="{ 'sidebar__toggle--rotated': !isCollapsed }"
        >
          <svg class="sidebar__toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 19l-7-7 7-7m8 14l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <!-- Mobile close button -->
        <button
          v-if="isMobile"
          @click="mobileMenuOpen = false"
          class="sidebar__mobile-close"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>

      <!-- Navigation (same content, but class adjustments) -->
      <nav class="sidebar__nav">
        <!-- Main Menu Section -->
        <div v-if="!isMobile && !isCollapsed" class="sidebar__section-title">
          <span>Main Menu</span>
        </div>

        <!-- Dashboard -->
        <router-link
          to="/"
          class="sidebar__link"
          active-class="sidebar__link--active"
          :class="{ 'sidebar__link--collapsed': !isMobile && isCollapsed }"
          @click="closeMobileIfNeeded"
        >
          <div class="sidebar__icon">📊</div>
          <span v-if="!isMobile && !isCollapsed" class="sidebar__label">Dashboard</span>
          <span v-if="!isMobile && !isCollapsed" class="sidebar__badge sidebar__badge--primary">New</span>
          <span v-if="!isMobile && isCollapsed" class="sidebar__tooltip">Dashboard</span>
        </router-link>

        <!-- POS System -->
        <router-link
          to="/pos"
          class="sidebar__link"
          active-class="sidebar__link--active"
          :class="{ 'sidebar__link--collapsed': !isMobile && isCollapsed }"
          @click="closeMobileIfNeeded"
        >
          <div class="sidebar__icon">🖥️</div>
          <span v-if="!isMobile && !isCollapsed" class="sidebar__label">POS System</span>
          <span v-if="!isMobile && !isCollapsed" class="sidebar__badge sidebar__badge--success">Live</span>
          <span v-if="!isMobile && isCollapsed" class="sidebar__tooltip">POS System</span>
        </router-link>

        <!-- Inventory Section -->
        <div class="sidebar__group">
          <button
            @click="toggleInventoryMenu"
            class="sidebar__group-btn"
            :class="{
              'sidebar__group-btn--active': isInventoryOpen,
              'sidebar__group-btn--collapsed': !isMobile && isCollapsed
            }"
          >
            <div class="sidebar__icon">🔧</div>
            <span v-if="!isMobile && !isCollapsed" class="sidebar__label">Inventory</span>
            <span v-if="!isMobile && !isCollapsed && !isInventoryOpen" class="sidebar__badge sidebar__badge--warning">6</span>
            <svg v-if="!isMobile && !isCollapsed" class="sidebar__chevron" :class="{ 'sidebar__chevron--open': isInventoryOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span v-if="!isMobile && isCollapsed" class="sidebar__tooltip">Inventory</span>
          </button>

          <transition name="submenu">
            <div v-if="!isMobile && !isCollapsed && isInventoryOpen" class="sidebar__submenu">
              <router-link to="/products" class="sidebar__sub-link" @click="closeMobileIfNeeded">All Products</router-link>
              <router-link to="/categories" class="sidebar__sub-link" @click="closeMobileIfNeeded">Categories</router-link>
              <router-link to="/brands" class="sidebar__sub-link" @click="closeMobileIfNeeded">Brands</router-link>
              <router-link to="/units" class="sidebar__sub-link" @click="closeMobileIfNeeded">Units</router-link>
              <router-link to="/taxes" class="sidebar__sub-link" @click="closeMobileIfNeeded">Taxes</router-link>
              <router-link to="/variants" class="sidebar__sub-link" @click="closeMobileIfNeeded">Variants</router-link>
            </div>
          </transition>
          <!-- Mobile submenu (always expanded style) -->
          <div v-if="isMobile && isInventoryOpen" class="sidebar__submenu sidebar__submenu--mobile">
            <router-link to="/products" class="sidebar__sub-link" @click="closeMobileIfNeeded">All Products</router-link>
            <router-link to="/categories" class="sidebar__sub-link" @click="closeMobileIfNeeded">Categories</router-link>
            <router-link to="/brands" class="sidebar__sub-link" @click="closeMobileIfNeeded">Brands</router-link>
            <router-link to="/units" class="sidebar__sub-link" @click="closeMobileIfNeeded">Units</router-link>
            <router-link to="/taxes" class="sidebar__sub-link" @click="closeMobileIfNeeded">Taxes</router-link>
            <router-link to="/variants" class="sidebar__sub-link" @click="closeMobileIfNeeded">Variants</router-link>
          </div>
        </div>

        <!-- Customers Section -->
        <div class="sidebar__group">
          <button
            @click="toggleCustomersMenu"
            class="sidebar__group-btn"
            :class="{
              'sidebar__group-btn--active': isCustomersOpen,
              'sidebar__group-btn--collapsed': !isMobile && isCollapsed
            }"
          >
            <div class="sidebar__icon">👥</div>
            <span v-if="!isMobile && !isCollapsed" class="sidebar__label">Customers</span>
            <span v-if="!isMobile && !isCollapsed && pendingCount > 0" class="sidebar__badge sidebar__badge--danger">{{ pendingCount }}</span>
            <svg v-if="!isMobile && !isCollapsed" class="sidebar__chevron" :class="{ 'sidebar__chevron--open': isCustomersOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span v-if="!isMobile && isCollapsed" class="sidebar__tooltip">Customers</span>
          </button>

          <transition name="submenu">
            <div v-if="!isMobile && !isCollapsed && isCustomersOpen" class="sidebar__submenu">
              <router-link to="/customers" class="sidebar__sub-link" @click="closeMobileIfNeeded">Customer List</router-link>
              <router-link to="/customers/pending" class="sidebar__sub-link" @click="closeMobileIfNeeded">
                <div class="sidebar__sub-label">
                  <span>Pending Payments</span>
                  <span v-if="pendingCount > 0" class="sidebar__sub-badge">{{ pendingCount }}</span>
                </div>
              </router-link>
            </div>
          </transition>
          <div v-if="isMobile && isCustomersOpen" class="sidebar__submenu sidebar__submenu--mobile">
            <router-link to="/customers" class="sidebar__sub-link" @click="closeMobileIfNeeded">Customer List</router-link>
            <router-link to="/customers/pending" class="sidebar__sub-link" @click="closeMobileIfNeeded">
              <div class="sidebar__sub-label">
                <span>Pending Payments</span>
                <span v-if="pendingCount > 0" class="sidebar__sub-badge">{{ pendingCount }}</span>
              </div>
            </router-link>
          </div>
        </div>

        <!-- Operations Section -->
        <div v-if="!isMobile && !isCollapsed" class="sidebar__section-title sidebar__section-title--mt">
          <span>Operations</span>
        </div>

        <!-- Sales Section -->
        <div class="sidebar__group">
          <button
            @click="toggleSalesMenu"
            class="sidebar__group-btn"
            :class="{
              'sidebar__group-btn--active': isSalesOpen,
              'sidebar__group-btn--collapsed': !isMobile && isCollapsed
            }"
          >
            <div class="sidebar__icon">💰</div>
            <span v-if="!isMobile && !isCollapsed" class="sidebar__label">Sales</span>
            <svg v-if="!isMobile && !isCollapsed" class="sidebar__chevron" :class="{ 'sidebar__chevron--open': isSalesOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span v-if="!isMobile && isCollapsed" class="sidebar__tooltip">Sales</span>
          </button>

          <transition name="submenu">
            <div v-if="!isMobile && !isCollapsed && isSalesOpen" class="sidebar__submenu">
              <router-link to="/sales" class="sidebar__sub-link" @click="closeMobileIfNeeded">Sales History</router-link>
              <router-link to="/sales/returns" class="sidebar__sub-link" @click="closeMobileIfNeeded">Sales Returns</router-link>
            </div>
          </transition>
          <div v-if="isMobile && isSalesOpen" class="sidebar__submenu sidebar__submenu--mobile">
            <router-link to="/sales" class="sidebar__sub-link" @click="closeMobileIfNeeded">Sales History</router-link>
            <router-link to="/sales/returns" class="sidebar__sub-link" @click="closeMobileIfNeeded">Sales Returns</router-link>
          </div>
        </div>

        <!-- Purchases Section -->
        <div class="sidebar__group">
          <button
            @click="togglePurchasesMenu"
            class="sidebar__group-btn"
            :class="{
              'sidebar__group-btn--active': isPurchasesOpen,
              'sidebar__group-btn--collapsed': !isMobile && isCollapsed
            }"
          >
            <div class="sidebar__icon">📋</div>
            <span v-if="!isMobile && !isCollapsed" class="sidebar__label">Purchases</span>
            <svg v-if="!isMobile && !isCollapsed" class="sidebar__chevron" :class="{ 'sidebar__chevron--open': isPurchasesOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span v-if="!isMobile && isCollapsed" class="sidebar__tooltip">Purchases</span>
          </button>

          <transition name="submenu">
            <div v-if="!isMobile && !isCollapsed && isPurchasesOpen" class="sidebar__submenu">
              <router-link to="/purchases" class="sidebar__sub-link" @click="closeMobileIfNeeded">Purchase List</router-link>
              <router-link to="/purchases/create" class="sidebar__sub-link" @click="closeMobileIfNeeded">New Purchase</router-link>
              <router-link to="/purchases/returns" class="sidebar__sub-link" @click="closeMobileIfNeeded">Purchase Returns</router-link>
            </div>
          </transition>
          <div v-if="isMobile && isPurchasesOpen" class="sidebar__submenu sidebar__submenu--mobile">
            <router-link to="/purchases" class="sidebar__sub-link" @click="closeMobileIfNeeded">Purchase List</router-link>
            <router-link to="/purchases/create" class="sidebar__sub-link" @click="closeMobileIfNeeded">New Purchase</router-link>
            <router-link to="/purchases/returns" class="sidebar__sub-link" @click="closeMobileIfNeeded">Purchase Returns</router-link>
          </div>
        </div>

        <!-- Standard Links -->
        <router-link
          to="/suppliers"
          class="sidebar__link"
          active-class="sidebar__link--active"
          :class="{ 'sidebar__link--collapsed': !isMobile && isCollapsed }"
          @click="closeMobileIfNeeded"
        >
          <div class="sidebar__icon">🏭</div>
          <span v-if="!isMobile && !isCollapsed" class="sidebar__label">Suppliers</span>
          <span v-if="!isMobile && isCollapsed" class="sidebar__tooltip">Suppliers</span>
        </router-link>

        <router-link
          to="/warehouses"
          class="sidebar__link"
          active-class="sidebar__link--active"
          :class="{ 'sidebar__link--collapsed': !isMobile && isCollapsed }"
          @click="closeMobileIfNeeded"
        >
          <div class="sidebar__icon">🏪</div>
          <span v-if="!isMobile && !isCollapsed" class="sidebar__label">Warehouses</span>
          <span v-if="!isMobile && isCollapsed" class="sidebar__tooltip">Warehouses</span>
        </router-link>

        <router-link
          to="/inventory/transfer"
          class="sidebar__link"
          active-class="sidebar__link--active"
          :class="{ 'sidebar__link--collapsed': !isMobile && isCollapsed }"
          @click="closeMobileIfNeeded"
        >
          <div class="sidebar__icon">🚚</div>
          <span v-if="!isMobile && !isCollapsed" class="sidebar__label">Stock Transfer</span>
          <span v-if="!isMobile && isCollapsed" class="sidebar__tooltip">Stock Transfer</span>
        </router-link>

        <router-link
          to="/products/damaged"
          class="sidebar__link"
          active-class="sidebar__link--active"
          :class="{ 'sidebar__link--collapsed': !isMobile && isCollapsed }"
          @click="closeMobileIfNeeded"
        >
          <div class="sidebar__icon">⚠️</div>
          <span v-if="!isMobile && !isCollapsed" class="sidebar__label">Damage Stock</span>
          <span v-if="!isMobile && isCollapsed" class="sidebar__tooltip">Damage Stock</span>
        </router-link>

        <!-- Expenses Section -->
        <div class="sidebar__group">
          <button
            @click="toggleExpensesMenu"
            class="sidebar__group-btn"
            :class="{
              'sidebar__group-btn--active': isExpensesOpen,
              'sidebar__group-btn--collapsed': !isMobile && isCollapsed
            }"
          >
            <div class="sidebar__icon">📉</div>
            <span v-if="!isMobile && !isCollapsed" class="sidebar__label">Expenses</span>
            <svg v-if="!isMobile && !isCollapsed" class="sidebar__chevron" :class="{ 'sidebar__chevron--open': isExpensesOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span v-if="!isMobile && isCollapsed" class="sidebar__tooltip">Expenses</span>
          </button>

          <transition name="submenu">
            <div v-if="!isMobile && !isCollapsed && isExpensesOpen" class="sidebar__submenu">
              <router-link to="/expenses" class="sidebar__sub-link" @click="closeMobileIfNeeded">Expense List</router-link>
              <router-link to="/expenses/create" class="sidebar__sub-link" @click="closeMobileIfNeeded">New Expense</router-link>
              <router-link to="/expenses/categories" class="sidebar__sub-link" @click="closeMobileIfNeeded">Expense Categories</router-link>
            </div>
          </transition>
          <div v-if="isMobile && isExpensesOpen" class="sidebar__submenu sidebar__submenu--mobile">
            <router-link to="/expenses" class="sidebar__sub-link" @click="closeMobileIfNeeded">Expense List</router-link>
            <router-link to="/expenses/create" class="sidebar__sub-link" @click="closeMobileIfNeeded">New Expense</router-link>
            <router-link to="/expenses/categories" class="sidebar__sub-link" @click="closeMobileIfNeeded">Expense Categories</router-link>
          </div>
        </div>

        <!-- Administration Section (Admin Only) -->
        <div v-if="isAdmin">
          <div v-if="!isMobile && !isCollapsed" class="sidebar__section-title sidebar__section-title--mt">
            <span>Administration</span>
          </div>
          <router-link
            v-for="item in adminItems"
            :key="item.to"
            :to="item.to"
            class="sidebar__link"
            active-class="sidebar__link--active"
            :class="{ 'sidebar__link--collapsed': !isMobile && isCollapsed }"
            @click="closeMobileIfNeeded"
          >
            <div class="sidebar__icon">{{ item.icon }}</div>
            <span v-if="!isMobile && !isCollapsed" class="sidebar__label">{{ item.label }}</span>
            <span v-if="!isMobile && isCollapsed" class="sidebar__tooltip">{{ item.label }}</span>
          </router-link>
        </div>
      </nav>

      <!-- User Section -->
      <div class="sidebar__footer">
        <div class="sidebar__user">
          <div class="sidebar__avatar">
            <span class="sidebar__avatar-initial">{{ userInitial }}</span>
            <span class="sidebar__status"></span>
          </div>
          <transition name="fade-slide">
            <div v-if="!isMobile && !isCollapsed" class="sidebar__user-info">
              <p class="sidebar__user-name">{{ userName }}</p>
              <p class="sidebar__user-role">
                <span class="sidebar__user-status-dot"></span>
                {{ userRole }}
              </p>
            </div>
          </transition>
        </div>

        <button
          @click="handleLogout"
          class="sidebar__logout"
          :class="{ 'sidebar__logout--collapsed': !isMobile && isCollapsed }"
        >
          <div class="sidebar__icon">🚪</div>
          <span v-if="!isMobile && !isCollapsed" class="sidebar__label">Logout</span>
          <span v-if="!isMobile && isCollapsed" class="sidebar__tooltip">Logout</span>
        </button>
      </div>
    </div>

    <!-- Mobile Backdrop -->
    <transition name="fade">
      <div v-if="isMobile && mobileMenuOpen" class="sidebar__mobile-backdrop" @click="mobileMenuOpen = false"></div>
    </transition>
  </aside>
</template>

<script setup>
import { computed, onMounted, ref, watch, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useSidebarStore } from '@/stores/sidebarStore';
import api from "@/api/axios";

const emit = defineEmits(['toggle']);
const router = useRouter();
const auth = useAuthStore();
const sidebarStore = useSidebarStore();

// Sidebar state (desktop)
const isCollapsed = computed(() => sidebarStore.isCollapsed);

// Menu states
const isInventoryOpen = ref(false);
const isCustomersOpen = ref(false);
const isSalesOpen = ref(false);
const isPurchasesOpen = ref(false);
const isExpensesOpen = ref(false);
const pendingCount = ref(0);

// Responsive
const isMobile = ref(window.innerWidth < 768);
const mobileMenuOpen = ref(false);

// Close mobile sidebar on navigation
const closeMobileIfNeeded = () => {
  if (isMobile.value) {
    mobileMenuOpen.value = false;
  }
};

// Handle window resize
const checkMobile = () => {
  isMobile.value = window.innerWidth < 768;
  if (!isMobile.value) {
    mobileMenuOpen.value = false; // reset mobile overlay when switching to desktop
  }
};

// Load saved states
onMounted(() => {
  sidebarStore.loadState();
  window.addEventListener('resize', checkMobile);

  const savedStates = {
    inventoryMenuOpen: isInventoryOpen,
    customersMenuOpen: isCustomersOpen,
    salesMenuOpen: isSalesOpen,
    purchasesMenuOpen: isPurchasesOpen,
    expensesMenuOpen: isExpensesOpen
  };
  Object.entries(savedStates).forEach(([key, refState]) => {
    const saved = localStorage.getItem(key);
    if (saved !== null) refState.value = JSON.parse(saved);
  });

  fetchPendingCount();
  const interval = setInterval(fetchPendingCount, 30000);
  onUnmounted(() => {
    clearInterval(interval);
    window.removeEventListener('resize', checkMobile);
  });
});

const fetchPendingCount = async () => {
  try {
    const response = await api.get('/customers/pending-payments/count');
    pendingCount.value = response.data.count || 0;
  } catch (error) {
    console.warn("Could not fetch pending payments count:", error.message);
    pendingCount.value = 0;
  }
};

// Toggle functions with persistence
const toggleSidebar = () => sidebarStore.toggleSidebar();
const toggleInventoryMenu = () => !isCollapsed.value && toggleWithStorage('inventoryMenuOpen', isInventoryOpen);
const toggleCustomersMenu = () => !isCollapsed.value && toggleWithStorage('customersMenuOpen', isCustomersOpen);
const toggleSalesMenu = () => !isCollapsed.value && toggleWithStorage('salesMenuOpen', isSalesOpen);
const togglePurchasesMenu = () => !isCollapsed.value && toggleWithStorage('purchasesMenuOpen', isPurchasesOpen);
const toggleExpensesMenu = () => !isCollapsed.value && toggleWithStorage('expensesMenuOpen', isExpensesOpen);

const toggleWithStorage = (key, stateRef) => {
  stateRef.value = !stateRef.value;
  localStorage.setItem(key, JSON.stringify(stateRef.value));
};

// Watch for collapse changes to close submenus when collapsed (desktop)
watch(isCollapsed, (collapsed) => {
  if (!isMobile.value && collapsed) {
    isInventoryOpen.value = false;
    isCustomersOpen.value = false;
    isSalesOpen.value = false;
    isPurchasesOpen.value = false;
    isExpensesOpen.value = false;
  }
});

// Auth computed properties
const isAdmin = computed(() => auth.user?.role_id === 1 || auth.user?.role === 'admin');
const userName = computed(() => auth.user?.name || 'User');
const userRole = computed(() => {
  if (auth.user?.role?.name) return auth.user.role.name;
  if (typeof auth.user?.role === 'string') return auth.user.role;
  return 'Guest';
});
const userInitial = computed(() => userName.value.charAt(0).toUpperCase());

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
/* ===== CSS Variables ===== */
:root {
  --sidebar-width-expanded: 280px;
  --sidebar-width-collapsed: 80px;
  --black-bg: #471b7e;
  --glass-bg: rgba(18, 3, 83, 0.75);
  --glass-border: rgba(255, 255, 255, 0.08);
  --accent-gradient: linear-gradient(135deg, #3b82f6, #8b5cf6, #d946ef);
  --text-dim: rgb(255, 255, 255);
  --text-bright: rgba(255, 255, 255, 0.95);
  --transition-base: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ===== Base Sidebar ===== */
.sidebar {
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  z-index: 1000;
  transition: var(--transition-base);
}

/* Desktop Expanded */
.sidebar--desktop-expanded {
  width: var(--sidebar-width-expanded);
}

/* Desktop Collapsed */
.sidebar--desktop-collapsed {
  width: var(--sidebar-width-collapsed);
}

/* Mobile Closed (hidden) */
.sidebar--mobile-closed {
  width: 0;
  visibility: hidden;
}

/* Mobile Open (overlay) */
.sidebar--mobile-open {
  width: 280px;
  visibility: visible;
}

/* Mobile Floating Menu Button */
.sidebar__mobile-menu-btn {
  position: fixed;
  top: 1rem;
  left: 1rem;
  z-index: 1001;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 12px;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.sidebar__mobile-menu-btn:hover {
  background: rgba(30, 30, 40, 0.9);
  transform: scale(1.02);
}

/* Mobile Backdrop */
.sidebar__mobile-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 999;
}

/* Animated Background Orbs (subtle) */
.sidebar__bg {
  position: absolute;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
}

.sidebar__orb {
  position: absolute;
  width: 300px;
  height: 300px;
  border-radius: 50%;
  filter: blur(100px);
  opacity: 0.25;
  animation: float 20s infinite alternate;
}

.sidebar__orb--1 {
  background: radial-gradient(circle, #3b82f6, transparent);
  top: -100px;
  right: -100px;
  animation-duration: 25s;
}

.sidebar__orb--2 {
  background: radial-gradient(circle, #8b5cf6, transparent);
  bottom: -120px;
  left: -80px;
  animation-duration: 30s;
}

.sidebar__orb--3 {
  background: radial-gradient(circle, #d946ef, transparent);
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 400px;
  height: 400px;
  animation: pulseGlow 12s infinite alternate;
  opacity: 0.15;
}

/* Glass Panel - Solid Black + subtle glass */
.sidebar__panel {
  position: relative;
  height: 100%;
  background: var(--black-bg);
  background: linear-gradient(145deg, #040135 0%, #180133 100%);
  border-right: 1px solid var(--glass-border);
  display: flex;
  flex-direction: column;
  transition: var(--transition-base);
  backdrop-filter: blur(2px);
}

/* Header Section */
.sidebar__header {
  position: relative;
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--glass-border);
  min-height: 72px;
  background: rgba(255, 255, 255, 0.4);
}

.sidebar__logo-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  overflow: hidden;
}

.sidebar__logo-icon {
  position: relative;
  width: 40px;
  height: 40px;
  background: var(--accent-gradient);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
  transition: var(--transition-base);
}

.sidebar__logo-text {
  font-weight: 800;
  font-size: 1.1rem;
  color: white;
  letter-spacing: -0.5px;
}

.sidebar__logo-title {
  font-size: 1.25rem;
  font-weight: 700;
  background: linear-gradient(135deg, #fff, #c4b5fd);
  background-clip: text;
  -webkit-background-clip: text;
  color: transparent;
  white-space: nowrap;
}

/* Desktop Toggle Button */
.sidebar__toggle {
  position: absolute;
  right: -12px;
  top: 50%;
  transform: translateY(-50%);
  width: 24px;
  height: 24px;
  background: #1a1a1a;
  backdrop-filter: blur(4px);
  border: 1px solid var(--glass-border);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition-base);
  z-index: 10;
}

.sidebar__toggle:hover {
  background: #2a2a2a;
  transform: translateY(-50%) scale(1.1);
  border-color: rgba(255,255,255,0.3);
}

.sidebar__toggle-icon {
  width: 12px;
  height: 12px;
  color: rgba(255,255,255,0.7);
  transition: transform 0.3s;
}

.sidebar__toggle--rotated .sidebar__toggle-icon {
  transform: rotate(180deg);
}

/* Mobile Close Button */
.sidebar__mobile-close {
  background: rgba(255,255,255,0.08);
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
}

.sidebar__mobile-close:hover {
  background: rgba(255,255,255,0.15);
}

/* Navigation */
.sidebar__nav {
  flex: 1;
  padding: 1.25rem 0.75rem;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #3b82f6 #1e1e1e;
}

.sidebar__nav::-webkit-scrollbar {
  width: 4px;
}

.sidebar__nav::-webkit-scrollbar-track {
  background: #1e1e1e;
  border-radius: 4px;
}

.sidebar__nav::-webkit-scrollbar-thumb {
  background: #3b82f6;
  border-radius: 4px;
}

/* Section Titles */
.sidebar__section-title {
  padding: 0 0.75rem;
  margin-bottom: 0.75rem;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: rgba(255,255,255,0.4);
}

.sidebar__section-title--mt {
  margin-top: 1.5rem;
}

/* Navigation Links */
.sidebar__link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 0.875rem;
  margin-bottom: 0.25rem;
  border-radius: 14px;
  color: var(--text-dim);
  transition: var(--transition-base);
  position: relative;
  overflow: hidden;
  text-decoration: none;
}

.sidebar__link--collapsed {
  justify-content: center;
  padding: 0.625rem;
}

.sidebar__link:hover {
  background: rgba(255, 255, 255, 0.06);
  color: var(--text-bright);
  transform: translateX(4px);
}

.sidebar__link--active {
  background: linear-gradient(135deg, rgba(59,130,246,0.15), rgba(139,92,246,0.15));
  color: white;
  border-left: 2px solid #3b82f6;
}

.sidebar__link--active .sidebar__icon {
  filter: drop-shadow(0 0 6px #3b82f6);
}

/* Group Buttons (collapsible menus) */
.sidebar__group {
  margin-bottom: 0.25rem;
}

.sidebar__group-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 0.875rem;
  width: 100%;
  border-radius: 14px;
  color: var(--text-dim);
  transition: var(--transition-base);
  background: transparent;
  border: none;
  cursor: pointer;
  font-size: inherit;
}

.sidebar__group-btn--collapsed {
  justify-content: center;
  padding: 0.625rem;
}

.sidebar__group-btn:hover {
  background: rgb(255 254 254 / 54%); 
  color: var(--text-bright);
  transform: translateX(4px);
}

.sidebar__group-btn--active {
  background: rgba(255, 255, 255, 0.08);
  color: white;
}

.sidebar__icon {
  font-size: 1.25rem;
  flex-shrink: 0;
  transition: transform 0.2s;
}

.sidebar__label {
  flex: 1;
  text-align: left;
  font-size: 0.875rem;
  font-weight: 500;
  color: white;
}

.sidebar__chevron {
  width: 14px;
  height: 14px;
  transition: transform 0.3s;
}

.sidebar__chevron--open {
  transform: rotate(180deg);
}

/* Badges */
.sidebar__badge {
  padding: 0.125rem 0.375rem;
  font-size: 0.65rem;
  font-weight: 700;
  border-radius: 20px;
  background: rgba(255,255,255,0.1);
  color: white;
}

.sidebar__badge--primary { background: linear-gradient(135deg, #2563eb, #7c3aed); }
.sidebar__badge--success { background: linear-gradient(135deg, #059669, #10b981); }
.sidebar__badge--warning { background: linear-gradient(135deg, #ea580c, #f97316); }
.sidebar__badge--danger { background: linear-gradient(135deg, #dc2626, #ef4444); }

/* Submenu */
.sidebar__submenu {
  margin-left: 2.5rem;
  margin-top: 0.25rem;
  margin-bottom: 0.25rem;
  border-left: 1px dashed rgba(255,255,255,0.15);
  padding-left: 0.75rem;
}

.sidebar__submenu--mobile {
  margin-left: 2rem;
  border-left-color: rgba(255,255,255,0.2);
}

.sidebar__sub-link {
  display: block;
  padding: 0.5rem 0.75rem;
  font-size: 0.8rem;
  color: rgba(255,255,255,0.6);
  border-radius: 10px;
  transition: var(--transition-base);
  text-decoration: none;
}

.sidebar__sub-link:hover {
  color: white;
  background: rgba(255,255,255,0.05);
  transform: translateX(4px);
}

.sidebar__sub-link--active {
  color: white;
  background: linear-gradient(135deg, rgba(59,130,246,0.15), rgba(139,92,246,0.15));
  border-left: 2px solid #3b82f6;
  padding-left: calc(0.75rem - 2px);
}

.sidebar__sub-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.sidebar__sub-badge {
  background: #ef4444;
  padding: 0.125rem 0.375rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: bold;
}

/* Tooltips for desktop collapsed mode */
.sidebar__tooltip {
  position: absolute;
  left: 100%;
  margin-left: 0.75rem;
  background: #1a1a1a;
  backdrop-filter: blur(8px);
  padding: 0.25rem 0.75rem;
  border-radius: 8px;
  font-size: 0.75rem;
  white-space: nowrap;
  color: white;
  border: 1px solid rgba(255,255,255,0.1);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
  z-index: 100;
}

.sidebar__link--collapsed:hover .sidebar__tooltip,
.sidebar__group-btn--collapsed:hover .sidebar__tooltip {
  opacity: 1;
}

/* Footer Section */
.sidebar__footer {
  margin-top: auto;
  border-top: 1px solid var(--glass-border);
  padding: 1rem;
  background: rgba(0, 0, 0, 0.5);
}

.sidebar__user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.sidebar__avatar {
  position: relative;
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #8b5cf6, #ec4899);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: white;
  box-shadow: 0 4px 12px rgba(139,92,246,0.3);
}

.sidebar__status {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 10px;
  height: 10px;
  background: #10b981;
  border-radius: 50%;
  border: 2px solid #000;
}

.sidebar__user-info {
  overflow: hidden;
}

.sidebar__user-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: white;
  white-space: nowrap;
}

.sidebar__user-role {
  font-size: 0.7rem;
  color: rgba(255,255,255,0.6);
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.sidebar__user-status-dot {
  width: 6px;
  height: 6px;
  background: #10b981;
  border-radius: 50%;
  display: inline-block;
  animation: pulse 1.5s infinite;
}

/* Logout Button */
.sidebar__logout {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.625rem;
  border-radius: 14px;
  background: rgba(239, 68, 68, 0.08);
  border: none;
  color: rgba(255,255,255,0.7);
  cursor: pointer;
  transition: var(--transition-base);
}

.sidebar__logout:hover {
  background: rgba(239, 68, 68, 0.2);
  color: white;
  transform: translateX(4px);
}

.sidebar__logout--collapsed {
  justify-content: center;
}

/* Transitions */
.fade-slide-enter-active,
.fade-slide-leave-active,
.submenu-enter-active,
.submenu-leave-active,
.fade-enter-active,
.fade-leave-active {
  transition: all 0.2s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-10px);
}

.submenu-enter-from,
.submenu-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Animations */
@keyframes float {
  0% { transform: translate(0, 0) scale(1); }
  100% { transform: translate(20px, 20px) scale(1.1); }
}

@keyframes pulseGlow {
  0% { opacity: 0.1; transform: translate(-50%, -50%) scale(0.9); }
  100% { opacity: 0.25; transform: translate(-50%, -50%) scale(1.1); }
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.2); }
}

/* Responsive touch adjustments */
@media (max-width: 768px) {
  .sidebar__group-btn,
  .sidebar__link {
    padding: 0.75rem 0.875rem;
    margin-bottom: 0.5rem;
  }
  .sidebar__sub-link {
    padding: 0.6rem 0.75rem;
  }
}
</style>