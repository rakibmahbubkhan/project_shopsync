<template>
  <div>
    <!-- Add New Product Button -->
    <div class="mb-4 flex justify-end">
      <button 
        @click="showCreateModal = true" 
        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors"
      >
        + Add New Product
      </button>
    </div>

    <!-- SmartTable Component -->
    <SmartTable
      endpoint="/products"
      :columns="columns"
      :key="tableKey"
    />

    <!-- Create Product Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white p-8 rounded-2xl w-full max-w-lg shadow-2xl">
        <h2 class="text-xl font-bold mb-4">Register New Machinery/Part</h2>
        <form @submit.prevent="saveProduct" class="space-y-4">
          <input 
            v-model="form.name" 
            placeholder="Item Name" 
            class="w-full border p-2 rounded focus:ring-2 focus:ring-primary focus:outline-none" 
            required 
          />
          
          <div class="grid grid-cols-2 gap-4">
            <select 
              v-model="form.category_id" 
              class="border p-2 rounded focus:ring-2 focus:ring-primary focus:outline-none"
              required
            >
              <option value="" disabled selected>Select Category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
            <input 
              v-model="form.selling_price" 
              type="number" 
              step="0.01"
              placeholder="Selling Price" 
              class="border p-2 rounded focus:ring-2 focus:ring-primary focus:outline-none" 
              required 
            />
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <button 
              type="button" 
              @click="closeModal" 
              class="px-4 py-2 text-gray-500 hover:text-gray-700 transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Saving...' : 'Save Item' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import SmartTable from "@/components/SmartTable.vue"
import axios from 'axios'

// Table columns definition
const columns = [
  { key: "id", label: "ID" },
  { key: "name", label: "Name" },
  { key: "sku", label: "SKU" },
  { key: "stock_quantity", label: "Stock" },
  { key: "selling_price", label: "Price" },
]

// Modal state
const showCreateModal = ref(false)
const isSubmitting = ref(false)
const categories = ref([])
const tableKey = ref(0) // Used to force table refresh

// Form state
const form = reactive({
  name: '',
  category_id: '',
  selling_price: ''
})

// Fetch categories on component mount
onMounted(async () => {
  try {
    const response = await axios.get('/categories')
    categories.value = response.data
  } catch (error) {
    console.error('Error fetching categories:', error)
  }
})

// Reset form function
const resetForm = () => {
  form.name = ''
  form.category_id = ''
  form.selling_price = ''
}

// Close modal function
const closeModal = () => {
  showCreateModal.value = false
  resetForm()
}

// Save product function
const saveProduct = async () => {
  isSubmitting.value = true
  
  try {
    await axios.post('/products', {
      name: form.name,
      category_id: form.category_id,
      selling_price: form.selling_price
    })
    
    // Close modal and reset form
    closeModal()
    
    // Refresh the table by incrementing the key
    tableKey.value += 1
    
    // Optional: Show success message
    // You can integrate a toast notification here
    
  } catch (error) {
    console.error('Error saving product:', error)
    // Handle error (show error message to user)
    alert('Failed to save product. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.bg-primary {
  background-color: #4f46e5; /* You can adjust this to match your theme */
}

.bg-primary-dark {
  background-color: #4338ca;
}

.hover\:bg-primary-dark:hover {
  background-color: #4338ca;
}
</style>