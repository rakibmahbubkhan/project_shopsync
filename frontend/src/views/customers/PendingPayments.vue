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
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">
                  Pending Payments
                </h1>
                <p class="text-sm text-slate-500 mt-0.5">Customers with outstanding balances</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="bg-red-100 text-red-700 px-4 py-2 rounded-xl font-semibold">
                Total Due: ৳{{ formatNumber(totalDue) }}
              </div>
              <button @click="refreshData" class="p-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Customers with Due</p>
              <p class="text-2xl font-bold text-slate-800">{{ pendingCustomers.length }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Total Invoices Due</p>
              <p class="text-2xl font-bold text-slate-800">{{ totalInvoices }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Average Due</p>
              <p class="text-2xl font-bold text-slate-800">৳{{ formatNumber(averageDue) }}</p>
            </div>
            <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Highest Due</p>
              <p class="text-2xl font-bold text-slate-800">৳{{ formatNumber(highestDue) }}</p>
            </div>
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Search Bar -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search by customer name, email, or mobile..." 
              class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
            >
          </div>
          <select v-model="sortBy" class="px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 outline-none bg-white">
            <option value="name">Sort by Name</option>
            <option value="due">Sort by Due Amount</option>
            <option value="invoices">Sort by Invoices</option>
          </select>
        </div>
      </div>

      <!-- Pending Payments Table -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
              <tr>
                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Email</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Pending Invoices</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Total Due</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="customer in sortedAndFilteredCustomers" :key="customer.id" class="hover:bg-slate-50/50 transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-100 to-orange-100 flex items-center justify-center text-red-700 font-semibold">
                      {{ getInitials(customer.name) }}
                    </div>
                    <div>
                      <div class="font-semibold text-slate-800">{{ customer.name }}</div>
                      <div class="text-sm text-slate-500">{{ customer.contact_person || 'No contact person' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-1 text-sm">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>{{ customer.mobile_number || 'N/A' }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-1 text-sm">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>{{ customer.email || 'N/A' }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <button 
                    @click="showInvoicesModal(customer)" 
                    class="bg-orange-100 text-orange-700 px-3 py-1.5 rounded-full text-xs font-medium hover:bg-orange-200 transition-colors inline-flex items-center gap-1"
                  >
                    {{ customer.sales?.length || 0 }} Invoices
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="font-bold text-red-600 text-lg">৳{{ formatNumber(calculateTotalDue(customer.sales)) }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <button 
                      @click="showInvoicesModal(customer)" 
                      class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors" 
                      title="View Invoices"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </button>
                    <button 
                      @click="collectPayment(customer)" 
                      class="p-2 text-green-500 hover:bg-green-50 rounded-lg transition-colors" 
                      title="Collect Payment"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="sortedAndFilteredCustomers.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-slate-700 mb-1">No pending payments</h3>
                    <p class="text-sm text-slate-400">All customer payments are up to date</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Invoices Modal -->
    <div v-if="showInvoicesModalFlag" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="closeInvoicesModal">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-y-auto animate-slide-up">
        <div class="sticky top-0 bg-white border-b border-slate-100 p-5 flex justify-between items-center">
          <div>
            <h2 class="text-xl font-bold text-slate-800">Customer Invoices</h2>
            <p class="text-sm text-slate-500">
              {{ selectedCustomerForInvoices?.name }} | 
              Total Due: <span class="text-red-600 font-semibold">৳{{ formatNumber(selectedCustomerDue) }}</span>
            </p>
          </div>
          <button @click="closeInvoicesModal" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="p-6">
          <!-- Invoices Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 rounded-xl">
                <tr>
                  <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Invoice #</th>
                  <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Date</th>
                  <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Items</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Total Amount</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Paid Amount</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Due Amount</th>
                  <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="sale in selectedCustomerSales" :key="sale.id" class="hover:bg-slate-50/50 transition-colors">
                  <td class="px-4 py-3">
                    <span class="font-mono font-semibold text-slate-700">#{{ sale.id }}</span>
                  </td>
                  <td class="px-4 py-3 text-sm text-slate-600">
                    {{ formatDate(sale.sale_date) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-slate-600">
                    {{ sale.items?.length || 0 }} items
                  </td>
                  <td class="px-4 py-3 text-right font-semibold text-slate-800">
                    ৳{{ formatNumber(sale.total_amount) }}
                  </td>
                  <td class="px-4 py-3 text-right text-green-600">
                    ৳{{ formatNumber(sale.paid_amount || 0) }}
                  </td>
                  <td class="px-4 py-3 text-right font-bold text-red-600">
                    ৳{{ formatNumber((sale.total_amount || 0) - (sale.paid_amount || 0)) }}
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span 
                      :class="{
                        'bg-yellow-100 text-yellow-700': sale.payment_status === 'pending',
                        'bg-blue-100 text-blue-700': sale.payment_status === 'partial',
                        'bg-red-100 text-red-700': sale.payment_status === 'unpaid',
                        'bg-green-100 text-green-700': sale.payment_status === 'paid'
                      }" 
                      class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                    >
                      {{ sale.payment_status || 'pending' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <router-link 
                        :to="`/sales/${sale.id}`" 
                        class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors" 
                        title="View Details"
                        target="_blank"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </router-link>
                      <button 
                        v-if="(sale.total_amount || 0) > (sale.paid_amount || 0)"
                        @click="collectPaymentForSale(sale)" 
                        class="p-1.5 text-green-500 hover:bg-green-50 rounded-lg transition-colors" 
                        title="Collect Payment"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                      </button>
                      <button 
                        @click="downloadInvoice(sale.id)" 
                        class="p-1.5 text-purple-500 hover:bg-purple-50 rounded-lg transition-colors" 
                        title="Download Invoice"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot class="bg-slate-50 border-t border-slate-200">
                <tr>
                  <td colspan="5" class="px-4 py-3 text-right font-semibold text-slate-700">Total Due:</td>
                  <td class="px-4 py-3 text-right font-bold text-red-600 text-lg">
                    ৳{{ formatNumber(selectedCustomerDue) }}
                  </td>
                  <td colspan="2"></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Quick Payment Section -->
          <div v-if="selectedCustomerDue > 0" class="mt-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-100">
            <h3 class="font-semibold text-green-800 mb-3 flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Quick Payment Collection
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Payment Amount</label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">৳</span>
                  <input 
                    type="number" 
                    v-model="quickPaymentAmount" 
                    :max="selectedCustomerDue"
                    min="0"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-slate-200 rounded-lg focus:border-green-300 focus:ring-2 focus:ring-green-100 outline-none transition-all"
                    placeholder="Enter amount"
                  >
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Payment Method</label>
                <select v-model="quickPaymentMethod" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:border-green-300 outline-none bg-white">
                  <option value="cash">Cash</option>
                  <option value="card">Card</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="mobile_banking">Mobile Banking</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Reference Number (Optional)</label>
                <input 
                  type="text" 
                  v-model="quickPaymentReference" 
                  class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:border-green-300 focus:ring-2 focus:ring-green-100 outline-none transition-all"
                  placeholder="Transaction ID / Cheque No"
                >
              </div>
              <div class="flex items-end">
                <button 
                  @click="processQuickPayment" 
                  :disabled="quickPaymentAmount <= 0 || quickPaymentAmount > selectedCustomerDue || processingPayment"
                  class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white py-2 rounded-lg font-medium transition-all disabled:from-slate-300 disabled:to-slate-400 disabled:cursor-not-allowed"
                >
                  <span v-if="!processingPayment">Process Payment</span>
                  <span v-else class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Modal (for individual sale) -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showPaymentModal = false">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md animate-slide-up">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
          <div>
            <h2 class="text-xl font-bold text-slate-800">Collect Payment</h2>
            <p class="text-sm text-slate-500">Customer: {{ selectedCustomer?.name }}</p>
            <p class="text-xs text-slate-400" v-if="selectedSaleForPayment">Invoice #{{ selectedSaleForPayment.id }}</p>
          </div>
          <button @click="closePaymentModal" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="p-5 space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Total Due</label>
            <div class="text-2xl font-bold text-red-600">৳{{ formatNumber(selectedCustomerDue) }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Amount</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">৳</span>
              <input 
                type="number" 
                v-model="paymentAmount" 
                :max="selectedCustomerDue"
                min="0"
                step="0.01"
                class="w-full pl-8 pr-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
                placeholder="Enter amount"
              >
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Method</label>
            <select v-model="paymentMethod" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 outline-none bg-white">
              <option value="cash">Cash</option>
              <option value="card">Card</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="mobile_banking">Mobile Banking</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Reference Number (Optional)</label>
            <input 
              type="text" 
              v-model="paymentReference" 
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
              placeholder="Transaction ID / Cheque No"
            >
          </div>
          <div class="pt-2">
            <button 
              @click="processPayment" 
              :disabled="paymentAmount <= 0 || paymentAmount > selectedCustomerDue || processingPayment"
              class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white py-3 rounded-xl font-medium transition-all disabled:from-slate-300 disabled:to-slate-400 disabled:cursor-not-allowed"
            >
              <span v-if="!processingPayment">Process Payment</span>
              <span v-else class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black/40 backdrop-blur-md flex items-center justify-center z-50">
      <div class="bg-white p-8 rounded-2xl shadow-2xl text-center animate-slide-up">
        <div class="relative">
          <div class="w-16 h-16 mx-auto">
            <div class="absolute inset-0 rounded-full border-4 border-purple-200"></div>
            <div class="absolute inset-0 rounded-full border-4 border-purple-600 border-t-transparent animate-spin"></div>
          </div>
        </div>
        <p class="mt-4 text-lg font-semibold text-slate-800">{{ loadingMessage }}</p>
        <p class="text-sm text-slate-500 mt-1">Please wait</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from "@/api/axios";

const router = useRouter();
const pendingCustomers = ref([]);
const loading = ref(false);
const processingPayment = ref(false);
const loadingMessage = ref('Loading...');
const searchQuery = ref('');
const sortBy = ref('name');

// Invoices Modal
const showInvoicesModalFlag = ref(false);
const selectedCustomerForInvoices = ref(null);
const selectedCustomerSales = ref([]);

// Payment Modal
const showPaymentModal = ref(false);
const selectedCustomer = ref(null);
const selectedSaleForPayment = ref(null);
const paymentAmount = ref(0);
const paymentMethod = ref('cash');
const paymentReference = ref('');

// Quick Payment in Invoices Modal
const quickPaymentAmount = ref(0);
const quickPaymentMethod = ref('cash');
const quickPaymentReference = ref('');

const loadPendingPayments = async () => {
  loading.value = true;
  loadingMessage.value = 'Loading pending payments...';
  try {
    const res = await api.get('/customers/pending-payments');
    pendingCustomers.value = res.data;
  } catch (error) {
    console.error("Error loading pending payments:", error);
  } finally {
    loading.value = false;
  }
};

const refreshData = () => {
  loadPendingPayments();
};

const calculateTotalDue = (sales) => {
  if (!sales || sales.length === 0) return 0;
  return sales.reduce((sum, sale) => {
    const due = (sale.total_amount || 0) - (sale.paid_amount || 0);
    return sum + Math.max(0, due);
  }, 0);
};

const formatNumber = (value) => {
  if (value === null || value === undefined) return '0.00';
  return parseFloat(value).toFixed(2);
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-BD', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const showInvoicesModal = (customer) => {
  selectedCustomerForInvoices.value = customer;
  selectedCustomerSales.value = customer.sales || [];
  quickPaymentAmount.value = calculateTotalDue(customer.sales);
  showInvoicesModalFlag.value = true;
};

const closeInvoicesModal = () => {
  showInvoicesModalFlag.value = false;
  selectedCustomerForInvoices.value = null;
  selectedCustomerSales.value = [];
  quickPaymentAmount.value = 0;
  quickPaymentReference.value = '';
};

const collectPayment = (customer) => {
  selectedCustomer.value = customer;
  selectedSaleForPayment.value = null;
  paymentAmount.value = calculateTotalDue(customer.sales);
  paymentReference.value = '';
  showPaymentModal.value = true;
};

const collectPaymentForSale = (sale) => {
  selectedCustomer.value = pendingCustomers.value.find(c => 
    c.id === sale.customer_id
  );
  selectedSaleForPayment.value = sale;
  paymentAmount.value = (sale.total_amount || 0) - (sale.paid_amount || 0);
  paymentReference.value = '';
  showPaymentModal.value = true;
};

const closePaymentModal = () => {
  showPaymentModal.value = false;
  selectedCustomer.value = null;
  selectedSaleForPayment.value = null;
  paymentAmount.value = 0;
  paymentReference.value = '';
};

const processPayment = async () => {
  if (paymentAmount.value <= 0 || paymentAmount.value > selectedCustomerDue.value) {
    alert('Invalid payment amount');
    return;
  }
  
  processingPayment.value = true;
  loadingMessage.value = 'Processing payment...';
  
  try {
    if (selectedSaleForPayment.value) {
      // Single sale payment
      const response = await api.post('/payments', {
        sale_id: selectedSaleForPayment.value.id,
        amount: paymentAmount.value,
        payment_method: paymentMethod.value,
        reference_number: paymentReference.value || null,
        notes: `Payment collected for invoice #${selectedSaleForPayment.value.id}`
      });
      
      if (response.data.success) {
        alert(`Payment of ৳${formatNumber(paymentAmount.value)} collected successfully for invoice #${selectedSaleForPayment.value.id}`);
        closePaymentModal();
        await loadPendingPayments();
      } else {
        alert(response.data.message);
      }
    } else if (selectedCustomer.value) {
      // Bulk payment for all pending invoices
      const saleIds = selectedCustomer.value.sales.map(s => s.id);
      const response = await api.post('/payments/bulk', {
        customer_id: selectedCustomer.value.id,
        sale_ids: saleIds,
        amount: paymentAmount.value,
        payment_method: paymentMethod.value,
        reference_number: paymentReference.value || null,
        notes: `Bulk payment collected for customer ${selectedCustomer.value.name}`
      });
      
      if (response.data.success) {
        alert(`Payment of ৳${formatNumber(paymentAmount.value)} collected successfully from ${selectedCustomer.value.name}`);
        closePaymentModal();
        await loadPendingPayments();
      } else {
        alert(response.data.message);
      }
    }
  } catch (error) {
    console.error('Payment failed:', error);
    alert(error.response?.data?.message || 'Payment processing failed');
  } finally {
    processingPayment.value = false;
  }
};

const processQuickPayment = async () => {
  if (quickPaymentAmount.value <= 0 || quickPaymentAmount.value > selectedCustomerDue.value) {
    alert('Invalid payment amount');
    return;
  }
  
  processingPayment.value = true;
  loadingMessage.value = 'Processing payment...';
  
  try {
    const saleIds = selectedCustomerSales.value.map(s => s.id);
    const response = await api.post('/payments/bulk', {
      customer_id: selectedCustomerForInvoices.value.id,
      sale_ids: saleIds,
      amount: quickPaymentAmount.value,
      payment_method: quickPaymentMethod.value,
      reference_number: quickPaymentReference.value || null,
      notes: `Quick payment collected for customer ${selectedCustomerForInvoices.value.name}`
    });
    
    if (response.data.success) {
      alert(`Payment of ৳${formatNumber(quickPaymentAmount.value)} collected successfully from ${selectedCustomerForInvoices.value.name}`);
      closeInvoicesModal();
      await loadPendingPayments();
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    console.error('Payment failed:', error);
    alert(error.response?.data?.message || 'Payment processing failed');
  } finally {
    processingPayment.value = false;
  }
};

const downloadInvoice = (saleId) => {
  window.open(`/sales/${saleId}/receipt`, '_blank');
};

// Computed properties for stats
const totalDue = computed(() => {
  return pendingCustomers.value.reduce((sum, customer) => {
    return sum + calculateTotalDue(customer.sales);
  }, 0);
});

const totalInvoices = computed(() => {
  return pendingCustomers.value.reduce((sum, customer) => {
    return sum + (customer.sales?.length || 0);
  }, 0);
});

const averageDue = computed(() => {
  if (pendingCustomers.value.length === 0) return 0;
  return totalDue.value / pendingCustomers.value.length;
});

const highestDue = computed(() => {
  if (pendingCustomers.value.length === 0) return 0;
  return Math.max(...pendingCustomers.value.map(c => calculateTotalDue(c.sales)));
});

const selectedCustomerDue = computed(() => {
  if (selectedCustomerForInvoices.value) {
    return calculateTotalDue(selectedCustomerForInvoices.value.sales);
  }
  if (selectedCustomer.value) {
    if (selectedSaleForPayment.value) {
      return (selectedSaleForPayment.value.total_amount || 0) - (selectedSaleForPayment.value.paid_amount || 0);
    }
    return calculateTotalDue(selectedCustomer.value.sales);
  }
  return 0;
});

// Filtered and sorted customers
const sortedAndFilteredCustomers = computed(() => {
  let filtered = [...pendingCustomers.value];
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(c => 
      c.name?.toLowerCase().includes(query) ||
      c.email?.toLowerCase().includes(query) ||
      c.mobile_number?.includes(query) ||
      c.contact_person?.toLowerCase().includes(query)
    );
  }
  
  if (sortBy.value === 'name') {
    filtered.sort((a, b) => a.name.localeCompare(b.name));
  } else if (sortBy.value === 'due') {
    filtered.sort((a, b) => calculateTotalDue(b.sales) - calculateTotalDue(a.sales));
  } else if (sortBy.value === 'invoices') {
    filtered.sort((a, b) => (b.sales?.length || 0) - (a.sales?.length || 0));
  }
  
  return filtered;
});

onMounted(() => {
  loadPendingPayments();
});
</script>

<style scoped>
@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.animate-slide-up {
  animation: slide-up 0.25s ease-out;
}

/* Custom scrollbar for modal */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>