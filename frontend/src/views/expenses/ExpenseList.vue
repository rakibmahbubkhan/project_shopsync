<template>
  <div class="expense-list">
    <!-- Animated Background -->
    <div class="expense-list__bg">
      <div class="expense-list__orb expense-list__orb--1"></div>
      <div class="expense-list__orb expense-list__orb--2"></div>
      <div class="expense-list__orb expense-list__orb--3"></div>
    </div>

    <div class="expense-list__container">
      <!-- Header Section -->
      <div class="expense-list__header">
        <div>
          <h1 class="expense-list__title">Expense Tracker</h1>
          <p class="expense-list__subtitle">Monitor and analyze your business expenditures</p>
        </div>
        <button @click="$router.push('/expenses/create')" class="expense-list__btn-primary">
          <svg class="expense-list__btn-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          New Expense
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="expense-list__stats">
        <div class="expense-list__stat-card">
          <div class="expense-list__stat-icon">📊</div>
          <div>
            <p class="expense-list__stat-label">Total Expenses</p>
            <p class="expense-list__stat-value">{{ totalExpensesCount }}</p>
          </div>
        </div>
        <div class="expense-list__stat-card">
          <div class="expense-list__stat-icon">💰</div>
          <div>
            <p class="expense-list__stat-label">Total Amount</p>
            <p class="expense-list__stat-value">${{ totalExpensesAmount.toFixed(2) }}</p>
          </div>
        </div>
        <div class="expense-list__stat-card">
          <div class="expense-list__stat-icon">📁</div>
          <div>
            <p class="expense-list__stat-label">Categories</p>
            <p class="expense-list__stat-value">{{ categories.length }}</p>
          </div>
        </div>
        <div class="expense-list__stat-card">
          <div class="expense-list__stat-icon">🎯</div>
          <div>
            <p class="expense-list__stat-label">Filtered</p>
            <p class="expense-list__stat-value">{{ filteredExpenses.length }}</p>
          </div>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="expense-list__filter-bar">
        <div class="expense-list__filter-group">
          <label class="expense-list__filter-label">Category</label>
          <select v-model="selectedCategory" class="expense-list__filter-select">
            <option value="ALL">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div class="expense-list__filter-group">
          <label class="expense-list__filter-label">Date Range</label>
          <div class="expense-list__date-range">
            <input type="date" v-model="dateFrom" class="expense-list__date-input" placeholder="From">
            <span>→</span>
            <input type="date" v-model="dateTo" class="expense-list__date-input" placeholder="To">
          </div>
        </div>
        <button v-if="dateFrom || dateTo || selectedCategory !== 'ALL'" @click="clearFilters" class="expense-list__clear-btn">
          Clear Filters
        </button>
      </div>

      <!-- Expenses Table Card -->
      <div class="expense-list__table-card">
        <div class="expense-list__table-wrapper">
          <table class="expense-list__table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Title</th>
                <th>Category</th>
                <th>Notes</th>
                <th>Amount</th>
                <th class="expense-list__th-actions">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="expense in paginatedExpenses" :key="expense.id" class="expense-list__table-row">
                <td class="expense-list__cell-date">{{ formatDate(expense.expense_date) }}</td>
                <td class="expense-list__cell-title">{{ expense.title }}</td>
                <td>
                  <span class="expense-list__category-badge">
                    {{ expense.category?.name || 'Uncategorized' }}
                  </span>
                </td>
                <td class="expense-list__cell-notes">{{ expense.note || '—' }}</td>
                <td class="expense-list__cell-amount">${{ parseFloat(expense.amount).toFixed(2) }}</td>
                <td class="expense-list__cell-actions">
                  <button @click="$router.push(`/expenses/${expense.id}/edit`)" class="expense-list__action-btn expense-list__action-btn--edit" title="Edit">
                    ✏️
                  </button>
                  <button @click="deleteExpense(expense.id)" class="expense-list__action-btn expense-list__action-btn--delete" title="Delete">
                    🗑️
                  </button>
                </td>
              </tr>
              <tr v-if="filteredExpenses.length === 0">
                <td colspan="6" class="expense-list__empty-state">
                  <div class="expense-list__empty-content">
                    <span class="expense-list__empty-icon">📭</span>
                    <p>No expenses found</p>
                    <button @click="$router.push('/expenses/create')" class="expense-list__empty-btn">Create your first expense</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="expense-list__pagination">
          <button @click="currentPage--" :disabled="currentPage === 1" class="expense-list__page-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M15 18l-6-6 6-6" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
          <span class="expense-list__page-info">{{ currentPage }} / {{ totalPages }}</span>
          <button @click="currentPage++" :disabled="currentPage === totalPages" class="expense-list__page-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M9 18l6-6-6-6" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/api/axios';

const router = useRouter();
const expenses = ref([]);
const categories = ref([]);
const selectedCategory = ref('ALL');
const dateFrom = ref('');
const dateTo = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;

const fetchData = async () => {
  const [expRes, catRes] = await Promise.all([
    api.get('/expenses'),
    api.get('/expense-categories')
  ]);
  expenses.value = expRes.data;
  categories.value = catRes.data;
};

const filteredExpenses = computed(() => {
  let filtered = expenses.value;
  
  if (selectedCategory.value !== 'ALL') {
    filtered = filtered.filter(e => e.expense_category_id === selectedCategory.value);
  }
  
  if (dateFrom.value) {
    filtered = filtered.filter(e => e.expense_date >= dateFrom.value);
  }
  
  if (dateTo.value) {
    filtered = filtered.filter(e => e.expense_date <= dateTo.value);
  }
  
  return filtered.sort((a, b) => new Date(b.expense_date) - new Date(a.expense_date));
});

const totalExpensesCount = computed(() => expenses.value.length);
const totalExpensesAmount = computed(() => {
  return expenses.value.reduce((sum, e) => sum + parseFloat(e.amount || 0), 0);
});

const totalPages = computed(() => Math.ceil(filteredExpenses.value.length / itemsPerPage));
const paginatedExpenses = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredExpenses.value.slice(start, start + itemsPerPage);
});

const clearFilters = () => {
  selectedCategory.value = 'ALL';
  dateFrom.value = '';
  dateTo.value = '';
  currentPage.value = 1;
};

const formatDate = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const deleteExpense = async (id) => {
  if (confirm('Are you sure you want to delete this expense?')) {
    await api.delete(`/expenses/${id}`);
    fetchData();
  }
};

onMounted(fetchData);
</script>

<style scoped>
.expense-list {
  min-height: 100vh;
  position: relative;
  padding: 2rem;
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
}

.expense-list__bg {
  position: fixed;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
}

.expense-list__orb {
  position: absolute;
  width: 450px;
  height: 450px;
  border-radius: 50%;
  filter: blur(120px);
  opacity: 0.25;
  animation: float 25s infinite alternate;
}

.expense-list__orb--1 {
  background: radial-gradient(circle, #3b82f6, transparent);
  top: -150px;
  right: -100px;
}

.expense-list__orb--2 {
  background: radial-gradient(circle, #8b5cf6, transparent);
  bottom: -150px;
  left: -100px;
  animation-delay: -8s;
}

.expense-list__orb--3 {
  background: radial-gradient(circle, #ec4899, transparent);
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 600px;
  height: 600px;
  animation: pulseGlow 15s infinite alternate;
}

@keyframes float {
  0% { transform: translate(0, 0) scale(1); }
  100% { transform: translate(40px, 40px) scale(1.15); }
}

@keyframes pulseGlow {
  0% { opacity: 0.15; transform: translate(-50%, -50%) scale(0.9); }
  100% { opacity: 0.3; transform: translate(-50%, -50%) scale(1.1); }
}

.expense-list__container {
  max-width: 1400px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.expense-list__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding: 1rem 1.5rem;
  background: rgba(15, 23, 42, 0.5);
  backdrop-filter: blur(12px);
  border-radius: 28px;
  border: 1px solid rgba(255,255,255,0.1);
}

.expense-list__title {
  font-size: 1.75rem;
  font-weight: 700;
  background: linear-gradient(135deg, #fff, #a78bfa);
  background-clip: text;
  -webkit-background-clip: text;
  color: transparent;
}

.expense-list__subtitle {
  font-size: 0.875rem;
  color: rgba(255,255,255,0.5);
  margin-top: 0.25rem;
}

.expense-list__btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  border: none;
  border-radius: 40px;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.expense-list__btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
}

.expense-list__btn-icon {
  width: 18px;
  height: 18px;
}

.expense-list__stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.expense-list__stat-card {
  background: rgba(30, 41, 59, 0.5);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 20px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s;
}

.expense-list__stat-card:hover {
  transform: translateY(-2px);
  background: rgba(30, 41, 59, 0.7);
}

.expense-list__stat-icon {
  font-size: 2rem;
}

.expense-list__stat-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: rgba(255,255,255,0.5);
}

.expense-list__stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  line-height: 1.2;
}

.expense-list__filter-bar {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 20px;
  padding: 1rem;
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.expense-list__filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.expense-list__filter-label {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  color: rgba(255,255,255,0.5);
}

.expense-list__filter-select,
.expense-list__date-input {
  background: rgba(0,0,0,0.3);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 12px;
  padding: 0.5rem 1rem;
  color: white;
  font-size: 0.875rem;
}

.expense-list__date-range {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.expense-list__clear-btn {
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-radius: 40px;
  padding: 0.5rem 1rem;
  color: #f87171;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
}

.expense-list__clear-btn:hover {
  background: rgba(239, 68, 68, 0.4);
}

.expense-list__table-card {
  background: rgba(15, 23, 42, 0.5);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 24px;
  overflow: hidden;
}

.expense-list__table-wrapper {
  overflow-x: auto;
}

.expense-list__table {
  width: 100%;
  border-collapse: collapse;
}

.expense-list__table th {
  text-align: left;
  padding: 1rem;
  background: rgba(0,0,0,0.3);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: rgba(255,255,255,0.6);
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.expense-list__table-row {
  border-bottom: 1px solid rgba(255,255,255,0.05);
  transition: background 0.2s;
}

.expense-list__table-row:hover {
  background: rgba(255,255,255,0.03);
}

.expense-list__table td {
  padding: 1rem;
  font-size: 0.875rem;
  color: rgba(255,255,255,0.8);
}

.expense-list__cell-date {
  white-space: nowrap;
  font-family: monospace;
}

.expense-list__cell-title {
  font-weight: 500;
}

.expense-list__cell-notes {
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.expense-list__cell-amount {
  font-weight: 700;
  color: #34d399;
}

.expense-list__category-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background: rgba(139, 92, 246, 0.15);
  border: 1px solid rgba(139, 92, 246, 0.3);
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 500;
  color: #c4b5fd;
}

.expense-list__cell-actions {
  display: flex;
  gap: 0.5rem;
}

.expense-list__action-btn {
  background: none;
  border: none;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 8px;
  transition: all 0.2s;
}

.expense-list__action-btn--edit:hover {
  background: rgba(59, 130, 246, 0.2);
  transform: scale(1.1);
}

.expense-list__action-btn--delete:hover {
  background: rgba(239, 68, 68, 0.2);
  transform: scale(1.1);
}

.expense-list__empty-state {
  text-align: center;
  padding: 3rem;
}

.expense-list__empty-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.expense-list__empty-icon {
  font-size: 4rem;
  opacity: 0.5;
}

.expense-list__empty-btn {
  background: rgba(255,255,255,0.1);
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 40px;
  color: white;
  cursor: pointer;
}

.expense-list__pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-top: 1px solid rgba(255,255,255,0.1);
}

.expense-list__page-btn {
  background: rgba(255,255,255,0.1);
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  color: white;
}

.expense-list__page-btn:hover:not(:disabled) {
  background: rgba(255,255,255,0.2);
}

.expense-list__page-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.expense-list__page-info {
  font-size: 0.875rem;
  color: rgba(255,255,255,0.7);
}
</style>