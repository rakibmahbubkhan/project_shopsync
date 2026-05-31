<template>
  <div class="expense-form">
    <div class="expense-form__bg">
      <div class="expense-form__orb expense-form__orb--1"></div>
      <div class="expense-form__orb expense-form__orb--2"></div>
    </div>

    <div class="expense-form__container">
      <!-- Header -->
      <div class="expense-form__header">
        <button @click="$router.push('/expenses')" class="expense-form__back-btn">
          <svg class="expense-form__back-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Back to Expenses
        </button>
        <h1 class="expense-form__title">{{ isEditMode ? 'Edit Expense' : 'Record New Expense' }}</h1>
      </div>

      <!-- Form Card -->
      <div class="expense-form__card">
        <form @submit.prevent="submitExpense" class="expense-form__form">
          <!-- Category -->
          <div class="expense-form__field">
            <label class="expense-form__label">
              <span class="expense-form__label-icon">🏷️</span>
              Expense Category
            </label>
            <select v-model="form.expense_category_id" required class="expense-form__select">
              <option value="" disabled>Select a category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>

          <!-- Title -->
          <div class="expense-form__field">
            <label class="expense-form__label">
              <span class="expense-form__label-icon">📝</span>
              Title / Description
            </label>
            <input 
              v-model="form.title" 
              type="text" 
              required 
              placeholder="e.g., Office Supplies, Internet Bill"
              class="expense-form__input"
            >
          </div>

          <!-- Amount & Date Row -->
          <div class="expense-form__row">
            <div class="expense-form__field expense-form__field--half">
              <label class="expense-form__label">
                <span class="expense-form__label-icon">💰</span>
                Amount
              </label>
              <div class="expense-form__currency-wrapper">
                <span class="expense-form__currency-symbol">$</span>
                <input 
                  v-model="form.amount" 
                  type="number" 
                  step="0.01" 
                  min="0" 
                  required 
                  placeholder="0.00"
                  class="expense-form__input expense-form__input--currency"
                >
              </div>
            </div>
            <div class="expense-form__field expense-form__field--half">
              <label class="expense-form__label">
                <span class="expense-form__label-icon">📅</span>
                Date
              </label>
              <input 
                v-model="form.expense_date" 
                type="date" 
                required 
                class="expense-form__input"
              >
            </div>
          </div>

          <!-- Notes -->
          <div class="expense-form__field">
            <label class="expense-form__label">
              <span class="expense-form__label-icon">📌</span>
              Notes (Optional)
            </label>
            <textarea 
              v-model="form.note" 
              rows="4"
              placeholder="Add any additional details..."
              class="expense-form__textarea"
            ></textarea>
          </div>

          <!-- Form Actions -->
          <div class="expense-form__actions">
            <button type="button" @click="$router.push('/expenses')" class="expense-form__btn expense-form__btn--secondary">
              Cancel
            </button>
            <button type="submit" class="expense-form__btn expense-form__btn--primary">
              <svg v-if="!isEditMode" class="expense-form__btn-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
              </svg>
              <svg v-else class="expense-form__btn-icon" viewBox="0 0 20 20" fill="currentColor">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
              </svg>
              {{ isEditMode ? 'Update Expense' : 'Save Expense' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Preview Card (optional summary) -->
      <div v-if="form.amount && form.title" class="expense-form__preview">
        <div class="expense-form__preview-header">
          <span>Expense Summary</span>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-width="1.5"/>
          </svg>
        </div>
        <div class="expense-form__preview-content">
          <div class="expense-form__preview-item">
            <span>Title:</span>
            <strong>{{ form.title || '—' }}</strong>
          </div>
          <div class="expense-form__preview-item">
            <span>Amount:</span>
            <strong class="expense-form__preview-amount">${{ parseFloat(form.amount || 0).toFixed(2) }}</strong>
          </div>
          <div class="expense-form__preview-item">
            <span>Date:</span>
            <strong>{{ form.expense_date || '—' }}</strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/api/axios';

const route = useRoute();
const router = useRouter();
const categories = ref([]);
const isEditMode = computed(() => !!route.params.id);

const form = ref({
  expense_category_id: '',
  title: '',
  amount: '',
  expense_date: new Date().toISOString().split('T')[0],
  note: ''
});

onMounted(async () => {
  const catRes = await api.get('/expense-categories');
  categories.value = catRes.data;

  if (isEditMode.value) {
    const expRes = await api.get(`/expenses/${route.params.id}`);
    form.value = {
      expense_category_id: expRes.data.expense_category_id,
      title: expRes.data.title,
      amount: expRes.data.amount,
      expense_date: expRes.data.expense_date,
      note: expRes.data.note || ''
    };
  }
});

const submitExpense = async () => {
  try {
    if (isEditMode.value) {
      await api.put(`/expenses/${route.params.id}`, form.value);
    } else {
      await api.post('/expenses', form.value);
    }
    router.push('/expenses');
  } catch (err) {
    alert('Failed to save expense. Please check your input.');
  }
};
</script>

<style scoped>
.expense-form {
  min-height: 100vh;
  position: relative;
  padding: 2rem;
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
}

.expense-form__bg {
  position: fixed;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
}

.expense-form__orb {
  position: absolute;
  width: 400px;
  height: 400px;
  border-radius: 50%;
  filter: blur(100px);
  opacity: 0.3;
  animation: float 20s infinite alternate;
}

.expense-form__orb--1 {
  background: radial-gradient(circle, rgba(59,130,246,0.4), transparent);
  top: -150px;
  right: -100px;
}

.expense-form__orb--2 {
  background: radial-gradient(circle, rgba(139,92,246,0.4), transparent);
  bottom: -150px;
  left: -100px;
}

@keyframes float {
  0% { transform: translate(0, 0) scale(1); }
  100% { transform: translate(30px, 30px) scale(1.1); }
}

.expense-form__container {
  max-width: 800px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.expense-form__header {
  margin-bottom: 2rem;
}

.expense-form__back-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255,255,255,0.05);
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 40px;
  color: rgba(255,255,255,0.7);
  font-size: 0.875rem;
  cursor: pointer;
  margin-bottom: 1rem;
  transition: all 0.2s;
}

.expense-form__back-btn:hover {
  background: rgba(255,255,255,0.1);
  color: white;
  transform: translateX(-4px);
}

.expense-form__back-icon {
  width: 18px;
  height: 18px;
}

.expense-form__title {
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #fff, #c084fc);
  background-clip: text;
  -webkit-background-clip: text;
  color: transparent;
  letter-spacing: -0.02em;
}

.expense-form__card {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 32px;
  padding: 2rem;
  margin-bottom: 1.5rem;
}

.expense-form__form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.expense-form__field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.expense-form__row {
  display: flex;
  gap: 1rem;
}

.expense-form__field--half {
  flex: 1;
}

.expense-form__label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: rgba(255,255,255,0.7);
}

.expense-form__label-icon {
  font-size: 1rem;
}

.expense-form__input,
.expense-form__select,
.expense-form__textarea {
  background: rgba(0,0,0,0.3);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px;
  padding: 0.75rem 1rem;
  color: white;
  font-size: 0.875rem;
  transition: all 0.2s;
  width: 100%;
}

.expense-form__input:focus,
.expense-form__select:focus,
.expense-form__textarea:focus {
  outline: none;
  border-color: #7c3aed;
  box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.3);
}

.expense-form__currency-wrapper {
  position: relative;
}

.expense-form__currency-symbol {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(255,255,255,0.5);
  font-weight: 600;
}

.expense-form__input--currency {
  padding-left: 2rem;
}

.expense-form__actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 0.5rem;
}

.expense-form__btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 40px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.expense-form__btn--secondary {
  background: rgba(255,255,255,0.1);
  color: white;
}

.expense-form__btn--secondary:hover {
  background: rgba(255,255,255,0.2);
}

.expense-form__btn--primary {
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: white;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.expense-form__btn--primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
}

.expense-form__btn-icon {
  width: 18px;
  height: 18px;
}

.expense-form__preview {
  background: rgba(30, 41, 59, 0.5);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 20px;
  padding: 1rem;
}

.expense-form__preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  color: rgba(255,255,255,0.5);
  padding-bottom: 0.75rem;
  border-bottom: 1px dashed rgba(255,255,255,0.1);
  margin-bottom: 0.75rem;
}

.expense-form__preview-content {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.expense-form__preview-item {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
  color: rgba(255,255,255,0.7);
}

.expense-form__preview-amount {
  color: #34d399;
  font-weight: 700;
}
</style>