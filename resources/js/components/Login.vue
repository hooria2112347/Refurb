<template>
  <div class="login">
    <h2>Login</h2>
    <form @submit.prevent="handleLogin">
      <!-- Email -->
      <div class="form-group">
        <input
          type="email"
          id="email"
          v-model="form.email"
          placeholder="Email"
          required
        />
      </div>

      <!-- Password with toggle -->
      <div class="form-group password-wrapper">
        <input
          :type="passwordFieldType"
          id="password"
          v-model="form.password"
          placeholder="Password"
          required
        />
        <button
          type="button"
          class="toggle-button"
          @click="togglePasswordVisibility"
        >
          <!-- Show appropriate icon or text depending on current type -->
          <span v-if="passwordFieldType === 'password'">👁️</span>
          <span v-else>🙈</span>
        </button>
      </div>

      <!-- Error message -->
      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

      <!-- Submit -->
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
      errorMessage: "",
      // Controls whether password is visible or hidden
      passwordFieldType: "password"
    };
  },
  methods: {
    // Toggles the input field type between "password" and "text"
    togglePasswordVisibility() {
      this.passwordFieldType =
        this.passwordFieldType === "password" ? "text" : "password";
    },

    async handleLogin() {
      console.log("handleLogin triggered");
      try {
        const response = await axios.post("/api/login", this.form);
        console.log("Server response:", response.data);

        // Check for token
        if (response.data.access_token) {
          // Save user data
          localStorage.setItem(
            "userSession",
            JSON.stringify(response.data.user)
          );
          localStorage.setItem("access_token", response.data.access_token);

          // Update global state if needed
          this.$root.isLoggedIn = true;
          this.$root.userName = response.data.user.name;
          this.$root.userRole = response.data.user.role;

          // Redirect to the correct dashboard
          const role = response.data.user.role;
          if (role === "admin") {
            this.$router.push("/admin-dashboard");
          } else if (role === "artist") {
            this.$router.push("/artist-dashboard");
          } else if (role === "scrapSeller") {
            this.$router.push("/scrap-seller-dashboard");
          } else {
            // general or any other role
            this.$router.push("/");
          }
        } else {
          this.errorMessage = "Login failed: No access token returned.";
          localStorage.removeItem("userSession");
        }
      } catch (error) {
        if (error.response && error.response.data && error.response.data.message) {
          this.errorMessage = error.response.data.message;
        } else {
          this.errorMessage = "An error occurred. Please try again.";
        }
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

/* 
  Wrap the password field and toggle button in a container
  so they can be positioned cleanly.
*/
.password-wrapper {
  position: relative;
}

/* 
  Make sure the toggle button doesn't push content
  by placing it absolutely.
*/
.toggle-button {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem; /* Adjust icon/text size as desired */
  outline: none;
}

button[type="submit"] {
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

button[type="submit"]:hover {
  background-color: #D7B26D;
}

button[type="submit"]:active {
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
