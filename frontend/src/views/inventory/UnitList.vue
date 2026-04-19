<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-purple-50/30">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8">
      
      <!-- Header Section -->
      <div class="mb-6 md:mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-purple-200">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                </svg>
              </div>
              <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Units of Measurement</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Manage your product measurement units</p>
              </div>
            </div>
            <button 
              @click="openModal()" 
              class="w-full sm:w-auto bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-4 sm:px-6 py-2.5 rounded-xl font-semibold transition-all duration-200 shadow-lg shadow-purple-200 flex items-center justify-center gap-2 transform hover:scale-105 active:scale-95"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add Unit
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Total Units</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ items.length }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Active Units</p>
              <p class="text-2xl font-bold text-green-600 mt-1">{{ items.length }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">With Products</p>
              <p class="text-2xl font-bold text-blue-600 mt-1">{{ unitsWithProducts }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Recent Added</p>
              <p class="text-2xl font-bold text-purple-600 mt-1">{{ recentCount }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-2xl shadow-sm p-12">
        <div class="flex flex-col items-center justify-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
          <p class="mt-4 text-gray-600">Loading units...</p>
        </div>
      </div>

      <!-- Units Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="item in items" 
          :key="item.id" 
          class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 transform hover:-translate-y-1"
        >
          <div class="relative h-28 bg-gradient-to-r from-purple-500 to-purple-600">
            <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
            <div class="absolute top-4 right-4 flex space-x-2">
              <button 
                @click="openModal(item)" 
                class="p-2 bg-white rounded-lg text-purple-600 hover:bg-purple-50 transition-colors duration-200 shadow-md"
                title="Edit"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button 
                @click="deleteItem(item.id)" 
                class="p-2 bg-white rounded-lg text-red-600 hover:bg-red-50 transition-colors duration-200 shadow-md"
                title="Delete"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
            <div class="absolute bottom-4 left-4">
              <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                </svg>
              </div>
            </div>
          </div>
          <div class="p-6">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-xl font-semibold text-gray-900">{{ item.name }}</h3>
              <span v-if="item.symbol" class="px-2 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-lg">
                {{ item.symbol }}
              </span>
            </div>
            <p v-if="item.description" class="text-sm text-gray-600 mb-3 line-clamp-2">{{ item.description }}</p>
            <p v-else class="text-sm text-gray-400 italic mb-3">No description</p>
            <div class="border-t border-gray-100 pt-4">
              <div class="flex items-center justify-between text-sm">
                <div class="flex items-center text-gray-500">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  {{ formatDate(item.created_at) }}
                </div>
                <div class="flex items-center text-gray-500">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                  </svg>
                  ID: {{ item.id }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && items.length === 0" class="bg-white rounded-2xl shadow-sm p-12 text-center">
        <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No units yet</h3>
        <p class="text-gray-600 mb-4">Get started by creating your first unit of measurement.</p>
        <button 
          @click="openModal()" 
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-purple-600 hover:bg-purple-700"
        >
          Add your first unit
        </button>
      </div>

      <!-- Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showModal = false">
        <div class="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl animate-slide-up">
          <!-- Modal Header -->
          <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center">
            <div>
              <h2 class="text-xl font-bold text-gray-800">{{ editMode ? 'Edit Unit' : 'Add New Unit' }}</h2>
              <p class="text-sm text-gray-500 mt-0.5">{{ editMode ? 'Update unit information' : 'Fill in the details to create a new unit' }}</p>
            </div>
            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <!-- Modal Body -->
          <form @submit.prevent="saveItem" class="p-6 space-y-5">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Unit Name <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="form.name" 
                type="text" 
                class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-purple-400 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
                placeholder="e.g., Kilogram, Liter, Piece"
                required 
              />
            </div>
            
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Symbol (Optional)</label>
              <input 
                v-model="form.symbol" 
                type="text" 
                class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-purple-400 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
                placeholder="e.g., kg, L, pcs"
              />
            </div>
            
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
              <textarea 
                v-model="form.description" 
                rows="3"
                class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-purple-400 focus:ring-4 focus:ring-purple-100 outline-none transition-all resize-none"
                placeholder="Enter unit description (optional)"
              ></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
              <button 
                type="button" 
                @click="showModal = false" 
                class="px-6 py-2.5 text-gray-600 hover:text-gray-800 font-medium transition-colors rounded-xl"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-6 py-2.5 rounded-xl font-semibold hover:from-purple-700 hover:to-purple-800 transition-all disabled:opacity-50 shadow-lg shadow-purple-200"
                :disabled="saving"
              >
                {{ saving ? 'Saving...' : (editMode ? 'Update Unit' : 'Create Unit') }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Toast Notification -->
      <div v-if="toast.show" class="fixed bottom-4 right-4 z-50 animate-slide-up">
        <div :class="[
          'rounded-lg shadow-lg p-4 min-w-[200px]',
          toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'
        ]">
          <div class="flex items-center">
            <svg v-if="toast.type === 'success'" class="w-5 h-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg v-else class="w-5 h-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <p class="text-white font-medium">{{ toast.message }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '@/api/axios';

const items = ref([]);
const showModal = ref(false);
const editMode = ref(false);
const loading = ref(true);
const saving = ref(false);
const form = ref({ id: null, name: '', symbol: '', description: '' });
const toast = ref({ show: false, message: '', type: 'success' });

// Computed stats
const unitsWithProducts = computed(() => {
  return items.value.filter(u => u.products_count > 0).length;
});
const recentCount = computed(() => {
  const oneWeekAgo = new Date();
  oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
  return items.value.filter(item => new Date(item.created_at) > oneWeekAgo).length;
});

const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type };
  setTimeout(() => {
    toast.value.show = false;
  }, 3000);
};

const loadItems = async () => {
  loading.value = true;
  try {
    const res = await api.get('/units');
    items.value = res.data.data || res.data;
  } catch (err) {
    console.error('Failed to load units:', err);
    showToast('Failed to load units', 'error');
  } finally {
    loading.value = false;
  }
};

const openModal = (item = null) => {
  editMode.value = !!item;
  form.value = item ? { ...item } : { id: null, name: '', symbol: '', description: '' };
  showModal.value = true;
};

const saveItem = async () => {
  saving.value = true;
  try {
    if (editMode.value) {
      await api.put(`/units/${form.value.id}`, form.value);
      showToast('Unit updated successfully', 'success');
    } else {
      await api.post('/units', form.value);
      showToast('Unit created successfully', 'success');
    }
    showModal.value = false;
    await loadItems();
  } catch (err) {
    console.error('Operation failed:', err);
    const message = err.response?.data?.message || 'Operation failed';
    showToast(message, 'error');
  } finally {
    saving.value = false;
  }
};

const deleteItem = async (id) => {
  if (!confirm('Are you sure you want to delete this unit? This action cannot be undone.')) return;
  
  try {
    await api.delete(`/units/${id}`);
    showToast('Unit deleted successfully', 'success');
    await loadItems();
  } catch (err) {
    console.error('Delete failed:', err);
    const message = err.response?.data?.message || 'Delete failed: Item might be in use';
    showToast(message, 'error');
  }
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

onMounted(loadItems);
</script>

<style scoped>
@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>