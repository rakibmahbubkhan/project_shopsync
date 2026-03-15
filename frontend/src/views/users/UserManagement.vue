<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">Staff Management</h1>
      <button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition-all">
        + Add New Staff
      </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="px-6 py-4 text-sm font-bold text-gray-600">Name</th>
            <th class="px-6 py-4 text-sm font-bold text-gray-600">Email</th>
            <th class="px-6 py-4 text-sm font-bold text-gray-600">Role</th>
            <th class="px-6 py-4 text-sm font-bold text-gray-600 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm text-gray-800 font-medium">{{ user.name }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ user.email }}</td>
            <td class="px-6 py-4">
              <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider" 
                :class="user.role?.name === 'Admin' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'">
                {{ user.role?.name || 'No Role' }}
              </span>
            </td>
            <td class="px-6 py-4 text-right space-x-2">
              <button @click="editUser(user)" class="text-blue-600 hover:underline">Edit</button>
              <button @click="deleteUser(user.id)" class="text-red-600 hover:underline">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl w-full max-w-md p-8 shadow-2xl">
        <h2 class="text-xl font-bold mb-6">{{ isEditing ? 'Edit Staff Member' : 'Add New Staff' }}</h2>
        <form @submit.prevent="saveUser" class="space-y-4">
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Full Name</label>
            <input v-model="form.name" type="text" class="w-full border rounded-lg p-2.5" required />
          </div>
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Email Address</label>
            <input v-model="form.email" type="email" class="w-full border rounded-lg p-2.5" required />
          </div>
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Assign Role</label>
            <select v-model="form.role_id" class="w-full border rounded-lg p-2.5" required>
              <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Password {{ isEditing ? '(Leave blank to keep current)' : '' }}</label>
            <input v-model="form.password" type="password" class="w-full border rounded-lg p-2.5" :required="!isEditing" />
          </div>
          <div class="flex justify-end gap-3 mt-8">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-500">Cancel</button>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold">Save Staff</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import api from '@/api/axios';

const users = ref([]);
const roles = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const currentUserId = ref(null);

const form = reactive({
  name: '', email: '', role_id: '', password: ''
});

const fetchData = async () => {
  const [userRes, roleRes] = await Promise.all([api.get('/users'), api.get('/users/roles')]);
  users.value = userRes.data;
  roles.value = roleRes.data;
};

const openCreateModal = () => {
  isEditing.value = false;
  Object.assign(form, { name: '', email: '', role_id: '', password: '' });
  showModal.value = true;
};

const editUser = (user) => {
  isEditing.value = true;
  currentUserId.value = user.id;
  Object.assign(form, { name: user.name, email: user.email, role_id: user.role_id, password: '' });
  showModal.value = true;
};

const saveUser = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/users/${currentUserId.value}`, form);
    } else {
      await api.post('/users', form);
    }
    fetchData();
    showModal.value = false;
  } catch (err) { alert(err.response?.data?.message || 'Error saving user'); }
};

const deleteUser = async (id) => {
  if (confirm('Are you sure you want to remove this staff member?')) {
    await api.delete(`/users/${id}`);
    fetchData();
  }
};

onMounted(fetchData);
</script>