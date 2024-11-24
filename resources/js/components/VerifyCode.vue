<template>
  <div class="verify-code">
    <h2>Enter Verification Code</h2>
    <form @submit.prevent="handleVerifyCode">
      <div class="form-group">
        <label for="code">Verification Code</label>
        <input type="text" id="code" v-model="code" required />
      </div>

      <button type="submit">Verify</button>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'VerifyCode',
  data() {
    return {
      code: '',
      email: this.$route.query.email || '',
    };
  },
  methods: {
    async handleVerifyCode() {
      try {
        await axios.post('/api/password/verify-code', {
          email: this.email,
          code: this.code,
        });
        // Redirect to reset password page
        this.$router.push({ name: 'ResetPassword', query: { email: this.email, code: this.code } });
      } catch (error) {
        console.error(error.response.data.message);
      }
    },
  },
};
</script>

<style scoped>
.verify-code {
  max-width: 400px;
  margin: 2rem auto;
  padding: 2rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #f9f9f9;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  font-family: Arial, sans-serif;
}

.verify-code h2 {
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
