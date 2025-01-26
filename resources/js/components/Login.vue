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
/* LOGIN COMPONENT STYLING */
.login {
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
.login h2 {
  text-align: center;
  margin-bottom: 1.8rem;
  font-size: 24px;
  font-weight: bold;
  color: #3B1E54;
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

/* PASSWORD TOGGLE BUTTON */
.password-wrapper {
  position: relative;
}

.toggle-button {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.4rem;
  color: #555;
}

/* ERROR MESSAGE */
.error-message {
  color: #b00000;
  margin-top: 0.5rem;
  text-align: center;
  font-weight: bold;
}

/* SUBMIT BUTTON */
button[type="submit"] {
  width: 100%;
  padding: 14px;
  font-size: 18px;
  font-weight: bold;
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-top: 1.5rem;
}

button[type="submit"]:hover {
  background-color: #EEEEEE;
}

button[type="submit"]:active {
  background-color: #426b5c;
}

/* LINK STYLING */
p {
  text-align: center;
  margin-top: 1rem;
}

a {
  color: #3B1E54;
  text-decoration: none;
  font-weight: bold;
}

a:hover {
  color: #9B7EBD;
}

/* RESPONSIVE DESIGN */
@media screen and (max-width: 480px) {
  .login {
    padding: 1.5rem;
  }

  button {
    padding: 12px;
    font-size: 16px;
  }
}
</style>
