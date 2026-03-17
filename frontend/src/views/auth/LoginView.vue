<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-96">
      <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
      
      <form @submit.prevent="handleLogin">
        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full border rounded-lg p-2.5"
            required
          />
        </div>
        
        <div class="mb-6">
          <label class="block text-sm font-medium mb-2">Password</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full border rounded-lg p-2.5"
            required
          />
        </div>
        
        <div v-if="error" class="mb-4 text-red-600 text-sm text-center">
          {{ error }}
        </div>
        
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 disabled:opacity-50"
        >
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/api/axios';

const router = useRouter();
const loading = ref(false);
const error = ref('');

const form = ref({
  email: '',
  password: ''
});

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    // Optional: Get CSRF cookie first (for Sanctum)
    // await api.get('/sanctum/csrf-cookie');
    
    const response = await api.post('/login', form.value);
    
    if (response.data.success) {
      // Store token and user data
      localStorage.setItem('token', response.data.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.data.user));
      
      // Set default Authorization header
      api.defaults.headers.common['Authorization'] = `Bearer ${response.data.data.token}`;
      
      // Redirect to dashboard
      router.push('/dashboard');
    }
  } catch (err) {
    console.error('Login error:', err);
    
    if (err.response) {
      // The request was made and the server responded with a status code
      if (err.response.status === 401) {
        error.value = 'Invalid email or password';
      } else if (err.response.status === 422) {
        error.value = 'Please check your input';
      } else if (err.response.status === 500) {
        error.value = 'Server error. Please try again later.';
        // Log the error details in development
        if (import.meta.env.DEV && err.response.data.debug) {
          console.error('Server debug:', err.response.data.debug);
        }
      } else {
        error.value = err.response.data.message || 'Login failed';
      }
    } else if (err.request) {
      // The request was made but no response was received
      error.value = 'No response from server. Please check your connection.';
    } else {
      // Something happened in setting up the request
      error.value = 'Error setting up request';
    }
  } finally {
    loading.value = false;
  }
};
</script>