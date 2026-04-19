<template>
  <div class="h-screen flex flex-col bg-gray-50 overflow-hidden">
    <!-- Top Header -->
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3 sm:py-4 flex-shrink-0">
      <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">POS System</h1>
        <div class="flex items-center gap-2 sm:gap-4">
          <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">{{ currentDateTime }}</span>
          <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white font-semibold">
            A
          </div>
        </div>
      </div>
    </div>
    
    <div class="flex-1 flex flex-col lg:flex-row min-h-0 overflow-hidden">
      
      <!-- LEFT SIDE: Products Section -->
      <div class="flex-1 flex flex-col bg-gray-50 overflow-hidden">
        
        <!-- Category Tabs -->
        <div class="bg-white border-b border-gray-200 px-2 sm:px-4">
          <div class="flex gap-1 overflow-x-auto whitespace-nowrap scrollbar-thin">
            <button
              v-for="category in categories"
              :key="category.id"
              @click="selectCategory(category.id)"
              class="px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-sm font-medium transition-colors flex-shrink-0"
              :class="selectedCategory === category.id 
                ? 'text-indigo-600 border-b-2 border-indigo-600' 
                : 'text-gray-600 hover:text-gray-900'"
            >
              {{ category.name }}
            </button>
          </div>
        </div>
        
        <!-- Search and Filter Bar -->
        <div class="p-3 sm:p-4 space-y-2 sm:space-y-3 flex-shrink-0">
          <div class="relative">
            <input
              v-model="search"
              @input="debouncedSearch"
              type="text"
              placeholder="Search By Name or SKU..."
              class="w-full pl-10 pr-20 sm:pr-24 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm"
            />
            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <button 
              @click="scanBarcode"
              class="absolute right-2 top-1.5 px-2 sm:px-3 py-0.5 text-xs text-white bg-indigo-600 rounded hover:bg-indigo-700"
            >
              Scan
            </button>
          </div>
          
          <!-- Brand Filter -->
          <select 
            v-model="selectedBrand"
            @change="filterProducts"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 text-sm bg-white"
          >
            <option value="">All Brands</option>
            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
              {{ brand.name }}
            </option>
          </select>
        </div>

        <div class="px-3 sm:px-4 mb-2 flex-shrink-0">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-xs sm:text-sm font-semibold text-gray-700">Recently Sold</h3>
            <button 
              @click="refreshRecentProducts"
              class="text-xs text-indigo-600 hover:text-indigo-700"
            >
              Refresh
            </button>
          </div>
          <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-thin">
            <div
              v-for="product in recentProducts"
              :key="product.id"
              @click="addToCart(product)"
              class="flex-shrink-0 w-24 sm:w-32 bg-white border border-gray-200 rounded-lg p-2 cursor-pointer hover:shadow-md hover:border-indigo-300 transition-all"
            >
              <div class="w-full h-14 sm:h-16 bg-gray-100 rounded-lg mb-1 flex items-center justify-center overflow-hidden">
                <img 
                  v-if="product.image" 
                  :src="product.image" 
                  :alt="product.name"
                  class="w-full h-full object-cover"
                />
                <svg v-else class="w-5 h-5 sm:w-6 sm:h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <h4 class="font-medium text-gray-800 text-xs truncate">{{ product.name }}</h4>
              <p class="text-indigo-600 font-bold text-xs mt-1">${{ product.selling_price }}</p>
            </div>
            <div v-if="recentProducts.length === 0 && !recentProductsLoading" class="text-xs text-gray-400 py-4 px-2">
              No recent sales
            </div>
            <div v-if="recentProductsLoading" class="flex-shrink-0 w-24 sm:w-32 bg-white border border-gray-200 rounded-lg p-2">
              <div class="w-full h-14 sm:h-16 bg-gray-100 rounded-lg mb-1 animate-pulse"></div>
              <div class="h-3 bg-gray-100 rounded mt-1 animate-pulse"></div>
              <div class="h-3 bg-gray-100 rounded mt-1 w-2/3 animate-pulse"></div>
            </div>
          </div>
        </div>
        
        <!-- Products Grid with Infinite Scroll -->
        <div 
          class="flex-1 overflow-y-auto px-3 sm:px-4 pb-4" 
          ref="productsContainer" 
          @scroll="handleScroll"
        >
          <!-- Loading skeleton -->
          <div v-if="productsLoading && displayedProducts.length === 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 sm:gap-3">
            <div v-for="n in 12" :key="n" class="bg-white border border-gray-200 rounded-lg p-2 sm:p-3 animate-pulse">
              <div class="w-full h-16 sm:h-20 bg-gray-200 rounded-lg mb-2"></div>
              <div class="h-3 bg-gray-200 rounded mb-2"></div>
              <div class="h-4 bg-gray-200 rounded w-2/3"></div>
            </div>
          </div>
          
          <!-- Products Grid -->
          <div v-else-if="displayedProducts.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 sm:gap-3">
            <div
              v-for="product in displayedProducts"
              :key="product.id"
              @click="addToCart(product)"
              class="bg-white border border-gray-200 rounded-lg p-2 sm:p-3 hover:shadow-lg hover:border-indigo-300 cursor-pointer transition-all"
            >
              <div class="w-full h-16 sm:h-20 bg-gray-100 rounded-lg mb-2 flex items-center justify-center overflow-hidden">
                <img 
                  v-if="product.thumbnail_url" 
                  :src="product.thumbnail_url" 
                  :alt="product.name"
                  loading="lazy"
                  class="w-full h-full object-cover"
                />
                <svg v-else class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <h4 class="font-medium text-gray-800 text-xs sm:text-sm truncate">{{ product.name }}</h4>
              <div class="flex items-center justify-between mt-1">
                <p class="text-indigo-600 font-bold text-xs sm:text-sm">${{ product.selling_price }}</p>
                <p class="text-xs text-gray-500">{{ getUnitName(product.unit) }}</p>
              </div>
            </div>
          </div>
          
          <!-- Loading more indicator -->
          <div v-if="loadingMore" class="flex justify-center py-4">
            <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          
          <div v-else-if="!productsLoading && displayedProducts.length === 0" class="flex flex-col items-center justify-center h-64 text-center">
            <svg class="w-16 h-16 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="text-gray-500">No products found</p>
          </div>
        </div>
        
      </div>
      
      <!-- RIGHT SIDE: Cart Section -->
      <div class="w-full lg:w-96 bg-white border-t lg:border-t-0 lg:border-l border-gray-200 flex flex-col shadow-lg mt-4 lg:mt-0">
        
        <!-- Cart Header -->
        <div class="p-3 sm:p-4 border-b border-gray-200 flex-shrink-0">
          <h2 class="font-semibold text-gray-800">Current Order</h2>
        </div>
        
        <!-- Order Details Form -->
        <div class="p-3 sm:p-4 space-y-3 border-b border-gray-200 flex-shrink-0">
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Customer Name *</label>
            <select v-model="selectedCustomer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 text-sm">
              <option value="" disabled>Select customer</option>
              <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Store/Location *</label>
            <select v-model="selectedWarehouse" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 text-sm">
              <option value="" disabled>Select warehouse</option>
              <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select>
          </div>
          
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Delivery Status *</label>
            <select v-model="deliveryStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 text-sm">
              <option value="delivered">Delivered</option>
              <option value="pending">Pending</option>
              <option value="processing">Processing</option>
            </select>
          </div>
        </div>
        
        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto min-h-0">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 border-b border-gray-200 sticky top-0">
                <tr>
                  <th class="text-left p-2 sm:p-3 font-medium text-gray-600">Product</th>
                  <th class="text-center p-2 sm:p-3 font-medium text-gray-600 hidden sm:table-cell">Unit</th>
                  <th class="text-center p-2 sm:p-3 font-medium text-gray-600 hidden md:table-cell">Price</th>
                  <th class="text-center p-2 sm:p-3 font-medium text-gray-600">Qty</th>
                  <th class="text-right p-2 sm:p-3 font-medium text-gray-600">Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in cart" :key="item.id" class="border-b border-gray-100 hover:bg-gray-50">
                  <td class="p-2 sm:p-3">
                    <div class="font-medium text-gray-800 text-sm">{{ item.name }}</div>
                    <button @click="removeFromCart(item.id)" class="text-xs text-red-500 hover:text-red-700 mt-1">
                      Remove
                    </button>
                    <div class="sm:hidden text-xs text-gray-500 mt-1">
                      {{ getUnitName(item.unit) }} @ ${{ item.selling_price }}
                    </div>
                   </td>
                  <td class="p-2 sm:p-3 text-center text-gray-600 hidden sm:table-cell">
                    {{ getUnitName(item.unit) }}
                   </td>
                  <td class="p-2 sm:p-3 text-center text-gray-600 hidden md:table-cell">
                    ${{ item.selling_price }}
                   </td>
                  <td class="p-2 sm:p-3">
                    <div class="flex items-center justify-center gap-1 sm:gap-2">
                      <button
                        @click="decreaseQty(item)"
                        class="w-6 h-6 flex items-center justify-center bg-gray-100 rounded hover:bg-gray-200 text-gray-600 text-sm"
                      >-</button>
                      <span class="w-6 sm:w-8 text-center text-sm">{{ item.quantity }}</span>
                      <button
                        @click="increaseQty(item)"
                        class="w-6 h-6 flex items-center justify-center bg-gray-100 rounded hover:bg-gray-200 text-gray-600 text-sm"
                      >+</button>
                    </div>
                   </td>
                  <td class="p-2 sm:p-3 text-right font-medium text-gray-800 text-sm">
                    ${{ (item.selling_price * item.quantity).toFixed(2) }}
                   </td>
                 </tr>
                
                <tr v-if="cart.length === 0">
                  <td colspan="5" class="p-8 text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <p>No items in cart</p>
                    <p class="text-xs">Click on products to add</p>
                   </td>
                 </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Totals Section -->
        <div class="border-t border-gray-200 p-3 sm:p-4 bg-gray-50 flex-shrink-0">
          <div class="space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Sub Total:</span>
              <span class="font-medium">${{ subtotal.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Tax (5%):</span>
              <span class="font-medium">${{ tax.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-base sm:text-lg font-bold pt-2 border-t border-gray-200">
              <span>Grand Total:</span>
              <span class="text-indigo-600">${{ total.toFixed(2) }}</span>
            </div>
          </div>
        </div>
        
        <!-- Payment Section -->
        <div class="border-t border-gray-200 p-3 sm:p-4 space-y-3 flex-shrink-0">
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Payment Method</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
              <button
                v-for="method in paymentMethods"
                :key="method.value"
                @click="paymentMethod = method.value"
                class="py-2 px-2 rounded-lg text-xs sm:text-sm font-medium transition-all"
                :class="paymentMethod === method.value 
                  ? 'bg-indigo-600 text-white' 
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              >
                {{ method.label }}
              </button>
            </div>
          </div>
          
          <div v-if="paymentMethod === 'card'">
            <label class="block text-xs font-medium text-gray-700 mb-1">Reference Number</label>
            <input
              v-model="referenceNumber"
              type="text"
              placeholder="Enter reference number"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 text-sm"
            />
          </div>
          
          <!-- Action Buttons -->
          <div class="flex gap-3 pt-2">
            <button
              @click="resetCart"
              class="flex-1 py-2 px-4 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors text-sm"
            >
              Cancel
            </button>
            <button
              @click="checkout"
              :disabled="loading || !cart.length || !selectedCustomer || !selectedWarehouse"
              class="flex-1 py-2 px-4 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm"
            >
              <span v-if="loading" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
              </span>
              <span v-else>Pay</span>
            </button>
          </div>
        </div>
        
      </div>
      
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import api from "@/api/axios";

// Payment Methods
const paymentMethods = [
  { value: 'cash', label: 'Cash' },
  { value: 'card', label: 'Card' },
  { value: 'bkash', label: 'bKash' },
  { value: 'nagad', label: 'Nagad' }
];

// State
const search = ref("");
const products = ref([]);
const recentProducts = ref([]);
const cart = ref([]);
const customers = ref([]);
const warehouses = ref([]);
const categories = ref([]);
const brands = ref([]);
const selectedCategory = ref(null);
const selectedBrand = ref("");
const selectedCustomer = ref(null);
const selectedWarehouse = ref(null);
const deliveryStatus = ref('delivered');
const paymentMethod = ref('cash');
const referenceNumber = ref("");
const loading = ref(false);
const productsLoading = ref(false);
const loadingMore = ref(false);
const recentProductsLoading = ref(false);
const discount = ref(0);

// Pagination
const currentPage = ref(1);
const hasMorePages = ref(true);
const productsContainer = ref(null);
let searchTimeout = null;
let scrollTimeout = null;

// Current Date Time (updates every minute)
const currentDateTime = ref("");
const updateDateTime = () => {
  currentDateTime.value = new Date().toLocaleString('en-US', { 
    hour: 'numeric', 
    minute: '2-digit',
    hour12: true,
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

// Displayed products
const displayedProducts = computed(() => products.value);

// Helper function to get unit name
const getUnitName = (unit) => {
  if (!unit) return 'pc';
  if (typeof unit === 'string') return unit;
  if (typeof unit === 'object' && unit.name) return unit.name;
  return 'pc';
};

// Helper function to extract data from API responses
const extractDataFromResponse = (response) => {
  if (response && response.success === true && response.data) {
    if (response.data.data && Array.isArray(response.data.data)) {
      return response.data.data;
    }
    if (Array.isArray(response.data)) {
      return response.data;
    }
    if (response.data && typeof response.data === 'object' && 'data' in response.data) {
      return response.data.data;
    }
  }
  if (response && response.data && Array.isArray(response.data)) {
    return response.data;
  }
  if (Array.isArray(response)) {
    return response;
  }
  return [];
};

// Load recent sold products
const loadRecentProducts = async () => {
  recentProductsLoading.value = true;
  try {
    const res = await api.get('/sales/recent-products', { params: { limit: 10 } });
    recentProducts.value = extractDataFromResponse(res.data);
  } catch (error) {
    console.warn('Recent products endpoint not available, using fallback');
    // Fallback: Use first 10 products from loaded products
    recentProducts.value = products.value.slice(0, 10);
  } finally {
    recentProductsLoading.value = false;
  }
};

// Refresh recent products
const refreshRecentProducts = () => {
  loadRecentProducts();
};

// Load products with pagination
const loadProducts = async (reset = false) => {
  if (reset) {
    currentPage.value = 1;
    products.value = [];
    hasMorePages.value = true;
  }
  
  if (!hasMorePages.value) return;
  
  const isLoading = reset ? productsLoading : loadingMore;
  if (isLoading.value) return;
  
  if (reset) {
    productsLoading.value = true;
  } else {
    loadingMore.value = true;
  }
  
  try {
    const params = {
      page: currentPage.value,
      per_page: 20,
    };
    
    if (search.value) {
      params.search = search.value;
    }
    
    if (selectedCategory.value) {
      params.category_id = selectedCategory.value;
    }
    
    if (selectedBrand.value) {
      params.brand_id = selectedBrand.value;
    }
    
    const res = await api.get('/products', { params });
    const newProducts = res.data?.data || [];
    
    if (reset) {
      products.value = newProducts;
    } else {
      products.value = [...products.value, ...newProducts];
    }
    
    hasMorePages.value = newProducts.length === 20;
    currentPage.value++;
    
  } catch (error) {
    console.error('Failed to load products:', error);
  } finally {
    productsLoading.value = false;
    loadingMore.value = false;
  }
};

// Handle scroll for infinite loading
const handleScroll = () => {
  if (!productsContainer.value) return;
  
  clearTimeout(scrollTimeout);
  scrollTimeout = setTimeout(() => {
    const { scrollTop, scrollHeight, clientHeight } = productsContainer.value;
    if (scrollTop + clientHeight >= scrollHeight - 200 && hasMorePages.value && !loadingMore.value && !productsLoading.value) {
      loadProducts(false);
    }
  }, 100);
};

// Debounced search
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadProducts(true);
    // Also refresh recent products when searching
    loadRecentProducts();
  }, 500);
};

// Filter products (reset and reload)
const filterProducts = () => {
  loadProducts(true);
};

// Select category
const selectCategory = (categoryId) => {
  selectedCategory.value = categoryId;
  loadProducts(true);
};

// Load initial data (one consolidated request)
const loadInitialData = async () => {
  try {
    // Try to get all data from consolidated endpoint
    const posInitRes = await api.get('/pos/init').catch(() => null);
    
    if (posInitRes?.data) {
      categories.value = [{ id: null, name: 'All' }, ...(posInitRes.data.categories || [])];
      brands.value = posInitRes.data.brands || [];
      customers.value = posInitRes.data.customers || [];
      warehouses.value = posInitRes.data.warehouses || [];
    } else {
      // Fallback to individual requests
      await Promise.all([
        api.get('/categories').then(res => {
          let cats = res.data?.data || res.data || [];
          categories.value = [{ id: null, name: 'All' }, ...cats];
        }).catch(() => {}),
        api.get('/brands').then(res => {
          brands.value = res.data?.data || res.data || [];
        }).catch(() => {}),
        api.get('/customers').then(res => {
          customers.value = res.data?.data || res.data || [];
        }).catch(() => {}),
        api.get('/warehouses').then(res => {
          warehouses.value = res.data?.data || res.data || [];
        }).catch(() => {})
      ]);
    }
    
    selectedCategory.value = null;
    
    if (customers.value.length > 0) {
      selectedCustomer.value = customers.value[0].id;
    }
    if (warehouses.value.length > 0) {
      selectedWarehouse.value = warehouses.value[0].id;
    }
    
    // Load products
    await loadProducts(true);
    
    // Load recent products after products are loaded
    await loadRecentProducts();
    
  } catch (error) {
    console.error('Initialization failed:', error);
    categories.value = [{ id: null, name: 'All' }];
    customers.value = [];
    warehouses.value = [];
    brands.value = [];
  }
};

// Scan barcode
const scanBarcode = () => {
  alert("Please scan barcode using your barcode scanner");
};

// Add to Cart
const addToCart = (product) => {
  if (!product) return;
  
  const availableStock = Number(product.stock_quantity || product.stock || 0);
  
  if (availableStock <= 0) {
    alert("This item is out of stock.");
    return;
  }

  const exists = cart.value.find(i => i.id === product.id);
  if (exists) {
    if (exists.quantity < availableStock) {
      exists.quantity++;
    } else {
      alert(`Insufficient stock. Only ${availableStock} units available.`);
    }
  } else {
    cart.value.push({ 
      ...product, 
      quantity: 1,
      stock: availableStock,
      unit: product.unit || 'pc'
    });
  }
};

// Cart Operations
const increaseQty = (item) => {
  if (item && item.quantity < item.stock) {
    item.quantity++;
  } else if (item) {
    alert("Maximum available stock reached.");
  }
};

const decreaseQty = (item) => {
  if (item && item.quantity > 1) item.quantity--;
};

const removeFromCart = (id) => {
  cart.value = cart.value.filter(i => i.id !== id);
};

const resetCart = () => {
  if (cart.value.length > 0 && confirm('Clear all items from cart?')) {
    cart.value = [];
  }
};

// Totals
const subtotal = computed(() => 
  cart.value.reduce((sum, i) => sum + (Number(i.selling_price) * i.quantity), 0)
);
const tax = computed(() => subtotal.value * 0.05);
const total = computed(() => subtotal.value + tax.value - discount.value);

// Print Invoice
const printInvoice = async (saleId) => {
  try {
    const response = await api.get(`/sales/${saleId}/receipt`, {
      responseType: 'blob',
      timeout: 30000
    });
    
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const blobUrl = URL.createObjectURL(blob);
    const printWindow = window.open(blobUrl, '_blank');
    
    if (!printWindow) {
      const userConfirmed = confirm('Pop-up was blocked. Would you like to download the receipt instead?');
      if (userConfirmed) {
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = `receipt_${saleId}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    }
    
    setTimeout(() => URL.revokeObjectURL(blobUrl), 10000);
  } catch (error) {
    console.error('Error printing invoice:', error);
    alert('Failed to generate receipt. Please try again.');
  }
};

// Checkout
const checkout = async () => {
  if (!selectedCustomer.value || !selectedWarehouse.value) {
    alert("Please select both a Customer and a Warehouse.");
    return;
  }
  
  if (cart.value.length === 0) {
    alert("Please add items to the cart.");
    return;
  }
  
  loading.value = true;
  
  try {
    const payload = {
      customer_id: selectedCustomer.value,
      warehouse_id: selectedWarehouse.value,
      sale_date: new Date().toISOString().split('T')[0],
      payment_method: paymentMethod.value,
      payment_status: 'paid',
      tax: tax.value,
      discount: discount.value,
      reference_number: referenceNumber.value || null,
      delivery_status: deliveryStatus.value,
      items: cart.value.map(i => ({ 
        product_id: i.id, 
        quantity: i.quantity, 
        selling_price: i.selling_price 
      }))
    };
    
    const res = await api.post('/sales', payload);
    
    if (res.data?.id) {
      // Refresh recent products after successful sale
      await loadRecentProducts();
      
      const shouldPrint = confirm('Sale completed successfully!\n\nWould you like to print the receipt?');
      if (shouldPrint) {
        await printInvoice(res.data.id);
      }
    }
    
    cart.value = [];
    search.value = "";
    referenceNumber.value = "";
    discount.value = 0;
    
    // Refresh products to update stock
    await loadProducts(true);
    
    alert("Transaction completed successfully!");
    
  } catch (error) {
    console.error('Checkout error:', error);
    let errorMessage = "Transaction failed. Please try again.";
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    }
    alert(`Transaction Failed: ${errorMessage}`);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  updateDateTime();
  const interval = setInterval(updateDateTime, 60000);
  loadInitialData();
  
  onBeforeUnmount(() => {
    clearInterval(interval);
    clearTimeout(searchTimeout);
    clearTimeout(scrollTimeout);
  });
});
</script>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar,
.overflow-x-auto::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track,
.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb,
.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover,
.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Sticky header */
.sticky {
  position: sticky;
  top: 0;
  z-index: 10;
}

/* Skeleton animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>