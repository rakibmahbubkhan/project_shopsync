<template>
  <Line :data="chartData" />
</template>

<script setup>
import { Line } from "vue-chartjs";
import { ref, onMounted } from "vue";
import api from "@/api/axios";

const chartData = ref({
  labels: [],
  datasets: [
    {
      label: "Monthly Revenue",
      data: [],
      borderColor: "#3b82f6",
      fill: false,
    },
  ],
});

onMounted(async () => {
  const response = await api.get("/reports/sales?monthly=true");

  chartData.value.labels = response.data.labels;
  chartData.value.datasets[0].data = response.data.values;
});
</script>