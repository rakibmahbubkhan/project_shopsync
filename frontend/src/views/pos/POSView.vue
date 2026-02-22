<template>
  <div class="flex h-screen bg-gray-100">
    
    <!-- Product Section -->
    <div class="w-2/3 p-4">
      <input
        v-model="search"
        @input="searchProducts"
        placeholder="Scan barcode or search product..."
        class="w-full p-3 border rounded-lg mb-4"
        autofocus
      />

      <div class="grid grid-cols-3 gap-4">
        <div
          v-for="product in products"
          :key="product.id"
          @click="addToCart(product)"
          class="bg-white p-4 rounded shadow cursor-pointer hover:bg-blue-50"
        >
          <h3 class="font-semibold">{{ product.name }}</h3>
          <p class="text-sm text-gray-500">Stock: {{ product.stock }}</p>
          <p class="font-bold">${{ product.selling_price }}</p>
        </div>
      </div>
    </div>

    <!-- Cart Section -->
    <div class="w-1/3 bg-white p-4 shadow-lg flex flex-col">
      <h2 class="text-lg font-bold mb-4">Cart</h2>

      <div class="flex-1 overflow-y-auto">
        <div
          v-for="item in cart"
          :key="item.id"
          class="flex justify-between py-2 border-b"
        >
          <div>
            <p>{{ item.name }}</p>
            <p class="text-sm text-gray-500">
              {{ item.quantity }} × {{ item.selling_price }}
            </p>
          </div>
          <button @click="removeFromCart(item.id)">✕</button>
        </div>
      </div>

      <div class="mt-4 border-t pt-4">
        <p>Subtotal: {{ subtotal }}</p>
        <p>Tax (5%): {{ tax }}</p>
        <p class="font-bold text-lg">Total: {{ total }}</p>

        <button
          @click="checkout"
          class="w-full bg-blue-600 text-white py-3 rounded mt-3"
        >
          Complete Sale
        </button>
      </div>
    </div>

  </div>
</template>


<script setup>
import { ref, computed } from "vue";
import api from "@/api/axios";

const search = ref("");
const products = ref([]);
const cart = ref([]);

const searchProducts = async () => {
  const response = await api.get("/products", {
    params: { search: search.value }
  });
  products.value = response.data.data;
};

const addToCart = (product) => {
  const existing = cart.value.find(i => i.id === product.id);

  if (existing) {
    existing.quantity++;
  } else {
    cart.value.push({
      ...product,
      quantity: 1
    });
  }
};

const removeFromCart = (id) => {
  cart.value = cart.value.filter(i => i.id !== id);
};

const subtotal = computed(() =>
  cart.value.reduce((sum, item) =>
    sum + item.quantity * item.selling_price, 0)
);

const tax = computed(() => subtotal.value * 0.05);

const total = computed(() => subtotal.value + tax.value);

const checkout = async () => {
  await api.post("/sales", {
    items: cart.value,
    subtotal: subtotal.value,
    tax: tax.value,
    total: total.value
  });

  cart.value = [];
  alert("Sale completed");
};
</script>