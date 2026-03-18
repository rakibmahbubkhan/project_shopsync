<template>
  <div class="flex h-screen w-full bg-gray-100 overflow-hidden">
    <!-- Sidebar - part of the flex layout, not fixed -->
    <Sidebar 
      class="hidden md:block fixed left-0 top-0 h-screen transition-all duration-500 ease-in-out z-40"
      :class="sidebarWidth"
    />

    <!-- Main Content Area - automatically adjusts based on sidebar width -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <Navbar class="ml-16"/>
      <main class="flex-1 overflow-y-auto ml-16">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import Sidebar from "@/components/Sidebar.vue";
import Navbar from "@/components/Navbar.vue";
import { useSidebarStore } from '@/stores/sidebarStore';

const sidebarStore = useSidebarStore();

// Load saved state on mount
onMounted(() => {
  sidebarStore.loadState();
});

// Compute sidebar width class based on collapsed state from store
const sidebarWidth = computed(() => {
  return sidebarStore.isCollapsed ? 'w-20' : 'w-72';
});
</script>