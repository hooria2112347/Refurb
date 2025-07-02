<template>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <div id="app">
    <header>
      <div class="nav-container">
        <div class="brand-section">
          <h1 class="logo">Refurb</h1>
          <p class="slogan">Transforming waste into wonderful creations</p>
        </div>
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

    <!-- Main content area -->
    <main class="main-content">
      <router-view></router-view>
    </main>
    
    <!-- Footer - will appear on all pages -->
    <footer class="site-footer">
      <div class="footer-container">
        <div class="footer-content">
          <div class="footer-section">
            <h3 class="footer-title">Refurb</h3>
            <p class="footer-description">Transforming waste into wonderful creations</p>
          </div>
          
          <div class="footer-links">
            <router-link to="/">Home</router-link>
            <router-link to="/about">About Us</router-link>
            <router-link to="/faq">FAQ</router-link>
          </div>
        </div>
        
        <div class="footer-bottom">
          <p>&copy; 2025 Refurb. All rights reserved.</p>
        </div>
      </div>
    </footer>
    
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
      cartUpdateInterval: null,
      newsletterEmail: ""
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
    subscribeNewsletter() {
      if (!this.newsletterEmail) {
        alert("Please enter a valid email address");
        return;
      }
      
      // Add your newsletter subscription logic here
      console.log("Subscribing email:", this.newsletterEmail);
      // You can make an API call to your backend here
      
      // Reset the input
      this.newsletterEmail = "";
      alert("Thank you for subscribing to our newsletter!");
    },
    
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
/* App Layout */
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
  min-height: calc(100vh - 200px); /* Adjust based on header/footer height */
}

/* Enhanced Header styles */
.nav-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem ;
  background-color: #ffffff;
  border-bottom: 2px solid #eaeaea;
  position: relative;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.brand-section {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.logo {
  font-size: 2rem;
  color: #3B1E54;
  margin: 0;
  font-weight: bold;
  letter-spacing: -0.5px;
}

.slogan {
  font-size: 0.85rem;
  color: #9B7EBD;
  margin: 0;
  font-style: italic;
  font-weight: 400;
  letter-spacing: 0.3px;
}

.main-nav {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.nav-link {
  text-decoration: none;
  color: #3B1E54;
  font-weight: 500;
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
  border-radius: 6px;
  position: relative;
}

.nav-link:hover {
  color: #9B7EBD;
  background-color: rgba(155, 126, 189, 0.1);
  transform: translateY(-1px);
}

/* Dropdown menu styling */
.dropdown {
  position: relative;
}

.dropbtn {
  background-color: #D4BEE4;
  color: #3B1E54;
  padding: 0.75rem 1.25rem;
  font-size: 1rem;
  border: none;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  font-weight: 500;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

.dropbtn:hover {
  background-color: #EEEEEE;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
}

.arrow {
  margin-left: 0.75rem;
  font-size: 0.8rem;
  transition: transform 0.3s ease;
}

.dropdown:hover .arrow {
  transform: rotate(180deg);
}

.dropdown-content {
  position: absolute;
  right: 0;
  background-color: white;
  min-width: 180px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  z-index: 1;
  border-radius: 8px;
  overflow: hidden;
  margin-top: 0.75rem;
  opacity: 0;
  transform: translateY(-10px);
  transition: opacity 0.3s ease, transform 0.3s ease;
  border: 1px solid #eaeaea;
}

.dropdown-content a,
.dropdown-content router-link {
  color: #3C552D;
  padding: 1rem 1.25rem;
  display: block;
  text-align: left;
  text-decoration: none;
  transition: background-color 0.2s ease;
  font-weight: 500;
}

.dropdown-content a:hover,
.dropdown-content router-link:hover {
  background-color: #f8f9fa;
  color: #3B1E54;
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
  font-size: 1.3rem;
  cursor: pointer;
  color: #3B1E54;
  padding: 0.5rem;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.nav-link.cart-link a {
  color: inherit;
  text-decoration: none;
}

.cart-icon:hover {
  color: #9B7EBD;
  background-color: rgba(155, 126, 189, 0.1);
  transform: translateY(-1px);
}

.cart-count {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #CA7373;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Footer Styles */
.site-footer {
  background-color: #ffffff;
  border-top: 1px solid #eaeaea;
  color: #3B1E54;
  margin-top: auto;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.footer-section {
  flex: 1;
}

.footer-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0 0 0.5rem 0;
  color: #3B1E54;
}

.footer-description {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

.footer-links {
  display: flex;
  gap: 2rem;
  justify-content: flex-end;
}

.footer-links a {
  color: #3B1E54;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}

.footer-links a:hover {
  color: #9B7EBD;
}

.footer-bottom {
  text-align: center;
  padding-top: 1rem;
  border-top: 1px solid #eaeaea;
}

.footer-bottom p {
  margin: 0;
  color: #666;
  font-size: 0.85rem;
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

/* Responsive Design */
@media (max-width: 768px) {
  .nav-container {
    padding: 1rem 1.5rem;
    flex-direction: column;
    gap: 1rem;
  }

  .brand-section {
    text-align: center;
  }

  .logo {
    font-size: 1.8rem;
  }

  .slogan {
    font-size: 0.8rem;
  }

  .main-nav {
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
  }

  .nav-link {
    padding: 0.5rem 0.8rem;
    font-size: 0.9rem;
  }

  .dropbtn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
  }

  .dropdown-content {
    min-width: 160px;
  }

  .dropdown-content a,
  .dropdown-content router-link {
    padding: 0.8rem 1rem;
    font-size: 0.9rem;
  }

  /* Footer responsive */
  .footer-content {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .footer-links {
    justify-content: center;
    gap: 1rem;
  }
}

@media (max-width: 480px) {
  .nav-container {
    padding: 1rem;
  }

  .logo {
    font-size: 1.6rem;
  }

  .slogan {
    font-size: 0.75rem;
  }

  .main-nav {
    gap: 0.5rem;
  }

  .nav-link {
    font-size: 0.8rem;
    padding: 0.4rem 0.6rem;
  }

  .footer-container {
    padding: 1.5rem 1rem;
  }

  .footer-links {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>