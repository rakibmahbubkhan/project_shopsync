<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Trial Balance</h1>
        <p class="text-sm text-gray-500">Financial summary as of {{ currentDate }}</p>
      </div>
      <button @click="printReport" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold transition-all">
        Print PDF
      </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-gray-50 border-b text-gray-400 text-xs uppercase tracking-widest font-bold">
          <tr>
            <th class="px-6 py-4">Account Code</th>
            <th class="px-6 py-4">Account Name</th>
            <th class="px-6 py-4 text-right">Debit (৳)</th>
            <th class="px-6 py-4 text-right">Credit (৳)</th>
          </tr>
        </thead>
        <tbody class="divide-y text-sm">
          <tr v-for="row in reportData" :key="row.code" class="hover:bg-blue-50/50 transition-colors">
            <td class="px-6 py-4 font-mono text-gray-500">{{ row.code }}</td>
            <td class="px-6 py-4 font-bold text-gray-700">{{ row.name }}</td>
            <td class="px-6 py-4 text-right text-primary font-medium">
              {{ row.debit > 0 ? row.debit.toLocaleString(undefined, {minimumFractionDigits: 2}) : '-' }}
            </td>
            <td class="px-6 py-4 text-right text-secondary font-medium">
              {{ row.credit > 0 ? row.credit.toLocaleString(undefined, {minimumFractionDigits: 2}) : '-' }}
            </td>
          </tr>
        </tbody>
        <tfoot class="bg-gray-900 text-white font-bold">
          <tr>
            <td colspan="2" class="px-6 py-4 text-right uppercase text-xs tracking-widest">Total</td>
            <td class="px-6 py-4 text-right">৳ {{ totalDebits.toLocaleString() }}</td>
            <td class="px-6 py-4 text-right">৳ {{ totalCredits.toLocaleString() }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '@/api/axios';

const reportData = ref([]);
const currentDate = new Date().toLocaleDateString();

const fetchData = async () => {
  const res = await api.get('/reports/trial-balance');
  reportData.value = res.data;
};

const totalDebits = computed(() => reportData.value.reduce((sum, r) => sum + r.debit, 0));
const totalCredits = computed(() => reportData.value.reduce((sum, r) => sum + r.credit, 0));

const printReport = () => window.print();

onMounted(fetchData);
</script>