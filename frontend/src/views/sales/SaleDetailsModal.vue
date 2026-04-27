<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
    <div class="bg-white rounded-2xl w-full max-w-6xl max-h-[90vh] overflow-y-auto shadow-2xl animate-slide-up">
      
      <!-- Header -->
      <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center z-10">
        <div>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-800">
                Sale Invoice #{{ sale?.id }}
              </h2>
              <p class="text-sm text-gray-500 mt-0.5">
                {{ formatDate(sale?.sale_date) }}
              </p>
            </div>
          </div>
        </div>
        <div class="flex gap-2">
          <button 
            @click="printInvoice" 
            class="px-4 py-2 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white rounded-lg transition-all shadow-md flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Invoice
          </button>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <div class="p-6 space-y-6">
        <!-- Customer & Warehouse Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <h3 class="font-semibold text-gray-800">Customer Information</h3>
            </div>
            <p class="text-lg font-bold text-gray-900">{{ sale?.customer?.name || 'Walk-in Customer' }}</p>
            <p v-if="sale?.customer?.email" class="text-sm text-gray-600">{{ sale?.customer?.email }}</p>
            <p v-if="sale?.customer?.phone" class="text-sm text-gray-600">{{ sale?.customer?.phone }}</p>
          </div>

          <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-2">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <h3 class="font-semibold text-gray-800">Warehouse Information</h3>
            </div>
            <p class="text-lg font-bold text-gray-900">{{ sale?.warehouse?.name || 'N/A' }}</p>
            <p v-if="sale?.warehouse?.address" class="text-sm text-gray-600">{{ sale?.warehouse?.address }}</p>
          </div>
        </div>

        <!-- Financial Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Revenue</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">
              ৳ {{ formatNumber(sale?.total_amount) }}
            </p>
          </div>

          <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total COGS</p>
            <p class="text-2xl font-bold text-gray-600 mt-1">
              ৳ {{ formatNumber(sale?.total_cogs) }}
            </p>
          </div>

          <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Gross Profit</p>
            <p class="text-2xl font-bold text-green-600 mt-1">
              ৳ {{ formatNumber(sale?.gross_profit) }}
            </p>
          </div>

          <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Payment</p>
            <div class="mt-1">
              <span :class="getPaymentStatusClass(sale?.payment_status)" class="text-xs px-3 py-1 rounded-full">
                {{ sale?.payment_status || 'N/A' }}
              </span>
              <p class="text-sm text-gray-600 mt-2">
                Method: {{ getPaymentMethodLabel(sale?.payment_method) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Sale Items Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-3 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="font-semibold text-gray-800">Sale Items</h3>
                <p class="text-xs text-gray-500 mt-0.5">Detailed breakdown of products sold</p>
              </div>
              <div class="text-sm text-gray-600">
                Total Items: {{ sale?.items?.length || 0 }}
              </div>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Quantity</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit Price</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Cost Price</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Subtotal</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Profit</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr 
                  v-for="(item, index) in sale?.items || []" 
                  :key="item.id"
                  class="hover:bg-gray-50 transition-colors"
                >
                  <td class="px-4 py-3">
                    <span class="text-gray-500">{{ index + 1 }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                      </div>
                      <div>
                        <!-- Debug: Show product data -->
                        <p class="font-medium text-gray-800">
                          {{ getProductName(item) }}
                        </p>
                        <p class="text-xs text-gray-500">
                          SKU: {{ getProductSku(item) }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <span class="font-semibold text-gray-800">{{ item.quantity }}</span>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <span class="text-gray-700">৳ {{ formatNumber(item.selling_price) }}</span>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <span class="text-gray-500">৳ {{ formatNumber(item.cost_price) }}</span>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <span class="font-semibold text-blue-600">৳ {{ formatNumber(item.subtotal) }}</span>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <span class="font-semibold text-green-600">৳ {{ formatNumber(item.gross_profit) }}</span>
                  </td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-50 border-t border-gray-200">
                <tr>
                  <td colspan="5" class="px-4 py-3 text-right font-semibold text-gray-800">Subtotal:</td>
                  <td class="px-4 py-3 text-right font-bold text-blue-600">
                    ৳ {{ formatNumber(calculateSubtotal()) }}
                  </td>
                  <td></td>
                </tr>
                <tr v-if="sale?.discount > 0">
                  <td colspan="5" class="px-4 py-3 text-right text-gray-600">Discount:</td>
                  <td class="px-4 py-3 text-right text-red-600">
                    - ৳ {{ formatNumber(sale.discount) }}
                  </td>
                  <td></td>
                </tr>
                <tr v-if="sale?.tax > 0">
                  <td colspan="5" class="px-4 py-3 text-right text-gray-600">Tax:</td>
                  <td class="px-4 py-3 text-right text-orange-600">
                    + ৳ {{ formatNumber(sale.tax) }}
                  </td>
                  <td></td>
                </tr>
                <tr class="border-t-2 border-gray-300">
                  <td colspan="5" class="px-4 py-3 text-right text-lg font-bold text-gray-800">Total:</td>
                  <td class="px-4 py-3 text-right text-xl font-bold text-green-600">
                    ৳ {{ formatNumber(sale?.total_amount) }}
                  </td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Profit Summary Card -->
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200">
          <div class="flex justify-between items-center">
            <div>
              <h3 class="font-bold text-gray-800 text-lg">Profit Summary</h3>
              <p class="text-sm text-gray-600 mt-1">Overall profitability analysis</p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-600">Profit Margin</p>
              <p class="text-2xl font-bold text-green-600">
                {{ calculateProfitMargin() }}%
              </p>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-green-200">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Total Revenue:</span>
              <span class="font-semibold text-gray-800">৳ {{ formatNumber(sale?.total_amount) }}</span>
            </div>
            <div class="flex justify-between text-sm mt-2">
              <span class="text-gray-600">Total Cost:</span>
              <span class="font-semibold text-gray-800">৳ {{ formatNumber(sale?.total_cogs) }}</span>
            </div>
            <div class="flex justify-between text-sm mt-2">
              <span class="text-gray-600">Total Profit:</span>
              <span class="font-semibold text-green-600">৳ {{ formatNumber(sale?.gross_profit) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import api from '@/api/axios'

const props = defineProps({
  sale: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'refresh'])

// Helper function to get product name safely
const getProductName = (item) => {
  if (!item) return 'N/A'
  // Check different possible locations for product name
  if (item.product?.name) return item.product.name
  if (item.product_name) return item.product_name
  if (item.name) return item.name
  return `Product #${item.product_id || item.id}`
}

// Helper function to get product SKU safely
const getProductSku = (item) => {
  if (!item) return 'N/A'
  if (item.product?.sku) return item.product.sku
  if (item.sku) return item.sku
  return 'N/A'
}

// Helper function to format numbers
const formatNumber = (value) => {
  return Number(value || 0).toFixed(2)
}

// Helper function to format dates
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Calculate subtotal
const calculateSubtotal = () => {
  if (!props.sale?.items) return 0
  return props.sale.items.reduce((sum, item) => sum + Number(item.subtotal || 0), 0)
}

// Calculate profit margin
const calculateProfitMargin = () => {
  const total = Number(props.sale?.total_amount || 0)
  const profit = Number(props.sale?.gross_profit || 0)
  if (total === 0) return 0
  return ((profit / total) * 100).toFixed(2)
}

// Get payment method label
const getPaymentMethodLabel = (method) => {
  const methods = {
    'cash': 'Cash',
    'card': 'Card',
    'bank': 'Bank Transfer',
    'mobile': 'Mobile Wallet',
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

// Print invoice
const printInvoice = async () => {
  try {
    const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
    const receiptUrl = `${apiUrl}/api/sales/${props.sale.id}/receipt`
    window.open(receiptUrl, '_blank')
  } catch (error) {
    console.error('Error printing invoice:', error)
    alert('Failed to print invoice. Please try again.')
  }
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