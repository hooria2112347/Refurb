<template>
  <div class="signup">
    <h2>Create Your Account</h2>

    <!-- MAIN FORM -->
    <!-- note 'novalidate' to disable default HTML popup validations -->
    <form @submit.prevent="handleSignup" novalidate>
      <!-- NAME -->
      <div class="form-group">
        <input
          type="text"
          id="name"
          v-model="form.name"
          placeholder="Name"
          required
        />
        <!-- Inline error for name -->
        <p v-if="fieldErrors.name" class="error-message">{{ fieldErrors.name }}</p>
      </div>

      <!-- EMAIL -->
      <div class="form-group">
        <input
          type="email"
          id="email"
          v-model="form.email"
          placeholder="Email"
          required
        />
        <!-- Inline error for email -->
        <p v-if="fieldErrors.email" class="error-message">{{ fieldErrors.email }}</p>
      </div>

      <!-- PHONE -->
      <div class="form-group">
        <input
          type="tel"
          id="phone"
          v-model="form.phone"
          placeholder="Phone Number"
          required
          @input="handlePhoneInput"
        />
        <!-- Inline error for phone -->
        <p v-if="fieldErrors.phone" class="error-message">{{ fieldErrors.phone }}</p>
      </div>

      <!-- PASSWORD -->
      <div class="form-group">
        <input
          type="password"
          id="password"
          v-model="form.password"
          placeholder="Password"
          required
        />
        <!-- Inline error for password -->
        <p v-if="fieldErrors.password" class="error-message">{{ fieldErrors.password }}</p>
      </div>

      <!-- PASSWORD CONFIRMATION -->
      <div class="form-group">
        <input
          type="password"
          id="password_confirmation"
          v-model="form.password_confirmation"
          placeholder="Confirm Password"
          required
        />
        <!-- Inline error for password confirmation -->
        <p v-if="fieldErrors.password_confirmation" class="error-message">
          {{ fieldErrors.password_confirmation }}
        </p>
      </div>

      <!-- ROLE -->
      <div class="form-group">
        <select id="role" v-model="form.role" required>
          <option value="" disabled>Select your role</option>
          <option value="artist">Artist</option>
          <option value="scrapSeller">Scrap Seller</option>
        </select>
        <!-- Inline error for role -->
        <p v-if="fieldErrors.role" class="error-message">{{ fieldErrors.role }}</p>
      </div>

      <!-- A general error message (if any non-field-specific error arises) -->
      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

      <!-- SUBMIT BUTTON -->
      <button type="submit">Sign Up</button>
    </form>
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
      // Inline field errors for local + server validations
      fieldErrors: {
        name: "",
        email: "",
        phone: "",
        password: "",
        password_confirmation: "",
        role: ""
      },
      // A general error message (e.g., if server fails in some unexpected way)
      errorMessage: ""
    };
  },
  methods: {
    // Remove all non-digit characters from phone and limit to 11 digits
    handlePhoneInput(event) {
      let cleaned = event.target.value.replace(/[^\d]/g, "");
      if (cleaned.length > 11) {
        cleaned = cleaned.slice(0, 11);
      }
      this.form.phone = cleaned;
    },

    // Validate fields locally before sending to server
    validateFields() {
      // Clear previous errors
      this.fieldErrors = {
        name: "",
        email: "",
        phone: "",
        password: "",
        password_confirmation: "",
        role: ""
      };

      let isValid = true;

      // Check Name
      if (!this.form.name) {
        this.fieldErrors.name = "Name is required.";
        isValid = false;
      }

      // Check Email (simple regex or at least presence)
      if (!this.form.email) {
        this.fieldErrors.email = "Email is required.";
        isValid = false;
      } else {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!re.test(this.form.email)) {
          this.fieldErrors.email = "Please enter a valid email address.";
          isValid = false;
        }
      }

      // Check Phone
      if (!this.form.phone) {
        this.fieldErrors.phone = "Phone number is required.";
        isValid = false;
      }

      // Check Password
      if (!this.form.password) {
        this.fieldErrors.password = "Password is required.";
        isValid = false;
      }

      // Check Password Confirmation
      if (!this.form.password_confirmation) {
        this.fieldErrors.password_confirmation = "Please confirm your password.";
        isValid = false;
      } else if (this.form.password !== this.form.password_confirmation) {
        this.fieldErrors.password_confirmation = "Passwords do not match.";
        isValid = false;
      }

      // Check Role
      if (!this.form.role) {
        this.fieldErrors.role = "Role is required.";
        isValid = false;
      }

      return isValid;
    },

    handleSignup() {
      // First, do local (client-side) validation
      this.errorMessage = "";
      const isFormValid = this.validateFields();

      if (!isFormValid) {
        // If there are local validation errors, just stop here
        return;
      }

      // Otherwise, send data to the server
      axios
        .post("/api/signup", this.form)
        .then((response) => {
          console.log("User registered:", response.data);
          // Redirect to the login page after successful signup
          this.$router.push("/login");
        })
        .catch((error) => {
          console.error("Error during registration:", error);
          // Clear old field errors
          this.fieldErrors = {
            name: "",
            email: "",
            phone: "",
            password: "",
            password_confirmation: "",
            role: ""
          };

          if (error.response && error.response.data && error.response.data.errors) {
            // Server responded with validation errors
            const serverErrors = error.response.data.errors;
            // Map each field's errors to our fieldErrors
            for (const field in serverErrors) {
              if (this.fieldErrors[field] !== undefined) {
                // Join multiple server messages into a single string if needed
                this.fieldErrors[field] = serverErrors[field].join(" ");
              }
            }
          } else {
            // If it's some other kind of error, set a general error message
            this.errorMessage = "An error occurred during registration. Please try again.";
          }
        });
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

/* ERROR MESSAGE (inline) */
.error-message {
  color: #b00000;
  margin-top: 0.5rem;
  text-align: center;
  font-weight: bold;
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
