<!-- views/inventory/TransferCreate.vue -->
<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="p-6 max-w-7xl mx-auto space-y-6">
      <!-- Header with Glassmorphism Effect -->
      <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/20 p-6">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              New Stock Transfer
            </h1>
            <p class="text-gray-500 mt-2">Transfer stock between warehouses efficiently</p>
          </div>
          <div class="flex gap-3">
            <button 
              @click="submitTransfer" 
              :disabled="loading || !isValid"
              class="group relative px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-semibold transition-all duration-300 hover:shadow-xl hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
            >
              <span v-if="loading" class="flex items-center gap-2">
                <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Processing...
              </span>
              <span v-else class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Complete Transfer
              </span>
            </button>
          </div>
        </div>
      </div>

      <!-- Warehouse Selection Cards -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Source Warehouse Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                </svg>
              </div>
              <div>
                <h3 class="text-white font-semibold">Source Warehouse</h3>
                <p class="text-blue-100 text-sm">Select warehouse to transfer from</p>
              </div>
            </div>
          </div>
          <div class="p-6">
            <select 
              v-model="form.from_warehouse_id" 
              @change="onWarehouseChange"
              class="w-full border-2 border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
              <option value="">Select source warehouse</option>
              <option v-for="w in warehouseList" :key="w.id" :value="w.id">
                {{ w.name }} {{ w.code ? '(' + w.code + ')' : '' }} - {{ w.address || 'No address' }}
              </option>
            </select>
            <div v-if="warehouseList.length === 0 && !loadingWarehouses" class="mt-3 p-3 bg-yellow-50 rounded-lg text-yellow-800 text-sm">
              ⚠️ No warehouses found. Please create a warehouse first.
            </div>
            <div v-if="form.from_warehouse_id" class="mt-3 flex items-center gap-2 text-green-600 text-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Warehouse selected - Ready to add products
            </div>
          </div>
        </div>

        <!-- Destination Warehouse Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
          <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h3 class="text-white font-semibold">Destination Warehouse</h3>
                <p class="text-purple-100 text-sm">Select warehouse to transfer to</p>
              </div>
            </div>
          </div>
          <div class="p-6">
            <select 
              v-model="form.to_warehouse_id" 
              class="w-full border-2 border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
            >
              <option value="">Select destination warehouse</option>
              <option 
                v-for="w in warehouseList" 
                :key="w.id" 
                :value="w.id"
                :disabled="w.id === form.from_warehouse_id"
              >
                {{ w.name }} {{ w.code ? '(' + w.code + ')' : '' }}
                <span v-if="w.id === form.from_warehouse_id" class="text-gray-400"> - Same as source</span>
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Transfer Items Section -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
          <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-white/10 rounded-xl">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <div>
                <h3 class="text-white font-semibold">Transfer Items</h3>
                <p class="text-gray-300 text-sm">{{ form.items.length }} item(s) to transfer</p>
              </div>
            </div>
            <button 
              @click="openProductModal" 
              :disabled="!form.from_warehouse_id"
              class="px-5 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl font-medium transition-all duration-300 hover:shadow-lg hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add Products
            </button>
          </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Product Details</th>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">SKU/Barcode</th>
                <th class="p-4 text-center text-sm font-semibold text-gray-600">Available Stock</th>
                <th class="p-4 text-center text-sm font-semibold text-gray-600">Transfer Quantity</th>
                <th class="p-4 text-center text-sm font-semibold text-gray-600">Unit</th>
                <th class="p-4 text-center w-16"></th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-if="form.items.length === 0">
                <td colspan="6" class="p-12 text-center">
                  <div class="flex flex-col items-center gap-3">
                    <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <div class="text-gray-500">No items added yet</div>
                    <button 
                      @click="openProductModal" 
                      :disabled="!form.from_warehouse_id"
                      class="text-blue-600 hover:text-blue-700 font-medium"
                    >
                      Click here to add products
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50 transition-colors">
                <td class="p-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-purple-100 rounded-lg flex items-center justify-center">
                      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7m14-4H4m16 4H4" />
                      </svg>
                    </div>
                    <div>
                      <div class="font-semibold text-gray-800">{{ item.name }}</div>
                      <div class="text-xs text-gray-500">{{ item.category }}</div>
                    </div>
                  </div>
                </td>
                <td class="p-4">
                  <div class="text-sm text-gray-600">{{ item.sku || '-' }}</div>
                  <div class="text-xs text-gray-400">{{ item.barcode || '-' }}</div>
                </td>
                <td class="p-4 text-center">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    {{ formatNumber(item.current_stock) }} {{ item.unit }}
                  </span>
                </td>
                <td class="p-4">
                  <div class="flex justify-center">
                    <input 
                      type="number" 
                      v-model="item.quantity" 
                      @input="validateQuantity(item, index)"
                      class="w-32 text-center border-2 border-gray-200 rounded-xl p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      :class="{ 'border-red-500 ring-2 ring-red-200': item.quantity > item.current_stock }"
                      step="0.01"
                      min="0.01"
                    >
                  </div>
                  <div v-if="item.quantity > item.current_stock" class="text-center text-xs text-red-500 mt-1">
                    Exceeds available stock
                  </div>
                </td>
                <td class="p-4 text-center text-gray-600">{{ item.unit }}</td>
                <td class="p-4 text-center">
                  <button 
                    @click="removeItem(index)" 
                    class="group p-2 hover:bg-red-50 rounded-lg transition-all duration-200"
                    title="Remove item"
                  >
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Notes Section -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-white/10 rounded-xl">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </div>
            <div>
              <h3 class="text-white font-semibold">Transfer Notes</h3>
              <p class="text-gray-300 text-sm">Add any additional information (optional)</p>
            </div>
          </div>
        </div>
        <div class="p-6">
          <textarea 
            v-model="form.notes" 
            rows="3" 
            class="w-full border-2 border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            placeholder="e.g., Urgent transfer, Quality check required, Seasonal restocking, etc..."
          ></textarea>
        </div>
      </div>

      <!-- Modern Product Selection Modal -->
      <div v-if="showProductModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 animate-fadeIn" @click.self="closeProductModal">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[85vh] flex flex-col animate-slideUp">
          <!-- Modal Header -->
          <div class="p-6 border-b bg-gradient-to-r from-blue-600 to-purple-600 rounded-t-2xl">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-2xl font-bold text-white">Select Products</h3>
                <p class="text-blue-100 mt-1">Choose products to transfer from {{ selectedWarehouseName }}</p>
              </div>
              <button @click="closeProductModal" class="text-white hover:bg-white/20 rounded-lg p-2 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Search Bar -->
          <div class="p-4 border-b bg-gray-50">
            <div class="relative">
              <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input 
                v-model="productSearch" 
                type="text" 
                placeholder="Search by product name, SKU, or barcode..."
                class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              >
            </div>
          </div>

          <!-- Products Grid -->
          <div class="flex-1 overflow-y-auto p-6">
            <div v-if="loadingProducts" class="flex flex-col items-center justify-center py-12">
              <svg class="animate-spin h-12 w-12 text-blue-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              <p class="text-gray-500 mt-3">Loading products...</p>
            </div>
            <div v-else-if="filteredProducts.length === 0" class="text-center py-12">
              <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-gray-500">No products found with stock in this warehouse</p>
              <p class="text-gray-400 text-sm mt-1">Try a different search term or add products first</p>
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div 
                v-for="product in filteredProducts" 
                :key="product.id"
                @click="addProductToTransfer(product)"
                class="group relative border-2 rounded-xl p-4 cursor-pointer transition-all duration-300 hover:shadow-xl"
                :class="isProductAdded(product.id) 
                  ? 'border-blue-500 bg-blue-50 shadow-md' 
                  : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'"
              >
                <div v-if="isProductAdded(product.id)" class="absolute top-2 right-2">
                  <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                </div>
                <div class="flex items-start gap-3">
                  <div class="w-12 h-12 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7m14-4H4m16 4H4" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">
                      {{ product.name }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">SKU: {{ product.sku || '-' }}</div>
                    <div class="text-xs text-gray-400">{{ product.category }}</div>
                    <div class="mt-2">
                      <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-green-100 text-green-700">
                        Stock: {{ formatNumber(product.current_stock) }} {{ product.unit }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="p-4 border-t bg-gray-50 rounded-b-2xl flex justify-between items-center">
            <div class="text-sm text-gray-600">
              <span class="font-semibold text-blue-600">{{ form.items.length }}</span> product(s) selected
            </div>
            <div class="flex gap-3">
              <button @click="closeProductModal" class="px-6 py-2 border-2 border-gray-300 rounded-xl hover:bg-gray-100 transition-all">
                Cancel
              </button>
              <button @click="closeProductModal" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:shadow-lg transition-all">
                Done
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/api/axios';

const router = useRouter();

// State
const warehouses = ref([]);
const products = ref([]);
const loading = ref(false);
const loadingWarehouses = ref(false);
const loadingProducts = ref(false);
const showProductModal = ref(false);
const productSearch = ref('');
const toastMessage = ref('');
const toastType = ref('success');

// Computed property to extract warehouse array
const warehouseList = computed(() => {
  if (Array.isArray(warehouses.value)) {
    return warehouses.value;
  }
  if (warehouses.value && warehouses.value.data && Array.isArray(warehouses.value.data)) {
    return warehouses.value.data;
  }
  return [];
});

// Form data
const form = reactive({
  from_warehouse_id: '',
  to_warehouse_id: '',
  transfer_date: new Date().toISOString().slice(0, 10),
  items: [],
  notes: ''
});

// Computed
const isValid = computed(() => {
  return form.from_warehouse_id && 
         form.to_warehouse_id && 
         form.items.length > 0 &&
         form.items.every(item => item.quantity > 0 && item.quantity <= item.current_stock);
});

const selectedWarehouseName = computed(() => {
  const warehouse = warehouseList.value.find(w => w.id === form.from_warehouse_id);
  return warehouse?.name || 'Selected Warehouse';
});

const filteredProducts = computed(() => {
  if (!productSearch.value) return products.value;
  const search = productSearch.value.toLowerCase();
  return products.value.filter(p => 
    p.name.toLowerCase().includes(search) || 
    (p.sku && p.sku.toLowerCase().includes(search)) ||
    (p.barcode && p.barcode.toLowerCase().includes(search))
  );
});

// Methods
const showToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  setTimeout(() => {
    toastMessage.value = '';
  }, 3000);
};

const fetchWarehouses = async () => {
  loadingWarehouses.value = true;
  try {
    const response = await api.get('/warehouses');
    
    if (response.data && response.data.success) {
      warehouses.value = response.data.data;
    } else if (response.data && response.data.data) {
      warehouses.value = response.data.data;
    } else if (Array.isArray(response.data)) {
      warehouses.value = response.data;
    } else {
      warehouses.value = response.data;
    }
    
    if (warehouseList.value.length === 0) {
      showToast('No warehouses found. Please create a warehouse first.', 'error');
    }
  } catch (error) {
    console.error('Failed to load warehouses:', error);
    showToast('Failed to load warehouses', 'error');
    warehouses.value = [];
  } finally {
    loadingWarehouses.value = false;
  }
};

const fetchAvailableProducts = async (warehouseId) => {
  if (!warehouseId) return;
  
  loadingProducts.value = true;
  try {
    const response = await api.get(`/stock-transfers/available-products`, {
      params: { warehouse_id: warehouseId }
    });
    
    if (response.data && response.data.success) {
      products.value = response.data.data;
    } else if (response.data && response.data.data) {
      products.value = response.data.data;
    } else {
      products.value = response.data;
    }
    
    console.log('Products loaded:', products.value.length);
  } catch (error) {
    console.error('Failed to load products:', error);
    showToast('Failed to load products: ' + (error.response?.data?.message || error.message), 'error');
    products.value = [];
  } finally {
    loadingProducts.value = false;
  }
};

const onWarehouseChange = () => {
  form.items = [];
  form.to_warehouse_id = '';
  if (form.from_warehouse_id) {
    fetchAvailableProducts(form.from_warehouse_id);
  }
};

const openProductModal = () => {
  if (!form.from_warehouse_id) {
    showToast('Please select a source warehouse first', 'warning');
    return;
  }
  if (products.value.length === 0 && !loadingProducts.value) {
    fetchAvailableProducts(form.from_warehouse_id);
  }
  showProductModal.value = true;
};

const closeProductModal = () => {
  showProductModal.value = false;
  productSearch.value = '';
};

const isProductAdded = (productId) => {
  return form.items.some(item => item.product_id === productId);
};

const addProductToTransfer = (product) => {
  const existingIndex = form.items.findIndex(item => item.product_id === product.id);
  
  if (existingIndex !== -1) {
    form.items.splice(existingIndex, 1);
  } else {
    form.items.push({
      product_id: product.id,
      name: product.name,
      sku: product.sku,
      barcode: product.barcode,
      current_stock: product.current_stock,
      quantity: 1,
      unit: product.unit,
      category: product.category
    });
  }
};

const validateQuantity = (item, index) => {
  if (item.quantity > item.current_stock) {
    showToast(`Quantity exceeds available stock (${item.current_stock} ${item.unit})`, 'error');
  }
  if (item.quantity <= 0) {
    item.quantity = 0.01;
  }
};

const removeItem = (index) => {
  form.items.splice(index, 1);
  showToast('Item removed from transfer', 'success');
};

const submitTransfer = async () => {
  if (!isValid.value) {
    showToast('Please fill all required fields and ensure quantities are valid', 'error');
    return;
  }
  
  loading.value = true;
  
  const submitData = {
    from_warehouse_id: parseInt(form.from_warehouse_id),
    to_warehouse_id: parseInt(form.to_warehouse_id),
    transfer_date: form.transfer_date,
    items: form.items.map(item => ({
      product_id: parseInt(item.product_id),
      quantity: parseFloat(item.quantity)
    })),
    notes: form.notes
  };
  
  try {
    const response = await api.post('/stock-transfers', submitData);
    
    if (response.data.success) {
      showToast('Stock transfer completed successfully!', 'success');
      setTimeout(() => {
        router.push('/inventory/transfer');
      }, 1500);
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Transfer failed. Please try again.';
    showToast(message, 'error');
    console.error('Transfer error:', error);
  } finally {
    loading.value = false;
  }
};

const formatNumber = (num) => {
  if (!num) return '0';
  return num.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
};

onMounted(() => {
  fetchWarehouses();
});
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    transform: translateY(50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}

.animate-slideUp {
  animation: slideUp 0.4s ease-out;
}
</style>