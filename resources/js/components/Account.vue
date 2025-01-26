<template>
  <div class="artist-dashboard">
    <!-- Side navigation for Account -->
    <aside class="side-nav">
      <router-link to="/artist-dashboard" exact-active-class="active">Overview</router-link>
      <router-link to="/account" exact-active-class="active">Account</router-link>
      <router-link to="/password-change" exact-active-class="active">Change Password</router-link>
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
      },
    };
  },
  computed: {
    isArtist() {
      const session = localStorage.getItem("userSession");
      if (session) {
        try {
          const userData = JSON.parse(session);
          return userData.role === 'artist';
        } catch (e) {
          console.error("Error parsing session data:", e);
          return false;
        }
      }
      return false;
    },
  },
  methods: {
    fetchUserDetails() {
      // Retrieve session data from localStorage
      const session = localStorage.getItem("userSession");
      if (session) {
        try {
          const userData = JSON.parse(session);
          this.user.name = userData.name;
          this.user.email = userData.email;
        } catch (e) {
          console.error("Error parsing session data:", e);
          alert("Failed to load user details. Please log in again.");
          this.$router.push("/login"); // Redirect to login on error
        }
      } else {
        alert("No active session found. Please log in.");
        this.$router.push("/login");
      }
    },
  },
  mounted() {
    // Confirm user is Artist
    if (this.isArtist) {
      this.fetchUserDetails(); // Load user details on component mount
    } else {
      this.$router.push("/login"); // Redirect if not an artist
    }
  },
};
</script>

<style scoped>
/* MAIN WRAPPER FOR ACCOUNT DASHBOARD */
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
  /* Remove background-color to eliminate highlight */
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
