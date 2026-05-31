<template>
  <div class="expense-categories">
    <!-- Animated Background -->
    <div class="expense-categories__bg">
      <div class="expense-categories__orb expense-categories__orb--1"></div>
      <div class="expense-categories__orb expense-categories__orb--2"></div>
    </div>

    <!-- Main Content -->
    <div class="expense-categories__container">
      <!-- Header Card -->
      <div class="expense-categories__header">
        <div>
          <h1 class="expense-categories__title">Expense Categories</h1>
          <p class="expense-categories__subtitle">Manage your expense classification system</p>
        </div>
        <button @click="openModal()" class="expense-categories__btn-primary">
          <svg class="expense-categories__btn-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Add Category
        </button>
      </div>

      <!-- Categories Grid -->
      <div class="expense-categories__grid">
        <div 
          v-for="category in categories" 
          :key="category.id" 
          class="expense-categories__card"
        >
          <div class="expense-categories__card-header">
            <div class="expense-categories__card-icon">
              <span>📂</span>
            </div>
            <div class="expense-categories__card-actions">
              <button @click="openModal(category)" class="expense-categories__action-btn expense-categories__action-btn--edit">
                ✏️
              </button>
              <button @click="deleteCategory(category.id)" class="expense-categories__action-btn expense-categories__action-btn--delete">
                🗑️
              </button>
            </div>
          </div>
          <h3 class="expense-categories__card-title">{{ category.name }}</h3>
          <p class="expense-categories__card-desc">{{ category.description || 'No description provided' }}</p>
          <div class="expense-categories__card-footer">
            <span class="expense-categories__card-badge">Active</span>
          </div>
        </div>

        <div v-if="categories.length === 0" class="expense-categories__empty">
          <div class="expense-categories__empty-icon">📭</div>
          <p class="expense-categories__empty-text">No expense categories yet</p>
          <button @click="openModal()" class="expense-categories__empty-btn">Create your first category</button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="expense-categories__modal-overlay" @click.self="showModal = false">
          <div class="expense-categories__modal">
            <div class="expense-categories__modal-header">
              <h3 class="expense-categories__modal-title">{{ isEditing ? 'Edit Category' : 'Create New Category' }}</h3>
              <button @click="showModal = false" class="expense-categories__modal-close">&times;</button>
            </div>
            <form @submit.prevent="saveCategory" class="expense-categories__form">
              <div class="expense-categories__form-group">
                <label class="expense-categories__form-label">Category Name</label>
                <input 
                  v-model="form.name" 
                  type="text" 
                  required 
                  placeholder="e.g., Utilities, Travel, Supplies"
                  class="expense-categories__form-input"
                >
              </div>
              <div class="expense-categories__form-group">
                <label class="expense-categories__form-label">Description (Optional)</label>
                <textarea 
                  v-model="form.description" 
                  rows="3"
                  placeholder="Add a brief description..."
                  class="expense-categories__form-textarea"
                ></textarea>
              </div>
              <div class="expense-categories__modal-actions">
                <button type="button" @click="showModal = false" class="expense-categories__btn-secondary">Cancel</button>
                <button type="submit" class="expense-categories__btn-primary expense-categories__btn-primary--small">
                  {{ isEditing ? 'Update' : 'Create' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api/axios';

const categories = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const currentId = ref(null);
const form = ref({ name: '', description: '' });

const fetchCategories = async () => {
  const res = await api.get('/expense-categories');
  categories.value = res.data;
};

const openModal = (category = null) => {
  if (category) {
    isEditing.value = true;
    currentId.value = category.id;
    form.value = { name: category.name, description: category.description || '' };
  } else {
    isEditing.value = false;
    currentId.value = null;
    form.value = { name: '', description: '' };
  }
  showModal.value = true;
};

const saveCategory = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/expense-categories/${currentId.value}`, form.value);
    } else {
      await api.post('/expense-categories', form.value);
    }
    showModal.value = false;
    fetchCategories();
  } catch (err) {
    alert(err.response?.data?.message || 'Validation error');
  }
};

const deleteCategory = async (id) => {
  if (confirm('Are you sure you want to delete this category? This may affect existing expenses.')) {
    await api.delete(`/expense-categories/${id}`);
    fetchCategories();
  }
};

onMounted(fetchCategories);
</script>

<style scoped>
.expense-categories {
  min-height: 100vh;
  position: relative;
  padding: 2rem;
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
}

.expense-categories__bg {
  position: fixed;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
}

.expense-categories__orb {
  position: absolute;
  width: 400px;
  height: 400px;
  border-radius: 50%;
  filter: blur(100px);
  opacity: 0.3;
  animation: float 20s infinite alternate;
}

.expense-categories__orb--1 {
  background: radial-gradient(circle, rgba(59,130,246,0.4), transparent);
  top: -150px;
  right: -100px;
}

.expense-categories__orb--2 {
  background: radial-gradient(circle, rgba(139,92,246,0.4), transparent);
  bottom: -150px;
  left: -100px;
  animation-delay: -5s;
}

@keyframes float {
  0% { transform: translate(0, 0) scale(1); }
  100% { transform: translate(30px, 30px) scale(1.1); }
}

.expense-categories__container {
  max-width: 1200px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.expense-categories__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(12px);
  border-radius: 24px;
  border: 1px solid rgba(255,255,255,0.1);
}

.expense-categories__title {
  font-size: 1.75rem;
  font-weight: 700;
  background: linear-gradient(135deg, #fff, #a78bfa);
  background-clip: text;
  -webkit-background-clip: text;
  color: transparent;
  letter-spacing: -0.02em;
}

.expense-categories__subtitle {
  font-size: 0.875rem;
  color: rgba(255,255,255,0.5);
  margin-top: 0.25rem;
}

.expense-categories__btn-primary {
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
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.expense-categories__btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
}

.expense-categories__btn-icon {
  width: 18px;
  height: 18px;
}

.expense-categories__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.expense-categories__card {
  background: rgba(30, 41, 59, 0.5);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 20px;
  padding: 1.25rem;
  transition: all 0.3s ease;
  cursor: pointer;
}

.expense-categories__card:hover {
  transform: translateY(-4px);
  background: rgba(30, 41, 59, 0.7);
  border-color: rgba(139, 92, 246, 0.4);
  box-shadow: 0 20px 30px -12px rgba(0,0,0,0.3);
}

.expense-categories__card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.expense-categories__card-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #2563eb, #7c3aed);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  box-shadow: 0 8px 16px -6px rgba(37, 99, 235, 0.3);
}

.expense-categories__card-actions {
  display: flex;
  gap: 0.5rem;
}

.expense-categories__action-btn {
  background: rgba(255,255,255,0.05);
  border: none;
  border-radius: 10px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 1rem;
}

.expense-categories__action-btn--edit:hover {
  background: rgba(59, 130, 246, 0.3);
  transform: scale(1.05);
}

.expense-categories__action-btn--delete:hover {
  background: rgba(239, 68, 68, 0.3);
  transform: scale(1.05);
}

.expense-categories__card-title {
  font-size: 1.2rem;
  font-weight: 600;
  color: white;
  margin-bottom: 0.5rem;
}

.expense-categories__card-desc {
  font-size: 0.875rem;
  color: rgba(255,255,255,0.6);
  line-height: 1.4;
  margin-bottom: 1rem;
}

.expense-categories__card-footer {
  display: flex;
  justify-content: flex-end;
}

.expense-categories__card-badge {
  font-size: 0.7rem;
  padding: 0.25rem 0.75rem;
  background: rgba(34, 197, 94, 0.2);
  border: 1px solid rgba(34, 197, 94, 0.3);
  border-radius: 20px;
  color: #4ade80;
}

.expense-categories__empty {
  grid-column: 1 / -1;
  text-align: center;
  padding: 4rem;
  background: rgba(30, 41, 59, 0.4);
  backdrop-filter: blur(8px);
  border-radius: 32px;
  border: 1px dashed rgba(255,255,255,0.2);
}

.expense-categories__empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.expense-categories__empty-text {
  color: rgba(255,255,255,0.6);
  margin-bottom: 1.5rem;
}

.expense-categories__empty-btn {
  background: rgba(255,255,255,0.1);
  border: none;
  padding: 0.5rem 1.5rem;
  border-radius: 40px;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
}

.expense-categories__empty-btn:hover {
  background: rgba(255,255,255,0.2);
}

/* Modal Styles */
.expense-categories__modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.expense-categories__modal {
  background: rgba(15, 23, 42, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 32px;
  width: 90%;
  max-width: 500px;
  padding: 1.5rem;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
}

.expense-categories__modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.expense-categories__modal-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: white;
}

.expense-categories__modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: rgba(255,255,255,0.6);
  cursor: pointer;
  line-height: 1;
}

.expense-categories__form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.expense-categories__form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.expense-categories__form-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: rgba(255,255,255,0.6);
}

.expense-categories__form-input,
.expense-categories__form-textarea {
  background: rgba(0,0,0,0.3);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px;
  padding: 0.75rem 1rem;
  color: white;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.expense-categories__form-input:focus,
.expense-categories__form-textarea:focus {
  outline: none;
  border-color: #7c3aed;
  box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.3);
}

.expense-categories__modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1rem;
}

.expense-categories__btn-secondary {
  background: rgba(255,255,255,0.1);
  border: none;
  padding: 0.5rem 1.25rem;
  border-radius: 40px;
  color: white;
  cursor: pointer;
}

.expense-categories__btn-primary--small {
  padding: 0.5rem 1.25rem;
}

/* Modal Transition */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>