<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-pink-50/30">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8">
      
      <!-- Header Section -->
      <div class="mb-6 md:mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg shadow-pink-200">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
              </div>
              <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Product Variants</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Manage product variants and their options</p>
              </div>
            </div>
            <button 
              @click="openModal()" 
              class="w-full sm:w-auto bg-gradient-to-r from-pink-600 to-pink-700 hover:from-pink-700 hover:to-pink-800 text-white px-4 sm:px-6 py-2.5 rounded-xl font-semibold transition-all duration-200 shadow-lg shadow-pink-200 flex items-center justify-center gap-2 transform hover:scale-105 active:scale-95"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add Variant
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Total Variants</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ variants.length }}</p>
            </div>
            <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Total Items</p>
              <p class="text-2xl font-bold text-green-600 mt-1">{{ totalItems }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Avg Items/Variant</p>
              <p class="text-2xl font-bold text-purple-600 mt-1">{{ avgItemsPerVariant }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Recent Added</p>
              <p class="text-2xl font-bold text-blue-600 mt-1">{{ recentCount }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-2xl shadow-sm p-12">
        <div class="flex flex-col items-center justify-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-pink-600"></div>
          <p class="mt-4 text-gray-600">Loading variants...</p>
        </div>
      </div>

      <!-- Variants Grid -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div 
          v-for="variant in variants" 
          :key="variant.id" 
          class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 transform hover:-translate-y-1"
        >
          <div class="relative h-28 bg-gradient-to-r from-pink-500 to-pink-600">
            <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
            <div class="absolute top-4 right-4 flex space-x-2">
              <button 
                @click="openModal(variant)" 
                class="p-2 bg-white rounded-lg text-pink-600 hover:bg-pink-50 transition-colors duration-200 shadow-md"
                title="Edit"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button 
                @click="deleteVariant(variant.id)" 
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
                <span class="text-white font-bold text-lg">{{ getInitial(variant.name) }}</span>
              </div>
            </div>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ variant.name }}</h3>
            
            <!-- Variant Items Section -->
            <div class="mb-4">
              <div class="flex items-center justify-between mb-3">
                <label class="text-sm font-semibold text-gray-700">Variant Items</label>
                <button 
                  @click="openItemModal(variant)"
                  class="text-xs text-pink-600 hover:text-pink-700 font-medium flex items-center gap-1"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Add Item
                </button>
              </div>
              
              <div class="space-y-2">
                <div 
                  v-for="item in variant.items" 
                  :key="item.id"
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <span class="text-sm text-gray-700">{{ item.name }}</span>
                  <div class="flex gap-2">
                    <button 
                      @click="openItemModal(variant, item)"
                      class="text-blue-600 hover:text-blue-700 text-xs"
                      title="Edit Item"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button 
                      @click="deleteItem(variant.id, item.id)"
                      class="text-red-600 hover:text-red-700 text-xs"
                      title="Delete Item"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
                
                <div v-if="!variant.items || variant.items.length === 0" class="text-center py-4 text-gray-400 text-sm">
                  No items added yet
                </div>
              </div>
            </div>
            
            <div class="border-t border-gray-100 pt-4">
              <div class="flex items-center justify-between text-sm">
                <div class="flex items-center text-gray-500">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  {{ formatDate(variant.created_at) }}
                </div>
                <div class="flex items-center text-gray-500">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                  </svg>
                  ID: {{ variant.id }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && variants.length === 0" class="bg-white rounded-2xl shadow-sm p-12 text-center">
        <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No variants yet</h3>
        <p class="text-gray-600 mb-4">Get started by creating your first product variant.</p>
        <button 
          @click="openModal()" 
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-pink-600 hover:bg-pink-700"
        >
          Add your first variant
        </button>
      </div>

      <!-- Variant Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showModal = false">
        <div class="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl animate-slide-up">
          <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center">
            <div>
              <h2 class="text-xl font-bold text-gray-800">{{ editMode ? 'Edit Variant' : 'Add New Variant' }}</h2>
              <p class="text-sm text-gray-500 mt-0.5">{{ editMode ? 'Update variant information' : 'Create a new product variant' }}</p>
            </div>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="saveVariant" class="p-6 space-y-5">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Variant Name <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="variantForm.name" 
                type="text" 
                class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-pink-400 focus:ring-4 focus:ring-pink-100 outline-none transition-all"
                placeholder="e.g., Size, Color, Material"
                required 
              />
              <p class="text-xs text-gray-400 mt-1">Example: Size (Small, Medium, Large) or Color (Red, Blue, Green)</p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
              <button 
                type="button" 
                @click="closeModal" 
                class="px-6 py-2.5 text-gray-600 hover:text-gray-800 font-medium transition-colors rounded-xl"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                class="bg-gradient-to-r from-pink-600 to-pink-700 text-white px-6 py-2.5 rounded-xl font-semibold hover:from-pink-700 hover:to-pink-800 transition-all disabled:opacity-50 shadow-lg shadow-pink-200"
                :disabled="saving"
              >
                {{ saving ? 'Saving...' : (editMode ? 'Update Variant' : 'Create Variant') }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Variant Item Modal -->
      <div v-if="showItemModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showItemModal = false">
        <div class="bg-white rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-2xl animate-slide-up">
          <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center">
            <div>
              <h2 class="text-xl font-bold text-gray-800">{{ isEditingItem ? 'Edit Variant Item' : 'Add Variant Item' }}</h2>
              <p class="text-sm text-gray-500 mt-0.5">For variant: {{ currentVariant?.name }}</p>
            </div>
            <button @click="closeItemModal" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="saveItem" class="p-6 space-y-5">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Item Name <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="itemForm.name" 
                type="text" 
                class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-pink-400 focus:ring-4 focus:ring-pink-100 outline-none transition-all"
                :placeholder="`Enter ${currentVariant?.name} item`"
                required 
              />
              <p class="text-xs text-gray-400 mt-1">Example: {{ getExampleText() }}</p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
              <button 
                type="button" 
                @click="closeItemModal" 
                class="px-6 py-2.5 text-gray-600 hover:text-gray-800 font-medium transition-colors rounded-xl"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                class="bg-gradient-to-r from-pink-600 to-pink-700 text-white px-6 py-2.5 rounded-xl font-semibold hover:from-pink-700 hover:to-pink-800 transition-all disabled:opacity-50 shadow-lg shadow-pink-200"
                :disabled="savingItem"
              >
                {{ savingItem ? 'Saving...' : (isEditingItem ? 'Update Item' : 'Add Item') }}
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

const variants = ref([]);
const showModal = ref(false);
const showItemModal = ref(false);
const editMode = ref(false);
const isEditingItem = ref(false);
const loading = ref(true);
const saving = ref(false);
const savingItem = ref(false);
const currentVariant = ref(null);
const currentItem = ref(null);

const variantForm = ref({ id: null, name: '' });
const itemForm = ref({ id: null, name: '' });
const toast = ref({ show: false, message: '', type: 'success' });

// Computed stats
const totalItems = computed(() => {
  return variants.value.reduce((sum, variant) => sum + (variant.items?.length || 0), 0);
});

const avgItemsPerVariant = computed(() => {
  if (variants.value.length === 0) return 0;
  return (totalItems.value / variants.value.length).toFixed(1);
});

const recentCount = computed(() => {
  const oneWeekAgo = new Date();
  oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
  return variants.value.filter(variant => new Date(variant.created_at) > oneWeekAgo).length;
});

const getInitial = (name) => {
  return name ? name.charAt(0).toUpperCase() : '?';
};

const getExampleText = () => {
  if (!currentVariant.value) return 'Enter item name';
  const examples = {
    'Size': 'Small, Medium, Large, XL',
    'Color': 'Red, Blue, Green, Black',
    'Material': 'Cotton, Polyester, Wool, Leather'
  };
  return examples[currentVariant.value.name] || 'Enter a specific option for this variant';
};

const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type };
  setTimeout(() => {
    toast.value.show = false;
  }, 3000);
};

const loadVariants = async () => {
  loading.value = true;
  try {
    const res = await api.get('/variants');
    variants.value = res.data.data || res.data;
  } catch (err) {
    console.error('Failed to load variants:', err);
    showToast('Failed to load variants', 'error');
  } finally {
    loading.value = false;
  }
};

const openModal = (variant = null) => {
  editMode.value = !!variant;
  variantForm.value = variant ? { id: variant.id, name: variant.name } : { id: null, name: '' };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  variantForm.value = { id: null, name: '' };
};

const saveVariant = async () => {
  saving.value = true;
  try {
    if (editMode.value) {
      await api.put(`/variants/${variantForm.value.id}`, variantForm.value);
      showToast('Variant updated successfully', 'success');
    } else {
      await api.post('/variants', variantForm.value);
      showToast('Variant created successfully', 'success');
    }
    closeModal();
    await loadVariants();
  } catch (err) {
    console.error('Operation failed:', err);
    const message = err.response?.data?.message || 'Operation failed';
    showToast(message, 'error');
  } finally {
    saving.value = false;
  }
};

const deleteVariant = async (id) => {
  if (!confirm('Are you sure you want to delete this variant? All associated items will also be deleted. This action cannot be undone.')) return;
  
  try {
    await api.delete(`/variants/${id}`);
    showToast('Variant deleted successfully', 'success');
    await loadVariants();
  } catch (err) {
    console.error('Delete failed:', err);
    const message = err.response?.data?.message || 'Delete failed: Item might be in use';
    showToast(message, 'error');
  }
};

const openItemModal = (variant, item = null) => {
  currentVariant.value = variant;
  isEditingItem.value = !!item;
  currentItem.value = item;
  itemForm.value = item ? { id: item.id, name: item.name } : { id: null, name: '' };
  showItemModal.value = true;
};

const closeItemModal = () => {
  showItemModal.value = false;
  currentVariant.value = null;
  currentItem.value = null;
  itemForm.value = { id: null, name: '' };
};

const saveItem = async () => {
  savingItem.value = true;
  try {
    if (isEditingItem.value) {
      await api.put(`/variants/${currentVariant.value.id}/items/${itemForm.value.id}`, itemForm.value);
      showToast('Variant item updated successfully', 'success');
    } else {
      await api.post(`/variants/${currentVariant.value.id}/items`, itemForm.value);
      showToast('Variant item created successfully', 'success');
    }
    closeItemModal();
    await loadVariants();
  } catch (err) {
    console.error('Operation failed:', err);
    const message = err.response?.data?.message || 'Operation failed';
    showToast(message, 'error');
  } finally {
    savingItem.value = false;
  }
};

const deleteItem = async (variantId, itemId) => {
  if (!confirm('Are you sure you want to delete this variant item? This action cannot be undone.')) return;
  
  try {
    await api.delete(`/variants/${variantId}/items/${itemId}`);
    showToast('Variant item deleted successfully', 'success');
    await loadVariants();
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

onMounted(loadVariants);
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