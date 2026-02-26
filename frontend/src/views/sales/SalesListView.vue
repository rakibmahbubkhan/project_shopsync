<template>
  <div class="p-6">

    <h1 class="text-2xl font-bold mb-6">Sales</h1>

    <div class="bg-white shadow rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-3 text-left">#</th>
            <th class="p-3 text-left">Customer</th>
            <th class="p-3 text-left">Warehouse</th>
            <th class="p-3 text-left">Total</th>
            <th class="p-3 text-left">Profit</th>
            <th class="p-3 text-left">Date</th>
            <th class="p-3 text-left">Action</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="sale in sales"
            :key="sale.id"
            class="border-t"
          >
            <td class="p-3">{{ sale.id }}</td>
            <td class="p-3">{{ sale.customer?.name }}</td>
            <td class="p-3">{{ sale.warehouse?.name }}</td>
            <td class="p-3">৳ {{ sale.total_amount }}</td>
            <td class="p-3">৳ {{ sale.gross_profit }}</td>
            <td class="p-3">{{ sale.sale_date }}</td>
            <td class="p-3">
              <button
                @click="openSale(sale.id)"
                class="px-3 py-1 bg-blue-600 text-white rounded"
              >
                View
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <SaleDetailsModal
      v-if="selectedSale"
      :sale="selectedSale"
      @close="selectedSale = null"
      @refresh="fetchSales"
    />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'
import SaleDetailsModal from './SaleDetailsModal.vue'

const sales = ref([])
const selectedSale = ref(null)

const fetchSales = async () => {
  const res = await api.get('/sales')
  sales.value = res.data.data
}

const openSale = async (id) => {
  const res = await api.get(`/sales/${id}`)
  selectedSale.value = res.data.data
}

onMounted(fetchSales)
</script>