<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50/30">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8">
      
      <!-- Header Section -->
      <div class="mb-6 md:mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-green-200">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Sales Management</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Track and manage all your sales transactions</p>
              </div>
            </div>
            
            <!-- Date Range Filter -->
            <div class="flex gap-2 w-full sm:w-auto">
              <button 
                @click="filterPeriod('today')"
                :class="[
                  'px-3 py-1.5 text-sm rounded-lg transition-all',
                  activeFilter === 'today' ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                ]"
              >
                Today
              </button>
              <button 
                @click="filterPeriod('week')"
                :class="[
                  'px-3 py-1.5 text-sm rounded-lg transition-all',
                  activeFilter === 'week' ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                ]"
              >
                This Week
              </button>
              <button 
                @click="filterPeriod('month')"
                :class="[
                  'px-3 py-1.5 text-sm rounded-lg transition-all',
                  activeFilter === 'month' ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                ]"
              >
                This Month
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Total Sales</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ totalSales }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Total Revenue</p>
              <p class="text-2xl font-bold text-green-600 mt-1">৳ {{ totalRevenue.toFixed(2) }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Total Profit</p>
              <p class="text-2xl font-bold text-purple-600 mt-1">৳ {{ totalProfit.toFixed(2) }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Avg. Order Value</p>
              <p class="text-2xl font-bold text-orange-600 mt-1">৳ {{ avgOrderValue.toFixed(2) }}</p>
            </div>
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Search and Filter Bar -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              v-model="searchQuery"
              @input="handleSearch"
              type="text"
              placeholder="Search by customer name, invoice #, or product..."
              class="w-full pl-10 pr-4 py-2.5 border-2 border-gray-200 rounded-xl focus:border-green-300 focus:ring-4 focus:ring-green-100 outline-none transition-all"
            />
          </div>
          
          <select
            v-model="paymentFilter"
            @change="fetchSales"
            class="px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:border-green-300 focus:ring-4 focus:ring-green-100 outline-none bg-white"
          >
            <option value="">All Payment Methods</option>
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="wallet">Mobile Wallet</option>
          </select>
          
          <button
            @click="refreshData"
            class="px-4 py-2.5 text-gray-600 hover:text-gray-800 border-2 border-gray-200 rounded-xl hover:border-green-300 transition-all flex items-center gap-2 justify-center"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="hidden sm:inline">Refresh</span>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-xl shadow-sm border border-gray-100 p-12">
        <div class="flex flex-col items-center justify-center">
          <div class="relative">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
          </div>
          <p class="mt-4 text-gray-500">Loading sales data...</p>
        </div>
      </div>

      <!-- Content (shown when not loading) -->
      <template v-else>
        <!-- Sales Table (Desktop) -->
        <div class="hidden lg:block bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Invoice #</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Warehouse</th>
                  <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                  <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Profit</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr 
                  v-for="sale in filteredSales" 
                  :key="sale.id" 
                  class="hover:bg-gray-50 transition-colors duration-150"
                >
                  <td class="px-6 py-4">
                    <span class="font-mono text-sm font-semibold text-gray-800">#{{ sale.id }}</span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                      </div>
                      <span class="text-sm text-gray-800">{{ sale.customer?.name || 'Walk-in Customer' }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="text-sm text-gray-600">{{ sale.warehouse?.name }}</span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <span class="text-sm font-semibold text-green-600">৳ {{ Number(sale.total_amount).toFixed(2) }}</span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <span class="text-sm font-medium text-purple-600">৳ {{ Number(sale.gross_profit).toFixed(2) }}</span>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="getPaymentBadgeClass(sale.payment_method)" class="px-2 py-1 text-xs rounded-full">
                      {{ getPaymentMethodLabel(sale.payment_method) }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-1">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <span class="text-sm text-gray-600">{{ formatDate(sale.sale_date) }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <button
                      @click="openSale(sale.id)"
                      class="px-3 py-1.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-sm rounded-lg transition-all shadow-md hover:shadow-lg transform hover:scale-105"
                    >
                      View Details
                    </button>
                  </td>
                </tr>
                
                <!-- Empty State -->
                <tr v-if="filteredSales.length === 0">
                  <td :colspan="8" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                      <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <h3 class="text-lg font-medium text-gray-700 mb-1">No sales found</h3>
                      <p class="text-sm text-gray-400">Try adjusting your search or filters</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden space-y-3">
          <div
            v-for="sale in filteredSales"
            :key="sale.id"
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all"
          >
            <div class="flex justify-between items-start mb-3">
              <div>
                <span class="font-mono text-xs font-semibold text-gray-500">Invoice #{{ sale.id }}</span>
                <h3 class="font-semibold text-gray-800 mt-1">{{ sale.customer?.name || 'Walk-in Customer' }}</h3>
              </div>
              <span :class="getPaymentBadgeClass(sale.payment_method)" class="px-2 py-1 text-xs rounded-full">
                {{ getPaymentMethodLabel(sale.payment_method) }}
              </span>
            </div>
            
            <div class="space-y-2 mb-4">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Warehouse:</span>
                <span class="font-medium text-gray-800">{{ sale.warehouse?.name }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Date:</span>
                <span class="font-medium text-gray-800">{{ formatDate(sale.sale_date) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Total:</span>
                <span class="font-bold text-green-600">৳ {{ Number(sale.total_amount).toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Profit:</span>
                <span class="font-medium text-purple-600">৳ {{ Number(sale.gross_profit).toFixed(2) }}</span>
              </div>
            </div>
            
            <button
              @click="openSale(sale.id)"
              class="w-full py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg transition-all shadow-md"
            >
              View Details
            </button>
          </div>

          <!-- Mobile Empty State -->
          <div v-if="filteredSales.length === 0" class="bg-white border border-gray-200 rounded-xl p-8 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-700 mb-1">No sales found</h3>
            <p class="text-sm text-gray-400">Try adjusting your search or filters</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="meta.last_page > 1" class="mt-6">
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-4 py-3">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
              <div class="text-sm text-gray-600">
                Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} entries
              </div>
              
              <div class="flex gap-2">
                <button
                  @click="changePage(meta.current_page - 1)"
                  :disabled="meta.current_page === 1"
                  class="px-4 py-2 text-gray-600 hover:text-green-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors rounded-lg border border-gray-200 hover:border-green-300"
                >
                  Previous
                </button>
                
                <div class="hidden md:flex gap-1">
                  <button
                    v-for="page in visiblePages"
                    :key="page"
                    @click="changePage(page)"
                    :class="[
                      'px-3 py-2 rounded-lg transition-all',
                      meta.current_page === page
                        ? 'bg-green-600 text-white shadow-md'
                        : 'text-gray-600 hover:bg-gray-100'
                    ]"
                  >
                    {{ page }}
                  </button>
                </div>
                
                <button
                  @click="changePage(meta.current_page + 1)"
                  :disabled="meta.current_page === meta.last_page"
                  class="px-4 py-2 text-gray-600 hover:text-green-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors rounded-lg border border-gray-200 hover:border-green-300"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Sale Details Modal -->
    <SaleDetailsModal
      v-if="selectedSale"
      :sale="selectedSale"
      @close="selectedSale = null"
      @refresh="fetchSales"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'
import SaleDetailsModal from './SaleDetailsModal.vue'

const sales = ref([])
const selectedSale = ref(null)
const loading = ref(false)
const searchQuery = ref('')
const paymentFilter = ref('')
const activeFilter = ref('all')
const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0
})

let searchTimeout = null

// Computed properties for stats
const totalSales = computed(() => sales.value.length)
const totalRevenue = computed(() => 
  sales.value.reduce((sum, sale) => sum + Number(sale.total_amount || 0), 0)
)
const totalProfit = computed(() => 
  sales.value.reduce((sum, sale) => sum + Number(sale.gross_profit || 0), 0)
)
const avgOrderValue = computed(() => 
  totalSales.value > 0 ? totalRevenue.value / totalSales.value : 0
)

// Filtered sales based on search
const filteredSales = computed(() => {
  let filtered = sales.value
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(sale => 
      sale.customer?.name?.toLowerCase().includes(query) ||
      sale.id.toString().includes(query) ||
      sale.items?.some(item => item.product?.name?.toLowerCase().includes(query))
    )
  }
  
  if (paymentFilter.value) {
    filtered = filtered.filter(sale => sale.payment_method === paymentFilter.value)
  }
  
  return filtered
})

// Visible pages for pagination
const visiblePages = computed(() => {
  const current = meta.value.current_page
  const last = meta.value.last_page
  const delta = 2
  const range = []
  
  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i)
  }
  
  if (current - delta > 2) range.unshift('...')
  if (current + delta < last - 1) range.push('...')
  
  range.unshift(1)
  if (last !== 1) range.push(last)
  
  return range
})

// Helper functions
const getPaymentMethodLabel = (method) => {
  const methods = {
    cash: 'Cash',
    card: 'Card',
    wallet: 'Mobile Wallet'
  }
  return methods[method] || method
}

const getPaymentBadgeClass = (method) => {
  const classes = {
    cash: 'bg-green-100 text-green-700',
    card: 'bg-blue-100 text-blue-700',
    wallet: 'bg-purple-100 text-purple-700'
  }
  return classes[method] || 'bg-gray-100 text-gray-700'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}

// Filter by period
const filterPeriod = (period) => {
  activeFilter.value = period
  const now = new Date()
  let startDate = new Date()
  
  switch (period) {
    case 'today':
      startDate.setHours(0, 0, 0, 0)
      break
    case 'week':
      startDate.setDate(now.getDate() - now.getDay())
      startDate.setHours(0, 0, 0, 0)
      break
    case 'month':
      startDate = new Date(now.getFullYear(), now.getMonth(), 1)
      break
    default:
      fetchSales()
      return
  }
  
  fetchSales(startDate.toISOString().split('T')[0])
}

// Handle search with debounce
const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    // Search is handled by computed property
  }, 300)
}

// Fetch sales data
const fetchSales = async (startDate = null) => {
  loading.value = true
  
  try {
    const params = {
      page: meta.value.current_page,
      per_page: meta.value.per_page
    }
    
    if (startDate) {
      params.start_date = startDate
    }
    
    const response = await api.get('/sales', { params })
    sales.value = response.data.data || []
    meta.value = response.data.meta || response.data
  } catch (error) {
    console.error('Error fetching sales:', error)
    sales.value = []
  } finally {
    loading.value = false
  }
}

// Open sale details
const openSale = async (id) => {
  try {
    const response = await api.get(`/sales/${id}`)
    selectedSale.value = response.data.data
  } catch (error) {
    console.error('Error fetching sale details:', error)
    alert('Failed to load sale details')
  }
}

// Change page
const changePage = (page) => {
  if (page > 0 && page <= meta.value.last_page && page !== meta.value.current_page) {
    meta.value.current_page = page
    fetchSales()
  }
}

// Refresh data
const refreshData = () => {
  fetchSales()
}

// Initial fetch
onMounted(() => {
  fetchSales()
})
</script>

<style scoped>
/* Custom animations */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Smooth transitions */
* {
  transition: all 0.2s ease;
}
</style>