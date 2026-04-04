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
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Record New Stock Purchase</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Create a new purchase order and manage inventory</p>
              </div>
            </div>
            <router-link to="/purchases" class="text-gray-500 hover:text-gray-700 inline-flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100 transition-all">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Cancel
            </router-link>
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
            <p class="text-sm text-gray-500 mt-1">Search and add products to this purchase order</p>
          </div>
          
          <div class="p-4 sm:p-6">
            <!-- Product Search with Dropdown -->
            <div class="mb-6 relative">
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
                      Cost: ৳{{ product.purchase_price || product.cost_price }}
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

            <!-- Cart Items Table - Desktop -->
            <div class="hidden lg:block overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SKU</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Qty</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit Cost</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Discount %</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tax %</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Subtotal</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
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
                        class="w-20 px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-purple-300 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
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
                          class="w-24 pl-6 pr-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-purple-300 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
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
                          class="w-20 px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-purple-300 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
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
                          class="w-20 px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-purple-300 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
                          placeholder="0"
                        >
                        <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                      </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                      <span class="font-semibold text-purple-600">৳{{ formatNumber(item.total || (item.quantity * item.purchase_price)) }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                      <button @click="removeFromCart(index)" class="text-red-400 hover:text-red-600 transition-colors p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </td>
                  </tr>
                  
                  <!-- Empty Cart Message -->
                  <tr v-if="cart.length === 0">
                    <td colspan="8" class="px-4 py-12 text-center">
                      <div class="flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-700 mb-1">Cart is empty</h3>
                        <p class="text-sm text-gray-400">Search and add products to get started</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Cart Items - Mobile Card View -->
            <div class="lg:hidden space-y-3">
              <div v-for="(item, index) in cart" :key="index" class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <div class="flex justify-between items-start mb-3">
                  <div>
                    <h4 class="font-semibold text-gray-800">{{ item.name }}</h4>
                    <p class="text-xs text-gray-500 mt-0.5">SKU: {{ item.sku || item.code }}</p>
                  </div>
                  <button @click="removeFromCart(index)" class="text-red-400 hover:text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                
                <div class="space-y-2">
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="text-xs text-gray-500">Quantity</label>
                      <input 
                        type="number" 
                        v-model="item.quantity" 
                        @input="calculateItemTotal(item)"
                        min="0.01"
                        step="0.01"
                        class="w-full px-2 py-1.5 border border-gray-200 rounded-lg focus:border-purple-300 outline-none"
                      >
                    </div>
                    <div>
                      <label class="text-xs text-gray-500">Unit Cost</label>
                      <input 
                        type="number" 
                        v-model="item.purchase_price" 
                        @input="calculateItemTotal(item)"
                        min="0"
                        step="0.01"
                        class="w-full px-2 py-1.5 border border-gray-200 rounded-lg focus:border-purple-300 outline-none"
                      >
                    </div>
                    <div>
                      <label class="text-xs text-gray-500">Discount %</label>
                      <input 
                        type="number" 
                        v-model="item.discount" 
                        @input="calculateItemTotal(item)"
                        min="0"
                        max="100"
                        step="0.1"
                        class="w-full px-2 py-1.5 border border-gray-200 rounded-lg focus:border-purple-300 outline-none"
                      >
                    </div>
                    <div>
                      <label class="text-xs text-gray-500">Tax %</label>
                      <input 
                        type="number" 
                        v-model="item.tax" 
                        @input="calculateItemTotal(item)"
                        min="0"
                        max="100"
                        step="0.1"
                        class="w-full px-2 py-1.5 border border-gray-200 rounded-lg focus:border-purple-300 outline-none"
                      >
                    </div>
                  </div>
                  <div class="flex justify-between pt-2 border-t border-gray-200">
                    <span class="text-sm text-gray-600">Subtotal:</span>
                    <span class="font-semibold text-purple-600">৳{{ formatNumber(item.total || (item.quantity * item.purchase_price)) }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Mobile Empty Cart -->
              <div v-if="cart.length === 0" class="bg-gray-50 rounded-xl p-8 text-center border border-gray-100">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-700 mb-1">Cart is empty</h3>
                <p class="text-sm text-gray-400">Search and add products to get started</p>
              </div>
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
              <p class="text-sm text-gray-500 mt-1">Fill in the purchase information</p>
            </div>
            
            <div class="p-4 sm:p-6 space-y-4">
              <!-- Supplier Selection -->
              <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Supplier *</label>
                <select v-model="form.supplier_id" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white" required>
                  <option value="">Select Supplier</option>
                  <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>

              <!-- Warehouse Selection -->
              <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Target Warehouse *</label>
                <select v-model="form.warehouse_id" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white" required>
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
                >
              </div>

              <!-- Purchase Status -->
              <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Status</label>
                <select v-model="form.status" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white">
                  <option value="ordered">📦 Ordered</option>
                  <option value="received">✅ Received</option>
                  <option value="pending">⏳ Pending</option>
                </select>
              </div>

              <!-- Payment Status -->
              <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Payment Status</label>
                <select v-model="form.payment_status" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white">
                  <option value="unpaid">💰 Unpaid</option>
                  <option value="partial">💳 Partial</option>
                  <option value="paid">✅ Paid</option>
                </select>
              </div>

              <!-- Paid Amount -->
              <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Paid Amount (৳)</label>
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

              <!-- Totals Section -->
              <div class="pt-4 border-t border-gray-200 space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Subtotal:</span>
                  <span class="font-medium">৳{{ formatNumber(subtotal) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Discount:</span>
                  <span class="text-red-600">- ৳{{ formatNumber(totalDiscount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Tax:</span>
                  <span class="text-green-600">+ ৳{{ formatNumber(totalTax) }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                  <span>Total:</span>
                  <span class="text-purple-600">৳{{ formatNumber(totalAmount) }}</span>
                </div>
                
                <!-- Payment Summary -->
                <div v-if="form.paid_amount > 0" class="mt-3 p-3 bg-green-50 rounded-xl">
                  <div class="flex justify-between text-sm text-green-700">
                    <span>Paid:</span>
                    <span class="font-semibold">- ৳{{ formatNumber(form.paid_amount) }}</span>
                  </div>
                  <div v-if="form.paid_amount < totalAmount" class="flex justify-between text-sm text-orange-600 mt-1">
                    <span>Due:</span>
                    <span class="font-semibold">৳{{ formatNumber(totalAmount - form.paid_amount) }}</span>
                  </div>
                </div>
              </div>

              <!-- Submit Button -->
              <button 
                @click="submitPurchase" 
                :disabled="loading || !canSubmit"
                class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white py-4 rounded-xl font-bold transition-all shadow-lg shadow-purple-200 disabled:from-gray-300 disabled:to-gray-400 disabled:cursor-not-allowed transform hover:scale-[1.02] active:scale-95"
              >
                <span v-if="!loading" class="flex items-center justify-center gap-2">
                  {{ submitButtonText }}
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
import { useRouter } from 'vue-router'
import api from '@/api/axios'

export default {
  name: 'NewPurchase',
  
  setup() {
    const router = useRouter()
    
    // State
    const loading = ref(false)
    const loadingMessage = ref('Creating purchase...')
    const suppliers = ref([])
    const warehouses = ref([])
    const cart = ref([])
    const searchQuery = ref('')
    const searchResults = ref([])
    const searchTimeout = ref(null)
    
    // Form Data
    const form = reactive({
      supplier_id: '',
      warehouse_id: '',
      purchase_date: new Date().toISOString().split('T')[0],
      payment_status: 'unpaid',
      paid_amount: 0,
      status: 'ordered'
    })
    
    // Computed Properties
    const subtotal = computed(() => {
      return cart.value.reduce((sum, item) => {
        return sum + (parseFloat(item.quantity || 0) * parseFloat(item.purchase_price || 0))
      }, 0)
    })
    
    const totalDiscount = computed(() => {
      return cart.value.reduce((sum, item) => {
        const itemSubtotal = parseFloat(item.quantity || 0) * parseFloat(item.purchase_price || 0)
        const discountAmount = (itemSubtotal * (parseFloat(item.discount) || 0)) / 100
        return sum + discountAmount
      }, 0)
    })
    
    const totalTax = computed(() => {
      return cart.value.reduce((sum, item) => {
        const itemSubtotal = parseFloat(item.quantity || 0) * parseFloat(item.purchase_price || 0)
        const discountAmount = (itemSubtotal * (parseFloat(item.discount) || 0)) / 100
        const taxableAmount = itemSubtotal - discountAmount
        const taxAmount = (taxableAmount * (parseFloat(item.tax) || 0)) / 100
        return sum + taxAmount
      }, 0)
    })
    
    const totalAmount = computed(() => {
      return subtotal.value - totalDiscount.value + totalTax.value
    })
    
    const totalQuantity = computed(() => {
      return cart.value.reduce((sum, item) => sum + parseFloat(item.quantity || 0), 0)
    })
    
    const canSubmit = computed(() => {
      return form.supplier_id && 
             form.warehouse_id && 
             cart.value.length > 0
    })
    
    const submitButtonText = computed(() => {
      if (!form.supplier_id) return 'Select Supplier'
      if (!form.warehouse_id) return 'Select Warehouse'
      if (cart.value.length === 0) return 'Add Items to Cart'
      return 'Confirm Purchase'
    })
    
    // Methods
    const formatNumber = (value) => {
      return parseFloat(value || 0).toFixed(2)
    }
    
    const calculateItemTotal = (item) => {
      const quantity = parseFloat(item.quantity) || 0
      const price = parseFloat(item.purchase_price) || 0
      const discount = parseFloat(item.discount) || 0
      const tax = parseFloat(item.tax) || 0
      
      const subtotal = quantity * price
      const discountAmount = (subtotal * discount) / 100
      const taxableAmount = subtotal - discountAmount
      const taxAmount = (taxableAmount * tax) / 100
      
      item.total = subtotal - discountAmount + taxAmount
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
            params: { search: searchQuery.value }
          })
          searchResults.value = response.data.data || response.data
        } catch (error) {
          console.error('Product search failed:', error)
        }
      }, 300)
    }
    
    const addToCart = (product) => {
      // Check if product already in cart
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
      
      // Clear search
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
    
    const submitPurchase = async () => {
      if (!canSubmit.value) return
      
      loading.value = true
      
      try {
        // Auto-set paid_amount to total if payment status is paid
        if (form.payment_status === 'paid') {
          form.paid_amount = totalAmount.value
        }
        
        const payload = {
          ...form,
          paid_amount: parseFloat(form.paid_amount) || 0,
          items: cart.value.map(item => ({
            product_id: item.product_id,
            quantity: parseFloat(item.quantity),
            purchase_price: parseFloat(item.purchase_price),
            discount: parseFloat(item.discount || 0),
            tax: parseFloat(item.tax || 0)
          }))
        }
        
        const response = await api.post('/purchases', payload)
        
        alert('Purchase created successfully!')
        
        // Redirect to purchase list
        router.push('/purchases')
        
      } catch (error) {
        console.error('Purchase failed:', error)
        const errorMessage = error.response?.data?.message || 'Failed to create purchase'
        alert(errorMessage)
      } finally {
        loading.value = false
      }
    }
    
    // Watch for paid amount to auto-update payment status
    watch(() => form.paid_amount, (newVal) => {
      const paid = parseFloat(newVal) || 0
      
      if (paid >= totalAmount.value && totalAmount.value > 0) {
        form.payment_status = 'paid'
      } else if (paid > 0) {
        form.payment_status = 'partial'
      } else {
        form.payment_status = 'unpaid'
      }
    })
    
    // Watch for total amount changes to validate paid amount
    watch(totalAmount, (newTotal) => {
      if (parseFloat(form.paid_amount) > newTotal) {
        form.paid_amount = newTotal
      }
    })
    
    // Load initial data
    onMounted(() => {
      loadSuppliers()
      loadWarehouses()
    })
    
    return {
      loading,
      loadingMessage,
      suppliers,
      warehouses,
      cart,
      form,
      searchQuery,
      searchResults,
      subtotal,
      totalDiscount,
      totalTax,
      totalAmount,
      totalQuantity,
      canSubmit,
      submitButtonText,
      formatNumber,
      calculateItemTotal,
      searchProducts,
      addToCart,
      removeFromCart,
      submitPurchase
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

/* Smooth scrolling for dropdown */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f7fafc;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f7fafc;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #cbd5e0;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background-color: #94a3b8;
}

/* Remove spinner from number inputs */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
  opacity: 0.5;
}

/* Focus styles */
input:focus, select:focus {
  outline: none;
}

/* Smooth transitions */
* {
  transition: all 0.2s ease;
}
</style>