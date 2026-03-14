<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">New Stock Purchase</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column - Items Selection -->
      <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border">
        <h3 class="font-bold mb-4">Items Selection</h3>
        
        <!-- Product Search -->
        <div class="mb-4 relative">
          <input 
            type="text" 
            v-model="searchQuery"
            @input="searchProducts"
            placeholder="Search products by name or code..."
            class="w-full border rounded-lg p-2 pl-10"
          >
          <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
          
          <!-- Search Results Dropdown -->
          <div v-if="searchResults.length > 0" class="absolute z-10 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
            <div 
              v-for="product in searchResults" 
              :key="product.id"
              @click="addToCart(product)"
              class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0"
            >
              <div class="font-medium">{{ product.name }}</div>
              <div class="text-sm text-gray-500">
                Code: {{ product.code }} | Price: ৳{{ product.purchase_price }}
                <span v-if="product.unit" class="ml-2">Unit: {{ product.unit }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Cart Items Table -->
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="p-2 text-left">Product</th>
              <th class="p-2 text-left">Code</th>
              <th class="p-2 text-left">Qty</th>
              <th class="p-2 text-left">Unit Cost</th>
              <th class="p-2 text-left">Discount %</th>
              <th class="p-2 text-left">Tax %</th>
              <th class="p-2 text-right">Subtotal</th>
              <th class="p-2 text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in cart" :key="index" class="border-b hover:bg-gray-50">
              <td class="p-2 font-medium">{{ item.name }}</td>
              <td class="p-2 text-gray-500">{{ item.code }}</td>
              <td class="p-2">
                <input 
                  type="number" 
                  v-model="item.quantity" 
                  @input="calculateItemTotal(item)"
                  min="0.01"
                  step="0.01"
                  class="w-20 border rounded p-1"
                >
              </td>
              <td class="p-2">
                <input 
                  type="number" 
                  v-model="item.purchase_price" 
                  @input="calculateItemTotal(item)"
                  min="0"
                  step="0.01"
                  class="w-24 border rounded p-1"
                >
              </td>
              <td class="p-2">
                <input 
                  type="number" 
                  v-model="item.discount" 
                  @input="calculateItemTotal(item)"
                  min="0"
                  max="100"
                  step="0.1"
                  class="w-16 border rounded p-1"
                  placeholder="0%"
                >
              </td>
              <td class="p-2">
                <input 
                  type="number" 
                  v-model="item.tax" 
                  @input="calculateItemTotal(item)"
                  min="0"
                  max="100"
                  step="0.1"
                  class="w-16 border rounded p-1"
                  placeholder="0%"
                >
              </td>
              <td class="p-2 text-right font-medium">
                ৳{{ formatNumber(item.total || (item.quantity * item.purchase_price)) }}
              </td>
              <td class="p-2 text-center">
                <button @click="removeFromCart(index)" class="text-red-500 hover:text-red-700">
                  🗑️
                </button>
              </td>
            </tr>
            
            <!-- Empty Cart Message -->
            <tr v-if="cart.length === 0">
              <td colspan="8" class="p-8 text-center text-gray-500">
                No items added. Search and select products above.
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Cart Summary -->
        <div v-if="cart.length > 0" class="mt-4 p-4 bg-gray-50 rounded-lg">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
              <span class="text-sm text-gray-500">Total Items</span>
              <div class="font-bold">{{ cart.length }}</div>
            </div>
            <div>
              <span class="text-sm text-gray-500">Total Quantity</span>
              <div class="font-bold">{{ formatNumber(totalQuantity) }}</div>
            </div>
            <div>
              <span class="text-sm text-gray-500">Subtotal</span>
              <div class="font-bold">৳{{ formatNumber(subtotal) }}</div>
            </div>
            <div>
              <span class="text-sm text-gray-500">Tax Amount</span>
              <div class="font-bold">৳{{ formatNumber(totalTax) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column - Purchase Details -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border h-fit">
        <h3 class="font-bold mb-4">Purchase Details</h3>
        
        <label class="block text-sm font-bold mb-1">Supplier *</label>
        <select v-model="form.supplier_id" class="w-full border rounded-lg p-2 mb-4" required>
          <option value="">Select Supplier</option>
          <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>

        <label class="block text-sm font-bold mb-1">Target Warehouse *</label>
        <select v-model="form.warehouse_id" class="w-full border rounded-lg p-2 mb-4" required>
          <option value="">Select Warehouse</option>
          <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
        </select>

        <label class="block text-sm font-bold mb-1">Purchase Date</label>
        <input type="date" v-model="form.purchase_date" class="w-full border rounded-lg p-2 mb-4">

        <label class="block text-sm font-bold mb-1">Payment Status</label>
        <select v-model="form.payment_status" class="w-full border rounded-lg p-2 mb-4">
          <option value="unpaid">Unpaid</option>
          <option value="partial">Partial</option>
          <option value="paid">Paid</option>
        </select>

        <label class="block text-sm font-bold mb-1">Paid Amount</label>
        <input 
          type="number" 
          v-model="form.paid_amount" 
          min="0"
          step="0.01"
          class="w-full border rounded-lg p-2 mb-4"
          :max="totalAmount"
        >

        <label class="block text-sm font-bold mb-1">Status</label>
        <select v-model="form.status" class="w-full border rounded-lg p-2 mb-6">
          <option value="pending">Pending</option>
          <option value="ordered">Ordered</option>
          <option value="received">Received</option>
        </select>

        <div class="border-t pt-4">
          <div class="flex justify-between mb-2">
            <span class="text-gray-600">Subtotal:</span>
            <span>৳{{ formatNumber(subtotal) }}</span>
          </div>
          <div class="flex justify-between mb-2">
            <span class="text-gray-600">Discount:</span>
            <span>- ৳{{ formatNumber(totalDiscount) }}</span>
          </div>
          <div class="flex justify-between mb-2">
            <span class="text-gray-600">Tax:</span>
            <span>+ ৳{{ formatNumber(totalTax) }}</span>
          </div>
          <div class="flex justify-between font-bold text-lg mb-4">
            <span>Total:</span>
            <span>৳{{ formatNumber(totalAmount) }}</span>
          </div>
          <div v-if="form.paid_amount > 0" class="flex justify-between text-sm mb-2 text-green-600">
            <span>Paid:</span>
            <span>- ৳{{ formatNumber(form.paid_amount) }}</span>
          </div>
          <div v-if="form.paid_amount < totalAmount && form.paid_amount > 0" class="flex justify-between text-sm mb-4 text-orange-600">
            <span>Due:</span>
            <span>৳{{ formatNumber(totalAmount - form.paid_amount) }}</span>
          </div>

          <button 
            @click="submitPurchase" 
            class="w-full bg-primary text-white py-3 rounded-xl font-bold disabled:bg-gray-300 disabled:cursor-not-allowed"
            :disabled="!canSubmit"
          >
            {{ submitButtonText }}
          </button>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
        <p class="mt-4 text-gray-600">{{ loadingMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

export default {
  name: 'NewPurchase',
  
  setup() {
    const router = useRouter()
    const toast = useToast()
    
    // State
    const loading = ref(false)
    const loadingMessage = ref('')
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
        return sum + (item.quantity * item.purchase_price)
      }, 0)
    })
    
    const totalDiscount = computed(() => {
      return cart.value.reduce((sum, item) => {
        const itemSubtotal = item.quantity * item.purchase_price
        const discountAmount = (itemSubtotal * (item.discount || 0)) / 100
        return sum + discountAmount
      }, 0)
    })
    
    const totalTax = computed(() => {
      return cart.value.reduce((sum, item) => {
        const itemSubtotal = item.quantity * item.purchase_price
        const discountAmount = (itemSubtotal * (item.discount || 0)) / 100
        const taxableAmount = itemSubtotal - discountAmount
        const taxAmount = (taxableAmount * (item.tax || 0)) / 100
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
      return 'Complete Purchase'
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
          const response = await axios.get('/api/products/search', {
            params: { query: searchQuery.value }
          })
          searchResults.value = response.data
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
        toast.info(`Increased quantity for ${product.name}`)
      } else {
        cart.value.push({
          product_id: product.id,
          name: product.name,
          code: product.code,
          quantity: 1,
          purchase_price: product.purchase_price || 0,
          discount: 0,
          tax: 0,
          total: product.purchase_price || 0
        })
        toast.success(`${product.name} added to cart`)
      }
      
      // Clear search
      searchQuery.value = ''
      searchResults.value = []
    }
    
    const removeFromCart = (index) => {
      const item = cart.value[index]
      cart.value.splice(index, 1)
      toast.warning(`${item.name} removed from cart`)
    }
    
    const loadSuppliers = async () => {
      try {
        const response = await axios.get('/api/suppliers?active=1')
        suppliers.value = response.data.data || response.data
      } catch (error) {
        toast.error('Failed to load suppliers')
        console.error(error)
      }
    }
    
    const loadWarehouses = async () => {
      try {
        const response = await axios.get('/api/warehouses?is_active=1')
        warehouses.value = response.data.data || response.data
      } catch (error) {
        toast.error('Failed to load warehouses')
        console.error(error)
      }
    }
    
    const submitPurchase = async () => {
      if (!canSubmit.value) return
      
      loading.value = true
      loadingMessage.value = 'Creating purchase...'
      
      try {
        const payload = {
          ...form,
          items: cart.value.map(item => ({
            product_id: item.product_id,
            quantity: parseFloat(item.quantity),
            purchase_price: parseFloat(item.purchase_price),
            discount: parseFloat(item.discount || 0),
            tax: parseFloat(item.tax || 0)
          }))
        }
        
        const response = await axios.post('/api/purchases', payload)
        
        toast.success('Purchase created successfully!')
        
        // Redirect to purchase details or list
        setTimeout(() => {
          router.push(`/purchases/${response.data.data.id}`)
        }, 1500)
        
      } catch (error) {
        console.error('Purchase failed:', error)
        
        if (error.response?.data?.errors) {
          // Validation errors
          Object.values(error.response.data.errors).forEach(msg => {
            toast.error(msg[0])
          })
        } else {
          toast.error(error.response?.data?.message || 'Failed to create purchase')
        }
      } finally {
        loading.value = false
        loadingMessage.value = ''
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
    loadSuppliers()
    loadWarehouses()
    
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
/* Add any component-specific styles here */
</style>