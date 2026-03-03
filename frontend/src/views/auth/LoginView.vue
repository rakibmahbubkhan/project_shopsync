<template>
  <div class="bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">
    <div class="hidden md:flex md:w-1/2 bg-primary p-12 text-white flex-col justify-center">
      <h1 class="text-4xl font-bold">ShopSync</h1>
      <p class="mt-4 text-blue-100 text-lg">Inventory & Financial Management System</p>
    </div>

    <div class="w-full md:w-1/2 p-8 sm:p-12">
      <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Welcome Back</h2>
        <p class="text-gray-500">Sign in to manage your workshop</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-5">
        <div>
          <label class="block text-sm font-semibold text-gray-600 mb-1">Email</label>
          <input 
            v-model="form.email" 
            type="email" 
            placeholder="admin@example.com"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-600 mb-1">Password</label>
          <input 
            v-model="form.password" 
            type="password" 
            placeholder="••••••••"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all"
            required
          />
        </div>

        <button 
          type="submit" 
          :disabled="loading"
          class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-3 rounded-xl shadow-lg transition-all transform active:scale-95 disabled:opacity-50"
        >
          <span v-if="loading">Verifying...</span>
          <span v-else>Login</span>
        </button>

        <p v-if="error" class="text-danger text-sm text-center font-medium mt-2">
          {{ error }}
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore'; //
import api from '@/api/axios'; //

const router = useRouter();
const auth = useAuthStore();
const loading = ref(false);
const error = ref('');

const form = reactive({
  email: '',
  password: ''
});

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  try {
    const response = await api.post('/login', form);
    auth.token = response.data.token;
    auth.user = response.data.user;
    router.push({ name: 'dashboard' }); //
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed. Check your credentials.';
  } finally {
    loading.value = false;
  }
};
</script>