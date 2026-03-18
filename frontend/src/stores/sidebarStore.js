// stores/sidebarStore.js
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useSidebarStore = defineStore('sidebar', () => {
  const isCollapsed = ref(false);
  
  // Load initial state
  const loadState = () => {
    const saved = localStorage.getItem('sidebarCollapsed');
    if (saved !== null) {
      isCollapsed.value = JSON.parse(saved);
    }
  };
  
  // Toggle sidebar
  const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('sidebarCollapsed', JSON.stringify(isCollapsed.value));
  };
  
  // Set sidebar state
  const setCollapsed = (value) => {
    isCollapsed.value = value;
    localStorage.setItem('sidebarCollapsed', JSON.stringify(value));
  };
  
  return {
    isCollapsed,
    loadState,
    toggleSidebar,
    setCollapsed
  };
});