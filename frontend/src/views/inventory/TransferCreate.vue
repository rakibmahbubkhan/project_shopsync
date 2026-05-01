<!-- views/inventory/TransferCreate.vue -->
<template>
  <div class="p-6 max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border p-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">New Stock Transfer</h1>
          <p class="text-gray-500 mt-1">Transfer stock between warehouses</p>
        </div>
        <div class="flex gap-3">
          <button 
            @click="saveAsDraft" 
            :disabled="loading"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
          >
            Save as Draft
          </button>
          <button 
            @click="submitTransfer" 
            :disabled="loading || !isValid"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="flex items-center gap-2">
              <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              Processing...
            </span>
            <span v-else>Complete Transfer</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Warehouse Selection -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded-2xl shadow-sm border p-6">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          From Warehouse (Source)
          <span class="text-red-500">*</span>
        </label>
        <select 
          v-model="form.from_warehouse_id" 
          @change="onWarehouseChange"
          class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="">Select source warehouse</option>
          <option v-for="w in warehouses" :key="w.id" :value="w.id">
            {{ w.name }} ({{ w.location || 'No location' }})
          </option>
        </select>
        <p v-if="form.from_warehouse_id" class="text-xs text-gray-500 mt-2">
          Available stock will be shown for selected warehouse
        </p>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border p-6">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          To Warehouse (Destination)
          <span class="text-red-500">*</span>
        </label>
        <select 
          v-model="form.to_warehouse_id" 
          class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="">Select destination warehouse</option>
          <option 
            v-for="w in warehouses" 
            :key="w.id" 
            :value="w.id"
            :disabled="w.id === form.from_warehouse_id"
          >
            {{ w.name }} ({{ w.location || 'No location' }})
            <span v-if="w.id === form.from_warehouse_id" class="text-gray-400"> - Same as source</span>
          </option>
        </select>
      </div>
    </div>

    <!-- Transfer Items -->
    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
      <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">Transfer Items</h3>
        <p class="text-sm text-gray-500 mt-1">Add products to transfer between warehouses</p>
      </div>

      <!-- Items Table -->
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="p-4 text-left text-sm font-semibold text-gray-600">Product</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-600">SKU</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-600">Available Stock</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-600">Transfer Quantity</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-600">Unit</th>
              <th class="p-4 w-16"></th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-if="form.items.length === 0">
              <td colspan="6" class="p-8 text-center text-gray-500">
                No items added yet. Click "Add Product" to start transferring.
              </td>
            </tr>
            <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50">
              <td class="p-4">
                <div class="font-medium text-gray-800">{{ item.name }}</div>
                <div class="text-xs text-gray-500">{{ item.category?.name || 'Uncategorized' }}</div>
              </td>
              <td class="p-4 text-sm text-gray-600">{{ item.sku || '-' }}</td>
              <td class="p-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  {{ formatNumber(item.current_stock) }} {{ item.unit }}
                </span>
              </td>
              <td class="p-4 w-48">
                <input 
                  type="number" 
                  v-model="item.quantity" 
                  @input="validateQuantity(item, index)"
                  class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': item.quantity > item.current_stock }"
                  step="0.01"
                  min="0.01"
                >
                <p v-if="item.quantity > item.current_stock" class="text-xs text-red-500 mt-1">
                  Exceeds available stock
                </p>
              </td>
              <td class="p-4 text-sm text-gray-600">{{ item.unit || 'Unit' }}</td>
              <td class="p-4">
                <button 
                  @click="removeItem(index)" 
                  class="text-red-400 hover:text-red-600 transition p-1"
                  title="Remove item"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Add Product Button -->
      <div class="p-4 bg-gray-50 border-t">
        <button 
          @click="openProductModal" 
          :disabled="!form.from_warehouse_id"
          class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Product to Transfer
        </button>
        <p v-if="!form.from_warehouse_id" class="text-xs text-gray-500 mt-2">
          Please select a source warehouse first to see available products
        </p>
      </div>
    </div>

    <!-- Notes -->
    <div class="bg-white rounded-2xl shadow-sm border p-6">
      <label class="block text-sm font-semibold text-gray-700 mb-2">Transfer Notes (Optional)</label>
      <textarea 
        v-model="form.notes" 
        rows="3" 
        class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500"
        placeholder="Add any additional information about this transfer..."
      ></textarea>
    </div>

    <!-- Product Selection Modal -->
    <div v-if="showProductModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="closeProductModal">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[80vh] flex flex-col">
        <div class="p-6 border-b flex justify-between items-center">
          <div>
            <h3 class="text-xl font-bold text-gray-800">Add Products</h3>
            <p class="text-sm text-gray-500 mt-1">Select products to transfer from {{ selectedWarehouseName }}</p>
          </div>
          <button @click="closeProductModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Search -->
        <div class="p-4 border-b">
          <input 
            v-model="productSearch" 
            type="text" 
            placeholder="Search products by name or SKU..."
            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500"
          >
        </div>

        <!-- Products List -->
        <div class="flex-1 overflow-y-auto p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div 
              v-for="product in filteredProducts" 
              :key="product.id"
              @click="addProductToTransfer(product)"
              class="border rounded-lg p-4 hover:shadow-md cursor-pointer transition group"
              :class="{ 
                'bg-blue-50 border-blue-300': isProductAdded(product.id),
                'hover:border-blue-300': !isProductAdded(product.id)
              }"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="font-semibold text-gray-800 group-hover:text-blue-600">
                    {{ product.name }}
                  </div>
                  <div class="text-sm text-gray-500 mt-1">SKU: {{ product.sku || '-' }}</div>
                  <div class="text-xs text-gray-400 mt-1">{{ product.category || 'Uncategorized' }}</div>
                  <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      Available: {{ formatNumber(product.current_stock) }} {{ product.unit }}
                    </span>
                  </div>
                </div>
                <div v-if="isProductAdded(product.id)" class="text-blue-600">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </div>
            </div>
          </div>
          <div v-if="filteredProducts.length === 0" class="text-center py-12">
            <p class="text-gray-500">No products found with stock in this warehouse</p>
          </div>
        </div>

        <div class="p-4 border-t bg-gray-50 flex justify-end">
          <button @click="closeProductModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Done ({{ form.items.length }} items)
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/api/axios';
import { useToast } from '@/composables/useToast';

const router = useRouter();
const { showToast } = useToast();

// State
const warehouses = ref([]);
const products = ref([]);
const loading = ref(false);
const showProductModal = ref(false);
const productSearch = ref('');

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
  const warehouse = warehouses.value.find(w => w.id === form.from_warehouse_id);
  return warehouse?.name || 'Selected Warehouse';
});

const filteredProducts = computed(() => {
  if (!productSearch.value) return products.value;
  const search = productSearch.value.toLowerCase();
  return products.value.filter(p => 
    p.name.toLowerCase().includes(search) || 
    (p.sku && p.sku.toLowerCase().includes(search))
  );
});

// Methods
const fetchWarehouses = async () => {
  try {
    const response = await api.get('/warehouses');
    warehouses.value = response.data;
  } catch (error) {
    console.error('Failed to load warehouses:', error);
    showToast('Failed to load warehouses', 'error');
  }
};

const fetchAvailableProducts = async (warehouseId) => {
  if (!warehouseId) return;
  
  try {
    const response = await api.get(`/stock-transfers/available-products`, {
      params: { warehouse_id: warehouseId }
    });
    products.value = response.data.data;
  } catch (error) {
    console.error('Failed to load products:', error);
    showToast('Failed to load products', 'error');
  }
};

const onWarehouseChange = () => {
  form.items = []; // Clear items when warehouse changes
  fetchAvailableProducts(form.from_warehouse_id);
};

const openProductModal = () => {
  if (!form.from_warehouse_id) {
    showToast('Please select a source warehouse first', 'warning');
    return;
  }
  showProductModal.value = true;
};

const closeProductModal = () => {
  showProductModal.value = false;
  productSearch.value = '';
};

const isProductAdded = (productId) => {
  return form.items.some(item => item.id === productId);
};

const addProductToTransfer = (product) => {
  const existingIndex = form.items.findIndex(item => item.id === product.id);
  
  if (existingIndex !== -1) {
    // Remove if already added
    form.items.splice(existingIndex, 1);
  } else {
    // Add new product
    form.items.push({
      id: product.id,
      product_id: product.id,
      name: product.name,
      sku: product.sku,
      current_stock: product.current_stock,
      quantity: 1,
      unit: product.unit,
      category: product.category
    });
  }
};

const validateQuantity = (item, index) => {
  if (item.quantity > item.current_stock) {
    showToast(`Quantity exceeds available stock (${item.current_stock} ${item.unit})`, 'warning');
  }
  if (item.quantity <= 0) {
    item.quantity = 0.01;
  }
};

const removeItem = (index) => {
  form.items.splice(index, 1);
  showToast('Item removed from transfer', 'info');
};

const saveAsDraft = async () => {
  // Implement draft saving logic
  showToast('Draft feature coming soon', 'info');
};

const submitTransfer = async () => {
  if (!isValid.value) {
    showToast('Please fill all required fields and ensure quantities are valid', 'warning');
    return;
  }
  
  loading.value = true;
  
  const submitData = {
    from_warehouse_id: form.from_warehouse_id,
    to_warehouse_id: form.to_warehouse_id,
    transfer_date: form.transfer_date,
    items: form.items.map(item => ({
      product_id: item.product_id,
      quantity: item.quantity
    })),
    notes: form.notes
  };
  
  try {
    const response = await api.post('/stock-transfers', submitData);
    
    if (response.data.success) {
      showToast('Stock transfer completed successfully!', 'success');
      router.push('/inventory/transfer');
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