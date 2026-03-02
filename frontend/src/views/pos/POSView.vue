<template>
  <div class="flex h-screen bg-gray-100">

    <!-- LEFT: PRODUCT SECTION -->
    <div class="w-2/3 p-6 flex flex-col">

      <!-- Search / Barcode Input -->
      <input
        v-model="search"
        @input="searchProducts"
        placeholder="Scan barcode or search product..."
        class="w-full p-3 border rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500"
        autofocus
      />

      <!-- Product Grid -->
      <div class="grid grid-cols-3 gap-4 overflow-y-auto">
        <div
          v-for="product in products"
          :key="product.id"
          @click="addToCart(product)"
          class="bg-white p-4 rounded-xl shadow hover:shadow-lg cursor-pointer transition"
        >
          <h3 class="font-semibold text-gray-800">
            {{ product.name }}
          </h3>

          <p class="text-sm text-gray-500">
            Stock: {{ product.stock }}
          </p>

          <p class="mt-2 font-bold text-blue-600">
            ৳ {{ product.selling_price }}
          </p>
        </div>
      </div>

    </div>

    <!-- RIGHT: CART SECTION -->
    <div class="w-1/3 bg-white p-6 shadow-xl flex flex-col">

      <h2 class="text-xl font-bold mb-4">
        Cart
      </h2>

      <!-- Customer, Warehouse, Payment Selection (Added above Cart Items) -->
      <div class="mb-4 space-y-3 bg-gray-50 p-3 rounded-lg border">
        <div>
          <label class="text-xs font-bold text-gray-500 uppercase">Customer</label>
          <select v-model="selectedCustomer" class="w-full border rounded p-1 text-sm bg-white">
            <option value="" disabled>Select customer</option>
            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        
        <div>
          <label class="text-xs font-bold text-gray-500 uppercase">Warehouse</label>
          <select v-model="selectedWarehouse" class="w-full border rounded p-1 text-sm bg-white">
            <option value="" disabled>Select warehouse</option>
            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
          </select>
        </div>

        <div>
          <label class="text-xs font-bold text-gray-500 uppercase">Payment Method</label>
          <select v-model="paymentMethod" class="w-full border rounded p-1 text-sm bg-white">
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="wallet">Mobile Wallet</option>
          </select>
        </div>
      </div>

      <!-- Cart Items -->
      <div class="flex-1 overflow-y-auto border-t pt-4">

        <div
          v-for="item in cart"
          :key="item.id"
          class="flex justify-between items-center border-b py-3"
        >
          <div>
            <p class="font-medium">
              {{ item.name }}
            </p>

            <div class="flex items-center gap-2 mt-1">
              <button
                @click="decreaseQty(item)"
                class="px-2 bg-gray-200 rounded hover:bg-gray-300"
              >-</button>

              <span>{{ item.quantity }}</span>

              <button
                @click="increaseQty(item)"
                class="px-2 bg-gray-200 rounded hover:bg-gray-300"
              >+</button>
            </div>

            <p class="text-sm text-gray-500">
              ৳ {{ item.selling_price }} each
            </p>
          </div>

          <button
            @click="removeFromCart(item.id)"
            class="text-red-500 font-bold hover:text-red-700"
          >
            ✕
          </button>
        </div>

        <div v-if="cart.length === 0" class="text-center text-gray-400 py-8">
          Cart is empty
        </div>

      </div>

      <!-- Totals -->
      <div class="border-t pt-4 mt-4 space-y-2">
        <div class="flex justify-between">
          <span>Subtotal</span>
          <span>৳ {{ subtotal.toFixed(2) }}</span>
        </div>

        <div class="flex justify-between">
          <span>Tax (5%)</span>
          <span>৳ {{ tax.toFixed(2) }}</span>
        </div>

        <div class="flex justify-between text-lg font-bold">
          <span>Total</span>
          <span>৳ {{ total.toFixed(2) }}</span>
        </div>

        <button
          @click="checkout"
          :disabled="cart.length === 0 || loading || !selectedCustomer || !selectedWarehouse"
          class="w-full bg-blue-600 text-white py-3 rounded-lg mt-3 hover:bg-blue-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed"
        >
          {{ loading ? 'Processing...' : 'Complete Sale' }}
        </button>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue"
import api from "@/api/axios"

// Core refs
const search = ref("")
const products = ref([])
const cart = ref([])
const loading = ref(false)

// Metadata refs
const customers = ref([])
const warehouses = ref([])
const selectedCustomer = ref("")
const selectedWarehouse = ref("")
const paymentMethod = ref("cash")

// Fetch initialization data
const fetchMetadata = async () => {
  try {
    const [custRes, wareRes] = await Promise.all([
      api.get('/customers'),
      api.get('/warehouses')
    ])
    
    customers.value = custRes.data.data || []
    warehouses.value = wareRes.data.data || []
    
    // Set defaults
    if (customers.value.length) {
      selectedCustomer.value = customers.value[0].id
    }
    if (warehouses.value.length) {
      selectedWarehouse.value = warehouses.value[0].id
    }
  } catch (error) {
    console.error('Error fetching metadata:', error)
    alert('Failed to load customers and warehouses')
  }
}

// Search products
const searchProducts = async () => {
  if (!search.value) {
    products.value = []
    return
  }

  try {
    const response = await api.get("/products", {
      params: { 
        search: search.value,
        warehouse_id: selectedWarehouse.value || undefined 
      }
    })

    products.value = response.data.data || []
  } catch (error) {
    console.error('Error searching products:', error)
  }
}

// Add to cart
const addToCart = (product) => {
  const existing = cart.value.find(i => i.id === product.id)

  if (existing) {
    if (existing.quantity < product.stock) {
      existing.quantity++
    } else {
      alert(`Only ${product.stock} items available in stock`)
    }
  } else {
    cart.value.push({
      id: product.id,
      name: product.name,
      selling_price: product.selling_price,
      quantity: 1,
      stock: product.stock
    })
  }
}

// Cart item controls
const increaseQty = (item) => {
  if (item.quantity < item.stock) {
    item.quantity++
  } else {
    alert(`Only ${item.stock} items available in stock`)
  }
}

const decreaseQty = (item) => {
  if (item.quantity > 1) {
    item.quantity--
  }
}

const removeFromCart = (id) => {
  cart.value = cart.value.filter(i => i.id !== id)
}

// Computed totals
const subtotal = computed(() =>
  cart.value.reduce((sum, item) =>
    sum + item.quantity * item.selling_price, 0)
)

const tax = computed(() => subtotal.value * 0.05)

const total = computed(() => subtotal.value + tax.value)

// Checkout
const checkout = async () => {
  if (!selectedCustomer.value || !selectedWarehouse.value) {
    alert("Please select a customer and warehouse")
    return
  }

  try {
    loading.value = true

    const response = await api.post("/sales", {
      customer_id: selectedCustomer.value,
      warehouse_id: selectedWarehouse.value,
      payment_method: paymentMethod.value,
      payment_status: 'paid',
      sale_date: new Date().toISOString().split('T')[0],
      tax: tax.value,
      discount: 0,
      // Map cart items to the keys the backend expects
      items: cart.value.map(item => ({
        product_id: item.id,
        quantity: item.quantity,
        selling_price: item.selling_price
      }))
    })

    // Open receipt in new tab using the ID from JSON response
    if (response.data.id) {
      window.open(`/api/sales/${response.data.id}/receipt`, "_blank")
    }

    // Reset POS
    cart.value = []
    search.value = ""
    products.value = []
    alert("Transaction Complete")

  } catch (error) {
    console.error('Checkout error:', error)
    alert(error.response?.data?.message || "Sale failed")
  } finally {
    loading.value = false
  }
}

// Watch for warehouse change to refresh product search
watch(selectedWarehouse, () => {
  if (search.value) {
    searchProducts()
  }
})

// Load metadata on mount
onMounted(fetchMetadata)
</script>

<style scoped>
/* Optional: Add smooth scrolling for cart */
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
</style>