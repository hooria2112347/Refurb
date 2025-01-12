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
/* VERIFY CODE COMPONENT STYLING */
.verify-code {
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
.verify-code h2 {
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

.form-group label {
  display: block;
  font-weight: bold;
  margin-bottom: 0.5rem;
  color: #3a3d40;
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
  .verify-code {
    padding: 1.5rem;
  }

  button {
    padding: 12px;
    font-size: 16px;
  }
}
</style>
