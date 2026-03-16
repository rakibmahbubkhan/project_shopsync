<template>
  <div class="p-6 max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border">
      <h1 class="text-2xl font-bold text-gray-800">Record New Stock Purchase</h1>
      <router-link to="/purchases" class="text-gray-500 hover:text-gray-700">
        Cancel
      </router-link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column - Items Selection (2/3 width) -->
      <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border">
        <h3 class="font-bold mb-4 text-lg">Items Selection</h3>
        
        <!-- Product Search with Dropdown -->
        <div class="mb-4 relative">
          <input 
            type="text" 
            v-model="searchQuery"
            @input="searchProducts"
            placeholder="Search products by name or SKU..."
            class="w-full border rounded-xl p-3 pl-10 bg-gray-50 focus:ring-2 focus:ring-blue-500 outline-none"
            autocomplete="off"
          >
          <span class="absolute left-3 top-3.5 text-gray-400">🔍</span>
          
          <!-- Search Results Dropdown -->
          <div 
            v-if="searchResults.length > 0" 
            class="absolute z-10 w-full mt-1 bg-white border rounded-xl shadow-xl max-h-80 overflow-y-auto divide-y"
          >
            <div 
              v-for="product in searchResults" 
              :key="product.id"
              @click="addToCart(product)"
              class="p-3 hover:bg-blue-50 cursor-pointer flex justify-between items-center"
            >
              <div>
                <div class="font-medium">{{ product.name }}</div>
                <div class="text-sm text-gray-500">
                  SKU: {{ product.sku || product.code }} | Cost: ৳{{ product.purchase_price || product.cost_price }}
                </div>
              </div>
              <div class="text-xs bg-gray-100 px-2 py-1 rounded">
                Stock: {{ product.stock_quantity || product.stock || 0 }}
              </div>
            </div>
          </div>
        </div>

        <!-- Cart Items Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
              <tr>
                <th class="p-3 text-left">Product</th>
                <th class="p-3 text-left">SKU</th>
                <th class="p-3 text-left">Qty</th>
                <th class="p-3 text-left">Unit Cost (৳)</th>
                <th class="p-3 text-left">Discount %</th>
                <th class="p-3 text-left">Tax %</th>
                <th class="p-3 text-right">Subtotal</th>
                <th class="p-3 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="(item, index) in cart" :key="index" class="hover:bg-gray-50">
                <td class="p-3 font-medium">{{ item.name }}</td>
                <td class="p-3 text-gray-500">{{ item.sku || item.code }}</td>
                <td class="p-3">
                  <input 
                    type="number" 
                    v-model="item.quantity" 
                    @input="calculateItemTotal(item)"
                    min="0.01"
                    step="0.01"
                    class="w-20 border rounded-lg p-1.5 focus:ring-1 focus:ring-blue-500"
                  >
                </td>
                <td class="p-3">
                  <input 
                    type="number" 
                    v-model="item.purchase_price" 
                    @input="calculateItemTotal(item)"
                    min="0"
                    step="0.01"
                    class="w-24 border rounded-lg p-1.5 focus:ring-1 focus:ring-blue-500"
                  >
                </td>
                <td class="p-3">
                  <input 
                    type="number" 
                    v-model="item.discount" 
                    @input="calculateItemTotal(item)"
                    min="0"
                    max="100"
                    step="0.1"
                    class="w-16 border rounded-lg p-1.5 focus:ring-1 focus:ring-blue-500"
                    placeholder="0%"
                  >
                </td>
                <td class="p-3">
                  <input 
                    type="number" 
                    v-model="item.tax" 
                    @input="calculateItemTotal(item)"
                    min="0"
                    max="100"
                    step="0.1"
                    class="w-16 border rounded-lg p-1.5 focus:ring-1 focus:ring-blue-500"
                    placeholder="0%"
                  >
                </td>
                <td class="p-3 text-right font-bold text-gray-700">
                  ৳{{ formatNumber(item.total || (item.quantity * item.purchase_price)) }}
                </td>
                <td class="p-3 text-center">
                  <button @click="removeFromCart(index)" class="text-red-400 hover:text-red-600 text-xl">
                    ✕
                  </button>
                </td>
              </tr>
              
              <!-- Empty Cart Message -->
              <tr v-if="cart.length === 0">
                <td colspan="8" class="p-12 text-center text-gray-400 italic">
                  No items added to this purchase yet. Search and select products above.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Cart Summary Cards -->
        <div v-if="cart.length > 0" class="mt-6 p-4 bg-gray-50 rounded-xl border">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
              <span class="text-xs font-bold text-gray-400 uppercase">Total Items</span>
              <div class="text-xl font-bold text-gray-800">{{ cart.length }}</div>
            </div>
            <div>
              <span class="text-xs font-bold text-gray-400 uppercase">Total Quantity</span>
              <div class="text-xl font-bold text-gray-800">{{ formatNumber(totalQuantity) }}</div>
            </div>
            <div>
              <span class="text-xs font-bold text-gray-400 uppercase">Subtotal</span>
              <div class="text-xl font-bold text-gray-800">৳{{ formatNumber(subtotal) }}</div>
            </div>
            <div>
              <span class="text-xs font-bold text-gray-400 uppercase">Tax Amount</span>
              <div class="text-xl font-bold text-gray-800">৳{{ formatNumber(totalTax) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column - Purchase Details (1/3 width) -->
      <div class="space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border space-y-4">
          <h3 class="font-bold text-lg mb-2">Purchase Details</h3>
          
          <!-- Supplier Selection -->
          <div>
            <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Supplier *</label>
            <select v-model="form.supplier_id" class="w-full border rounded-lg p-2.5 bg-white" required>
              <option value="">Select Supplier</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>

          <!-- Warehouse Selection -->
          <div>
            <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Target Warehouse *</label>
            <select v-model="form.warehouse_id" class="w-full border rounded-lg p-2.5 bg-white" required>
              <option value="">Select Warehouse</option>
              <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select>
          </div>

          <!-- Purchase Date -->
          <div>
            <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Purchase Date</label>
            <input 
              type="date" 
              v-model="form.purchase_date" 
              class="w-full border rounded-lg p-2.5 bg-white"
            >
          </div>

          <!-- Purchase Status -->
          <div>
            <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Status</label>
            <select v-model="form.status" class="w-full border rounded-lg p-2.5 bg-white">
              <option value="ordered">Ordered</option>
              <option value="received">Received</option>
              <option value="pending">Pending</option>
            </select>
          </div>

          <!-- Payment Status -->
          <div>
            <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Payment Status</label>
            <select v-model="form.payment_status" class="w-full border rounded-lg p-2.5 bg-white">
              <option value="unpaid">Unpaid</option>
              <option value="partial">Partial</option>
              <option value="paid">Paid</option>
            </select>
          </div>

          <!-- Paid Amount -->
          <div>
            <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Paid Amount (৳)</label>
            <input 
              type="number" 
              v-model="form.paid_amount" 
              min="0"
              step="0.01"
              class="w-full border rounded-lg p-2.5"
              :max="totalAmount"
            >
          </div>

          <!-- Totals Section -->
          <div class="pt-4 border-t space-y-2">
            <div class="flex justify-between text-gray-600">
              <span>Subtotal:</span>
              <span>৳{{ formatNumber(subtotal) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Discount:</span>
              <span class="text-red-500">- ৳{{ formatNumber(totalDiscount) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Tax:</span>
              <span class="text-green-600">+ ৳{{ formatNumber(totalTax) }}</span>
            </div>
            <div class="flex justify-between font-bold text-xl pt-2 border-t">
              <span>Total:</span>
              <span>৳{{ formatNumber(totalAmount) }}</span>
            </div>
            
            <!-- Payment Summary -->
            <div v-if="form.paid_amount > 0" class="mt-2 p-3 bg-green-50 rounded-lg">
              <div class="flex justify-between text-sm text-green-700">
                <span>Paid:</span>
                <span>- ৳{{ formatNumber(form.paid_amount) }}</span>
              </div>
              <div v-if="form.paid_amount < totalAmount" class="flex justify-between text-sm text-orange-600 mt-1">
                <span>Due:</span>
                <span>৳{{ formatNumber(totalAmount - form.paid_amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            @click="submitPurchase" 
            :disabled="loading || !canSubmit"
            class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Processing...' : submitButtonText }}
          </button>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-8 rounded-2xl shadow-xl text-center">
        <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-600 border-t-transparent mx-auto"></div>
        <p class="mt-4 text-lg text-gray-700">{{ loadingMessage }}</p>
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
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #cbd5e0;
  border-radius: 3px;
}

/* Remove spinner from number inputs */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
  opacity: 0.5;
}
</style>