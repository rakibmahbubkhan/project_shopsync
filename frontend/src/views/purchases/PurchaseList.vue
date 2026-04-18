<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50/30">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8">
      
      <!-- Header - Simplified for performance -->
      <div class="mb-6 md:mb-8">
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 transition-all">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-purple-200">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
              <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Purchase Orders</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Manage and track all purchase orders</p>
              </div>
            </div>
            <router-link to="/purchases/create" class="group bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-xl font-semibold transition-all shadow-lg shadow-purple-200 flex items-center gap-2 transform hover:scale-105 active:scale-95">
              <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              New Purchase
            </router-link>
          </div>
        </div>
      </div>

      <!-- Filters Section - Optimized with better UX -->
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6 transition-all">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
          <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Filters</h2>
          <button @click="resetFilters" class="text-sm text-gray-500 hover:text-purple-600 transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Reset All
          </button>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- Search -->
          <div class="lg:col-span-2">
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input 
                type="text" 
                v-model="filters.search"
                @input="debounceSearch"
                placeholder="Search by PO # or supplier..."
                class="w-full pl-10 pr-4 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
              >
            </div>
          </div>

          <!-- Status Filter -->
          <div>
            <select v-model="filters.status" @change="applyFilters" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white appearance-none cursor-pointer">
              <option value="">📊 All Status</option>
              <option value="ordered">📦 Ordered</option>
              <option value="received">✅ Received</option>
              <option value="pending">⏳ Pending</option>
            </select>
          </div>

          <!-- Payment Status Filter -->
          <div>
            <select v-model="filters.payment_status" @change="applyFilters" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white appearance-none cursor-pointer">
              <option value="">💰 All Payment</option>
              <option value="unpaid">⚠️ Unpaid</option>
              <option value="partial">💳 Partial</option>
              <option value="paid">✅ Paid</option>
            </select>
          </div>

          <!-- Supplier Filter -->
          <div>
            <select v-model="filters.supplier_id" @change="applyFilters" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white appearance-none cursor-pointer">
              <option value="">🏭 All Suppliers</option>
              <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
            </select>
          </div>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Sort By</label>
          <select v-model="filters.sort_by" @change="applyFilters" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white">
            <option value="latest">📅 Latest First</option>
            <option value="oldest">📅 Oldest First</option>
            <option value="highest">💰 Highest Amount</option>
            <option value="lowest">💰 Lowest Amount</option>
          </select>
        </div>

        <!-- Date Range - Collapsible on mobile -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
          <div>
            <input type="date" v-model="filters.date_from" @change="applyFilters" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all">
          </div>
          <div>
            <input type="date" v-model="filters.date_to" @change="applyFilters" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all">
          </div>
        </div>
      </div>

      <!-- Purchases Table - Virtual scrolling ready -->
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all">
        <div class="overflow-x-auto" style="scroll-behavior: smooth;">
          <table class="w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200 sticky top-0 z-10">
              <tr>
                <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">PO #</th>
                <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Supplier</th>
                <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Warehouse</th>
                <th class="px-4 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                <th class="px-4 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Paid</th>
                <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment</th>
                <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-gradient-to-r hover:from-purple-50/50 hover:to-transparent transition-all duration-200 group">
                <td class="px-4 py-4">
                  <div class="font-semibold text-gray-800">{{ purchase.reference_no }}</div>
                </td>
                <td class="px-4 py-4 text-gray-600 whitespace-nowrap">{{ formatDate(purchase.purchase_date) }}</td>
                <td class="px-4 py-4">
                  <div class="font-medium text-gray-800">{{ purchase.supplier?.name || 'N/A' }}</div>
                  <div class="text-xs text-gray-400">{{ purchase.supplier?.phone || 'No phone' }}</div>
                </td>
                <td class="px-4 py-4">
                  <span class="text-gray-600">{{ purchase.warehouse?.name || 'N/A' }}</span>
                </td>
                <td class="px-4 py-4 text-right">
                  <span class="font-semibold text-gray-800">৳{{ formatNumber(purchase.total_amount) }}</span>
                </td>
                <td class="px-4 py-4 text-right">
                  <span class="text-gray-600">৳{{ formatNumber(purchase.paid_amount) }}</span>
                  <div v-if="purchase.total_amount - purchase.paid_amount > 0" class="text-xs text-orange-600 font-medium">
                    Due: ৳{{ formatNumber(purchase.total_amount - purchase.paid_amount) }}
                  </div>
                </td>
                <td class="px-4 py-4 text-center">
                  <span :class="getStatusBadgeClass(purchase.status)" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold shadow-sm">
                    {{ getStatusLabel(purchase.status) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-center">
                  <span :class="getPaymentStatusBadgeClass(purchase.payment_status)" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold shadow-sm">
                    {{ getPaymentStatusLabel(purchase.payment_status) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-center">
                  <div class="flex items-center justify-center gap-1">
                    <!-- Edit Button - Disabled when completed -->
                    <router-link 
                      :to="`/purchases/${purchase.id}/edit`"
                      :class="[
                        'p-2 rounded-lg transition-all duration-200',
                        isPurchaseCompleted(purchase) 
                          ? 'bg-gray-100 text-gray-400 cursor-not-allowed opacity-50' 
                          : 'text-blue-500 hover:bg-blue-50 hover:scale-110 active:scale-95'
                      ]"
                      :title="isPurchaseCompleted(purchase) ? 'Cannot edit completed purchase' : 'Edit Purchase'"
                      :event="isPurchaseCompleted(purchase) ? '' : 'click'"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </router-link>
                    
                    <!-- Print Button -->
                    <button @click="printPurchase(purchase)" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:scale-110 active:scale-95 transition-all duration-200" title="Print Purchase">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                      </svg>
                    </button>

                    <!-- Receive Button - Hidden when completed -->
                    <button 
                      v-if="!isPurchaseCompleted(purchase) && purchase.status !== 'received'" 
                      @click="receivePurchase(purchase)" 
                      class="p-2 rounded-lg text-green-500 hover:bg-green-50 hover:scale-110 active:scale-95 transition-all duration-200" 
                      title="Mark as Received"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>

                    <!-- Delete Button -->
                    <button @click="confirmDelete(purchase)" class="p-2 rounded-lg text-red-500 hover:bg-red-50 hover:scale-110 active:scale-95 transition-all duration-200" title="Delete Purchase">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              
              <!-- Empty State with animation -->
              <tr v-if="purchases.length === 0 && !loading">
                <td colspan="9" class="px-4 py-16 text-center">
                  <div class="flex flex-col items-center justify-center animate-fadeIn">
                    <div class="w-24 h-24 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-full flex items-center justify-center mb-4">
                      <svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                      </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700 mb-1">No purchases found</h3>
                    <p class="text-sm text-gray-400 mb-4">Create your first purchase order to get started</p>
                    <router-link to="/purchases/create" class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-2.5 rounded-lg hover:shadow-lg transition-all transform hover:scale-105">
                      Create Purchase
                    </router-link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination - Optimized -->
        <div class="border-t border-gray-200 px-4 py-4 sm:px-6 bg-gray-50/50">
          <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-500">
              Showing <span class="font-semibold text-gray-700">{{ purchases.length }}</span> of 
              <span class="font-semibold text-gray-700">{{ pagination.total }}</span> results
            </div>
            <div class="flex gap-2">
              <button 
                @click="changePage(pagination.current_page - 1)"
                :disabled="!pagination.prev_page_url"
                class="px-4 py-2 border-2 border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all font-medium hover:border-purple-300"
              >
                ← Previous
              </button>
              <span class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-semibold shadow-md">
                {{ pagination.current_page }}
              </span>
              <button 
                @click="changePage(pagination.current_page + 1)"
                :disabled="!pagination.next_page_url"
                class="px-4 py-2 border-2 border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all font-medium hover:border-purple-300"
              >
                Next →
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay - Optimized with better animation -->
    <Transition name="fade">
      <div v-if="loading" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white p-8 rounded-2xl shadow-2xl text-center transform transition-all">
          <div class="relative">
            <div class="animate-spin rounded-full h-16 w-16 border-4 border-purple-200 border-t-purple-600 mx-auto"></div>
            <div class="absolute inset-0 flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
          </div>
          <p class="mt-4 text-gray-600 font-medium">Loading purchases...</p>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script>
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue'
import api from '@/api/axios'

export default {
  name: 'PurchaseList',
  
  setup() {
    // State
    const loading = ref(false)
    const purchases = ref([])
    const suppliers = ref([])
    let searchTimeout = null
    let abortController = null
    
    // Filters
    const filters = reactive({
      search: '',
      status: '',
      payment_status: '',
      supplier_id: '',
      date_from: '',
      date_to: '',
      sort_by: 'latest' // Add this line
    })
    
    // Pagination
    const pagination = reactive({
      current_page: 1,
      total: 0,
      per_page: 10,
      last_page: 1,
      next_page_url: null,
      prev_page_url: null
    })
    
    // Helper Functions
    const formatNumber = (value) => {
      return parseFloat(value || 0).toFixed(2)
    }
    
    const formatDate = (date) => {
      if (!date) return 'N/A'
      return new Date(date).toLocaleDateString('en-BD')
    }
    
    const isPurchaseCompleted = (purchase) => {
      return purchase.status === 'received' && 
             purchase.payment_status === 'paid' && 
             purchase.paid_amount >= purchase.total_amount
    }
    
    const getStatusBadgeClass = (status) => {
      const classes = {
        ordered: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
        received: 'bg-green-100 text-green-800 border border-green-200',
        pending: 'bg-gray-100 text-gray-800 border border-gray-200'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }
    
    const getStatusLabel = (status) => {
      const labels = {
        ordered: '📦 Ordered',
        received: '✅ Received',
        pending: '⏳ Pending'
      }
      return labels[status] || status
    }
    
    const getPaymentStatusBadgeClass = (status) => {
      const classes = {
        unpaid: 'bg-red-100 text-red-800 border border-red-200',
        partial: 'bg-orange-100 text-orange-800 border border-orange-200',
        paid: 'bg-green-100 text-green-800 border border-green-200'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }
    
    const getPaymentStatusLabel = (status) => {
      const labels = {
        unpaid: '⚠️ Unpaid',
        partial: '💳 Partial',
        paid: '✅ Paid'
      }
      return labels[status] || status
    }
    
    // API Calls with AbortController for better performance
    const loadPurchases = async () => {
      // Cancel previous request
      if (abortController) {
        abortController.abort()
      }
      
      abortController = new AbortController()
      loading.value = true
      
      try {
        const params = {
          page: pagination.current_page,
          per_page: pagination.per_page,
          ...filters
        }
        
        // Add sort parameters based on sort_by value
        if (filters.sort_by === 'latest') {
          params.order_by = 'purchase_date'
          params.order_direction = 'desc'
        } else if (filters.sort_by === 'oldest') {
          params.order_by = 'purchase_date'
          params.order_direction = 'asc'
        } else if (filters.sort_by === 'highest') {
          params.order_by = 'total_amount'
          params.order_direction = 'desc'
        } else if (filters.sort_by === 'lowest') {
          params.order_by = 'total_amount'
          params.order_direction = 'asc'
        }
        
        // Remove empty filter values
        Object.keys(params).forEach(key => {
          if (!params[key] && params[key] !== 0) delete params[key]
        })
        
        const response = await api.get('/purchases', { 
          params,
          signal: abortController.signal
        })
        
        purchases.value = response.data.data || []
        pagination.current_page = response.data.current_page || 1
        pagination.total = response.data.total || 0
        pagination.last_page = response.data.last_page || 1
        pagination.next_page_url = response.data.next_page_url
        pagination.prev_page_url = response.data.prev_page_url
      } catch (error) {
        if (error.name !== 'AbortError') {
          console.error('Failed to load purchases:', error)
        }
      } finally {
        loading.value = false
        abortController = null
      }
    }
    
    const loadSuppliers = async () => {
      try {
        const response = await api.get('/suppliers', { params: { per_page: 100 } })
        suppliers.value = response.data.data || response.data || []
      } catch (error) {
        console.error('Failed to load suppliers:', error)
      }
    }
    
    const applyFilters = () => {
      pagination.current_page = 1
      loadPurchases()
    }
    
    const debounceSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        applyFilters()
      }, 500)
    }
    
    const resetFilters = () => {
      filters.search = ''
      filters.status = ''
      filters.payment_status = ''
      filters.supplier_id = ''
      filters.date_from = ''
      filters.date_to = ''
      applyFilters()
    }
    
    const changePage = (page) => {
      if (page < 1 || page > pagination.last_page) return
      pagination.current_page = page
      loadPurchases()
      // Scroll to top smoothly
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
    
    const receivePurchase = async (purchase) => {
      if (!confirm(`Mark purchase ${purchase.reference_no} as received? This will update inventory.`)) return
      
      loading.value = true
      try {
        await api.post(`/purchases/${purchase.id}/receive`)
        // Show success message
        const notification = document.createElement('div')
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-slideIn'
        notification.textContent = '✓ Purchase marked as received!'
        document.body.appendChild(notification)
        setTimeout(() => notification.remove(), 3000)
        
        await loadPurchases()
      } catch (error) {
        console.error('Receive failed:', error)
        alert('Failed to receive purchase: ' + (error.response?.data?.message || error.message))
      } finally {
        loading.value = false
      }
    }
    
    const confirmDelete = async (purchase) => {
      if (!confirm(`Are you sure you want to delete purchase ${purchase.reference_no}? This action cannot be undone.`)) return
      
      loading.value = true
      try {
        await api.delete(`/purchases/${purchase.id}`)
        const notification = document.createElement('div')
        notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-slideIn'
        notification.textContent = '✓ Purchase deleted successfully!'
        document.body.appendChild(notification)
        setTimeout(() => notification.remove(), 3000)
        
        await loadPurchases()
      } catch (error) {
        console.error('Delete failed:', error)
        alert('Failed to delete purchase: ' + (error.response?.data?.message || error.message))
      } finally {
        loading.value = false
      }
    }
    
    const printPurchase = (purchase) => {
      const printWindow = window.open('', '_blank', 'width=800,height=600')
      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>Purchase Order - ${purchase.reference_no}</title>
          <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 20px; background: #fff; }
            .container { max-width: 1000px; margin: 0 auto; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #4f46e5; padding-bottom: 20px; }
            .company-name { font-size: 28px; font-weight: bold; color: #4f46e5; margin-bottom: 5px; }
            .title { font-size: 20px; color: #666; }
            .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 8px; }
            .info-row { display: flex; justify-content: space-between; padding: 5px 0; }
            .info-label { font-weight: bold; color: #555; }
            .info-value { color: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600; }
            td { color: #333; }
            .totals { margin-top: 20px; text-align: right; padding: 20px; background: #f8f9fa; border-radius: 8px; }
            .totals p { margin: 5px 0; font-size: 16px; }
            .totals strong { color: #4f46e5; }
            .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #ddd; padding-top: 20px; }
            @media print {
              body { padding: 0; }
              .no-print { display: none; }
            }
          </style>
        </head>
        <body>
          <div class="container">
            <div class="header">
              <div class="company-name">ShopSync</div>
              <div class="title">Purchase Order</div>
            </div>
            <div class="info-grid">
              <div><div class="info-row"><span class="info-label">PO Number:</span><span class="info-value">${purchase.reference_no}</span></div>
              <div class="info-row"><span class="info-label">Date:</span><span class="info-value">${formatDate(purchase.purchase_date)}</span></div>
              <div class="info-row"><span class="info-label">Status:</span><span class="info-value">${getStatusLabel(purchase.status)}</span></div></div>
              <div><div class="info-row"><span class="info-label">Supplier:</span><span class="info-value">${purchase.supplier?.name || 'N/A'}</span></div>
              <div class="info-row"><span class="info-label">Warehouse:</span><span class="info-value">${purchase.warehouse?.name || 'N/A'}</span></div>
              <div class="info-row"><span class="info-label">Payment:</span><span class="info-value">${getPaymentStatusLabel(purchase.payment_status)}</span></div></div>
            </div>
            <table>
              <thead>
                <tr><th>Product</th><th>Quantity</th><th>Unit Cost</th><th>Discount</th><th>Tax</th><th>Total</th></tr>
              </thead>
              <tbody>
                ${purchase.items?.map(item => `<tr><td>${item.product?.name || 'N/A'}</td><td>${item.quantity}</td><td>৳${formatNumber(item.purchase_price)}</td><td>${item.discount_percent || 0}%</td><td>${item.tax_percent || 0}%</td><td>৳${formatNumber(item.total)}</td></tr>`).join('') || '<tr><td colspan="6" style="text-align:center">No items found</td></tr>'}
              </tbody>
            </table>
            <div class="totals">
              <p><strong>Total Amount:</strong> ৳${formatNumber(purchase.total_amount)}</p>
              <p><strong>Paid Amount:</strong> ৳${formatNumber(purchase.paid_amount)}</p>
              <p><strong>Due Amount:</strong> ৳${formatNumber(purchase.total_amount - purchase.paid_amount)}</p>
            </div>
            <div class="footer">
              <p>Generated on ${new Date().toLocaleString()}</p>
              <p>Thank you for your business!</p>
            </div>
          </div>
        </body>
        </html>
      `)
      printWindow.document.close()
      printWindow.print()
    }
    
    // Lifecycle
    onMounted(async () => {
      await loadSuppliers()
      await loadPurchases()
    })
    
    onBeforeUnmount(() => {
      if (searchTimeout) clearTimeout(searchTimeout)
      if (abortController) abortController.abort()
    })
    
    return {
      loading,
      purchases,
      suppliers,
      filters,
      pagination,
      formatNumber,
      formatDate,
      isPurchaseCompleted,
      getStatusBadgeClass,
      getStatusLabel,
      getPaymentStatusBadgeClass,
      getPaymentStatusLabel,
      applyFilters,
      debounceSearch,
      resetFilters,
      changePage,
      receivePurchase,
      confirmDelete,
      printPurchase
    }
  }
}
</script>

<style scoped>
/* Smooth animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}

.animate-slideIn {
  animation: slideIn 0.3s ease-out;
}

/* Optimized transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Better scrollbar */
.overflow-x-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f1f5f9;
}

.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Performance optimizations */
.group,
button,
a {
  will-change: transform;
  transform: translateZ(0);
}

/* Backdrop blur performance */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Sticky header */
.sticky {
  position: sticky;
  top: 0;
  z-index: 10;
}
</style>