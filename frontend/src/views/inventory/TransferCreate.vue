<template>
  <div class="p-6 max-w-5xl mx-auto space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">New Internal Transfer</h1>
      <button @click="submitTransfer" :disabled="loading" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold">
        {{ loading ? 'Processing...' : 'Complete Transfer' }}
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white p-6 rounded-2xl shadow-sm border">
        <label class="block text-xs font-bold text-gray-400 uppercase">From Warehouse (Source)</label>
        <select v-model="form.from_warehouse_id" class="w-full border rounded-lg p-3 mt-1">
          <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
        </select>
      </div>
      <div class="bg-white p-6 rounded-2xl shadow-sm border">
        <label class="block text-xs font-bold text-gray-400 uppercase">To Warehouse (Destination)</label>
        <select v-model="form.to_warehouse_id" class="w-full border rounded-lg p-3 mt-1">
          <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
        </select>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-gray-50 border-b text-gray-500 text-sm">
          <tr>
            <th class="p-4">Product Name</th>
            <th class="p-4 w-48">Transfer Quantity</th>
            <th class="p-4 w-20"></th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="(item, index) in form.items" :key="index">
            <td class="p-4 font-medium">{{ item.name }}</td>
            <td class="p-4">
              <input type="number" v-model="item.quantity" class="w-full border rounded p-2" min="0.1">
            </td>
            <td class="p-4 text-right">
              <button @click="removeItem(index)" class="text-red-400">✕</button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="p-4 bg-gray-50 border-t">
        <button @click="showProductSearch = true" class="text-blue-600 font-bold text-sm">+ Add Item to Transfer</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '@/api/axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const warehouses = ref([]);
const loading = ref(false);

const form = reactive({
  from_warehouse_id: '',
  to_warehouse_id: '',
  transfer_date: new Date().toISOString().slice(0, 10),
  items: []
});

const submitTransfer = async () => {
  if (!form.items.length) return alert('Add at least one item');
  loading.value = true;
  try {
    await api.post('/stock-transfers', form);
    alert('Transfer completed successfully');
    router.push('/inventory');
  } catch (e) {
    alert(e.response?.data?.message || 'Transfer failed');
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  const res = await api.get('/warehouses');
  warehouses.value = res.data;
});
</script>