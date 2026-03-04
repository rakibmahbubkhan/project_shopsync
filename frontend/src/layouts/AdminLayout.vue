<template>
  <div class="flex h-screen w-full bg-gray-100 overflow-hidden">
    
    <Sidebar class="hidden md:flex flex-shrink-0" />

    <div class="flex-1 flex flex-col min-w-0">
      <Navbar />

      <main class="flex-1 overflow-y-auto p-4 md:p-8">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
// Imports happen HERE, not in App.vue
import Sidebar from "@/components/Sidebar.vue";
import Navbar from "@/components/Navbar.vue";
</script>