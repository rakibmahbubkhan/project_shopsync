<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-indigo-50/30">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8">
      
      <!-- Header Section -->
      <div class="mb-6 md:mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Warehouses</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Manage workshop storage locations</p>
              </div>
            </div>
            <button 
              @click="openModal()" 
              class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-5 py-2.5 rounded-xl font-semibold transition-all shadow-lg shadow-indigo-200 flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              New Warehouse
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Warehouses</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ totalWarehouses }}</p>
            </div>
            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Capacity</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ formatNumber(totalCapacity) }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Products</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ totalProducts }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Value</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">৳{{ formatNumber(stockValue) }}</p>
            </div>
            <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Search and Filter Bar -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input 
              type="text" 
              v-model="searchQuery"
              @input="handleSearch"
              placeholder="Search warehouses by name or address..."
              class="w-full pl-10 pr-4 py-2.5 border-2 border-gray-200 rounded-xl focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100 outline-none transition-all"
            >
          </div>
          <div class="flex gap-3">
            <select v-model="sortBy" class="px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:border-indigo-300 outline-none bg-white">
              <option value="name">Sort by Name</option>
              <option value="created_at">Sort by Date</option>
              <option value="products_count">Sort by Products</option>
            </select>
            <button @click="refreshTable" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Warehouses Grid/Cards (Mobile) -->
      <div class="block lg:hidden space-y-4">
        <div v-for="warehouse in paginatedWarehouses" :key="warehouse.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
          <div class="p-4">
            <div class="flex justify-between items-start mb-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
                <div>
                  <h3 class="font-bold text-gray-800">{{ warehouse.name }}</h3>
                  <p class="text-xs text-gray-400">ID: #{{ warehouse.id }}</p>
                </div>
              </div>
              <div class="flex gap-1">
                <button @click="openModal(warehouse)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button @click="deleteWarehouse(warehouse.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
            
            <div class="space-y-2 text-sm">
              <div class="flex items-start gap-2 text-gray-600">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="flex-1">{{ warehouse.address || 'No address specified' }}</span>
              </div>
            </div>
            
            <div class="mt-4 pt-3 border-t border-gray-100 grid grid-cols-2 gap-3">
              <div class="text-center">
                <p class="text-xs text-gray-500">Products</p>
                <p class="text-lg font-bold text-indigo-600">{{ warehouse.products_count || 0 }}</p>
              </div>
              <div class="text-center">
                <p class="text-xs text-gray-500">Stock Value</p>
                <p class="text-lg font-bold text-green-600">৳{{ formatNumber(warehouse.stock_value || 0) }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="paginatedWarehouses.length === 0 && !loading" class="text-center py-12">
          <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <p class="text-gray-500">No warehouses found</p>
          <button @click="openModal()" class="mt-4 text-indigo-600 hover:text-indigo-700 font-medium">Create your first warehouse →</button>
        </div>
      </div>

      <!-- Warehouses Table (Desktop) -->
      <div class="hidden lg:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Warehouse Name</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Location</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Products</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock Value</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="warehouse in paginatedWarehouses" :key="warehouse.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <span class="font-mono text-sm text-gray-500">#{{ warehouse.id }}</span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                    <span class="font-semibold text-gray-800">{{ warehouse.name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-start gap-2 text-gray-600">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-sm">{{ warehouse.address || '-' }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                    {{ warehouse.products_count || 0 }} products
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="font-semibold text-gray-800">৳{{ formatNumber(warehouse.stock_value || 0) }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <button @click="openModal(warehouse)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button @click="deleteWarehouse(warehouse.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="paginatedWarehouses.length === 0 && !loading">
                <td colspan="6" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-700 mb-1">No warehouses found</h3>
                    <p class="text-sm text-gray-400">Click "New Warehouse" to create your first warehouse</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="filteredWarehouses.length > perPage" class="mt-6 flex justify-center">
        <div class="flex gap-2">
          <button 
            @click="prevPage" 
            :disabled="currentPage === 1"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          >
            Previous
          </button>
          <span class="px-4 py-2 bg-indigo-600 text-white rounded-lg">{{ currentPage }}</span>
          <button 
            @click="nextPage" 
            :disabled="currentPage * perPage >= filteredWarehouses.length"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showModal = false">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-slide-up">
        <div class="p-6 border-b bg-gradient-to-r from-gray-50 to-white flex justify-between items-center">
          <div>
            <h2 class="text-xl font-bold text-gray-800">{{ isEditing ? 'Edit Warehouse' : 'Add New Warehouse' }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ isEditing ? 'Update warehouse information' : 'Enter warehouse details' }}</p>
          </div>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        
        <form @submit.prevent="saveWarehouse" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Warehouse Code (Optional)</label>
            <input 
              v-model="form.code" 
              type="text" 
              class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100 outline-none transition-all"
              placeholder="Auto-generated if left empty"
            />
            <p class="text-xs text-gray-400 mt-1">Leave empty to auto-generate a unique code</p>
          </div>
          
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Warehouse Name *</label>
            <input 
              v-model="form.name" 
              type="text" 
              required 
              class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100 outline-none transition-all"
              placeholder="e.g., Main Storage, Workshop A"
            />
          </div>
          
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Location / Address</label>
            <textarea 
              v-model="form.address" 
              class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100 outline-none transition-all" 
              rows="3"
              placeholder="Full address or location description"
            ></textarea>
          </div>
          
          <button 
            type="submit" 
            :disabled="loading" 
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white py-3 rounded-xl font-bold transition-all shadow-lg shadow-indigo-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!loading" class="flex items-center justify-center gap-2">
              {{ isEditing ? 'Update Warehouse' : 'Create Warehouse' }}
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </span>
            <span v-else class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Saving...
            </span>
          </button>
        </form>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white p-8 rounded-2xl shadow-2xl text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-600 border-t-transparent mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading warehouses...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import api from "@/api/axios";

// State
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const warehouses = ref([]);
const searchQuery = ref('');
const sortBy = ref('name');
const currentPage = ref(1);
const perPage = ref(10);

// Stats
const totalWarehouses = ref(0);
const totalCapacity = ref(0);
const totalProducts = ref(0);
const stockValue = ref(0);

// Form
const form = reactive({ 
  id: null, 
  code: '',
  name: '', 
  address: '' 
});

// Computed
const filteredWarehouses = computed(() => {
  let filtered = [...warehouses.value];
  
  // Apply search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(w => 
      w.name.toLowerCase().includes(query) ||
      (w.address && w.address.toLowerCase().includes(query))
    );
  }
  
  // Apply sorting
  filtered.sort((a, b) => {
    if (sortBy.value === 'name') return a.name.localeCompare(b.name);
    if (sortBy.value === 'created_at') return new Date(b.created_at) - new Date(a.created_at);
    if (sortBy.value === 'products_count') return (b.products_count || 0) - (a.products_count || 0);
    return 0;
  });
  
  return filtered;
});

const paginatedWarehouses = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  const end = start + perPage.value;
  return filteredWarehouses.value.slice(start, end);
});

// Methods
const formatNumber = (value) => {
  return parseFloat(value || 0).toFixed(2);
};

const loadWarehouses = async () => {
  loading.value = true;
  try {
    const response = await api.get('/warehouses');
    console.log('API Response:', response.data);
    
    // Handle the paginated response structure
    let warehousesData = [];
    if (response.data && response.data.data && response.data.data.data) {
      // Response structure: { success: true, data: { data: [...], current_page: 1, ... } }
      warehousesData = response.data.data.data;
    } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
      // Response structure: { success: true, data: [...] }
      warehousesData = response.data.data;
    } else if (response.data && Array.isArray(response.data)) {
      warehousesData = response.data;
    } else {
      warehousesData = [];
    }
    
    console.log('Processed warehouses:', warehousesData);
    warehouses.value = warehousesData;
    
    // Calculate stats
    if (warehouses.value.length > 0) {
      totalWarehouses.value = warehouses.value.length;
      totalProducts.value = warehouses.value.reduce((sum, w) => sum + (Number(w.products_count) || 0), 0);
      stockValue.value = warehouses.value.reduce((sum, w) => sum + (Number(w.stock_value) || 0), 0);
      totalCapacity.value = warehouses.value.reduce((sum, w) => sum + (Number(w.capacity) || 0), 0);
    } else {
      totalWarehouses.value = 0;
      totalProducts.value = 0;
      stockValue.value = 0;
      totalCapacity.value = 0;
    }
  } catch (error) {
    console.error('Failed to load warehouses:', error);
    warehouses.value = [];
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {
  currentPage.value = 1;
};

const refreshTable = () => {
  loadWarehouses();
};

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
  if (currentPage.value * perPage.value < filteredWarehouses.value.length) currentPage.value++;
};

const openModal = (warehouse = null) => {
  if (warehouse) {
    isEditing.value = true;
    form.id = warehouse.id;
    form.code = warehouse.code || '';
    form.name = warehouse.name || '';
    form.address = warehouse.address || '';
  } else {
    isEditing.value = false;
    form.id = null;
    form.code = '';
    form.name = '';
    form.address = '';
  }
  showModal.value = true;
};

const saveWarehouse = async () => {
  loading.value = true;
  try {
    const payload = {
      name: form.name,
      address: form.address
    };
    
    // Only include code if it's provided (not empty)
    if (form.code && form.code.trim() !== '') {
      payload.code = form.code;
    }
    
    if (isEditing.value) {
      await api.put(`/warehouses/${form.id}`, payload);
    } else {
      await api.post('/warehouses', payload);
    }
    showModal.value = false;
    await loadWarehouses();
  } catch (error) {
    console.error('Save error:', error);
    alert(error.response?.data?.message || "Error saving warehouse data");
  } finally {
    loading.value = false;
  }
};

const deleteWarehouse = async (id) => {
  if (!confirm("Are you sure you want to delete this warehouse? This action cannot be undone.")) return;
  
  loading.value = true;
  try {
    await api.delete(`/warehouses/${id}`);
    await loadWarehouses();
  } catch (error) {
    console.error('Delete error:', error);
    alert(error.response?.data?.message || "Cannot delete warehouse that contains stock.");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadWarehouses();
});
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
</style>