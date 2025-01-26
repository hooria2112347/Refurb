<template>
  <div class="signup">
    <h2>Create Your Account</h2>

    <!-- MAIN FORM -->
    <form @submit.prevent="handleSignup">
      <div class="form-group">
        <input
          type="text"
          id="name"
          v-model="form.name"
          placeholder="Name"
          required
        />
      </div>

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
          type="tel"
          id="phone"
          v-model="form.phone"
          placeholder="Phone Number"
          required
          @input="handlePhoneInput"
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

      <div class="form-group">
        <input
          type="password"
          id="password_confirmation"
          v-model="form.password_confirmation"
          placeholder="Confirm Password"
          required
        />
      </div>

      <div class="form-group">
        <select id="role" v-model="form.role" required>
          <option value="" disabled>Select your role</option>
          <option value="artist">Artist</option>
          <option value="scrapSeller">Scrap Seller</option>
        </select>
      </div>

      <button type="submit">Sign Up</button>
    </form>

    <!-- ERROR POPUP (displayed if hasErrors is true) -->
    <transition name="fade">
      <div v-if="hasErrors" class="error-popup-overlay">
        <div class="error-popup-content">
          <h3>Registration Errors</h3>
          <ul>
            <!-- Loop over each field's array of messages -->
            <li v-for="(messages, field) in validationErrors" :key="field">
              <p v-for="(msg, idx) in messages" :key="idx">- {{ msg }}</p>
            </li>
          </ul>
          <button @click="closeErrorPopup">Close</button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "Signup",
  data() {
    return {
      form: {
        name: "",
        email: "",
        phone: "",
        password: "",
        password_confirmation: "",
        role: ""
      },
      validationErrors: {}, // Store server errors
      showErrorPopup: false // Controls whether the popup is visible
    };
  },
  computed: {
    // If there is at least one error in validationErrors, show the popup
    hasErrors() {
      return this.showErrorPopup && Object.keys(this.validationErrors).length > 0;
    }
  },
  methods: {
    // Remove all non-digit characters and limit to 11 digits
    handlePhoneInput(event) {
      let cleaned = event.target.value.replace(/[^\d]/g, "");
      if (cleaned.length > 11) {
        cleaned = cleaned.slice(0, 11);
      }
      this.form.phone = cleaned;
    },

    handleSignup() {
      // Clear old errors before a new request
      this.validationErrors = {};
      this.showErrorPopup = false;

      // Send form data to backend API
      axios
        .post("/api/signup", this.form)
        .then((response) => {
          console.log("User registered:", response.data);
          // Redirect to the login page after successful signup
          this.$router.push("/login");
        })
        .catch((error) => {
          console.error("Error during registration:", error);
          if (error.response && error.response.data && error.response.data.errors) {
            // Store all validation errors
            this.validationErrors = error.response.data.errors;
            // Show the popup
            this.showErrorPopup = true;
          } else {
            // If it's some other kind of error, show a generic message
            alert("An error occurred during registration.");
          }
        });
    },

    closeErrorPopup() {
      this.showErrorPopup = false;
    }
  }
};
</script>

<style scoped>
/* GLOBAL STYLING */
.signup {
  max-width: 420px;
  margin: 2rem auto;
  padding: 1.8rem;
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  font-family: 'Poppins', sans-serif;
  border: 1px solid #e4e4e4;
}

/* HEADER STYLING */
.signup h2 {
  text-align: center;
  margin-bottom: 2rem;
  font-size: 24px;
  font-weight: bold;
  color: #3B1E54;
}

/* FORM GROUP STYLING */
.form-group {
  margin-bottom: 1.5rem;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 12px 16px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 8px;
  background-color: #f9f9f9;
  transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
  border-color: #5d9b8b;
  outline: none;
  background-color: #ffffff;
}

/* BUTTON STYLING */
button {
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

button:hover {
  background-color: #D4BEE4;
}

button:active {
  background-color: #9B7EBD;
}

/* ERROR POPUP OVERLAY */
.error-popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

/* ERROR POPUP CONTENT */
.error-popup-content {
  background-color: #ffffff;
  padding: 2rem;
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
  text-align: center;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.error-popup-content h3 {
  color: #b00000;
  font-size: 22px;
  margin-bottom: 1rem;
}

.error-popup-content ul {
  list-style: none;
  padding: 0;
  margin: 1rem 0;
}

.error-popup-content li {
  color: #b00000;
  font-size: 16px;
  margin-bottom: 0.5rem;
}

.error-popup-content button {
  background-color: #b00000;
  color: #ffffff;
  padding: 12px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 1.5rem;
  transition: background-color 0.3s ease;
}

.error-popup-content button:hover {
  background-color: #d9534f;
}

.error-popup-content button:active {
  background-color: #a52a2a;
}

/* RESPONSIVE DESIGN */
@media screen and (max-width: 480px) {
  .signup {
    padding: 1.2rem;
  }

  button {
    padding: 12px;
    font-size: 16px;
  }
}
</style>
