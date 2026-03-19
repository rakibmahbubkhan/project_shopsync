<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 p-3 md:p-6">
    <!-- Main Container -->
    <div class="max-w-7xl mx-auto">
      
      <!-- Header -->
      <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Point of Sale</h1>
          <p class="text-sm text-gray-500 mt-1">{{ new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
        </div>
        
        <!-- Quick Stats -->
        <div class="flex gap-3">
          <div class="bg-white px-4 py-2 rounded-xl shadow-sm">
            <p class="text-xs text-gray-500">Items in cart</p>
            <p class="text-xl font-bold text-indigo-600">{{ cart.length }}</p>
          </div>
          <div class="bg-white px-4 py-2 rounded-xl shadow-sm">
            <p class="text-xs text-gray-500">Total</p>
            <p class="text-xl font-bold text-green-600">৳{{ total.toFixed(0) }}</p>
          </div>
        </div>
      </div>

      <!-- Mobile/Desktop Layout -->
      <div class="flex flex-col lg:flex-row gap-6">
        
        <!-- LEFT: PRODUCT SECTION -->
        <div class="flex-1 bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 border border-white/50">
          
          <!-- Search Bar with Barcode Icon -->
          <div class="relative mb-6">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              v-model="search"
              @input="searchProducts"
              placeholder="Scan barcode, search by name or SKU..."
              class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100 outline-none transition-all bg-white"
              autofocus
            />
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
              <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-lg">⌘ K</span>
            </div>
          </div>
          
          <!-- Product Grid -->
          <div v-if="products.length > 0" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4 overflow-y-auto max-h-[calc(100vh-300px)] pb-4">
            <div
              v-for="product in products"
              :key="product.id"
              @click="addToCart(product)"
              class="group bg-white border-2 border-gray-100 rounded-xl p-4 hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-100 cursor-pointer transition-all duration-200 transform hover:-translate-y-1"
            >
              <!-- Product Image Placeholder -->
              <div class="w-full h-20 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-lg mb-3 flex items-center justify-center">
                <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
              
              <h4 class="font-semibold text-gray-800 line-clamp-2">{{ product.name }}</h4>
              
              <!-- Stock Indicator -->
              <div class="mt-2 flex items-center gap-1">
                <span class="text-xs" :class="(product.stock_quantity || product.stock) > 10 ? 'text-green-600' : (product.stock_quantity || product.stock) > 0 ? 'text-orange-500' : 'text-red-500'">
                  ●
                </span>
                <p class="text-xs text-gray-500">Stock: {{ product.stock_quantity || product.stock }}</p>
              </div>
              
              <!-- Price -->
              <div class="mt-3 flex items-center justify-between">
                <span class="text-lg font-bold text-indigo-600">৳{{ product.selling_price }}</span>
                <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Add +</span>
              </div>
            </div>
          </div>
          
          <!-- Empty State -->
          <div v-else class="flex flex-col items-center justify-center py-12 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
              <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-700 mb-1">Search Products</h3>
            <p class="text-sm text-gray-400">Start typing to find products</p>
          </div>
          
        </div>

        <!-- RIGHT: CART SECTION -->
        <div class="lg:w-96 bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 flex flex-col h-[calc(100vh-120px)] lg:h-auto">
          
          <!-- Cart Header -->
          <div class="p-6 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
              Current Order
            </h2>
          </div>
          
          <!-- Customer & Payment Selection -->
          <div class="p-6 space-y-4 bg-gradient-to-r from-indigo-50 to-purple-50">
            
            <!-- Customer Selection with Avatar -->
            <div class="relative">
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 block">Customer</label>
              <select v-model="selectedCustomer" class="w-full p-3 border-2 border-white rounded-xl focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100 outline-none bg-white appearance-none cursor-pointer">
                <option value="" disabled>Select customer</option>
                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <div class="absolute inset-y-0 right-0 top-6 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
            
            <!-- Warehouse Selection -->
            <div class="relative">
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 block">Warehouse</label>
              <select v-model="selectedWarehouse" class="w-full p-3 border-2 border-white rounded-xl focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100 outline-none bg-white appearance-none cursor-pointer">
                <option value="" disabled>Select warehouse</option>
                <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
              </select>
              <div class="absolute inset-y-0 right-0 top-6 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>

            <!-- Payment Method Tabs -->
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 block">Payment Method</label>
              <div class="grid grid-cols-3 gap-2">
                <button
                  v-for="method in paymentMethods"
                  :key="method.value"
                  @click="paymentMethod = method.value"
                  class="p-2 rounded-xl text-sm font-medium transition-all"
                  :class="paymentMethod === method.value ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-50'"
                >
                  {{ method.label }}
                </button>
              </div>
            </div>
            
          </div>

          <!-- Cart Items -->
          <div class="flex-1 overflow-y-auto p-6 space-y-4">
            
            <div v-for="item in cart" :key="item.id" class="group bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition-all">
              
              <!-- Item Header -->
              <div class="flex justify-between items-start mb-2">
                <div>
                  <p class="font-semibold text-gray-800">{{ item.name }}</p>
                  <p class="text-sm text-gray-500">৳{{ item.selling_price }} each</p>
                </div>
                <button @click="removeFromCart(item.id)" class="text-gray-400 hover:text-red-500 transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
              
              <!-- Quantity Controls -->
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <button
                    @click="decreaseQty(item)"
                    :disabled="item.quantity <= 1"
                    class="w-8 h-8 flex items-center justify-center bg-white rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >−</button>
                  <span class="w-8 text-center font-medium">{{ item.quantity }}</span>
                  <button
                    @click="increaseQty(item)"
                    :disabled="item.quantity >= (item.stock || item.stock_quantity)"
                    class="w-8 h-8 flex items-center justify-center bg-white rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >+</button>
                </div>
                <span class="font-bold text-indigo-600">৳{{ (item.selling_price * item.quantity).toFixed(0) }}</span>
              </div>
              
            </div>
            
            <div v-if="cart.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
              <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
              <p class="text-gray-400">Your cart is empty</p>
              <p class="text-sm text-gray-300">Add items to get started</p>
            </div>
            
          </div>

          <!-- Totals & Checkout -->
          <div class="p-6 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
            
            <!-- Price Breakdown -->
            <div class="space-y-2 mb-4">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium">৳{{ subtotal.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Tax (5%)</span>
                <span class="font-medium">৳{{ tax.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                <span>Total</span>
                <span class="text-indigo-600">৳{{ total.toFixed(2) }}</span>
              </div>
            </div>
            
            <!-- Checkout Button -->
            <button
              @click="checkout"
              :disabled="loading || !cart.length || !selectedCustomer || !selectedWarehouse"
              class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-xl font-bold hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg disabled:from-gray-300 disabled:to-gray-400 disabled:cursor-not-allowed transform hover:scale-[1.02] active:scale-[0.98]"
            >
              <span v-if="loading" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
              </span>
              <span v-else class="flex items-center justify-center gap-2">
                Complete Transaction
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
              </span>
            </button>
            
          </div>
          
        </div>
        
      </div>
      
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import api from "@/api/axios";

// Payment methods
const paymentMethods = [
  { value: 'cash', label: 'Cash' },
  { value: 'card', label: 'Card' },
  { value: 'wallet', label: 'Wallet' }
];

// State
const search = ref("");
const products = ref([]);
const cart = ref([]);
const customers = ref([]);
const warehouses = ref([]);
const selectedCustomer = ref(null);
const selectedWarehouse = ref(null);
const paymentMethod = ref('cash');
const loading = ref(false);

// Search products
const searchProducts = async () => {
  if (!search.value) {
    products.value = [];
    return;
  }
  
  try {
    const params = { 
      search: search.value,
      warehouse_id: selectedWarehouse.value || undefined 
    };
    const res = await api.get('/products', { params });
    products.value = res.data.data || [];
  } catch (error) {
    console.error('Error searching products:', error);
  }
};

// Add to cart with stock validation
const addToCart = (product) => {
  const availableStock = product.stock_quantity || product.stock;
  
  const exists = cart.value.find(i => i.id === product.id);
  if (exists) {
    if (exists.quantity < availableStock) {
      exists.quantity++;
    } else {
      alert(`Only ${availableStock} items available in stock`);
    }
  } else {
    cart.value.push({ 
      ...product, 
      quantity: 1,
      stock: availableStock
    });
  }
};

// Cart item controls
const increaseQty = (item) => {
  if (item.quantity < item.stock) {
    item.quantity++;
  } else {
    alert(`Only ${item.stock} items available in stock`);
  }
};

const decreaseQty = (item) => {
  if (item.quantity > 1) {
    item.quantity--;
  }
};

const removeFromCart = (id) => {
  cart.value = cart.value.filter(i => i.id !== id);
};

// Computed totals
const subtotal = computed(() => 
  cart.value.reduce((sum, i) => sum + (i.selling_price * i.quantity), 0)
);

const tax = computed(() => subtotal.value * 0.05);

const total = computed(() => subtotal.value + tax.value);

// Checkout
const checkout = async () => {
  // Validate selections
  if (!selectedCustomer.value || !selectedWarehouse.value) {
    alert("Please select a customer and warehouse");
    return;
  }
  
  if (!cart.value.length) {
    alert("Cart is empty. Please add items to continue.");
    return;
  }

  loading.value = true;
  
  try {
    const payload = {
      customer_id: selectedCustomer.value,
      warehouse_id: selectedWarehouse.value,
      sale_date: new Date().toISOString().slice(0, 10),
      payment_method: paymentMethod.value,
      payment_status: 'paid',
      tax: tax.value,
      discount: 0,
      items: cart.value.map(i => ({ 
        product_id: i.id, 
        quantity: i.quantity, 
        selling_price: i.selling_price 
      }))
    };
    
    const res = await api.post('/sales', payload);
    
    // Open receipt
    if (res.data?.id) {
      const receiptUrl = `${import.meta.env.VITE_API_URL}/sales/${res.data.id}/receipt`;
      window.open(receiptUrl, '_blank');
    }
    
    // Reset POS
    cart.value = [];
    search.value = "";
    products.value = [];
    
    alert(`Sale #${res.data.id} completed successfully!`);
    
  } catch (error) {
    console.error('Checkout error:', error);
    const errorMessage = error.response?.data?.message || error.message || "Checkout Failed";
    alert(`Transaction Failed: ${errorMessage}`);
  } finally {
    loading.value = false;
  }
};

// Watch for warehouse change to refresh product search
watch(selectedWarehouse, () => {
  if (search.value) {
    searchProducts();
  }
});

// Load initial data
onMounted(async () => {
  try {
    const [cRes, wRes] = await Promise.all([
      api.get('/customers'),
      api.get('/warehouses')
    ]);
    
    customers.value = cRes.data.data || cRes.data || [];
    warehouses.value = wRes.data.data || wRes.data || [];
    
    // Set defaults
    if (customers.value.length) {
      selectedCustomer.value = customers.value[0].id;
    }
    if (warehouses.value.length) {
      selectedWarehouse.value = warehouses.value[0].id;
    }
  } catch (error) {
    console.error('Error loading metadata:', error);
    alert('Failed to load customers and warehouses');
  }
});
</script>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f1f5f9;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 5px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth transitions */
* {
  transition: background-color 0.2s, border-color 0.2s, box-shadow 0.2s;
}

/* Better focus styles */
input:focus, select:focus, button:focus {
  outline: none;
}

/* Mobile optimizations */
@media (max-width: 640px) {
  .max-h-\[calc\(100vh-300px\)\] {
    max-height: calc(100vh - 350px);
  }
}
</style>