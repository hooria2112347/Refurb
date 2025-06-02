<template>
  <div class="checkout-page bg-gray-50">
    <div class="container mx-auto py-8 px-4">
      <div class="row">
        <div class="col-12">
          <!-- Page Title and Description -->
          <div class="page-header mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Checkout</h1>
            <p class="text-gray-600 mt-2">Complete your purchase</p>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="loading-state text-center my-12">
            <div class="spinner"></div>
            <p class="mt-4 text-gray-600">Loading checkout...</p>
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
            <p class="text-gray-600 mb-6">Add items to your cart before checking out.</p>
            <router-link to="/scrap-items" class="browse-btn">Browse Items</router-link>
          </div>

          <!-- Checkout Form -->
          <div v-else class="checkout-container">
            <div class="lg:flex gap-6">
              <!-- Order Summary -->
              <div class="flex-grow">
                <div class="order-summary bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                  <div class="summary-header bg-gray-50 p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Order Summary</h3>
                  </div>
                  
                  <div class="summary-items px-4">
                    <div v-for="(item, index) in cartItems" :key="item.id" 
                         class="summary-item py-4 border-b border-gray-100 last:border-0">
                      <div class="flex items-center">
                        <div class="item-image-container">
                          <img 
                            :src="item.images && item.images.length ? item.images[0] : 'default-image.jpg'" 
                            :alt="item.name" 
                            class="item-image"
                          />
                        </div>
                        <div class="item-details ml-4">
                          <h4 class="item-name text-gray-800 font-medium">{{ item.name }}</h4>
                          <div class="item-meta text-gray-600 text-sm mt-1">
                            <span class="quantity">Quantity: {{ item.quantity }}</span>
                          </div>
                        </div>
                        <div class="item-price ml-auto font-medium">
                          PKR {{ (item.price * item.quantity).toLocaleString() }}
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="summary-totals p-4 bg-gray-50">
                    <div class="total-row flex justify-between py-2">
                      <span class="text-gray-600">Subtotal</span>
                      <span class="font-medium">PKR {{ calculateSubtotal().toLocaleString() }}</span>
                    </div>
                    <div class="total-row flex justify-between py-2">
                      <span class="text-gray-600">Shipping</span>
                      <span class="font-medium">PKR {{ shipping.toLocaleString() }}</span>
                    </div>
                    <div class="total-row grand-total flex justify-between py-2 border-t border-gray-200 mt-2 pt-2">
                      <span class="font-semibold">Total</span>
                      <span class="font-bold text-lg">PKR {{ calculateTotal().toLocaleString() }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Checkout Form -->
              <div class="lg:w-96">
                <div class="checkout-form bg-white rounded-lg shadow-sm p-6">
                  <h3 class="text-lg font-semibold text-gray-800 mb-4">Delivery Information</h3>
                  
                  <form @submit.prevent="placeOrder">
                    <div class="form-group mb-4">
                      <label class="block text-gray-700 text-sm font-medium mb-2" for="name">
                        Full Name
                      </label>
                      <input 
                        type="text"
                        id="name"
                        v-model="formData.name"
                        class="form-input w-full px-3 py-2 border border-gray-300 rounded-md"
                        placeholder="Enter your full name"
                        required
                      />
                    </div>
                    
                    <div class="form-group mb-4">
                      <label class="block text-gray-700 text-sm font-medium mb-2" for="phone">
                        Phone Number
                      </label>
                      <input 
                        type="tel"
                        id="phone"
                        v-model="formData.phone"
                        class="form-input w-full px-3 py-2 border border-gray-300 rounded-md"
                        placeholder="Enter your phone number"
                        required
                      />
                    </div>
                    
                    <div class="form-group mb-4">
                      <label class="block text-gray-700 text-sm font-medium mb-2" for="address">
                        Delivery Address
                      </label>
                      <textarea 
                        id="address"
                        v-model="formData.address"
                        class="form-textarea w-full px-3 py-2 border border-gray-300 rounded-md"
                        placeholder="Enter your complete address"
                        rows="3"
                        required
                      ></textarea>
                    </div>
                    
                    <div class="form-group mb-6">
                      <label class="block text-gray-700 text-sm font-medium mb-2" for="notes">
                        Order Notes (Optional)
                      </label>
                      <textarea 
                        id="notes"
                        v-model="formData.notes"
                        class="form-textarea w-full px-3 py-2 border border-gray-300 rounded-md"
                        placeholder="Any special instructions for delivery"
                        rows="2"
                      ></textarea>
                    </div>
                    
                    <div class="payment-options mb-6">
                      <h4 class="text-md font-semibold text-gray-800 mb-3">Payment Method</h4>
                      <div class="payment-option mb-2">
                        <input
                          type="radio"
                          id="cod"
                          name="payment"
                          value="cod"
                          v-model="formData.paymentMethod"
                          class="mr-2"
                          checked
                        />
                        <label for="cod">Cash on Delivery (COD)</label>
                      </div>
                    </div>
                    
                    <button 
                      type="submit" 
                      class="place-order-btn w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 transition duration-200"
                      :disabled="isSubmitting"
                    >
                      <span v-if="!isSubmitting">Place Order</span>
                      <span v-else class="inline-flex items-center">
                        <span class="spinner-small mr-2"></span>
                        Processing...
                      </span>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Confirmation Modal -->
    <div v-if="showConfirmModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="modal-content bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <div class="success-icon mb-4 text-5xl text-green-500 text-center">✓</div>
        <h3 class="text-xl font-semibold mb-2 text-center">Order Placed Successfully!</h3>
        <p class="text-gray-600 mb-6 text-center">Your order has been placed successfully. Order ID: #{{ orderId }}</p>
        <div class="modal-actions flex flex-col sm:flex-row gap-3 justify-center">
          <router-link :to="{ name: 'order-history-with-id', params: { orderId: orderId } }" class="view-orders-btn bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200 text-center">
          View My Orders
        </router-link>
        <router-link to="/scrap-items" class="shop-more-btn bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300 transition duration-200 text-center">
          Shop More
        </router-link>
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
      isSubmitting: false,
      showConfirmModal: false,
      orderId: null,
      shipping: 200, // Fixed shipping cost (PKR)
      formData: {
        name: '',
        phone: '',
        address: '',
        notes: '',
        paymentMethod: 'cod'
      }
    };
  },
  
  methods: {
    async fetchCart() {
      this.loading = true;
      this.error = null;
      
      const token = localStorage.getItem("access_token");
      if (!token) {
        this.$router.push('/login');
        return;
      }
      
      try {
        const response = await fetch('http://127.0.0.1:8000/api/cart', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.message || 'Failed to fetch cart');
        }
        
        const data = await response.json();
        this.cartItems = data.items || [];
        
        if (this.cartItems.length === 0) {
          // Redirect to cart if cart is empty
          this.$router.push('/cart');
        }
        
        // Pre-fill name from user data if available
        if (data.user && data.user.name) {
          this.formData.name = data.user.name;
        }
      } catch (err) {
        console.error("Error fetching cart:", err);
        this.error = err.message || "Could not load your cart. Please try again.";
      } finally {
        this.loading = false;
      }
    },
    
    calculateSubtotal() {
      return this.cartItems.reduce((total, item) => {
        return total + (item.price * item.quantity);
      }, 0);
    },
    
    calculateTotal() {
      return this.calculateSubtotal() + this.shipping;
    },
    
    async placeOrder() {
      if (!this.validateForm()) {
        return;
      }
      
      const token = localStorage.getItem("access_token");
      if (!token) {
        this.$router.push('/login');
        return;
      }
      
      this.isSubmitting = true;
      this.error = null;
      
      try {
        const orderData = {
          shipping_address: this.formData.address,
          contact_phone: this.formData.phone,
          payment_method: 'cash_on_delivery', // Use the exact value expected by the backend
          notes: this.formData.notes || ''
        };
        
        console.log("Sending order data:", orderData);
        
        // Add detailed logging for API call
        console.log("Making API request to:", 'http://127.0.0.1:8000/api/orders/checkout');
        console.log("With headers:", {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        });
        
        const response = await fetch('http://127.0.0.1:8000/api/orders/checkout', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(orderData)
        });
        
        // Log full response for debugging
        const responseText = await response.text();
        console.log("API Response Status:", response.status);
        console.log("API Response Text:", responseText);
        
        // Try to parse the response as JSON
        let responseData;
        try {
          responseData = JSON.parse(responseText);
        } catch (e) {
          console.error("Failed to parse response as JSON:", e);
          throw new Error("Invalid response format from server");
        }
        
        if (!response.ok) {
          console.error("Order API error response:", responseData);
          throw new Error(responseData?.error || responseData?.message || 'Failed to place order');
        }
        
        console.log("Order placed successfully:", responseData);
        this.orderId = responseData.order_id || responseData.id;
        this.showConfirmModal = true;
        
        // Clear cart data from local storage if you're storing it there
        // localStorage.removeItem('cart');
      } catch (err) {
        console.error("Error placing order:", err);
        this.error = err.message || "Could not place your order. Please try again.";
        // Scroll to error message
        this.$nextTick(() => {
          const errorElement = document.querySelector('.error-state');
          if (errorElement) {
            errorElement.scrollIntoView({ behavior: 'smooth' });
          }
        });
      } finally {
        this.isSubmitting = false;
      }
    },
    
    validateForm() {
      // Basic form validation
      if (!this.formData.name || this.formData.name.trim() === '') {
        this.error = "Please enter your full name";
        return false;
      }
      
      if (!this.formData.phone || this.formData.phone.trim() === '') {
        this.error = "Please enter your phone number";
        return false;
      }
      
      // Validate phone number format (optional)
      const phoneRegex = /^[0-9+\-\s]{10,15}$/;
      if (this.formData.phone.trim() && !phoneRegex.test(this.formData.phone.trim())) {
        this.error = "Please enter a valid phone number";
        return false;
      }
      
      if (!this.formData.address || this.formData.address.trim() === '') {
        this.error = "Please enter your delivery address";
        return false;
      }
      
      return true;
    }
  },
  
  mounted() {
    this.fetchCart();
  }
};
</script>
  
<style scoped>
  .checkout-page {
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
  
  .spinner-small {
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top: 2px solid #ffffff;
    width: 16px;
    height: 16px;
    animation: spin 1s linear infinite;
    display: inline-block;
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
  
  /* Item Summary */
  .item-image-container {
    width: 60px;
    height: 60px;
    flex-shrink: 0;
  }
  
  .item-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
  }
  
  .item-name {
    font-size: 1rem;
    line-height: 1.3;
  }
  
  /* Form Styling */
  .form-input, .form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background-color: #f8fafc;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  
  .form-input:focus, .form-textarea:focus {
    border-color: #9B7EBD;
    box-shadow: 0 0 0 3px rgba(155, 126, 189, 0.2);
    outline: none;
  }
  
  .form-textarea {
    resize: vertical;
    min-height: 80px;
  }
  
  .place-order-btn {
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
  }
  
  .place-order-btn:hover:not(:disabled) {
    background-color: #9B7EBD;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
  
  .place-order-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
  }
  
  /* Confirmation Modal */
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
    padding: 32px;
    max-width: 450px;
    width: 90%;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  }
  
  .success-icon {
    background-color: #10B981;
    color: white;
    font-size: 1.8rem;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
  }
  
  .modal-actions {
    display: flex;
    justify-content: center;
    gap: 16px;
    margin-top: 10px;
  }
  
  .view-orders-btn {
    background-color: #D4BEE4;
    color: #3B1E54;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
  }
  
  .view-orders-btn:hover {
    background-color: #9B7EBD;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
  
  .shop-more-btn {
    background-color: #EDF2F7;
    color: #4A5568;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
  }
  
  .shop-more-btn:hover {
    background-color: #E2E8F0;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
  
  /* Responsive */
  @media (max-width: 992px) {
    .lg\:w-96 {
      width: 100%;
      margin-top: 24px;
    }
  }
  
  @media (max-width: 576px) {
    .modal-content {
      padding: 24px 16px;
    }
    
    .modal-actions {
      flex-direction: column;
      gap: 12px;
    }
    
    .view-orders-btn, .shop-more-btn {
      width: 100%;
    }
  }
  </style>