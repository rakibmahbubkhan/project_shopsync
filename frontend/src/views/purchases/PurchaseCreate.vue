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
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">{{ isEditMode ? 'Edit Purchase Order' : 'Record New Stock Purchase' }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                  {{ isEditMode ? 'Update purchase order and manage installment payments' : 'Create a new purchase order and manage inventory' }}
                </p>
              </div>
            </div>
            <div class="flex gap-3">
              <router-link to="/purchases" class="text-gray-500 hover:text-gray-700 inline-flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancel
              </router-link>
              <button 
                v-if="isEditMode && purchaseData.status !== 'received'"
                @click="receivePurchase" 
                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl font-semibold transition-all shadow-lg shadow-green-200 flex items-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Mark as Received
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Left Column - Items Selection -->
        <div class="flex-1 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="p-4 sm:p-6 border-b border-gray-100">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              <h3 class="font-bold text-gray-800">Items Selection</h3>
            </div>
            <p class="text-sm text-gray-500 mt-1">{{ isEditMode ? 'View products in this purchase' : 'Search and add products to this purchase order' }}</p>
          </div>
          
          <div class="p-4 sm:p-6">
            <!-- Product Search (only for create mode) -->
            <div v-if="!isEditMode" class="mb-6 relative">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                <input 
                  type="text" 
                  v-model="searchQuery"
                  @input="searchProducts"
                  placeholder="Search products by name or SKU..."
                  class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white"
                  autocomplete="off"
                >
              </div>
              
              <!-- Search Results Dropdown -->
              <div 
                v-if="searchResults.length > 0" 
                class="absolute z-10 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-xl max-h-80 overflow-y-auto divide-y divide-gray-100"
              >
                <div 
                  v-for="product in searchResults" 
                  :key="product.id"
                  @click="addToCart(product)"
                  class="p-4 hover:bg-purple-50 cursor-pointer transition-all flex justify-between items-center group"
                >
                  <div class="flex-1">
                    <div class="font-semibold text-gray-800 group-hover:text-purple-600">{{ product.name }}</div>
                    <div class="text-sm text-gray-500 mt-0.5">
                      SKU: {{ product.sku || product.code }}
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                      Cost: ৳{{ formatNumber(product.purchase_price || product.cost_price) }}
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="text-xs bg-gray-100 px-2 py-1 rounded-full">
                      Stock: {{ product.stock_quantity || product.stock || 0 }}
                    </div>
                    <svg class="w-5 h-5 text-purple-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- Cart Items Table -->
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 sticky top-0">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SKU</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Qty</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit Cost</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Discount %</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tax %</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                    <th v-if="!isEditMode" class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="(item, index) in cart" :key="index" class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                      <div class="font-medium text-gray-800">{{ item.name }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ item.sku || item.code }}</td>
                    <td class="px-4 py-3">
                      <input 
                        type="number" 
                        v-model="item.quantity" 
                        @input="calculateItemTotal(item)"
                        min="0.01"
                        step="0.01"
                        :readonly="isEditMode"
                        class="w-20 px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-purple-300 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
                        :class="{'bg-gray-100': isEditMode}"
                      >
                    </td>
                    <td class="px-4 py-3">
                      <div class="relative">
                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">৳</span>
                        <input 
                          type="number" 
                          v-model="item.purchase_price" 
                          @input="calculateItemTotal(item)"
                          min="0"
                          step="0.01"
                          :readonly="isEditMode"
                          class="w-24 pl-6 pr-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-purple-300 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
                          :class="{'bg-gray-100': isEditMode}"
                        >
                      </div>
                    </td>
                    <td class="px-4 py-3">
                      <div class="relative">
                        <input 
                          type="number" 
                          v-model="item.discount" 
                          @input="calculateItemTotal(item)"
                          min="0"
                          max="100"
                          step="0.1"
                          :readonly="isEditMode"
                          class="w-20 px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-purple-300 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
                          :class="{'bg-gray-100': isEditMode}"
                          placeholder="0"
                        >
                        <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                      </div>
                    </td>
                    <td class="px-4 py-3">
                      <div class="relative">
                        <input 
                          type="number" 
                          v-model="item.tax" 
                          @input="calculateItemTotal(item)"
                          min="0"
                          max="100"
                          step="0.1"
                          :readonly="isEditMode"
                          class="w-20 px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-purple-300 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
                          :class="{'bg-gray-100': isEditMode}"
                          placeholder="0"
                        >
                        <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                      </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                      <span class="font-semibold text-purple-600">৳{{ formatNumber(item.total) }}</span>
                    </td>
                    <td v-if="!isEditMode" class="px-4 py-3 text-center">
                      <button @click="removeFromCart(index)" class="text-red-400 hover:text-red-600 transition-colors p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </td>
                  </tr>
                  
                  <!-- Empty Cart Message -->
                  <tr v-if="cart.length === 0">
                    <td :colspan="isEditMode ? 7 : 8" class="px-4 py-12 text-center">
                      <div class="flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-700 mb-1">Cart is empty</h3>
                        <p class="text-sm text-gray-400">{{ isEditMode ? 'No items found in this purchase' : 'Search and add products to get started' }}</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Cart Summary Cards -->
            <div v-if="cart.length > 0" class="mt-6 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl border border-purple-100">
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                  <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Total Items</p>
                  <p class="text-2xl font-bold text-gray-800 mt-1">{{ cart.length }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Total Quantity</p>
                  <p class="text-2xl font-bold text-gray-800 mt-1">{{ formatNumber(totalQuantity) }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Subtotal</p>
                  <p class="text-2xl font-bold text-gray-800 mt-1">৳{{ formatNumber(subtotal) }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Tax Amount</p>
                  <p class="text-2xl font-bold text-gray-800 mt-1">৳{{ formatNumber(totalTax) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Purchase Details -->
        <div class="lg:w-96 space-y-6">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="font-bold text-gray-800">Purchase Details</h3>
              </div>
              <p class="text-sm text-gray-500 mt-1">{{ isEditMode ? 'Update purchase information and manage installment payments' : 'Fill in the purchase information' }}</p>
            </div>
            
            <div class="p-4 sm:p-6 space-y-4">
              <!-- Reference Number (Edit Mode Only) -->
              <div v-if="isEditMode">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Reference Number</label>
                <div class="text-gray-800 bg-gray-50 px-3 py-2.5 rounded-lg border border-gray-200 font-mono">
                  {{ purchaseData.reference_no }}
                </div>
              </div>

              <!-- Supplier Selection -->
              <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Supplier *</label>
                <select v-model="form.supplier_id" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white" :disabled="isEditMode" required>
                  <option value="">Select Supplier</option>
                  <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>

              <!-- Warehouse Selection -->
              <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Target Warehouse *</label>
                <select v-model="form.warehouse_id" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white" :disabled="isEditMode" required>
                  <option value="">Select Warehouse</option>
                  <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                </select>
              </div>

              <!-- Purchase Date -->
              <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Purchase Date</label>
                <input 
                  type="date" 
                  v-model="form.purchase_date" 
                  class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white"
                  :readonly="isEditMode"
                  :class="{'bg-gray-100': isEditMode}"
                >
              </div>

              <!-- Purchase Status -->
              <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Status</label>
                <select v-model="form.status" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white" :disabled="isEditMode && purchaseData.status === 'received'">
                  <option value="ordered">📦 Ordered</option>
                  <option value="received">✅ Received</option>
                  <option value="pending">⏳ Pending</option>
                </select>
              </div>

              <!-- Payment Status (for create mode) -->
              <div v-if="!isEditMode">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Initial Payment Status</label>
                <select v-model="form.payment_status" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white">
                  <option value="unpaid">💰 Unpaid</option>
                  <option value="partial">💳 Partial</option>
                  <option value="paid">✅ Paid</option>
                </select>
              </div>

              <!-- Initial Paid Amount (for create mode) -->
              <div v-if="!isEditMode && form.payment_status !== 'unpaid'">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Initial Paid Amount (৳)</label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">৳</span>
                  <input 
                    type="number" 
                    v-model="form.paid_amount" 
                    min="0"
                    step="0.01"
                    class="w-full pl-8 pr-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
                    :max="totalAmount"
                  >
                </div>
              </div>

              <!-- ============ INSTALLMENT PAYMENTS SECTION (EDIT MODE ONLY) ============ -->
            <div v-if="isEditMode" class="border-t border-gray-200 pt-4">
              <div class="flex items-center justify-between mb-3">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Installment Payments</label>
                <span class="text-xs text-gray-400">Total: ৳{{ formatNumber(totalAmount) }}</span>
              </div>
              
              <!-- List of all installment payments -->
              <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                <div v-for="(payment, idx) in payments" :key="payment.id" class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                  <div class="flex justify-between items-start mb-2">
                    <div>
                      <span class="font-semibold text-indigo-600 text-sm">Installment #{{ payment.installment_number }}</span>
                      <div class="text-xs text-gray-500 mt-1">
                        {{ formatDate(payment.payment_date) }} • {{ getPaymentMethodLabel(payment.payment_method) }}
                      </div>
                      <div v-if="payment.reference_no" class="text-xs text-gray-400 mt-1">
                        Ref: {{ payment.reference_no }}
                      </div>
                    </div>
                    <span class="text-green-600 font-bold text-lg">৳{{ formatNumber(payment.amount) }}</span>
                  </div>
                </div>
                
                <div v-if="payments.length === 0" class="text-center text-gray-400 text-sm py-4">
                  No payments made yet
                </div>
              </div>

              <!-- Payment Progress -->
              <div class="mt-4 mb-4">
                <div class="flex justify-between text-xs mb-1">
                  <span class="text-gray-600">Paid: ৳{{ formatNumber(form.paid_amount) }}</span>
                  <span class="text-gray-600">Due: ৳{{ formatNumber(remainingBalance) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="bg-green-600 rounded-full h-2 transition-all duration-300" :style="{ width: paymentProgress + '%' }"></div>
                </div>
                <div class="text-right text-xs text-gray-500 mt-1">{{ paymentProgress.toFixed(1) }}% paid</div>
              </div>

              <!-- Average Discount and Tax (Edit Mode) -->
              <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Avg. Discount (%)</label>
                  <input type="number" v-model="form.average_discount" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-purple-200 outline-none" step="0.1">
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Avg. Tax (%)</label>
                  <input type="number" v-model="form.average_tax" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-purple-200 outline-none" step="0.1">
                </div>
              </div>

              <!-- Add New Installment Form - ALWAYS SHOW when not fully paid -->
              <div v-if="remainingBalance > 0" class="mt-4 p-4 bg-indigo-50 rounded-xl border border-indigo-200">
                <h4 class="text-sm font-bold text-indigo-800 mb-3">
                  Add Installment #{{ payments.length + 1 }}
                </h4>
                
                <div class="space-y-3">
                  <div>
                    <label class="text-xs font-semibold text-indigo-700 block mb-1">Payment Amount (৳)</label>
                    <div class="relative">
                      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-indigo-400">৳</span>
                      <input 
                        type="number" 
                        v-model="newPayment.amount" 
                        @input="validatePaymentAmount"
                        class="w-full pl-8 pr-3 py-2 border-2 border-indigo-200 rounded-lg focus:border-indigo-400 outline-none bg-white"
                        placeholder="0.00"
                        :max="remainingBalance"
                        step="0.01"
                      >
                    </div>
                    <p class="text-xs text-indigo-500 mt-1">Maximum: ৳{{ formatNumber(remainingBalance) }}</p>
                  </div>

                  <div>
                    <label class="text-xs font-semibold text-indigo-700 block mb-1">Payment Date</label>
                    <input 
                      type="date" 
                      v-model="newPayment.date" 
                      class="w-full px-3 py-2 border-2 border-indigo-200 rounded-lg focus:border-indigo-400 outline-none bg-white"
                    >
                  </div>

                  <div>
                    <label class="text-xs font-semibold text-indigo-700 block mb-1">Payment Method</label>
                    <select v-model="newPayment.method" class="w-full px-3 py-2 border-2 border-indigo-200 rounded-lg focus:border-indigo-400 outline-none bg-white">
                      <option value="cash">Cash</option>
                      <option value="bank_transfer">Bank Transfer</option>
                      <option value="check">Check</option>
                      <option value="mobile_banking">Mobile Banking</option>
                    </select>
                  </div>

                  <div>
                    <label class="text-xs font-semibold text-indigo-700 block mb-1">Reference No (Optional)</label>
                    <input 
                      type="text" 
                      v-model="newPayment.reference_no" 
                      class="w-full px-3 py-2 border-2 border-indigo-200 rounded-lg focus:border-indigo-400 outline-none bg-white"
                      placeholder="Check/Transaction number"
                    >
                  </div>

                  <button 
                    @click="addPayment" 
                    :disabled="!newPayment.amount || newPayment.amount <= 0 || newPayment.amount > remainingBalance"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Add Installment #{{ payments.length + 1 }}
                  </button>
                </div>
              </div>

              <!-- Fully Paid Message -->
              <div v-if="remainingBalance <= 0 && totalAmount > 0" class="mt-4 p-3 bg-green-100 rounded-lg text-center">
                <svg class="w-5 h-5 text-green-600 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-green-700 font-semibold">Fully Paid!</span>
              </div>
            </div>

              <!-- Totals Section -->
              <div class="pt-4 border-t border-gray-200 space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Subtotal:</span>
                  <span class="font-medium">৳{{ formatNumber(subtotal) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Total Discount:</span>
                  <span class="text-red-600">- ৳{{ formatNumber(totalDiscount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Total Tax:</span>
                  <span class="text-green-600">+ ৳{{ formatNumber(totalTax) }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                  <span>Total Amount:</span>
                  <span class="text-purple-600">৳{{ formatNumber(totalAmount) }}</span>
                </div>
              </div>

              <!-- Submit Button -->
              <button 
                @click="submitPurchase" 
                :disabled="loading || !canSubmit"
                class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white py-4 rounded-xl font-bold transition-all shadow-lg shadow-purple-200 disabled:from-gray-300 disabled:to-gray-400 disabled:cursor-not-allowed transform hover:scale-[1.02] active:scale-95"
              >
                <span v-if="!loading" class="flex items-center justify-center gap-2">
                  {{ isEditMode ? 'Update Purchase' : 'Confirm Purchase' }}
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </span>
                <span v-else class="flex items-center justify-center gap-2">
                  <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Processing...
                </span>
              </button>

              <!-- Delete Button (Edit Mode Only) -->
              <button 
                v-if="isEditMode"
                type="button"
                @click="deletePurchase"
                class="w-full bg-red-50 hover:bg-red-100 text-red-600 py-3 rounded-xl font-semibold transition-all border border-red-200"
              >
                Delete Purchase
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white p-8 rounded-2xl shadow-2xl text-center animate-slide-up">
        <div class="relative">
          <div class="animate-spin rounded-full h-16 w-16 border-4 border-purple-600 border-t-transparent mx-auto"></div>
        </div>
        <p class="mt-4 text-lg font-semibold text-gray-800">{{ loadingMessage }}</p>
        <p class="text-sm text-gray-500 mt-1">Please wait while we process your request</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/api/axios'

export default {
  name: 'PurchaseForm',
  
  setup() {
    const router = useRouter()
    const route = useRoute()
    
    // State
    const loading = ref(false)
    const loadingMessage = ref('Processing...')
    const suppliers = ref([])
    const warehouses = ref([])
    const cart = ref([])
    const searchQuery = ref('')
    const searchResults = ref([])
    const searchTimeout = ref(null)
    const payments = ref([])
    
    // New payment data
    const newPayment = reactive({
      amount: 0,
      date: new Date().toISOString().split('T')[0],
      method: 'cash',
      reference_no: ''
    })
    
    // Check if edit mode
    const isEditMode = computed(() => !!route.params.id)
    const purchaseId = computed(() => route.params.id)
    const purchaseData = ref({})
    
    // Helper function to format date for input
    const formatDateForInput = (date) => {
      if (!date) return new Date().toISOString().split('T')[0]
      // Handle ISO format: "2026-04-17T00:00:00.000000Z" -> "2026-04-17"
      return date.split('T')[0]
    }
    
    // Form Data
    const form = reactive({
      supplier_id: '',
      warehouse_id: '',
      purchase_date: new Date().toISOString().split('T')[0],
      status: 'ordered',
      payment_status: 'unpaid',
      paid_amount: 0,
      new_payment_amount: 0,
      average_discount: 0,
      average_tax: 0
    })
    
    // Computed Properties
    const subtotal = computed(() => {
      return cart.value.reduce((sum, item) => {
        const qty = parseFloat(item.quantity) || 0
        const price = parseFloat(item.purchase_price) || 0
        return sum + (qty * price)
      }, 0)
    })
    
    const totalDiscount = computed(() => {
      if (isEditMode.value) {
        // Calculate from individual items in edit mode
        return cart.value.reduce((sum, item) => {
          const qty = parseFloat(item.quantity) || 0
          const price = parseFloat(item.purchase_price) || 0
          const discount = parseFloat(item.discount) || 0
          const itemSubtotal = qty * price
          const discountAmount = (itemSubtotal * discount) / 100
          return sum + (isNaN(discountAmount) ? 0 : discountAmount)
        }, 0)
      } else {
        // Use average discount for create mode
        return (subtotal.value * (parseFloat(form.average_discount) || 0)) / 100
      }
    })
    
    const taxableAmount = computed(() => subtotal.value - totalDiscount.value)
    
    const totalTax = computed(() => {
      if (isEditMode.value) {
        // Calculate from individual items in edit mode
        return cart.value.reduce((sum, item) => {
          const qty = parseFloat(item.quantity) || 0
          const price = parseFloat(item.purchase_price) || 0
          const discount = parseFloat(item.discount) || 0
          const tax = parseFloat(item.tax) || 0
          const itemSubtotal = qty * price
          const discountAmount = (itemSubtotal * discount) / 100
          const taxableAmount = itemSubtotal - discountAmount
          const taxAmount = (taxableAmount * tax) / 100
          return sum + (isNaN(taxAmount) ? 0 : taxAmount)
        }, 0)
      } else {
        // Use average tax for create mode
        return (taxableAmount.value * (parseFloat(form.average_tax) || 0)) / 100
      }
    })
    
    const totalAmount = computed(() => taxableAmount.value + totalTax.value)
    
    const totalQuantity = computed(() => {
      return cart.value.reduce((sum, item) => sum + (parseFloat(item.quantity) || 0), 0)
    })
    
    const paymentProgress = computed(() => {
      if (totalAmount.value > 0) {
        const progress = (form.paid_amount / totalAmount.value) * 100
        return isNaN(progress) ? 0 : Math.min(100, progress)
      }
      return 0
    })
    
    const canSubmit = computed(() => {
      return form.supplier_id && 
             form.warehouse_id && 
             cart.value.length > 0
    })
    
    const remainingBalance = computed(() => {
      const balance = totalAmount.value - form.paid_amount
      return isNaN(balance) ? 0 : Math.max(0, balance)
    })    
    // Methods
    const formatNumber = (value) => {
      const num = parseFloat(value || 0)
      return isNaN(num) ? '0.00' : num.toFixed(2)
    }
    
    const formatDate = (date) => {
      if (!date) return 'N/A'
      const d = new Date(date)
      return isNaN(d.getTime()) ? 'N/A' : d.toLocaleDateString('en-BD')
    }
    
    const getPaymentStatusLabel = (status) => {
      const labels = {
        unpaid: '💰 Unpaid',
        partial: '💳 Partial',
        paid: '✅ Paid'
      }
      return labels[status] || status
    }
    
    const getPaymentMethodLabel = (method) => {
      const labels = {
        cash: 'Cash',
        bank_transfer: 'Bank Transfer',
        check: 'Check',
        mobile_banking: 'Mobile Banking'
      }
      return labels[method] || method
    }
    
    const validatePaymentAmount = () => {
      const maxAmount = totalAmount.value - form.paid_amount
      if (newPayment.amount > maxAmount) {
        newPayment.amount = maxAmount
      }
      if (newPayment.amount < 0) {
        newPayment.amount = 0
      }
    }
    
    const calculateItemTotal = (item) => {
      const quantity = parseFloat(item.quantity) || 0
      const price = parseFloat(item.purchase_price) || 0
      const discount = parseFloat(item.discount) || 0
      const tax = parseFloat(item.tax) || 0
      
      const itemSubtotal = quantity * price
      const discountAmount = (itemSubtotal * discount) / 100
      const taxableAmount = itemSubtotal - discountAmount
      const taxAmount = (taxableAmount * tax) / 100
      
      item.total = itemSubtotal - discountAmount + taxAmount
    }
    
    const searchProducts = () => {
      clearTimeout(searchTimeout.value)
      
      if (searchQuery.value.length < 2) {
        searchResults.value = []
        return
      }
      
      searchTimeout.value = setTimeout(async () => {
        try {
          const response = await api.get('/products', {
            params: { search: searchQuery.value, per_page: 20 }
          })
          searchResults.value = response.data.data || response.data
        } catch (error) {
          console.error('Product search failed:', error)
        }
      }, 300)
    }
    
    const addToCart = (product) => {
      const existing = cart.value.find(item => item.product_id === product.id)
      
      if (existing) {
        existing.quantity += 1
        calculateItemTotal(existing)
      } else {
        cart.value.push({
          product_id: product.id,
          name: product.name,
          sku: product.sku || product.code,
          quantity: 1,
          purchase_price: parseFloat(product.purchase_price || product.cost_price || 0),
          discount: 0,
          tax: 0,
          total: parseFloat(product.purchase_price || product.cost_price || 0)
        })
      }
      
      searchQuery.value = ''
      searchResults.value = []
    }
    
    const removeFromCart = (index) => {
      cart.value.splice(index, 1)
    }
    
    const loadSuppliers = async () => {
      try {
        const response = await api.get('/suppliers')
        suppliers.value = response.data.data || response.data
      } catch (error) {
        console.error('Failed to load suppliers:', error)
      }
    }
    
    const loadWarehouses = async () => {
      try {
        const response = await api.get('/warehouses')
        warehouses.value = response.data.data || response.data
      } catch (error) {
        console.error('Failed to load warehouses:', error)
      }
    }
    
    const loadPurchaseData = async () => {
      if (!isEditMode.value) return
      
      loading.value = true
      try {
        const response = await api.get(`/purchases/${purchaseId.value}`)
        const data = response.data.data
        purchaseData.value = data
        
        // Set form values with proper date formatting
        form.supplier_id = data.supplier_id
        form.warehouse_id = data.warehouse_id
        form.purchase_date = formatDateForInput(data.purchase_date)
        form.status = data.status
        form.payment_status = data.payment_status
        form.paid_amount = parseFloat(data.paid_amount) || 0
        form.average_discount = data.discount_percent || 0
        form.average_tax = data.tax_percent || 0
        
        // Load payments
        payments.value = data.payments || []
        
        // Load cart items with all fields
        cart.value = data.items.map(item => ({
          id: item.id,
          product_id: item.product_id,
          name: item.product?.name || 'Product',
          sku: item.product?.sku || item.product?.code,
          quantity: parseFloat(item.quantity),
          purchase_price: parseFloat(item.purchase_price),
          discount: parseFloat(item.discount_percent || 0),
          tax: parseFloat(item.tax_percent || 0),
          total: parseFloat(item.total)
        }))
      } catch (error) {
        console.error('Failed to load purchase:', error)
        alert('Failed to load purchase details')
        router.push('/purchases')
      } finally {
        loading.value = false
      }
    }
    
    const addPayment = async () => {
      if (!newPayment.amount || newPayment.amount <= 0) {
        alert('Please enter a valid payment amount')
        return
      }
      
      const maxAmount = totalAmount.value - form.paid_amount
      if (newPayment.amount > maxAmount) {
        alert(`Payment amount cannot exceed ${formatNumber(maxAmount)}`)
        return
      }
      
      loading.value = true
      try {
        await api.post(`/purchases/${purchaseId.value}/payments`, {
          amount: parseFloat(newPayment.amount),
          payment_date: newPayment.date,
          payment_method: newPayment.method,
          reference_no: newPayment.reference_no || null
        })
        
        const newPaidAmount = form.paid_amount + parseFloat(newPayment.amount)
        form.paid_amount = newPaidAmount
        
        if (newPaidAmount >= totalAmount.value) {
          form.payment_status = 'paid'
        } else if (newPaidAmount > 0) {
          form.payment_status = 'partial'
        }
        
        alert(`Payment of ৳${formatNumber(newPayment.amount)} added successfully!`)
        
        // Reset new payment form
        newPayment.amount = 0
        newPayment.reference_no = ''
        newPayment.date = new Date().toISOString().split('T')[0]
        
        // Reload purchase data to get updated payments
        await loadPurchaseData()
      } catch (error) {
        console.error('Add payment failed:', error)
        alert('Failed to add payment: ' + (error.response?.data?.message || error.message))
      } finally {
        loading.value = false
      }
    }
    
    const receivePurchase = async () => {
      if (!confirm(`Mark purchase ${purchaseData.value.reference_no} as received? This will update inventory.`)) return
      
      loading.value = true
      try {
        await api.post(`/purchases/${purchaseId.value}/receive`)
        alert('Purchase marked as received!')
        await loadPurchaseData()
        form.status = 'received'
      } catch (error) {
        console.error('Receive failed:', error)
        alert('Failed to receive purchase: ' + (error.response?.data?.message || error.message))
      } finally {
        loading.value = false
      }
    }
    
    const submitPurchase = async () => {
      if (!canSubmit.value) return
      
      loading.value = true
      loadingMessage.value = isEditMode.value ? 'Updating purchase...' : 'Creating purchase...'
      
      try {
        if (isEditMode.value) {
          // Update existing purchase - only payment and status
          const payload = {
            paid_amount: parseFloat(form.paid_amount) || 0,
            payment_status: form.payment_status,
            status: form.status
          }
          
          await api.put(`/purchases/${purchaseId.value}`, payload)
          alert('Purchase updated successfully!')
          router.push('/purchases')
        } else {
          // Create new purchase
          let finalPaidAmount = parseFloat(form.paid_amount) || 0
          if (form.payment_status === 'paid') {
            finalPaidAmount = totalAmount.value
          }
          
          const payload = {
            supplier_id: form.supplier_id,
            warehouse_id: form.warehouse_id,
            purchase_date: form.purchase_date,
            status: form.status,
            payment_status: form.payment_status,
            paid_amount: finalPaidAmount,
            subtotal: subtotal.value,
            total_discount: totalDiscount.value,
            total_tax: totalTax.value,
            total_amount: totalAmount.value,
            discount_percent: form.average_discount,
            tax_percent: form.average_tax,
            shipping_cost: 0,
            items: cart.value.map(item => ({
              product_id: item.product_id,
              quantity: parseFloat(item.quantity),
              purchase_price: parseFloat(item.purchase_price),
              discount_percent: parseFloat(item.discount || 0),
              tax_percent: parseFloat(item.tax || 0)
            }))
          }
          
          await api.post('/purchases', payload)
          alert('Purchase created successfully!')
          router.push('/purchases')
        }
      } catch (error) {
        console.error('Purchase failed:', error)
        const errorMessage = error.response?.data?.message || (isEditMode.value ? 'Failed to update purchase' : 'Failed to create purchase')
        alert(errorMessage)
      } finally {
        loading.value = false
      }
    }
    
    const deletePurchase = async () => {
      if (!confirm(`Are you sure you want to delete purchase ${purchaseData.value.reference_no}? This action cannot be undone.`)) return
      
      loading.value = true
      try {
        await api.delete(`/purchases/${purchaseId.value}`)
        alert('Purchase deleted successfully!')
        router.push('/purchases')
      } catch (error) {
        console.error('Delete failed:', error)
        alert('Failed to delete purchase: ' + (error.response?.data?.message || error.message))
      } finally {
        loading.value = false
      }
    }
    
    // Watch for paid amount to auto-update payment status (for create mode)
    watch(() => form.paid_amount, (newVal) => {
      if (!isEditMode.value) {
        const paid = parseFloat(newVal) || 0
        
        if (paid >= totalAmount.value && totalAmount.value > 0) {
          form.payment_status = 'paid'
        } else if (paid > 0) {
          form.payment_status = 'partial'
        } else {
          form.payment_status = 'unpaid'
        }
      }
    })
    
    // Watch for total amount changes to validate paid amount
    watch(totalAmount, (newTotal) => {
      if (parseFloat(form.paid_amount) > newTotal) {
        form.paid_amount = newTotal
      }
    })
    
    // Initialize on mount - single onMounted
    onMounted(async () => {
      await Promise.all([loadSuppliers(), loadWarehouses()])
      
      if (isEditMode.value) {
        await loadPurchaseData()
      }
    })
    
    return {
      loading,
      loadingMessage,
      isEditMode,
      purchaseData,
      suppliers,
      warehouses,
      cart,
      form,
      payments,
      newPayment,
      searchQuery,
      searchResults,
      subtotal,
      totalDiscount,
      totalTax,
      totalAmount,
      remainingBalance,
      totalQuantity,
      paymentProgress,
      canSubmit,
      formatNumber,
      formatDate,
      getPaymentStatusLabel,
      getPaymentMethodLabel,
      validatePaymentAmount,
      calculateItemTotal,
      searchProducts,
      addToCart,
      removeFromCart,
      addPayment,
      receivePurchase,
      submitPurchase,
      deletePurchase
    }
  }
}
</script>

<style scoped>
@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}

.overflow-x-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f7fafc;
}

.overflow-x-auto::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f7fafc;
  border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background-color: #cbd5e0;
  border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background-color: #94a3b8;
}

input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
  opacity: 0.5;
}

input:focus, select:focus {
  outline: none;
}

input:read-only {
  background-color: #f3f4f6;
  cursor: not-allowed;
}

.max-h-80 {
  max-height: 20rem;
}
</style>