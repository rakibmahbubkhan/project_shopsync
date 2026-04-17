<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50/30">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8">
      
      <!-- Header -->
      <div class="mb-6 md:mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
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
            <router-link to="/purchases/create" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-xl font-semibold transition-all shadow-lg shadow-purple-200 flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              New Purchase
            </router-link>
          </div>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- Search -->
          <div class="lg:col-span-2">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Search</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Status</label>
            <select v-model="filters.status" @change="applyFilters" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white">
              <option value="">All Status</option>
              <option value="ordered">📦 Ordered</option>
              <option value="received">✅ Received</option>
              <option value="pending">⏳ Pending</option>
            </select>
          </div>

          <!-- Payment Status Filter -->
          <div>
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Payment Status</label>
            <select v-model="filters.payment_status" @change="applyFilters" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white">
              <option value="">All Payment</option>
              <option value="unpaid">💰 Unpaid</option>
              <option value="partial">💳 Partial</option>
              <option value="paid">✅ Paid</option>
            </select>
          </div>

          <!-- Supplier Filter -->
          <div>
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Supplier</label>
            <select v-model="filters.supplier_id" @change="applyFilters" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white">
              <option value="">All Suppliers</option>
              <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
            </select>
          </div>
        </div>

        <!-- Date Range -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
          <div>
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Date From</label>
            <input type="date" v-model="filters.date_from" @change="applyFilters" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all">
          </div>
          <div>
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Date To</label>
            <input type="date" v-model="filters.date_to" @change="applyFilters" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all">
          </div>
        </div>

        <!-- Filter Actions -->
        <div class="flex justify-end gap-3 mt-4">
          <button @click="resetFilters" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-all">
            Clear Filters
          </button>
          <button @click="applyFilters" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-all">
            Apply Filters
          </button>
        </div>
      </div>

      <!-- Purchases Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
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
              <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-4">
                  <div class="font-semibold text-gray-800">{{ purchase.reference_no }}</div>
                </td>
                <td class="px-4 py-4 text-gray-600">{{ formatDate(purchase.purchase_date) }}</td>
                <td class="px-4 py-4">
                  <div class="font-medium text-gray-800">{{ purchase.supplier?.name }}</div>
                  <div class="text-xs text-gray-400">{{ purchase.supplier?.phone }}</div>
                </td>
                <td class="px-4 py-4 text-gray-600">{{ purchase.warehouse?.name }}</td>
                <td class="px-4 py-4 text-right">
                  <span class="font-semibold text-gray-800">৳{{ formatNumber(purchase.total_amount) }}</span>
                </td>
                <td class="px-4 py-4 text-right">
                  <span class="text-gray-600">৳{{ formatNumber(purchase.paid_amount) }}</span>
                  <div v-if="purchase.total_amount - purchase.paid_amount > 0" class="text-xs text-orange-600">
                    Due: ৳{{ formatNumber(purchase.total_amount - purchase.paid_amount) }}
                  </div>
                </td>
                <td class="px-4 py-4 text-center">
                  <span :class="getStatusBadgeClass(purchase.status)" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold">
                    {{ getStatusLabel(purchase.status) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-center">
                  <span :class="getPaymentStatusBadgeClass(purchase.payment_status)" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold">
                    {{ getPaymentStatusLabel(purchase.payment_status) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <router-link :to="`/purchases/${purchase.id}/edit`" class="text-blue-500 hover:text-blue-700 transition-colors p-1" title="Edit Purchase">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </router-link>
                    
                    <button @click="printPurchase(purchase)" class="text-gray-500 hover:text-gray-700 transition-colors p-1" title="Print Purchase">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                      </svg>
                    </button>

                    <button v-if="purchase.status !== 'received'" @click="receivePurchase(purchase)" class="text-green-500 hover:text-green-700 transition-colors p-1" title="Mark as Received">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>

                    <button @click="confirmDelete(purchase)" class="text-red-500 hover:text-red-700 transition-colors p-1" title="Delete Purchase">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              
              <!-- Empty State -->
              <tr v-if="purchases.length === 0 && !loading">
                <td colspan="9" class="px-4 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-700 mb-1">No purchases found</h3>
                    <p class="text-sm text-gray-400">Create your first purchase order to get started</p>
                    <router-link to="/purchases/create" class="mt-4 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-all">
                      Create Purchase
                    </router-link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="border-t border-gray-200 px-4 py-4 sm:px-6">
          <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-500">
              Showing {{ purchases.length }} of {{ pagination.total }} results
            </div>
            <div class="flex gap-2">
              <button 
                @click="changePage(pagination.current_page - 1)"
                :disabled="!pagination.prev_page_url"
                class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
              >
                Previous
              </button>
              <span class="px-3 py-1 bg-purple-600 text-white rounded-lg">
                {{ pagination.current_page }}
              </span>
              <button 
                @click="changePage(pagination.current_page + 1)"
                :disabled="!pagination.next_page_url"
                class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white p-8 rounded-2xl shadow-2xl text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-purple-600 border-t-transparent mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading purchases...</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'

export default {
  name: 'PurchaseList',
  
  setup() {
    const router = useRouter()
    
    // State
    const loading = ref(false)
    const purchases = ref([])
    const suppliers = ref([])
    let searchTimeout = null
    
    // Filters
    const filters = reactive({
      search: '',
      status: '',
      payment_status: '',
      supplier_id: '',
      date_from: '',
      date_to: ''
    })
    
    // Pagination
    const pagination = reactive({
      current_page: 1,
      total: 0,
      per_page: 15,
      last_page: 1,
      next_page_url: null,
      prev_page_url: null
    })
    
    // Methods
    const formatNumber = (value) => {
      return parseFloat(value || 0).toFixed(2)
    }
    
    const formatDate = (date) => {
      if (!date) return 'N/A'
      return new Date(date).toLocaleDateString('en-BD')
    }
    
    const getStatusBadgeClass = (status) => {
      const classes = {
        ordered: 'bg-yellow-100 text-yellow-800',
        received: 'bg-green-100 text-green-800',
        pending: 'bg-gray-100 text-gray-800'
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
        unpaid: 'bg-red-100 text-red-800',
        partial: 'bg-orange-100 text-orange-800',
        paid: 'bg-green-100 text-green-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }
    
    const getPaymentStatusLabel = (status) => {
      const labels = {
        unpaid: '💰 Unpaid',
        partial: '💳 Partial',
        paid: '✅ Paid'
      }
      return labels[status] || status
    }
    
    const loadPurchases = async () => {
      loading.value = true
      try {
        const params = {
          page: pagination.current_page,
          per_page: pagination.per_page,
          ...filters
        }
        
        Object.keys(params).forEach(key => {
          if (!params[key]) delete params[key]
        })
        
        const response = await api.get('/purchases', { params })
        purchases.value = response.data.data || []
        pagination.current_page = response.data.current_page || 1
        pagination.total = response.data.total || 0
        pagination.last_page = response.data.last_page || 1
        pagination.next_page_url = response.data.next_page_url
        pagination.prev_page_url = response.data.prev_page_url
      } catch (error) {
        console.error('Failed to load purchases:', error)
        alert('Failed to load purchases: ' + (error.response?.data?.message || error.message))
      } finally {
        loading.value = false
      }
    }
    
    const loadSuppliers = async () => {
      try {
        const response = await api.get('/suppliers')
        suppliers.value = response.data.data || response.data
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
    }
    
    const receivePurchase = async (purchase) => {
      if (!confirm(`Mark purchase ${purchase.reference_no} as received? This will update inventory.`)) return
      
      try {
        await api.post(`/purchases/${purchase.id}/receive`)
        alert('Purchase marked as received!')
        loadPurchases()
      } catch (error) {
        console.error('Receive failed:', error)
        alert('Failed to receive purchase: ' + (error.response?.data?.message || error.message))
      }
    }
    
    const confirmDelete = async (purchase) => {
      if (!confirm(`Are you sure you want to delete purchase ${purchase.reference_no}? This action cannot be undone.`)) return
      
      try {
        await api.delete(`/purchases/${purchase.id}`)
        alert('Purchase deleted successfully!')
        loadPurchases()
      } catch (error) {
        console.error('Delete failed:', error)
        alert('Failed to delete purchase: ' + (error.response?.data?.message || error.message))
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
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
            .company-name { font-size: 24px; font-weight: bold; color: #4f46e5; }
            .purchase-info { margin-bottom: 20px; }
            .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            th { background-color: #f5f5f5; }
            .totals { margin-top: 20px; text-align: right; }
            .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
          </style>
        </head>
        <body>
          <div class="header">
            <div class="company-name">ShopSync</div>
            <div>Purchase Order</div>
          </div>
          <div class="purchase-info">
            <div class="info-row"><span><strong>PO Number:</strong> ${purchase.reference_no}</span><span><strong>Date:</strong> ${formatDate(purchase.purchase_date)}</span></div>
            <div class="info-row"><span><strong>Supplier:</strong> ${purchase.supplier?.name}</span><span><strong>Warehouse:</strong> ${purchase.warehouse?.name}</span></div>
            <div class="info-row"><span><strong>Status:</strong> ${getStatusLabel(purchase.status)}</span><span><strong>Payment:</strong> ${getPaymentStatusLabel(purchase.payment_status)}</span></div>
          </div>
          <table><thead><tr><th>Product</th><th>Quantity</th><th>Unit Cost</th><th>Discount</th><th>Tax</th><th>Total</th></tr></thead><tbody>
            ${purchase.items?.map(item => `<tr><td>${item.product?.name}</td><td>${item.quantity}</td><td>৳${formatNumber(item.purchase_price)}</td><td>${item.discount_percent || 0}%</td><td>${item.tax_percent || 0}%</td><td>৳${formatNumber(item.total)}</td>`).join('') || '<tr><td colspan="6">No items found</td></tr>'}
          </tbody></table>
          <div class="totals"><p><strong>Total:</strong> ৳${formatNumber(purchase.total_amount)}</p><p><strong>Paid:</strong> ৳${formatNumber(purchase.paid_amount)}</p><p><strong>Due:</strong> ৳${formatNumber(purchase.total_amount - purchase.paid_amount)}</p></div>
          <div class="footer"><p>Generated on ${new Date().toLocaleString()}</p></div>
        </body>
        </html>
      `)
      printWindow.document.close()
      printWindow.print()
    }
    
    onMounted(async () => {
      await loadSuppliers()
      await loadPurchases()
    })
    
    return {
      loading,
      purchases,
      suppliers,
      filters,
      pagination,
      formatNumber,
      formatDate,
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
.overflow-x-auto {
  scrollbar-width: thin;
}

.fixed {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>