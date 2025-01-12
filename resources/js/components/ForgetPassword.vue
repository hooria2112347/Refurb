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
/* FORGET PASSWORD COMPONENT STYLING */
.forget-password {
  max-width: 420px;
  margin: 2rem auto;
  padding: 2rem;
  border: 1px solid #e4e4e4;
  border-radius: 12px;
  background-color: #ffffff;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  font-family: 'Poppins', sans-serif;
}

/* HEADER STYLING */
.forget-password h2 {
  text-align: center;
  margin-bottom: 1.8rem;
  font-size: 24px;
  font-weight: bold;
  color: #3a3d40;
}

/* FORM GROUP STYLING */
.form-group {
  margin-bottom: 1.5rem;
}

.form-group input {
  width: 100%;
  padding: 12px 16px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 8px;
  background-color: #f9f9f9;
  transition: border-color 0.3s ease;
}

.form-group input:focus {
  border-color: #5d9b8b;
  outline: none;
  background-color: #ffffff;
}

/* SUBMIT BUTTON */
button {
  width: 100%;
  padding: 14px;
  font-size: 18px;
  font-weight: bold;
  background-color: #5d9b8b;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-top: 1.5rem;
}

button:hover {
  background-color: #76b29d;
}

button:active {
  background-color: #426b5c;
}

/* RESPONSIVE DESIGN */
@media screen and (max-width: 480px) {
  .forget-password {
    padding: 1.5rem;
  }

  button {
    padding: 12px;
    font-size: 16px;
  }
}
</style>
