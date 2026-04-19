import { defineStore } from "pinia";
import api from "@/api/axios";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    // Parse the stored user string back into an object
    user: JSON.parse(localStorage.getItem("user")) || null,
    token: localStorage.getItem("token") || null,
  }),

  actions: {
    async login(credentials) {
      const response = await api.post("/login", credentials);
      
      // Note: your current backend returns data inside a 'data' wrapper
      const userData = response.data.data.user;
      const tokenData = response.data.data.token;

      this.token = tokenData;
      this.user = userData;

      localStorage.setItem("token", tokenData);
      localStorage.setItem("user", JSON.stringify(userData)); // Save the whole object
    },

    logout() {
      this.token = null;
      this.user = null;
      localStorage.removeItem("token");
      localStorage.removeItem("user");
    },
  },
});