<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Damaged Products Inventory</h2>
      <button 
        @click="openCreateModal"
        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Damaged Product
      </button>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-wrap gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
        <select 
          v-model="selectedWarehouse" 
          @change="fetchDamagedItems(1)"
          class="border border-gray-300 rounded-md px-3 py-2 w-64"
        >
          <option value="">All Warehouses</option>
          <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Search Product</label>
        <input 
          type="text" 
          v-model="searchTerm" 
          @input="fetchDamagedItems(1)"
          placeholder="Name or SKU"
          class="border border-gray-300 rounded-md px-3 py-2 w-64"
        />
      </div>
    </div>

    <!-- Damaged Items Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Report Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="item in damagedItems" :key="item.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ item.product?.name || 'N/A' }}</div>
              <div class="text-xs text-gray-500">SKU: {{ item.product?.sku || '-' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ item.warehouse?.name || 'N/A' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-bold">{{ item.quantity }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(item.report_date) }}</td>
            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ item.notes || '—' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button @click="editItem(item)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
              <button @click="confirmDelete(item.id)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
          <tr v-if="damagedItems.length === 0">
            <td colspan="6" class="px-6 py-8 text-center text-gray-500">No damaged products recorded.</td>
          </tr>
        </tbody>
       </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-between items-center" v-if="pagination.total > 0">
      <div class="text-sm text-gray-600">
        Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} entries
      </div>
      <div class="flex gap-2">
        <button 
          @click="fetchDamagedItems(pagination.current_page - 1)" 
          :disabled="!pagination.prev_page_url"
          class="px-3 py-1 border rounded disabled:opacity-50"
        >Previous</button>
        <button 
          @click="fetchDamagedItems(pagination.current_page + 1)" 
          :disabled="!pagination.next_page_url"
          class="px-3 py-1 border rounded disabled:opacity-50"
        >Next</button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="modalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold mb-4">{{ isEditing ? 'Edit Damaged Record' : 'Add Damaged Product' }}</h3>
        <form @submit.prevent="saveDamaged">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
            <select 
              v-model="form.product_id" 
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2"
            >
              <option value="">Select Product</option>
              <option v-for="p in allProducts" :key="p.id" :value="p.id">
                {{ p.name }} ({{ p.sku }})
              </option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse *</label>
            <select 
              v-model="form.warehouse_id" 
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2"
            >
              <option value="">Select Warehouse</option>
              <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
            <input 
              type="number" 
              v-model.number="form.quantity" 
              min="1"
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2"
            />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Report Date *</label>
            <input 
              type="date" 
              v-model="form.report_date" 
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2"
            />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea 
              v-model="form.notes" 
              rows="3"
              class="w-full border border-gray-300 rounded-md px-3 py-2"
            ></textarea>
          </div>
          <div class="flex justify-end gap-3">
            <button type="button" @click="closeModal" class="px-4 py-2 border rounded-md">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
              {{ isEditing ? 'Update' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import api from '@/api/axios';

const damagedItems = ref([]);
const warehouses = ref([]);
const allProducts = ref([]);
const selectedWarehouse = ref('');
const searchTerm = ref('');
const pagination = ref({
  current_page: 1,
  total: 0,
  from: 0,
  to: 0,
  prev_page_url: null,
  next_page_url: null,
});

// Modal state
const modalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const form = ref({
  product_id: '',
  warehouse_id: '',
  quantity: 1,
  report_date: new Date().toISOString().slice(0,10),
  notes: ''
});

// Fetch damaged items (with filter & pagination)
const fetchDamagedItems = async (page = 1) => {
  try {
    const params = {
      warehouse_id: selectedWarehouse.value || undefined,
      search: searchTerm.value || undefined,
      page: page,
      per_page: 10
    };
    const res = await api.get('/products/damaged', { params });
    // The backend returns paginated response with `data` as array
    damagedItems.value = res.data.data || [];
    pagination.value = {
      current_page: res.data.current_page,
      total: res.data.total,
      from: res.data.from,
      to: res.data.to,
      prev_page_url: res.data.prev_page_url,
      next_page_url: res.data.next_page_url,
    };
  } catch (error) {
    console.error('Failed to load damaged items', error);
    damagedItems.value = [];
  }
};

// Fetch warehouses
const fetchWarehouses = async () => {
  try {
    const res = await api.get('/warehouses');
    warehouses.value = res.data.data || [];
  } catch (error) {
    console.error('Failed to load warehouses', error);
  }
};

// Fetch all products (for dropdown)
const fetchAllProducts = async () => {
  try {
    const res = await api.get('/products?per_page=1000');
    allProducts.value = res.data.data || [];
  } catch (error) {
    console.error('Failed to load products', error);
  }
};

// Open create modal
const openCreateModal = () => {
  isEditing.value = false;
  editingId.value = null;
  form.value = {
    product_id: '',
    warehouse_id: selectedWarehouse.value || '',
    quantity: 1,
    report_date: new Date().toISOString().slice(0,10),
    notes: ''
  };
  modalOpen.value = true;
};

// Edit existing record
const editItem = (item) => {
  isEditing.value = true;
  editingId.value = item.id;
  form.value = {
    product_id: item.product_id,
    warehouse_id: item.warehouse_id,
    quantity: item.quantity,
    report_date: item.report_date,
    notes: item.notes || ''
  };
  modalOpen.value = true;
};

// Close modal
const closeModal = () => {
  modalOpen.value = false;
  isEditing.value = false;
  editingId.value = null;
};

// Save (create or update)
const saveDamaged = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/products/damaged/${editingId.value}`, form.value);
    } else {
      await api.post('/products/damaged', form.value);
    }
    closeModal();
    await fetchDamagedItems(pagination.value.current_page);
  } catch (error) {
    console.error('Save failed', error);
    alert(error.response?.data?.message || 'An error occurred');
  }
};

// Delete with confirmation
const confirmDelete = async (id) => {
  if (!confirm('Are you sure you want to delete this damaged record? This action may restore stock quantity.')) return;
  try {
    await api.delete(`/products/damaged/${id}`);
    await fetchDamagedItems(pagination.value.current_page);
  } catch (error) {
    console.error('Delete failed', error);
    alert(error.response?.data?.message || 'Delete failed');
  }
};

// Helper: format date
const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString();
};

// Watch filters (reset to first page)
watch([selectedWarehouse, searchTerm], () => {
  fetchDamagedItems(1);
});

onMounted(() => {
  fetchWarehouses();
  fetchAllProducts();
  fetchDamagedItems();
});
</script>