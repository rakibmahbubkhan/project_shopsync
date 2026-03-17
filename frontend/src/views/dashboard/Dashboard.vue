<template>
  <div class="space-y-6">
    <!-- Header with Live Analytics Badge and Date Range -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-black text-gray-800 tracking-tight">Dashboard Overview</h1>
        <p class="text-sm text-gray-500 mt-1">{{ currentDateRange }}</p>
      </div>
      <div class="flex items-center gap-3">
        <!-- Date Range Selector -->
        <select 
          v-model="dateRange" 
          @change="handleDateRangeChange"
          class="text-sm border rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 outline-none"
        >
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="quarter">This Quarter</option>
          <option value="year">This Year</option>
        </select>
        
        <span class="text-sm font-bold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full uppercase">
          Live Analytics
        </span>
      </div>
    </div>

    <!-- Main Stats Cards with Click Handlers -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div 
        v-for="card in mainStatsCards" 
        :key="card.label" 
        @click="handleStatCardClick(card.route)"
        class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 transition-all hover:shadow-md hover:border-blue-200 cursor-pointer group"
      >
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ card.label }}</p>
            <p class="text-2xl font-black" :class="card.color">
              {{ card.prefix }}{{ formatNumber(card.value) }}
            </p>
          </div>
          <span class="text-gray-300 group-hover:text-blue-500 transition-colors">→</span>
        </div>
        <p class="text-xs text-gray-400 mt-2">{{ card.subtext }}</p>
      </div>
    </div>

    <!-- Secondary Stats Cards with Click Handlers -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div 
        v-for="card in secondaryStatsCards" 
        :key="card.label" 
        @click="handleStatCardClick(card.route)"
        class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 transition-all hover:shadow-md hover:border-blue-200 cursor-pointer group"
      >
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-medium text-gray-500">{{ card.label }}</p>
            <p class="text-2xl font-bold mt-1" :class="card.color">
              <span v-if="card.prefix">{{ card.prefix }}</span>{{ formatNumber(card.value) }}
            </p>
          </div>
          <span class="text-gray-300 group-hover:text-blue-500 transition-colors">→</span>
        </div>
      </div>
    </div>

    <!-- Main Content Grid: Chart + Stock Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Chart Section with Period Selector -->
      <div class="lg:col-span-2 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h3 class="font-bold text-gray-700">Revenue Trend</h3>
            <p class="text-xs text-gray-400 mt-1">{{ chartPeriodLabel }}</p>
          </div>
          <div class="flex gap-2">
            <!-- Chart Period Toggle -->
            <div class="flex bg-gray-100 rounded-lg p-1">
              <button 
                v-for="period in chartPeriods" 
                :key="period.value"
                @click="changeChartPeriod(period.value)"
                class="text-xs px-3 py-1.5 rounded-md transition"
                :class="chartPeriod === period.value ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600 hover:text-gray-900'"
              >
                {{ period.label }}
              </button>
            </div>
            
            <button 
              @click="refreshData" 
              class="text-xs bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-full transition flex items-center gap-1"
              title="Refresh data"
              :disabled="refreshing"
            >
              <span class="text-lg" :class="{ 'animate-spin': refreshing }">↻</span>
              <span>{{ refreshing ? 'Refreshing...' : 'Refresh' }}</span>
            </button>
            
            <!-- Export Chart Data -->
            <button 
              @click="exportChartData"
              class="text-xs bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-full transition"
              title="Export data"
            >
              📊 Export
            </button>
          </div>
        </div>
        
        <!-- Chart Component -->
        <div v-if="chartData.length > 0" class="h-64">
          <DashboardRevenueChart 
            :data="chartData" 
            :period="chartPeriod"
            @point-click="handleChartPointClick"
          />
        </div>
        <div v-else class="h-64 flex flex-col items-center justify-center">
          <p class="text-gray-400 italic mb-2">No sales data available for this period</p>
          <button 
            @click="loadSampleData"
            class="text-xs text-blue-600 hover:text-blue-800 underline"
          >
            Load sample data
          </button>
        </div>
        
        <!-- Chart Summary -->
        <div v-if="chartData.length > 0" class="mt-4 pt-4 border-t flex justify-between text-sm">
          <div>
            <span class="text-gray-500">Average: </span>
            <span class="font-bold text-gray-800">৳{{ formatNumber(averageRevenue) }}</span>
          </div>
          <div>
            <span class="text-gray-500">Peak: </span>
            <span class="font-bold text-green-600">৳{{ formatNumber(peakRevenue) }}</span>
          </div>
          <div>
            <span class="text-gray-500">Total: </span>
            <span class="font-bold text-blue-600">৳{{ formatNumber(totalRevenue) }}</span>
          </div>
        </div>
      </div>

      <!-- Stock Alerts Section with Actions -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-700 mb-4 flex justify-between items-center">
          <span>Stock Alerts</span>
          <div class="flex gap-2">
            <span 
              class="text-[10px] px-2 py-0.5 rounded-full uppercase"
              :class="lowStock.length > 0 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'"
            >
              {{ lowStock.length > 0 ? `${lowStock.length} Items` : 'All Good' }}
            </span>
            <button 
              @click="refreshLowStock"
              class="text-xs text-gray-400 hover:text-gray-600"
              title="Refresh stock alerts"
            >
              ↻
            </button>
          </div>
        </h3>
        
        <div class="space-y-4 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
          <div 
            v-for="item in lowStock" 
            :key="item.id" 
            @click="goToProduct(item.id)"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-red-50 transition cursor-pointer group"
          >
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-gray-800 truncate group-hover:text-red-600">{{ item.name }}</p>
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
          
          <div v-if="lowStock.length === 0" class="text-center py-8">
            <p class="text-gray-400 italic text-sm mb-3">✅ All stock levels are healthy</p>
            <button 
              @click="goToProducts"
              class="text-xs bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-full transition"
            >
              View All Products
            </button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4 pt-2 border-t grid grid-cols-2 gap-2">
          <router-link 
            to="/reports/low-stock"
            class="text-xs text-center bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-2 rounded-lg font-medium transition"
          >
            View All Alerts
          </router-link>
          <router-link 
            to="/purchases/new"
            class="text-xs text-center bg-green-50 text-green-600 hover:bg-green-100 px-3 py-2 rounded-lg font-medium transition"
          >
            + New Purchase
          </router-link>
        </div>
      </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Sales -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-4">
          <h3 class="font-bold text-gray-700">Recent Sales</h3>
          <router-link to="/sales" class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1">
            View All <span>→</span>
          </router-link>
        </div>
        
        <div class="space-y-3">
          <div 
            v-for="sale in recentSales" 
            :key="sale.id"
            @click="goToSale(sale.id)"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition cursor-pointer"
          >
            <div>
              <p class="text-sm font-bold text-gray-800">{{ sale.customer?.name || 'Walk-in Customer' }}</p>
              <p class="text-xs text-gray-400">{{ formatDate(sale.sale_date) }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-black text-blue-600">৳{{ formatNumber(sale.total_amount) }}</p>
              <p class="text-xs text-gray-400">{{ sale.items_count || 0 }} items</p>
            </div>
          </div>
          
          <div v-if="recentSales.length === 0" class="text-center py-8 text-gray-400 italic">
            No recent sales
          </div>
        </div>
      </div>

      <!-- Recent Purchases -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-4">
          <h3 class="font-bold text-gray-700">Recent Purchases</h3>
          <router-link to="/purchases" class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1">
            View All <span>→</span>
          </router-link>
        </div>
        
        <div class="space-y-3">
          <div 
            v-for="purchase in recentPurchases" 
            :key="purchase.id"
            @click="goToPurchase(purchase.id)"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-green-50 transition cursor-pointer"
          >
            <div>
              <p class="text-sm font-bold text-gray-800">{{ purchase.supplier?.name || 'N/A' }}</p>
              <p class="text-xs text-gray-400">{{ formatDate(purchase.purchase_date) }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-black text-green-600">৳{{ formatNumber(purchase.total_amount) }}</p>
              <p class="text-xs text-gray-400">{{ purchase.items_count || 0 }} items</p>
            </div>
          </div>
          
          <div v-if="recentPurchases.length === 0" class="text-center py-8 text-gray-400 italic">
            No recent purchases
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Action Buttons -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <button 
        @click="goToNewSale"
        class="bg-blue-600 text-white p-4 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg flex items-center justify-center gap-2"
      >
        <span class="text-xl">+</span> New Sale
      </button>
      <button 
        @click="goToNewPurchase"
        class="bg-green-600 text-white p-4 rounded-xl font-bold hover:bg-green-700 transition shadow-lg flex items-center justify-center gap-2"
      >
        <span class="text-xl">+</span> New Purchase
      </button>
      <button 
        @click="goToNewProduct"
        class="bg-purple-600 text-white p-4 rounded-xl font-bold hover:bg-purple-700 transition shadow-lg flex items-center justify-center gap-2"
      >
        <span class="text-xl">+</span> New Product
      </button>
      <button 
        @click="generateReport"
        class="bg-orange-600 text-white p-4 rounded-xl font-bold hover:bg-orange-700 transition shadow-lg flex items-center justify-center gap-2"
      >
        <span class="text-xl">📊</span> Generate Report
      </button>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-20 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-2xl shadow-xl text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent mx-auto"></div>
        <p class="mt-4 text-gray-600 font-medium">{{ loadingMessage }}</p>
      </div>
    </div>

    <!-- Error Toast -->
    <div v-if="error" class="fixed bottom-4 right-4 bg-red-50 text-red-600 p-4 rounded-xl shadow-lg border border-red-200 flex items-center gap-3">
      <span class="text-lg">⚠️</span>
      <span class="text-sm font-medium">{{ error }}</span>
      <button @click="error = null" class="text-red-400 hover:text-red-600">✕</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/api/axios';
import DashboardRevenueChart from '@/components/charts/DashboardRevenueChart.vue';

const router = useRouter();

// State
const loading = ref(false);
const loadingMessage = ref('Loading dashboard data...');
const refreshing = ref(false);
const error = ref(null);
const dateRange = ref('week');
const chartPeriod = ref('week');
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
const chartData = ref([]);
const lowStock = ref([]);
const recentSales = ref([]);
const recentPurchases = ref([]);

// Chart periods
const chartPeriods = [
  { value: 'week', label: 'Week' },
  { value: 'month', label: 'Month' },
  { value: 'quarter', label: 'Quarter' },
  { value: 'year', label: 'Year' }
];

// Computed Properties
const currentDateRange = computed(() => {
  const now = new Date();
  switch(dateRange.value) {
    case 'today':
      return `Today, ${now.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`;
    case 'week':
      const weekStart = new Date(now.setDate(now.getDate() - now.getDay()));
      const weekEnd = new Date(now.setDate(weekStart.getDate() + 6));
      return `${weekStart.toLocaleDateString()} - ${weekEnd.toLocaleDateString()}`;
    case 'month':
      return `Month of ${now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}`;
    default:
      return 'Real-time updates';
  }
});

const chartPeriodLabel = computed(() => {
  switch(chartPeriod.value) {
    case 'week': return 'Last 7 days';
    case 'month': return 'This month';
    case 'quarter': return 'Last 90 days';
    case 'year': return 'This year';
    default: return '';
  }
});

const averageRevenue = computed(() => {
  if (!chartData.value.length) return 0;
  const sum = chartData.value.reduce((acc, item) => acc + (item.revenue || 0), 0);
  return sum / chartData.value.length;
});

const peakRevenue = computed(() => {
  if (!chartData.value.length) return 0;
  return Math.max(...chartData.value.map(item => item.revenue || 0));
});

const totalRevenue = computed(() => {
  return chartData.value.reduce((acc, item) => acc + (item.revenue || 0), 0);
});

const mainStatsCards = computed(() => [
  { 
    label: 'Total Revenue', 
    value: summary.value.total_revenue, 
    prefix: '৳', 
    color: 'text-blue-600',
    route: '/reports/sales',
    subtext: 'Lifetime revenue'
  },
  { 
    label: 'Gross Profit', 
    value: summary.value.total_profit || summary.value.gross_profit, 
    prefix: '৳', 
    color: 'text-green-600',
    route: '/reports/profit-loss',
    subtext: 'After costs'
  },
  { 
    label: 'Today\'s Sales', 
    value: summary.value.today_sales || 0, 
    prefix: '৳', 
    color: 'text-secondary',
    route: '/sales?period=today',
    subtext: 'Current day'
  },
  { 
    label: 'Low Stock Items', 
    value: summary.value.low_stock_count || summary.value.low_stock_products || 0, 
    prefix: '', 
    color: summary.value.low_stock_count > 0 ? 'text-red-600' : 'text-gray-600',
    route: '/reports/low-stock',
    subtext: summary.value.low_stock_count > 0 ? 'Need attention' : 'All good'
  },
]);

const secondaryStatsCards = computed(() => [
  { 
    label: 'Monthly Sales', 
    value: summary.value.monthly_sales || 0, 
    prefix: '৳', 
    color: 'text-gray-800',
    route: '/reports/sales?period=month'
  },
  { 
    label: 'Active Products', 
    value: summary.value.total_products || 0, 
    prefix: '', 
    color: 'text-gray-800',
    route: '/products'
  },
  { 
    label: 'Total Purchases', 
    value: summary.value.total_purchases || 0, 
    prefix: '৳', 
    color: 'text-orange-600',
    route: '/reports/purchases'
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

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const handleStatCardClick = (route) => {
  if (route) {
    router.push(route);
  }
};

const handleDateRangeChange = () => {
  fetchDashboardStats();
};

const changeChartPeriod = (period) => {
  chartPeriod.value = period;
  fetchSalesTrend();
};

const handleChartPointClick = (data) => {
  // Navigate to sales for that date
  if (data.date) {
    router.push(`/sales?date=${data.date}`);
  }
};

const fetchDashboardStats = async () => {
  loading.value = true;
  loadingMessage.value = 'Loading dashboard data...';
  error.value = null;
  
  try {
    const response = await api.get('/reports/dashboard', {
      params: { period: dateRange.value }
    });
    
    if (response.data.success && response.data.data) {
      const data = response.data.data;
      summary.value = {
        total_revenue: data.sales?.total || 0,
        total_profit: data.sales?.profit || 0,
        gross_profit: data.sales?.profit || 0,
        total_purchases: data.purchases?.total || 0,
        low_stock_count: data.inventory?.low_stock || 0,
        low_stock_products: data.inventory?.low_stock || 0,
        total_products: data.inventory?.total_products || 0,
        total_customers: data.customers?.total || 0,
        today_sales: data.sales?.today || 0,
        monthly_sales: data.sales?.month || 0,
        total_sales_count: data.sales?.count || 0,
      };
    }
    
    // Fetch other data in parallel
    await Promise.all([
      fetchSalesTrend(),
      fetchLowStock(),
      fetchRecentSales(),
      fetchRecentPurchases()
    ]);
    
  } catch (error) {
    console.error('Error fetching dashboard stats:', error);
    error.value = 'Failed to load dashboard data';
    
    // Load sample data for demo
    loadSampleData();
  } finally {
    loading.value = false;
    refreshing.value = false;
  }
};

const fetchSalesTrend = async () => {
  try {
    const response = await api.get('/reports/sales-trend', {
      params: { period: chartPeriod.value }
    });
    chartData.value = response.data.data?.trend || response.data || [];
  } catch (error) {
    console.error('Error fetching sales trend:', error);
    // Generate sample trend data
    generateSampleTrendData();
  }
};

const fetchLowStock = async () => {
  try {
    const response = await api.get('/reports/low-stock');
    lowStock.value = response.data.data?.products || response.data || [];
  } catch (error) {
    console.error('Error fetching low stock:', error);
    lowStock.value = [];
  }
};

const fetchRecentSales = async () => {
  try {
    const response = await api.get('/sales?limit=5');
    recentSales.value = response.data.data || response.data || [];
  } catch (error) {
    console.error('Error fetching recent sales:', error);
    recentSales.value = [];
  }
};

const fetchRecentPurchases = async () => {
  try {
    const response = await api.get('/purchases?limit=5');
    recentPurchases.value = response.data.data || response.data || [];
  } catch (error) {
    console.error('Error fetching recent purchases:', error);
    recentPurchases.value = [];
  }
};

const refreshData = async () => {
  refreshing.value = true;
  await fetchDashboardStats();
};

const refreshLowStock = async () => {
  await fetchLowStock();
};

const exportChartData = () => {
  const dataStr = JSON.stringify(chartData.value, null, 2);
  const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
  
  const exportFileDefaultName = `revenue-trend-${chartPeriod.value}-${new Date().toISOString().split('T')[0]}.json`;
  
  const linkElement = document.createElement('a');
  linkElement.setAttribute('href', dataUri);
  linkElement.setAttribute('download', exportFileDefaultName);
  linkElement.click();
};

const loadSampleData = () => {
  // Generate sample summary data
  summary.value = {
    total_revenue: 157890.50,
    total_profit: 45678.25,
    gross_profit: 45678.25,
    total_purchases: 89234.75,
    low_stock_count: 3,
    low_stock_products: 3,
    total_products: 156,
    total_customers: 89,
    today_sales: 5678.90,
    monthly_sales: 45678.50,
    total_sales_count: 234,
  };
  
  generateSampleTrendData();
  generateSampleLowStock();
  generateSampleRecentSales();
  generateSampleRecentPurchases();
};

const generateSampleTrendData = () => {
  const data = [];
  const days = chartPeriod.value === 'week' ? 7 : chartPeriod.value === 'month' ? 30 : 90;
  
  for (let i = 0; i < days; i++) {
    const date = new Date();
    date.setDate(date.getDate() - (days - i - 1));
    
    data.push({
      date: date.toISOString().split('T')[0],
      label: date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
      revenue: Math.random() * 5000 + 2000,
      profit: Math.random() * 1500 + 500
    });
  }
  
  chartData.value = data;
};

const generateSampleLowStock = () => {
  lowStock.value = [
    {
      id: 1,
      name: 'Engine Oil Filter',
      sku: 'FIL-001',
      category: { name: 'Filters' },
      unit: { short_name: 'pcs' },
      stock: 5,
      alert_quantity: 10
    },
    {
      id: 2,
      name: 'Brake Pad Set',
      sku: 'BRK-002',
      category: { name: 'Brakes' },
      unit: { short_name: 'set' },
      stock: 3,
      alert_quantity: 8
    },
    {
      id: 3,
      name: 'Spark Plug',
      sku: 'SPK-003',
      category: { name: 'Electrical' },
      unit: { short_name: 'pcs' },
      stock: 12,
      alert_quantity: 20
    }
  ];
};

const generateSampleRecentSales = () => {
  recentSales.value = [
    {
      id: 101,
      customer: { name: 'John Smith' },
      sale_date: new Date().toISOString(),
      total_amount: 1250.75,
      items_count: 3
    },
    {
      id: 102,
      customer: { name: 'Sarah Johnson' },
      sale_date: new Date(Date.now() - 86400000).toISOString(),
      total_amount: 890.50,
      items_count: 2
    },
    {
      id: 103,
      customer: { name: 'Mike Wilson' },
      sale_date: new Date(Date.now() - 172800000).toISOString(),
      total_amount: 2340.25,
      items_count: 5
    }
  ];
};

const generateSampleRecentPurchases = () => {
  recentPurchases.value = [
    {
      id: 201,
      supplier: { name: 'AutoParts Co.' },
      purchase_date: new Date().toISOString(),
      total_amount: 3450.00,
      items_count: 15
    },
    {
      id: 202,
      supplier: { name: 'Parts Plus' },
      purchase_date: new Date(Date.now() - 86400000).toISOString(),
      total_amount: 2100.50,
      items_count: 8
    },
    {
      id: 203,
      supplier: { name: 'Global Auto' },
      purchase_date: new Date(Date.now() - 172800000).toISOString(),
      total_amount: 5670.75,
      items_count: 22
    }
  ];
};

// Navigation Methods
const goToProduct = (id) => {
  router.push(`/products/${id}`);
};

const goToSale = (id) => {
  router.push(`/sales/${id}`);
};

const goToPurchase = (id) => {
  router.push(`/purchases/${id}`);
};

const goToProducts = () => {
  router.push('/products');
};

const goToNewSale = () => {
  router.push('/sales/new');
};

const goToNewPurchase = () => {
  router.push('/purchases/new');
};

const goToNewProduct = () => {
  router.push('/products/new');
};

const generateReport = () => {
  router.push('/reports?generate=true');
};

// Watchers
watch(chartPeriod, () => {
  fetchSalesTrend();
});

// Lifecycle
onMounted(() => {
  fetchDashboardStats();
});
</script>

<style scoped>
/* Smooth scrolling for stock alerts */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f7fafc;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f7fafc;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e0;
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #94a3b8;
}

/* Hover transition */
.transition-hover {
  transition: all 0.2s ease;
}

/* Animation for refresh button */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Card hover effects */
.cursor-pointer {
  cursor: pointer;
}

/* Loading overlay */
.fixed {
  backdrop-filter: blur(2px);
}
</style>