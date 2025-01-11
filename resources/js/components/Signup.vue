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
.signup {
  max-width: 400px;
  margin: 1rem auto;
  padding: 1.2rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #f9f9f9;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  font-family: Arial, sans-serif;
}

.signup h2 {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #3C552D;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group input,
.form-group select {
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

/* FADE TRANSITION */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

/* ERROR POPUP OVERLAY */
.error-popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5); /* semi-transparent black overlay */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999; /* on top of everything */
}

/* POPUP CONTENT */
.error-popup-content {
  background-color: #fff;
  padding: 1.5rem;
  border-radius: 8px;
  width: 90%;
  max-width: 400px;
  text-align: center;
}

.error-popup-content h3 {
  margin-top: 0;
  color: #B00000;
}

.error-popup-content ul {
  list-style: none;
  padding: 0;
  margin: 1rem 0;
  text-align: left;
}

.error-popup-content p {
  margin: 0.25rem 0;
  color: #B00000; /* error text color */
}

/* Close button styling inside popup */
.error-popup-content button {
  background-color: #CA7373;
  color: #fff;
  margin-top: 1rem;
  width: auto;
  cursor: pointer;
}
.error-popup-content button:hover {
  background-color: #D7B26D;
}
.error-popup-content button:active {
  background-color: #EEE2B5;
}
</style>
