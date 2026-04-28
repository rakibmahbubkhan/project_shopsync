<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/30">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8">
      
      <!-- Header Section -->
      <div class="mb-6 md:mb-8">
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg shadow-slate-200/50 border border-white/50 p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg shadow-red-200">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 4V9a2 2 0 00-2-2h-1" />
                </svg>
              </div>
              <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                  <router-link to="/sales" class="hover:text-red-600 transition-colors">Sales</router-link>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <span class="text-slate-700 font-medium">Returns</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">
                  Sales Returns
                </h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage and track customer returns</p>
              </div>
            </div>
            <div class="flex flex-wrap gap-3">
              <button 
                @click="showSearchModal = true" 
                class="group bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all duration-200 shadow-lg shadow-red-200 flex items-center gap-2 transform hover:scale-[1.02] active:scale-95"
              >
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Return
              </button>
              <button 
                @click="showFullReturnModal = true" 
                class="group bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all duration-200 shadow-lg shadow-purple-200 flex items-center gap-2 transform hover:scale-[1.02] active:scale-95"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Full Invoice Return
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Total Returns</p>
              <p class="text-2xl font-bold text-slate-800">{{ stats.total }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 4V9a2 2 0 00-2-2h-1" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Pending Approval</p>
              <p class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</p>
            </div>
            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Completed</p>
              <p class="text-2xl font-bold text-green-600">{{ stats.completed }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Total Refunded</p>
              <p class="text-2xl font-bold text-red-600">৳{{ formatNumber(stats.totalRefunded) }}</p>
            </div>
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Search and Filter Bar -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input 
              type="text" 
              v-model="filters.search" 
              @input="debouncedSearch"
              placeholder="Search by return ID, sale ID, customer name..." 
              class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl focus:border-red-300 focus:ring-4 focus:ring-red-100 outline-none transition-all"
            >
          </div>
          <select v-model="filters.status" @change="loadReturns" class="px-4 py-2.5 border border-slate-200 rounded-xl focus:border-red-300 outline-none bg-white">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="completed">Completed</option>
            <option value="rejected">Rejected</option>
          </select>
          <select v-model="filters.sort_by" @change="loadReturns" class="px-4 py-2.5 border border-slate-200 rounded-xl focus:border-red-300 outline-none bg-white">
            <option value="latest">Latest First</option>
            <option value="oldest">Oldest First</option>
            <option value="highest">Highest Amount</option>
            <option value="lowest">Lowest Amount</option>
          </select>
          <button @click="resetFilters" class="px-4 py-2.5 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Reset
          </button>
        </div>
        <!-- Date Range -->
        <div class="flex gap-4 mt-3">
          <input type="date" v-model="filters.date_from" @change="loadReturns" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
          <span class="text-slate-400 self-center">to</span>
          <input type="date" v-model="filters.date_to" @change="loadReturns" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
        </div>
      </div>

      <!-- Returns Table -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
              <tr>
                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Return #</th>
                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Sale #</th>
                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Products</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Qty</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Date</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="ret in returns" :key="ret.id" class="hover:bg-slate-50/50 transition-colors group">
                <td class="px-6 py-4">
                  <span class="font-mono font-semibold text-slate-700">#{{ ret.id }}</span>
                </td>
                <td class="px-6 py-4">
                  <button 
                    @click="viewSale(ret.sale_id)" 
                    class="font-mono text-blue-600 hover:text-blue-800 hover:underline transition-colors"
                  >
                    #{{ ret.sale_id }}
                  </button>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-red-100 to-orange-100 flex items-center justify-center text-red-700 font-semibold text-xs flex-shrink-0">
                      {{ getInitials(ret.sale?.customer?.name) }}
                    </div>
                    <div>
                      <span class="text-sm font-medium text-slate-700 block">{{ ret.sale?.customer?.name || 'Walk-in' }}</span>
                      <span class="text-xs text-slate-400">{{ ret.sale?.customer?.mobile_number || '' }}</span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span 
                      v-for="item in ret.items?.slice(0, 2)" 
                      :key="item.id"
                      class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full"
                    >
                      {{ item.product?.name || 'Product' }}
                    </span>
                    <span v-if="ret.items?.length > 2" class="text-xs text-slate-400">
                      +{{ ret.items.length - 2 }} more
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="font-medium text-slate-700">
                    {{ ret.items?.reduce((sum, i) => sum + i.quantity, 0) || 0 }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="font-semibold text-red-600">৳{{ formatNumber(ret.total_amount) }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span :class="getStatusClass(ret.status)" class="px-2.5 py-1 rounded-full text-xs font-semibold capitalize inline-flex items-center gap-1">
                    <span :class="getStatusDotClass(ret.status)" class="w-1.5 h-1.5 rounded-full"></span>
                    {{ ret.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center text-sm text-slate-500 whitespace-nowrap">
                  {{ formatDate(ret.return_date || ret.created_at) }}
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="flex items-center justify-center gap-1">
                    <!-- View Details -->
                    <button 
                      @click="showReturnDetails(ret)" 
                      class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors" 
                      title="View Details"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    
                    <!-- Approve -->
                    <button 
                      v-if="ret.status === 'pending'"
                      @click="approveReturn(ret)" 
                      class="p-2 text-green-500 hover:bg-green-50 rounded-lg transition-colors" 
                      title="Approve Return"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                    
                    <!-- Reject -->
                    <button 
                      v-if="ret.status === 'pending'"
                      @click="rejectReturn(ret)" 
                      class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" 
                      title="Reject Return"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                    
                    <!-- Print Receipt -->
                    <button 
                      @click="printReturnReceipt(ret)" 
                      class="p-2 text-purple-500 hover:bg-purple-50 rounded-lg transition-colors" 
                      title="Print Receipt"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              
              <!-- Empty State -->
              <tr v-if="returns.length === 0 && !loading">
                <td colspan="9" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                      <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 4V9a2 2 0 00-2-2h-1" />
                      </svg>
                    </div>
                    <h3 class="text-lg font-medium text-slate-700 mb-1">No returns found</h3>
                    <p class="text-sm text-slate-400 mb-4">Click "Add New Return" to process a customer return</p>
                    <button 
                      @click="showSearchModal = true" 
                      class="bg-gradient-to-r from-red-600 to-orange-600 text-white px-6 py-2.5 rounded-xl font-medium hover:shadow-lg transition-all"
                    >
                      Add New Return
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > 0" class="border-t border-slate-200 px-4 py-4 sm:px-6 bg-slate-50/50">
          <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-slate-500">
              Showing <span class="font-semibold text-slate-700">{{ returns.length }}</span> of 
              <span class="font-semibold text-slate-700">{{ pagination.total }}</span> results
            </div>
            <div class="flex gap-2">
              <button 
                @click="changePage(pagination.current_page - 1)"
                :disabled="!pagination.prev_page_url"
                class="px-4 py-2 border-2 border-slate-200 rounded-lg hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-all font-medium text-sm"
              >
                ← Previous
              </button>
              <span class="px-4 py-2 bg-gradient-to-r from-red-600 to-orange-600 text-white rounded-lg font-semibold text-sm">
                {{ pagination.current_page }}
              </span>
              <button 
                @click="changePage(pagination.current_page + 1)"
                :disabled="!pagination.next_page_url"
                class="px-4 py-2 border-2 border-slate-200 rounded-lg hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-all font-medium text-sm"
              >
                Next →
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL 1: Search Sale for Return -->
    <!-- ============================================ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showSearchModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showSearchModal = false">
          <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showSearchModal = false"></div>
            
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col">
              <!-- Modal Header -->
              <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <div>
                  <h2 class="text-xl font-bold text-slate-800">Find Sale for Return</h2>
                  <p class="text-sm text-slate-500 mt-1">Search by invoice number or customer name</p>
                </div>
                <button @click="showSearchModal = false" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                  <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <!-- Search Input -->
              <div class="p-6 border-b border-slate-100">
                <div class="relative">
                  <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <input 
                    type="text" 
                    v-model="saleSearchQuery" 
                    @input="searchSales"
                    placeholder="Search by invoice # or customer name..."
                    class="w-full pl-12 pr-4 py-3.5 border-2 border-slate-200 rounded-xl focus:border-red-400 focus:ring-4 focus:ring-red-50 outline-none text-lg transition-all"
                    autofocus
                  >
                </div>
              </div>

              <!-- Results -->
              <div class="flex-1 overflow-y-auto p-6">
                <!-- Loading -->
                <div v-if="searchingSales" class="flex items-center justify-center py-12">
                  <div class="animate-spin rounded-full h-8 w-8 border-4 border-red-200 border-t-red-600"></div>
                  <span class="ml-3 text-slate-500">Searching...</span>
                </div>

                <!-- Results List -->
                <div v-else-if="saleSearchResults.length > 0" class="space-y-3">
                  <div 
                    v-for="sale in saleSearchResults" 
                    :key="sale.id"
                    @click="selectSaleForReturn(sale)"
                    class="p-5 border-2 border-slate-200 rounded-xl hover:border-red-300 hover:bg-red-50/30 cursor-pointer transition-all group"
                  >
                    <div class="flex justify-between items-start">
                      <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                          <span class="font-mono font-bold text-lg text-slate-800">Invoice #{{ sale.id }}</span>
                          <span :class="getPaymentStatusClass(sale.payment_status)" class="px-2 py-0.5 rounded-full text-xs font-medium capitalize">
                            {{ sale.payment_status }}
                          </span>
                        </div>
                        <div class="flex items-center gap-2 mb-1">
                          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                          </svg>
                          <span class="font-medium text-slate-700">{{ sale.customer?.name || 'Walk-in Customer' }}</span>
                          <span class="text-sm text-slate-400">{{ sale.customer?.mobile_number || '' }}</span>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-slate-500">
                          <span>{{ formatDate(sale.sale_date) }}</span>
                          <span>{{ sale.warehouse?.name || 'N/A' }}</span>
                        </div>
                      </div>
                      <div class="text-right ml-4">
                        <p class="text-xl font-bold text-slate-800">৳{{ formatNumber(sale.total_amount) }}</p>
                        <p class="text-sm text-slate-400">{{ sale.items?.length || 0 }} items</p>
                      </div>
                    </div>
                    
                    <!-- Items Preview -->
                    <div class="mt-3 flex flex-wrap gap-1.5">
                      <span 
                        v-for="item in sale.items?.slice(0, 4)" 
                        :key="item.id"
                        class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-full"
                        :class="{ 'bg-red-50 text-red-600': item.available_for_return > 0 }"
                      >
                        {{ item.product_name }} 
                        <span class="font-semibold">({{ item.available_for_return }})</span>
                      </span>
                      <span v-if="sale.items?.length > 4" class="text-xs text-slate-400">
                        +{{ sale.items.length - 4 }} more
                      </span>
                    </div>
                    
                    <div class="mt-3 flex justify-end">
                      <span class="text-xs text-red-500 font-medium group-hover:underline">
                        Click to process return →
                      </span>
                    </div>
                  </div>
                </div>

                <!-- No Results -->
                <div v-else-if="saleSearchQuery && !searchingSales" class="text-center py-12">
                  <svg class="w-20 h-20 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <p class="text-lg text-slate-500 mb-2">No sales found</p>
                  <p class="text-sm text-slate-400">Try a different search term</p>
                </div>

                <!-- Initial State -->
                <div v-else class="text-center py-12">
                  <svg class="w-20 h-20 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <p class="text-lg text-slate-500 mb-2">Search for a sale</p>
                  <p class="text-sm text-slate-400">Enter invoice number or customer name</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ============================================ -->
    <!-- MODAL 2: Full Invoice Return -->
    <!-- ============================================ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showFullReturnModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showFullReturnModal = false">
          <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showFullReturnModal = false"></div>
            
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col">
              <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <div>
                  <h2 class="text-xl font-bold text-slate-800">Full Invoice Return</h2>
                  <p class="text-sm text-slate-500 mt-1">Search and return all items from an invoice</p>
                </div>
                <button @click="showFullReturnModal = false" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                  <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <div class="p-6 border-b border-slate-100">
                <div class="relative">
                  <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <input 
                    type="text" 
                    v-model="fullReturnSearchQuery" 
                    @input="searchSalesForFullReturn"
                    placeholder="Search by invoice # or customer name..."
                    class="w-full pl-12 pr-4 py-3.5 border-2 border-slate-200 rounded-xl focus:border-purple-400 focus:ring-4 focus:ring-purple-50 outline-none text-lg transition-all"
                    autofocus
                  >
                </div>
              </div>

              <div class="flex-1 overflow-y-auto p-6">
                <div v-if="searchingFullReturn" class="flex items-center justify-center py-12">
                  <div class="animate-spin rounded-full h-8 w-8 border-4 border-purple-200 border-t-purple-600"></div>
                  <span class="ml-3 text-slate-500">Searching...</span>
                </div>

                <div v-else-if="fullReturnResults.length > 0" class="space-y-3">
                  <div 
                    v-for="sale in fullReturnResults" 
                    :key="sale.id"
                    class="p-5 border-2 border-slate-200 rounded-xl hover:border-purple-300 hover:bg-purple-50/30 cursor-pointer transition-all"
                  >
                    <div class="flex justify-between items-start">
                      <div>
                        <span class="font-mono font-bold text-lg">#{{ sale.id }}</span>
                        <span class="ml-2 text-sm text-slate-500">{{ sale.customer?.name }}</span>
                      </div>
                      <div class="flex gap-2">
                        <button 
                          @click="processFullReturn(sale)" 
                          class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors"
                        >
                          Return All ({{ sale.items?.length }} items)
                        </button>
                        <button 
                          @click="selectSaleForReturn(sale); showFullReturnModal = false" 
                          class="border-2 border-slate-200 text-slate-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors"
                        >
                          Partial Return
                        </button>
                      </div>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1">
                      <span v-for="item in sale.items" :key="item.id" class="text-xs bg-slate-100 px-2 py-0.5 rounded-full">
                        {{ item.product_name }} ({{ item.available_for_return }})
                      </span>
                    </div>
                  </div>
                </div>

                <div v-else-if="fullReturnSearchQuery && !searchingFullReturn" class="text-center py-12">
                  <p class="text-slate-500">No sales found</p>
                </div>

                <div v-else class="text-center py-12">
                  <p class="text-slate-400">Search for a sale to return all items</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ============================================ -->
    <!-- MODAL 3: Return Details -->
    <!-- ============================================ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="selectedReturn" class="fixed inset-0 z-50 overflow-y-auto" @click.self="selectedReturn = null">
          <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="selectedReturn = null"></div>
            
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[85vh] overflow-y-auto">
              <div class="sticky top-0 bg-white border-b border-slate-200 p-6 flex justify-between items-center rounded-t-2xl">
                <div>
                  <h2 class="text-xl font-bold text-slate-800">Return #{{ selectedReturn.id }} Details</h2>
                  <p class="text-sm text-slate-500">Sale #{{ selectedReturn.sale_id }}</p>
                </div>
                <button @click="selectedReturn = null" class="p-2 hover:bg-slate-100 rounded-lg">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <div class="p-6 space-y-4">
                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-xs text-slate-400 uppercase">Customer</p>
                    <p class="font-semibold">{{ selectedReturn.sale?.customer?.name || 'Walk-in' }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-slate-400 uppercase">Status</p>
                    <span :class="getStatusClass(selectedReturn.status)" class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize">
                      {{ selectedReturn.status }}
                    </span>
                  </div>
                  <div>
                    <p class="text-xs text-slate-400 uppercase">Return Date</p>
                    <p class="font-semibold">{{ formatDate(selectedReturn.return_date) }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-slate-400 uppercase">Total Refund</p>
                    <p class="font-bold text-red-600">৳{{ formatNumber(selectedReturn.total_amount) }}</p>
                  </div>
                  <div class="col-span-2">
                    <p class="text-xs text-slate-400 uppercase">Reason</p>
                    <p class="text-sm">{{ selectedReturn.reason || 'N/A' }}</p>
                  </div>
                </div>

                <!-- Items Table -->
                <div>
                  <h3 class="font-semibold text-slate-700 mb-2">Returned Items</h3>
                  <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                      <tr>
                        <th class="p-2 text-left">Product</th>
                        <th class="p-2 text-center">Qty</th>
                        <th class="p-2 text-right">Price</th>
                        <th class="p-2 text-right">Subtotal</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y">
                      <tr v-for="item in selectedReturn.items" :key="item.id">
                        <td class="p-2">{{ item.product?.name || 'N/A' }}</td>
                        <td class="p-2 text-center">{{ item.quantity }}</td>
                        <td class="p-2 text-right">৳{{ formatNumber(item.selling_price) }}</td>
                        <td class="p-2 text-right font-medium">৳{{ formatNumber(item.subtotal) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Refund Info -->
                <div v-if="selectedReturn.refund?.length" class="bg-slate-50 rounded-xl p-4">
                  <h3 class="font-semibold text-slate-700 mb-2">Refund Details</h3>
                  <div v-for="ref in selectedReturn.refund" :key="ref.id" class="flex justify-between text-sm">
                    <span class="text-slate-500 capitalize">{{ ref.payment_method }}</span>
                    <span class="font-semibold text-green-600">৳{{ formatNumber(ref.amount) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white p-8 rounded-2xl shadow-2xl text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-red-200 border-t-red-600 mx-auto"></div>
        <p class="mt-4 text-slate-600 font-medium">{{ loadingMessage }}</p>
      </div>
    </div>

    <!-- Toast Notification -->
    <Transition name="slide">
      <div v-if="toast.show" :class="toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'" class="fixed top-4 right-4 z-50 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 max-w-md">
        <svg v-if="toast.type === 'success'" class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <svg v-else class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <p>{{ toast.message }}</p>
        <button @click="toast.show = false" class="ml-auto text-white/80 hover:text-white">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/api/axios';

const router = useRouter();

// ==================== STATE ====================
const loading = ref(false);
const loadingMessage = ref('Loading...');
const returns = ref([]);
const selectedReturn = ref(null);

// Search Modal
const showSearchModal = ref(false);
const saleSearchQuery = ref('');
const saleSearchResults = ref([]);
const searchingSales = ref(false);

// Full Return Modal
const showFullReturnModal = ref(false);
const fullReturnSearchQuery = ref('');
const fullReturnResults = ref([]);
const searchingFullReturn = ref(false);

// Filters
const filters = reactive({
  search: '',
  status: '',
  sort_by: 'latest',
  date_from: '',
  date_to: ''
});

// Pagination
const pagination = reactive({
  current_page: 1,
  total: 0,
  per_page: 15,
  last_page: 1,
  next_page_url: null,
  prev_page_url: null
});

// Stats
const stats = reactive({
  total: 0,
  pending: 0,
  completed: 0,
  approved: 0,
  rejected: 0,
  totalRefunded: 0
});

// Toast
const toast = reactive({
  show: false,
  message: '',
  type: 'success'
});

let searchTimeout = null;
let saleSearchTimeout = null;
let fullReturnTimeout = null;

// ==================== COMPUTED ====================
// (none needed currently)

// ==================== METHODS ====================

// --- Helpers ---
const formatNumber = (value) => parseFloat(value || 0).toFixed(2);

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-BD', {
    year: 'numeric', month: 'short', day: 'numeric'
  });
};

const getInitials = (name) => {
  if (!name) return 'W';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getStatusClass = (status) => {
  const map = {
    completed: 'bg-green-100 text-green-700',
    approved: 'bg-blue-100 text-blue-700',
    pending: 'bg-yellow-100 text-yellow-700',
    rejected: 'bg-red-100 text-red-700'
  };
  return map[status] || 'bg-slate-100 text-slate-600';
};

const getStatusDotClass = (status) => {
  const map = {
    completed: 'bg-green-500',
    approved: 'bg-blue-500',
    pending: 'bg-yellow-500',
    rejected: 'bg-red-500'
  };
  return map[status] || 'bg-slate-400';
};

const getPaymentStatusClass = (status) => {
  const map = {
    paid: 'bg-green-100 text-green-700',
    unpaid: 'bg-red-100 text-red-700',
    partial: 'bg-yellow-100 text-yellow-700'
  };
  return map[status] || 'bg-slate-100 text-slate-600';
};

const showToast = (message, type = 'success') => {
  toast.message = message;
  toast.type = type;
  toast.show = true;
  setTimeout(() => { toast.show = false; }, 4000);
};

// --- API Calls ---
const loadReturns = async () => {
  loading.value = true;
  loadingMessage.value = 'Loading returns...';
  
  try {
    const params = {
      page: pagination.current_page,
      per_page: pagination.per_page,
      search: filters.search || undefined,
      status: filters.status || undefined,
      sort_by: filters.sort_by || undefined,
      date_from: filters.date_from || undefined,
      date_to: filters.date_to || undefined
    };

    const { data } = await api.get('/returns', { params });
    
    returns.value = data.data || [];
    pagination.current_page = data.current_page || 1;
    pagination.total = data.total || 0;
    pagination.last_page = data.last_page || 1;
    pagination.next_page_url = data.next_page_url;
    pagination.prev_page_url = data.prev_page_url;

    // Update stats from the response if available
    if (data.stats) {
      stats.total = data.stats.total || returns.value.length;
      stats.pending = data.stats.pending || 0;
      stats.completed = data.stats.completed || 0;
      stats.approved = data.stats.approved || 0;
      stats.rejected = data.stats.rejected || 0;
      stats.totalRefunded = data.stats.total_refunded || 0;
    }
  } catch (error) {
    console.error('Failed to load returns:', error);
    showToast('Failed to load returns', 'error');
  } finally {
    loading.value = false;
  }
};

const searchSales = async () => {
  clearTimeout(saleSearchTimeout);
  
  if (saleSearchQuery.value.length < 2) {
    saleSearchResults.value = [];
    return;
  }
  
  searchingSales.value = true;
  saleSearchTimeout = setTimeout(async () => {
    try {
      const { data } = await api.get('/returns/search-sales', {
        params: { search: saleSearchQuery.value }
      });
      saleSearchResults.value = data || [];
    } catch (error) {
      console.error('Sale search failed:', error);
      saleSearchResults.value = [];
    } finally {
      searchingSales.value = false;
    }
  }, 400);
};

const searchSalesForFullReturn = async () => {
  clearTimeout(fullReturnTimeout);
  
  if (fullReturnSearchQuery.value.length < 2) {
    fullReturnResults.value = [];
    return;
  }
  
  searchingFullReturn.value = true;
  fullReturnTimeout = setTimeout(async () => {
    try {
      const { data } = await api.get('/returns/search-sales', {
        params: { search: fullReturnSearchQuery.value }
      });
      fullReturnResults.value = data || [];
    } catch (error) {
      console.error('Sale search failed:', error);
      fullReturnResults.value = [];
    } finally {
      searchingFullReturn.value = false;
    }
  }, 400);
};

const approveReturn = async (ret) => {
  if (!confirm(`Approve return #${ret.id} for ৳${formatNumber(ret.total_amount)}?`)) return;
  
  loading.value = true;
  loadingMessage.value = 'Approving return...';
  
  try {
    await api.post(`/returns/${ret.id}/approve`);
    showToast(`Return #${ret.id} approved successfully`);
    await loadReturns();
  } catch (error) {
    console.error('Approval failed:', error);
    showToast('Failed to approve return', 'error');
  } finally {
    loading.value = false;
  }
};

const rejectReturn = async (ret) => {
  if (!confirm(`Reject return #${ret.id}?`)) return;
  
  loading.value = true;
  loadingMessage.value = 'Rejecting return...';
  
  try {
    await api.post(`/returns/${ret.id}/reject`);
    showToast(`Return #${ret.id} rejected`);
    await loadReturns();
  } catch (error) {
    console.error('Rejection failed:', error);
    showToast('Failed to reject return', 'error');
  } finally {
    loading.value = false;
  }
};

const processFullReturn = async (sale) => {
  if (!confirm(`Return ALL ${sale.items?.length || 0} items from invoice #${sale.id}?`)) return;
  
  loading.value = true;
  loadingMessage.value = 'Processing full return...';
  
  try {
    const items = sale.items.map(item => ({
      product_id: item.product_id,
      quantity: item.available_for_return,
      discount: 0,
      tax: 0
    }));

    await api.post('/returns', {
      sale_id: sale.id,
      items: items,
      reason: 'Full invoice return',
      refund_method: 'cash'
    });
    
    showToast('Full invoice return processed successfully');
    showFullReturnModal.value = false;
    fullReturnSearchQuery.value = '';
    fullReturnResults.value = [];
    await loadReturns();
  } catch (error) {
    console.error('Full return failed:', error);
    showToast(error.response?.data?.message || 'Failed to process return', 'error');
  } finally {
    loading.value = false;
  }
};

// --- Navigation ---
const selectSaleForReturn = (sale) => {
  showSearchModal.value = false;
  saleSearchQuery.value = '';
  saleSearchResults.value = [];
  router.push({ name: 'return-create', query: { sale_id: sale.id } });
};

const viewSale = (saleId) => {
  router.push(`/sales/${saleId}`);
};

const showReturnDetails = async (ret) => {
  loading.value = true;
  loadingMessage.value = 'Loading return details...';
  
  try {
    const { data } = await api.get(`/returns/${ret.id}`);
    selectedReturn.value = data.data || data;
  } catch (error) {
    console.error('Failed to load return details:', error);
    showToast('Failed to load return details', 'error');
  } finally {
    loading.value = false;
  }
};

const printReturnReceipt = (ret) => {
  // Open return receipt in new tab
  window.open(`/returns/${ret.id}/receipt`, '_blank');
};

const changePage = (page) => {
  if (page < 1 || page > pagination.last_page) return;
  pagination.current_page = page;
  loadReturns();
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    pagination.current_page = 1;
    loadReturns();
  }, 400);
};

const resetFilters = () => {
  filters.search = '';
  filters.status = '';
  filters.sort_by = 'latest';
  filters.date_from = '';
  filters.date_to = '';
  pagination.current_page = 1;
  loadReturns();
};

// ==================== LIFECYCLE ====================
onMounted(() => {
  loadReturns();
});

onBeforeUnmount(() => {
  clearTimeout(searchTimeout);
  clearTimeout(saleSearchTimeout);
  clearTimeout(fullReturnTimeout);
});
</script>

<style scoped>
/* Modal Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.25s ease;
}
.modal-enter-active > div > div:last-child,
.modal-leave-active > div > div:last-child {
  transition: transform 0.25s ease, opacity 0.25s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from > div > div:last-child {
  transform: scale(0.95) translateY(10px);
  opacity: 0;
}
.modal-leave-to > div > div:last-child {
  transform: scale(0.95) translateY(10px);
  opacity: 0;
}

/* Slide Toast Transition */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}
.slide-enter-from {
  transform: translateX(100%);
  opacity: 0;
}
.slide-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

/* Custom scrollbar for modals */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>