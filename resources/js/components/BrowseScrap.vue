<template>
  <div class="browse-scrap">
    <!-- Loading and Error States -->
    <div v-if="loading" class="loading">Loading products...</div>
    <div v-else-if="error" class="error">{{ error }}</div>

    <!-- Grid of Products -->
    <div v-else-if="products.length" class="products-grid">
      <div 
        v-for="product in products" 
        :key="product.id" 
        class="product-card" 
      >
        <!-- Cart icon -->
        <div class="add-to-cart" title="Add to Cart" @click.stop="addToCart(product)">
          🛒
        </div>

        <!-- Wishlist Icon -->
        <div class="add-to-wishlist" title="Add to Wishlist" @click.stop="toggleWishlist(product)">
          <span v-if="isInWishlist(product.id)">❤️</span>
          <span v-else>🤍</span>
        </div>

        <!-- Display Image if Available -->
        <img 
          :src="product.images.length ? product.images[0] : 'default-image.jpg'" 
          alt="Product Image" 
          class="product-image" 
          @click="viewProductDetails(product)"
        />

        <!-- Product Name and Price -->
        <div class="product-info">
          <h2 class="product-name" @click="viewProductDetails(product)">
            {{ product.name }}
          </h2>
          <span class="price">{{ product.price }} PKR</span>
        </div>
      </div>
    </div>

    <!-- No Products Available Message -->
    <div v-else class="no-products">
      <p>No products found.</p>
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

    <!-- Toast Notification for Cart -->
    <div v-if="showCartNotification" class="toast-notification" :class="{ 'show': showCartNotification }">
      <div class="toast-content">
        <span class="toast-icon">✓</span>
        <span class="toast-message">{{ cartNotificationMessage }}</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      products: [],  // List of products
      loading: false, // Loading state
      error: null, // Error message if fetch fails
      wishlistItems: [], // Array to store wishlist product IDs
      loginModalVisible: false, // For login modal
      showCartNotification: false, // For cart notification toast
      cartNotificationMessage: "", // Message for the notification
    };
  },
  methods: {
    // Add to cart with enhanced feedback
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
            quantity: 1  // Default to adding 1 item from the browse page
          })
        });
        
        if (response.ok) {
          // Show toast notification
          this.cartNotificationMessage = `${product.name} added to cart!`;
          this.showCartNotification = true;
          
          // Hide notification after 3 seconds
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
    
    // Fetch all products from the API
    async fetchProducts() {
      this.loading = true;
      this.error = null; // Reset error state before fetching
      try {
        const response = await fetch('http://127.0.0.1:8000/api/products/all');
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        console.log(data);  // Log the fetched data to check structure
        this.products = data;
      } catch (err) {
        this.error = 'Failed to fetch products.';
        console.error(err); // Log the actual error for debugging
      } finally {
        this.loading = false;
      }
    },

    // Navigate to the product details page
    viewProductDetails(product) {
      this.$router.push({ name: 'product-details', params: { id: product.id } });
    },
    
    async fetchWishlist() {
      const token = localStorage.getItem("access_token");
      if (!token) return; // Not logged in
      
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
    
    // Check if product is in wishlist
    isInWishlist(productId) {
      return this.wishlistItems.includes(productId);
    },
    
    // Toggle wishlist status
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
            // Remove from local array
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
            // Add to local array
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
    this.fetchProducts(); // Fetch products on component load
    this.fetchWishlist(); // Fetch wishlist on mount
  },
};
</script>

<style scoped>
.browse-scrap {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}

.loading,
.error,
.no-products {
  text-align: center;
  font-size: 1.2rem;
  color: #555;
  margin-top: 2rem;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 1rem;
}

.product-card {
  position: relative;
  border: 1px solid #e0e0e0;
  padding: 1rem;
  border-radius: 12px;
  text-align: center;
  cursor: pointer;
  transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.product-card:hover {
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
  transform: translateY(-5px);
}

.add-to-cart {
  position: absolute;
  top: 10px;
  right: 10px;
  background-color: #9B7EBD;
  color: #fff;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.add-to-cart:hover {
  background-color: #ff4c3b;
  transform: scale(1.1);
}

.product-image {
  max-width: 100%;
  height: 180px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.product-info {
  margin-top: 0.5rem;
}

.product-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
  transition: color 0.3s ease;
}

.product-name:hover {
  color: #ff6f61;
}

.price {
  font-size: 1rem;
  color: #555;
  font-weight: bold;
}

.no-products p {
  color: #999;
}

.add-to-wishlist {
  position: absolute;
  top: 10px;
  left: 10px;
  background-color: #9B7EBD;
  color: #fff;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
  z-index: 2;
}

.add-to-wishlist:hover {
  background-color: #ff4c3b;
  transform: scale(1.1);
}

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

/* Toast Notification Styles */
.toast-notification {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%) translateY(100px);
  background-color: #D4BEE4;
  color: #3B1E54;
  padding: 12px 24px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 1100;
  display: flex;
  align-items: center;
  opacity: 0;
  transition: all 0.3s ease;
}

.toast-notification.show {
  opacity: 1;
  transform: translateX(-50%) translateY(0);
}

.toast-content {
  display: flex;
  align-items: center;
}

.toast-icon {
  background-color: #3B1E54;
  color: white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  font-weight: bold;
}

.toast-message {
  font-weight: 500;
}
</style>