<template>
  <div id="app">
    <header>
      <div class="nav-container">
        <h1 class="logo">Refurb</h1>
        <nav class="main-nav">
          <router-link to="/">Home</router-link>

          <!-- Admin-only feedback management -->
          <router-link v-if="isAdmin" to="/admin-feedback">Feedback Management</router-link>

          <!-- Scrap Seller-specific options -->
          <router-link v-if="isScrapSeller" to="/add-product">Add Product</router-link>
          <router-link v-if="isScrapSeller" to="/manage-products">Manage Products</router-link>

          <!-- Artist-specific options -->
          <router-link v-if="isArtist" to="/portfolio">My Portfolio</router-link>
          <router-link v-if="isArtist" to="/browse-scrap">Browse Scrap</router-link>

           <!-- General user and Scrap Seller-specific options -->
           <router-link v-if="isGeneralUser || isScrapSeller" to="/custom-request">
            Custom Request
          </router-link>


          <!-- Conditional display based on login status -->
          <div v-if="!isLoggedIn" class="dropdown">
            <button class="dropbtn">Guest ▼</button>
            <div class="dropdown-content">
              <router-link to="/login">Login</router-link>
              <router-link to="/signup">Sign Up</router-link>
            </div>
          </div>

          <!-- User dropdown when logged in -->
          <div v-else class="dropdown">
            <button class="dropbtn">{{ userName }} ▼</button>
            <div class="dropdown-content">
              <router-link to="/account">Profile</router-link>
              <router-link to="/saved-items">Saved Items</router-link>
              <a href="#" @click.prevent="logout">Logout</a>
            </div>
          </div>
        </nav>
      </div>
    </header>

    <!-- Displays the routed component based on the current route -->
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  name: "App",
  data() {
    return {
      isLoggedIn: false, // Tracks login status
      userName: "", // Stores user name
      userRole: "", // Stores user role, e.g., 'admin', 'scrapSeller', 'artist', 'general'
    };
  },
  computed: {
    isAdmin() {
      return this.userRole === "admin";
    },
    isScrapSeller() {
      return this.userRole === "scrapSeller";
    },
    isArtist() {
      return this.userRole === "artist";
    },
    isGeneralUser() {
      return this.userRole === "general"; // Add support for general users
    },
  },
  methods: {
    logout() {
      console.log("Logout button clicked");

      const token = localStorage.getItem('token');
      if (!token) {
        console.error("No token found in localStorage. Logging out anyway.");
        this.handleLogoutCleanup();
        return;
      }

      axios.post('/api/logout', {}, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      .then(response => {
        console.log("Logout API response:", response.data);

        if (response.data.success) {
          this.handleLogoutCleanup();
        } else {
          console.error("Logout failed:", response.data.message);
        }
      })
      .catch(error => {
        console.error("Logout API error:", error);

        // Cleanup even if API call fails
        this.handleLogoutCleanup();
      });
    },

    handleLogoutCleanup() {
      console.log("Handling logout cleanup...");

      // Clear local storage
      localStorage.removeItem('token');
      localStorage.removeItem('userSession');

      // Update global state
      this.isLoggedIn = false;
      this.userName = null;
      this.userRole = null;

      // Redirect to home page
      console.log("Redirecting to home...");
      this.$router.push('/').catch(err => console.error("Redirection error:", err));
    },

    checkSession() {
      // Retrieve session data from localStorage
      const session = localStorage.getItem("userSession");
      if (session) {
        try {
          const userData = JSON.parse(session);
          this.isLoggedIn = true;
          this.userName = userData.name;
          this.userRole = userData.role; // Ensure role is stored
        } catch (e) {
          console.error("Error parsing session data:", e);
          this.logout(); // Clear session on error
        }
      }
    },
  },
  mounted() {
    // Check session on page load
    this.checkSession();
  },
};

</script>

<style scoped>
/* Global styles for the App */
.nav-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 2rem;
  border-bottom: 1px solid #ddd;
}

.logo {
  font-size: 1.5rem;
  color: #3C552D;
  margin: 0;
}

.main-nav {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.main-nav a {
  text-decoration: none;
  color: #3C552D;
  font-weight: 500;
  padding: 0.5rem;
  transition: color 0.3s ease;
}

.main-nav a:hover {
  color: #0056b3;
}

/* Dropdown menu styling */
.dropdown {
  position: relative;
  display: inline-block;
}

.dropbtn {
  background-color: #CA7373;
  color: white;
  padding: 0.5rem 1rem;
  font-size: 1rem;
  border: none;
  cursor: pointer;
  border-radius: 5px;
  transition: background-color 0.3s ease;
}

.dropbtn:hover {
  background-color: #D7B26D;
}

.dropdown-content {
  display: none;
  position: absolute;
  right: 0;
  background-color: white;
  min-width: 160px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  z-index: 1;
  border-radius: 5px;
  overflow: hidden;
}

.dropdown-content a {
  color: #3C552D;
  padding: 0.5rem 1rem;
  display: block;
  text-align: left;
  text-decoration: none;
}

.dropdown-content a:hover {
  background-color: #f1f1f1;
}

.dropdown:hover .dropdown-content {
  display: block;
}
</style>
