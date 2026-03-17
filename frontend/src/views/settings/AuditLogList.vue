<template>
  <div class="space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border">
      <h1 class="text-2xl font-bold text-gray-800">System Activity Logs</h1>
      <p class="text-sm text-gray-500">History of all record changes and administrative actions</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-gray-50 border-b text-xs text-gray-400 uppercase font-bold">
          <tr>
            <th class="px-6 py-4">Timestamp</th>
            <th class="px-6 py-4">User</th>
            <th class="px-6 py-4">Action</th>
            <th class="px-6 py-4">Module</th>
            <th class="px-6 py-4">Changes</th>
          </tr>
        </thead>
        <tbody class="divide-y text-sm">
          <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 text-gray-500 font-mono">{{ new Date(log.created_at).toLocaleString() }}</td>
            <td class="px-6 py-4 font-bold text-gray-700">{{ log.user?.name || 'System' }}</td>
            <td class="px-6 py-4">
              <span :class="getActionClass(log.action)" class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase">
                {{ log.action }}
              </span>
            </td>
            <td class="px-6 py-4 text-gray-600">{{ formatModuleName(log.auditable_type) }} (ID: {{ log.auditable_id }})</td>
            <td class="px-6 py-4">
              <button @click="viewDetails(log)" class="text-blue-600 hover:underline">View JSON</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api/axios';

const logs = ref([]);

const fetchData = async () => {
  const res = await api.get('/audit-logs');
  logs.value = res.data.data;
};

const formatModuleName = (type) => type.split('\\').pop();

const getActionClass = (action) => {
  if (action === 'created') return 'bg-green-100 text-green-700';
  if (action === 'updated') return 'bg-blue-100 text-blue-700';
  return 'bg-red-100 text-red-700'; // deleted
};

const viewDetails = (log) => {
  console.log('Old:', JSON.parse(log.old_values));
  console.log('New:', JSON.parse(log.new_values));
  alert("Details logged to console for review.");
};

onMounted(fetchData);
</script>