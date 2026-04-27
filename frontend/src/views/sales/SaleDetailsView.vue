<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/30">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8">
      
      <!-- Header Section -->
      <div class="mb-6 md:mb-8">
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg shadow-slate-200/50 border border-white/50 p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                  <router-link to="/sales" class="hover:text-blue-600 transition-colors">Sales</router-link>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <span class="text-slate-700 font-medium">Sale Details</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">
                  Sale Invoice #{{ sale?.id }}
                </h1>
                <p class="text-sm text-slate-500 mt-0.5">View complete sale information and transaction details</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <button 
                @click="downloadInvoice" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 text-purple-600 hover:bg-purple-100 rounded-xl transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download Invoice
              </button>
              <router-link 
                to="/sales" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-xl transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Sales
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Sale Overview Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 uppercase tracking-wider">Invoice #</p>
              <p class="text-lg font-bold text-slate-800 mt-1">#{{ sale?.id }}</p>
            </div>
            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 uppercase tracking-wider">Date</p>
              <p class="text-lg font-bold text-slate-800 mt-1">{{ formatDate(sale?.sale_date) }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 uppercase tracking-wider">Payment Status</p>
              <span :class="paymentStatusClass" class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-medium">
                {{ formatPaymentStatus(sale?.payment_status) }}
              </span>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 uppercase tracking-wider">Payment Method</p>
              <p class="text-lg font-bold text-slate-800 mt-1 capitalize">{{ sale?.payment_method || 'N/A' }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 uppercase tracking-wider">Total Amount</p>
              <p class="text-lg font-bold text-emerald-600 mt-1">৳{{ formatNumber(sale?.total_amount) }}</p>
            </div>
            <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Customer & Order Info -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Customer Information -->
          <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <h3 class="font-semibold text-slate-800">Customer Information</h3>
              </div>
            </div>
            <div class="p-4 space-y-3">
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center text-blue-700 font-semibold">
                  {{ getInitials(sale?.customer?.name) }}
                </div>
                <div class="flex-1">
                  <p class="font-semibold text-slate-800">{{ sale?.customer?.name || 'Walk-in Customer' }}</p>
                  <p class="text-sm text-slate-500">{{ sale?.customer?.contact_person || 'No contact person' }}</p>
                </div>
              </div>
              <div class="border-t border-slate-100 pt-3">
                <div class="flex items-center gap-2 text-sm">
                  <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <span class="text-slate-600">{{ sale?.customer?.email || 'No email' }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm mt-2">
                  <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  <span class="text-slate-600">{{ sale?.customer?.mobile_number || 'No mobile' }}</span>
                </div>
              </div>
              <div class="border-t border-slate-100 pt-3">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Billing Address</p>
                <p class="text-sm text-slate-600">{{ sale?.customer?.billing_address }}, {{ sale?.customer?.billing_city }}, {{ sale?.customer?.billing_country }}</p>
              </div>
            </div>
          </div>

          <!-- Order Information -->
          <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="font-semibold text-slate-800">Order Information</h3>
              </div>
            </div>
            <div class="p-4 space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-slate-500">Warehouse</span>
                <span class="text-sm font-medium text-slate-700">{{ sale?.warehouse?.name || 'N/A' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-slate-500">Processed By</span>
                <span class="text-sm font-medium text-slate-700">{{ sale?.user?.name || 'N/A' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-slate-500">Items Count</span>
                <span class="text-sm font-medium text-slate-700">{{ sale?.items?.length || 0 }} products</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-slate-500">Total Quantity</span>
                <span class="text-sm font-medium text-slate-700">{{ totalQuantity }} units</span>
              </div>
            </div>
          </div>

          <!-- Payment Information -->
          <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="font-semibold text-slate-800">Payment Summary</h3>
              </div>
            </div>
            <div class="p-4 space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-slate-500">Subtotal</span>
                <span class="text-sm font-medium text-slate-700">৳{{ formatNumber(subtotal) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-slate-500">Discount</span>
                <span class="text-sm font-medium text-red-600">- ৳{{ formatNumber(sale?.discount) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-slate-500">Tax</span>
                <span class="text-sm font-medium text-emerald-600">+ ৳{{ formatNumber(sale?.tax) }}</span>
              </div>
              <div class="border-t border-slate-100 pt-2 flex justify-between">
                <span class="text-base font-semibold text-slate-800">Grand Total</span>
                <span class="text-xl font-bold text-emerald-600">৳{{ formatNumber(sale?.total_amount) }}</span>
              </div>
              <div class="border-t border-slate-100 pt-2 mt-1 flex justify-between">
                <span class="text-sm text-slate-500">Paid Amount</span>
                <span class="text-sm font-semibold text-green-600">৳{{ formatNumber(sale?.paid_amount) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-slate-500">Due Amount</span>
                <span class="text-sm font-bold text-red-600">৳{{ formatNumber(dueAmount) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Items & Returns -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Items Table -->
          <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <h3 class="font-semibold text-slate-800">Invoice Items</h3>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                  <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Product</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Quantity</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Unit Price</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Cost Price</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Subtotal</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Profit</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="item in sale?.items" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-4 py-3">
                      <div class="font-medium text-slate-800">{{ getProductName(item) }}</div>
                      <div class="text-xs text-slate-400">SKU: {{ getProductSku(item) }}</div>
                    </td>
                    <td class="px-4 py-3 text-center font-medium text-slate-700">{{ item.quantity }}</td>
                    <td class="px-4 py-3 text-right text-slate-600">৳{{ formatNumber(item.selling_price) }}</td>
                    <td class="px-4 py-3 text-right text-slate-500">৳{{ formatNumber(item.cost_price) }}</td>
                    <td class="px-4 py-3 text-right font-semibult text-slate-800">৳{{ formatNumber(item.subtotal) }}</td>
                    <td class="px-4 py-3 text-right">
                      <span :class="item.gross_profit >= 0 ? 'text-green-600' : 'text-red-600'" class="font-medium">
                        ৳{{ formatNumber(item.gross_profit) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-slate-50 border-t border-slate-200">
                  <tr>
                    <td colspan="4" class="px-4 py-3 text-right font-semibold text-slate-700">Total:</td>
                    <td class="px-4 py-3 text-right font-bold text-emerald-600">৳{{ formatNumber(sale?.total_amount) }}</td>
                    <td class="px-4 py-3 text-right font-bold text-green-600">৳{{ formatNumber(totalProfit) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Returns Section -->
          <div v-if="sale?.returns && sale.returns.length > 0" class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-gradient-to-r from-red-50 to-white">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 4V9a2 2 0 00-2-2h-1" />
                </svg>
                <h3 class="font-semibold text-slate-800">Return History</h3>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                  <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Product</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Quantity</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Refund Amount</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Payment Method</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Date</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="returnItem in sale.returns" :key="returnItem.id" class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-4 py-3">
                      <div class="font-medium text-slate-800">{{ returnItem.product?.name || 'Product' }}</div>
                    </td>
                    <td class="px-4 py-3 text-center font-medium text-slate-700">{{ returnItem.quantity }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-red-600">৳{{ formatNumber(returnItem.refund_amount) }}</td>
                    <td class="px-4 py-3 text-right capitalize text-slate-600">{{ returnItem.payment_method }}</td>
                    <td class="px-4 py-3 text-center">
                      <span :class="getReturnStatusClass(returnItem.status)" class="px-2 py-1 rounded-full text-xs font-medium capitalize">
                        {{ returnItem.status }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-center text-sm text-slate-500">{{ formatDate(returnItem.created_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black/40 backdrop-blur-md flex items-center justify-center z-50">
      <div class="bg-white p-8 rounded-2xl shadow-2xl text-center animate-slide-up">
        <div class="relative">
          <div class="w-16 h-16 mx-auto">
            <div class="absolute inset-0 rounded-full border-4 border-purple-200"></div>
            <div class="absolute inset-0 rounded-full border-4 border-purple-600 border-t-transparent animate-spin"></div>
          </div>
        </div>
        <p class="mt-4 text-lg font-semibold text-slate-800">Loading sale details...</p>
        <p class="text-sm text-slate-500 mt-1">Please wait</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from "@/api/axios";

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const sale = ref(null);

// Helper function to get product name from item
const getProductName = (item) => {
  if (item.product?.name) return item.product.name;
  if (item.product_name) return item.product_name;
  if (item.name) return item.name;
  return `Product #${item.product_id || item.id}`;
};

// Helper function to get product SKU from item
const getProductSku = (item) => {
  if (item.product?.sku) return item.product.sku;
  if (item.product?.code) return item.product.code;
  if (item.sku) return item.sku;
  return 'N/A';
};

const loadSaleDetails = async () => {
  const saleId = route.params.id;
  if (!saleId) {
    router.push('/sales');
    return;
  }
  
  loading.value = true;
  try {
    const response = await api.get(`/sales/${saleId}`);
    // Handle different response structures
    const saleData = response.data.data || response.data;
    sale.value = saleData;
    
    // Debug: Log the items to see the structure
    console.log('Sale items:', sale.value?.items);
  } catch (error) {
    console.error('Failed to load sale details:', error);
    alert('Could not load sale details');
    router.push('/sales');
  } finally {
    loading.value = false;
  }
};

const formatNumber = (value) => {
  if (value === null || value === undefined) return '0.00';
  return parseFloat(value).toFixed(2);
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-BD', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatPaymentStatus = (status) => {
  const statusMap = {
    paid: 'Paid',
    unpaid: 'Unpaid',
    partial: 'Partial',
    pending: 'Pending'
  };
  return statusMap[status] || status || 'Pending';
};

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getReturnStatusClass = (status) => {
  const classes = {
    approved: 'bg-green-100 text-green-700',
    pending: 'bg-yellow-100 text-yellow-700',
    rejected: 'bg-red-100 text-red-700'
  };
  return classes[status] || 'bg-slate-100 text-slate-600';
};

const paymentStatusClass = computed(() => {
  const status = sale.value?.payment_status;
  const classes = {
    paid: 'bg-green-100 text-green-700',
    unpaid: 'bg-red-100 text-red-700',
    partial: 'bg-yellow-100 text-yellow-700',
    pending: 'bg-orange-100 text-orange-700'
  };
  return classes[status] || 'bg-slate-100 text-slate-600';
});

const totalQuantity = computed(() => {
  if (!sale.value?.items) return 0;
  return sale.value.items.reduce((sum, item) => sum + (item.quantity || 0), 0);
});

const subtotal = computed(() => {
  if (!sale.value?.items) return 0;
  return sale.value.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
});

const totalProfit = computed(() => {
  if (!sale.value?.items) return 0;
  return sale.value.items.reduce((sum, item) => sum + (item.gross_profit || 0), 0);
});

const dueAmount = computed(() => {
  const total = parseFloat(sale.value?.total_amount || 0);
  const paid = parseFloat(sale.value?.paid_amount || 0);
  return Math.max(0, total - paid);
});

const downloadInvoice = () => {
  window.open(`/sales/${sale.value?.id}/receipt`, '_blank');
};

onMounted(() => {
  loadSaleDetails();
});
</script>

<style scoped>
@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.animate-slide-up {
  animation: slide-up 0.25s ease-out;
}
</style>