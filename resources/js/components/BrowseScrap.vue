<template>
  <div class="browse-container">
    <!-- Recommendations Banner (5-10% of page height) -->
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
        <!-- Product name displayed outside the card image -->
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
    
    <!-- Active Filters (only shown when filters are active) -->
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
.browse-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0.5rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

recommendations-banner {
  background: linear-gradient(135deg, #f9f5fc 0%, #ffffff 100%);
  border: 1px solid #e6d7f3;
  border-radius: 0.75rem;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 15px rgba(155, 126, 189, 0.1);
}

.recommendations-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.recommendations-header h3 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 700;
  color: #3b1e54;
  text-shadow: 0 1px 2px rgba(155, 126, 189, 0.1);
}

.banner-controls {
  display: flex;
  gap: 0.5rem;
}

.control-btn {
  width: 36px;
  height: 36px;
  border: 2px solid #e6d7f3;
  background: linear-gradient(135deg, #ffffff 0%, #f9f5fc 100%);
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  font-weight: bold;
  color: #9b7ebd;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 8px rgba(155, 126, 189, 0.15);
}

.control-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #9b7ebd 0%, #d4bee4 100%);
  border-color: #9b7ebd;
  color: #ffffff;
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 4px 15px rgba(155, 126, 189, 0.3);
}

.control-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
  transform: none;
}

.recommendations-slider {
  overflow: hidden;
  height: 280px; /* Increased height to accommodate product names */
  border-radius: 0.5rem;
}

.recommendations-track {
  display: flex;
  transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  height: 100%;
  gap: 1rem;
}

.recommendation-card {
  flex: 0 0 calc(25% - 0.9375rem); /* Show 4 cards at once */
  display: flex;
  flex-direction: column;
  background: linear-gradient(135deg, #ffffff 0%, #f9f5fc 100%);
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 2px solid #e6d7f3;
  overflow: hidden;
  position: relative;
  box-shadow: 0 4px 15px rgba(155, 126, 189, 0.1);
}

.recommendation-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 12px 30px rgba(155, 126, 189, 0.25);
  border-color: #9b7ebd;
}

.recommendation-tag {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: linear-gradient(135deg, #9b7ebd 0%, #d4bee4 100%);
  color: #ffffff;
  padding: 0.25rem 0.6rem;
  border-radius: 1rem;
  font-size: 0.65rem;
  font-weight: 600;
  z-index: 3;
  box-shadow: 0 2px 8px rgba(155, 126, 189, 0.3);
  text-transform: uppercase;
  letter-spacing: 0.3px;
  max-width: 70%;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.recommendation-image {
  width: 100%;
  height: 180px; /* Adjusted height */
  flex-shrink: 0;
  position: relative;
  overflow: hidden;
  border-radius: 0.5rem 0.5rem 0 0;
}

.recommendation-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.recommendation-card:hover .recommendation-image img {
  transform: scale(1.1);
}

/* NEW: Product details section outside the image */
.recommendation-details {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  flex: 1;
  background: linear-gradient(135deg, #ffffff 0%, #f9f5fc 100%);
  border-radius: 0 0 0.5rem 0.5rem;
}

.product-name {
  margin: 0 0 0.6rem 0;
  font-size: 0.95rem !important;
  font-weight: 700 !important;
  color: #3b1e54 !important;
  line-height: 1.3 !important;
  text-align: center;
  max-height: none;
  overflow: visible;
  text-overflow: unset;
  display: block;
  -webkit-line-clamp: unset;
  -webkit-box-orient: unset;
  white-space: normal;
  word-wrap: break-word;
}

.recommendation-price {
  font-size: 0.9rem;
  font-weight: 800;
  color: #9b7ebd;
  margin-top: auto;
  padding: 0.4rem 0.8rem;
  background: linear-gradient(135deg, #e6d7f3 0%, #f9f5fc 100%);
  border-radius: 1rem;
  text-align: center;
  box-shadow: 0 2px 6px rgba(155, 126, 189, 0.15);
}

/* Responsive Adjustments */
@media (max-width: 1024px) {
  .recommendations-banner {
    padding: 1.25rem;
  }
  
  .recommendations-header h3 {
    font-size: 1.2rem;
  }
  
  .recommendation-card {
    flex: 0 0 calc(33.333% - 0.833rem); /* Show 3 cards on tablet */
  }
  
  .recommendations-slider {
    height: 270px;
  }
  
  .recommendation-image {
    height: 170px;
  }
  
  .product-name {
    font-size: 0.9rem !important;
  }
}

@media (max-width: 768px) {
  .recommendations-banner {
    padding: 1rem;
    border-radius: 0.5rem;
  }
  
  .recommendations-header {
    margin-bottom: 1rem;
  }
  
  .recommendations-header h3 {
    font-size: 1.1rem;
  }
  
  .control-btn {
    width: 32px;
    height: 32px;
    font-size: 1rem;
  }
  
  .recommendations-slider {
    height: 250px;
  }
  
  .recommendation-card {
    flex: 0 0 calc(50% - 0.625rem); /* Show 2 cards on tablet */
  }
  
  .recommendation-image {
    height: 150px;
  }
  
  .recommendation-details {
    padding: 0.8rem;
  }
  
  .product-name {
    font-size: 0.85rem !important;
    margin-bottom: 0.5rem;
  }
  
  .recommendation-price {
    font-size: 0.8rem;
    padding: 0.3rem 0.6rem;
  }
}

@media (max-width: 640px) {
  .recommendations-banner {
    padding: 0.8rem;
    margin-bottom: 1rem;
  }
  
  .recommendations-header {
    margin-bottom: 0.8rem;
  }
  
  .recommendations-header h3 {
    font-size: 1rem;
  }
  
  .control-btn {
    width: 30px;
    height: 30px;
    font-size: 0.9rem;
  }
  
  .recommendations-slider {
    height: 230px;
  }
  
  .recommendation-card {
    flex: 0 0 calc(50% - 0.625rem); /* Keep 2 cards on mobile */
  }
  
  .recommendations-track {
    gap: 1rem;
  }
  
  .recommendation-image {
    height: 130px;
  }
  
  .recommendation-details {
    padding: 0.75rem;
  }
  
  .product-name {
    font-size: 0.8rem !important;
    margin-bottom: 0.4rem;
  }
  
  .recommendation-price {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
  }
  
  .recommendation-tag {
    font-size: 0.6rem;
    padding: 0.2rem 0.5rem;
    top: 0.4rem;
    right: 0.4rem;
    max-width: 60%;
  }
}

@media (max-width: 480px) {
  .recommendations-track {
    gap: 0.8rem;
  }
  
  .recommendation-card {
    flex: 0 0 calc(50% - 0.4rem);
  }
  
  .recommendations-slider {
    height: 220px;
  }
  
  .recommendation-image {
    height: 120px;
  }
  
  .recommendation-details {
    padding: 0.6rem;
  }
  
  .product-name {
    font-size: 0.75rem !important;
  }
  
  .recommendation-price {
    font-size: 0.7rem;
  }
}

/* Search and Filter Bar */
.search-filter-bar {
  margin-bottom: 0.75rem;
  background-color: #f9f9f9;
  border-radius: 0.25rem;
  padding: 0.75rem;
}

.search-category-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
}

.search-input {
  position: relative;
  flex: 1;
}

.search-input input {
  width: 100%;
  padding: 0.4rem 0.5rem;
  padding-right: 1.75rem;
  border: 1px solid #e0e0e0;
  border-radius: 0.25rem;
  font-size: 0.8rem;
  transition: border-color 0.2s;
}

.search-input input:focus {
  border-color: #b894d4;
  outline: none;
}

.search-icon {
  position: absolute;
  right: 0.5rem;
  top: 50%;
  transform: translateY(-50%);
  color: #8a8a8a;
  font-size: 0.8rem;
}

.category-select {
  padding: 0.4rem 0.5rem;
  border: 1px solid #e0e0e0;
  border-radius: 0.25rem;
  background-color: white;
  font-size: 0.8rem;
  min-width: 7rem;
}

/* Active Filters */
.active-filters {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  margin-bottom: 0.75rem;
  background-color: #f5f5f5;
  border-radius: 0.25rem;
  font-size: 0.75rem;
}

.filters-label {
  color: #666;
  font-weight: 500;
}

.filter-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.3rem;
}

.filter-tag {
  background-color: #e6d7f3;
  color: #3b1e54;
  padding: 0.2rem 0.4rem;
  border-radius: 0.25rem;
  display: flex;
  align-items: center;
}

.filter-tag span {
  margin-left: 0.3rem;
  font-weight: bold;
  cursor: pointer;
}

.clear-all {
  margin-left: auto;
  background: none;
  border: none;
  color: #9b7ebd;
  font-size: 0.75rem;
  cursor: pointer;
  text-decoration: underline;
}

/* Results */
.products-content {
  padding-top: 0.25rem;
}

.result-count {
  font-size: 0.75rem;
  color: #666;
  margin-bottom: 0.5rem;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr); 
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.product-card {
  border: 1px solid #eaeaea;
  border-radius: 0.25rem;
  overflow: hidden;
  transition: transform 0.15s, box-shadow 0.15s;
  background: white;
  cursor: pointer;
  min-height: 230px;
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.07);
}

.image-container {
  position: relative;
  height: 160px;
  overflow: hidden;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.product-card:hover .product-image {
  transform: scale(1.05);
}

.card-actions {
  position: absolute;
  top: 0.4rem;
  right: 0.4rem;
  display: flex;
  gap: 0.3rem;
  opacity: 0;
  transition: opacity 0.2s;
}

.product-card:hover .card-actions {
  opacity: 1;
}

.wishlist-btn, .cart-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background-color: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
  cursor: pointer;
  border: none;
  font-size: 1rem;
  transition: all 0.2s;
}

.wishlist-btn {
  color: #ccc;
}

.wishlist-btn.active {
  color: #ff6b6b;
}

.cart-btn {
  color: #9b7ebd;
  font-weight: bold;
}

.wishlist-btn:hover, .cart-btn:hover {
  transform: scale(1.1);
}

.product-info {
  padding: 0.75rem;
}

.product-info h3 {
  margin: 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.3rem;
  line-height: 1.2;
  height: 2.4em;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
}

.category {
  color: #888;
}

.price1 {
  font-weight: 600;
  color: #333;
}

/* Pagination Controls */
.pagination-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.75rem;
  margin: 1rem 0;
}

.pagination-btn {
  padding: 0.25rem 0.5rem;
  background-color: #f9f5fc;
  border: 1px solid #e0e0e0;
  border-radius: 0.25rem;
  cursor: pointer;
  font-size: 0.75rem;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #e6d7f3;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.75rem;
  color: #666;
}

/* Loading and Error States */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 0;
  color: #666;
  font-size: 0.85rem;
}

.loading-spinner {
  width: 1.5rem;
  height: 1.5rem;
  border: 2px solid #f3f3f3;
  border-top: 2px solid #9b7ebd;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 0.5rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state {
  text-align: center;
  padding: 1rem;
  color: #e74c3c;
  background-color: #fdeaea;
  border-radius: 0.25rem;
  font-size: 0.85rem;
  margin: 1rem 0;
}

.no-products {
  text-align: center;
  padding: 1.5rem 0;
  color: #666;
}

.no-products button {
  margin-top: 0.5rem;
  padding: 0.3rem 0.6rem;
  background-color: #d4bee4;
  border: none;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  cursor: pointer;
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
  background-color: white;
  padding: 1.25rem;
  border-radius: 0.25rem;
  width: 90%;
  max-width: 320px;
  text-align: center;
}

.modal-content h2 {
  margin-top: 0;
  font-size: 1.1rem;
  color: #333;
}

.modal-content p {
  font-size: 0.85rem;
  color: #666;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
  margin-top: 1rem;
}

.primary-button, .secondary-button {
  padding: 0.4rem 0.75rem;
  border-radius: 0.25rem;
  cursor: pointer;
  border: none;
  font-size: 0.8rem;
}

.primary-button {
  background-color: #d4bee4;
  color: #3b1e54;
  text-decoration: none;
}

.secondary-button {
  background-color: #f0f0f0;
  color: #333;
}

/* Toast */
.toast-notification {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  background-color: #d4bee4;
  color: #3b1e54;
  padding: 0.5rem 0.75rem;
  border-radius: 0.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  opacity: 0;
  transform: translateY(10px);
  transition: all 0.3s;
  z-index: 1000;
  font-size: 0.8rem;
}

.toast-notification.show {
  opacity: 1;
  transform: translateY(0);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .recommendations-banner {
    height: 100px;
    padding: 0.75rem;
  }
  
  .recommendation-card {
    flex: 0 0 33.333%; /* Show 3 cards on tablet */
  }
  
  .recommendations-header h3 {
    font-size: 0.9rem;
  }
  
  .recommendation-image {
    width: 60px;
    height: 58px;
  }
  
  .search-category-container {
    flex-direction: column;
    align-items: stretch;
  }

  .category-select {
    width: 100%;
  }
  
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
  }
  
  .image-container {
    height: 110px;
  }
  
  .pagination-controls {
    flex-wrap: wrap;
  }
}

@media (max-width: 480px) {
  .recommendations-banner {
    height: 90px;
    padding: 0.5rem;
  }
  
  .recommendation-card {
    flex: 0 0 50%; /* Show 2 cards on mobile */
  }
  
  .recommendation-image {
    width: 50px;
    height: 48px;
  }
  
  .recommendation-info h4 {
    font-size: 0.7rem;
  }
  
  .recommendation-price {
    font-size: 0.65rem;
  }
  
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>