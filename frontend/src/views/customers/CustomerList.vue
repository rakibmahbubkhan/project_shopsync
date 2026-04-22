<template>
  <div class="min-h-screen bg-gray-50 p-4 sm:p-8">
    <div class="container mx-auto">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Customer Management</h1>
        <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
          Add Customer
        </button>
      </div>

      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-left">
          <thead class="bg-gray-100 border-b">
            <tr>
              <th class="px-6 py-4">Customer/Contact</th>
              <th class="px-6 py-4">Email/Mobile</th>
              <th class="px-6 py-4">Location (Billing)</th>
              <th class="px-6 py-4 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="customer in customers" :key="customer.id" class="border-b hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="font-bold">{{ customer.name }}</div>
                <div class="text-sm text-gray-500">{{ customer.contact_person || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4">
                <div>{{ customer.email }}</div>
                <div class="text-sm text-gray-500">{{ customer.mobile_number }}</div>
              </td>
              <td class="px-6 py-4">
                {{ customer.billing_city }}, {{ customer.billing_country }}
              </td>
              <td class="px-6 py-4 text-center">
                <button @click="openModal(customer)" class="text-blue-600 mr-2">Edit</button>
                <button @click="deleteCustomer(customer.id)" class="text-red-600">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6">
        <h2 class="text-xl font-bold mb-4">{{ isEditing ? 'Edit Customer' : 'Add New Customer' }}</h2>
        
        <form @submit.prevent="saveCustomer" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-4">
            <h3 class="font-semibold border-b pb-2">Basic Information</h3>
            <input v-model="form.name" placeholder="Customer Name *" required class="w-full border p-2 rounded" />
            <input v-model="form.contact_person" placeholder="Contact Person" class="w-full border p-2 rounded" />
            <input v-model="form.email" type="email" placeholder="Email *" required class="w-full border p-2 rounded" />
            <input v-model="form.mobile_number" placeholder="Mobile Number *" required class="w-full border p-2 rounded" />
            <input v-model="form.website" placeholder="Website" class="w-full border p-2 rounded" />
          </div>

          <div class="space-y-4">
            <h3 class="font-semibold border-b pb-2">Billing Details</h3>
            <textarea v-model="form.billing_address" placeholder="Billing Address *" required class="w-full border p-2 rounded"></textarea>
            <div class="flex gap-2">
              <input v-model="form.billing_city" placeholder="City *" required class="w-1/2 border p-2 rounded" />
              <input v-model="form.billing_country" placeholder="Country *" required class="w-1/2 border p-2 rounded" />
            </div>
            
            <h3 class="font-semibold border-b pb-2 pt-2">Shipping Details</h3>
            <textarea v-model="form.shipping_address" placeholder="Shipping Address *" required class="w-full border p-2 rounded"></textarea>
            <div class="flex gap-2">
              <input v-model="form.shipping_city" placeholder="City *" required class="w-1/2 border p-2 rounded" />
              <input v-model="form.shipping_country" placeholder="Country *" required class="w-1/2 border p-2 rounded" />
            </div>
          </div>

          <div class="md:col-span-2 flex justify-end gap-2 mt-4">
            <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-200 rounded">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save Customer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from "@/api/axios"; //

const customers = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const form = reactive({
  id: null, name: '', contact_person: '', email: '', mobile_number: '',
  billing_address: '', billing_city: '', billing_country: '',
  shipping_address: '', shipping_city: '', shipping_country: '',
  website: '', description: ''
});

const loadCustomers = async () => {
  const res = await api.get('/customers');
  customers.value = res.data;
};

const openModal = (customer = null) => {
  isEditing.value = !!customer;
  if (customer) Object.assign(form, customer);
  else Object.keys(form).forEach(k => form[k] = '');
  showModal.value = true;
};

const saveCustomer = async () => {
  try {
    if (isEditing.value) await api.put(`/customers/${form.id}`, form);
    else await api.post('/customers', form);
    showModal.value = false;
    loadCustomers();
  } catch (e) { alert(e.response?.data?.message || "Error saving"); }
};

const deleteCustomer = async (id) => {
  if (confirm("Delete this customer?")) {
    await api.delete(`/customers/${id}`);
    loadCustomers();
  }
};

onMounted(loadCustomers);
</script>