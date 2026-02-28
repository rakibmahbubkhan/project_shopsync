<template>
  <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white w-4/5 rounded-xl p-6 max-h-[90vh] overflow-y-auto">
      
      <!-- Header -->
      <div class="flex justify-between mb-6">
        <h2 class="text-xl font-bold">
          Sale #{{ sale.id }}
        </h2>
        <button @click="$emit('close')" class="text-red-500 text-xl">✕</button>
      </div>

      <!-- Return Items Table -->
      <table class="w-full text-sm border">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-2 text-left">Product</th>
            <th class="p-2 text-left">Sold</th>
            <th class="p-2 text-left">Returned</th>
            <th class="p-2 text-left">Remaining</th>
            <th class="p-2 text-left">Return Qty</th>
            <th class="p-2 text-left">Reason</th>
            <th class="p-2 text-left">Payment Method</th>
            <th class="p-2 text-left">Action</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="item in sale.items"
            :key="item.id"
            class="border-t"
          >
            <td class="p-2">{{ item.product.name }}</td>
            <td class="p-2">{{ item.quantity }}</td>
            <td class="p-2 text-red-600">
              {{ getReturnedQty(item.product_id) }}
            </td>
            <td class="p-2 font-semibold">
              {{ getRemainingQty(item) }}
            </td>
            <td class="p-2">
              <input
                type="number"
                min="1"
                :max="getRemainingQty(item)"
                v-model.number="returnQuantities[item.product_id]"
                class="border p-1 w-20 rounded"
                :disabled="getRemainingQty(item) === 0"
              />
            </td>
            <td class="p-2">
              <select
                v-model="returnReasons[item.product_id]"
                class="border p-1 rounded min-w-[120px]"
                :disabled="getRemainingQty(item) === 0"
              >
                <option value="">Select reason</option>
                <option value="damaged">Damaged</option>
                <option value="wrong_item">Wrong Item</option>
                <option value="customer_request">Customer Request</option>
                <option value="expired">Expired</option>
                <option value="defective">Defective</option>
                <option value="other">Other</option>
              </select>
            </td>
            <td class="p-2">
              <select
                v-model="paymentMethods[item.product_id]"
                class="border p-1 rounded"
                :disabled="getRemainingQty(item) === 0"
              >
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="wallet">Wallet</option>
              </select>
            </td>
            <td class="p-2">
              <button
                @click="returnItem(item)"
                :disabled="getRemainingQty(item) === 0"
                class="bg-red-600 text-white px-3 py-1 rounded disabled:bg-gray-300"
              >
                Return
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Refunds Section -->
      <div v-if="sale.returns?.length" class="mt-8">
        <h3 class="font-bold mb-3">Refunds</h3>
        
        <table class="w-full text-sm border">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2 text-left">Product</th>
              <th class="p-2 text-left">Qty</th>
              <th class="p-2 text-left">Reason</th>
              <th class="p-2 text-left">Status</th>
              <th class="p-2 text-left">Amount</th>
              <th class="p-2 text-left">Payment Method</th>
              <th class="p-2 text-left">Processed By</th>
              <th class="p-2 text-left">Date</th>
              <th class="p-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="ret in sale.returns" 
              :key="ret.id" 
            >
              <td class="p-2">{{ ret.product.name }}</td>
              <td class="p-2">{{ ret.quantity }}</td>
              <td class="p-2">
                <span class="capitalize">{{ formatReason(ret.reason || 'N/A') }}</span>
              </td>
              <td class="p-2">
                <span 
                  class="px-2 py-1 rounded-full text-xs font-semibold"
                  :class="getStatusClass(ret.status)"
                >
                  {{ ret.status || 'pending' }}
                </span>
              </td>
              <td class="p-2 text-red-600">৳ {{ ret.refund?.amount || 'N/A' }}</td>
              <td class="p-2">{{ ret.refund?.payment_method || 'N/A' }}</td>
              <td class="p-2">{{ ret.refund?.processed_by || 'N/A' }}</td>
              <td class="p-2">{{ formatDate(ret.refund?.created_at || ret.created_at) }}</td>
              <td class="p-2">
                <button
                  @click="printReturn(ret.id)"
                  class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700"
                  :disabled="ret.status !== 'approved'"
                >
                  Print Receipt
                </button>
              </td>
            </tr>
            <tr v-if="!sale.returns?.length">
              <td colspan="9" class="p-4 text-center text-gray-500">
                No returns processed yet
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Financial Summary -->
      <div class="mt-8 grid grid-cols-3 gap-4 text-center">
        <div class="bg-gray-100 p-4 rounded">
          <p class="text-sm text-gray-500">Total Revenue</p>
          <p class="text-lg font-bold">
            ৳ {{ sale.total_amount }}
          </p>
        </div>

        <div class="bg-gray-100 p-4 rounded">
          <p class="text-sm text-gray-500">Total COGS</p>
          <p class="text-lg font-bold">
            ৳ {{ sale.total_cogs }}
          </p>
        </div>

        <div class="bg-green-100 p-4 rounded">
          <p class="text-sm text-gray-500">Gross Profit</p>
          <p class="text-lg font-bold text-green-700">
            ৳ {{ sale.gross_profit }}
          </p>
        </div>
      </div>

      <!-- Current Total -->
      <div class="mt-4 text-right font-semibold">
        Current Total: ৳ {{ sale.total_amount }}
      </div>

    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import api from '@/api/axios'

const props = defineProps({
  sale: Object
})

const emit = defineEmits(['close', 'refresh'])

const returnQuantities = reactive({})
const returnReasons = reactive({})
const paymentMethods = reactive({})

// Helper function to format dates
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleString()
}

// Format reason for display
const formatReason = (reason) => {
  const reasonMap = {
    'damaged': 'Damaged',
    'wrong_item': 'Wrong Item',
    'customer_request': 'Customer Request',
    'expired': 'Expired',
    'defective': 'Defective',
    'other': 'Other'
  }
  return reasonMap[reason] || reason
}

// Get status class for styling
const getStatusClass = (status) => {
  const statusClasses = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'approved': 'bg-green-100 text-green-800',
    'rejected': 'bg-red-100 text-red-800',
    'completed': 'bg-blue-100 text-blue-800',
    'cancelled': 'bg-gray-100 text-gray-800'
  }
  return statusClasses[status?.toLowerCase()] || 'bg-gray-100 text-gray-800'
}

// Get total returned quantity for a product
const getReturnedQty = (productId) => {
  if (!props.sale.returns) return 0

  return props.sale.returns
    .filter(r => r.product_id === productId)
    .reduce((sum, r) => sum + r.quantity, 0)
}

// Calculate remaining quantity available for return
const getRemainingQty = (item) => {
  return item.quantity - getReturnedQty(item.product_id)
}

// Process return
const returnItem = async (item) => {
  const quantity = returnQuantities[item.product_id]
  const reason = returnReasons[item.product_id]
  const paymentMethod = paymentMethods[item.product_id]

  // Validation
  if (!quantity || quantity <= 0) {
    alert("Enter valid quantity")
    return
  }

  if (!reason) {
    alert("Select return reason")
    return
  }

  if (!paymentMethod) {
    alert("Select payment method")
    return
  }

  if (quantity > getRemainingQty(item)) {
    alert("Exceeds remaining quantity")
    return
  }

  try {
    const res = await api.post(`/sales/${props.sale.id}/return`, {
      product_id: item.product_id,
      quantity,
      reason: returnReasons[item.product_id],
      payment_method: paymentMethod || 'cash'
    })

    alert("Return processed successfully")

    // Update sale live with the returned data
    Object.assign(props.sale, res.data.sale.data)

    // Reset input, reason and payment method
    returnQuantities[item.product_id] = null
    returnReasons[item.product_id] = ''
    paymentMethods[item.product_id] = 'cash'

    // Notify parent to refresh data
    emit('refresh')

  } catch (e) {
    alert(e.response?.data?.message || "Return failed")
  }
}

// Print return receipt
const printReturn = (returnId) => {
  const returnItem = props.sale.returns.find(r => r.id === returnId)
  
  const printWindow = window.open('', '_blank')
  printWindow.document.write(`
    <html>
      <head>
        <title>Return Receipt #${returnId}</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; }
          .receipt { max-width: 300px; margin: 0 auto; }
          .header { text-align: center; margin-bottom: 20px; }
          .status-badge { 
            display: inline-block;
            padding: 4px 8px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            background-color: ${returnItem?.status === 'approved' ? '#dcfce7' : '#fef9c3'};
            color: ${returnItem?.status === 'approved' ? '#166534' : '#854d0e'};
          }
          .details { margin-bottom: 20px; }
          .items { width: 100%; border-collapse: collapse; }
          .items th, .items td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
          .total { text-align: right; margin-top: 20px; font-weight: bold; }
          .reason { margin-top: 10px; padding: 8px; background-color: #f3f4f6; border-radius: 4px; }
        </style>
      </head>
      <body>
        <div class="receipt">
          <div class="header">
            <h2>Return Receipt</h2>
            <p>Receipt #: ${returnId}</p>
            <p>Date: ${new Date().toLocaleString()}</p>
            <div class="status-badge">${returnItem?.status || 'pending'}</div>
          </div>
          <div class="details">
            <p><strong>Sale #:</strong> ${props.sale.id}</p>
          </div>
          <table class="items">
            <thead>
              <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              ${props.sale.returns
                .filter(r => r.id === returnId)
                .map(ret => `
                  <tr>
                    <td>${ret.product.name}</td>
                    <td>${ret.quantity}</td>
                    <td>৳ ${ret.refund?.amount || 'N/A'}</td>
                  </tr>
                `).join('')}
            </tbody>
          </table>
          <div class="reason">
            <strong>Return Reason:</strong> ${formatReason(returnItem?.reason || 'N/A')}
          </div>
          <div class="total">
            Total Refund: ৳ ${props.sale.returns
              .filter(r => r.id === returnId)
              .reduce((sum, ret) => sum + (ret.refund?.amount || 0), 0)}
          </div>
        </div>
        <script>
          window.onload = () => window.print()
        <\/script>
      </body>
    </html>
  `)
  printWindow.document.close()
}
</script>

<style scoped>
.fixed {
  z-index: 1000;
}

.capitalize {
  text-transform: capitalize;
}
</style>