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
                  <div class="font-medium text-gray-800">{{ purchase.supplier?.name || getSupplierNameFromId(purchase.supplier_id) }}</div>
                  <div class="text-xs text-gray-400">{{ purchase.supplier?.phone }}</div>
                </td>
                <td class="px-4 py-4 text-gray-600">{{ purchase.warehouse?.name || getWarehouseNameFromId(purchase.warehouse_id) }}</td>
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
                    <button @click="openEditModal(purchase)" class="text-blue-500 hover:text-blue-700 transition-colors p-1" title="Edit Purchase">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    
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

    <!-- Edit Purchase Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="closeEditModal">
      <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
          <div>
            <h2 class="text-xl font-bold text-gray-800">Edit Purchase Order</h2>
            <p class="text-sm text-gray-500">Reference: {{ editingPurchase?.reference_no }}</p>
          </div>
          <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6">
          <form @submit.prevent="updatePurchase">
            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
              <div>
                <label class="text-sm font-semibold text-gray-700 block mb-2">Supplier *</label>
                <div class="text-gray-800 bg-gray-50 px-3 py-2 rounded-lg border border-gray-200">
                  {{ getSupplierDisplayName() }}
                </div>
              </div>
              <div>
                <label class="text-sm font-semibold text-gray-700 block mb-2">Warehouse *</label>
                <div class="text-gray-800 bg-gray-50 px-3 py-2 rounded-lg border border-gray-200">
                  {{ getWarehouseDisplayName() }}
                </div>
              </div>
              <div>
                <label class="text-sm font-semibold text-gray-700 block mb-2">Purchase Date</label>
                <div class="text-gray-800 bg-gray-50 px-3 py-2 rounded-lg border border-gray-200">
                  {{ formatDate(editForm.purchase_date) }}
                </div>
              </div>
              <div>
                <label class="text-sm font-semibold text-gray-700 block mb-2">Status</label>
                <select v-model="editForm.status" class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl focus:border-purple-300 outline-none" :disabled="editForm.status === 'received'">
                  <option value="ordered">Ordered</option>
                  <option value="received">Received</option>
                  <option value="pending">Pending</option>
                </select>
              </div>
            </div>

            <!-- Items Table -->
            <div class="mb-6">
              <h3 class="font-semibold text-gray-800 mb-3">Items</h3>
              <div class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-3 py-2 text-left">Product</th>
                      <th class="px-3 py-2 text-right">Quantity</th>
                      <th class="px-3 py-2 text-right">Unit Cost</th>
                      <th class="px-3 py-2 text-right">Discount %</th>
                      <th class="px-3 py-2 text-right">Tax %</th>
                      <th class="px-3 py-2 text-right">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in editForm.items" :key="index">
                      <td class="px-3 py-2">
                        <div class="font-medium text-gray-800">{{ item.product?.name || 'Product not found' }}</div>
                      </td>
                      <td class="px-3 py-2 text-right">
                        <div class="text-gray-700">{{ formatNumber(item.quantity) }}</div>
                      </td>
                      <td class="px-3 py-2 text-right">
                        <div class="text-gray-700">৳{{ formatNumber(item.purchase_price) }}</div>
                      </td>
                      <td class="px-3 py-2 text-right">
                        <div class="text-gray-700">{{ formatNumber(item.discount) }}%</div>
                      </td>
                      <td class="px-3 py-2 text-right">
                        <div class="text-gray-700">{{ formatNumber(item.tax) }}%</div>
                      </td>
                      <td class="px-3 py-2 text-right font-semibold">
                        <div class="text-purple-600">৳{{ formatNumber(item.total) }}</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Totals -->
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
              <div class="flex justify-between mb-2">
                <span>Subtotal:</span>
                <span>৳{{ formatNumber(editSubtotal) }}</span>
              </div>
              <div class="flex justify-between mb-2">
                <span>Total Discount:</span>
                <span class="text-red-600">- ৳{{ formatNumber(editTotalDiscount) }}</span>
              </div>
              <div class="flex justify-between mb-2">
                <span>Total Tax:</span>
                <span class="text-green-600">+ ৳{{ formatNumber(editTotalTax) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-gray-200 font-bold">
                <span>Total Amount:</span>
                <span class="text-purple-600">৳{{ formatNumber(editTotalAmount) }}</span>
              </div>
            </div>

            <!-- Payment Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
              <div>
                <label class="text-sm font-semibold text-gray-700 block mb-2">Payment Status</label>
                <div class="text-gray-800 bg-gray-50 px-3 py-2 rounded-lg border border-gray-200">
                  {{ getPaymentStatusLabel(editForm.payment_status) }}
                </div>
              </div>
              <div>
                <label class="text-sm font-semibold text-gray-700 block mb-2">Paid Amount</label>
                <input 
                  type="number" 
                  v-model="editForm.paid_amount" 
                  step="0.01" 
                  class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl focus:border-purple-300 outline-none"
                  :max="editTotalAmount"
                  :readonly="editForm.payment_status === 'paid'"
                  :class="{'bg-gray-100': editForm.payment_status === 'paid'}"
                >
                <p v-if="editForm.payment_status !== 'paid'" class="text-xs text-gray-500 mt-1">
                  Due amount: ৳{{ formatNumber(editTotalAmount - editForm.paid_amount) }}
                </p>
              </div>
            </div>

            <!-- Add Payment Button -->
            <div v-if="editForm.payment_status !== 'paid' && editForm.paid_amount < editTotalAmount" class="mb-6">
              <button 
                type="button" 
                @click="showAddPaymentModal = true"
                class="text-purple-600 hover:text-purple-700 font-medium flex items-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Additional Payment
              </button>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
              <button type="button" @click="closeEditModal" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                Cancel
              </button>
              <button type="submit" :disabled="editLoading" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-all disabled:opacity-50">
                <span v-if="!editLoading">Update Payment</span>
                <span v-else class="flex items-center gap-2">
                  <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Updating...
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Add Payment Modal -->
    <div v-if="showAddPaymentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showAddPaymentModal = false">
      <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
          <h2 class="text-xl font-bold text-gray-800">Add Payment</h2>
          <button @click="showAddPaymentModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="p-6">
          <div class="mb-4">
            <label class="text-sm font-semibold text-gray-700 block mb-2">Payment Amount</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">৳</span>
              <input 
                type="number" 
                v-model="paymentAmount" 
                step="0.01"
                :max="editTotalAmount - editForm.paid_amount"
                class="w-full pl-8 pr-3 py-2 border-2 border-gray-200 rounded-xl focus:border-purple-300 outline-none"
              >
            </div>
            <p class="text-xs text-gray-500 mt-1">Maximum: ৳{{ formatNumber(editTotalAmount - editForm.paid_amount) }}</p>
          </div>
          <div class="mb-4">
            <label class="text-sm font-semibold text-gray-700 block mb-2">Payment Date</label>
            <input type="date" v-model="paymentDate" class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl focus:border-purple-300 outline-none">
          </div>
          <div class="mb-4">
            <label class="text-sm font-semibold text-gray-700 block mb-2">Payment Method</label>
            <select v-model="paymentMethod" class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl focus:border-purple-300 outline-none">
              <option value="cash">Cash</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="check">Check</option>
              <option value="mobile_banking">Mobile Banking</option>
            </select>
          </div>
          <div class="flex justify-end gap-3">
            <button @click="showAddPaymentModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
              Cancel
            </button>
            <button @click="addPayment" :disabled="!paymentAmount || paymentAmount <= 0" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-all disabled:opacity-50">
              Add Payment
            </button>
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
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'

export default {
  name: 'PurchaseList',
  
  setup() {
    const router = useRouter()
    
    // State
    const loading = ref(false)
    const editLoading = ref(false)
    const showEditModal = ref(false)
    const showAddPaymentModal = ref(false)
    const editingPurchase = ref(null)
    const purchases = ref([])
    const suppliers = ref([])
    const warehouses = ref([])
    let searchTimeout = null
    
    // Payment modal data
    const paymentAmount = ref(0)
    const paymentDate = ref(new Date().toISOString().split('T')[0])
    const paymentMethod = ref('cash')
    
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
    
    // Edit Form
    const editForm = reactive({
      supplier_id: '',
      warehouse_id: '',
      purchase_date: '',
      status: '',
      payment_status: '',
      paid_amount: 0,
      items: []
    })
    
    // Helper functions to get names from IDs
    const getSupplierNameFromId = (id) => {
      if (!id) return 'N/A'
      const supplier = suppliers.value.find(s => s.id === id)
      return supplier?.name || 'N/A'
    }
    
    const getWarehouseNameFromId = (id) => {
      if (!id) return 'N/A'
      const warehouse = warehouses.value.find(w => w.id === id)
      return warehouse?.name || 'N/A'
    }
    
    const getSupplierDisplayName = () => {
      if (!editForm.supplier_id) return 'N/A'
      // First try to find in suppliers array
      const supplier = suppliers.value.find(s => s.id === editForm.supplier_id)
      if (supplier?.name) return supplier.name
      // If not found, try to get from editingPurchase
      if (editingPurchase.value?.supplier?.name) return editingPurchase.value.supplier.name
      return 'Loading...'
    }
    
    const getWarehouseDisplayName = () => {
      if (!editForm.warehouse_id) return 'N/A'
      // First try to find in warehouses array
      const warehouse = warehouses.value.find(w => w.id === editForm.warehouse_id)
      if (warehouse?.name) return warehouse.name
      // If not found, try to get from editingPurchase
      if (editingPurchase.value?.warehouse?.name) return editingPurchase.value.warehouse.name
      return 'Loading...'
    }
    
    // Computed for edit totals
    const editSubtotal = computed(() => {
      return editForm.items.reduce((sum, item) => {
        return sum + (parseFloat(item.quantity || 0) * parseFloat(item.purchase_price || 0))
      }, 0)
    })
    
    const editTotalDiscount = computed(() => {
      return editForm.items.reduce((sum, item) => {
        const itemSubtotal = parseFloat(item.quantity || 0) * parseFloat(item.purchase_price || 0)
        const discountAmount = (itemSubtotal * (parseFloat(item.discount) || 0)) / 100
        return sum + discountAmount
      }, 0)
    })
    
    const editTotalTax = computed(() => {
      return editForm.items.reduce((sum, item) => {
        const itemSubtotal = parseFloat(item.quantity || 0) * parseFloat(item.purchase_price || 0)
        const discountAmount = (itemSubtotal * (parseFloat(item.discount) || 0)) / 100
        const taxableAmount = itemSubtotal - discountAmount
        const taxAmount = (taxableAmount * (parseFloat(item.tax) || 0)) / 100
        return sum + taxAmount
      }, 0)
    })
    
    const editTotalAmount = computed(() => {
      return editSubtotal.value - editTotalDiscount.value + editTotalTax.value
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
        console.log('Suppliers loaded:', suppliers.value)
      } catch (error) {
        console.error('Failed to load suppliers:', error)
      }
    }
    
    const loadWarehouses = async () => {
      try {
        const response = await api.get('/warehouses')
        warehouses.value = response.data.data || response.data
        console.log('Warehouses loaded:', warehouses.value)
      } catch (error) {
        console.error('Failed to load warehouses:', error)
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
    
    const openEditModal = async (purchase) => {
      editingPurchase.value = purchase
      try {
        const response = await api.get(`/purchases/${purchase.id}`)
        const fullPurchase = response.data.data
        
        editForm.supplier_id = fullPurchase.supplier_id
        editForm.warehouse_id = fullPurchase.warehouse_id
        editForm.purchase_date = fullPurchase.purchase_date
        editForm.status = fullPurchase.status
        editForm.payment_status = fullPurchase.payment_status
        editForm.paid_amount = parseFloat(fullPurchase.paid_amount) || 0
        editForm.items = fullPurchase.items.map(item => ({
          id: item.id,
          product_id: item.product_id,
          product: item.product,
          quantity: parseFloat(item.quantity),
          purchase_price: parseFloat(item.purchase_price),
          discount: parseFloat(item.discount || 0),
          tax: parseFloat(item.tax || 0),
          total: parseFloat(item.total)
        }))
        
        console.log('Edit form data:', {
          supplier_id: editForm.supplier_id,
          warehouse_id: editForm.warehouse_id,
          supplier_name: getSupplierDisplayName(),
          warehouse_name: getWarehouseDisplayName()
        })
        
        showEditModal.value = true
      } catch (error) {
        console.error('Failed to load purchase details:', error)
        alert('Failed to load purchase details')
      }
    }
    
    const closeEditModal = () => {
      showEditModal.value = false
      showAddPaymentModal.value = false
      editingPurchase.value = null
      editForm.items = []
      paymentAmount.value = 0
      paymentDate.value = new Date().toISOString().split('T')[0]
      paymentMethod.value = 'cash'
    }
    
    const addPayment = async () => {
      if (!paymentAmount.value || paymentAmount.value <= 0) {
        alert('Please enter a valid payment amount')
        return
      }
      
      const maxAmount = editTotalAmount.value - editForm.paid_amount
      if (paymentAmount.value > maxAmount) {
        alert(`Payment amount cannot exceed ${formatNumber(maxAmount)}`)
        return
      }
      
      editLoading.value = true
      try {
        await api.post(`/purchases/${editingPurchase.value.id}/payments`, {
          amount: parseFloat(paymentAmount.value),
          payment_date: paymentDate.value,
          payment_method: paymentMethod.value
        })
        
        const newPaidAmount = editForm.paid_amount + parseFloat(paymentAmount.value)
        editForm.paid_amount = newPaidAmount
        
        if (newPaidAmount >= editTotalAmount.value) {
          editForm.payment_status = 'paid'
        } else if (newPaidAmount > 0) {
          editForm.payment_status = 'partial'
        }
        
        alert('Payment added successfully!')
        showAddPaymentModal.value = false
        paymentAmount.value = 0
        await loadPurchases()
      } catch (error) {
        console.error('Add payment failed:', error)
        alert('Failed to add payment: ' + (error.response?.data?.message || error.message))
      } finally {
        editLoading.value = false
      }
    }
    
    const updatePurchase = async () => {
      if (!editingPurchase.value) return
      
      editLoading.value = true
      try {
        if (editForm.payment_status === 'paid') {
          editForm.paid_amount = editTotalAmount.value
        }
        
        const payload = {
          paid_amount: parseFloat(editForm.paid_amount) || 0,
          payment_status: editForm.payment_status,
          status: editForm.status
        }
        
        await api.put(`/purchases/${editingPurchase.value.id}`, payload)
        
        alert('Purchase updated successfully!')
        closeEditModal()
        loadPurchases()
      } catch (error) {
        console.error('Update failed:', error)
        alert('Failed to update purchase: ' + (error.response?.data?.message || error.message))
      } finally {
        editLoading.value = false
      }
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
            <div class="info-row"><span><strong>Supplier:</strong> ${purchase.supplier?.name || getSupplierNameFromId(purchase.supplier_id)}</span><span><strong>Warehouse:</strong> ${purchase.warehouse?.name || getWarehouseNameFromId(purchase.warehouse_id)}</span></div>
            <div class="info-row"><span><strong>Status:</strong> ${getStatusLabel(purchase.status)}</span><span><strong>Payment:</strong> ${getPaymentStatusLabel(purchase.payment_status)}</span></div>
          </div>
          <table><thead><tr><th>Product</th><th>Quantity</th><th>Unit Cost</th><th>Discount</th><th>Tax</th><th>Total</th></tr></thead><tbody>
            ${purchase.items?.map(item => `<tr><td>${item.product?.name || 'N/A'}</td><td>${item.quantity}</td><td>৳${formatNumber(item.purchase_price)}</td><td>${item.discount || 0}%</td><td>${item.tax || 0}%</td><td>৳${formatNumber(item.total)}</td></tr>`).join('') || '<tr><td colspan="6">No items found</td></tr>'}
          </tbody></table>
          <div class="totals"><p><strong>Total:</strong> ৳${formatNumber(purchase.total_amount)}</p><p><strong>Paid:</strong> ৳${formatNumber(purchase.paid_amount)}</p><p><strong>Due:</strong> ৳${formatNumber(purchase.total_amount - purchase.paid_amount)}</p></div>
          <div class="footer"><p>Generated on ${new Date().toLocaleString()}</p></div>
        </body>
        </html>
      `)
      printWindow.document.close()
      printWindow.print()
    }
    
    watch(() => editForm.paid_amount, (newVal) => {
      const paid = parseFloat(newVal) || 0
      if (paid >= editTotalAmount.value && editTotalAmount.value > 0) {
        editForm.payment_status = 'paid'
      } else if (paid > 0) {
        editForm.payment_status = 'partial'
      } else {
        editForm.payment_status = 'unpaid'
      }
    })
    
    onMounted(async () => {
      await Promise.all([loadSuppliers(), loadWarehouses()])
      await loadPurchases()
    })
    
    return {
      loading,
      editLoading,
      showEditModal,
      showAddPaymentModal,
      editingPurchase,
      purchases,
      suppliers,
      warehouses,
      filters,
      pagination,
      editForm,
      paymentAmount,
      paymentDate,
      paymentMethod,
      editSubtotal,
      editTotalDiscount,
      editTotalTax,
      editTotalAmount,
      getSupplierNameFromId,
      getWarehouseNameFromId,
      getSupplierDisplayName,
      getWarehouseDisplayName,
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
      openEditModal,
      closeEditModal,
      addPayment,
      updatePurchase,
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

input:read-only {
  background-color: #f3f4f6;
  cursor: not-allowed;
}
</style>