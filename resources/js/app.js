// main.js or main.ts
import 'bootstrap/dist/css/bootstrap.min.css';

import './bootstrap';
import { createApp } from 'vue';
import App from './app.vue';
import router from './router';
import '../css/app.css';
import axios from 'axios';
import Toast, { POSITION } from 'vue-toastification'; // Correctly import Vue Toastification
import 'vue-toastification/dist/index.css'; // Import Vue Toastification CSS

/**
 * Create a fresh Vue application instance.
 */
const app = createApp(App);

/**
 * Configure Axios globally
 */
axios.defaults.baseURL = 'http://127.0.0.1:8000'; // Replace with your actual API base URL

/**
 * Axios Interceptor to include Authorization header
 */
axios.interceptors.request.use(
  config => {
    const token = localStorage.getItem('access_token'); // Corrected key
    console.log('Axios Interceptor - access_token:', token); // Optional: Log the token for debugging
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

/**
 * Configure Vue Toastification
 */
app.use(Toast, {
  position: POSITION.TOP_RIGHT,
  timeout: 3000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
});

/**
 * Register Vue Router
 */
app.use(router);

/**
 * Mount the Vue application
 */
app.mount('#app');
