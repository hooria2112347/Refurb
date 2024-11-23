<!-- resources/js/components/ResetPassword.vue -->
<template>
    <div class="reset-password">
      <h2>Reset Password</h2>
      <form @submit.prevent="handleResetPassword">
        <div class="form-group">
          <label for="password">New Password</label>
          <input type="password" id="password" v-model="password" required />
        </div>
        <div class="form-group">
          <label for="password_confirmation">Confirm Password</label>
          <input type="password" id="password_confirmation" v-model="password_confirmation" required />
        </div>
  
        <button type="submit">Reset Password</button>
      </form>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'ResetPassword',
    data() {
      return {
        password: '',
        password_confirmation: '',
        email: this.$route.query.email || '',
        code: this.$route.query.code || '',
      };
    },
    methods: {
      async handleResetPassword() {
        try {
          await axios.post('/api/password/reset', {
            email: this.email,
            code: this.code,
            password: this.password,
            password_confirmation: this.password_confirmation,
          });
          // Redirect to login page or show success message
          this.$router.push({ name: 'Login' });
        } catch (error) {
          console.error(error.response.data.message);
        }
      },
    },
  };
  </script>
  
  <style scoped>
  .forget-password {
    max-width: 400px;
    margin: 2rem auto;
    padding: 2rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f9f9f9;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    font-family: Arial, sans-serif;
  }
  
  .forget-password h2 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #3C552D;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
  
  .form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: #3C552D;
  }
  
  .form-group input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
  }
  
  button {
    width: 100%;
    padding: 0.75rem;
    background-color: #CA7373;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-top: 1rem;
  }
  
  button:hover {
    background-color: #D7B26D;
  }
  
  button:active {
    background-color: #EEE2B5;
  }
  </style>
  
  
  