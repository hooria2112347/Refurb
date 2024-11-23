<template>
  <div class="signup">
    <h2>Create Your Account</h2>
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
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Signup',
  data() {
    return {
      form: {
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
        role: ''
      }
    };
  },
  methods: {
    handleSignup() {
      // Send form data to backend API
      axios.post('/api/signup', this.form)
        .then(response => {
          console.log('User registered:', response.data);
          // Redirect to the login page after successful signup
          this.$router.push('/login');  // Use Vue Router to navigate
        })
        .catch(error => {
          console.error('Error during registration:', error);
          if (error.response && error.response.data && error.response.data.errors) {
            // Display the first validation error
            const errors = error.response.data.errors;
            const firstErrorField = Object.keys(errors)[0];
            alert(errors[firstErrorField][0]);
          } else {
            alert('An error occurred during registration.');
          }
        });
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
</style>
