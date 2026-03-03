<template>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <Card title="Total Products" :value="data.total_products" />
    <Card title="Today Sales" :value="data.today_sales" />
    <Card title="Monthly Sales" :value="data.monthly_sales" />
    <Card title="Low Stock" :value="data.low_stock_products" />
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import api from "@/api/axios";
import Card from "@/components/Card.vue";

const data = ref({});

const stats = ref({
  total_sales: 0,
  total_revenue: 0,
  gross_profit: 0,
  low_stock_count: 0
});

const fetchDashboardStats = async () => {
  const res = await api.get('/reports/dashboard');
  stats.value = res.data;
};

onMounted(async () => {
  const response = await api.get("/reports/dashboard");
  data.value = response.data;
});

onMounted(fetchDashboardStats);
</script>