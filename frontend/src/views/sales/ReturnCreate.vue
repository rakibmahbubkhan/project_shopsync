<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/30">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8 max-w-5xl">
      
      <!-- Back Navigation -->
      <div class="mb-4">
        <button 
          @click="goBack" 
          class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-700 transition-colors group"
        >
          <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Returns List
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loadingSale" class="bg-white rounded-2xl p-12 text-center shadow-sm">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-red-200 border-t-red-600 mx-auto"></div>
        <p class="mt-4 text-slate-500">Loading sale details...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="loadError" class="bg-white rounded-2xl p-12 text-center shadow-sm">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-slate-800 mb-2">Failed to Load Sale</h3>
        <p class="text-slate-500 mb-4">{{ loadError }}</p>
        <div class="flex gap-3 justify-center">
          <button @click="loadSale" class="px-6 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition-colors">
            Try Again
          </button>
          <button @click="goBack" class="px-6 py-2.5 border-2 border-slate-200 text-slate-600 rounded-xl font-medium hover:bg-slate-50 transition-colors">
            Go Back
          </button>
        </div>
      </div>

      <!-- Main Content -->
      <template v-if="sale && !loadingSale && !loadError">
        
        <!-- Sale Header Card -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/50 p-6 mb-6">
          <div class="flex flex-col lg:flex-row lg:items-center gap-6">
            <!-- Sale Identity -->
            <div class="flex items-center gap-4 flex-1">
              <div class="w-14 h-14 bg-gradient-to-br from-red-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg shadow-red-200 flex-shrink-0">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 4V9a2 2 0 00-2-2h-1" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl font-bold text-slate-800">Process Return</h1>
                <p class="text-sm text-slate-500">Invoice #{{ sale.id }} • {{ formatDate(sale.sale_date) }}</p>
              </div>
            </div>

            <!-- Quick Info Badges -->
            <div class="flex flex-wrap gap-3">
              <div class="flex items-center gap-2 px-4 py-2 bg-slate-100 rounded-xl">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-sm font-medium text-slate-700">{{ sale.customer?.name || 'Walk-in Customer' }}</span>
              </div>
              <div class="flex items-center gap-2 px-4 py-2 bg-slate-100 rounded-xl">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-sm font-medium text-slate-700">{{ sale.warehouse?.name || 'N/A' }}</span>
              </div>
              <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 rounded-xl">
                <span class="text-sm font-semibold text-blue-700">Total: ৳{{ formatNumber(sale.total_amount) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Items to Return Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
          <!-- Card Header -->
          <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
              <h2 class="text-lg font-bold text-slate-800">Return Items</h2>
              <p class="text-sm text-slate-500 mt-0.5">
                Select products and specify quantities to return
                <span v-if="totalSelectedItems > 0" class="text-red-500 font-medium">
                  • {{ totalSelectedItems }} item(s) selected
                </span>
              </p>
            </div>
            <!-- Bulk Actions -->
            <div class="flex gap-2">
              <button 
                @click="selectAllAvailable"
                class="text-xs px-3 py-1.5 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition-colors font-medium"
              >
                Select All Available
              </button>
              <button 
                @click="clearAll"
                v-if="totalSelectedItems > 0"
                class="text-xs px-3 py-1.5 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition-colors font-medium"
              >
                Clear All
              </button>
            </div>
          </div>

          <!-- Items Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                  <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Product</th>
                  <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Sold</th>
                  <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Returned</th>
                  <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Available</th>
                  <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider w-48">Return Qty</th>
                  <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit Price</th>
                  <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Refund</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr 
                  v-for="item in sale.items" 
                  :key="item.id"
                  :class="[
                    'transition-colors',
                    returnQuantities[item.id] > 0 
                      ? 'bg-red-50/40 hover:bg-red-50/60' 
                      : 'hover:bg-slate-50/50',
                    item.available_for_return <= 0 ? 'opacity-50' : ''
                  ]"
                >
                  <!-- Product Info -->
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                      </div>
                      <div>
                        <p class="font-medium text-slate-800">{{ item.product_name || 'Product' }}</p>
                        <p class="text-xs text-slate-400">SKU: {{ item.product_sku || 'N/A' }}</p>
                        <p v-if="item.available_for_return <= 0" class="text-xs text-red-500 mt-0.5 font-medium">
                          Fully returned
                        </p>
                      </div>
                    </div>
                  </td>

                  <!-- Sold Qty -->
                  <td class="px-6 py-4 text-center">
                    <span class="text-sm font-medium text-slate-600">{{ item.quantity }}</span>
                  </td>

                  <!-- Already Returned -->
                  <td class="px-6 py-4 text-center">
                    <span class="text-sm text-slate-500">{{ item.already_returned || 0 }}</span>
                  </td>

                  <!-- Available -->
                  <td class="px-6 py-4 text-center">
                    <span 
                      class="text-sm font-semibold"
                      :class="item.available_for_return > 0 ? 'text-green-600' : 'text-red-400'"
                    >
                      {{ item.available_for_return }}
                    </span>
                  </td>

                  <!-- Return Quantity Input -->
                  <td class="px-6 py-4">
                    <div v-if="item.available_for_return > 0" class="flex items-center justify-center gap-2">
                      <!-- Decrement -->
                      <button 
                        @click="decrementQty(item)"
                        :disabled="!returnQuantities[item.id]"
                        class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                      </button>
                      
                      <!-- Quantity Input -->
                      <input 
                        type="number" 
                        v-model.number="returnQuantities[item.id]"
                        @input="validateQty(item)"
                        @focus="$event.target.select()"
                        :max="item.available_for_return"
                        min="0"
                        step="1"
                        class="w-20 px-3 py-2 border-2 rounded-lg text-center font-medium text-sm focus:outline-none transition-all"
                        :class="returnQuantities[item.id] > 0 
                          ? 'border-red-300 bg-red-50 text-red-700 focus:border-red-500 focus:ring-4 focus:ring-red-100' 
                          : 'border-slate-200 text-slate-600 focus:border-slate-400'"
                      >
                      
                      <!-- Increment -->
                      <button 
                        @click="incrementQty(item)"
                        :disabled="returnQuantities[item.id] >= item.available_for_return"
                        class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                      </button>
                    </div>
                    <div v-else class="text-center">
                      <span class="text-xs text-slate-400">—</span>
                    </div>
                    
                    <!-- Quick Select Buttons -->
                    <div v-if="item.available_for_return > 0" class="flex justify-center gap-1 mt-1.5">
                      <button 
                        v-for="qty in getQuickQuantities(item.available_for_return)"
                        :key="qty"
                        @click="returnQuantities[item.id] = qty"
                        class="text-[10px] px-1.5 py-0.5 rounded-md transition-colors"
                        :class="returnQuantities[item.id] === qty 
                          ? 'bg-red-100 text-red-700 font-semibold' 
                          : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                      >
                        {{ qty }}
                      </button>
                      <button 
                        @click="returnQuantities[item.id] = item.available_for_return"
                        class="text-[10px] px-1.5 py-0.5 rounded-md transition-colors"
                        :class="returnQuantities[item.id] === item.available_for_return 
                          ? 'bg-red-100 text-red-700 font-semibold' 
                          : 'bg-red-50 text-red-500 hover:bg-red-100'"
                      >
                        All
                      </button>
                    </div>
                    
                    <!-- Validation Error -->
                    <p v-if="quantityErrors[item.id]" class="text-[10px] text-red-500 text-center mt-1">
                      {{ quantityErrors[item.id] }}
                    </p>
                  </td>

                  <!-- Unit Price -->
                  <td class="px-6 py-4 text-right">
                    <span class="text-sm font-medium text-slate-700">৳{{ formatNumber(item.selling_price) }}</span>
                  </td>

                  <!-- Refund Subtotal -->
                  <td class="px-6 py-4 text-right">
                    <span 
                      class="text-sm font-semibold"
                      :class="returnQuantities[item.id] > 0 ? 'text-red-600' : 'text-slate-300'"
                    >
                      ৳{{ formatNumber((returnQuantities[item.id] || 0) * item.selling_price) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Table Footer with Totals -->
          <div class="bg-slate-50 border-t-2 border-slate-200 px-6 py-4">
            <div class="flex flex-col sm:flex-row justify-between items-end gap-2">
              <div class="text-sm text-slate-500">
                <span v-if="totalSelectedItems > 0">
                  {{ totalSelectedItems }} item(s) selected • 
                  Total quantity: {{ totalSelectedQuantity }}
                </span>
                <span v-else>No items selected</span>
              </div>
              <div class="text-right">
                <p class="text-sm text-slate-500">Total Refund Amount</p>
                <p class="text-2xl font-bold text-red-600">৳{{ formatNumber(totalRefundAmount) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Return Details Form -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
          <h2 class="text-lg font-bold text-slate-800 mb-5">Return Details</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Reason -->
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Reason for Return <span class="text-red-500">*</span>
              </label>
              <textarea 
                v-model="form.reason" 
                rows="3"
                placeholder="Please provide a reason for this return (required)..."
                class="w-full px-4 py-3 border-2 rounded-xl focus:outline-none transition-all resize-none"
                :class="formErrors.reason 
                  ? 'border-red-400 focus:ring-4 focus:ring-red-50' 
                  : 'border-slate-200 focus:border-red-400 focus:ring-4 focus:ring-red-50'"
                @input="formErrors.reason = ''"
              ></textarea>
              <div class="flex justify-between mt-1">
                <p v-if="formErrors.reason" class="text-xs text-red-500">{{ formErrors.reason }}</p>
                <p v-else class="text-xs text-slate-400">Required</p>
                <p class="text-xs text-slate-400">{{ form.reason.length }}/500</p>
              </div>
            </div>

            <!-- Refund Method -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Refund Method <span class="text-red-500">*</span>
              </label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  v-for="method in refundMethods"
                  :key="method.value"
                  @click="form.refund_method = method.value"
                  type="button"
                  class="flex items-center gap-2.5 px-4 py-3 border-2 rounded-xl transition-all text-left"
                  :class="form.refund_method === method.value 
                    ? 'border-red-400 bg-red-50 text-red-700 shadow-sm' 
                    : 'border-slate-200 hover:border-slate-300 text-slate-600'"
                >
                  <span class="text-xl">{{ method.icon }}</span>
                  <div>
                    <span class="text-sm font-medium block">{{ method.label }}</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Additional Notes <span class="text-slate-400 font-normal">(optional)</span>
              </label>
              <textarea 
                v-model="form.notes" 
                rows="3"
                placeholder="Any additional information..."
                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-slate-400 focus:ring-4 focus:ring-slate-50 outline-none transition-all resize-none"
              ></textarea>
              <p class="text-xs text-slate-400 mt-1">{{ form.notes.length }}/1000</p>
            </div>
          </div>
        </div>

        <!-- Return Summary Card -->
        <div v-if="totalSelectedItems > 0" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
          <h2 class="text-lg font-bold text-slate-800 mb-4">Return Summary</h2>
          
          <div class="space-y-2">
            <div 
              v-for="item in selectedItemsSummary" 
              :key="item.id"
              class="flex justify-between items-center py-2 border-b border-slate-100 last:border-0"
            >
              <div class="flex-1">
                <p class="text-sm font-medium text-slate-700">{{ item.product_name }}</p>
                <p class="text-xs text-slate-400">{{ returnQuantities[item.id] }} × ৳{{ formatNumber(item.selling_price) }}</p>
              </div>
              <p class="text-sm font-semibold text-red-600">৳{{ formatNumber(returnQuantities[item.id] * item.selling_price) }}</p>
            </div>
          </div>
          
          <div class="mt-4 pt-4 border-t-2 border-slate-200 flex justify-between items-center">
            <div>
              <p class="text-sm text-slate-500">Total Refund</p>
              <p class="text-xs text-slate-400">{{ totalSelectedItems }} item(s) • {{ totalSelectedQuantity }} units</p>
            </div>
            <p class="text-2xl font-bold text-red-600">৳{{ formatNumber(totalRefundAmount) }}</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
          <button 
            @click="goBack"
            class="px-6 py-3 border-2 border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition-all font-medium text-center"
          >
            Cancel
          </button>
          
          <button 
            @click="processReturn" 
            :disabled="!canSubmit || submitting"
            class="flex items-center justify-center gap-2 px-8 py-3.5 bg-gradient-to-r from-red-600 to-orange-600 text-white rounded-xl font-bold shadow-lg shadow-red-200 transition-all transform active:scale-95 disabled:from-slate-300 disabled:to-slate-400 disabled:shadow-none disabled:cursor-not-allowed disabled:transform-none"
            :class="canSubmit && !submitting ? 'hover:from-red-700 hover:to-orange-700 hover:scale-[1.02]' : ''"
          >
            <!-- Spinner -->
            <svg v-if="submitting" class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <!-- Check Icon -->
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ submitting ? 'Processing...' : `Process Return & Refund ৳${formatNumber(totalRefundAmount)}` }}
          </button>
        </div>
      </template>

    </div>

    <!-- Success Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="goToList">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
          <div class="relative bg-white rounded-2xl p-8 shadow-2xl text-center max-w-md w-full">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Return Processed Successfully!</h3>
            <p class="text-slate-500 mb-2">Return #{{ completedReturnId }} has been created.</p>
            <p class="text-sm text-slate-400 mb-6">
              Stock has been restored to warehouse and refund of 
              <span class="font-semibold text-red-600">৳{{ formatNumber(completedReturnAmount) }}</span> 
              has been issued.
            </p>
            <div class="flex gap-3 justify-center">
              <button 
                @click="goToList"
                class="px-6 py-2.5 border-2 border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition-all font-medium"
              >
                Back to Returns
              </button>
              <button 
                @click="startNewReturn"
                class="px-6 py-2.5 bg-gradient-to-r from-red-600 to-orange-600 text-white rounded-xl font-medium hover:shadow-lg transition-all"
              >
                New Return
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Confirm Dialog -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showConfirmDialog" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showConfirmDialog = false">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
          <div class="relative bg-white rounded-2xl p-6 shadow-2xl max-w-md w-full">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-bold text-slate-800">Confirm Return</h3>
                <p class="text-sm text-slate-500 mt-1">
                  You are about to refund <strong class="text-red-600">৳{{ formatNumber(totalRefundAmount) }}</strong> 
                  for {{ totalSelectedItems }} item(s). This will restore stock to the warehouse.
                </p>
                <div class="flex gap-3 mt-4">
                  <button 
                    @click="showConfirmDialog = false"
                    class="flex-1 px-4 py-2.5 border-2 border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition-all font-medium text-sm"
                  >
                    Cancel
                  </button>
                  <button 
                    @click="confirmProcessReturn"
                    class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all font-medium text-sm"
                  >
                    Confirm Return
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/api/axios';

const route = useRoute();
const router = useRouter();

// ==================== STATE ====================
const sale = ref(null);
const loadingSale = ref(true);
const loadError = ref('');
const submitting = ref(false);
const showSuccessModal = ref(false);
const showConfirmDialog = ref(false);
const completedReturnId = ref(null);
const completedReturnAmount = ref(0);

// Form
const returnQuantities = ref({});
const quantityErrors = ref({});
const form = reactive({
  reason: '',
  notes: '',
  refund_method: 'cash'
});
const formErrors = reactive({
  reason: ''
});

// Refund methods
const refundMethods = [
  { value: 'cash', label: 'Cash', icon: '💵' },
  { value: 'card', label: 'Card', icon: '💳' },
  { value: 'bank_transfer', label: 'Bank Transfer', icon: '🏦' },
  { value: 'mobile_banking', label: 'Mobile Banking', icon: '📱' }
];

// ==================== COMPUTED ====================
const totalSelectedItems = computed(() => {
  return Object.entries(returnQuantities.value)
    .filter(([, qty]) => qty > 0)
    .length;
});

const totalSelectedQuantity = computed(() => {
  return Object.values(returnQuantities.value)
    .filter(qty => qty > 0)
    .reduce((sum, qty) => sum + qty, 0);
});

const totalRefundAmount = computed(() => {
  if (!sale.value?.items) return 0;
  return sale.value.items.reduce((sum, item) => {
    return sum + ((returnQuantities.value[item.id] || 0) * item.selling_price);
  }, 0);
});

const selectedItemsSummary = computed(() => {
  if (!sale.value?.items) return [];
  return sale.value.items.filter(item => (returnQuantities.value[item.id] || 0) > 0);
});

const canSubmit = computed(() => {
  return totalSelectedItems.value > 0 && 
         form.reason.trim().length > 0 && 
         !submitting.value;
});

// ==================== WATCHERS ====================
watch(() => form.reason, (val) => {
  if (val.length > 500) form.reason = val.slice(0, 500);
});

watch(() => form.notes, (val) => {
  if (val.length > 1000) form.notes = val.slice(0, 1000);
});

// ==================== METHODS ====================

// Helpers
const formatNumber = (value) => parseFloat(value || 0).toFixed(2);

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-BD', {
    year: 'numeric', month: 'short', day: 'numeric'
  });
};

const getQuickQuantities = (max) => {
  const quantities = [];
  if (max >= 1) quantities.push(1);
  if (max >= 2) quantities.push(2);
  if (max >= 5) quantities.push(5);
  if (max >= 10) quantities.push(10);
  // Filter out duplicates and sort
  return [...new Set(quantities)].sort((a, b) => a - b);
};

// Navigation
const goBack = () => {
  router.push('/sales/returns');
};

const goToList = () => {
  router.push('/sales/returns');
};

const startNewReturn = () => {
  showSuccessModal.value = false;
  sale.value = null;
  returnQuantities.value = {};
  quantityErrors.value = {};
  form.reason = '';
  form.notes = '';
  form.refund_method = 'cash';
  formErrors.reason = '';
  loadingSale.value = true;
  loadError.value = '';
  loadSale();
};

// Quantity Management
const incrementQty = (item) => {
  const current = returnQuantities.value[item.id] || 0;
  if (current < item.available_for_return) {
    returnQuantities.value[item.id] = current + 1;
    quantityErrors.value[item.id] = '';
  }
};

const decrementQty = (item) => {
  const current = returnQuantities.value[item.id] || 0;
  if (current > 0) {
    returnQuantities.value[item.id] = current - 1;
    quantityErrors.value[item.id] = '';
  }
};

const validateQty = (item) => {
  const qty = returnQuantities.value[item.id];
  
  if (qty === undefined || qty === null || qty === '' || isNaN(qty)) {
    returnQuantities.value[item.id] = 0;
    quantityErrors.value[item.id] = '';
    return;
  }
  
  if (qty < 0) {
    quantityErrors.value[item.id] = 'Min: 0';
    returnQuantities.value[item.id] = 0;
    return;
  }
  
  if (qty > item.available_for_return) {
    quantityErrors.value[item.id] = `Max: ${item.available_for_return}`;
    returnQuantities.value[item.id] = item.available_for_return;
    return;
  }
  
  if (!Number.isInteger(qty)) {
    returnQuantities.value[item.id] = Math.floor(qty);
  }
  
  quantityErrors.value[item.id] = '';
};

const selectAllAvailable = () => {
  if (!sale.value?.items) return;
  sale.value.items.forEach(item => {
    if (item.available_for_return > 0) {
      returnQuantities.value[item.id] = item.available_for_return;
      quantityErrors.value[item.id] = '';
    }
  });
};

const clearAll = () => {
  if (!sale.value?.items) return;
  sale.value.items.forEach(item => {
    returnQuantities.value[item.id] = 0;
    quantityErrors.value[item.id] = '';
  });
};

// API Calls
const loadSale = async () => {
  const saleId = route.query.sale_id || route.params.saleId;
  
  if (!saleId) {
    loadError.value = 'No sale ID provided. Please select a sale from the returns list.';
    loadingSale.value = false;
    return;
  }

  loadingSale.value = true;
  loadError.value = '';

  try {
    const { data } = await api.get('/returns/search-sales', {
      params: { search: saleId.toString() }
    });
    
    const matchingSales = data || [];
    const found = matchingSales.find(s => s.id == saleId);
    
    if (found) {
      sale.value = found;
    } else {
      // Try direct sale endpoint
      const saleResponse = await api.get(`/sales/${saleId}`);
      const saleData = saleResponse.data.data || saleResponse.data;
      sale.value = {
        ...saleData,
        items: (saleData.items || []).map(item => ({
          ...item,
          product_name: item.product?.name || item.product_name || 'Product',
          product_sku: item.product?.sku || item.product_sku || 'N/A',
          already_returned: 0,
          available_for_return: item.quantity
        }))
      };
    }
    
    // Initialize quantities
    sale.value.items.forEach(item => {
      returnQuantities.value[item.id] = 0;
    });
    
  } catch (error) {
    console.error('Failed to load sale:', error);
    loadError.value = error.response?.data?.message || 'Failed to load sale details. The sale may not exist or has no returnable items.';
  } finally {
    loadingSale.value = false;
  }
};

const processReturn = () => {
  // Validate
  if (!form.reason.trim()) {
    formErrors.reason = 'Please provide a reason for the return';
    return;
  }
  
  if (totalRefundAmount.value > 5000) {
    showConfirmDialog.value = true;
    return;
  }
  
  confirmProcessReturn();
};

const confirmProcessReturn = async () => {
  showConfirmDialog.value = false;
  submitting.value = true;

  const items = sale.value.items
    .filter(item => (returnQuantities.value[item.id] || 0) > 0)
    .map(item => ({
      product_id: item.product_id,
      quantity: returnQuantities.value[item.id],
      discount: 0,
      tax: 0
    }));

  try {
    const { data } = await api.post('/returns', {
      sale_id: sale.value.id,
      items: items,
      reason: form.reason,
      notes: form.notes || undefined,
      refund_method: form.refund_method
    });

    completedReturnId.value = data.data?.id || 'N/A';
    completedReturnAmount.value = totalRefundAmount.value;
    showSuccessModal.value = true;
    
  } catch (error) {
    console.error('Return failed:', error);
    alert(error.response?.data?.message || 'Failed to process return. Please try again.');
  } finally {
    submitting.value = false;
  }
};

// ==================== LIFECYCLE ====================
onMounted(() => {
  loadSale();
});
</script>

<style scoped>
/* Modal Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.25s ease;
}
.modal-enter-active > div:last-child,
.modal-leave-active > div:last-child {
  transition: transform 0.25s ease, opacity 0.25s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from > div:last-child {
  transform: scale(0.95) translateY(10px);
  opacity: 0;
}
.modal-leave-to > div:last-child {
  transform: scale(0.95) translateY(10px);
  opacity: 0;
}

/* Remove number input spinners */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}
</style>