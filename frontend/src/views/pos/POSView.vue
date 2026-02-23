<template>
  <div class="flex h-screen bg-gray-100">

    <!-- LEFT: PRODUCT SECTION -->
    <div class="w-2/3 p-6 flex flex-col">

      <!-- Search / Barcode Input -->
      <input
        v-model="search"
        @input="searchProducts"
        placeholder="Scan barcode or search product..."
        class="w-full p-3 border rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500"
        autofocus
      />

      <!-- Product Grid -->
      <div class="grid grid-cols-3 gap-4 overflow-y-auto">
        <div
          v-for="product in products"
          :key="product.id"
          @click="addToCart(product)"
          class="bg-white p-4 rounded-xl shadow hover:shadow-lg cursor-pointer transition"
        >
          <h3 class="font-semibold text-gray-800">
            {{ product.name }}
          </h3>

          <p class="text-sm text-gray-500">
            Stock: {{ product.stock }}
          </p>

          <p class="mt-2 font-bold text-blue-600">
            ৳ {{ product.selling_price }}
          </p>
        </div>
      </div>

    </div>

    <!-- RIGHT: CART SECTION -->
    <div class="w-1/3 bg-white p-6 shadow-xl flex flex-col">

      <h2 class="text-xl font-bold mb-4">
        Cart
      </h2>

      <!-- Cart Items -->
      <div class="flex-1 overflow-y-auto">

        <div
          v-for="item in cart"
          :key="item.id"
          class="flex justify-between items-center border-b py-3"
        >
          <div>
            <p class="font-medium">
              {{ item.name }}
            </p>

            <div class="flex items-center gap-2 mt-1">
              <button
                @click="decreaseQty(item)"
                class="px-2 bg-gray-200 rounded"
              >-</button>

              <span>{{ item.quantity }}</span>

              <button
                @click="increaseQty(item)"
                class="px-2 bg-gray-200 rounded"
              >+</button>
            </div>

            <p class="text-sm text-gray-500">
              ৳ {{ item.selling_price }}
            </p>
          </div>

          <button
            @click="removeFromCart(item.id)"
            class="text-red-500 font-bold"
          >
            ✕
          </button>
        </div>

      </div>

      <!-- Totals -->
      <div class="border-t pt-4 mt-4 space-y-2">
        <div class="flex justify-between">
          <span>Subtotal</span>
          <span>৳ {{ subtotal.toFixed(2) }}</span>
        </div>

        <div class="flex justify-between">
          <span>Tax (5%)</span>
          <span>৳ {{ tax.toFixed(2) }}</span>
        </div>

        <div class="flex justify-between text-lg font-bold">
          <span>Total</span>
          <span>৳ {{ total.toFixed(2) }}</span>
        </div>

        <button
          @click="checkout"
          :disabled="cart.length === 0 || loading"
          class="w-full bg-blue-600 text-white py-3 rounded-lg mt-3 hover:bg-blue-700 transition"
        >
          {{ loading ? 'Processing...' : 'Complete Sale' }}
        </button>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed } from "vue"
import api from "@/api/axios"

const search = ref("")
const products = ref([])
const cart = ref([])
const loading = ref(false)

const searchProducts = async () => {
  if (!search.value) return

  const response = await api.get("/products", {
    params: { search: search.value }
  })

  products.value = response.data.data
}

const addToCart = (product) => {
  const existing = cart.value.find(i => i.id === product.id)

  if (existing) {
    if (existing.quantity < product.stock) {
      existing.quantity++
    }
  } else {
    cart.value.push({
      id: product.id,
      name: product.name,
      selling_price: product.selling_price,
      quantity: 1,
      stock: product.stock
    })
  }
}

const increaseQty = (item) => {
  if (item.quantity < item.stock) {
    item.quantity++
  }
}

const decreaseQty = (item) => {
  if (item.quantity > 1) {
    item.quantity--
  }
}

const removeFromCart = (id) => {
  cart.value = cart.value.filter(i => i.id !== id)
}

const subtotal = computed(() =>
  cart.value.reduce((sum, item) =>
    sum + item.quantity * item.selling_price, 0)
)

const tax = computed(() => subtotal.value * 0.05)

const total = computed(() => subtotal.value + tax.value)

const checkout = async () => {
  try {
    loading.value = true

    const response = await api.post("/sales", {
      items: cart.value,
      subtotal: subtotal.value,
      tax: tax.value,
      total: total.value
    })

    // Download receipt
    window.open(
      `http://localhost:8000/api/sales/${response.data.id}/receipt`,
      "_blank"
    )

    cart.value = []
    search.value = ""
    products.value = []

  } catch (error) {
    alert(error.response?.data?.message || "Sale failed")
  } finally {
    loading.value = false
  }
}
</script>