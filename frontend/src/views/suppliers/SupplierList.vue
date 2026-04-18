<template>
  <div class="p-6 space-y-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
      <div>
        <h1 class="text-2xl font-black text-gray-800">Suppliers</h1>
        <p class="text-sm text-gray-500">Manage parts vendors and manufacturers</p>
      </div>
      <button @click="openModal()" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg">
        + Add Supplier
      </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <SmartTable :key="tableKey" endpoint="/suppliers" :columns="columns">
        <template #cell-actions="{ row }">
          <div class="flex gap-2">
            <button @click="openModal(row)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">✏️</button>
            <button @click="deleteSupplier(row.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">🗑️</button>
          </div>
        </template>
      </SmartTable>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50">
          <h2 class="text-xl font-black text-gray-800">{{ isEditing ? 'Edit Supplier' : 'New Supplier' }}</h2>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
        </div>
        <form @submit.prevent="saveSupplier" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Company Name</label>
            <input v-model="form.name" type="text" required class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Phone</label>
              <input v-model="form.phone" type="text" required class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Email</label>
              <input v-model="form.email" type="email" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Address</label>
            <textarea v-model="form.address" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" rows="3"></textarea>
          </div>
          <button type="submit" :disabled="loading" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 disabled:bg-gray-400 transition-all">
            {{ loading ? 'Saving...' : 'Save Supplier' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import SmartTable from "@/components/SmartTable.vue";
import api from "@/api/axios";

const columns = [
  { key: 'name', label: 'Company' },
  { key: 'phone', label: 'Phone' },
  { key: 'email', label: 'Email' },
  { key: 'actions', label: 'Actions' }
];

const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const tableKey = ref(0);
const form = reactive({ id: null, name: '', phone: '', email: '', address: '' });

const openModal = (supplier = null) => {
  if (supplier) {
    isEditing.value = true;
    Object.assign(form, supplier);
  } else {
    isEditing.value = false;
    Object.assign(form, { id: null, name: '', phone: '', email: '', address: '' });
  }
  showModal.value = true;
};

const saveSupplier = async () => {
  loading.value = true;
  try {
    if (isEditing.value) await api.put(`/suppliers/${form.id}`, form);
    else await api.post('/suppliers', form);
    showModal.value = false;
    tableKey.value++;
  } catch (e) {
    alert(e.response?.data?.message || "Operation failed");
  } finally {
    loading.value = false;
  }
};

const deleteSupplier = async (id) => {
  if (!confirm("Are you sure?")) return;
  try {
    await api.delete(`/suppliers/${id}`);
    tableKey.value++;
  } catch (e) {
    alert("Cannot delete: Supplier has active purchase records.");
  }
};
</script>