<template>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <div id="app">
    <header>
      <div class="nav-container">
        <h1 class="logo">Refurb</h1>
        <nav class="main-nav">
          <router-link to="/" class="nav-link">Home</router-link>

           <!-- Cart icon with counter -->
          <div class="nav-link cart-link">
            <router-link v-if="isLoggedIn" to="/cart" class="cart-icon">
              <i class="fas fa-shopping-cart"></i>
              <span v-if="cartItemCount > 0" class="cart-count">{{ cartItemCount }}</span>
            </router-link>
            <div v-else @click="showCartLoginModal" class="cart-icon">
              <i class="fas fa-shopping-cart"></i>
              <span v-if="cartItemCount > 0" class="cart-count">{{ cartItemCount }}</span>
            </div>
          </div>

          <!-- Login/Signup links when not logged in -->
          <template v-if="!isLoggedIn">
            <router-link to="/login" class="nav-link">Login</router-link>
            <router-link to="/signup" class="nav-link">Sign Up</router-link>
          </template>

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
              <router-link to="/wishlist">My Wishlist</router-link>
              <a href="#" @click.prevent="logout">Logout</a>
            </div>
          </div>
        </nav>
      </div>
    </header>

    <!-- Routed component -->
    <router-view></router-view>
    
    <!-- Modal for cart login -->
    <div v-if="cartLoginModalVisible" class="modal-overlay">
      <div class="modal-content">
        <h2>Please Log In</h2>
        <p>You need to be logged in to view your cart.</p>
        <div class="modal-actions">
          <router-link to="/login" @click="closeCartLoginModal" class="primary-button">Log In</router-link>
          <button @click="closeCartLoginModal" class="secondary-button">Cancel</button>
        </div>
      </div>
    </div>
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
      isDropdownOpen: false,
      cartItemCount: 0,
      cartLoginModalVisible: false,
      cartUpdateInterval: null
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
    showCartLoginModal() {
      this.cartLoginModalVisible = true;
    },
    
    closeCartLoginModal() {
      this.cartLoginModalVisible = false;
    },
    
    async fetchCartCount() {
      console.log("Fetching cart count...");
      
      // For guests, you might want to retrieve from localStorage or sessionStorage
      // if your app supports guest carts
      if (!this.isLoggedIn) {
        const guestCart = localStorage.getItem("guestCart");
        if (guestCart) {
          try {
            const cartItems = JSON.parse(guestCart);
            this.cartItemCount = Array.isArray(cartItems) ? cartItems.length : 0;
          } catch (err) {
            console.error("Error parsing guest cart:", err);
            this.cartItemCount = 0;
          }
          return;
        }
        
        this.cartItemCount = 0;
        return;
      }
      
      // For logged-in users, fetch from API
      const token = localStorage.getItem("access_token");
      if (!token) {
        this.cartItemCount = 0;
        return;
      }
      
      try {
        const response = await fetch('http://127.0.0.1:8000/api/cart', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.ok) {
          const cartData = await response.json();
          console.log("Cart data fetched:", cartData);
          
          // Check the structure of the response to correctly count items
          if (Array.isArray(cartData)) {
            this.cartItemCount = cartData.length;
          } else if (cartData.items && Array.isArray(cartData.items)) {
            this.cartItemCount = cartData.items.length;
          } else if (typeof cartData === 'object' && Object.keys(cartData).length) {
            // If it's an object with keys representing cart items
            this.cartItemCount = Object.keys(cartData).length;
          } else {
            this.cartItemCount = 0;
          }
          
          console.log("Cart count updated:", this.cartItemCount);
          
          // Broadcast the update to other components
          this.broadcastCartUpdate(this.cartItemCount);
        } else {
          console.error("Error fetching cart:", response.status);
          this.cartItemCount = 0;
        }
      } catch (err) {
        console.error("Error fetching cart:", err);
        this.cartItemCount = 0;
      }
    },
    
    // New method to broadcast cart updates to other components
    broadcastCartUpdate(count) {
      // Using a custom event to notify all components
      const event = new CustomEvent('global-cart-updated', {
        detail: { count: count }
      });
      window.dispatchEvent(event);
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
      this.cartItemCount = 0;  // Reset cart count on logout
      this.$router.push('/').catch(err => console.error("Redirection error:", err));
      this.closeDropdown();
      
      // Broadcast cart reset
      this.broadcastCartUpdate(0);
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
    
    // Setup cart polling
    setupCartPolling() {
      // Clear any existing interval
      if (this.cartUpdateInterval) {
        clearInterval(this.cartUpdateInterval);
      }
      
      // Set up polling to fetch cart count periodically
      this.cartUpdateInterval = setInterval(() => {
        this.fetchCartCount();
      }, 5000); // Check every 5 seconds
    }
  },
  
  mounted() {
    this.checkSession();
    document.addEventListener('click', this.handleClickOutside);
    
    // Listen for both custom events
    window.addEventListener('cart-updated', () => {
      console.log("Cart updated event received");
      this.fetchCartCount();
    });
    
    // Listen for storage changes (for cross-tab synchronization)
    window.addEventListener('storage', (event) => {
      if (event.key === 'guestCart' || event.key === 'userSession') {
        this.fetchCartCount();
      }
    });
    
    // Initial cart fetch - make sure this happens AFTER checking the session
    setTimeout(() => {
      this.fetchCartCount();
    }, 100);
    
    // Setup polling for cart updates
    this.setupCartPolling();
    
    // Route change handler
    this.$router.beforeEach((to, from, next) => {
      if (this.cartLoginModalVisible) {
        this.closeCartLoginModal();
      }
      next();
    });
    
    // Fetch cart count when route changes
    this.$router.afterEach(() => {
      this.fetchCartCount();
    });
  },

  watch: {
    // Watch for login status changes to update cart count
    isLoggedIn(newValue) {
      if (newValue) {
        this.fetchCartCount();
        this.setupCartPolling(); // Reset polling on login change
      } else {
        // Clear polling if logged out
        if (this.cartUpdateInterval) {
          clearInterval(this.cartUpdateInterval);
          this.cartUpdateInterval = null;
        }
      }
    }
  },

  beforeDestroy() {
    document.removeEventListener('click', this.handleClickOutside);
    window.removeEventListener('cart-updated', this.fetchCartCount);
    window.removeEventListener('storage', this.fetchCartCount);
    
    // Clear the polling interval
    if (this.cartUpdateInterval) {
      clearInterval(this.cartUpdateInterval);
      this.cartUpdateInterval = null;
    }
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

/* Cart styles */
.cart-link {
  position: relative;
  display: flex;
  align-items: center;
}

.cart-icon {
  position: relative;
  font-size: 1.2rem;
  cursor: pointer;
  color: #3B1E54;
}

.nav-link.cart-link a {
  color: inherit;
  text-decoration: none;
}

.cart-icon:hover {
  color: #9B7EBD;
}

.cart-count {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #CA7373;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  font-size: 0.7rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: #fff;
  padding: 24px;
  border-radius: 8px;
  width: 90%;
  max-width: 400px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.modal-actions {
  display: flex;
  justify-content: space-around;
  margin-top: 20px;
}

.primary-button {
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  text-decoration: none;
}

.primary-button:hover {
  background-color: #EEEEEE;
}

.secondary-button {
  background-color: #ccc;
  color: #333;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.secondary-button:hover {
  background-color: #b3b3b3;
}

/* Responsive styles */
@media (max-width: 768px) {
  .nav-container {
    padding: 0.75rem 1.5rem;
  }

  .logo {
    font-size: 1.5rem;
  }

  .main-nav {
    gap: 1rem;
  }

  .nav-link {
    padding: 0.4rem;
    font-size: 0.9rem;
  }

  .dropbtn {
    padding: 0.4rem 0.8rem;
    font-size: 0.9rem;
  }

  .dropdown-content {
    min-width: 140px;
  }

  .dropdown-content a,
  .dropdown-content router-link {
    padding: 0.6rem 0.8rem;
    font-size: 0.9rem;
  }
}
</style>