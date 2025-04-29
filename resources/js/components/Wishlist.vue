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
          
          <!-- Wishlist Items -->
          <div v-else class="wishlist-items">
            
            <!-- Wishlist Items List -->
            <div class="wishlist-list">
              <div v-for="item in wishlistItems" :key="item.id" class="wishlist-item">
                <div class="item-container">
                  <!-- Product Image -->
                  <div class="item-image-container">
                    <img 
                      :src="item.images && item.images.length ? item.images[0] : 'default-image.jpg'" 
                      :alt="item.name" 
                      class="item-image"
                      @click="viewProductDetails(item.id)"
                    />
                  </div>
                  
                  <!-- Product Info -->
                  <div class="item-details">
                    <h3 class="item-name" @click="viewProductDetails(item.id)">{{ item.name }}</h3>
                    <p class="item-price">Rs. {{ formatPrice(item.price) }}</p>
                    <p v-if="item.original_price && item.original_price > item.price" class="item-original-price">
                      Rs. {{ formatPrice(item.original_price) }}
                      <span class="discount-percentage">-{{ calculateDiscount(item.price, item.original_price) }}%</span>
                    </p>
                    <p v-if="item.price_dropped" class="price-dropped">Price dropped</p>
                  </div>
                  
                  <!-- Action Buttons -->
                  <div class="item-actions">
                    <button @click="removeFromWishlist(item.id)" class="delete-btn" title="Remove from wishlist">
                      <span class="trash-icon">🗑️</span>
                    </button>
                    <button @click="addToCart(item.id)" class="cart-btn" title="Add to cart">
                      <span class="cart-icon">+</span>
                    </button>
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
      showLoginModal: false
    };
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
    
    formatPrice(price) {
      return price.toLocaleString();
    },
    
    calculateDiscount(currentPrice, originalPrice) {
      if (!originalPrice || originalPrice <= currentPrice) return 0;
      const discount = ((originalPrice - currentPrice) / originalPrice) * 100;
      return Math.round(discount);
    },
    
    async addToCart(productId) {
      const token = localStorage.getItem("access_token");
      
      try {
        const response = await fetch(`http://127.0.0.1:8000/api/cart/add/${productId}`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ quantity: 1 })
        });
        
        if (response.ok) {
          // Show success message
          alert('Item added to cart successfully!');
        } else {
          throw new Error('Failed to add item to cart');
        }
      } catch (err) {
        console.error("Error adding item to cart:", err);
        alert('Could not add item to cart. Please try again.');
      }
    },
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
  color: #3B1E54;
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
  background-color: #D4BEE4;
  color: #3B1E54;
  padding: 12px 28px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s ease;
}

.browse-btn:hover {
  background-color: #EEEEEE;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Add All to Cart Button */
.add-all-cart-wrapper {
  margin-bottom: 20px;
}


/* Wishlist Items Styling */
.wishlist-list {
  margin-bottom: 40px;
}

.wishlist-item {
  background-color: white;
  border-radius: 8px;
  margin-bottom: 16px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.wishlist-item:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.item-container {
  padding: 16px;
  display: flex;
  align-items: center;
}

.item-image-container {
  width: 80px;
  height: 80px;
  flex-shrink: 0;
  margin-right: 16px;
  cursor: pointer;
}

.item-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 4px;
}

.item-details {
  flex-grow: 1;
}

.item-name {
  font-size: 1rem;
  font-weight: 500;
  color: #3B1E54;
  margin-bottom: 8px;
  cursor: pointer;
}

.item-name:hover {
  color: #D4BEE4;
}

.item-price {
  font-weight: 700;
  color: #3B1E54;
  font-size: 1.1rem;
}

.item-original-price {
  color: #718096;
  font-size: 0.9rem;
  text-decoration: line-through;
  display: flex;
  align-items: center;
  gap: 8px;
}

.discount-percentage {
  color: #e53e3e;
  text-decoration: none;
}

.price-dropped {
  color: #e53e3e;
  font-size: 0.9rem;
  margin-top: 4px;
}

.item-actions {
  display: flex;
  gap: 12px;
  margin-left: 16px;
}

.delete-btn, .cart-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.delete-btn {
  background-color: transparent;
  border: 1px solid #e2e8f0;
  color: #718096;
}

.delete-btn:hover {
  background-color: #f7fafc;
  color: #e53e3e;
}

.cart-btn {
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
}

.cart-btn:hover {
  background-color: #EEEEEE;
}

.trash-icon, .cart-icon {
  font-size: 1.2rem;
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
@media (max-width: 768px) {
  .item-container {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .item-image-container {
    width: 100%;
    height: auto;
    margin-right: 0;
    margin-bottom: 16px;
  }
  
  .item-image {
    aspect-ratio: 1/1;
  }
  
  .item-actions {
    margin-left: 0;
    margin-top: 16px;
    align-self: flex-end;
  }
}
</style>