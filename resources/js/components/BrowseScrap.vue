<template>
  <div class="browse-container">
    <!-- Recommendations Banner (Compact Version) -->
    <div class="recommendations-banner">
      <div class="recommendations-header">
        <h3>{{ recommendationTitle }}</h3>
        <div class="banner-controls">
          <button class="control-btn" @click="prevRecommendation" :disabled="currentRecommendationIndex === 0">‹</button>
          <button class="control-btn" @click="nextRecommendation" :disabled="currentRecommendationIndex >= recommendations.length - visibleRecommendations">›</button>
        </div>
      </div>
      <div class="recommendations-slider">
        <div class="recommendations-track" :style="{ transform: `translateX(-${currentRecommendationIndex * (100 / visibleRecommendations)}%)` }">
          <div 
            v-for="product in recommendations" 
            :key="product.id" 
            class="recommendation-card"
            @click="viewProductDetails(product)"
          >
            <div class="recommendation-tag">{{ getRecommendationReason(product) }}</div>
            <div class="recommendation-image">
              <img 
                :src="product.images && product.images.length ? product.images[0] : 'https://via.placeholder.com/150'" 
                :alt="product.name"
              />
            </div>
            <div class="recommendation-details">
              <h4 class="product-name">{{ product.name }}</h4>
              <span class="recommendation-price">{{ product.price }} PKR</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="search-filter-bar">
      <div class="search-category-container">
        <select v-model="selectedCategory" @change="filterProducts" class="category-select">
          <option value="">All Categories</option>
          <option v-for="category in categories" :key="category" :value="category">
            {{ category }}
          </option>
        </select>
        
        <div class="search-input">
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Search products..." 
            @input="filterProducts"
          />
          <span class="search-icon">🔍</span>
        </div>
      </div>
    </div>
    
    <!-- Active Filters -->
    <div v-if="hasActiveFilters" class="active-filters">
      <span class="filters-label">Active filters:</span>
      <div class="filter-tags">
        <div v-if="searchQuery" class="filter-tag">
          "{{ searchQuery }}" <span @click="clearSearch">×</span>
        </div>
        <div v-if="selectedCategory" class="filter-tag">
          {{ selectedCategory }} <span @click="clearCategory">×</span>
        </div>
      </div>
      <button class="clear-all" @click="resetFilters">Clear All</button>
    </div>

    <!-- Loading and Error States -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <span>Loading products...</span>
    </div>
    <div v-else-if="error" class="error-state">{{ error }}</div>

    <!-- Products Display -->
    <div v-else class="products-content">
      <!-- Result Count -->
      <div class="result-count">
        {{ filteredProducts.length }} products found
      </div>
      
      <!-- Products Grid -->
      <div v-if="filteredProducts.length" class="products-grid">
        <div 
          v-for="product in paginatedProducts" 
          :key="product.id" 
          class="product-card" 
          @click="viewProductDetails(product)"
        >
          <!-- Product Image with Overlay Actions -->
          <div class="image-container">
            <img 
              :src="product.images && product.images.length ? product.images[0] : 'https://via.placeholder.com/200'" 
              alt="Product Image" 
              class="product-image"
            />
            <div class="card-actions">
              <button 
                class="wishlist-btn" 
                :class="{ active: isInWishlist(product.id) }"
                @click.stop="toggleWishlist(product)"
              >
                ♥
              </button>
              <button class="cart-btn" @click.stop="addToCart(product)">
                +
              </button>
            </div>
          </div>

          <!-- Product Info -->
          <div class="product-info">
            <h3>{{ product.name }}</h3>
            <div class="product-meta">
              <span class="category">{{ product.category || 'Uncategorized' }}</span>
              <span class="price1">{{ product.price }} PKR</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination Controls -->
      <div v-if="filteredProducts.length > 0" class="pagination-controls">
        <button 
          class="pagination-btn prev" 
          :disabled="currentPage === 1"
          @click="prevPage"
        >
          ← Prev
        </button>
        
        <div class="page-info">
          Page {{ currentPage }} of {{ totalPages }}
        </div>
        
        <button 
          class="pagination-btn next" 
          :disabled="currentPage === totalPages"
          @click="nextPage"
        >
          Next →
        </button>
      </div>

      <!-- No Products Message -->
      <div v-else class="no-products">
        <p>No products found. Try adjusting your filters.</p>
        <button @click="resetFilters">Show All Products</button>
      </div>
    </div>

    <!-- Login Modal -->
    <div v-if="loginModalVisible" class="modal-overlay">
      <div class="modal-content">
        <h2>Please Log In</h2>
        <p>You need to be logged in to perform this action.</p>
        <div class="modal-actions">
          <router-link to="/login" class="primary-button">Log In</router-link>
          <button @click="closeLoginModal" class="secondary-button">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="showCartNotification" class="toast-notification" :class="{ 'show': showCartNotification }">
      <div class="toast-content">✓ {{ cartNotificationMessage }}</div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      products: [],
      filteredProducts: [],
      loading: false,
      error: null,
      wishlistItems: [],
      loginModalVisible: false,
      showCartNotification: false,
      cartNotificationMessage: "",
      cartCount: 0,
      // Search and filter data
      searchQuery: "",
      selectedCategory: "",
      categories: [],
      
      // Pagination data
      currentPage: 1,
      productsPerPage: 10,
      
      // Recommendation system data
      recommendations: [],
      currentRecommendationIndex: 0,
      visibleRecommendations: 4,
      recommendationTitle: "Recommended for You",
      recommendationsLoading: false,
      recommendationReasons: {} // Store reasons for each product
    };
  },
  computed: {
    hasActiveFilters() {
      return this.searchQuery || this.selectedCategory;
    },
    totalPages() {
      return Math.ceil(this.filteredProducts.length / this.productsPerPage);
    },
    paginatedProducts() {
      const startIndex = (this.currentPage - 1) * this.productsPerPage;
      const endIndex = startIndex + this.productsPerPage;
      return this.filteredProducts.slice(startIndex, endIndex);
    }
  },

  methods: {
    // Recommendation navigation methods
    nextRecommendation() {
      if (this.currentRecommendationIndex < this.recommendations.length - this.visibleRecommendations) {
        this.currentRecommendationIndex++;
      }
    },
    
    prevRecommendation() {
      if (this.currentRecommendationIndex > 0) {
        this.currentRecommendationIndex--;
      }
    },

    async fetchRecommendations() {
      const token = localStorage.getItem("access_token");
      
      if (!token) {
        console.log("No token found, showing fallback recommendations");
        this.generateFallbackRecommendations();
        return;
      }
      
      this.recommendationsLoading = true;
      
      try {
        console.log("Fetching recommendations with token:", token.substring(0, 10) + "...");
        
        const response = await fetch('http://127.0.0.1:8000/api/recommendations', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });
        
        console.log("Response status:", response.status);
        
        if (response.ok) {
          const data = await response.json();
          console.log("Recommendations data received:", data);
          
          // Check if it's the "no recommendations yet" message
          if (data.message && data.recommendations) {
            this.recommendationTitle = "Popular Products";
            this.recommendations = data.recommendations;
            console.log("Using popular products as recommendations");
          } else if (Array.isArray(data)) {
            // We have real recommendations
            this.recommendations = data;
            this.recommendationTitle = "Recommended for You";
            console.log("Using personalized recommendations");
          } else {
            console.log("Unexpected data format, using fallback");
            this.generateFallbackRecommendations();
          }
          
          // Set recommendation reasons from backend
          this.recommendations.forEach(product => {
            if (product.recommendation_reason) {
              this.recommendationReasons[product.product_id || product.id] = product.recommendation_reason;
            }
          });
          
          console.log("Final recommendations:", this.recommendations);
          console.log("Recommendation reasons:", this.recommendationReasons);
          
        } else {
          const errorText = await response.text();
          console.error("HTTP Error:", response.status, errorText);
          
          if (response.status === 401) {
            console.log("Unauthorized - removing token");
            localStorage.removeItem("access_token");
            this.generateFallbackRecommendations();
          } else if (response.status === 404) {
            console.error("Recommendations endpoint not found - check your routes");
            this.generateFallbackRecommendations();
          } else {
            console.error("Failed to fetch recommendations - HTTP", response.status);
            this.generateFallbackRecommendations();
          }
        }
      } catch (err) {
        console.error("Network error fetching recommendations:", err);
        console.error("Error details:", err.message);
        this.generateFallbackRecommendations();
      } finally {
        this.recommendationsLoading = false;
      }
    },

    // Updated generateFallbackRecommendations with better logging
    generateFallbackRecommendations() {
      if (!this.products || this.products.length === 0) {
        console.log("No products available for fallback recommendations");
        return;
      }

      console.log("Generating fallback recommendations from", this.products.length, "products");
      
      // Generate random recommendations when no user data is available
      const shuffled = [...this.products].sort(() => 0.5 - Math.random());
      this.recommendations = shuffled.slice(0, 8);
      
      const token = localStorage.getItem("access_token");
      if (!token) {
        this.recommendationTitle = "Featured Products";
      } else {
        this.recommendationTitle = "Popular Products";
      }
      
      // Set default reasons for fallback recommendations
      this.recommendations.forEach(product => {
        this.recommendationReasons[product.product_id || product.id] = "Featured";
      });
      
      console.log("Fallback recommendations set:", this.recommendations.length, "products");
    },

    getRecommendationReason(product) {
      const productId = product.product_id || product.id;
      return this.recommendationReasons[productId] || "Recommended";
    },

    // Updated to handle both product_id and id fields
    viewProductDetails(product) {
      const productId = product.product_id || product.id;
      this.$router.push({ name: 'product-details', params: { id: productId } });
    },

    // FIXED: Single addToCart method that handles both product_id and id fields
    async addToCart(product) {
      const token = localStorage.getItem("access_token");
      if (!token) {
        this.loginModalVisible = true;
        return;
      }

      const productId = product.product_id || product.id;
      
      try {
        const response = await fetch(`http://127.0.0.1:8000/api/cart/add/${productId}`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            quantity: 1
          })
        });
        
        const data = await response.json();
        
        if (response.ok) {
          this.fetchCartCount();
          this.cartNotificationMessage = `Added to cart: ${product.name}`;
          this.showCartNotification = true;
          
          setTimeout(() => {
            this.showCartNotification = false;
          }, 3000);
        } else {
          console.error('Cart error:', data);
          this.error = data.error || data.message || 'Failed to add to cart';
          
          if (response.status === 401) {
            this.loginModalVisible = true;
          }
        }
      } catch (err) {
        console.error("Error adding to cart:", err);
        this.error = "Network error. Please check your connection and try again.";
      }
    },
    
    async fetchCartCount() {
      const token = localStorage.getItem("access_token");
      if (!token) {
        this.cartCount = 0;
        return;
      }
      
      try {
        const response = await fetch('http://127.0.0.1:8000/api/cart', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });
        
        if (response.ok) {
          const data = await response.json();
          if (Array.isArray(data)) {
            this.cartCount = data.reduce((total, item) => total + (item.quantity || 0), 0);
          } else if (data.items && Array.isArray(data.items)) {
            this.cartCount = data.items.reduce((total, item) => total + (item.quantity || 0), 0);
          } else {
            this.cartCount = 0;
          }
        } else if (response.status === 401) {
          this.cartCount = 0;
          localStorage.removeItem("access_token");
        } else {
          console.error("Failed to fetch cart count");
          this.cartCount = 0;
        }
      } catch (err) {
        console.error("Error fetching cart:", err);
        this.cartCount = 0;
      }
    },

    // Updated to handle both product_id and id fields
    async toggleWishlist(product) {
      const token = localStorage.getItem("access_token");
      if (!token) {
        this.loginModalVisible = true;
        return;
      }

      const productId = product.product_id || product.id;
      
      try {
        if (this.isInWishlist(productId)) {
          const response = await fetch(`http://127.0.0.1:8000/api/wishlist/remove/${productId}`, {
            method: 'DELETE',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Accept': 'application/json'
            }
          });
          
          if (response.ok) {
            this.wishlistItems = this.wishlistItems.filter(id => id !== productId);
          } else if (response.status === 401) {
            this.loginModalVisible = true;
          }
        } else {
          const response = await fetch(`http://127.0.0.1:8000/api/wishlist/add/${productId}`, {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Accept': 'application/json'
            }
          });
          
          if (response.ok) {
            this.wishlistItems.push(productId);
          } else if (response.status === 401) {
            this.loginModalVisible = true;
          }
        }
      } catch (err) {
        console.error("Error updating wishlist:", err);
      }
    },

    // Debug method to test recommendations
    async testRecommendations() {
      const token = localStorage.getItem("access_token");
      if (!token) {
        console.log("No token for testing");
        return;
      }

      try {
        // Test the debug endpoint
        const response = await fetch('http://127.0.0.1:8000/api/recommendations/debug', {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.ok) {
          const data = await response.json();
          console.log("Debug data:", data);
        } else {
          console.error("Debug endpoint failed:", response.status);
        }
      } catch (err) {
        console.error("Error testing recommendations:", err);
      }
    },

    // Pagination methods
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    },
    
    async fetchProducts() {
      this.loading = true;
      this.error = null;
      try {
        const response = await fetch('http://127.0.0.1:8000/api/products/all');
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        this.products = data;
        this.filteredProducts = [...data];
        
        this.extractCategories();
        
        // Fetch recommendations after products are loaded
        await this.fetchRecommendations();
        
      } catch (err) {
        this.error = 'Failed to fetch products.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    
    extractCategories() {
      const categorySet = new Set();
      this.products.forEach(product => {
        if (product.category) {
          categorySet.add(product.category);
        }
      });
      this.categories = Array.from(categorySet).sort();
    },
    
    filterProducts() {
      this.filteredProducts = this.products.filter(product => {
        const matchesSearch = !this.searchQuery || 
          product.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          (product.description && product.description.toLowerCase().includes(this.searchQuery.toLowerCase()));
        
        const matchesCategory = !this.selectedCategory || 
          product.category === this.selectedCategory;
        
        return matchesSearch && matchesCategory;
      });
      
      this.currentPage = 1;
    },
    
    resetFilters() {
      this.searchQuery = "";
      this.selectedCategory = "";
      this.filterProducts();
    },
    
    clearSearch() {
      this.searchQuery = "";
      this.filterProducts();
    },
    
    clearCategory() {
      this.selectedCategory = "";
      this.filterProducts();
    },
    
    async fetchWishlist() {
      const token = localStorage.getItem("access_token");
      if (!token) return;
      
      try {
        const response = await fetch('http://127.0.0.1:8000/api/wishlist', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.ok) {
          const data = await response.json();
          this.wishlistItems = data.map(item => item.id);
        }
      } catch (err) {
        console.error("Error fetching wishlist:", err);
      }
    }, 
    
    isInWishlist(productId) {
      return this.wishlistItems.includes(productId);
    },
    
    closeLoginModal() {
      this.loginModalVisible = false;
    },
  },
  mounted() {
    this.fetchProducts();
    this.fetchWishlist();
    this.fetchCartCount();
    
    // Uncomment this line to test the debug endpoint
    this.testRecommendations();
  },
};
</script>
<style scoped>
:root {
  --primary-color: #9b7ebd;
  --primary-dark: #7a629a;
  --primary-light: #d4bee4;
  --secondary-color: #3b1e54;
  --accent-color: #e6d7f3;
  --background-color: #ffffff;
  --surface-color: #f8f9fa;
  --text-primary: #2c3e50;
  --text-secondary: #6c757d;
  --text-muted: #8a8a8a;
  --border-color: #e9ecef;
  --border-light: #f1f3f4;
  --success-color: #28a745;
  --error-color: #dc3545;
  --warning-color: #ffc107;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
}

.browse-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  background-color: var(--background-color);
}

/* Enhanced Recommendations Banner with Colors */
.recommendations-banner {
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
  border: 1px solid var(--primary-light);
  border-radius: 10px;
  padding: 1rem;
  margin-bottom: 1rem;
  box-shadow: var(--shadow-lg);
  position: relative;
  overflow: hidden;
}

.recommendations-banner::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.1) 75%);
  background-size: 20px 20px;
  animation: movePattern 10s linear infinite;
  pointer-events: none;
}

@keyframes movePattern {
  0% { background-position: 0 0; }
  100% { background-position: 20px 20px; }
}

.recommendations-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
  position: relative;
  z-index: 2;
}

.recommendations-header h3 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--background-color);
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
  letter-spacing: 0.5px;
}

.banner-controls {
  display: flex;
  gap: 0.5rem;
}

.control-btn {
  width: 36px;
  height: 36px;
  border: 2px solid rgba(255,255,255,0.3);
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(10px);
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--background-color);
  transition: all 0.3s ease;
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.control-btn:hover:not(:disabled) {
  background: rgba(255,255,255,0.25);
  border-color: rgba(255,255,255,0.5);
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}

.control-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
  transform: none;
}

/* Auto-moving Banner Animation */
.recommendations-slider {
  overflow: hidden;
  height: 200px;
  border-radius: 8px;
  position: relative;
  z-index: 2;
}

.recommendations-track {
  display: flex;
  height: 100%;
  gap: 1rem;
  animation: autoSlide 20s linear infinite;
  transition: transform 0.3s ease;
}

.recommendations-track:hover {
  animation-play-state: paused;
}

@keyframes autoSlide {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}

.recommendation-card {
  flex: 0 0 calc(25% - 0.75rem);
  display: flex;
  flex-direction: column;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(10px);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid rgba(255,255,255,0.2);
  overflow: hidden;
  position: relative;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.recommendation-card:hover {
  transform: translateY(-4px) scale(1.02);
  box-shadow: 0 8px 24px rgba(0,0,0,0.25);
  border-color: rgba(255,255,255,0.4);
  background: rgba(255,255,255,1);
}

.recommendation-tag {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: linear-gradient(135deg, var(--primary-light), var(--accent-color));
  color: var(--secondary-color);
  padding: 0.3rem 0.6rem;
  border-radius: 15px;
  font-size: 0.65rem;
  font-weight: 600;
  z-index: 3;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  max-width: 70%;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  animation: pulse 2s ease-in-out infinite;
  border: 1px solid rgba(255,255,255,0.8);
  color: white;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.recommendation-image {
  width: 100%;
  height: 120px;
  flex-shrink: 0;
  position: relative;
  overflow: hidden;
}

.recommendation-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
  filter: brightness(1.05) saturate(1.1);
}

.recommendation-card:hover .recommendation-image img {
  transform: scale(1.08) rotate(1deg);
}

.recommendation-details {
  padding: 0.6rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  flex: 1;
  background: rgba(255,255,255,0.95);
}

.product-name {
  margin: 0 0 0.5rem 0;
  font-size: 0.8rem !important;
  font-weight: 700 !important;
  color: var(--secondary-color) !important;
  line-height: 1.3 !important;
  text-align: left;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.recommendation-price {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--background-color);
  margin-top: auto;
  padding: 0.3rem 0.6rem;
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  border-radius: 6px;
  text-align: center;
  width: fit-content;
  align-self: flex-end;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  transition: all 0.2s ease;
}

.recommendation-card:hover .recommendation-price {
  transform: scale(1.05);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Search and Filter Bar */
.search-filter-bar {
  /* margin-bottom: 1rem; */
  background-color: var(--surface-color);
  /* border-radius: 6px; */
  /* padding: 1rem; */
  border: 1px solid var(--border-light);
}

.search-category-container {
  display: flex;
  align-items: center;
  /* gap: 1rem; */
  width: 100%;
}

.search-input {
  position: relative;
  flex: 1;
}

.search-input input {
  width: 100%;
  /* padding: 0.75rem 1rem; */
  /* padding-right: 2.5rem; */
  border: 1px solid var(--border-color);
  border-radius: 6px;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  background: var(--background-color);
}

.search-input input:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 2px rgba(155, 126, 189, 0.1);
}

.search-icon {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
  font-size: 1rem;
}

.category-select {
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background-color: var(--background-color);
  font-size: 0.9rem;
  min-width: 150px;
  cursor: pointer;
  transition: border-color 0.2s ease;
}

.category-select:focus {
  border-color: var(--primary-color);
  outline: none;
}

/* Active Filters */
.active-filters {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
  background-color: var(--accent-color);
  border-radius: 6px;
  font-size: 0.85rem;
  border: 1px solid var(--primary-light);
}

.filters-label {
  color: var(--text-secondary);
  font-weight: 500;
}

.filter-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.filter-tag {
  background-color: var(--primary-color);
  color: var(--background-color);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  display: flex;
  align-items: center;
  font-size: 0.8rem;
}

.filter-tag span {
  margin-left: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.9rem;
}

.filter-tag span:hover {
  opacity: 0.8;
}

.clear-all {
  margin-left: auto;
  background: none;
  border: none;
  color: var(--primary-color);
  font-size: 0.8rem;
  cursor: pointer;
  font-weight: 500;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.clear-all:hover {
  background-color: var(--primary-light);
}

/* Results */
.products-content {
  padding-top: 0.5rem;
}

.result-count {
  font-size: 0.85rem;
  color: var(--text-secondary);
  margin-bottom: 1rem;
  font-weight: 500;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.product-card {
  border: 1px solid var(--border-light);
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.2s ease;
  background: var(--background-color);
  cursor: pointer;
  box-shadow: var(--shadow-sm);
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
  border-color: var(--primary-light);
}

.image-container {
  position: relative;
  height: 180px;
  overflow: hidden;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card:hover .product-image {
  transform: scale(1.05);
}

.card-actions {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  display: flex;
  gap: 0.5rem;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.product-card:hover .card-actions {
  opacity: 1;
}

.wishlist-btn, .cart-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: var(--background-color);
  box-shadow: var(--shadow-sm);
  cursor: pointer;
  border: 1px solid var(--border-light);
  font-size: 1rem;
  transition: all 0.2s ease;
}

.wishlist-btn {
  color: var(--text-muted);
}

.wishlist-btn.active {
  color: #e74c3c;
  background-color: #fdeaea;
  border-color: #e74c3c;
}

.cart-btn {
  color: var(--primary-color);
  font-weight: 600;
  border-color: var(--primary-light);
}

.wishlist-btn:hover, .cart-btn:hover {
  transform: scale(1.05);
}

.cart-btn:hover {
  background-color: var(--primary-color);
  color: var(--background-color);
}

.product-info {
  padding: 1rem;
}

.product-info h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  line-height: 1.3;
  height: auto;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.product-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
}

.category {
  color: var(--text-secondary);
  background: var(--surface-color);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.price1 {
  font-weight: 600;
  color: var(--primary-color);
  font-size: 0.9rem;
}

/* Pagination Controls */
.pagination-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin: 2rem 0;
}

.pagination-btn {
  padding: 0.5rem 1rem;
  background-color: var(--background-color);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 500;
  transition: all 0.2s ease;
  color: var(--text-primary);
}

.pagination-btn:hover:not(:disabled) {
  background-color: var(--primary-color);
  color: var(--background-color);
  border-color: var(--primary-color);
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.85rem;
  color: var(--text-secondary);
  font-weight: 500;
  padding: 0 1rem;
}

/* Loading and Error States */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 0;
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.loading-spinner {
  width: 2rem;
  height: 2rem;
  border: 3px solid var(--border-light);
  border-top: 3px solid var(--primary-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state {
  text-align: center;
  padding: 1.5rem;
  color: var(--error-color);
  background-color: #fdeaea;
  border: 1px solid #f5c6cb;
  border-radius: 6px;
  font-size: 0.9rem;
  margin: 1rem 0;
}

.no-products {
  text-align: center;
  padding: 3rem 0;
  color: var(--text-secondary);
}

.no-products p {
  margin-bottom: 1rem;
  font-size: 1rem;
}

.no-products button {
  padding: 0.75rem 1.5rem;
  background-color: var(--primary-color);
  color: var(--background-color);
  border: none;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.no-products button:hover {
  background-color: var(--primary-dark);
  transform: translateY(-1px);
}

/* Modal */
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
  background-color: var(--background-color);
  padding: 2rem;
  border-radius: 8px;
  width: 90%;
  max-width: 400px;
  text-align: center;
  box-shadow: var(--shadow-lg);
}

.modal-content h2 {
  margin-top: 0;
  font-size: 1.25rem;
  color: var(--text-primary);
  font-weight: 600;
}

.modal-content p {
  font-size: 0.9rem;
  color: var(--text-secondary);
  margin-bottom: 1.5rem;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
}

.primary-button, .secondary-button {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid transparent;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s ease;
  text-decoration: none;
  display: inline-block;
}

.primary-button {
  background-color: var(--primary-color);
  color: var(--background-color);
  border-color: var(--primary-color);
}

.primary-button:hover {
  background-color: var(--primary-dark);
}

.secondary-button {
  background-color: var(--surface-color);
  color: var(--text-primary);
  border-color: var(--border-color);
}

.secondary-button:hover {
  background-color: var(--border-color);
}

/* Toast */
.toast-notification {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  background-color: var(--success-color);
  color: var(--background-color);
  padding: 1rem 1.5rem;
  border-radius: 6px;
  box-shadow: var(--shadow-lg);
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.3s ease;
  z-index: 1000;
  font-size: 0.9rem;
  font-weight: 500;
}

.toast-notification.show {
  opacity: 1;
  transform: translateY(0);
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .recommendations-slider {
    height: 190px;
  }
  
  .recommendation-card {
    flex: 0 0 calc(33.333% - 0.67rem);
  }
  
  .recommendation-image {
    height: 110px;
  }
  
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  }
}

@media (max-width: 768px) {
  .browse-container {
    padding: 0.75rem;
  }
  
  .recommendations-banner {
    padding: 0.75rem;
  }
  
  .recommendations-slider {
    height: 180px;
  }
  
  .recommendation-card {
    flex: 0 0 calc(50% - 0.5rem);
  }
  
  .recommendation-image {
    height: 100px;
  }
  
  .search-category-container {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .category-select {
    width: 100%;
    min-width: unset;
  }
  
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0.75rem;
  }
  
  .image-container {
    height: 150px;
  }
  
  .product-info {
    padding: 0.75rem;
  }
}

@media (max-width: 480px) {
  .browse-container {
    padding: 0.5rem;
  }
  
  .recommendations-slider {
    height: 170px;
  }
  
  .recommendation-image {
    height: 90px;
  }
  
  .recommendation-details {
    padding: 0.5rem;
  }
  
  .product-name {
    font-size: 0.75rem !important;
  }
  
  .recommendation-price {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
  }
  
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .image-container {
    height: 120px;
  }
  
  .product-info {
    padding: 0.5rem;
  }
  
  .product-info h3 {
    font-size: 0.85rem;
  }
  
  .product-meta {
    font-size: 0.75rem;
  }
  
  .price1 {
    font-size: 0.8rem;
  }
}
</style>