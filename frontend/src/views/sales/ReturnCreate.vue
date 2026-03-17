<template>
  <div class="p-6 max-w-4xl mx-auto space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border">
      <h1 class="text-2xl font-bold text-gray-800">Process Sale Return</h1>
      <div class="mt-4 flex gap-4">
        <input 
          v-model="saleSearchId" 
          placeholder="Enter Sale ID (e.g. 101)" 
          class="flex-1 p-3 border rounded-xl"
        />
        <button @click="fetchSale" class="bg-blue-600 text-white px-6 rounded-xl font-bold">Find Sale</button>
      </div>
    </div>

    <div v-if="selectedSale" class="bg-white rounded-2xl shadow-sm border overflow-hidden">
      <div class="p-6 border-b bg-gray-50 flex justify-between">
        <div>
          <p class="text-xs font-bold text-gray-400 uppercase">Customer</p>
          <p class="font-bold">{{ selectedSale.customer?.name || 'Walk-in' }}</p>
        </div>
        <div class="text-right">
          <p class="text-xs font-bold text-gray-400 uppercase">Original Total</p>
          <p class="font-bold text-blue-600">৳{{ selectedSale.total_amount }}</p>
        </div>
      </div>

      <table class="w-full text-left">
        <thead class="text-xs text-gray-400 uppercase font-bold border-b">
          <tr>
            <th class="p-6">Product</th>
            <th class="p-6">Sold Qty</th>
            <th class="p-6">Return Qty</th>
            <th class="p-6 text-right">Refund Subtotal</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="item in selectedSale.items" :key="item.id">
            <td class="p-6 font-medium">{{ item.product.name }}</td>
            <td class="p-6">{{ item.quantity }}</td>
            <td class="p-6">
              <input 
                type="number" 
                v-model="returnItems[item.product_id]" 
                class="w-20 border rounded p-2"
                :max="item.quantity"
                min="0"
              />
            </td>
            <td class="p-6 text-right font-bold">
              ৳{{ ((returnItems[item.product_id] || 0) * item.selling_price).toLocaleString() }}
            </td>
          </tr>
        </tbody>
      </table>

      <div class="p-6 bg-gray-50 border-t space-y-4">
        <textarea v-model="reason" placeholder="Reason for return..." class="w-full border rounded-xl p-3"></textarea>
        <div class="flex justify-between items-center">
          <p class="text-xl font-black">Total Refund: ৳{{ totalRefund.toLocaleString() }}</p>
          <button @click="submitReturn" :disabled="loading" class="bg-red-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-red-100">
            {{ loading ? 'Processing...' : 'Complete Return & Refund' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import api from '@/api/axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const saleSearchId = ref('');
const selectedSale = ref(null);
const returnItems = ref({});
const reason = ref('');
const loading = ref(false);

const fetchSale = async () => {
  try {
    const res = await api.get(`/sales/${saleSearchId.value}`);
    selectedSale.value = res.data.data;
    // Initialize return quantities to 0
    selectedSale.value.items.forEach(i => returnItems.value[i.product_id] = 0);
  } catch (e) { alert("Sale not found"); }
};

const totalRefund = computed(() => {
  if (!selectedSale.value) return 0;
  return selectedSale.value.items.reduce((sum, item) => {
    return sum + ((returnItems.value[item.product_id] || 0) * item.selling_price);
  }, 0);
});

const submitReturn = async () => {
  const itemsToReturn = Object.entries(returnItems.value)
    .filter(([id, qty]) => qty > 0)
    .map(([id, qty]) => ({ product_id: id, quantity: qty }));

  if (!itemsToReturn.length) return alert("Select at least one item to return");
  
  loading.value = true;
  try {
    await api.post('/returns', {
      sale_id: selectedSale.value.id,
      items: itemsToReturn,
      reason: reason.value
    });
    alert("Return processed successfully!");
    router.push('/sales');
  } catch (e) {
    alert(e.response?.data?.message || "Return failed");
  } finally {
    loading.value = false;
  }
};
</script>