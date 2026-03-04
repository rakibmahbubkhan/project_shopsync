<template>
  <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-primary">ShopSync</h1>
      <p class="text-gray-500 mt-2">Sign in to your account</p>
    </div>

    <form @submit.prevent="handleLogin" class="space-y-6">
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
        <input v-model="form.email" type="email" required class="w-full px-4 py-3 border rounded-xl outline-none focus:ring-2 focus:ring-primary" placeholder="admin@example.com" />
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
        <input v-model="form.password" type="password" required class="w-full px-4 py-3 border rounded-xl outline-none focus:ring-2 focus:ring-primary" placeholder="••••••••" />
      </div>
      <button type="submit" :disabled="loading" class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-3 rounded-xl transition-all">
        {{ loading ? 'Signing in...' : 'Login' }}
      </button>
      <p v-if="error" class="text-red-500 text-sm text-center">{{ error }}</p>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import api from '@/api/axios';

const router = useRouter();
const auth = useAuthStore();
const loading = ref(false);
const error = ref('');
const form = reactive({ email: '', password: '' });

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  try {
    const res = await api.post('/login', form);
    auth.token = res.data.token;
    auth.user = res.data.user;
    router.push('/');
  } catch (err) {
    error.value = 'Invalid login credentials';
  } finally {
    loading.value = false;
  }
};
</script>