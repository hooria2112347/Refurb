<template>
  <div class="browse-container">
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
              :src="product.images.length ? product.images[0] : 'default-image.jpg'" 
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
      productsPerPage: 10
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
    
    // Add to cart 
    async addToCart(product) {
      const token = localStorage.getItem("access_token");
      if (!token) {
        this.loginModalVisible = true;
        return;
      }
      
      try {
        const response = await fetch(`http://127.0.0.1:8000/api/cart/add/${product.id}`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            quantity: 1
          })
        });
        
        if (response.ok) {
          this.fetchCartCount();
          this.cartNotificationMessage = `Added to cart: ${product.name}`;
          this.showCartNotification = true;
          
          setTimeout(() => {
            this.showCartNotification = false;
          }, 3000);
        } else {
          throw new Error('Failed to add to cart');
        }
      } catch (err) {
        console.error("Error adding to cart:", err);
        this.error = "Failed to add to cart. Please try again.";
      }
    },
    
    // Fetch products
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
        
      } catch (err) {
        this.error = 'Failed to fetch products.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    
    // Extract categories
    extractCategories() {
      const categorySet = new Set();
      this.products.forEach(product => {
        if (product.category) {
          categorySet.add(product.category);
        }
      });
      this.categories = Array.from(categorySet).sort();
    },
    
    // Filter products
    filterProducts() {
      this.filteredProducts = this.products.filter(product => {
        // Search query filter
        const matchesSearch = !this.searchQuery || 
          product.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          (product.description && product.description.toLowerCase().includes(this.searchQuery.toLowerCase()));
        
        // Category filter
        const matchesCategory = !this.selectedCategory || 
          product.category === this.selectedCategory;
        
        return matchesSearch && matchesCategory;
      });
      
      // Reset to first page when filters change
      this.currentPage = 1;
    },
    
    // Reset filters
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

    // View product details
    viewProductDetails(product) {
      this.$router.push({ name: 'product-details', params: { id: product.id } });
    },
    
    // Go to cart
    goToCart() {
      this.$router.push({ name: 'cart' });
    },
    
    // Go to wishlist
    goToWishlist() {
      this.$router.push({ name: 'wishlist' });
    },
    
    // Fetch cart count
    async fetchCartCount() {
      const token = localStorage.getItem("access_token");
      if (!token) return;
      
      try {
        const response = await fetch('http://127.0.0.1:8000/api/cart', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.ok) {
          const data = await response.json();
          this.cartCount = data.reduce((total, item) => total + item.quantity, 0);
        }
      } catch (err) {
        console.error("Error fetching cart:", err);
      }
    },
    
    // Fetch wishlist
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
    
    // Check if in wishlist
    isInWishlist(productId) {
      return this.wishlistItems.includes(productId);
    },
    
    // Toggle wishlist
    async toggleWishlist(product) {
      const token = localStorage.getItem("access_token");
      if (!token) {
        this.loginModalVisible = true;
        return;
      }
      
      try {
        if (this.isInWishlist(product.id)) {
          // Remove from wishlist
          const response = await fetch(`http://127.0.0.1:8000/api/wishlist/remove/${product.id}`, {
            method: 'DELETE',
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });
          
          if (response.ok) {
            this.wishlistItems = this.wishlistItems.filter(id => id !== product.id);
          }
        } else {
          // Add to wishlist
          const response = await fetch(`http://127.0.0.1:8000/api/wishlist/add/${product.id}`, {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });
          
          if (response.ok) {
            this.wishlistItems.push(product.id);
          }
        }
      } catch (err) {
        console.error("Error updating wishlist:", err);
      }
    },
    
    // Close login modal
    closeLoginModal() {
      this.loginModalVisible = false;
    },
  },
  mounted() {
    this.fetchProducts();
    this.fetchWishlist();
    this.fetchCartCount();
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
  min-height: 230px; /* Increase minimum height */
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
  font-size: 0.9rem; /* Increase from 0.8rem to 0.9rem */
  font-weight: 600; /* Make font weight bolder */
  color: #333;
  margin-bottom: 0.3rem; /* Increase bottom margin */
  line-height: 1.2; /* Add line height */
  height: 2.4em; /* Set fixed height for 2 lines */
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
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>