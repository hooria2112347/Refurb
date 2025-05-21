<template>
  <div class="user-account-dashboard">
    <!-- Side navigation for Account -->
    <aside class="side-nav">
      <!-- Dynamic Overview route based on user.role -->
      <router-link :to="overviewRoute" exact-active-class="active">Overview</router-link>
      <router-link to="/order-history" exact-active-class="active">My Orders</router-link>
      <router-link to="/account" exact-active-class="active">Account</router-link>
      <router-link to="/password-change" exact-active-class="active">Change Password</router-link>
      <router-link to="/email-change" exact-active-class="active">Change Email</router-link>

    </aside>

    <section class="dashboard-content">
      <h2>Account Details</h2>
      <div class="form-container">
        <h3>My Information</h3>
        <div class="form-group">
          <label>Name:</label>
          <p>{{ user.name }}</p>
        </div>
        <div class="form-group">
          <label>Email:</label>
          <p>{{ user.email }}</p>
        </div>
        <!-- Optional: Display role-specific information -->
        <div class="form-group">
          <label>Role:</label>
          <p>{{ user.role }}</p>
        </div>
        <div class="form-group">
          <label>Phone no:</label>
          <p>{{ user.phone }}</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  name: "Account",
  data() {
    return {
      user: {
        name: "",
        email: "",
        role: "",
        phone: "",
        // Additional fields based on role
        shopName: "",      // For scrapSeller
        portfolio: "",     // For artist
      },
    };
  },
  computed: {
    isAuthenticated() {
      const session = localStorage.getItem("userSession");
      if (session) {
        try {
          const userData = JSON.parse(session);
          return userData;
        } catch (e) {
          console.error("Error parsing session data:", e);
          return null;
        }
      }
      return null;
    },
    overviewRoute() {
      // Dynamically return the appropriate Overview route based on user role
      switch (this.user.role) {
        case "artist":
          return "/artist-dashboard";
        case "scrapSeller":
          return "/scrap-seller-dashboard";
        case "admin":
          return "/admin-dashboard";
        default:
          return "/";
      }
    },
  },
  methods: {
    fetchUserDetails() {
      const session = localStorage.getItem("userSession");
      if (session) {
        try {
          const userData = JSON.parse(session);
          this.user.name = userData.name;
          this.user.email = userData.email;
          this.user.role = userData.role;
          this.user.phone = userData.phone;

          // Populate additional fields based on role
          if (userData.role === "scrapSeller") {
            this.user.shopName = userData.shopName || "N/A";
          }
          if (userData.role === "artist") {
            this.user.portfolio = userData.portfolio || "N/A";
          }
        } catch (e) {
          console.error("Error parsing session data:", e);
          alert("Failed to load user details. Please log in again.");
          this.$router.push("/login");
        }
      } else {
        alert("No active session found. Please log in.");
        this.$router.push("/login");
      }
    },
  },
  mounted() {
    const sessionData = this.isAuthenticated;
    if (sessionData) {
      this.fetchUserDetails();
    } else {
      this.$router.push("/login");
    }
  },
};
</script>

<style scoped>
/* MAIN WRAPPER FOR ACCOUNT DASHBOARD */
.user-account-dashboard {
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
  transition: background-color 0.3s, color 0.3s;
}

.side-nav a:hover {
  background-color: #D4BEE4; 
  color: #3B1E54;
}

.side-nav a.active {
  background-color: #9B7EBD;
  color: #3B1E54;
}

/* DASHBOARD CONTENT STYLING */
.dashboard-content {
  flex: 0.7;
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

.form-container h3 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: #3B1E54;
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

.form-group p {
  font-size: 16px;
  color: #3B1E54;
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
