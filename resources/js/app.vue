<template>
  <div id="app">
    <header>
      <div class="nav-container">
        <h1 class="logo">Refurb</h1>
        <nav class="main-nav">
          <router-link to="/" class="nav-link">Home</router-link>

          <!-- Conditional display based on login status -->
          <div v-if="!isLoggedIn" class="dropdown">
            <button 
              class="dropbtn" 
              @click="toggleDropdown" 
              aria-haspopup="true" 
              :aria-expanded="isDropdownOpen"
            >
              Guest <span class="arrow">&#x25BC;</span>
            </button>
            <div class="dropdown-content" v-if="isDropdownOpen">
              <router-link to="/login">Login</router-link>
              <router-link to="/signup">Sign Up</router-link>
            </div>
          </div>

          <!-- User dropdown when logged in -->
          <div v-else class="dropdown">
            <button 
              class="dropbtn" 
              @click="toggleDropdown" 
              aria-haspopup="true" 
              :aria-expanded="isDropdownOpen"
            >
              {{ userName }} <span class="arrow">&#x25BC;</span>
            </button>
            <div class="dropdown-content" v-if="isDropdownOpen">
              <router-link :to="dashboardRoute">Dashboard</router-link>
              <a href="#" @click.prevent="logout">Logout</a>
            </div>
          </div>
        </nav>

        <!-- Mobile menu toggle button -->
        <button 
          class="mobile-menu-toggle" 
          @click="toggleMobileMenu" 
          aria-label="Toggle navigation"
        >
          <span :class="['hamburger', { 'open': isMobileMenuOpen }]" ></span>
          <span :class="['hamburger', { 'open': isMobileMenuOpen }]" ></span>
          <span :class="['hamburger', { 'open': isMobileMenuOpen }]" ></span>
        </button>
      </div>

      <!-- Simplified Mobile menu -->
      <transition name="slide">
        <div v-if="isMobileMenuOpen" class="mobile-menu">
          <router-link to="/" class="mobile-nav-link" @click="closeMobileMenu">Home</router-link>
          <router-link v-if="!isLoggedIn" to="/login" class="mobile-nav-link" @click="closeMobileMenu">Login</router-link>
          <router-link v-if="!isLoggedIn" to="/signup" class="mobile-nav-link" @click="closeMobileMenu">Sign Up</router-link>
          <router-link v-else :to="dashboardRoute" class="mobile-nav-link" @click="closeMobileMenu">Dashboard</router-link>
          <a v-else href="#" class="mobile-nav-link" @click="logout">Logout</a>
        </div>
      </transition>
    </header>

    <!-- Routed component -->
    <router-view></router-view>
  </div>
</template>


<script>
import axios from "axios";

export default {
  name: "App",
  data() {
    return {
      isLoggedIn: false,
      userName: "",
      userRole: "",
      isMobileMenuOpen: false,
      isDropdownOpen: false,
    };
  },
  computed: {
    dashboardRoute() {
      switch (this.userRole) {
        case "artist":
          return "/artist-dashboard";
        case "admin":
          return "/admin-dashboard";
        case "scrapSeller":
          return "/scrap-seller-dashboard";
        case "general":
          return "/general-dashboard";
        default:
          return "/";
      }
    },
  },
  methods: {
    logout() {
      console.log("Logout button clicked");

      const token = localStorage.getItem('access_token');
      if (!token) {
        console.error("No token found. Logging out anyway.");
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
          // Optionally still logout even if API indicates failure
          this.handleLogoutCleanup();
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
      localStorage.removeItem('access_token');
      localStorage.removeItem('userSession');
      this.isLoggedIn = false;
      this.userName = "";
      this.userRole = "";
      this.$router.push('/').catch(err => console.error("Redirection error:", err));
      this.closeDropdown();
      this.closeMobileMenu();
    },
    checkSession() {
      const session = localStorage.getItem("userSession");
      if (session) {
        try {
          const userData = JSON.parse(session);
          this.isLoggedIn = true;
          this.userName = userData.name;
          this.userRole = userData.role;
        } catch (e) {
          console.error("Error parsing session data:", e);
          this.logout();
        }
      }
    },
    toggleMobileMenu() {
      this.isMobileMenuOpen = !this.isMobileMenuOpen;
      if (this.isMobileMenuOpen) {
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
      } else {
        document.body.style.overflow = '';
      }
    },
    closeMobileMenu() {
      this.isMobileMenuOpen = false;
      document.body.style.overflow = '';
    },
    toggleDropdown() {
      this.isDropdownOpen = !this.isDropdownOpen;
    },
    closeDropdown() {
      this.isDropdownOpen = false;
    },
    handleClickOutside(event) {
      const dropdown = this.$el.querySelector('.dropdown');
      if (dropdown && !dropdown.contains(event.target)) {
        this.closeDropdown();
      }
    },
  },
  mounted() {
    this.checkSession();
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeDestroy() {
    document.removeEventListener('click', this.handleClickOutside);
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
  background-color: #ffffff;
  border-bottom: 1px solid #eaeaea;
  position: relative;
}

.logo {
  font-size: 1.8rem;
  color: #3B1E54;
  margin: 0;
  font-weight: bold;
}

.main-nav {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.nav-link {
  text-decoration: none;
  color: #3B1E54;
  font-weight: 500;
  padding: 0.5rem;
  transition: color 0.3s ease;
}

.nav-link:hover {
  color: #9B7EBD;
}

/* Dropdown menu styling */
.dropdown {
  position: relative;
}

.dropbtn {
  background-color: #D4BEE4;
  color: #3B1E54;
  padding: 0.5rem 1rem;
  font-size: 1rem;
  border: none;
  cursor: pointer;
  border-radius: 5px;
  transition: background-color 0.3s ease;
  display: flex;
  align-items: center;
}

.dropbtn:hover {
  background-color: #EEEEEE;
}

.arrow {
  margin-left: 0.5rem;
  font-size: 0.8rem;
}

.dropdown-content {
  position: absolute;
  right: 0;
  background-color: white;
  min-width: 160px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  z-index: 1;
  border-radius: 5px;
  overflow: hidden;
  margin-top: 0.5rem;
  opacity: 0;
  transform: translateY(-10px);
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.dropdown-content a,
.dropdown-content router-link {
  color: #3C552D;
  padding: 0.75rem 1rem;
  display: block;
  text-align: left;
  text-decoration: none;
  transition: background-color 0.2s ease;
}

.dropdown-content a:hover,
.dropdown-content router-link:hover {
  background-color: #f1f1f1;
}

.dropdown:hover .dropdown-content {
  opacity: 1;
  transform: translateY(0);
}

/* Mobile menu toggle button */
.mobile-menu-toggle {
  display: none;
  flex-direction: column;
  justify-content: space-between;
  width: 24px;
  height: 18px;
  background: none;
  border: none;
  cursor: pointer;
}

.hamburger {
  width: 100%;
  height: 3px;
  background-color: #3C552D;
  border-radius: 2px;
  transition: all 0.3s ease;
  position: relative;
  transform-origin: 1px;
}

.hamburger.open:nth-child(1) {
  transform: rotate(45deg);
}

.hamburger.open:nth-child(2) {
  opacity: 0;
}

.hamburger.open:nth-child(3) {
  transform: rotate(-45deg);
}

/* Mobile menu styles */
.mobile-menu {
  display: flex;
  flex-direction: column;
  background-color: #ffffff;
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  z-index: 10;
  padding: 1rem 0;
}

.mobile-nav-link {
  padding: 0.75rem 2rem;
  text-decoration: none;
  color: #3C552D;
  transition: background-color 0.2s ease;
}

.mobile-nav-link:hover {
  background-color: #f1f1f1;
}

/* Slide-down animation for mobile menu */
.slide-enter-active, .slide-leave-active {
  transition: max-height 0.3s ease, opacity 0.3s ease;
}

.slide-enter, .slide-leave-to {
  max-height: 0;
  opacity: 0;
}

.slide-enter-to, .slide-leave {
  max-height: 500px; /* Adjust as needed */
  opacity: 1;
}

/* Responsive styles */
@media (max-width: 768px) {
  .main-nav {
    display: none;
  }

  .mobile-menu-toggle {
    display: flex;
  }

  /* Ensure mobile menu is hidden by default */
  .mobile-menu {
    display: none;
  }

  /* When mobile menu is open, display it */
  /* This is already handled by v-if and the transition */
}
</style>
