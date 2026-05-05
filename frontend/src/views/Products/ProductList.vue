<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50/30">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 md:py-8">
      
      <!-- Header Section -->
      <div class="mb-6 md:mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
              <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Product Inventory</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Track and manage your machinery parts & equipment</p>
              </div>
            </div>
            <button 
              @click="openCreateModal" 
              class="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 sm:px-6 py-2.5 rounded-xl font-semibold transition-all duration-200 shadow-lg shadow-blue-200 flex items-center justify-center gap-2 transform hover:scale-105 active:scale-95"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add New Part
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Total Products</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ totalProducts }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Low Stock Items</p>
              <p class="text-2xl font-bold text-orange-600 mt-1">{{ lowStockCount }}</p>
            </div>
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Total Categories</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ categories.length }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider">Total Value</p>
              <p class="text-2xl font-bold text-green-600 mt-1">৳{{ totalValue.toFixed(0) }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Warehouse Filter Bar -->
      <div class="mb-4 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <span class="text-sm font-semibold text-gray-700">Filter by Warehouse</span>
          </div>
          <select 
            v-model="selectedWarehouse" 
            class="w-full sm:w-64 border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none transition-all bg-white"
          >
            <option value="">🏭 All Warehouses</option>
            <option 
              v-for="warehouse in warehouses" 
              :key="warehouse.id" 
              :value="warehouse.id"
            >
              📦 {{ warehouse.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Products Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-gray-800">All Products</h2>
          <p class="text-sm text-gray-500 mt-1">Manage your product inventory and stock levels</p>
        </div>
        
        <div class="overflow-x-auto">
          <SmartTable
            :endpoint="productsEndpoint"
            :columns="columns"
            ref="productTable"
            :key="tableKey"
            @data-loaded="onTableDataLoaded"
          >
            <!-- Custom Stock Column with Low Stock Indicator -->
            <template #cell-stock_quantity="{ row }">
              <div class="flex items-center gap-2">
                <div class="flex items-center gap-2">
                  <div class="w-16 bg-gray-200 rounded-full h-2">
                    <div 
                      class="h-2 rounded-full transition-all duration-300"
                      :class="getStockPercentage(row) <= 20 ? 'bg-red-500' : getStockPercentage(row) <= 50 ? 'bg-orange-500' : 'bg-green-500'"
                      :style="{ width: `${Math.min(getStockPercentage(row), 100)}%` }"
                    ></div>
                  </div>
                  <span :class="row.stock_quantity <= row.alert_quantity ? 'text-red-600 font-bold' : 'text-gray-700'">
                    {{ row.stock_quantity }}
                  </span>
                </div>
                <span 
                  v-if="row.stock_quantity <= row.alert_quantity" 
                  class="bg-red-100 text-red-600 text-[10px] px-2 py-1 rounded-full font-bold animate-pulse"
                >
                  Low Stock
                </span>
              </div>
            </template>

            <!-- Custom Price Column with Currency -->
            <template #cell-selling_price="{ row }">
              <div class="flex flex-col">
                <span class="font-semibold text-gray-800">৳ {{ Number(row.selling_price).toFixed(2) }}</span>
                <span class="text-xs text-gray-400 line-through" v-if="row.cost_price">
                  ৳ {{ Number(row.cost_price).toFixed(2) }}
                </span>
              </div>
            </template>

            <!-- Custom Name Column with Image -->
            <template #cell-name="{ row }">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg flex items-center justify-center overflow-hidden">
                  <img 
                    v-if="row.image" 
                    :src="getImageUrl(row.image)" 
                    :alt="row.name"
                    class="w-full h-full object-cover"
                  />
                  <svg v-else class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <div>
                  <p class="font-medium text-gray-800">{{ row.name }}</p>
                  <p class="text-xs text-gray-400">SKU: {{ row.sku }}</p>
                </div>
              </div>
            </template>

            <!-- Action Buttons -->
            <template #cell-actions="{ row }">
              <div class="flex gap-2">
                <button 
                  @click="editProduct(row)" 
                  class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors group"
                  title="Edit"
                >
                  <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button 
                  @click="confirmDelete(row.id)" 
                  class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors group"
                  title="Delete"
                >
                  <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </template>
          </SmartTable>
        </div>
      </div>
    </div>

    <!-- Create/Edit Product Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="closeModal">
      <div class="bg-white rounded-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto shadow-2xl animate-slide-up">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center">
          <div>
            <h2 class="text-xl font-bold text-gray-800">{{ isEditing ? 'Edit Product' : 'Add New Product' }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ isEditing ? 'Update product information' : 'Fill in the details to create a new product' }}</p>
          </div>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <!-- Modal Body -->
        <form enctype="multipart/form-data" @submit.prevent="saveProduct" class="p-6 space-y-5">
          <!-- Product Name -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Product Name <span class="text-red-500">*</span>
            </label>
            <input 
              v-model="form.name" 
              placeholder="Enter product name" 
              class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 outline-none transition-all" 
              required 
            />
          </div>

          <!-- Two Column Grid for Selections -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Category Selection -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Category <span class="text-red-500">*</span>
              </label>
              <select 
                v-model="form.category_id" 
                class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 outline-none transition-all bg-white"
                required
              >
                <option value="" disabled>Select Category</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <!-- Brand Selection -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Brand</label>
              <select 
                v-model="form.brand_id" 
                class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 outline-none transition-all bg-white"
              >
                <option value="">Select Brand (Optional)</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                  {{ brand.name }}
                </option>
              </select>
            </div>

            <!-- Unit Selection -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Unit <span class="text-red-500">*</span>
              </label>
              <select 
                v-model="form.unit_id" 
                class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 outline-none transition-all bg-white"
                required
              >
                <option value="" disabled>Select Unit</option>
                <option v-for="unit in units" :key="unit.id" :value="unit.id">
                  {{ unit.name }}
                </option>
              </select>
            </div>

            <!-- Image Upload with Preview -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Product Image</label>
              <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 hover:border-blue-400 transition-colors">
                <!-- Image Preview -->
                <div v-if="imagePreview" class="mb-3 flex justify-center">
                  <div class="relative inline-block">
                    <img 
                      :src="imagePreview" 
                      alt="Product preview" 
                      class="w-32 h-32 object-cover rounded-lg border-2 border-gray-200"
                    />
                    <button 
                      type="button"
                      @click="removeImage"
                      class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                      title="Remove image"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
                
                <!-- File Input -->
                <input 
                  type="file" 
                  @change="handleImageUpload" 
                  accept="image/*"
                  ref="imageInput"
                  class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                />
                <p class="text-xs text-gray-400 mt-2">Recommended: Square image, max 2MB</p>
              </div>
            </div>

            <!-- Warehouse Selection - Only visible when creating a new product -->
            <div v-if="!isEditing">
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Warehouse <span class="text-red-500">*</span>
              </label>
              <select 
                v-model="form.warehouse_id" 
                class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 outline-none transition-all bg-white"
                required
              >
                <option value="" disabled>Select Warehouse</option>
                <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                  {{ warehouse.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- SKU and Barcode -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">SKU</label>
              <input 
                v-model="form.sku" 
                placeholder="Auto-generated if empty" 
                class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 outline-none transition-all" 
              />
              <p class="text-xs text-gray-400 mt-1">Leave empty for auto-generation</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Barcode</label>
              <input 
                v-model="form.barcode" 
                placeholder="Enter barcode" 
                class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 outline-none transition-all" 
              />
            </div>
          </div>

          <!-- Pricing Section -->
          <div class="bg-blue-50 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Pricing Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cost Price <span class="text-red-500">*</span></label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">৳</span>
                  <input 
                    v-model="form.cost_price" 
                    type="number" 
                    step="0.01"
                    min="0"
                    placeholder="0.00" 
                    class="w-full border-2 border-gray-200 p-3 pl-8 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 outline-none transition-all" 
                    required 
                  />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Selling Price <span class="text-red-500">*</span></label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">৳</span>
                  <input 
                    v-model="form.selling_price" 
                    type="number" 
                    step="0.01"
                    min="0"
                    placeholder="0.00" 
                    class="w-full border-2 border-gray-200 p-3 pl-8 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 outline-none transition-all" 
                    required 
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Stock Section -->
          <div class="bg-orange-50 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
              Stock Management
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Initial Stock</label>
                <input 
                  v-model="form.stock_quantity" 
                  type="number" 
                  min="0"
                  placeholder="0" 
                  class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 outline-none transition-all" 
                />
                <p class="text-xs text-gray-400 mt-1">For new products only</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Alert Quantity <span class="text-red-500">*</span></label>
                <input 
                  v-model="form.alert_quantity" 
                  type="number" 
                  min="0"
                  placeholder="0" 
                  class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 outline-none transition-all" 
                  required 
                />
                <p class="text-xs text-gray-400 mt-1">Low stock notification threshold</p>
              </div>
            </div>
          </div>

          <!-- Status Toggle -->
          <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
            <div>
              <label for="status" class="text-sm font-semibold text-gray-700">Product Status</label>
              <p class="text-xs text-gray-500 mt-0.5">Enable or disable this product</p>
            </div>
            <button 
              type="button"
              @click="form.status = !form.status"
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              :class="form.status ? 'bg-blue-600' : 'bg-gray-300'"
            >
              <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                :class="form.status ? 'translate-x-6' : 'translate-x-1'"
              />
            </button>
          </div>

          <!-- Form Actions -->
          <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
            <button 
              type="button" 
              @click="closeModal" 
              class="px-6 py-2.5 text-gray-600 hover:text-gray-800 font-medium transition-colors rounded-xl"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-2.5 rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 transition-all disabled:opacity-50 shadow-lg shadow-blue-200"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Saving...' : (isEditing ? 'Update Product' : 'Save Product') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from "vue";
import SmartTable from "@/components/SmartTable.vue";
import api from '@/api/axios';

// Table reference and key for refresh
const productTable = ref(null);
const tableKey = ref(0);
const allProducts = ref([]);

// Stats
const totalProducts = ref(0);
const lowStockCount = ref(0);
const totalValue = ref(0);

// Warehouse filtering
const warehouses = ref([]);
const selectedWarehouse = ref('');

// Computed endpoint that includes warehouse_id as query param
const productsEndpoint = computed(() => {
  let url = '/products';
  if (selectedWarehouse.value) {
    url += `?warehouse_id=${selectedWarehouse.value}`;
  }
  return url;
});

// Modal state
const showModal = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);
const editingId = ref(null);

const imagePreview = ref(null);
const imageInput = ref(null);
const existingImagePath = ref(null);

// Form state
const form = reactive({
  name: '',
  category_id: '',
  brand_id: '',
  unit_id: '',
  warehouse_id: '',
  sku: '',
  barcode: '',
  cost_price: '',
  selling_price: '',
  stock_quantity: '',
  alert_quantity: '',
  image: null,
  status: true
});

// Data lists
const categories = ref([]);
const brands = ref([]);
const units = ref([]);

// Table columns definition
const columns = [
  { key: "name", label: "Product", sortable: true },
  { key: "sku", label: "SKU", sortable: true },
  { key: "category.name", label: "Category" },
  { key: "stock_quantity", label: "Stock" },
  { key: "selling_price", label: "Price" },
  { key: "actions", label: "", align: "right" },
];

// Helper functions
const getStockPercentage = (row) => {
  if (!row.alert_quantity) return 100;
  return (row.stock_quantity / (row.alert_quantity * 2)) * 100;
};

const onTableDataLoaded = (data) => {
  allProducts.value = data;
  totalProducts.value = data.length;
  lowStockCount.value = data.filter(p => p.stock_quantity <= p.alert_quantity).length;
  totalValue.value = data.reduce((sum, p) => sum + (p.selling_price * p.stock_quantity), 0);
};

// Fetch warehouses
const fetchWarehouses = async () => {
  try {
    const response = await api.get('/warehouses');
    // Adjust based on your API response structure (pagination or direct array)
    warehouses.value = response.data.data || response.data;
  } catch (error) {
    console.error("Error fetching warehouses:", error);
  }
};

// Fetch other dropdown data (categories, brands, units)
const fetchFormData = async () => {
  try {
    const formDataResponse = await api.get('/products/form-data');
    const formData = formDataResponse.data;
    categories.value = formData.categories;
    brands.value = formData.brands;
    units.value = formData.units;
    // Optionally, if warehouses are also returned from form-data, merge them
    if (formData.warehouses && formData.warehouses.length) {
      warehouses.value = formData.warehouses;
    }
  } catch (error) {
    console.error("Dropdown load failed:", error);
  }
};

// Watch warehouse filter – refresh table when changed
watch(selectedWarehouse, () => {
  tableKey.value += 1;
});

// Reset form
const resetForm = () => {
  form.name = '';
  form.category_id = '';
  form.brand_id = '';
  form.unit_id = '';
  form.warehouse_id = '';
  form.sku = '';
  form.barcode = '';
  form.cost_price = '';
  form.selling_price = '';
  form.stock_quantity = '';
  form.alert_quantity = '';
  form.image = null;
  form.status = true;
  editingId.value = null;
  imagePreview.value = null;
  existingImagePath.value = null;
  if (imageInput.value) {
    imageInput.value.value = '';
  }
};

// Open create modal
const openCreateModal = () => {
  isEditing.value = false;
  resetForm();
  showModal.value = true;
};

// Edit product
const editProduct = (product) => {
  isEditing.value = true;
  editingId.value = product.id;
  form.name = product.name;
  form.category_id = product.category_id;
  form.brand_id = product.brand_id || '';
  form.unit_id = product.unit_id;
  form.warehouse_id = product.warehouse_id || '';
  form.sku = product.sku;
  form.barcode = product.barcode || '';
  form.cost_price = product.cost_price;
  form.selling_price = product.selling_price;
  form.stock_quantity = product.stock_quantity;
  form.alert_quantity = product.alert_quantity;
  form.status = product.status;
  if (product.image) {
    existingImagePath.value = product.image;
    imagePreview.value = getImageUrl(product.image);
  } else {
    existingImagePath.value = null;
    imagePreview.value = null;
  }
  form.image = null;
  if (imageInput.value) {
    imageInput.value.value = '';
  }
  showModal.value = true;
};

// Close modal
const closeModal = () => {
  showModal.value = false;
  resetForm();
};

// Handle image upload
const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      alert('Image size must be less than 2MB');
      event.target.value = '';
      return;
    }
    if (!file.type.match(/image\/(jpeg|png|jpg|gif)/)) {
      alert('Please upload a valid image file (JPEG, PNG, JPG, or GIF)');
      event.target.value = '';
      return;
    }
    form.image = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
    existingImagePath.value = null;
  } else {
    form.image = null;
  }
};

// Get full image URL
const getImageUrl = (imagePath) => {
  if (!imagePath) return '';
  if (imagePath.startsWith('http')) return imagePath;
  const baseUrl = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';
  return `${baseUrl}/storage/${imagePath}`;
};

// Remove image
const removeImage = () => {
  form.image = null;
  imagePreview.value = null;
  existingImagePath.value = null;
  if (imageInput.value) {
    imageInput.value.value = '';
  }
};

// Save product (create or update)
const saveProduct = async () => {
  isSubmitting.value = true;
  try {
    const formData = new FormData();
    if (form.name) formData.append('name', form.name);
    if (form.category_id) formData.append('category_id', form.category_id);
    if (form.brand_id) formData.append('brand_id', form.brand_id);
    if (form.unit_id) formData.append('unit_id', form.unit_id);
    if (form.sku) formData.append('sku', form.sku);
    if (form.barcode) formData.append('barcode', form.barcode);
    if (form.cost_price !== '' && form.cost_price !== null) formData.append('cost_price', form.cost_price);
    if (form.selling_price !== '' && form.selling_price !== null) formData.append('selling_price', form.selling_price);
    if (form.alert_quantity !== '' && form.alert_quantity !== null) formData.append('alert_quantity', form.alert_quantity);
    if (form.stock_quantity !== '' && form.stock_quantity !== null) formData.append('stock_quantity', form.stock_quantity);
    formData.append('status', form.status ? '1' : '0');
    if (!isEditing.value && form.warehouse_id) {
      formData.append('warehouse_id', form.warehouse_id);
    }
    if (form.image instanceof File) {
      formData.append('image', form.image);
    }
    
    let response;
    if (isEditing.value) {
      formData.append('_method', 'PUT');
      response = await api.post(`/products/${editingId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } else {
      response = await api.post('/products', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    }
    
    showModal.value = false;
    alert("Product saved successfully!");
    tableKey.value += 1;
  } catch (error) {
    console.error("Save failed:", error.response?.data);
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      const errorMessages = [];
      for (const [field, messages] of Object.entries(errors)) {
        errorMessages.push(`${field}: ${messages.join(', ')}`);
      }
      alert(`Validation Error:\n${errorMessages.join('\n')}`);
    } else if (error.response?.data?.message) {
      alert(error.response.data.message);
    } else {
      alert("An error occurred while saving the product.");
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Delete product
const confirmDelete = async (id) => {
  if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
    try {
      await api.delete(`/products/${id}`);
      tableKey.value += 1;
      alert('Product deleted successfully!');
    } catch (error) {
      console.error('Error deleting product:', error);
      if (error.response?.status === 422) {
        alert(error.response.data.message || 'Cannot delete product with transaction history.');
      } else {
        alert('Failed to delete product. Please try again.');
      }
    }
  }
};

// Lifecycle
onMounted(() => {
  fetchWarehouses();
  fetchFormData();
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