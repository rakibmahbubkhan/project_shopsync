/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: "#2563eb",   // Blue-600
          light: "#3b82f6",
          dark: "#1e40af",
        },
        secondary: {
          DEFAULT: "#10b981",   // Emerald-500
        },
        danger: "#ef4444",
        warning: "#f59e0b",
      },
      fontFamily: {
        sans: ["Inter", "sans-serif"],
      },
      boxShadow: {
        card: "0 4px 20px rgba(0,0,0,0.05)",
      },
    },
  },
  plugins: [],
}
