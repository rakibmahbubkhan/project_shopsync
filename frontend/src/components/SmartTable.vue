<template>
  <div class="bg-white shadow rounded-xl p-4">
    <!-- Search -->
    <div class="flex justify-between mb-4">
      <input
        v-model="searchTerm"
        @input="fetchData"
        type="text"
        placeholder="Search..."
        class="border px-3 py-2 rounded w-64"
      />
    </div>

    <!-- Table -->
    <table class="min-w-full text-sm">
      <thead>
        <tr>
          <th
            v-for="col in columns"
            :key="col.key"
            @click="sort(col.key)"
            class="cursor-pointer text-left py-2"
          >
            {{ col.label }}
          </th>
        </tr>
      </thead>

      <tbody>
        <tr v-if="loading">
          <td :colspan="columns.length" class="text-center py-4">
            Loading...
          </td>
        </tr>

        <tr v-for="item in rows" :key="item.id" class="border-t">
          <td v-for="col in columns" :key="col.key" class="py-2">
            {{ item[col.key] }}
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="flex justify-between items-center mt-4">
      <button
        @click="changePage(meta.current_page - 1)"
        :disabled="meta.current_page === 1"
        class="px-3 py-1 bg-gray-200 rounded"
      >
        Previous
      </button>

      <span>
        Page {{ meta.current_page }} of {{ meta.last_page }}
      </span>

      <button
        @click="changePage(meta.current_page + 1)"
        :disabled="meta.current_page === meta.last_page"
        class="px-3 py-1 bg-gray-200 rounded"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import api from "@/api/axios";

const props = defineProps({
  endpoint: String,
  columns: Array,
});

const rows = ref([]);
const meta = ref({});
const searchTerm = ref("");
const loading = ref(false);
const sortBy = ref(null);
const order = ref("asc");

const fetchData = async (page = 1) => {
  loading.value = true;

  const response = await api.get(props.endpoint, {
    params: {
      page,
      search: searchTerm.value,
      sort_by: sortBy.value,
      order: order.value,
    },
  });

  rows.value = response.data.data;
  meta.value = response.data;
  loading.value = false;
};

const changePage = (page) => {
  if (page > 0 && page <= meta.value.last_page) {
    fetchData(page);
  }
};

const sort = (column) => {
  if (sortBy.value === column) {
    order.value = order.value === "asc" ? "desc" : "asc";
  } else {
    sortBy.value = column;
    order.value = "asc";
  }
  fetchData();
};

fetchData();
</script>