<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/30">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8">
      
      <!-- Header Section -->
      <div class="mb-6 md:mb-8">
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg shadow-slate-200/50 border border-white/50 p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-purple-200">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">
                  Customer Management
                </h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage your customers, view their details, and track interactions</p>
              </div>
            </div>
            <button @click="openModal()" class="group bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all duration-200 shadow-lg shadow-purple-200 flex items-center gap-2 transform hover:scale-[1.02] active:scale-95">
              <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add Customer
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Total Customers</p>
              <p class="text-2xl font-bold text-slate-800">{{ customers.length }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Active Customers</p>
              <p class="text-2xl font-bold text-slate-800">{{ customers.filter(c => c.status !== 'inactive').length }}</p>
            </div>
            <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">With Email</p>
              <p class="text-2xl font-bold text-slate-800">{{ customers.filter(c => c.email).length }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">With Mobile</p>
              <p class="text-2xl font-bold text-slate-800">{{ customers.filter(c => c.mobile_number).length }}</p>
            </div>
            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Search and Filter Bar -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search by name, email, or mobile..." 
              class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
            >
          </div>
          <select v-model="filterStatus" class="px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 outline-none bg-white">
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
          <button @click="resetFilters" class="px-4 py-2.5 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
            Reset
          </button>
        </div>
      </div>

      <!-- Customers Table -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
              <tr>
                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Customer / Contact</th>
                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Email / Mobile</th>
                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Billing Location</th>
                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="customer in filteredCustomers" :key="customer.id" class="hover:bg-slate-50/50 transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center text-purple-700 font-semibold">
                      {{ getInitials(customer.name) }}
                    </div>
                    <div>
                      <div class="font-semibold text-slate-800">{{ customer.name }}</div>
                      <div class="text-sm text-slate-500">{{ customer.contact_person || 'No contact person' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-col gap-0.5">
                    <div class="flex items-center gap-1 text-sm">
                      <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                      </svg>
                      <span>{{ customer.email || 'N/A' }}</span>
                    </div>
                    <div v-if="customer.mobile_number" class="flex items-center gap-1 text-sm text-slate-500">
                      <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                      </svg>
                      <span>{{ customer.mobile_number }}</span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm">
                    {{ customer.billing_city || 'N/A' }}, {{ customer.billing_country || 'N/A' }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="customer.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'" class="px-2 py-1 rounded-full text-xs font-medium">
                    {{ customer.status || 'active' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <button @click="openModal(customer)" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button @click="toggleStatus(customer.id, customer.status)" class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors" :title="customer.status === 'active' ? 'Deactivate' : 'Activate'">
                      <svg v-if="customer.status === 'active'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </button>
                    <button @click="deleteCustomer(customer.id)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredCustomers.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-slate-700 mb-1">No customers found</h3>
                    <p class="text-sm text-slate-400">Click "Add Customer" to create your first customer</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Customer Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showModal = false">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-y-auto animate-slide-up">
        <div class="sticky top-0 bg-white border-b border-slate-100 p-5 flex justify-between items-center">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-purple-100 rounded-xl">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-bold text-slate-800">{{ isEditing ? 'Edit Customer' : 'Add New Customer' }}</h2>
              <p class="text-sm text-slate-500">{{ isEditing ? 'Update customer information' : 'Enter customer details to get started' }}</p>
            </div>
          </div>
          <button @click="showModal = false" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="saveCustomer" class="p-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column - Basic Information -->
            <div class="space-y-4">
              <h3 class="font-semibold text-slate-800 border-b border-slate-200 pb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Basic Information
              </h3>
              
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Customer Name <span class="text-red-500">*</span></label>
                <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all" placeholder="Enter customer name">
              </div>
              
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Contact Person</label>
                <input v-model="form.contact_person" type="text" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all" placeholder="Enter contact person name">
              </div>
              
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
                  <input v-model="form.email" type="email" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all" placeholder="customer@example.com">
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">Mobile Number <span class="text-red-500">*</span></label>
                  <input v-model="form.mobile_number" type="tel" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all" placeholder="+880 1XXXXXXXXX">
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Website</label>
                <input v-model="form.website" type="url" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all" placeholder="https://example.com">
              </div>
              
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select v-model="form.status" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all bg-white">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>

            <!-- Right Column - Address Information -->
            <div class="space-y-4">
              <h3 class="font-semibold text-slate-800 border-b border-slate-200 pb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Billing Address
              </h3>
              
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Address <span class="text-red-500">*</span></label>
                <textarea v-model="form.billing_address" rows="2" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all resize-none" placeholder="Street address, P.O. Box"></textarea>
              </div>
              
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">City <span class="text-red-500">*</span></label>
                  <input v-model="form.billing_city" type="text" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all" placeholder="City">
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">Country <span class="text-red-500">*</span></label>
                  <input v-model="form.billing_country" type="text" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all" placeholder="Country">
                </div>
              </div>

              <h3 class="font-semibold text-slate-800 border-b border-slate-200 pb-2 pt-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Shipping Address
              </h3>
              
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Address <span class="text-red-500">*</span></label>
                <textarea v-model="form.shipping_address" rows="2" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all resize-none" placeholder="Street address, P.O. Box"></textarea>
              </div>
              
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">City <span class="text-red-500">*</span></label>
                  <input v-model="form.shipping_city" type="text" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all" placeholder="City">
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">Country <span class="text-red-500">*</span></label>
                  <input v-model="form.shipping_country" type="text" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all" placeholder="Country">
                </div>
              </div>
            </div>
          </div>

          <!-- Additional Notes -->
          <div class="mt-6">
            <label class="block text-sm font-medium text-slate-700 mb-1">Additional Notes / Description</label>
            <textarea v-model="form.description" rows="2" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-purple-300 focus:ring-4 focus:ring-purple-100 outline-none transition-all resize-none" placeholder="Any additional information about the customer..."></textarea>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
            <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-colors">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl font-medium transition-all shadow-md shadow-purple-200">
              {{ isEditing ? 'Update Customer' : 'Save Customer' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black/40 backdrop-blur-md flex items-center justify-center z-50">
      <div class="bg-white p-8 rounded-2xl shadow-2xl text-center animate-slide-up">
        <div class="relative">
          <div class="w-16 h-16 mx-auto">
            <div class="absolute inset-0 rounded-full border-4 border-purple-200"></div>
            <div class="absolute inset-0 rounded-full border-4 border-purple-600 border-t-transparent animate-spin"></div>
          </div>
        </div>
        <p class="mt-4 text-lg font-semibold text-slate-800">Processing...</p>
        <p class="text-sm text-slate-500 mt-1">Please wait</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import api from "@/api/axios";

// State
const customers = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const searchQuery = ref('');
const filterStatus = ref('all');

// Form Data
const form = reactive({
  id: null,
  name: '',
  contact_person: '',
  email: '',
  mobile_number: '',
  billing_address: '',
  billing_city: '',
  billing_country: '',
  shipping_address: '',
  shipping_city: '',
  shipping_country: '',
  website: '',
  description: '',
  status: 'active'
});

// Computed
const filteredCustomers = computed(() => {
  let filtered = customers.value;
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(c => 
      c.name?.toLowerCase().includes(query) ||
      c.email?.toLowerCase().includes(query) ||
      c.mobile_number?.includes(query) ||
      c.contact_person?.toLowerCase().includes(query)
    );
  }
  
  if (filterStatus.value !== 'all') {
    filtered = filtered.filter(c => (c.status || 'active') === filterStatus.value);
  }
  
  return filtered;
});

// Methods
const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const resetFilters = () => {
  searchQuery.value = '';
  filterStatus.value = 'all';
};

const loadCustomers = async () => {
  loading.value = true;
  try {
    const res = await api.get('/customers');
    customers.value = res.data.data || res.data;
  } catch (error) {
    console.error('Failed to load customers:', error);
    alert('Could not load customers');
  } finally {
    loading.value = false;
  }
};

const openModal = (customer = null) => {
  isEditing.value = !!customer;
  if (customer) {
    Object.assign(form, customer);
  } else {
    Object.keys(form).forEach(k => {
      if (k === 'status') form[k] = 'active';
      else if (k === 'id') form[k] = null;
      else form[k] = '';
    });
  }
  showModal.value = true;
};

const saveCustomer = async () => {
  loading.value = true;
  try {
    if (isEditing.value) {
      await api.put(`/customers/${form.id}`, form);
    } else {
      await api.post('/customers', form);
    }
    showModal.value = false;
    await loadCustomers();
  } catch (error) {
    console.error('Failed to save customer:', error);
    alert(error.response?.data?.message || "Error saving customer");
  } finally {
    loading.value = false;
  }
};

const toggleStatus = async (id, currentStatus) => {
  const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
  if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this customer?`)) {
    loading.value = true;
    try {
      await api.patch(`/customers/${id}/status`, { status: newStatus });
      await loadCustomers();
    } catch (error) {
      console.error('Failed to update status:', error);
      alert('Could not update customer status');
    } finally {
      loading.value = false;
    }
  }
};

const deleteCustomer = async (id) => {
  if (confirm("Delete this customer? This action cannot be undone.")) {
    loading.value = true;
    try {
      await api.delete(`/customers/${id}`);
      await loadCustomers();
    } catch (error) {
      console.error('Failed to delete customer:', error);
      alert('Could not delete customer');
    } finally {
      loading.value = false;
    }
  }
};

// Lifecycle
onMounted(() => {
  loadCustomers();
});
</script>

<style scoped>
@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.animate-slide-up {
  animation: slide-up 0.25s ease-out;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>