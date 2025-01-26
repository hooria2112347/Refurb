<template>
  <div class="artist-dashboard">
    <!-- Side navigation for Change Password -->
    <aside class="side-nav">
      <router-link to="/artist-dashboard" exact-active-class="active">Overview</router-link>
      <router-link to="/account" exact-active-class="active">Account</router-link>
      <router-link to="/password-change" exact-active-class="active">Change Password</router-link>
    </aside>

    <section class="dashboard-content">
      <h2>Change Password</h2>

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
    </section>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "ChangePassword",
  data() {
    return {
      form: {
        currentPassword: "",
        newPassword: "",
        confirmPassword: "",
      },
      successMessage: "",
    };
  },
  computed: {
    isArtist() {
      const session = localStorage.getItem("userSession");
      if (session) {
        try {
          const userData = JSON.parse(session);
          return userData.role === "artist";
        } catch (e) {
          console.error("Error parsing session data:", e);
          return false;
        }
      }
      return false;
    },
  },
  methods: {
    changePassword() {
      // Basic front-end validation
      if (this.form.newPassword !== this.form.confirmPassword) {
        alert("New Password and Confirm Password do not match.");
        return;
      }

      // Make API call to change password
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
            headers: {
              Authorization: `Bearer ${
                localStorage.getItem("userSession")
                  ? JSON.parse(localStorage.getItem("userSession")).token
                  : ""
              }`,
            },
          }
        )
        .then((response) => {
          this.successMessage =
            response.data.message || "Password updated successfully!";
          // Clear form fields
          this.form.currentPassword = "";
          this.form.newPassword = "";
          this.form.confirmPassword = "";
        })
        .catch((error) => {
          console.error("Error changing password:", error);
          alert(
            error.response?.data?.message ||
              "Failed to change password. Please try again."
          );
        });
    },
  },
  mounted() {
    // Confirm user is Artist
    if (!this.isArtist) {
      this.$router.push("/login"); // Redirect if not an artist
    }
  },
};
</script>

<style scoped>
/* MAIN WRAPPER FOR CHANGE PASSWORD DASHBOARD */
.artist-dashboard {
  display: flex;
  background-color: #f7f9fc;
  min-height: 100vh;
}

/* SIDE NAVIGATION STYLING */
.side-nav {
  width: 220px;
  background-color: #ffffff;
  padding: 1rem;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
  border-right: 1px solid #e4e4e4;
  display: flex;
  flex-direction: column;
}

.side-nav a {
  display: block;
  margin-bottom: 0.8rem;
  padding: 10px;
  color: #3B1E54;
  text-decoration: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  transition: color 0.3s;
}

.side-nav a:hover {
  background-color: #9B7EBD; /* Optional: Keep hover background if desired */
  color: #3B1E54;
}

.side-nav a.active {
  /* Removed background-color to eliminate highlight */
  /* Optional: Change text color to indicate active link */
  color: #EEEEEE;
}

/* DASHBOARD CONTENT STYLING */
.dashboard-content {
  flex: 0.8;
  padding: 2rem;
  background-color: #f7f9fc;
}

.dashboard-content h2 {
  font-size: 28px;
  color: #3B1E54;
  margin-bottom: 1rem;
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
  color: #3B1E54;
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
  background-color: #D4BEE4;
  color: #3B1E54;
  font-size: 1rem;
  font-weight: bold;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
}

button:hover {
  background-color: #EEEEEE;
}

/* Success Message */
.success-message {
  background-color: #3B1E54;
  color: D4BEE4;
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

/* RESPONSIVE DESIGN */
@media screen and (max-width: 768px) {
  .side-nav {
    width: 180px;
  }

  .dashboard-content {
    padding: 1rem;
  }
}

@media screen and (max-width: 480px) {
  .side-nav {
    display: none; /* Consider implementing a hamburger menu for mobile */
  }

  .dashboard-content {
    padding: 1rem;
  }
}
</style>
