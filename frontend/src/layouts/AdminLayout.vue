<template>
  <div class="flex h-screen w-full bg-gray-50 overflow-hidden font-sans">
    
    <Sidebar class="flex-shrink-0" />
    
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <Navbar />
      
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
        <div class="max-w-[1600px] mx-auto"> <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from "@/components/Sidebar.vue";
import Navbar from "@/components/Navbar.vue";
</script>