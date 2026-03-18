<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 p-4 md:p-6 lg:p-8">
    <!-- Animated Background Elements (keep as is) -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-indigo-400/20 rounded-full blur-3xl animate-pulse-slow"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-400/20 rounded-full blur-3xl animate-pulse-slower"></div>
    </div>

    <div class="relative space-y-6 md:space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 backdrop-blur-xl bg-white/70 p-4 md:p-6 rounded-2xl shadow-lg border border-white/50">
        <div>
          <h1 class="text-3xl md:text-4xl font-black bg-gradient-to-r from-slate-800 to-indigo-900 bg-clip-text text-transparent">
            Dashboard Overview
          </h1>
          <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            {{ currentDateRange }}
          </p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
          <!-- Date Range Selector -->
          <div class="relative group">
            <select 
              v-model="dateRange" 
              @change="handleDateRangeChange"
              class="appearance-none bg-white/80 backdrop-blur-sm border border-slate-200 rounded-xl px-4 py-2.5 pr-10 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 cursor-pointer hover:bg-white transition-all"
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
          
          <!-- Live Analytics Badge -->
          <div class="relative group">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-blue-600 rounded-xl blur group-hover:blur-md transition-all opacity-75"></div>
            <span class="relative flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold">
              <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
              </span>
              Live Analytics
            </span>
          </div>
        </div>
      </div>

      <!-- Main Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div 
          v-for="card in mainStatsCards" 
          :key="card.label" 
          @click="handleStatCardClick(card.route)"
          class="group relative cursor-pointer transform transition-all duration-300 hover:scale-105 hover:-translate-y-1"
        >
          <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-blue-600 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 blur-xl"></div>
          
          <div class="relative bg-white/90 backdrop-blur-xl rounded-2xl p-6 shadow-xl border border-white/50 transition-all duration-300 group-hover:shadow-2xl group-hover:bg-white h-full">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">{{ card.label }}</p>
                <p class="text-3xl font-black" :class="card.color">
                  {{ card.prefix }}{{ formatNumber(card.value) }}
                </p>
              </div>
              <div class="w-8 h-8 rounded-full bg-slate-100 group-hover:bg-indigo-100 flex items-center justify-center transition-all duration-300 group-hover:scale-110">
                <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </div>
            </div>
            <p class="text-xs text-slate-400 mt-3 flex items-center gap-1">
              <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
              {{ card.subtext }}
            </p>
          </div>
        </div>
      </div>

      <!-- Secondary Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6">
        <div 
          v-for="card in secondaryStatsCards" 
          :key="card.label" 
          @click="handleStatCardClick(card.route)"
          class="group cursor-pointer transform transition-all duration-300 hover:scale-105"
        >
          <div class="relative bg-white/80 backdrop-blur-sm rounded-xl p-5 shadow-lg border border-white/50 hover:shadow-xl transition-all overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-blue-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
            
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm font-medium text-slate-500">{{ card.label }}</p>
                <p class="text-2xl font-bold mt-1" :class="card.color">
                  <span v-if="card.prefix">{{ card.prefix }}</span>{{ formatNumber(card.value) }}
                </p>
              </div>
              <div class="w-8 h-8 rounded-full bg-slate-100 group-hover:bg-indigo-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid: Chart + Stock Alerts -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
        <!-- Chart Section -->
        <div class="lg:col-span-2 bg-white/90 backdrop-blur-xl p-6 rounded-2xl shadow-xl border border-white/50">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
              <h3 class="text-xl font-bold bg-gradient-to-r from-slate-800 to-indigo-900 bg-clip-text text-transparent">Revenue Trend</h3>
              <p class="text-xs text-slate-400 mt-1">{{ chartPeriodLabel }}</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
              <!-- Chart Period Toggle -->
              <div class="flex bg-slate-100/80 backdrop-blur-sm rounded-xl p-1 border border-white/50">
                <button 
                  v-for="period in chartPeriods" 
                  :key="period.value"
                  @click="changeChartPeriod(period.value)"
                  class="text-xs px-3 py-2 rounded-lg font-medium transition-all duration-300"
                  :class="chartPeriod === period.value 
                    ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg scale-105' 
                    : 'text-slate-600 hover:text-indigo-600 hover:bg-white/50'"
                >
                  {{ period.label }}
                </button>
              </div>
              
              <!-- Action Buttons -->
              <button 
                @click="refreshData" 
                class="p-2 rounded-xl bg-slate-100/80 backdrop-blur-sm hover:bg-white text-slate-600 hover:text-indigo-600 transition-all border border-white/50"
                :disabled="refreshing"
              >
                <svg class="w-4 h-4" :class="{ 'animate-spin': refreshing }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Chart Component -->
          <div v-if="chartData.length > 0" class="h-64 md:h-80">
            <!-- Replace with your actual chart component -->
            <div class="w-full h-full bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl flex items-center justify-center">
              <p class="text-slate-400">Chart will appear here</p>
            </div>
          </div>
          <div v-else class="h-64 md:h-80 flex items-center justify-center bg-gradient-to-br from-slate-50 to-indigo-50/30 rounded-xl">
            <p class="text-slate-400">No sales data available</p>
          </div>
          
          <!-- Chart Summary -->
          <div v-if="chartData.length > 0" class="mt-6 pt-4 border-t border-slate-200/50 grid grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-slate-50 to-indigo-50/30 p-3 rounded-xl">
              <p class="text-xs text-slate-400 mb-1">Average</p>
              <p class="text-lg font-bold text-slate-800">৳{{ formatNumber(averageRevenue) }}</p>
            </div>
            <div class="bg-gradient-to-br from-slate-50 to-emerald-50/30 p-3 rounded-xl">
              <p class="text-xs text-slate-400 mb-1">Peak</p>
              <p class="text-lg font-bold text-emerald-600">৳{{ formatNumber(peakRevenue) }}</p>
            </div>
            <div class="bg-gradient-to-br from-slate-50 to-blue-50/30 p-3 rounded-xl">
              <p class="text-xs text-slate-400 mb-1">Total</p>
              <p class="text-lg font-bold text-blue-600">৳{{ formatNumber(totalRevenue) }}</p>
            </div>
          </div>
        </div>

        <!-- Stock Alerts Section -->
        <div class="bg-white/90 backdrop-blur-xl p-6 rounded-2xl shadow-xl border border-white/50">
          <h3 class="text-xl font-bold bg-gradient-to-r from-slate-800 to-indigo-900 bg-clip-text text-transparent mb-4 flex justify-between items-center">
            <span>Stock Alerts</span>
            <div class="flex items-center gap-3">
              <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-orange-500 rounded-full blur opacity-50" v-if="lowStock.length > 0"></div>
                <span 
                  class="relative text-xs px-3 py-1.5 rounded-full font-bold"
                  :class="lowStock.length > 0 
                    ? 'bg-gradient-to-r from-red-500 to-orange-500 text-white' 
                    : 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white'"
                >
                  {{ lowStock.length > 0 ? `${lowStock.length} Items Need Attention` : 'All Stock Healthy' }}
                </span>
              </div>
              <button 
                @click="refreshLowStock"
                class="p-1.5 rounded-lg bg-slate-100 hover:bg-white text-slate-600 hover:text-indigo-600 transition-all"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
              </button>
            </div>
          </h3>
          
          <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
            <div 
              v-for="item in lowStock" 
              :key="item.id" 
              @click="goToProduct(item.id)"
              class="group relative cursor-pointer transform transition-all duration-300 hover:scale-[1.02]"
            >
              <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 blur-md"></div>
              
              <div class="relative bg-gradient-to-r from-slate-50 to-white p-4 rounded-xl border border-slate-200 group-hover:border-transparent transition-all">
                <div class="flex items-center justify-between">
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate group-hover:text-red-600">{{ item.name }}</p>
                    <div class="flex items-center gap-2 text-xs text-slate-400 mt-1">
                      <span class="px-2 py-0.5 bg-slate-100 rounded-full">{{ item.category || 'Uncategorized' }}</span>
                      <span>•</span>
                      <span class="font-mono">{{ item.sku || 'N/A' }}</span>
                    </div>
                  </div>
                  <div class="text-right ml-4">
                    <p class="text-lg font-black" :class="item.stock <= 0 ? 'text-red-600' : 'text-orange-600'">
                      {{ formatNumber(item.stock) }}
                    </p>
                    <p class="text-xs text-slate-400">Min: {{ formatNumber(item.alert_quantity) }} pcs</p>
                  </div>
                </div>
                
                <div class="mt-3 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                  <div 
                    class="h-full rounded-full bg-gradient-to-r from-red-500 to-orange-500 transition-all duration-500"
                    :style="{ width: Math.min((item.stock / item.alert_quantity) * 100, 100) + '%' }"
                  ></div>
                </div>
              </div>
            </div>
            
            <div v-if="lowStock.length === 0" class="text-center py-12">
              <div class="relative inline-block">
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full blur-xl opacity-50 animate-pulse"></div>
                <svg class="relative w-20 h-20 text-emerald-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <p class="text-slate-400 font-medium mb-4">All stock levels are healthy</p>
              <button 
                @click="goToProducts"
                class="text-sm bg-gradient-to-r from-indigo-600 to-blue-600 text-white px-6 py-2.5 rounded-xl font-medium hover:shadow-lg hover:scale-105 transition-all"
              >
                View All Products
              </button>
            </div>
          </div>

          <div class="mt-6 pt-4 border-t border-slate-200/50 grid grid-cols-2 gap-3">
            <router-link 
              to="/reports/low-stock"
              class="text-sm text-center bg-gradient-to-r from-indigo-50 to-blue-50 text-indigo-600 hover:from-indigo-100 hover:to-blue-100 px-4 py-2.5 rounded-xl font-medium transition-all hover:scale-105 border border-indigo-100"
            >
              View All Alerts
            </router-link>
            <router-link 
              to="/purchases/new"
              class="text-sm text-center bg-gradient-to-r from-emerald-600 to-teal-600 text-white px-4 py-2.5 rounded-xl font-medium hover:from-emerald-700 hover:to-teal-700 transition-all hover:scale-105 shadow-lg"
            >
              + New Purchase
            </router-link>
          </div>
        </div>
      </div>

      <!-- Recent Activity Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
        <!-- Recent Sales -->
        <div class="bg-white/90 backdrop-blur-xl p-6 rounded-2xl shadow-xl border border-white/50">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-slate-800 to-indigo-900 bg-clip-text text-transparent">Recent Sales</h3>
            <router-link to="/sales" class="group flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800">
              View All
              <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </router-link>
          </div>
          
          <div class="space-y-3">
            <div 
              v-for="sale in recentSales" 
              :key="sale.id"
              @click="goToSale(sale.id)"
              class="group cursor-pointer transform transition-all duration-300 hover:scale-[1.02]"
            >
              <div class="bg-gradient-to-r from-slate-50 to-white p-4 rounded-xl border border-slate-200 group-hover:border-indigo-200 transition-all">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="font-bold text-slate-800 group-hover:text-indigo-600">{{ sale.customer || 'Walk-in Customer' }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ formatDate(sale.date) }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-lg font-black text-indigo-600">৳{{ formatNumber(sale.amount) }}</p>
                    <p class="text-xs text-slate-400">{{ sale.items || 0 }} items</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-if="recentSales.length === 0" class="text-center py-8 text-slate-400 italic">
              No recent sales
            </div>
          </div>
        </div>

        <!-- Recent Purchases -->
        <div class="bg-white/90 backdrop-blur-xl p-6 rounded-2xl shadow-xl border border-white/50">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-slate-800 to-indigo-900 bg-clip-text text-transparent">Recent Purchases</h3>
            <router-link to="/purchases" class="group flex items-center gap-2 text-sm font-medium text-emerald-600 hover:text-emerald-800">
              View All
              <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </router-link>
          </div>
          
          <div class="space-y-3">
            <div 
              v-for="purchase in recentPurchases" 
              :key="purchase.id"
              @click="goToPurchase(purchase.id)"
              class="group cursor-pointer transform transition-all duration-300 hover:scale-[1.02]"
            >
              <div class="bg-gradient-to-r from-slate-50 to-white p-4 rounded-xl border border-slate-200 group-hover:border-emerald-200 transition-all">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="font-bold text-slate-800 group-hover:text-emerald-600">{{ purchase.supplier || 'N/A' }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ formatDate(purchase.date) }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-lg font-black text-emerald-600">৳{{ formatNumber(purchase.amount) }}</p>
                    <p class="text-xs text-slate-400">{{ purchase.items || 0 }} items</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-if="recentPurchases.length === 0" class="text-center py-8 text-slate-400 italic">
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
          class="group relative overflow-hidden rounded-xl bg-gradient-to-r p-[2px] hover:shadow-2xl transition-all duration-300 hover:scale-105"
          :class="action.gradient"
        >
          <div class="relative flex items-center justify-center gap-2 rounded-xl bg-white/90 backdrop-blur-sm px-4 py-4 transition-all group-hover:bg-transparent group-hover:text-white">
            <span class="text-xl">{{ action.icon }}</span>
            <span class="font-bold">{{ action.label }}</span>
          </div>
        </button>
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
          <p class="mt-4 text-slate-700 font-medium text-center">{{ loadingMessage }}</p>
        </div>
      </div>
    </div>

    <!-- Error Toast -->
    <Transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="error" class="fixed bottom-4 right-4 z-50">
        <div class="relative">
          <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-pink-500 rounded-xl blur"></div>
          <div class="relative bg-white/90 backdrop-blur-xl text-red-600 p-4 rounded-xl shadow-2xl border border-white/50 flex items-center gap-3 min-w-[300px]">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium text-red-600">{{ error }}</p>
            </div>
            <button @click="error = null" class="text-red-400 hover:text-red-600 p-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// State with mock data
const loading = ref(false);
const loadingMessage = ref('Loading dashboard data...');
const refreshing = ref(false);
const error = ref(null);
const dateRange = ref('week');
const chartPeriod = ref('week');

// Mock summary data
const summary = ref({ 
  total_revenue: 45250.75,
  total_profit: 12580.50,
  gross_profit: 12580.50,
  total_purchases: 28750.25,
  low_stock_count: 3,
  low_stock_products: 3,
  total_products: 156,
  today_sales: 2450.00,
  monthly_sales: 15250.50,
  total_sales_count: 48
});

// Mock chart data
const chartData = ref([
  { date: '2024-01-01', revenue: 5200 },
  { date: '2024-01-02', revenue: 6100 },
  { date: '2024-01-03', revenue: 4800 },
  { date: '2024-01-04', revenue: 5900 },
  { date: '2024-01-05', revenue: 6700 },
  { date: '2024-01-06', revenue: 7200 },
  { date: '2024-01-07', revenue: 6900 }
]);

// Mock low stock data
const lowStock = ref([
  { id: 1, name: 'Engine Oil Filter', category: 'Auto Parts', sku: 'EOF-001', stock: 5, alert_quantity: 10 },
  { id: 2, name: 'Brake Pads', category: 'Auto Parts', sku: 'BP-002', stock: 3, alert_quantity: 15 },
  { id: 3, name: 'Air Filter', category: 'Auto Parts', sku: 'AF-003', stock: 8, alert_quantity: 20 }
]);

// Mock recent sales
const recentSales = ref([
  { id: 1, customer: 'John Smith', date: '2024-01-07T10:30:00', amount: 1250.75, items: 3 },
  { id: 2, customer: 'Sarah Johnson', date: '2024-01-07T09:15:00', amount: 890.50, items: 2 },
  { id: 3, customer: 'Mike Wilson', date: '2024-01-06T16:45:00', amount: 2340.00, items: 5 },
  { id: 4, customer: 'Emily Brown', date: '2024-01-06T14:20:00', amount: 675.25, items: 1 },
  { id: 5, customer: 'David Lee', date: '2024-01-06T11:10:00', amount: 1890.00, items: 4 }
]);

// Mock recent purchases
const recentPurchases = ref([
  { id: 1, supplier: 'AutoParts Co.', date: '2024-01-06T13:30:00', amount: 3450.00, items: 15 },
  { id: 2, supplier: 'Tire Supply Inc.', date: '2024-01-05T10:45:00', amount: 5670.50, items: 20 },
  { id: 3, supplier: 'Battery World', date: '2024-01-04T09:20:00', amount: 2340.75, items: 12 },
  { id: 4, supplier: 'Filter Masters', date: '2024-01-03T15:15:00', amount: 1890.25, items: 8 }
]);

// Quick Actions
const quickActions = [
  { label: 'New Sale', icon: '➕', handler: () => router.push('/sales/new'), gradient: 'from-indigo-600 to-blue-600' },
  { label: 'New Purchase', icon: '📦', handler: () => router.push('/purchases/new'), gradient: 'from-emerald-600 to-teal-600' },
  { label: 'New Product', icon: '✨', handler: () => router.push('/products/new'), gradient: 'from-purple-600 to-pink-600' },
  { label: 'Generate Report', icon: '📊', handler: () => router.push('/reports?generate=true'), gradient: 'from-orange-600 to-red-600' }
];

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
    label: 'Gross Profit', 
    value: summary.value.total_profit, 
    prefix: '৳', 
    color: 'text-emerald-600',
    route: '/reports/profit-loss',
    subtext: 'After costs'
  },
  { 
    label: 'Today\'s Sales', 
    value: summary.value.today_sales, 
    prefix: '৳', 
    color: 'text-purple-600',
    route: '/sales?period=today',
    subtext: 'Current day'
  },
  { 
    label: 'Low Stock Items', 
    value: summary.value.low_stock_count, 
    prefix: '', 
    color: summary.value.low_stock_count > 0 ? 'text-orange-600' : 'text-slate-600',
    route: '/reports/low-stock',
    subtext: summary.value.low_stock_count > 0 ? 'Need attention' : 'All good'
  },
  { 
    label: 'Total Purchases', 
    value: summary.value.total_purchases, 
    prefix: '৳', 
    color: 'text-blue-600',
    route: '/reports/purchases',
    subtext: 'Last 7 days'
  },
]);

const secondaryStatsCards = computed(() => [
  { 
    label: 'Total Revenue', 
    value: summary.value.total_revenue, 
    prefix: '৳', 
    color: 'text-indigo-600',
    route: '/reports/sales'
  },
  { 
    label: 'Active Products', 
    value: summary.value.total_products, 
    prefix: '', 
    color: 'text-slate-800',
    route: '/products'
  },
  { 
    label: 'Monthly Sales', 
    value: summary.value.monthly_sales, 
    prefix: '৳', 
    color: 'text-emerald-600',
    route: '/reports/sales?period=month'
  },
]);

// Methods
const formatNumber = (value) => {
  if (value === null || value === undefined) return '0';
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
  // Simulate data refresh
  console.log('Date range changed:', dateRange.value);
};

const changeChartPeriod = (period) => {
  chartPeriod.value = period;
};

const refreshData = async () => {
  refreshing.value = true;
  // Simulate refresh
  setTimeout(() => {
    refreshing.value = false;
  }, 1000);
};

const refreshLowStock = async () => {
  // Simulate refresh
  console.log('Refreshing low stock');
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

const goToProducts = () => {
  router.push('/products');
};

// Lifecycle
onMounted(() => {
  // Data is already loaded with mock data
  console.log('Dashboard mounted with mock data');
});
</script>

<style scoped>
/* Keep all your existing styles */
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
  scrollbar-color: rgba(99, 102, 241, 0.3) rgba(226, 232, 240, 0.5);
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(226, 232, 240, 0.5);
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #6366f1, #3b82f6);
  border-radius: 3px;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>