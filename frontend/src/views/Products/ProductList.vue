<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Inventory Management</h1>
        <p class="text-sm text-gray-500">Track and manage machinery parts and equipment</p>
      </div>
      <button 
        @click="openCreateModal" 
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-bold transition-all shadow-lg shadow-blue-200"
      >
        + Add New Part
      </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <SmartTable
        endpoint="/products"
        :columns="columns"
        ref="productTable"
        :key="tableKey"
      >
        <!-- Custom Stock Column with Low Stock Indicator -->
        <template #cell-stock_quantity="{ row }">
          <div class="flex items-center gap-2">
            <span :class="row.stock_quantity <= row.alert_quantity ? 'text-red-600 font-bold' : 'text-gray-700'">
              {{ row.stock_quantity }}
            </span>
            <span 
              v-if="row.stock_quantity <= row.alert_quantity" 
              class="bg-red-100 text-red-600 text-[10px] px-2 py-0.5 rounded-full uppercase font-bold"
            >
              Low
            </span>
          </div>
        </template>

        <!-- Custom Price Column with Currency -->
        <template #cell-selling_price="{ row }">
          <span>৳ {{ Number(row.selling_price).toFixed(2) }}</span>
        </template>

        <!-- Action Buttons -->
        <template #cell-actions="{ row }">
          <div class="flex gap-3">
            <button 
              @click="editProduct(row)" 
              class="text-blue-600 hover:text-blue-800 font-medium text-sm"
            >
              Edit
            </button>
            <button 
              @click="confirmDelete(row.id)" 
              class="text-red-600 hover:text-red-800 font-medium text-sm"
            >
              Delete
            </button>
          </div>
        </template>
      </SmartTable>
    </div>

    <!-- Create/Edit Product Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white p-8 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <h2 class="text-xl font-bold mb-6">{{ isEditing ? 'Edit Product' : 'Add New Product' }}</h2>
        
        <form @submit.prevent="saveProduct" class="space-y-4">
          <!-- Product Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
            <input 
              v-model="form.name" 
              placeholder="Enter product name" 
              class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
              required 
            />
          </div>

          <!-- Two Column Grid for Selections -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Category Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
              <select 
                v-model="form.category_id" 
                class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required
              >
                <option value="" disabled>Select Category</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <!-- Brand Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Brand (Optional)</label>
              <select 
                v-model="form.brand_id" 
                class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option value="">Select Brand</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                  {{ brand.name }}
                </option>
              </select>
            </div>

            <!-- Unit Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Unit *</label>
              <select 
                v-model="form.unit_id" 
                class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required
              >
                <option value="" disabled>Select Unit</option>
                <option v-for="unit in units" :key="unit.id" :value="unit.id">
                  {{ unit.name }}
                </option>
              </select>
            </div>

            <!-- Warehouse Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse *</label>
              <select 
                v-model="form.warehouse_id" 
                class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required
              >
                <option value="" disabled>Select Warehouse</option>
                <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                  {{ warehouse.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- SKU and Barcode -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">SKU (Leave empty for auto-generated)</label>
              <input 
                v-model="form.sku" 
                placeholder="Auto-generated if empty" 
                class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
              <input 
                v-model="form.barcode" 
                placeholder="Enter barcode" 
                class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
              />
            </div>
          </div>

          <!-- Pricing Section -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price *</label>
              <input 
                v-model="form.cost_price" 
                type="number" 
                step="0.01"
                min="0"
                placeholder="0.00" 
                class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                required 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price *</label>
              <input 
                v-model="form.selling_price" 
                type="number" 
                step="0.01"
                min="0"
                placeholder="0.00" 
                class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                required 
              />
            </div>
          </div>

          <!-- Stock Section -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Initial Stock</label>
              <input 
                v-model="form.initial_stock" 
                type="number" 
                min="0"
                placeholder="0" 
                class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
              />
              <p class="text-xs text-gray-500 mt-1">Initial quantity (for new products only)</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Alert Quantity *</label>
              <input 
                v-model="form.alert_quantity" 
                type="number" 
                min="0"
                placeholder="0" 
                class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                required 
              />
              <p class="text-xs text-gray-500 mt-1">Notify when stock falls below this</p>
            </div>
          </div>

          <!-- Image Upload -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
            <input 
              type="file" 
              @change="handleImageUpload" 
              accept="image/*"
              class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
            />
          </div>

          <!-- Status Toggle -->
          <div class="flex items-center gap-2">
            <input 
              type="checkbox" 
              v-model="form.status" 
              id="status" 
              class="rounded text-blue-600 focus:ring-blue-500"
            />
            <label for="status" class="text-sm font-medium text-gray-700">Active</label>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end gap-2 pt-4 border-t">
            <button 
              type="button" 
              @click="closeModal" 
              class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Saving...' : (isEditing ? 'Update Product' : 'Save Product') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import SmartTable from "@/components/SmartTable.vue";
import axios from 'axios';

// Table reference and key for refresh
const productTable = ref(null);
const tableKey = ref(0);

// Modal state
const showModal = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);
const editingId = ref(null);

// Form state
const form = reactive({
  name: '',
  category_id: '',
  brand_id: '',
  unit_id: '',
  warehouse_id: '',
  sku: '',
  barcode: '',
  cost_price: '',
  selling_price: '',
  initial_stock: 0,
  alert_quantity: '',
  image: null,
  status: true
});

// Data lists
const categories = ref([]);
const brands = ref([]);
const units = ref([]);
const warehouses = ref([]);

// Table columns definition
const columns = [
  { key: "sku", label: "SKU", sortable: true },
  { key: "name", label: "Part Name", sortable: true },
  { key: "category.name", label: "Category" },
  { key: "stock_quantity", label: "Stock" },
  { key: "selling_price", label: "Sale Price" },
  { key: "actions", label: "Actions" },
];

// Fetch required data on mount
onMounted(async () => {
  try {
    const [categoriesRes, brandsRes, unitsRes, warehousesRes] = await Promise.all([
      axios.get('/categories'),
      axios.get('/brands'),
      axios.get('/units'),
      axios.get('/warehouses')
    ]);
    
    categories.value = categoriesRes.data;
    brands.value = brandsRes.data;
    units.value = unitsRes.data;
    warehouses.value = warehousesRes.data;
  } catch (error) {
    console.error('Error fetching data:', error);
  }
});

// Reset form to default values
const resetForm = () => {
  form.name = '';
  form.category_id = '';
  form.brand_id = '';
  form.unit_id = '';
  form.warehouse_id = '';
  form.sku = '';
  form.barcode = '';
  form.cost_price = '';
  form.selling_price = '';
  form.initial_stock = 0;
  form.alert_quantity = '';
  form.image = null;
  form.status = true;
  editingId.value = null;
};

// Open modal for creating new product
const openCreateModal = () => {
  isEditing.value = false;
  resetForm();
  showModal.value = true;
};

// Open modal for editing existing product
const editProduct = (product) => {
  isEditing.value = true;
  editingId.value = product.id;
  
  // Populate form with product data
  form.name = product.name;
  form.category_id = product.category_id;
  form.brand_id = product.brand_id || '';
  form.unit_id = product.unit_id;
  form.warehouse_id = product.warehouse_id;
  form.sku = product.sku;
  form.barcode = product.barcode || '';
  form.cost_price = product.cost_price;
  form.selling_price = product.selling_price;
  form.alert_quantity = product.alert_quantity;
  form.status = product.status;
  // initial_stock is not set when editing
  
  showModal.value = true;
};

// Close modal
const closeModal = () => {
  showModal.value = false;
  resetForm();
};

// Handle image upload
const handleImageUpload = (event) => {
  form.image = event.target.files[0];
};

// Save product (create or update)
const saveProduct = async () => {
  isSubmitting.value = true;
  
  try {
    const formData = new FormData();
    
    // Append all form fields
    Object.keys(form).forEach(key => {
      if (form[key] !== null && form[key] !== '') {
        if (key === 'image' && form.image) {
          formData.append('image', form.image);
        } else {
          formData.append(key, form[key]);
        }
      }
    });

    let response;
    if (isEditing.value) {
      // Update existing product
      formData.append('_method', 'PUT');
      response = await axios.post(`/products/${editingId.value}`, formData);
    } else {
      // Create new product
      response = await axios.post('/products', formData);
    }
    
    // Close modal and refresh table
    closeModal();
    tableKey.value += 1;
    
    // Show success message
    alert(`Product ${isEditing.value ? 'updated' : 'created'} successfully!`);
    
  } catch (error) {
    console.error('Error saving product:', error);
    
    // Show error message
    if (error.response?.data?.errors) {
      // Handle validation errors
      const errors = Object.values(error.response.data.errors).flat();
      alert(errors.join('\n'));
    } else {
      alert('Failed to save product. Please try again.');
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Confirm delete
const confirmDelete = async (id) => {
  if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
    try {
      await axios.delete(`/products/${id}`);
      
      // Refresh table
      tableKey.value += 1;
      
      alert('Product deleted successfully!');
    } catch (error) {
      console.error('Error deleting product:', error);
      
      if (error.response?.status === 422) {
        alert(error.response.data.message || 'Cannot delete product with transaction history.');
      } else {
        alert('Failed to delete product. Please try again.');
      }
    }
  }
};
</script>