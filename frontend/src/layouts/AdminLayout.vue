<template>
  <div class="flex h-screen w-full bg-gray-100 overflow-hidden">
    <!-- Sidebar - fixed with proper z-index -->
    <Sidebar @toggle="handleSidebarToggle" />
    
    <!-- Main Content Area - margin adjusts based on sidebar width -->
    <div 
      class="flex-1 flex flex-col min-w-0 overflow-hidden transition-all duration-300 ease-in-out"
      :class="sidebarExpanded ? 'ml-80' : 'ml-[72px]'"
    >
      <Navbar />
      <main class="flex-1 overflow-y-auto p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Sidebar from "@/components/Sidebar.vue";
import Navbar from "@/components/Navbar.vue";
import { useSidebarStore } from '@/stores/sidebarStore';

const sidebarStore = useSidebarStore();
const sidebarExpanded = ref(!sidebarStore.isCollapsed);

onMounted(() => {
  sidebarStore.loadState();
  sidebarExpanded.value = !sidebarStore.isCollapsed;
});

const handleSidebarToggle = (expanded) => {
  sidebarExpanded.value = expanded;
};

// Watch for store changes
watch(() => sidebarStore.isCollapsed, (newVal) => {
  sidebarExpanded.value = !newVal;
});
</script>