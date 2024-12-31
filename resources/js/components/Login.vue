<template>
  <div class="login">
    <h2>Login</h2>
    <form @submit.prevent="handleLogin">
      <div class="form-group">
        <input
          type="email"
          id="email"
          v-model="form.email"
          placeholder="Email"
          required
        />
      </div>

      <div class="form-group">
        <input
          type="password"
          id="password"
          v-model="form.password"
          placeholder="Password"
          required
        />
      </div>

      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
      <button type="submit">Login</button>
    </form>

    <p>
      <router-link to="/forget-password">Forgot Password?</router-link>
    </p>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "Login",
  data() {
    return {
      form: {
        email: "",
        password: ""
      },
      errorMessage: "" // For displaying error messages
    };
  },
  methods: {
    async handleLogin() {
      console.log("handleLogin triggered");
      try {
        const response = await axios.post('/api/login', this.form);

        console.log("Server response:", response.data);
        
        // Check if the server returned an `access_token`
        if (response.data.access_token) {
          // Save user data in localStorage
          localStorage.setItem('userSession', JSON.stringify(response.data.user));

          // Store the token for subsequent requests
          localStorage.setItem('access_token', response.data.access_token);

          // Update global state (if you use $root or a global store)
          this.$root.isLoggedIn = true;
          this.$root.userName = response.data.user.name;
          this.$root.userRole = response.data.user.role;

          // Redirect to home (or any other route)
          this.$router.push('/');
        } else {
          // If no token is returned, show an error or handle accordingly
          this.errorMessage = "Login failed: No access token returned.";
          localStorage.removeItem("userSession");
        }
      } catch (error) {
        // Handle error (e.g., network issues or server errors)
        if (error.response && error.response.data && error.response.data.message) {
          this.errorMessage = error.response.data.message;
        } else {
          this.errorMessage = "An error occurred. Please try again.";
        }

        // Clear any existing user session data
        localStorage.removeItem("userSession");
      }
    }
  }
};
</script>

<style scoped>
.login {
  max-width: 400px;
  margin: 2rem auto;
  padding: 2rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #f9f9f9;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  font-family: Arial, sans-serif;
}

.login h2 {
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

p {
  text-align: center;
  margin-top: 1rem;
}

a {
  color: #007bff;
  text-decoration: none;
  transition: color 0.3s;
}

a:hover {
  color: #0056b3;
}

.error-message {
  color: red;
  margin-top: 0.5rem;
  text-align: center;
}
</style>
