<template>
  <div class="cart-page bg-gray-50">
    <div class="container mx-auto py-8 px-4">
      <div class="row">
        <div class="col-12">
          <!-- Page Title and Description -->
          <div class="page-header mb-6">
            <h1 class="text-3xl font-bold text-gray-800">My Shopping Cart</h1>
            <p class="text-gray-600 mt-2">Items you're ready to purchase</p>
          </div>
          
          <!-- Loading State -->
          <div v-if="loading" class="loading-state text-center my-12">
            <div class="spinner"></div>
            <p class="mt-4 text-gray-600">Loading your cart...</p>
          </div>
          
          <!-- Error State -->
          <div v-else-if="error" class="error-state text-center my-12 p-6 bg-red-50 rounded-lg">
            <div class="error-icon mb-4">❌</div>
            <p class="error-message mb-4">{{ error }}</p>
            <button @click="fetchCart" class="retry-btn">Try Again</button>
          </div>
          
          <!-- Empty Cart State -->
          <div v-else-if="!cartItems.length" class="empty-state text-center my-12 p-8 bg-white rounded-lg shadow-sm">
            <div class="empty-icon mb-4">🛒</div>
            <h3 class="text-xl font-semibold mb-2">Your cart is empty</h3>
            <p class="text-gray-600 mb-6">Add items to your cart to get started!</p>
            <router-link to="/scrap-items" class="browse-btn">Browse Items</router-link>
          </div>
          
          <!-- Cart Items -->
          <div v-else class="cart-container">
            <div class="lg:flex gap-6">
              <!-- Cart Items List -->
              <div class="flex-grow">
                <div class="cart-header d-none d-md-flex bg-white p-4 rounded-t-lg shadow-sm border-b border-gray-200">
                  <div class="row w-100">
                    <div class="col-md-6 font-medium text-gray-700">Product</div>
                    <div class="col-md-2 text-center font-medium text-gray-700">Price</div>
                    <div class="col-md-2 text-center font-medium text-gray-700">Quantity</div>
                    <div class="col-md-2 text-center font-medium text-gray-700">Subtotal</div>
                  </div>
                </div>
                
                <div class="cart-items-wrapper bg-white rounded-b-lg shadow-sm overflow-hidden">
                  <div v-for="(item, index) in cartItems" :key="item.id" class="cart-item border-b border-gray-100 last:border-0">
                    <div class="row align-items-center p-4">
                      <!-- Product Info -->
                      <div class="col-md-6 product-col">
                        <div class="d-flex align-items-center">
                          <div class="relative">
                            <img 
                              :src="item.images && item.images.length ? item.images[0] : 'default-image.jpg'" 
                              :alt="item.name" 
                              class="cart-item-image"
                              @click="viewProductDetails(item.id)"
                            />
                          </div>
                          <div class="product-info ml-4">
                            <h3 class="product-name" @click="viewProductDetails(item.id)">{{ item.name }}</h3>
                            <button @click="removeFromCart(item.id)" class="remove-btn mt-1">
                              <span class="inline-flex items-center">
                                <span class="remove-icon mr-1">×</span> Remove
                              </span>
                            </button>
                          </div>
                        </div>
                      </div>
                      
                      <!-- Price -->
                      <div class="col-6 col-md-2 text-md-center">
                        <div class="mobile-label d-md-none">Price:</div>
                        <div class="price">PKR {{ item.price.toLocaleString() }}</div>
                      </div>
                      
                      <!-- Quantity -->
                      <div class="col-6 col-md-2 text-md-center">
                        <div class="mobile-label d-md-none">Quantity:</div>
                        <div class="quantity-controls">
                          <button 
                            @click="decrementQuantity(item)" 
                            :disabled="item.quantity <= 1"
                            class="quantity-btn decrement"
                            aria-label="Decrease quantity"
                          >−</button>
                          <span class="quantity">{{ item.quantity }}</span>
                          <button 
                            @click="incrementQuantity(item)" 
                            class="quantity-btn increment"
                            aria-label="Increase quantity"
                          >+</button>
                        </div>
                      </div>
                      
                      <!-- Subtotal -->
                      <div class="col-12 col-md-2 text-md-center mt-3 mt-md-0">
                        <div class="mobile-label d-md-none">Subtotal:</div>
                        <div class="subtotal">PKR {{ calculateSubtotal(item).toLocaleString() }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Cart Summary -->
              <div class="summary-wrapper lg:w-80 mt-6 lg:mt-0">
                <div class="summary-card sticky top-4">
                  <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
                  <div class="summary-row">
                    <span class="text-gray-600">Items ({{ getTotalItems }})</span>
                    <span class="font-medium">PKR {{ calculateTotal().toLocaleString() }}</span>
                  </div>
                  <div class="summary-row">
                    <span class="text-gray-600">Shipping</span>
                    <span class="text-gray-600">To be calculated</span>
                  </div>
                  <div class="summary-total">
                    <span>Total</span>
                    <span class="text-xl">PKR {{ calculateTotal().toLocaleString() }}</span>
                  </div>
                  <button @click="proceedToCheckout" class="checkout-btn">
                    Proceed to Checkout
                  </button>
                  <button @click="clearCart" class="clear-cart-btn">
                    Clear Cart
                  </button>
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
        <h3 class="text-lg font-semibold mb-2">{{ confirmModalTitle }}</h3>
        <p class="text-gray-600">{{ confirmModalMessage }}</p>
        <div class="modal-actions">
          <button @click="confirmAction" class="confirm-btn">Yes</button>
          <button @click="cancelAction" class="cancel-btn">Cancel</button>
        </div>
      </div>
    </div>
    
    <!-- Login Modal -->
    <div v-if="showLoginModal" class="modal-overlay">
      <div class="modal-content">
        <h3 class="text-lg font-semibold mb-2">Please Log In</h3>
        <p class="text-gray-600 mb-4">You need to be logged in to view your cart.</p>
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
      cartItems: [],
      loading: true,
      error: null,
      showConfirmModal: false,
      confirmModalTitle: '',
      confirmModalMessage: '',
      confirmActionType: null,
      actionItemId: null,
      showLoginModal: false,
      pendingUpdates: {},
      updateTimeout: null
    };
  },
  
  computed: {
    getTotalItems() {
      return this.cartItems.reduce((total, item) => total + item.quantity, 0);
    }
  },
  
  methods: {
    calculateSubtotal(item) {
      return item.price * item.quantity;
    },
    
    calculateTotal() {
      return this.cartItems.reduce((total, item) => {
        return total + (item.price * item.quantity);
      }, 0);
    },
    
    async fetchCart() {
      this.loading = true;
      this.error = null;
      
      const token = localStorage.getItem("access_token");
      if (!token) {
        this.showLoginModal = true;
        this.loading = false;
        return;
      }
      
      try {
        const response = await fetch('http://127.0.0.1:8000/api/cart', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (!response.ok) {
          throw new Error('Failed to fetch cart');
        }
        
        const data = await response.json();
        this.cartItems = data.items;
      } catch (err) {
        console.error("Error fetching cart:", err);
        this.error = "Could not load your cart. Please try again.";
      } finally {
        this.loading = false;
      }
    },
    
    viewProductDetails(productId) {
      this.$router.push({ name: 'product-details', params: { id: productId } });
    },
    
    removeFromCart(productId) {
      this.confirmModalTitle = "Remove Item";
      this.confirmModalMessage = "Are you sure you want to remove this item from your cart?";
      this.confirmActionType = 'remove';
      this.actionItemId = productId;
      this.showConfirmModal = true;
    },
    
    // Client-side quantity increment with debounced API update
    incrementQuantity(item) {
      item.quantity += 1;
      this.debouncedUpdateQuantity(item.id, item.quantity);
    },
    
    // Client-side quantity decrement with debounced API update
    decrementQuantity(item) {
      if (item.quantity > 1) {
        item.quantity -= 1;
        this.debouncedUpdateQuantity(item.id, item.quantity);
      }
    },
    
    // Debounced API update to prevent excessive API calls
    debouncedUpdateQuantity(productId, newQuantity) {
      this.pendingUpdates[productId] = newQuantity;
      
      if (this.updateTimeout) {
        clearTimeout(this.updateTimeout);
      }
      
      this.updateTimeout = setTimeout(() => {
        this.syncPendingUpdates();
      }, 500); // Wait 500ms before sending updates to API
    },
    
    // Sync any pending quantity updates with the server
    async syncPendingUpdates() {
      const token = localStorage.getItem("access_token");
      const updates = { ...this.pendingUpdates };
      this.pendingUpdates = {};
      
      for (const [productId, quantity] of Object.entries(updates)) {
        try {
          await fetch(`http://127.0.0.1:8000/api/cart/update/${productId}`, {
            method: 'PUT',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ quantity: quantity })
          });
        } catch (err) {
          console.error("Error updating quantity:", err);
          // If there's an error, we could restore the original quantity or show an error message
          // For now, we'll just log the error and keep the client-side quantity
        }
      }
    },
    
    clearCart() {
      this.confirmModalTitle = "Clear Cart";
      this.confirmModalMessage = "Are you sure you want to remove all items from your cart?";
      this.confirmActionType = 'clear';
      this.showConfirmModal = true;
    },
    
    async confirmAction() {
      const token = localStorage.getItem("access_token");
      
      try {
        if (this.confirmActionType === 'remove') {
          const response = await fetch(`http://127.0.0.1:8000/api/cart/remove/${this.actionItemId}`, {
            method: 'DELETE',
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });
          
          if (response.ok) {
            // Remove the item from local cart state
            this.cartItems = this.cartItems.filter(item => item.id !== this.actionItemId);
          } else {
            throw new Error('Failed to remove item');
          }
        } else if (this.confirmActionType === 'clear') {
          const response = await fetch('http://127.0.0.1:8000/api/cart/clear', {
            method: 'DELETE',
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });
          
          if (response.ok) {
            // Clear the cart
            this.cartItems = [];
          } else {
            throw new Error('Failed to clear cart');
          }
        }
      } catch (err) {
        console.error("Error performing cart action:", err);
        this.error = "Action failed. Please try again.";
      } finally {
        this.showConfirmModal = false;
        this.actionItemId = null;
      }
    },
    
    cancelAction() {
      this.showConfirmModal = false;
      this.actionItemId = null;
    },
    
    closeLoginModal() {
      this.showLoginModal = false;
      this.$router.push('/browse-scrap');
    },
    
    proceedToCheckout() {
      // This will be implemented when you have a checkout page
      alert('Checkout functionality will be implemented in the future!');
    }
  },
  
  mounted() {
    this.fetchCart();
  },
  
  beforeUnmount() {
    // Make sure to sync any pending updates when component is unmounted
    if (this.updateTimeout) {
      clearTimeout(this.updateTimeout);
      this.syncPendingUpdates();
    }
  }
};
</script>

<style scoped>
.cart-page {
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

/* Cart Container */
.cart-container {
  margin-bottom: 40px;
}

.cart-items-wrapper {
  transition: box-shadow 0.3s ease;
}

.cart-items-wrapper:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* Cart Header */
.cart-header {
  font-weight: 600;
  padding: 16px;
  border-radius: 8px 8px 0 0;
  color: #4a5568;
}

/* Cart Items */
.cart-item {
  background-color: #fff;
  transition: background-color 0.2s ease;
}

.cart-item:hover {
  background-color: #f7fafc;
}

.cart-item-image {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.cart-item-image:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.product-col {
  margin-bottom: 15px;
}

.product-info {
  margin-left: 15px;
}

.product-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 8px;
  cursor: pointer;
  transition: color 0.2s;
}

.product-name:hover {
  color: #9B7EBD;
}

.remove-btn {
  background: none;
  border: none;
  color: #e53e3e;
  padding: 0;
  font-size: 0.9rem;
  cursor: pointer;
  transition: color 0.2s;
  display: inline-flex;
  align-items: center;
}

.remove-btn:hover {
  color: #c53030;
}

.remove-icon {
  font-size: 1.2rem;
  font-weight: bold;
}

.mobile-label {
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 5px;
}

.price, .subtotal {
  font-weight: 600;
  color: #2d3748;
}
.subtotal{
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
}
/* Quantity Controls */
.quantity-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  overflow: hidden;
  width: fit-content;
  margin: 0 auto;
}

.quantity-btn {
  width: 32px;
  height: 32px;
  background-color: #f7fafc;
  border: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1.2rem;
  line-height: 1;
  transition: all 0.2s ease;
}

.quantity-btn:hover:not(:disabled) {
  background-color: #edf2f7;
}

.quantity-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.decrement {
  border-radius: 8px 0 0 8px;
}

.increment {
  border-radius: 0 8px 8px 0;
}

.quantity {
  min-width: 40px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: white;
  border-top: 1px solid #e2e8f0;
  border-bottom: 1px solid #e2e8f0;
  font-weight: 600;
  color: #2d3748;
}

/* Cart Summary */
.summary-wrapper {
  position: relative;
}

.summary-card {
  background-color: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 20px;
}

.summary-card h3 {
  font-weight: 600;
  color: #3B1E54;
  margin-bottom: 20px;
  text-align: center;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  font-weight: 700;
  font-size: 1.1rem;
  margin-top: 15px;
  margin-bottom: 24px;
  padding-top: 16px;
  border-top: 2px solid #e2e8f0;
  color: #2d3748;
}

.checkout-btn {
  display: block;
  width: 100%;
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  padding: 14px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: 12px;
}

.checkout-btn:hover {
  background-color: #EEEEEE;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.clear-cart-btn {
  display: block;
  width: 100%;
  background-color: white;
  color: #718096;
  border: 1px solid #e2e8f0;
  padding: 12px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.clear-cart-btn:hover {
  background-color: #f7fafc;
  color: #4a5568;
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
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
}

.confirm-btn:hover, .primary-button:hover {
  background-color: #EEEEEE;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.cancel-btn, .secondary-button {
  background-color: #EEEEEE;
  border: 1px solid #e2e8f0;
  color: #3B1E54;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.cancel-btn:hover, .secondary-button:hover {
  background-color: #D4BEE4;
  color: #4a5568;
}

/* Responsive design */
@media (max-width: 767px) {
  .mobile-label {
    display: block;
    text-align: left;
    font-size: 0.85rem;
  }
  
  .price, .subtotal {
    text-align: left;
  }
  
  .quantity-controls {
    justify-content: flex-start;
  }
  
  .product-col {
    margin-bottom: 15px;
  }
}

@media (max-width: 576px) {
  .cart-item {
    padding: 12px;
  }
  
  .cart-item-image {
    width: 70px;
    height: 70px;
  }
  
  .product-name {
    font-size: 1rem;
  }
  
  .summary-card {
    padding: 20px;
  }
}
</style>