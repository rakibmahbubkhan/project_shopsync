<template>
  <div class="space-y-6">
    <!-- Header with Live Analytics Badge -->
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-black text-gray-800 tracking-tight">Dashboard Overview</h1>
      <span class="text-sm font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase">Live Analytics</span>
    </div>

    <!-- Main Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div 
        v-for="card in mainStatsCards" 
        :key="card.label" 
        class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 transition-hover hover:shadow-md"
      >
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ card.label }}</p>
        <p class="text-2xl font-black" :class="card.color">
          {{ card.prefix }}{{ formatNumber(card.value) }}
        </p>
      </div>
    </div>

    <!-- Secondary Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div 
        v-for="card in secondaryStatsCards" 
        :key="card.label" 
        class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100"
      >
        <p class="text-sm font-medium text-gray-500">{{ card.label }}</p>
        <p class="text-2xl font-bold mt-1" :class="card.color">
          <span v-if="card.prefix">{{ card.prefix }}</span>{{ formatNumber(card.value) }}
        </p>
      </div>
    </div>

    <!-- Main Content Grid: Chart + Stock Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Chart Section -->
      <div class="lg:col-span-2 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
          <h3 class="font-bold text-gray-700">Revenue Trend (Last 7 Days)</h3>
          <div class="flex gap-2">
            <button 
              @click="refreshData" 
              class="text-xs bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-full transition"
              title="Refresh data"
            >
              ↻ Refresh
            </button>
          </div>
        </div>
        
        <!-- Chart Component or Placeholder -->
        <div v-if="salesTrend.length > 0" class="h-64">
          <DashboardRevenueChart :data="salesTrend" />
        </div>
        <div v-else class="h-64 flex items-center justify-center">
          <p class="text-gray-400 italic">No sales data available for the last 7 days</p>
        </div>
      </div>

      <!-- Stock Alerts Section -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-700 mb-4 flex justify-between items-center">
          <span>Stock Alerts</span>
          <span 
            class="bg-red-100 text-red-600 text-[10px] px-2 py-0.5 rounded-full uppercase"
            :class="{ 'bg-yellow-100 text-yellow-600': lowStock.length === 0 }"
          >
            {{ lowStock.length > 0 ? 'Urgent' : 'All Good' }}
          </span>
        </h3>
        
        <div class="space-y-4 max-h-[350px] overflow-y-auto pr-2">
          <div 
            v-for="item in lowStock" 
            :key="item.id" 
            class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-red-50 transition"
          >
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-gray-800 truncate">{{ item.name }}</p>
              <div class="flex items-center gap-2 text-[10px] text-gray-400 uppercase font-medium">
                <span>{{ item.category?.name || 'Uncategorized' }}</span>
                <span>•</span>
                <span>SKU: {{ item.sku || 'N/A' }}</span>
              </div>
            </div>
            <div class="text-right ml-4">
              <p class="text-sm font-black" :class="item.stock <= 0 ? 'text-red-600' : 'text-orange-600'">
                {{ formatNumber(item.stock) }} {{ item.unit?.short_name || 'pcs' }}
              </p>
              <p class="text-[10px] text-gray-400">Min: {{ formatNumber(item.alert_quantity) }}</p>
            </div>
          </div>
          
          <div v-if="lowStock.length === 0" class="text-center py-8 text-gray-400 italic text-sm">
            <p>✅ All stock levels are healthy</p>
            <p class="text-xs mt-2">No items below alert quantity</p>
          </div>
        </div>

        <!-- View All Link -->
        <div v-if="lowStock.length > 0" class="mt-4 pt-2 border-t text-center">
          <router-link to="/reports/low-stock" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
            View All Low Stock Items →
          </router-link>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-20 flex items-center justify-center z-50">
      <div class="bg-white p-4 rounded-lg shadow-lg">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary border-t-transparent"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '@/api/axios';
import DashboardRevenueChart from '@/components/charts/DashboardRevenueChart.vue';

// State
const loading = ref(false);
const summary = ref({ 
  total_revenue: 0, 
  total_profit: 0, 
  total_purchases: 0, 
  low_stock_count: 0,
  total_products: 0,
  today_sales: 0,
  monthly_sales: 0,
  gross_profit: 0,
  total_sales_count: 0,
  low_stock_products: 0
});
const salesTrend = ref([]);
const lowStock = ref([]);

// Computed Properties for Stats Cards
const mainStatsCards = computed(() => [
  { 
    label: 'Total Revenue', 
    value: summary.value.total_revenue, 
    prefix: '৳', 
    color: 'text-blue-600' 
  },
  { 
    label: 'Gross Profit', 
    value: summary.value.total_profit || summary.value.gross_profit, 
    prefix: '৳', 
    color: 'text-green-600' 
  },
  { 
    label: 'Today\'s Sales', 
    value: summary.value.today_sales || 0, 
    prefix: '৳', 
    color: 'text-secondary' 
  },
  { 
    label: 'Low Stock Items', 
    value: summary.value.low_stock_count || summary.value.low_stock_products || 0, 
    prefix: '', 
    color: summary.value.low_stock_count > 0 ? 'text-red-600' : 'text-gray-600' 
  },
]);

const secondaryStatsCards = computed(() => [
  { 
    label: 'Monthly Sales', 
    value: summary.value.monthly_sales || 0, 
    prefix: '৳', 
    color: 'text-gray-800' 
  },
  { 
    label: 'Active Products', 
    value: summary.value.total_products || 0, 
    prefix: '', 
    color: 'text-gray-800' 
  },
  { 
    label: 'Total Purchases', 
    value: summary.value.total_purchases || 0, 
    prefix: '৳', 
    color: 'text-orange-600' 
  },
]);

// Methods
const formatNumber = (value) => {
  if (value === null || value === undefined) return '0.00';
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
};

const fetchDashboardStats = async () => {
  loading.value = true;
  try {
    // Fetch from the enhanced dashboard endpoint
    const response = await api.get('/reports/dashboard');
    
    if (response.data.success && response.data.data) {
      // Map the structured response to our summary object
      const data = response.data.data;
      summary.value = {
        total_revenue: data.sales?.year || 0,
        total_profit: data.sales?.month?.profit || 0,
        gross_profit: data.sales?.month?.profit || 0,
        total_purchases: data.purchases?.month || 0,
        low_stock_count: data.summary?.low_stock_count || 0,
        low_stock_products: data.summary?.low_stock_count || 0,
        total_products: data.summary?.total_products || 0,
        total_customers: data.summary?.total_customers || 0,
        today_sales: data.sales?.today?.amount || 0,
        monthly_sales: data.sales?.month?.amount || 0,
        total_sales_count: data.sales?.total_transactions || 0,
      };
    } else {
      // Fallback to individual endpoints if dashboard endpoint doesn't return expected format
      await Promise.all([
        fetchSummary(),
        fetchSalesTrend(),
        fetchLowStock()
      ]);
    }
  } catch (error) {
    console.error('Error fetching dashboard stats:', error);
    // Fallback to individual endpoints on error
    await Promise.all([
      fetchSummary(),
      fetchSalesTrend(),
      fetchLowStock()
    ]);
  } finally {
    loading.value = false;
  }
};

// Fallback methods for backward compatibility
const fetchSummary = async () => {
  try {
    const response = await api.get('/reports/summary');
    summary.value = {
      ...summary.value,
      ...response.data
    };
  } catch (error) {
    console.error('Error fetching summary:', error);
  }
};

const fetchSalesTrend = async () => {
  try {
    const response = await api.get('/reports/sales-trend');
    salesTrend.value = response.data.data?.trend || response.data;
  } catch (error) {
    console.error('Error fetching sales trend:', error);
  }
};

const fetchLowStock = async () => {
  try {
    const response = await api.get('/reports/low-stock');
    lowStock.value = response.data.data?.products || response.data;
  } catch (error) {
    console.error('Error fetching low stock:', error);
  }
};

const refreshData = () => {
  fetchDashboardStats();
};

// Lifecycle
onMounted(() => {
  fetchDashboardStats();
});
</script>

<style scoped>
/* Smooth scrolling for stock alerts */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f7fafc;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f7fafc;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #cbd5e0;
  border-radius: 3px;
}

/* Hover transition */
.transition-hover {
  transition: all 0.2s ease;
}
</style>