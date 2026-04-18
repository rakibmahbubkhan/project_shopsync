<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50/30">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8">
      
      <!-- Header Section -->
      <div class="mb-6 md:mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Suppliers</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Manage parts vendors and manufacturers</p>
              </div>
            </div>
            <button 
              @click="openModal()" 
              class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-xl font-semibold transition-all shadow-lg shadow-blue-200 flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add Supplier
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Suppliers</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ totalSuppliers }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Active Suppliers</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ activeSuppliers }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Purchases</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ totalPurchases }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Spent</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">৳{{ formatNumber(totalSpent) }}</p>
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
              placeholder="Search suppliers by name, phone or email..."
              class="w-full pl-10 pr-4 py-2.5 border-2 border-gray-200 rounded-xl focus:border-blue-300 focus:ring-4 focus:ring-blue-100 outline-none transition-all"
            >
          </div>
          <div class="flex gap-3">
            <select v-model="sortBy" class="px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:border-blue-300 outline-none bg-white">
              <option value="name">Sort by Name</option>
              <option value="created_at">Sort by Date</option>
              <option value="purchase_count">Sort by Purchases</option>
            </select>
            <button @click="refreshTable" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Suppliers Grid/Cards (Mobile) -->
      <div class="block lg:hidden space-y-4">
        <div v-for="supplier in filteredSuppliers" :key="supplier.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
          <div class="flex justify-between items-start mb-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                  <span class="text-white font-bold text-sm">{{ supplier.name.charAt(0).toUpperCase() }}</span>
                </div>
                <h3 class="font-bold text-gray-800">{{ supplier.name }}</h3>
              </div>
              <div class="space-y-1 text-sm">
                <div class="flex items-center gap-2 text-gray-500">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <span>{{ supplier.phone }}</span>
                </div>
                <div v-if="supplier.email" class="flex items-center gap-2 text-gray-500">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <span class="text-sm break-all">{{ supplier.email }}</span>
                </div>
                <div v-if="supplier.address" class="flex items-start gap-2 text-gray-500">
                  <svg class="w-4 h-4 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span class="text-sm">{{ supplier.address }}</span>
                </div>
              </div>
            </div>
            <div class="flex gap-1">
              <button @click="openModal(supplier)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button @click="deleteSupplier(supplier.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
          <div class="pt-3 border-t border-gray-100 flex justify-between text-xs">
            <span class="text-gray-500">Total Purchases: {{ supplier.purchases_count || 0 }}</span>
            <span class="text-gray-500">Total Spent: ৳{{ formatNumber(supplier.total_spent || 0) }}</span>
          </div>
        </div>
        
        <div v-if="filteredSuppliers.length === 0 && !loading" class="text-center py-12">
          <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <p class="text-gray-500">No suppliers found</p>
        </div>
      </div>

      <!-- Suppliers Table (Desktop) -->
      <div class="hidden lg:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Company</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Address</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Purchases</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Spent</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="supplier in filteredSuppliers" :key="supplier.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                      <span class="text-white font-bold text-sm">{{ supplier.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <span class="font-semibold text-gray-800">{{ supplier.name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    {{ supplier.phone }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ supplier.email || '-' }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-start gap-2 text-gray-600">
                    <svg class="w-4 h-4 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-sm">{{ supplier.address || '-' }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                    {{ supplier.purchases_count || 0 }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="font-semibold text-gray-800">৳{{ formatNumber(supplier.total_spent || 0) }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <button @click="openModal(supplier)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button @click="deleteSupplier(supplier.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredSuppliers.length === 0 && !loading">
                <td colspan="7" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-700 mb-1">No suppliers found</h3>
                    <p class="text-sm text-gray-400">Click "Add Supplier" to create your first supplier</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex justify-center">
        <div class="flex gap-2">
          <button 
            @click="prevPage" 
            :disabled="currentPage === 1"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          >
            Previous
          </button>
          <span class="px-4 py-2 bg-blue-600 text-white rounded-lg">{{ currentPage }}</span>
          <button 
            @click="nextPage" 
            :disabled="filteredSuppliers.length < perPage"
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
            <h2 class="text-xl font-bold text-gray-800">{{ isEditing ? 'Edit Supplier' : 'Add New Supplier' }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ isEditing ? 'Update supplier information' : 'Enter supplier details' }}</p>
          </div>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        
        <form @submit.prevent="saveSupplier" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Company Name *</label>
            <input 
              v-model="form.name" 
              type="text" 
              required 
              class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-blue-300 focus:ring-4 focus:ring-blue-100 outline-none transition-all"
              placeholder="Enter company name"
            />
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Phone *</label>
              <input 
                v-model="form.phone" 
                type="text" 
                required 
                class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-blue-300 focus:ring-4 focus:ring-blue-100 outline-none transition-all"
                placeholder="Contact number"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Email</label>
              <input 
                v-model="form.email" 
                type="email" 
                class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-blue-300 focus:ring-4 focus:ring-blue-100 outline-none transition-all"
                placeholder="Email address"
              />
            </div>
          </div>
          
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Address</label>
            <textarea 
              v-model="form.address" 
              class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-blue-300 focus:ring-4 focus:ring-blue-100 outline-none transition-all" 
              rows="3"
              placeholder="Full address"
            ></textarea>
          </div>
          
          <button 
            type="submit" 
            :disabled="loading" 
            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-3 rounded-xl font-bold transition-all shadow-lg shadow-blue-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!loading" class="flex items-center justify-center gap-2">
              {{ isEditing ? 'Update Supplier' : 'Create Supplier' }}
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
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading suppliers...</p>
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
const suppliers = ref([]);
const searchQuery = ref('');
const sortBy = ref('name');
const currentPage = ref(1);
const perPage = ref(10);

// Stats
const totalSuppliers = ref(0);
const activeSuppliers = ref(0);
const totalPurchases = ref(0);
const totalSpent = ref(0);

// Form
const form = reactive({ 
  id: null, 
  name: '', 
  phone: '', 
  email: '', 
  address: '' 
});

// Computed
const filteredSuppliers = computed(() => {
  let filtered = [...suppliers.value];
  
  // Apply search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(s => 
      s.name.toLowerCase().includes(query) ||
      s.phone.toLowerCase().includes(query) ||
      (s.email && s.email.toLowerCase().includes(query))
    );
  }
  
  // Apply sorting
  filtered.sort((a, b) => {
    if (sortBy.value === 'name') return a.name.localeCompare(b.name);
    if (sortBy.value === 'created_at') return new Date(b.created_at) - new Date(a.created_at);
    if (sortBy.value === 'purchase_count') return (b.purchases_count || 0) - (a.purchases_count || 0);
    return 0;
  });
  
  return filtered;
});

// Methods
const formatNumber = (value) => {
  return parseFloat(value || 0).toFixed(2);
};

const loadSuppliers = async () => {
  loading.value = true;
  try {
    const response = await api.get('/suppliers');
    const data = response.data.data || response.data;
    suppliers.value = data;
    
    // Calculate stats
    totalSuppliers.value = data.length;
    activeSuppliers.value = data.filter(s => s.status === 'active').length;
    totalPurchases.value = data.reduce((sum, s) => sum + (s.purchases_count || 0), 0);
    totalSpent.value = data.reduce((sum, s) => sum + (s.total_spent || 0), 0);
  } catch (error) {
    console.error('Failed to load suppliers:', error);
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {
  currentPage.value = 1;
};

const refreshTable = () => {
  loadSuppliers();
};

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
  if (filteredSuppliers.value.length === perPage.value) currentPage.value++;
};

const openModal = (supplier = null) => {
  if (supplier) {
    isEditing.value = true;
    Object.assign(form, supplier);
  } else {
    isEditing.value = false;
    Object.assign(form, { id: null, name: '', phone: '', email: '', address: '' });
  }
  showModal.value = true;
};

const saveSupplier = async () => {
  loading.value = true;
  try {
    if (isEditing.value) {
      await api.put(`/suppliers/${form.id}`, form);
    } else {
      await api.post('/suppliers', form);
    }
    showModal.value = false;
    await loadSuppliers();
  } catch (error) {
    alert(error.response?.data?.message || "Operation failed");
  } finally {
    loading.value = false;
  }
};

const deleteSupplier = async (id) => {
  if (!confirm("Are you sure you want to delete this supplier? This action cannot be undone.")) return;
  
  loading.value = true;
  try {
    await api.delete(`/suppliers/${id}`);
    await loadSuppliers();
  } catch (error) {
    alert(error.response?.data?.message || "Cannot delete: Supplier has active purchase records.");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadSuppliers();
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