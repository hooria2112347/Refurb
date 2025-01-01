/**
 * First, we load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. This is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import { createApp } from 'vue';
import App from './app.vue'; 
import router from './router';
import '../css/app.css';
import axios from 'axios';
import Toast, { POSITION } from 'vue-toastification'; // Import Vue Toastification
import 'vue-toastification/dist/index.css'; // Import Vue Toastification CSS

/**
 * Create a fresh Vue application instance.
 */

const app = createApp(App);

/**
 * Configure Axios globally
 */

// Set the base URL for Axios (optional, adjust as needed)
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

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// Uncomment and adjust the following lines if you want to auto-register components
// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     const componentName = path.split('/').pop().replace(/\.\w+$/, '');
//     app.component(componentName, definition.default);
// });

/**
 * Finally, we have already attached the application instance to a HTML element with
 * an "id" attribute of "app" above. This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */
