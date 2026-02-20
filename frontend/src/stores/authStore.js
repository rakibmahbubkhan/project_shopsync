import { defineStore } from "pinia";
import api from "@/api/axios";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    token: localStorage.getItem("token") || null,
  }),

  actions: {
    async login(credentials) {
      const response = await api.post("/login", credentials);
      this.token = response.data.token;
      this.user = response.data.user;
      localStorage.setItem("token", this.token);
    },

    logout() {
      this.token = null;
      this.user = null;
      localStorage.removeItem("token");
    },
  },
});