<template>
  <div class="forget-password">
    <h2>Forgot Password</h2>
    <form @submit.prevent="handleForgetPassword">
      <div class="form-group">
        <input
          type="email"
          id="email"
          v-model="email"
          placeholder="Enter your email"
          required
        />
      </div>

      <button type="submit">Submit</button>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ForgetPassword',
  data() {
    return {
      email: '',
    };
  },
  methods: {
    async handleForgetPassword() {
      try {
        await axios.post('/api/password/forgot', { email: this.email });
        this.$router.push({ name: 'VerifyCode', query: { email: this.email } });
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
