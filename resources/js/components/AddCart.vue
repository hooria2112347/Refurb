<template>
    <div class="cart-page">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12">
            <!-- Page Title and Description -->
            <div class="page-header mb-4">
              <h1>My Shopping Cart</h1>
              <p>Items you're ready to purchase</p>
            </div>
            
            <!-- Loading State -->
            <div v-if="loading" class="loading-state text-center my-5">
              <div class="spinner"></div>
              <p>Loading your cart...</p>
            </div>
            
            <!-- Error State -->
            <div v-else-if="error" class="error-state text-center my-5">
              <p class="error-message">{{ error }}</p>
              <button @click="fetchCart" class="retry-btn">Try Again</button>
            </div>
            
            <!-- Empty Cart State -->
            <div v-else-if="!cartItems.length" class="empty-state text-center my-5">
              <div class="empty-icon">🛒</div>
              <h3>Your cart is empty</h3>
              <p>Add items to your cart to get started!</p>
              <router-link to="/scrap-items" class="browse-btn">Browse Items</router-link>
            </div>
            
            <!-- Cart Items -->
            <div v-else>
              <div class="cart-items mt-4">
                <div class="cart-header d-none d-md-flex">
                  <div class="row w-100">
                    <div class="col-md-6">Product</div>
                    <div class="col-md-2 text-center">Price</div>
                    <div class="col-md-2 text-center">Quantity</div>
                    <div class="col-md-2 text-center">Subtotal</div>
                  </div>
                </div>
                
                <div v-for="item in cartItems" :key="item.id" class="cart-item">
                  <div class="row align-items-center">
                    <!-- Product Info -->
                    <div class="col-md-6 product-col">
                      <div class="d-flex align-items-center">
                        <img 
                          :src="item.images && item.images.length ? item.images[0] : 'default-image.jpg'" 
                          :alt="item.name" 
                          class="cart-item-image"
                          @click="viewProductDetails(item.id)"
                        />
                        <div class="product-info ml-3">
                          <h3 class="product-name" @click="viewProductDetails(item.id)">{{ item.name }}</h3>
                          <button @click="removeFromCart(item.id)" class="remove-btn">Remove</button>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Price -->
                    <div class="col-6 col-md-2 text-center">
                      <div class="mobile-label d-md-none">Price:</div>
                      <div class="price">PKR {{ item.price }}</div>
                    </div>
                    
                    <!-- Quantity -->
                    <div class="col-6 col-md-2 text-center">
                      <div class="mobile-label d-md-none">Quantity:</div>
                      <div class="quantity-controls">
                        <button 
                          @click="updateQuantity(item.id, item.quantity - 1)" 
                          :disabled="item.quantity <= 1"
                          class="quantity-btn"
                        >−</button>
                        <span class="quantity">{{ item.quantity }}</span>
                        <button 
                          @click="updateQuantity(item.id, item.quantity + 1)" 
                          class="quantity-btn"
                        >+</button>
                      </div>
                    </div>
                    
                    <!-- Subtotal -->
                    <div class="col-12 col-md-2 text-center mt-3 mt-md-0">
                      <div class="mobile-label d-md-none">Subtotal:</div>
                      <div class="subtotal">PKR {{ item.subtotal }}</div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Cart Summary -->
              <div class="cart-summary mt-4">
                <div class="row justify-content-end">
                  <div class="col-md-5">
                    <div class="summary-card">
                      <h3>Order Summary</h3>
                      <div class="summary-row">
                        <span>Items ({{ getTotalItems }})</span>
                        <span>PKR {{ cartTotal }}</span>
                      </div>
                      <div class="summary-row">
                        <span>Shipping</span>
                        <span>To be calculated at checkout</span>
                      </div>
                      <div class="summary-total">
                        <span>Total</span>
                        <span>PKR {{ cartTotal }}</span>
                      </div>
                      <button @click="proceedToCheckout" class="checkout-btn">Proceed to Checkout</button>
                      <button @click="clearCart" class="clear-cart-btn">Clear Cart</button>
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
          <h3>{{ confirmModalTitle }}</h3>
          <p>{{ confirmModalMessage }}</p>
          <div class="modal-actions">
            <button @click="confirmAction" class="confirm-btn">Yes</button>
            <button @click="cancelAction" class="cancel-btn">Cancel</button>
          </div>
        </div>
      </div>
      
      <!-- Login Modal -->
      <div v-if="showLoginModal" class="modal-overlay">
        <div class="modal-content">
          <h3>Please Log In</h3>
          <p>You need to be logged in to view your cart.</p>
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
        cartTotal: 0
      };
    },
    
    computed: {
      getTotalItems() {
        return this.cartItems.reduce((total, item) => total + item.quantity, 0);
      }
    },
    
    methods: {
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
          this.cartTotal = data.total;
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
      
      async updateQuantity(productId, newQuantity) {
        if (newQuantity < 1) return;
        
        const token = localStorage.getItem("access_token");
        
        try {
          const response = await fetch(`http://127.0.0.1:8000/api/cart/update/${productId}`, {
            method: 'PUT',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ quantity: newQuantity })
          });
          
          if (response.ok) {
            // Update local cart state
            await this.fetchCart();
          } else {
            throw new Error('Failed to update quantity');
          }
        } catch (err) {
          console.error("Error updating quantity:", err);
          this.error = "Failed to update quantity. Please try again.";
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
              this.cartTotal = this.cartItems.reduce((total, item) => total + item.subtotal, 0);
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
              this.cartTotal = 0;
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
    }
  };
  </script>

<style scoped>
.cart-page {
  min-height: 80vh;
}

.page-header h1 {
  color: #333;
  font-weight: 600;
}

.page-header p {
  color: #666;
  font-size: 1.1rem;
}

/* Loading, Error, Empty States */
.loading-state, .error-state, .empty-state {
  padding: 40px 20px;
}

.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top: 4px solid #D4BEE4;
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
  color: #dc3545;
  font-size: 1.1rem;
}

.retry-btn {
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  padding: 10px 20px;
  border-radius: 25px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.retry-btn:hover {
  background-color: #C2A3DC;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 20px;
}

.empty-state h3 {
  font-weight: 600;
  color: #333;
  margin-bottom: 10px;
}

.empty-state p {
  color: #666;
  margin-bottom: 20px;
}

.browse-btn {
  display: inline-block;
  background-color: #D4BEE4;
  color: #3B1E54;
  padding: 10px 25px;
  border-radius: 25px;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.2s, transform 0.2s;
}

.browse-btn:hover {
  background-color: #C2A3DC;
  transform: translateY(-2px);
}

/* Cart Header */
.cart-header {
  font-weight: 600;
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 15px;
  color: #555;
}

/* Cart Items */
.cart-item {
  background-color: #fff;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 15px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.cart-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.cart-item-image {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 6px;
  cursor: pointer;
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
  color: #333;
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
  color: #dc3545;
  padding: 0;
  font-size: 0.9rem;
  cursor: pointer;
  transition: color 0.2s;
}

.remove-btn:hover {
  color: #bd2130;
  text-decoration: underline;
}

.mobile-label {
  font-weight: 600;
  color: #555;
  margin-bottom: 5px;
}

.price, .subtotal {
  font-weight: 600;
  color: #333;
}

/* Quantity Controls */
.quantity-controls {
  display: flex;
  align-items: center;
  justify-content: center;
}

.quantity-btn {
  width: 30px;
  height: 30px;
  background-color: #f0f0f0;
  border: 1px solid #ddd;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1.2rem;
  line-height: 1;
  transition: background-color 0.2s;
}

.quantity-btn:hover:not(:disabled) {
  background-color: #e0e0e0;
}

.quantity-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity {
  margin: 0 10px;
  font-weight: 600;
  min-width: 30px;
  text-align: center;
}

/* Cart Summary */
.summary-card {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.summary-card h3 {
  font-weight: 600;
  color: #333;
  margin-bottom: 15px;
  text-align: center;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e0e0e0;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  font-weight: 700;
  font-size: 1.1rem;
  margin-top: 15px;
  margin-bottom: 20px;
  padding-top: 15px;
  border-top: 2px solid #ddd;
  color: #333;
}

.checkout-btn {
  display: block;
  width: 100%;
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  padding: 12px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s, transform 0.2s;
  margin-bottom: 10px;
}

.checkout-btn:hover {
  background-color: #C2A3DC;
  transform: translateY(-2px);
}

.clear-cart-btn {
  display: block;
  width: 100%;
  background-color: transparent;
  color: #6c757d;
  border: 1px solid #6c757d;
  padding: 12px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.clear-cart-btn:hover {
  background-color: #e9ecef;
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
  border-radius: 10px;
  padding: 25px;
  max-width: 400px;
  width: 90%;
  text-align: center;
}

.modal-content h3 {
  font-weight: 600;
  color: #333;
  margin-bottom: 15px;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 25px;
}

.confirm-btn, .primary-button {
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
  text-decoration: none;
}

.confirm-btn:hover, .primary-button:hover {
  background-color: #C2A3DC;
}

.cancel-btn, .secondary-button {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  color: #6c757d;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.cancel-btn:hover, .secondary-button:hover {
  background-color: #e9ecef;
}

/* Responsive design */
@media (max-width: 767px) {
  .mobile-label {
    display: block;
    text-align: left;
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
    width: 60px;
    height: 60px;
  }
  
  .product-name {
    font-size: 1rem;
  }
  
  .summary-card {
    padding: 15px;
  }
}
</style>