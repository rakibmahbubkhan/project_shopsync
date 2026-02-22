<template>
  <SmartTable
    endpoint="/products"
    :columns="columns"
  />
</template>

<script setup>
import SmartTable from "@/components/SmartTable.vue";

const columns = [
  { key: "id", label: "ID" },
  { key: "name", label: "Name" },
  { key: "sku", label: "SKU" },
  { key: "stock_quantity", label: "Stock" },
  { key: "selling_price", label: "Price" },
];
</script>