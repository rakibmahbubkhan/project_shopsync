<!-- views/inventory/TransferList.vue -->
<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Stock Transfers</h1>
        <p class="text-gray-500 mt-1">Manage and track inventory movements between warehouses</p>
      </div>
      <router-link 
        to="/inventory/transfer/create" 
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition shadow-sm"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Transfer
      </router-link>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" class="w-full border border-gray-300 rounded-lg p-2">
            <option value="">All Status</option>
            <option value="completed">Completed</option>
            <option value="pending">Pending</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
          <input type="date" v-model="filters.from_date" class="w-full border border-gray-300 rounded-lg p-2">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
          <input type="date" v-model="filters.to_date" class="w-full border border-gray-300 rounded-lg p-2">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
          <select v-model="filters.warehouse_id" class="w-full border border-gray-300 rounded-lg p-2">
            <option value="">All Warehouses</option>
            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
              {{ warehouse.name }}
            </option>
          </select>
        </div>
      </div>
      <div class="flex justify-end mt-4 gap-2">
        <button @click="resetFilters" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
          Reset
        </button>
        <button @click="fetchTransfers" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
          Apply Filters
        </button>
      </div>
    </div>

    <!-- Transfers Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="p-4 text-left text-sm font-semibold text-gray-600">Reference</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-600">Date</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-600">From</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-600">To</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-600">Items</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-600">Total Value</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-600">Status</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-600">Created By</th>
              <th class="p-4 text-center text-sm font-semibold text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-if="loading">
              <td colspan="9" class="p-8 text-center">
                <div class="flex justify-center">
                  <svg class="animate-spin h-8 w-8 text-blue-600" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                  </svg>
                </div>
              </td>
            </tr>
            <tr v-else-if="transfers.length === 0">
              <td colspan="9" class="p-8 text-center text-gray-500">
                No stock transfers found
              </td>
            </tr>
            <tr v-for="transfer in transfers" :key="transfer.id" class="hover:bg-gray-50 transition">
              <td class="p-4">
                <button @click="viewDetails(transfer)" class="font-medium text-blue-600 hover:text-blue-800">
                  {{ transfer.reference_no }}
                </button>
              </td>
              <td class="p-4 text-gray-600">{{ formatDate(transfer.transfer_date) }}</td>
              <td class="p-4">
                <div class="font-medium">{{ transfer.from_warehouse?.name }}</div>
                <div class="text-xs text-gray-500">{{ transfer.from_warehouse?.location }}</div>
              </td>
              <td class="p-4">
                <div class="font-medium">{{ transfer.to_warehouse?.name }}</div>
                <div class="text-xs text-gray-500">{{ transfer.to_warehouse?.location }}</div>
              </td>
              <td class="p-4 text-gray-600">{{ transfer.total_items }} items</td>
              <td class="p-4 font-semibold text-gray-800">{{ formatCurrency(transfer.total_cost) }}</td>
              <td class="p-4">
                <span :class="statusBadgeClass(transfer.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ getStatusText(transfer.status) }}
                </span>
              </td>
              <td class="p-4 text-gray-600">{{ transfer.user?.name || 'System' }}</td>
              <td class="p-4">
                <div class="flex justify-center gap-2">
                  <button 
                    @click="viewDetails(transfer)" 
                    class="text-gray-400 hover:text-blue-600 transition p-1"
                    title="View Details"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button 
                    v-if="transfer.status === 'pending'"
                    @click="cancelTransfer(transfer)" 
                    class="text-gray-400 hover:text-red-600 transition p-1"
                    title="Cancel Transfer"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-4 border-t bg-gray-50">
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-600">
            Showing {{ transfers.length }} of {{ pagination.total }} transfers
          </div>
          <div class="flex gap-2">
            <button 
              @click="changePage(pagination.current_page - 1)" 
              :disabled="!pagination.prev_page_url"
              class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            <span class="px-3 py-1">
              Page {{ pagination.current_page }} of {{ pagination.last_page }}
            </span>
            <button 
              @click="changePage(pagination.current_page + 1)" 
              :disabled="!pagination.next_page_url"
              class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Transfer Details Modal -->
    <div v-if="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="closeDetailsModal">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[85vh] flex flex-col">
        <div class="p-6 border-b flex justify-between items-center">
          <div>
            <h3 class="text-xl font-bold text-gray-800">Transfer Details</h3>
            <p class="text-sm text-gray-500">Reference: {{ selectedTransfer?.reference_no }}</p>
          </div>
          <button @click="closeDetailsModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6" v-if="selectedTransfer">
          <!-- Transfer Info -->
          <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-xs text-gray-500 uppercase mb-1">From Warehouse</p>
              <p class="font-semibold">{{ selectedTransfer.from_warehouse?.name }}</p>
              <p class="text-sm text-gray-600">{{ selectedTransfer.from_warehouse?.location }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-xs text-gray-500 uppercase mb-1">To Warehouse</p>
              <p class="font-semibold">{{ selectedTransfer.to_warehouse?.name }}</p>
              <p class="text-sm text-gray-600">{{ selectedTransfer.to_warehouse?.location }}</p>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4 mb-6">
            <div>
              <p class="text-xs text-gray-500">Transfer Date</p>
              <p class="font-medium">{{ formatDate(selectedTransfer.transfer_date) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Status</p>
              <span :class="statusBadgeClass(selectedTransfer.status)" class="inline-flex px-2 py-1 rounded-full text-xs font-medium">
                {{ getStatusText(selectedTransfer.status) }}
              </span>
            </div>
            <div>
              <p class="text-xs text-gray-500">Created By</p>
              <p class="font-medium">{{ selectedTransfer.user?.name || 'System' }}</p>
            </div>
          </div>

          <!-- Items Table -->
          <div class="mb-6">
            <h4 class="font-semibold text-gray-800 mb-3">Transferred Items</h4>
            <div class="border rounded-lg overflow-hidden">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="p-3 text-left text-sm font-semibold">Product</th>
                    <th class="p-3 text-right text-sm font-semibold">Quantity</th>
                    <th class="p-3 text-right text-sm font-semibold">Unit Cost</th>
                    <th class="p-3 text-right text-sm font-semibold">Total</th>
                  </tr>
                </thead>
                <tbody class="divide-y">
                  <tr v-for="item in selectedTransfer.items" :key="item.id">
                    <td class="p-3">
                      <div class="font-medium">{{ item.product?.name }}</div>
                      <div class="text-xs text-gray-500">SKU: {{ item.product?.sku }}</div>
                    </td>
                    <td class="p-3 text-right">{{ formatNumber(item.quantity) }} {{ item.product?.unit?.name }}</td>
                    <td class="p-3 text-right">{{ formatCurrency(item.unit_cost) }}</td>
                    <td class="p-3 text-right font-semibold">{{ formatCurrency(item.total_cost) }}</td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50 border-t">
                  <tr>
                    <td colspan="3" class="p-3 text-right font-semibold">Total Value:</td>
                    <td class="p-3 text-right font-bold text-blue-600">{{ formatCurrency(selectedTransfer.total_cost) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="selectedTransfer.notes" class="bg-gray-50 rounded-lg p-4">
            <p class="text-xs text-gray-500 uppercase mb-1">Notes</p>
            <p class="text-gray-700">{{ selectedTransfer.notes }}</p>
          </div>
        </div>

        <div class="p-4 border-t bg-gray-50 flex justify-end">
          <button 
            v-if="selectedTransfer?.status === 'pending'"
            @click="cancelTransfer(selectedTransfer)" 
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 mr-2"
          >
            Cancel Transfer
          </button>
          <button @click="closeDetailsModal" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api/axios';
import { useToast } from '@/composables/useToast';

const { showToast } = useToast();

// State
const transfers = ref([]);
const warehouses = ref([]);
const loading = ref(false);
const showDetailsModal = ref(false);
const selectedTransfer = ref(null);

// Filters
const filters = ref({
  status: '',
  from_date: '',
  to_date: '',
  warehouse_id: ''
});

// Pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 15,
  next_page_url: null,
  prev_page_url: null
});

// Methods
const fetchWarehouses = async () => {
  try {
    const response = await api.get('/warehouses');
    warehouses.value = response.data;
  } catch (error) {
    console.error('Failed to load warehouses:', error);
  }
};

const fetchTransfers = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      ...filters.value
    };
    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (!params[key]) delete params[key];
    });
    
    const response = await api.get('/stock-transfers', { params });
    transfers.value = response.data.data.data;
    pagination.value = {
      current_page: response.data.data.current_page,
      last_page: response.data.data.last_page,
      total: response.data.data.total,
      per_page: response.data.data.per_page,
      next_page_url: response.data.data.next_page_url,
      prev_page_url: response.data.data.prev_page_url
    };
  } catch (error) {
    console.error('Failed to load transfers:', error);
    showToast('Failed to load transfers', 'error');
  } finally {
    loading.value = false;
  }
};

const resetFilters = () => {
  filters.value = {
    status: '',
    from_date: '',
    to_date: '',
    warehouse_id: ''
  };
  fetchTransfers();
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchTransfers(page);
  }
};

const viewDetails = async (transfer) => {
  try {
    const response = await api.get(`/stock-transfers/${transfer.id}`);
    selectedTransfer.value = response.data.data;
    showDetailsModal.value = true;
  } catch (error) {
    console.error('Failed to load transfer details:', error);
    showToast('Failed to load transfer details', 'error');
  }
};

const closeDetailsModal = () => {
  showDetailsModal.value = false;
  selectedTransfer.value = null;
};

const cancelTransfer = async (transfer) => {
  if (!confirm('Are you sure you want to cancel this transfer? This will reverse all stock movements.')) {
    return;
  }
  
  try {
    const response = await api.post(`/stock-transfers/${transfer.id}/cancel`);
    if (response.data.success) {
      showToast('Transfer cancelled successfully', 'success');
      fetchTransfers();
      closeDetailsModal();
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to cancel transfer';
    showToast(message, 'error');
  }
};

const statusBadgeClass = (status) => {
  const base = "px-2.5 py-0.5 rounded-full text-xs font-medium ";
  switch (status) {
    case 'completed':
      return base + "bg-green-100 text-green-800";
    case 'pending':
      return base + "bg-yellow-100 text-yellow-800";
    case 'cancelled':
      return base + "bg-red-100 text-red-800";
    default:
      return base + "bg-gray-100 text-gray-800";
  }
};

const getStatusText = (status) => {
  const statusMap = {
    completed: 'Completed',
    pending: 'Pending',
    cancelled: 'Cancelled',
    draft: 'Draft'
  };
  return statusMap[status] || status;
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString();
};

const formatCurrency = (value) => {
  if (!value) return '₱0.00';
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value);
};

const formatNumber = (num) => {
  if (!num) return '0';
  return num.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
};

onMounted(() => {
  fetchWarehouses();
  fetchTransfers();
});
</script>