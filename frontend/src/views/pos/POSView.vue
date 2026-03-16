<template>
  <div class="flex h-screen bg-gray-100 p-6 gap-6">
    
    <!-- LEFT: PRODUCT SECTION -->
    <div class="flex-1 flex flex-col bg-white rounded-2xl shadow-sm p-6">
      
      <!-- Search / Barcode Input -->
      <input
        v-model="search"
        @input="searchProducts"
        placeholder="Scan barcode, search part name or SKU..."
        class="w-full p-4 border rounded-xl mb-6 outline-none focus:ring-2 focus:ring-blue-500"
        autofocus
      />
      
      <!-- Product Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 overflow-y-auto">
        <div
          v-for="product in products"
          :key="product.id"
          @click="addToCart(product)"
          class="p-4 border rounded-xl hover:bg-blue-50 cursor-pointer transition-all"
        >
          <h4 class="font-bold text-gray-800">{{ product.name }}</h4>
          <p class="text-xs text-gray-400">Stock: {{ product.stock_quantity || product.stock }}</p>
          <p class="mt-2 text-blue-600 font-bold">৳{{ product.selling_price }}</p>
        </div>
      </div>
      
    </div>

    <!-- RIGHT: CART SECTION -->
    <div class="w-96 bg-white rounded-2xl shadow-lg p-6 flex flex-col">
      
      <h2 class="text-xl font-bold mb-4">Current Order</h2>
      
      <!-- Customer, Warehouse, Payment Selection -->
      <div class="space-y-3 mb-6 bg-gray-50 p-4 rounded-xl border">
        
        <div>
          <label class="text-xs font-bold text-gray-500 uppercase">Customer</label>
          <select v-model="selectedCustomer" class="w-full p-2 border rounded-lg text-sm bg-white">
            <option value="" disabled>Select customer</option>
            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        
        <div>
          <label class="text-xs font-bold text-gray-500 uppercase">Warehouse</label>
          <select v-model="selectedWarehouse" class="w-full p-2 border rounded-lg text-sm bg-white">
            <option value="" disabled>Select warehouse</option>
            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
          </select>
        </div>

        <div>
          <label class="text-xs font-bold text-gray-500 uppercase">Payment Method</label>
          <select v-model="paymentMethod" class="w-full p-2 border rounded-lg text-sm bg-white">
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="wallet">Mobile Wallet</option>
          </select>
        </div>
        
      </div>

      <!-- Cart Items -->
      <div class="flex-1 overflow-y-auto border-t py-4">
        
        <div v-for="item in cart" :key="item.id" class="flex justify-between items-center mb-4 pb-2 border-b">
          <div>
            <p class="text-sm font-bold">{{ item.name }}</p>
            <p class="text-xs text-gray-500">৳{{ item.selling_price }} x {{ item.quantity }}</p>
            
            <!-- Quantity Controls -->
            <div class="flex items-center gap-2 mt-2">
              <button
                @click="decreaseQty(item)"
                class="px-2 bg-gray-200 rounded hover:bg-gray-300 text-sm"
                :disabled="item.quantity <= 1"
              >-</button>
              <span class="text-sm">{{ item.quantity }}</span>
              <button
                @click="increaseQty(item)"
                class="px-2 bg-gray-200 rounded hover:bg-gray-300 text-sm"
                :disabled="item.quantity >= (item.stock || item.stock_quantity)"
              >+</button>
            </div>
          </div>
          
          <button @click="removeFromCart(item.id)" class="text-red-400 hover:text-red-600">✕</button>
        </div>
        
        <div v-if="cart.length === 0" class="text-center text-gray-400 py-8">
          Cart is empty
        </div>
        
      </div>

      <!-- Totals & Checkout -->
      <div class="border-t pt-4 space-y-2">
        
        <div class="flex justify-between text-sm">
          <span>Subtotal:</span>
          <span>৳{{ subtotal.toFixed(2) }}</span>
        </div>
        
        <div class="flex justify-between text-sm">
          <span>Tax (5%):</span>
          <span>৳{{ tax.toFixed(2) }}</span>
        </div>
        
        <div class="flex justify-between font-bold text-lg">
          <span>Total:</span>
          <span>৳{{ total.toFixed(2) }}</span>
        </div>
        
        <button
          @click="checkout"
          :disabled="loading || !cart.length || !selectedCustomer || !selectedWarehouse"
          class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg disabled:bg-gray-300 disabled:cursor-not-allowed"
        >
          {{ loading ? 'Processing...' : 'Complete Transaction' }}
        </button>
        
      </div>
      
    </div>
    
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import api from "@/api/axios";

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
/* Smooth scrolling for cart */
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