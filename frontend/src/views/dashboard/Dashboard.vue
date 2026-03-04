<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard Overview</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Total Revenue</p>
        <p class="text-2xl font-bold text-primary mt-1">৳ {{ formatNumber(stats.total_revenue) }}</p>
      </div>
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Today's Sales</p>
        <p class="text-2xl font-bold text-secondary mt-1">{{ formatNumber(stats.today_sales || stats.total_sales) }}</p>
      </div>
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Active Products</p>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ formatNumber(stats.total_products || 0) }}</p>
      </div>
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Low Stock Alerts</p>
        <p class="text-2xl font-bold text-danger mt-1">{{ formatNumber(stats.low_stock_count || stats.low_stock_products || 0) }}</p>
      </div>
    </div>

    <!-- Additional Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Monthly Sales</p>
        <p class="text-2xl font-bold text-gray-800 mt-1">৳ {{ formatNumber(stats.monthly_sales || 0) }}</p>
      </div>
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Gross Profit</p>
        <p class="text-2xl font-bold text-green-600 mt-1">৳ {{ formatNumber(stats.gross_profit || 0) }}</p>
      </div>
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Total Sales (Count)</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ formatNumber(stats.total_sales_count || 0) }}</p>
      </div>
    </div>

    <!-- Chart Placeholder -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-64 flex items-center justify-center">
      <p class="text-gray-400 italic">Sales analytics chart will appear here...</p>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import api from "@/api/axios";

const stats = ref({
  total_sales: 0,
  total_revenue: 0,
  gross_profit: 0,
  low_stock_count: 0,
  total_products: 0,
  today_sales: 0,
  monthly_sales: 0,
  total_sales_count: 0,
  low_stock_products: 0
});

const formatNumber = (value) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value || 0);
};

const fetchDashboardStats = async () => {
  try {
    const response = await api.get('/reports/dashboard');
    stats.value = {
      ...stats.value,
      ...response.data
    };
  } catch (error) {
    console.error('Error fetching dashboard stats:', error);
    // You might want to show a toast notification here
  }
};

onMounted(() => {
  fetchDashboardStats();
});
</script>