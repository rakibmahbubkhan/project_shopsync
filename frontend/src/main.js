import './assets/main.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia' // Import Pinia
import App from './App.vue'
import router from './router'

const app = createApp(App) // 1. Create the app instance

app.use(createPinia())    // 2. Install Pinia (MUST be before router)
app.use(router)           // 3. Install the router

app.mount('#app')         // 4. Mount to the DOM