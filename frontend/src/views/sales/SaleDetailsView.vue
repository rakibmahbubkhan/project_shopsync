<template>
  <div class="p-6 space-y-6">

    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold">
          Sale #{{ sale.id }}
        </h1>
        <p class="text-gray-500">
          {{ sale.sale_date }}
        </p>
      </div>

      <div class="flex gap-3">
        <span :class="statusClass">
          {{ sale.payment_status }}
        </span>

        <button
          @click="printReceipt"
          class="bg-blue-600 text-white px-4 py-2 rounded"
        >
          Print Receipt
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-4 gap-4">

      <div class="bg-white p-4 rounded-xl shadow">
        <p class="text-sm text-gray-500">Revenue</p>
        <p class="text-xl font-bold">৳ {{ sale.total_amount }}</p>
      </div>

      <div class="bg-white p-4 rounded-xl shadow">
        <p class="text-sm text-gray-500">COGS</p>
        <p class="text-xl font-bold">৳ {{ sale.total_cogs }}</p>
      </div>

      <div class="bg-white p-4 rounded-xl shadow">
        <p class="text-sm text-gray-500">Gross Profit</p>
        <p class="text-xl font-bold"
           :class="sale.gross_profit >= 0 ? 'text-green-600' : 'text-red-600'">
          ৳ {{ sale.gross_profit }}
        </p>
      </div>

      <div class="bg-white p-4 rounded-xl shadow">
        <p class="text-sm text-gray-500">Warehouse</p>
        <p class="text-lg font-semibold">
          {{ sale.warehouse?.name }}
        </p>
      </div>

    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">

      <table class="w-full text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-3 text-left">Product</th>
            <th class="p-3 text-left">Sold</th>
            <th class="p-3 text-left">Returned</th>
            <th class="p-3 text-left">Remaining</th>
            <th class="p-3 text-left">Return Qty</th>
            <th class="p-3 text-left">Refund Method</th>
            <th class="p-3 text-left">Action</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="item in sale.items"
            :key="item.id"
            class="border-t"
          >
            <td class="p-3">{{ item.product.name }}</td>

            <td class="p-3">{{ item.quantity }}</td>

            <td class="p-3 text-red-600">
              {{ getReturnedQty(item.product_id) }}
            </td>

            <td class="p-3 font-semibold">
              {{ getRemainingQty(item) }}
            </td>

            <td class="p-3">
              <input
                type="number"
                min="1"
                :max="getRemainingQty(item)"
                v-model.number="returnState[item.product_id]?.quantity"
                class="border p-1 w-20 rounded"
                :disabled="getRemainingQty(item) === 0"
              />
            </td>

            <td class="p-3">
              <select
                v-model="returnState[item.product_id]?.refund_method"
                class="border p-1 rounded"
              >
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="wallet">Wallet</option>
              </select>
            </td>

            <td class="p-3">
              <button
                @click="processReturn(item)"
                :disabled="getRemainingQty(item) === 0"
                class="bg-red-600 text-white px-3 py-1 rounded disabled:bg-gray-300"
              >
                Return
              </button>
            </td>

          </tr>
        </tbody>
      </table>

    </div>

    <!-- Return History -->
    <div v-if="sale.returns?.length" class="bg-white rounded-xl shadow p-4">

      <h3 class="font-bold mb-4">Return History</h3>

      <table class="w-full text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-2 text-left">Product</th>
            <th class="p-2 text-left">Qty</th>
            <th class="p-2 text-left">Refund</th>
            <th class="p-2 text-left">Method</th>
            <th class="p-2 text-left">Date</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="ret in sale.returns" :key="ret.id" class="border-t">
            <td class="p-2">{{ ret.product.name }}</td>
            <td class="p-2">{{ ret.quantity }}</td>
            <td class="p-2 text-red-600">
              ৳ {{ ret.refund_amount }}
            </td>
            <td class="p-2">{{ ret.refund_method }}</td>
            <td class="p-2">{{ ret.created_at }}</td>
          </tr>
        </tbody>
      </table>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'

const route = useRoute()
const sale = ref({})
const returnState = ref({})

const fetchSale = async () => {
  const res = await api.get(`/sales/${route.params.id}`)
  sale.value = res.data.data

  sale.value.items.forEach(item => {
    returnState.value[item.product_id] = {
      quantity: 1,
      refund_method: 'cash'
    }
  })
}

const getReturnedQty = (productId) => {
  if (!sale.value.returns) return 0
  return sale.value.returns
    .filter(r => r.product_id === productId)
    .reduce((sum, r) => sum + r.quantity, 0)
}

const getRemainingQty = (item) => {
  return item.quantity - getReturnedQty(item.product_id)
}

const processReturn = async (item) => {

  const state = returnState.value[item.product_id]

  if (state.quantity > getRemainingQty(item)) {
    alert("Exceeds remaining")
    return
  }

  await api.post(`/sales/${sale.value.id}/return`, {
    product_id: item.product_id,
    quantity: state.quantity,
    refund_method: state.refund_method
  })

  await fetchSale() // live update
}

const statusClass = computed(() => {
  switch (sale.value.payment_status) {
    case 'paid': return 'bg-green-100 text-green-700 px-3 py-1 rounded'
    case 'pending': return 'bg-yellow-100 text-yellow-700 px-3 py-1 rounded'
    default: return 'bg-gray-100 text-gray-700 px-3 py-1 rounded'
  }
})

const printReceipt = () => {
  window.open(`/api/sales/${sale.value.id}/receipt`, '_blank')
}

onMounted(fetchSale)
</script>