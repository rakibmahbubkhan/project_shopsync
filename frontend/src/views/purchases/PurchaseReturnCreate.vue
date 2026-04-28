<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/30">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8 max-w-5xl">
      
      <div class="mb-4">
        <button @click="goBack" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-700 transition-colors group">
          <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Purchase Returns
        </button>
      </div>

      <div v-if="loadingPurchase" class="bg-white rounded-2xl p-12 text-center shadow-sm">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-amber-200 border-t-amber-600 mx-auto"></div>
        <p class="mt-4 text-slate-500">Loading purchase details...</p>
      </div>

      <div v-else-if="loadError" class="bg-white rounded-2xl p-12 text-center shadow-sm">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-slate-800 mb-2">Failed to Load Purchase</h3>
        <p class="text-slate-500 mb-4">{{ loadError }}</p>
        <div class="flex gap-3 justify-center">
          <button @click="loadPurchase" class="px-6 py-2.5 bg-amber-600 text-white rounded-xl font-medium hover:bg-amber-700">Try Again</button>
          <button @click="goBack" class="px-6 py-2.5 border-2 border-slate-200 text-slate-600 rounded-xl font-medium hover:bg-slate-50">Go Back</button>
        </div>
      </div>

      <template v-if="purchase && !loadingPurchase && !loadError">
        
        <!-- Purchase Header Card -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/50 p-6 mb-6">
          <div class="flex flex-col lg:flex-row lg:items-center gap-6">
            <div class="flex items-center gap-4 flex-1">
              <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg shadow-amber-200 flex-shrink-0">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl font-bold text-slate-800">Return to Supplier</h1>
                <p class="text-sm text-slate-500">{{ purchase.reference_no || 'PO #'+purchase.id }} • {{ formatDate(purchase.purchase_date) }}</p>
              </div>
            </div>
            <div class="flex flex-wrap gap-3">
              <div class="flex items-center gap-2 px-4 py-2 bg-slate-100 rounded-xl">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-sm font-medium text-slate-700">{{ purchase.supplier?.name || 'N/A' }}</span>
              </div>
              <div class="flex items-center gap-2 px-4 py-2 bg-amber-50 rounded-xl">
                <span class="text-sm font-semibold text-amber-700">Total: ৳{{ formatNumber(purchase.total_amount) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Items Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
          <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
              <h2 class="text-lg font-bold text-slate-800">Return Items</h2>
              <p class="text-sm text-slate-500 mt-0.5">Select products to return to supplier</p>
            </div>
            <div class="flex gap-2">
              <button @click="selectAllAvailable" class="text-xs px-3 py-1.5 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition-colors font-medium">Select All</button>
              <button @click="clearAll" v-if="totalSelectedItems > 0" class="text-xs px-3 py-1.5 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition-colors font-medium">Clear All</button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                  <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase">Product</th>
                  <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase">Purchased</th>
                  <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase">Returned</th>
                  <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase">Available</th>
                  <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase w-48">Return Qty</th>
                  <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase">Unit Price</th>
                  <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase">Credit</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="item in purchase.items" :key="item.id" :class="[returnQuantities[item.id] > 0 ? 'bg-amber-50/40' : 'hover:bg-slate-50/50', item.available_for_return <= 0 ? 'opacity-50' : '']">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                      </div>
                      <div>
                        <p class="font-medium text-slate-800">{{ item.product_name }}</p>
                        <p class="text-xs text-slate-400">SKU: {{ item.product_sku || 'N/A' }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center"><span class="text-sm font-medium text-slate-600">{{ item.quantity }}</span></td>
                  <td class="px-6 py-4 text-center"><span class="text-sm text-slate-500">{{ item.already_returned || 0 }}</span></td>
                  <td class="px-6 py-4 text-center"><span class="text-sm font-semibold" :class="item.available_for_return > 0 ? 'text-green-600' : 'text-red-400'">{{ item.available_for_return }}</span></td>
                  <td class="px-6 py-4">
                    <div v-if="item.available_for_return > 0" class="flex items-center justify-center gap-2">
                      <button @click="decrementQty(item)" :disabled="!returnQuantities[item.id]" class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed">−</button>
                      <input type="number" v-model.number="returnQuantities[item.id]" @input="validateQty(item)" :max="item.available_for_return" min="0" class="w-20 px-3 py-2 border-2 rounded-lg text-center font-medium text-sm focus:outline-none" :class="returnQuantities[item.id] > 0 ? 'border-amber-300 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-600'">
                      <button @click="incrementQty(item)" :disabled="returnQuantities[item.id] >= item.available_for_return" class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed">+</button>
                    </div>
                    <div v-else class="text-center"><span class="text-xs text-slate-400">—</span></div>
                    <div v-if="item.available_for_return > 0" class="flex justify-center gap-1 mt-1.5">
                      <button v-for="qty in [1, 2, 5]" :key="qty" v-show="qty <= item.available_for_return" @click="returnQuantities[item.id] = qty" class="text-[10px] px-1.5 py-0.5 rounded-md" :class="returnQuantities[item.id] === qty ? 'bg-amber-100 text-amber-700 font-semibold' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'">{{ qty }}</button>
                      <button @click="returnQuantities[item.id] = item.available_for_return" class="text-[10px] px-1.5 py-0.5 rounded-md" :class="returnQuantities[item.id] === item.available_for_return ? 'bg-amber-100 text-amber-700 font-semibold' : 'bg-amber-50 text-amber-500 hover:bg-amber-100'">All</button>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right"><span class="text-sm font-medium text-slate-700">৳{{ formatNumber(item.purchase_price) }}</span></td>
                  <td class="px-6 py-4 text-right"><span class="text-sm font-semibold" :class="returnQuantities[item.id] > 0 ? 'text-amber-600' : 'text-slate-300'">৳{{ formatNumber((returnQuantities[item.id] || 0) * item.purchase_price) }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="bg-slate-50 border-t-2 border-slate-200 px-6 py-4">
            <div class="flex flex-col sm:flex-row justify-between items-end gap-2">
              <div class="text-sm text-slate-500">{{ totalSelectedItems > 0 ? `${totalSelectedItems} item(s) selected` : 'No items selected' }}</div>
              <div class="text-right">
                <p class="text-sm text-slate-500">Total Supplier Credit</p>
                <p class="text-2xl font-bold text-amber-600">৳{{ formatNumber(totalCreditAmount) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Return Details Form -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
          <h2 class="text-lg font-bold text-slate-800 mb-5">Return Details</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-slate-700 mb-2">Reason for Return <span class="text-red-500">*</span></label>
              <textarea v-model="form.reason" rows="3" placeholder="Reason for returning to supplier..." class="w-full px-4 py-3 border-2 rounded-xl focus:outline-none transition-all resize-none" :class="formErrors.reason ? 'border-red-400' : 'border-slate-200 focus:border-amber-400 focus:ring-4 focus:ring-amber-50'"></textarea>
              <p v-if="formErrors.reason" class="text-xs text-red-500 mt-1">{{ formErrors.reason }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-slate-700 mb-2">Additional Notes</label>
              <textarea v-model="form.notes" rows="2" placeholder="Any additional information..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-slate-400 focus:ring-4 focus:ring-slate-50 outline-none transition-all resize-none"></textarea>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
          <button @click="goBack" class="px-6 py-3 border-2 border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition-all font-medium text-center">Cancel</button>
          <button @click="processReturn" :disabled="!canSubmit || submitting" class="flex items-center justify-center gap-2 px-8 py-3.5 bg-gradient-to-r from-amber-600 to-orange-600 text-white rounded-xl font-bold shadow-lg shadow-amber-200 transition-all active:scale-95 disabled:from-slate-300 disabled:to-slate-400 disabled:shadow-none disabled:cursor-not-allowed">
            <svg v-if="submitting" class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            {{ submitting ? 'Processing...' : `Return to Supplier • Credit ৳${formatNumber(totalCreditAmount)}` }}
          </button>
        </div>
      </template>

    </div>

    <!-- Success Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="goToList">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
          <div class="relative bg-white rounded-2xl p-8 shadow-2xl text-center max-w-md w-full">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Return Processed!</h3>
            <p class="text-sm text-slate-400 mb-6">Stock decreased and supplier credit of <span class="font-semibold text-amber-600">৳{{ formatNumber(completedReturnAmount) }}</span> created.</p>
            <div class="flex gap-3 justify-center">
              <button @click="goToList" class="px-6 py-2.5 border-2 border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50">Back to Returns</button>
              <button @click="startNewReturn" class="px-6 py-2.5 bg-gradient-to-r from-amber-600 to-orange-600 text-white rounded-xl font-medium hover:shadow-lg">New Return</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Error Toast -->
    <Transition name="slide">
      <div v-if="errorMessage" class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 max-w-md">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        <div class="flex-1"><p class="font-semibold text-sm">Error</p><p class="text-sm text-red-100">{{ errorMessage }}</p></div>
        <button @click="errorMessage = ''" class="ml-auto text-red-200 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/api/axios';

const route = useRoute();
const router = useRouter();

// State
const purchase = ref(null);
const loadingPurchase = ref(true);
const loadError = ref('');
const submitting = ref(false);
const showSuccessModal = ref(false);
const completedReturnAmount = ref(0);
const errorMessage = ref('');

const returnQuantities = ref({});
const form = reactive({ reason: '', notes: '' });
const formErrors = reactive({ reason: '' });

// Computed
const totalSelectedItems = computed(() => Object.values(returnQuantities.value).filter(q => q > 0).length);
const totalCreditAmount = computed(() => {
  if (!purchase.value?.items) return 0;
  return purchase.value.items.reduce((sum, item) => sum + ((returnQuantities.value[item.id] || 0) * item.purchase_price), 0);
});
const canSubmit = computed(() => totalSelectedItems.value > 0 && form.reason.trim().length > 0 && !submitting.value);

// Helpers
const formatNumber = (v) => parseFloat(v || 0).toFixed(2);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-BD', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';

const goBack = () => router.push('/purchases/returns');
const goToList = () => router.push('/purchases/returns');

const startNewReturn = () => {
  showSuccessModal.value = false;
  purchase.value = null;
  returnQuantities.value = {};
  form.reason = '';
  form.notes = '';
  loadingPurchase.value = true;
  loadError.value = '';
  loadPurchase();
};

const incrementQty = (item) => {
  const cur = returnQuantities.value[item.id] || 0;
  if (cur < item.available_for_return) returnQuantities.value[item.id] = cur + 1;
};
const decrementQty = (item) => {
  const cur = returnQuantities.value[item.id] || 0;
  if (cur > 0) returnQuantities.value[item.id] = cur - 1;
};
const validateQty = (item) => {
  const qty = returnQuantities.value[item.id];
  if (!qty || qty < 0) returnQuantities.value[item.id] = 0;
  else if (qty > item.available_for_return) returnQuantities.value[item.id] = item.available_for_return;
};

const selectAllAvailable = () => {
  purchase.value?.items.forEach(item => {
    if (item.available_for_return > 0) returnQuantities.value[item.id] = item.available_for_return;
  });
};
const clearAll = () => {
  purchase.value?.items.forEach(item => { returnQuantities.value[item.id] = 0; });
};

// API
const loadPurchase = async () => {
  const purchaseId = route.query.purchase_id;
  if (!purchaseId) { loadError.value = 'No purchase ID provided.'; loadingPurchase.value = false; return; }
  loadingPurchase.value = true;
  try {
    const { data } = await api.get('/purchase-returns/search-purchases', { params: { search: purchaseId.toString() } });
    const found = (Array.isArray(data) ? data : (data.data || [])).find(p => p.id == purchaseId);
    if (found) {
      purchase.value = found;
    } else {
      const res = await api.get(`/purchases/${purchaseId}`);
      const pd = res.data.data || res.data;
      purchase.value = { ...pd, items: (pd.items || []).map(i => ({ ...i, product_name: i.product?.name || 'Product', already_returned: 0, available_for_return: i.quantity })) };
    }
    purchase.value.items.forEach(i => { returnQuantities.value[i.id] = 0; });
  } catch (e) { loadError.value = e.response?.data?.message || 'Failed to load purchase.'; }
  finally { loadingPurchase.value = false; }
};

const processReturn = () => {
  if (!form.reason.trim()) { formErrors.reason = 'Please provide a reason'; return; }
  confirmProcessReturn();
};

const confirmProcessReturn = async () => {
  submitting.value = true;
  errorMessage.value = '';
  const items = purchase.value.items.filter(i => (returnQuantities.value[i.id] || 0) > 0).map(i => ({ product_id: i.product_id, quantity: returnQuantities.value[i.id] }));
  try {
    const { data } = await api.post('/purchase-returns', {
      purchase_id: purchase.value.id,
      items,
      reason: form.reason,
      notes: form.notes || undefined
    });
    completedReturnAmount.value = totalCreditAmount.value;
    showSuccessModal.value = true;
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Failed to process return';
  } finally { submitting.value = false; }
};

onMounted(() => loadPurchase());
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active > div:last-child, .modal-leave-active > div:last-child { transition: transform 0.25s ease, opacity 0.25s ease; }
.modal-enter-from > div:last-child { transform: scale(0.95) translateY(10px); opacity: 0; }
.slide-enter-active, .slide-leave-active { transition: all 0.3s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(100%); opacity: 0; }
input[type="number"]::-webkit-inner-spin-button, input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
</style>