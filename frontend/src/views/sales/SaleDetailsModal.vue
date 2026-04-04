<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
    <div class="bg-white rounded-2xl w-full max-w-7xl max-h-[90vh] overflow-y-auto shadow-2xl animate-slide-up">
      
      <!-- Header -->
      <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center">
        <div>
          <h2 class="text-xl font-bold text-gray-800">
            Sale #{{ sale?.id }}
          </h2>
          <p class="text-sm text-gray-500 mt-0.5">
            {{ formatDate(sale?.sale_date) }}
          </p>
        </div>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="p-6 space-y-6">
        <!-- Financial Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Revenue</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">
              ৳ {{ Number(sale?.total_amount || 0).toFixed(2) }}
            </p>
          </div>

          <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total COGS</p>
            <p class="text-2xl font-bold text-gray-600 mt-1">
              ৳ {{ Number(sale?.total_cogs || 0).toFixed(2) }}
            </p>
          </div>

          <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Gross Profit</p>
            <p class="text-2xl font-bold text-green-600 mt-1">
              ৳ {{ Number(sale?.gross_profit || 0).toFixed(2) }}
            </p>
          </div>

          <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Payment Status</p>
            <p class="text-2xl font-bold mt-1">
              <span :class="getPaymentStatusClass(sale?.payment_status)" class="text-sm px-3 py-1 rounded-full">
                {{ sale?.payment_status || 'N/A' }}
              </span>
            </p>
          </div>
        </div>

        <!-- Return Items Section -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Return Items</h3>
            <p class="text-xs text-gray-500 mt-0.5">Process product returns and refunds</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sold</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Returned</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Remaining</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Return Qty</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reason</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment Method</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr
                  v-for="item in sale?.items || []"
                  :key="item.id"
                  class="hover:bg-gray-50 transition-colors"
                >
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                      </div>
                      <span class="font-medium text-gray-800">{{ item.product?.name || 'N/A' }}</span>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <span class="text-gray-700">{{ item.quantity }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <span class="text-red-600 font-medium">{{ getReturnedQty(item.product_id) }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <span class="font-semibold text-gray-800">{{ getRemainingQty(item) }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <input
                      type="number"
                      min="1"
                      :max="getRemainingQty(item)"
                      v-model.number="returnQuantities[item.product_id]"
                      class="w-20 px-2 py-1 border-2 border-gray-200 rounded-lg focus:border-red-300 focus:ring-2 focus:ring-red-100 outline-none transition-all"
                      :disabled="getRemainingQty(item) === 0"
                    />
                  </td>
                  <td class="px-4 py-3">
                    <select
                      v-model="returnReasons[item.product_id]"
                      class="px-2 py-1 border-2 border-gray-200 rounded-lg focus:border-red-300 focus:ring-2 focus:ring-red-100 outline-none transition-all bg-white min-w-[120px]"
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
                  <td class="px-4 py-3">
                    <select
                      v-model="paymentMethods[item.product_id]"
                      class="px-2 py-1 border-2 border-gray-200 rounded-lg focus:border-red-300 focus:ring-2 focus:ring-red-100 outline-none transition-all bg-white"
                      :disabled="getRemainingQty(item) === 0"
                    >
                      <option value="cash">Cash</option>
                      <option value="card">Card</option>
                      <option value="wallet">Mobile Wallet</option>
                    </select>
                  </td>
                  <td class="px-4 py-3">
                    <button
                      @click="returnItem(item)"
                      :disabled="getRemainingQty(item) === 0 || isProcessing"
                      class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-4 py-1.5 rounded-lg transition-all disabled:from-gray-300 disabled:to-gray-400 disabled:cursor-not-allowed text-sm font-medium"
                    >
                      Process Return
                    </button>
                  </td>
                </tr>
                
                <tr v-if="!sale?.items?.length">
                  <td :colspan="8" class="px-4 py-8 text-center text-gray-500">
                    No items found in this sale
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Refunds History Section -->
        <div v-if="sale?.returns?.length" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Refund History</h3>
            <p class="text-xs text-gray-500 mt-0.5">Track all processed returns and refunds</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Qty</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reason</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment Method</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Processed By</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr 
                  v-for="ret in sale.returns" 
                  :key="ret.id" 
                  class="hover:bg-gray-50 transition-colors"
                >
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                      </div>
                      <span class="text-gray-800">{{ ret.product?.name || 'N/A' }}</span>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <span class="font-medium">{{ ret.quantity }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <span class="capitalize px-2 py-1 bg-gray-100 rounded-full text-xs">{{ formatReason(ret.reason || 'N/A') }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <span 
                      class="px-2 py-1 rounded-full text-xs font-semibold"
                      :class="getStatusClass(ret.status)"
                    >
                      {{ ret.status || 'pending' }}
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <span class="text-red-600 font-semibold">৳ {{ Number(ret.refund?.amount || 0).toFixed(2) }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <span class="text-gray-700">{{ getPaymentMethodLabel(ret.refund?.payment_method) }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <span class="text-gray-700">{{ ret.refund?.processed_by || 'N/A' }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <span class="text-gray-600 text-xs">{{ formatDate(ret.refund?.created_at || ret.created_at) }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <button
                      @click="printReturn(ret.id)"
                      class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-3 py-1 rounded-lg text-xs transition-all disabled:from-gray-300 disabled:to-gray-400"
                      :disabled="ret.status !== 'approved'"
                    >
                      Print Receipt
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import api from '@/api/axios'

const props = defineProps({
  sale: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'refresh'])

const returnQuantities = reactive({})
const returnReasons = reactive({})
const paymentMethods = reactive({})
const isProcessing = ref(false)

// Helper function to format dates
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
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

// Get payment method label
const getPaymentMethodLabel = (method) => {
  const methods = {
    'cash': 'Cash',
    'card': 'Card',
    'wallet': 'Mobile Wallet'
  }
  return methods[method] || method || 'N/A'
}

// Get payment status class
const getPaymentStatusClass = (status) => {
  const statusClasses = {
    'paid': 'bg-green-100 text-green-700',
    'pending': 'bg-yellow-100 text-yellow-700',
    'partial': 'bg-blue-100 text-blue-700',
    'failed': 'bg-red-100 text-red-700'
  }
  return statusClasses[status?.toLowerCase()] || 'bg-gray-100 text-gray-700'
}

// Get status class for returns
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
  if (!props.sale?.returns) return 0

  return props.sale.returns
    .filter(r => r.product_id === productId)
    .reduce((sum, r) => sum + (r.quantity || 0), 0)
}

// Calculate remaining quantity available for return
const getRemainingQty = (item) => {
  if (!item) return 0
  return (item.quantity || 0) - getReturnedQty(item.product_id)
}

// Process return
const returnItem = async (item) => {
  const quantity = returnQuantities[item.product_id]
  const reason = returnReasons[item.product_id]
  const paymentMethod = paymentMethods[item.product_id]

  // Validation
  if (!quantity || quantity <= 0) {
    alert("Please enter a valid quantity")
    return
  }

  if (!reason) {
    alert("Please select a return reason")
    return
  }

  if (!paymentMethod) {
    alert("Please select a payment method")
    return
  }

  const remainingQty = getRemainingQty(item)
  if (quantity > remainingQty) {
    alert(`Maximum return quantity is ${remainingQty}`)
    return
  }

  isProcessing.value = true

  try {
    const res = await api.post(`/sales/${props.sale.id}/return`, {
      product_id: item.product_id,
      quantity: quantity,
      reason: reason,
      payment_method: paymentMethod
    })

    alert("Return processed successfully")

    // Update sale data
    if (res.data.sale?.data) {
      Object.assign(props.sale, res.data.sale.data)
    }

    // Reset inputs for this product
    delete returnQuantities[item.product_id]
    delete returnReasons[item.product_id]
    delete paymentMethods[item.product_id]

    // Notify parent to refresh data
    emit('refresh')

  } catch (error) {
    console.error('Return error:', error)
    const errorMessage = error.response?.data?.message || error.message || "Return failed"
    alert(`Return failed: ${errorMessage}`)
  } finally {
    isProcessing.value = false
  }
}

// Print return receipt
const printReturn = (returnId) => {
  const returnItem = props.sale.returns?.find(r => r.id === returnId)
  if (!returnItem) return
  
  const printWindow = window.open('', '_blank')
  printWindow.document.write(`
    <html>
      <head>
        <title>Return Receipt #${returnId}</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; }
          .receipt { max-width: 400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
          .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; }
          .status-badge { 
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            background-color: ${returnItem.status === 'approved' ? '#dcfce7' : '#fef9c3'};
            color: ${returnItem.status === 'approved' ? '#166534' : '#854d0e'};
          }
          .details { margin-bottom: 20px; background: #f9fafb; padding: 10px; border-radius: 6px; }
          .items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
          .items th, .items td { padding: 8px; text-align: left; border-bottom: 1px solid #e5e7eb; }
          .total { text-align: right; margin-top: 20px; padding-top: 10px; border-top: 2px solid #e5e7eb; font-weight: bold; }
          .reason { margin-top: 10px; padding: 10px; background-color: #f3f4f6; border-radius: 6px; }
          .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #6b7280; }
        </style>
      </head>
      <body>
        <div class="receipt">
          <div class="header">
            <h2>Return Receipt</h2>
            <p>Receipt #: ${returnId}</p>
            <p>Date: ${new Date().toLocaleString()}</p>
            <div class="status-badge">${returnItem.status || 'pending'}</div>
          </div>
          <div class="details">
            <p><strong>Sale #:</strong> ${props.sale.id}</p>
            <p><strong>Customer:</strong> ${props.sale.customer?.name || 'Walk-in Customer'}</p>
          </div>
          <table class="items">
            <thead>
              <tr><th>Product</th><th>Qty</th><th>Amount</th></tr>
            </thead>
            <tbody>
              <tr>
                <td>${returnItem.product?.name || 'N/A'}</td>
                <td>${returnItem.quantity}</td>
                <td>৳ ${Number(returnItem.refund?.amount || 0).toFixed(2)}</td>
              </tr>
            </tbody>
          </table>
          <div class="reason">
            <strong>Return Reason:</strong> ${formatReason(returnItem.reason)}
          </div>
          <div class="total">
            Total Refund Amount: ৳ ${Number(returnItem.refund?.amount || 0).toFixed(2)}
          </div>
          <div class="footer">
            <p>Thank you for your business!</p>
          </div>
        </div>
        <script>
          window.onload = () => setTimeout(() => window.print(), 500)
        <\/script>
      </body>
    </html>
  `)
  printWindow.document.close()
}
</script>

<style scoped>
@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}

.capitalize {
  text-transform: capitalize;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>