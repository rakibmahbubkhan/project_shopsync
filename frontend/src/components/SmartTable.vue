<template>
  <div class="smart-table-container">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <!-- Search Box -->
      <div class="relative w-full sm:w-80">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <input
          v-model="searchTerm"
          @input="debouncedSearch"
          type="text"
          placeholder="Search products, SKU, or barcode..."
          class="w-full pl-10 pr-4 py-2.5 border-2 border-gray-200 rounded-xl focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100 outline-none transition-all"
        />
        <div v-if="searchTerm" class="absolute inset-y-0 right-0 pr-3 flex items-center">
          <button @click="clearSearch" class="text-gray-400 hover:text-gray-600">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Right Actions -->
      <div class="flex gap-3 w-full sm:w-auto">
        <!-- Export Button -->
        <button 
          @click="exportData" 
          class="px-4 py-2 text-gray-600 hover:text-gray-800 border-2 border-gray-200 rounded-xl hover:border-indigo-300 transition-all flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          <span class="hidden sm:inline">Export</span>
        </button>

        <!-- Refresh Button -->
        <button 
          @click="refreshData" 
          class="px-4 py-2 text-gray-600 hover:text-gray-800 border-2 border-gray-200 rounded-xl hover:border-indigo-300 transition-all flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span class="hidden sm:inline">Refresh</span>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-12">
      <div class="relative">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
      <p class="mt-4 text-gray-500">Loading data...</p>
    </div>

    <!-- Content (shown when not loading) -->
    <template v-else>
      <!-- Table (Desktop) -->
      <div class="hidden lg:block overflow-x-auto rounded-xl border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
            <tr>
              <th
                v-for="col in columns"
                :key="col.key"
                @click="col.sortable !== false && sort(col.key)"
                :class="[
                  'px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider',
                  col.sortable !== false ? 'cursor-pointer hover:text-indigo-600 transition-colors' : '',
                  col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left'
                ]"
              >
                <div class="flex items-center gap-2" :class="col.align === 'right' ? 'justify-end' : col.align === 'center' ? 'justify-center' : ''">
                  {{ col.label }}
                  <span v-if="col.sortable !== false && sortBy === col.key" class="inline-flex">
                    <svg v-if="order === 'asc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-for="(item, index) in rows" :key="item.id" class="hover:bg-gray-50 transition-colors duration-150">
              <td
                v-for="col in columns"
                :key="col.key"
                :class="[
                  'px-6 py-4 text-sm text-gray-700',
                  col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left'
                ]"
              >
                <!-- Slot for custom rendering -->
                <slot :name="`cell-${col.key}`" :row="item" :value="getNestedValue(item, col.key)">
                  {{ formatValue(getNestedValue(item, col.key), col.type) }}
                </slot>
              </td>
            </tr>
            
            <!-- Empty State -->
            <tr v-if="rows.length === 0">
              <td :colspan="columns.length" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center justify-center">
                  <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <h3 class="text-lg font-medium text-gray-700 mb-1">No results found</h3>
                  <p class="text-sm text-gray-400">Try adjusting your search or filters</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Card View -->
      <div class="lg:hidden space-y-3">
        <div
          v-for="item in rows"
          :key="item.id"
          class="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow"
        >
          <div class="space-y-3">
            <div v-for="col in columns" :key="col.key" class="flex justify-between items-start">
              <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ col.label }}</span>
              <div class="text-sm text-gray-800 text-right max-w-[60%] break-words">
                <slot :name="`cell-${col.key}`" :row="item" :value="getNestedValue(item, col.key)">
                  {{ formatValue(getNestedValue(item, col.key), col.type) }}
                </slot>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile Empty State -->
        <div v-if="rows.length === 0" class="bg-white border border-gray-200 rounded-xl p-8 text-center">
          <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="text-lg font-medium text-gray-700 mb-1">No results found</h3>
          <p class="text-sm text-gray-400">Try adjusting your search</p>
        </div>
      </div>
    </template>

    <!-- Pagination -->
    <div v-if="meta.last_page > 1 && !loading" class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-6 pt-4 border-t border-gray-200">
      <!-- Per Page Selector -->
      <div class="flex items-center gap-2">
        <span class="text-sm text-gray-600">Show</span>
        <select
          v-model="perPage"
          @change="changePerPage"
          class="border-2 border-gray-200 rounded-lg px-2 py-1 text-sm focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100 outline-none"
        >
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
        <span class="text-sm text-gray-600">entries</span>
      </div>

      <!-- Page Info -->
      <div class="text-sm text-gray-600">
        Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} entries
      </div>

      <!-- Pagination Controls -->
      <div class="flex gap-2">
        <button
          @click="changePage(1)"
          :disabled="meta.current_page === 1"
          class="px-3 py-2 text-gray-600 hover:text-indigo-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors rounded-lg"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
          </svg>
        </button>
        
        <button
          @click="changePage(meta.current_page - 1)"
          :disabled="meta.current_page === 1"
          class="px-4 py-2 text-gray-600 hover:text-indigo-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors rounded-lg border border-gray-200 hover:border-indigo-300"
        >
          Previous
        </button>
        
        <!-- Page Numbers -->
        <div class="hidden md:flex gap-1">
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="changePage(page)"
            :class="[
              'px-3 py-2 rounded-lg transition-all',
              meta.current_page === page
                ? 'bg-indigo-600 text-white shadow-md'
                : 'text-gray-600 hover:bg-gray-100'
            ]"
          >
            {{ page }}
          </button>
        </div>
        
        <button
          @click="changePage(meta.current_page + 1)"
          :disabled="meta.current_page === meta.last_page"
          class="px-4 py-2 text-gray-600 hover:text-indigo-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors rounded-lg border border-gray-200 hover:border-indigo-300"
        >
          Next
        </button>
        
        <button
          @click="changePage(meta.last_page)"
          :disabled="meta.current_page === meta.last_page"
          class="px-3 py-2 text-gray-600 hover:text-indigo-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors rounded-lg"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import api from "@/api/axios";

const props = defineProps({
  endpoint: {
    type: String,
    required: true
  },
  columns: {
    type: Array,
    required: true
  },
  defaultSort: {
    type: String,
    default: null
  },
  defaultOrder: {
    type: String,
    default: "asc"
  }
});

const emit = defineEmits(['data-loaded']);

const rows = ref([]);
const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0
});
const searchTerm = ref("");
const loading = ref(false);
const sortBy = ref(props.defaultSort);
const order = ref(props.defaultOrder);
const perPage = ref(10);

let searchTimeout = null;

// Get nested object value using dot notation
const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => {
    return current && current[key] !== undefined ? current[key] : null;
  }, obj);
};

// Format value based on type
const formatValue = (value, type) => {
  if (value === null || value === undefined) return '—';
  
  switch (type) {
    case 'currency':
      return `৳ ${Number(value).toFixed(2)}`;
    case 'date':
      return new Date(value).toLocaleDateString();
    case 'datetime':
      return new Date(value).toLocaleString();
    case 'number':
      return Number(value).toLocaleString();
    default:
      return value;
  }
};

// Calculate visible page numbers
const visiblePages = computed(() => {
  const current = meta.value.current_page;
  const last = meta.value.last_page;
  const delta = 2;
  const range = [];
  
  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i);
  }
  
  if (current - delta > 2) {
    range.unshift('...');
  }
  if (current + delta < last - 1) {
    range.push('...');
  }
  
  range.unshift(1);
  if (last !== 1) range.push(last);
  
  return range;
});

// Debounced search
const debouncedSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchData(1);
  }, 500);
};

// Clear search
const clearSearch = () => {
  searchTerm.value = "";
  fetchData(1);
};

// Fetch data from API
const fetchData = async (page = 1) => {
  loading.value = true;
  
  try {
    const params = {
      page,
      per_page: perPage.value,
      search: searchTerm.value,
      sort_by: sortBy.value,
      order: order.value,
    };
    
    // Remove undefined/null params
    Object.keys(params).forEach(key => {
      if (params[key] === null || params[key] === undefined || params[key] === '') {
        delete params[key];
      }
    });
    
    const response = await api.get(props.endpoint, { params });
    
    // Handle different API response structures
    rows.value = response.data.data || response.data || [];
    meta.value = response.data.meta || response.data;
    
    // Emit data loaded event
    emit('data-loaded', rows.value);
    
  } catch (error) {
    console.error('Error fetching data:', error);
    rows.value = [];
    meta.value = {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
      from: 0,
      to: 0
    };
  } finally {
    loading.value = false;
  }
};

// Change page
const changePage = (page) => {
  if (page > 0 && page <= meta.value.last_page && page !== meta.value.current_page) {
    fetchData(page);
  }
};

// Change items per page
const changePerPage = () => {
  fetchData(1);
};

// Sort column
const sort = (column) => {
  if (sortBy.value === column) {
    order.value = order.value === "asc" ? "desc" : "asc";
  } else {
    sortBy.value = column;
    order.value = "asc";
  }
  fetchData(1);
};

// Refresh data
const refreshData = () => {
  fetchData(meta.value.current_page);
};

// Export data
const exportData = () => {
  // Implement export logic (CSV, Excel, etc.)
  console.log('Export functionality can be implemented here');
  alert('Export feature - can implement CSV/Excel export');
};

// Watch for endpoint changes
watch(() => props.endpoint, () => {
  fetchData(1);
});

// Initial fetch
fetchData(1);

// Expose methods for parent component
defineExpose({
  refresh: refreshData,
  fetch: fetchData
});
</script>

<style scoped>
/* Custom animations */
.smart-table-container {
  @apply w-full;
}

/* Smooth transitions */
* {
  @apply transition-all duration-200;
}

/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Loading spinner animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>