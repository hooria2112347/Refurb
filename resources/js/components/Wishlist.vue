<template>
  <div class="wishlist-page bg-gray-50">
    <div class="container mx-auto py-8 px-4">
      <div class="row">
        <div class="col-12">
          <!-- Page Title and Description -->
          <div class="page-header mb-6">
            <h1 class="text-3xl font-bold text-gray-800">My Wishlist</h1>
            <p class="text-gray-600 mt-2">Items you've saved for later</p>
          </div>
          
          <!-- Loading State -->
          <div v-if="loading" class="loading-state text-center my-12">
            <div class="spinner"></div>
            <p class="mt-4 text-gray-600">Loading your wishlist...</p>
          </div>
          
          <!-- Error State -->
          <div v-else-if="error" class="error-state text-center my-12 p-6 bg-red-50 rounded-lg">
            <div class="error-icon mb-4">❌</div>
            <p class="error-message mb-4">{{ error }}</p>
            <button @click="fetchWishlist" class="retry-btn">Try Again</button>
          </div>
          
          <!-- Empty Wishlist State -->
          <div v-else-if="!wishlistItems.length" class="empty-state text-center my-12 p-8 bg-white rounded-lg shadow-sm">
            <div class="empty-icon mb-4">❤️</div>
            <h3 class="text-xl font-semibold mb-2">Your wishlist is empty</h3>
            <p class="text-gray-600 mb-6">Browse our products and save your favorites!</p>
            <router-link to="/scrap-items" class="browse-btn">Explore Items</router-link>
          </div>
          
          <!-- Wishlist Items - Improved Grid Layout -->
          <div v-else class="wishlist-items">
            <!-- Option to sort and filter -->
            <div class="mb-8 flex flex-wrap items-center justify-between">
              <div class="flex items-center gap-4 mb-3 md:mb-0">
                <p class="text-gray-700"><span class="font-medium">{{ wishlistItems.length }}</span> items saved</p>
              </div>
              <div class="flex items-center gap-3">
                <select v-model="sortOption" class="sort-select">
                  <option value="newest">Newest First</option>
                  <option value="oldest">Oldest First</option>
                  <option value="price-high">Price: High to Low</option>
                  <option value="price-low">Price: Low to High</option>
                </select>
              </div>
            </div>
            
            <!-- Improved Grid Display -->
            <div class="grid-container">
              <div v-for="item in sortedWishlistItems" :key="item.id" class="wishlist-card">
                <!-- New Badge if item was added recently -->
                <div v-if="isRecentlyAdded(item.added_at)" class="new-badge">NEW</div>
                
                <!-- Product Image with Hover Effects -->
                <div class="image-container" @click="viewProductDetails(item.id)">
                  <img 
                    :src="item.images && item.images.length ? item.images[0] : 'default-image.jpg'" 
                    :alt="item.name" 
                    class="product-image"
                  />
                  <div class="image-overlay">
                    <span class="view-details">View Details</span>
                  </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="quick-actions">
                  <button @click="removeFromWishlist(item.id)" class="action-btn remove-btn" aria-label="Remove from wishlist">
                    <span class="action-icon">×</span>
                  </button>
                  <button @click="addToCart(item.id)" class="action-btn cart-btn" aria-label="Add to cart">
                    <span class="action-icon">🛒</span>
                  </button>
                </div>
                
                <!-- Product Info -->
                <div class="product-info">
                  <h3 class="product-name" @click="viewProductDetails(item.id)">{{ item.name }}</h3>
                  <div class="price-row">
                    <p class="product-price">PKR {{ formatPrice(item.price) }}</p>
                    <p class="added-date">Added {{ formatDateRelative(item.added_at) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Confirmation Modal -->
    <div v-if="showConfirmModal" class="modal-overlay">
      <div class="modal-content">
        <h3 class="text-lg font-semibold mb-2">Remove from Wishlist?</h3>
        <p class="text-gray-600">Are you sure you want to remove this item from your wishlist?</p>
        <div class="modal-actions">
          <button @click="confirmRemove" class="confirm-btn">Yes, Remove</button>
          <button @click="cancelRemove" class="cancel-btn">Cancel</button>
        </div>
      </div>
    </div>
    
    <!-- Login Modal -->
    <div v-if="showLoginModal" class="modal-overlay">
      <div class="modal-content">
        <h3 class="text-lg font-semibold mb-2">Please Log In</h3>
        <p class="text-gray-600 mb-4">You need to be logged in to view your wishlist.</p>
        <div class="modal-actions">
          <router-link to="/login" class="primary-button">Log In</router-link>
          <button @click="closeLoginModal" class="secondary-button">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      wishlistItems: [],
      loading: true,
      error: null,
      showConfirmModal: false,
      itemToRemove: null,
      showLoginModal: false,
      sortOption: 'newest'
    };
  },
  
  computed: {
    sortedWishlistItems() {
      const items = [...this.wishlistItems];
      
      switch(this.sortOption) {
        case 'newest':
          return items.sort((a, b) => new Date(b.added_at) - new Date(a.added_at));
        case 'oldest':
          return items.sort((a, b) => new Date(a.added_at) - new Date(b.added_at));
        case 'price-high':
          return items.sort((a, b) => b.price - a.price);
        case 'price-low':
          return items.sort((a, b) => a.price - b.price);
        default:
          return items;
      }
    }
  },
  
  methods: {
    async fetchWishlist() {
      this.loading = true;
      this.error = null;
      
      const token = localStorage.getItem("access_token");
      if (!token) {
        this.showLoginModal = true;
        this.loading = false;
        return;
      }
      
      try {
        const response = await fetch('http://127.0.0.1:8000/api/wishlist', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (!response.ok) {
          throw new Error('Failed to fetch wishlist');
        }
        
        const data = await response.json();
        this.wishlistItems = data;
      } catch (err) {
        console.error("Error fetching wishlist:", err);
        this.error = "Could not load your wishlist. Please try again.";
      } finally {
        this.loading = false;
      }
    },
    
    viewProductDetails(productId) {
      this.$router.push({ name: 'product-details', params: { id: productId } });
    },
    
    removeFromWishlist(productId) {
      this.itemToRemove = productId;
      this.showConfirmModal = true;
    },
    
    async confirmRemove() {
      const token = localStorage.getItem("access_token");
      
      try {
        const response = await fetch(`http://127.0.0.1:8000/api/wishlist/remove/${this.itemToRemove}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.ok) {
          // Remove the item from the local array
          this.wishlistItems = this.wishlistItems.filter(item => item.id !== this.itemToRemove);
        } else {
          throw new Error('Failed to remove item');
        }
      } catch (err) {
        console.error("Error removing item from wishlist:", err);
        this.error = "Could not remove item. Please try again.";
      } finally {
        this.showConfirmModal = false;
        this.itemToRemove = null;
      }
    },
    
    cancelRemove() {
      this.showConfirmModal = false;
      this.itemToRemove = null;
    },
    
    closeLoginModal() {
      this.showLoginModal = false;
      this.$router.push('/browse-scrap');
    },
    
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    
    formatDateRelative(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diffTime = Math.abs(now - date);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      
      if (diffDays < 1) {
        return 'Today';
      } else if (diffDays === 1) {
        return 'Yesterday';
      } else if (diffDays < 7) {
        return `${diffDays} days ago`;
      } else if (diffDays < 30) {
        const weeks = Math.floor(diffDays / 7);
        return `${weeks} ${weeks === 1 ? 'week' : 'weeks'} ago`;
      } else {
        return this.formatDate(dateString);
      }
    },
    
    formatPrice(price) {
      return price.toLocaleString();
    },
    
    isRecentlyAdded(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diffTime = Math.abs(now - date);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      
      return diffDays <= 3; // Items added within last 3 days are considered "new"
    },
    
    async addToCart(productId) {
      const token = localStorage.getItem("access_token");
      
      try {
        // Assuming you have an API endpoint to add items to cart
        const response = await fetch(`http://127.0.0.1:8000/api/cart/add/${productId}`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ quantity: 1 })
        });
        
        if (response.ok) {
          // Show success message or update UI
          alert('Item added to cart successfully!');
        } else {
          throw new Error('Failed to add item to cart');
        }
      } catch (err) {
        console.error("Error adding item to cart:", err);
        alert('Could not add item to cart. Please try again.');
      }
    }
  },
  
  mounted() {
    this.fetchWishlist();
  }
};
</script>

<style scoped>
.wishlist-page {
  min-height: 80vh;
  background-color: #f9f9fc;
}

.page-header h1 {
  margin-top: 1rem;
  color: #2d3748;
  font-weight: 700;
}

.page-header p {
  color: #718096;
  font-size: 1.1rem;
}

/* Loading, Error, Empty States */
.loading-state, .error-state, .empty-state {
  padding: 40px 20px;
}

.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top: 4px solid #9B7EBD;
  width: 50px;
  height: 50px;
  margin: 0 auto 20px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  color: #e53e3e;
  font-size: 1.1rem;
}

.error-icon {
  font-size: 2rem;
  color: #e53e3e;
}

.retry-btn {
  background-color: #9B7EBD;
  color: #fff;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.retry-btn:hover {
  background-color: #8a68ad;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.empty-icon {
  font-size: 3.5rem;
  margin-bottom: 20px;
  color: #9B7EBD;
}

.empty-state h3 {
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 10px;
}

.empty-state p {
  color: #718096;
  margin-bottom: 20px;
}

.browse-btn {
  display: inline-block;
  background-color: #9B7EBD;
  color: #fff;
  padding: 12px 28px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s ease;
}

.browse-btn:hover {
  background-color: #8a68ad;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Improved Grid Layout */
.wishlist-items {
  margin-bottom: 40px;
}

.sort-select {
  padding: 8px 12px;
  margin-bottom: 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background-color: white;
  color: #4a5568;
  font-size: 0.9rem;
  cursor: pointer;
  transition: border-color 0.2s;
}

.sort-select:hover {
  border-color: #cbd5e0;
}

.sort-select:focus {
  outline: none;
  border-color: #9B7EBD;
  box-shadow: 0 0 0 2px rgba(155, 126, 189, 0.2);
}

/* New Grid Container with CSS Grid */
.grid-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
  margin: 0 auto;
}

.wishlist-card {
  position: relative;
  background-color: white;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  height: 100%;
  display: flex;
  flex-direction: column;
}

.wishlist-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
}

/* New Badge for recently added items */
.new-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background-color: #9B7EBD;
  color: white;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 4px;
  z-index: 2;
}

.image-container {
  position: relative;
  height: 220px;
  overflow: hidden;
  cursor: pointer;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.wishlist-card:hover .product-image {
  transform: scale(1.08);
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.wishlist-card:hover .image-overlay {
  opacity: 1;
}

.view-details {
  color: white;
  background-color: rgba(0, 0, 0, 0.6);
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 500;
  transform: translateY(10px);
  transition: transform 0.3s ease;
}

.wishlist-card:hover .view-details {
  transform: translateY(0);
}

/* Quick action buttons that appear on hover */
.quick-actions {
  position: absolute;
  top: 12px;
  right: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  z-index: 3;
  opacity: 0;
  transform: translateX(10px);
  transition: all 0.3s ease;
}

.wishlist-card:hover .quick-actions {
  opacity: 1;
  transform: translateX(0);
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.remove-btn {
  background-color: rgba(229, 62, 62, 0.9);
  color: white;
}

.remove-btn:hover {
  background-color: rgba(229, 62, 62, 1);
  transform: scale(1.1);
}

.cart-btn {
  background-color: rgba(155, 126, 189, 0.9);
  color: white;
}

.cart-btn:hover {
  background-color: rgba(155, 126, 189, 1);
  transform: scale(1.1);
}

.action-icon {
  font-size: 1.2rem;
  line-height: 1;
}

/* Product Info Section */
.product-info {
  padding: 16px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.product-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3748;
  cursor: pointer;
  transition: color 0.2s;
  margin-bottom: 8px;
  line-height: 1.4;
  height: 2.8em; /* Fixed height for two lines of text */
}

.product-name:hover {
  color: #9B7EBD;
}

.price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.product-price {
  font-weight: 700;
  color: #9B7EBD;
  font-size: 1.1rem;
}

.added-date {
  font-size: 0.8rem;
  color: #718096;
  text-align: right;
}

/* Modals */
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
  backdrop-filter: blur(2px);
}

.modal-content {
  background-color: white;
  border-radius: 12px;
  padding: 28px;
  max-width: 400px;
  width: 90%;
  text-align: center;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.modal-content h3 {
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 12px;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 16px;
  margin-top: 28px;
}

.confirm-btn, .primary-button {
  background-color: #9B7EBD;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
}

.confirm-btn:hover, .primary-button:hover {
  background-color: #8a68ad;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.cancel-btn, .secondary-button {
  background-color: white;
  border: 1px solid #e2e8f0;
  color: #718096;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.cancel-btn:hover, .secondary-button:hover {
  background-color: #f7fafc;
  color: #4a5568;
}

/* Responsive Adjustments */
@media (max-width: 1200px) {
  .grid-container {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 992px) {
  .grid-container {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .grid-container {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }
  
  .image-container {
    height: 180px;
  }
}

@media (max-width: 576px) {
  .grid-container {
    grid-template-columns: 1fr;
    max-width: 320px;
    margin: 0 auto;
  }
  
  .price-row {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .added-date {
    margin-top: 4px;
    text-align: left;
  }
}
</style>