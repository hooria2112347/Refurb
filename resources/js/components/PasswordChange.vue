<template>
  <div class="profile-container">
    <div class="sidebar">
      <ul class="menu">
        <li><router-link to="/account" active-class="active">Account</router-link></li>
        <li><router-link to="/password-change" active-class="active">Change Password</router-link></li>
        <li><router-link to="/billing-address" active-class="active">Billing Address</router-link></li>
        <li><router-link to="/shipping-address" active-class="active">Shipping Address</router-link></li>
        <li><router-link to="/my-orders" active-class="active">My Orders</router-link></li>
      </ul>
    </div>
    <div class="main-content">
      <h1>Change Password</h1>

      <!-- Success Message -->
      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
        <button @click="successMessage = ''" class="close">&times;</button>
      </div>

      <div class="form-container">
        <form @submit.prevent="changePassword">
          <div class="form-group">
            <label for="currentPassword">Current Password</label>
            <input
              type="password"
              id="currentPassword"
              v-model="form.currentPassword"
              required
            />
          </div>
          <div class="form-group">
            <label for="newPassword">New Password</label>
            <input
              type="password"
              id="newPassword"
              v-model="form.newPassword"
              required
            />
          </div>
          <div class="form-group">
            <label for="confirmPassword">Confirm New Password</label>
            <input
              type="password"
              id="confirmPassword"
              v-model="form.confirmPassword"
              required
            />
          </div>
          <button type="submit">Update Password</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      user: {
        name: "",
        email: "",
      },
      form: {
        currentPassword: "",
        newPassword: "",
        confirmPassword: "",
      },
      successMessage: "",
    };
  },
  methods: {
    fetchUserDetails() {
      axios
        .get("/api/user")
        .then((response) => {
          this.user = response.data;
        })
        .catch((error) => {
          console.error("Failed to fetch user details:", error);
          alert("Could not fetch user details. Please log in again.");
        });
    },
    changePassword() {
  axios
    .post(
      "/api/change-password",
      {
        currentPassword: this.form.currentPassword,
        newPassword: this.form.newPassword,
        confirmPassword: this.form.confirmPassword,
      },
      {
        withCredentials: true,
      },
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("userSession") 
            ? JSON.parse(localStorage.getItem("userSession")).token 
            : ""}`,
        },
      }
    )
    .then((response) => {
      this.successMessage = response.data.message || "Password updated successfully!";
      this.form.currentPassword = "";
      this.form.newPassword = "";
      this.form.confirmPassword = "";
    })
    .catch((error) => {
      alert(
        error.response?.data?.message || "Failed to change password. Please try again."
      );
    });
},
  },
  mounted() {
    this.fetchUserDetails();
  },
};
</script>

<style scoped>
  /* Layout Styling */
.profile-container {
  display: flex;
  background-color: #f9f9f9;
  font-family: Arial, sans-serif;
  align-items: flex-start; /* Ensures items align at the top */
}

/* Sidebar */
.sidebar {
  width: 25%;
  background-color: #ffffff;
  border-right: 1px solid #ddd;
  padding: 1.5rem;
  height: 88vh;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
}

.menu {
  list-style: none;
  padding: 0;
  margin: 0;
}

.menu li {
  margin: 0.8rem 0;
}

.menu li a {
  text-decoration: none;
  color: #333;
  font-size: 1rem;
  display: block;
  padding: 0.5rem 0;
}

.menu li.active a {
  font-weight: bold;
  color: #CA7373;
}

/* Main Content */
.main-content {
  flex: 1;
  padding: 2rem;
}

.main-content h1 {
  font-size: 1.8rem;
  margin-bottom: 1rem;
  color: #333;
}

.form-container {
  background-color: #ffffff;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
  color: #555;
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ccc;
  border-radius: 5px;
}

button {
  display: inline-block;
  width: 100%;
  padding: 0.75rem;
  background-color: #CA7373;
  color: #fff;
  font-size: 1rem;
  font-weight: bold;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
}

button:hover {
  background-color: #D7B26D;
}

/* Success Message */
.success-message {
  background-color: #4caf50;
  color: white;
  padding: 0.75rem;
  border-radius: 5px;
  margin-bottom: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.success-message .close {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
}
  </style>
