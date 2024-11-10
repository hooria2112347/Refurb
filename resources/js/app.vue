<template>
  <div id="app">
    <header>
      <div class="nav-container">
        <h1 class="logo">Refurb</h1>
        <nav class="main-nav">
          <router-link to="/">Home</router-link>
          
          <router-link v-if="isAdmin" to="/admin-feedback">Feedback Management</router-link>

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
              <router-link to="/profile">Profile</router-link>
              <router-link to="/saved-items">Saved Items</router-link>
              <router-link @click="logout">Logout</router-link>
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
  name: 'App',
  data() {
    return {
      isLoggedIn: false, // Tracks login status, default to not logged in
      userName: '', // Stores user name when logged in
      userRole: 'user' // Stores role, could be 'user' or 'admin'
    };
  },
  computed: {
    isAdmin() {
      return this.userRole === 'admin';
    }
  },
  methods: {
    logout() {
      // Clear login state and reset user information
      this.isLoggedIn = false;
      this.userName = '';
      this.userRole = 'user'; // Reset to default role

      // Redirect to Home or Login page
      this.$router.push('/');
    }
  },
  mounted() {
    // Check session or token to determine if user is logged in
    const session = localStorage.getItem('userSession');
    if (session) {
      // Simulate fetching user data
      const userData = JSON.parse(session);
      this.isLoggedIn = true;
      this.userName = userData.name;
      this.userRole = userData.role;
    }
  }
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
