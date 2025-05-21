<template>
    <div class="order-history-page bg-gray-50">
      <div class="container mx-auto py-8 px-4">
        <div class="row">
          <div class="col-12">
            <!-- Page Title and Description -->
            <div class="page-header mb-6">
              <h1 class="text-3xl font-bold text-gray-800">My Order History</h1>
              <p class="text-gray-600 mt-2">Track and manage your previous orders</p>
            </div>
  
            <!-- Success Alert (when coming from checkout) -->
            <div v-if="successMessage" class="success-alert mb-6">
              <div class="alert-content">
                <div class="alert-icon">✓</div>
                <div class="alert-message">{{ successMessage }}</div>
                <button @click="dismissAlert" class="dismiss-btn">×</button>
              </div>
            </div>
            
            <!-- Loading State -->
            <div v-if="loading" class="loading-state text-center my-12">
              <div class="spinner"></div>
              <p class="mt-4 text-gray-600">Loading your orders...</p>
            </div>
            
            <!-- Error State -->
            <div v-else-if="error" class="error-state text-center my-12 p-6 bg-red-50 rounded-lg">
              <div class="error-icon mb-4">❌</div>
              <p class="error-message mb-4">{{ error }}</p>
              <button @click="fetchOrders" class="retry-btn">Try Again</button>
            </div>
            
            <!-- Empty Orders State -->
            <div v-else-if="!orders.length" class="empty-state text-center my-12 p-8 bg-white rounded-lg shadow-sm">
              <div class="empty-icon mb-4">📦</div>
              <h3 class="text-xl font-semibold mb-2">No orders yet</h3>
              <p class="text-gray-600 mb-6">Looks like you haven't placed any orders yet.</p>
              <router-link to="/scrap-items" class="browse-btn">Browse Items</router-link>
            </div>
            
            <!-- Orders List -->
            <div v-else class="orders-container">
              <div v-for="order in orders" :key="order.order_id" class="order-card mb-6">
                <div class="order-header">
                  <div class="order-meta">
                    <div class="order-id">
                      <span class="label">Order ID:</span>
                      <span class="value">#{{ order.order_id }}</span>
                    </div>
                    <div class="order-date">
                      <span class="label">Date:</span>
                      <span class="value">{{ formatDate(order.created_at) }}</span>
                    </div>
                  </div>
                  <div class="order-status" :class="getStatusClass(order.status)">
                    {{ capitalizeFirstLetter(order.status) }}
                  </div>
                </div>
                
                <div class="order-body">
                  <div class="order-items">
                    <div v-for="item in order.items" :key="item.id" class="order-item">
                      <div class="item-image-container">
                        <img 
                          :src="item.image_path ? item.image_path : 'default-image.jpg'" 
                          :alt="item.name" 
                          class="item-image"
                        />
                      </div>
                      <div class="item-details">
                        <h4 class="item-name">{{ item.name }}</h4>
                        <div class="item-meta">
                          <span class="item-price">PKR {{ item.price.toLocaleString() }}</span>
                          <span class="item-quantity">× {{ item.quantity }}</span>
                        </div>
                      </div>
                      <div class="item-subtotal">
                        PKR {{ (item.price * item.quantity).toLocaleString() }}
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="order-footer">
                  <div class="order-totals">
                    <div class="total-row">
                      <span>Total Items</span>
                      <span>{{ getTotalItems(order) }}</span>
                    </div>
                    <div class="total-row grand-total">
                      <span>Grand Total</span>
                      <span>PKR {{ order.total_amount.toLocaleString() }}</span>
                    </div>
                  </div>
                  
                  <div class="order-actions">
                    <button 
                      v-if="order.status === 'pending'" 
                      @click="cancelOrder(order.order_id)" 
                      class="cancel-order-btn"
                    >
                      Cancel Order
                    </button>
                    <button @click="viewOrderDetails(order.order_id)" class="view-details-btn">
                      View Details
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
      
      <!-- Order Detail Modal -->
      <div v-if="selectedOrder" class="modal-overlay">
        <div class="modal-content order-detail-modal">
          <div class="modal-header">
            <h3 class="text-lg font-semibold">Order Details</h3>
            <button @click="closeOrderDetails" class="close-btn">×</button>
          </div>
          
          <div class="modal-body">
            <div class="order-info">
              <div class="info-row">
                <span class="info-label">Order ID:</span>
                <span class="info-value">#{{ selectedOrder.order_id }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value">{{ formatDate(selectedOrder.created_at) }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value status" :class="getStatusClass(selectedOrder.status)">
                  {{ capitalizeFirstLetter(selectedOrder.status) }}
                </span>
              </div>
              <div class="info-row">
                <span class="info-label">Total Amount:</span>
                <span class="info-value">PKR {{ selectedOrder.total_amount.toLocaleString() }}</span>
              </div>
            </div>
            
            <h4 class="items-title">Items</h4>
            <div class="detail-items">
              <div v-for="item in selectedOrder.items" :key="item.id" class="detail-item">
                <div class="item-image-container">
                  <img 
                    :src="item.image_path ? item.image_path : 'default-image.jpg'" 
                    :alt="item.name" 
                    class="item-image"
                  />
                </div>
                <div class="item-info">
                  <h5 class="item-name">{{ item.name }}</h5>
                  <div class="item-price-qty">
                    <span>PKR {{ item.price.toLocaleString() }} × {{ item.quantity }}</span>
                  </div>
                </div>
                <div class="item-subtotal">
                  PKR {{ (item.price * item.quantity).toLocaleString() }}
                </div>
              </div>
            </div>
          </div>
          
          <div class="modal-footer">
            <button 
              v-if="selectedOrder.status === 'pending'" 
              @click="cancelOrderFromDetails(selectedOrder.order_id)" 
              class="cancel-order-btn"
            >
              Cancel Order
            </button>
            <button @click="closeOrderDetails" class="close-details-btn">Close</button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        orders: [],
        loading: true,
        error: null,
        successMessage: null,
        highlightedOrderId: null,
        showConfirmModal: false,
        confirmModalTitle: '',
        confirmModalMessage: '',
        actionOrderId: null,
        selectedOrder: null
      };
    },
    
    methods: {
      async fetchOrders() {
        this.loading = true;
        this.error = null;
        
        const token = localStorage.getItem("access_token");
        if (!token) {
          this.$router.push('/login');
          return;
        }
        
        try {
          const response = await fetch('http://127.0.0.1:8000/api/orders', {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });
          
          if (!response.ok) {
            throw new Error('Failed to fetch orders');
          }
          
          const data = await response.json();
          this.orders = data;
          
          // If we have a highlighted order ID from route params, scroll to it
          if (this.highlightedOrderId) {
            this.$nextTick(() => {
              const element = document.getElementById(`order-${this.highlightedOrderId}`);
              if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
                element.classList.add('highlight-order');
                
                // Remove highlight class after animation completes
                setTimeout(() => {
                  element.classList.remove('highlight-order');
                }, 3000);
              }
            });
          }
        } catch (err) {
          console.error("Error fetching orders:", err);
          this.error = "Could not load your orders. Please try again.";
        } finally {
          this.loading = false;
        }
      },
      
      formatDate(dateString) {
        const options = { 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        };
        return new Date(dateString).toLocaleDateString(undefined, options);
      },
      
      capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
      },
      
      getStatusClass(status) {
        const statusClasses = {
          'pending': 'status-pending',
          'processing': 'status-processing',
          'shipped': 'status-shipped',
          'delivered': 'status-delivered',
          'cancelled': 'status-cancelled'
        };
        
        return statusClasses[status] || 'status-default';
      },
      
      getTotalItems(order) {
        return order.items.reduce((total, item) => total + item.quantity, 0);
      },
      
      cancelOrder(orderId) {
        this.confirmModalTitle = "Cancel Order";
        this.confirmModalMessage = "Are you sure you want to cancel this order?";
        this.actionOrderId = orderId;
        this.showConfirmModal = true;
      },
      
      cancelOrderFromDetails(orderId) {
        this.cancelOrder(orderId);
        this.closeOrderDetails();
      },
      
      async confirmAction() {
        const token = localStorage.getItem("access_token");
        
        try {
          const response = await fetch(`http://127.0.0.1:8000/api/orders/${this.actionOrderId}/cancel`, {
            method: 'PUT',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          });
          
          if (response.ok) {
            // Update the order status in our local state
            const order = this.orders.find(o => o.order_id === this.actionOrderId);
            if (order) {
              order.status = 'cancelled';
            }
            
            this.successMessage = "Order cancelled successfully";
          } else {
            throw new Error('Failed to cancel order');
          }
        } catch (err) {
          console.error("Error cancelling order:", err);
          this.error = "Failed to cancel order. Please try again.";
        } finally {
          this.showConfirmModal = false;
          this.actionOrderId = null;
        }
      },
      
      cancelAction() {
        this.showConfirmModal = false;
        this.actionOrderId = null;
      },
      
      viewOrderDetails(orderId) {
        this.selectedOrder = this.orders.find(order => order.order_id === orderId);
      },
      
      closeOrderDetails() {
        this.selectedOrder = null;
      },
      
      dismissAlert() {
        this.successMessage = null;
      }
    },
    
    mounted() {
      // Check for route params that might indicate a recently placed order
      if (this.$route.params.message) {
        this.successMessage = this.$route.params.message;
      }
      
      if (this.$route.params.orderId) {
        this.highlightedOrderId = this.$route.params.orderId;
      }
      
      this.fetchOrders();
    }
  };
  </script>
  
  <style scoped>
  .order-history-page {
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
  
  /* Success Alert */
  .success-alert {
    background-color: #D4EDDA;
    border-radius: 8px;
    border-left: 4px solid #28A745;
    padding: 16px;
  }
  
  .alert-content {
    display: flex;
    align-items: center;
  }
  
  .alert-icon {
    background-color: #28A745;
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
  
  .alert-message {
    flex-grow: 1;
    color: #155724;
    font-weight: 500;
  }
  
  .dismiss-btn {
    background: none;
    border: none;
    color: #155724;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0 8px;
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
  
  /* Order Card */
  .order-card {
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.3s ease, transform 0.3s ease;
  }
  
  .order-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
  }
  
  .highlight-order {
    animation: highlight 3s ease;
  }
  
  @keyframes highlight {
    0% { box-shadow: 0 0 0 2px #D4BEE4; }
    50% { box-shadow: 0 0 0 4px #D4BEE4; }
    100% { box-shadow: 0 0 0 0 #D4BEE4; }
  }
  
  /* Order Header */
  .order-header {
    background-color: #f9f9fc;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .order-meta {
    display: flex;
    gap: 20px;
  }
  
  .order-id, .order-date {
    display: flex;
    align-items: center;
  }
  
  .label {
    color: #718096;
    margin-right: 6px;
    font-size: 0.9rem;
  }
  
  .value {
    font-weight: 600;
    color: #2d3748;
  }
  
  .order-status {
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 0.9rem;
    font-weight: 600;
  }
  
  .status-pending {
    background-color: #FEF9C3;
    color: #854D0E;
  }
  
  .status-processing {
    background-color: #E0F2FE;
    color: #075985;
  }
  
  .status-shipped {
    background-color: #DBEAFE;
    color: #1E40AF;
  }
  
  .status-delivered {
    background-color: #DCFCE7;
    color: #166534;
  }
  
  .status-cancelled {
    background-color: #FEE2E2;
    color: #991B1B;
  }
  
  /* Order Body */
  .order-body {
    padding: 20px;
  }
  
  .order-items {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  
  .order-item {
    display: flex;
    align-items: center;
    padding-bottom: 16px;
    border-bottom: 1px solid #f1f1f1;
  }
  
  .order-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }
  
  .item-image-container {
    width: 70px;
    height: 70px;
    flex-shrink: 0;
    margin-right: 16px;
  }
  
  .item-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
  }
  
  .item-details {
    flex-grow: 1;
  }
  
  .item-name {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 6px;
    font-size: 1.05rem;
  }
  
  .item-meta {
    display: flex;
    gap: 12px;
    color: #718096;
    font-size: 0.95rem;
  }
  
  .item-subtotal {
    font-weight: 600;
    color: #2d3748;
    font-size: 1.05rem;
  }
  
  /* Order Footer */
  .order-footer {
    padding: 16px 20px;
    background-color: #f9f9fc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .order-totals {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  
  .total-row {
    display: flex;
    justify-content: space-between;
    gap: 24px;
  }
  
  .total-row span:first-child {
    color: #718096;
  }
  
  .total-row span:last-child {
    font-weight: 500;
    color: #2d3748;
  }
  
  .grand-total {
    font-weight: 600;
    color: #3B1E54;
    font-size: 1.1rem;
  }
  
  .order-actions {
    display: flex;
    gap: 12px;
  }
  
  .cancel-order-btn {
    background-color: #FEE2E2;
    color: #991B1B;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  
  .cancel-order-btn:hover {
    background-color: #FCA5A5;
  }
  
  .view-details-btn {
    background-color: #D4BEE4;
    color: #3B1E54;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  
  .view-details-btn:hover {
    background-color: #9B7EBD;
    color: white;
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
    padding: 28px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  }
  
  .order-detail-modal {
    max-width: 600px;
    padding: 0;
    text-align: left;
  }
  
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
  }
  
  .close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    color: #718096;
    cursor: pointer;
    transition: color 0.2s;
  }
  
  .close-btn:hover {
    color: #2d3748;
  }
  
  .modal-body {
    padding: 20px;
    max-height: 60vh;
    overflow-y: auto;
  }
  
  .order-info {
    background-color: #f9f9fc;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 20px;
  }
  
  .info-row {
    display: flex;
    margin-bottom: 8px;
  }
  
  .info-row:last-child {
    margin-bottom: 0;
  }
  
  .info-label {
    width: 120px;
    color: #718096;
    font-size: 0.95rem;
  }
  
  .info-value {
    font-weight: 500;
    color: #2d3748;
  }
  
  .info-value.status {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.85rem;
  }
  
  .items-title {
    font-weight: 600;
    color: #3B1E54;
    margin-bottom: 12px;
  }
  
  .detail-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  
  .detail-item {
    display: flex;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid #f1f1f1;
  }
  
  .detail-item:last-child {
    border-bottom: none;
  }
  
  .item-info {
    flex-grow: 1;
    margin-left: 12px;
  }
  
  .item-price-qty {
    color: #718096;
    font-size: 0.95rem;
    margin-top: 4px;
  }
  
  .modal-footer {
    padding: 16px 20px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
  }
  
  .close-details-btn {
  background-color: #EDF2F7;
  color: #2D3748;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-details-btn:hover {
  background-color: #E2E8F0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .order-meta {
    flex-direction: column;
    gap: 8px;
  }
  
  .order-footer {
    flex-direction: column;
    gap: 16px;
  }
  
  .order-actions {
    width: 100%;
    justify-content: space-between;
  }
  
  .order-item {
    flex-wrap: wrap;
  }
  
  .item-subtotal {
    width: 100%;
    text-align: right;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px dashed #f1f1f1;
  }
}

@media (max-width: 576px) {
  .order-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .order-status {
    align-self: flex-start;
  }
  
  .item-image-container {
    width: 60px;
    height: 60px;
  }
  
  .modal-content {
    width: 95%;
    padding: 20px;
  }
  
  .order-detail-modal {
    padding: 0;
  }
}
</style>