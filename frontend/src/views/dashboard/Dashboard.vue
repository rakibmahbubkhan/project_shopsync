<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50/20">
    <!-- Animated Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-indigo-400/20 rounded-full blur-3xl animate-pulse-slow"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-400/20 rounded-full blur-3xl animate-pulse-slower"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-r from-purple-400/10 to-pink-400/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8">
      
      <!-- Header Section -->
      <div class="mb-6 md:mb-8">
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/50 p-4 sm:p-6">
          <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="flex items-center gap-4">
              <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl blur-lg opacity-75"></div>
                <div class="relative w-12 h-12 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-xl">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                  </svg>
                </div>
              </div>
              <div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black bg-gradient-to-r from-slate-800 to-indigo-900 bg-clip-text text-transparent">
                  Dashboard
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 flex items-center gap-2">
                  <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                  {{ currentDateRange }}
                </p>
              </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
              <!-- Date Range Selector -->
              <div class="relative flex-1 lg:flex-none group">
                <select 
                  v-model="dateRange" 
                  @change="handleDateRangeChange"
                  class="appearance-none bg-white/80 backdrop-blur-sm border border-slate-200 rounded-xl px-4 py-2.5 pr-10 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 cursor-pointer hover:bg-white transition-all w-full"
                >
                  <option value="today">Today</option>
                  <option value="week">This Week</option>
                  <option value="month">This Month</option>
                  <option value="quarter">This Quarter</option>
                  <option value="year">This Year</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                  <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </div>
              </div>
              
              <!-- Live Badge -->
              <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-blue-600 rounded-xl blur group-hover:blur-md transition-all opacity-75"></div>
                <span class="relative flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap">
                  <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                  </span>
                  Live Analytics
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPI Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
        <div 
          v-for="kpi in kpiCards" 
          :key="kpi.label"
          @click="navigateTo(kpi.route)"
          class="group cursor-pointer transform transition-all duration-300 hover:scale-105 hover:-translate-y-1"
        >
          <div class="relative bg-white rounded-2xl p-5 shadow-lg border border-slate-100 hover:shadow-xl transition-all h-full">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/5 to-blue-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
            
            <div class="flex justify-between items-start mb-3">
              <div class="p-2 rounded-xl" :class="kpi.bgColor">
                <svg class="w-5 h-5" :class="kpi.iconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path :stroke-linecap="round" :stroke-linejoin="round" stroke-width="2" :d="kpi.icon"></path>
                </svg>
              </div>
              <span class="text-xs font-medium px-2 py-1 rounded-full" :class="kpi.trendClass">
                {{ kpi.trend }}
              </span>
            </div>
            
            <p class="text-2xl lg:text-3xl font-black text-slate-800">{{ kpi.prefix }}{{ formatNumber(kpi.value) }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ kpi.label }}</p>
            
            <div class="mt-3 pt-3 border-t border-slate-100">
              <p class="text-xs text-slate-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                {{ kpi.subtext }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts and Alerts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 md:mb-8">
        <!-- Revenue Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg border border-slate-100 p-5">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
            <div>
              <h3 class="text-lg font-bold text-slate-800">Revenue Overview</h3>
              <p class="text-xs text-slate-400 mt-1">Track your business performance</p>
            </div>
            
            <div class="flex bg-slate-100 rounded-xl p-1">
              <button 
                v-for="period in chartPeriods" 
                :key="period.value"
                @click="changeChartPeriod(period.value)"
                class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all"
                :class="chartPeriod === period.value 
                  ? 'bg-white shadow-md text-indigo-600' 
                  : 'text-slate-600 hover:text-indigo-600'"
              >
                {{ period.label }}
              </button>
            </div>
          </div>
          
          <!-- Chart Container -->
          <div class="h-64 md:h-80">
            <canvas ref="chartCanvas"></canvas>
          </div>
          
          <!-- Chart Stats -->
          <div class="grid grid-cols-3 gap-3 mt-5 pt-4 border-t border-slate-100">
            <div class="text-center">
              <p class="text-xs text-slate-400 mb-1">Average</p>
              <p class="text-lg font-bold text-indigo-600">৳{{ formatNumber(averageRevenue) }}</p>
            </div>
            <div class="text-center">
              <p class="text-xs text-slate-400 mb-1">Peak</p>
              <p class="text-lg font-bold text-emerald-600">৳{{ formatNumber(peakRevenue) }}</p>
            </div>
            <div class="text-center">
              <p class="text-xs text-slate-400 mb-1">Total</p>
              <p class="text-lg font-bold text-blue-600">৳{{ formatNumber(totalRevenue) }}</p>
            </div>
          </div>
        </div>

        <!-- Stock Alerts -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-5">
          <div class="flex justify-between items-center mb-5">
            <div>
              <h3 class="text-lg font-bold text-slate-800">Stock Alerts</h3>
              <p class="text-xs text-slate-400 mt-1">Items below threshold</p>
            </div>
            <div class="relative">
              <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-orange-500 rounded-full blur opacity-50" v-if="lowStock.length"></div>
              <span class="relative text-xs font-bold px-3 py-1.5 rounded-full" :class="lowStock.length ? 'bg-gradient-to-r from-red-500 to-orange-500 text-white' : 'bg-emerald-100 text-emerald-700'">
                {{ lowStock.length ? `${lowStock.length} Critical` : 'All Good' }}
              </span>
            </div>
          </div>
          
          <div class="space-y-3 max-h-[320px] overflow-y-auto custom-scrollbar">
            <div 
              v-for="item in lowStock" 
              :key="item.id"
              @click="goToProduct(item.id)"
              class="group cursor-pointer bg-gradient-to-r from-red-50 to-orange-50 rounded-xl p-4 hover:shadow-md transition-all"
            >
              <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                  <p class="font-bold text-slate-800 group-hover:text-red-600 transition-colors">{{ item.name }}</p>
                  <p class="text-xs text-slate-500 mt-0.5">{{ item.sku }}</p>
                </div>
                <div class="text-right">
                  <p class="text-xl font-black text-red-600">{{ item.stock }}</p>
                  <p class="text-xs text-slate-500">Min: {{ item.alert_quantity }}</p>
                </div>
              </div>
              <div class="w-full h-1.5 bg-red-200 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-red-500 to-orange-500 rounded-full transition-all" :style="{ width: `${Math.min((item.stock / item.alert_quantity) * 100, 100)}%` }"></div>
              </div>
            </div>
            
            <div v-if="!lowStock.length" class="text-center py-8">
              <div class="w-16 h-16 mx-auto mb-3 bg-emerald-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <p class="text-slate-500 font-medium">All stock levels healthy</p>
              <p class="text-xs text-slate-400 mt-1">No items below threshold</p>
            </div>
          </div>
          
          <div class="mt-5 pt-4 border-t border-slate-100 flex gap-3">
            <router-link to="/reports/low-stock" class="flex-1 text-center text-sm bg-slate-100 text-slate-700 px-4 py-2.5 rounded-xl font-medium hover:bg-slate-200 transition-all">
              View All Alerts
            </router-link>
            <router-link to="/purchases/create" class="flex-1 text-center text-sm bg-gradient-to-r from-indigo-600 to-blue-600 text-white px-4 py-2.5 rounded-xl font-medium hover:shadow-lg transition-all">
              + New Purchase
            </router-link>
          </div>
        </div>
      </div>

      <!-- Recent Activity Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 md:mb-8">
        <!-- Recent Sales -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
          <div class="p-5 border-b border-slate-100">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-lg font-bold text-slate-800">Recent Sales</h3>
                <p class="text-xs text-slate-400 mt-1">Latest transactions</p>
              </div>
              <router-link to="/sales" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                View All
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </router-link>
            </div>
          </div>
          
          <div class="divide-y divide-slate-100">
            <div 
              v-for="sale in recentSales" 
              :key="sale.id"
              @click="goToSale(sale.id)"
              class="p-4 hover:bg-slate-50 cursor-pointer transition-all"
            >
              <div class="flex justify-between items-center">
                <div>
                  <p class="font-semibold text-slate-800">{{ sale.customer }}</p>
                  <div class="flex items-center gap-2 text-xs text-slate-400 mt-1">
                    <span>{{ formatDate(sale.date) }}</span>
                    <span>•</span>
                    <span>{{ sale.items }} items</span>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-lg font-bold text-emerald-600">৳{{ formatNumber(sale.amount) }}</p>
                  <p class="text-xs text-slate-400">#{{ sale.invoice_no }}</p>
                </div>
              </div>
            </div>
            
            <div v-if="!recentSales.length" class="p-8 text-center text-slate-400">
              No recent sales
            </div>
          </div>
        </div>

        <!-- Recent Purchases -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
          <div class="p-5 border-b border-slate-100">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-lg font-bold text-slate-800">Recent Purchases</h3>
                <p class="text-xs text-slate-400 mt-1">Latest orders from suppliers</p>
              </div>
              <router-link to="/purchases" class="text-sm text-emerald-600 hover:text-emerald-800 font-medium flex items-center gap-1">
                View All
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </router-link>
            </div>
          </div>
          
          <div class="divide-y divide-slate-100">
            <div 
              v-for="purchase in recentPurchases" 
              :key="purchase.id"
              @click="goToPurchase(purchase.id)"
              class="p-4 hover:bg-slate-50 cursor-pointer transition-all"
            >
              <div class="flex justify-between items-center">
                <div>
                  <p class="font-semibold text-slate-800">{{ purchase.supplier }}</p>
                  <div class="flex items-center gap-2 text-xs text-slate-400 mt-1">
                    <span>{{ formatDate(purchase.date) }}</span>
                    <span>•</span>
                    <span>{{ purchase.items }} items</span>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-lg font-bold text-blue-600">৳{{ formatNumber(purchase.amount) }}</p>
                  <p class="text-xs text-slate-400">PO: {{ purchase.po_number }}</p>
                </div>
              </div>
            </div>
            
            <div v-if="!recentPurchases.length" class="p-8 text-center text-slate-400">
              No recent purchases
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <button 
          v-for="action in quickActions"
          :key="action.label"
          @click="action.handler"
          class="group relative overflow-hidden rounded-xl bg-gradient-to-r p-[2px] hover:shadow-xl transition-all duration-300 hover:scale-105"
          :class="action.gradient"
        >
          <div class="relative flex flex-col items-center justify-center gap-2 rounded-xl bg-white/95 backdrop-blur-sm px-4 py-4 transition-all group-hover:bg-transparent group-hover:text-white">
            <span class="text-2xl">{{ action.icon }}</span>
            <span class="font-semibold text-sm">{{ action.label }}</span>
          </div>
        </button>
      </div>

      <!-- System Status Footer -->
      <div class="mt-8 text-center">
        <p class="text-xs text-slate-400">
          Last updated: {{ lastUpdated }} • Data refreshes every 5 minutes
        </p>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 flex items-center justify-center z-50 backdrop-blur-xl bg-white/30">
      <div class="relative">
        <div class="absolute inset-0 rounded-full bg-gradient-to-r from-indigo-600 to-blue-600 blur-xl animate-pulse"></div>
        <div class="relative bg-white/90 backdrop-blur-xl rounded-2xl p-8 shadow-2xl border border-white/50">
          <div class="relative w-20 h-20 mx-auto">
            <div class="absolute inset-0 rounded-full border-4 border-indigo-200"></div>
            <div class="absolute inset-0 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></div>
            <div class="absolute inset-3 rounded-full bg-gradient-to-r from-indigo-600 to-blue-600"></div>
          </div>
          <p class="mt-4 text-slate-700 font-medium">{{ loadingMessage }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Chart from 'chart.js/auto';

const router = useRouter();

// State
const loading = ref(false);
const loadingMessage = ref('Loading dashboard...');
const dateRange = ref('week');
const chartPeriod = ref('week');
const chartCanvas = ref(null);
let chartInstance = null;

// Mock Data
const summary = ref({
  total_revenue: 45250.75,
  total_profit: 12580.50,
  gross_margin: 27.8,
  total_purchases: 28750.25,
  low_stock_count: 3,
  total_products: 156,
  today_sales: 2450.00,
  monthly_sales: 15250.50,
  avg_order_value: 942.72,
  conversion_rate: 68.5
});

const chartDataSets = {
  week: [
    { date: 'Mon', revenue: 4200, sales: 8 },
    { date: 'Tue', revenue: 5800, sales: 12 },
    { date: 'Wed', revenue: 5100, sales: 10 },
    { date: 'Thu', revenue: 6900, sales: 15 },
    { date: 'Fri', revenue: 7200, sales: 18 },
    { date: 'Sat', revenue: 6500, sales: 14 },
    { date: 'Sun', revenue: 4800, sales: 9 }
  ],
  month: [
    { date: 'Week 1', revenue: 18500, sales: 42 },
    { date: 'Week 2', revenue: 22400, sales: 51 },
    { date: 'Week 3', revenue: 19800, sales: 45 },
    { date: 'Week 4', revenue: 25600, sales: 58 }
  ],
  quarter: [
    { date: 'Jan', revenue: 68400, sales: 156 },
    { date: 'Feb', revenue: 72300, sales: 168 },
    { date: 'Mar', revenue: 79100, sales: 182 }
  ],
  year: [
    { date: 'Q1', revenue: 219800, sales: 506 },
    { date: 'Q2', revenue: 245600, sales: 567 },
    { date: 'Q3', revenue: 238900, sales: 548 },
    { date: 'Q4', revenue: 289400, sales: 623 }
  ]
};

const lowStock = ref([
  { id: 1, name: 'Engine Oil Filter', category: 'Auto Parts', sku: 'EOF-001', stock: 5, alert_quantity: 10 },
  { id: 2, name: 'Brake Pads Set', category: 'Auto Parts', sku: 'BP-002', stock: 3, alert_quantity: 15 },
  { id: 3, name: 'Air Filter', category: 'Auto Parts', sku: 'AF-003', stock: 8, alert_quantity: 20 }
]);

const recentSales = ref([
  { id: 1, customer: 'John Smith', date: '2024-01-07T10:30:00', amount: 1250.75, items: 3, invoice_no: 'INV-001' },
  { id: 2, customer: 'Sarah Johnson', date: '2024-01-07T09:15:00', amount: 890.50, items: 2, invoice_no: 'INV-002' },
  { id: 3, customer: 'Mike Wilson', date: '2024-01-06T16:45:00', amount: 2340.00, items: 5, invoice_no: 'INV-003' },
  { id: 4, customer: 'Emily Brown', date: '2024-01-06T14:20:00', amount: 675.25, items: 1, invoice_no: 'INV-004' }
]);

const recentPurchases = ref([
  { id: 1, supplier: 'AutoParts Co.', date: '2024-01-06T13:30:00', amount: 3450.00, items: 15, po_number: 'PO-001' },
  { id: 2, supplier: 'Tire Supply Inc.', date: '2024-01-05T10:45:00', amount: 5670.50, items: 20, po_number: 'PO-002' },
  { id: 3, supplier: 'Battery World', date: '2024-01-04T09:20:00', amount: 2340.75, items: 12, po_number: 'PO-003' }
]);

// KPI Cards Configuration
const kpiCards = computed(() => [
  { 
    label: 'Total Revenue', 
    value: summary.value.total_revenue, 
    prefix: '৳',
    icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    bgColor: 'bg-indigo-100',
    iconColor: 'text-indigo-600',
    trend: '+12.5%',
    trendClass: 'bg-emerald-100 text-emerald-700',
    subtext: 'vs last period',
    route: '/reports/sales'
  },
  { 
    label: 'Gross Profit', 
    value: summary.value.total_profit, 
    prefix: '৳',
    icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
    bgColor: 'bg-emerald-100',
    iconColor: 'text-emerald-600',
    trend: '+8.2%',
    trendClass: 'bg-emerald-100 text-emerald-700',
    subtext: 'Margin: 27.8%',
    route: '/reports/profit-loss'
  },
  { 
    label: 'Low Stock Items', 
    value: summary.value.low_stock_count, 
    prefix: '',
    icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    bgColor: summary.value.low_stock_count > 0 ? 'bg-orange-100' : 'bg-emerald-100',
    iconColor: summary.value.low_stock_count > 0 ? 'text-orange-600' : 'text-emerald-600',
    trend: summary.value.low_stock_count > 0 ? 'Critical' : 'Healthy',
    trendClass: summary.value.low_stock_count > 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700',
    subtext: 'Need immediate attention',
    route: '/reports/low-stock'
  },
  { 
    label: 'Avg Order Value', 
    value: summary.value.avg_order_value, 
    prefix: '৳',
    icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
    bgColor: 'bg-purple-100',
    iconColor: 'text-purple-600',
    trend: '+5.3%',
    trendClass: 'bg-emerald-100 text-emerald-700',
    subtext: `${summary.value.total_sales_count || 48} total orders`,
    route: '/sales'
  }
]);

// Chart periods
const chartPeriods = [
  { value: 'week', label: 'Week' },
  { value: 'month', label: 'Month' },
  { value: 'quarter', label: 'Quarter' },
  { value: 'year', label: 'Year' }
];

// Quick Actions
const quickActions = [
  { label: 'New Sale', icon: '💰', handler: () => router.push('/pos'), gradient: 'from-indigo-600 to-blue-600' },
  { label: 'New Purchase', icon: '📦', handler: () => router.push('/purchases/create'), gradient: 'from-emerald-600 to-teal-600' },
  { label: 'Add Product', icon: '✨', handler: () => router.push('/products/create'), gradient: 'from-purple-600 to-pink-600' },
  { label: 'Reports', icon: '📊', handler: () => router.push('/financial'), gradient: 'from-orange-600 to-red-600' }
];

// Computed
const currentDateRange = computed(() => {
  const now = new Date();
  switch(dateRange.value) {
    case 'today': return `Today, ${now.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`;
    case 'week': return `Week of ${now.toLocaleDateString('en-US', { month: 'long', day: 'numeric' })}`;
    case 'month': return `Month of ${now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}`;
    default: return 'Real-time dashboard';
  }
});

const currentChartData = computed(() => chartDataSets[chartPeriod.value] || chartDataSets.week);

const averageRevenue = computed(() => {
  const sum = currentChartData.value.reduce((acc, item) => acc + item.revenue, 0);
  return sum / currentChartData.value.length;
});

const peakRevenue = computed(() => {
  return Math.max(...currentChartData.value.map(item => item.revenue));
});

const totalRevenue = computed(() => {
  return currentChartData.value.reduce((acc, item) => acc + item.revenue, 0);
});

const lastUpdated = computed(() => {
  return new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
});

// Methods
const formatNumber = (value) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const initChart = () => {
  if (!chartCanvas.value) return;
  
  if (chartInstance) {
    chartInstance.destroy();
  }
  
  const ctx = chartCanvas.value.getContext('2d');
  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: currentChartData.value.map(item => item.date),
      datasets: [
        {
          label: 'Revenue (৳)',
          data: currentChartData.value.map(item => item.revenue),
          borderColor: '#4f46e5',
          backgroundColor: 'rgba(79, 70, 229, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#4f46e5',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (context) => `Revenue: ৳${context.raw.toLocaleString()}`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: '#e2e8f0' },
          ticks: { callback: (value) => `৳${value.toLocaleString()}` }
        },
        x: { grid: { display: false } }
      }
    }
  });
};

const changeChartPeriod = (period) => {
  chartPeriod.value = period;
  setTimeout(() => initChart(), 100);
};

const handleDateRangeChange = () => {
  // Refresh data based on date range
  console.log('Date range changed:', dateRange.value);
};

const navigateTo = (route) => {
  if (route) router.push(route);
};

const goToProduct = (id) => {
  router.push(`/products/${id}`);
};

const goToSale = (id) => {
  router.push(`/sales/${id}`);
};

const goToPurchase = (id) => {
  router.push(`/purchases/${id}`);
};

// Lifecycle
onMounted(() => {
  setTimeout(() => initChart(), 500);
});
</script>

<style scoped>
@keyframes pulse-slow {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.05); }
}

@keyframes pulse-slower {
  0%, 100% { opacity: 0.2; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(1.1); }
}

.animate-pulse-slow {
  animation: pulse-slow 8s ease-in-out infinite;
}

.animate-pulse-slower {
  animation: pulse-slower 12s ease-in-out infinite;
}

.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(99, 102, 241, 0.3) #e2e8f0;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #e2e8f0;
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #6366f1, #3b82f6);
  border-radius: 3px;
}
</style>