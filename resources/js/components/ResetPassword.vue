<template>
  <div class="reset-password">
    <h2>Reset Password</h2>
    <form @submit.prevent="handleResetPassword">
      <div class="form-group">
        <input
          type="password"
          id="password"
          v-model="form.password"
          placeholder="Enter new password"
          required
        />
      </div>
      <div class="form-group">
        <input
          type="password"
          id="password_confirmation"
          v-model="form.password_confirmation"
          placeholder="Confirm Password"
          required
        />
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
      form: {
        password: '',
        password_confirmation: '',
        email: this.$route.query.email || '',
        code: this.$route.query.code || '',
      },
    };
  },
  created() {
    // Log email and code for debugging
    console.log('Email:', this.form.email);
    console.log('Code:', this.form.code);
  },
  methods: {
    handleResetPassword() {
      // Log data before sending for debugging
      console.log('Data to send:', this.form);

      // Send reset password data to the backend
      axios.post('/api/password/reset', this.form)
        .then(response => {
          console.log('Password reset successful:', response.data);
          // Redirect to login page upon success
          this.$router.push('/login');
        })
        .catch(error => {
          console.error('Error during password reset:', error);
          if (error.response && error.response.data && error.response.data.errors) {
            // Display the first validation error
            const errors = error.response.data.errors;
            const firstErrorField = Object.keys(errors)[0];
            alert(errors[firstErrorField][0]);
          } else if (error.response && error.response.data && error.response.data.message) {
            // Display general error message
            alert(error.response.data.message);
          } else {
            alert('An unexpected error occurred during password reset.');
          }
        });
    },
  },
};
</script>

<style scoped>
/* RESET PASSWORD COMPONENT STYLING */
.reset-password {
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
.reset-password h2 {
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
  .reset-password {
    padding: 1.5rem;
  }

  button {
    padding: 12px;
    font-size: 16px;
  }
}
</style>
