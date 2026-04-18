<template>
  <div class="p-6 space-y-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
      <div>
        <h1 class="text-2xl font-black text-gray-800">Warehouses</h1>
        <p class="text-sm text-gray-500">Manage workshop storage locations</p>
      </div>
      <button @click="openModal()" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
        + New Warehouse
      </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <SmartTable :key="tableKey" endpoint="/warehouses" :columns="columns">
        <template #cell-actions="{ row }">
          <div class="flex gap-2">
            <button @click="openModal(row)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">✏️</button>
            <button @click="deleteWarehouse(row.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">🗑️</button>
          </div>
        </template>
      </SmartTable>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50">
          <h2 class="text-xl font-black text-gray-800">{{ isEditing ? 'Edit Warehouse' : 'New Warehouse' }}</h2>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
        </div>
        <form @submit.prevent="saveWarehouse" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Warehouse Name</label>
            <input v-model="form.name" type="text" required placeholder="Main Storage" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none" />
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Location / Address</label>
            <textarea v-model="form.address" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none" rows="3" placeholder="Industrial Area, Sector 5"></textarea>
          </div>
          <button type="submit" :disabled="loading" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-indigo-700 disabled:bg-gray-400 transition-all">
            {{ loading ? 'Saving...' : 'Save Warehouse' }}
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
  { key: 'id', label: 'ID' },
  { key: 'name', label: 'Warehouse Name' },
  { key: 'address', label: 'Location' },
  { key: 'actions', label: 'Actions' }
];

const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const tableKey = ref(0);
const form = reactive({ id: null, name: '', address: '' });

const openModal = (warehouse = null) => {
  if (warehouse) {
    isEditing.value = true;
    Object.assign(form, warehouse);
  } else {
    isEditing.value = false;
    Object.assign(form, { id: null, name: '', address: '' });
  }
  showModal.value = true;
};

const saveWarehouse = async () => {
  loading.value = true;
  try {
    if (isEditing.value) await api.put(`/warehouses/${form.id}`, form);
    else await api.post('/warehouses', form);
    showModal.value = false;
    tableKey.value++;
  } catch (e) {
    alert("Error saving warehouse data");
  } finally {
    loading.value = false;
  }
};

const deleteWarehouse = async (id) => {
  if (!confirm("Are you sure?")) return;
  try {
    await api.delete(`/warehouses/${id}`);
    tableKey.value++;
  } catch (e) {
    alert("Cannot delete warehouse that contains stock.");
  }
};
</script>